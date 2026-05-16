<?php
/**
 * AJAX request handlers.
 *
 * All handlers verify nonces and sanitize all input.
 *
 * @package    HolyRosary
 * @subpackage HolyRosary/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Holy_Rosary_Ajax
 */
class Holy_Rosary_Ajax {

	/**
	 * @var Holy_Rosary_Database $db Database instance.
	 */
	private $db;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->db = new Holy_Rosary_Database();
	}

	/**
	 * Save a completed Rosary session.
	 * Requires: logged-in user, valid nonce.
	 */
	public function save_session() {
		Holy_Rosary_Security::verify_nonce();
		Holy_Rosary_Security::require_logged_in();

		$mystery_set   = isset( $_POST['mystery_set'] ) ? Holy_Rosary_Security::sanitize_mystery_set( wp_unslash( $_POST['mystery_set'] ) ) : false;
		$duration_secs = isset( $_POST['duration_secs'] ) ? Holy_Rosary_Security::sanitize_absint( $_POST['duration_secs'] ) : 0;
		$intention     = isset( $_POST['intention'] ) ? Holy_Rosary_Security::sanitize_intention( $_POST['intention'] ) : '';

		if ( false === $mystery_set ) {
			wp_send_json_error(
				array( 'message' => esc_html__( 'Invalid mystery set.', 'holy-rosary' ) ),
				400
			);
		}

		$user_id    = get_current_user_id();
		$session_id = $this->db->save_session( $user_id, $mystery_set, $duration_secs, $intention );

		if ( false === $session_id ) {
			wp_send_json_error(
				array( 'message' => esc_html__( 'Could not save session. Please try again.', 'holy-rosary' ) ),
				500
			);
		}

		// Return updated stats.
		$streak = $this->db->get_streak( $user_id );

		wp_send_json_success(
			array(
				'session_id'     => $session_id,
				'current_streak' => $streak ? (int) $streak->current_streak : 1,
				'longest_streak' => $streak ? (int) $streak->longest_streak : 1,
				'total_sessions' => $streak ? (int) $streak->total_sessions : 1,
				'message'        => esc_html__( 'Rosary session saved. God bless you!', 'holy-rosary' ),
			)
		);
	}

	/**
	 * Get user stats (streak, sessions).
	 * Requires: logged-in user, valid nonce.
	 */
	public function get_stats() {
		Holy_Rosary_Security::verify_nonce_get();
		Holy_Rosary_Security::require_logged_in();

		$user_id  = get_current_user_id();
		$streak   = $this->db->get_streak( $user_id );
		$sessions = $this->db->get_user_sessions( $user_id, 5, 0 );

		wp_send_json_success(
			array(
				'streak'   => $streak,
				'sessions' => $sessions,
			)
		);
	}

	/**
	 * Save a prayer intention.
	 * Requires: logged-in user, valid nonce.
	 */
	public function save_intention() {
		Holy_Rosary_Security::verify_nonce();
		Holy_Rosary_Security::require_logged_in();

		$intention    = isset( $_POST['intention'] ) ? Holy_Rosary_Security::sanitize_intention( $_POST['intention'] ) : '';
		$is_anonymous = isset( $_POST['is_anonymous'] ) && '1' === $_POST['is_anonymous'];

		if ( empty( $intention ) ) {
			wp_send_json_error(
				array( 'message' => esc_html__( 'Intention cannot be empty.', 'holy-rosary' ) ),
				400
			);
		}

		if ( mb_strlen( $intention ) > 500 ) {
			wp_send_json_error(
				array( 'message' => esc_html__( 'Intention must be 500 characters or fewer.', 'holy-rosary' ) ),
				400
			);
		}

		$user_id      = get_current_user_id();
		$intention_id = $this->db->save_intention( $user_id, $intention, $is_anonymous );

		if ( false === $intention_id ) {
			wp_send_json_error(
				array( 'message' => esc_html__( 'Could not save intention. Please try again.', 'holy-rosary' ) ),
				500
			);
		}

		$settings   = get_option( 'holy_rosary_settings', array() );
		$moderation = ! empty( $settings['prayer_wall_moderation'] );

		wp_send_json_success(
			array(
				'intention_id' => $intention_id,
				'message'      => $moderation
					? esc_html__( 'Your intention has been submitted and is awaiting approval.', 'holy-rosary' )
					: esc_html__( 'Your intention has been added to the prayer wall.', 'holy-rosary' ),
			)
		);
	}

	/**
	 * Get approved intentions for the prayer wall.
	 * Available to all users (logged in and out).
	 */
	public function get_prayer_wall() {
		Holy_Rosary_Security::verify_nonce_get();

		$limit  = isset( $_GET['limit'] )  ? Holy_Rosary_Security::sanitize_absint( $_GET['limit'] )  : 20;
		$offset = isset( $_GET['offset'] ) ? Holy_Rosary_Security::sanitize_absint( $_GET['offset'] ) : 0;

		// Cap limit to prevent abuse.
		$limit = min( $limit, 50 );

		$intentions = $this->db->get_approved_intentions( $limit, $offset );

		// Mask display names for anonymous intentions.
		foreach ( $intentions as &$item ) {
			if ( $item->is_anonymous ) {
				$item->display_name = esc_html__( 'Anonymous', 'holy-rosary' );
			}
		}
		unset( $item );

		wp_send_json_success( array( 'intentions' => $intentions ) );
	}

	/**
	 * Increment pray count for an intention.
	 * Available to all users.
	 */
	public function pray_for_intention() {
		Holy_Rosary_Security::verify_nonce();

		$intention_id = isset( $_POST['intention_id'] ) ? Holy_Rosary_Security::sanitize_absint( $_POST['intention_id'] ) : 0;

		if ( 0 === $intention_id ) {
			wp_send_json_error(
				array( 'message' => esc_html__( 'Invalid intention.', 'holy-rosary' ) ),
				400
			);
		}

		$updated = $this->db->increment_pray_count( $intention_id );

		if ( ! $updated ) {
			wp_send_json_error(
				array( 'message' => esc_html__( 'Could not update pray count.', 'holy-rosary' ) ),
				500
			);
		}

		wp_send_json_success(
			array( 'message' => esc_html__( 'Your prayer has been counted.', 'holy-rosary' ) )
		);
	}
}

<?php
/**
 * Database abstraction layer for Holy Rosary custom tables.
 *
 * All queries use $wpdb->prepare() for security.
 *
 * @package    HolyRosary
 * @subpackage HolyRosary/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Holy_Rosary_Database
 */
class Holy_Rosary_Database {

	/**
	 * @var wpdb $wpdb WordPress DB object.
	 */
	private $wpdb;

	/**
	 * Table names.
	 *
	 * @var array $tables
	 */
	private $tables;

	/**
	 * Constructor.
	 */
	public function __construct() {
		global $wpdb;
		$this->wpdb   = $wpdb;
		$this->tables = array(
			'sessions'   => $wpdb->prefix . 'rosary_sessions',
			'journal'    => $wpdb->prefix . 'rosary_journal',
			'intentions' => $wpdb->prefix . 'rosary_intentions',
			'streaks'    => $wpdb->prefix . 'rosary_streaks',
		);
	}

	// ─────────────────────────────────────────────
	// SESSIONS
	// ─────────────────────────────────────────────

	/**
	 * Save a completed Rosary session.
	 *
	 * @param int    $user_id       WordPress user ID (0 for guest).
	 * @param string $mystery_set   Mystery set slug.
	 * @param int    $duration_secs Duration in seconds.
	 * @param string $intention     Optional intention text.
	 * @return int|false Inserted row ID or false on failure.
	 */
	public function save_session( $user_id, $mystery_set, $duration_secs, $intention = '' ) {
		$result = $this->wpdb->insert(
			$this->tables['sessions'],
			array(
				'user_id'       => absint( $user_id ),
				'mystery_set'   => sanitize_text_field( $mystery_set ),
				'duration_secs' => absint( $duration_secs ),
				'intention'     => sanitize_textarea_field( $intention ),
				'completed_at'  => current_time( 'mysql' ),
			),
			array( '%d', '%s', '%d', '%s', '%s' )
		);

		if ( false === $result ) {
			return false;
		}

		$session_id = $this->wpdb->insert_id;
		$this->update_streak( $user_id );
		return $session_id;
	}

	/**
	 * Get sessions for a user.
	 *
	 * @param int $user_id WordPress user ID.
	 * @param int $limit   Number of results to return.
	 * @param int $offset  Offset for pagination.
	 * @return array
	 */
	public function get_user_sessions( $user_id, $limit = 10, $offset = 0 ) {
		return $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT * FROM {$this->tables['sessions']}
				WHERE user_id = %d
				ORDER BY completed_at DESC
				LIMIT %d OFFSET %d",
				absint( $user_id ),
				absint( $limit ),
				absint( $offset )
			)
		);
	}

	// ─────────────────────────────────────────────
	// JOURNAL
	// ─────────────────────────────────────────────

	/**
	 * Save a journal entry.
	 *
	 * @param int    $user_id    WordPress user ID.
	 * @param int    $session_id Session ID.
	 * @param string $note       Journal note text.
	 * @return int|false
	 */
	public function save_journal_entry( $user_id, $session_id, $note ) {
		$result = $this->wpdb->insert(
			$this->tables['journal'],
			array(
				'user_id'    => absint( $user_id ),
				'session_id' => absint( $session_id ),
				'note'       => sanitize_textarea_field( $note ),
				'created_at' => current_time( 'mysql' ),
			),
			array( '%d', '%d', '%s', '%s' )
		);

		return false === $result ? false : $this->wpdb->insert_id;
	}

	/**
	 * Get journal entries for a user.
	 *
	 * @param int $user_id WordPress user ID.
	 * @param int $limit   Number of results.
	 * @param int $offset  Pagination offset.
	 * @return array
	 */
	public function get_journal_entries( $user_id, $limit = 10, $offset = 0 ) {
		return $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT j.*, s.mystery_set, s.completed_at
				FROM {$this->tables['journal']} j
				LEFT JOIN {$this->tables['sessions']} s ON j.session_id = s.id
				WHERE j.user_id = %d
				ORDER BY j.created_at DESC
				LIMIT %d OFFSET %d",
				absint( $user_id ),
				absint( $limit ),
				absint( $offset )
			)
		);
	}

	// ─────────────────────────────────────────────
	// STREAKS
	// ─────────────────────────────────────────────

	/**
	 * Update streak data for a user after a session.
	 *
	 * @param int $user_id WordPress user ID.
	 */
	public function update_streak( $user_id ) {
		if ( 0 === $user_id ) {
			return; // Guests don't get streak tracking.
		}

		$today  = gmdate( 'Y-m-d' );
		$streak = $this->get_streak( $user_id );

		if ( ! $streak ) {
			// First session ever.
			$this->wpdb->insert(
				$this->tables['streaks'],
				array(
					'user_id'        => absint( $user_id ),
					'current_streak' => 1,
					'longest_streak' => 1,
					'last_prayed_at' => $today,
					'total_sessions' => 1,
				),
				array( '%d', '%d', '%d', '%s', '%d' )
			);
			return;
		}

		$last_prayed    = $streak->last_prayed_at;
		$yesterday      = gmdate( 'Y-m-d', strtotime( '-1 day' ) );
		$current_streak = (int) $streak->current_streak;
		$total_sessions = (int) $streak->total_sessions + 1;

		if ( $last_prayed === $today ) {
			// Already prayed today — just increment total, no streak change.
			$this->wpdb->update(
				$this->tables['streaks'],
				array( 'total_sessions' => $total_sessions ),
				array( 'user_id' => absint( $user_id ) ),
				array( '%d' ),
				array( '%d' )
			);
			return;
		}

		if ( $last_prayed === $yesterday ) {
			// Consecutive day — extend streak.
			$current_streak++;
		} else {
			// Streak broken — reset to 1.
			$current_streak = 1;
		}

		$longest_streak = max( $current_streak, (int) $streak->longest_streak );

		$this->wpdb->update(
			$this->tables['streaks'],
			array(
				'current_streak' => $current_streak,
				'longest_streak' => $longest_streak,
				'last_prayed_at' => $today,
				'total_sessions' => $total_sessions,
			),
			array( 'user_id' => absint( $user_id ) ),
			array( '%d', '%d', '%s', '%d' ),
			array( '%d' )
		);
	}

	/**
	 * Get streak data for a user.
	 *
	 * @param int $user_id WordPress user ID.
	 * @return object|null
	 */
	public function get_streak( $user_id ) {
		return $this->wpdb->get_row(
			$this->wpdb->prepare(
				"SELECT * FROM {$this->tables['streaks']} WHERE user_id = %d",
				absint( $user_id )
			)
		);
	}

	// ─────────────────────────────────────────────
	// INTENTIONS / PRAYER WALL
	// ─────────────────────────────────────────────

	/**
	 * Save a prayer intention.
	 *
	 * @param int    $user_id      WordPress user ID.
	 * @param string $intention    Intention text.
	 * @param bool   $is_anonymous Whether to hide user identity.
	 * @return int|false
	 */
	public function save_intention( $user_id, $intention, $is_anonymous = false ) {
		$settings   = get_option( 'holy_rosary_settings', array() );
		$moderation = ! empty( $settings['prayer_wall_moderation'] );
		$status     = $moderation ? 'pending' : 'approved';

		$result = $this->wpdb->insert(
			$this->tables['intentions'],
			array(
				'user_id'      => absint( $user_id ),
				'intention'    => sanitize_textarea_field( $intention ),
				'is_anonymous' => $is_anonymous ? 1 : 0,
				'status'       => $status,
				'created_at'   => current_time( 'mysql' ),
			),
			array( '%d', '%s', '%d', '%s', '%s' )
		);

		return false === $result ? false : $this->wpdb->insert_id;
	}

	/**
	 * Get approved intentions for the prayer wall.
	 *
	 * @param int $limit  Number of intentions to return.
	 * @param int $offset Pagination offset.
	 * @return array
	 */
	public function get_approved_intentions( $limit = 20, $offset = 0 ) {
		$cache_key = "holy_rosary_wall_{$limit}_{$offset}";
		$cached    = wp_cache_get( $cache_key, 'holy_rosary' );

		if ( false !== $cached ) {
			return $cached;
		}

		$results = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT i.id, i.intention, i.is_anonymous, i.pray_count, i.created_at,
				        u.display_name
				FROM {$this->tables['intentions']} i
				LEFT JOIN {$this->wpdb->users} u ON i.user_id = u.ID
				WHERE i.status = 'approved'
				ORDER BY i.created_at DESC
				LIMIT %d OFFSET %d",
				absint( $limit ),
				absint( $offset )
			)
		);

		wp_cache_set( $cache_key, $results, 'holy_rosary', 300 );
		return $results;
	}

	/**
	 * Increment pray_count for an intention.
	 *
	 * @param int $intention_id Intention ID.
	 * @return bool
	 */
	public function increment_pray_count( $intention_id ) {
		$result = $this->wpdb->query(
			$this->wpdb->prepare(
				"UPDATE {$this->tables['intentions']}
				SET pray_count = pray_count + 1
				WHERE id = %d AND status = 'approved'",
				absint( $intention_id )
			)
		);

		// Bust cache.
		wp_cache_delete( 'holy_rosary_wall_20_0', 'holy_rosary' );

		return false !== $result;
	}

	/**
	 * Get pending intentions for admin moderation.
	 *
	 * @return array
	 */
	public function get_pending_intentions() {
		return $this->wpdb->get_results(
			"SELECT i.*, u.display_name
			FROM {$this->tables['intentions']} i
			LEFT JOIN {$this->wpdb->users} u ON i.user_id = u.ID
			WHERE i.status = 'pending'
			ORDER BY i.created_at ASC"
		);
	}

	/**
	 * Approve or reject an intention (admin only).
	 *
	 * @param int    $intention_id Intention ID.
	 * @param string $status       'approved' or 'rejected'.
	 * @return bool
	 */
	public function moderate_intention( $intention_id, $status ) {
		$allowed = array( 'approved', 'rejected' );
		if ( ! in_array( $status, $allowed, true ) ) {
			return false;
		}

		return (bool) $this->wpdb->update(
			$this->tables['intentions'],
			array( 'status' => $status ),
			array( 'id'     => absint( $intention_id ) ),
			array( '%s' ),
			array( '%d' )
		);
	}
}

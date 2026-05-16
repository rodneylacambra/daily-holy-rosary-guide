<?php
/**
 * Security helpers: nonces, sanitization, capability checks.
 *
 * @package    HolyRosary
 * @subpackage HolyRosary/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Holy_Rosary_Security
 */
class Holy_Rosary_Security {

	/**
	 * Nonce action for AJAX requests.
	 */
	const NONCE_ACTION = 'holy_rosary_nonce';

	/**
	 * Generate a nonce for use in templates and JS.
	 *
	 * @return string
	 */
	public static function create_nonce() {
		return wp_create_nonce( self::NONCE_ACTION );
	}

	/**
	 * Verify an AJAX nonce. Dies on failure.
	 */
	public static function verify_nonce() {
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
			wp_send_json_error(
				array( 'message' => esc_html__( 'Security check failed.', 'holy-rosary' ) ),
				403
			);
		}
	}

	/**
	 * Verify nonce from GET request. Dies on failure.
	 */
	public static function verify_nonce_get() {
		$nonce = isset( $_GET['nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['nonce'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
			wp_send_json_error(
				array( 'message' => esc_html__( 'Security check failed.', 'holy-rosary' ) ),
				403
			);
		}
	}

	/**
	 * Check if the current user is logged in. Dies with JSON error if not.
	 */
	public static function require_logged_in() {
		if ( ! is_user_logged_in() ) {
			wp_send_json_error(
				array( 'message' => esc_html__( 'You must be logged in to perform this action.', 'holy-rosary' ) ),
				401
			);
		}
	}

	/**
	 * Check if the current user has admin capability.
	 *
	 * @return bool
	 */
	public static function current_user_is_admin() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Sanitize a mystery set string.
	 *
	 * @param string $mystery_set The mystery set value.
	 * @return string|false Sanitized value or false if invalid.
	 */
	public static function sanitize_mystery_set( $mystery_set ) {
		$allowed = array( 'joyful', 'luminous', 'sorrowful', 'glorious' );
		$value   = strtolower( sanitize_text_field( $mystery_set ) );
		return in_array( $value, $allowed, true ) ? $value : false;
	}

	/**
	 * Sanitize an intention text.
	 *
	 * @param string $intention Raw intention text.
	 * @return string
	 */
	public static function sanitize_intention( $intention ) {
		return sanitize_textarea_field( wp_unslash( $intention ) );
	}

	/**
	 * Sanitize a positive integer.
	 *
	 * @param mixed $value The value to sanitize.
	 * @return int
	 */
	public static function sanitize_absint( $value ) {
		return absint( $value );
	}
}

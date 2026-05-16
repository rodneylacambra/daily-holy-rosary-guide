<?php
/**
 * Fired during plugin deactivation.
 *
 * @package    HolyRosary
 * @subpackage HolyRosary/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Holy_Rosary_Deactivator
 */
class Holy_Rosary_Deactivator {

	/**
	 * Run on plugin deactivation.
	 *
	 * Clears scheduled cron jobs and flushes rewrite rules.
	 * Does NOT remove data — that is handled by uninstall.php.
	 */
	public static function deactivate() {
		// Clear any scheduled cron events.
		$timestamp = wp_next_scheduled( 'holy_rosary_daily_reminder' );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, 'holy_rosary_daily_reminder' );
		}

		// Clear transients.
		delete_transient( 'holy_rosary_prayer_wall' );

		// Flush rewrite rules.
		flush_rewrite_rules();
	}
}

<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * Removes all plugin data: options, custom tables, transients.
 *
 * @package HolyRosary
 */

// If uninstall not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

// Only remove data if the option is set.
$remove_data = get_option( 'holy_rosary_remove_data_on_uninstall', false );

if ( ! $remove_data ) {
	return;
}

// Remove plugin options.
$options = array(
	'holy_rosary_version',
	'holy_rosary_settings',
	'holy_rosary_remove_data_on_uninstall',
	'holy_rosary_db_version',
);

foreach ( $options as $option ) {
	delete_option( $option );
}

// Remove custom database tables.
$tables = array(
	$wpdb->prefix . 'rosary_sessions',
	$wpdb->prefix . 'rosary_journal',
	$wpdb->prefix . 'rosary_intentions',
	$wpdb->prefix . 'rosary_streaks',
);

foreach ( $tables as $table ) {
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.NoCaching
	$wpdb->query( "DROP TABLE IF EXISTS `{$table}`" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
}

// Remove user meta.
$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	"DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE 'holy_rosary_%'"
);

// Clear any cached data.
wp_cache_flush();

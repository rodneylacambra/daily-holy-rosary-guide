<?php
/**
 * Fired during plugin activation.
 *
 * Creates custom database tables and sets default options.
 *
 * @package    HolyRosary
 * @subpackage HolyRosary/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Holy_Rosary_Activator
 */
class Holy_Rosary_Activator {

	/**
	 * Run on plugin activation.
	 *
	 * Creates custom tables and sets default plugin options.
	 */
	public static function activate() {
		self::create_tables();
		self::set_default_options();

		// Store DB version for future migrations.
		update_option( 'holy_rosary_db_version', '1.0.0' );

		// Flush rewrite rules.
		flush_rewrite_rules();
	}

	/**
	 * Create custom database tables using dbDelta.
	 */
	private static function create_tables() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		// Sessions table: one row per completed Rosary.
		$sql_sessions = "CREATE TABLE {$wpdb->prefix}rosary_sessions (
			id            bigint(20)   UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id       bigint(20)   UNSIGNED NOT NULL DEFAULT 0,
			mystery_set   varchar(20)  NOT NULL,
			completed_at  datetime     NOT NULL DEFAULT CURRENT_TIMESTAMP,
			duration_secs int(11)      UNSIGNED NOT NULL DEFAULT 0,
			intention     text         DEFAULT NULL,
			PRIMARY KEY  (id),
			KEY user_id (user_id),
			KEY completed_at (completed_at)
		) $charset_collate;";

		// Journal table: optional notes per session.
		$sql_journal = "CREATE TABLE {$wpdb->prefix}rosary_journal (
			id         bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id    bigint(20) UNSIGNED NOT NULL DEFAULT 0,
			session_id bigint(20) UNSIGNED NOT NULL DEFAULT 0,
			note       text       NOT NULL,
			created_at datetime   NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY user_id (user_id),
			KEY session_id (session_id)
		) $charset_collate;";

		// Intentions/prayer wall.
		$sql_intentions = "CREATE TABLE {$wpdb->prefix}rosary_intentions (
			id           bigint(20)   UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id      bigint(20)   UNSIGNED NOT NULL DEFAULT 0,
			intention    text         NOT NULL,
			is_anonymous tinyint(1)   UNSIGNED NOT NULL DEFAULT 0,
			status       varchar(20)  NOT NULL DEFAULT 'pending',
			pray_count   int(11)      UNSIGNED NOT NULL DEFAULT 0,
			created_at   datetime     NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY user_id (user_id),
			KEY status (status),
			KEY created_at (created_at)
		) $charset_collate;";

		// Streaks table: cached per-user streak data.
		$sql_streaks = "CREATE TABLE {$wpdb->prefix}rosary_streaks (
			id              bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id         bigint(20) UNSIGNED NOT NULL DEFAULT 0,
			current_streak  int(11)    UNSIGNED NOT NULL DEFAULT 0,
			longest_streak  int(11)    UNSIGNED NOT NULL DEFAULT 0,
			last_prayed_at  date       DEFAULT NULL,
			total_sessions  int(11)    UNSIGNED NOT NULL DEFAULT 0,
			PRIMARY KEY  (id),
			UNIQUE KEY user_id (user_id)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql_sessions );
		dbDelta( $sql_journal );
		dbDelta( $sql_intentions );
		dbDelta( $sql_streaks );
	}

	/**
	 * Set default plugin options on first activation.
	 */
	private static function set_default_options() {
		$defaults = array(
			'show_date_banner'            => true,
			'auto_detect_mystery'         => true,
			'show_decade_dots'            => true,
			'enable_audio'                => true,
			'enable_prayer_wall'          => true,
			'prayer_wall_moderation'      => true,
			'remove_data_on_uninstall'    => false,
		);

		// add_option will not overwrite if the option already exists.
		add_option( 'holy_rosary_settings', $defaults );
	}
}

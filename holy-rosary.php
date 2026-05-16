<?php
/**
 * Holy Rosary
 *
 * @package           HolyRosary
 * @author            Rodney Lacambra
 * @copyright         2026 Rodney Web Solutions
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Holy Rosary
 * Plugin URI:        https://github.com/rodneylacambra/holy-rosary-interactive-guide
 * Description:       A beautiful, interactive step-by-step Holy Rosary prayer guide for your WordPress site. Includes all 4 mystery sets, auto day detection, full prayer texts, and closing prayers.
 * Version:           1.0.0
 * Requires at least: 5.9
 * Requires PHP:      7.4
 * Author:            Rodney Lacambra
 * Author URI:        https://www.rodneywebsolutions.com/
 * Text Domain:       holy-rosary
 * Domain Path:       /languages
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin constants.
define( 'HOLY_ROSARY_VERSION',     '1.0.0' );
define( 'HOLY_ROSARY_PLUGIN_FILE', __FILE__ );
define( 'HOLY_ROSARY_PLUGIN_DIR',  plugin_dir_path( __FILE__ ) );
define( 'HOLY_ROSARY_PLUGIN_URL',  plugin_dir_url( __FILE__ ) );
define( 'HOLY_ROSARY_PLUGIN_BASE', plugin_basename( __FILE__ ) );
define( 'HOLY_ROSARY_MIN_PHP',     '7.4' );
define( 'HOLY_ROSARY_MIN_WP',      '5.9' );

/**
 * Check PHP and WordPress version requirements.
 *
 * @return bool
 */
function holy_rosary_requirements_met() {
	if ( version_compare( PHP_VERSION, HOLY_ROSARY_MIN_PHP, '<' ) ) {
		add_action(
			'admin_notices',
			function () {
				printf(
					'<div class="notice notice-error"><p>%s</p></div>',
					sprintf(
						/* translators: 1: required PHP version, 2: current PHP version */
						esc_html__( 'Holy Rosary requires PHP %1$s or higher. You are running PHP %2$s. Please upgrade PHP.', 'holy-rosary' ),
						esc_html( HOLY_ROSARY_MIN_PHP ),
						esc_html( PHP_VERSION )
					)
				);
			}
		);
		return false;
	}

	global $wp_version;
	if ( version_compare( $wp_version, HOLY_ROSARY_MIN_WP, '<' ) ) {
		add_action(
			'admin_notices',
			function () use ( $wp_version ) {
				printf(
					'<div class="notice notice-error"><p>%s</p></div>',
					sprintf(
						/* translators: 1: required WP version, 2: current WP version */
						esc_html__( 'Holy Rosary requires WordPress %1$s or higher. You are running WordPress %2$s. Please upgrade WordPress.', 'holy-rosary' ),
						esc_html( HOLY_ROSARY_MIN_WP ),
						esc_html( $wp_version )
					)
				);
			}
		);
		return false;
	}

	return true;
}

/**
 * Autoloader for plugin classes.
 *
 * @param string $class_name The fully-qualified class name.
 */
function holy_rosary_autoloader( $class_name ) {
	// Only handle our namespace.
	if ( strpos( $class_name, 'Holy_Rosary' ) !== 0 ) {
		return;
	}

	// Convert class name to file path.
	$file = strtolower( str_replace( '_', '-', $class_name ) );
	$file = 'class-' . str_replace( 'holy-rosary-', '', $file ) . '.php';

	$locations = array(
		HOLY_ROSARY_PLUGIN_DIR . 'includes/' . $file,
		HOLY_ROSARY_PLUGIN_DIR . 'admin/' . $file,
		HOLY_ROSARY_PLUGIN_DIR . 'public/' . $file,
	);

	foreach ( $locations as $path ) {
		if ( file_exists( $path ) ) {
			require_once $path;
			return;
		}
	}
}
spl_autoload_register( 'holy_rosary_autoloader' );

/**
 * Activation hook.
 */
function holy_rosary_activate() {
	require_once HOLY_ROSARY_PLUGIN_DIR . 'includes/class-activator.php';
	Holy_Rosary_Activator::activate();
}
register_activation_hook( __FILE__, 'holy_rosary_activate' );

/**
 * Deactivation hook.
 */
function holy_rosary_deactivate() {
	require_once HOLY_ROSARY_PLUGIN_DIR . 'includes/class-deactivator.php';
	Holy_Rosary_Deactivator::deactivate();
}
register_deactivation_hook( __FILE__, 'holy_rosary_deactivate' );

/**
 * Bootstrap the plugin.
 */
function holy_rosary_init() {
	if ( ! holy_rosary_requirements_met() ) {
		return;
	}

	require_once HOLY_ROSARY_PLUGIN_DIR . 'includes/class-holy-rosary.php';
	$plugin = new Holy_Rosary();
	$plugin->run();
}
add_action( 'plugins_loaded', 'holy_rosary_init' );

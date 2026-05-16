<?php
/**
 * Define the internationalization functionality.
 *
 * @package    HolyRosary
 * @subpackage HolyRosary/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Holy_Rosary_I18n
 */
class Holy_Rosary_I18n {

	/**
	 * Load the plugin text domain for translation.
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			'holy-rosary',
			false,
			dirname( HOLY_ROSARY_PLUGIN_BASE ) . '/languages/'
		);
	}
}

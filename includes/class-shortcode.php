<?php
/**
 * Shortcode handler for [holy_rosary].
 *
 * Usage: [holy_rosary]
 * With attributes: [holy_rosary mystery="joyful" show_wall="true"]
 *
 * @package    HolyRosary
 * @subpackage HolyRosary/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Holy_Rosary_Shortcode
 */
class Holy_Rosary_Shortcode {

	/**
	 * Plugin version.
	 *
	 * @var string $version
	 */
	private $version;

	/**
	 * Constructor.
	 *
	 * @param string $version Plugin version.
	 */
	public function __construct( $version ) {
		$this->version = $version;
	}

	/**
	 * Register the shortcode.
	 */
	public function register() {
		add_shortcode( 'holy_rosary', array( $this, 'render' ) );
	}

	/**
	 * Render the shortcode output.
	 *
	 * @param array  $atts    Shortcode attributes.
	 * @param string $content Inner content (not used).
	 * @return string HTML output.
	 */
	public function render( $atts, $content = '' ) {
		$atts = shortcode_atts(
			array(
				'mystery'    => 'auto',   // auto | joyful | luminous | sorrowful | glorious
				'show_wall'  => 'true',   // true | false
				'show_stats' => 'true',   // true | false (logged-in only)
			),
			$atts,
			'holy_rosary'
		);

		// Validate mystery attribute.
		$allowed_mysteries = array( 'auto', 'joyful', 'luminous', 'sorrowful', 'glorious' );
		if ( ! in_array( $atts['mystery'], $allowed_mysteries, true ) ) {
			$atts['mystery'] = 'auto';
		}

		$atts['show_wall']  = filter_var( $atts['show_wall'],  FILTER_VALIDATE_BOOLEAN );
		$atts['show_stats'] = filter_var( $atts['show_stats'], FILTER_VALIDATE_BOOLEAN );

		// Respect global admin setting — if prayer wall is disabled site-wide, override shortcode.
		$global_settings = get_option( 'holy_rosary_settings', array() );
		if ( empty( $global_settings['enable_prayer_wall'] ) ) {
			$atts['show_wall'] = false;
		}

		// Ensure assets are enqueued when shortcode is used.
		if ( ! wp_style_is( 'holy-rosary', 'enqueued' ) ) {
			wp_enqueue_style( 'holy-rosary' );
			wp_enqueue_script( 'holy-rosary' );
		}

		ob_start();
		include HOLY_ROSARY_PLUGIN_DIR . 'public/partials/rosary-display.php';
		return ob_get_clean();
	}
}

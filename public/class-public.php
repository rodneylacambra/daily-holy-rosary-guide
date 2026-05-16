<?php
/**
 * Public-facing functionality.
 *
 * @package    HolyRosary
 * @subpackage HolyRosary/public
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Holy_Rosary_Public
 */
class Holy_Rosary_Public {

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
	 * Enqueue frontend styles.
	 * Only loads on pages where the shortcode or block is present.
	 */
	public function enqueue_styles() {
		wp_register_style(
			'holy-rosary',
			HOLY_ROSARY_PLUGIN_URL . 'public/css/holy-rosary.css',
			array(),
			$this->version
		);
	}

	/**
	 * Enqueue frontend scripts.
	 */
	public function enqueue_scripts() {
		// Core rosary script.
		wp_register_script(
			'holy-rosary',
			HOLY_ROSARY_PLUGIN_URL . 'public/js/holy-rosary.js',
			array(),
			$this->version,
			true // Load in footer.
		);

		// Audio module — registered separately, enqueued only when audio is enabled.
		wp_register_script(
			'holy-rosary-audio',
			HOLY_ROSARY_PLUGIN_URL . 'public/js/holy-rosary-audio.js',
			array( 'holy-rosary' ), // Depends on core script.
			$this->version,
			true
		);

		$options       = get_option( 'holy_rosary_settings', array() );
		$audio_enabled = ! empty( $options['enable_audio'] );

		if ( $audio_enabled ) {
			wp_enqueue_script( 'holy-rosary-audio' );
		}

		// Pass data to JS.
		wp_localize_script(
			'holy-rosary',
			'holyRosaryData',
			array(
				'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
				'nonce'      => Holy_Rosary_Security::create_nonce(),
				'isLoggedIn' => is_user_logged_in(),
				'userId'     => get_current_user_id(),
				'settings'   => $this->get_public_settings(),
				'i18n'       => $this->get_i18n_strings(),
			)
		);
	}

	/**
	 * Register the Gutenberg block.
	 */
	public function register_block() {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		register_block_type(
			'holy-rosary/rosary',
			array(
				'editor_script'   => 'holy-rosary-block-editor',
				'render_callback' => array( $this, 'render_block' ),
				'attributes'      => array(
					'mystery'   => array( 'type' => 'string', 'default' => 'auto' ),
					'showWall'  => array( 'type' => 'boolean', 'default' => true ),
					'showStats' => array( 'type' => 'boolean', 'default' => true ),
				),
			)
		);
	}

	/**
	 * Render callback for the Gutenberg block.
	 *
	 * @param array $attributes Block attributes.
	 * @return string HTML output.
	 */
	public function render_block( $attributes ) {
		$atts = array(
			'mystery'    => $attributes['mystery']   ?? 'auto',
			'show_wall'  => $attributes['showWall']  ?? true,
			'show_stats' => $attributes['showStats'] ?? true,
		);

		if ( ! wp_style_is( 'holy-rosary', 'enqueued' ) ) {
			wp_enqueue_style( 'holy-rosary' );
			wp_enqueue_script( 'holy-rosary' );
		}

		ob_start();
		include HOLY_ROSARY_PLUGIN_DIR . 'public/partials/rosary-display.php';
		return ob_get_clean();
	}

	/**
	 * Get sanitized public-facing settings to pass to JS.
	 *
	 * @return array
	 */
	private function get_public_settings() {
		$options = get_option( 'holy_rosary_settings', array() );
		return array(
			'showDateBanner'       => ! empty( $options['show_date_banner'] ),
			'autoDetectMystery'    => ! empty( $options['auto_detect_mystery'] ),
			'showDecadeDots'       => ! empty( $options['show_decade_dots'] ),
			'enablePrayerWall'     => ! empty( $options['enable_prayer_wall'] ),
			'enableAudio'          => ! empty( $options['enable_audio'] ),
		);
	}

	/**
	 * Get all i18n strings for the frontend JS.
	 *
	 * @return array
	 */
	private function get_i18n_strings() {
		return array(
			'welcome'          => esc_html__( 'Welcome! Let us begin and meditate on the %s of the Holy Rosary.', 'holy-rosary' ),
			'holdRosary'       => esc_html__( 'Hold your rosary, find a quiet place, and let us pray together.', 'holy-rosary' ),
			'tapToBegin'       => esc_html__( 'Your progress — tap Next to begin', 'holy-rosary' ),
			'complete'         => esc_html__( '🎉 Rosary complete! God bless you.', 'holy-rosary' ),
			'progress'         => esc_html__( 'Progress: %d%% complete', 'holy-rosary' ),
			'todayMystery'     => esc_html__( 'Today\'s Mystery:', 'holy-rosary' ),
			'todayIs'          => esc_html__( '📅 Today is %s, %s %s %s', 'holy-rosary' ),
			'stepOf'           => esc_html__( 'Step %d of %d', 'holy-rosary' ),
			'savingSession'    => esc_html__( 'Saving your session...', 'holy-rosary' ),
			'sessionSaved'     => esc_html__( 'Session saved! God bless you.', 'holy-rosary' ),
			'loginToSave'      => esc_html__( 'Log in to save your progress and track your streak.', 'holy-rosary' ),
			'additionalPrayer' => esc_html__( 'Additional Prayer', 'holy-rosary' ),
			'heartClosing'     => esc_html__( 'Heart — Closing Prayers', 'holy-rosary' ),
			// Day names.
			'days'             => array(
				esc_html__( 'Sunday', 'holy-rosary' ),
				esc_html__( 'Monday', 'holy-rosary' ),
				esc_html__( 'Tuesday', 'holy-rosary' ),
				esc_html__( 'Wednesday', 'holy-rosary' ),
				esc_html__( 'Thursday', 'holy-rosary' ),
				esc_html__( 'Friday', 'holy-rosary' ),
				esc_html__( 'Saturday', 'holy-rosary' ),
			),
			// Month names.
			'months'           => array(
				esc_html__( 'January', 'holy-rosary' ),
				esc_html__( 'February', 'holy-rosary' ),
				esc_html__( 'March', 'holy-rosary' ),
				esc_html__( 'April', 'holy-rosary' ),
				esc_html__( 'May', 'holy-rosary' ),
				esc_html__( 'June', 'holy-rosary' ),
				esc_html__( 'July', 'holy-rosary' ),
				esc_html__( 'August', 'holy-rosary' ),
				esc_html__( 'September', 'holy-rosary' ),
				esc_html__( 'October', 'holy-rosary' ),
				esc_html__( 'November', 'holy-rosary' ),
				esc_html__( 'December', 'holy-rosary' ),
			),
		);
	}
}

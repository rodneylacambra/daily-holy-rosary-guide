<?php
/**
 * Admin-facing functionality.
 *
 * @package    HolyRosary
 * @subpackage HolyRosary/admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Holy_Rosary_Admin
 */
class Holy_Rosary_Admin {

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
	 * Enqueue admin styles.
	 *
	 * @param string $hook_suffix The current admin page hook.
	 */
	public function enqueue_styles( $hook_suffix ) {
		if ( 'toplevel_page_holy-rosary' !== $hook_suffix ) {
			return;
		}

		wp_enqueue_style(
			'holy-rosary-admin',
			HOLY_ROSARY_PLUGIN_URL . 'admin/css/admin.css',
			array(),
			$this->version
		);
	}

	/**
	 * Enqueue admin scripts.
	 *
	 * @param string $hook_suffix The current admin page hook.
	 */
	public function enqueue_scripts( $hook_suffix ) {
		if ( 'toplevel_page_holy-rosary' !== $hook_suffix ) {
			return;
		}

		wp_enqueue_script(
			'holy-rosary-admin',
			HOLY_ROSARY_PLUGIN_URL . 'admin/js/admin.js',
			array( 'jquery' ),
			$this->version,
			true
		);

		wp_localize_script(
			'holy-rosary-admin',
			'holyRosaryAdmin',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => Holy_Rosary_Security::create_nonce(),
				'i18n'    => array(
					'confirmApprove' => esc_html__( 'Approve this intention?', 'holy-rosary' ),
					'confirmReject'  => esc_html__( 'Reject this intention?', 'holy-rosary' ),
					'saved'          => esc_html__( 'Settings saved.', 'holy-rosary' ),
				),
			)
		);
	}

	/**
	 * Add the plugin admin menu page.
	 */
	public function add_plugin_admin_menu() {
		add_menu_page(
			esc_html__( 'Holy Rosary', 'holy-rosary' ),
			esc_html__( 'Holy Rosary', 'holy-rosary' ),
			'manage_options',
			'holy-rosary',
			array( $this, 'display_plugin_admin_page' ),
			'dashicons-heart',
			30
		);

		add_submenu_page(
			'holy-rosary',
			esc_html__( 'Settings', 'holy-rosary' ),
			esc_html__( 'Settings', 'holy-rosary' ),
			'manage_options',
			'holy-rosary',
			array( $this, 'display_plugin_admin_page' )
		);

		add_submenu_page(
			'holy-rosary',
			esc_html__( 'Prayer Wall', 'holy-rosary' ),
			esc_html__( 'Prayer Wall', 'holy-rosary' ),
			'manage_options',
			'holy-rosary-wall',
			array( $this, 'display_prayer_wall_page' )
		);
	}

	/**
	 * Render the main settings admin page.
	 */
	public function display_plugin_admin_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'holy-rosary' ) );
		}
		include HOLY_ROSARY_PLUGIN_DIR . 'admin/partials/admin-display.php';
	}

	/**
	 * Render the prayer wall moderation page.
	 */
	public function display_prayer_wall_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'holy-rosary' ) );
		}
		include HOLY_ROSARY_PLUGIN_DIR . 'admin/partials/admin-prayer-wall.php';
	}

	/**
	 * Register plugin settings using the Settings API.
	 */
	public function register_settings() {
		register_setting(
			'holy_rosary_settings_group',
			'holy_rosary_settings',
			array(
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
			)
		);

		// ── General Section ──
		add_settings_section(
			'holy_rosary_general',
			esc_html__( 'General Settings', 'holy-rosary' ),
			null,
			'holy-rosary'
		);

		add_settings_field(
			'show_date_banner',
			esc_html__( 'Show Date Banner', 'holy-rosary' ),
			array( $this, 'render_checkbox_field' ),
			'holy-rosary',
			'holy_rosary_general',
			array( 'key' => 'show_date_banner', 'label' => esc_html__( 'Display today\'s date and mystery at the top', 'holy-rosary' ) )
		);

		add_settings_field(
			'auto_detect_mystery',
			esc_html__( 'Auto-Detect Mystery', 'holy-rosary' ),
			array( $this, 'render_checkbox_field' ),
			'holy-rosary',
			'holy_rosary_general',
			array( 'key' => 'auto_detect_mystery', 'label' => esc_html__( 'Automatically select the correct mystery set based on the day of the week', 'holy-rosary' ) )
		);

		add_settings_field(
			'show_decade_dots',
			esc_html__( 'Show Decade Dots', 'holy-rosary' ),
			array( $this, 'render_checkbox_field' ),
			'holy-rosary',
			'holy_rosary_general',
			array( 'key' => 'show_decade_dots', 'label' => esc_html__( 'Show bead progress dots during each decade', 'holy-rosary' ) )
		);

		add_settings_field(
			'enable_audio',
			esc_html__( 'Enable Audio Mode', 'holy-rosary' ),
			array( $this, 'render_checkbox_field' ),
			'holy-rosary',
			'holy_rosary_general',
			array( 'key' => 'enable_audio', 'label' => esc_html__( 'Enable text-to-speech audio guide (uses built-in browser voices — no cost, no external service)', 'holy-rosary' ) )
		);

		// ── Prayer Wall Section ──
		add_settings_section(
			'holy_rosary_wall',
			esc_html__( 'Community Prayer Wall', 'holy-rosary' ),
			null,
			'holy-rosary'
		);

		add_settings_field(
			'enable_prayer_wall',
			esc_html__( 'Enable Prayer Wall', 'holy-rosary' ),
			array( $this, 'render_checkbox_field' ),
			'holy-rosary',
			'holy_rosary_wall',
			array( 'key' => 'enable_prayer_wall', 'label' => esc_html__( 'Allow users to submit and view community prayer intentions', 'holy-rosary' ) )
		);

		add_settings_field(
			'prayer_wall_moderation',
			esc_html__( 'Moderate Intentions', 'holy-rosary' ),
			array( $this, 'render_checkbox_field' ),
			'holy-rosary',
			'holy_rosary_wall',
			array( 'key' => 'prayer_wall_moderation', 'label' => esc_html__( 'Require admin approval before intentions appear on the prayer wall', 'holy-rosary' ) )
		);

		// ── Advanced Section ──
		add_settings_section(
			'holy_rosary_advanced',
			esc_html__( 'Advanced', 'holy-rosary' ),
			null,
			'holy-rosary'
		);

		add_settings_field(
			'remove_data_on_uninstall',
			esc_html__( 'Remove Data on Uninstall', 'holy-rosary' ),
			array( $this, 'render_checkbox_field' ),
			'holy-rosary',
			'holy_rosary_advanced',
			array( 'key' => 'remove_data_on_uninstall', 'label' => esc_html__( 'Delete all plugin data (sessions, journal, intentions, streaks) when the plugin is uninstalled', 'holy-rosary' ) )
		);
	}

	/**
	 * Render a checkbox settings field.
	 *
	 * @param array $args Field arguments.
	 */
	public function render_checkbox_field( $args ) {
		$options = get_option( 'holy_rosary_settings', array() );
		$value   = ! empty( $options[ $args['key'] ] );
		printf(
			'<label><input type="checkbox" name="holy_rosary_settings[%1$s]" value="1" %2$s /> %3$s</label>',
			esc_attr( $args['key'] ),
			checked( $value, true, false ),
			esc_html( $args['label'] )
		);
	}

	/**
	 * Sanitize settings before saving.
	 *
	 * @param array $input Raw input from the settings form.
	 * @return array
	 */
	public function sanitize_settings( $input ) {
		$sanitized = array();
		$checkboxes = array(
			'show_date_banner',
			'auto_detect_mystery',
			'show_decade_dots',
			'enable_audio',
			'enable_prayer_wall',
			'prayer_wall_moderation',
			'remove_data_on_uninstall',
		);

		foreach ( $checkboxes as $key ) {
			$sanitized[ $key ] = ! empty( $input[ $key ] );
		}

		return $sanitized;
	}

	/**
	 * Add Settings link to the plugins list page.
	 *
	 * @param array $links Existing action links.
	 * @return array
	 */
	public function add_action_links( $links ) {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'admin.php?page=holy-rosary' ) ),
			esc_html__( 'Settings', 'holy-rosary' )
		);
		array_unshift( $links, $settings_link );
		return $links;
	}
}

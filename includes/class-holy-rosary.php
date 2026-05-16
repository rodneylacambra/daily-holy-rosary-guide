<?php
/**
 * The core plugin class.
 *
 * Maintains the plugin version, defines internationalization,
 * admin-specific hooks, and public-facing site hooks.
 *
 * @package    HolyRosary
 * @subpackage HolyRosary/includes
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Holy_Rosary
 *
 * Core plugin orchestrator. Registers all hooks via the loader.
 */
class Holy_Rosary {

	/**
	 * The loader that's responsible for maintaining and registering all hooks.
	 *
	 * @var Holy_Rosary_Loader $loader
	 */
	protected $loader;

	/**
	 * The current version of the plugin.
	 *
	 * @var string $version
	 */
	protected $version;

	/**
	 * Constructor. Sets version and initialises the loader.
	 */
	public function __construct() {
		$this->version = HOLY_ROSARY_VERSION;
		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load required dependencies.
	 */
	private function load_dependencies() {
		require_once HOLY_ROSARY_PLUGIN_DIR . 'includes/class-loader.php';
		require_once HOLY_ROSARY_PLUGIN_DIR . 'includes/class-i18n.php';
		require_once HOLY_ROSARY_PLUGIN_DIR . 'includes/class-database.php';
		require_once HOLY_ROSARY_PLUGIN_DIR . 'includes/class-security.php';
		require_once HOLY_ROSARY_PLUGIN_DIR . 'includes/class-ajax.php';
		require_once HOLY_ROSARY_PLUGIN_DIR . 'includes/class-shortcode.php';
		require_once HOLY_ROSARY_PLUGIN_DIR . 'admin/class-admin.php';
		require_once HOLY_ROSARY_PLUGIN_DIR . 'public/class-public.php';

		$this->loader = new Holy_Rosary_Loader();
	}

	/**
	 * Define the locale for internationalization.
	 */
	private function set_locale() {
		$plugin_i18n = new Holy_Rosary_I18n();
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all admin-facing hooks.
	 */
	private function define_admin_hooks() {
		$plugin_admin = new Holy_Rosary_Admin( $this->version );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_admin_menu' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'register_settings' );
		$this->loader->add_filter( 'plugin_action_links_' . HOLY_ROSARY_PLUGIN_BASE, $plugin_admin, 'add_action_links' );
	}

	/**
	 * Register all public-facing hooks.
	 */
	private function define_public_hooks() {
		$plugin_public = new Holy_Rosary_Public( $this->version );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		// Shortcode.
		$shortcode = new Holy_Rosary_Shortcode( $this->version );
		$this->loader->add_action( 'init', $shortcode, 'register' );

		// AJAX handlers (logged-in and non-logged-in users).
		$ajax = new Holy_Rosary_Ajax();
		$this->loader->add_action( 'wp_ajax_holy_rosary_save_session',     $ajax, 'save_session' );
		$this->loader->add_action( 'wp_ajax_holy_rosary_get_stats',        $ajax, 'get_stats' );
		$this->loader->add_action( 'wp_ajax_holy_rosary_save_intention',   $ajax, 'save_intention' );
		$this->loader->add_action( 'wp_ajax_nopriv_holy_rosary_get_wall',  $ajax, 'get_prayer_wall' );
		$this->loader->add_action( 'wp_ajax_holy_rosary_get_wall',         $ajax, 'get_prayer_wall' );
		$this->loader->add_action( 'wp_ajax_holy_rosary_pray_for',         $ajax, 'pray_for_intention' );
		$this->loader->add_action( 'wp_ajax_nopriv_holy_rosary_pray_for',  $ajax, 'pray_for_intention' );

		// Gutenberg block.
		$this->loader->add_action( 'init', $plugin_public, 'register_block' );
	}

	/**
	 * Run the plugin loader.
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * Get the plugin version.
	 *
	 * @return string
	 */
	public function get_version() {
		return $this->version;
	}
}

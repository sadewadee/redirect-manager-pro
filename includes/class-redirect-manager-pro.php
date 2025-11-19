<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://example.com
 * @since      1.0.0
 * @package    Redirect_Manager_Pro
 * @subpackage Redirect_Manager_Pro/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * @since      1.0.0
 * @package    Redirect_Manager_Pro
 * @subpackage Redirect_Manager_Pro/includes
 * @author     Sadewadee
 */
class Redirect_Manager_Pro {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Redirect_Manager_Pro_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $redirect_manager_pro    The string used to uniquely identify this plugin.
	 */
	protected $redirect_manager_pro;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'RMP_VERSION' ) ) {
			$this->version = RMP_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->redirect_manager_pro = 'redirect-manager-pro';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_redirect_hooks();
		$this->define_typo_hooks();
		$this->define_scanner_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Redirect_Manager_Pro_Loader. Orchestrates the hooks of the plugin.
	 * - Redirect_Manager_Pro_i18n. Defines internationalization functionality.
	 * - Redirect_Manager_Pro_Admin. Defines all hooks for the admin area.
	 * - Redirect_Manager_Pro_Public. Defines all hooks for the public side of the site.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-rmp-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-rmp-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-rmp-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-rmp-public.php';

		/**
		 * The class responsible for defining the core redirection logic.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-rmp-redirector.php';

		/**
		 * The class responsible for 404 monitoring and typo detection.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-rmp-typo-fixer.php';

		/**
		 * The class responsible for link scanning.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-rmp-scanner.php';

		$this->loader = new Redirect_Manager_Pro_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Redirect_Manager_Pro_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Redirect_Manager_Pro_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Redirect_Manager_Pro_Admin( $this->get_redirect_manager_pro(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_admin_menu' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'handle_post_requests' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Redirect_Manager_Pro_Public( $this->get_redirect_manager_pro(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Register all of the hooks related to the redirection functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_redirect_hooks() {

		$plugin_redirector = new Redirect_Manager_Pro_Redirector();

		$this->loader->add_action( 'template_redirect', $plugin_redirector, 'redirect' );

	}

	/**
	 * Register all of the hooks related to the typo detection functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_typo_hooks() {

		$plugin_typo_fixer = new Redirect_Manager_Pro_Typo_Fixer();

		$this->loader->add_action( 'template_redirect', $plugin_typo_fixer, 'handle_404', 20 );

	}

	/**
	 * Register all of the hooks related to the link scanner functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_scanner_hooks() {

		$plugin_scanner = new Redirect_Manager_Pro_Scanner();

		$this->loader->add_action( 'wp_ajax_rmp_scan_batch', $plugin_scanner, 'ajax_scan_batch' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_redirect_manager_pro() {
		return $this->redirect_manager_pro;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Redirect_Manager_Pro_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}

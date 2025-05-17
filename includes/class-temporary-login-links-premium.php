<?php
/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Temporary_Login_Links_Premium
 * @subpackage Temporary_Login_Links_Premium/includes
 * @author     Your Name <email@example.com>
 */
class Temporary_Login_Links_Premium {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      TLP_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * The links manager instance.
     *
     * @since    1.0.0
     * @access   protected
     * @var      TLP_Links    $links    The links manager instance.
     */
    protected $links;

    /**
     * The user manager instance.
     *
     * @since    1.0.0
     * @access   protected
     * @var      TLP_User_Manager    $user_manager    The user manager instance.
     */
    protected $user_manager;

    /**
     * The security manager instance.
     *
     * @since    1.0.0
     * @access   protected
     * @var      TLP_Security    $security    The security manager instance.
     */
    protected $security;

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
        if (defined('TEMPORARY_LOGIN_LINKS_PREMIUM_VERSION')) {
            $this->version = TEMPORARY_LOGIN_LINKS_PREMIUM_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'temporary-login-links-premium';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->define_shortcodes();
        $this->schedule_tasks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - TLP_Loader. Orchestrates the hooks of the plugin.
     * - TLP_i18n. Defines internationalization functionality.
     * - TLP_Admin. Defines all hooks for the admin area.
     * - TLP_Public. Defines all hooks for the public side of the site.
     * - TLP_Links. Manages link creation and validation.
     * - TLP_User_Manager. Manages temporary users.
     * - TLP_Security. Implements security features.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-tlp-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-tlp-i18n.php';

        /**
         * The class responsible for managing links.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-tlp-links.php';

        /**
         * The class responsible for managing temporary users.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-tlp-user-manager.php';

        /**
         * The class responsible for security features.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-tlp-security.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-tlp-admin.php';

        /**
         * The class responsible for settings management.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-tlp-settings.php';

        /**
         * Make sure WP_List_Table is available
         */
        if (!class_exists('WP_List_Table')) {
            require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
        }

        /**
         * The class responsible for links list table in admin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-tlp-list-table.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-tlp-public.php';

        /**
         * The class responsible for defining shortcodes.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-tlp-shortcodes.php';

        // Initialize core components
        $this->loader = new TLP_Loader();
        $this->links = new TLP_Links();
        $this->user_manager = new TLP_User_Manager();
        $this->security = new TLP_Security();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the TLP_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale() {
        $plugin_i18n = new TLP_i18n();
        $plugin_i18n->set_domain($this->get_plugin_name());

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {
        // Initialize admin class
        $plugin_admin = new TLP_Admin($this->get_plugin_name(), $this->get_version());
        
        // Register admin assets
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        
        // Register admin menu
        $this->loader->add_action('admin_menu', $plugin_admin, 'register_admin_menu');
        
        // Register AJAX handlers
        $this->loader->add_action('admin_init', $plugin_admin, 'register_ajax_handlers');
        
        // Handle link actions
        $this->loader->add_action('admin_init', $plugin_admin, 'handle_link_actions');
        
        // Check for activation redirect
        $this->loader->add_action('admin_init', $plugin_admin, 'maybe_redirect_to_welcome_page');
        
        // Check for plugin upgrades
        $this->loader->add_action('admin_init', $plugin_admin, 'check_plugin_upgrade');
        
        // Register settings
        $plugin_settings = new TLP_Settings($this->get_plugin_name(), $this->get_version());
        $this->loader->add_action('admin_init', $plugin_settings, 'register_settings');
        
        // Initialize user manager hooks
        $this->loader->add_action('admin_init', $this->user_manager, 'register_hooks');
        
        // Initialize security hooks
        $this->loader->add_action('admin_init', $this->security, 'register_hooks');
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {
        $plugin_public = new TLP_Public($this->get_plugin_name(), $this->get_version());
        
        // Register public assets
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
        
        // Register public hooks
        $this->loader->add_action('init', $plugin_public, 'register_hooks');
    }

    /**
     * Register shortcodes.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_shortcodes() {
        $plugin_public = new TLP_Public($this->get_plugin_name(), $this->get_version());
        $this->loader->add_action('init', $plugin_public, 'init_shortcodes');
    }

    /**
     * Schedule plugin tasks.
     *
     * @since    1.0.0
     * @access   private
     */
    private function schedule_tasks() {
        // Schedule cleanup of expired links
        if (!wp_next_scheduled('temporary_login_links_cleanup_event')) {
            wp_schedule_event(time(), 'daily', 'temporary_login_links_cleanup_event');
        }
        
        // Add cleanup action
        $this->loader->add_action('temporary_login_links_cleanup_event', $this->links, 'cleanup_expired_links');
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
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    TLP_Loader    Orchestrates the hooks of the plugin.
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
<?php
/**
 * Main loader for Temporary Login Links Pro plugin.
 *
 * @package Temporary_Login_Links_Premium
 */

class Temporary_Login_Links_Premium {

    /**
     * Start the plugin by loading all dependencies and registering hooks.
     */
    public function run() {

        // Load shared core logic first
        require_once TEMPORARY_LOGIN_LINKS_PREMIUM_DIR . 'includes/class-tlp-links.php';
        require_once TEMPORARY_LOGIN_LINKS_PREMIUM_DIR . 'includes/class-tlp-user-manager.php';
        require_once TEMPORARY_LOGIN_LINKS_PREMIUM_DIR . 'includes/class-tlp-security.php';

        // Admin side
        if ( is_admin() ) {
            require_once TEMPORARY_LOGIN_LINKS_PREMIUM_DIR . 'admin/class-tlp-admin.php';
            $plugin_admin = new TLP_Admin('temporary-login-links-premium', TEMPORARY_LOGIN_LINKS_PREMIUM_VERSION);

            //$plugin_admin->load_dependencies();

            add_action('admin_menu', array($plugin_admin, 'register_admin_menu'));

            add_action('admin_enqueue_scripts', array($plugin_admin, 'enqueue_styles'));
            add_action('admin_enqueue_scripts', array($plugin_admin, 'enqueue_scripts'));

            add_action('admin_init', array($plugin_admin, 'maybe_redirect_to_welcome_page'));
            add_action('admin_notices', array($plugin_admin, 'display_admin_notices'));

            add_action('admin_init', array($plugin_admin, 'register_ajax_handlers'));
        }

        // Public side
        require_once TEMPORARY_LOGIN_LINKS_PREMIUM_DIR . 'public/class-tlp-public.php';
        $plugin_public = new TLP_Public('temporary-login-links-premium', TEMPORARY_LOGIN_LINKS_PREMIUM_VERSION);

        add_action('wp_enqueue_scripts', array($plugin_public, 'enqueue_styles'));
        add_action('wp_enqueue_scripts', array($plugin_public, 'enqueue_scripts'));

        //add_action('init', array($plugin_public, 'maybe_process_login_request'));
        add_action('init', array($plugin_public, 'register_hooks'));
        add_shortcode('tlp_login_link_message', array($plugin_public, 'render_login_message'));
    }
}

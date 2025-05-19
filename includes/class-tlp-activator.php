<?php
/**
 * Fired during plugin activation.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Temporary_Login_Links_Premium
 * @subpackage Temporary_Login_Links_Premium/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Temporary_Login_Links_Premium
 * @subpackage Temporary_Login_Links_Premium/includes
 * @author     Your Name <email@example.com>
 */
class Temporary_Login_Links_Premium_Activator {

    /**
     * Runs on plugin activation.
     *
     * Creates necessary database tables, sets up default options,
     * and performs compatibility checks.
     *
     * @since    1.0.0
     */
    public static function activate() {
        
        // Check PHP version
        if (version_compare(PHP_VERSION, '7.0', '<')) {
            deactivate_plugins(TEMPORARY_LOGIN_LINKS_PREMIUM_BASENAME);
            wp_die(
                esc_html__('Temporary Login Links Premium requires PHP 7.0 or higher.', 'temporary-login-links-premium'),
                'Plugin Activation Error',
                array('back_link' => true)
            );
        }        
        
        
        // Check WordPress version
        if ( version_compare( get_bloginfo( 'version' ), '5.0', '<' ) ) {
            deactivate_plugins( TEMPORARY_LOGIN_LINKS_PREMIUM_BASENAME );
            wp_die( 
                esc_html__( 'Temporary Login Links Premium requires WordPress 5.0 or higher.', 'temporary-login-links-premium' ), 
                'Plugin Activation Error', 
                array( 'back_link' => true ) 
            );
        }

        // Create database tables
        self::create_tables();

        // Set up default options
        self::set_default_options();

        // Create capability for managing temporary logins
        self::create_capabilities();

        // Set transient to trigger welcome page
        set_transient( 'tlp_activation_redirect', true, 30 );
    }

    /**
     * Creates the necessary database tables.
     *
     * @since    1.0.0
     */
    private static function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Links tracking table
        $table_name = $wpdb->prefix . 'temporary_login_links';

        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            link_token varchar(255) NOT NULL,
            user_id bigint(20) NOT NULL,
            user_email varchar(100) NOT NULL,
            user_login varchar(60) NOT NULL,
            role varchar(50) NOT NULL,
            expiry datetime NOT NULL,
            created_by bigint(20) NOT NULL,
            created_at datetime NOT NULL,
            redirect_to varchar(255) DEFAULT '',
            access_count int(11) DEFAULT 0,
            max_accesses int(11) DEFAULT 0,
            is_active tinyint(1) DEFAULT 1,
            ip_restriction varchar(255) DEFAULT '',
            last_accessed datetime DEFAULT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY link_token (link_token),
            KEY user_email (user_email),
            KEY expiry (expiry),
            KEY is_active (is_active)
        ) $charset_collate;";

        // Access log table
        $table_name_log = $wpdb->prefix . 'temporary_login_access_log';

        $sql_log = "CREATE TABLE $table_name_log (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            link_id bigint(20) NOT NULL,
            user_ip varchar(100) NOT NULL,
            user_agent text NOT NULL,
            accessed_at datetime NOT NULL,
            status varchar(20) NOT NULL,
            notes text,
            PRIMARY KEY  (id),
            KEY link_id (link_id),
            KEY accessed_at (accessed_at)
        ) $charset_collate;";

        // Security logs table
        $table_name_security = $wpdb->prefix . 'temporary_login_security_logs';

        $sql_security = "CREATE TABLE $table_name_security (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            token_fragment varchar(255) NOT NULL,
            user_email varchar(100) DEFAULT '',
            user_ip varchar(100) NOT NULL,
            user_agent text NOT NULL,
            logged_at datetime NOT NULL,
            status varchar(20) NOT NULL,
            reason text,
            PRIMARY KEY  (id),
            KEY logged_at (logged_at),
            KEY status (status)
        ) $charset_collate;";

        // Use dbDelta for database updates
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        dbDelta($sql_log);
        dbDelta($sql_security);
    }

    /**
     * Sets up default plugin options.
     *
     * @since    1.0.0
     */
    private static function set_default_options() {
        // Main plugin settings
        $default_settings = array(
            'delete_data_on_uninstall' => 0,
            'link_expiry_default'      => '7 days',
            'email_notifications'      => 1,
            'track_login_activity'     => 1,
            'default_redirect'         => admin_url(),
            'default_role'             => 'editor',
            'cleanup_expired_links'    => 1,
            'keep_expired_links_days'  => 30,
        );
        
        // Only set options if they don't exist
        if ( ! get_option( 'temporary_login_links_premium_settings' ) ) {
            update_option( 'temporary_login_links_premium_settings', $default_settings );
        }
        
        // Default branding settings
        $default_branding = array(
            'enable_branding'            => 1,
            'login_logo'                 => '',
            'login_background_color'     => '#f1f1f1',
            'login_form_background'      => '#ffffff',
            'login_form_text_color'      => '#333333',
            'login_button_color'         => '#0085ba',
            'login_button_text_color'    => '#ffffff',
            'login_custom_css'           => '',
            'login_welcome_text' => esc_html__( 'Welcome! You have been granted temporary access to this site.', 'temporary-login-links-premium' ),
            'company_name'               => get_bloginfo( 'name' ),
            'email_branding'             => 1,
        );
        
        // Only set branding options if they don't exist
        if ( ! get_option( 'temporary_login_links_premium_branding' ) ) {
            update_option( 'temporary_login_links_premium_branding', $default_branding );
        }
        
        // Store plugin version
        update_option( 'temporary_login_links_premium_version', TEMPORARY_LOGIN_LINKS_PREMIUM_VERSION );
    }

    /**
     * Creates capabilities for managing temporary logins.
     *
     * @since    1.0.0
     */
    private static function create_capabilities() {
        // Define capabilities
        $caps = array(
            'manage_temporary_logins' => __( 'Manage temporary login links', 'temporary-login-links-premium' ),
        );
        
        // Add capabilities to administrator role
        $role = get_role( 'administrator' );
        
        if ( $role ) {
            foreach ( $caps as $cap => $label ) {
                $role->add_cap( $cap );
            }
        }
    }
}
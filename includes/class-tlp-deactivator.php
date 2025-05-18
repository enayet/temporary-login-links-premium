<?php
/**
 * Fired during plugin deactivation.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Temporary_Login_Links_Premium
 * @subpackage Temporary_Login_Links_Premium/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Temporary_Login_Links_Premium
 * @subpackage Temporary_Login_Links_Premium/includes
 * @author     Your Name <email@example.com>
 */
class Temporary_Login_Links_Premium_Deactivator {

    /**
     * Runs on plugin deactivation.
     *
     * Handles tasks such as disabling active temporary links,
     * clearing scheduled events, and saving plugin state.
     *
     * @since    1.0.0
     */
    public static function deactivate() {
        // Clear scheduled events
        self::clear_scheduled_events();
        
        // Remove transients
        self::remove_transients();
    }

    /**
     * Clears all scheduled events created by the plugin.
     *
     * @since    1.0.0
     */
    private static function clear_scheduled_events() {
        // Clear the cleanup cron job
        wp_clear_scheduled_hook( 'temporary_login_links_cleanup_event' );
        
        // Clear the link expiry check cron job
        wp_clear_scheduled_hook( 'temporary_login_links_check_expiry_event' );
    }



    /**
     * Removes any transients created by the plugin.
     *
     * @since    1.0.0
     */
    private static function remove_transients() {
        // Remove the activation redirect transient
        delete_transient( 'tlp_activation_redirect' );
        
        // Remove links cache transients
        delete_transient( 'tlp_active_links_count' );
        delete_transient( 'tlp_expired_links_count' );
        
        // Remove any other plugin-specific transients
        global $wpdb;
        $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '%_transient_tlp_%'" );
        $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '%_transient_timeout_tlp_%'" );
    }
}
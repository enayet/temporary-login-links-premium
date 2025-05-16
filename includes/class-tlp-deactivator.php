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
        
        // Disable active temporary logins
        self::disable_active_logins();
        
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
     * Disables all active temporary logins.
     *
     * Doesn't delete the links but marks them as inactive to prevent usage
     * while the plugin is deactivated.
     *
     * @since    1.0.0
     */
    private static function disable_active_logins() {
        global $wpdb;
        
        // Get the table name
        $table_name = $wpdb->prefix . 'temporary_login_links';
        
        // Check if the table exists
        if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
            return;
        }
        
        // Mark all active links as inactive
        $wpdb->update(
            $table_name,
            array( 
                'is_active' => 0,
                'notes'     => 'Automatically deactivated due to plugin deactivation'
            ),
            array( 'is_active' => 1 ),
            array( '%d', '%s' ),
            array( '%d' )
        );
        
        // Log deactivation for all active links
        $access_log_table = $wpdb->prefix . 'temporary_login_access_log';
        
        // Check if log table exists
        if ( $wpdb->get_var( "SHOW TABLES LIKE '$access_log_table'" ) != $access_log_table ) {
            return;
        }
        
        // Get all active link IDs
        $active_link_ids = $wpdb->get_col( "SELECT id FROM $table_name WHERE is_active = 0" );
        
        if ( ! empty( $active_link_ids ) ) {
            foreach ( $active_link_ids as $link_id ) {
                // Add entry to access log
                $wpdb->insert(
                    $access_log_table,
                    array(
                        'link_id'     => $link_id,
                        'user_ip'     => '127.0.0.1',
                        'user_agent'  => 'Temporary Login Links Premium Deactivation',
                        'accessed_at' => current_time( 'mysql' ),
                        'status'      => 'deactivated',
                        'notes'       => 'Link deactivated due to plugin deactivation'
                    ),
                    array( '%d', '%s', '%s', '%s', '%s', '%s' )
                );
            }
        }
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
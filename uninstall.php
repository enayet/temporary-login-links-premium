<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Temporary_Login_Links_Premium
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Get plugin options to check deletion preferences
$options = get_option( 'temporary_login_links_premium_settings', array() );
$delete_data = isset( $options['delete_data_on_uninstall'] ) ? (bool) $options['delete_data_on_uninstall'] : false;

// Only proceed with deletion if user has opted in
if ( $delete_data ) {
    // Delete all temporary users created by this plugin
    delete_temporary_users();
    
    // Delete plugin options
    delete_option( 'temporary_login_links_premium_settings' );
    delete_option( 'temporary_login_links_premium_branding' );
    delete_option( 'temporary_login_links_premium_version' );
    
    // Delete plugin user meta for all users
    delete_plugin_user_meta();
    
    // Delete plugin database tables
    drop_plugin_tables();
}

/**
 * Delete temporary users created by the plugin
 */
function delete_temporary_users() {
    global $wpdb;
    
    // Find all temporary users by meta key
    $temp_users = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = %s",
            'temporary_login_links_premium_user'
        )
    );
    
    if ( ! empty( $temp_users ) ) {
        foreach ( $temp_users as $user_id ) {
            // Check if this is still a valid user
            if ( get_userdata( $user_id ) ) {
                // Get all posts by this user
                $posts = get_posts(
                    array(
                        'author' => $user_id,
                        'post_type' => 'any',
                        'numberposts' => -1,
                        'post_status' => 'any',
                    )
                );
                
                // Reassign posts to admin if any exist
                if ( ! empty( $posts ) ) {
                    // Get first admin user
                    $admins = get_users( array( 'role' => 'administrator', 'number' => 1 ) );
                    if ( ! empty( $admins ) ) {
                        $admin_id = $admins[0]->ID;
                        
                        // Reassign all posts to admin
                        $wpdb->update( 
                            $wpdb->posts, 
                            array( 'post_author' => $admin_id ), 
                            array( 'post_author' => $user_id ) 
                        );
                    }
                }
                
                // Delete the user
                wp_delete_user( $user_id );
            }
        }
    }
}

/**
 * Delete all plugin user meta
 */
function delete_plugin_user_meta() {
    global $wpdb;
    
    // Delete all metadata related to this plugin
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE %s",
            'temporary_login_links_premium_%'
        )
    );
}

/**
 * Drop custom tables created by the plugin
 */
function drop_plugin_tables() {
    global $wpdb;
    
    // Drop the links tracking table
    $table_name = $wpdb->prefix . 'temporary_login_links';
    $wpdb->query( "DROP TABLE IF EXISTS $table_name" );
    
    // Drop the links access log table
    $table_name = $wpdb->prefix . 'temporary_login_access_log';
    $wpdb->query( "DROP TABLE IF EXISTS $table_name" );
}
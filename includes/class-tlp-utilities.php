<?php
/**
 * Utility functions for the plugin.
 *
 * @package    Temporary_Login_Links_Premium
 * @subpackage Temporary_Login_Links_Premium/includes
 */

class TLP_Utilities {
    /**
     * Get the client's IP address.
     * 
     * @return string The client's IP address.
     */
    public static function get_client_ip() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return sanitize_text_field($_SERVER['HTTP_CLIENT_IP']);
        }
        
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip_list = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ip_list[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return sanitize_text_field($ip);
            }
        }
        
        if (!empty($_SERVER['REMOTE_ADDR'])) {
            return sanitize_text_field($_SERVER['REMOTE_ADDR']);
        }
        
        return '127.0.0.1';
    }
    
    /**
     * Get the display name for a user role.
     *
     * @param string $role The role slug.
     * @return string The display name for the role.
     */
    public static function get_role_display_name($role) {
        global $wp_roles;
        
        if (!isset($wp_roles)) {
            $wp_roles = new WP_Roles();
        }
        
        return isset($wp_roles->roles[$role]) ? translate_user_role($wp_roles->roles[$role]['name']) : $role;
    }
    
    /**
     * Get available user roles.
     *
     * @return array The available roles.
     */
    public static function get_available_roles() {
        global $wp_roles;
        
        if (!isset($wp_roles)) {
            $wp_roles = new WP_Roles();
        }
        
        $roles = $wp_roles->get_names();
        
        // Remove the administrator role if the current user is not an admin
        if (!current_user_can('administrator') && isset($roles['administrator'])) {
            unset($roles['administrator']);
        }
        
        return $roles;
    }
    
    /**
     * Validate an IP address.
     *
     * @param string $ip The IP address to validate.
     * @return bool Whether the IP address is valid.
     */
    public static function is_valid_ip($ip) {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }
    
    /**
     * Calculate the expiry date based on the provided duration.
     *
     * @param string $duration The duration string (e.g., '7 days', '1 month').
     * @return string|WP_Error The calculated expiry date in MySQL format or an error.
     */
    public static function calculate_expiry_date($duration) {
        // Handle predefined durations
        $preset_durations = array(
            '1 hour'    => '+1 hour',
            '3 hours'   => '+3 hours',
            '6 hours'   => '+6 hours',
            '12 hours'  => '+12 hours',
            '1 day'     => '+1 day',
            '3 days'    => '+3 days',
            '7 days'    => '+7 days',
            '14 days'   => '+14 days',
            '1 month'   => '+1 month',
            '3 months'  => '+3 months',
            '6 months'  => '+6 months',
            '1 year'    => '+1 year',
        );
        
        if (isset($preset_durations[$duration])) {
            $expiry_date = date('Y-m-d H:i:s', strtotime($preset_durations[$duration]));
        } elseif (preg_match('/^custom_(.+)$/', $duration, $matches)) {
            // Handle custom date
            $custom_date = $matches[1];
            
            // Validate date format (YYYY-MM-DD HH:MM:SS)
            if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $custom_date)) {
                return new WP_Error('invalid_date', __('Invalid date format. Please use YYYY-MM-DD HH:MM:SS.', 'temporary-login-links-premium'));
            }
            
            // Check if date is in the future
            if (strtotime($custom_date) <= time()) {
                return new WP_Error('past_date', __('The expiry date must be in the future.', 'temporary-login-links-premium'));
            }
            
            $expiry_date = $custom_date;
        } else {
            // Try to parse as a strtotime-compatible string
            $time = strtotime($duration);
            
            if (false === $time || $time <= time()) {
                return new WP_Error('invalid_duration', __('Invalid duration. Please use a valid time format.', 'temporary-login-links-premium'));
            }
            
            $expiry_date = date('Y-m-d H:i:s', $time);
        }
        
        return $expiry_date;
    }
}
<?php
/**
 * Core functionality for generating and validating temporary login links.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Temporary_Login_Links_Premium
 * @subpackage Temporary_Login_Links_Premium/includes
 */

/**
 * Core functionality for generating and validating temporary login links.
 *
 * This class handles the creation, validation, and management of temporary login links.
 * It generates secure tokens, creates database entries, and validates login attempts.
 *
 * @since      1.0.0
 * @package    Temporary_Login_Links_Premium
 * @subpackage Temporary_Login_Links_Premium/includes
 * @author     Your Name <email@example.com>
 */
class TLP_Links {

    /**
     * The database table name for links.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $table_name    The database table name for links.
     */
    private $table_name;

    /**
     * The database table name for access logs.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $log_table_name    The database table name for access logs.
     */
    private $log_table_name;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'temporary_login_links';
        $this->log_table_name = $wpdb->prefix . 'temporary_login_access_log';
    }

    /**
     * Generate a secure random token for a login link.
     *
     * @since    1.0.0
     * @return   string    The generated token.
     */
    public function generate_token() {
        $bytes = random_bytes(32);
        return bin2hex($bytes);
    }

    /**
     * Create a new temporary login link.
     *
     * @since    1.0.0
     * @param    array     $args    The link parameters.
     * @return   array|WP_Error    The link data or an error.
     */
    public function create_link( $args ) {
        global $wpdb;
        
        $defaults = array(
            'user_email'    => '',
            'first_name'    => '',
            'last_name'     => '',
            'role'          => '',
            'expiry'        => '7 days',
            'redirect_to'   => '',
            'max_accesses'  => 0,
            'ip_restriction' => '',
            'language'      => get_locale(),
        );
        
        $args = wp_parse_args( $args, $defaults );
        
        // Validate required fields
        if ( empty( $args['user_email'] ) || ! is_email( $args['user_email'] ) ) {
            return new WP_Error( 'invalid_email', __( 'Please provide a valid email address.', 'temporary-login-links-premium' ) );
        }
        
        if ( empty( $args['role'] ) ) {
            // Get default role from settings
            $settings = get_option( 'temporary_login_links_premium_settings', array() );
            $args['role'] = isset( $settings['default_role'] ) ? $settings['default_role'] : 'editor';
        }
        
        // Check if role exists
        if ( ! get_role( $args['role'] ) ) {
            return new WP_Error( 'invalid_role', __( 'Invalid user role.', 'temporary-login-links-premium' ) );
        }
        
        // Process expiration time
        $expiry = $this->calculate_expiry_date( $args['expiry'] );
        if ( is_wp_error( $expiry ) ) {
            return $expiry;
        }
        
        // Process redirect URL
        if ( empty( $args['redirect_to'] ) ) {
            $settings = get_option( 'temporary_login_links_premium_settings', array() );
            $args['redirect_to'] = isset( $settings['default_redirect'] ) ? $settings['default_redirect'] : admin_url();
        }
        
        // Create or get user
        $user_id = $this->get_or_create_temporary_user( $args );
        if ( is_wp_error( $user_id ) ) {
            return $user_id;
        }
        
        $user_data = get_userdata( $user_id );
        
        // Generate token
        $token = $this->generate_token();
        
        // Store link in database
        $result = $wpdb->insert(
            $this->table_name,
            array(
                'link_token'    => $token,
                'user_id'       => $user_id,
                'user_email'    => $args['user_email'],
                'user_login'    => $user_data->user_login,
                'role'          => $args['role'],
                'expiry'        => $expiry,
                'created_by'    => get_current_user_id(),
                'created_at'    => current_time( 'mysql' ),
                'redirect_to'   => $args['redirect_to'],
                'max_accesses'  => absint( $args['max_accesses'] ),
                'is_active'     => 1,
                'ip_restriction' => sanitize_text_field( $args['ip_restriction'] ),
            ),
            array( '%s', '%d', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%d', '%d', '%s' )
        );
        
        if ( false === $result ) {
            return new WP_Error( 'db_error', __( 'Could not create temporary login link.', 'temporary-login-links-premium' ) );
        }
        
        $link_id = $wpdb->insert_id;
        
        // Save language preference
        update_user_meta( $user_id, 'temporary_login_language', sanitize_text_field( $args['language'] ) );
        
        // Set user meta to identify as a temporary user
        update_user_meta( $user_id, 'temporary_login_links_premium_user', $link_id );
        
        // Generate the full login URL
        $login_url = $this->get_login_url( $token );
        
        // Maybe send email notification
        $this->maybe_send_notification( $args, $login_url, $expiry );
        
        return array(
            'id'        => $link_id,
            'token'     => $token,
            'user_id'   => $user_id,
            'email'     => $args['user_email'],
            'role'      => $args['role'],
            'expiry'    => $expiry,
            'url'       => $login_url,
        );
    }

    /**
     * Calculate the expiry date based on the provided duration.
     *
     * @since    1.0.0
     * @param    string    $duration    The duration string (e.g., '7 days', '1 month').
     * @return   string|WP_Error        The calculated expiry date in MySQL format or an error.
     */
    private function calculate_expiry_date( $duration ) {
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
        
        if ( isset( $preset_durations[ $duration ] ) ) {
            $expiry_date = date( 'Y-m-d H:i:s', strtotime( $preset_durations[ $duration ] ) );
        } elseif ( preg_match( '/^custom_(.+)$/', $duration, $matches ) ) {
            // Handle custom date
            $custom_date = $matches[1];
            
            // Validate date format (YYYY-MM-DD HH:MM:SS)
            if ( ! preg_match( '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $custom_date ) ) {
                return new WP_Error( 'invalid_date', __( 'Invalid date format. Please use YYYY-MM-DD HH:MM:SS.', 'temporary-login-links-premium' ) );
            }
            
            // Check if date is in the future
            if ( strtotime( $custom_date ) <= time() ) {
                return new WP_Error( 'past_date', __( 'The expiry date must be in the future.', 'temporary-login-links-premium' ) );
            }
            
            $expiry_date = $custom_date;
        } else {
            // Try to parse as a strtotime-compatible string
            $time = strtotime( $duration );
            
            if ( false === $time || $time <= time() ) {
                return new WP_Error( 'invalid_duration', __( 'Invalid duration. Please use a valid time format.', 'temporary-login-links-premium' ) );
            }
            
            $expiry_date = date( 'Y-m-d H:i:s', $time );
        }
        
        return $expiry_date;
    }

    /**
     * Get or create a temporary user for the login link.
     *
     * @since    1.0.0
     * @param    array     $args    The user data.
     * @return   int|WP_Error       The user ID or an error.
     */
    private function get_or_create_temporary_user( $args ) {
        // Check if user with this email already exists
        $existing_user = get_user_by( 'email', $args['user_email'] );
        
        if ( $existing_user ) {
            // Check if this is already a temporary user
            $temp_user = get_user_meta( $existing_user->ID, 'temporary_login_links_premium_user', true );
            
            if ( $temp_user ) {
                // Update the role if needed
                $existing_user->set_role( $args['role'] );
                
                // Update user data if provided
                if ( ! empty( $args['first_name'] ) || ! empty( $args['last_name'] ) ) {
                    $userdata = array(
                        'ID' => $existing_user->ID,
                    );
                    
                    if ( ! empty( $args['first_name'] ) ) {
                        $userdata['first_name'] = sanitize_text_field( $args['first_name'] );
                    }
                    
                    if ( ! empty( $args['last_name'] ) ) {
                        $userdata['last_name'] = sanitize_text_field( $args['last_name'] );
                    }
                    
                    wp_update_user( $userdata );
                }
                
                return $existing_user->ID;
            } else {
                // Existing non-temporary user - don't modify
                return new WP_Error( 
                    'user_exists', 
                    __( 'A user with this email already exists and is not a temporary user.', 'temporary-login-links-premium' ) 
                );
            }
        }
        
        // Create a new temporary user
        $username = $this->generate_username( $args['user_email'] );
        $password = wp_generate_password( 24, true, true );
        
        $userdata = array(
            'user_login'   => $username,
            'user_email'   => $args['user_email'],
            'user_pass'    => $password,
            'role'         => $args['role'],
        );
        
        if ( ! empty( $args['first_name'] ) ) {
            $userdata['first_name'] = sanitize_text_field( $args['first_name'] );
        }
        
        if ( ! empty( $args['last_name'] ) ) {
            $userdata['last_name'] = sanitize_text_field( $args['last_name'] );
        }
        
        $user_id = wp_insert_user( $userdata );
        
        if ( is_wp_error( $user_id ) ) {
            return $user_id;
        }
        
        return $user_id;
    }

    /**
     * Generate a unique username based on the email address.
     *
     * @since    1.0.0
     * @param    string    $email    The user's email address.
     * @return   string              The generated username.
     */
    private function generate_username( $email ) {
        $base_username = substr( md5( $email . time() ), 0, 12 );
        $username = 'tmp_' . $base_username;
        
        // Ensure username is unique
        $suffix = 1;
        $temp_username = $username;
        
        while ( username_exists( $temp_username ) ) {
            $temp_username = $username . '_' . $suffix;
            $suffix++;
        }
        
        return $temp_username;
    }

    /**
     * Generate the login URL for a token.
     *
     * @since    1.0.0
     * @param    string    $token    The login token.
     * @return   string              The full login URL.
     */
//    public function get_login_url( $token ) {
//        $base_url = site_url( 'wp-login.php' );
//        return add_query_arg( array( 'temp_login' => $token ), $base_url );
//    }
    
    public function get_login_url($token, $auto = false) {
        $base_url = site_url('wp-login.php');
        $args = array('temp_login' => $token);

        // Add auto parameter if specified
        if ($auto) {
            $args['auto'] = '1';
        }

        return add_query_arg($args, $base_url);
    }    
    

    /**
     * Validate a login token and perform the login if valid.
     *
     * @since    1.0.0
     * @param    string    $token    The login token to validate.
     * @return   array|WP_Error     The result of the validation or an error.
     */
    public function validate_login_token( $token ) {
        global $wpdb;
        
        // Sanitize token
        $token = sanitize_text_field( $token );
        
        // Get link data
        $link = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE link_token = %s LIMIT 1",
                $token
            ),
            ARRAY_A
        );
        
        if ( ! $link ) {
            return $this->log_access_attempt( 0, 'invalid_token', __( 'Invalid login token.', 'temporary-login-links-premium' ) );
        }
        
        // Check if link is active
        if ( ! $link['is_active'] ) {
            return $this->log_access_attempt( $link['id'], 'inactive', __( 'This login link has been deactivated.', 'temporary-login-links-premium' ) );
        }
        
        // Check expiration
        if ( strtotime( $link['expiry'] ) < time() ) {
            // Update link status to inactive
            $wpdb->update(
                $this->table_name,
                array( 'is_active' => 0 ),
                array( 'id' => $link['id'] ),
                array( '%d' ),
                array( '%d' )
            );
            
            return $this->log_access_attempt( $link['id'], 'expired', __( 'This login link has expired.', 'temporary-login-links-premium' ) );
        }
        
        // Check max accesses
        if ( $link['max_accesses'] > 0 && $link['access_count'] >= $link['max_accesses'] ) {
            // Update link status to inactive
            $wpdb->update(
                $this->table_name,
                array( 'is_active' => 0 ),
                array( 'id' => $link['id'] ),
                array( '%d' ),
                array( '%d' )
            );
            
            return $this->log_access_attempt( $link['id'], 'max_accesses', __( 'This login link has reached its maximum number of uses.', 'temporary-login-links-premium' ) );
        }
        
        // Check IP restriction if set
        if ( ! empty( $link['ip_restriction'] ) ) {
            $ip_addresses = array_map( 'trim', explode( ',', $link['ip_restriction'] ) );
            $current_ip = $this->get_client_ip();
            
            if ( ! in_array( $current_ip, $ip_addresses ) ) {
                return $this->log_access_attempt( $link['id'], 'ip_restricted', __( 'Access denied from your IP address.', 'temporary-login-links-premium' ) );
            }
        }
        
        // Get user
        $user = get_user_by( 'id', $link['user_id'] );
        
        if ( ! $user ) {
            return $this->log_access_attempt( $link['id'], 'user_not_found', __( 'The user associated with this link no longer exists.', 'temporary-login-links-premium' ) );
        }
        
        // Update access count and last accessed time
        $wpdb->update(
            $this->table_name,
            array(
                'access_count'  => $link['access_count'] + 1,
                'last_accessed' => current_time( 'mysql' ),
            ),
            array( 'id' => $link['id'] ),
            array( '%d', '%s' ),
            array( '%d' )
        );
        
        // Log successful access
        $this->log_access_attempt( $link['id'], 'success', __( 'Login successful.', 'temporary-login-links-premium' ) );
        
        // Set language if specified
        $language = get_user_meta( $user->ID, 'temporary_login_language', true );
        if ( $language ) {
            update_user_meta( $user->ID, 'locale', $language );
        }
        
        // Return success response
        return array(
            'status'      => 'success',
            'message'     => __( 'Login successful.', 'temporary-login-links-premium' ),
            'user_id'     => $user->ID,
            'redirect_to' => $link['redirect_to'] ? $link['redirect_to'] : admin_url(),
        );
    }

    /**
     * Get the client's IP address.
     *
     * @since    1.0.0
     * @return   string    The client's IP address.
     */
    private function get_client_ip() {
        // Check for shared internet/ISP IP
        if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
            return sanitize_text_field( $_SERVER['HTTP_CLIENT_IP'] );
        }
        
        // Check for IPs passing through proxies
        if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            // Use the first IP from the list
            $ip_list = explode( ',', sanitize_text_field( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
            return trim( $ip_list[0] );
        }
        
        // Use remote address if available
        if ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
            return sanitize_text_field( $_SERVER['REMOTE_ADDR'] );
        }
        
        // Fallback to a generic IP
        return '127.0.0.1';
    }

    /**
     * Log an access attempt.
     *
     * @since    1.0.0
     * @param    int       $link_id    The link ID.
     * @param    string    $status     The status of the attempt.
     * @param    string    $message    The message to log.
     * @return   WP_Error              An error object with the message.
     */
    private function log_access_attempt( $link_id, $status, $message ) {
        global $wpdb;
        
        $wpdb->insert(
            $this->log_table_name,
            array(
                'link_id'     => $link_id,
                'user_ip'     => $this->get_client_ip(),
                'user_agent'  => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ) : '',
                'accessed_at' => current_time( 'mysql' ),
                'status'      => $status,
                'notes'       => $message,
            ),
            array( '%d', '%s', '%s', '%s', '%s', '%s' )
        );
        
        return new WP_Error( $status, $message );
    }

    /**
     * Get a link by its ID.
     *
     * @since    1.0.0
     * @param    int       $link_id    The link ID.
     * @return   array|bool            The link data or false if not found.
     */
    public function get_link( $link_id ) {
        global $wpdb;
        
        $link = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE id = %d",
                $link_id
            ),
            ARRAY_A
        );
        
        if ( ! $link ) {
            return false;
        }
        
        return $link;
    }

    /**
     * Delete a link by its ID.
     *
     * @since    1.0.0
     * @param    int       $link_id    The link ID.
     * @return   bool                  Whether the link was deleted.
     */
    public function delete_link( $link_id ) {
        global $wpdb;
        
        // Get the link to check the user ID
        $link = $this->get_link( $link_id );
        
        if ( ! $link ) {
            return false;
        }
        
        // Delete the link
        $result = $wpdb->delete(
            $this->table_name,
            array( 'id' => $link_id ),
            array( '%d' )
        );
        
        if ( $result ) {
            // Check if user has other active links
            $other_links = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM {$this->table_name} WHERE user_id = %d AND id != %d",
                    $link['user_id'],
                    $link_id
                )
            );
            
            if ( $other_links == 0 ) {
                // No other links, delete the user
                $this->delete_temporary_user( $link['user_id'] );
            }
            
            // Clear any caches
            delete_transient( 'tlp_active_links_count' );
            delete_transient( 'tlp_expired_links_count' );
        }
        
        return (bool) $result;
    }

    /**
     * Delete a temporary user.
     *
     * @since    1.0.0
     * @param    int       $user_id    The user ID.
     * @return   bool                  Whether the user was deleted.
     */
    private function delete_temporary_user( $user_id ) {
        // Check if this is a temporary user
        $temp_user = get_user_meta( $user_id, 'temporary_login_links_premium_user', true );
        
        if ( ! $temp_user ) {
            return false;
        }
        
        // Delete the user
        require_once( ABSPATH . 'wp-admin/includes/user.php' );
        $reassign = $this->get_reassign_user();
        
        return wp_delete_user( $user_id, $reassign );
    }

    /**
     * Get the user ID to reassign content to when deleting a user.
     *
     * @since    1.0.0
     * @return   int       The user ID to reassign content to.
     */
    private function get_reassign_user() {
        // Get the first admin user
        $admins = get_users( array(
            'role'    => 'administrator',
            'number'  => 1,
            'orderby' => 'ID',
            'order'   => 'ASC',
        ) );
        
        if ( ! empty( $admins ) ) {
            return $admins[0]->ID;
        }
        
        // Fallback to current user
        $current_user_id = get_current_user_id();
        
        if ( $current_user_id ) {
            return $current_user_id;
        }
        
        // Fallback to first user (usually admin with ID 1)
        return 1;
    }

    /**
     * Maybe send an email notification about the new temporary login.
     *
     * @since    1.0.0
     * @param    array     $args       The link parameters.
     * @param    string    $login_url  The login URL.
     * @param    string    $expiry     The expiry date.
     */
    private function maybe_send_notification( $args, $login_url, $expiry ) {
        // Check if notifications are enabled
        $settings = get_option( 'temporary_login_links_premium_settings', array() );
        
        if ( empty( $settings['email_notifications'] ) ) {
            return;
        }
        
        // Get branding settings
        $branding = get_option( 'temporary_login_links_premium_branding', array() );
        $company_name = isset( $branding['company_name'] ) ? $branding['company_name'] : get_bloginfo( 'name' );
        
        // Prepare email content
        $subject = sprintf( __( 'Temporary access to %s', 'temporary-login-links-premium' ), $company_name );
        
        $message = sprintf( __( "Hello%s,\n\n", 'temporary-login-links-premium' ), 
            ! empty( $args['first_name'] ) ? ' ' . $args['first_name'] : ''
        );
        
        $message .= sprintf( __( "You have been granted temporary access to %s with %s privileges.\n\n", 'temporary-login-links-premium' ),
            get_bloginfo( 'name' ),
            $this->get_role_display_name( $args['role'] )
        );
        
        $message .= sprintf( __( "You can log in using this link (no password required):\n%s\n\n", 'temporary-login-links-premium' ),
            $login_url
        );
        
        $message .= sprintf( __( "This link will expire on %s.\n\n", 'temporary-login-links-premium' ),
            date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $expiry ) )
        );
        
        $message .= sprintf( __( "Regards,\n%s Team", 'temporary-login-links-premium' ),
            $company_name
        );
        
        // Maybe add HTML formatting
        $headers = array();
        
        if ( ! empty( $branding['email_branding'] ) ) {
            $headers[] = 'Content-Type: text/html; charset=UTF-8';
            $message = $this->format_email_html( $message, $login_url, $args, $expiry );
        }
        
        // Send the email
        wp_mail( $args['user_email'], $subject, $message, $headers );
    }

    /**
     * Format the email content as HTML.
     *
     * @since    1.0.0
     * @param    string    $message    The plain text message.
     * @param    string    $login_url  The login URL.
     * @param    array     $args       The link parameters.
     * @param    string    $expiry     The expiry date.
     * @return   string                The HTML formatted message.
     */
    private function format_email_html( $message, $login_url, $args, $expiry ) {
        // Get branding settings
        $branding = get_option( 'temporary_login_links_premium_branding', array() );
        $company_name = isset( $branding['company_name'] ) ? $branding['company_name'] : get_bloginfo( 'name' );
        
        // Get site logo
        $logo_url = '';
        
        if ( ! empty( $branding['login_logo'] ) ) {
            $logo_url = $branding['login_logo'];
        } elseif ( function_exists( 'get_custom_logo' ) && get_theme_mod( 'custom_logo' ) ) {
            $logo_url = wp_get_attachment_image_url( get_theme_mod( 'custom_logo' ), 'full' );
        }
        
        // Set default button styles
        $button_bg = isset( $branding['login_button_color'] ) ? $branding['login_button_color'] : '#0085ba';
        $button_text = isset( $branding['login_button_text_color'] ) ? $branding['login_button_text_color'] : '#ffffff';
        
        // Build HTML email
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">';
        
        // Add logo if available
        if ( $logo_url ) {
            $html .= '<div style="text-align: center; margin-bottom: 30px;"><img src="' . esc_url( $logo_url ) . '" alt="' . esc_attr( $company_name ) . '" style="max-width: 200px; max-height: 80px;"></div>';
        } else {
            $html .= '<div style="text-align: center; margin-bottom: 30px;"><h1>' . esc_html( $company_name ) . '</h1></div>';
        }
        
        $html .= '<div style="background-color: #f7f7f7; padding: 20px; border-radius: 5px;">';
        
        // Greeting
        $html .= '<p style="margin-bottom: 15px;">Hello' . ( ! empty( $args['first_name'] ) ? ' ' . esc_html( $args['first_name'] ) : '' ) . ',</p>';
        
        // Message
        $html .= '<p style="margin-bottom: 15px;">' . sprintf( 
            __( 'You have been granted temporary access to %s with %s privileges.', 'temporary-login-links-premium' ),
            '<strong>' . esc_html( get_bloginfo( 'name' ) ) . '</strong>',
            '<strong>' . esc_html( $this->get_role_display_name( $args['role'] ) ) . '</strong>'
        ) . '</p>';
        
        // Login button
        $html .= '<div style="text-align: center; margin: 30px 0;"><a href="' . esc_url( $login_url ) . '" style="display: inline-block; background-color: ' . esc_attr( $button_bg ) . '; color: ' . esc_attr( $button_text ) . '; padding: 12px 25px; text-decoration: none; border-radius: 4px; font-weight: bold;">' . __( 'Log In Now', 'temporary-login-links-premium' ) . '</a></div>';
        
        // Expiry notice
        $html .= '<p style="margin-bottom: 15px;">' . sprintf( 
            __( 'This link will expire on %s.', 'temporary-login-links-premium' ),
            '<strong>' . date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $expiry ) ) . '</strong>'
        ) . '</p>';
        
        // Additional info if max accesses is set
        if ( ! empty( $args['max_accesses'] ) && $args['max_accesses'] > 0 ) {
            $html .= '<p style="margin-bottom: 15px; font-style: italic;">' . sprintf( 
                __( 'This link can only be used a maximum of %d times.', 'temporary-login-links-premium' ),
                $args['max_accesses']
            ) . '</p>';
        }
        
        $html .= '</div>';
        
        // Footer
        $html .= '<div style="margin-top: 30px; text-align: center; font-size: 12px; color: #777;">';
        $html .= '<p>' . sprintf( __( 'Regards,<br>%s Team', 'temporary-login-links-premium' ), esc_html( $company_name ) ) . '</p>';
        
        // Maybe add link URL as text for email clients that block images/buttons
        $html .= '<p style="margin-top: 20px; font-size: 11px;">' . __( 'If the button doesn\'t work, copy and paste this URL into your browser:', 'temporary-login-links-premium' ) . '<br>';
        $html .= '<a href="' . esc_url( $login_url ) . '" style="color: #555;">' . esc_html( $login_url ) . '</a></p>';
        
        $html .= '</div>';
        $html .= '</body></html>';
        
        return $html;
    }

    /**
     * Get the display name for a user role.
     *
     * @since    1.0.0
     * @param    string    $role    The role slug.
     * @return   string             The display name for the role.
     */
    private function get_role_display_name( $role ) {
        global $wp_roles;
        
        if ( ! isset( $wp_roles ) ) {
            $wp_roles = new WP_Roles();
        }
        
        return isset( $wp_roles->roles[ $role ] ) ? translate_user_role( $wp_roles->roles[ $role ]['name'] ) : $role;
    }
    
    /**
     * Update a link's status.
     *
     * @since    1.0.0
     * @param    int       $link_id    The link ID.
     * @param    bool      $is_active  Whether the link should be active.
     * @return   bool                  Whether the link was updated.
     */
    public function update_link_status( $link_id, $is_active ) {
        global $wpdb;
        
        $result = $wpdb->update(
            $this->table_name,
            array( 'is_active' => $is_active ? 1 : 0 ),
            array( 'id' => $link_id ),
            array( '%d' ),
            array( '%d' )
        );
        
        if ( $result ) {
            // Log the status change
            $status = $is_active ? 'activated' : 'deactivated';
            $message = $is_active ? __( 'Link activated by admin.', 'temporary-login-links-premium' ) : __( 'Link deactivated by admin.', 'temporary-login-links-premium' );
            
            $wpdb->insert(
                $this->log_table_name,
                array(
                    'link_id'     => $link_id,
                    'user_ip'     => $this->get_client_ip(),
                    'user_agent'  => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ) : '',
                    'accessed_at' => current_time( 'mysql' ),
                    'status'      => $status,
                    'notes'       => $message,
                ),
                array( '%d', '%s', '%s', '%s', '%s', '%s' )
            );
            
            // Clear any caches
            delete_transient( 'tlp_active_links_count' );
            delete_transient( 'tlp_expired_links_count' );
        }
        
        return (bool) $result;
    }
    
    /**
     * Extend a link's expiry date.
     *
     * @since    1.0.0
     * @param    int       $link_id       The link ID.
     * @param    string    $new_expiry    The new expiry date or duration.
     * @return   bool|WP_Error            Whether the link was updated or an error.
     */
    public function extend_link( $link_id, $new_expiry ) {
        global $wpdb;
        
        // Get the link
        $link = $this->get_link( $link_id );
        
        if ( ! $link ) {
            return new WP_Error( 'not_found', __( 'Link not found.', 'temporary-login-links-premium' ) );
        }
        
        // Calculate new expiry date
        $expiry = $this->calculate_expiry_date( $new_expiry );
        
        if ( is_wp_error( $expiry ) ) {
            return $expiry;
        }
        
        // Update link
        $result = $wpdb->update(
            $this->table_name,
            array( 
                'expiry'    => $expiry,
                'is_active' => 1,
            ),
            array( 'id' => $link_id ),
            array( '%s', '%d' ),
            array( '%d' )
        );
        
        if ( $result ) {
            // Log the extension
            $wpdb->insert(
                $this->log_table_name,
                array(
                    'link_id'     => $link_id,
                    'user_ip'     => $this->get_client_ip(),
                    'user_agent'  => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ) : '',
                    'accessed_at' => current_time( 'mysql' ),
                    'status'      => 'extended',
                    'notes'       => sprintf( __( 'Link expiry extended to %s.', 'temporary-login-links-premium' ), $expiry ),
                ),
                array( '%d', '%s', '%s', '%s', '%s', '%s' )
            );
            
            // Clear any caches
            delete_transient( 'tlp_active_links_count' );
            delete_transient( 'tlp_expired_links_count' );
        }
        
        return (bool) $result;
    }
    
    /**
     * Get links by specified criteria.
     *
     * @since    1.0.0
     * @param    array     $args    The query args.
     * @return   array              The links data.
     */
    public function get_links( $args = array() ) {
        global $wpdb;
        
        $defaults = array(
            'status'    => 'all',      // 'all', 'active', 'inactive', 'expired'
            'per_page'  => 20,
            'page'      => 1,
            'orderby'   => 'created_at',
            'order'     => 'DESC',
            'search'    => '',
            'role'      => '',
        );
        
        $args = wp_parse_args( $args, $defaults );
        
        // Build WHERE clause
        $where = array();
        $values = array();
        
        // Filter by status
        if ( 'active' === $args['status'] ) {
            $where[] = 'is_active = 1 AND expiry > %s';
            $values[] = current_time( 'mysql' );
        } elseif ( 'inactive' === $args['status'] ) {
            $where[] = 'is_active = 0';
        } elseif ( 'expired' === $args['status'] ) {
            $where[] = 'expiry <= %s';
            $values[] = current_time( 'mysql' );
        }
        
        // Search
        if ( ! empty( $args['search'] ) ) {
            $search_term = '%' . $wpdb->esc_like( $args['search'] ) . '%';
            $where[] = '(user_email LIKE %s OR user_login LIKE %s)';
            $values[] = $search_term;
            $values[] = $search_term;
        }
        
        // Filter by role
        if ( ! empty( $args['role'] ) ) {
            $where[] = 'role = %s';
            $values[] = $args['role'];
        }
        
        // Build the query
        $sql = "SELECT * FROM {$this->table_name}";
        
        if ( ! empty( $where ) ) {
            $sql .= ' WHERE ' . implode( ' AND ', $where );
        }
        
        // Order
        $allowed_orderby = array( 'created_at', 'expiry', 'user_email', 'role', 'access_count', 'last_accessed' );
        $allowed_order = array( 'ASC', 'DESC' );
        
        $orderby = in_array( $args['orderby'], $allowed_orderby ) ? $args['orderby'] : 'created_at';
        $order = in_array( strtoupper( $args['order'] ), $allowed_order ) ? strtoupper( $args['order'] ) : 'DESC';
        
        //$sql .= " ORDER BY {$orderby} {$order}";
        
        $order_by_map = [
            'created_at' => 'created_at',
            'expiry' => 'expiry',
            'user_email' => 'user_email',
            'role' => 'role',
            'access_count' => 'access_count',
            'last_accessed' => 'last_accessed'
        ];

        $order_map = [
            'ASC' => 'ASC',
            'DESC' => 'DESC'
        ];

        $safe_orderby = isset($order_by_map[$args['orderby']]) ? $order_by_map[$args['orderby']] : 'created_at';
        $safe_order = isset($order_map[strtoupper($args['order'])]) ? $order_map[strtoupper($args['order'])] : 'DESC';

        $sql .= " ORDER BY {$safe_orderby} {$safe_order}";        
        
        
        // Pagination
        $per_page = max( 1, absint( $args['per_page'] ) );
        $page = max( 1, absint( $args['page'] ) );
        $offset = ( $page - 1 ) * $per_page;
        
        $sql .= " LIMIT %d, %d";
        $values[] = $offset;
        $values[] = $per_page;
        
        // Prepare the query
        $prepared_sql = $wpdb->prepare( $sql, $values );
        
        // Get results
        $results = $wpdb->get_results( $prepared_sql, ARRAY_A );
        
        // Count total items

        $count_sql = "SELECT COUNT(*) FROM {$this->table_name}";
        
        if (!empty($where)) {
            $count_sql .= ' WHERE ' . implode(' AND ', $where);
            $total_items = $wpdb->get_var($wpdb->prepare($count_sql, array_slice($values, 0, count($values) - 2)));
        } else {
            // No prepare needed if there are no placeholders
            $total_items = $wpdb->get_var($count_sql);
        }        
         
        
        return array(
            'items'       => $results,
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'page'        => $page,
        );
    }
    
    /**
     * Get access logs for a link.
     *
     * @since    1.0.0
     * @param    int       $link_id    The link ID.
     * @param    array     $args       The query args.
     * @return   array                 The access logs data.
     */
    public function get_access_logs( $link_id, $args = array() ) {
        global $wpdb;
        
        $defaults = array(
            'per_page'  => 20,
            'page'      => 1,
            'orderby'   => 'accessed_at',
            'order'     => 'DESC',
        );
        
        $args = wp_parse_args( $args, $defaults );
        
        // Build the query
        $sql = $wpdb->prepare(
            "SELECT * FROM {$this->log_table_name} WHERE link_id = %d",
            $link_id
        );
        
        // Order
        $allowed_orderby = array( 'accessed_at', 'user_ip', 'status' );
        $allowed_order = array( 'ASC', 'DESC' );
        
        $orderby = in_array( $args['orderby'], $allowed_orderby ) ? $args['orderby'] : 'accessed_at';
        $order = in_array( strtoupper( $args['order'] ), $allowed_order ) ? strtoupper( $args['order'] ) : 'DESC';
        
        $sql .= " ORDER BY {$orderby} {$order}";
        
        // Pagination
        $per_page = max( 1, absint( $args['per_page'] ) );
        $page = max( 1, absint( $args['page'] ) );
        $offset = ( $page - 1 ) * $per_page;
        
        $sql .= $wpdb->prepare( " LIMIT %d, %d", $offset, $per_page );
        
        // Get results
        $results = $wpdb->get_results( $sql, ARRAY_A );
        
        // Count total items
        $total_items = $wpdb->get_var( $wpdb->prepare( 
            "SELECT COUNT(*) FROM {$this->log_table_name} WHERE link_id = %d",
            $link_id 
        ) );
        
        return array(
            'items'       => $results,
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'page'        => $page,
        );
    }
    
    /**
     * Cleanup expired links.
     *
     * @since    1.0.0
     * @return   int       The number of links cleaned up.
     */
    public function cleanup_expired_links() {
        global $wpdb;
        
        // Get settings
        $settings = get_option( 'temporary_login_links_premium_settings', array() );
        $days_to_keep = isset( $settings['keep_expired_links_days'] ) ? absint( $settings['keep_expired_links_days'] ) : 30;
        
        // Calculate date threshold
        $threshold = date( 'Y-m-d H:i:s', strtotime( '-' . $days_to_keep . ' days' ) );
        
        // Get expired links older than threshold
        $links = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, user_id FROM {$this->table_name} WHERE expiry < %s",
                $threshold
            ),
            ARRAY_A
        );
        
        if ( empty( $links ) ) {
            return 0;
        }
        
        $count = 0;
        
        // Process each link
        foreach ( $links as $link ) {
            if ( $this->delete_link( $link['id'] ) ) {
                $count++;
                    
                // Also clean up related access logs
                $wpdb->delete(
                    $this->log_table_name,
                    array('link_id' => $link['id']),
                    array('%d')
                );
            }
        }
        
        
        // Also clean up orphaned security logs over threshold
        $security_log_table = $wpdb->prefix . 'temporary_login_security_logs';
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$security_log_table} WHERE logged_at < %s",
                $threshold
            )
        );        
        
        
        return $count;
    }
    
    
    /**
     * Update a link's expiry date directly.
     *
     * @since    1.0.0
     * @param    int       $link_id    The link ID.
     * @param    string    $new_expiry The new expiry date (YYYY-MM-DD HH:MM:SS format).
     * @return   bool|WP_Error        Whether the link was updated or an error.
     */
    public function update_link_expiry($link_id, $new_expiry) {
        global $wpdb;

        // Get the link
        $link = $this->get_link($link_id);

        if (!$link) {
            return new WP_Error('not_found', __('Link not found.', 'temporary-login-links-premium'));
        }

        // Validate date format (YYYY-MM-DD HH:MM:SS)
        if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $new_expiry)) {
            return new WP_Error('invalid_date', __('Invalid date format. Please use YYYY-MM-DD HH:MM:SS.', 'temporary-login-links-premium'));
        }

        // Check if date is in the future
        if (strtotime($new_expiry) <= time()) {
            return new WP_Error('past_date', __('The expiry date must be in the future.', 'temporary-login-links-premium'));
        }

        // Update link
        $result = $wpdb->update(
            $this->table_name,
            array( 
                'expiry'    => $new_expiry,
                'is_active' => 1,
            ),
            array('id' => $link_id),
            array('%s', '%d'),
            array('%d')
        );

        if ($result) {
            // Log the extension
            $wpdb->insert(
                $this->log_table_name,
                array(
                    'link_id'     => $link_id,
                    'user_ip'     => $this->get_client_ip(),
                    'user_agent'  => isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : '',
                    'accessed_at' => current_time('mysql'),
                    'status'      => 'extended',
                    'notes'       => sprintf(__('Link expiry updated to %s.', 'temporary-login-links-premium'), $new_expiry),
                ),
                array('%d', '%s', '%s', '%s', '%s', '%s')
            );

            // Clear any caches
            delete_transient('tlp_active_links_count');
            delete_transient('tlp_expired_links_count');
        }

        return (bool) $result;
    }    
    
    
    /**
     * Get all access logs.
     *
     * @since    1.0.0
     * @param    array     $args       The query args.
     * @return   array                 The access logs data.
     */
    public function get_all_access_logs($args = array()) {
        global $wpdb;

        $defaults = array(
            'per_page'   => 20,
            'page'       => 1,
            'orderby'    => 'accessed_at',
            'order'      => 'DESC',
            'status'     => '',
            'search'     => '',
            'start_date' => '',
            'end_date'   => '',
        );

        $args = wp_parse_args($args, $defaults);

        // Build WHERE clause
        $where = array();
        $values = array();

        // Filter by status
        if (!empty($args['status'])) {
            if ($args['status'] === 'success') {
                $where[] = "status = 'success'";
            } elseif ($args['status'] === 'failed') {
                $where[] = "status != 'success'";
            } else {
                $where[] = "status = %s";
                $values[] = $args['status'];
            }
        }

        // Search filter (searches in notes or link-related data)
        if (!empty($args['search'])) {
            $search_term = '%' . $wpdb->esc_like($args['search']) . '%';

            // Join with links table to search by email
            $where[] = "(notes LIKE %s OR l.user_email LIKE %s OR user_ip LIKE %s)";
            $values[] = $search_term;
            $values[] = $search_term;
            $values[] = $search_term;
        }

        // Date range filter
        if (!empty($args['start_date'])) {
            $where[] = "accessed_at >= %s";
            $values[] = $args['start_date'] . ' 00:00:00';
        }

        if (!empty($args['end_date'])) {
            $where[] = "accessed_at <= %s";
            $values[] = $args['end_date'] . ' 23:59:59';
        }

        // Build the query
        $sql = "SELECT a.*, l.user_email 
                FROM {$this->log_table_name} a
                LEFT JOIN {$this->table_name} l ON a.link_id = l.id";

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        // Order
        $allowed_orderby = array('accessed_at', 'user_ip', 'status');
        $allowed_order = array('ASC', 'DESC');

        $orderby = in_array($args['orderby'], $allowed_orderby) ? $args['orderby'] : 'accessed_at';
        $order = in_array(strtoupper($args['order']), $allowed_order) ? strtoupper($args['order']) : 'DESC';

        $sql .= " ORDER BY a.{$orderby} {$order}";

        // Pagination
        $per_page = max(1, absint($args['per_page']));
        $page = max(1, absint($args['page']));
        $offset = ($page - 1) * $per_page;

        $sql .= " LIMIT %d, %d";
        $values[] = $offset;
        $values[] = $per_page;

        // Get results
        $logs = $wpdb->get_results($wpdb->prepare($sql, $values), ARRAY_A);

        // Count total items
        $count_sql = "SELECT COUNT(*) FROM {$this->log_table_name} a";

        if (!empty($where)) {
            if (!empty($args['search'])) {
                $count_sql .= " LEFT JOIN {$this->table_name} l ON a.link_id = l.id";
            }
            $count_sql .= ' WHERE ' . implode(' AND ', $where);
            $total_items = $wpdb->get_var($wpdb->prepare($count_sql, array_slice($values, 0, count($values) - 2)));
        } else {
            $total_items = $wpdb->get_var($count_sql);
        }

        return array(
            'items'       => $logs,
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'page'        => $page,
        );
    }    
    
    
}
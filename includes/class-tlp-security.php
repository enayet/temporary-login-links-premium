<?php
/**
 * Security functionality for the plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Temporary_Login_Links_Premium
 * @subpackage Temporary_Login_Links_Premium/includes
 */

/**
 * Security functionality for the plugin.
 *
 * This class handles security aspects of the temporary login process,
 * including token validation, brute force protection, and secure token generation.
 *
 * @since      1.0.0
 * @package    Temporary_Login_Links_Premium
 * @subpackage Temporary_Login_Links_Premium/includes
 * @author     Your Name <email@example.com>
 */
class TLP_Security {

    /**
     * The database table name for security logs.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $security_log_table    The database table name for security logs.
     */
    private $security_log_table;

    /**
     * Maximum number of failed attempts before throttling.
     *
     * @since    1.0.0
     * @access   private
     * @var      int    $max_failed_attempts    Maximum failed attempts.
     */
    private $max_failed_attempts = 5;

    /**
     * The lockout time in seconds.
     *
     * @since    1.0.0
     * @access   private
     * @var      int    $lockout_time    Lockout time in seconds.
     */
    private $lockout_time = 1800; // 30 minutes

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */
    public function __construct() {
        global $wpdb;
        $this->security_log_table = $wpdb->prefix . 'temporary_login_security_logs';
        
        // Maybe adjust max failed attempts based on settings
        $settings = get_option('temporary_login_links_premium_settings', array());
        if (!empty($settings['max_failed_attempts'])) {
            $this->max_failed_attempts = (int) $settings['max_failed_attempts'];
        }
        
        // Maybe adjust lockout time based on settings
        if (!empty($settings['lockout_time'])) {
            $this->lockout_time = (int) $settings['lockout_time'] * 60; // Convert minutes to seconds
        }
    }

    /**
     * Register hooks related to security features.
     *
     * @since    1.0.0
     */
    public function register_hooks() {
        // Add nonce verification to admin actions
        add_action('admin_init', array($this, 'validate_admin_actions'));
        
        // Initialize IP blocking if enabled
        add_action('init', array($this, 'maybe_block_ip'));
        
        // Cleanup old security logs
        add_action('wp_scheduled_delete', array($this, 'cleanup_security_logs'));
        
        // Add security headers
        add_action('admin_init', array($this, 'add_security_headers'));
    }

    /**
     * Validate admin actions using nonces.
     *
     * @since    1.0.0
     */
    public function validate_admin_actions() {
        // Check if we're on our plugin's admin page
        if (!isset($_GET['page']) || strpos($_GET['page'], 'temporary-login-links-premium') !== 0) {
            return;
        }
        
        // Check for actions that require nonce verification
        $actions_requiring_nonce = array('create', 'edit', 'delete', 'deactivate', 'activate', 'extend');
        
        if (isset($_REQUEST['action']) && in_array($_REQUEST['action'], $actions_requiring_nonce)) {
            // Verify nonce
            if (!isset($_REQUEST['_wpnonce']) || !wp_verify_nonce($_REQUEST['_wpnonce'], 'tlp_' . $_REQUEST['action'])) {
                wp_die(__('Security check failed. Please try again.', 'temporary-login-links-premium'), 
                       __('Security Error', 'temporary-login-links-premium'), 
                       array('response' => 403, 'back_link' => true));
            }
        }
    }

    /**
     * Generate a secure random token.
     *
     * @since    1.0.0
     * @param    int     $length    Optional. The length of the token. Default 32.
     * @return   string             The generated token.
     */
    public function generate_secure_token($length = 32) {
        try {
            // Use random_bytes() if available (PHP 7+)
            $bytes = random_bytes($length / 2);
            return bin2hex($bytes);
        } catch (Exception $e) {
            // Fallback for older PHP versions
            $chars = '0123456789abcdef';
            $max = strlen($chars) - 1;
            $token = '';
            
            for ($i = 0; $i < $length; $i++) {
                $token .= $chars[random_int(0, $max)];
            }
            
            return $token;
        }
    }

    /**
     * Record a security event in the database.
     *
     * @since    1.0.0
     * @param    string    $token     The token that was used (or attempted).
     * @param    string    $status    The status of the attempt (e.g., 'failed', 'blocked').
     * @param    string    $reason    The reason for the log entry.
     * @param    string    $email     Optional. User email if available.
     * @return   int|false            The inserted ID or false on failure.
     */
    public function log_security_event($token, $status, $reason, $email = '') {
        global $wpdb;
        
        // Insert the log entry
        $result = $wpdb->insert(
            $this->security_log_table,
            array(
                'token_fragment' => substr($token, 0, 8) . '...',
                'user_email'     => $email,
                'user_ip'        => $this->get_client_ip(),
                'user_agent'     => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Unknown',
                'logged_at'      => current_time('mysql'),
                'status'         => $status,
                'reason'         => $reason,
            ),
            array('%s', '%s', '%s', '%s', '%s', '%s', '%s')
        );
        
        if ($result) {
            // If it was a failed attempt, check if we need to notify admin
            if ($status === 'failed' || $status === 'blocked') {
                $this->maybe_notify_admin_of_suspicious_activity($token, $reason);
            }
            
            return $wpdb->insert_id;
        }
        
        return false;
    }

    /**
     * Check if an IP is currently locked out due to too many failed attempts.
     *
     * @since    1.0.0
     * @param    string    $ip    Optional. The IP to check. Default current IP.
     * @return   bool|int          False if not locked, lockout expiry time if locked.
     */
    public function is_ip_locked($ip = null) {
        global $wpdb;
        
        if (null === $ip) {
            $ip = $this->get_client_ip();
        }
        
        // Get the number of failed attempts in the lockout period
        $lockout_start = date('Y-m-d H:i:s', time() - $this->lockout_time);
        
        $attempts = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->security_log_table} 
            WHERE user_ip = %s 
            AND status = 'failed' 
            AND logged_at > %s",
            $ip,
            $lockout_start
        ));
        
        if ($attempts >= $this->max_failed_attempts) {
            // Get the time of the most recent failed attempt
            $last_attempt = $wpdb->get_var($wpdb->prepare(
                "SELECT MAX(logged_at) FROM {$this->security_log_table} 
                WHERE user_ip = %s AND status = 'failed'",
                $ip
            ));
            
            if ($last_attempt) {
                $lockout_expiry = strtotime($last_attempt) + $this->lockout_time;
                
                // If the lockout hasn't expired yet
                if (time() < $lockout_expiry) {
                    return $lockout_expiry;
                }
            }
        }
        
        return false;
    }

    /**
     * Block an IP if it has too many failed attempts.
     *
     * @since    1.0.0
     */
    public function maybe_block_ip() {
        // Only check on the login page with our token parameter
        if (!isset($_GET['temp_login'])) {
            return;
        }
        
        $ip = $this->get_client_ip();
        $lockout_expiry = $this->is_ip_locked($ip);
        
        if ($lockout_expiry) {
            $time_remaining = $lockout_expiry - time();
            $minutes = ceil($time_remaining / 60);
            
            // Log the blocked attempt
            $token = isset($_GET['temp_login']) ? sanitize_text_field($_GET['temp_login']) : 'unknown';
            $this->log_security_event(
                $token,
                'blocked',
                sprintf('IP blocked due to too many failed attempts. %d minutes remaining.', $minutes)
            );
            
            wp_die(
                sprintf(
                    __('Too many failed login attempts from your IP address. Please try again in %d minutes.', 'temporary-login-links-premium'),
                    $minutes
                ),
                __('Temporary Access Blocked', 'temporary-login-links-premium'),
                array('response' => 403)
            );
        }
    }

    /**
     * Clean up old security logs.
     *
     * @since    1.0.0
     * @param    int       $days    Optional. Number of days to keep logs. Default 30.
     * @return   int                Number of deleted logs.
     */
    public function cleanup_security_logs($days = 30) {
        global $wpdb;
        
        // Get retention setting
        $settings = get_option('temporary_login_links_premium_settings', array());
        if (!empty($settings['keep_logs_days'])) {
            $days = (int) $settings['keep_logs_days'];
        }
        
        // Calculate cutoff date
        $cutoff_date = date('Y-m-d H:i:s', time() - ($days * DAY_IN_SECONDS));
        
        // Delete old logs
        $deleted = $wpdb->query($wpdb->prepare(
            "DELETE FROM {$this->security_log_table} WHERE logged_at < %s",
            $cutoff_date
        ));
        
        return $deleted;
    }

    /**
     * Get the client's IP address.
     *
     * @since    1.0.0
     * @return   string    The client's IP address.
     */
    
    public function get_client_ip() {
        
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return sanitize_text_field($_SERVER['HTTP_CLIENT_IP']);
        }        
        
        // If you're behind a load balancer or proxy that sets X-Forwarded-For
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) && filter_var($_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP)) {
            // Use the first IP in the list
            $ip_list = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ip_list[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return sanitize_text_field($ip);
            }
        }

        // Get direct remote address as fallback
        if (!empty($_SERVER['REMOTE_ADDR']) && filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP)) {
            return sanitize_text_field($_SERVER['REMOTE_ADDR']);
        }

        // Default
        return '127.0.0.1';
    }    
    

    /**
     * Validate an IP address.
     *
     * @since    1.0.0
     * @param    string    $ip    The IP address to validate.
     * @return   bool             Whether the IP address is valid.
     */
    public function is_valid_ip($ip) {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * Validate IP restriction format.
     *
     * @since    1.0.0
     * @param    string    $ip_restriction    The IP restriction string (comma-separated IPs).
     * @return   bool|string                  True if valid, error message if invalid.
     */
    public function validate_ip_restriction($ip_restriction) {
        if (empty($ip_restriction)) {
            return true; // No restrictions is valid
        }
        
        $ips = array_map('trim', explode(',', $ip_restriction));
        
        foreach ($ips as $ip) {
            if (!$this->is_valid_ip($ip)) {
                return sprintf(__('Invalid IP address: %s', 'temporary-login-links-premium'), $ip);
            }
        }
        
        return true;
    }

    /**
     * Notify admin of suspicious activity.
     *
     * @since    1.0.0
     * @param    string    $token     The token involved.
     * @param    string    $reason    The reason for the suspicious activity.
     */
    private function maybe_notify_admin_of_suspicious_activity($token, $reason) {
        // Check settings to see if we should send notifications
        $settings = get_option('temporary_login_links_premium_settings', array());
        
        if (empty($settings['security_notifications']) || $settings['security_notifications'] != 1) {
            return;
        }
        
        global $wpdb;
        
        // Check how many failed attempts in the last hour from this IP
        $ip = $this->get_client_ip();
        $one_hour_ago = date('Y-m-d H:i:s', time() - HOUR_IN_SECONDS);
        
        $attempts = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->security_log_table} 
            WHERE user_ip = %s 
            AND status IN ('failed', 'blocked') 
            AND logged_at > %s",
            $ip,
            $one_hour_ago
        ));
        
        // Only notify if we've hit the threshold
        if ($attempts < $this->max_failed_attempts) {
            return;
        }
        
        // Check if we've already notified for this IP recently
        $notifications = get_option('temporary_login_links_premium_notifications', array());
        
        if (isset($notifications[$ip]) && $notifications[$ip] > time() - HOUR_IN_SECONDS) {
            return; // Already notified in the last hour
        }
        
        // Record this notification
        $notifications[$ip] = time();
        update_option('temporary_login_links_premium_notifications', $notifications);
        
        // Get admin email
        $admin_email = get_option('admin_email');
        
        // Prepare email content
        $subject = sprintf(
            __('[%s] Suspicious temporary login activity detected', 'temporary-login-links-premium'),
            get_bloginfo('name')
        );
        
        $message = sprintf(
            __("Multiple failed temporary login attempts have been detected from IP: %s\n\n", 'temporary-login-links-premium'),
            $ip
        );
        
        $message .= sprintf(
            __("Number of failed attempts: %d\n", 'temporary-login-links-premium'),
            $attempts
        );
        
        $message .= sprintf(
            __("Latest reason: %s\n\n", 'temporary-login-links-premium'),
            $reason
        );
        
        // Get recent failed attempts
        $recent_attempts = $wpdb->get_results($wpdb->prepare(
            "SELECT logged_at, token_fragment, reason 
            FROM {$this->security_log_table} 
            WHERE user_ip = %s 
            AND status IN ('failed', 'blocked') 
            ORDER BY logged_at DESC LIMIT 5",
            $ip
        ));
        
        if ($recent_attempts) {
            $message .= __("Recent failed attempts:\n", 'temporary-login-links-premium');
            
            foreach ($recent_attempts as $attempt) {
                $message .= sprintf(
                    "- %s: %s (%s)\n",
                    $attempt->logged_at,
                    $attempt->token_fragment,
                    $attempt->reason
                );
            }
        }
        
        $message .= "\n";
        $message .= sprintf(
            __("The IP has been temporarily blocked for %d minutes.\n\n", 'temporary-login-links-premium'),
            $this->lockout_time / 60
        );
        
        $message .= __("You can view all security logs in your WordPress dashboard.\n", 'temporary-login-links-premium');
        $message .= admin_url('admin.php?page=temporary-login-links-premium-security');
        
        // Send the email
        wp_mail($admin_email, $subject, $message);
    }

    /**
     * Sanitize and validate form inputs.
     *
     * @since    1.0.0
     * @param    array     $inputs    The form inputs to sanitize.
     * @param    array     $fields    The field definitions with sanitize/validate rules.
     * @return   array|WP_Error       The sanitized inputs or error.
     */
    public function sanitize_form_inputs($inputs, $fields) {
        $sanitized = array();
        $errors = array();
        
        foreach ($fields as $field_name => $field) {
            // Skip if field not in inputs and not required
            if (!isset($inputs[$field_name])) {
                if (isset($field['required']) && $field['required']) {
                    $errors[] = sprintf(__('%s is required.', 'temporary-login-links-premium'), $field['label']);
                }
                continue;
            }
            
            $value = $inputs[$field_name];
            
            // Apply sanitization based on field type
            switch ($field['type']) {
                case 'email':
                    $value = sanitize_email($value);
                    if (!empty($value) && !is_email($value)) {
                        $errors[] = sprintf(__('%s must be a valid email address.', 'temporary-login-links-premium'), $field['label']);
                    }
                    break;
                    
                case 'text':
                    $value = sanitize_text_field($value);
                    break;
                    
                case 'textarea':
                    $value = sanitize_textarea_field($value);
                    break;
                    
                case 'number':
                    $value = intval($value);
                    // Check min/max if specified
                    if (isset($field['min']) && $value < $field['min']) {
                        $errors[] = sprintf(__('%s must be at least %d.', 'temporary-login-links-premium'), $field['label'], $field['min']);
                    }
                    if (isset($field['max']) && $value > $field['max']) {
                        $errors[] = sprintf(__('%s must be at most %d.', 'temporary-login-links-premium'), $field['label'], $field['max']);
                    }
                    break;
                    
                case 'ip':
                    $value = sanitize_text_field($value);
                    if (!empty($value)) {
                        $validation = $this->validate_ip_restriction($value);
                        if ($validation !== true) {
                            $errors[] = $validation;
                        }
                    }
                    break;
                    
                case 'role':
                    $value = sanitize_text_field($value);
                    if (!empty($value) && !get_role($value)) {
                        $errors[] = sprintf(__('%s is not a valid role.', 'temporary-login-links-premium'), $value);
                    }
                    break;
                    
                case 'url':
                    $value = esc_url_raw($value);
                    break;
                    
                case 'checkbox':
                    $value = isset($value) ? 1 : 0;
                    break;
                    
                case 'select':
                    $value = sanitize_text_field($value);
                    // Check if value is in allowed options
                    if (isset($field['options']) && !isset($field['options'][$value])) {
                        $errors[] = sprintf(__('%s is not a valid option.', 'temporary-login-links-premium'), $field['label']);
                    }
                    break;
                    
                case 'date':
                    $value = sanitize_text_field($value);
                    // Basic date validation (Y-m-d H:i:s)
                    if (!empty($value) && !preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $value)) {
                        $errors[] = sprintf(__('%s must be in the format YYYY-MM-DD HH:MM:SS.', 'temporary-login-links-premium'), $field['label']);
                    }
                    break;
                    
                default:
                    $value = sanitize_text_field($value);
                    break;
            }
            
            // Apply custom validation if specified
            if (isset($field['validate_callback']) && is_callable($field['validate_callback'])) {
                $validation = call_user_func($field['validate_callback'], $value);
                if ($validation !== true) {
                    $errors[] = $validation;
                }
            }
            
            $sanitized[$field_name] = $value;
        }
        
        if (!empty($errors)) {
            return new WP_Error('validation_failed', implode('<br>', $errors));
        }
        
        return $sanitized;
    }

    /**
     * Get security logs from the database.
     *
     * @since    1.0.0
     * @param    array     $args    Query arguments.
     * @return   array              Security logs data.
     */
    public function get_security_logs($args = array()) {
        global $wpdb;
        
        $defaults = array(
            'per_page' => 20,
            'page' => 1,
            'status' => '',
            'search' => '',
            'daterange' => '',
        );
        
        $args = wp_parse_args($args, $defaults);
        
        // Build query
        $sql = "SELECT * FROM {$this->security_log_table}";
        $where = array();
        $where_args = array();
        
        // Status filter
        if (!empty($args['status'])) {
            if ($args['status'] === 'failed') {
                $where[] = "status IN ('failed', 'blocked')";
            } else {
                $where[] = "status = %s";
                $where_args[] = $args['status'];
            }
        }
        
        // Search filter
        if (!empty($args['search'])) {
            $search = '%' . $wpdb->esc_like($args['search']) . '%';
            $where[] = "(token_fragment LIKE %s OR user_email LIKE %s OR user_ip LIKE %s OR reason LIKE %s)";
            $where_args[] = $search;
            $where_args[] = $search;
            $where_args[] = $search;
            $where_args[] = $search;
        }
        
        // Date range filter
        if (!empty($args['daterange'])) {
            list($start_date, $end_date) = explode('|', $args['daterange']);
            if (!empty($start_date)) {
                $where[] = "logged_at >= %s";
                $where_args[] = $start_date . ' 00:00:00';
            }
            if (!empty($end_date)) {
                $where[] = "logged_at <= %s";
                $where_args[] = $end_date . ' 23:59:59';
            }
        }
        
        // Add WHERE clause if needed
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        
        // Add ORDER BY
        $sql .= " ORDER BY logged_at DESC";
        
        // Count total items
        $count_sql = "SELECT COUNT(*) FROM {$this->security_log_table}";
        if (!empty($where)) {
            $count_sql .= " WHERE " . implode(' AND ', $where);
        }
        
        if (!empty($where_args)) {
            $total_items = $wpdb->get_var($wpdb->prepare($count_sql, $where_args));
        } else {
            $total_items = $wpdb->get_var($count_sql);
        }
        
        // Pagination
        $per_page = max(1, absint($args['per_page']));
        $page = max(1, absint($args['page']));
        $offset = ($page - 1) * $per_page;
        
        $sql .= " LIMIT %d, %d";
        $where_args[] = $offset;
        $where_args[] = $per_page;
        
        // Get results
        if (!empty($where_args)) {
            $logs = $wpdb->get_results($wpdb->prepare($sql, $where_args), ARRAY_A);
        } else {
            $logs = $wpdb->get_results($sql, ARRAY_A);
        }
        
        return array(
            'items' => $logs,
            'total_items' => $total_items,
            'per_page' => $per_page,
            'page' => $page,
        );
    }

    /**
     * Check if a request is a valid AJAX request.
     *
     * @since    1.0.0
     * @return   bool    Whether the request is a valid AJAX request.
     */
    public function is_valid_ajax_request() {
        return (
            defined('DOING_AJAX') && 
            DOING_AJAX && 
            check_ajax_referer('tlp_ajax_nonce', 'nonce', false)
        );
    }

    /**
     * Validate that the current user has the required capability.
     *
     * @since    1.0.0
     * @return   bool     Whether the user has the capability.
     */
    public function current_user_can_manage() {
        return current_user_can('manage_temporary_logins');
    }

    /**
     * Add security headers for plugin pages.
     *
     * @since    1.0.0
     */
    public function add_security_headers() {
        // Only add headers for our plugin pages
        if (!isset($_GET['page']) || strpos($_GET['page'], 'temporary-login-links-premium') !== 0) {
            return;
        }
        
        // Add Content-Security-Policy header
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data:;");
        
        // Add X-Content-Type-Options header
        header("X-Content-Type-Options: nosniff");
        
        // Add X-Frame-Options header
        header("X-Frame-Options: SAMEORIGIN");
        
        // Add X-XSS-Protection header
        header("X-XSS-Protection: 1; mode=block");
        
        // Add Referrer-Policy header
        header("Referrer-Policy: strict-origin-when-cross-origin");
    }
}
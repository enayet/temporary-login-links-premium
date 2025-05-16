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
     * The storage for failed login attempts.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $failed_attempts_option    The option name for storing failed login attempts.
     */
    private $failed_attempts_option = 'temporary_login_links_premium_failed_attempts';

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
        // Maybe adjust max failed attempts based on settings
        $settings = get_option('temporary_login_links_premium_settings', array());
        if (!empty($settings['max_failed_attempts'])) {
            $this->max_failed_attempts = (int) $settings['max_failed_attempts'];
        }
        
        // Maybe adjust lockout time based on settings
        if (!empty($settings['lockout_time'])) {
            $this->lockout_time = (int) $settings['lockout_time'];
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
        
        // Cleanup old failed attempts
        add_action('wp_scheduled_delete', array($this, 'cleanup_failed_attempts'));
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
     * Record a failed login attempt.
     *
     * @since    1.0.0
     * @param    string    $token    The login token that failed.
     * @param    string    $reason   The reason for the failure.
     */
    public function record_failed_attempt($token, $reason) {
        $ip = $this->get_client_ip();
        $failed_attempts = get_option($this->failed_attempts_option, array());
        
        // Initialize or update the IP entry
        if (!isset($failed_attempts[$ip])) {
            $failed_attempts[$ip] = array(
                'count' => 0,
                'first_attempt' => time(),
                'last_attempt' => time(),
                'attempts' => array()
            );
        }
        
        // Record this attempt
        $failed_attempts[$ip]['count']++;
        $failed_attempts[$ip]['last_attempt'] = time();
        $failed_attempts[$ip]['attempts'][] = array(
            'token' => substr($token, 0, 8) . '...', // Store only part of the token for security
            'time' => time(),
            'reason' => $reason,
            'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : 'Unknown'
        );
        
        // Keep only the last 10 attempts to save space
        if (count($failed_attempts[$ip]['attempts']) > 10) {
            $failed_attempts[$ip]['attempts'] = array_slice($failed_attempts[$ip]['attempts'], -10);
        }
        
        // Save updated failed attempts
        update_option($this->failed_attempts_option, $failed_attempts);
        
        // Maybe notify admin of suspicious activity
        $this->maybe_notify_admin_of_suspicious_activity($ip, $failed_attempts[$ip]);
    }

    /**
     * Check if an IP is currently locked out.
     *
     * @since    1.0.0
     * @param    string    $ip    Optional. The IP to check. Default current IP.
     * @return   bool|int          False if not locked, lockout expiry time if locked.
     */
    public function is_ip_locked($ip = null) {
        if (null === $ip) {
            $ip = $this->get_client_ip();
        }
        
        $failed_attempts = get_option($this->failed_attempts_option, array());
        
        if (isset($failed_attempts[$ip])) {
            $attempts = $failed_attempts[$ip];
            
            // If we have more than max failed attempts within the lockout period
            if ($attempts['count'] >= $this->max_failed_attempts) {
                $lockout_expiry = $attempts['last_attempt'] + $this->lockout_time;
                
                // If the lockout period has expired, reset the count
                if (time() > $lockout_expiry) {
                    $failed_attempts[$ip]['count'] = 0;
                    $failed_attempts[$ip]['first_attempt'] = time();
                    update_option($this->failed_attempts_option, $failed_attempts);
                    return false;
                }
                
                return $lockout_expiry;
            }
        }
        
        return false;
    }

    /**
     * Maybe block an IP based on failed attempts.
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
     * Clean up old failed attempts.
     *
     * @since    1.0.0
     */
    public function cleanup_failed_attempts() {
        $failed_attempts = get_option($this->failed_attempts_option, array());
        $now = time();
        $cleanup_time = $this->lockout_time * 2; // Keep data for twice the lockout time
        
        foreach ($failed_attempts as $ip => $attempts) {
            // Remove entries older than cleanup time
            if ($now - $attempts['last_attempt'] > $cleanup_time) {
                unset($failed_attempts[$ip]);
            }
        }
        
        update_option($this->failed_attempts_option, $failed_attempts);
    }

    /**
     * Get the client's IP address.
     *
     * @since    1.0.0
     * @return   string    The client's IP address.
     */
    public function get_client_ip() {
        // Check for shared internet/ISP IP
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return sanitize_text_field($_SERVER['HTTP_CLIENT_IP']);
        }
        
        // Check for IPs passing through proxies
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // Use the first IP from the list
            $ip_list = explode(',', sanitize_text_field($_SERVER['HTTP_X_FORWARDED_FOR']));
            return trim($ip_list[0]);
        }
        
        // Use remote address if available
        if (!empty($_SERVER['REMOTE_ADDR'])) {
            return sanitize_text_field($_SERVER['REMOTE_ADDR']);
        }
        
        // Fallback to a generic IP
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
     * Maybe notify admin of suspicious activity.
     *
     * @since    1.0.0
     * @param    string    $ip         The IP address with suspicious activity.
     * @param    array     $attempts   The failed attempts data.
     */
    private function maybe_notify_admin_of_suspicious_activity($ip, $attempts) {
        // Check settings to see if we should send notifications
        $settings = get_option('temporary_login_links_premium_settings', array());
        
        if (empty($settings['security_notifications']) || $settings['security_notifications'] != 1) {
            return;
        }
        
        // Only notify if we've hit the threshold
        if ($attempts['count'] < $this->max_failed_attempts) {
            return;
        }
        
        // Check if we've already notified for this IP recently
        $notifications = get_option('temporary_login_links_premium_notifications', array());
        
        if (isset($notifications[$ip]) && $notifications[$ip] > time() - 3600) {
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
            $attempts['count']
        );
        
        $message .= sprintf(
            __("First attempt: %s\n", 'temporary-login-links-premium'),
            date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $attempts['first_attempt'])
        );
        
        $message .= sprintf(
            __("Latest attempt: %s\n\n", 'temporary-login-links-premium'),
            date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $attempts['last_attempt'])
        );
        
        $message .= __("Recent failed attempts:\n", 'temporary-login-links-premium');
        
        foreach (array_slice($attempts['attempts'], -5) as $attempt) {
            $message .= sprintf(
                "- %s: %s (%s)\n",
                date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $attempt['time']),
                $attempt['token'],
                $attempt['reason']
            );
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
     * Get security logs for the admin.
     *
     * @since    1.0.0
     * @param    array     $args    Query arguments.
     * @return   array              Security logs data.
     */
    public function get_security_logs($args = array()) {
        $defaults = array(
            'per_page' => 20,
            'page' => 1,
        );
        
        $args = wp_parse_args($args, $defaults);
        $failed_attempts = get_option($this->failed_attempts_option, array());
        $logs = array();
        
        // Flatten the logs for display
        foreach ($failed_attempts as $ip => $attempts) {
            foreach ($attempts['attempts'] as $attempt) {
                $logs[] = array(
                    'ip' => $ip,
                    'time' => $attempt['time'],
                    'token' => $attempt['token'],
                    'reason' => $attempt['reason'],
                    'user_agent' => $attempt['user_agent'],
                );
            }
        }
        
        // Sort by time descending
        usort($logs, function($a, $b) {
            return $b['time'] - $a['time'];
        });
        
        // Paginate
        $total_items = count($logs);
        $per_page = max(1, absint($args['per_page']));
        $page = max(1, absint($args['page']));
        $offset = ($page - 1) * $per_page;
        
        $logs = array_slice($logs, $offset, $per_page);
        
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
     * @param    string    $capability    The capability to check.
     * @return   bool                     Whether the user has the capability.
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
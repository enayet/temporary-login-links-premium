<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Temporary_Login_Links_Premium
 * @subpackage Temporary_Login_Links_Premium/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for
 * the public-facing side of the site.
 *
 * @package    Temporary_Login_Links_Premium
 * @subpackage Temporary_Login_Links_Premium/public
 * @author     Your Name <email@example.com>
 */
class TLP_Public {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * The links instance.
     *
     * @since    1.0.0
     * @access   private
     * @var      TLP_Links    $links    The links instance.
     */
    private $links;

    /**
     * The security instance.
     *
     * @since    1.0.0
     * @access   private
     * @var      TLP_Security    $security    The security instance.
     */
    private $security;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param    string    $plugin_name       The name of the plugin.
     * @param    string    $version           The version of this plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        
        // Initialize the links and security instances
        $this->links = new TLP_Links();
        $this->security = new TLP_Security();
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        // Only enqueue on login page with temp_login parameter
        if ($this->is_temporary_login_page()) {
            wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/tlp-public.css', array(), $this->version, 'all');
        }
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        // Only enqueue on login page with temp_login parameter
        if ($this->is_temporary_login_page()) {
            wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/tlp-public.js', array('jquery'), $this->version, false);
        }
    }

    /**
     * Check if current page is a temporary login page.
     *
     * @since    1.0.0
     * @return   bool    True if this is a temporary login page.
     */
    private function is_temporary_login_page() {
        global $pagenow;
        
        return $pagenow === 'wp-login.php' && isset($_GET['temp_login']);
    }

    /**
     * Register hooks for the public-facing functionality.
     *
     * @since    1.0.0
     */
    public function register_hooks() {
        // Intercept login page for temporary login links
        add_action('login_init', array($this, 'process_temporary_login'));
        
        // Customize the login page with branding
        add_action('login_head', array($this, 'customize_login_page'));
        add_action('login_header', array($this, 'add_welcome_message'));
        add_action('login_enqueue_scripts', array($this, 'enqueue_branding_styles'));
        
        // Add custom login form
        add_filter('login_message', array($this, 'add_temporary_login_message'));
        
        // Change login logo URL
        add_filter('login_headerurl', array($this, 'change_login_logo_url'));
        add_filter('login_headertext', array($this, 'change_login_logo_text'));
    }

    /**
     * Process temporary login links.
     *
     * @since    1.0.0
     */
    public function process_temporary_login() {
        // Only process if the temp_login parameter is present
        if (!isset($_GET['temp_login'])) {
            return;
        }
        
        // Get the token
        $token = sanitize_text_field($_GET['temp_login']);
        
        // Validate the token
        $result = $this->links->validate_login_token($token);
        
        // Check if validation was successful
        if (is_wp_error($result)) {
            // Show error message
            $this->show_login_error($result->get_error_message());
            return;
        }
        
        // Log in the user
        $this->login_user($result['user_id'], $result['redirect_to']);
    }

    /**
     * Show login error message.
     *
     * @since    1.0.0
     * @param    string    $message    The error message.
     */
    private function show_login_error($message) {
        // Store the error message for display
        global $error;
        $error = $message;
        
        // Add a hook to display error in the login form
        add_filter('login_message', function() use ($message) {
            return '<div id="login_error">' . esc_html($message) . '</div>';
        });
    }

    /**
     * Log in the user with a temporary login link.
     *
     * @since    1.0.0
     * @param    int       $user_id       The user ID.
     * @param    string    $redirect_to   The URL to redirect to after login.
     */
    private function login_user($user_id, $redirect_to) {
        // Get the user
        $user = get_user_by('id', $user_id);
        
        if (!$user) {
            $this->show_login_error(__('User not found.', 'temporary-login-links-premium'));
            return;
        }
        
        // Set auth cookie
        wp_set_auth_cookie($user_id, false);
        
        // Set current user
        wp_set_current_user($user_id);
        
        // Update user last login
        update_user_meta($user_id, 'tlp_last_login', current_time('mysql'));
        
        // Send admin notification if enabled
        $this->maybe_send_admin_notification($user);
        
        // Redirect after login
        wp_redirect($redirect_to);
        exit;
    }

    /**
     * Maybe send admin notification when a temporary login is used.
     *
     * @since    1.0.0
     * @param    WP_User    $user    The user who logged in.
     */
    private function maybe_send_admin_notification($user) {
        // Check if admin notifications are enabled
        $settings = get_option('temporary_login_links_premium_settings', array());
        
        if (empty($settings['admin_notification']) || $settings['admin_notification'] != 1) {
            return;
        }
        
        // Get admin email
        $admin_email = get_option('admin_email');
        
        // Prepare email content
        $subject = sprintf(__('[%s] Temporary Login Used', 'temporary-login-links-premium'), get_bloginfo('name'));
        
        $message = sprintf(__("Hello,\n\nThis is a notification that a temporary login link has been used on your website %s.\n\n", 'temporary-login-links-premium'), get_bloginfo('name'));
        
        $message .= sprintf(__("User Email: %s\n", 'temporary-login-links-premium'), $user->user_email);
        $message .= sprintf(__("User Role: %s\n", 'temporary-login-links-premium'), $this->get_role_display_name($user->roles[0]));
        $message .= sprintf(__("Login Time: %s\n", 'temporary-login-links-premium'), current_time('mysql'));
        $message .= sprintf(__("IP Address: %s\n\n", 'temporary-login-links-premium'), $this->security->get_client_ip());
        
        $message .= sprintf(__("You can view all temporary links here: %s\n\n", 'temporary-login-links-premium'), admin_url('admin.php?page=temporary-login-links-premium-links'));
        
        $message .= sprintf(__("Regards,\n%s Team", 'temporary-login-links-premium'), get_bloginfo('name'));
        
        // Send the email
        wp_mail($admin_email, $subject, $message);
    }

    /**
     * Customize the login page with branding.
     *
     * @since    1.0.0
     */
    public function customize_login_page() {
        // Only customize if this is a temporary login page
        if (!$this->is_temporary_login_page()) {
            return;
        }
        
        // Get branding settings
        $branding = get_option('temporary_login_links_premium_branding', array());
        
        // Check if branding is enabled
        if (empty($branding['enable_branding']) || $branding['enable_branding'] != 1) {
            return;
        }
        
        // Build custom CSS
        $css = '<style type="text/css">';
        
        // Logo
        if (!empty($branding['login_logo'])) {
            $css .= 'body.login h1 a { 
                background-image: url(' . esc_url($branding['login_logo']) . '); 
                background-size: contain; 
                width: 320px; 
                height: 80px; 
                margin-bottom: 30px;
            }';
        }
        
        // Background color
        if (!empty($branding['login_background_color'])) {
            $css .= 'body.login { background-color: ' . esc_attr($branding['login_background_color']) . '; }';
        }
        
        // Form background
        if (!empty($branding['login_form_background'])) {
            $css .= 'body.login #loginform { background-color: ' . esc_attr($branding['login_form_background']) . '; }';
        }
        
        // Form text color
        if (!empty($branding['login_form_text_color'])) {
            $css .= 'body.login #loginform label, body.login #loginform .forgetmenot label { color: ' . esc_attr($branding['login_form_text_color']) . '; }';
        }
        
        // Button colors
        if (!empty($branding['login_button_color'])) {
            $css .= 'body.login #loginform #wp-submit { 
                background-color: ' . esc_attr($branding['login_button_color']) . '; 
                border-color: ' . esc_attr($branding['login_button_color']) . ';
            }';
        }
        
        if (!empty($branding['login_button_text_color'])) {
            $css .= 'body.login #loginform #wp-submit { color: ' . esc_attr($branding['login_button_text_color']) . '; }';
        }
        
        // Custom CSS
        if (!empty($branding['login_custom_css'])) {
            $css .= $branding['login_custom_css'];
        }
        
        $css .= '</style>';
        
        echo $css;
    }

    /**
     * Add welcome message to login page.
     *
     * @since    1.0.0
     */
    public function add_welcome_message() {
        // Only add welcome message if this is a temporary login page
        if (!$this->is_temporary_login_page()) {
            return;
        }
        
        // Get branding settings
        $branding = get_option('temporary_login_links_premium_branding', array());
        
        // Check if branding is enabled
        if (empty($branding['enable_branding']) || $branding['enable_branding'] != 1) {
            return;
        }
        
        // Get welcome text
        $welcome_text = isset($branding['login_welcome_text']) ? $branding['login_welcome_text'] : __('Welcome! You have been granted temporary access to this site.', 'temporary-login-links-premium');
        
        if (!empty($welcome_text)) {
            echo '<div class="tlp-welcome-message">' . wp_kses_post($welcome_text) . '</div>';
        }
    }

    /**
     * Enqueue branding styles for the login page.
     *
     * @since    1.0.0
     */
    public function enqueue_branding_styles() {
        // Only enqueue if this is a temporary login page
        if (!$this->is_temporary_login_page()) {
            return;
        }
        
        // Enqueue the branded login stylesheet
        wp_enqueue_style('tlp-branded-login', plugin_dir_url(__FILE__) . 'css/tlp-public.css', array(), $this->version);
    }

    /**
     * Add a message to the login form for temporary login links.
     *
     * @since    1.0.0
     * @param    string    $message    The current login message.
     * @return   string                The modified login message.
     */
    public function add_temporary_login_message($message) {
        // Only add message if this is a temporary login page
        if (!$this->is_temporary_login_page()) {
            return $message;
        }
        
        $token = isset($_GET['temp_login']) ? sanitize_text_field($_GET['temp_login']) : '';
        
        // Get token info
        global $wpdb;
        $table_name = $wpdb->prefix . 'temporary_login_links';
        
        $link = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE link_token = %s LIMIT 1",
                $token
            )
        );
        
        if (!$link) {
            return $message;
        }
        
        // Build message
        $temp_message = '<div class="tlp-login-message">';
        
        // Check status
        if ($link->is_active == 0) {
            $temp_message .= '<p class="tlp-status-message tlp-status-inactive">';
            $temp_message .= __('This login link has been deactivated.', 'temporary-login-links-premium');
            $temp_message .= '</p>';
        } elseif (strtotime($link->expiry) < time()) {
            $temp_message .= '<p class="tlp-status-message tlp-status-expired">';
            $temp_message .= __('This login link has expired.', 'temporary-login-links-premium');
            $temp_message .= '</p>';
        } elseif ($link->max_accesses > 0 && $link->access_count >= $link->max_accesses) {
            $temp_message .= '<p class="tlp-status-message tlp-status-maxed">';
            $temp_message .= __('This login link has reached its maximum number of uses.', 'temporary-login-links-premium');
            $temp_message .= '</p>';
        } else {
            $temp_message .= '<p class="tlp-status-message tlp-status-active">';
            $temp_message .= __('You are using a temporary login link. No password is required.', 'temporary-login-links-premium');
            $temp_message .= '</p>';
            
            // Add expiry info
            $expiry_date = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($link->expiry));
            $temp_message .= '<p class="tlp-expiry-info">';
            $temp_message .= sprintf(__('This link will expire on: %s', 'temporary-login-links-premium'), '<strong>' . $expiry_date . '</strong>');
            $temp_message .= '</p>';
            
            // Add auto-login script
            $temp_message .= $this->get_auto_login_script($link->user_login);
        }
        
        $temp_message .= '</div>';
        
        return $message . $temp_message;
    }

    /**
     * Get auto-login script for temporary links.
     *
     * @since    1.0.0
     * @param    string    $username    The username to auto-fill.
     * @return   string                 The auto-login script.
     */
    private function get_auto_login_script($username) {
        ob_start();
        ?>
        <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-fill the username
            var usernameField = document.getElementById('user_login');
            if (usernameField) {
                usernameField.value = '<?php echo esc_js($username); ?>';
            }
            
            // Hide the password field
            var passwordField = document.getElementById('user_pass');
            var passwordLabel = document.querySelector('label[for="user_pass"]');
            
            if (passwordField && passwordLabel) {
                passwordField.parentNode.style.display = 'none';
                passwordLabel.style.display = 'none';
            }
            
            // Change submit button text
            var submitButton = document.getElementById('wp-submit');
            if (submitButton) {
                submitButton.value = '<?php echo esc_js(__('Access Site', 'temporary-login-links-premium')); ?>';
                
                // Auto-submit the form
                setTimeout(function() {
                    document.getElementById('loginform').submit();
                }, 1500);
            }
            
            // Add loading indicator
            var form = document.getElementById('loginform');
            if (form) {
                var loadingIndicator = document.createElement('div');
                loadingIndicator.className = 'tlp-loading-indicator';
                loadingIndicator.innerHTML = '<?php echo esc_js(__('Logging in automatically...', 'temporary-login-links-premium')); ?>';
                form.appendChild(loadingIndicator);
            }
        });
        </script>
        <?php
        return ob_get_clean();
    }

    /**
     * Change login logo URL.
     *
     * @since    1.0.0
     * @param    string    $url    The login logo URL.
     * @return   string            The modified login logo URL.
     */
    public function change_login_logo_url($url) {
        // Only change if this is a temporary login page
        if (!$this->is_temporary_login_page()) {
            return $url;
        }
        
        // Get branding settings
        $branding = get_option('temporary_login_links_premium_branding', array());
        
        // Check if branding is enabled
        if (empty($branding['enable_branding']) || $branding['enable_branding'] != 1) {
            return $url;
        }
        
        return home_url();
    }

    /**
     * Change login logo text.
     *
     * @since    1.0.0
     * @param    string    $text    The login logo text.
     * @return   string             The modified login logo text.
     */
    public function change_login_logo_text($text) {
        // Only change if this is a temporary login page
        if (!$this->is_temporary_login_page()) {
            return $text;
        }
        
        // Get branding settings
        $branding = get_option('temporary_login_links_premium_branding', array());
        
        // Check if branding is enabled
        if (empty($branding['enable_branding']) || $branding['enable_branding'] != 1) {
            return $text;
        }
        
        // Use company name if set
        $company_name = isset($branding['company_name']) ? $branding['company_name'] : get_bloginfo('name');
        
        return $company_name;
    }

    /**
     * Get the display name for a user role.
     *
     * @since    1.0.0
     * @param    string    $role    The role slug.
     * @return   string             The display name for the role.
     */
    private function get_role_display_name($role) {
        global $wp_roles;
        
        if (!isset($wp_roles)) {
            $wp_roles = new WP_Roles();
        }
        
        return isset($wp_roles->roles[$role]) ? translate_user_role($wp_roles->roles[$role]['name']) : $role;
    }

    /**
     * Initialize the shortcodes.
     *
     * @since    1.0.0
     */
    public function init_shortcodes() {
        $shortcodes = new TLP_Shortcodes($this->plugin_name, $this->version, $this->links);
        $shortcodes->register_shortcodes();
    }
}
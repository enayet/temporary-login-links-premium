<?php
/**
 * Shortcodes for front-end functionality.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Temporary_Login_Links_Premium
 * @subpackage Temporary_Login_Links_Premium/public
 */

/**
 * Shortcodes for front-end functionality.
 *
 * This class defines all shortcodes for front-end functionality,
 * including creating links, displaying access information,
 * and showing user-specific content.
 *
 * @package    Temporary_Login_Links_Premium
 * @subpackage Temporary_Login_Links_Premium/public
 * @author     Your Name <email@example.com>
 */
class TLP_Shortcodes {

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
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param    string      $plugin_name    The name of the plugin.
     * @param    string      $version        The version of this plugin.
     * @param    TLP_Links   $links          The links instance.
     */
    public function __construct($plugin_name, $version, $links) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->links = $links;
    }
    
    
    
/**
 * Get the inline CSS styles for shortcodes.
 *
 * @since    1.0.0
 * @return   string    The inline CSS styles.
 */
    private function get_inline_styles() {
        // Check if inline styles have already been added to this page
        static $styles_added = false;

        // If styles already added, return empty string
        if ($styles_added) {
            return '';
        }

        // Mark styles as added
        $styles_added = true;

        // Start output buffer
        ob_start();
        ?>
    <style>
    /* Base styles for all TLP components */
    .tlp-shortcode-form,
    .tlp-shortcode-login-form,
    .tlp-user-info,
    .tlp-links-list,
    .tlp-message,
    .tlp-link-url {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
        font-size: 15px;
        line-height: 1.6;
        color: #333;
        box-sizing: border-box;
    }

    .tlp-shortcode-form *,
    .tlp-shortcode-login-form *,
    .tlp-user-info *,
    .tlp-links-list * {
        box-sizing: border-box;
    }

    /* Message Styles */
    .tlp-message {
        margin: 20px 0;
        padding: 15px 20px;
        border-radius: 6px;
        border-left: 4px solid transparent;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        animation: tlp-fade-in 0.4s ease-out;
    }

    .tlp-success {
        background-color: #f0f8f0;
        color: #2a8b32;
        border-left-color: #2a8b32;
    }

    .tlp-error {
        background-color: #fff5f5;
        color: #d32f2f;
        border-left-color: #d32f2f;
    }

    .tlp-notice {
        background-color: #f0f7fd;
        color: #0277bd;
        border-left-color: #0277bd;
    }

    /* Status Badge Styles */
    .tlp-status {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 16px;
        font-size: 0.85em;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .tlp-status-active {
        background-color: rgba(46, 204, 113, 0.15);
        color: #27ae60;
    }

    .tlp-status-inactive {
        background-color: rgba(189, 195, 199, 0.15);
        color: #7f8c8d;
    }

    .tlp-status-expired {
        background-color: rgba(231, 76, 60, 0.15);
        color: #c0392b;
    }

    /* Form Styles */
    .tlp-shortcode-form,
    .tlp-shortcode-login-form {
        max-width: 550px;
        margin: 30px auto;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        padding: 30px;
        border: 1px solid rgba(0, 0, 0, 0.06);
        transition: all 0.2s ease;
    }

    .tlp-form-field {
        margin-bottom: 20px;
    }

    .tlp-form-field label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        font-size: 0.95em;
        color: #444;
    }

    .tlp-form-field label .required {
        color: #e74c3c;
        margin-left: 3px;
    }

    .tlp-form-field input[type="text"],
    .tlp-form-field input[type="email"],
    .tlp-form-field input[type="password"],
    .tlp-form-field select,
    .tlp-form-field textarea {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #ddd;
        border-radius: 6px;
        background-color: #fafafa;
        font-size: 15px;
        transition: all 0.2s ease;
    }

    .tlp-form-field input[type="text"]:focus,
    .tlp-form-field input[type="email"]:focus,
    .tlp-form-field input[type="password"]:focus,
    .tlp-form-field select:focus,
    .tlp-form-field textarea:focus {
        border-color: #4e9bf1;
        background-color: #fff;
        box-shadow: 0 0 0 3px rgba(78, 155, 241, 0.15);
        outline: none;
    }

    .tlp-form-field input[type="text"]::placeholder,
    .tlp-form-field input[type="email"]::placeholder,
    .tlp-form-field input[type="password"]::placeholder,
    .tlp-form-field textarea::placeholder {
        color: #aaa;
    }

    .tlp-form-field select {
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23888' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 12px center;
        background-size: 16px;
        padding-right: 40px;
    }

    .tlp-form-submit {
        margin-top: 25px;
    }

    .tlp-form-submit input[type="submit"],
    .tlp-button {
        display: inline-block;
        padding: 12px 24px;
        background: #4e9bf1;
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
        text-align: center;
        line-height: 1.5;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .tlp-form-submit input[type="submit"]:hover,
    .tlp-button:hover {
        background: #2f80ed;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .tlp-form-submit input[type="submit"]:active,
    .tlp-button:active {
        transform: translateY(0);
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    }

    /* Temporary Login Link URL Display */
    .tlp-link-url {
        background: #f7f9fc;
        border: 1px solid #e1e7ef;
        padding: 15px;
        border-radius: 6px;
        margin: 20px 0;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        animation: tlp-fade-in 0.4s ease-out;
    }

    .tlp-link-input {
        flex: 1;
        min-width: 200px;
        padding: 10px 15px;
        font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
        font-size: 14px;
        border: 1px solid #ddd;
        border-radius: 6px;
        background: #fff;
        color: #333;
    }

    .tlp-copy-link {
        padding: 10px 20px;
        background: #4e9bf1;
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
        transition: all 0.2s ease;
        white-space: nowrap;
    }

    .tlp-copy-link:hover {
        background: #2f80ed;
    }

    /* User Info Display */
    .tlp-user-info {
        background: #fff;
        border: 1px solid #e1e7ef;
        border-radius: 8px;
        padding: 25px;
        margin: 25px 0;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
    }

    .tlp-info-heading {
        margin-top: 0;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid #f0f0f0;
        font-size: 18px;
        color: #333;
    }

    .tlp-info-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .tlp-info-list li {
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 1px solid #f5f5f5;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 10px;
    }

    .tlp-info-list li:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: none;
    }

    .tlp-info-list strong {
        display: inline-block;
        min-width: 150px;
        font-weight: 600;
    }

    .tlp-remaining-time {
        font-size: 0.9em;
        color: #4e9bf1;
        margin-left: 5px;
    }

    /* Temporary Links List Table */
    .tlp-links-list {
        margin: 30px 0;
    }

    .tlp-links-table {
        width: 100%;
        border-collapse: collapse;
        border-spacing: 0;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        background: #fff;
    }

    .tlp-links-table th,
    .tlp-links-table td {
        padding: 15px;
        text-align: left;
        border-bottom: 1px solid #f0f0f0;
    }

    .tlp-links-table th {
        background: #f8fafc;
        font-weight: 600;
        color: #333;
        position: sticky;
        top: 0;
        z-index: 1;
    }

    .tlp-links-table thead tr {
        border-bottom: 2px solid #e1e7ef;
    }

    .tlp-links-table tbody tr:hover {
        background-color: #f8fafc;
    }

    .tlp-links-table tbody tr:last-child td {
        border-bottom: none;
    }

    .tlp-links-table .button {
        margin-right: 5px;
    }

    .tlp-links-table .button-small {
        padding: 6px 12px;
        font-size: 12px;
        border-radius: 4px;
        background: #f5f7fa;
        border: 1px solid #e1e7ef;
        color: #555;
        text-decoration: none;
        transition: all 0.2s ease;
        display: inline-block;
        margin-right: 5px;
    }

    .tlp-links-table .button-small:hover {
        background: #e8edf5;
        color: #333;
    }

    /* Responsive styles */
    @media screen and (max-width: 768px) {
        .tlp-shortcode-form,
        .tlp-shortcode-login-form {
            padding: 20px;
        }

        .tlp-link-url {
            flex-direction: column;
            align-items: stretch;
        }

        .tlp-copy-link {
            width: 100%;
        }

        .tlp-info-list strong {
            min-width: 100%;
            margin-bottom: 5px;
        }

        .tlp-links-table thead {
            display: none;
        }

        .tlp-links-table tbody tr {
            display: block;
            margin-bottom: 15px;
            border: 1px solid #e1e7ef;
            border-radius: 6px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .tlp-links-table tbody td {
            display: block;
            text-align: right;
            padding: 12px 15px;
            position: relative;
            padding-left: 35%;
            min-height: 50px;
        }

        .tlp-links-table td:before {
            content: attr(data-label);
            position: absolute;
            left: 15px;
            width: 30%;
            font-weight: 600;
            text-align: left;
        }
    }

    /* Animations */
    @keyframes tlp-fade-in {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Accessibility improvements */
    .tlp-form-field input[type="text"]:focus,
    .tlp-form-field input[type="email"]:focus,
    .tlp-form-field input[type="password"]:focus,
    .tlp-form-field select:focus,
    .tlp-form-field textarea:focus,
    .tlp-copy-link:focus,
    .tlp-button:focus,
    .tlp-form-submit input[type="submit"]:focus {
        outline: 2px solid #4e9bf1;
        outline-offset: 1px;
    }
    </style>
        <?php
        return ob_get_clean();
    }    
    
    

    /**
     * Register all shortcodes.
     *
     * @since    1.0.0
     */
    public function register_shortcodes() {
        add_shortcode('temporary_login_link', array($this, 'temporary_login_link_shortcode'));
        add_shortcode('temporary_login_form', array($this, 'temporary_login_form_shortcode'));
        add_shortcode('temporary_login_info', array($this, 'temporary_login_info_shortcode'));
        add_shortcode('temporary_access_content', array($this, 'temporary_access_content_shortcode'));
        add_shortcode('temporary_user_expiry', array($this, 'temporary_user_expiry_shortcode'));
        add_shortcode('temporary_links_list', array($this, 'temporary_links_list_shortcode'));
    }

    /**
     * Shortcode to display a temporary login link.
     *
     * Usage: [temporary_login_link email="user@example.com" role="editor" expiry="7 days"]
     *
     * @since    1.0.0
     * @param    array     $atts    Shortcode attributes.
     * @return   string             The shortcode output.
     */
    public function temporary_login_link_shortcode($atts) {
        // Check user capabilities
        if (!current_user_can('manage_temporary_logins')) {
            return '<div class="tlp-error">' . esc_html__('You do not have permission to create temporary login links.', 'temporary-login-links-premium') . '</div>';
        }
        
        // Extract and validate attributes
        $atts = shortcode_atts(array(
            'email' => '',
            'role' => '',
            'first_name' => '',
            'last_name' => '',
            'expiry' => '7 days',
            'max_accesses' => 0,
            'redirect_to' => '',
            'ip_restriction' => '',
            'button_text' => esc_html__('Create Login Link', 'temporary-login-links-premium'),
            'success_text' => esc_html__('Login link created successfully. The link has been sent to the email address.', 'temporary-login-links-premium'),
            'show_form' => 'true',
            'class' => '',
        ), $atts, 'temporary_login_link');
        
        // Process form submission
        $link_url = '';
        $message = '';
        $message_type = '';
        
        if (isset($_POST['tlp_create_link_submit']) && wp_verify_nonce($_POST['tlp_shortcode_nonce'], 'tlp_create_link_shortcode')) {
            $email = isset($_POST['tlp_email']) ? sanitize_email($_POST['tlp_email']) : '';
            $role = isset($_POST['tlp_role']) ? sanitize_text_field($_POST['tlp_role']) : $atts['role'];
            $first_name = isset($_POST['tlp_first_name']) ? sanitize_text_field($_POST['tlp_first_name']) : $atts['first_name'];
            $last_name = isset($_POST['tlp_last_name']) ? sanitize_text_field($_POST['tlp_last_name']) : $atts['last_name'];
            $expiry = isset($_POST['tlp_expiry']) ? sanitize_text_field($_POST['tlp_expiry']) : $atts['expiry'];
            
            // Prepare data for link creation
            $link_data = array(
                'user_email' => $email,
                'role' => $role,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'expiry' => $expiry,
                'max_accesses' => intval($atts['max_accesses']),
                'redirect_to' => esc_url_raw($atts['redirect_to']),
                'ip_restriction' => sanitize_text_field($atts['ip_restriction']),
            );
            
            // Create the link
            $result = $this->links->create_link($link_data);
            
            if (is_wp_error($result)) {
                $message = $result->get_error_message();
                $message_type = 'error';
            } else {
                $link_url = $result['url'];
                $message = $atts['success_text'];
                $message_type = 'success';
            }
        }
        
        // Start output buffering
        ob_start();
        
        // Include the inline styles
        echo $this->get_inline_styles();        
        
        // Display message if any
        if (!empty($message)) {
            $message_class = $message_type === 'error' ? 'tlp-error' : 'tlp-success';
            echo '<div class="tlp-message ' . esc_attr($message_class) . '">' . esc_html($message) . '</div>';
        }
        
        // Display link URL if available
        if (!empty($link_url)) {
            echo '<div class="tlp-link-url">';
            echo '<strong>' . esc_html__('Login Link:', 'temporary-login-links-premium') . '</strong> ';
            echo '<input type="text" value="' . esc_url($link_url) . '" readonly onclick="this.select();" class="tlp-link-input">';
            echo '<button type="button" class="tlp-copy-link button" data-clipboard-text="' . esc_url($link_url) . '">' . esc_html__('Copy', 'temporary-login-links-premium') . '</button>';
            echo '</div>';
            
            // Add copy functionality
            echo '<script>
                document.addEventListener("DOMContentLoaded", function() {
                    var copyButton = document.querySelector(".tlp-copy-link");
                    if (copyButton) {
                        copyButton.addEventListener("click", function() {
                            var linkInput = document.querySelector(".tlp-link-input");
                            linkInput.select();
                            document.execCommand("copy");
                            this.textContent = "' . esc_js(esc_html__('Copied!', 'temporary-login-links-premium')) . '";
                            setTimeout(function() {
                                copyButton.textContent = "' . esc_js(esc_html__('Copy', 'temporary-login-links-premium')) . '";
                            }, 2000);
                        });
                    }
                });
            </script>';
        }
        
        // Display form if enabled
        if ($atts['show_form'] === 'true' && empty($link_url)) {
            // Get available roles
            $roles = $this->get_available_roles();
            
            // Get default settings
            $settings = get_option('temporary_login_links_premium_settings', array());
            $default_role = !empty($atts['role']) ? $atts['role'] : (isset($settings['default_role']) ? $settings['default_role'] : 'editor');
            
            // Get expiry options
            $expiry_options = array(
                '1 hour' => esc_html__('1 Hour', 'temporary-login-links-premium'),
                '3 hours' => esc_html__('3 Hours', 'temporary-login-links-premium'),
                '6 hours' => esc_html__('6 Hours', 'temporary-login-links-premium'),
                '12 hours' => esc_html__('12 Hours', 'temporary-login-links-premium'),
                '1 day' => esc_html__('1 Day', 'temporary-login-links-premium'),
                '3 days' => esc_html__('3 Days', 'temporary-login-links-premium'),
                '7 days' => esc_html__('7 Days', 'temporary-login-links-premium'),
                '14 days' => esc_html__('14 Days', 'temporary-login-links-premium'),
                '1 month' => esc_html__('1 Month', 'temporary-login-links-premium'),
                '3 months' => esc_html__('3 Months', 'temporary-login-links-premium'),
                '6 months' => esc_html__('6 Months', 'temporary-login-links-premium'),
                '1 year' => esc_html__('1 Year', 'temporary-login-links-premium'),
            );
            
            // Default expiry
            $default_expiry = !empty($atts['expiry']) ? $atts['expiry'] : (isset($settings['link_expiry_default']) ? $settings['link_expiry_default'] : '7 days');
            
            // Form output
            echo '<div class="tlp-shortcode-form ' . esc_attr($atts['class']) . '">';
            echo '<form method="post" action="">';
            wp_nonce_field('tlp_create_link_shortcode', 'tlp_shortcode_nonce');
            
            // Email field
            echo '<div class="tlp-form-field">';
            echo '<label for="tlp_email">' . esc_html__('Email Address', 'temporary-login-links-premium') . ' <span class="required">*</span></label>';
            echo '<input type="email" name="tlp_email" id="tlp_email" value="' . esc_attr($atts['email']) . '" required>';
            echo '</div>';
            
            // First Name field
            echo '<div class="tlp-form-field">';
            echo '<label for="tlp_first_name">' . esc_html__('First Name', 'temporary-login-links-premium') . '</label>';
            echo '<input type="text" name="tlp_first_name" id="tlp_first_name" value="' . esc_attr($atts['first_name']) . '">';
            echo '</div>';
            
            // Last Name field
            echo '<div class="tlp-form-field">';
            echo '<label for="tlp_last_name">' . esc_html__('Last Name', 'temporary-login-links-premium') . '</label>';
            echo '<input type="text" name="tlp_last_name" id="tlp_last_name" value="' . esc_attr($atts['last_name']) . '">';
            echo '</div>';
            
            // Role field
            echo '<div class="tlp-form-field">';
            echo '<label for="tlp_role">' . esc_html__('User Role', 'temporary-login-links-premium') . ' <span class="required">*</span></label>';
            echo '<select name="tlp_role" id="tlp_role" required>';
            foreach ($roles as $role_key => $role_name) {
                echo '<option value="' . esc_attr($role_key) . '" ' . selected($default_role, $role_key, false) . '>' . esc_html($role_name) . '</option>';
            }
            echo '</select>';
            echo '</div>';
            
            // Expiry field
            echo '<div class="tlp-form-field">';
            echo '<label for="tlp_expiry">' . esc_html__('Expiration', 'temporary-login-links-premium') . ' <span class="required">*</span></label>';
            echo '<select name="tlp_expiry" id="tlp_expiry" required>';
            foreach ($expiry_options as $expiry_key => $expiry_name) {
                echo '<option value="' . esc_attr($expiry_key) . '" ' . selected($default_expiry, $expiry_key, false) . '>' . esc_html($expiry_name) . '</option>';
            }
            echo '</select>';
            echo '</div>';
            
            // Submit button
            echo '<div class="tlp-form-field tlp-form-submit">';
            echo '<input type="submit" name="tlp_create_link_submit" value="' . esc_attr($atts['button_text']) . '" class="button button-primary">';
            echo '</div>';
            
            echo '</form>';
            echo '</div>';
        }
        
        // Return the output
        return ob_get_clean();
    }

    /**
     * Shortcode to display a temporary login form.
     *
     * Usage: [temporary_login_form redirect="/dashboard/" message="Welcome!"]
     *
     * @since    1.0.0
     * @param    array     $atts    Shortcode attributes.
     * @return   string             The shortcode output.
     */
    public function temporary_login_form_shortcode($atts) {
        // Extract and validate attributes
        $atts = shortcode_atts(array(
            'redirect' => '',
            'message' => esc_html__('Enter your temporary login token to access the site.', 'temporary-login-links-premium'),
            'button_text' => esc_html__('Access Site', 'temporary-login-links-premium'),
            'class' => '',
        ), $atts, 'temporary_login_form');
        
        // Default redirect to current page
        if (empty($atts['redirect'])) {
            $atts['redirect'] = get_permalink();
        }
        
        // Process form submission
        $error = '';
        
        if (isset($_POST['tlp_login_submit']) && wp_verify_nonce($_POST['tlp_login_form_nonce'], 'tlp_login_form_shortcode')) {
            $token = isset($_POST['tlp_token']) ? sanitize_text_field($_POST['tlp_token']) : '';
            
            if (empty($token)) {
                $error = esc_html__('Please enter a login token.', 'temporary-login-links-premium');
            } else {
                // Validate the token
                $result = $this->links->validate_login_token($token);
                
                if (is_wp_error($result)) {
                    $error = $result->get_error_message();
                } else {
                    // Log in the user
                    $user = get_user_by('id', $result['user_id']);
                    
                    if ($user) {
                        // Set auth cookie
                        wp_set_auth_cookie($user->ID, false);
                        
                        // Set current user
                        wp_set_current_user($user->ID);
                        
                        // Update user last login
                        update_user_meta($user->ID, 'tlp_last_login', current_time('mysql'));
                        
                        // Get the redirect URL
                        $redirect_url = !empty($result['redirect_to']) ? $result['redirect_to'] : $atts['redirect'];
                        
                        // Redirect after login
                        wp_redirect($redirect_url);
                        exit;
                    } else {
                        $error = esc_html__('User not found.', 'temporary-login-links-premium');
                    }
                }
            }
        }
        
        // Start output buffering
        ob_start();
        
        // Include the inline styles
        echo $this->get_inline_styles();        
        
        // Display the form
        echo '<div class="tlp-shortcode-login-form ' . esc_attr($atts['class']) . '">';
        
        // Display message
        if (!empty($atts['message'])) {
            echo '<div class="tlp-form-message">' . esc_html($atts['message']) . '</div>';
        }
        
        // Display error if any
        if (!empty($error)) {
            echo '<div class="tlp-error">' . esc_html($error) . '</div>';
        }
        
        // Login form
        echo '<form method="post" action="">';
        wp_nonce_field('tlp_login_form_shortcode', 'tlp_login_form_nonce');
        
        // Token field
        echo '<div class="tlp-form-field">';
        echo '<label for="tlp_token">' . esc_html__('Login Token', 'temporary-login-links-premium') . ' <span class="required">*</span></label>';
        echo '<input type="text" name="tlp_token" id="tlp_token" required>';
        echo '</div>';
        
        // Submit button
        echo '<div class="tlp-form-field tlp-form-submit">';
        echo '<input type="submit" name="tlp_login_submit" value="' . esc_attr($atts['button_text']) . '" class="button button-primary">';
        echo '</div>';
        
        echo '</form>';
        echo '</div>';
        
        // Return the output
        return ob_get_clean();
    }

    /**
     * Shortcode to display temporary login information for current user.
     *
     * Usage: [temporary_login_info]
     *
     * @since    1.0.0
     * @param    array     $atts    Shortcode attributes.
     * @return   string             The shortcode output.
     */
    public function temporary_login_info_shortcode($atts) {
        // Extract and validate attributes
        $atts = shortcode_atts(array(
            'show_expiry' => 'true',
            'show_role' => 'true',
            'show_created' => 'true',
            'show_accesses' => 'true',
            'class' => '',
        ), $atts, 'temporary_login_info');
        
        // Get current user
        $current_user = wp_get_current_user();
        
        // Check if user is logged in
        if (!$current_user->exists()) {
            return '<div class="tlp-error">' . esc_html__('You need to be logged in to view this information.', 'temporary-login-links-premium') . '</div>';
        }
        
        // Check if user is a temporary user
        $link_id = get_user_meta($current_user->ID, 'temporary_login_links_premium_user', true);
        
        if (empty($link_id)) {
            return '<div class="tlp-notice">' . esc_html__('You are not accessing the site via a temporary login link.', 'temporary-login-links-premium') . '</div>';
        }
        
        // Get link details
        $link = $this->links->get_link($link_id);
        
        if (!$link) {
            return '<div class="tlp-error">' . esc_html__('Temporary login information not found.', 'temporary-login-links-premium') . '</div>';
        }
        
        // Start output buffering
        ob_start();
        
        // Include the inline styles
        echo $this->get_inline_styles();
        
        echo '<div class="tlp-user-info ' . esc_attr($atts['class']) . '">';
        
        // Add heading
        echo '<h4 class="tlp-info-heading">' . esc_html__('Temporary Access Information', 'temporary-login-links-premium') . '</h4>';
        
        echo '<ul class="tlp-info-list">';
        
        // Show expiry
        if ($atts['show_expiry'] === 'true') {
            $expiry_date = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($link['expiry']));
            $expiry_status = '';
            
            // Check if expired
            if (strtotime($link['expiry']) < time()) {
                $expiry_status = ' <span class="tlp-status tlp-status-expired">' . esc_html__('Expired', 'temporary-login-links-premium') . '</span>';
            } else {
                // Calculate time remaining
                $time_diff = strtotime($link['expiry']) - time();
                $days_remaining = floor($time_diff / (60 * 60 * 24));
                $hours_remaining = floor(($time_diff % (60 * 60 * 24)) / (60 * 60));
                
                if ($days_remaining > 0) {
                    /* translators: %d days  */
                    $time_remaining = sprintf(_n('%d day', '%d days', $days_remaining, 'temporary-login-links-premium'),
                        $days_remaining
                    );
                    
                    if ($hours_remaining > 0) {
                        /* translators: %d hours  */
                        $time_remaining .= ' ' . sprintf(_n('%d hour', '%d hours', $hours_remaining, 'temporary-login-links-premium'),
                            $hours_remaining
                        );
                    }
                } else {
                    /* translators: %d hours  */
                    $time_remaining = sprintf(_n('%d hour', '%d hours', $hours_remaining, 'temporary-login-links-premium'),
                        $hours_remaining
                    );
                }
                
                /* translators: %s time remaining  */
                $expiry_status = ' <span class="tlp-status tlp-status-active">' . sprintf(esc_html__('(%s remaining)', 'temporary-login-links-premium'), $time_remaining) . '</span>';
            }
            
            echo '<li class="tlp-info-expiry"><strong>' . esc_html__('Access Expires:', 'temporary-login-links-premium') . '</strong> ' . esc_html($expiry_date) . esc_html($expiry_status) . '</li>';
        }
        
        // Show role
        if ($atts['show_role'] === 'true') {
            $role_name = $this->get_role_display_name($link['role']);
            echo '<li class="tlp-info-role"><strong>' . esc_html__('Access Level:', 'temporary-login-links-premium') . '</strong> ' . esc_html($role_name) . '</li>';
        }
        
        // Show creation date
        if ($atts['show_created'] === 'true') {
            $created_date = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($link['created_at']));
            echo '<li class="tlp-info-created"><strong>' . esc_html__('Access Granted:', 'temporary-login-links-premium') . '</strong> ' . esc_html($created_date) . '</li>';
        }
        
        // Show access count
        if ($atts['show_accesses'] === 'true') {
            $max_accesses = $link['max_accesses'] > 0 ? ' / ' . esc_html($link['max_accesses']) : '';
            echo '<li class="tlp-info-accesses"><strong>' . esc_html__('Access Count:', 'temporary-login-links-premium') . '</strong> ' . esc_html($link['access_count']) . esc_html($max_accesses) . '</li>';
        }
        
        echo '</ul>';
        echo '</div>';
        
        // Return the output
        return ob_get_clean();
    }

    /**
     * Shortcode to display content only for temporary users.
     *
     * Usage: [temporary_access_content]This content is only visible to temporary users.[/temporary_access_content]
     *
     * @since    1.0.0
     * @param    array     $atts    Shortcode attributes.
     * @param    string    $content The shortcode content.
     * @return   string             The shortcode output.
     */
    public function temporary_access_content_shortcode($atts, $content = null) {
        // Extract and validate attributes
        $atts = shortcode_atts(array(
            'role' => '',
            'class' => '',
        ), $atts, 'temporary_access_content');
        
        // Get current user
        $current_user = wp_get_current_user();
        
        // Check if user is logged in
        if (!$current_user->exists()) {
            return '';
        }
        
        // Check if user is a temporary user
        $link_id = get_user_meta($current_user->ID, 'temporary_login_links_premium_user', true);
        
        if (empty($link_id)) {
            return '';
        }
        
        // Check role if specified
        if (!empty($atts['role'])) {
            $roles = explode(',', $atts['role']);
            $roles = array_map('trim', $roles);
            
            $user_roles = $current_user->roles;
            $intersection = array_intersect($roles, $user_roles);
            
            if (empty($intersection)) {
                return '';
            }
        }
        
        // Process shortcode content
        $content = do_shortcode($content);
        
        // Add wrapper if class is specified
        if (!empty($atts['class'])) {
            $content = '<div class="' . esc_attr($atts['class']) . '">' . $content . '</div>';
        }
        
        // Get the inline styles and add them to the beginning of the content
        $styles = $this->get_inline_styles();        
        
        return $styles . $content;
    }

    /**
     * Shortcode to display a temporary user's expiry date.
     *
     * Usage: [temporary_user_expiry format="F j, Y" expired_text="Your access has expired."]
     *
     * @since    1.0.0
     * @param    array     $atts    Shortcode attributes.
     * @return   string             The shortcode output.
     */
    public function temporary_user_expiry_shortcode($atts) {
        // Extract and validate attributes
        $atts = shortcode_atts(array(
            'format' => get_option('date_format') . ' ' . get_option('time_format'),
            'expired_text' => esc_html__('Your access has expired.', 'temporary-login-links-premium'),
            'show_remaining' => 'true',
            'class' => '',
        ), $atts, 'temporary_user_expiry');
        
        // Get current user
        $current_user = wp_get_current_user();
        
        // Check if user is logged in
        if (!$current_user->exists()) {
            return '';
        }
        
        // Check if user is a temporary user
        $link_id = get_user_meta($current_user->ID, 'temporary_login_links_premium_user', true);
        
        if (empty($link_id)) {
            return '';
        }
        
        // Get link details
        $link = $this->links->get_link($link_id);
        
        if (!$link) {
            return '';
        }
        
        // Check if expired
        if (strtotime($link['expiry']) < time()) {
            return '<span class="tlp-expiry-date tlp-expired ' . esc_attr($atts['class']) . '">' . esc_html($atts['expired_text']) . '</span>';
        }
        
        // Format the expiry date
        $expiry_date = date_i18n($atts['format'], strtotime($link['expiry']));
        
        // Add remaining time if enabled
        $remaining_text = '';
        
        if ($atts['show_remaining'] === 'true') {
            // Calculate time remaining
            $time_diff = strtotime($link['expiry']) - time();
            $days_remaining = floor($time_diff / (60 * 60 * 24));
            $hours_remaining = floor(($time_diff % (60 * 60 * 24)) / (60 * 60));
            
            if ($days_remaining > 0) {
                /* translators: %d days  */
                $time_remaining = sprintf(_n('%d day', '%d days', $days_remaining, 'temporary-login-links-premium'),
                    $days_remaining
                );
                
                if ($hours_remaining > 0) {
                    /* translators: %d hours  */
                    $time_remaining .= ' ' . sprintf(_n('%d hour', '%d hours', $hours_remaining, 'temporary-login-links-premium'),
                        $hours_remaining
                    );
                }
            } else {
                /* translators: %s hours  */
                $time_remaining = sprintf(_n('%d hour', '%d hours', $hours_remaining, 'temporary-login-links-premium'),
                    $hours_remaining
                );
            }
            
            /* translators: %s time remaining  */
            $remaining_text = ' <span class="tlp-remaining-time">(' . sprintf(esc_html__('%s remaining', 'temporary-login-links-premium'), $time_remaining) . ')</span>';
        }
        
        return '<span class="tlp-expiry-date ' . esc_attr($atts['class']) . '">' . esc_html($expiry_date) . $remaining_text . '</span>';
    }

    /**
     * Shortcode to display a list of active temporary links.
     *
     * Usage: [temporary_links_list limit="10" show_expired="false"]
     *
     * @since    1.0.0
     * @param    array     $atts    Shortcode attributes.
     * @return   string             The shortcode output.
     */
    public function temporary_links_list_shortcode($atts) {
        // Check user capabilities
        if (!current_user_can('manage_temporary_logins')) {
            return '<div class="tlp-error">' . esc_html__('You do not have permission to view temporary login links.', 'temporary-login-links-premium') . '</div>';
        }
        
        // Extract and validate attributes
        $atts = shortcode_atts(array(
            'limit' => 10,
            'show_expired' => 'false',
            'show_inactive' => 'false',
            'orderby' => 'created_at',
            'order' => 'DESC',
            'role' => '',
            'class' => '',
        ), $atts, 'temporary_links_list');
        
        // Determine status based on attributes
        $status = 'active';
        
        if ($atts['show_expired'] === 'true' && $atts['show_inactive'] === 'true') {
            $status = 'all';
        } elseif ($atts['show_expired'] === 'true') {
            $status = 'expired';
        } elseif ($atts['show_inactive'] === 'true') {
            $status = 'inactive';
        }
        
        // Get links
        $links_data = $this->links->get_links(array(
            'per_page' => intval($atts['limit']),
            'page' => 1,
            'status' => $status,
            'role' => sanitize_text_field($atts['role']),
            'orderby' => sanitize_text_field($atts['orderby']),
            'order' => strtoupper(sanitize_text_field($atts['order'])),
        ));
        
        $links = $links_data['items'];
        
        // Start output buffering
        ob_start();
        
        // Include the inline styles
        echo $this->get_inline_styles();        
        
        echo '<div class="tlp-links-list ' . esc_attr($atts['class']) . '">';
        
        if (empty($links)) {
            echo '<div class="tlp-notice">' . esc_html__('No temporary login links found.', 'temporary-login-links-premium') . '</div>';
        } else {
            echo '<table class="tlp-links-table">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>' . esc_html__('Email', 'temporary-login-links-premium') . '</th>';
            echo '<th>' . esc_html__('Role', 'temporary-login-links-premium') . '</th>';
            echo '<th>' . esc_html__('Expiry', 'temporary-login-links-premium') . '</th>';
            echo '<th>' . esc_html__('Status', 'temporary-login-links-premium') . '</th>';
            echo '<th>' . esc_html__('Actions', 'temporary-login-links-premium') . '</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            
            foreach ($links as $link) {
                echo '<tr>';
                
                // Email
                echo '<td>' . esc_html($link['user_email']) . '</td>';
                
                // Role
                echo '<td>' . esc_html($this->get_role_display_name($link['role'])) . '</td>';
                
                // Expiry
                $expiry_date = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($link['expiry']));
                echo '<td>' . esc_html($expiry_date) . '</td>';
                
                // Status
                $status_class = '';
                $status_text = '';
                
                if ($link['is_active'] == 0) {
                    $status_class = 'inactive';
                    $status_text = esc_html__('Inactive', 'temporary-login-links-premium');
                } elseif (strtotime($link['expiry']) < time()) {
                    $status_class = 'expired';
                    $status_text = esc_html__('Expired', 'temporary-login-links-premium');
                } else {
                    $status_class = 'active';
                    $status_text = esc_html__('Active', 'temporary-login-links-premium');
                }
                
                echo '<td><span class="tlp-status tlp-status-' . esc_attr($status_class) . '">' . esc_html($status_text) . '</span></td>';
                
                // Actions
                echo '<td>';
                
                // View link
                $admin_url = admin_url('admin.php?page=temporary-login-links-premium-links&action=view&id=' . $link['id']);
                echo '<a href="' . esc_url($admin_url) . '" class="button button-small">' . esc_html__('View', 'temporary-login-links-premium') . '</a>';
                
                // Copy link button (using data attribute)
                echo ' <button type="button" class="button button-small tlp-copy-link-btn" data-clipboard-text="' . esc_url($this->links->get_login_url($link['link_token'])) . '">' . esc_html__('Copy Link', 'temporary-login-links-premium') . '</button>';
                
                echo '</td>';
                echo '</tr>';
            }
            
            echo '</tbody>';
            echo '</table>';
            
            // Add copy functionality
            echo '<script>
                document.addEventListener("DOMContentLoaded", function() {
                    var copyButtons = document.querySelectorAll(".tlp-copy-link-btn");
                    copyButtons.forEach(function(button) {
                        button.addEventListener("click", function() {
                            var text = this.getAttribute("data-clipboard-text");
                            var tempInput = document.createElement("input");
                            document.body.appendChild(tempInput);
                            tempInput.value = text;
                            tempInput.select();
                            document.execCommand("copy");
                            document.body.removeChild(tempInput);
                            
                            var originalText = this.textContent;
                            this.textContent = "' . esc_js(esc_html__('Copied!', 'temporary-login-links-premium')) . '";
                            
                            var btn = this;
                            setTimeout(function() {
                                btn.textContent = originalText;
                            }, 2000);
                        });
                    });
                });
            </script>';
        }
        
        echo '</div>';
        
        // Return the output
        return ob_get_clean();
    }

    /**
     * Get available user roles.
     *
     * @since    1.0.0
     * @return   array    The available roles.
     */
    private function get_available_roles() {
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
}
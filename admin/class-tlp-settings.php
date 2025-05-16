<?php
/**
 * Plugin settings functionality.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Temporary_Login_Links_Premium
 * @subpackage Temporary_Login_Links_Premium/admin
 */

/**
 * Plugin settings functionality.
 *
 * Handles plugin settings management, registration, and display.
 *
 * @package    Temporary_Login_Links_Premium
 * @subpackage Temporary_Login_Links_Premium/admin
 * @author     Your Name <email@example.com>
 */
class TLP_Settings {

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
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param    string    $plugin_name    The name of this plugin.
     * @param    string    $version        The version of this plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the settings.
     *
     * @since    1.0.0
     */
    public function register_settings() {
        // Register main settings
        register_setting(
            'temporary_login_links_premium_settings',
            'temporary_login_links_premium_settings',
            array($this, 'validate_settings')
        );
        
        // Register branding settings
        register_setting(
            'temporary_login_links_premium_branding',
            'temporary_login_links_premium_branding',
            array($this, 'validate_branding_settings')
        );
        
        // Add settings sections
        add_settings_section(
            'tlp_general_settings',
            __('General Settings', 'temporary-login-links-premium'),
            array($this, 'render_general_settings_section'),
            'temporary_login_links_premium_settings'
        );
        
        add_settings_section(
            'tlp_security_settings',
            __('Security Settings', 'temporary-login-links-premium'),
            array($this, 'render_security_settings_section'),
            'temporary_login_links_premium_settings'
        );
        
        add_settings_section(
            'tlp_notification_settings',
            __('Notification Settings', 'temporary-login-links-premium'),
            array($this, 'render_notification_settings_section'),
            'temporary_login_links_premium_settings'
        );
        
        add_settings_section(
            'tlp_advanced_settings',
            __('Advanced Settings', 'temporary-login-links-premium'),
            array($this, 'render_advanced_settings_section'),
            'temporary_login_links_premium_settings'
        );
        
        // Add settings fields
        
        // General settings
        add_settings_field(
            'link_expiry_default',
            __('Default Expiration', 'temporary-login-links-premium'),
            array($this, 'render_link_expiry_default_field'),
            'temporary_login_links_premium_settings',
            'tlp_general_settings'
        );
        
        add_settings_field(
            'default_role',
            __('Default Role', 'temporary-login-links-premium'),
            array($this, 'render_default_role_field'),
            'temporary_login_links_premium_settings',
            'tlp_general_settings'
        );
        
        add_settings_field(
            'default_redirect',
            __('Default Redirect URL', 'temporary-login-links-premium'),
            array($this, 'render_default_redirect_field'),
            'temporary_login_links_premium_settings',
            'tlp_general_settings'
        );
        
        // Security settings
        add_settings_field(
            'max_failed_attempts',
            __('Max Failed Attempts', 'temporary-login-links-premium'),
            array($this, 'render_max_failed_attempts_field'),
            'temporary_login_links_premium_settings',
            'tlp_security_settings'
        );
        
        add_settings_field(
            'lockout_time',
            __('Lockout Time (minutes)', 'temporary-login-links-premium'),
            array($this, 'render_lockout_time_field'),
            'temporary_login_links_premium_settings',
            'tlp_security_settings'
        );
        
        add_settings_field(
            'security_notifications',
            __('Security Notifications', 'temporary-login-links-premium'),
            array($this, 'render_security_notifications_field'),
            'temporary_login_links_premium_settings',
            'tlp_security_settings'
        );
        
        // Notification settings
        add_settings_field(
            'email_notifications',
            __('Email Notifications', 'temporary-login-links-premium'),
            array($this, 'render_email_notifications_field'),
            'temporary_login_links_premium_settings',
            'tlp_notification_settings'
        );
        
        add_settings_field(
            'admin_notification',
            __('Admin Notifications', 'temporary-login-links-premium'),
            array($this, 'render_admin_notification_field'),
            'temporary_login_links_premium_settings',
            'tlp_notification_settings'
        );
        
        // Advanced settings
        add_settings_field(
            'cleanup_expired_links',
            __('Auto Cleanup Links', 'temporary-login-links-premium'),
            array($this, 'render_cleanup_expired_links_field'),
            'temporary_login_links_premium_settings',
            'tlp_advanced_settings'
        );
        
        add_settings_field(
            'keep_expired_links_days',
            __('Keep Expired Links (days)', 'temporary-login-links-premium'),
            array($this, 'render_keep_expired_links_days_field'),
            'temporary_login_links_premium_settings',
            'tlp_advanced_settings'
        );
        
        add_settings_field(
            'delete_data_on_uninstall',
            __('Delete Data on Uninstall', 'temporary-login-links-premium'),
            array($this, 'render_delete_data_on_uninstall_field'),
            'temporary_login_links_premium_settings',
            'tlp_advanced_settings'
        );
    }

    /**
     * Display the settings page.
     *
     * @since    1.0.0
     */
    public function display_settings_page() {
        // Check capability
        $security = new TLP_Security();
        if (!$security->current_user_can_manage()) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'temporary-login-links-premium'));
        }
        
        // Get the active tab (default to general)
        $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';
        
        // Render the settings page
        include plugin_dir_path(__FILE__) . 'partials/settings-display.php';
    }

    /**
     * Save branding settings.
     *
     * @since    1.0.0
     * @param    array    $data    The form data.
     * @return   string|WP_Error   Success message or error.
     */
    
    public function save_branding_settings($data) {
        // Define the fields to process
        $fields = array(
            'enable_branding' => array(
                'type' => 'checkbox',
                'required' => false,
                'label' => __('Enable Branding', 'temporary-login-links-premium')
            ),
            'login_logo' => array(
                'type' => 'url',
                'required' => false,
                'label' => __('Login Logo', 'temporary-login-links-premium')
            ),
            'login_background_color' => array(
                'type' => 'text',
                'required' => false,
                'label' => __('Login Background Color', 'temporary-login-links-premium')
            ),
            'login_form_background' => array(
                'type' => 'text',
                'required' => false,
                'label' => __('Login Form Background', 'temporary-login-links-premium')
            ),
            'login_form_text_color' => array(
                'type' => 'text',
                'required' => false,
                'label' => __('Login Form Text Color', 'temporary-login-links-premium')
            ),
            'login_button_color' => array(
                'type' => 'text',
                'required' => false,
                'label' => __('Login Button Color', 'temporary-login-links-premium')
            ),
            'login_button_text_color' => array(
                'type' => 'text',
                'required' => false,
                'label' => __('Login Button Text Color', 'temporary-login-links-premium')
            ),
            'login_custom_css' => array(
                'type' => 'textarea',
                'required' => false,
                'label' => __('Login Custom CSS', 'temporary-login-links-premium')
            ),
            'login_welcome_text' => array(
                'type' => 'textarea',
                'required' => false,
                'label' => __('Login Welcome Text', 'temporary-login-links-premium')
            ),
            'company_name' => array(
                'type' => 'text',
                'required' => false,
                'label' => __('Company Name', 'temporary-login-links-premium')
            ),
            'email_branding' => array(
                'type' => 'checkbox',
                'required' => false,
                'label' => __('Email Branding', 'temporary-login-links-premium')
            ),
        );
        
        // Get security class
        $security = new TLP_Security();
        
        // Sanitize and validate
        $sanitized = $security->sanitize_form_inputs($data, $fields);
        
        if (is_wp_error($sanitized)) {
            return $sanitized;
        }
        
        // Update settings
        update_option('temporary_login_links_premium_branding', $sanitized);
        
        return __('Branding settings saved successfully.', 'temporary-login-links-premium');
    }

    /**
     * Validate settings.
     *
     * @since    1.0.0
     * @param    array    $input    The settings input.
     * @return   array              The validated settings.
     */
    public function validate_settings($input) {
        $new_input = array();
        
        // Default settings for comparison
        $defaults = array(
            'delete_data_on_uninstall' => 0,
            'link_expiry_default'      => '7 days',
            'email_notifications'      => 1,
            'admin_notification'       => 0,
            'track_login_activity'     => 1,
            'default_redirect'         => admin_url(),
            'default_role'             => 'editor',
            'cleanup_expired_links'    => 1,
            'keep_expired_links_days'  => 30,
            'max_failed_attempts'      => 5,
            'lockout_time'             => 30,
            'security_notifications'   => 1,
        );
        
        // Sanitize each setting field
        if (isset($input['delete_data_on_uninstall'])) {
            $new_input['delete_data_on_uninstall'] = (int) $input['delete_data_on_uninstall'];
        } else {
            $new_input['delete_data_on_uninstall'] = 0;
        }
        
        if (isset($input['link_expiry_default'])) {
            $new_input['link_expiry_default'] = sanitize_text_field($input['link_expiry_default']);
        } else {
            $new_input['link_expiry_default'] = $defaults['link_expiry_default'];
        }
        
        if (isset($input['email_notifications'])) {
            $new_input['email_notifications'] = (int) $input['email_notifications'];
        } else {
            $new_input['email_notifications'] = 0;
        }
        
        if (isset($input['admin_notification'])) {
            $new_input['admin_notification'] = (int) $input['admin_notification'];
        } else {
            $new_input['admin_notification'] = 0;
        }
        
        if (isset($input['track_login_activity'])) {
            $new_input['track_login_activity'] = (int) $input['track_login_activity'];
        } else {
            $new_input['track_login_activity'] = 0;
        }
        
        if (isset($input['default_redirect'])) {
            $new_input['default_redirect'] = esc_url_raw($input['default_redirect']);
        } else {
            $new_input['default_redirect'] = $defaults['default_redirect'];
        }
        
        if (isset($input['default_role'])) {
            $new_input['default_role'] = sanitize_text_field($input['default_role']);
            
            // Make sure the role exists
            if (!get_role($new_input['default_role'])) {
                $new_input['default_role'] = $defaults['default_role'];
            }
        } else {
            $new_input['default_role'] = $defaults['default_role'];
        }
        
        if (isset($input['cleanup_expired_links'])) {
            $new_input['cleanup_expired_links'] = (int) $input['cleanup_expired_links'];
        } else {
            $new_input['cleanup_expired_links'] = 0;
        }
        
        if (isset($input['keep_expired_links_days'])) {
            $new_input['keep_expired_links_days'] = (int) $input['keep_expired_links_days'];
            
            // Make sure the value is at least 1
            if ($new_input['keep_expired_links_days'] < 1) {
                $new_input['keep_expired_links_days'] = 1;
            }
        } else {
            $new_input['keep_expired_links_days'] = $defaults['keep_expired_links_days'];
        }
        
        if (isset($input['max_failed_attempts'])) {
            $new_input['max_failed_attempts'] = (int) $input['max_failed_attempts'];
            
            // Make sure the value is at least 1
            if ($new_input['max_failed_attempts'] < 1) {
                $new_input['max_failed_attempts'] = 1;
            }
        } else {
            $new_input['max_failed_attempts'] = $defaults['max_failed_attempts'];
        }
        
        if (isset($input['lockout_time'])) {
            $new_input['lockout_time'] = (int) $input['lockout_time'];
            
            // Make sure the value is at least 1
            if ($new_input['lockout_time'] < 1) {
                $new_input['lockout_time'] = 1;
            }
        } else {
            $new_input['lockout_time'] = $defaults['lockout_time'];
        }
        
        if (isset($input['security_notifications'])) {
            $new_input['security_notifications'] = (int) $input['security_notifications'];
        } else {
            $new_input['security_notifications'] = 0;
        }
        
        return $new_input;
    }

    /**
     * Validate branding settings.
     *
     * @since    1.0.0
     * @param    array    $input    The settings input.
     * @return   array              The validated settings.
     */
    public function validate_branding_settings($input) {
        $new_input = array();
        
        // Default settings for comparison
        $defaults = array(
            'enable_branding'            => 1,
            'login_logo'                 => '',
            'login_background_color'     => '#f1f1f1',
            'login_form_background'      => '#ffffff',
            'login_form_text_color'      => '#333333',
            'login_button_color'         => '#0085ba',
            'login_button_text_color'    => '#ffffff',
            'login_custom_css'           => '',
            'login_welcome_text'         => __('Welcome! You have been granted temporary access to this site.', 'temporary-login-links-premium'),
            'company_name'               => get_bloginfo('name'),
            'email_branding'             => 1,
        );
        
        // Sanitize each setting field
        if (isset($input['enable_branding'])) {
            $new_input['enable_branding'] = (int) $input['enable_branding'];
        } else {
            $new_input['enable_branding'] = 0;
        }
        
        if (isset($input['login_logo'])) {
            $new_input['login_logo'] = esc_url_raw($input['login_logo']);
        } else {
            $new_input['login_logo'] = '';
        }
        
        if (isset($input['login_background_color'])) {
            $new_input['login_background_color'] = sanitize_hex_color($input['login_background_color']);
        } else {
            $new_input['login_background_color'] = $defaults['login_background_color'];
        }
        
        if (isset($input['login_form_background'])) {
            $new_input['login_form_background'] = sanitize_hex_color($input['login_form_background']);
        } else {
            $new_input['login_form_background'] = $defaults['login_form_background'];
        }
        
        if (isset($input['login_form_text_color'])) {
            $new_input['login_form_text_color'] = sanitize_hex_color($input['login_form_text_color']);
        } else {
            $new_input['login_form_text_color'] = $defaults['login_form_text_color'];
        }
        
        if (isset($input['login_button_color'])) {
            $new_input['login_button_color'] = sanitize_hex_color($input['login_button_color']);
        } else {
            $new_input['login_button_color'] = $defaults['login_button_color'];
        }
        
        if (isset($input['login_button_text_color'])) {
            $new_input['login_button_text_color'] = sanitize_hex_color($input['login_button_text_color']);
        } else {
            $new_input['login_button_text_color'] = $defaults['login_button_text_color'];
        }
        
        if (isset($input['login_custom_css'])) {
            $new_input['login_custom_css'] = sanitize_textarea_field($input['login_custom_css']);
        } else {
            $new_input['login_custom_css'] = '';
        }
        
        if (isset($input['login_welcome_text'])) {
            $new_input['login_welcome_text'] = wp_kses_post($input['login_welcome_text']);
        } else {
            $new_input['login_welcome_text'] = $defaults['login_welcome_text'];
        }
        
        if (isset($input['company_name'])) {
            $new_input['company_name'] = sanitize_text_field($input['company_name']);
        } else {
            $new_input['company_name'] = $defaults['company_name'];
        }
        
        if (isset($input['email_branding'])) {
            $new_input['email_branding'] = (int) $input['email_branding'];
        } else {
            $new_input['email_branding'] = 0;
        }
        
        return $new_input;
    }

    /**
     * Render general settings section description.
     *
     * @since    1.0.0
     */
    public function render_general_settings_section() {
        echo '<p>' . esc_html__('Configure the default settings for temporary login links.', 'temporary-login-links-premium') . '</p>';
    }

    /**
     * Render security settings section description.
     *
     * @since    1.0.0
     */
    public function render_security_settings_section() {
        echo '<p>' . esc_html__('Configure security options for temporary login links.', 'temporary-login-links-premium') . '</p>';
    }

    /**
     * Render notification settings section description.
     *
     * @since    1.0.0
     */
    public function render_notification_settings_section() {
        echo '<p>' . esc_html__('Configure notification settings for temporary login links.', 'temporary-login-links-premium') . '</p>';
    }

    /**
     * Render advanced settings section description.
     *
     * @since    1.0.0
     */
    public function render_advanced_settings_section() {
        echo '<p>' . esc_html__('Configure advanced settings for temporary login links.', 'temporary-login-links-premium') . '</p>';
    }

    /**
     * Render link expiry default field.
     *
     * @since    1.0.0
     */
    public function render_link_expiry_default_field() {
        $options = get_option('temporary_login_links_premium_settings');
        $value = isset($options['link_expiry_default']) ? $options['link_expiry_default'] : '7 days';
        
        $expiry_options = array(
            '1 hour'    => __('1 Hour', 'temporary-login-links-premium'),
            '3 hours'   => __('3 Hours', 'temporary-login-links-premium'),
            '6 hours'   => __('6 Hours', 'temporary-login-links-premium'),
            '12 hours'  => __('12 Hours', 'temporary-login-links-premium'),
            '1 day'     => __('1 Day', 'temporary-login-links-premium'),
            '3 days'    => __('3 Days', 'temporary-login-links-premium'),
            '7 days'    => __('7 Days', 'temporary-login-links-premium'),
            '14 days'   => __('14 Days', 'temporary-login-links-premium'),
            '1 month'   => __('1 Month', 'temporary-login-links-premium'),
            '3 months'  => __('3 Months', 'temporary-login-links-premium'),
            '6 months'  => __('6 Months', 'temporary-login-links-premium'),
            '1 year'    => __('1 Year', 'temporary-login-links-premium'),
        );
        
        echo '<select name="temporary_login_links_premium_settings[link_expiry_default]" id="link_expiry_default">';
        
        foreach ($expiry_options as $key => $label) {
            echo '<option value="' . esc_attr($key) . '" ' . selected($value, $key, false) . '>' . esc_html($label) . '</option>';
        }
        
        echo '</select>';
        echo '<p class="description">' . esc_html__('The default expiration time for new login links.', 'temporary-login-links-premium') . '</p>';
    }

    /**
     * Render default role field.
     *
     * @since    1.0.0
     */
    public function render_default_role_field() {
        $options = get_option('temporary_login_links_premium_settings');
        $value = isset($options['default_role']) ? $options['default_role'] : 'editor';
        
        // Get available roles
        $roles = $this->get_available_roles();
        
        echo '<select name="temporary_login_links_premium_settings[default_role]" id="default_role">';
        
        foreach ($roles as $role_key => $role_name) {
            echo '<option value="' . esc_attr($role_key) . '" ' . selected($value, $role_key, false) . '>' . esc_html($role_name) . '</option>';
        }
        
        echo '</select>';
        echo '<p class="description">' . esc_html__('The default role assigned to new temporary users.', 'temporary-login-links-premium') . '</p>';
    }

    /**
     * Render default redirect field.
     *
     * @since    1.0.0
     */
    public function render_default_redirect_field() {
        $options = get_option('temporary_login_links_premium_settings');
        $value = isset($options['default_redirect']) ? $options['default_redirect'] : admin_url();
        
        echo '<input type="url" name="temporary_login_links_premium_settings[default_redirect]" id="default_redirect" value="' . esc_url($value) . '" class="regular-text">';
        echo '<p class="description">' . esc_html__('The default URL to redirect users to after logging in.', 'temporary-login-links-premium') . '</p>';
    }

    /**
     * Render max failed attempts field.
     *
     * @since    1.0.0
     */
    public function render_max_failed_attempts_field() {
        $options = get_option('temporary_login_links_premium_settings');
        $value = isset($options['max_failed_attempts']) ? intval($options['max_failed_attempts']) : 5;
        
        echo '<input type="number" name="temporary_login_links_premium_settings[max_failed_attempts]" id="max_failed_attempts" value="' . esc_attr($value) . '" class="small-text" min="1">';
        echo '<p class="description">' . esc_html__('Maximum failed login attempts before blocking the IP address.', 'temporary-login-links-premium') . '</p>';
    }

    /**
     * Render lockout time field.
     *
     * @since    1.0.0
     */
    public function render_lockout_time_field() {
        $options = get_option('temporary_login_links_premium_settings');
        $value = isset($options['lockout_time']) ? intval($options['lockout_time']) : 30;
        
        echo '<input type="number" name="temporary_login_links_premium_settings[lockout_time]" id="lockout_time" value="' . esc_attr($value) . '" class="small-text" min="1">';
        echo '<p class="description">' . esc_html__('Time in minutes to block an IP address after too many failed attempts.', 'temporary-login-links-premium') . '</p>';
    }

    /**
     * Render security notifications field.
     *
     * @since    1.0.0
     */
    public function render_security_notifications_field() {
        $options = get_option('temporary_login_links_premium_settings');
        $value = isset($options['security_notifications']) ? (int) $options['security_notifications'] : 1;
        
        echo '<label>';
        echo '<input type="checkbox" name="temporary_login_links_premium_settings[security_notifications]" value="1" ' . checked(1, $value, false) . '>';
        echo esc_html__('Send email notifications for suspicious login activity', 'temporary-login-links-premium');
        echo '</label>';
        echo '<p class="description">' . esc_html__('Receive email notifications when suspicious activity is detected, such as multiple failed login attempts.', 'temporary-login-links-premium') . '</p>';
    }

    /**
     * Render email notifications field.
     *
     * @since    1.0.0
     */
    public function render_email_notifications_field() {
        $options = get_option('temporary_login_links_premium_settings');
        $value = isset($options['email_notifications']) ? (int) $options['email_notifications'] : 1;
        
        echo '<label>';
        echo '<input type="checkbox" name="temporary_login_links_premium_settings[email_notifications]" value="1" ' . checked(1, $value, false) . '>';
        echo esc_html__('Send email notifications to users when creating temporary login links', 'temporary-login-links-premium');
        echo '</label>';
        echo '<p class="description">' . esc_html__('Enable to automatically send email notifications to users when a temporary login link is created for them.', 'temporary-login-links-premium') . '</p>';
    }

    /**
     * Render admin notification field.
     *
     * @since    1.0.0
     */
    public function render_admin_notification_field() {
        $options = get_option('temporary_login_links_premium_settings');
        $value = isset($options['admin_notification']) ? (int) $options['admin_notification'] : 0;
        
        echo '<label>';
        echo '<input type="checkbox" name="temporary_login_links_premium_settings[admin_notification]" value="1" ' . checked(1, $value, false) . '>';
        echo esc_html__('Notify admin when a temporary link is used', 'temporary-login-links-premium');
        echo '</label>';
        echo '<p class="description">' . esc_html__('Enable to send an email notification to the admin whenever a temporary login link is used.', 'temporary-login-links-premium') . '</p>';
    }

    /**
     * Render cleanup expired links field.
     *
     * @since    1.0.0
     */
    public function render_cleanup_expired_links_field() {
        $options = get_option('temporary_login_links_premium_settings');
        $value = isset($options['cleanup_expired_links']) ? (int) $options['cleanup_expired_links'] : 1;
        
        echo '<label>';
        echo '<input type="checkbox" name="temporary_login_links_premium_settings[cleanup_expired_links]" value="1" ' . checked(1, $value, false) . '>';
        echo esc_html__('Automatically clean up expired links', 'temporary-login-links-premium');
        echo '</label>';
        echo '<p class="description">' . esc_html__('Enable to automatically delete expired links and their associated users after the specified number of days.', 'temporary-login-links-premium') . '</p>';
    }

    /**
     * Render keep expired links days field.
     *
     * @since    1.0.0
     */
    public function render_keep_expired_links_days_field() {
        $options = get_option('temporary_login_links_premium_settings');
        $value = isset($options['keep_expired_links_days']) ? intval($options['keep_expired_links_days']) : 30;
        
        echo '<input type="number" name="temporary_login_links_premium_settings[keep_expired_links_days]" id="keep_expired_links_days" value="' . esc_attr($value) . '" class="small-text" min="1">';
        echo '<p class="description">' . esc_html__('Number of days to keep expired links before deleting them.', 'temporary-login-links-premium') . '</p>';
    }

    /**
     * Render delete data on uninstall field.
     *
     * @since    1.0.0
     */
    public function render_delete_data_on_uninstall_field() {
        $options = get_option('temporary_login_links_premium_settings');
        $value = isset($options['delete_data_on_uninstall']) ? (int) $options['delete_data_on_uninstall'] : 0;
        
        echo '<label>';
        echo '<input type="checkbox" name="temporary_login_links_premium_settings[delete_data_on_uninstall]" value="1" ' . checked(1, $value, false) . '>';
        echo esc_html__('Delete all plugin data when uninstalling', 'temporary-login-links-premium');
        echo '</label>';
        echo '<p class="description">' . esc_html__('Warning: This will delete all temporary users, links, settings, and logs when the plugin is uninstalled.', 'temporary-login-links-premium') . '</p>';
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
}     
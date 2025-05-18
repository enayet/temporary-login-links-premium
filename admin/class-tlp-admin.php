<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Temporary_Login_Links_Premium
 * @subpackage Temporary_Login_Links_Premium/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for
 * the admin area functionality of the plugin.
 *
 * @package    Temporary_Login_Links_Premium
 * @subpackage Temporary_Login_Links_Premium/admin
 * @author     Your Name <email@example.com>
 */
class TLP_Admin {

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
     * The TLP_Links instance.
     *
     * @since    1.0.0
     * @access   private
     * @var      TLP_Links    $links    The TLP_Links instance.
     */
    private $links;

    /**
     * The TLP_User_Manager instance.
     *
     * @since    1.0.0
     * @access   private
     * @var      TLP_User_Manager    $user_manager    The TLP_User_Manager instance.
     */
    private $user_manager;

    /**
     * The TLP_Security instance.
     *
     * @since    1.0.0
     * @access   private
     * @var      TLP_Security    $security    The TLP_Security instance.
     */
    private $security;

    /**
     * The settings instance.
     *
     * @since    1.0.0
     * @access   private
     * @var      TLP_Settings    $settings    The settings instance.
     */
    private $settings;

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
        
        $this->load_dependencies();
        
        // Add action to handle access log actions
        add_action('admin_init', array($this, 'handle_access_log_actions'));    
    }

    /**
     * Load dependencies.
     *
     * @since    1.0.0
     */
    private function load_dependencies() {
        $this->links = new TLP_Links();
        $this->user_manager = new TLP_User_Manager();
        $this->security = new TLP_Security();
        
        // Load the settings
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-tlp-settings.php';
        $this->settings = new TLP_Settings($this->plugin_name, $this->version);
        
        // Load the list table
        if (!class_exists('WP_List_Table')) {
            require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
        }
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-tlp-list-table.php';
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        // Only enqueue on plugin pages
        if ($this->is_plugin_page()) {
            wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/tlp-admin.css', array(), $this->version, 'all');
            
            // Add color picker styles for branding settings
            if (isset($_GET['page']) && $_GET['page'] === 'temporary-login-links-premium-branding') {
                wp_enqueue_style('wp-color-picker');
            }
            
            // Add datepicker styles for creating/editing links
            if ((isset($_GET['page']) && $_GET['page'] === 'temporary-login-links-premium-links') && 
                (isset($_GET['action']) && ($_GET['action'] === 'create' || $_GET['action'] === 'edit'))) {
                wp_enqueue_style('jquery-ui-datepicker');
            }
        }
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        // Only enqueue on plugin pages
        if ($this->is_plugin_page()) {
            wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/tlp-admin.js', array('jquery'), $this->version, false);
            
            // Add AJAX nonce
            wp_localize_script($this->plugin_name, 'tlp_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('tlp_ajax_nonce'),
                'confirm_delete' => __('Are you sure you want to delete this link? This action cannot be undone.', 'temporary-login-links-premium'),
                'confirm_deactivate' => __('Are you sure you want to deactivate this link? The user will no longer be able to log in.', 'temporary-login-links-premium'),
                'copied' => __('Copied to clipboard!', 'temporary-login-links-premium')
            ));
            
            // Add color picker for branding settings
            if (isset($_GET['page']) && $_GET['page'] === 'temporary-login-links-premium-branding') {
                wp_enqueue_script('wp-color-picker');
                
                // Add media uploader for logo upload
                wp_enqueue_media();
            }
            
            // Add datepicker for custom expiry date
            if ((isset($_GET['page']) && $_GET['page'] === 'temporary-login-links-premium-links') && 
                (isset($_GET['action']) && ($_GET['action'] === 'create' || $_GET['action'] === 'edit'))) {
                wp_enqueue_script('jquery-ui-datepicker');
                wp_enqueue_script('jquery-ui-slider');
            }
        }
    }

    /**
     * Check if we're on a plugin page.
     *
     * @since    1.0.0
     * @return   bool    Whether we're on a plugin page.
     */
    private function is_plugin_page() {
        if (!is_admin()) {
            return false;
        }
        
        $screen = get_current_screen();
        
        if (is_null($screen)) {
            return false;
        }
        
        // Plugin page slugs
        $plugin_pages = array(
            'toplevel_page_temporary-login-links-premium',
            'temporary-login-links_page_temporary-login-links-premium-links',
            'temporary-login-links_page_temporary-login-links-premium-settings',
            'temporary-login-links_page_temporary-login-links-premium-branding',
            'temporary-login-links_page_temporary-login-links-premium-security'
        );
        
        return in_array($screen->id, $plugin_pages) || 
               (isset($_GET['page']) && strpos($_GET['page'], 'temporary-login-links-premium') === 0);
    }

    /**
     * Register the admin menu.
     *
     * @since    1.0.0
     */
    public function register_admin_menu() {
        //$capability = 'manage_temporary_logins';
        $capability = 'manage_options';
        
        // Add the main menu item
        add_menu_page(
            __('Temporary Login Links', 'temporary-login-links-premium'),
            __('Temporary Logins', 'temporary-login-links-premium'),
            $capability,
            'temporary-login-links-premium',
            array($this, 'display_dashboard_page'),
            'dashicons-admin-users',
            85 // Position after users but before tools
        );
        
        // Add dashboard submenu
        add_submenu_page(
            'temporary-login-links-premium',
            __('Dashboard', 'temporary-login-links-premium'),
            __('Dashboard', 'temporary-login-links-premium'),
            $capability,
            'temporary-login-links-premium',
            array($this, 'display_dashboard_page')
        );
        
        // Add manage links submenu
        add_submenu_page(
            'temporary-login-links-premium',
            __('Manage Links', 'temporary-login-links-premium'),
            __('Manage Links', 'temporary-login-links-premium'),
            $capability,
            'temporary-login-links-premium-links',
            array($this, 'display_links_page')
        );
        
        // Add branding settings submenu
        add_submenu_page(
            'temporary-login-links-premium',
            __('Branding', 'temporary-login-links-premium'),
            __('Branding', 'temporary-login-links-premium'),
            $capability,
            'temporary-login-links-premium-branding',
            array($this, 'display_branding_page')
        );
        
        add_submenu_page(
            'temporary-login-links-premium',
            __('Access Logs', 'temporary-login-links-premium'),
            __('Access Logs', 'temporary-login-links-premium'),
            $capability,
            'temporary-login-links-premium-access-logs',
            array($this, 'display_access_logs_page')
        );        
        
        
        // Add security logs submenu
        add_submenu_page(
            'temporary-login-links-premium',
            __('Security Logs', 'temporary-login-links-premium'),
            __('Security Logs', 'temporary-login-links-premium'),
            $capability,
            'temporary-login-links-premium-security',
            array($this, 'display_security_page')
        );
        
        // Add settings submenu
        add_submenu_page(
            'temporary-login-links-premium',
            __('Settings', 'temporary-login-links-premium'),
            __('Settings', 'temporary-login-links-premium'),
            $capability,
            'temporary-login-links-premium-settings',
            array($this, 'display_settings_page')
        );
    }

    /**
     * Display the dashboard page.
     *
     * @since    1.0.0
     */
    public function display_dashboard_page() {
        // Check capability
        if (!$this->security->current_user_can_manage()) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'temporary-login-links-premium'));
        }
        
        // Get statistics
        $stats = $this->get_dashboard_stats();
        
        // Load template
        include plugin_dir_path(__FILE__) . 'partials/admin-display.php';
    }

    /**
     * Display the links management page.
     *
     * @since    1.0.0
     */
    public function display_links_page() {
        // Check capability
        if (!$this->security->current_user_can_manage()) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'temporary-login-links-premium'));
        }
        
        // Process link actions
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '';
        
        switch ($action) {
            case 'create':
                $this->display_create_link_page();
                break;
                
            case 'edit':
                $this->display_edit_link_page();
                break;
                
            case 'view':
                $this->display_view_link_page();
                break;
                
            default:
                $this->display_links_list();
                break;
        }
    }

    /**
     * Display the branding settings page.
     *
     * @since    1.0.0
     */
    public function display_branding_page() {
        // Check capability
        if (!$this->security->current_user_can_manage()) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'temporary-login-links-premium'));
        }
        
        // Handle form submission
        $message = '';
        $message_type = 'success';
        
        if (isset($_POST['tlp_branding_submit']) && check_admin_referer('tlp_branding_nonce')) {
            $message = $this->settings->save_branding_settings($_POST);
            
            if (is_wp_error($message)) {
                $message_type = 'error';
                $message = $message->get_error_message();
            } else {
                $message_type = 'success';
            }
        }
        
        // Get branding settings
        $branding = get_option('temporary_login_links_premium_branding', array());
        
        // Load template
        include plugin_dir_path(__FILE__) . 'partials/branding-settings.php';
    }

    /**
     * Display the security logs page.
     *
     * @since    1.0.0
     */
    public function display_security_page() {
        // Check capability
        if (!$this->security->current_user_can_manage()) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'temporary-login-links-premium'));
        }
        
        // Get current page
        $page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $per_page = 20;
        
        // Get security logs
        $logs = $this->security->get_security_logs(array(
            'page' => $page,
            'per_page' => $per_page
        ));
        
        // Load template
        include plugin_dir_path(__FILE__) . 'partials/security-logs.php';
    }

    /**
     * Display the settings page.
     *
     * @since    1.0.0
     */
    public function display_settings_page() {
        // Call settings class display method
        $this->settings->display_settings_page();
    }

    /**
     * Display the create link page.
     *
     * @since    1.0.0
     */
    private function display_create_link_page() {
        // Handle form submission
        $message = '';
        $message_type = 'success';
        
        if (isset($_POST['tlp_create_link']) && check_admin_referer('tlp_create_link_nonce')) {
            $result = $this->process_create_link($_POST);
            
            if (is_wp_error($result)) {
                $message_type = 'error';
                $message = $result->get_error_message();
            } else {
                // Redirect to the view page
                wp_redirect(admin_url('admin.php?page=temporary-login-links-premium-links&action=view&id=' . $result['id'] . '&created=1'));
                exit;
            }
        }
        
        // Get settings for default values
        $settings = get_option('temporary_login_links_premium_settings', array());
        $default_expiry = isset($settings['link_expiry_default']) ? $settings['link_expiry_default'] : '7 days';
        $default_role = isset($settings['default_role']) ? $settings['default_role'] : 'editor';
        $default_redirect = isset($settings['default_redirect']) ? $settings['default_redirect'] : admin_url();
        
        // Define form fields
        $form_data = array(
            'user_email' => isset($_POST['user_email']) ? sanitize_email($_POST['user_email']) : '',
            'first_name' => isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '',
            'last_name' => isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '',
            'role' => isset($_POST['role']) ? sanitize_text_field($_POST['role']) : $default_role,
            'expiry' => isset($_POST['expiry']) ? sanitize_text_field($_POST['expiry']) : $default_expiry,
            'custom_expiry' => isset($_POST['custom_expiry']) ? sanitize_text_field($_POST['custom_expiry']) : '',
            'max_accesses' => isset($_POST['max_accesses']) ? intval($_POST['max_accesses']) : 0,
            'redirect_to' => isset($_POST['redirect_to']) ? esc_url_raw($_POST['redirect_to']) : $default_redirect,
            'ip_restriction' => isset($_POST['ip_restriction']) ? sanitize_text_field($_POST['ip_restriction']) : '',
            'language' => isset($_POST['language']) ? sanitize_text_field($_POST['language']) : get_locale(),
            'send_email' => isset($_POST['send_email']) ? 1 : 0,
        );
        
        // Get available roles
        $roles = $this->get_available_roles();
        
        // Get available languages
        $languages = $this->get_available_languages();
        
        // Load template
        include plugin_dir_path(__FILE__) . 'partials/create-link.php';
    }

    /**
     * Display the edit link page.
     *
     * @since    1.0.0
     */
    private function display_edit_link_page() {
        // Get link ID
        $link_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if (!$link_id) {
            wp_redirect(admin_url('admin.php?page=temporary-login-links-premium-links'));
            exit;
        }
        
        // Get link details
        $link = $this->links->get_link($link_id);
        
        if (!$link) {
            wp_redirect(admin_url('admin.php?page=temporary-login-links-premium-links&error=not_found'));
            exit;
        }
        
        // Handle form submission
        $message = '';
        $message_type = 'success';
        
        if (isset($_POST['tlp_edit_link']) && check_admin_referer('tlp_edit_link_nonce')) {
            $result = $this->process_edit_link($link_id, $_POST);
            
            if (is_wp_error($result)) {
                $message_type = 'error';
                $message = $result->get_error_message();
            } else {
                $message_type = 'success';
                $message = __('Link updated successfully.', 'temporary-login-links-premium');
                
                // Refresh link data
                $link = $this->links->get_link($link_id);
            }
        }
        
        // Get user data
        $user = get_user_by('id', $link['user_id']);
        
        // Define form fields
        $form_data = array(
            'user_email' => $link['user_email'],
            'first_name' => $user ? get_user_meta($user->ID, 'first_name', true) : '',
            'last_name' => $user ? get_user_meta($user->ID, 'last_name', true) : '',
            'role' => $link['role'],
            'expiry' => 'custom_' . $link['expiry'], // Custom format for the datepicker
            'custom_expiry' => $link['expiry'],
            'max_accesses' => $link['max_accesses'],
            'redirect_to' => $link['redirect_to'],
            'ip_restriction' => $link['ip_restriction'],
            'language' => get_user_meta($link['user_id'], 'temporary_login_language', true),
            'is_active' => $link['is_active'],
        );
        
        // Get available roles
        $roles = $this->get_available_roles();
        
        // Get available languages
        $languages = $this->get_available_languages();
        
        // Get login URL
        $login_url = $this->links->get_login_url($link['link_token']);
        
        // Load template
        include plugin_dir_path(__FILE__) . 'partials/edit-link.php';
    }

    /**
     * Display the view link page.
     *
     * @since    1.0.0
     */
    private function display_view_link_page() {
        // Get link ID
        $link_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if (!$link_id) {
            wp_redirect(admin_url('admin.php?page=temporary-login-links-premium-links'));
            exit;
        }
        
        // Get link details
        $link = $this->links->get_link($link_id);
        
        if (!$link) {
            wp_redirect(admin_url('admin.php?page=temporary-login-links-premium-links&error=not_found'));
            exit;
        }
        
        // Get user data
        $user = get_user_by('id', $link['user_id']);
        
        // Get login URL
        $login_url = $this->links->get_login_url($link['link_token']);
        
        // Check if this is a new link
        $is_new = isset($_GET['created']) && $_GET['created'] == 1;
        
        // Get access logs
        $page = isset($_GET['log_page']) ? max(1, intval($_GET['log_page'])) : 1;
        $per_page = 10;
        
        $logs = $this->links->get_access_logs($link_id, array(
            'page' => $page,
            'per_page' => $per_page
        ));
        
        // Load template
        include plugin_dir_path(__FILE__) . 'partials/view-link.php';
    }

    /**
     * Display the links list.
     *
     * @since    1.0.0
     */
    private function display_links_list() {
        // Create an instance of the list table
        $list_table = new TLP_List_Table($this->links);
        
        // Process bulk actions
        $list_table->process_bulk_action();
        
        // Prepare items for display
        $list_table->prepare_items();
        
        // Display notices
        $this->display_admin_notices();
        
        // Load template
        include plugin_dir_path(__FILE__) . 'partials/links-list.php';
    }

    /**
     * Display admin notices for link actions.
     *
     * @since    1.0.0
     */
    public function display_admin_notices() {
        if (isset($_GET['error'])) {
            $error = sanitize_text_field($_GET['error']);
            $message = '';
            
            switch ($error) {
                case 'not_found':
                    $message = __('Link not found.', 'temporary-login-links-premium');
                    break;
                    
                case 'delete_failed':
                    $message = __('Failed to delete link.', 'temporary-login-links-premium');
                    break;
                    
                case 'update_failed':
                    $message = __('Failed to update link.', 'temporary-login-links-premium');
                    break;
                    
                case 'permission':
                    $message = __('You do not have permission to perform this action.', 'temporary-login-links-premium');
                    break;
            }
            
            if ($message) {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($message) . '</p></div>';
            }
        }
        
        if (isset($_GET['deleted']) && $_GET['deleted'] == 1) {
            $count = isset($_GET['count']) ? intval($_GET['count']) : 1;
            $message = sprintf(
                _n('Link deleted successfully.', '%d links deleted successfully.', $count, 'temporary-login-links-premium'),
                $count
            );
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($message) . '</p></div>';
        }
        
        if (isset($_GET['deactivated']) && $_GET['deactivated'] == 1) {
            $count = isset($_GET['count']) ? intval($_GET['count']) : 1;
            $message = sprintf(
                _n('Link deactivated successfully.', '%d links deactivated successfully.', $count, 'temporary-login-links-premium'),
                $count
            );
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($message) . '</p></div>';
        }
        
        if (isset($_GET['activated']) && $_GET['activated'] == 1) {
            $count = isset($_GET['count']) ? intval($_GET['count']) : 1;
            $message = sprintf(
                _n('Link activated successfully.', '%d links activated successfully.', $count, 'temporary-login-links-premium'),
                $count
            );
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($message) . '</p></div>';
        }
        
        if (isset($_GET['extended']) && $_GET['extended'] == 1) {
            $count = isset($_GET['count']) ? intval($_GET['count']) : 1;
            $message = sprintf(
                _n('Link expiry date extended successfully.', '%d links expiry dates extended successfully.', $count, 'temporary-login-links-premium'),
                $count
            );
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($message) . '</p></div>';
        }
    }

    /**
     * Process create link form submission.
     *
     * @since    1.0.0
     * @param    array    $data    The form data.
     * @return   array|WP_Error    The link data or an error.
     */
    private function process_create_link($data) {
        // Define the fields to process
        $fields = array(
            'user_email' => array(
                'type' => 'email',
                'required' => true,
                'label' => __('Email Address', 'temporary-login-links-premium')
            ),
            'first_name' => array(
                'type' => 'text',
                'required' => false,
                'label' => __('First Name', 'temporary-login-links-premium')
            ),
            'last_name' => array(
                'type' => 'text',
                'required' => false,
                'label' => __('Last Name', 'temporary-login-links-premium')
            ),
            'role' => array(
                'type' => 'role',
                'required' => true,
                'label' => __('Role', 'temporary-login-links-premium')
            ),
            'expiry' => array(
                'type' => 'text',
                'required' => true,
                'label' => __('Expiry', 'temporary-login-links-premium')
            ),
            'custom_expiry' => array(
                'type' => 'date',
                'required' => false,
                'label' => __('Custom Expiry Date', 'temporary-login-links-premium')
            ),
            'max_accesses' => array(
                'type' => 'number',
                'required' => false,
                'label' => __('Maximum Accesses', 'temporary-login-links-premium'),
                'min' => 0
            ),
            'redirect_to' => array(
                'type' => 'url',
                'required' => false,
                'label' => __('Redirect URL', 'temporary-login-links-premium')
            ),
            'ip_restriction' => array(
                'type' => 'ip',
                'required' => false,
                'label' => __('IP Restriction', 'temporary-login-links-premium')
            ),
            'language' => array(
                'type' => 'text',
                'required' => false,
                'label' => __('Language', 'temporary-login-links-premium')
            ),
            'send_email' => array(
                'type' => 'checkbox',
                'required' => false,
                'label' => __('Send Email', 'temporary-login-links-premium')
            ),
        );
        
        // Sanitize and validate
        $sanitized = $this->security->sanitize_form_inputs($data, $fields);
        
        if (is_wp_error($sanitized)) {
            return $sanitized;
        }
        
        // Process expiry
        if ($sanitized['expiry'] === 'custom' && !empty($sanitized['custom_expiry'])) {
            $sanitized['expiry'] = 'custom_' . $sanitized['custom_expiry'];
        }
        
        // Override the email notification setting if specified
        if (isset($sanitized['send_email'])) {
            add_filter('temporary_login_links_premium_send_email', function($send) use ($sanitized) {
                return $sanitized['send_email'] == 1;
            });
        }
        
        // Create the link
        $result = $this->links->create_link($sanitized);
        
        return $result;
    }

    /**
     * Process edit link form submission.
     *
     * @since    1.0.0
     * @param    int      $link_id    The link ID.
     * @param    array    $data       The form data.
     * @return   bool|WP_Error        Whether the link was updated or an error.
     */
    private function process_edit_link($link_id, $data) {
        // Define the fields to process
        $fields = array(
            'first_name' => array(
                'type' => 'text',
                'required' => false,
                'label' => __('First Name', 'temporary-login-links-premium')
            ),
            'last_name' => array(
                'type' => 'text',
                'required' => false,
                'label' => __('Last Name', 'temporary-login-links-premium')
            ),
            'role' => array(
                'type' => 'role',
                'required' => true,
                'label' => __('Role', 'temporary-login-links-premium')
            ),
            'expiry' => array(
                'type' => 'text',
                'required' => true,
                'label' => __('Expiry', 'temporary-login-links-premium')
            ),
            'custom_expiry' => array(
                'type' => 'date',
                'required' => false,
                'label' => __('Custom Expiry Date', 'temporary-login-links-premium')
            ),
            'max_accesses' => array(
                'type' => 'number',
                'required' => false,
                'label' => __('Maximum Accesses', 'temporary-login-links-premium'),
                'min' => 0
            ),
            'redirect_to' => array(
                'type' => 'url',
                'required' => false,
                'label' => __('Redirect URL', 'temporary-login-links-premium')
            ),
            'ip_restriction' => array(
                'type' => 'ip',
                'required' => false,
                'label' => __('IP Restriction', 'temporary-login-links-premium')
            ),
            'language' => array(
                'type' => 'text',
                'required' => false,
                'label' => __('Language', 'temporary-login-links-premium')
            ),
            'is_active' => array(
                'type' => 'checkbox',
                'required' => false,
                'label' => __('Active', 'temporary-login-links-premium')
            ),
        );
        
        // Sanitize and validate

        
        
        
        
        
        
        

// Sanitize and validate
        $sanitized = $this->security->sanitize_form_inputs($data, $fields);
        
        if (is_wp_error($sanitized)) {
            return $sanitized;
        }
        
        // Get the link
        $link = $this->links->get_link($link_id);
        
        if (!$link) {
            return new WP_Error('not_found', __('Link not found.', 'temporary-login-links-premium'));
        }
        
        // Process expiry
        if ($sanitized['expiry'] === 'custom' && !empty($sanitized['custom_expiry'])) {
            $expiry = 'custom_' . $sanitized['custom_expiry'];
        } else {
            $expiry = $sanitized['expiry'];
        }
        
        // Check if expiry date has changed
        if (strpos($expiry, 'custom_') === 0) {
            $new_expiry = substr($expiry, 7); // Remove 'custom_' prefix
            
            if ($new_expiry !== $link['expiry']) {
                // Update expiry date
                $result = $this->links->update_link_expiry($link_id, $new_expiry);
                
                if (is_wp_error($result)) {
                    return $result;
                }
            }
        } else {
            // Use expiry duration
            $result = $this->links->extend_link($link_id, $expiry);
            
            if (is_wp_error($result)) {
                return $result;
            }
        }
        
        // Update user data
        $user_id = $link['user_id'];
        $user = get_user_by('id', $user_id);
        
        if ($user) {
            // Update name
            if (isset($sanitized['first_name']) || isset($sanitized['last_name'])) {
                $user_data = array(
                    'ID' => $user_id
                );
                
                if (isset($sanitized['first_name'])) {
                    $user_data['first_name'] = $sanitized['first_name'];
                }
                
                if (isset($sanitized['last_name'])) {
                    $user_data['last_name'] = $sanitized['last_name'];
                }
                
                wp_update_user($user_data);
            }
            
            // Update role if changed
            if ($sanitized['role'] !== $link['role']) {
                $user->set_role($sanitized['role']);
                
                // Update role in links table
                global $wpdb;
                $table_name = $wpdb->prefix . 'temporary_login_links';
                
                $wpdb->update(
                    $table_name,
                    array('role' => $sanitized['role']),
                    array('id' => $link_id),
                    array('%s'),
                    array('%d')
                );
            }
            
            // Update language
            if (isset($sanitized['language'])) {
                update_user_meta($user_id, 'temporary_login_language', $sanitized['language']);
            }
        }
        
        // Update max accesses
        if (isset($sanitized['max_accesses']) && $sanitized['max_accesses'] !== $link['max_accesses']) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'temporary_login_links';
            
            $wpdb->update(
                $table_name,
                array('max_accesses' => intval($sanitized['max_accesses'])),
                array('id' => $link_id),
                array('%d'),
                array('%d')
            );
        }
        
        // Update redirect URL
        if (isset($sanitized['redirect_to']) && $sanitized['redirect_to'] !== $link['redirect_to']) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'temporary_login_links';
            
            $wpdb->update(
                $table_name,
                array('redirect_to' => $sanitized['redirect_to']),
                array('id' => $link_id),
                array('%s'),
                array('%d')
            );
        }
        
        // Update IP restriction
        if (isset($sanitized['ip_restriction']) && $sanitized['ip_restriction'] !== $link['ip_restriction']) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'temporary_login_links';
            
            $wpdb->update(
                $table_name,
                array('ip_restriction' => $sanitized['ip_restriction']),
                array('id' => $link_id),
                array('%s'),
                array('%d')
            );
        }
        
        // Update active status
        if (isset($sanitized['is_active']) && $sanitized['is_active'] != $link['is_active']) {
            $this->links->update_link_status($link_id, $sanitized['is_active']);
        }
        
        return true;
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
     * Get available languages.
     *
     * @since    1.0.0
     * @return   array    The available languages.
     */
    private function get_available_languages() {
        $languages = array();
        
        // Add site's default language
        $languages[get_locale()] = get_locale();
        
        // Get all available languages
        if (function_exists('get_available_languages')) {
            $available_languages = get_available_languages();
            
            if (!empty($available_languages)) {
                // Get translations
                $translations = wp_get_available_translations();
                
                foreach ($available_languages as $locale) {
                    if (isset($translations[$locale])) {
                        $languages[$locale] = $translations[$locale]['native_name'];
                    } else {
                        $languages[$locale] = $locale;
                    }
                }
            }
        }
        
        // Add English to the list
        $languages['en_US'] = 'English (United States)';
        
        return $languages;
    }

    /**
     * Get dashboard statistics.
     *
     * @since    1.0.0
     * @return   array    The dashboard statistics.
     */
    private function get_dashboard_stats() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'temporary_login_links';
        $current_time = current_time('mysql');      
        
        // Get active links count
        $active_links_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE is_active = 1 AND expiry > %s",
            $current_time
        ));
        
        // Get expired links count
        $expired_links_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE expiry <= %s",
            $current_time
        ));
        
        // Get total accesses
        $total_accesses = $wpdb->get_var(
            "SELECT SUM(access_count) FROM $table_name"
        );
        
        // Get links expiring soon (in the next 48 hours)
        $expiring_soon_time = date('Y-m-d H:i:s', strtotime('+48 hours'));
        
        $expiring_soon = $wpdb->get_results($wpdb->prepare(
            "SELECT id, user_email, expiry FROM $table_name 
            WHERE is_active = 1 AND expiry > %s AND expiry <= %s 
            ORDER BY expiry ASC LIMIT 5",
            $current_time, $expiring_soon_time
        ));
        
        // Get recent links
        $recent_links = $wpdb->get_results(
            "SELECT id, user_email, role, created_at FROM $table_name 
            ORDER BY created_at DESC LIMIT 5"
        );
        
        // Get recent accesses
        $log_table_name = $wpdb->prefix . 'temporary_login_access_log';
        $recent_accesses = $wpdb->get_results($wpdb->prepare(
            "SELECT l.id, l.user_email, a.accessed_at, a.status, a.user_ip 
            FROM $log_table_name a 
            JOIN $table_name l ON a.link_id = l.id 
            WHERE a.accessed_at > %s 
            ORDER BY a.accessed_at DESC LIMIT 10",
            date('Y-m-d H:i:s', strtotime('-7 days'))
        ));
        
        return array(
            'active_links' => $active_links_count,
            'expired_links' => $expired_links_count,
            'total_accesses' => $total_accesses ? $total_accesses : 0,
            'expiring_soon' => $expiring_soon,
            'recent_links' => $recent_links,
            'recent_accesses' => $recent_accesses,
        );
    }

    /**
     * Register the admin AJAX handlers.
     *
     * @since    1.0.0
     */
    public function register_ajax_handlers() {
        // Delete link handler
        add_action('wp_ajax_tlp_delete_link', array($this, 'ajax_delete_link'));
        
        // Deactivate link handler
        add_action('wp_ajax_tlp_deactivate_link', array($this, 'ajax_deactivate_link'));
        
        // Activate link handler
        add_action('wp_ajax_tlp_activate_link', array($this, 'ajax_activate_link'));
        
        // Extend link handler
        add_action('wp_ajax_tlp_extend_link', array($this, 'ajax_extend_link'));
        
        // Resend email handler
        add_action('wp_ajax_tlp_resend_email', array($this, 'ajax_resend_email'));
        
        // Copy link handler (for clipboard functionality)
        add_action('wp_ajax_tlp_copy_link', array($this, 'ajax_copy_link'));
    }

    /**
     * AJAX handler for deleting a link.
     *
     * @since    1.0.0
     */
    public function ajax_delete_link() {
        // Check nonce and capability
        if (!$this->security->is_valid_ajax_request() || !$this->security->current_user_can_manage()) {
            wp_send_json_error(array('message' => __('Permission denied.', 'temporary-login-links-premium')));
        }
        
        // Get link ID
        $link_id = isset($_POST['link_id']) ? intval($_POST['link_id']) : 0;
        
        if (!$link_id) {
            wp_send_json_error(array('message' => __('Invalid link ID.', 'temporary-login-links-premium')));
        }
        
        // Delete the link
        $result = $this->links->delete_link($link_id);
        
        if ($result) {
            wp_send_json_success(array('message' => __('Link deleted successfully.', 'temporary-login-links-premium')));
        } else {
            wp_send_json_error(array('message' => __('Failed to delete link.', 'temporary-login-links-premium')));
        }
    }

    /**
     * AJAX handler for deactivating a link.
     *
     * @since    1.0.0
     */
    public function ajax_deactivate_link() {
        // Check nonce and capability
        if (!$this->security->is_valid_ajax_request() || !$this->security->current_user_can_manage()) {
            wp_send_json_error(array('message' => __('Permission denied.', 'temporary-login-links-premium')));
        }
        
        // Get link ID
        $link_id = isset($_POST['link_id']) ? intval($_POST['link_id']) : 0;
        
        if (!$link_id) {
            wp_send_json_error(array('message' => __('Invalid link ID.', 'temporary-login-links-premium')));
        }
        
        // Deactivate the link
        $result = $this->links->update_link_status($link_id, false);
        
        if ($result) {
            wp_send_json_success(array('message' => __('Link deactivated successfully.', 'temporary-login-links-premium')));
        } else {
            wp_send_json_error(array('message' => __('Failed to deactivate link.', 'temporary-login-links-premium')));
        }
    }

    /**
     * AJAX handler for activating a link.
     *
     * @since    1.0.0
     */
    public function ajax_activate_link() {
        // Check nonce and capability
        if (!$this->security->is_valid_ajax_request() || !$this->security->current_user_can_manage()) {
            wp_send_json_error(array('message' => __('Permission denied.', 'temporary-login-links-premium')));
        }
        
        // Get link ID
        $link_id = isset($_POST['link_id']) ? intval($_POST['link_id']) : 0;
        
        if (!$link_id) {
            wp_send_json_error(array('message' => __('Invalid link ID.', 'temporary-login-links-premium')));
        }
        
        // Activate the link
        $result = $this->links->update_link_status($link_id, true);
        
        if ($result) {
            wp_send_json_success(array('message' => __('Link activated successfully.', 'temporary-login-links-premium')));
        } else {
            wp_send_json_error(array('message' => __('Failed to activate link.', 'temporary-login-links-premium')));
        }
    }

    /**
     * AJAX handler for extending a link.
     *
     * @since    1.0.0
     */
    public function ajax_extend_link() {
        // Check nonce and capability
        if (!$this->security->is_valid_ajax_request() || !$this->security->current_user_can_manage()) {
            wp_send_json_error(array('message' => __('Permission denied.', 'temporary-login-links-premium')));
        }
        
        // Get link ID
        $link_id = isset($_POST['link_id']) ? intval($_POST['link_id']) : 0;
        $duration = isset($_POST['duration']) ? sanitize_text_field($_POST['duration']) : '7 days';
        
        if (!$link_id) {
            wp_send_json_error(array('message' => __('Invalid link ID.', 'temporary-login-links-premium')));
        }
        
        // Extend the link
        $result = $this->links->extend_link($link_id, $duration);
        
        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        } else {
            wp_send_json_success(array('message' => __('Link expiry extended successfully.', 'temporary-login-links-premium')));
        }
    }

    /**
     * AJAX handler for resending an email.
     *
     * @since    1.0.0
     */
    public function ajax_resend_email() {
        // Check nonce and capability
        if (!$this->security->is_valid_ajax_request() || !$this->security->current_user_can_manage()) {
            wp_send_json_error(array('message' => __('Permission denied.', 'temporary-login-links-premium')));
        }
        
        // Get link ID
        $link_id = isset($_POST['link_id']) ? intval($_POST['link_id']) : 0;
        
        if (!$link_id) {
            wp_send_json_error(array('message' => __('Invalid link ID.', 'temporary-login-links-premium')));
        }
        
        // Get link details
        $link = $this->links->get_link($link_id);
        
        if (!$link) {
            wp_send_json_error(array('message' => __('Link not found.', 'temporary-login-links-premium')));
        }
        
        // Get user data
        $user = get_user_by('id', $link['user_id']);
        
        if (!$user) {
            wp_send_json_error(array('message' => __('User not found.', 'temporary-login-links-premium')));
        }
        
        // Prepare login URL
        $login_url = $this->links->get_login_url($link['link_token']);
        
        // Get user data for the email
        $user_data = array(
            'user_email' => $link['user_email'],
            'first_name' => get_user_meta($user->ID, 'first_name', true),
            'last_name' => get_user_meta($user->ID, 'last_name', true),
            'role' => $link['role'],
            'max_accesses' => $link['max_accesses'],
        );
        
        // Force sending email
        add_filter('temporary_login_links_premium_send_email', '__return_true');
        
        // Send the email
        $this->links->send_notification($user_data, $login_url, $link['expiry']);
        
        wp_send_json_success(array('message' => __('Email sent successfully.', 'temporary-login-links-premium')));
    }

    /**
     * AJAX handler for copying a link.
     *
     * @since    1.0.0
     */
    public function ajax_copy_link() {
        // Check nonce and capability
        if (!$this->security->is_valid_ajax_request() || !$this->security->current_user_can_manage()) {
            wp_send_json_error(array('message' => __('Permission denied.', 'temporary-login-links-premium')));
        }
        
        // Get link ID
        $link_id = isset($_POST['link_id']) ? intval($_POST['link_id']) : 0;
        
        if (!$link_id) {
            wp_send_json_error(array('message' => __('Invalid link ID.', 'temporary-login-links-premium')));
        }
        
        // Get link details
        $link = $this->links->get_link($link_id);
        
        if (!$link) {
            wp_send_json_error(array('message' => __('Link not found.', 'temporary-login-links-premium')));
        }
        
        // Get login URL
        $login_url = $this->links->get_login_url($link['link_token']);
        
        wp_send_json_success(array(
            'message' => __('Link copied to clipboard.', 'temporary-login-links-premium'),
            'url' => $login_url
        ));
    }
    
    /**
     * Add welcome page redirect on activation.
     *
     * @since    1.0.0
     */
    public function maybe_redirect_to_welcome_page() {
        // Check if activation redirect is needed
        if (get_transient('tlp_activation_redirect')) {
            // Delete the transient
            delete_transient('tlp_activation_redirect');
            
            // Redirect to welcome page
            wp_redirect(admin_url('admin.php?page=temporary-login-links-premium&welcome=1'));
            exit;
        }
    }
    
    /**
     * Handle link actions.
     *
     * @since    1.0.0
     */
    public function handle_link_actions() {
        // Check if we're on the links page
        if (!isset($_GET['page']) || $_GET['page'] !== 'temporary-login-links-premium-links') {
            return;
        }
        
        // Check capability
        if (!$this->security->current_user_can_manage()) {
            return;
        }
        
        // Get action
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '';
        
        // Handle delete action
        if ($action === 'delete' && isset($_GET['id']) && check_admin_referer('tlp_delete_link')) {
            $link_id = intval($_GET['id']);
            $result = $this->links->delete_link($link_id);
            
            if ($result) {
                wp_redirect(admin_url('admin.php?page=temporary-login-links-premium-links&deleted=1'));
                exit;
            } else {
                wp_redirect(admin_url('admin.php?page=temporary-login-links-premium-links&error=delete_failed'));
                exit;
            }
        }
        
        // Handle deactivate action
        if ($action === 'deactivate' && isset($_GET['id']) && check_admin_referer('tlp_deactivate_link')) {
            $link_id = intval($_GET['id']);
            $result = $this->links->update_link_status($link_id, false);
            
            if ($result) {
                wp_redirect(admin_url('admin.php?page=temporary-login-links-premium-links&deactivated=1'));
                exit;
            } else {
                wp_redirect(admin_url('admin.php?page=temporary-login-links-premium-links&error=update_failed'));
                exit;
            }
        }
        
        // Handle activate action
        if ($action === 'activate' && isset($_GET['id']) && check_admin_referer('tlp_activate_link')) {
            $link_id = intval($_GET['id']);
            $result = $this->links->update_link_status($link_id, true);
            
            if ($result) {
                wp_redirect(admin_url('admin.php?page=temporary-login-links-premium-links&activated=1'));
                exit;
            } else {
                wp_redirect(admin_url('admin.php?page=temporary-login-links-premium-links&error=update_failed'));
                exit;
            }
        }
        
        // Handle extend action
        if ($action === 'extend' && isset($_GET['id']) && isset($_GET['duration']) && check_admin_referer('tlp_extend_link')) {
            $link_id = intval($_GET['id']);
            $duration = sanitize_text_field($_GET['duration']);
            $result = $this->links->extend_link($link_id, $duration);
            
            if (!is_wp_error($result)) {
                wp_redirect(admin_url('admin.php?page=temporary-login-links-premium-links&extended=1'));
                exit;
            } else {
                wp_redirect(admin_url('admin.php?page=temporary-login-links-premium-links&error=update_failed'));
                exit;
            }
        }
    }
    
    /**
     * Check if plugin has been upgraded.
     *
     * @since    1.0.0
     */
    public function check_plugin_upgrade() {
        $current_version = get_option('temporary_login_links_premium_version', '0.0.0');
        
        if (version_compare($current_version, $this->version, '<')) {
            // Perform upgrade tasks if needed
            
            // Update version
            update_option('temporary_login_links_premium_version', $this->version);
        }
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
     * Handle security log actions.
     *
     * @since    1.0.0
     */
    public function handle_security_log_actions() {
        // Check if we're on the security logs page
        if (!isset($_GET['page']) || $_GET['page'] !== 'temporary-login-links-premium-security') {
            return;
        }

        // Check capability
        if (!$this->security->current_user_can_manage()) {
            return;
        }

        // Handle clear logs action
        if (isset($_GET['action']) && $_GET['action'] === 'clear_logs' && check_admin_referer('tlp_clear_security_logs')) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'temporary_login_security_logs';

            // Delete all logs
            $wpdb->query("TRUNCATE TABLE $table_name");

            // Redirect back to the security logs page
            wp_redirect(admin_url('admin.php?page=temporary-login-links-premium-security&cleared=1'));
            exit;
        }
    }
    
    
    /**
     * Display the access logs page.
     *
     * @since    1.0.0
     */
    public function display_access_logs_page() {
        // Check capability
        if (!$this->security->current_user_can_manage()) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'temporary-login-links-premium'));
        }

        // Get current page
        $page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $per_page = 20;

        // Prepare filter arguments
        $args = array(
            'page' => $page,
            'per_page' => $per_page
        );

        // Status filter
        if (isset($_GET['status']) && !empty($_GET['status'])) {
            $args['status'] = sanitize_text_field($_GET['status']);
        }

        // Search filter
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $args['search'] = sanitize_text_field($_GET['search']);
        }

        // Date range filter
        if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
            $args['start_date'] = sanitize_text_field($_GET['start_date']);
        }

        if (isset($_GET['end_date']) && !empty($_GET['end_date'])) {
            $args['end_date'] = sanitize_text_field($_GET['end_date']);
        }

        // Get access logs
        $logs = $this->links->get_all_access_logs($args);

        // Load template
        include plugin_dir_path(__FILE__) . 'partials/access-logs.php';
    }
    
    
    /**
     * Handle access log actions.
     *
     * @since    1.0.0
     */
    public function handle_access_log_actions() {
        // Check if we're on the access logs page
        if (!isset($_GET['page']) || $_GET['page'] !== 'temporary-login-links-premium-access-logs') {
            return;
        }

        // Check capability
        if (!$this->security->current_user_can_manage()) {
            return;
        }

        // Handle clear logs action
        if (isset($_GET['action']) && $_GET['action'] === 'clear_logs' && check_admin_referer('tlp_clear_access_logs')) {
            $filter_args = array();

            // Preserve any filter parameters
            if (isset($_GET['status']) && !empty($_GET['status'])) {
                $filter_args['status'] = sanitize_text_field($_GET['status']);
            }

            if (isset($_GET['search']) && !empty($_GET['search'])) {
                $filter_args['search'] = sanitize_text_field($_GET['search']);
            }

            if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
                $filter_args['start_date'] = sanitize_text_field($_GET['start_date']);
            }

            if (isset($_GET['end_date']) && !empty($_GET['end_date'])) {
                $filter_args['end_date'] = sanitize_text_field($_GET['end_date']);
            }

            // Delete the logs based on filter
            $deleted = $this->links->delete_access_logs($filter_args);

            // Redirect back to the logs page
            $redirect_url = add_query_arg(array(
                'cleared' => 1,
                'count' => $deleted
            ), admin_url('admin.php?page=temporary-login-links-premium-access-logs'));

            wp_redirect($redirect_url);
            exit;
        }
    }    
    
    
    
}        
        
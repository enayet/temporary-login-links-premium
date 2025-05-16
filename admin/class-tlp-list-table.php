<?php
/**
 * Links list table class.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Temporary_Login_Links_Premium
 * @subpackage Temporary_Login_Links_Premium/admin
 */

/**
 * Links list table class.
 *
 * This class extends the WP_List_Table class to create a custom table
 * for displaying the temporary login links.
 *
 * @package    Temporary_Login_Links_Premium
 * @subpackage Temporary_Login_Links_Premium/admin
 * @author     Your Name <email@example.com>
 */
class TLP_List_Table extends WP_List_Table {

    /**
     * The TLP_Links instance.
     *
     * @since    1.0.0
     * @access   private
     * @var      TLP_Links    $links    The TLP_Links instance.
     */
    private $links;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param    TLP_Links    $links    The TLP_Links instance.
     */
    public function __construct($links) {
        parent::__construct(array(
            'singular' => 'link',
            'plural'   => 'links',
            'ajax'     => false
        ));
        
        $this->links = $links;
    }

    /**
     * Retrieve links data from the database.
     *
     * @since    1.0.0
     * @param    int       $per_page    The number of items per page.
     * @param    int       $page_number The page number.
     * @return   array                  The links data.
     */
    public function get_links($per_page = 20, $page_number = 1) {
        // Get search term
        $search = isset($_REQUEST['s']) ? sanitize_text_field($_REQUEST['s']) : '';
        
        // Get status filter
        $status = isset($_REQUEST['status']) ? sanitize_text_field($_REQUEST['status']) : 'all';
        
        // Get role filter
        $role = isset($_REQUEST['role']) ? sanitize_text_field($_REQUEST['role']) : '';
        
        // Get order
        $orderby = isset($_REQUEST['orderby']) ? sanitize_text_field($_REQUEST['orderby']) : 'created_at';
        $order = isset($_REQUEST['order']) ? sanitize_text_field($_REQUEST['order']) : 'DESC';
        
        // Query the links
        $result = $this->links->get_links(array(
            'per_page' => $per_page,
            'page'     => $page_number,
            'search'   => $search,
            'status'   => $status,
            'role'     => $role,
            'orderby'  => $orderby,
            'order'    => $order
        ));
        
        return $result;
    }

    /**
     * Delete a link.
     *
     * @since    1.0.0
     * @param    int       $id    The link ID.
     */
    public function delete_link($id) {
        $this->links->delete_link($id);
    }

    /**
     * Activate a link.
     *
     * @since    1.0.0
     * @param    int       $id    The link ID.
     */
    public function activate_link($id) {
        $this->links->update_link_status($id, true);
    }

    /**
     * Deactivate a link.
     *
     * @since    1.0.0
     * @param    int       $id    The link ID.
     */
    public function deactivate_link($id) {
        $this->links->update_link_status($id, false);
    }

    /**
     * Extend a link.
     *
     * @since    1.0.0
     * @param    int       $id        The link ID.
     * @param    string    $duration  The duration to extend.
     */
    public function extend_link($id, $duration) {
        $this->links->extend_link($id, $duration);
    }

    /**
     * Returns the count of records in the database.
     *
     * @since    1.0.0
     * @return   int    The count of records.
     */
    public function record_count() {
        $result = $this->get_links(1, 1); // Just to get the total count
        return $result['total_items'];
    }

    /**
     * Add checkbox column.
     *
     * @since    1.0.0
     * @param    object    $item    The current item.
     * @return   string             The checkbox HTML.
     */
    function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="bulk-delete[]" value="%s" />',
            $item['id']
        );
    }

    /**
     * Render email column.
     *
     * @since    1.0.0
     * @param    array     $item    The current item.
     * @return   string             The email column HTML.
     */
    function column_user_email($item) {
        // Build row actions
        $actions = array(
            'view' => sprintf(
                '<a href="%s">%s</a>',
                admin_url('admin.php?page=temporary-login-links-premium-links&action=view&id=' . $item['id']),
                __('View', 'temporary-login-links-premium')
            ),
            'edit' => sprintf(
                '<a href="%s">%s</a>',
                admin_url('admin.php?page=temporary-login-links-premium-links&action=edit&id=' . $item['id']),
                __('Edit', 'temporary-login-links-premium')
            ),
            'delete' => sprintf(
                '<a href="%s" class="tlp-delete-link" data-id="%s">%s</a>',
                wp_nonce_url(admin_url('admin.php?page=temporary-login-links-premium-links&action=delete&id=' . $item['id']), 'tlp_delete_link'),
                $item['id'],
                __('Delete', 'temporary-login-links-premium')
            ),
        );
        
        // Return the email + row actions
        return sprintf(
            '<strong><a href="%s">%s</a></strong>%s',
            admin_url('admin.php?page=temporary-login-links-premium-links&action=view&id=' . $item['id']),
            $item['user_email'],
            $this->row_actions($actions)
        );
    }

    /**
     * Render role column.
     *
     * @since    1.0.0
     * @param    array     $item    The current item.
     * @return   string             The role column HTML.
     */
    function column_role($item) {
        return $this->get_role_display_name($item['role']);
    }

    /**
     * Render status column.
     *
     * @since    1.0.0
     * @param    array     $item    The current item.
     * @return   string             The status column HTML.
     */
    function column_status($item) {
        $current_time = current_time('mysql');
        $status_class = '';
        $status_text = '';
        
        if ($item['is_active'] == 0) {
            $status_class = 'inactive';
            $status_text = __('Inactive', 'temporary-login-links-premium');
        } elseif (strtotime($item['expiry']) < strtotime($current_time)) {
            $status_class = 'expired';
            $status_text = __('Expired', 'temporary-login-links-premium');
        } else {
            $status_class = 'active';
            $status_text = __('Active', 'temporary-login-links-premium');
        }
        
        return sprintf(
            '<span class="tlp-status tlp-status-%s">%s</span>',
            $status_class,
            $status_text
        );
    }

    /**
     * Render expiry column.
     *
     * @since    1.0.0
     * @param    array     $item    The current item.
     * @return   string             The expiry column HTML.
     */
    function column_expiry($item) {
        $current_time = current_time('mysql');
        $expiry_time = strtotime($item['expiry']);
        $current_timestamp = strtotime($current_time);
        
        // Format the expiry date
        $expiry_date = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $expiry_time);
        
        // Calculate time remaining
        if ($expiry_time > $current_timestamp) {
            $time_diff = $expiry_time - $current_timestamp;
            
            if ($time_diff < 86400) { // Less than a day
                $hours = floor($time_diff / 3600);
                $minutes = floor(($time_diff % 3600) / 60);
                
                $time_remaining = sprintf(
                    _n('%d hour', '%d hours', $hours, 'temporary-login-links-premium'),
                    $hours
                );
                
                if ($minutes > 0) {
                    $time_remaining .= ' ' . sprintf(
                        _n('%d minute', '%d minutes', $minutes, 'temporary-login-links-premium'),
                        $minutes
                    );
                }
            } else { // More than a day
                $days = floor($time_diff / 86400);
                
                $time_remaining = sprintf(
                    _n('%d day', '%d days', $days, 'temporary-login-links-premium'),
                    $days
                );
            }
            
            return sprintf(
                '%s<br><small>%s: %s</small>',
                $expiry_date,
                __('Remaining', 'temporary-login-links-premium'),
                $time_remaining
            );
        } else {
            return sprintf(
                '%s<br><small>%s</small>',
                $expiry_date,
                __('Expired', 'temporary-login-links-premium')
            );
        }
    }

    /**
     * Render created column.
     *
     * @since    1.0.0
     * @param    array     $item    The current item.
     * @return   string             The created column HTML.
     */
    function column_created_at($item) {
        return date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($item['created_at']));
    }

    /**
     * Render accesses column.
     *
     * @since    1.0.0
     * @param    array     $item    The current item.
     * @return   string             The accesses column HTML.
     */
    function column_access_count($item) {
        if ($item['max_accesses'] > 0) {
            return sprintf(
                '%d / %d',
                $item['access_count'],
                $item['max_accesses']
            );
        } else {
            return $item['access_count'];
        }
    }

    /**
     * Render actions column.
     *
     * @since    1.0.0
     * @param    array     $item    The current item.
     * @return   string             The actions column HTML.
     */
    function column_actions($item) {
        $current_time = current_time('mysql');
        $actions = array();
        
        // View button
        $actions[] = sprintf(
            '<a href="%s" class="button button-small">%s</a>',
            admin_url('admin.php?page=temporary-login-links-premium-links&action=view&id=' . $item['id']),
            __('View', 'temporary-login-links-premium')
        );
        
        // Activate/Deactivate button
        if ($item['is_active'] == 1) {
            $actions[] = sprintf(
                '<a href="%s" class="button button-small tlp-deactivate-link" data-id="%s">%s</a>',
                wp_nonce_url(admin_url('admin.php?page=temporary-login-links-premium-links&action=deactivate&id=' . $item['id']), 'tlp_deactivate_link'),
                $item['id'],
                __('Deactivate', 'temporary-login-links-premium')
            );
        } else {
            $actions[] = sprintf(
                '<a href="%s" class="button button-small tlp-activate-link" data-id="%s">%s</a>',
                wp_nonce_url(admin_url('admin.php?page=temporary-login-links-premium-links&action=activate&id=' . $item['id']), 'tlp_activate_link'),
                $item['id'],
                __('Activate', 'temporary-login-links-premium')
            );
        }
        
        // Extend button
        if (strtotime($item['expiry']) > strtotime($current_time) && $item['is_active'] == 1) {
            $actions[] = sprintf(
                '<a href="%s" class="button button-small tlp-extend-link" data-id="%s">%s</a>',
                wp_nonce_url(admin_url('admin.php?page=temporary-login-links-premium-links&action=extend&id=' . $item['id']), 'tlp_extend_link'),
                $item['id'],
                __('Extend', 'temporary-login-links-premium')
            );
        }
        
        return implode(' ', $actions);
    }

    /**
     * Define the columns that are going to be used in the table.
     *
     * @since    1.0.0
     * @return   array    The columns of the table.
     */
    function get_columns() {
        return array(
            'cb'           => '<input type="checkbox" />',
            'user_email'   => __('Email', 'temporary-login-links-premium'),
            'role'         => __('Role', 'temporary-login-links-premium'),
            'status'       => __('Status', 'temporary-login-links-premium'),
            'expiry'       => __('Expiry', 'temporary-login-links-premium'),
            'created_at'   => __('Created', 'temporary-login-links-premium'),
            'access_count' => __('Accesses', 'temporary-login-links-premium'),
            'actions'      => __('Actions', 'temporary-login-links-premium'),
        );
    }

    /**
     * Define which columns are hidden.
     *
     * @since    1.0.0
     * @return   array    The hidden columns.
     */
    function get_hidden_columns() {
        return array();
    }

    /**
     * Define the sortable columns.
     *
     * @since    1.0.0
     * @return   array    The sortable columns.
     */
    function get_sortable_columns() {
        return array(
            'user_email'   => array('user_email', false),
            'role'         => array('role', false),
            'expiry'       => array('expiry', false),
            'created_at'   => array('created_at', true),
            'access_count' => array('access_count', false),
        );
    }

    /**
     * Define bulk actions.
     *
     * @since    1.0.0
     * @return   array    The bulk actions.
     */
    function get_bulk_actions() {
        return array(
            'bulk-delete'      => __('Delete', 'temporary-login-links-premium'),
            'bulk-deactivate'  => __('Deactivate', 'temporary-login-links-premium'),
            'bulk-activate'    => __('Activate', 'temporary-login-links-premium'),
            'bulk-extend-7'    => __('Extend by 7 days', 'temporary-login-links-premium'),
            'bulk-extend-30'   => __('Extend by 30 days', 'temporary-login-links-premium'),
        );
    }

    /**
     * Process bulk actions.
     *
     * @since    1.0.0
     */
    function process_bulk_action() {
        // Detect when a bulk action is being triggered
        if ('bulk-delete' === $this->current_action()) {
            // Verify the nonce
            if (isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'bulk-' . $this->_args['plural'])) {
                $delete_ids = isset($_POST['bulk-delete']) ? $_POST['bulk-delete'] : array();
                
                if (!empty($delete_ids)) {
                    // Loop through IDs and delete them
                    foreach ($delete_ids as $id) {
                        $this->delete_link(absint($id));
                    }
                    
                    // Redirect to the links page with a success message
                    wp_redirect(add_query_arg(array(
                        'deleted' => 1,
                        'count' => count($delete_ids)
                    ), admin_url('admin.php?page=temporary-login-links-premium-links')));
                    exit;
                }
            }
        }
        
        // Deactivate bulk action
        if ('bulk-deactivate' === $this->current_action()) {
            // Verify the nonce
            if (isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'bulk-' . $this->_args['plural'])) {
                $deactivate_ids = isset($_POST['bulk-delete']) ? $_POST['bulk-delete'] : array();
                
                if (!empty($deactivate_ids)) {
                    // Loop through IDs and deactivate them
                    foreach ($deactivate_ids as $id) {
                        $this->deactivate_link(absint($id));
                    }
                    
                    // Redirect to the links page with a success message
                    wp_redirect(add_query_arg(array(
                        'deactivated' => 1,
                        'count' => count($deactivate_ids)
                    ), admin_url('admin.php?page=temporary-login-links-premium-links')));
                    exit;
                }
            }
        }
        
        // Activate bulk action
        if ('bulk-activate' === $this->current_action()) {
            // Verify the nonce
            if (isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'bulk-' . $this->_args['plural'])) {
                $activate_ids = isset($_POST['bulk-delete']) ? $_POST['bulk-delete'] : array();
                
                if (!empty($activate_ids)) {
                    // Loop through IDs and activate them
                    foreach ($activate_ids as $id) {
                        $this->activate_link(absint($id));
                    }
                    
                    // Redirect to the links page with a success message
                    wp_redirect(add_query_arg(array(
                        'activated' => 1,
                        'count' => count($activate_ids)
                    ), admin_url('admin.php?page=temporary-login-links-premium-links')));
                    exit;
                }
            }
        }
        
        // Extend by 7 days bulk action
        if ('bulk-extend-7' === $this->current_action()) {
            // Verify the nonce
            if (isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'bulk-' . $this->_args['plural'])) {
                $extend_ids = isset($_POST['bulk-delete']) ? $_POST['bulk-delete'] : array();
                
                if (!empty($extend_ids)) {
                    // Loop through IDs and extend them
                    foreach ($extend_ids as $id) {
                        $this->extend_link(absint($id), '7 days');
                    }
                    
                    // Redirect to the links page with a success message
                    wp_redirect(add_query_arg(array(
                        'extended' => 1,
                        'count' => count($extend_ids)
                    ), admin_url('admin.php?page=temporary-login-links-premium-links')));
                    exit;
                }
            }
        }
        
        // Extend by 30 days bulk action
        if ('bulk-extend-30' === $this->current_action()) {
            // Verify the nonce
            if (isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'bulk-' . $this->_args['plural'])) {
                $extend_ids = isset($_POST['bulk-delete']) ? $_POST['bulk-delete'] : array();
                
                if (!empty($extend_ids)) {
                    // Loop through IDs and extend them
                    foreach ($extend_ids as $id) {
                        $this->extend_link(absint($id), '1 month');
                    }
                    
                    // Redirect to the links page with a success message
                    wp_redirect(add_query_arg(array(
                        'extended' => 1,
                        'count' => count($extend_ids)
                    ), admin_url('admin.php?page=temporary-login-links-premium-links')));
                    exit;
                }
            }
        }
    }

    /**
     * Add extra filters above the table.
     *
     * @since    1.0.0
     * @param    string    $which    The location: 'top' or 'bottom'.
     */
    function extra_tablenav($which) {
        if ('top' === $which) {
            // Get the current status filter
            $status = isset($_REQUEST['status']) ? sanitize_text_field($_REQUEST['status']) : 'all';
            
            // Get the current role filter
            $current_role = isset($_REQUEST['role']) ? sanitize_text_field($_REQUEST['role']) : '';
            
            // Status filter
            echo '<div class="alignleft actions">';
            echo '<select name="status">';
            echo '<option value="all"' . selected($status, 'all', false) . '>' . esc_html__('All statuses', 'temporary-login-links-premium') . '</option>';
            echo '<option value="active"' . selected($status, 'active', false) . '>' . esc_html__('Active only', 'temporary-login-links-premium') . '</option>';
            echo '<option value="inactive"' . selected($status, 'inactive', false) . '>' . esc_html__('Inactive only', 'temporary-login-links-premium') . '</option>';
            echo '<option value="expired"' . selected($status, 'expired', false) . '>' . esc_html__('Expired only', 'temporary-login-links-premium') . '</option>';
            echo '</select>';
            
            // Role filter
            $roles = $this->get_available_roles();
            
            echo '<select name="role">';
            echo '<option value=""' . selected($current_role, '', false) . '>' . esc_html__('All roles', 'temporary-login-links-premium') . '</option>';
            
            foreach ($roles as $role => $name) {
                echo '<option value="' . esc_attr($role) . '"' . selected($current_role, $role, false) . '>' . esc_html($name) . '</option>';
            }
            
            echo '</select>';
            
            submit_button(__('Filter', 'temporary-login-links-premium'), '', 'filter_action', false);
            echo '</div>';
        }
    }

    /**
     * Prepare items for display.
     *
     * @since    1.0.0
     */
    function prepare_items() {
        // Set column headers
        $this->_column_headers = array(
            $this->get_columns(),
            $this->get_hidden_columns(),
            $this->get_sortable_columns()
        );
        
        // Process bulk actions
        $this->process_bulk_action();
        
        // Get the data
        $per_page = $this->get_items_per_page('links_per_page', 20);
        $current_page = $this->get_pagenum();
        $result = $this->get_links($per_page, $current_page);
        
        $this->items = $result['items'];
        
        // Set pagination args
        $this->set_pagination_args(array(
            'total_items' => $result['total_items'],
            'per_page'    => $per_page,
            'total_pages' => ceil($result['total_items'] / $per_page)
        ));
    }

    /**
     * Get the role display name.
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
     * Message to be displayed when there are no items.
     *
     * @since    1.0.0
     */
    public function no_items() {
        _e('No temporary login links found.', 'temporary-login-links-premium');
    }
}
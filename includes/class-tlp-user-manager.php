<?php
/**
 * Handles temporary user management.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Temporary_Login_Links_Premium
 * @subpackage Temporary_Login_Links_Premium/includes
 */

/**
 * Handles temporary user management.
 *
 * This class manages temporary users, their roles, and cleanup.
 * It also handles the synchronization between temporary users and their login links.
 *
 * @since      1.0.0
 * @package    Temporary_Login_Links_Premium
 * @subpackage Temporary_Login_Links_Premium/includes
 * @author     Your Name <email@example.com>
 */
class TLP_User_Manager {

    /**
     * The database table name for links.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $table_name    The database table name for links.
     */
    private $table_name;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'temporary_login_links';
    }

    /**
     * Register hooks related to user management.
     *
     * @since    1.0.0
     */
    public function register_hooks() {
        // Prevent temporary users from changing their profile
        add_action( 'init', array( $this, 'prevent_profile_update' ) );
        
        // Add custom column to users table
        add_filter( 'manage_users_columns', array( $this, 'add_temporary_user_column' ) );
        add_filter( 'manage_users_custom_column', array( $this, 'render_temporary_user_column' ), 10, 3 );
        
        // Add a filter to users table
        add_action( 'restrict_manage_users', array( $this, 'add_temporary_user_filter' ) );
        add_filter( 'pre_get_users', array( $this, 'filter_temporary_users' ) );
        
        // Add user role filter
        add_filter( 'editable_roles', array( $this, 'filter_roles_for_temporary_users' ) );
        
        // Add bulk actions
        add_filter( 'bulk_actions-users', array( $this, 'add_bulk_actions' ) );
        add_filter( 'handle_bulk_actions-users', array( $this, 'handle_bulk_actions' ), 10, 3 );
        
        // Display admin notices for bulk actions
        add_action( 'admin_notices', array( $this, 'bulk_action_admin_notice' ) );
    }

    /**
     * Prevent temporary users from updating their profile.
     *
     * @since    1.0.0
     */
    public function prevent_profile_update() {
        // Check if this is a profile update
        if ( isset( $_POST['action'] ) && 'update' === $_POST['action'] && isset( $_POST['user_id'] ) ) {
            // Check if this is a temporary user
            $user_id = intval( $_POST['user_id'] );
            $is_temp_user = $this->is_temporary_user( $user_id );
            
            if ( $is_temp_user && isset( $_POST['submit'] ) ) {
                // Get the current user ID
                $current_user_id = get_current_user_id();
                
                // If the temporary user is trying to update their own profile
                if ( $current_user_id === $user_id ) {
                    wp_die( 
                        __( 'Temporary users cannot update their profile. Please contact the site administrator if you need to make changes.', 'temporary-login-links-premium' ),
                        __( 'Profile Update Restricted', 'temporary-login-links-premium' ),
                        array( 'response' => 403, 'back_link' => true )
                    );
                }
            }
        }
    }

    /**
     * Add a custom column to the users table.
     *
     * @since    1.0.0
     * @param    array     $columns    The current columns.
     * @return   array                 The modified columns.
     */
    public function add_temporary_user_column( $columns ) {
        $columns['temporary_user'] = __( 'Temporary User', 'temporary-login-links-premium' );
        return $columns;
    }

    /**
     * Render the custom column in the users table.
     *
     * @since    1.0.0
     * @param    string    $output     The column output.
     * @param    string    $column_name The column name.
     * @param    int       $user_id    The user ID.
     * @return   string                The modified column output.
     */
    public function render_temporary_user_column( $output, $column_name, $user_id ) {
        if ( 'temporary_user' === $column_name ) {
            $is_temp_user = $this->is_temporary_user( $user_id );
            
            if ( $is_temp_user ) {
                $link_id = get_user_meta( $user_id, 'temporary_login_links_premium_user', true );
                $link = $this->get_link_details( $link_id );
                
                if ( $link ) {
                    $status_class = $this->get_link_status_class( $link );
                    $status_text = $this->get_link_status_text( $link );
                    
                    $output = '<span class="tlp-status tlp-status-' . esc_attr( $status_class ) . '">' . esc_html( $status_text ) . '</span>';
                    
                    // Add expiry date
                    if ( ! empty( $link['expiry'] ) ) {
                        $output .= '<br><span class="tlp-expiry">' . __( 'Expires: ', 'temporary-login-links-premium' ) . date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $link['expiry'] ) ) . '</span>';
                    }
                    
                    // Add view link button
                    $output .= '<br><a href="' . admin_url( 'admin.php?page=temporary-login-links-premium-links&action=view&id=' . $link_id ) . '" class="button-link">' . __( 'View Link', 'temporary-login-links-premium' ) . '</a>';
                } else {
                    $output = '<span class="tlp-status tlp-status-error">' . __( 'Orphaned', 'temporary-login-links-premium' ) . '</span>';
                    
                    // Delete orphaned user meta
                    delete_user_meta( $user_id, 'temporary_login_links_premium_user' );
                }
            } else {
                $output = '—';
            }
        }
        
        return $output;
    }

    /**
     * Get the status class for a link.
     *
     * @since    1.0.0
     * @param    array     $link    The link data.
     * @return   string             The status class.
     */
    private function get_link_status_class( $link ) {
        if ( empty( $link['is_active'] ) ) {
            return 'inactive';
        }
        
        if ( strtotime( $link['expiry'] ) < time() ) {
            return 'expired';
        }
        
        return 'active';
    }

    /**
     * Get the status text for a link.
     *
     * @since    1.0.0
     * @param    array     $link    The link data.
     * @return   string             The status text.
     */
    private function get_link_status_text( $link ) {
        if ( empty( $link['is_active'] ) ) {
            return __( 'Inactive', 'temporary-login-links-premium' );
        }
        
        if ( strtotime( $link['expiry'] ) < time() ) {
            return __( 'Expired', 'temporary-login-links-premium' );
        }
        
        return __( 'Active', 'temporary-login-links-premium' );
    }

    /**
     * Add a filter dropdown to the users table.
     *
     * @since    1.0.0
     */
    public function add_temporary_user_filter() {
        if ( ! isset( $_GET['page'] ) ) {
            $temp_user_filter = isset( $_GET['temp_user_filter'] ) ? $_GET['temp_user_filter'] : '';
            ?>
            <label for="temp-user-filter" class="screen-reader-text"><?php _e( 'Filter by temporary user status', 'temporary-login-links-premium' ); ?></label>
            <select name="temp_user_filter" id="temp-user-filter">
                <option value=""><?php _e( 'All users', 'temporary-login-links-premium' ); ?></option>
                <option value="temp" <?php selected( $temp_user_filter, 'temp' ); ?>><?php _e( 'Temporary users only', 'temporary-login-links-premium' ); ?></option>
                <option value="regular" <?php selected( $temp_user_filter, 'regular' ); ?>><?php _e( 'Regular users only', 'temporary-login-links-premium' ); ?></option>
                <option value="active" <?php selected( $temp_user_filter, 'active' ); ?>><?php _e( 'Active temporary users', 'temporary-login-links-premium' ); ?></option>
                <option value="inactive" <?php selected( $temp_user_filter, 'inactive' ); ?>><?php _e( 'Inactive temporary users', 'temporary-login-links-premium' ); ?></option>
                <option value="expired" <?php selected( $temp_user_filter, 'expired' ); ?>><?php _e( 'Expired temporary users', 'temporary-login-links-premium' ); ?></option>
            </select>
            <?php
        }
    }

    /**
     * Filter users based on temporary user status.
     *
     * @since    1.0.0
     * @param    WP_User_Query    $query    The user query.
     */
    public function filter_temporary_users( $query ) {
        if ( ! isset( $_GET['page'] ) && isset( $_GET['temp_user_filter'] ) && ! empty( $_GET['temp_user_filter'] ) ) {
            global $wpdb;
            
            $meta_query = array();
            
            switch ( $_GET['temp_user_filter'] ) {
                case 'temp':
                    // Temporary users only
                    $meta_query[] = array(
                        'key'     => 'temporary_login_links_premium_user',
                        'compare' => 'EXISTS',
                    );
                    break;
                    
                case 'regular':
                    // Regular users only
                    $meta_query[] = array(
                        'key'     => 'temporary_login_links_premium_user',
                        'compare' => 'NOT EXISTS',
                    );
                    break;
                    
                case 'active':
                    // Active temporary users
                    $current_time = current_time( 'mysql' );
                    
                    // Get active link IDs
                    $link_ids = $wpdb->get_col( $wpdb->prepare(
                        "SELECT id FROM {$this->table_name} WHERE is_active = 1 AND expiry > %s",
                        $current_time
                    ) );
                    
                    if ( ! empty( $link_ids ) ) {
                        $meta_query[] = array(
                            'key'     => 'temporary_login_links_premium_user',
                            'value'   => $link_ids,
                            'compare' => 'IN',
                        );
                    } else {
                        // No active links, force no results
                        $meta_query[] = array(
                            'key'     => 'temporary_login_links_premium_user',
                            'value'   => 'none',
                            'compare' => '=',
                        );
                    }
                    break;
                    
                case 'inactive':
                    // Inactive temporary users
                    // Get inactive link IDs
                    $link_ids = $wpdb->get_col(
                        "SELECT id FROM {$this->table_name} WHERE is_active = 0"
                    );
                    
                    if ( ! empty( $link_ids ) ) {
                        $meta_query[] = array(
                            'key'     => 'temporary_login_links_premium_user',
                            'value'   => $link_ids,
                            'compare' => 'IN',
                        );
                    } else {
                        // No inactive links, force no results
                        $meta_query[] = array(
                            'key'     => 'temporary_login_links_premium_user',
                            'value'   => 'none',
                            'compare' => '=',
                        );
                    }
                    break;
                    
                case 'expired':
                    // Expired temporary users
                    $current_time = current_time( 'mysql' );
                    
                    // Get expired link IDs
                    $link_ids = $wpdb->get_col( $wpdb->prepare(
                        "SELECT id FROM {$this->table_name} WHERE expiry <= %s AND is_active = 1",
                        $current_time
                    ) );
                    
                    if ( ! empty( $link_ids ) ) {
                        $meta_query[] = array(
                            'key'     => 'temporary_login_links_premium_user',
                            'value'   => $link_ids,
                            'compare' => 'IN',
                        );
                    } else {
                        // No expired links, force no results
                        $meta_query[] = array(
                            'key'     => 'temporary_login_links_premium_user',
                            'value'   => 'none',
                            'compare' => '=',
                        );
                    }
                    break;
            }
            
            if ( ! empty( $meta_query ) ) {
                // Add our meta query to the user query
                $query->set( 'meta_query', $meta_query );
            }
        }
    }

    /**
     * Filter available roles for temporary users.
     *
     * @since    1.0.0
     * @param    array     $roles    The available roles.
     * @return   array               The filtered roles.
     */
    public function filter_roles_for_temporary_users( $roles ) {
        // Check if we're on a user edit page and it's a temporary user
        if ( isset( $_GET['user_id'] ) && $this->is_temporary_user( intval( $_GET['user_id'] ) ) ) {
            // Get the user's current role
            $user = get_userdata( intval( $_GET['user_id'] ) );
            $current_roles = $user->roles;
            $current_role = reset( $current_roles );
            
            // Only allow the current role
            foreach ( $roles as $role => $details ) {
                if ( $role !== $current_role ) {
                    unset( $roles[ $role ] );
                }
            }
        }
        
        return $roles;
    }

    /**
     * Add bulk actions for temporary users.
     *
     * @since    1.0.0
     * @param    array     $actions    The current bulk actions.
     * @return   array                 The modified bulk actions.
     */
    public function add_bulk_actions( $actions ) {
        $actions['tlp_extend_7days'] = __( 'Extend Temporary Access (7 days)', 'temporary-login-links-premium' );
        $actions['tlp_extend_30days'] = __( 'Extend Temporary Access (30 days)', 'temporary-login-links-premium' );
        $actions['tlp_deactivate'] = __( 'Deactivate Temporary Access', 'temporary-login-links-premium' );
        $actions['tlp_reactivate'] = __( 'Reactivate Temporary Access', 'temporary-login-links-premium' );
        
        return $actions;
    }

    /**
     * Handle bulk actions for temporary users.
     *
     * @since    1.0.0
     * @param    string    $redirect_to    The redirect URL.
     * @param    string    $action         The bulk action.
     * @param    array     $user_ids       The user IDs.
     * @return   string                    The modified redirect URL.
     */
    public function handle_bulk_actions( $redirect_to, $action, $user_ids ) {
        if ( strpos( $action, 'tlp_' ) === 0 ) {
            $processed = 0;
            
            foreach ( $user_ids as $user_id ) {
                if ( $this->is_temporary_user( $user_id ) ) {
                    $link_id = get_user_meta( $user_id, 'temporary_login_links_premium_user', true );
                    
                    if ( $link_id ) {
                        $tlp_links = new TLP_Links();
                        
                        switch ( $action ) {
                            case 'tlp_extend_7days':
                                $result = $tlp_links->extend_link( $link_id, '7 days' );
                                break;
                                
                            case 'tlp_extend_30days':
                                $result = $tlp_links->extend_link( $link_id, '1 month' );
                                break;
                                
                            case 'tlp_deactivate':
                                $result = $tlp_links->update_link_status( $link_id, false );
                                break;
                                
                            case 'tlp_reactivate':
                                $result = $tlp_links->update_link_status( $link_id, true );
                                break;
                                
                            default:
                                $result = false;
                                break;
                        }
                        
                        if ( $result && ! is_wp_error( $result ) ) {
                            $processed++;
                        }
                    }
                }
            }
            
            $redirect_to = add_query_arg( array(
                'tlp_action'  => $action,
                'tlp_processed' => $processed,
                'tlp_total'   => count( $user_ids ),
            ), $redirect_to );
        }
        
        return $redirect_to;
    }

    /**
     * Display admin notice for bulk actions.
     *
     * @since    1.0.0
     */
    public function bulk_action_admin_notice() {
        if ( isset( $_GET['tlp_action'] ) && isset( $_GET['tlp_processed'] ) && isset( $_GET['tlp_total'] ) ) {
            $action = sanitize_text_field( $_GET['tlp_action'] );
            $processed = intval( $_GET['tlp_processed'] );
            $total = intval( $_GET['tlp_total'] );
            
            $message = '';
            
            switch ( $action ) {
                case 'tlp_extend_7days':
                    $message = sprintf( 
                        _n( 'Extended temporary access by 7 days for %d user.', 'Extended temporary access by 7 days for %d users.', $processed, 'temporary-login-links-premium' ),
                        $processed
                    );
                    break;
                    
                case 'tlp_extend_30days':
                    $message = sprintf( 
                        _n( 'Extended temporary access by 30 days for %d user.', 'Extended temporary access by 30 days for %d users.', $processed, 'temporary-login-links-premium' ),
                        $processed
                    );
                    break;
                    
                case 'tlp_deactivate':
                    $message = sprintf( 
                        _n( 'Deactivated temporary access for %d user.', 'Deactivated temporary access for %d users.', $processed, 'temporary-login-links-premium' ),
                        $processed
                    );
                    break;
                    
                case 'tlp_reactivate':
                    $message = sprintf( 
                        _n( 'Reactivated temporary access for %d user.', 'Reactivated temporary access for %d users.', $processed, 'temporary-login-links-premium' ),
                        $processed
                    );
                    break;
            }
            
            if ( $processed < $total ) {
                $message .= ' ' . sprintf( 
                    __( '%d user(s) were skipped because they are not temporary users.', 'temporary-login-links-premium' ),
                    $total - $processed
                );
            }
            
            if ( ! empty( $message ) ) {
                ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php echo esc_html( $message ); ?></p>
                </div>
                <?php
            }
        }
    }

    /**
     * Check if a user is a temporary user.
     *
     * @since    1.0.0
     * @param    int       $user_id    The user ID.
     * @return   bool                  Whether the user is a temporary user.
     */
    public function is_temporary_user( $user_id ) {
        return (bool) get_user_meta( $user_id, 'temporary_login_links_premium_user', true );
    }

    /**
     * Get link details by ID.
     *
     * @since    1.0.0
     * @param    int       $link_id    The link ID.
     * @return   array|bool            The link details or false if not found.
     */
    private function get_link_details( $link_id ) {
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
     * Add capability to administrators.
     *
     * @since    1.0.0
     */
    public static function add_capabilities() {
        $role = get_role( 'administrator' );
        
        if ( $role ) {
            $role->add_cap( 'manage_temporary_logins' );
        }
    }

    /**
     * Remove capability from administrators.
     *
     * @since    1.0.0
     */
    public static function remove_capabilities() {
        $role = get_role( 'administrator' );
        
        if ( $role ) {
            $role->remove_cap( 'manage_temporary_logins' );
        }
    }
}
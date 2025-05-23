<?php
/**
 * Template for the view link page.
 *
 * This file provides the HTML for viewing a temporary login link's details,
 * including link information, user details, and access logs.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Temporary_Login_Links_Premium
 * @subpackage Temporary_Login_Links_Premium/admin/partials
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>

<div class="wrap tlp-wrap">
    <h1>
        <?php echo esc_html__('View Temporary Login Link', 'temporary-login-links-premium'); ?>
        <a href="<?php echo esc_url(admin_url('admin.php?page=temporary-login-links-premium-links&action=edit&id=' . absint($link_id))); ?>" class="page-title-action"><?php echo esc_html__('Edit', 'temporary-login-links-premium'); ?></a>
    </h1>
    
    <?php 
    // Display notification for new link
    if ($is_new) : 
    ?>
    <div class="notice notice-success is-dismissible">
        <p><?php echo esc_html__('Temporary login link created successfully!', 'temporary-login-links-premium'); ?></p>
    </div>
    <?php endif; ?>
    
    <div class="tlp-view-link">
        <!-- Header Actions -->
        <div class="tlp-view-link-header">
            <div class="tlp-user-info">
                <h2><?php echo esc_html($link['user_email']); ?></h2>
                <p><?php 
                        /* translators: Role */
                        echo esc_html(sprintf(__('Role: %s', 'temporary-login-links-premium'), $this->get_role_display_name($link['role'])));
                    ?>
                </p>
            </div>
            
            <div class="tlp-actions">
                <?php if ($link['is_active'] == 1) : ?>
                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=temporary-login-links-premium-links&action=deactivate&id=' . absint($link_id)), 'tlp_deactivate_link')); ?>" class="button tlp-deactivate-link"><?php echo esc_html__('Deactivate', 'temporary-login-links-premium'); ?></a>
                <?php else : ?>
                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=temporary-login-links-premium-links&action=activate&id=' . absint($link_id)), 'tlp_activate_link')); ?>" class="button"><?php echo esc_html__('Activate', 'temporary-login-links-premium'); ?></a>
                <?php endif; ?>
                
                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=temporary-login-links-premium-links&action=delete&id=' . absint($link_id)), 'tlp_delete_link')); ?>" class="button button-link-delete tlp-delete-link"><?php echo esc_html__('Delete', 'temporary-login-links-premium'); ?></a>
            </div>
        </div>
        
        <!-- Login URL -->
        <div class="tlp-view-link-url">
            <p class="tlp-login-url"><?php echo esc_url($login_url); ?></p>
            <button type="button" class="button tlp-copy-button" data-copy="<?php echo esc_attr($login_url); ?>">
                <span class="dashicons dashicons-clipboard"></span> <?php echo esc_html__('Copy', 'temporary-login-links-premium'); ?>
            </button>
            <span class="tlp-copy-success" style="display: none;"><?php echo esc_html__('Copied!', 'temporary-login-links-premium'); ?></span>
        </div>
        
        <!-- Link Details -->
        <div class="tlp-view-link-details">
            <h3><?php echo esc_html__('Link Details', 'temporary-login-links-premium'); ?></h3>
            
            <table class="tlp-view-link-details-table">
                <tr>
                    <th><?php echo esc_html__('Status', 'temporary-login-links-premium'); ?></th>
                    <td>
                        <?php 
                        if ($link['is_active'] == 0) {
                            echo '<span class="tlp-status tlp-status-inactive">' . esc_html__('Inactive', 'temporary-login-links-premium') . '</span>';
                        } elseif (strtotime($link['expiry']) < time()) {
                            echo '<span class="tlp-status tlp-status-expired">' . esc_html__('Expired', 'temporary-login-links-premium') . '</span>';
                        } else {
                            echo '<span class="tlp-status tlp-status-active">' . esc_html__('Active', 'temporary-login-links-premium') . '</span>';
                        }
                        ?>
                    </td>
                </tr>
                
                <tr>
                    <th><?php echo esc_html__('Expiry Date', 'temporary-login-links-premium'); ?></th>
                    <td>
                        <?php 
                        // Show only the date part
                        echo esc_html(date_i18n(get_option('date_format'), strtotime($link['expiry'])));

                        // Show time remaining if not expired
                        if (strtotime($link['expiry']) > time()) {
                            $time_diff = strtotime($link['expiry']) - time();
                            $days = floor($time_diff / (60 * 60 * 24));

                            echo ' (';
                            /* translators: Days */
                            echo esc_html(sprintf(_n('%d day', '%d days', $days, 'temporary-login-links-premium'), $days));
                            echo ' ' . esc_html__('remaining', 'temporary-login-links-premium') . ')';
                        }
                        ?>
                    </td>
                </tr>
                
                <tr>
                    <th><?php echo esc_html__('Created', 'temporary-login-links-premium'); ?></th>
                    <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($link['created_at']))); ?></td>
                </tr>
                
                <tr>
                    <th><?php echo esc_html__('Created By', 'temporary-login-links-premium'); ?></th>
                    <td>
                        <?php 
                        $created_by_user = get_userdata($link['created_by']);
                        echo $created_by_user ? esc_html($created_by_user->display_name) : esc_html__('Unknown', 'temporary-login-links-premium');
                        ?>
                    </td>
                </tr>
                
                <tr>
                    <th><?php echo esc_html__('Access Count', 'temporary-login-links-premium'); ?></th>
                    <td>
                        <?php 
                        echo esc_html(absint($link['access_count']));
                        
                        if ($link['max_accesses'] > 0) {
                            echo ' / ' . esc_html(absint($link['max_accesses']));
                        }
                        ?>
                    </td>
                </tr>
                
                <tr>
                    <th><?php echo esc_html__('Last Accessed', 'temporary-login-links-premium'); ?></th>
                    <td>
                        <?php 
                        if (!empty($link['last_accessed'])) {
                            echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($link['last_accessed'])));
                        } else {
                            echo esc_html__('Never', 'temporary-login-links-premium');
                        }
                        ?>
                    </td>
                </tr>
                
                <tr>
                    <th><?php echo esc_html__('Redirect URL', 'temporary-login-links-premium'); ?></th>
                    <td><?php echo esc_url($link['redirect_to']); ?></td>
                </tr>
                
                <tr>
                    <th><?php echo esc_html__('IP Restriction', 'temporary-login-links-premium'); ?></th>
                    <td>
                        <?php 
                        if (!empty($link['ip_restriction'])) {
                            echo esc_html($link['ip_restriction']);
                        } else {
                            echo esc_html__('None', 'temporary-login-links-premium');
                        }
                        ?>
                    </td>
                </tr>
                
                <tr>
                    <th><?php echo esc_html__('User', 'temporary-login-links-premium'); ?></th>
                    <td>
                        <?php 
                        if ($user) {
                            echo sprintf(
                                '<a href="%s">%s</a>',
                                esc_url(admin_url('user-edit.php?user_id=' . absint($user->ID))),
                                esc_html($user->display_name . ' (' . $user->user_login . ')')
                            );
                        } else {
                            echo esc_html__('User not found', 'temporary-login-links-premium');
                        }
                        ?>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Actions Section -->
        <div class="tlp-view-link-actions">
            <h3><?php echo esc_html__('Actions', 'temporary-login-links-premium'); ?></h3>
            
            <div class="tlp-action-buttons">
                <button type="button" class="button tlp-copy-button" data-copy="<?php echo esc_attr($login_url); ?>">
                    <span class="dashicons dashicons-clipboard"></span> <?php echo esc_html__('Copy Link', 'temporary-login-links-premium'); ?>
                </button>
                
                <button type="button" class="button tlp-resend-email" data-id="<?php echo absint($link_id); ?>">
                    <span class="dashicons dashicons-email"></span> <?php echo esc_html__('Resend Email', 'temporary-login-links-premium'); ?>
                </button>
                
                <button type="button" class="button tlp-extend-link" data-id="<?php echo absint($link_id); ?>">
                    <span class="dashicons dashicons-calendar-alt"></span> <?php echo esc_html__('Extend Expiry', 'temporary-login-links-premium'); ?>
                </button>
            </div>
        </div>
        
        <!-- Access Logs -->
        <div class="tlp-access-logs">
            <h3><?php echo esc_html__('Access Logs', 'temporary-login-links-premium'); ?></h3>
            
            <?php if (!empty($logs['items'])) : ?>
            <table class="widefat">
                <thead>
                    <tr>
                        <th><?php echo esc_html__('Time', 'temporary-login-links-premium'); ?></th>
                        <th><?php echo esc_html__('IP Address', 'temporary-login-links-premium'); ?></th>
                        <th><?php echo esc_html__('Status', 'temporary-login-links-premium'); ?></th>
                        <th><?php echo esc_html__('Notes', 'temporary-login-links-premium'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs['items'] as $log) : ?>
                    <tr>
                        <td>
                            <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($log['accessed_at']))); ?>
                        </td>
                        <td><?php echo esc_html($log['user_ip']); ?></td>
                        <td>
                            <?php 
                            switch ($log['status']) {
                                case 'success':
                                    echo '<span class="tlp-status tlp-status-active">' . esc_html__('Success', 'temporary-login-links-premium') . '</span>';
                                    break;
                                    
                                case 'expired':
                                    echo '<span class="tlp-status tlp-status-expired">' . esc_html__('Expired', 'temporary-login-links-premium') . '</span>';
                                    break;
                                    
                                case 'inactive':
                                    echo '<span class="tlp-status tlp-status-inactive">' . esc_html__('Inactive', 'temporary-login-links-premium') . '</span>';
                                    break;
                                    
                                case 'ip_restricted':
                                    echo '<span class="tlp-status tlp-status-expired">' . esc_html__('IP Restricted', 'temporary-login-links-premium') . '</span>';
                                    break;
                                    
                                case 'max_accesses':
                                    echo '<span class="tlp-status tlp-status-expired">' . esc_html__('Max Accesses', 'temporary-login-links-premium') . '</span>';
                                    break;

                                case 'extended':
                                    echo '<span class="tlp-status tlp-status-active">' . esc_html__('Extended', 'temporary-login-links-premium') . '</span>';
                                    break;
                                    
                                case 'activated':
                                    echo '<span class="tlp-status tlp-status-active">' . esc_html__('Activated', 'temporary-login-links-premium') . '</span>';
                                    break;
                                    
                                case 'deactivated':
                                    echo '<span class="tlp-status tlp-status-inactive">' . esc_html__('Deactivated', 'temporary-login-links-premium') . '</span>';
                                    break;
                                    
                                default:
                                    echo esc_html($log['status']);
                                    break;
                            }
                            ?>
                        </td>
                        <td>
                            <?php 
                            echo !empty($log['notes']) ? esc_html($log['notes']) : '';
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <?php if ($logs['total_items'] > $logs['per_page']) : ?>
            <div class="tlp-pagination">
                <?php
                $total_pages = ceil($logs['total_items'] / $logs['per_page']);
                $current_page = $logs['page'];
                
                for ($i = 1; $i <= $total_pages; $i++) {
                    if ($i == $current_page) {
                        echo '<span class="page-numbers current">' . absint($i) . '</span>';
                    } else {
                        echo '<a href="' . esc_url(add_query_arg(array('page' => 'temporary-login-links-premium-links', 'action' => 'view', 'id' => absint($link_id), 'log_page' => absint($i)), admin_url('admin.php'))) . '" class="page-numbers">' . absint($i) . '</a>';
                    }
                }
                ?>
            </div>
            <?php endif; ?>
            
            <?php else : ?>
            <div class="tlp-empty-logs">
                <?php echo esc_html__('No access logs found.', 'temporary-login-links-premium'); ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Extend Modal -->
<div class="tlp-modal-backdrop" style="display: none;"></div>
<div id="tlp-extend-modal" class="tlp-modal" style="display: none;">
    <div class="tlp-modal-header">
        <h3><?php echo esc_html__('Extend Expiry Date', 'temporary-login-links-premium'); ?></h3>
        <span class="tlp-modal-close dashicons dashicons-no-alt"></span>
    </div>
    <div class="tlp-modal-content">
        <p><?php echo esc_html__('Choose how long to extend the expiry date:', 'temporary-login-links-premium'); ?></p>
        
        <select id="tlp-extend-duration" class="regular-text">
            <option value="1 day"><?php echo esc_html__('1 Day', 'temporary-login-links-premium'); ?></option>
            <option value="3 days"><?php echo esc_html__('3 Days', 'temporary-login-links-premium'); ?></option>
            <option value="7 days" selected><?php echo esc_html__('7 Days', 'temporary-login-links-premium'); ?></option>
            <option value="14 days"><?php echo esc_html__('14 Days', 'temporary-login-links-premium'); ?></option>
            <option value="1 month"><?php echo esc_html__('1 Month', 'temporary-login-links-premium'); ?></option>
            <option value="3 months"><?php echo esc_html__('3 Months', 'temporary-login-links-premium'); ?></option>
            <option value="6 months"><?php echo esc_html__('6 Months', 'temporary-login-links-premium'); ?></option>
            <option value="1 year"><?php echo esc_html__('1 Year', 'temporary-login-links-premium'); ?></option>
        </select>
    </div>
    <div class="tlp-modal-footer">
        <button type="button" class="button tlp-modal-cancel"><?php echo esc_html__('Cancel', 'temporary-login-links-premium'); ?></button>
        <button type="button" class="button button-primary tlp-extend-link-submit" data-id="<?php echo absint($link_id); ?>"><?php echo esc_html__('Extend', 'temporary-login-links-premium'); ?></button>
    </div>
</div>
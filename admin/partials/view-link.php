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
        <?php _e('View Temporary Login Link', 'temporary-login-links-premium'); ?>
        <a href="<?php echo esc_url(admin_url('admin.php?page=temporary-login-links-premium-links&action=edit&id=' . $link_id)); ?>" class="page-title-action"><?php _e('Edit', 'temporary-login-links-premium'); ?></a>
    </h1>
    
    <?php 
    // Display notification for new link
    if ($is_new) : 
    ?>
    <div class="notice notice-success is-dismissible">
        <p><?php _e('Temporary login link created successfully!', 'temporary-login-links-premium'); ?></p>
    </div>
    <?php endif; ?>
    
    <div class="tlp-view-link">
        <!-- Header Actions -->
        <div class="tlp-view-link-header">
            <div class="tlp-user-info">
                <h2><?php echo esc_html($link['user_email']); ?></h2>
                <p><?php printf(__('Role: %s', 'temporary-login-links-premium'), $this->get_role_display_name($link['role'])); ?></p>
            </div>
            
            <div class="tlp-actions">
                <?php if ($link['is_active'] == 1) : ?>
                <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=temporary-login-links-premium-links&action=deactivate&id=' . $link_id), 'tlp_deactivate_link'); ?>" class="button tlp-deactivate-link"><?php _e('Deactivate', 'temporary-login-links-premium'); ?></a>
                <?php else : ?>
                <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=temporary-login-links-premium-links&action=activate&id=' . $link_id), 'tlp_activate_link'); ?>" class="button"><?php _e('Activate', 'temporary-login-links-premium'); ?></a>
                <?php endif; ?>
                
                <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=temporary-login-links-premium-links&action=extend&id=' . $link_id . '&duration=7+days'), 'tlp_extend_link'); ?>" class="button"><?php _e('Extend', 'temporary-login-links-premium'); ?></a>
                
                <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=temporary-login-links-premium-links&action=delete&id=' . $link_id), 'tlp_delete_link'); ?>" class="button button-link-delete tlp-delete-link"><?php _e('Delete', 'temporary-login-links-premium'); ?></a>
            </div>
        </div>
        
        <!-- Login URL -->
        <div class="tlp-view-link-url">
            <p class="tlp-login-url"><?php echo esc_url($login_url); ?></p>
            <button type="button" class="button tlp-copy-button" data-copy="<?php echo esc_url($login_url); ?>">
                <span class="dashicons dashicons-clipboard"></span> <?php _e('Copy', 'temporary-login-links-premium'); ?>
            </button>
            <span class="tlp-copy-success" style="display: none;"><?php _e('Copied!', 'temporary-login-links-premium'); ?></span>
        </div>
        
        <!-- Link Details -->
        <div class="tlp-view-link-details">
            <h3><?php _e('Link Details', 'temporary-login-links-premium'); ?></h3>
            
            <table class="tlp-view-link-details-table">
                <tr>
                    <th><?php _e('Status', 'temporary-login-links-premium'); ?></th>
                    <td>
                        <?php 
                        if ($link['is_active'] == 0) {
                            echo '<span class="tlp-status tlp-status-inactive">' . __('Inactive', 'temporary-login-links-premium') . '</span>';
                        } elseif (strtotime($link['expiry']) < time()) {
                            echo '<span class="tlp-status tlp-status-expired">' . __('Expired', 'temporary-login-links-premium') . '</span>';
                        } else {
                            echo '<span class="tlp-status tlp-status-active">' . __('Active', 'temporary-login-links-premium') . '</span>';
                        }
                        ?>
                    </td>
                </tr>
                
                <tr>
                    <th><?php _e('Expiry Date', 'temporary-login-links-premium'); ?></th>
                    <td>
                        <?php 
                        echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($link['expiry']));
                        
                        // Show time remaining if not expired
                        if (strtotime($link['expiry']) > time()) {
                            $time_diff = strtotime($link['expiry']) - time();
                            $days = floor($time_diff / (60 * 60 * 24));
                            $hours = floor(($time_diff % (60 * 60 * 24)) / (60 * 60));
                            
                            echo ' (';
                            if ($days > 0) {
                                echo sprintf(_n('%d day', '%d days', $days, 'temporary-login-links-premium'), $days);
                                
                                if ($hours > 0) {
                                    echo ', ';
                                }
                            }
                            
                            if ($hours > 0) {
                                echo sprintf(_n('%d hour', '%d hours', $hours, 'temporary-login-links-premium'), $hours);
                            }
                            echo ' ' . __('remaining', 'temporary-login-links-premium') . ')';
                        }
                        ?>
                    </td>
                </tr>
                
                <tr>
                    <th><?php _e('Created', 'temporary-login-links-premium'); ?></th>
                    <td><?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($link['created_at'])); ?></td>
                </tr>
                
                <tr>
                    <th><?php _e('Created By', 'temporary-login-links-premium'); ?></th>
                    <td>
                        <?php 
                        $created_by_user = get_userdata($link['created_by']);
                        echo $created_by_user ? esc_html($created_by_user->display_name) : __('Unknown', 'temporary-login-links-premium');
                        ?>
                    </td>
                </tr>
                
                <tr>
                    <th><?php _e('Access Count', 'temporary-login-links-premium'); ?></th>
                    <td>
                        <?php 
                        echo $link['access_count'];
                        
                        if ($link['max_accesses'] > 0) {
                            echo ' / ' . $link['max_accesses'];
                        }
                        ?>
                    </td>
                </tr>
                
                <tr>
                    <th><?php _e('Last Accessed', 'temporary-login-links-premium'); ?></th>
                    <td>
                        <?php 
                        if (!empty($link['last_accessed'])) {
                            echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($link['last_accessed']));
                        } else {
                            echo __('Never', 'temporary-login-links-premium');
                        }
                        ?>
                    </td>
                </tr>
                
                <tr>
                    <th><?php _e('Redirect URL', 'temporary-login-links-premium'); ?></th>
                    <td><?php echo esc_url($link['redirect_to']); ?></td>
                </tr>
                
                <tr>
                    <th><?php _e('IP Restriction', 'temporary-login-links-premium'); ?></th>
                    <td>
                        <?php 
                        if (!empty($link['ip_restriction'])) {
                            echo esc_html($link['ip_restriction']);
                        } else {
                            echo __('None', 'temporary-login-links-premium');
                        }
                        ?>
                    </td>
                </tr>
                
                <tr>
                    <th><?php _e('User', 'temporary-login-links-premium'); ?></th>
                    <td>
                        <?php 
                        if ($user) {
                            echo sprintf(
                                '<a href="%s">%s</a>',
                                esc_url(admin_url('user-edit.php?user_id=' . $user->ID)),
                                esc_html($user->display_name . ' (' . $user->user_login . ')')
                            );
                        } else {
                            echo __('User not found', 'temporary-login-links-premium');
                        }
                        ?>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Actions Section -->
        <div class="tlp-view-link-actions">
            <h3><?php _e('Actions', 'temporary-login-links-premium'); ?></h3>
            
            <div class="tlp-action-buttons">
                <button type="button" class="button tlp-copy-button" data-copy="<?php echo esc_url($login_url); ?>">
                    <span class="dashicons dashicons-clipboard"></span> <?php _e('Copy Link', 'temporary-login-links-premium'); ?>
                </button>
                
                <button type="button" class="button tlp-resend-email" data-id="<?php echo $link_id; ?>">
                    <span class="dashicons dashicons-email"></span> <?php _e('Resend Email', 'temporary-login-links-premium'); ?>
                </button>
                
                <button type="button" class="button tlp-extend-link" data-id="<?php echo $link_id; ?>">
                    <span class="dashicons dashicons-calendar-alt"></span> <?php _e('Extend Expiry', 'temporary-login-links-premium'); ?>
                </button>
            </div>
        </div>
        
        <!-- Access Logs -->
        <div class="tlp-access-logs">
            <h3><?php _e('Access Logs', 'temporary-login-links-premium'); ?></h3>
            
            <?php if (!empty($logs['items'])) : ?>
            <table class="widefat">
                <thead>
                    <tr>
                        <th><?php _e('Time', 'temporary-login-links-premium'); ?></th>
                        <th><?php _e('IP Address', 'temporary-login-links-premium'); ?></th>
                        <th><?php _e('Status', 'temporary-login-links-premium'); ?></th>
                        <th><?php _e('Notes', 'temporary-login-links-premium'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs['items'] as $log) : ?>
                    <tr>
                        <td>
                            <?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($log['accessed_at'])); ?>
                        </td>
                        <td><?php echo esc_html($log['user_ip']); ?></td>
                        <td>
                            <?php 
                            switch ($log['status']) {
                                case 'success':
                                    echo '<span class="tlp-status tlp-status-active">' . __('Success', 'temporary-login-links-premium') . '</span>';
                                    break;
                                    
                                case 'expired':
                                    echo '<span class="tlp-status tlp-status-expired">' . __('Expired', 'temporary-login-links-premium') . '</span>';
                                    break;
                                    
                                case 'inactive':
                                    echo '<span class="tlp-status tlp-status-inactive">' . __('Inactive', 'temporary-login-links-premium') . '</span>';
                                    break;
                                    
                                case 'ip_restricted':
                                    echo '<span class="tlp-status tlp-status-expired">' . __('IP Restricted', 'temporary-login-links-premium') . '</span>';
                                    break;
                                    
                                case 'max_accesses':
                                    echo '<span class="tlp-status tlp-status-expired">' . __('Max Accesses', 'temporary-login-links-premium') . '</span>';
                                    break;

                                case 'extended':
                                    echo '<span class="tlp-status tlp-status-active">' . __('Extended', 'temporary-login-links-premium') . '</span>';
                                    break;
                                    
                                case 'activated':
                                    echo '<span class="tlp-status tlp-status-active">' . __('Activated', 'temporary-login-links-premium') . '</span>';
                                    break;
                                    
                                case 'deactivated':
                                    echo '<span class="tlp-status tlp-status-inactive">' . __('Deactivated', 'temporary-login-links-premium') . '</span>';
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
                        echo '<span class="page-numbers current">' . $i . '</span>';
                    } else {
                        echo '<a href="' . esc_url(add_query_arg(array('page' => 'temporary-login-links-premium-links', 'action' => 'view', 'id' => $link_id, 'log_page' => $i), admin_url('admin.php'))) . '" class="page-numbers">' . $i . '</a>';
                    }
                }
                ?>
            </div>
            <?php endif; ?>
            
            <?php else : ?>
            <div class="tlp-empty-logs">
                <?php _e('No access logs found.', 'temporary-login-links-premium'); ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Extend Modal -->
<div class="tlp-modal-backdrop" style="display: none;"></div>
<div id="tlp-extend-modal" class="tlp-modal" style="display: none;">
    <div class="tlp-modal-header">
        <h3><?php _e('Extend Expiry Date', 'temporary-login-links-premium'); ?></h3>
        <span class="tlp-modal-close dashicons dashicons-no-alt"></span>
    </div>
    <div class="tlp-modal-content">
        <p><?php _e('Choose how long to extend the expiry date:', 'temporary-login-links-premium'); ?></p>
        
        <select id="tlp-extend-duration" class="regular-text">
            <option value="1 day"><?php _e('1 Day', 'temporary-login-links-premium'); ?></option>
            <option value="3 days"><?php _e('3 Days', 'temporary-login-links-premium'); ?></option>
            <option value="7 days" selected><?php _e('7 Days', 'temporary-login-links-premium'); ?></option>
            <option value="14 days"><?php _e('14 Days', 'temporary-login-links-premium'); ?></option>
            <option value="1 month"><?php _e('1 Month', 'temporary-login-links-premium'); ?></option>
            <option value="3 months"><?php _e('3 Months', 'temporary-login-links-premium'); ?></option>
            <option value="6 months"><?php _e('6 Months', 'temporary-login-links-premium'); ?></option>
            <option value="1 year"><?php _e('1 Year', 'temporary-login-links-premium'); ?></option>
        </select>
    </div>
    <div class="tlp-modal-footer">
        <button type="button" class="button tlp-modal-cancel"><?php _e('Cancel', 'temporary-login-links-premium'); ?></button>
        <button type="button" class="button button-primary tlp-extend-link-submit" data-id="<?php echo $link_id; ?>"><?php _e('Extend', 'temporary-login-links-premium'); ?></button>
    </div>
</div>
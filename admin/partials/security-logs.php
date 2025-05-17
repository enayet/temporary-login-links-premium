<?php
/**
 * Template for the security logs page.
 *
 * This file provides the HTML for the security logs page,
 * displaying failed login attempts and suspicious activity.
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
    <h1><?php _e('Security Logs', 'temporary-login-links-premium'); ?></h1>
    
    <div class="tlp-security-logs">
        <div class="tlp-log-info">
            <p><?php _e('This page shows all security-related events, including failed login attempts and suspicious activity.', 'temporary-login-links-premium'); ?></p>
        </div>
        
        <?php if (empty($logs['items'])) : ?>
            <div class="notice notice-info">
                <p><?php _e('No security logs found. This is a good sign! It means there have been no failed login attempts or suspicious activities.', 'temporary-login-links-premium'); ?></p>
            </div>
        <?php else : ?>
            <!-- Filters -->
            <div class="tablenav top">
                <div class="alignleft actions">
                    <form method="get">
                        <input type="hidden" name="page" value="temporary-login-links-premium-security">
                        <select name="status">
                            <option value=""><?php _e('All Statuses', 'temporary-login-links-premium'); ?></option>
                            <option value="success" <?php selected(isset($_GET['status']) ? $_GET['status'] : '', 'success'); ?>><?php _e('Success', 'temporary-login-links-premium'); ?></option>
                            <option value="failed" <?php selected(isset($_GET['status']) ? $_GET['status'] : '', 'failed'); ?>><?php _e('Failed', 'temporary-login-links-premium'); ?></option>
                            <option value="expired" <?php selected(isset($_GET['status']) ? $_GET['status'] : '', 'expired'); ?>><?php _e('Expired', 'temporary-login-links-premium'); ?></option>
                            <option value="ip_restricted" <?php selected(isset($_GET['status']) ? $_GET['status'] : '', 'ip_restricted'); ?>><?php _e('IP Restricted', 'temporary-login-links-premium'); ?></option>
                        </select>
                        <?php submit_button(__('Filter', 'temporary-login-links-premium'), 'action', 'filter', false); ?>
                    </form>
                </div>
                
                <?php if ($logs['total_items'] > 0) : ?>
                    <div class="tablenav-pages">
                        <span class="displaying-num">
                            <?php printf(
                                _n('%s item', '%s items', $logs['total_items'], 'temporary-login-links-premium'),
                                number_format_i18n($logs['total_items'])
                            ); ?>
                        </span>
                        <?php
                        echo paginate_links(array(
                            'base' => add_query_arg('paged', '%#%'),
                            'format' => '',
                            'prev_text' => '&laquo;',
                            'next_text' => '&raquo;',
                            'total' => ceil($logs['total_items'] / $logs['per_page']),
                            'current' => $logs['page'],
                        ));
                        ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <table class="wp-list-table widefat fixed striped logs-table">
                <thead>
                    <tr>
                        <th><?php _e('Time', 'temporary-login-links-premium'); ?></th>
                        <th><?php _e('IP Address', 'temporary-login-links-premium'); ?></th>
                        <th><?php _e('Email', 'temporary-login-links-premium'); ?></th>
                        <th><?php _e('Status', 'temporary-login-links-premium'); ?></th>
                        <th><?php _e('Notes', 'temporary-login-links-premium'); ?></th>
                        <th><?php _e('User Agent', 'temporary-login-links-premium'); ?></th>
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
                                <?php if (isset($log['user_email']) && !empty($log['user_email'])) : ?>
                                    <?php echo esc_html($log['user_email']); ?>
                                <?php else : ?>
                                    <em><?php _e('Not available', 'temporary-login-links-premium'); ?></em>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $status_class = '';
                                $status_text = '';
                                
                                switch ($log['status']) {
                                    case 'success':
                                        $status_class = 'success';
                                        $status_text = __('Success', 'temporary-login-links-premium');
                                        break;
                                    case 'invalid_token':
                                        $status_class = 'error';
                                        $status_text = __('Invalid Token', 'temporary-login-links-premium');
                                        break;
                                    case 'expired':
                                        $status_class = 'error';
                                        $status_text = __('Expired', 'temporary-login-links-premium');
                                        break;
                                    case 'inactive':
                                        $status_class = 'error';
                                        $status_text = __('Inactive', 'temporary-login-links-premium');
                                        break;
                                    case 'ip_restricted':
                                        $status_class = 'error';
                                        $status_text = __('IP Restricted', 'temporary-login-links-premium');
                                        break;
                                    case 'max_accesses':
                                        $status_class = 'error';
                                        $status_text = __('Max Accesses', 'temporary-login-links-premium');
                                        break;
                                    default:
                                        $status_text = $log['status'];
                                        break;
                                }
                                ?>
                                <span class="tlp-log-status tlp-log-status-<?php echo esc_attr($status_class); ?>">
                                    <?php echo esc_html($status_text); ?>
                                </span>
                            </td>
                            <td>
                                <?php echo isset($log['notes']) ? esc_html($log['notes']) : ''; ?>
                            </td>
                            <td>
                                <span class="tlp-truncate" title="<?php echo esc_attr($log['user_agent']); ?>">
                                    <?php echo esc_html(substr($log['user_agent'], 0, 50) . (strlen($log['user_agent']) > 50 ? '...' : '')); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th><?php _e('Time', 'temporary-login-links-premium'); ?></th>
                        <th><?php _e('IP Address', 'temporary-login-links-premium'); ?></th>
                        <th><?php _e('Email', 'temporary-login-links-premium'); ?></th>
                        <th><?php _e('Status', 'temporary-login-links-premium'); ?></th>
                        <th><?php _e('Notes', 'temporary-login-links-premium'); ?></th>
                        <th><?php _e('User Agent', 'temporary-login-links-premium'); ?></th>
                    </tr>
                </tfoot>
            </table>
            
            <div class="tablenav bottom">
                <?php if ($logs['total_items'] > 0) : ?>
                    <div class="tablenav-pages">
                        <span class="displaying-num">
                            <?php printf(
                                _n('%s item', '%s items', $logs['total_items'], 'temporary-login-links-premium'),
                                number_format_i18n($logs['total_items'])
                            ); ?>
                        </span>
                        <?php
                        echo paginate_links(array(
                            'base' => add_query_arg('paged', '%#%'),
                            'format' => '',
                            'prev_text' => '&laquo;',
                            'next_text' => '&raquo;',
                            'total' => ceil($logs['total_items'] / $logs['per_page']),
                            'current' => $logs['page'],
                        ));
                        ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <!-- Security Settings Link -->
        <div class="tlp-security-settings-link">
            <a href="<?php echo esc_url(admin_url('admin.php?page=temporary-login-links-premium-settings&tab=security')); ?>" class="button">
                <?php _e('Security Settings', 'temporary-login-links-premium'); ?>
            </a>
        </div>
    </div>
</div>

<style>
.tlp-security-logs {
    margin-top: 20px;
}

.tlp-log-info {
    margin-bottom: 20px;
    border-left: 4px solid #0073aa;
    padding: 10px 15px;
    background: #f8f8f8;
}

.tlp-log-status {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 12px;
    font-weight: 500;
}

.tlp-log-status-success {
    background-color: #dff2e0;
    color: #2a8b32;
}

.tlp-log-status-error {
    background-color: #fbe9e7;
    color: #c62828;
}

.tlp-truncate {
    display: inline-block;
    max-width: 200px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.tlp-security-settings-link {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}
</style>
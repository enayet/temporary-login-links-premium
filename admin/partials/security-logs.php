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

<?php 
// Display messages
if (isset($_GET['cleared']) && $_GET['cleared'] == 1) {
    echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Security logs have been cleared successfully.', 'temporary-login-links-premium') . '</p></div>';
}
?>

<div class="wrap tlp-wrap">
    <h1><?php echo esc_html__('Security Logs', 'temporary-login-links-premium'); ?></h1>
    
    <div class="tlp-security-logs">
        <div class="tlp-log-info">
            <p><?php echo esc_html__('This page shows security-related events, including failed login attempts, blocked IPs, and suspicious activity.', 'temporary-login-links-premium'); ?></p>
        </div>
        
        <?php if (empty($logs['items'])) : ?>
            <div class="notice notice-info">
                <p><?php echo esc_html__('No security logs found. This is a good sign! It means there have been no failed login attempts or suspicious activities.', 'temporary-login-links-premium'); ?></p>
            </div>
        <?php else : ?>
            <!-- Filters -->
            <div class="tablenav top">
                <div class="alignleft actions">
                    <form method="get">
                        <input type="hidden" name="page" value="temporary-login-links-premium-security">
                        <select name="status">
                            <option value=""><?php echo esc_html__('All Statuses', 'temporary-login-links-premium'); ?></option>
                            <option value="failed" <?php selected(isset($_GET['status']) ? $_GET['status'] : '', 'failed'); ?>><?php echo esc_html__('Failed Attempts', 'temporary-login-links-premium'); ?></option>
                            <option value="blocked" <?php selected(isset($_GET['status']) ? $_GET['status'] : '', 'blocked'); ?>><?php echo esc_html__('Blocked IPs', 'temporary-login-links-premium'); ?></option>
                        </select>
                        
                        <input type="text" name="search" placeholder="<?php esc_attr_e('Search logs...', 'temporary-login-links-premium'); ?>" value="<?php echo isset($_GET['search']) ? esc_attr($_GET['search']) : ''; ?>">
                        
                        <span class="tlp-date-range">
                            <input type="text" name="start_date" id="start_date" class="tlp-datepicker" placeholder="<?php esc_attr_e('From date', 'temporary-login-links-premium'); ?>" value="<?php echo isset($_GET['start_date']) ? esc_attr($_GET['start_date']) : ''; ?>">
                            <input type="text" name="end_date" id="end_date" class="tlp-datepicker" placeholder="<?php esc_attr_e('To date', 'temporary-login-links-premium'); ?>" value="<?php echo isset($_GET['end_date']) ? esc_attr($_GET['end_date']) : ''; ?>">
                        </span>
                        
                        <?php submit_button(__('Filter', 'temporary-login-links-premium'), 'action', 'filter', false); ?>
                        
                        <?php if (isset($_GET['status']) || isset($_GET['search']) || isset($_GET['start_date'])): ?>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=temporary-login-links-premium-security')); ?>" class="button"><?php echo esc_html__('Reset', 'temporary-login-links-premium'); ?></a>
                        <?php endif; ?>
                    </form>
                </div>
                
                <?php if ($logs['total_items'] > 0) : ?>
                    <div class="tablenav-pages">
                        <span class="displaying-num">
                            <?php
                                /* translators: %s Items */    
                                echo esc_html(sprintf(_n('%d item', '%d items', $logs['total_items'], 'temporary-login-links-premium'), number_format_i18n($logs['total_items'])));   
                            ?>
                        </span>
                        <?php
                        echo wp_kses_post(paginate_links(array(
                            'base' => add_query_arg('paged', '%#%'),
                            'format' => '',
                            'prev_text' => '&laquo;',
                            'next_text' => '&raquo;',
                            'total' => ceil($logs['total_items'] / $logs['per_page']),
                            'current' => $logs['page'],
                        )));
                        ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <table class="wp-list-table widefat fixed striped logs-table">
                <thead>
                    <tr>
                        <th width="15%"><?php echo esc_html__('Time', 'temporary-login-links-premium'); ?></th>
                        <th width="12%"><?php echo esc_html__('IP Address', 'temporary-login-links-premium'); ?></th>
                        <th width="15%"><?php echo esc_html__('Token', 'temporary-login-links-premium'); ?></th>
                        <th width="15%"><?php echo esc_html__('Email', 'temporary-login-links-premium'); ?></th>
                        <th width="10%"><?php echo esc_html__('Status', 'temporary-login-links-premium'); ?></th>
                        <th width="20%"><?php echo esc_html__('Reason', 'temporary-login-links-premium'); ?></th>
                        <th width="13%"><?php echo esc_html__('User Agent', 'temporary-login-links-premium'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs['items'] as $log) : ?>
                        <tr>
                            <td>
                                <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($log['logged_at']))); ?>
                            </td>
                            <td><?php echo esc_html($log['user_ip']); ?></td>
                            <td><?php echo esc_html($log['token_fragment']); ?></td>
                            <td>
                                <?php if (!empty($log['user_email'])) : ?>
                                    <?php echo esc_html($log['user_email']); ?>
                                <?php else : ?>
                                    <em><?php echo esc_html__('Unknown', 'temporary-login-links-premium'); ?></em>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $status_class = '';
                                
                                switch ($log['status']) {
                                    case 'failed':
                                        $status_class = 'error';
                                        $status_text = __('Failed', 'temporary-login-links-premium');
                                        break;
                                    case 'blocked':
                                        $status_class = 'error';
                                        $status_text = __('Blocked', 'temporary-login-links-premium');
                                        break;
                                    default:
                                        $status_class = 'info';
                                        $status_text = ucfirst($log['status']);
                                        break;
                                }
                                ?>
                                <span class="tlp-log-status tlp-log-status-<?php echo esc_attr($status_class); ?>">
                                    <?php echo esc_html($status_text); ?>
                                </span>
                            </td>
                            <td>
                                <?php echo !empty($log['reason']) ? esc_html($log['reason']) : ''; ?>
                            </td>
                            <td>
                                <span class="tlp-truncate" title="<?php echo esc_attr($log['user_agent']); ?>">
                                    <?php 
                                    $user_agent = $log['user_agent'];
                                    echo esc_html(strlen($user_agent) > 30 ? substr($user_agent, 0, 27) . '...' : $user_agent); 
                                    ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th><?php echo esc_html__('Time', 'temporary-login-links-premium'); ?></th>
                        <th><?php echo esc_html__('IP Address', 'temporary-login-links-premium'); ?></th>
                        <th><?php echo esc_html__('Token', 'temporary-login-links-premium'); ?></th>
                        <th><?php echo esc_html__('Email', 'temporary-login-links-premium'); ?></th>
                        <th><?php echo esc_html__('Status', 'temporary-login-links-premium'); ?></th>
                        <th><?php echo esc_html__('Reason', 'temporary-login-links-premium'); ?></th>
                        <th><?php echo esc_html__('User Agent', 'temporary-login-links-premium'); ?></th>
                    </tr>
                </tfoot>
            </table>
            
            <div class="tablenav bottom">
                <?php if ($logs['total_items'] > 0) : ?>
                    <div class="tablenav-pages">
                        <span class="displaying-num">
                           
                            <?php 
                            
                                /* translators: %s Items */                                
                                echo esc_html(sprintf(_n('%d item', '%d items', $logs['total_items'], 'temporary-login-links-premium'), number_format_i18n($logs['total_items']))); 
                            
                            ?>
                        </span>
                        <?php
                        echo wp_kses_post(paginate_links(array(
                            'base' => add_query_arg('paged', '%#%'),
                            'format' => '',
                            'prev_text' => '&laquo;',
                            'next_text' => '&raquo;',
                            'total' => ceil($logs['total_items'] / $logs['per_page']),
                            'current' => $logs['page'],
                        )));
                        ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <!-- Security Settings Link -->
        <div class="tlp-security-settings-link">
            <a href="<?php echo esc_url(admin_url('admin.php?page=temporary-login-links-premium-settings&tab=security')); ?>" class="button">
                <?php echo esc_html__('Security Settings', 'temporary-login-links-premium'); ?>
            </a>
            
            <?php if (!empty($logs['items'])) : ?>
                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=temporary-login-links-premium-security&action=clear_logs'), 'tlp_clear_security_logs')); ?>" class="button button-link-delete" onclick="return confirm('<?php esc_attr_e('Are you sure you want to clear all security logs? This action cannot be undone.', 'temporary-login-links-premium'); ?>');">
                    <?php echo esc_html__('Clear All Logs', 'temporary-login-links-premium'); ?>
                </a>
            <?php endif; ?>
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

.tlp-log-status-error {
    background-color: #fbe9e7;
    color: #c62828;
}

.tlp-log-status-info {
    background-color: #e8f4fd;
    color: #0277bd;
}

.tlp-truncate {
    display: inline-block;
    max-width: 100%;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.tlp-security-settings-link {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #eee;
    display: flex;
    justify-content: space-between;
}

.tlp-date-range {
    display: inline-block;
    margin: 0 5px;
}

.tlp-date-range input[type="text"] {
    width: 110px;
}
</style>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Initialize datepickers
    if ($.fn.datepicker) {
        $('.tlp-datepicker').datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true,
            maxDate: 0
        });
    }
});
</script>
<?php
/**
 * Template for the access logs page.
 *
 * This file provides the HTML for displaying all access logs,
 * with filtering, searching, and sorting capabilities.
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
    <h1><?php _e('Access Logs', 'temporary-login-links-premium'); ?></h1>
    
    <div class="tlp-access-logs-page">
        <div class="tlp-log-info">
            <p><?php _e('This page shows all access attempts to your site using temporary login links.', 'temporary-login-links-premium'); ?></p>
        </div>
        
        <?php if (empty($logs['items'])) : ?>
            <div class="notice notice-info">
                <p><?php _e('No access logs found. This means no one has attempted to use any temporary login links yet.', 'temporary-login-links-premium'); ?></p>
            </div>
        <?php else : ?>
            <!-- Filters -->
            <div class="tablenav top">
                <div class="alignleft actions">
                    <form method="get">
                        <input type="hidden" name="page" value="temporary-login-links-premium-access-logs">
                        <select name="status">
                            <option value=""><?php _e('All Statuses', 'temporary-login-links-premium'); ?></option>
                            <option value="success" <?php selected(isset($_GET['status']) ? $_GET['status'] : '', 'success'); ?>><?php _e('Successful Logins', 'temporary-login-links-premium'); ?></option>
                            <option value="failed" <?php selected(isset($_GET['status']) ? $_GET['status'] : '', 'failed'); ?>><?php _e('Failed Attempts', 'temporary-login-links-premium'); ?></option>
                        </select>
                        
                        <input type="text" name="search" placeholder="<?php esc_attr_e('Search logs...', 'temporary-login-links-premium'); ?>" value="<?php echo isset($_GET['search']) ? esc_attr($_GET['search']) : ''; ?>">
                        
                        <span class="tlp-date-range">
                            <input type="text" name="start_date" id="start_date" class="tlp-datepicker" placeholder="<?php esc_attr_e('From date', 'temporary-login-links-premium'); ?>" value="<?php echo isset($_GET['start_date']) ? esc_attr($_GET['start_date']) : ''; ?>">
                            <input type="text" name="end_date" id="end_date" class="tlp-datepicker" placeholder="<?php esc_attr_e('To date', 'temporary-login-links-premium'); ?>" value="<?php echo isset($_GET['end_date']) ? esc_attr($_GET['end_date']) : ''; ?>">
                        </span>
                        
                        <?php submit_button(__('Filter', 'temporary-login-links-premium'), 'action', 'filter', false); ?>
                        
                        <?php if (isset($_GET['status']) || isset($_GET['search']) || isset($_GET['start_date'])): ?>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=temporary-login-links-premium-access-logs')); ?>" class="button"><?php _e('Reset', 'temporary-login-links-premium'); ?></a>
                        <?php endif; ?>
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
                        <th width="15%"><?php _e('Time', 'temporary-login-links-premium'); ?></th>
                        <th width="20%"><?php _e('Email', 'temporary-login-links-premium'); ?></th>
                        <th width="12%"><?php _e('IP Address', 'temporary-login-links-premium'); ?></th>
                        <th width="10%"><?php _e('Status', 'temporary-login-links-premium'); ?></th>
                        <th width="15%"><?php _e('Notes', 'temporary-login-links-premium'); ?></th>
                        <th width="13%"><?php _e('User Agent', 'temporary-login-links-premium'); ?></th>
                        <th width="15%"><?php _e('Actions', 'temporary-login-links-premium'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs['items'] as $log) : ?>
                        <tr>
                            <td>
                                <?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($log['accessed_at'])); ?>
                            </td>
                            <td>
                                <?php 
                                $link = $this->links->get_link($log['link_id']);
                                if ($link) {
                                    echo esc_html($link['user_email']);
                                } else {
                                    echo '<em>' . __('Deleted Link', 'temporary-login-links-premium') . '</em>';
                                }
                                ?>
                            </td>
                            <td><?php echo esc_html($log['user_ip']); ?></td>
                            <td>
                                <?php
                                $status_class = '';
                                
                                switch ($log['status']) {
                                    case 'success':
                                        $status_class = 'active';
                                        $status_text = __('Success', 'temporary-login-links-premium');
                                        break;
                                    case 'expired':
                                        $status_class = 'expired';
                                        $status_text = __('Expired', 'temporary-login-links-premium');
                                        break;
                                    case 'inactive':
                                        $status_class = 'inactive';
                                        $status_text = __('Inactive', 'temporary-login-links-premium');
                                        break;
                                    case 'max_accesses':
                                        $status_class = 'error';
                                        $status_text = __('Max Access', 'temporary-login-links-premium');
                                        break;
                                    case 'ip_restricted':
                                        $status_class = 'error';
                                        $status_text = __('IP Restricted', 'temporary-login-links-premium');
                                        break;
                                    default:
                                        $status_class = 'info';
                                        $status_text = ucfirst($log['status']);
                                        break;
                                }
                                ?>
                                <span class="tlp-status tlp-status-<?php echo esc_attr($status_class); ?>">
                                    <?php echo esc_html($status_text); ?>
                                </span>
                            </td>
                            <td>
                                <?php echo !empty($log['notes']) ? esc_html($log['notes']) : ''; ?>
                            </td>
                            <td>
                                <span class="tlp-truncate" title="<?php echo esc_attr($log['user_agent']); ?>">
                                    <?php 
                                    $user_agent = $log['user_agent'];
                                    echo esc_html(strlen($user_agent) > 30 ? substr($user_agent, 0, 27) . '...' : $user_agent); 
                                    ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($link) : ?>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=temporary-login-links-premium-links&action=view&id=' . $log['link_id'])); ?>" class="button button-small">
                                        <?php _e('View Link', 'temporary-login-links-premium'); ?>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th><?php _e('Time', 'temporary-login-links-premium'); ?></th>
                        <th><?php _e('Email', 'temporary-login-links-premium'); ?></th>
                        <th><?php _e('IP Address', 'temporary-login-links-premium'); ?></th>
                        <th><?php _e('Status', 'temporary-login-links-premium'); ?></th>
                        <th><?php _e('Notes', 'temporary-login-links-premium'); ?></th>
                        <th><?php _e('User Agent', 'temporary-login-links-premium'); ?></th>
                        <th><?php _e('Actions', 'temporary-login-links-premium'); ?></th>
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
    </div>
</div>

<style>
/* Styles for the access logs page */
.tlp-access-logs-page {
    margin-top: 20px;
}

.tlp-log-info {
    margin-bottom: 20px;
    border-left: 4px solid #0073aa;
    padding: 10px 15px;
    background: #f8f8f8;
}

.tlp-status {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 12px;
    font-weight: 500;
}

.tlp-status-active {
    background-color: #dff2e0;
    color: #2a8b32;
}

.tlp-status-inactive {
    background-color: #f1f1f1;
    color: #777;
}

.tlp-status-expired {
    background-color: #fbe9e7;
    color: #c62828;
}

.tlp-status-error {
    background-color: #fbe9e7;
    color: #c62828;
}

.tlp-status-info {
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
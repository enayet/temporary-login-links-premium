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
    <h1><?php esc_html_e('Access Logs', 'temporary-login-links-premium'); ?></h1>
    
    <div class="tlp-access-logs-page">
        <div class="tlp-log-info">
            <p><?php esc_html_e('This page shows all access attempts to your site using temporary login links.', 'temporary-login-links-premium'); ?></p>
        </div>
        
        <!-- Display success message for deletion -->
        <?php 
        if (isset($_GET['cleared']) && $_GET['cleared'] == 1) {
            $count = isset($_GET['count']) ? intval($_GET['count']) : 0;
            echo '<div class="notice notice-success is-dismissible"><p>' . 
                /* translators: %d no of log entry  */   
                sprintf(esc_html(_n('%d log entry has been deleted successfully.', '%d log entries have been deleted successfully.', $count, 'temporary-login-links-premium')), esc_html($count)) . '</p></div>';
        }
        ?>
        
        <!-- Always show filters -->
        <div class="tablenav top">
            <div class="alignleft actions">
                <form method="get">
                    <input type="hidden" name="page" value="temporary-login-links-premium-access-logs">
                    <select name="status">
                        <option value=""><?php esc_html_e('All Statuses', 'temporary-login-links-premium'); ?></option>
                        <option value="success" <?php selected(isset($_GET['status']) ? $_GET['status'] : '', 'success'); ?>><?php esc_html_e('Successful Logins', 'temporary-login-links-premium'); ?></option>
                        <option value="expired" <?php selected(isset($_GET['status']) ? $_GET['status'] : '', 'expired'); ?>><?php esc_html_e('Expired', 'temporary-login-links-premium'); ?></option>
                        <option value="inactive" <?php selected(isset($_GET['status']) ? $_GET['status'] : '', 'inactive'); ?>><?php esc_html_e('Inactive', 'temporary-login-links-premium'); ?></option>
                        <option value="max_accesses" <?php selected(isset($_GET['status']) ? $_GET['status'] : '', 'max_accesses'); ?>><?php esc_html_e('Max Accesses Reached', 'temporary-login-links-premium'); ?></option>
                        <option value="ip_restricted" <?php selected(isset($_GET['status']) ? $_GET['status'] : '', 'ip_restricted'); ?>><?php esc_html_e('IP Restricted', 'temporary-login-links-premium'); ?></option>
                        <option value="activated" <?php selected(isset($_GET['status']) ? $_GET['status'] : '', 'activated'); ?>><?php esc_html_e('Activated', 'temporary-login-links-premium'); ?></option>
                        <option value="deactivated" <?php selected(isset($_GET['status']) ? $_GET['status'] : '', 'deactivated'); ?>><?php esc_html_e('Deactivated', 'temporary-login-links-premium'); ?></option>
                        <option value="extended" <?php selected(isset($_GET['status']) ? $_GET['status'] : '', 'extended'); ?>><?php esc_html_e('Extended', 'temporary-login-links-premium'); ?></option>
                        <option value="failed" <?php selected(isset($_GET['status']) ? $_GET['status'] : '', 'failed'); ?>><?php esc_html_e('Failed Attempts', 'temporary-login-links-premium'); ?></option>
                    </select>
                    
                    <input type="text" name="search" placeholder="<?php esc_attr_e('Search logs...', 'temporary-login-links-premium'); ?>" value="<?php echo isset($_GET['search']) ? esc_attr($_GET['search']) : ''; ?>">
                    

                    <span class="tlp-date-range">
                        <input type="date" name="start_date" id="start_date" value="<?php echo isset($_GET['start_date']) ? esc_attr($_GET['start_date']) : ''; ?>">
                        <input type="date" name="end_date" id="end_date" value="<?php echo isset($_GET['end_date']) ? esc_attr($_GET['end_date']) : ''; ?>">
                    </span>
                    
                    <?php submit_button(esc_html__('Filter', 'temporary-login-links-premium'), 'action', 'filter', false); ?>
                    
                    <?php if (isset($_GET['status']) || isset($_GET['search']) || isset($_GET['start_date']) || isset($_GET['end_date'])): ?>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=temporary-login-links-premium-access-logs')); ?>" class="button"><?php esc_html_e('Reset', 'temporary-login-links-premium'); ?></a>
                    <?php endif; ?>
                    
                    <?php if ($logs['total_items'] > 0): ?>
                        <?php 
                        // Create the clear URL with current filters
                        $clear_url = wp_nonce_url(
                            add_query_arg(
                                array_merge(
                                    array('action' => 'clear_logs'),
                                    isset($_GET['status']) ? array('status' => $_GET['status']) : array(),
                                    isset($_GET['search']) ? array('search' => $_GET['search']) : array(),
                                    isset($_GET['start_date']) ? array('start_date' => $_GET['start_date']) : array(),
                                    isset($_GET['end_date']) ? array('end_date' => $_GET['end_date']) : array()
                                ),
                                admin_url('admin.php?page=temporary-login-links-premium-access-logs')
                            ),
                            'tlp_clear_access_logs'
                        );
                        ?>
                        <a href="<?php echo esc_url($clear_url); ?>" class="button button-link-delete" onclick="return confirm('<?php esc_attr_e('Are you sure you want to delete these logs? This action cannot be undone.', 'temporary-login-links-premium'); ?>');"><?php esc_html_e('Delete Filtered Logs', 'temporary-login-links-premium'); ?></a>
                    <?php endif; ?>
                </form>
            </div>
            
            <?php if ($logs['total_items'] > 0) : ?>
                <div class="tablenav-pages">
                    <span class="displaying-num">
                        <?php
                            /* translators: %s items  */   
                            printf(esc_html(_n('%s item', '%s items', $logs['total_items'], 'temporary-login-links-premium')), esc_html(number_format_i18n($logs['total_items']))); 
                        ?>
                    </span>
                    <?php
                    
                    $pagination = paginate_links(array(
                        'base' => add_query_arg('paged', '%#%'),
                        'format' => '',
                        'prev_text' => '&laquo;',
                        'next_text' => '&raquo;',
                        'total' => ceil($logs['total_items'] / $logs['per_page']),
                        'current' => $logs['page'],
                    ));
                    echo $pagination ? wp_kses_post($pagination) : '';                    
                    ?>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if (empty($logs['items'])) : ?>
            <div class="notice notice-info">
                <p>
                <?php 
                // Different message based on whether filters are active
                if (isset($_GET['status']) && !empty($_GET['status']) || 
                    isset($_GET['search']) && !empty($_GET['search']) || 
                    isset($_GET['start_date']) && !empty($_GET['start_date']) || 
                    isset($_GET['end_date']) && !empty($_GET['end_date'])) {
                    esc_html_e('No access logs found matching your filter criteria.', 'temporary-login-links-premium');
                } else {
                    esc_html_e('No access logs found. This means no one has attempted to use any temporary login links yet.', 'temporary-login-links-premium');
                }
                ?>
                </p>
            </div>
        <?php else : ?>
            <table class="wp-list-table widefat fixed striped logs-table">
                <thead>
                    <tr>
                        <th width="15%"><?php esc_html_e('Time', 'temporary-login-links-premium'); ?></th>
                        <th width="20%"><?php esc_html_e('Email', 'temporary-login-links-premium'); ?></th>
                        <th width="12%"><?php esc_html_e('IP Address', 'temporary-login-links-premium'); ?></th>
                        <th width="10%"><?php esc_html_e('Status', 'temporary-login-links-premium'); ?></th>
                        <th width="15%"><?php esc_html_e('Notes', 'temporary-login-links-premium'); ?></th>
                        <th width="13%"><?php esc_html_e('User Agent', 'temporary-login-links-premium'); ?></th>
                        <th width="15%"><?php esc_html_e('Actions', 'temporary-login-links-premium'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs['items'] as $log) : ?>
                        <tr>
                            <td>
                                <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($log['accessed_at']))); ?>
                            </td>
                            <td>
                                <?php 
                                if (!empty($log['user_email'])) {
                                    echo esc_html($log['user_email']);
                                } elseif ($link = $this->links->get_link($log['link_id'])) {
                                    echo esc_html($link['user_email']);
                                } else {
                                    echo '<em>' . esc_html__('Deleted Link', 'temporary-login-links-premium') . '</em>';
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
                                        $status_text = esc_html__('Success', 'temporary-login-links-premium');
                                        break;
                                    case 'expired':
                                        $status_class = 'expired';
                                        $status_text = esc_html__('Expired', 'temporary-login-links-premium');
                                        break;
                                    case 'inactive':
                                        $status_class = 'inactive';
                                        $status_text = esc_html__('Inactive', 'temporary-login-links-premium');
                                        break;
                                    case 'max_accesses':
                                        $status_class = 'error';
                                        $status_text = esc_html__('Max Access', 'temporary-login-links-premium');
                                        break;
                                    case 'ip_restricted':
                                        $status_class = 'error';
                                        $status_text = esc_html__('IP Restricted', 'temporary-login-links-premium');
                                        break;
                                    case 'activated':
                                        $status_class = 'active';
                                        $status_text = esc_html__('Activated', 'temporary-login-links-premium');
                                        break;
                                    case 'deactivated':
                                        $status_class = 'inactive';
                                        $status_text = esc_html__('Deactivated', 'temporary-login-links-premium');
                                        break;
                                    case 'extended':
                                        $status_class = 'active';
                                        $status_text = esc_html__('Extended', 'temporary-login-links-premium');
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
                                <?php if ($this->links->get_link($log['link_id'])) : ?>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=temporary-login-links-premium-links&action=view&id=' . $log['link_id'])); ?>" class="button button-small">
                                        <?php esc_html_e('View Link', 'temporary-login-links-premium'); ?>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th><?php esc_html_e('Time', 'temporary-login-links-premium'); ?></th>
                        <th><?php esc_html_e('Email', 'temporary-login-links-premium'); ?></th>
                        <th><?php esc_html_e('IP Address', 'temporary-login-links-premium'); ?></th>
                        <th><?php esc_html_e('Status', 'temporary-login-links-premium'); ?></th>
                        <th><?php esc_html_e('Notes', 'temporary-login-links-premium'); ?></th>
                        <th><?php esc_html_e('User Agent', 'temporary-login-links-premium'); ?></th>
                        <th><?php esc_html_e('Actions', 'temporary-login-links-premium'); ?></th>
                    </tr>
                </tfoot>
            </table>
            
            <div class="tablenav bottom">
                <?php if ($logs['total_items'] > 0) : ?>
                    <div class="tablenav-pages">
                        <span class="displaying-num">
                            <?php
                                /* translators: %s items  */   
                                printf(esc_html(_n('%s item', '%s items', $logs['total_items'], 'temporary-login-links-premium')), esc_html(number_format_i18n($logs['total_items']))); 
                            ?>
                        </span>
                        <?php
                        
                        $pagination = paginate_links(array(
                            'base' => add_query_arg('paged', '%#%'),
                            'format' => '',
                            'prev_text' => '&laquo;',
                            'next_text' => '&raquo;',
                            'total' => ceil($logs['total_items'] / $logs['per_page']),
                            'current' => $logs['page'],
                        ));
                        echo $pagination ? wp_kses_post($pagination) : '';                        
                        ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
/**
 * Template for the main admin dashboard page.
 *
 * This file provides the HTML for the plugin's main dashboard page,
 * showing statistics, recently created links, and quick actions.
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
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <?php
    // Display welcome message if this is the first visit after activation
    if (isset($_GET['welcome']) && $_GET['welcome'] == 1) :
    ?>
    <div class="notice notice-success is-dismissible">
        <p><?php _e('Thank you for installing Temporary Login Links Premium! This dashboard gives you an overview of your temporary links.', 'temporary-login-links-premium'); ?></p>
    </div>
    <?php endif; ?>

    <div class="tlp-dashboard">
        <!-- Stats Widgets -->
        <div class="tlp-dashboard-widgets">
            <div class="tlp-stats-widget tlp-active">
                <h3><?php _e('Active Links', 'temporary-login-links-premium'); ?></h3>
                <div class="tlp-big-number"><?php echo esc_html($stats['active_links']); ?></div>
            </div>
            
            <div class="tlp-stats-widget tlp-inactive">
                <h3><?php _e('Expired Links', 'temporary-login-links-premium'); ?></h3>
                <div class="tlp-big-number"><?php echo esc_html($stats['expired_links']); ?></div>
            </div>
            
            <div class="tlp-stats-widget tlp-total">
                <h3><?php _e('Total Accesses', 'temporary-login-links-premium'); ?></h3>
                <div class="tlp-big-number"><?php echo esc_html($stats['total_accesses']); ?></div>
            </div>
        </div>

        <!-- Call to Action -->
        <div class="tlp-dashboard-cta">
            <a href="<?php echo esc_url(admin_url('admin.php?page=temporary-login-links-premium-links&action=create')); ?>" class="button button-primary">
                <?php _e('Create New Temporary Login Link', 'temporary-login-links-premium'); ?>
            </a>
        </div>

        <!-- Dashboard Tables -->
        <div class="tlp-dashboard-tables">
            <!-- Recent Links -->
            <div class="tlp-dashboard-table">
                <h3><?php _e('Recently Created Links', 'temporary-login-links-premium'); ?></h3>
                
                <?php if (!empty($stats['recent_links'])) : ?>
                <table>
                    <thead>
                        <tr>
                            <th><?php _e('Email', 'temporary-login-links-premium'); ?></th>
                            <th><?php _e('Role', 'temporary-login-links-premium'); ?></th>
                            <th><?php _e('Created', 'temporary-login-links-premium'); ?></th>
                            <th><?php _e('Actions', 'temporary-login-links-premium'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stats['recent_links'] as $link) : ?>
                        <tr>
                            <td>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=temporary-login-links-premium-links&action=view&id=' . $link->id)); ?>">
                                    <?php echo esc_html($link->user_email); ?>
                                </a>
                            </td>
                            <td><?php echo esc_html($this->get_role_display_name($link->role)); ?></td>
                            <td><?php echo esc_html(human_time_diff(strtotime($link->created_at), current_time('timestamp')) . ' ' . __('ago', 'temporary-login-links-premium')); ?></td>
                            <td>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=temporary-login-links-premium-links&action=view&id=' . $link->id)); ?>" class="button button-small">
                                    <?php _e('View', 'temporary-login-links-premium'); ?>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else : ?>
                <div class="tlp-empty-table">
                    <?php _e('No links created yet.', 'temporary-login-links-premium'); ?>
                </div>
                <?php endif; ?>
                
                <div class="tlp-table-footer">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=temporary-login-links-premium-links')); ?>" class="button">
                        <?php _e('View All Links', 'temporary-login-links-premium'); ?>
                    </a>
                </div>
            </div>

            <!-- Links Expiring Soon -->
            <div class="tlp-dashboard-table">
                <h3><?php _e('Links Expiring Soon', 'temporary-login-links-premium'); ?></h3>
                
                <?php if (!empty($stats['expiring_soon'])) : ?>
                <table>
                    <thead>
                        <tr>
                            <th><?php _e('Email', 'temporary-login-links-premium'); ?></th>
                            <th><?php _e('Expires', 'temporary-login-links-premium'); ?></th>
                            <th><?php _e('Actions', 'temporary-login-links-premium'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stats['expiring_soon'] as $link) : ?>
                        <tr>
                            <td>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=temporary-login-links-premium-links&action=view&id=' . $link->id)); ?>">
                                    <?php echo esc_html($link->user_email); ?>
                                </a>
                            </td>
                            <td>
                                <?php 
                                $expiry_time = strtotime($link->expiry);
                                $current_time = current_time('timestamp');
                                $time_diff = $expiry_time - $current_time;
                                
                                if ($time_diff < 86400) { // Less than a day
                                    $hours = floor($time_diff / 3600);
                                    $minutes = floor(($time_diff % 3600) / 60);
                                    
                                    echo sprintf(
                                        _n('%d hour', '%d hours', $hours, 'temporary-login-links-premium'),
                                        $hours
                                    );
                                    
                                    if ($minutes > 0) {
                                        echo ' ' . sprintf(
                                            _n('%d minute', '%d minutes', $minutes, 'temporary-login-links-premium'),
                                            $minutes
                                        );
                                    }
                                } else { // More than a day
                                    $days = floor($time_diff / 86400);
                                    
                                    echo sprintf(
                                        _n('%d day', '%d days', $days, 'temporary-login-links-premium'),
                                        $days
                                    );
                                }
                                ?>
                            </td>
                            <td>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=temporary-login-links-premium-links&action=extend&id=' . $link->id . '&duration=7+days'), 'tlp_extend_link')); ?>" class="button button-small">
                                    <?php _e('Extend', 'temporary-login-links-premium'); ?>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else : ?>
                <div class="tlp-empty-table">
                    <?php _e('No links expiring soon.', 'temporary-login-links-premium'); ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Access Logs -->
        <div class="tlp-dashboard-table tlp-mb-20">
            <h3><?php _e('Recent Access Activity', 'temporary-login-links-premium'); ?></h3>
            
            <?php if (!empty($stats['recent_accesses'])) : ?>
            <table>
                <thead>
                    <tr>
                        <th><?php _e('Email', 'temporary-login-links-premium'); ?></th>
                        <th><?php _e('Time', 'temporary-login-links-premium'); ?></th>
                        <th><?php _e('IP Address', 'temporary-login-links-premium'); ?></th>
                        <th><?php _e('Status', 'temporary-login-links-premium'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stats['recent_accesses'] as $access) : ?>
                    <tr>
                        <td>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=temporary-login-links-premium-links&action=view&id=' . $access->id)); ?>">
                                <?php echo esc_html($access->user_email); ?>
                            </a>
                        </td>
                        <td><?php echo esc_html(human_time_diff(strtotime($access->accessed_at), current_time('timestamp')) . ' ' . __('ago', 'temporary-login-links-premium')); ?></td>
                        <td><?php echo esc_html($access->user_ip); ?></td>
                        <td>
                            <?php
                            switch ($access->status) {
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
                                    
                                default:
                                    echo '<span class="tlp-status tlp-status-expired">' . esc_html($access->status) . '</span>';
                                    break;
                            }
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else : ?>
            <div class="tlp-empty-table">
                <?php _e('No recent access activity.', 'temporary-login-links-premium'); ?>
            </div>
            <?php endif; ?>
            
            <div class="tlp-table-footer">
                <a href="<?php echo esc_url(admin_url('admin.php?page=temporary-login-links-premium-security')); ?>" class="button">
                    <?php _e('View Security Logs', 'temporary-login-links-premium'); ?>
                </a>
            </div>
        </div>

        <!-- Feature Highlights -->
        <div class="tlp-feature-highlights">
            <h3><?php _e('Premium Features', 'temporary-login-links-premium'); ?></h3>
            
            <div class="tlp-dashboard-widgets">
                <div class="tlp-feature-box">
                    <h4><span class="dashicons dashicons-shield"></span> <?php _e('IP Restrictions', 'temporary-login-links-premium'); ?></h4>
                    <p><?php _e('Limit access to specific IP addresses for enhanced security.', 'temporary-login-links-premium'); ?></p>
                </div>
                
                <div class="tlp-feature-box">
                    <h4><span class="dashicons dashicons-admin-appearance"></span> <?php _e('Branded Login Pages', 'temporary-login-links-premium'); ?></h4>
                    <p><?php _e('Customize the login page with your logo and colors.', 'temporary-login-links-premium'); ?></p>
                </div>
                
                <div class="tlp-feature-box">
                    <h4><span class="dashicons dashicons-lock"></span> <?php _e('Usage Limits', 'temporary-login-links-premium'); ?></h4>
                    <p><?php _e('Set maximum number of times a link can be used.', 'temporary-login-links-premium'); ?></p>
                </div>
                
                <div class="tlp-feature-box">
                    <h4><span class="dashicons dashicons-chart-line"></span> <?php _e('Detailed Access Logs', 'temporary-login-links-premium'); ?></h4>
                    <p><?php _e('Track who accessed your site and when.', 'temporary-login-links-premium'); ?></p>
                </div>
            </div>
            
            <div class="tlp-feature-footer">
                <a href="<?php echo esc_url(admin_url('admin.php?page=temporary-login-links-premium-branding')); ?>" class="button button-secondary">
                    <?php _e('Configure Branding', 'temporary-login-links-premium'); ?>
                </a>
                
                <a href="<?php echo esc_url(admin_url('admin.php?page=temporary-login-links-premium-settings')); ?>" class="button button-secondary">
                    <?php _e('Adjust Settings', 'temporary-login-links-premium'); ?>
                </a>
            </div>
        </div>
    </div>
</div>
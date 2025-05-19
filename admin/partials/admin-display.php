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
        <p><?php echo esc_html__('Thank you for installing Temporary Login Links Premium! This dashboard gives you an overview of your temporary links.', 'temporary-login-links-premium'); ?></p>
    </div>
    <?php endif; ?>

    <div class="tlp-dashboard">
        <!-- Stats Widgets -->
        <div class="tlp-dashboard-widgets">
            <div class="tlp-stats-widget tlp-active">
                <h3><?php echo esc_html__('Active Links', 'temporary-login-links-premium'); ?></h3>
                <div class="tlp-big-number"><?php echo esc_html($stats['active_links']); ?></div>
            </div>
            
            <div class="tlp-stats-widget tlp-inactive">
                <h3><?php echo esc_html__('Expired Links', 'temporary-login-links-premium'); ?></h3>
                <div class="tlp-big-number"><?php echo esc_html($stats['expired_links']); ?></div>
            </div>
            
            <div class="tlp-stats-widget tlp-total">
                <h3><?php echo esc_html__('Total Accesses', 'temporary-login-links-premium'); ?></h3>
                <div class="tlp-big-number"><?php echo esc_html($stats['total_accesses']); ?></div>
            </div>
        </div>

        <!-- Call to Action -->
        <div class="tlp-dashboard-cta">
            <a href="<?php echo esc_url(admin_url('admin.php?page=temporary-login-links-premium-links&action=create')); ?>" class="button button-primary">
                <?php echo esc_html__('Create New Temporary Login Link', 'temporary-login-links-premium'); ?>
            </a>
        </div>

        <!-- Dashboard Tables -->
        <div class="tlp-dashboard-tables">
            <!-- Recent Links -->
            <div class="tlp-dashboard-table">
                <h3><?php echo esc_html__('Recently Created Links', 'temporary-login-links-premium'); ?></h3>
                
                <?php if (!empty($stats['recent_links'])) : ?>
                <div class="tlp-table-container">
                    <table>
                        <thead>
                            <tr>
                                <th><?php echo esc_html__('Email', 'temporary-login-links-premium'); ?></th>
                                <th><?php echo esc_html__('Role', 'temporary-login-links-premium'); ?></th>
                                <th><?php echo esc_html__('Created', 'temporary-login-links-premium'); ?></th>
                                <th><?php echo esc_html__('Actions', 'temporary-login-links-premium'); ?></th>
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
                                <td><?php echo esc_html(human_time_diff(strtotime($link->created_at), current_time('timestamp')) . ' ' . esc_html__('ago', 'temporary-login-links-premium')); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=temporary-login-links-premium-links&action=view&id=' . $link->id)); ?>" class="button button-small">
                                        <?php echo esc_html__('View', 'temporary-login-links-premium'); ?>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else : ?>
                <div class="tlp-empty-table">
                    <?php echo esc_html__('No links created yet.', 'temporary-login-links-premium'); ?>
                </div>
                <?php endif; ?>
                
                <div class="tlp-table-footer">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=temporary-login-links-premium-links')); ?>" class="button">
                        <?php echo esc_html__('View All Links', 'temporary-login-links-premium'); ?>
                    </a>
                </div>
            </div>

            <!-- Links Expiring Soon -->
            <div class="tlp-dashboard-table">
                <h3><?php echo esc_html__('Links Expiring Soon', 'temporary-login-links-premium'); ?></h3>
                
                <?php if (!empty($stats['expiring_soon'])) : ?>
                <div class="tlp-table-container">
                    <table>
                        <thead>
                            <tr>
                                <th><?php echo esc_html__('Email', 'temporary-login-links-premium'); ?></th>
                                <th><?php echo esc_html__('Expires', 'temporary-login-links-premium'); ?></th>
                                <th><?php echo esc_html__('Actions', 'temporary-login-links-premium'); ?></th>
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
                                        
                                        /* translators: %d hours  */                                           
                                        echo esc_html( sprintf( _n( '%d hour', '%d hours', $days, 'temporary-login-links-premium' ), $hours ));
                                        
                                        if ($minutes > 0) {
                                            /* translators: %d minutes  */   
                                            echo esc_html( sprintf( _n( '%d minute', '%d minutes', $days, 'temporary-login-links-premium' ), $minutes ));
                                        }
                                    } else { // More than a day
                                        $days = floor($time_diff / 86400);
                                        
                                        /* translators: %d Days  */    
                                        echo esc_html( sprintf( _n( '%d day', '%d days', $days, 'temporary-login-links-premium' ), $days ));
                                        
                                    }
                                    ?>
                                </td>
                                <td>
                                    <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=temporary-login-links-premium-links&action=extend&id=' . $link->id . '&duration=7+days'), 'tlp_extend_link')); ?>" class="button button-small">
                                        <?php echo esc_html__('Extend', 'temporary-login-links-premium'); ?>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else : ?>
                <div class="tlp-empty-table">
                    <?php echo esc_html__('No links expiring soon.', 'temporary-login-links-premium'); ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Access Logs -->
        <div class="tlp-dashboard-table tlp-mb-20">
            <h3><?php echo esc_html__('Recent Access Activity', 'temporary-login-links-premium'); ?></h3>
            
            <?php if (!empty($stats['recent_accesses'])) : ?>
            <div class="tlp-table-container">
                <table>
                    <thead>
                        <tr>
                            <th><?php echo esc_html__('Email', 'temporary-login-links-premium'); ?></th>
                            <th><?php echo esc_html__('Time', 'temporary-login-links-premium'); ?></th>
                            <th><?php echo esc_html__('IP Address', 'temporary-login-links-premium'); ?></th>
                            <th><?php echo esc_html__('Status', 'temporary-login-links-premium'); ?></th>
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
                            <td><?php echo esc_html(human_time_diff(strtotime($access->accessed_at), current_time('timestamp')) . ' ' . esc_html__('ago', 'temporary-login-links-premium')); ?></td>
                            <td><?php echo esc_html($access->user_ip); ?></td>
                            <td>
                                <?php
                                switch ($access->status) {
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
            </div>
            <?php else : ?>
            <div class="tlp-empty-table">
                <?php echo esc_html__('No recent access activity.', 'temporary-login-links-premium'); ?>
            </div>
            <?php endif; ?>
            
            <div class="tlp-table-footer">
                <a href="<?php echo esc_url(admin_url('admin.php?page=temporary-login-links-premium-security')); ?>" class="button">
                    <?php echo esc_html__('View Security Logs', 'temporary-login-links-premium'); ?>
                </a>
            </div>
        </div>

        <!-- Feature Highlights -->
        <div class="tlp-feature-highlights">
            <h3><?php echo esc_html__('Premium Features', 'temporary-login-links-premium'); ?></h3>
            
            <div class="tlp-dashboard-widgets">
                <div class="tlp-feature-box">
                    <h4><span class="dashicons dashicons-shield"></span> <?php echo esc_html__('IP Restrictions', 'temporary-login-links-premium'); ?></h4>
                    <p><?php echo esc_html__('Limit access to specific IP addresses for enhanced security.', 'temporary-login-links-premium'); ?></p>
                </div>
                
                <div class="tlp-feature-box">
                    <h4><span class="dashicons dashicons-admin-appearance"></span> <?php echo esc_html__('Branded Login Pages', 'temporary-login-links-premium'); ?></h4>
                    <p><?php echo esc_html__('Customize the login page with your logo and colors.', 'temporary-login-links-premium'); ?></p>
                </div>
                
                <div class="tlp-feature-box">
                    <h4><span class="dashicons dashicons-lock"></span> <?php echo esc_html__('Usage Limits', 'temporary-login-links-premium'); ?></h4>
                    <p><?php echo esc_html__('Set maximum number of times a link can be used.', 'temporary-login-links-premium'); ?></p>
                </div>
                
                <div class="tlp-feature-box">
                    <h4><span class="dashicons dashicons-chart-line"></span> <?php esc_html__('Detailed Access Logs', 'temporary-login-links-premium'); ?></h4>
                    <p><?php echo esc_html__('Track who accessed your site and when.', 'temporary-login-links-premium'); ?></p>
                </div>
            </div>
            
            <div class="tlp-feature-footer">
                <a href="<?php echo esc_url(admin_url('admin.php?page=temporary-login-links-premium-branding')); ?>" class="button button-secondary">
                    <?php echo esc_html__('Configure Branding', 'temporary-login-links-premium'); ?>
                </a>
                
                <a href="<?php echo esc_url(admin_url('admin.php?page=temporary-login-links-premium-settings')); ?>" class="button button-secondary">
                    <?php echo esc_html__('Adjust Settings', 'temporary-login-links-premium'); ?>
                </a>
            </div>
        </div>
    </div>
</div>

<style>
/* Dashboard Styles */
.tlp-dashboard {
    margin-top: 20px;
}

/* Stats Widgets */
.tlp-dashboard-widgets {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    margin-bottom: 30px;
}

.tlp-stats-widget {
    background: #fff;
    border: 1px solid #ccd0d4;
    box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);
    min-width: 200px;
    flex: 1;
    padding: 15px;
    box-sizing: border-box;
    text-align: center;
    border-radius: 3px;
}

.tlp-stats-widget h3 {
    margin-top: 0;
    margin-bottom: 10px;
    font-size: 14px;
    color: #23282d;
}

.tlp-stats-widget .tlp-big-number {
    font-size: 32px;
    font-weight: 600;
    color: #23282d;
    line-height: 1.3;
}

.tlp-stats-widget.tlp-active {
    border-top: 3px solid #46b450;
}

.tlp-stats-widget.tlp-inactive {
    border-top: 3px solid #dc3232;
}

.tlp-stats-widget.tlp-total {
    border-top: 3px solid #00a0d2;
}

/* Dashboard Tables */
.tlp-dashboard-tables {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    margin-bottom: 30px;
}

.tlp-dashboard-table {
    background: #fff;
    border: 1px solid #ccd0d4;
    box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);
    flex: 1;
    min-width: 300px;
    box-sizing: border-box;
    border-radius: 3px;
}

.tlp-dashboard-table h3 {
    margin: 0;
    padding: 12px 15px;
    border-bottom: 1px solid #ccd0d4;
    font-size: 14px;
    line-height: 1.4;
    background: #f7f7f7;
}

.tlp-table-container {
    overflow-x: auto;
}

.tlp-dashboard-table table {
    width: 100%;
    border-collapse: collapse;
}

.tlp-dashboard-table th,
.tlp-dashboard-table td {
    padding: 10px 15px;
    text-align: left;
    border-bottom: 1px solid #f0f0f0;
}

.tlp-dashboard-table th {
    font-weight: 600;
}

.tlp-dashboard-table tr:last-child td {
    border-bottom: none;
}

.tlp-dashboard-table .tlp-empty-table {
    padding: 15px;
    text-align: center;
    color: #777;
}

.tlp-dashboard-cta {
    text-align: center;
    margin: 30px 0;
}

.tlp-dashboard-cta .button-primary {
    padding: 6px 25px;
    height: auto;
    line-height: 1.5;
    font-size: 14px;
}

/* Table Footer */
.tlp-table-footer {
    padding: 12px 15px;
    border-top: 1px solid #f0f0f0;
    background: #f7f7f7;
}

/* Feature Boxes */
.tlp-feature-box {
    background: #fff;
    border: 1px solid #ccd0d4;
    box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);
    padding: 15px;
    border-radius: 3px;
    flex: 1;
    min-width: 200px;
}

.tlp-feature-box h4 {
    margin-top: 0;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
}

.tlp-feature-box .dashicons {
    margin-right: 5px;
    color: #0073aa;
}

.tlp-feature-box p {
    margin: 0;
    color: #555;
}

.tlp-feature-footer {
    margin-top: 20px;
    text-align: center;
}

.tlp-feature-footer .button {
    margin: 0 5px;
}

/* Status Indicators */
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

/* Responsive styles */
@media screen and (max-width: 782px) {
    .tlp-dashboard-widgets,
    .tlp-dashboard-tables {
        flex-direction: column;
        gap: 15px;
    }
    
    .tlp-stats-widget,
    .tlp-feature-box {
        min-width: 100%;
    }
}

/* Utility Classes */
.tlp-mb-20 {
    margin-bottom: 20px;
}
</style>

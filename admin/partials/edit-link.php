<?php
/**
 * Template for the edit link form.
 *
 * This file provides the HTML for editing an existing temporary login link,
 * allowing administrators to modify link parameters and user details.
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
    <h1><?php esc_html__('Edit Temporary Login Link', 'temporary-login-links-premium'); ?></h1>
    
    <?php 
    // Display messages
    if (!empty($message)) {
        $message_class = $message_type === 'error' ? 'error' : 'updated';
        echo '<div class="notice ' . esc_attr($message_class) . ' is-dismissible"><p>' . esc_html($message) . '</p></div>';
    }
    ?>
    
    <div class="tlp-form-page">
        <form method="post" action="" id="tlp-edit-link-form">
            <?php wp_nonce_field('tlp_edit_link_nonce'); ?>
            <input type="hidden" name="tlp_edit_link" value="1">
            
            <div class="tlp-view-link-url">
                <p><strong><?php esc_html__('Login URL:', 'temporary-login-links-premium'); ?></strong></p>
                <div class="tlp-login-url"><?php echo esc_url($login_url); ?></div>
                <button type="button" class="button tlp-copy-button" data-copy="<?php echo esc_url($login_url); ?>">
                    <span class="dashicons dashicons-clipboard"></span> <?php esc_html__('Copy', 'temporary-login-links-premium'); ?>
                </button>
                <span class="tlp-copy-success" style="display: none;"><?php esc_html__('Copied!', 'temporary-login-links-premium'); ?></span>
            </div>
            
            <table class="tlp-form-table">
                <tbody>
                    <!-- Email Address (read-only) -->
                    <tr>
                        <th>
                            <label for="user_email"><?php esc_html__('Email Address', 'temporary-login-links-premium'); ?></label>
                        </th>
                        <td>
                            <input type="email" name="user_email" id="user_email" value="<?php echo esc_attr($form_data['user_email']); ?>" class="regular-text" readonly disabled>
                            <p class="description"><?php esc_html__('The email address cannot be changed. To use a different email, create a new link.', 'temporary-login-links-premium'); ?></p>
                        </td>
                    </tr>
                    
                    <!-- First Name -->
                    <tr>
                        <th>
                            <label for="first_name"><?php esc_html__('First Name', 'temporary-login-links-premium'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="first_name" id="first_name" value="<?php echo esc_attr($form_data['first_name']); ?>" class="regular-text">
                            <p class="description"><?php esc_html__('The first name of the user (optional).', 'temporary-login-links-premium'); ?></p>
                        </td>
                    </tr>
                    
                    <!-- Last Name -->
                    <tr>
                        <th>
                            <label for="last_name"><?php esc_html__('Last Name', 'temporary-login-links-premium'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="last_name" id="last_name" value="<?php echo esc_attr($form_data['last_name']); ?>" class="regular-text">
                            <p class="description"><?php esc_html__('The last name of the user (optional).', 'temporary-login-links-premium'); ?></p>
                        </td>
                    </tr>
                    
                    <!-- Role -->
                    <tr>
                        <th>
                            <label for="role"><?php esc_html__('User Role', 'temporary-login-links-premium'); ?> <span class="required">*</span></label>
                        </th>
                        <td>
                            <select name="role" id="role" required>
                                <?php foreach ($roles as $role_value => $role_name) : ?>
                                    <option value="<?php echo esc_attr($role_value); ?>" <?php selected($form_data['role'], $role_value); ?>><?php echo esc_html($role_name); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description"><?php esc_html__('The role assigned to the temporary user.', 'temporary-login-links-premium'); ?></p>
                            
                            <div class="tlp-tooltip">
                                <span class="dashicons dashicons-info"></span>
                                <div class="tlp-tooltip-content">
                                    <?php esc_html__('Choose the appropriate role based on what the user needs to do. For security, use the least privileged role necessary.', 'temporary-login-links-premium'); ?>
                                </div>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Expiry -->
                    <tr>
                        <th>
                            <label for="expiry"><?php esc_html__('Expiration', 'temporary-login-links-premium'); ?> <span class="required">*</span></label>
                        </th>
                        <td>
                            <div class="tlp-expiry-wrapper">
                                <select name="expiry" id="expiry" required>
                                    <option value="1 hour" <?php selected($form_data['expiry'], '1 hour'); ?>><?php esc_html__('1 Hour', 'temporary-login-links-premium'); ?></option>
                                    <option value="3 hours" <?php selected($form_data['expiry'], '3 hours'); ?>><?php esc_html__('3 Hours', 'temporary-login-links-premium'); ?></option>
                                    <option value="6 hours" <?php selected($form_data['expiry'], '6 hours'); ?>><?php esc_html__('6 Hours', 'temporary-login-links-premium'); ?></option>
                                    <option value="12 hours" <?php selected($form_data['expiry'], '12 hours'); ?>><?php esc_html__('12 Hours', 'temporary-login-links-premium'); ?></option>
                                    <option value="1 day" <?php selected($form_data['expiry'], '1 day'); ?>><?php esc_html__('1 Day', 'temporary-login-links-premium'); ?></option>
                                    <option value="3 days" <?php selected($form_data['expiry'], '3 days'); ?>><?php esc_html__('3 Days', 'temporary-login-links-premium'); ?></option>
                                    <option value="7 days" <?php selected($form_data['expiry'], '7 days'); ?>><?php esc_html__('7 Days', 'temporary-login-links-premium'); ?></option>
                                    <option value="14 days" <?php selected($form_data['expiry'], '14 days'); ?>><?php esc_html__('14 Days', 'temporary-login-links-premium'); ?></option>
                                    <option value="1 month" <?php selected($form_data['expiry'], '1 month'); ?>><?php esc_html__('1 Month', 'temporary-login-links-premium'); ?></option>
                                    <option value="3 months" <?php selected($form_data['expiry'], '3 months'); ?>><?php esc_html__('3 Months', 'temporary-login-links-premium'); ?></option>
                                    <option value="6 months" <?php selected($form_data['expiry'], '6 months'); ?>><?php esc_html__('6 Months', 'temporary-login-links-premium'); ?></option>
                                    <option value="1 year" <?php selected($form_data['expiry'], '1 year'); ?>><?php esc_html__('1 Year', 'temporary-login-links-premium'); ?></option>
                                    <option value="custom" <?php selected(strpos($form_data['expiry'], 'custom_') === 0); ?>><?php esc_html__('Custom Date', 'temporary-login-links-premium'); ?></option>
                                </select>
                                <p class="description"><?php esc_html__('How long the link will be valid.', 'temporary-login-links-premium'); ?></p>
                            </div>
                            
                            <div class="tlp-custom-expiry" style="<?php echo strpos($form_data['expiry'], 'custom_') === 0 ? 'display: block;' : 'display: none;'; ?>">
                                <label for="custom_expiry_date"><?php esc_html__('Custom Expiry Date and Time:', 'temporary-login-links-premium'); ?></label>
                                <div class="tlp-custom-expiry-inputs">
                                    <input type="text" name="custom_expiry" id="custom_expiry" value="<?php echo esc_attr($form_data['custom_expiry']); ?>" class="tlp-datepicker" placeholder="YYYY-MM-DD HH:MM:SS">
                                </div>
                                <p class="description"><?php esc_html__('Set a specific date and time when the link will expire.', 'temporary-login-links-premium'); ?></p>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Status -->
                    <tr>
                        <th>
                            <label for="is_active"><?php esc_html__('Status', 'temporary-login-links-premium'); ?></label>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" name="is_active" id="is_active" value="1" <?php checked($form_data['is_active'], 1); ?>>
                                <?php esc_html__('Active', 'temporary-login-links-premium'); ?>
                            </label>
                            <p class="description"><?php esc_html__('If unchecked, the link will be deactivated and cannot be used for login.', 'temporary-login-links-premium'); ?></p>
                        </td>
                    </tr>
                    
                    <!-- Maximum Accesses -->
                    <tr>
                        <th>
                            <label for="max_accesses"><?php esc_html__('Maximum Accesses', 'temporary-login-links-premium'); ?></label>
                        </th>
                        <td>
                            <input type="number" name="max_accesses" id="max_accesses" value="<?php echo esc_attr($form_data['max_accesses']); ?>" class="small-text" min="0">
                            <p class="description"><?php esc_html__('Maximum number of times the link can be used. Set to 0 for unlimited.', 'temporary-login-links-premium'); ?></p>
                            
                            <div class="tlp-tooltip">
                                <span class="dashicons dashicons-info"></span>
                                <div class="tlp-tooltip-content">
                                    <?php esc_html__('This is a premium feature that limits the number of times a link can be used. For one-time access, set to 1.', 'temporary-login-links-premium'); ?>
                                </div>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Redirect URL -->
                    <tr>
                        <th>
                            <label for="redirect_to"><?php esc_html__('Redirect URL', 'temporary-login-links-premium'); ?></label>
                        </th>
                        <td>
                            <input type="url" name="redirect_to" id="redirect_to" value="<?php echo esc_url($form_data['redirect_to']); ?>" class="regular-text">
                            <p class="description"><?php esc_html__('Where to redirect the user after successful login. Leave empty to use the default.', 'temporary-login-links-premium'); ?></p>
                        </td>
                    </tr>
                    
                    <!-- IP Restriction -->
                    <tr>
                        <th>
                            <label for="ip_restriction"><?php esc_html__('IP Restriction', 'temporary-login-links-premium'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="ip_restriction" id="ip_restriction" value="<?php echo esc_attr($form_data['ip_restriction']); ?>" class="regular-text" placeholder="<?php echo esc_attr($this->security->get_client_ip()); ?>">
                            <p class="description"><?php esc_html__('Restrict access to specific IP addresses. Enter comma-separated IPs.', 'temporary-login-links-premium'); ?></p>
                            
                            <div class="tlp-tooltip">
                                <span class="dashicons dashicons-info"></span>
                                <div class="tlp-tooltip-content">
                                    <?php esc_html__('This is a premium feature that restricts link usage to specific IP addresses. Leave empty to allow access from any IP.', 'temporary-login-links-premium'); ?>
                                </div>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Language -->
                    <tr>
                        <th>
                            <label for="language"><?php esc_html__('Language', 'temporary-login-links-premium'); ?></label>
                        </th>
                        <td>
                            <select name="language" id="language">
                                <?php foreach ($languages as $locale => $language_name) : ?>
                                    <option value="<?php echo esc_attr($locale); ?>" <?php selected($form_data['language'], $locale); ?>><?php echo esc_html($language_name); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description"><?php esc_html__('The language to use for the user\'s admin interface.', 'temporary-login-links-premium'); ?></p>
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <div class="tlp-form-actions">
                <button type="submit" class="button button-primary"><?php esc_html__('Update Login Link', 'temporary-login-links-premium'); ?></button>
                <a href="<?php echo esc_url(admin_url('admin.php?page=temporary-login-links-premium-links&action=view&id=' . $link_id)); ?>" class="button button-secondary"><?php esc_html__('Cancel', 'temporary-login-links-premium'); ?></a>
            </div>
        </form>
    </div>
    
    <!-- Helpful Tips Sidebar -->
    <div class="tlp-sidebar">
        <div class="tlp-sidebar-box">
            <h3><?php esc_html__('Link Access Statistics', 'temporary-login-links-premium'); ?></h3>
            
            <div class="tlp-stats">
                <div class="tlp-stat-item">
                    <span class="tlp-stat-label"><?php esc_html__('Created:', 'temporary-login-links-premium'); ?></span>
                    <span class="tlp-stat-value"><?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($link['created_at'])); ?></span>
                </div>
                
                <div class="tlp-stat-item">
                    <span class="tlp-stat-label"><?php esc_html__('Current Expiry:', 'temporary-login-links-premium'); ?></span>
                    <span class="tlp-stat-value"><?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($link['expiry'])); ?></span>
                </div>
                
                <div class="tlp-stat-item">
                    <span class="tlp-stat-label"><?php esc_html__('Access Count:', 'temporary-login-links-premium'); ?></span>
                    <span class="tlp-stat-value"><?php echo $link['access_count']; ?><?php echo $link['max_accesses'] > 0 ? ' / ' . $link['max_accesses'] : ''; ?></span>
                </div>
                
                <div class="tlp-stat-item">
                    <span class="tlp-stat-label"><?php esc_html__('Last Accessed:', 'temporary-login-links-premium'); ?></span>
                    <span class="tlp-stat-value">
                        <?php
                        if (!empty($link['last_accessed'])) {
                            echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($link['last_accessed']));
                        } else {
                            echo __('Never', 'temporary-login-links-premium');
                        }
                        ?>
                    </span>
                </div>
                
                <div class="tlp-stat-item">
                    <span class="tlp-stat-label"><?php esc_html__('Current Status:', 'temporary-login-links-premium'); ?></span>
                    <span class="tlp-stat-value">
                        <?php
                        if ($link['is_active'] == 0) {
                            echo '<span class="tlp-status tlp-status-inactive">' . __('Inactive', 'temporary-login-links-premium') . '</span>';
                        } elseif (strtotime($link['expiry']) < time()) {
                            echo '<span class="tlp-status tlp-status-expired">' . __('Expired', 'temporary-login-links-premium') . '</span>';
                        } else {
                            echo '<span class="tlp-status tlp-status-active">' . __('Active', 'temporary-login-links-premium') . '</span>';
                        }
                        ?>
                    </span>
                </div>
            </div>
            
            <div class="tlp-sidebar-actions">
                <a href="<?php echo esc_url(admin_url('admin.php?page=temporary-login-links-premium-links&action=view&id=' . $link_id)); ?>" class="button"><?php esc_html__('View Full Details', 'temporary-login-links-premium'); ?></a>
            </div>
        </div>
        
        <div class="tlp-sidebar-box">
            <h3><?php esc_html__('Helpful Tips', 'temporary-login-links-premium'); ?></h3>
            
            <div class="tlp-tips">
                <div class="tlp-tip">
                    <div class="tlp-tip-icon">
                        <span class="dashicons dashicons-shield"></span>
                    </div>
                    <div class="tlp-tip-content">
                        <h4><?php esc_html__('Security Best Practices', 'temporary-login-links-premium'); ?></h4>
                        <p><?php esc_html__('Always set an appropriate expiration time and use the least privileged role needed.', 'temporary-login-links-premium'); ?></p>
                    </div>
                </div>
                
                <div class="tlp-tip">
                    <div class="tlp-tip-icon">
                        <span class="dashicons dashicons-lock"></span>
                    </div>
                    <div class="tlp-tip-content">
                        <h4><?php esc_html__('IP Restriction', 'temporary-login-links-premium'); ?></h4>
                        <p><?php esc_html__('For maximum security, restrict access to known IP addresses when possible.', 'temporary-login-links-premium'); ?></p>
                    </div>
                </div>
                
                <div class="tlp-tip">
                    <div class="tlp-tip-icon">
                        <span class="dashicons dashicons-calendar-alt"></span>
                    </div>
                    <div class="tlp-tip-content">
                        <h4><?php esc_html__('Custom Expiry', 'temporary-login-links-premium'); ?></h4>
                        <p><?php esc_html__('Use the custom expiry option to set a specific date and time for the link to expire.', 'temporary-login-links-premium'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Date/Time Picker Initialization -->
<script type="text/javascript">
jQuery(document).ready(function($) {
    // Initialize the datepicker
    if ($.fn.datepicker) {
        $('#custom_expiry').datetimepicker({
            dateFormat: 'yy-mm-dd',
            timeFormat: 'HH:mm:ss',
            minDate: 0, // Only future dates
            showSecond: true
        });
    }
    
    // Toggle custom expiry field
    $('#expiry').on('change', function() {
        if ($(this).val() === 'custom') {
            $('.tlp-custom-expiry').slideDown(200);
        } else {
            $('.tlp-custom-expiry').slideUp(200);
        }
    });
});
</script>

<style>
/* Page layout with sidebar */
@media screen and (min-width: 783px) {
    .tlp-form-page {
        float: left;
        width: 72%;
        box-sizing: border-box;
    }
    
    .tlp-sidebar {
        float: right;
        width: 26%;
        margin-top: 20px;
    }
    
    .tlp-wrap:after {
        content: "";
        display: table;
        clear: both;
    }
}

/* Sidebar box */
.tlp-sidebar-box {
    background: #fff;
    border: 1px solid #ccd0d4;
    box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);
    padding: 15px;
    margin-bottom: 20px;
}

.tlp-sidebar-box h3 {
    margin-top: 0;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}

/* Stats */
.tlp-stats {
    margin-bottom: 15px;
}

.tlp-stat-item {
    padding: 8px 0;
    border-bottom: 1px solid #f5f5f5;
    display: flex;
    justify-content: space-between;
}

.tlp-stat-item:last-child {
    border-bottom: none;
}

.tlp-stat-label {
    font-weight: 600;
}

.tlp-sidebar-actions {
    text-align: center;
    margin-top: 15px;
}

/* Tips */
.tlp-tip {
    display: flex;
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #f7f7f7;
}

.tlp-tip:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}

.tlp-tip-icon {
    margin-right: 10px;
}

.tlp-tip-icon .dashicons {
    color: #0073aa;
    font-size: 24px;
    width: 24px;
    height: 24px;
}

.tlp-tip-content h4 {
    margin: 0 0 5px;
    font-size: 14px;
}

.tlp-tip-content p {
    margin: 0;
    color: #666;
    font-size: 13px;
}

/* View Link URL */
.tlp-view-link-url {
    background: #f7f7f7;
    border: 1px solid #ddd;
    padding: 15px;
    border-radius: 3px;
    margin-bottom: 20px;
    position: relative;
}

.tlp-login-url {
    word-break: break-all;
    font-family: monospace;
    margin-bottom: 10px;
    background: #fff;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 3px;
}

.tlp-copy-button {
    position: absolute;
    right: 15px;
    top: 15px;
}
</style>
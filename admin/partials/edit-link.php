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
    <h1><?php esc_html_e('Edit Temporary Login Link', 'temporary-login-links-premium'); ?></h1>
    
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
                <p><strong><?php esc_html_e('Login URL:', 'temporary-login-links-premium'); ?></strong></p>
                <div class="tlp-login-url"><?php echo esc_url($login_url); ?></div>
                <button type="button" class="button tlp-copy-button" data-copy="<?php echo esc_attr($login_url); ?>">
                    <span class="dashicons dashicons-clipboard"></span> <?php esc_html_e('Copy', 'temporary-login-links-premium'); ?>
                </button>
                <span class="tlp-copy-success" style="display: none;"><?php esc_html_e('Copied!', 'temporary-login-links-premium'); ?></span>
            </div>
            
            <table class="tlp-form-table">
                <tbody>
                    <!-- Email Address (read-only) -->
                    <tr>
                        <th>
                            <label for="user_email"><?php esc_html_e('Email Address', 'temporary-login-links-premium'); ?></label>
                        </th>
                        <td>
                            <input type="email" name="user_email" id="user_email" value="<?php echo esc_attr($form_data['user_email']); ?>" class="regular-text" readonly disabled>
                            <p class="description"><?php esc_html_e('The email address cannot be changed. To use a different email, create a new link.', 'temporary-login-links-premium'); ?></p>
                        </td>
                    </tr>
                    
                    <!-- First Name -->
                    <tr>
                        <th>
                            <label for="first_name"><?php esc_html_e('First Name', 'temporary-login-links-premium'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="first_name" id="first_name" value="<?php echo esc_attr($form_data['first_name']); ?>" class="regular-text">
                            <p class="description"><?php esc_html_e('The first name of the user (optional).', 'temporary-login-links-premium'); ?></p>
                        </td>
                    </tr>
                    
                    <!-- Last Name -->
                    <tr>
                        <th>
                            <label for="last_name"><?php esc_html_e('Last Name', 'temporary-login-links-premium'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="last_name" id="last_name" value="<?php echo esc_attr($form_data['last_name']); ?>" class="regular-text">
                            <p class="description"><?php esc_html_e('The last name of the user (optional).', 'temporary-login-links-premium'); ?></p>
                        </td>
                    </tr>
                    
                    <!-- Role -->
                    <tr>
                        <th>
                            <label for="role"><?php esc_html_e('User Role', 'temporary-login-links-premium'); ?> <span class="required">*</span></label>
                        </th>
                        <td>
                            <select name="role" id="role" required>
                                <?php foreach ($roles as $role_value => $role_name) : ?>
                                    <option value="<?php echo esc_attr($role_value); ?>" <?php selected($form_data['role'], $role_value); ?>><?php echo esc_html($role_name); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description"><?php esc_html_e('The role assigned to the temporary user.', 'temporary-login-links-premium'); ?></p>
                            
                            <div class="tlp-tooltip">
                                <span class="dashicons dashicons-info"></span>
                                <div class="tlp-tooltip-content">
                                    <?php esc_html_e('Choose the appropriate role based on what the user needs to do. For security, use the least privileged role necessary.', 'temporary-login-links-premium'); ?>
                                </div>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Expiry -->
                    <tr>
                        <th>
                            <label for="expiry"><?php esc_html_e('Expiration', 'temporary-login-links-premium'); ?> <span class="required">*</span></label>
                        </th>
                        <td>
                            <div class="tlp-expiry-wrapper">
                                <select name="expiry" id="expiry" required>
                                    <option value="1 hour" <?php selected($form_data['expiry'], '1 hour'); ?>><?php esc_html_e('1 Hour', 'temporary-login-links-premium'); ?></option>
                                    <option value="3 hours" <?php selected($form_data['expiry'], '3 hours'); ?>><?php esc_html_e('3 Hours', 'temporary-login-links-premium'); ?></option>
                                    <option value="6 hours" <?php selected($form_data['expiry'], '6 hours'); ?>><?php esc_html_e('6 Hours', 'temporary-login-links-premium'); ?></option>
                                    <option value="12 hours" <?php selected($form_data['expiry'], '12 hours'); ?>><?php esc_html_e('12 Hours', 'temporary-login-links-premium'); ?></option>
                                    <option value="1 day" <?php selected($form_data['expiry'], '1 day'); ?>><?php esc_html_e('1 Day', 'temporary-login-links-premium'); ?></option>
                                    <option value="3 days" <?php selected($form_data['expiry'], '3 days'); ?>><?php esc_html_e('3 Days', 'temporary-login-links-premium'); ?></option>
                                    <option value="7 days" <?php selected($form_data['expiry'], '7 days'); ?>><?php esc_html_e('7 Days', 'temporary-login-links-premium'); ?></option>
                                    <option value="14 days" <?php selected($form_data['expiry'], '14 days'); ?>><?php esc_html_e('14 Days', 'temporary-login-links-premium'); ?></option>
                                    <option value="1 month" <?php selected($form_data['expiry'], '1 month'); ?>><?php esc_html_e('1 Month', 'temporary-login-links-premium'); ?></option>
                                    <option value="3 months" <?php selected($form_data['expiry'], '3 months'); ?>><?php esc_html_e('3 Months', 'temporary-login-links-premium'); ?></option>
                                    <option value="6 months" <?php selected($form_data['expiry'], '6 months'); ?>><?php esc_html_e('6 Months', 'temporary-login-links-premium'); ?></option>
                                    <option value="1 year" <?php selected($form_data['expiry'], '1 year'); ?>><?php esc_html_e('1 Year', 'temporary-login-links-premium'); ?></option>
                                    <option value="custom" <?php selected(strpos($form_data['expiry'], 'custom_') === 0); ?>><?php esc_html_e('Custom Date', 'temporary-login-links-premium'); ?></option>
                                </select>
                                <p class="description"><?php esc_html_e('How long the link will be valid.', 'temporary-login-links-premium'); ?></p>
                            </div>
                            
                            <div class="tlp-custom-expiry" style="<?php echo strpos($form_data['expiry'], 'custom_') === 0 ? 'display: block;' : 'display: none;'; ?>">
                                <label for="custom_expiry_date"><?php esc_html_e('Custom Expiry Date:', 'temporary-login-links-premium'); ?></label>
                                <div class="tlp-custom-expiry-inputs">
                                    <input type="text" name="custom_expiry" id="custom_expiry" value="<?php echo esc_attr($form_data['custom_expiry']); ?>" class="tlp-datepicker" placeholder="YYYY-MM-DD">
                                </div>
                                <p class="description"><?php esc_html_e('Set a specific date when the link will expire. The link will expire at the end of the selected day.', 'temporary-login-links-premium'); ?></p>
                            </div>                            
                            
                            
                        </td>
                    </tr>
                    
                    <!-- Status -->
                    <tr>
                        <th>
                            <label for="is_active"><?php esc_html_e('Status', 'temporary-login-links-premium'); ?></label>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" name="is_active" id="is_active" value="1" <?php checked($form_data['is_active'], 1); ?>>
                                <?php esc_html_e('Active', 'temporary-login-links-premium'); ?>
                            </label>
                            <p class="description"><?php esc_html_e('If unchecked, the link will be deactivated and cannot be used for login.', 'temporary-login-links-premium'); ?></p>
                        </td>
                    </tr>
                    
                    <!-- Maximum Accesses -->
                    <tr>
                        <th>
                            <label for="max_accesses"><?php esc_html_e('Maximum Accesses', 'temporary-login-links-premium'); ?></label>
                        </th>
                        <td>
                            <input type="number" name="max_accesses" id="max_accesses" value="<?php echo esc_attr($form_data['max_accesses']); ?>" class="small-text" min="0">
                            <p class="description"><?php esc_html_e('Maximum number of times the link can be used. Set to 0 for unlimited.', 'temporary-login-links-premium'); ?></p>
                            
                            <div class="tlp-tooltip">
                                <span class="dashicons dashicons-info"></span>
                                <div class="tlp-tooltip-content">
                                    <?php esc_html_e('This is a premium feature that limits the number of times a link can be used. For one-time access, set to 1.', 'temporary-login-links-premium'); ?>
                                </div>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Redirect URL -->
                    <tr>
                        <th>
                            <label for="redirect_to"><?php esc_html_e('Redirect URL', 'temporary-login-links-premium'); ?></label>
                        </th>
                        <td>
                            <input type="url" name="redirect_to" id="redirect_to" value="<?php echo esc_url($form_data['redirect_to']); ?>" class="regular-text">
                            <p class="description"><?php esc_html_e('Where to redirect the user after successful login. Leave empty to use the default.', 'temporary-login-links-premium'); ?></p>
                        </td>
                    </tr>
                    
                    <!-- IP Restriction -->
                    <tr>
                        <th>
                            <label for="ip_restriction"><?php esc_html_e('IP Restriction', 'temporary-login-links-premium'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="ip_restriction" id="ip_restriction" value="<?php echo esc_attr($form_data['ip_restriction']); ?>" class="regular-text" placeholder="<?php echo esc_attr($this->security->get_client_ip()); ?>">
                            <p class="description"><?php esc_html_e('Restrict access to specific IP addresses. Enter comma-separated IPs.', 'temporary-login-links-premium'); ?></p>
                            
                            <div class="tlp-tooltip">
                                <span class="dashicons dashicons-info"></span>
                                <div class="tlp-tooltip-content">
                                    <?php esc_html_e('This is a premium feature that restricts link usage to specific IP addresses. Leave empty to allow access from any IP.', 'temporary-login-links-premium'); ?>
                                </div>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Language -->
                    <tr>
                        <th>
                            <label for="language"><?php esc_html_e('Language', 'temporary-login-links-premium'); ?></label>
                        </th>
                        <td>
                            <select name="language" id="language">
                                <?php foreach ($languages as $locale => $language_name) : ?>
                                    <option value="<?php echo esc_attr($locale); ?>" <?php selected($form_data['language'], $locale); ?>><?php echo esc_html($language_name); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description"><?php esc_html_e('The language to use for the user\'s admin interface.', 'temporary-login-links-premium'); ?></p>
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <div class="tlp-form-actions">
                <button type="submit" class="button button-primary"><?php esc_html_e('Update Login Link', 'temporary-login-links-premium'); ?></button>
                <a href="<?php echo esc_url(admin_url('admin.php?page=temporary-login-links-premium-links&action=view&id=' . intval($link_id))); ?>" class="button button-secondary"><?php esc_html_e('Cancel', 'temporary-login-links-premium'); ?></a>
            </div>
        </form>
    </div>
    
    <!-- Helpful Tips Sidebar -->
    <div class="tlp-sidebar">
        <div class="tlp-sidebar-box">
            <h3><?php esc_html_e('Link Access Statistics', 'temporary-login-links-premium'); ?></h3>
            
            <div class="tlp-stats">
                <div class="tlp-stat-item">
                    <span class="tlp-stat-label"><?php esc_html_e('Created:', 'temporary-login-links-premium'); ?></span>
                    <span class="tlp-stat-value"><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($link['created_at']))); ?></span>
                </div>
                
                <div class="tlp-stat-item">
                    <span class="tlp-stat-label"><?php esc_html_e('Current Expiry:', 'temporary-login-links-premium'); ?></span>
                    <span class="tlp-stat-value"><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($link['expiry']))); ?></span>
                </div>
                
                <div class="tlp-stat-item">
                    <span class="tlp-stat-label"><?php esc_html_e('Access Count:', 'temporary-login-links-premium'); ?></span>
                    <span class="tlp-stat-value"><?php echo intval($link['access_count']); ?><?php echo intval($link['max_accesses']) > 0 ? ' / ' . intval($link['max_accesses']) : ''; ?></span>
                </div>
                
                <div class="tlp-stat-item">
                    <span class="tlp-stat-label"><?php esc_html_e('Last Accessed:', 'temporary-login-links-premium'); ?></span>
                    <span class="tlp-stat-value">
                        <?php
                        if (!empty($link['last_accessed'])) {
                            echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($link['last_accessed'])));
                        } else {
                            esc_html_e('Never', 'temporary-login-links-premium');
                        }
                        ?>
                    </span>
                </div>
                
                <div class="tlp-stat-item">
                    <span class="tlp-stat-label"><?php esc_html_e('Current Status:', 'temporary-login-links-premium'); ?></span>
                    <span class="tlp-stat-value">
                        <?php
                        if (intval($link['is_active']) == 0) {
                            echo '<span class="tlp-status tlp-status-inactive">' . esc_html__('Inactive', 'temporary-login-links-premium') . '</span>';
                        } elseif (strtotime($link['expiry']) < time()) {
                            echo '<span class="tlp-status tlp-status-expired">' . esc_html__('Expired', 'temporary-login-links-premium') . '</span>';
                        } else {
                            echo '<span class="tlp-status tlp-status-active">' . esc_html__('Active', 'temporary-login-links-premium') . '</span>';
                        }
                        ?>
                    </span>
                </div>
            </div>
            
            <div class="tlp-sidebar-actions">
                <a href="<?php echo esc_url(admin_url('admin.php?page=temporary-login-links-premium-links&action=view&id=' . intval($link_id))); ?>" class="button"><?php esc_html_e('View Full Details', 'temporary-login-links-premium'); ?></a>
            </div>
        </div>
        
        <div class="tlp-sidebar-box">
            <h3><?php esc_html_e('Helpful Tips', 'temporary-login-links-premium'); ?></h3>
            
            <div class="tlp-tips">
                <div class="tlp-tip">
                    <div class="tlp-tip-icon">
                        <span class="dashicons dashicons-shield"></span>
                    </div>
                    <div class="tlp-tip-content">
                        <h4><?php esc_html_e('Security Best Practices', 'temporary-login-links-premium'); ?></h4>
                        <p><?php esc_html_e('Always set an appropriate expiration time and use the least privileged role needed.', 'temporary-login-links-premium'); ?></p>
                    </div>
                </div>
                
                <div class="tlp-tip">
                    <div class="tlp-tip-icon">
                        <span class="dashicons dashicons-lock"></span>
                    </div>
                    <div class="tlp-tip-content">
                        <h4><?php esc_html_e('IP Restriction', 'temporary-login-links-premium'); ?></h4>
                        <p><?php esc_html_e('For maximum security, restrict access to known IP addresses when possible.', 'temporary-login-links-premium'); ?></p>
                    </div>
                </div>
                
                <div class="tlp-tip">
                    <div class="tlp-tip-icon">
                        <span class="dashicons dashicons-calendar-alt"></span>
                    </div>
                    <div class="tlp-tip-content">
                        <h4><?php esc_html_e('Custom Expiry', 'temporary-login-links-premium'); ?></h4>
                        <p><?php esc_html_e('Use the custom expiry option to set a specific date and time for the link to expire.', 'temporary-login-links-premium'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script type="text/javascript">
jQuery(document).ready(function($) {
    // Initialize the datepicker - remove time-related options
    if ($.fn.datepicker) {
        $('#custom_expiry').datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true,
            minDate: 0,
            yearRange: 'c:c+10'
            // Removed: timeFormat, showSecond, controlType, oneLine
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

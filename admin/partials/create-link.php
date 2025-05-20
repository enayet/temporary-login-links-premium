<?php
/**
 * Template for the create link form.
 *
 * This file provides the HTML for the create link form,
 * allowing administrators to create new temporary login links.
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
    <h1><?php echo esc_html__('Create Temporary Login Link', 'temporary-login-links-premium'); ?></h1>
    
    <?php 
    // Display messages
    if (!empty($message)) {
        $message_class = $message_type === 'error' ? 'error' : 'updated';
        echo '<div class="notice ' . esc_attr($message_class) . ' is-dismissible"><p>' . esc_html($message) . '</p></div>';
    }
    ?>
    
    <div class="tlp-form-page">
        <form method="post" action="" id="tlp-create-link-form">
            <?php wp_nonce_field('tlp_create_link_nonce'); ?>
            <input type="hidden" name="tlp_create_link" value="1">
            
            <table class="tlp-form-table">
                <tbody>
                    <!-- Email Address -->
                    <tr>
                        <th>
                            <label for="user_email"><?php echo esc_html__('Email Address', 'temporary-login-links-premium'); ?> <span class="required">*</span></label>
                        </th>
                        <td>
                            <input type="email" name="user_email" id="user_email" value="<?php echo esc_attr($form_data['user_email']); ?>" class="regular-text" required>
                            <p class="description"><?php echo esc_html__('The email address of the user to grant temporary access.', 'temporary-login-links-premium'); ?></p>
                        </td>
                    </tr>
                    
                    <!-- First Name -->
                    <tr>
                        <th>
                            <label for="first_name"><?php echo esc_html__('First Name', 'temporary-login-links-premium'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="first_name" id="first_name" value="<?php echo esc_attr($form_data['first_name']); ?>" class="regular-text">
                            <p class="description"><?php echo esc_html__('The first name of the user (optional).', 'temporary-login-links-premium'); ?></p>
                        </td>
                    </tr>
                    
                    <!-- Last Name -->
                    <tr>
                        <th>
                            <label for="last_name"><?php echo esc_html__('Last Name', 'temporary-login-links-premium'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="last_name" id="last_name" value="<?php echo esc_attr($form_data['last_name']); ?>" class="regular-text">
                            <p class="description"><?php echo esc_html__('The last name of the user (optional).', 'temporary-login-links-premium'); ?></p>
                        </td>
                    </tr>
                    
                    <!-- Role -->
                    <tr>
                        <th>
                            <label for="role"><?php echo esc_html__('User Role', 'temporary-login-links-premium'); ?> <span class="required">*</span></label>
                        </th>
                        <td>
                            <select name="role" id="role" required>
                                <?php foreach ($roles as $role_value => $role_name) : ?>
                                    <option value="<?php echo esc_attr($role_value); ?>" <?php selected($form_data['role'], $role_value); ?>><?php echo esc_html($role_name); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description"><?php echo esc_html__('The role to assign to the temporary user.', 'temporary-login-links-premium'); ?></p>
                            
                            <div class="tlp-tooltip">
                                <span class="dashicons dashicons-info"></span>
                                <div class="tlp-tooltip-content">
                                    <?php echo esc_html__('Choose the appropriate role based on what the user needs to do. For security, use the least privileged role necessary.', 'temporary-login-links-premium'); ?>
                                </div>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Expiry -->
                    <tr>
                        <th>
                            <label for="expiry"><?php echo esc_html__('Expiration', 'temporary-login-links-premium'); ?> <span class="required">*</span></label>
                        </th>
                        <td>
                            <div class="tlp-expiry-wrapper">
                                <select name="expiry" id="expiry" required>
                                    <option value="1 hour" <?php selected($form_data['expiry'], '1 hour'); ?>><?php echo esc_html__('1 Hour', 'temporary-login-links-premium'); ?></option>
                                    <option value="3 hours" <?php selected($form_data['expiry'], '3 hours'); ?>><?php echo esc_html__('3 Hours', 'temporary-login-links-premium'); ?></option>
                                    <option value="6 hours" <?php selected($form_data['expiry'], '6 hours'); ?>><?php echo esc_html__('6 Hours', 'temporary-login-links-premium'); ?></option>
                                    <option value="12 hours" <?php selected($form_data['expiry'], '12 hours'); ?>><?php echo esc_html__('12 Hours', 'temporary-login-links-premium'); ?></option>
                                    <option value="1 day" <?php selected($form_data['expiry'], '1 day'); ?>><?php echo esc_html__('1 Day', 'temporary-login-links-premium'); ?></option>
                                    <option value="3 days" <?php selected($form_data['expiry'], '3 days'); ?>><?php echo esc_html__('3 Days', 'temporary-login-links-premium'); ?></option>
                                    <option value="7 days" <?php selected($form_data['expiry'], '7 days'); ?>><?php echo esc_html__('7 Days', 'temporary-login-links-premium'); ?></option>
                                    <option value="14 days" <?php selected($form_data['expiry'], '14 days'); ?>><?php echo esc_html__('14 Days', 'temporary-login-links-premium'); ?></option>
                                    <option value="1 month" <?php selected($form_data['expiry'], '1 month'); ?>><?php echo esc_html__('1 Month', 'temporary-login-links-premium'); ?></option>
                                    <option value="3 months" <?php selected($form_data['expiry'], '3 months'); ?>><?php echo esc_html__('3 Months', 'temporary-login-links-premium'); ?></option>
                                    <option value="6 months" <?php selected($form_data['expiry'], '6 months'); ?>><?php echo esc_html__('6 Months', 'temporary-login-links-premium'); ?></option>
                                    <option value="1 year" <?php selected($form_data['expiry'], '1 year'); ?>><?php echo esc_html__('1 Year', 'temporary-login-links-premium'); ?></option>
                                    <option value="custom" <?php selected(strpos($form_data['expiry'], 'custom_') === 0); ?>><?php echo esc_html__('Custom Date', 'temporary-login-links-premium'); ?></option>
                                </select>
                                <p class="description"><?php echo esc_html__('How long the link will be valid.', 'temporary-login-links-premium'); ?></p>
                            </div>
                            
                            <div class="tlp-custom-expiry" style="<?php echo strpos($form_data['expiry'], 'custom_') === 0 ? 'display: block;' : 'display: none;'; ?>">
                                <label for="custom_expiry_date"><?php echo esc_html__('Custom Expiry Date and Time:', 'temporary-login-links-premium'); ?></label>
                                <div class="tlp-custom-expiry-inputs">
                                    <input type="text" name="custom_expiry" id="custom_expiry" value="<?php echo esc_attr($form_data['custom_expiry']); ?>" class="tlp-datepicker" placeholder="YYYY-MM-DD">
                                </div>
                                <p class="description"><?php echo esc_html__('Set a specific date and time when the link will expire.', 'temporary-login-links-premium'); ?></p>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Maximum Accesses -->
                    <tr>
                        <th>
                            <label for="max_accesses"><?php echo esc_html__('Maximum Accesses', 'temporary-login-links-premium'); ?></label>
                        </th>
                        <td>
                            <input type="number" name="max_accesses" id="max_accesses" value="<?php echo esc_attr($form_data['max_accesses']); ?>" class="small-text" min="0">
                            <p class="description"><?php echo esc_html__('Maximum number of times the link can be used. Set to 0 for unlimited.', 'temporary-login-links-premium'); ?></p>
                            
                            <div class="tlp-tooltip">
                                <span class="dashicons dashicons-info"></span>
                                <div class="tlp-tooltip-content">
                                    <?php echo esc_html__('This is a premium feature that limits the number of times a link can be used. For one-time access, set to 1.', 'temporary-login-links-premium'); ?>
                                </div>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Redirect URL -->
                    <tr>
                        <th>
                            <label for="redirect_to"><?php echo esc_html__('Redirect URL', 'temporary-login-links-premium'); ?></label>
                        </th>
                        <td>
                            <input type="url" name="redirect_to" id="redirect_to" value="<?php echo esc_url($form_data['redirect_to']); ?>" class="regular-text">
                            <p class="description"><?php echo esc_html__('Where to redirect the user after successful login. Leave empty to use the default.', 'temporary-login-links-premium'); ?></p>
                        </td>
                    </tr>
                    
                    <!-- IP Restriction -->
                    <tr>
                        <th>
                            <label for="ip_restriction"><?php echo esc_html__('IP Restriction', 'temporary-login-links-premium'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="ip_restriction" id="ip_restriction" value="<?php echo esc_attr($form_data['ip_restriction']); ?>" class="regular-text" placeholder="<?php echo esc_attr($this->security->get_client_ip()); ?>">
                            <p class="description"><?php echo esc_html__('Restrict access to specific IP addresses. Enter comma-separated IPs.', 'temporary-login-links-premium'); ?></p>
                            
                            <div class="tlp-tooltip">
                                <span class="dashicons dashicons-info"></span>
                                <div class="tlp-tooltip-content">
                                    <?php echo esc_html__('This is a premium feature that restricts link usage to specific IP addresses. Leave empty to allow access from any IP.', 'temporary-login-links-premium'); ?>
                                </div>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Language -->
                    <tr>
                        <th>
                            <label for="language"><?php echo esc_html__('Language', 'temporary-login-links-premium'); ?></label>
                        </th>
                        <td>
                            <select name="language" id="language">
                                <?php foreach ($languages as $locale => $language_name) : ?>
                                    <option value="<?php echo esc_attr($locale); ?>" <?php selected($form_data['language'], $locale); ?>><?php echo esc_html($language_name); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description"><?php echo esc_html__('The language to use for the user\'s admin interface.', 'temporary-login-links-premium'); ?></p>
                        </td>
                    </tr>
                    
                    <!-- Email Notification -->
                    <tr>
                        <th>
                            <label for="send_email"><?php echo esc_html__('Email Notification', 'temporary-login-links-premium'); ?></label>
                        </th>
                        <td>
                            <label for="send_email">
                                <input type="checkbox" name="send_email" id="send_email" value="1" <?php checked($form_data['send_email'], 1); ?>>
                                <?php esc_html__('Send email notification to the user with login link', 'temporary-login-links-premium'); ?>
                            </label>
                            <p class="description"><?php echo esc_html__('If checked, an email will be sent to the user with the login link.', 'temporary-login-links-premium'); ?></p>
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <div class="tlp-form-actions">
                <button type="submit" class="button button-primary"><?php echo esc_html__('Create Login Link', 'temporary-login-links-premium'); ?></button>
                <a href="<?php echo esc_url(admin_url('admin.php?page=temporary-login-links-premium-links')); ?>" class="button button-secondary"><?php echo esc_html__('Cancel', 'temporary-login-links-premium'); ?></a>
            </div>
        </form>
    </div>
    
    <!-- Helpful Tips Sidebar -->
    <div class="tlp-sidebar">
        <div class="tlp-sidebar-box">
            <h3><?php echo esc_html__('Helpful Tips', 'temporary-login-links-premium'); ?></h3>
            
            <div class="tlp-tips">
                <div class="tlp-tip">
                    <div class="tlp-tip-icon">
                        <span class="dashicons dashicons-shield"></span>
                    </div>
                    <div class="tlp-tip-content">
                        <h4><?php echo esc_html__('Security Best Practices', 'temporary-login-links-premium'); ?></h4>
                        <p><?php echo esc_html__('Always set an appropriate expiration time and use the least privileged role needed.', 'temporary-login-links-premium'); ?></p>
                    </div>
                </div>
                
                <div class="tlp-tip">
                    <div class="tlp-tip-icon">
                        <span class="dashicons dashicons-lock"></span>
                    </div>
                    <div class="tlp-tip-content">
                        <h4><?php echo esc_html__('IP Restriction', 'temporary-login-links-premium'); ?></h4>
                        <p><?php echo esc_html__('For maximum security, restrict access to known IP addresses when possible.', 'temporary-login-links-premium'); ?></p>
                    </div>
                </div>
                
                <div class="tlp-tip">
                    <div class="tlp-tip-icon">
                        <span class="dashicons dashicons-email"></span>
                    </div>
                    <div class="tlp-tip-content">
                        <h4><?php echo esc_html__('Email Notifications', 'temporary-login-links-premium'); ?></h4>
                        <p><?php echo esc_html__('Email notifications include your company branding if configured in the Branding settings.', 'temporary-login-links-premium'); ?></p>
                    </div>
                </div>
                
                <div class="tlp-tip">
                    <div class="tlp-tip-icon">
                        <span class="dashicons dashicons-admin-users"></span>
                    </div>
                    <div class="tlp-tip-content">
                        <h4><?php echo esc_html__('User Experience', 'temporary-login-links-premium'); ?></h4>
                        <p><?php echo esc_html__('The temporary user will be redirected to the URL you specify after logging in.', 'temporary-login-links-premium'); ?></p>
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
        $('#custom_expiry').datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true,
            minDate: 0
            // Removed time-related options
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
<?php
/**
 * Template for the settings page.
 *
 * This file provides the HTML for the plugin's settings page,
 * offering configuration options for general settings, security,
 * notifications, and advanced options.
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

// Get plugin options
$options = get_option('temporary_login_links_premium_settings', array());
?>

<div class="wrap tlp-wrap tlp-settings-page">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <?php settings_errors(); ?>
    
    <h2 class="nav-tab-wrapper tlp-tabs-wrapper">
        <a href="<?php echo esc_url(add_query_arg('tab', 'general')); ?>" class="nav-tab <?php echo $active_tab === 'general' ? 'nav-tab-active' : ''; ?>" data-tab="tlp-general-settings">
            <span class="dashicons dashicons-admin-generic"></span> <?php esc_html_e('General', 'temporary-login-links-premium'); ?>
        </a>
        <a href="<?php echo esc_url(add_query_arg('tab', 'security')); ?>" class="nav-tab <?php echo $active_tab === 'security' ? 'nav-tab-active' : ''; ?>" data-tab="tlp-security-settings">
            <span class="dashicons dashicons-shield"></span> <?php esc_html_e('Security', 'temporary-login-links-premium'); ?>
        </a>
        <a href="<?php echo esc_url(add_query_arg('tab', 'notifications')); ?>" class="nav-tab <?php echo $active_tab === 'notifications' ? 'nav-tab-active' : ''; ?>" data-tab="tlp-notification-settings">
            <span class="dashicons dashicons-email"></span> <?php esc_html_e('Notifications', 'temporary-login-links-premium'); ?>
        </a>
        <a href="<?php echo esc_url(add_query_arg('tab', 'advanced')); ?>" class="nav-tab <?php echo $active_tab === 'advanced' ? 'nav-tab-active' : ''; ?>" data-tab="tlp-advanced-settings">
            <span class="dashicons dashicons-admin-tools"></span> <?php esc_html_e('Advanced', 'temporary-login-links-premium'); ?>
        </a>
    </h2>
    
    <form method="post" action="options.php" class="tlp-settings-form">
        <?php
        // Output the settings fields
        settings_fields('temporary_login_links_premium_settings');
        ?>
        
        <!-- General Settings Tab -->
        <div id="tlp-general-settings" class="tlp-tab-content" <?php echo $active_tab !== 'general' ? 'style="display: none;"' : ''; ?>>
            <h2><?php esc_html_e('General Settings', 'temporary-login-links-premium'); ?></h2>
            <p><?php esc_html_e('Configure the default behavior for temporary login links.', 'temporary-login-links-premium'); ?></p>
            
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Default Expiration', 'temporary-login-links-premium'); ?></th>
                    <td>
                        <select name="temporary_login_links_premium_settings[link_expiry_default]">
                            <option value="1 hour" <?php selected(isset($options['link_expiry_default']) ? $options['link_expiry_default'] : '7 days', '1 hour'); ?>><?php esc_html_e('1 Hour', 'temporary-login-links-premium'); ?></option>
                            <option value="3 hours" <?php selected(isset($options['link_expiry_default']) ? $options['link_expiry_default'] : '7 days', '3 hours'); ?>><?php esc_html_e('3 Hours', 'temporary-login-links-premium'); ?></option>
                            <option value="6 hours" <?php selected(isset($options['link_expiry_default']) ? $options['link_expiry_default'] : '7 days', '6 hours'); ?>><?php esc_html_e('6 Hours', 'temporary-login-links-premium'); ?></option>
                            <option value="12 hours" <?php selected(isset($options['link_expiry_default']) ? $options['link_expiry_default'] : '7 days', '12 hours'); ?>><?php esc_html_e('12 Hours', 'temporary-login-links-premium'); ?></option>
                            <option value="1 day" <?php selected(isset($options['link_expiry_default']) ? $options['link_expiry_default'] : '7 days', '1 day'); ?>><?php esc_html_e('1 Day', 'temporary-login-links-premium'); ?></option>
                            <option value="3 days" <?php selected(isset($options['link_expiry_default']) ? $options['link_expiry_default'] : '7 days', '3 days'); ?>><?php esc_html_e('3 Days', 'temporary-login-links-premium'); ?></option>
                            <option value="7 days" <?php selected(isset($options['link_expiry_default']) ? $options['link_expiry_default'] : '7 days', '7 days'); ?>><?php esc_html_e('7 Days', 'temporary-login-links-premium'); ?></option>
                            <option value="14 days" <?php selected(isset($options['link_expiry_default']) ? $options['link_expiry_default'] : '7 days', '14 days'); ?>><?php esc_html_e('14 Days', 'temporary-login-links-premium'); ?></option>
                            <option value="1 month" <?php selected(isset($options['link_expiry_default']) ? $options['link_expiry_default'] : '7 days', '1 month'); ?>><?php esc_html_e('1 Month', 'temporary-login-links-premium'); ?></option>
                            <option value="3 months" <?php selected(isset($options['link_expiry_default']) ? $options['link_expiry_default'] : '7 days', '3 months'); ?>><?php esc_html_e('3 Months', 'temporary-login-links-premium'); ?></option>
                            <option value="6 months" <?php selected(isset($options['link_expiry_default']) ? $options['link_expiry_default'] : '7 days', '6 months'); ?>><?php esc_html_e('6 Months', 'temporary-login-links-premium'); ?></option>
                            <option value="1 year" <?php selected(isset($options['link_expiry_default']) ? $options['link_expiry_default'] : '7 days', '1 year'); ?>><?php esc_html_e('1 Year', 'temporary-login-links-premium'); ?></option>
                        </select>
                        <p class="description"><?php esc_html_e('Default expiration time when creating new links.', 'temporary-login-links-premium'); ?></p>
                    </td>
                </tr>
                
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Default User Role', 'temporary-login-links-premium'); ?></th>
                    <td>
                        <select name="temporary_login_links_premium_settings[default_role]">
                            <?php
                            // Get available roles
                            global $wp_roles;
                            if (!isset($wp_roles)) {
                                $wp_roles = new WP_Roles();
                            }
                            $roles = $wp_roles->get_names();
                            
                            // Remove administrator if current user is not an admin
                            if (!current_user_can('administrator') && isset($roles['administrator'])) {
                                unset($roles['administrator']);
                            }
                            
                            foreach ($roles as $role_value => $role_name) {
                                echo '<option value="' . esc_attr($role_value) . '" ' . selected(isset($options['default_role']) ? $options['default_role'] : 'editor', $role_value, false) . '>' . esc_html($role_name) . '</option>';
                            }
                            ?>
                        </select>
                        <p class="description"><?php esc_html_e('Default role assigned to new temporary users.', 'temporary-login-links-premium'); ?></p>
                    </td>
                </tr>
                
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Default Redirect URL', 'temporary-login-links-premium'); ?></th>
                    <td>
                        <input type="url" name="temporary_login_links_premium_settings[default_redirect]" value="<?php echo esc_url(isset($options['default_redirect']) ? $options['default_redirect'] : admin_url()); ?>" class="regular-text">
                        <p class="description"><?php esc_html_e('Default URL to redirect users to after login.', 'temporary-login-links-premium'); ?></p>
                    </td>
                </tr>
                
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Track Login Activity', 'temporary-login-links-premium'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="temporary_login_links_premium_settings[track_login_activity]" value="1" <?php checked(isset($options['track_login_activity']) ? $options['track_login_activity'] : 1); ?>>
                            <?php esc_html_e('Track all login attempts with temporary links', 'temporary-login-links-premium'); ?>
                        </label>
                        <p class="description"><?php esc_html_e('Records successful and failed login attempts for temporary links.', 'temporary-login-links-premium'); ?></p>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Security Settings Tab -->
        <div id="tlp-security-settings" class="tlp-tab-content" <?php echo $active_tab !== 'security' ? 'style="display: none;"' : ''; ?>>
            <h2><?php esc_html_e('Security Settings', 'temporary-login-links-premium'); ?></h2>
            <p><?php esc_html_e('Configure security options for temporary login links.', 'temporary-login-links-premium'); ?></p>
            
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Max Failed Attempts', 'temporary-login-links-premium'); ?></th>
                    <td>
                        <input type="number" name="temporary_login_links_premium_settings[max_failed_attempts]" value="<?php echo esc_attr(isset($options['max_failed_attempts']) ? $options['max_failed_attempts'] : 5); ?>" class="small-text" min="1">
                        <p class="description"><?php esc_html_e('Maximum failed login attempts before blocking an IP address.', 'temporary-login-links-premium'); ?></p>
                    </td>
                </tr>
                
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Lockout Time (minutes)', 'temporary-login-links-premium'); ?></th>
                    <td>
                        <input type="number" name="temporary_login_links_premium_settings[lockout_time]" value="<?php echo esc_attr(isset($options['lockout_time']) ? $options['lockout_time'] : 30); ?>" class="small-text" min="1">
                        <p class="description"><?php esc_html_e('Time in minutes to block an IP address after too many failed attempts.', 'temporary-login-links-premium'); ?></p>
                    </td>
                </tr>
                
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Security Notifications', 'temporary-login-links-premium'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="temporary_login_links_premium_settings[security_notifications]" value="1" <?php checked(isset($options['security_notifications']) ? $options['security_notifications'] : 1); ?>>
                            <?php esc_html_e('Send email notifications for suspicious login activity', 'temporary-login-links-premium'); ?>
                        </label>
                        <p class="description"><?php esc_html_e('Receive email alerts when suspicious activity is detected, such as multiple failed login attempts.', 'temporary-login-links-premium'); ?></p>
                    </td>
                </tr>
            </table>
            
            <div class="tlp-premium-callout">
                <h3><?php esc_html_e('Premium Security Features', 'temporary-login-links-premium'); ?></h3>
                <ul>
                    <li><span class="dashicons dashicons-yes"></span> <?php esc_html_e('IP Address Restrictions', 'temporary-login-links-premium'); ?></li>
                    <li><span class="dashicons dashicons-yes"></span> <?php esc_html_e('Usage Limits (Maximum Access Count)', 'temporary-login-links-premium'); ?></li>
                    <li><span class="dashicons dashicons-yes"></span> <?php esc_html_e('Detailed Access Logs', 'temporary-login-links-premium'); ?></li>
                    <li><span class="dashicons dashicons-yes"></span> <?php esc_html_e('Security Email Notifications', 'temporary-login-links-premium'); ?></li>
                </ul>
            </div>
        </div>
        
        <!-- Notification Settings Tab -->
        <div id="tlp-notification-settings" class="tlp-tab-content" <?php echo $active_tab !== 'notifications' ? 'style="display: none;"' : ''; ?>>
            <h2><?php esc_html_e('Notification Settings', 'temporary-login-links-premium'); ?></h2>
            <p><?php esc_html_e('Configure email notifications for temporary login links.', 'temporary-login-links-premium'); ?></p>
            
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Email Notifications', 'temporary-login-links-premium'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="temporary_login_links_premium_settings[email_notifications]" value="1" <?php checked(isset($options['email_notifications']) ? $options['email_notifications'] : 1); ?>>
                            <?php esc_html_e('Send email notifications to users when creating temporary login links', 'temporary-login-links-premium'); ?>
                        </label>
                        <p class="description"><?php esc_html_e('Automatically send login details to users when creating temporary links.', 'temporary-login-links-premium'); ?></p>
                    </td>
                </tr>
                
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Admin Notifications', 'temporary-login-links-premium'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="temporary_login_links_premium_settings[admin_notification]" value="1" <?php checked(isset($options['admin_notification']) ? $options['admin_notification'] : 0); ?>>
                            <?php esc_html_e('Notify admin when a temporary link is used', 'temporary-login-links-premium'); ?>
                        </label>
                        <p class="description"><?php esc_html_e('Send an email notification to the site administrator whenever a temporary login link is used.', 'temporary-login-links-premium'); ?></p>
                    </td>
                </tr>
            </table>
            
            <div class="tlp-email-preview">
                <h3><?php esc_html_e('Email Preview', 'temporary-login-links-premium'); ?></h3>
                <p><?php esc_html_e('You can customize the email appearance in the Branding settings.', 'temporary-login-links-premium'); ?></p>
                
                <div class="tlp-email-preview-box">
                    <div class="tlp-email-preview-header">
                        <?php
                        // Get branding settings
                        $branding = get_option('temporary_login_links_premium_branding', array());
                        $company_name = isset($branding['company_name']) ? $branding['company_name'] : get_bloginfo('name');
                        
                        // Show logo if available
                        if (!empty($branding['login_logo'])) {
                            echo '<img src="' . esc_url($branding['login_logo']) . '" alt="Logo" class="tlp-email-logo">';
                        } else {
                            echo '<h2>' . esc_html($company_name) . '</h2>';
                        }
                        ?>
                    </div>
                    
                    <div class="tlp-email-preview-content">
                        <p><?php esc_html_e('Hello John,', 'temporary-login-links-premium'); ?></p>
                        
                        <p><?php 
                            /* translators: %s company name  */   
                            printf(esc_html__('You have been granted temporary access to %s with Editor privileges.', 'temporary-login-links-premium'), esc_html($company_name)); ?></p>
                        
                        <div class="tlp-email-button">
                            <a href="#" class="button button-primary"><?php esc_html_e('Log In Now', 'temporary-login-links-premium'); ?></a>
                        </div>
                        
                        <p><?php esc_html_e('This link will expire on May 15, 2025 at 12:00 PM.', 'temporary-login-links-premium'); ?></p>
                    </div>
                    
                    <div class="tlp-email-preview-footer">
                        <p><?php 
                            /* translators: %s company name  */   
                            printf(esc_html__('Regards,<br>%s Team', 'temporary-login-links-premium'), esc_html($company_name)); ?></p>
                    </div>
                </div>
                
                <p class="tlp-email-branding-link">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=temporary-login-links-premium-branding')); ?>" class="button">
                        <?php esc_html_e('Customize Email Branding', 'temporary-login-links-premium'); ?>
                    </a>
                </p>
            </div>
        </div>
        
        <!-- Advanced Settings Tab -->
        <div id="tlp-advanced-settings" class="tlp-tab-content" <?php echo $active_tab !== 'advanced' ? 'style="display: none;"' : ''; ?>>
            <h2><?php esc_html_e('Advanced Settings', 'temporary-login-links-premium'); ?></h2>
            <p><?php esc_html_e('Configure advanced options for temporary login links.', 'temporary-login-links-premium'); ?></p>
            
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Auto Cleanup Links', 'temporary-login-links-premium'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="temporary_login_links_premium_settings[cleanup_expired_links]" value="1" <?php checked(isset($options['cleanup_expired_links']) ? $options['cleanup_expired_links'] : 1); ?>>
                            <?php esc_html_e('Automatically clean up expired links', 'temporary-login-links-premium'); ?>
                        </label>
                        <p class="description"><?php esc_html_e('Automatically delete expired links and their associated users after the specified number of days.', 'temporary-login-links-premium'); ?></p>
                    </td>
                </tr>
                
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Keep Expired Links (days)', 'temporary-login-links-premium'); ?></th>
                    <td>
                        <input type="number" name="temporary_login_links_premium_settings[keep_expired_links_days]" value="<?php echo esc_attr(isset($options['keep_expired_links_days']) ? $options['keep_expired_links_days'] : 30); ?>" class="small-text" min="1">
                        <p class="description"><?php esc_html_e('Number of days to keep expired links before deleting them.', 'temporary-login-links-premium'); ?></p>
                    </td>
                </tr>
                
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Delete Data on Uninstall', 'temporary-login-links-premium'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="temporary_login_links_premium_settings[delete_data_on_uninstall]" value="1" <?php checked(isset($options['delete_data_on_uninstall']) ? $options['delete_data_on_uninstall'] : 0); ?>>
                            <?php esc_html_e('Delete all plugin data when uninstalling', 'temporary-login-links-premium'); ?>
                        </label>
                        <p class="description"><?php esc_html_e('Warning: This will delete all temporary users, links, settings, and logs when the plugin is uninstalled.', 'temporary-login-links-premium'); ?></p>
                    </td>
                </tr>
            </table>
            
            <div class="tlp-import-export">
                <h3><?php esc_html_e('Import/Export Settings', 'temporary-login-links-premium'); ?></h3>
                <p><?php esc_html_e('Export your settings to use on another site or import settings from another installation.', 'temporary-login-links-premium'); ?></p>
                
                <div class="tlp-import-export-buttons">
                    <button type="button" class="button tlp-export-settings"><?php esc_html_e('Export Settings', 'temporary-login-links-premium'); ?></button>
                    <button type="button" class="button tlp-import-settings"><?php esc_html_e('Import Settings', 'temporary-login-links-premium'); ?></button>
                </div>
                
                <div class="tlp-import-form" style="display: none;">
                    <textarea id="tlp-import-data" class="large-text" rows="5" placeholder="<?php esc_attr_e('Paste exported settings data here...', 'temporary-login-links-premium'); ?>"></textarea>
                    <button type="button" class="button button-primary tlp-import-settings-submit"><?php esc_html_e('Import', 'temporary-login-links-premium'); ?></button>
                    <button type="button" class="button tlp-import-cancel"><?php esc_html_e('Cancel', 'temporary-login-links-premium'); ?></button>
                </div>
            </div>
        </div>
        
        <?php submit_button(); ?>
    </form>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Tab navigation
    $('.tlp-tabs-wrapper a.nav-tab').on('click', function(e) {
        e.preventDefault();
        
        // Remove active class from all tabs
        $('.tlp-tabs-wrapper a.nav-tab').removeClass('nav-tab-active');
        
        // Add active class to the clicked tab
        $(this).addClass('nav-tab-active');
        
        // Hide all tab content
        $('.tlp-tab-content').hide();
        
        // Show the content for the clicked tab
        var target = $(this).data('tab');
        $('#' + target).show();
        
        // Update URL hash
        window.history.pushState({}, '', $(this).attr('href'));
    });
    
    // Export/Import functionality
    $('.tlp-export-settings').on('click', function() {
        // Show loading state
        $(this).addClass('button-disabled').text('<?php echo esc_js(__('Exporting...', 'temporary-login-links-premium')); ?>');

        // Get settings data via AJAX
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'tlp_export_settings',
                nonce: '<?php echo esc_js(wp_create_nonce('tlp_export_settings_nonce')); ?>'
            },
            success: function(response) {
                // Reset button state
                $('.tlp-export-settings').removeClass('button-disabled').text('<?php echo esc_js(__('Export Settings', 'temporary-login-links-premium')); ?>');

                if (response.success) {
                    // Create textarea with the exported data
                    var exportData = response.data;
                    var $textarea = $('<textarea>').val(exportData).css({
                        width: '100%',
                        height: '200px',
                        marginBottom: '15px'
                    });

                    // Create a dialog with the export data
                    var $dialog = $('<div>').attr({
                        'id': 'tlp-export-dialog',
                        'title': '<?php echo esc_js(__('Export Settings', 'temporary-login-links-premium')); ?>'
                    }).append(
                        $('<p>').text('<?php echo esc_js(__('Copy the settings data below:', 'temporary-login-links-premium')); ?>'),
                        $textarea,
                        $('<button>').attr({
                            'type': 'button',
                            'class': 'button button-primary tlp-copy-export'
                        }).text('<?php echo esc_js(__('Copy to Clipboard', 'temporary-login-links-premium')); ?>')
                    );

                    // Show the dialog
                    $dialog.dialog({
                        width: 600,
                        modal: true,
                        close: function() {
                            $(this).dialog('destroy').remove();
                        }
                    });

                    // Copy to clipboard functionality
                    $('.tlp-copy-export').on('click', function() {
                        $textarea.select();
                        document.execCommand('copy');
                        $(this).text('<?php echo esc_js(__('Copied!', 'temporary-login-links-premium')); ?>');
                        setTimeout(function() {
                            $('.tlp-copy-export').text('<?php echo esc_js(__('Copy to Clipboard', 'temporary-login-links-premium')); ?>');
                        }, 2000);
                    });
                } else {
                    alert('<?php echo esc_js(__('Error exporting settings.', 'temporary-login-links-premium')); ?>');
                }
            },
            error: function() {
                // Reset button state
                $('.tlp-export-settings').removeClass('button-disabled').text('<?php echo esc_js(__('Export Settings', 'temporary-login-links-premium')); ?>');
                alert('<?php echo esc_js(__('An error occurred. Please try again.', 'temporary-login-links-premium')); ?>');
            }
        });
    });

    // Show import form
    $('.tlp-import-settings').on('click', function() {
        $('.tlp-import-form').slideDown();
    });

    // Hide import form
    $('.tlp-import-cancel').on('click', function() {
        $('.tlp-import-form').slideUp();
        $('#tlp-import-data').val('');
    });

    // Import settings
    $('.tlp-import-settings-submit').on('click', function() {
        var data = $('#tlp-import-data').val().trim();
        var $button = $(this);

        if (!data) {
            alert('<?php echo esc_js(__('Please paste exported settings data.', 'temporary-login-links-premium')); ?>');
            return;
        }

        // Basic validation - check if it looks like JSON
        try {
            // Try parsing the JSON to validate it
            JSON.parse(data);
        } catch (e) {
            alert('<?php echo esc_js(__('Invalid JSON format. Please ensure you copied the entire exported settings correctly.', 'temporary-login-links-premium')); ?>');
            return;
        }

        // Show loading state
        $button.addClass('button-disabled').text('<?php echo esc_js(__('Importing...', 'temporary-login-links-premium')); ?>');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'tlp_import_settings',
                data: data,
                nonce: '<?php echo esc_js(wp_create_nonce('tlp_import_settings_nonce')); ?>'
            },
            success: function(response) {
                // Reset button state
                $button.removeClass('button-disabled').text('<?php echo esc_js(__('Import', 'temporary-login-links-premium')); ?>');

                if (response.success) {
                    alert('<?php echo esc_js(__('Settings imported successfully! The page will now reload.', 'temporary-login-links-premium')); ?>');
                    location.reload();
                } else {
                    alert('<?php echo esc_js(__('Error importing settings: ', 'temporary-login-links-premium')); ?>' + response.data);
                    console.error("Import error:", response);
                }
            },
            error: function(xhr, status, error) {
                // Reset button state
                $button.removeClass('button-disabled').text('<?php echo esc_js(__('Import', 'temporary-login-links-premium')); ?>');
                alert('<?php echo esc_js(__('An error occurred. Please try again.', 'temporary-login-links-premium')); ?>');
                console.error("AJAX Error:", status, error, xhr.responseText);
            }
        });
    });
    
    
    
    
});
</script>
<?php
/**
 * Template for the branding settings page.
 *
 * This file provides the HTML for customizing the login page
 * and email notifications with custom branding.
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

<div class="wrap tlp-wrap tlp-branding-page">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <?php 
    // Display messages
    if (!empty($message)) {
        $message_class = $message_type === 'error' ? 'error' : 'updated';
        echo '<div class="notice ' . esc_attr($message_class) . ' is-dismissible"><p>' . esc_html($message) . '</p></div>';
    }
    ?>
    
    <div class="tlp-tabs-container">
        <!-- Left column: Form -->
        <div class="tlp-branding-form-container">
            <form method="post" action="" id="tlp-branding-form">
                <?php wp_nonce_field('tlp_branding_nonce'); ?>
                <input type="hidden" name="tlp_branding_submit" value="1">
                
                <div class="tlp-branding-form">
                    <h2><?php echo esc_html__('Customize Login Experience', 'temporary-login-links-premium'); ?></h2>
                    <p class="tlp-form-description"><?php echo esc_html__('Customize the login page and email notifications with your branding.', 'temporary-login-links-premium'); ?></p>
                    
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row"><?php echo esc_html__('Enable Branding', 'temporary-login-links-premium'); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="enable_branding" id="enable_branding" value="1" <?php checked(isset($branding['enable_branding']) ? $branding['enable_branding'] : 1); ?> class="tlp-branding-setting">
                                    <?php echo esc_html__('Enable custom branding for login page', 'temporary-login-links-premium'); ?>
                                </label>
                                <p class="description"><?php echo esc_html__('When enabled, temporary login pages will use your custom branding.', 'temporary-login-links-premium'); ?></p>
                            </td>
                        </tr>
                        
                        <tr valign="top">
                            <th scope="row"><?php echo esc_html__('Company Name', 'temporary-login-links-premium'); ?></th>
                            <td>
                                <input type="text" name="company_name" id="company_name" value="<?php echo esc_attr(isset($branding['company_name']) ? $branding['company_name'] : get_bloginfo('name')); ?>" class="regular-text tlp-branding-setting">
                                <p class="description"><?php echo esc_html__('Your company or website name for branding.', 'temporary-login-links-premium'); ?></p>
                            </td>
                        </tr>
                        
                        <tr valign="top">
                            <th scope="row"><?php echo esc_html__('Login Logo', 'temporary-login-links-premium'); ?></th>
                            <td>
                                <div class="tlp-media-wrapper">
                                    <input type="text" name="login_logo" id="login_logo" value="<?php echo esc_url(isset($branding['login_logo']) ? $branding['login_logo'] : ''); ?>" class="regular-text tlp-media-input tlp-branding-setting">
                                    <button type="button" class="button tlp-upload-button"><?php echo esc_html__('Select Logo', 'temporary-login-links-premium'); ?></button>
                                    <button type="button" class="button tlp-remove-media" style="<?php echo empty($branding['login_logo']) ? 'display:none;' : ''; ?>"><?php echo esc_html__('Remove', 'temporary-login-links-premium'); ?></button>
                                    
                                    <div class="tlp-media-preview">
                                        <?php if (!empty($branding['login_logo'])) : ?>
                                            <img src="<?php echo esc_url($branding['login_logo']); ?>" alt="Logo preview">
                                        <?php endif; ?>
                                    </div>
                                    
                                    <p class="description"><?php echo esc_html__('Upload your logo for the login page (recommended size: 320×80px).', 'temporary-login-links-premium'); ?></p>
                                </div>
                            </td>
                        </tr>
                        
                        <tr valign="top">
                            <th scope="row"><?php echo esc_html__('Login Background Color', 'temporary-login-links-premium'); ?></th>
                            <td>
                                <div class="tlp-color-picker-wrapper">
                                    <input type="text" name="login_background_color" id="login_background_color" value="<?php echo esc_attr(isset($branding['login_background_color']) ? $branding['login_background_color'] : '#f1f1f1'); ?>" class="tlp-color-picker tlp-branding-setting">
                                </div>
                                <p class="description"><?php echo esc_html__('Background color for the login page.', 'temporary-login-links-premium'); ?></p>
                            </td>
                        </tr>
                        
                        <tr valign="top">
                            <th scope="row"><?php echo esc_html__('Login Form Background', 'temporary-login-links-premium'); ?></th>
                            <td>
                                <div class="tlp-color-picker-wrapper">
                                    <input type="text" name="login_form_background" id="login_form_background" value="<?php echo esc_attr(isset($branding['login_form_background']) ? $branding['login_form_background'] : '#ffffff'); ?>" class="tlp-color-picker tlp-branding-setting">
                                </div>
                                <p class="description"><?php echo esc_html__('Background color for the login form.', 'temporary-login-links-premium'); ?></p>
                            </td>
                        </tr>
                        
                        <tr valign="top">
                            <th scope="row"><?php echo esc_html__('Login Form Text Color', 'temporary-login-links-premium'); ?></th>
                            <td>
                                <div class="tlp-color-picker-wrapper">
                                    <input type="text" name="login_form_text_color" id="login_form_text_color" value="<?php echo esc_attr(isset($branding['login_form_text_color']) ? $branding['login_form_text_color'] : '#333333'); ?>" class="tlp-color-picker tlp-branding-setting">
                                </div>
                                <p class="description"><?php echo esc_html__('Text color for the login form.', 'temporary-login-links-premium'); ?></p>
                            </td>
                        </tr>
                        
                        <tr valign="top">
                            <th scope="row"><?php echo esc_html__('Login Button Color', 'temporary-login-links-premium'); ?></th>
                            <td>
                                <div class="tlp-color-picker-wrapper">
                                    <input type="text" name="login_button_color" id="login_button_color" value="<?php echo esc_attr(isset($branding['login_button_color']) ? $branding['login_button_color'] : '#0085ba'); ?>" class="tlp-color-picker tlp-branding-setting">
                                </div>
                                <p class="description"><?php echo esc_html__('Background color for the login button.', 'temporary-login-links-premium'); ?></p>
                            </td>
                        </tr>
                        
                        <tr valign="top">
                            <th scope="row"><?php echo esc_html__('Login Button Text Color', 'temporary-login-links-premium'); ?></th>
                            <td>
                                <div class="tlp-color-picker-wrapper">
                                    <input type="text" name="login_button_text_color" id="login_button_text_color" value="<?php echo esc_attr(isset($branding['login_button_text_color']) ? $branding['login_button_text_color'] : '#ffffff'); ?>" class="tlp-color-picker tlp-branding-setting">
                                </div>
                                <p class="description"><?php echo esc_html__('Text color for the login button.', 'temporary-login-links-premium'); ?></p>
                            </td>
                        </tr>
                        
                        <tr valign="top">
                            <th scope="row"><?php echo esc_html__('Welcome Text', 'temporary-login-links-premium'); ?></th>
                            <td>
                                <textarea name="login_welcome_text" id="login_welcome_text" rows="4" class="large-text tlp-branding-setting"><?php echo esc_textarea(isset($branding['login_welcome_text']) ? $branding['login_welcome_text'] : __('Welcome! You have been granted temporary access to this site.', 'temporary-login-links-premium')); ?></textarea>
                                <p class="description"><?php echo esc_html__('Welcome message displayed on the login page.', 'temporary-login-links-premium'); ?></p>
                            </td>
                        </tr>
                        
                        <tr valign="top">
                            <th scope="row"><?php echo esc_html__('Custom CSS', 'temporary-login-links-premium'); ?></th>
                            <td>
                                <textarea name="login_custom_css" id="login_custom_css" rows="8" class="large-text code tlp-branding-setting"><?php echo esc_textarea(isset($branding['login_custom_css']) ? $branding['login_custom_css'] : ''); ?></textarea>
                                <p class="description"><?php echo esc_html__('Add custom CSS for additional login page customization.', 'temporary-login-links-premium'); ?></p>
                            </td>
                        </tr>
                        
                        <tr valign="top">
                            <th scope="row"><?php echo esc_html__('Email Branding', 'temporary-login-links-premium'); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="email_branding" id="email_branding" value="1" <?php checked(isset($branding['email_branding']) ? $branding['email_branding'] : 1); ?> class="tlp-branding-setting">
                                    <?php echo esc_html__('Apply branding to email notifications', 'temporary-login-links-premium'); ?>
                                </label>
                                <p class="description"><?php echo esc_html__('When enabled, email notifications will use your custom branding.', 'temporary-login-links-premium'); ?></p>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo esc_html__('Save Branding Settings', 'temporary-login-links-premium'); ?>">
                    </p>
                </div>
            </form>
        </div>
        
        <!-- Right column: Preview -->
        <div class="tlp-branding-preview-container">
            <div class="tlp-branding-preview-box">
                <h3><?php echo esc_html__('Login Page Preview', 'temporary-login-links-premium'); ?></h3>
                
                <div class="tlp-branding-preview" style="background-color: <?php echo esc_attr(isset($branding['login_background_color']) ? $branding['login_background_color'] : '#f1f1f1'); ?>;">
                    <div class="tlp-branding-preview-logo">
                        <?php if (!empty($branding['login_logo'])) : ?>
                            <img src="<?php echo esc_url($branding['login_logo']); ?>" alt="Logo">
                        <?php else : ?>
                            <h2 style="color: <?php echo esc_attr(isset($branding['login_form_text_color']) ? $branding['login_form_text_color'] : '#333333'); ?>;">
                                <?php echo esc_html(isset($branding['company_name']) ? $branding['company_name'] : get_bloginfo('name')); ?>
                            </h2>
                        <?php endif; ?>
                    </div>
                    
                    <div class="tlp-branding-preview-welcome">
                        <?php echo wp_kses_post(isset($branding['login_welcome_text']) ? $branding['login_welcome_text'] : __('Welcome! You have been granted temporary access to this site.', 'temporary-login-links-premium')); ?>
                    </div>
                    
                    <div class="tlp-branding-preview-form" style="background-color: <?php echo esc_attr(isset($branding['login_form_background']) ? $branding['login_form_background'] : '#ffffff'); ?>; color: <?php echo esc_attr(isset($branding['login_form_text_color']) ? $branding['login_form_text_color'] : '#333333'); ?>;">
                        <div class="tlp-form-row">
                            <label for="tlp_preview_username"><?php echo esc_html__('Username or Email Address', 'temporary-login-links-premium'); ?></label>
                            <input type="text" id="tlp_preview_username" disabled>
                        </div>
                        
                        <div class="tlp-form-row">
                            <label for="tlp_preview_password"><?php echo esc_html__('Password', 'temporary-login-links-premium'); ?></label>
                            <input type="password" id="tlp_preview_password" disabled>
                        </div>
                        
                        <div class="tlp-form-row">
                            <button type="button" style="background-color: <?php echo esc_attr(isset($branding['login_button_color']) ? $branding['login_button_color'] : '#0085ba'); ?>; color: <?php echo esc_attr(isset($branding['login_button_text_color']) ? $branding['login_button_text_color'] : '#ffffff'); ?>;">
                                <?php echo esc_html__('Log In', 'temporary-login-links-premium'); ?>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="tlp-branding-preview-footnote">
                    <p><?php echo esc_html__('This is a preview of how your temporary login page will look. Actual appearance may vary slightly depending on the user\'s browser.', 'temporary-login-links-premium'); ?></p>
                </div>
            </div>
            
            <div class="tlp-branding-preview-box">
                <h3><?php echo esc_html__('Email Preview', 'temporary-login-links-premium'); ?></h3>
                
                <div class="tlp-email-preview">
                    <div class="tlp-email-preview-header" style="background-color: <?php echo esc_attr(isset($branding['login_background_color']) ? $branding['login_background_color'] : '#f1f1f1'); ?>;">
                        <?php if (!empty($branding['login_logo'])) : ?>
                            <img src="<?php echo esc_url($branding['login_logo']); ?>" alt="Logo">
                        <?php else : ?>
                            <h2 style="color: <?php echo esc_attr(isset($branding['login_form_text_color']) ? $branding['login_form_text_color'] : '#333333'); ?>;">
                                <?php echo esc_html(isset($branding['company_name']) ? $branding['company_name'] : get_bloginfo('name')); ?>
                            </h2>
                        <?php endif; ?>
                    </div>
                    
                    <div class="tlp-email-preview-content">
                        <p><?php echo esc_html__('Hello John,', 'temporary-login-links-premium'); ?></p>
                        
                        <p><?php 
                        $company = isset($branding['company_name']) ? $branding['company_name'] : get_bloginfo('name');
                        /* translators: %s Company name */        
                        printf(esc_html__('You have been granted temporary access to %s with Editor privileges.', 'temporary-login-links-premium'), 
                            '<strong>' . esc_html($company) . '</strong>'
                        ); 
                        ?></p>
                        
                        <div class="tlp-email-button">
                            <a href="#" style="background-color: <?php echo esc_attr(isset($branding['login_button_color']) ? $branding['login_button_color'] : '#0085ba'); ?>; color: <?php echo esc_attr(isset($branding['login_button_text_color']) ? $branding['login_button_text_color'] : '#ffffff'); ?>;">
                                <?php echo esc_html__('Log In Now', 'temporary-login-links-premium'); ?>
                            </a>
                        </div>
                        
                        <p><?php echo esc_html__('This link will expire on May 15, 2025 at 12:00 PM.', 'temporary-login-links-premium'); ?></p>
                    </div>
                    
                    <div class="tlp-email-preview-footer" style="background-color: <?php echo esc_attr(isset($branding['login_background_color']) ? $branding['login_background_color'] : '#f1f1f1'); ?>;">
                        <p><?php 
                        $company = isset($branding['company_name']) ? $branding['company_name'] : get_bloginfo('name');
                        /* translators: %s Company name */    
                        printf(esc_html__('Regards,<br>%s Team', 'temporary-login-links-premium'), 
                            esc_html($company)
                        ); 
                        ?></p>
                    </div>
                </div>
                
                <div class="tlp-branding-preview-footnote">
                    <p><?php echo esc_html__('This is a preview of how your email notifications will look. Actual appearance may vary slightly depending on the email client.', 'temporary-login-links-premium'); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Initialize color pickers
    if ($.fn.wpColorPicker) {
        $('.tlp-color-picker').wpColorPicker({
            change: function(event, ui) {
                // Update preview when color changes
                updatePreview();
            }
        });
    }
    
    // Initialize media uploader
    $('.tlp-upload-button').on('click', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var previewContainer = button.siblings('.tlp-media-preview');
        var inputField = button.siblings('.tlp-media-input');
        var removeButton = button.siblings('.tlp-remove-media');
        
        var customUploader = wp.media({
            title: '<?php esc_html__('Select Logo', 'temporary-login-links-premium'); ?>',
            button: {
                text: '<?php esc_html__('Use this image', 'temporary-login-links-premium'); ?>'
            },
            multiple: false
        }).on('select', function() {
            var attachment = customUploader.state().get('selection').first().toJSON();
            inputField.val(attachment.url);
            previewContainer.html('<img src="' + attachment.url + '" alt="Logo preview">');
            removeButton.show();
            
            // Update preview
            updatePreview();
        }).open();
    });
    
    // Remove media
    $('.tlp-remove-media').on('click', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var previewContainer = button.siblings('.tlp-media-preview');
        var inputField = button.siblings('.tlp-media-input');
        
        inputField.val('');
        previewContainer.html('');
        button.hide();
        
        // Update preview
        updatePreview();
    });
    
    // Update preview on input change
    $('.tlp-branding-setting').on('change input', function() {
        updatePreview();
    });
    
    // Update preview function
    function updatePreview() {
        // Get values
        var enableBranding = $('#enable_branding').is(':checked');
        var companyName = $('#company_name').val();
        var logoUrl = $('#login_logo').val();
        var backgroundColor = $('#login_background_color').val();
        var formBackground = $('#login_form_background').val();
        var formTextColor = $('#login_form_text_color').val();
        var buttonColor = $('#login_button_color').val();
        var buttonTextColor = $('#login_button_text_color').val();
        var welcomeText = $('#login_welcome_text').val();
        var emailBranding = $('#email_branding').is(':checked');
        
        // Update login preview
        $('.tlp-branding-preview').css('background-color', backgroundColor);
        
        if (logoUrl) {
            $('.tlp-branding-preview-logo').html('<img src="' + logoUrl + '" alt="Logo">');
        } else {
            $('.tlp-branding-preview-logo').html('<h2 style="color: ' + formTextColor + '">' + companyName + '</h2>');
        }
        
        $('.tlp-branding-preview-form').css({
            'background-color': formBackground,
            'color': formTextColor
        });
        
        $('.tlp-branding-preview-form button').css({
            'background-color': buttonColor,
            'color': buttonTextColor
        });
        
        $('.tlp-branding-preview-welcome').html(welcomeText);
        
        // Update email preview
        $('.tlp-email-preview-header').css('background-color', backgroundColor);
        
        if (logoUrl) {
            $('.tlp-email-preview-header').html('<img src="' + logoUrl + '" alt="Logo">');
        } else {
            $('.tlp-email-preview-header').html('<h2 style="color: ' + formTextColor + '">' + companyName + '</h2>');
        }
        
        $('.tlp-email-button a').css({
            'background-color': buttonColor,
            'color': buttonTextColor
        });
        
        $('.tlp-email-preview-content p:nth-child(2) strong').text(companyName);
        $('.tlp-email-preview-footer p').html('<?php esc_html__('Regards,<br>', 'temporary-login-links-premium'); ?>' + companyName + ' <?php esc_html__('Team', 'temporary-login-links-premium'); ?>');
        
        $('.tlp-email-preview-footer').css('background-color', backgroundColor);
    }
    
    // Initially update preview
    updatePreview();
});
</script>
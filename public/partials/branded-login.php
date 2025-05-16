<?php
/**
 * Template for the branded login page.
 *
 * This file provides the HTML for the branded login page when a user
 * accesses the site via a temporary login link. It customizes the
 * WordPress login page with branding options from the plugin settings.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Temporary_Login_Links_Premium
 * @subpackage Temporary_Login_Links_Premium/public/partials
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Get token from URL
$token = isset($_GET['temp_login']) ? sanitize_text_field($_GET['temp_login']) : '';

if (empty($token)) {
    return;
}

// Get branding settings
$branding = get_option('temporary_login_links_premium_branding', array());

// Check if branding is enabled
if (empty($branding['enable_branding']) || $branding['enable_branding'] != 1) {
    return;
}

// Get link details
global $wpdb;
$table_name = $wpdb->prefix . 'temporary_login_links';

$link = $wpdb->get_row(
    $wpdb->prepare(
        "SELECT * FROM $table_name WHERE link_token = %s LIMIT 1",
        $token
    )
);

// Default values
$company_name = isset($branding['company_name']) ? $branding['company_name'] : get_bloginfo('name');
$welcome_text = isset($branding['login_welcome_text']) ? $branding['login_welcome_text'] : __('Welcome! You have been granted temporary access to this site.', 'temporary-login-links-premium');
$background_color = isset($branding['login_background_color']) ? $branding['login_background_color'] : '#f1f1f1';
$form_background = isset($branding['login_form_background']) ? $branding['login_form_background'] : '#ffffff';
$form_text_color = isset($branding['login_form_text_color']) ? $branding['login_form_text_color'] : '#333333';
$button_color = isset($branding['login_button_color']) ? $branding['login_button_color'] : '#0085ba';
$button_text_color = isset($branding['login_button_text_color']) ? $branding['login_button_text_color'] : '#ffffff';
$logo_url = isset($branding['login_logo']) ? $branding['login_logo'] : '';
$custom_css = isset($branding['login_custom_css']) ? $branding['login_custom_css'] : '';
?>

<style type="text/css">
    /* Custom branding styles */
    body.login {
        background-color: <?php echo esc_attr($background_color); ?>;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
        line-height: 1.5;
    }
    
    /* Logo */
    body.login h1 a {
        <?php if (!empty($logo_url)) : ?>
        background-image: url(<?php echo esc_url($logo_url); ?>);
        background-size: contain;
        background-position: center center;
        width: 320px;
        height: 80px;
        <?php else : ?>
        background-image: none;
        width: auto;
        height: auto;
        text-indent: 0;
        <?php endif; ?>
        margin: 0 auto 30px;
        padding: 0;
    }
    
    <?php if (empty($logo_url)) : ?>
    body.login h1 a:before {
        content: "<?php echo esc_attr($company_name); ?>";
        font-size: 24px;
        font-weight: 600;
        color: <?php echo esc_attr($form_text_color); ?>;
        display: block;
        text-align: center;
    }
    <?php endif; ?>
    
    /* Form */
    body.login #loginform {
        background-color: <?php echo esc_attr($form_background); ?>;
        color: <?php echo esc_attr($form_text_color); ?>;
        border: none;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12);
        border-radius: 4px;
        padding: 30px;
    }
    
    body.login label {
        color: <?php echo esc_attr($form_text_color); ?>;
        font-size: 14px;
        font-weight: 500;
    }
    
    body.login input[type="text"],
    body.login input[type="password"] {
        background-color: <?php echo esc_attr($this->adjust_brightness($form_background, -5)); ?>;
        border: 1px solid <?php echo esc_attr($this->adjust_brightness($form_background, -10)); ?>;
        color: <?php echo esc_attr($form_text_color); ?>;
        border-radius: 4px;
        padding: 10px 12px;
        font-size: 16px;
        box-shadow: none;
    }
    
    body.login input[type="text"]:focus,
    body.login input[type="password"]:focus {
        border-color: <?php echo esc_attr($button_color); ?>;
        box-shadow: 0 0 0 1px <?php echo esc_attr($button_color); ?>;
    }
    
    /* Button */
    body.login .button.button-primary {
        background-color: <?php echo esc_attr($button_color); ?>;
        border-color: <?php echo esc_attr($this->adjust_brightness($button_color, -10)); ?>;
        color: <?php echo esc_attr($button_text_color); ?>;
        text-shadow: none;
        box-shadow: 0 1px 0 <?php echo esc_attr($this->adjust_brightness($button_color, -15)); ?>;
        border-radius: 4px;
        padding: 6px 12px;
        height: auto;
        line-height: 1.5;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.2s ease;
    }
    
    body.login .button.button-primary:hover,
    body.login .button.button-primary:focus {
        background-color: <?php echo esc_attr($this->adjust_brightness($button_color, -10)); ?>;
        border-color: <?php echo esc_attr($this->adjust_brightness($button_color, -15)); ?>;
        color: <?php echo esc_attr($button_text_color); ?>;
        box-shadow: 0 1px 0 <?php echo esc_attr($this->adjust_brightness($button_color, -20)); ?>;
    }
    
    /* Welcome message */
    .tlp-welcome-message {
        background-color: <?php echo esc_attr($this->hex_to_rgba($form_background, 0.9)); ?>;
        color: <?php echo esc_attr($form_text_color); ?>;
        border-radius: 4px;
        padding: 15px 20px;
        margin-bottom: 25px;
        text-align: center;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        font-size: 16px;
        line-height: 1.5;
    }
    
    /* Status messages */
    .tlp-login-message .tlp-status-message {
        padding: 12px 15px;
        border-radius: 4px;
        margin-bottom: 15px;
        font-size: 14px;
        line-height: 1.5;
    }
    
    .tlp-login-message .tlp-status-active {
        background-color: #dff2e0;
        color: #2a8b32;
        border: 1px solid #c0e9c2;
    }
    
    .tlp-login-message .tlp-status-inactive,
    .tlp-login-message .tlp-status-expired,
    .tlp-login-message .tlp-status-maxed {
        background-color: #fbe9e7;
        color: #c62828;
        border: 1px solid #f5c1bb;
    }
    
    /* Loading indicator */
    .tlp-loading-indicator {
        text-align: center;
        padding: 10px;
        margin-top: 15px;
        color: <?php echo esc_attr($this->adjust_brightness($form_text_color, 20)); ?>;
        font-style: italic;
    }
    
    /* Hide elements */
    body.login.temporary-login #nav,
    body.login.temporary-login #backtoblog {
        display: none;
    }
    
    /* Animations */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .tlp-welcome-message,
    .tlp-login-message {
        animation: fadeIn 0.3s ease-out;
    }
    
    @keyframes pulse {
        0% { opacity: 0.6; }
        50% { opacity: 1; }
        100% { opacity: 0.6; }
    }
    
    .tlp-loading-indicator {
        animation: pulse 1.5s infinite ease-in-out;
    }
    
    /* Responsive adjustments */
    @media screen and (max-width: 782px) {
        body.login #login {
            width: 90%;
            max-width: 360px;
        }
        
        body.login h1 a {
            width: 100%;
            background-size: contain;
        }
    }
    
    /* Custom CSS from settings */
    <?php echo $custom_css; ?>
</style>

<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        // Add temporary-login class to body
        document.body.classList.add('temporary-login');
        
        <?php if ($link && $link->is_active == 1 && strtotime($link->expiry) > time() && ($link->max_accesses == 0 || $link->access_count < $link->max_accesses)) : ?>
        // Add auto-login functionality
        var usernameField = document.getElementById('user_login');
        if (usernameField) {
            usernameField.value = '<?php echo esc_js($link->user_login); ?>';
        }
        
        // Hide the password field
        var passwordField = document.getElementById('user_pass');
        var passwordLabel = document.querySelector('label[for="user_pass"]');
        
        if (passwordField && passwordLabel) {
            passwordField.parentNode.style.display = 'none';
            passwordLabel.style.display = 'none';
        }
        
        // Hide remember me checkbox
        var rememberMe = document.querySelector('.forgetmenot');
        if (rememberMe) {
            rememberMe.style.display = 'none';
        }
        
        // Change submit button text
        var submitButton = document.getElementById('wp-submit');
        if (submitButton) {
            submitButton.value = '<?php echo esc_js(__('Access Site', 'temporary-login-links-premium')); ?>';
            
            // Add loading indicator
            var form = document.getElementById('loginform');
            if (form) {
                var loadingIndicator = document.createElement('div');
                loadingIndicator.className = 'tlp-loading-indicator';
                loadingIndicator.textContent = '<?php echo esc_js(__('Logging in automatically...', 'temporary-login-links-premium')); ?>';
                form.appendChild(loadingIndicator);
            }
            
            // Auto-submit the form
            setTimeout(function() {
                document.getElementById('loginform').submit();
            }, 1500);
        }
        <?php endif; ?>
    });
</script>
<?php

/**
 * Helper function to adjust brightness of a hex color.
 *
 * @since    1.0.0
 * @param    string    $hex        The hex color.
 * @param    int       $steps      The brightness adjustment (-255 to 255).
 * @return   string                The adjusted hex color.
 */
private function adjust_brightness($hex, $steps) {
    // Remove # if present
    $hex = ltrim($hex, '#');
    
    // Convert to RGB
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    
    // Adjust brightness
    $r = max(0, min(255, $r + $steps));
    $g = max(0, min(255, $g + $steps));
    $b = max(0, min(255, $b + $steps));
    
    // Convert back to hex
    return sprintf('#%02x%02x%02x', $r, $g, $b);
}

/**
 * Helper function to convert hex to rgba.
 *
 * @since    1.0.0
 * @param    string    $hex        The hex color.
 * @param    float     $alpha      The alpha value (0-1).
 * @return   string                The rgba color.
 */
private function hex_to_rgba($hex, $alpha = 1) {
    // Remove # if present
    $hex = ltrim($hex, '#');
    
    // Convert to RGB
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    
    // Return rgba
    return "rgba($r, $g, $b, $alpha)";
}
?>
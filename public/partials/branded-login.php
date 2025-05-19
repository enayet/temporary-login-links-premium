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

// Check if auto login is specified
$auto_login = isset($_GET['auto']) && $_GET['auto'] == '1';

// Get link details
global $wpdb;
$table_name = $wpdb->prefix . 'temporary_login_links';

$link = $wpdb->get_row(
    $wpdb->prepare(
        "SELECT * FROM $table_name WHERE link_token = %s LIMIT 1",
        $token
    ),
    ARRAY_A
);

if (!$link) {
    // Invalid token, let the normal login process handle it
    return;
}

// Get branding settings
$branding = get_option('temporary_login_links_premium_branding', array());

// Check if branding is enabled
if (empty($branding['enable_branding']) || $branding['enable_branding'] != 1) {
    // If auto login is specified, proceed with auto login
    if ($auto_login) {
        return;
    }
    
    // Otherwise, display the default login form
    return;
}

// Check link status
$is_active = !empty($link['is_active']);
$is_expired = strtotime($link['expiry']) < time();
$is_max_accesses = $link['max_accesses'] > 0 && $link['access_count'] >= $link['max_accesses'];

// Default values
$company_name = isset($branding['company_name']) ? $branding['company_name'] : get_bloginfo('name');
$welcome_text = isset($branding['login_welcome_text']) ? $branding['login_welcome_text'] : esc_html__('Welcome! You have been granted temporary access to this site.', 'temporary-login-links-premium');
$background_color = isset($branding['login_background_color']) ? $branding['login_background_color'] : '#f1f1f1';
$form_background = isset($branding['login_form_background']) ? $branding['login_form_background'] : '#ffffff';
$form_text_color = isset($branding['login_form_text_color']) ? $branding['login_form_text_color'] : '#333333';
$button_color = isset($branding['login_button_color']) ? $branding['login_button_color'] : '#0085ba';
$button_text_color = isset($branding['login_button_text_color']) ? $branding['login_button_text_color'] : '#ffffff';
$logo_url = isset($branding['login_logo']) ? $branding['login_logo'] : '';
$custom_css = isset($branding['login_custom_css']) ? $branding['login_custom_css'] : '';

// Adjust brightness function
function adjust_brightness($hex, $steps) {
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

// Convert hex to rgba function
function hex_to_rgba($hex, $alpha = 1) {
    // Remove # if present
    $hex = ltrim($hex, '#');
    
    // Convert to RGB
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    
    // Return rgba
    return "rgba($r, $g, $b, $alpha)";
}

// Get user data if link is valid
$user_data = null;
if ($is_active && !$is_expired && !$is_max_accesses) {
    $user = get_user_by('id', $link['user_id']);
    if ($user) {
        $user_data = array(
            'display_name' => $user->display_name,
            'first_name' => get_user_meta($user->ID, 'first_name', true),
            'role' => $link['role']
        );
    }
}

// Get role display name
function get_role_display_name($role) {
    global $wp_roles;
    
    if (!isset($wp_roles)) {
        $wp_roles = new WP_Roles();
    }
    
    return isset($wp_roles->roles[$role]) ? translate_user_role($wp_roles->roles[$role]['name']) : $role;
}

// Generate auto-login URL
$auto_login_url = add_query_arg('auto', '1', $_SERVER['REQUEST_URI']);
?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html($company_name); ?> - <?php esc_html__('Temporary Access', 'temporary-login-links-premium'); ?></title>
    <?php wp_head(); ?>
    <style>
        body {
            background-color: <?php echo esc_attr($background_color); ?>;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            align-items: center;
            justify-content: center;
        }
        
        .tlp-branded-container {
            width: 100%;
            max-width: 500px;
            padding: 20px;
            box-sizing: border-box;
        }
        
        .tlp-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .tlp-logo {
            max-width: 280px;
            max-height: 100px;
            margin: 0 auto 20px;
        }
        
        .tlp-company-name {
            font-size: 24px;
            font-weight: 600;
            color: <?php echo esc_attr($form_text_color); ?>;
            margin: 0;
        }
        
        .tlp-content {
            background-color: <?php echo esc_attr($form_background); ?>;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        
        .tlp-welcome {
            font-size: 16px;
            color: <?php echo esc_attr($form_text_color); ?>;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .tlp-user-info {
            margin-bottom: 25px;
            color: <?php echo esc_attr($form_text_color); ?>;
        }
        
        .tlp-access-info {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .tlp-access-active {
            background-color: #dff2e0;
            color: #2a8b32;
            border: 1px solid #c0e9c2;
        }
        
        .tlp-access-inactive {
            background-color: #fbe9e7;
            color: #c62828;
            border: 1px solid #f5c1bb;
        }
        
        .tlp-access-cta {
            text-align: center;
            margin-top: 25px;
        }
        
        .tlp-button {
            display: inline-block;
            background-color: <?php echo esc_attr($button_color); ?>;
            color: <?php echo esc_attr($button_text_color); ?>;
            border: none;
            border-radius: 4px;
            padding: 12px 25px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.2s ease;
        }
        
        .tlp-button:hover {
            background-color: <?php echo esc_attr(adjust_brightness($button_color, -15)); ?>;
            color: <?php echo esc_attr($button_text_color); ?>;
        }
        
        .tlp-expiry-info {
            margin-top: 15px;
            font-size: 14px;
            text-align: center;
            color: <?php echo esc_attr(adjust_brightness($form_text_color, 20)); ?>;
        }
        
        .tlp-footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: <?php echo esc_attr(adjust_brightness($form_text_color, 20)); ?>;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .tlp-content {
            animation: fadeIn 0.4s ease-out;
        }
        
        /* Custom CSS */
        <?php echo $custom_css; ?>
        
        /* Responsive */
        @media screen and (max-width: 600px) {
            .tlp-branded-container {
                padding: 15px;
            }
            
            .tlp-content {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="tlp-branded-container">
        <div class="tlp-header">
            <?php if (!empty($logo_url)) : ?>
                <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($company_name); ?>" class="tlp-logo">
            <?php else : ?>
                <h1 class="tlp-company-name"><?php echo esc_html($company_name); ?></h1>
            <?php endif; ?>
        </div>
        
        <div class="tlp-content">
            <div class="tlp-welcome">
                <?php echo wp_kses_post($welcome_text); ?>
            </div>
            
            <?php if ($user_data) : ?>
                <div class="tlp-user-info">
                    <p>
                        <?php 
                        
                        /* translators: %s First name  */
                        $greeting = !empty($user_data['first_name']) ? sprintf(esc_html__('Hello, %s!', 'temporary-login-links-premium'), esc_html($user_data['first_name'])) : esc_html__('Hello!', 'temporary-login-links-premium');
                        echo $greeting;
                        ?>
                    </p>
                    <p>
                        <?php 
                        
                            /* translators: Company name and Role  */
                            printf(esc_html__('You have been granted temporary access to %1$s with %2$s privileges.', 'temporary-login-links-premium'),
                            '<strong>' . esc_html(get_bloginfo('name')) . '</strong>',
                            '<strong>' . esc_html(get_role_display_name($user_data['role'])) . '</strong>'
                        ); ?>
                    </p>
                </div>
                
                <div class="tlp-access-info tlp-access-active">
                    <p style="margin: 0;"><?php echo esc_html__('Your temporary login link is valid and ready to use.', 'temporary-login-links-premium'); ?></p>
                </div>
                
                <div class="tlp-access-cta">
                    <a href="<?php echo esc_url($auto_login_url); ?>" class="tlp-button">
                        <?php echo esc_html__('Access Site', 'temporary-login-links-premium'); ?>
                    </a>
                </div>
                
                <div class="tlp-expiry-info">
                    <?php 
                        /* translators: %s expiry date  */
                        printf(esc_html__('This link will expire on %s.', 'temporary-login-links-premium'),
                        '<strong>' . date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($link['expiry'])) . '</strong>'
                    ); ?>
                    
                    <?php if ($link['max_accesses'] > 0) : ?>
                        <br>
                        <?php 
                            /* translators: %d max accesses  */
                            printf(esc_html__('This link can be used %d more time(s).', 'temporary-login-links-premium'),
                            $link['max_accesses'] - $link['access_count']
                        ); ?>
                    <?php endif; ?>
                </div>
            <?php elseif (!$is_active) : ?>
                <div class="tlp-access-info tlp-access-inactive">
                    <p style="margin: 0;"><?php esc_html__('This login link has been deactivated.', 'temporary-login-links-premium'); ?></p>
                </div>
            <?php elseif ($is_expired) : ?>
                <div class="tlp-access-info tlp-access-inactive">
                    <p style="margin: 0;"><?php esc_html__('This login link has expired.', 'temporary-login-links-premium'); ?></p>
                </div>
            <?php elseif ($is_max_accesses) : ?>
                <div class="tlp-access-info tlp-access-inactive">
                    <p style="margin: 0;"><?php esc_html__('This login link has reached its maximum number of uses.', 'temporary-login-links-premium'); ?></p>
                </div>
            <?php else : ?>
                <div class="tlp-access-info tlp-access-inactive">
                    <p style="margin: 0;"><?php esc_html__('There was an issue with this login link.', 'temporary-login-links-premium'); ?></p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="tlp-footer">
            <p><?php 
                /* translators: Year and company name  */
                printf(esc_html__('&copy; %1$s %2$s', 'temporary-login-links-premium'), date('Y'), esc_html($company_name)); ?></p>
        </div>
    </div>
    
    <?php wp_footer(); ?>
</body>
</html>
<?php
// Exit to prevent default WordPress login page from loading
exit();
?>
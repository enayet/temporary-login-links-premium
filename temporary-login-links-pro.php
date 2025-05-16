<?php
/**
 * Plugin Name:       Temporary Login Links Pro
 * Plugin URI:        https://example.com/temporary-login-links-premium
 * Description:       Create secure, branded, temporary login links with advanced controls for WordPress.
 * Version:           1.0.0
 * Author:            Your Name
 * Author URI:        https://example.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       temporary-login-links-premium
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Current plugin version.
 */
define( 'TEMPORARY_LOGIN_LINKS_PREMIUM_VERSION', '1.0.0' );

/**
 * Plugin basename.
 */
define( 'TEMPORARY_LOGIN_LINKS_PREMIUM_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Plugin directory path.
 */
define( 'TEMPORARY_LOGIN_LINKS_PREMIUM_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Plugin directory URL.
 */
define( 'TEMPORARY_LOGIN_LINKS_PREMIUM_URL', plugin_dir_url( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-tlp-activator.php
 */
function activate_temporary_login_links_premium() {
    require_once TEMPORARY_LOGIN_LINKS_PREMIUM_DIR . 'includes/class-tlp-activator.php';
    Temporary_Login_Links_Premium_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-tlp-deactivator.php
 */
function deactivate_temporary_login_links_premium() {
    require_once TEMPORARY_LOGIN_LINKS_PREMIUM_DIR . 'includes/class-tlp-deactivator.php';
    Temporary_Login_Links_Premium_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_temporary_login_links_premium' );
register_deactivation_hook( __FILE__, 'deactivate_temporary_login_links_premium' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require TEMPORARY_LOGIN_LINKS_PREMIUM_DIR . 'includes/class-temporary-login-links-premium.php';

/**
 * Begins execution of the plugin.
 *
 * @since    1.0.0
 */
function run_temporary_login_links_premium() {
    $plugin = new Temporary_Login_Links_Premium();
    $plugin->run();
}

/**
 * Check if free version is active and display admin notice if it is
 */
function tlp_check_free_version() {
    if ( is_plugin_active( 'temporary-login-without-password/temporary-login-without-password.php' ) ) {
        add_action( 'admin_notices', 'tlp_free_version_notice' );
    }
}
add_action( 'admin_init', 'tlp_check_free_version' );

/**
 * Admin notice for free version conflict
 */
function tlp_free_version_notice() {
    ?>
    <div class="notice notice-warning is-dismissible">
        <p><?php _e( 'You have both Temporary Login Links Premium and the free Temporary Login Without Password plugin activated. We recommend deactivating the free version to avoid conflicts.', 'temporary-login-links-premium' ); ?></p>
    </div>
    <?php
}

/**
 * Add plugin action links
 * 
 * @param array $links Plugin action links
 * @return array Modified plugin action links
 */
function tlp_plugin_action_links( $links ) {
    $plugin_links = array(
        '<a href="' . admin_url( 'admin.php?page=temporary-login-links-premium' ) . '">' . __( 'Settings', 'temporary-login-links-premium' ) . '</a>',
        '<a href="' . admin_url( 'admin.php?page=temporary-login-links-premium-links' ) . '">' . __( 'Manage Links', 'temporary-login-links-premium' ) . '</a>',
    );
    return array_merge( $plugin_links, $links );
}
add_filter( 'plugin_action_links_' . TEMPORARY_LOGIN_LINKS_PREMIUM_BASENAME, 'tlp_plugin_action_links' );

// Run the plugin
run_temporary_login_links_premium();
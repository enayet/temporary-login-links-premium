<?php
/**
 * Template for the links list page.
 *
 * This file provides the HTML for displaying the list of temporary login links,
 * with filtering, actions, and pagination functionality.
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

<div class="wrap tlp-wrap tlp-list-table-page">
    <h1 class="wp-heading-inline"><?php echo esc_html__('Manage Temporary Login Links', 'temporary-login-links-premium'); ?></h1>
    <a href="<?php echo esc_url(admin_url('admin.php?page=temporary-login-links-premium-links&action=create')); ?>" class="page-title-action"><?php echo esc_html__('Add New', 'temporary-login-links-premium'); ?></a>
    
    <!-- Display admin notices -->
    <?php $this->display_admin_notices(); ?>
    
    <!-- Search box -->
    <form method="post">
        <?php $list_table->search_box(__('Search Links', 'temporary-login-links-premium'), 'tlp-search'); ?>
        
        <!-- Required for bulk actions -->
        <?php $list_table->display(); ?>
    </form>
</div>

<!-- Extend Modal -->
<div class="tlp-modal-backdrop" style="display: none;"></div>
<div id="tlp-extend-modal" class="tlp-modal" style="display: none;">
    <div class="tlp-modal-header">
        <h3><?php esc_html__('Extend Expiry Date', 'temporary-login-links-premium'); ?></h3>
        <span class="tlp-modal-close dashicons dashicons-no-alt"></span>
    </div>
    <div class="tlp-modal-content">
        <p><?php esc_html__('Choose how long to extend the expiry date:', 'temporary-login-links-premium'); ?></p>
        
        <select id="tlp-extend-duration" class="regular-text">
            <option value="1 day"><?php esc_html__('1 Day', 'temporary-login-links-premium'); ?></option>
            <option value="3 days"><?php esc_html__('3 Days', 'temporary-login-links-premium'); ?></option>
            <option value="7 days" selected><?php esc_html__('7 Days', 'temporary-login-links-premium'); ?></option>
            <option value="14 days"><?php esc_html__('14 Days', 'temporary-login-links-premium'); ?></option>
            <option value="1 month"><?php esc_html__('1 Month', 'temporary-login-links-premium'); ?></option>
            <option value="3 months"><?php esc_html__('3 Months', 'temporary-login-links-premium'); ?></option>
            <option value="6 months"><?php esc_html__('6 Months', 'temporary-login-links-premium'); ?></option>
            <option value="1 year"><?php esc_html__('1 Year', 'temporary-login-links-premium'); ?></option>
        </select>
    </div>
    <div class="tlp-modal-footer">
        <button type="button" class="button tlp-modal-cancel"><?php esc_html__('Cancel', 'temporary-login-links-premium'); ?></button>
        <button type="button" class="button button-primary tlp-extend-link-submit" data-id="0"><?php esc_html__('Extend', 'temporary-login-links-premium'); ?></button>
    </div>
</div>
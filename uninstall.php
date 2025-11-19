<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * @link       http://example.com
 * @since      1.0.0
 * @package    Redirect_Manager_Pro
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Add your uninstall logic here.
// For example, deleting options or custom tables.
// delete_option( 'redirect_manager_pro_option' );

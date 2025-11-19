<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * administrative area. This file also includes all of the dependencies used by
 * the plugin, registers the activation and deactivation functions, and defines
 * a function that starts the plugin.
 *
 * @link              http://example.com
 * @since             1.0.0
 * @package           Redirect_Manager_Pro
 *
 * @wordpress-plugin
 * Plugin Name:       Redirect Manager Pro
 * Plugin URI:        http://example.com/redirect-manager-pro/
 * Description:       A lightweight, high-performance redirection manager with smart typo detection and link scanning.
 * Version:           1.0.0
 * Author:            Sadewadee
 * Author URI:        http://example.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       redirect-manager-pro
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'RMP_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-rmp-activator.php
 */
function activate_redirect_manager_pro() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-rmp-activator.php';
	Redirect_Manager_Pro_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-rmp-deactivator.php
 */
function deactivate_redirect_manager_pro() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-rmp-deactivator.php';
	Redirect_Manager_Pro_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_redirect_manager_pro' );
register_deactivation_hook( __FILE__, 'deactivate_redirect_manager_pro' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-redirect-manager-pro.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_redirect_manager_pro() {

	$plugin = new Redirect_Manager_Pro();
	$plugin->run();

}
run_redirect_manager_pro();

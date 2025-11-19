<?php

/**
 * Fired during plugin activation
 *
 * @link       http://example.com
 * @since      1.0.0
 * @package    Redirect_Manager_Pro
 * @subpackage Redirect_Manager_Pro/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Redirect_Manager_Pro
 * @subpackage Redirect_Manager_Pro/includes
 * @author     Sadewadee
 */
class Redirect_Manager_Pro_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		require_once plugin_dir_path( __FILE__ ) . 'class-rmp-database.php';
		Redirect_Manager_Pro_Database::create_tables();
	}

}

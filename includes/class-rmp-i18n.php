<?php

/**
 * Define the internationalization functionality
 *
 * @link       http://example.com
 * @since      1.0.0
 * @package    Redirect_Manager_Pro
 * @subpackage Redirect_Manager_Pro/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Redirect_Manager_Pro
 * @subpackage Redirect_Manager_Pro/includes
 * @author     Your Name <email@example.com>
 */
class Redirect_Manager_Pro_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'redirect-manager-pro',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}

<?php

/**
 * Fired during plugin execution
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Redirect_Manager_Pro
 * @subpackage Redirect_Manager_Pro/includes
 */

/**
 * Fired during plugin execution.
 *
 * This class defines the core redirection logic.
 *
 * @since      1.0.0
 * @package    Redirect_Manager_Pro
 * @subpackage Redirect_Manager_Pro/includes
 * @author     Sadewadee
 */
class Redirect_Manager_Pro_Redirector {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

	}

	/**
	 * Execute the redirection logic.
	 *
	 * @since    1.0.0
	 */
	public function redirect() {
		if ( is_admin() ) {
			return;
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'rmp_redirects';

		// Get current path
		$request_uri = $_SERVER['REQUEST_URI'];
		$path = parse_url( $request_uri, PHP_URL_PATH );
		$query = parse_url( $request_uri, PHP_URL_QUERY );

		// Normalize path (remove trailing slash for matching if needed, but keep it consistent)
		// For now, let's try exact match with the path as is.

		// 1. Exact Match
		$redirect = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE url_from = %s AND type = 'exact'", $path ) );

		if ( ! $redirect ) {
			// Try with/without trailing slash
			$alt_path = ( substr( $path, -1 ) == '/' ) ? rtrim( $path, '/' ) : $path . '/';
			$redirect = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE url_from = %s AND type = 'exact'", $alt_path ) );
		}

		// 2. Wildcard Match (Simple * support)
		if ( ! $redirect ) {
			// Fetch all wildcards - caching recommended here for performance in production
			$wildcards = $wpdb->get_results( "SELECT * FROM $table_name WHERE type = 'wildcard'" );
			foreach ( $wildcards as $wc ) {
				$pattern = str_replace( '*', '.*', $wc->url_from );
				if ( preg_match( "#^$pattern$#", $path ) ) {
					$redirect = $wc;
					break;
				}
			}
		}

		// 3. Regex Match
		if ( ! $redirect ) {
			$regexs = $wpdb->get_results( "SELECT * FROM $table_name WHERE type = 'regex'" );
			foreach ( $regexs as $rx ) {
				if ( preg_match( "#" . $rx->url_from . "#", $path ) ) {
					$redirect = $rx;
					break;
				}
			}
		}

		if ( $redirect ) {
			// Increment hits
			$wpdb->query( $wpdb->prepare( "UPDATE $table_name SET hits = hits + 1 WHERE id = %d", $redirect->id ) );

			// Perform Redirect
			wp_redirect( $redirect->url_to, $redirect->status );
			exit;
		}
	}

}

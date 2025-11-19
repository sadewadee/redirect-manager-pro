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
 * This class defines the 404 monitoring and typo detection logic.
 *
 * @since      1.0.0
 * @package    Redirect_Manager_Pro
 * @subpackage Redirect_Manager_Pro/includes
 * @author     Sadewadee
 */
class Redirect_Manager_Pro_Typo_Fixer {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

	}

	/**
	 * Handle 404 events.
	 *
	 * @since    1.0.0
	 */
	public function handle_404() {
		if ( ! is_404() ) {
			return;
		}

		global $wpdb;
		$request_uri = $_SERVER['REQUEST_URI'];
		$path = parse_url( $request_uri, PHP_URL_PATH );
		$slug = basename( untrailingslashit( $path ) );

		// 1. Log the 404
		$table_logs = $wpdb->prefix . 'rmp_404_logs';
		$wpdb->insert(
			$table_logs,
			array(
				'url'        => $request_uri,
				'ip'         => $_SERVER['REMOTE_ADDR'],
				'user_agent' => isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '',
			),
			array( '%s', '%s', '%s' )
		);

		// 2. Smart Typo Detection
		// Don't run on short slugs to avoid false positives
		if ( strlen( $slug ) < 4 ) {
			return;
		}

		// Get all public post slugs (cached)
		$slugs = wp_cache_get( 'rmp_all_slugs', 'rmp' );
		if ( false === $slugs ) {
			$slugs = $wpdb->get_col( "SELECT post_name FROM $wpdb->posts WHERE post_status = 'publish' AND post_type IN ('post', 'page')" );
			wp_cache_set( 'rmp_all_slugs', $slugs, 'rmp', 3600 ); // Cache for 1 hour
		}

		$shortest = -1;
		$closest = '';

		foreach ( $slugs as $s ) {
			$lev = levenshtein( $slug, $s );

			if ( $lev == 0 ) {
				$closest = $s;
				$shortest = 0;
				break;
			}

			if ( $lev <= $shortest || $shortest < 0 ) {
				$closest  = $s;
				$shortest = $lev;
			}
		}

		// Threshold: Allow 1 typo for every 4 characters, max 3
		$threshold = ceil( strlen( $slug ) / 4 );
		if ( $threshold > 3 ) {
			$threshold = 3;
		}

		if ( $shortest >= 0 && $shortest <= $threshold && ! empty( $closest ) ) {
			// Found a match!
			$redirect_url = home_url( '/' . $closest . '/' );
			wp_redirect( $redirect_url, 301 );
			exit;
		}
	}

}

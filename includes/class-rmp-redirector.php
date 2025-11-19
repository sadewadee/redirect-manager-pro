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
		// We need to check for both exact path matches and handle query params

		// Fetch potential matches by path first to reduce DB load
		$potential_redirects = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE url_from = %s AND type = 'exact'", $path ) );

		if ( empty( $potential_redirects ) ) {
			// Try with/without trailing slash
			$alt_path = ( substr( $path, -1 ) == '/' ) ? rtrim( $path, '/' ) : $path . '/';
			$potential_redirects = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE url_from = %s AND type = 'exact'", $alt_path ) );
		}

		$redirect = null; // Initialize redirect variable

		if ( ! empty( $potential_redirects ) ) {
			foreach ( $potential_redirects as $pr ) {
				// Check Query Status
				if ( $pr->query_status == 'exact' ) {
					// Reconstruct full request URI for comparison or just compare query string
					// If query_status is exact, the url_from in DB might include query params?
					// Usually 'exact' in other plugins means the source URL in DB *has* params and they must match.
					// BUT our schema splits url_from. Let's assume url_from is PATH only for now,
					// and if 'exact', we might need to store the query string in url_from?
					// Actually, standard practice: url_from contains the full string if it has params.

					// Let's refine: If user enters /foo?a=b, url_from is /foo?a=b.
					// So we should match against $request_uri, not $path.

					if ( $pr->url_from == $request_uri ) {
						$redirect = $pr;
						break;
					}
				} elseif ( $pr->query_status == 'ignore' ) {
					// Default: Match path, ignore params.
					$redirect = $pr;
					break;
				} elseif ( $pr->query_status == 'pass' ) {
					// Match path, pass params to target.
					$redirect = $pr;
					// Append query to target
					if ( ! empty( $query ) ) {
						$redirect->url_to = add_query_arg( $_GET, $redirect->url_to );
					}
					break;
				}
			}
		}

		// 2. Wildcard Match
		if ( ! $redirect ) {
			$wildcards = $wpdb->get_results( "SELECT * FROM $table_name WHERE type = 'wildcard'" );
			foreach ( $wildcards as $wc ) {
				$pattern = str_replace( '*', '.*', $wc->url_from );
				// Check against full URI if it contains ?, else path
				$check_against = ( strpos( $wc->url_from, '?' ) !== false ) ? $request_uri : $path;

				if ( preg_match( "#^$pattern$#", $check_against ) ) {
					$redirect = $wc;
					// Pass params if requested
					if ( $wc->query_status == 'pass' && ! empty( $query ) ) {
						$redirect->url_to = add_query_arg( $_GET, $redirect->url_to );
					}
					break;
				}
			}
		}

		// 3. Regex Match
		if ( ! $redirect ) {
			$regexs = $wpdb->get_results( "SELECT * FROM $table_name WHERE type = 'regex'" );
			foreach ( $regexs as $rx ) {
				if ( preg_match( "#" . $rx->url_from . "#", $request_uri ) ) {
					$redirect = $rx;
					// Pass params if requested
					if ( $rx->query_status == 'pass' && ! empty( $query ) ) {
						$redirect->url_to = add_query_arg( $_GET, $redirect->url_to );
					}
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

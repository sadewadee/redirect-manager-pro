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
 * This class defines the link scanner logic.
 *
 * @since      1.0.0
 * @package    Redirect_Manager_Pro
 * @subpackage Redirect_Manager_Pro/includes
 * @author     Sadewadee
 */
class Redirect_Manager_Pro_Scanner {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

	}

	/**
	 * Process a batch of posts via AJAX.
	 *
	 * @since    1.0.0
	 */
	public function ajax_scan_batch() {
		check_ajax_referer( 'rmp_scan_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Unauthorized' );
		}

		$offset = isset( $_POST['offset'] ) ? intval( $_POST['offset'] ) : 0;
		$limit  = isset( $_POST['limit'] ) ? intval( $_POST['limit'] ) : 5;

		$args = array(
			'post_type'      => array( 'post', 'page' ),
			'post_status'    => 'publish',
			'posts_per_page' => $limit,
			'offset'         => $offset,
			'orderby'        => 'ID',
			'order'          => 'ASC',
		);

		$query = new WP_Query( $args );
		$posts = $query->posts;
		$total_posts = $query->found_posts;

		$processed = 0;
		$broken_count = 0;

		foreach ( $posts as $post ) {
			$this->scan_post( $post );
			$processed++;
		}

		$next_offset = $offset + $processed;
		$completed = ( $next_offset >= $total_posts );

		wp_send_json_success( array(
			'processed' => $processed,
			'next_offset' => $next_offset,
			'completed' => $completed,
			'total' => $total_posts
		) );
	}

	/**
	 * Scan a single post for broken links.
	 *
	 * @param WP_Post $post The post object.
	 */
	private function scan_post( $post ) {
		$content = $post->post_content;
		if ( empty( $content ) ) {
			return;
		}

		// Extract links
		if ( preg_match_all( '/<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>/siU', $content, $matches, PREG_SET_ORDER ) ) {
			foreach ( $matches as $match ) {
				$url = $match[2];
				$text = $match[3];

				if ( empty( $url ) || strpos( $url, '#' ) === 0 || strpos( $url, 'mailto:' ) === 0 || strpos( $url, 'tel:' ) === 0 ) {
					continue;
				}

				$status = $this->check_url( $url );

				if ( $status >= 400 ) {
					$this->log_broken_link( $post->ID, $url, $status, $text );
				}
			}
		}
	}

	/**
	 * Check URL status.
	 *
	 * @param string $url The URL to check.
	 * @return int HTTP status code.
	 */
	private function check_url( $url ) {
		// Handle relative URLs
		if ( strpos( $url, '/' ) === 0 ) {
			$url = home_url( $url );
		}

		// Simple check for internal links via DB to save HTTP requests
		if ( strpos( $url, home_url() ) !== false ) {
			$post_id = url_to_postid( $url );
			if ( $post_id > 0 ) {
				return 200;
			}
			// If 0, it might be an archive or 404. Fallback to HTTP check.
		}

		$response = wp_remote_head( $url, array( 'timeout' => 5, 'redirection' => 5 ) );

		if ( is_wp_error( $response ) ) {
			return 500; // Treat connection errors as 500
		}

		return wp_remote_retrieve_response_code( $response );
	}

	/**
	 * Log broken link to DB.
	 */
	private function log_broken_link( $post_id, $url, $status, $text ) {
		global $wpdb;
		$table = $wpdb->prefix . 'rmp_broken_links';

		// Check if already exists
		$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table WHERE post_id = %d AND url = %s", $post_id, $url ) );

		if ( ! $exists ) {
			$wpdb->insert(
				$table,
				array(
					'post_id'     => $post_id,
					'url'         => $url,
					'status_code' => $status,
					'link_text'   => strip_tags( $text ),
				),
				array( '%d', '%s', '%d', '%s' )
			);
		}
	}

}

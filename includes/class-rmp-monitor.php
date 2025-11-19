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
 * This class defines the permalink monitoring logic.
 *
 * @since      1.0.0
 * @package    Redirect_Manager_Pro
 * @subpackage Redirect_Manager_Pro/includes
 * @author     Sadewadee
 */
class Redirect_Manager_Pro_Monitor {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

	}

	/**
	 * Monitor post updates for slug changes.
	 *
	 * @param int     $post_id     Post ID.
	 * @param WP_Post $post_after  Post object following the update.
	 * @param WP_Post $post_before Post object before the update.
	 */
	public function monitor_slug_change( $post_id, $post_after, $post_before ) {
		// Check if post status is publish
		if ( $post_after->post_status != 'publish' ) {
			return;
		}

		// Check if slug changed
		if ( $post_before->post_name == $post_after->post_name ) {
			return;
		}

		// Check if post type is supported (post, page)
		if ( ! in_array( $post_after->post_type, array( 'post', 'page' ) ) ) {
			return;
		}

		// Get old and new URLs
		$old_url = get_permalink( $post_id ); // This might already be the new one? No, get_permalink uses current DB state.
		// Actually, get_permalink might use the cache or the object passed.
		// Let's construct it manually to be safe or use the before object if possible.
		// A safer way is to construct the old path from the old slug.

		// Simple approach: /old-slug/ -> /new-slug/
		// This assumes standard permalink structure. For robustness, we should respect the permalink structure.
		// But for this "lightweight" plugin, let's assume relative paths based on slug.

		$old_slug = $post_before->post_name;
		$new_slug = $post_after->post_name;

		// Construct relative paths
		$old_path = '/' . $old_slug . '/';
		$new_path = '/' . $new_slug . '/';

		// Create Redirect
		$this->create_redirect( $old_path, $new_path );
	}

	/**
	 * Create a redirect in the database.
	 *
	 * @param string $from Source path.
	 * @param string $to   Target path.
	 */
	private function create_redirect( $from, $to ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'rmp_redirects';

		// Check if exists
		$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table_name WHERE url_from = %s", $from ) );

		if ( ! $exists ) {
			$wpdb->insert(
				$table_name,
				array(
					'url_from' => $from,
					'url_to'   => $to,
					'status'   => 301,
					'type'     => 'exact',
					'query_status' => 'ignore',
					'group_id' => 0
				),
				array( '%s', '%s', '%d', '%s', '%s', '%d' )
			);
		}
	}

}

<?php

/**
 * Fired during plugin activation
 *
 * @link       http://example.com
 * @since      1.0.0
 *
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
class Redirect_Manager_Pro_Database {

	/**
	 * Create the database tables.
	 *
	 * @since    1.0.0
	 */
	public static function create_tables() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		$table_redirects = $wpdb->prefix . 'rmp_redirects';
		$table_404_logs  = $wpdb->prefix . 'rmp_404_logs';
		$table_broken_links = $wpdb->prefix . 'rmp_broken_links';
		$table_groups = $wpdb->prefix . 'rmp_groups';

		$sql_redirects = "CREATE TABLE $table_redirects (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			url_from varchar(191) NOT NULL,
			url_to text NOT NULL,
			status int(3) NOT NULL DEFAULT 301,
			type varchar(20) NOT NULL DEFAULT 'exact',
			query_status varchar(20) NOT NULL DEFAULT 'ignore',
			group_id bigint(20) unsigned NOT NULL DEFAULT 0,
			hits bigint(20) unsigned NOT NULL DEFAULT 0,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY url_from (url_from)
		) $charset_collate;";

		$sql_404_logs = "CREATE TABLE $table_404_logs (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			url text NOT NULL,
			ip varchar(45) NOT NULL,
			user_agent text,
			count bigint(20) unsigned NOT NULL DEFAULT 1,
			timestamp datetime DEFAULT CURRENT_TIMESTAMP,
			last_updated datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id)
		) $charset_collate;";

		$sql_broken_links = "CREATE TABLE $table_broken_links (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			post_id bigint(20) unsigned NOT NULL,
			url text NOT NULL,
			status_code int(3) NOT NULL,
			link_text text,
			checked_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY post_id (post_id)
		) $charset_collate;";

		$sql_groups = "CREATE TABLE $table_groups (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			name varchar(191) NOT NULL,
			status varchar(20) NOT NULL DEFAULT 'enabled',
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql_redirects );
		dbDelta( $sql_404_logs );
		dbDelta( $sql_broken_links );
		dbDelta( $sql_groups );
	}

}

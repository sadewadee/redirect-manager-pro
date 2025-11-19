<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 * @package    Redirect_Manager_Pro
 * @subpackage Redirect_Manager_Pro/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Redirect_Manager_Pro
 * @subpackage Redirect_Manager_Pro/admin
 * @author     Sadewadee
 */
class Redirect_Manager_Pro_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $redirect_manager_pro    The ID of this plugin.
	 */
	private $redirect_manager_pro;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    string    $redirect_manager_pro       The name of this plugin.
	 * @param    string    $version    The version of this plugin.
	 */
	public function __construct( $redirect_manager_pro, $version ) {

		$this->redirect_manager_pro = $redirect_manager_pro;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->redirect_manager_pro, plugin_dir_url( __FILE__ ) . 'css/redirect-manager-pro-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->redirect_manager_pro, plugin_dir_url( __FILE__ ) . 'js/redirect-manager-pro-admin.js', array( 'jquery' ), $this->version, false );

		wp_localize_script( $this->redirect_manager_pro, 'rmp_ajax', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'rmp_scan_nonce' ),
		) );

	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {

		add_menu_page(
			'Redirect Manager Pro',
			'Redirect Manager',
			'manage_options',
			$this->redirect_manager_pro,
			array( $this, 'display_plugin_admin_page' ),
			'dashicons-randomize',
			30
		);

	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_page() {

		global $wpdb;
		$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'redirects';

		// Fetch data based on tab
		$data = array();

		if ( $active_tab == 'redirects' ) {
			$table_name = $wpdb->prefix . 'rmp_redirects';
			$data['redirects'] = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY id DESC" );
			$data['groups'] = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}rmp_groups" );
		} elseif ( $active_tab == 'groups' ) {
			$data['groups'] = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}rmp_groups ORDER BY id DESC" );
		} elseif ( $active_tab == 'logs' ) {
			$table_name = $wpdb->prefix . 'rmp_404_logs';
			$data['logs'] = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY last_updated DESC LIMIT 100" );
		} elseif ( $active_tab == 'scanner' ) {
			$table_name = $wpdb->prefix . 'rmp_broken_links';
			$data['broken_links'] = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY id DESC" );
		}

		include_once 'partials/rmp-admin-display.php';

	}

	/**
	 * Handle form submissions.
	 */
	public function handle_post_requests() {
		if ( ! isset( $_POST['rmp_action'] ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		check_admin_referer( 'rmp_action_nonce', 'rmp_nonce' );

		global $wpdb;
		$redirects_table = $wpdb->prefix . 'rmp_redirects';
		$groups_table = $wpdb->prefix . 'rmp_groups';

		if ( $_POST['rmp_action'] == 'add_redirect' ) {
			$url_from = sanitize_text_field( $_POST['url_from'] );
			$url_to = sanitize_text_field( $_POST['url_to'] );
			$status = intval( $_POST['status'] );
			$type = sanitize_text_field( $_POST['type'] );
			$query_status = sanitize_text_field( $_POST['query_status'] );
			$group_id = intval( $_POST['group_id'] );

			$wpdb->insert(
				$redirects_table,
				array(
					'url_from' => $url_from,
					'url_to'   => $url_to,
					'status'   => $status,
					'type'     => $type,
					'query_status' => $query_status,
					'group_id' => $group_id
				),
				array( '%s', '%s', '%d', '%s', '%s', '%d' )
			);

			wp_redirect( admin_url( 'admin.php?page=redirect-manager-pro&message=added' ) );
			exit;
		}

		if ( $_POST['rmp_action'] == 'delete_redirect' ) {
			$id = intval( $_POST['id'] );
			$wpdb->delete( $redirects_table, array( 'id' => $id ), array( '%d' ) );

			wp_redirect( admin_url( 'admin.php?page=redirect-manager-pro&message=deleted' ) );
			exit;
		}

		if ( $_POST['rmp_action'] == 'add_group' ) {
			$name = sanitize_text_field( $_POST['group_name'] );
			$wpdb->insert(
				$groups_table,
				array( 'name' => $name ),
				array( '%s' )
			);
			wp_redirect( admin_url( 'admin.php?page=redirect-manager-pro&tab=groups&message=group_added' ) );
			exit;
		}

		if ( $_POST['rmp_action'] == 'delete_group' ) {
			$id = intval( $_POST['id'] );
			$wpdb->delete( $groups_table, array( 'id' => $id ), array( '%d' ) );
			// Optionally update redirects to remove group_id
			$wpdb->update( $redirects_table, array( 'group_id' => 0 ), array( 'group_id' => $id ), array( '%d' ), array( '%d' ) );

			wp_redirect( admin_url( 'admin.php?page=redirect-manager-pro&tab=groups&message=group_deleted' ) );
			exit;
		}

		if ( $_POST['rmp_action'] == 'import_csv' ) {
			if ( ! empty( $_FILES['csv_file']['tmp_name'] ) ) {
				$file = fopen( $_FILES['csv_file']['tmp_name'], 'r' );
				while ( ( $line = fgetcsv( $file ) ) !== FALSE ) {
					// Assuming CSV format: from, to, status, type
					if ( count( $line ) >= 2 ) {
						$url_from = sanitize_text_field( $line[0] );
						$url_to = sanitize_text_field( $line[1] );
						$status = isset( $line[2] ) ? intval( $line[2] ) : 301;
						$type = isset( $line[3] ) ? sanitize_text_field( $line[3] ) : 'exact';

						$wpdb->insert(
							$redirects_table,
							array(
								'url_from' => $url_from,
								'url_to'   => $url_to,
								'status'   => $status,
								'type'     => $type
							),
							array( '%s', '%s', '%d', '%s' )
						);
					}
				}
				fclose( $file );
				wp_redirect( admin_url( 'admin.php?page=redirect-manager-pro&message=imported' ) );
				exit;
			}
		}
	}

}

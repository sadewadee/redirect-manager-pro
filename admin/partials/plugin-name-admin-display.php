<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/admin/partials
 */
?>

<div class="wrap plugin-name-admin">
	<div class="plugin-name-header">
		<h1><?php echo esc_html( get_admin_page_title() ); ?> <span class="version"><?php echo esc_html( $this->version ); ?></span></h1>
	</div>

	<div class="plugin-name-tabs">
		<nav class="nav-tab-wrapper">
			<a href="#dashboard" class="nav-tab nav-tab-active">Dashboard</a>
			<a href="#settings" class="nav-tab">Settings</a>
			<a href="#extensions" class="nav-tab">Extensions</a>
			<a href="#support" class="nav-tab">Support</a>
		</nav>
	</div>

	<div class="plugin-name-content">

		<!-- Dashboard Tab -->
		<div id="dashboard" class="tab-content active">
			<div class="card-grid">
				<div class="card welcome-card">
					<h2>Welcome to Plugin Name</h2>
					<p>Thank you for using our plugin. This boilerplate is designed to help you build amazing WordPress plugins faster.</p>
					<a href="#" class="button button-primary">Get Started</a>
				</div>
				<div class="card status-card">
					<h3>System Status</h3>
					<ul>
						<li><span class="dashicons dashicons-yes success"></span> PHP Version: <?php echo phpversion(); ?></li>
						<li><span class="dashicons dashicons-yes success"></span> WordPress Version: <?php echo get_bloginfo('version'); ?></li>
						<li><span class="dashicons dashicons-yes success"></span> Memory Limit: <?php echo ini_get('memory_limit'); ?></li>
					</ul>
				</div>
			</div>
		</div>

		<!-- Settings Tab -->
		<div id="settings" class="tab-content">
			<div class="card">
				<h2>General Settings</h2>
				<form method="post" action="options.php">
					<table class="form-table">
						<tr valign="top">
						<th scope="row">Example Option</th>
						<td><input type="text" name="plugin_name_option_name" value="<?php echo esc_attr( get_option('plugin_name_option_name') ); ?>" /></td>
						</tr>

						<tr valign="top">
						<th scope="row">Enable Feature</th>
						<td>
							<label class="switch">
								<input type="checkbox" name="plugin_name_feature_enabled">
								<span class="slider round"></span>
							</label>
						</td>
						</tr>
					</table>
					<?php submit_button(); ?>
				</form>
			</div>
		</div>

		<!-- Extensions Tab -->
		<div id="extensions" class="tab-content">
			<div class="card">
				<h2>Available Extensions</h2>
				<p>Extend the functionality of this plugin with these add-ons.</p>
				<!-- Placeholder for extensions -->
				<div class="extension-placeholder">
					<p>No extensions available yet.</p>
				</div>
			</div>
		</div>

		<!-- Support Tab -->
		<div id="support" class="tab-content">
			<div class="card">
				<h2>Need Help?</h2>
				<p>If you have any questions or need assistance, please check our documentation or contact support.</p>
				<p>
					<a href="#" class="button">Documentation</a>
					<a href="#" class="button">Contact Support</a>
				</p>
			</div>
		</div>

	</div>
</div>

<script>
jQuery(document).ready(function($) {
	$('.nav-tab').click(function(e) {
		e.preventDefault();
		$('.nav-tab').removeClass('nav-tab-active');
		$(this).addClass('nav-tab-active');
		$('.tab-content').removeClass('active');
		var target = $(this).attr('href');
		$(target).addClass('active');
	});
});
</script>

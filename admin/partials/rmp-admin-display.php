<div class="wrap">
	<h1>Redirect Manager Pro</h1>

	<?php
	$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'redirects';
	?>

	<h2 class="nav-tab-wrapper">
		<a href="?page=redirect-manager-pro&tab=redirects" class="nav-tab <?php echo $active_tab == 'redirects' ? 'nav-tab-active' : ''; ?>">Redirects</a>
		<a href="?page=redirect-manager-pro&tab=logs" class="nav-tab <?php echo $active_tab == 'logs' ? 'nav-tab-active' : ''; ?>">404 Logs</a>
		<a href="?page=redirect-manager-pro&tab=scanner" class="nav-tab <?php echo $active_tab == 'scanner' ? 'nav-tab-active' : ''; ?>">Link Scanner</a>
		<a href="?page=redirect-manager-pro&tab=settings" class="nav-tab <?php echo $active_tab == 'settings' ? 'nav-tab-active' : ''; ?>">Settings & Import</a>
	</h2>

	<?php if ( $active_tab == 'redirects' ): ?>
		<div class="card" style="margin-top: 20px; padding: 20px;">
			<h3>Add New Redirect</h3>
			<form method="post" action="">
				<?php wp_nonce_field( 'rmp_action_nonce', 'rmp_nonce' ); ?>
				<input type="hidden" name="rmp_action" value="add_redirect">
				<table class="form-table">
					<tr>
						<th><label for="url_from">Source URL (Relative)</label></th>
						<td><input type="text" name="url_from" id="url_from" class="regular-text" placeholder="/old-page" required></td>
					</tr>
					<tr>
						<th><label for="url_to">Target URL</label></th>
						<td><input type="text" name="url_to" id="url_to" class="regular-text" placeholder="/new-page" required></td>
					</tr>
					<tr>
						<th><label for="status">HTTP Status</label></th>
						<td>
							<select name="status" id="status">
								<option value="301">301 Permanent</option>
								<option value="302">302 Temporary</option>
								<option value="307">307 Temporary</option>
							</select>
						</td>
					</tr>
					<tr>
						<th><label for="type">Type</label></th>
						<td>
							<select name="type" id="type">
								<option value="exact">Exact Match</option>
								<option value="wildcard">Wildcard (*)</option>
								<option value="regex">Regex</option>
							</select>
						</td>
					</tr>
				</table>
				<p class="submit"><input type="submit" class="button button-primary" value="Add Redirect"></p>
			</form>
		</div>

		<h3>Existing Redirects</h3>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th>Source</th>
					<th>Target</th>
					<th>Type</th>
					<th>Status</th>
					<th>Hits</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tbody>
				<?php if ( ! empty( $data['redirects'] ) ): ?>
					<?php foreach ( $data['redirects'] as $redirect ): ?>
						<tr>
							<td><?php echo esc_html( $redirect->url_from ); ?></td>
							<td><?php echo esc_html( $redirect->url_to ); ?></td>
							<td><?php echo esc_html( $redirect->type ); ?></td>
							<td><?php echo esc_html( $redirect->status ); ?></td>
							<td><?php echo esc_html( $redirect->hits ); ?></td>
							<td>
								<form method="post" action="" style="display:inline;">
									<?php wp_nonce_field( 'rmp_action_nonce', 'rmp_nonce' ); ?>
									<input type="hidden" name="rmp_action" value="delete_redirect">
									<input type="hidden" name="id" value="<?php echo $redirect->id; ?>">
									<button type="submit" class="button button-small button-link-delete" onclick="return confirm('Are you sure?')">Delete</button>
								</form>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php else: ?>
					<tr><td colspan="6">No redirects found.</td></tr>
				<?php endif; ?>
			</tbody>
		</table>

	<?php elseif ( $active_tab == 'logs' ): ?>
		<h3>404 Error Logs (Last 100)</h3>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th>URL</th>
					<th>IP</th>
					<th>User Agent</th>
					<th>Time</th>
				</tr>
			</thead>
			<tbody>
				<?php if ( ! empty( $data['logs'] ) ): ?>
					<?php foreach ( $data['logs'] as $log ): ?>
						<tr>
							<td><?php echo esc_html( $log->url ); ?></td>
							<td><?php echo esc_html( $log->ip ); ?></td>
							<td><?php echo esc_html( $log->user_agent ); ?></td>
							<td><?php echo esc_html( $log->timestamp ); ?></td>
						</tr>
					<?php endforeach; ?>
				<?php else: ?>
					<tr><td colspan="4">No logs found.</td></tr>
				<?php endif; ?>
			</tbody>
		</table>

	<?php elseif ( $active_tab == 'scanner' ): ?>
		<h3>Link Scanner</h3>
		<div class="card" style="padding: 20px;">
			<p>Scan your posts and pages for broken internal and external links.</p>
			<button id="rmp-start-scan" class="button button-primary">Start Scan</button>
			<div id="rmp-scan-progress" style="margin-top: 10px; display: none;">
				<div style="background: #f1f1f1; height: 20px; border-radius: 10px; overflow: hidden;">
					<div id="rmp-progress-bar" style="background: #2271b1; height: 100%; width: 0%;"></div>
				</div>
				<p id="rmp-scan-status">Scanning...</p>
			</div>
		</div>

		<h3>Broken Links Found</h3>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th>Post ID</th>
					<th>Broken URL</th>
					<th>Status</th>
					<th>Link Text</th>
					<th>Checked At</th>
				</tr>
			</thead>
			<tbody>
				<?php if ( ! empty( $data['broken_links'] ) ): ?>
					<?php foreach ( $data['broken_links'] as $link ): ?>
						<tr>
							<td><a href="<?php echo get_edit_post_link( $link->post_id ); ?>" target="_blank"><?php echo esc_html( $link->post_id ); ?></a></td>
							<td><?php echo esc_html( $link->url ); ?></td>
							<td><?php echo esc_html( $link->status_code ); ?></td>
							<td><?php echo esc_html( $link->link_text ); ?></td>
							<td><?php echo esc_html( $link->checked_at ); ?></td>
						</tr>
					<?php endforeach; ?>
				<?php else: ?>
					<tr><td colspan="5">No broken links found.</td></tr>
				<?php endif; ?>
			</tbody>
		</table>

	<?php elseif ( $active_tab == 'settings' ): ?>
		<h3>CSV Import</h3>
		<div class="card" style="padding: 20px;">
			<p>Upload a CSV file to bulk import redirects. Format: <code>source,target,status,type</code></p>
			<form method="post" action="" enctype="multipart/form-data">
				<?php wp_nonce_field( 'rmp_action_nonce', 'rmp_nonce' ); ?>
				<input type="hidden" name="rmp_action" value="import_csv">
				<input type="file" name="csv_file" accept=".csv" required>
				<p class="submit"><input type="submit" class="button button-primary" value="Import CSV"></p>
			</form>
		</div>
	<?php endif; ?>
</div>

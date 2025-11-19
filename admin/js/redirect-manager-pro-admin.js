(function( $ ) {
	'use strict';

	$(function() {

		$('#rmp-start-scan').on('click', function(e) {
			e.preventDefault();

			var $btn = $(this);
			var $progress = $('#rmp-scan-progress');
			var $bar = $('#rmp-progress-bar');
			var $status = $('#rmp-scan-status');

			$btn.prop('disabled', true);
			$progress.show();
			$status.text('Starting scan...');
			$bar.css('width', '0%');

			scanBatch(0, 5);

			function scanBatch(offset, limit) {
				$.ajax({
					url: rmp_ajax.ajax_url,
					type: 'POST',
					data: {
						action: 'rmp_scan_batch',
						nonce: rmp_ajax.nonce,
						offset: offset,
						limit: limit
					},
					success: function(response) {
						if (response.success) {
							var data = response.data;
							var percent = Math.round((data.next_offset / data.total) * 100);
							$bar.css('width', percent + '%');
							$status.text('Scanned ' + data.next_offset + ' of ' + data.total + ' posts...');

							if (!data.completed) {
								scanBatch(data.next_offset, limit);
							} else {
								$status.text('Scan complete!');
								$btn.prop('disabled', false);
								setTimeout(function() {
									location.reload(); // Reload to show results
								}, 1000);
							}
						} else {
							$status.text('Error: ' + response.data);
							$btn.prop('disabled', false);
						}
					},
					error: function() {
						$status.text('AJAX Error');
						$btn.prop('disabled', false);
					}
				});
			}
		});

	});

})( jQuery );

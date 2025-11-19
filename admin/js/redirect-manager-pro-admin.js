(function ($) {
	'use strict';

	$(document).ready(function () {

		// Toggle Add Redirect Form
		$('#rmp-add-new-btn').on('click', function (e) {
			e.preventDefault();
			$('#rmp-add-redirect-form').slideDown();
			$(this).hide();
		});

		$('#rmp-cancel-btn').on('click', function (e) {
			e.preventDefault();
			$('#rmp-add-redirect-form').slideUp(function () {
				$('#rmp-add-new-btn').show();
			});
		});

		// Link Scanner Logic
		$('#rmp-start-scan').on('click', function (e) {
			e.preventDefault();

			var $btn = $(this);
			var $progress = $('#rmp-scan-progress');
			var $bar = $progress.find('.bar');
			var $status = $('#rmp-scan-status');

			$btn.prop('disabled', true);
			$progress.show();
			$status.text('Starting scan...');
			$bar.css('width', '0%');

			scanBatch(1, 0);

			function scanBatch(page, totalProcessed) {
				$.ajax({
					url: rmp_ajax.ajax_url,
					type: 'POST',
					data: {
						action: 'rmp_scan_batch',
						nonce: rmp_ajax.nonce,
						page: page
					},
					success: function (response) {
						if (response.success) {
							var data = response.data;
							var processed = data.processed_count;
							var total = data.total_posts;
							var currentTotal = totalProcessed + processed;

							var percent = 0;
							if (total > 0) {
								percent = Math.round((currentTotal / total) * 100); // This is rough estimation as total might change or be per batch
								// Better: The response should ideally return global progress if possible.
								// For now, let's just increment bar based on pages.
								// Actually, let's just show "Processing page X..."
							}

							$bar.css('width', percent + '%');
							$status.text('Scanning page ' + page + '... Found ' + data.broken_links_found + ' broken links so far.');

							if (!data.done) {
								scanBatch(page + 1, currentTotal);
							} else {
								$bar.css('width', '100%');
								$status.text('Scan complete! Reloading...');
								setTimeout(function () {
									location.reload();
								}, 1000);
							}
						} else {
							$status.text('Error: ' + response.data);
							$btn.prop('disabled', false);
						}
					},
					error: function () {
						$status.text('Server error occurred.');
						$btn.prop('disabled', false);
					}
				});
			}
		});

	});

})(jQuery);

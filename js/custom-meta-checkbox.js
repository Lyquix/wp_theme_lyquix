jQuery(document).ready(function ($) {
	$('.featured-checkbox').on('change', function () {
			const checkbox = $(this);
			const postId = checkbox.data('post-id');
			const checked = checkbox.is(':checked');

			$.ajax({
					url: CustomMetaAjax.ajax_url,
					method: 'POST',
					data: {
							action: 'update_featured',
							nonce: CustomMetaAjax.nonce,
							post_id: postId,
							checked: checked
					},
					success: function (response) {
							if (response.success) {
									console.log('Meta updated successfully.');
									location.reload();
							} else {
									console.error('Failed to update meta:', response.data);
							}
					},
					error: function () {
							console.error('An error occurred while updating the meta field.');
					}
			});
	});
});

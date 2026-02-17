/**
 * featured-posts-list.js - Add featured posts and filtering by them
 *
 * @version     3.2.0
 * @package     wp_theme_lyquix
 * @author      Lyquix
 * @copyright   Copyright (C) 2015 - 2024 Lyquix
 * @license     GNU General Public License version 2 or later
 * @link        https://github.com/Lyquix/wp_theme_lyquix
 */

//    .d8888b. 88888888888 .d88888b.  8888888b.   888
//   d88P  Y88b    888    d88P" "Y88b 888   Y88b  888
//   Y88b.         888    888     888 888    888  888
//    "Y888b.      888    888     888 888   d88P  888
//       "Y88b.    888    888     888 8888888P"   888
//         "888    888    888     888 888         Y8P
//   Y88b  d88P    888    Y88b. .d88P 888          "
//    "Y8888P"     888     "Y88888P"  888         888
//
//  DO NOT MODIFY THIS FILE!

jQuery(document).ready(function ($) {
	$('.featured-checkbox').on('change', function () {
		const checkbox = $(this);
		const postId = checkbox.data('post-id');
		const checked = checkbox.is(':checked');

		$.ajax({
			url: FeaturedPostsAjax.ajax_url,
			method: 'POST',
			data: {
				action: 'update_featured',
				nonce: FeaturedPostsAjax.nonce,
				post_id: postId,
				checked: checked,
			},
			success: function (response) {
				if (!response.success) {
					checkbox.prop('checked', !checked);
					console.error('Failed to update meta:', response.data);
				}
			},
			error: function () {
				console.error('An error occurred while updating the meta field.');
			},
		});
	});
});

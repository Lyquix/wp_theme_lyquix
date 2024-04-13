<?php

/**
 * render.php - Lyquix Filters module render functions
 *
 * @version     3.0.0
 * @package     wp_theme_lyquix
 * @author      Lyquix
 * @copyright   Copyright (C) 2015 - 2017 Lyquix
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
//  If you need a custom renderer, copy this file to php/custom/blocks/filters/render.php and modify it there

namespace lqx\blocks\filters;

/**
 * Render the filters
 * THIS FUNCTION IS REQUIRED TO HANDLE PHP RENDERING FOR API CALLS
 * @param  array $settings Block settings
 */
function render_filters($s) {
	if ($s['render_mode'] == 'cards') return \lqx\filters\render_filters($s);
	else {
		// Custom filters rendering here
	}
}

/**
 * Render the posts
 * THIS FUNCTION IS REQUIRED TO HANDLE PHP RENDERING FOR API CALLS
 * @param  array $settings Block settings
 */
function render_posts($s) {
	if ($s['render_mode'] == 'cards') return \lqx\filters\render_posts($s);
	else {
		// Custom posts rendering here
	}
}

/**
 * Render the pagination
 * THIS FUNCTION IS REQUIRED TO HANDLE PHP RENDERING FOR API CALLS
 * @param  array $settings Block settings
 */
function render_pagination($s) {
	if ($s['render_mode'] == 'cards') return \lqx\filters\render_pagination($s);
	else {
		// Custom pagination rendering here
	}
}

/**
 * Render the filters block
 * @param  array $settings Block settings
 */
function render($settings) {
	// Return if no preset has been selected
	if ($settings['local']['user']['preset'] == '') return;

	// Validate settings
	$s = \lqx\filters\validate_settings($settings);

	// Initialize settings
	$s = \lqx\filters\init_settings($s);

	// Get options
	$s = \lqx\filters\get_options($s);

	// Get the posts
	$post_info = \lqx\filters\get_posts_with_data($s);
	$s['posts'] = $post_info['posts'];
	$s['total_pages'] =  $post_info['total_pages'];

	file_put_contents(__DIR__ . '/render.log', json_encode(['$settings' => $settings, '$s' => $s], JSON_PRETTY_PRINT));

	if ($s['render_mode'] == 'custom_js') : ?>
		<script>
			((settings) => {
				lqx.ready(() => {
					lqx.filters.setup(JSON.parse(settings));
				});
			})('<?= json_encode(\lqx\filters\prepare_json_data($settings)) ?>');
		</script>
	<?php else : ?>
		<div class="filters">
			<?= render_filters($s) ?>
		</div>
		<ul class="posts">
			<?= render_posts($s) ?>
		</ul>
		<div class="pagination">
			<?= render_pagination($s) ?>
		</div>
	<?php endif;
}
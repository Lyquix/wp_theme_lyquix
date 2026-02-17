<?php

/**
 * embed.php - Facebook and Instagram embed blocks
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

namespace lqx\embed;

/**
 * Register embed blocks
 */
add_action('init', function () {
	// Facebook Embed block
	wp_register_script(
		'facebook-embed-block',
		get_template_directory_uri() . '/php/embed/facebook/block.js',
		['wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor'],
		filemtime(get_template_directory() . '/php/embed/facebook/block.js'),
		true
	);

	register_block_type('lqx/facebook', [
		'editor_script' => 'facebook-embed-block',
		'render_callback' => function ($attributes) {
			ob_start();
			include get_template_directory() . '/php/embed/facebook/render.php';
			return ob_get_clean();
		}
	]);

	// Instagram Embed block
	wp_register_script(
		'instagram-embed-block',
		get_template_directory_uri() . '/php/embed/instagram/block.js',
		['wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor'],
		filemtime(get_template_directory() . '/php/embed/instagram/block.js'),
		true
	);

	register_block_type('lqx/instagram', [
		'editor_script' => 'instagram-embed-block',
		'render_callback' => function ($attributes) {
			ob_start();
			include get_template_directory() . '/php/embed/instagram/render.php';
			return ob_get_clean();
		}
	]);
});

/**
 * Load SDKs in the block editor
 */
add_action('enqueue_block_editor_assets', function () {
	wp_enqueue_script(
		'facebook-sdk',
		'https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v21.0',
		[],
		null,
		true
	);

	wp_enqueue_script(
		'instagram-embed-sdk',
		'https://www.instagram.com/embed.js',
		[],
		null,
		true
	);
});

/**
 * Conditionally load SDKs on the frontend only when embed blocks are used
 */
add_action('wp_enqueue_scripts', function () {
	if (has_block('lqx/facebook')) {
		wp_enqueue_script(
			'facebook-sdk',
			'https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v21.0',
			[],
			null,
			true
		);
	}

	if (has_block('lqx/instagram')) {
		wp_enqueue_script(
			'instagram-embed-sdk',
			'https://www.instagram.com/embed.js',
			[],
			null,
			true
		);
	}
});

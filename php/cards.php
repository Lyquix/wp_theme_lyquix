<?php

/**
 * cards.php - Lyquix Cards
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
//  Instead add directories under /php/custom/blocks and use block.json files

namespace lqx\cards;

// Schema for the cards block
define('lqx\cards\schema', [
	'type' => 'object',
	'keys' => [
		'heading' => \lqx\util\schema_str_req_emp,
		'subheading' => \lqx\util\schema_str_req_emp,
		'image' => [
			'type' => 'object',
			'default' => [],
			'keys' => \lqx\util\schema_data_image
		],
		'icon_image' => [
			'type' => 'object',
			'default' => [],
			'keys' => \lqx\util\schema_data_image
		],
		'video' => [
			'type' => 'object',
			'keys' => [
				'type' => [
					'type' => 'string',
					'required' => true,
					'default' => 'url',
					'allowed' => ['url', 'upload']
				],
				'url' => \lqx\util\schema_str_req_emp,
				'upload' => [
					'type' => 'object',
					'default' => [],
					'keys' => \lqx\util\schema_data_video
				]
			]
		],
		'body' => \lqx\util\schema_str_req_emp,
		'link' => [
			'type' => 'object',
			'required' => true,
			'keys' => \lqx\util\schema_data_link,
			'default' => null
		],
		'link_style' => [
			'type' => 'string',
			'required' => true,
			'default' => 'button',
			'allowed' => ['button', 'link']
		],
		'labels' => [
			'type' =>	'array',
			'default' => [],
			'elems' => [
				'type' => 'object',
				'required' => true,
				'keys' => [
					'label' => \lqx\util\schema_str_req_emp,
					'value' => \lqx\util\schema_str_req_emp
				]
			]
		],
		'additional_classes' => \lqx\util\schema_str_req_emp,
		'item_id' => \lqx\util\schema_str_req_emp
	]
]);

/**
 * Render a cards block
 *
 * @param array $posts - the array of WP_Post objects or post IDs to render
 * @param string $preset - the preset to use for the cards block
 * @param string $style - the style to use for the cards block
 * @param array $fields_map - the fields map to use to process the WP_Post objects
 * 							- An associative array where the keys are the card object field names
 * 							- and the values are the ACF field names or field keys or WP_Post field names
 * @param array $fields_values - the values to use for the card object fields
 * 							- An array of associative arrays where the keys are the card object field names
 * 							- and the values are the values to use for the card object fields
 * @return string - the rendered cards block
 */
function render($posts, $preset = null, $style = null, $fields_map = [], $fields_values = []) {
	// Get the cards block settings with the specified preset and style
	$settings = \lqx\blocks\get_settings('cards', null, $preset, $style);

	// Process WP_Post objects or post IDs
	$posts = process_wp_posts($posts, $fields_map, $fields_values);

	// Validate posts and filter out invalid content
	$content = array_filter(array_map(function($item) {
		$v = \lqx\util\validate_data($item, \lqx\cards\schema);
		return $v['isValid'] ? $v['data'] : null;
	}, $posts));

	// Load the rendered for the specified preset
	require \lqx\blocks\get_renderer('cards', $preset);
}

/**
 * Process an array of WP_Post objects into an array of card objects
 *
 * @param array $posts - the array of WP_Post objects to process
 * @param array $fields_map - the fields map to use to process the WP_Post objects
 * 							- An associative array where the keys are the card object field names
 * 							- and the values are the ACF field names or field keys or WP_Post field names
 * @param array $fields_values - the values to use for the card object fields
 * 							- An array of associative arrays where the keys are the card object field names
 * 							- and the values are the values to use for the card object fields
 * @return array - the processed card objects
 */
function process_wp_posts($posts, $fields_map, $fields_values) {
	$processed_posts = [];

	foreach ($posts as $i => $post) {
		$processed_post = process_wp_post($post, $fields_map, $fields_values[$i]);
		if ($processed_post) $processed_posts[] = $processed_post;
	}

	return $processed_posts;
}

/**
 * Process a WP_Post object into a card object
 *
 * The card object is an associative array with the following keys:
 * - id: the post ID
 * - date: the post date
 * - heading: the post title
 * - subheading: the post subheading +
 * - slug: the post slug
 * - modified: the post modified date
 * - url: the post URL
 * - body: the post excerpt
 * - links: an array of links +
 * - labels: an array of labels +
 * - image: an image object
 * - icon_image: an image object +
 * - video: a video object +
 *
 * All the items get a value by default, except for the ones marked with a + sign
 *
 * @param WP_Post|int $post - the WP_Post object or integer ID to process
 * @param array $fields_map - the fields map to use to process the WP_Post object
 *  						- An associative array where the keys are the card object field names
 * 							- and the values are the ACF field names or field keys or WP_Post field names
 * @param array $fields_values - the values to use for the card object fields
 * 							- An associative array where the keys are the card object field names
 * 							- and the values are the values to use for the card object fields
 * @return array - the processed card object
 */
function process_wp_post($post, $fields_map, $fields_values) {
	// List of field names that represent WP_Post fields, not ACF fields
	$wp_post_keys = [
		'ID', 'post_author', 'post_date', 'post_date_gmt', 'post_content',
		'post_content_filtered', 'post_title', 'post_excerpt', 'post_name',
		'post_modified', 'post_modified_gmt', 'post_type', 'post_mime_type'
	];

	// Check if $post is a WP_Post object (from a relation field)
	if (!is_a($post, 'WP_Post') && is_int($post)) get_post($post);

	$card = [
		'id' => $post->ID,
		'date' => $post->post_date_gmt,
		'heading' => $post->post_title,
		'subheading' => null,
		'slug' => $post->post_name,
		'modified' => $post->post_modified_gmt,
		'link' => [
			'url' => get_permalink($post->ID),
			'title' => 'Read More',
			'target' => ''
		],
		'link_style' => 'button',
		'body' => $post-> post_excerpt,
		'labels' => null,
		'image' => \lqx\util\get_thumbnail_image_object($post->ID),
		'icon_image' => null,
		'video' => [
			'type' => ''
		]
	];

	// Handle values from post fields or ACF fields
	foreach ($fields_map as $card_field => $post_field) {
		if (array_key_exists($card_field, $card)) {
			if ($post_field) {
				switch ($card_field) {
					case 'image':
					case 'icon_image':
						if ($post_field == 'thumbnail') $card[$card_field] = \lqx\util\get_thumbnail_image_object($post->ID);
						else $card[$card_field] = get_field($post_field, $post->ID);
						break;

					default:
						if (in_array($post_field, $wp_post_keys)) $card[$card_field] = $post->$post_field;
						else $card[$card_field] = get_field($post_field, $post->ID);
						break;
				}
			}
			else $card[$card_field] = null;
		}
	}

	// Handle values passed directly
	foreach ($fields_values as $card_field => $field_value) {
		if (array_key_exists($card_field, $card)) $card[$card_field] = $field_value;
	}

	return $card;
}


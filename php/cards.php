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
 * 							- An associative array where the keys are the card object field names
 * 							- and the values are the values to use for the card object fields
 * @return string - the rendered cards block
 */
function render($posts, $preset = null, $style = null, $fields_map = [], $fields_values = []) {
	// Get the cards block settings with the specified preset and style
	$settings = \lqx\blocks\get_settings('cards', null, $preset, $style);

	// Process WP_Post objects or post IDs
	$posts = process_wp_posts($posts, $fields_map, $fields_values);

	// TODO validate posts

	// Load the rendered for the specified preset
	require_once \lqx\blocks\get_renderer('cards', $preset);

	// Render the cards
	return \lqx\blocks\cards\render($settings, $posts);
}

/**
 * Process an array of WP_Post objects into an array of card objects
 *
 * @param array $posts - the array of WP_Post objects to process
 * @param array $fields_map - the fields map to use to process the WP_Post objects
 * @return array - the processed card objects
 */
function process_wp_posts($posts, $fields_map, $fields_values) {
	$processed_posts = [];

	foreach ($posts as $post) {
		$processed_post = process_wp_post($post, $fields_map, $fields_values);
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
		'image' => get_thumbnail_image_object($post->ID),
		'icon_image' => null,
		'video' => [
			'type' => ''
		]
	];

	// TODO we need to handle url, labels, video differently

	// Handle values from post fields or ACF fields
	foreach ($fields_map as $card_field => $post_field) {
		if (array_key_exists($card_field, $card)) {
			if ($post_field) {
				if (in_array($post_field, $wp_post_keys)) $card[$card_field] = $post->$post_field;
				else $card[$card_field] = get_field($post_field, $post->ID);
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

function get_thumbnail_image_object($post_id) {
	// Get the thumbnail ID
	$post_thumbnail_id = get_post_thumbnail_id($post_id);

	// No thumbnail, return null
	if (!$post_thumbnail_id) return null;

	// Get the WP Post object for the thumbnail
	$post = get_post($post_thumbnail_id);

	$image = [
		'ID' => $post_thumbnail_id,
		'id' => $post_thumbnail_id,
		'title' => $post->post_title,
		'filename' => basename(get_attached_file($post_thumbnail_id)),
		'filesize' => filesize(get_attached_file($post_thumbnail_id)),
		'url' => wp_get_attachment_url($post_thumbnail_id),
		'link' => get_attachment_link($post_thumbnail_id),
		'alt' => get_post_meta($post_thumbnail_id, '_wp_attachment_image_alt', true),
		'author' => $post->post_author,
		'description' => $post->post_content,
		'caption' => $post->post_excerpt,
		'name' => $post->post_name,
		'status' => get_post_status($post_thumbnail_id),
		'uploaded_to' => $post->post_parent,
		'date' => $post->post_date,
		'modified' => $post->post_modified,
		'menu_order' => $post->menu_order,
		'mime_type' => get_post_mime_type($post_thumbnail_id),
		'type' => explode('/', get_post_mime_type($post_thumbnail_id))[0],
		'subtype' => explode('/', get_post_mime_type($post_thumbnail_id))[1],
		'icon' => wp_mime_type_icon('mime_type'),
		'width' => wp_get_attachment_image_src($post_thumbnail_id, 'full')[1],
		'height' => wp_get_attachment_image_src($post_thumbnail_id, 'full')[2],
		'sizes' => []
	];

	// Set the sizes
	foreach (get_intermediate_image_sizes() as $size) {
		$s = wp_get_attachment_image_src( $post_thumbnail_id, $size);
		$image['sizes'][$size] = $s[0];
		$image['sizes'][$size . '-width'] = $s[1];
		$image['sizes'][$size . '-height'] = $s[2];
	}

	return $image;
}

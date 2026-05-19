<?php

/**
 * default.php - Lyquix Related Items block render function
 *
 * A simplified version of the Filters block that displays related posts
 * without interactive controls, search, or pagination.
 *
 * @version     3.2.0
 * @package     wp_theme_lyquix
 * @author      Lyquix
 * @copyright   Copyright (C) 2015 - 2024 Lyquix
 * @license     GNU General Public License version 2 or later
 * @link        https://github.com/Lyquix/wp_theme_lyquix
 */

$s = \lqx\util\validate_data($settings['processed'], [
	'type' => 'object',
	'required' => true,
	'keys' => [
		'anchor' => \lqx\util\schema_str_req_emp,
		'class' => \lqx\util\schema_str_req_emp,
		'hash' => [
			'type' => 'string',
			'required' => true,
			'default' => 'id-' . md5(json_encode([$settings, random_int(1000, 9999)]))
		],
		'post_type' => \lqx\util\schema_str_req_notemp,
		'relationship_type' => [
			'type' => 'string',
			'required' => true,
			'default' => 'taxonomy',
			'allowed' => ['taxonomy', 'field', 'post_parent']
		],
		'taxonomy' => \lqx\util\schema_str_req_emp,
		'acf_field' => \lqx\util\schema_str_req_emp,
		'limit' => [
			'type' => 'integer',
			'required' => true,
			'default' => 6,
			'range' => [1, 50]
		],
		'heading' => \lqx\util\schema_str_req_emp,
		'heading_tag' => [
			'type' => 'string',
			'required' => true,
			'default' => 'h2',
			'allowed' => ['h2', 'h3', 'h4', 'h5']
		],
		'cards_preset' => \lqx\util\schema_str_req_emp,
		'cards_style' => \lqx\util\schema_str_req_emp,
		'render_php' => [
			'type' => 'object',
			'required' => true,
			'keys' => [
				'heading' => \lqx\util\schema_str_req_emp,
				'subheading' => \lqx\util\schema_str_req_emp,
				'body' => \lqx\util\schema_str_req_emp,
				'image' => \lqx\util\schema_str_req_emp,
				'icon_image' => \lqx\util\schema_str_req_emp,
				'use_post_url' => \lqx\util\schema_str_req_y,
				'link_style' => [
					'type' => 'string',
					'required' => true,
					'default' => 'button',
					'allowed' => ['button', 'link']
				],
				'link_title' => \lqx\util\schema_str_req_emp,
				'link_target' => [
					'type' => 'string',
					'required' => true,
					'default' => '',
					'allowed' => ['', '_blank', '_self', '_parent', '_top']
				],
				'label_type' => [
					'type' => 'string',
					'required' => true,
					'default' => 'taxonomy',
					'allowed' => ['taxonomy', 'field']
				],
				'label_taxonomies' => [
					'type' => 'array',
					'required' => true,
					'default' => [],
					'elems' => ['type' => 'string']
				]
			]
		]
	]
]);

if (!$s['isValid']) return;
$s = $s['data'];

// Get the current post
$current_post_id = get_the_ID();
if (!$current_post_id) return;

// Build the query based on relationship type
$query_args = [
	'post_type' => $s['post_type'],
	'post_status' => 'publish',
	'posts_per_page' => $s['limit'],
	'post__not_in' => [$current_post_id],
	'tribe_suppress_query_filters' => true
];

switch ($s['relationship_type']) {
	case 'taxonomy':
		if (empty($s['taxonomy'])) return;
		$terms = wp_get_post_terms($current_post_id, $s['taxonomy'], ['fields' => 'ids']);
		if (is_wp_error($terms) || empty($terms)) return;
		$query_args['tax_query'] = [
			[
				'taxonomy' => $s['taxonomy'],
				'field' => 'term_id',
				'terms' => $terms,
				'operator' => 'IN'
			]
		];
		break;

	case 'field':
		if (empty($s['acf_field'])) return;
		$field_value = get_field($s['acf_field'], $current_post_id);
		if (empty($field_value)) return;
		// If the field returns an array of post IDs (relationship field), use post__in
		if (is_array($field_value)) {
			$related_ids = array_filter(array_map(function($v) {
				return is_object($v) ? $v->ID : (is_numeric($v) ? (int)$v : null);
			}, $field_value));
			if (empty($related_ids)) return;
			$query_args['post__in'] = $related_ids;
			$query_args['post__not_in'] = []; // override exclusion since we want exact matches
		} else {
			// Scalar field — find posts with the same value
			$field_obj = get_field_object($s['acf_field'], $current_post_id, false, false);
			$query_args['meta_query'] = [
				[
					'key' => $field_obj['name'] ?? $s['acf_field'],
					'value' => $field_value,
					'compare' => 'LIKE'
				]
			];
		}
		break;

	case 'post_parent':
		$parent_id = wp_get_post_parent_id($current_post_id);
		if (!$parent_id) return;
		$query_args['post_parent'] = $parent_id;
		break;
}

$query = new \WP_Query($query_args);

if (!$query->have_posts()) {
	wp_reset_postdata();
	return;
}

// Build post items array matching the cards block format
$wp_post_keys = ['post_content', 'post_title', 'post_excerpt', 'post_name'];
$posts = [];

while ($query->have_posts()) {
	$query->the_post();
	$post = get_post(get_the_ID());

	$p = [
		'id' => $post->ID,
		'heading' => null,
		'subheading' => null,
		'slug' => $post->post_name,
		'link' => ['url' => get_permalink($post->ID), 'title' => $s['render_php']['link_title'], 'target' => $s['render_php']['link_target']],
		'link_style' => $s['render_php']['link_style'] ?? 'button',
		'image' => null,
		'icon_image' => null,
		'video' => ['type' => 'url'],
		'labels' => [],
		'additional_classes' => '',
		'item_id' => '',
	];

	// Map heading, subheading, body
	foreach (['heading', 'subheading', 'body'] as $key) {
		if (!empty($s['render_php'][$key])) {
			$field_name = $s['render_php'][$key];
			if (in_array($field_name, $wp_post_keys)) {
				$p[$key] = $post->$field_name;
			} else {
				$p[$key] = get_field($field_name, $post->ID);
			}
		}
	}

	// Map image
	if (!empty($s['render_php']['image'])) {
		if ($s['render_php']['image'] == 'thumbnail') {
			$p['image'] = \lqx\util\get_thumbnail_image_object($post->ID);
		} else {
			$p['image'] = get_field($s['render_php']['image'], $post->ID);
		}
	}

	// Map icon_image
	if (!empty($s['render_php']['icon_image'])) {
		if ($s['render_php']['icon_image'] == 'thumbnail') {
			$p['icon_image'] = \lqx\util\get_thumbnail_image_object($post->ID);
		} else {
			$p['icon_image'] = get_field($s['render_php']['icon_image'], $post->ID);
		}
	}

	// Map labels
	if (($s['render_php']['label_type'] ?? '') == 'taxonomy' && !empty($s['render_php']['label_taxonomies'])) {
		foreach ($s['render_php']['label_taxonomies'] as $tax) {
			$terms = get_the_terms($post->ID, $tax);
			if (!is_wp_error($terms) && !empty($terms)) {
				foreach ($terms as $term) {
					$p['labels'][] = ['label' => $term->name, 'value' => $tax . ':' . $term->slug];
				}
			}
		}
	}

	// Custom link from ACF field
	if (($s['render_php']['use_post_url'] ?? 'y') == 'n' && !empty($s['render_php']['link'])) {
		$custom_link = get_field($s['render_php']['link'], $post->ID);
		if ($custom_link) $p['link'] = $custom_link;
	}

	$posts[] = $p;
}

wp_reset_postdata();

if (empty($posts)) return;

// Render using template
require __DIR__ . '/default.tmpl.php';

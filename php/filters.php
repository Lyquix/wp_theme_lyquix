<?php

/**
 * filters.php - Utility functions and REST API for filters
 *
 * @version     3.0.0
 * @package     wp_theme_lyquix
 * @author      Lyquix
 * @copyright   Copyright (C) 2015 - 2018 Lyquix
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

namespace lqx\filters;

/**
 * Populate the Cards block styles and presets
 */
add_filter('acf/load_field', function ($field) {
	$field_keys = [
		// Cards
		[ // style and style_name fields
			'user' => 'field_65fc7adc8549d',
			'choice' => 'field_658db3c35c5ac'
		],
		[ // preset and preset_name fields
			'user' => 'field_65fc7acd8549c',
			'choice' => 'field_658db3c5e9695'
		]
	];

	foreach ($field_keys as $k) {
		if ($field['key'] == $k['user']) {
			$choice_field = get_field_object($k['choice']);

			// Add an empty choice
			$field['choices'] = ['' => 'Select'];

			while (have_rows($choice_field['parent'], 'option')) {
				the_row();
				$value = get_sub_field($k['choice'], 'option');
				$field['choices'][$value] = $value;
			}
		}
	}
	return $field;
});

/**
 * Get ACF fields as options
 * Recursive function to build field options, including sub-fields
 * @param  array $field_details - ACF field details
 * @param  array $choices - array to store the options
 * @param  int $depth - depth of the field
 */
function get_acf_fields_as_options($field_details, &$choices, $depth = 0) {
	$key = $field_details['key'];
	$choices[$key] = str_repeat('- ', $depth) . ($field_details['label'] ? $field_details['label'] : $field_details['name']) . ' [' . $field_details['key'] . ']';

	if ($field_details['sub_fields'] ?? false) {
		foreach ($field_details['sub_fields'] as $sub_field_details) {
			get_acf_fields_as_options($sub_field_details, $choices, $depth + 1);
		}
	}
}

/**
 * Populate the Field Name fields in the Filters settings
 */
add_filter('acf/load_field', function ($field) {
	$field_keys = [
		'field_65f1ea274754b' => null, // pref_filters > acf_field
		'field_65f1ebb9ef068' => null, // filters > acf_field
		'field_65f248687356f' => null, // posts_order > acf_field
		'field_65f3010821d84' => null, // render_js > post_fields > acf_field
		'field_65f471bf7d99b' => 'text', // render_php > heading
		'field_65f475117fcd5' => 'text', // render_php > subheading
		'field_65f4752a7fcd6' => 'image', // render_php > image
		'field_65f4752f7fcd7' => 'image', // render_php > icon_image
		'field_65f475367fcd8' => 'link', // render_php > video_url
		'field_65f4753d7fcd9' => 'file', // render_php > video_upload
		'field_65f475457fcda' => 'text', // render_php > body
		'field_65f4754e7fcdb' => 'text', // render_php > labels
		'field_66393791fb81d' => 'link'  // render_php > url
	];

	$field_types = [
		'text' => [
			'text', 'textarea', 'number', 'range', 'email', 'url', 'password', 'wysiwyg',
			'select', 'checkbox', 'radio', 'button_group', 'date_picker', 'date_time_picker',
			'time_picker', 'color_picker'
		],
		'image' => ['image'],
		'file' => ['file'],
		'link' => ['link']
	];

	if (!array_key_exists($field['key'], $field_keys)) return $field;

	// Get all field groups
	$field_groups = acf_get_field_groups();

	// Filter out certain field groups
	$field_groups = array_filter($field_groups, function ($group) {
		if (strpos($group['title'], 'Custom Post Type: ') !== false) return true;
	});

	// Loop through field groups
	foreach ($field_groups as $group) {
		// Create group option
		$field['choices'][$group['title']] = [];

		// Get the field group fields
		$group['fields'] = acf_get_fields($group['key']);

		// Loop through fields in group and filter out by field type
		foreach ($group['fields'] as $field_details) {
			if ($field_keys[$field['key']] == null || in_array($field_details['type'], $field_types[$field_keys[$field['key']]])) {
				\lqx\filters\get_acf_fields_as_options($field_details, $field['choices'][$group['title']]);
			}
		}
	}

	return $field;
});

/**
 * Get the post author info
 * @param  int $author_id - author ID
 * @param  array $fields - fields to return
 */
function get_author_info($author_id, $fields = ['name', 'avatar']) {
	$author = get_user_by('id', $author_id);

	if (!$author) return null;

	// Prepare author info
	$author_info = [
		'id' => $author->ID,
		'name' => $author->display_name,
		'login' => $author->user_login,
		'slug' => $author->user_nicename,
		'email' => $author->user_email,
		'url' => $author->user_url,
		'bio' => get_user_meta($author->ID, 'description', true),
		'avatar' => get_avatar_url($author->user_email),
	];

	// Remove fields that have not been requested
	foreach ($author_info as $k => $v) {
		if (!in_array($k, $fields)) unset($author_info[$k]);
	}

	return $author_info;
}

/**
 * Get the post thumbnails info
 * @param  int $post_id - post ID
 * @param  array $sizes - thumbnail sizes
 */
function get_thumbnails_info($post_id, $sizes = ['Large']) {
	$thumbnails = [];

	foreach ($sizes as $size) {
		$thumbnails[$size] = get_the_post_thumbnail_url($post_id, $size);
	}

	return $thumbnails;
}

/**
 * Perform the complete settings processing and get the posts with data
 * @param  array $settings - settings data
 * @return array - processed settings data with posts and total pages
 */
function get_settings_and_posts($settings) {
	// Validate settings / get processed settings
	$s = validate_settings($settings);

	// Initialize settings
	$s = init_settings($s);

	// Get options
	$s = get_options($s);

	// Get the posts
	$post_info = get_posts_with_data($s);
	$s['posts'] = $post_info['posts'];
	$s['pagination']['total_posts'] =  $post_info['total_posts'];
	$s['pagination']['total_pages'] =  $post_info['total_pages'];

	return $s;
}

/**
 * Validate the settings data and return the processed settings
 * @param  array $settings - settings data
 * @return array - processed settings data
 */
function validate_settings($settings) {
	// Get and validate processed settings
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
			'pre_filters' => [
				'type' => 'array',
				'required' => true,
				'default' => [],
				'elems' => [
					'type' => 'object',
					'keys' => [
						'type' => [
							'type' => 'string',
							'required' => true,
							'allowed' => ['author', 'date', 'field', 'taxonomy']
						],
						// TODO add validation for taxonomy_term
						/*
						'taxonomy_term' => [
							'type' => 'object',
							'keys' => [
								'term_id' => \lqx\util\schema_int,
								'name' => \lqx\util\schema_str_req_emp,
								'slug' => \lqx\util\schema_str_req_emp,
								'term_group' => \lqx\util\schema_int,
								'term_taxonomy_id' => \lqx\util\schema_int,
								'taxonomy' => \lqx\util\schema_str_req_emp,
								'description' => \lqx\util\schema_str_req_emp,
								'parent' => \lqx\util\schema_int,
								'count' => \lqx\util\schema_int,
								'filter' => \lqx\util\schema_str_req_emp,
								'term_order' => \lqx\util\schema_int
							]
						],
						*/
						'acf_field' => \lqx\util\schema_str_req_emp,
						'operator_simple' => [
							'type' => 'string',
							'allowed' => ['=', '!='],
							'default' => '='
						],
						'operator_advanced' => [
							'type' => 'string',
							'allowed' => ['=', '!=', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN', 'NOT EXISTS', 'REGEXP', 'NOT REGEXP', 'RLIKE'],
							'default' => '='
						],
						'value' => \lqx\util\schema_str_req_emp,
						'anchor' => [
							'type' => 'string',
							'allowed' => ['d', 'w', 'm', 'y'],
							'default' => 'd'
						],
						'unit' => [
							'type' => 'string',
							'allowed' => ['d', 'w', 'm', 'y'],
							'default' => 'd'
						],
						'start' => [
							'type' => 'integer',
							'required' => true,
							'range' => [-30, 30],
							'default' => 0
						],
						'end' => [
							'type' => 'integer',
							'required' => true,
							'range' => [-30, 30],
							'default' => 1
						]
					]
				]
			],
			'use_hash' => \lqx\util\schema_str_req_n,
			'show_open_close' => \lqx\util\schema_str_req_n,
			'show_search' => \lqx\util\schema_str_req_y,
			'show_clear' => \lqx\util\schema_str_req_y,
			'layout' => [
				'type' => 'string',
				'required' => true,
				'default' => 'stacked',
				'allowed' => ['stacked', 'tabbed']
			],
			'open_label' => \lqx\util\schema_str_req_emp,
			'close_label' => \lqx\util\schema_str_req_emp,
			'search_placeholder' => \lqx\util\schema_str_req_emp,
			'clear_label' => \lqx\util\schema_str_req_emp,
			'controls' => [
				'type' => 'array',
				'required' => true,
				'default' => [],
				'elems' => [
					'type' => 'object',
					'keys' => [
						'visible' => \lqx\util\schema_str_req_y,
						'type' => [
							'type' => 'string',
							'required' => true,
							'allowed' => ['taxonomy', 'field']
						],
						'taxonomy' => \lqx\util\schema_str_req_emp,
						'acf_field' => \lqx\util\schema_str_req_emp,
						'alias' => \lqx\util\schema_str_req_emp,
						'presentation' => [
							'type' => 'string',
							'required' => true,
							'default' => 'select',
							'allowed' => ['select', 'checkbox', 'radio', 'list']
						],
						'order_by' => [
							'type' => 'string',
							'required' => true,
							'default' => 'alpha',
							'allowed' => ['alpha', 'count', 'custom']
						],
						'order' => [
							'type' => 'string',
							'required' => true,
							'default' => 'asc',
							'allowed' => ['asc', 'desc']
						],
						'custom_order' => [
							'type' => 'array',
							'required' => true,
							'default' => [],
							'elems' => [
								'type' => 'object',
								'keys' => [
									'value' => \lqx\util\schema_str_req_emp
								],
								'default' => ['value' => '']
							]
						]
					]
				]
			],
			'show_all' => \lqx\util\schema_str_req_n,
			'pagination' => \lqx\util\schema_str_req_y,
			'page_numbers' => [
				'type' => 'string',
				'required' => true,
				'default' => '3',
				'allowed' => ['0', '1', '3', '5', '7', 'all']
			],
			'pagination_details' => \lqx\util\schema_str_req_y,
			'show_posts_per_page' => \lqx\util\schema_str_req_n,
			'posts_per_page' => [
				'type' => 'integer',
				'default' => 10,
				'range' => [1, 100]
			],
			'posts_per_page_options' => \lqx\util\schema_str_req_notemp,
			'posts_order' => [
				'type' => 'array',
				'required' => true,
				'default' => [],
				'elems' => [
					'type' => 'object',
					'keys' => [
						'order_by' => [
							'type' => 'string',
							'required' => true,
							'allowed' => ['date', 'title', 'name', 'author', 'rand', 'field', 'modified']
						],
						'order' => [
							'type' => 'string',
							'required' => true,
							'allowed' => ['asc', 'desc']
						],
						'acf_field' => \lqx\util\schema_str_req_emp,
						'data_type' => [
							'type' => 'string',
							'required' => true,
							'allowed' => ['', 'NUMERIC', 'BINARY', 'DATE', 'DATETIME', 'DECIMAL', 'SIGNED', 'TIME', 'UNSIGNED'],
							'default' => ''
						]
					]
				]
			],
			'render_mode' => [
				'type' => 'string',
				'required' => true,
				'default' => 'php',
				'allowed' => ['php', 'js']
			],
			'render_php' => [
				'type' => 'object',
				'required' => true,
				'keys' => [
					'preset' => \lqx\util\schema_str_req_emp,
					'style' => \lqx\util\schema_str_req_emp,
					'heading' => \lqx\util\schema_str_req_emp,
					'subheading' => \lqx\util\schema_str_req_emp,
					'body' => \lqx\util\schema_str_req_emp,
					'image' => \lqx\util\schema_str_req_emp,
					'icon_image' => \lqx\util\schema_str_req_emp,
					'video_type' => [
						'type' => 'string',
						'required' => true,
						'default' => 'url',
						'allowed' => ['url', 'upload']
					],
					'video_url' => \lqx\util\schema_str_req_emp,
					'video_upload' => \lqx\util\schema_str_req_emp,
					'label_type' => [
						'type' => 'string',
						'required' => true,
						'default' => 'taxonomy',
						'allowed' => ['taxonomy', 'field']
					],
					'label_field' => \lqx\util\schema_str_req_emp,
					'label_taxonomies' => [
						'type' => 'array',
						'required' => true,
						'default' => [],
						'elems' => [
							'type' => 'string'
						]
					],
					'use_post_url' => \lqx\util\schema_str_req_y,
					'link_style' => [
						'type' => 'string',
						'required' => true,
						'default' => 'button',
						'allowed' => ['button', 'link']
					],
					'link' => \lqx\util\schema_str_req_emp,
					'link_title' => \lqx\util\schema_str_req_emp,
					'link_target' => [
						'type' => 'string',
						'required' => true,
						'default' => '',
						'allowed' => ['', '_blank', '_self', '_parent', '_top']
					]
				]
			],
			'render_js' => [
				'type' => 'object',
				'required' => true,
				'keys' => [
					'post_content' => \lqx\util\schema_str_req_n,
					'post_excerpt' => \lqx\util\schema_str_req_n,
					'post_author' => [
						'type' => 'array',
						'required' => true,
						'default' => ['name', 'avatar'],
						'elems' => [
							'type' => 'string',
							'allowed' => ['id', 'name', 'login', 'slug', 'email', 'url', 'bio', 'avatar']
						]
					],
					'post_thumbnail' => \lqx\util\schema_str_req_n,
					'thumbnail_sizes' => [
						'type' => 'array',
						'required' => true,
						'default' => ['Large'],
						'elems' => [
							'type' => 'string'
						]
					],
					'post_taxonomies' => [
						'type' => 'array',
						'required' => true,
						'default' => [],
						'elems' => [
							'type' => 'string'
						]
					],
					'post_fields' => [
						'type' => 'array',
						'required' => true,
						'default' => [],
						'elems' => [
							'type' => 'object',
							'keys' => [
								'acf_field' => [
									'type' => 'string'
								]
							]
						]
					],
					'link_style' => [
						'type' => 'string',
						'required' => true,
						'default' => 'button',
						'allowed' => ['button', 'link']
					],
				]
			],
		]
	]);

	// If valid settings, use them, otherwise throw exception
	if ($s['isValid']) return $s['data'];
	else throw new \Exception('Invalid block settings: ' . var_export($s, true));
}

/**
 * Get settings for the filters block
 * @param  array $s - processed settings
 * @param  int $post_id - post ID
 */
function init_settings($s) {
	// Clean pre-filters settings
	foreach ($s['pre_filters'] as $i => $pre_filter) {
		switch ($pre_filter['type']) {
			case 'date':
				foreach ([
					'operator_simple',
					'operator_advanced',
					'taxonomy_term',
					'value'
				] as $k) {
					unset($pre_filter[$k]);
				}

				if ($pre_filter['date_source'] !== 'field') unset($pre_filter['acf_field']);

				break;

			case 'taxonomy':
				foreach ([
					'operator_advanced',
					'date_source',
					'acf_field',
					'value',
					'anchor',
					'unit',
					'start',
					'end'
				] as $k) {
					unset($pre_filter[$k]);
				}

				break;

			case 'field':
				foreach ([
					'operator_simple',
					'date_source',
					'taxonomy_term',
					'anchor',
					'unit',
					'start',
					'end'
				] as $k) {
					unset($pre_filter[$k]);
				}

				break;

			case 'author':
				foreach ([
					'operator_advanced',
					'date_source',
					'taxonomy_term',
					'acf_field',
					'anchor',
					'unit',
					'start',
					'end'
				] as $k) {
					unset($pre_filter[$k]);
				}

				break;
		}

		$s['pre_filters'][$i] = $pre_filter;
	}

	// Clean up posts order settings
	foreach ($s['posts_order'] as $i => $post_order) {
		if (in_array($post_order['order_by'], [
			'author',
			'date',
			'modified',
			'title',
			'name',
			'rand'
		])) {
			unset($post_order['acf_field']);
			unset($post_order['data_type']);
		}

		if ($post_order['order_by'] == 'rand') unset($post_order['order']);

		$s['posts_order'][$i] = $post_order;
	}

	// Clean up controls settings and get control labels and other details
	foreach ($s['controls'] as $i => $control) {
		// Add empty `selected` key
		if (!array_key_exists('selected', $control)) $control['selected'] = '';

		// Get the label and other details
		switch ($control['type']) {
			case 'taxonomy':
				// Set the label
				if (!isset($control['label'])) $control['label'] = get_taxonomy($control['taxonomy'])->label;

				// Remove non-taxonomy fields
				unset($control['acf_field']);
				break;

			case 'field':
				// Get the field settings
				$field = get_field_object($control['acf_field'], null, true, false, false);

				// Set the label
				if (!isset($control['label'])) $control['label'] = $field['label'];

				// Get key field settings
				$control['field_type'] = $field['type'];
				$control['field_name'] = $field['name'];
				if (isset($field['choices'])) $control['field_choices'] = $field['choices'];

				// Remove non-field fields
				unset($control['taxonomy']);
				break;
		}

		// Create 'slug' string to use as name of control in IDs, strings for active controls in hashes, etc
		$control['slug'] = \lqx\util\slugify($control['label']);

		// Convert custom order into an array of values
		$control['custom_order'] = array_map(function ($value) {
			return $value['value'];
		}, $control['custom_order']);

		$s['controls'][$i] = $control;
	}

	if ($s['render_mode'] == 'js') {
		// Convert post_fields to an array of field objects
		$post_fields = [];

		foreach ($s['render_js']['post_fields'] as $field_obj) {
			$field_obj = get_field_object($field_obj['acf_field'], null, true, false, false);

			$post_fields[$field_obj['name']] = [
				'key' => $field_obj['key'],
				'name' => $field_obj['name'],
				'label' => $field_obj['label'],
				'type' => $field_obj['type'],
				'choices' => isset($field_obj['choices']) ? $field_obj['choices'] : []
			];
		}

		$s['render_js']['post_fields'] = $post_fields;
	}

	// Add an empty `search` key
	if (!array_key_exists('search', $s)) $s['search'] = '';

	// Set the pagination object
	if ($s['show_all'] == 'y') {
		$pagination = [
			'show_all' => 'y'
		];
	} else {
		$pagination = [
			'show_all' => 'n',
			'page' => 1,
			'pagination' => $s['pagination'],
			'page_numbers' => $s['page_numbers'],
			'pagination_details' => $s['pagination_details'],
			'show_posts_per_page' => $s['show_posts_per_page']
		];

		if ($s['show_posts_per_page'] == 'y') {
			$pagination['posts_per_page_options'] = [];

			// Process the list of posts per page options
			foreach (explode(',', $s['posts_per_page_options']) as $option) {
				// Trim the option
				$option = trim($option);

				// The default option contains an asterisk
				if (strpos($option, '*') !== false) {
					$option = str_replace('*', '', $option);
					if (is_numeric($option)) {
						$option = intval($option);
						$pagination['posts_per_page_options'][] = $option;
						$pagination['posts_per_page'] = $option;
					}
				}
				else if (is_numeric($option)) {
					$pagination['posts_per_page_options'][] = intval($option);
				}
			}

			// If no default was set, pick the first option
			if (!isset($pagination['posts_per_page'])) $pagination['posts_per_page'] = $pagination['posts_per_page_options'][0];
		} else {
			$pagination['posts_per_page'] = $s['posts_per_page'];
		}
	}

	foreach ([
		'show_all',
		'pagination',
		'page_numbers',
		'pagination_details',
		'show_posts_per_page',
		'posts_per_page',
		'posts_per_page_options'
	] as $k) {
		unset($s[$k]);
	}

	$s['pagination'] = $pagination;

	// Remove unused render mode data
	switch ($s['render_mode']) {
		case 'php':
			unset($s['render_js']);
			break;

		case 'js':
			unset($s['render_php']);
			break;
	}

	return $s;
}

/**
 * Get the options for each control, from a given list of posts
 * @param  array $s - controls settings
 * @param  array $posts - list of posts
 */
function get_options($s) {
	global $wpdb;

	// If no posts were passed
	$posts = get_post_ids($s);

	// Cycle through controls
	foreach ($s['controls'] as $i => $control) {
		// If there are no posts, then controls should have no options available
		if (!count($posts)) {
			$s['controls'][$i]['options'] = [];
			continue;
		}

		// Get control options
		$options = [];

		switch ($control['type']) {
			case 'taxonomy':
				$terms = get_terms([
					'taxonomy' => $control['taxonomy'],
					'object_ids' => $posts
				]);

				foreach ($terms as $term) {
					$options[$term->name] = [
						'value' => $term->term_id,
						'slug' => $term->slug,
						'text' => $term->name,
						'disabled' => false,
						'selected' => false,
						'count' => $term->count
					];
				}

				break;

			case 'field':
				// Prepare the SQL query to get field values and post counts
				$sql = $wpdb->prepare(
					"SELECT `meta_value`, COUNT(`post_id`) as `count` " .
						"FROM $wpdb->postmeta " .
						"WHERE `meta_key` = '%s' " . // TODO: are we handling sub-fields within groups and repeaters correctly? We may need a LIKE operator here
						"AND `post_id` IN (" . implode(',', array_map('intval', $posts)) . ") " .
						"GROUP BY `meta_value`",
					$control['acf_field']
				);

				// Execute the query
				$field_values = $wpdb->get_results($sql);

				if (is_array($field_values)) {
					if ($control['field_type'] == 'relation') {
						foreach ($field_values as $field_value) {
							// Relation fields returns ids and so we need to get the slug and name for the posts
							$relation_values = unserialize($field_value->meta_value);
							if (is_array($relation_values)) {
								foreach ($relation_values as $relation_value) {
									if (!array_key_exists($relation_value, $options)) {
										$title = get_the_title($relation_value);
										$options[$relation_value] = [
											'value' => $relation_value,
											'slug' => get_post_field('post_name', $relation_value),
											'text' => $title,
											'disabled' => false,
											'selected' => false,
											'count' => $field_value->count
										];
									} else $options[$relation_value]['count'] += $field_value->count;
								}
							}
						}
					} else {
						if (isset($field['field_choices'])) {
							foreach ($field_values as $field_value) {
								// Field with choices like select, radio, checkbox
								foreach ($field['field_choices'] as $choice_key => $choice) {
									if ($choice_key == $field_value->meta_value) {
										$options[$field_value] = [
											'value' => $choice_key,
											'slug' => get_post_field('post_name', $choice_key),
											'text' => $choice,
											'disabled' => false,
											'selected' => false,
											'count' => $field_value->count
										];
									}
								}
							}
						} else {
							// All other field types
							// Loop through the field values
							foreach ($field_values as $field_value) {
								// Add the field value to the options array
								$options[$field_value->meta_value] = [
									'value' => $field_value->meta_value,
									'slug' => \lqx\util\slugify($field_value->meta_value),
									'text' => $field_value->meta_value,
									'disabled' => false,
									'selected' => false,
									'count' => $field_value->count
								];
							}
						}
					}
				}
				break;
		}

		// Outside the cases because this will always be utilized regardless of the order
		$order = $control['order'];

		switch ($control['order_by']) {
			case 'alpha':
				usort($options, function ($a, $b) use ($order) {
					if ($order === 'asc') {
						return strcmp($a['text'], $b['text']);
					} else {
						return strcmp($b['text'], $a['text']);
					}
				});
				break;

			case 'count':
				usort($options, function ($a, $b) use ($order) {
					if ($order === 'asc') {
						if ((int)$a['count'] >= (int)$b['count']) return 1;
						if ((int)$a['count'] < (int)$b['count']) return -1;
					} else {
						if ((int)$a['count'] < (int)$b['count']) return 1;
						if ((int)$a['count'] >= (int)$b['count']) return -1;
					}
				});
				break;

			case 'custom':
				// Create a temporary options to save things.
				$new_options = [];
				foreach ($options as $option) {
					//get index within settings and assign it to the new array
					$index = array_search($option['text'], $control['custom_order']);
					$new_options[$index] = $option;
				}

				// Sort new array, just adding the keys does not do this automatically so we need to do it here
				ksort($new_options);
				$options = $new_options;
				break;
		}

		// Add options to control
		$control['options'] = $options;

		// Save the control
		$s['controls'][$i] = $control;
	}

	return $s;
}

/**
 * Generate a WP Query object based on pre-filters, applied filters and passed arguments
 * @param  array $s - processed settings
 * @param  array $query - query arguments
 */
function prepare_query($query, $s) {
	// Pre-filters args
	$pre_filters = $s['pre_filters'];

	foreach ($pre_filters as $pre_filter) {
		switch ($pre_filter['type']) {
			case 'taxonomy':
				// find the tax that the term belongs to
				if ($pre_filter['taxonomy_term']->taxonomy !== null) {
					$tax_query = [
						'taxonomy' => $pre_filter['taxonomy_term']->taxonomy,
						'field' => 'term_id',
						'terms' => $pre_filter['taxonomy_term']->term_id,
					];
					// add the relevant query
					if (isset($query['tax_query'])) {
						$query['tax_query']['relation'] = 'AND';
						$query['tax_query'][] = $tax_query;
					} else {
						$query['tax_query'] = array();
						$query['tax_query'][] = $tax_query;
					}
				}
				break;

			case 'field':
				$acf_meta_query = [
					'key' => get_field_object($pre_filter['acf_field'])['name'],
					'compare' => $pre_filter['operator'],
					'value' => $pre_filter['value']
				];

				if (isset($query['meta_query'])) {
					$query['meta_query']['relation'] = 'AND';
					$query['meta_query'][] = $acf_meta_query;
				} else {
					$query['meta_query'] = array();
					$query['meta_query'][] = $acf_meta_query;
				}
				break;

			case 'date':
				// Before we set the date we need to understand the range we are making.
				// $anchor determines the point from where the beginning and end of the range is determined.
				// Thoughts: should we specify in the tooltip that because we can set both ends of the range here, we should only have one value of date in the pre-filters?
				$anchor = $pre_filter['anchor'];

				// Dates will need to check both the anchor for where the comparison starts and the unit to see how far back we go and in what increments
				switch ($anchor) {
					// Today
					case 'd':
						$anchor = date('Y-m-d');
						break;

					// This year
					case 'y':
						$anchor = date('Y', strtotime(date('Y-m-d'))) . '-01-01';
						break;

					// This month
					case 'm':
						$anchor = date('Y-m', strtotime(date('Y-m-d'))) . '-01';
						break;

					// This week
					case 'w':
						$anchor = date("Y-m-d", strtotime(date('Y-m-d') . ' sunday last week'));
						break;
				}

				// Unit is what type of scoping we're doing: day, week, month, year
				// We can use before to set a start and after to set an end
				// Since these are inside an array declaration we should generate these values before generating the date query
				// For these switch/cases:
				// before should use end and "+"
				// after should use start and "-"
				switch ($pre_filter['unit']) {
					case 'd':
						$before = date('Y-m-d', strtotime($anchor . ' +' . $pre_filter['end'] . ' days'));
						$after = date('Y-m-d', strtotime($anchor . ' -' . $pre_filter['start'] . ' days'));
						break;

					case 'w':
						$before = date('Y-m-d', strtotime($anchor . ' +' . $pre_filter['end'] . ' weeks'));
						$after = date('Y-m-d', strtotime($anchor . ' -' . $pre_filter['start'] . ' weeks'));
						break;

					case 'm':
						$before = date('Y-m-d', strtotime($anchor . ' +' . $pre_filter['end'] . ' months'));
						$after = date('Y-m-d', strtotime($anchor . ' -' . $pre_filter['start'] . ' months'));
						break;

					case 'y':
						$before = date('Y-m-d', strtotime($anchor . ' +' . $pre_filter['end'] . ' years'));
						$after = date('Y-m-d', strtotime($anchor . ' -' . $pre_filter['start'] . ' years'));
						break;
				}

				$date_query = [
					'before' => $before,
					'after' => $after,
					// Inclusive scopes in the current date when dealing with the before/after system
					'inclusive' => true,
				];

				$query['date_query'] = $date_query;
				break;

			case 'author':
				$query['author'] = $pre_filter['value'];
				break;
		}
	}

	// Controls args
	$controls = $s['controls'];

	foreach ($controls as $control) {
		// TODO we will need to update this code to handle taxonomy object
		// Ee only need to edit things if there is a value that has been set
		if ($control['selected'] !== '' && $control['selected'] !== false) {
			// Use different logic depending on the control
			// CONSIDER: will we have pre-filtered values alongside selected values?
			// In theory it should be okay because tax and field queries are arrays.
			switch ($control['type']) {
				case 'taxonomy':
					$tax_query = [
						'taxonomy' => $control['taxonomy'],
						'field' => 'term_id',
						'terms' => $control['selected'],
					];

					if (isset($query['tax_query'])) {
						$query['tax_query']['relation'] = 'AND';
						$query['tax_query'][] = $tax_query;
					} else {
						$query['tax_query'] = array();
						$query['tax_query'][] = $tax_query;
					}
					break;
					// TODO: we might need to edit this to try and figure out how to deal with subfields and dates.
					// TODO: We may need to rework how the comparison is done here

				case 'field':
					$acf_meta_query = [
						'key' => get_field_object($control['acf_field'])['name'],
						'compare' => 'LIKE',
						'value' => $control['selected']
					];

					if (isset($query['meta_query'])) {
						$query['meta_query']['relation'] = 'AND';
						$query['meta_query'][] = $acf_meta_query;
					} else {
						$query['meta_query'] = array();
						$query['meta_query'][] = $acf_meta_query;
					}
					break;
			}
		}
	}

	// Search args

	if ($s['search'] !== '') {
		$query['s'] = $s['search'];
	}

	// We need to iterate through the controls to see what has been selected and use that to narrow our query
	return new \WP_Query($query);
}

/**
 * Get the list of post IDs
 * @param  array $s - processed settings
 */
function get_post_ids($s) {
	$query = [
		'post_type' => $s['post_type'],
		'post_status' => 'publish',
		'posts_per_page' => -1,
		'fields' => 'ids'
	];

	$query = prepare_query($query, $s);

	$posts = $query->posts;

	wp_reset_postdata();

	return $posts;
}

/**
 * Get a list of posts with the specified fields
 * @param  array $s - processed settings
 * @param  int $page - page number
 */
function get_posts_with_data($s) {
	$query = [
		'post_type' => $s['post_type'],
		'post_status' => 'publish',
		'posts_per_page' => $s['pagination']['posts_per_page'],
		'paged' => $s['pagination']['page'],
		'orderby' => []
	];

	// First things first, we need to iterate through post order to get each type and its priority.
	// orderby has to be formatted as orderby => [type => order] to work as an array.
	if (is_array($s['posts_order'])) {
		foreach ($s['posts_order'] as $order) {
			if ($order['order_by'] === 'field') {
				$query['orderby']['meta_value'] = $order['order'];
				// We need to get the name of the field, not its key!
				$field_name = get_field_object($order['acf_field'])['name'];
				$query['meta_key'] = $field_name;
			} else if ($order['order_by'] === 'rand') {
				$query['orderby'] = 'rand';
				break;
			} else {
				$query['orderby'][$order['order_by']] = $order['order'];
			}
		}
	}

	$query = prepare_query($query, $s);
	$posts = [];

	if ($query->have_posts()) {
		while ($query->have_posts()) {
			$query->the_post();

			$post = get_post(get_the_ID());

			switch ($s['render_mode']) {
				case 'js':
					// Set the post object
					$post = [
						'id' => $post->ID,
						'author' => get_author_info($post->post_author, $s['render_js']['post_author']),
						'date' => $post->post_date_gmt,
						'content' => $s['render_js']['post_content'] == 'y' ? $post->post_content : '',
						'title' => $post->post_title,
						'excerpt' => $s['render_js']['post_excerpt'] == 'y' ? $post->post_excerpt : '',
						'slug' => $post->post_name,
						'modified' => $post->post_modified_gmt,
						'link' => get_permalink($post->ID),
						'link_style' => $s['render_js']['link_style'] ?? 'button',
						'type' => $post->post_type,
						'thumbnail' => $s['render_js']['post_thumbnail'] == 'y' ? get_thumbnails_info($post->ID, $s['render_js']['thumbnail_sizes']) : [],
						'taxonomies' => [],
						'fields' => []
					];

					// Get taxonomies
					foreach ($s['render_js']['post_taxonomies'] as $taxonomy) {
						$terms = get_terms([
							'taxonomy' => $taxonomy,
							'object_ids' => $post['id']
						]);

						$post['taxonomies'][$taxonomy] = [];

						foreach ($terms as $term) {
							$post['taxonomies'][$taxonomy][$term->name] = [
								'value' => $term->term_id,
								'slug' => $term->slug,
								'text' => $term->name,
							];
						}
					}

					// Get fields
					foreach ($s['render_js']['post_fields'] as $field_name => $field_obj) {
						$post['fields'][$field_name] = get_field($field_obj['key'], $post['id']);
					}

					break;

				case 'php':
					// List of field names that represent WP_Post fields, not ACF fields
					$wp_post_keys = ['post_content', 'post_title', 'post_excerpt', 'post_name'];

					// Set the defaults
					$p = [
						'id' => $post->ID,
						'date' => $post->post_date_gmt,
						'heading' => null,
						'subheading' => null,
						'slug' => $post->post_name,
						'modified' => $post->post_modified_gmt,
						'link' => [
							'url' => get_permalink($post->ID),
							'title' => $s['render_php']['link_title'],
							'target' => $s['render_php']['link_target']
						],
						'link_style' => $s['render_php']['link_style'] ?? 'button',
						'body' => null,
						'labels' => [],
						'image' => null,
						'icon_image' => null,
						'video' => [
							'type' => $s['render_php']['video_type'] ?? 'url'
						]
					];

					// Handle heading, subheading and body
					foreach (['heading', 'subheading', 'body'] as $key) {
						if ($s['render_php'][$key]) {
							if (in_array($s['render_php'][$key], $wp_post_keys)) {
								$p[$key] = $post->{$s['render_php'][$key]};
							} else {
								$p[$key] = get_field($s['render_php'][$key], $post->ID);
							}
						}
					}

					// Handle image and icon_image
					foreach (['image', 'icon_image'] as $key) {
						if ($s['render_php'][$key]) {
							if ($s['render_php'][$key] == 'thumbnail') {
								$p[$key] = \lqx\util\get_thumbnail_image_object($post->ID);
							} else {
								$p[$key] = get_field($s['render_php'][$key], $post->ID);
							}
						}
					}

					// Handle the URL
					if ($s['render_php']['use_post_url'] == 'n'){
						if ($s['render_php']['link']) $p['link'] = get_field($s['render_php']['link'], $post->ID);
						else $p['link'] = null;
					}

					// Handle video
					$video_url = get_field($s['render_php']['video_url'], $post->ID);
					$video_upload = get_field($s['render_php']['video_upload'], $post->ID);
					if ($s['render_php']['video_type'] == 'url' && $video_url) {
						$p['video'] = [
							'type' => 'url',
							'url' => $video_url
						];
					}
					elseif ($s['render_php']['video_type'] == 'upload' && $video_upload) {
						$p['video'] = [
							'type' => 'upload',
							'upload' => $video_upload
						];
					}

					// Handle labels
					switch ($s['render_php']['label_type']) {
						case 'taxonomy':
							foreach ($s['render_php']['label_taxonomies'] ?? [] as $tax) {
								$terms = get_the_terms($post->ID, $tax);
								if ($terms !== false) {
									foreach ($terms as $term) {
										$p['labels'][] = [
											'label' => $term->name,
											'value' => $tax . ':' . $term->slug
										];
									}
								}
							}
							break;

						case 'field':
							$label_field_object = get_field_object($s['render_php']['label_field'], $post->ID);

							if ($label_field_object != false) {
								if (is_array($label_field_object['value'])) {
									foreach ($label_field_object['value'] as $value) {
										$p['labels'][] = [
											'label' => $value,
											'value' => \lqx\util\slugify($value)
										];
									}
								}
								else {
									$p['labels'][] = [
										'label' => $label_field_object['value'],
										'value' => \lqx\util\slugify($label_field_object['value'])
									];
								}
							}

							break;
					}

					$post = $p;

					break;
			}

			$posts[] = $post;
		}
	}

	$total_posts = $query->found_posts;
	$total_pages = $query->max_num_pages;
	wp_reset_postdata();

	return [
		'posts' => $posts,
		'total_posts' => $total_posts,
		'total_pages' => $total_pages
	];
}

/**
 * Default HTML render for controls and search bar
 * @param  array $s - processed settings
 */
function render_controls($s) {
	// Start output buffering
	ob_start();

	// Render the controls
	require \lqx\blocks\get_template('filters', $s['preset'], 'controls');

	// Return the output
	return ob_get_clean();
}

/**
 * Default HTML render for posts using the Cards block
 * @param  array $s - processed settings and posts data
 */
function render_posts($s) {
	// Start output buffering
	ob_start();

	// Render the posts
	require \lqx\blocks\get_template('filters', $s['preset'], 'posts');

	// Return the output
	return ob_get_clean();
}

/**
 * Default HTML render for the pagination, pagination details and posts per page selector
 * @param  array $s - processed settings
 */
function render_pagination($s) {
	// Start output buffering
	ob_start();

	// Render the pagination
	require \lqx\blocks\get_template('filters', $s['preset'], 'pagination');

	// Return the output
	return ob_get_clean();
}

/**
 * Get the label of the selected option
 * @param  array $control - control settings
 */
function get_selected_option_label($control) {
	$index = array_search($control['selected'], array_column($control['options'], 'value'));
	if ($index !== false) return $control['options'][$index]['text'];
	return '';
}

/**
 * Prepare the data for JSON
 * @param  array $s - processed settings
 */
function prepare_json_data($s) {
	$res = $s;

	// Remove keys that are not needed or that should not be disclosed
	foreach (['post_type', 'pre_filters', 'posts_order', 'render_php'] as $key) unset($res[$key]);

	// Handle server-side rendering
	if ($s['render_mode'] == 'php') {
		foreach(['anchor', 'block', 'class', 'clear_label', 'posts', 'render_js',
		'search_placeholder', 'show_clear', 'show_search'] as $key) unset($res[$key]);
	}

	return $res;
}

/**
 * Validate the payload
 * @param  array $payload - received payload
 */
function validate_payload($payload) {
	// TODO: validate received $payload - leave this comment until we're done with the ts file
	return \lqx\util\validate_data($payload, [
		'type' => 'object',
		'required' => true,
		'keys' => [
			'preset' => \lqx\util\schema_str_req_notemp,
			'post_id' => \lqx\util\schema_int_req,
			'controls' => [
				'type' => 'array',
				'required' => true,
				'elems' => [
					'type' => 'object',
					'keys' => [
						'type' => [
							'type' => 'string',
							'required' => true,
							'allowed' => ['taxonomy', 'field']
						],
						'taxonomy' => \lqx\util\schema_str_req_emp,
						'acf_field' => \lqx\util\schema_str_req_emp,
						'selected' => \lqx\util\schema_str_req_emp
					]
				]
			],
			'search' => \lqx\util\schema_str_req_emp,
			'page' => \lqx\util\schema_int_req,
			'pagination' => [
				'type' => 'object',
				'keys' => [
					'posts_per_page' => \lqx\util\schema_int_req
				]
			]
		]
	]);
}

/**
 * Merge the payload with the settings
 * @param  array $s - processed settings
 * @param  array $p - received payload
 */
function merge_settings($s, $p) {
	foreach ($p as $key => $value) {
		if (is_array($value)) {
			// List arrays
			if (\lqx\util\array_is_list($value)) {
				// Assumes that both lists must be the same length and same order
				// Traverse the list
				foreach ($value as $i => $v) {
					// If the setting is being overriden, traverse list arrays
					$s[$key][$i] = merge_settings($s[$key][$i], $v);
				}
			}
			// Associative arrays
			else {
				if (isset($s[$key])) {
					// If the setting is being overriden, traverse associative arrays
					$s[$key] = merge_settings($s[$key], $value);
				} else {
					// Otherwise, just set the value
					$s[$key] = $value;
				}
			}
		} else {
			// Set the value for primitive values
			$s[$key] = $value;
		}
	}

	return $s;
}

/**
 * Handle the API calls
 * @param  array $payload - received payload
 */
function handle_api_call($request) {
	// Get the payload
	$payload = $request->get_json_params();

	// TODO payload validation
	/*
	// Remove any keys that are not allowed
	foreach (array_keys($payload) as $k) {
		if (!in_array($k, ['preset', 'post_id', 'controls', 'search', 'pagination'])) unset($payload[$k]);

		if ($k == 'pagination') {
			foreach (array_keys($payload['pagination']) as $kk) {
				if ($k != 'posts_per_page') unset($payload['pagination'][$kk]);
			}
		}
	}

	// Validate the payload. If invalid return null, otherwise get the data
	$p = validate_payload($payload);
	if (!$p['isValid']) return null;
	$p = $p['data'];
	*/
	$p = $payload;

	// Get settings
	$settings = \lqx\blocks\get_settings('filters', $p['post_id'], $p['preset'], $p['style']);

	// Validate settings / get processed settings
	$s = \lqx\filters\validate_settings($settings);

	// Initialize settings
	$s = \lqx\filters\init_settings($s);

	// Merge payload with settings
	$s = \lqx\filters\merge_settings($s, $p);

	// Get options
	$s = \lqx\filters\get_options($s);

	// Get the posts
	$post_info = \lqx\filters\get_posts_with_data($s);
	$s['posts'] = $post_info['posts'];
	$s['pagination']['total_posts'] = $post_info['total_posts'];
	$s['pagination']['total_pages'] = $post_info['total_pages'];

	// Prepare the JSON data
	$res = \lqx\filters\prepare_json_data($s);

	// Prepare JSON render
	if ($s['render_mode'] == 'php') {
		$res['render'] = [
			'controls' => \lqx\util\minify_html(render_controls($s)),
			'posts' => \lqx\util\minify_html(render_posts($s)),
			'pagination' => \lqx\util\minify_html(render_pagination($s))
		];
	}

	return $res;
}

/**
 * Register a REST API endpoint for filters
 */
add_action('rest_api_init', function () {
	register_rest_route('lyquix/v3', '/filters', [
		'methods' => 'POST',
		'callback' => '\lqx\filters\handle_api_call',
		'permission_callback' => '__return_true',
	]);
});

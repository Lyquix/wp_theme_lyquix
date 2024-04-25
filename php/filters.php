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
	if (!in_array($field['key'], [
		'field_65f1ea274754b', // pref_filters > acf_field
		'field_65f1ebb9ef068', // filters > acf_field
		'field_65f248687356f', // posts_order > acf_field
		'field_65f3010821d84', // render_custom > post_fields > acf_field
		'field_65f471bf7d99b', // render_cards > heading
		'field_65f475117fcd5', // render_cards > subheading
		'field_65f4752a7fcd6', // render_cards > image
		'field_65f4752f7fcd7', // render_cards > icon_image
		'field_65f475367fcd8', // render_cards > video_url
		'field_65f4753d7fcd9', // render_cards > video_upload
		'field_65f475457fcda', // render_cards > body
		'field_65f4754e7fcdb', // render_cards > labels
		'field_65f475567fcdc' // render_cards > links
	])) return $field;

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

		// Loop through fields in group
		foreach ($group['fields'] as $field_details) {
			\lqx\filters\get_acf_fields_as_options($field_details, $field['choices'][$group['title']]);
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
 * Validate the settings data
 * @param  array $settings - settings data
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
			'preset' => [
				'type' => 'string',
				'required' => true,
				'default' => $settings['local']['user']['preset']
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
						'taxonomy_term' => \lqx\util\schema_str_req_emp,
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
							'range' => [-30, 30],
							'default' => 0
						],
						'end' => [
							'type' => 'integer',
							'range' => [-30, 30],
							'default' => 1
						]
					]
				]
			],
			'show_all' => \lqx\util\schema_str_req_n,
			'pagination' => \lqx\util\schema_str_req_y,
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
			'filters' => [
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
			'show_search' => [
				'type' => 'string',
				'required' => true,
				'default' => 'before',
				'allowed' => ['before', 'after', 'no']
			],
			'render_mode' => [
				'type' => 'string',
				'required' => true,
				'default' => 'php',
				'allowed' => ['cards', 'php', 'js']
			],
			'render_cards' => [
				'type' => 'object',
				'required' => true,
				'keys' => [
					'preset' => \lqx\util\schema_str_req_emp,
					'style' => \lqx\util\schema_str_req_emp,
					'heading' => \lqx\util\schema_str_req_emp,
					'subheading' => \lqx\util\schema_str_req_emp,
					'image' => \lqx\util\schema_str_req_emp,
					'icon_image' => \lqx\util\schema_str_req_emp,
					'video_url' => \lqx\util\schema_str_req_emp,
					'video_upload' => \lqx\util\schema_str_req_emp,
					'labels' => \lqx\util\schema_str_req_emp,
					'links' => \lqx\util\schema_str_req_emp
				]
			],
			'render_custom' => [
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
					]
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
 * @param  array $settings - filters settings
 * @param  int $post_id - post ID
 */
function init_settings($settings) {
	// Clean pre-filters settings
	foreach ($settings['pre_filters'] as $i => $pre_filter) {
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

		$settings['pre_filters'][$i] = $pre_filter;
	}

	// Clean up posts order settings
	foreach ($settings['posts_order'] as $i => $post_order) {
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

		$settings['posts_order'][$i] = $post_order;
	}

	// Clean up filters settings and get filters labels and other details
	foreach ($settings['filters'] as $i => $filter) {
		// Add empty `selected` key
		if (!array_key_exists('selected', $filter)) $filter['selected'] = '';

		// Get the label and other details
		switch ($filter['type']) {
			case 'taxonomy':
				// Set the label
				if (!isset($filter['label'])) $filter['label'] = get_taxonomy($filter['taxonomy'])->label;

				// Remove non-taxonomy fields
				unset($filter['acf_field']);
				break;

			case 'field':
				// Get the field settings
				$field = get_field_object($filter['acf_field'], null, true, false, false);

				// Set the label
				if (!isset($filter['label'])) $filter['label'] = $field['label'];

				// Get key field settings
				$filter['field_type'] = $field['type'];
				$filter['field_name'] = $field['name'];
				if (isset($field['choices'])) $filter['field_choices'] = $field['choices'];

				// Remove non-field fields
				unset($filter['taxonomy']);
				break;
		}

		// Create 'slug' string to use as name of filter in IDs, strings for active filters in hashes, etc
		$filter['slug'] = \lqx\util\slugify($filter['label']);

		// Convert custom order into an array of values
		$filter['custom_order'] = array_map(function ($value) {
			return $value['value'];
		}, $filter['custom_order']);

		$settings['filters'][$i] = $filter;
	}

	if ($settings['render_mode'] !== 'cards') {
		// Convert post_fields to an array of field objects
		$post_fields = [];

		foreach ($settings['post_fields'] as $field_obj) {
			$field_obj = get_field_object($field_obj['acf_field'], null, true, false, false);

			$post_fields[$field_obj['name']] = [
				'key' => $field_obj['key'],
				'name' => $field_obj['name'],
				'label' => $field_obj['label'],
				'type' => $field_obj['type'],
				'choices' => isset($field_obj['choices']) ? $field_obj['choices'] : []
			];
		}

		$settings['post_fields'] = $post_fields;
	}

	// Add an empty `search` key
	if (!array_key_exists('search', $settings)) $settings['search'] = '';

	// Set the page number
	if (!array_key_exists('page', $settings)) $settings['page'] = 1;

	// Set the pagination object
	if ($settings['show_all'] == 'y') {
		$pagination = [
			'show_all' => 'y'
		];
	} else {
		$pagination = [
			'show_all' => 'n',
			'pagination' => $settings['pagination'],
			'pagination_details' => $settings['pagination_details'],
			'show_posts_per_page' => $settings['show_posts_per_page']
		];

		if ($settings['show_posts_per_page'] == 'y') {
			$pagination['posts_per_page_options'] = $settings['posts_per_page_options'];
		} else {
			$pagination['posts_per_page'] = $settings['posts_per_page'];
		}
	}

	foreach ([
		'show_all',
		'pagination',
		'pagination_details',
		'show_posts_per_page',
		'posts_per_page',
		'posts_per_page_options'
	] as $k) {
		unset($settings[$k]);
	}

	$settings['pagination'] = $pagination;

	// Remove unused render mode data
	if ($settings['render_mode'] == 'cards') {
		unset($settings['render_custom']);
	} else {
		unset($settings['render_cards']);
	}

	return $settings;
}

/**
 * Get the options for each filter, from a given list of posts
 * @param  array $settings - filters settings
 * @param  array $posts - list of posts
 */
function get_options($settings) {
	global $wpdb;

	// If no posts were passed
	$posts = get_post_ids($settings);

	// Cycle through filters
	foreach ($settings['filters'] as $i => $filter) {
		// Get filter options
		$options = [];

		switch ($filter['type']) {
			case 'taxonomy':
				$terms = get_terms([
					'taxonomy' => $filter['taxonomy'],
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
						"WHERE `meta_key` = %s " . // TODO: are we handling sub-fields within groups and repeaters correctly?
						"AND `post_id` IN (" . implode(',', array_map('intval', $posts)) . ") " .
						"GROUP BY `meta_value`",
					$filter['acf_field']
				);

				// Execute the query
				$field_values = $wpdb->get_results($sql);

				if (is_array($field_values)) {
					if ($filter['field_type'] == 'relation') {
						foreach ($field_values as $field_value) {
							// Relation fields returns ids and so we need to get the slug and name for the posts
							$relation_values = unserialize($field_value->meta_value);
							if (is_array($relation_values)) {
								foreach ($relation_values as $relation_value) {
									if (!array_key_exists($relation_value, $options)) {
										$title = get_the_title($relation_value);
										$options[$relation_value] = [
											'value' => $relation_value,
											'slug' => get_post_field('post_name', $relation_value), // TODO should we get the actual post slug instead?
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
											'slug' => get_post_field('post_name', $choice_key), // TODO should we get the actual slug for the choice if available?
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
		$order = $filter['order'];

		switch ($filter['order_by']) {
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
					$index = array_search($option['text'], $filter['custom_order']);
					$new_options[$index] = $option;
				}

				// Sort new array, just adding the keys does not do this automatically so we need to do it here
				ksort($new_options);
				$options = $new_options;
				break;
		}

		// Add options to filter
		$filter['options'] = $options;

		// Save the filter
		$settings['filters'][$i] = $filter;
	}

	return $settings;
}

/**
 * Generate a WP Query object based on pre-filters, applied filters and passed arguments
 * @param  array $settings - filters settings
 * @param  array $query - query arguments
 */
function prepare_query($query, $settings) {
	// Pre-filters args
	$pre_filters = $settings['pre_filters'];

	foreach ($pre_filters as $pre_filter) {
		switch ($pre_filter['type']) {
			case 'taxonomy':
				$tax_query = [
					'taxonomy' => 'category',
					'field' => 'term_id',
					'terms' => $pre_filter['taxonomy_term'],
				];

				if (isset($query['tax_query'])) {
					$query['tax_query']['relation'] = 'AND';
					$query['tax_query'][] = $tax_query;
				} else {
					$query['tax_query'] = array();
					$query['tax_query'][] = $tax_query;
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

	// Implement filters args
	$filters = $settings['filters'];

	foreach ($filters as $filter) {
		// Ee only need to edit things if there is a value that has been set
		if ($filter['selected'] !== '' && $filter['selected'] !== false) {
			// Use different logic depending on the filter
			// CONSIDER: will we have pre-filtered values alongside selected values?
			// In theory it should be okay because tax and field queries are arrays.
			switch ($filter['type']) {
				case 'taxonomy':
					$tax_query = [
						'taxonomy' => 'category',
						'field' => 'term_id',
						'terms' => $filter['selected'],
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
						'key' => get_field_object($filter['acf_field'])['name'],
						'compare' => 'LIKE',
						'value' => $filter['selected']
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

	// We need to iterate through the filters to see what has been selected and use that to narrow our query
	return new \WP_Query($query);
}

/**
 * Get the list of post IDs
 * @param  array $settings - filters settings
 */
function get_post_ids($settings) {
	$query = [
		'post_type' => $settings['post_type'],
		'post_status' => 'publish',
		'posts_per_page' => -1,
		'fields' => 'ids'
	];

	$query = prepare_query($query, $settings);

	$posts = $query->posts;

	wp_reset_postdata();

	return $posts;
}

/**
 * Get a list of posts with the specified fields
 * @param  array $settings - filters settings
 * @param  int $page - page number
 */
function get_posts_with_data($settings) {
	$query = [
		'post_type' => $settings['post_type'],
		'post_status' => 'publish',
		'posts_per_page' => $settings['pagination']['posts_per_page'],
		'paged' => $settings['page'],
		'orderby' => []
	];

	// First things first, we need to iterate through post order to get each type and its priority.
	// orderby has to be formatted as orderby => [type => order] to work as an array.
	if (is_array($settings['posts_order'])) {
		foreach ($settings['posts_order'] as $order) {
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

	$query = prepare_query($query, $settings);
	$posts = [];

	if ($query->have_posts()) {
		while ($query->have_posts()) {
			$query->the_post();

			$post = get_post(get_the_ID());

			switch ($settings['render_mode']) {
				case 'custom_js':
				case 'custom_php':
					// Set the post object
					$post = [
						'id' => $post->ID,
						'author' => get_author_info($post->post_author, $settings['post_author']),
						'date' => $post->post_date_gmt,
						'content' => $settings['post_content'] == 'y' ? $post->post_content : '',
						'title' => $post->post_title,
						'excerpt' => $settings['post_excerpt'] == 'y' ? $post->post_excerpt : '',
						'slug' => $post->post_name,
						'modified' => $post->post_modified_gmt,
						'url' => $post->guid,
						'type' => $post->post_type,
						'thumbnail' => $settings['post_thumbnail'] == 'y' ? get_thumbnails_info($post->ID, $settings['thumbnail_sizes']) : [],
						'taxonomies' => [],
						'fields' => []
					];

					// Get taxonomies
					foreach ($settings['post_taxonomies'] as $taxonomy) {
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
					foreach ($settings['post_fields'] as $field_name => $field_obj) {
						$post['fields'][$field_name] = get_field($field_obj['key'], $post['id']);
					}

					break;

				case 'cards':
					// Set the post object
					$id = $post->ID;

					// If content doesn't pass the metrics used by the cards render, we'll need to iterate and populate the needed keys
					$video_url = get_field($settings['render_cards']['video_url'], $id);
					$video_upload = get_field($settings['render_cards']['video_upload'], $id);

					// Assemble links array
					$links = [];

					// Use the post URL as the first link
					if ($settings['render_cards']['use_post_url'] == 'y') $links[] = [
						'type' => 'text',
						'link' => [
							'url' => get_permalink($id),
							'title' => 'Read More',
							'target' => null
						]
					];

					// Links field
					$card_links = get_field($settings['render_cards']['links'], $id);
					if (!is_array($card_links)) $card_links = [];
					foreach ($card_links as $link) {
						$links[] = [
							'type' => 'text',
							'link' => [
								'url' => $link['link']['url'],
								'title' => $link['link']['title'],
								'target' => $link['link']['target']
							]
						];
					}

					//for body we do need to have some additional support to allow for excerpt
					//I don't think we should load main body content, this seems like it could be problematic but I can do that as well if need be?
					$body = null;
					switch ($settings['render_cards']['body']) {
						case 'excerpt':
							$body = get_the_excerpt($id);
							break;

						case 'content':
							$body = get_the_content($id);
							break;

						default:
							$body = get_field($settings['render_cards']['body'], $id);
							break;
					}

					// For labels, we need to get both the associated taxonomies and any other fields used to build this out.
					$labels = [];

					$label_taxes = $settings['render_cards']['label_taxonomies'] ?? [];

					foreach ($label_taxes as $tax) {
						$terms = get_the_terms($id, $tax->name);
						foreach ($terms as $term) {
							array_push($labels, ['label' => $term->name, 'value' => $term->slug]);
						}
					}

					$field_label = null;

					if (array_key_exists('label', $settings['render_cards'])) $field_label = get_field($settings['render_cards']['label'], $id);

					if (is_string($field_label)) {
						$labels[] = [
							'label' => $field_label,
							'value' => \lqx\util\slugify($field_label)
						];
					}

					$post = [
						'id' => $post->ID,
						'date' => $post->post_date_gmt,
						'heading' => $post->post_title,
						'subheading' => get_field($settings['render_cards']['subheading'], $id),
						'slug' => $post->post_name,
						'modified' => $post->post_modified_gmt,
						'url' => $post->guid,
						'body' => $body,
						'links' => $links,
						'labels' => $labels,
						'image' => get_field($settings['render_cards']['image'], $id),
						'icon_image' => get_field($settings['render_cards']['icon_image'], $id),
						'labels' => $labels,
						'video' => ['type' => ($video_url !== NULL && $video_url !== '' ? 'url' : ($video_upload !== NULL && $video_upload !== '' ? 'upload' : '')), 'url' => $video_url, 'upload' => $video_upload]
					];
					break;
			}

			$posts[] = $post;
		}
	}

	$total_pages = $query->max_num_pages;
	wp_reset_postdata();

	return ['posts' => $posts, 'total_pages' => $total_pages];
}

/**
 * Default HTML render for filters and search bar
 * @param  array $settings - filters settings
 */
function render_filters($settings) {
	// Iterate through each and output them according to their settings
	$filters = $settings['filters'];
	$html = '';

	if (count($filters)) $html .= '<div class="filters" id="' . $settings['hash'] . '-filters">';

	foreach ($filters as $filter) {
		if ($filter['visible'] == 'y') {
			$html .= '<div class="filter-wrapper" data-filter="' . $filter['slug'] . '" data-filter-type="' . $filter['type'] . '">';
			$options = $filter['options'];

			switch ($filter['presentation']) {
				case 'select':
					// Label for the field, for accessability purposes
					$html .= '<label for="' . $filter['label'] . '">' . $filter['label'] . '</label>';
					$html .= '<select name="' . $filter['label'] . '" id="' . $filter['label'] . '">';

					// Iterate through options
					foreach ($options as $option) {
						$html .= '<option value="' . $option['value'] . '"' . ($filter['selected'] == $option['value'] ? ' selected' : '') . '>' . $option['text'] . '</option>';
					}

					$html .= '</select>';

					break;

				case 'checkbox':
					// Label for the field, for accessability purposes
					foreach ($options as $option) {
						$html .= '<label for="' . $filter['label'] . '">' . $option['text'] . '</label>';
						$html .= '<input type="checkbox" id="' . $filter['slug'] . '_' . $option['value'] . '" name="radio_' . $filter['slug'] . '" value="' . $option['value'] . '" />';
					}

					break;

				case 'radio':
					// Label for the field, for accessability purposes
					foreach ($options as $option) {
						$html .= '<label for="' . $filter['slug'] . '_' . $option['value'] . '" id="label_' . $option['value'] . '">' . $option['text'] . '</label>';
						$html .= '<input type="radio" id="' . $filter['slug'] . '_' . $option['value'] . '" name="radio_' . $filter['slug'] . '" value="' . $option['value'] . '" />';
					}

					break;

				case 'list':
					// Label for the field, for accessability purposes
					$html .= '<ul class="filter-list" id="' . $filter['slug'] . '" role="combobox">';

					foreach ($options as $option) {
						$html .= '<li class="filter' . ($filter['selected'] == $option['value'] ? ' class' : '') . '" data-value="' . $option['value'] . '">' . $option['text'] . '</li>';
					}

					$html .= '</ul>';

					break;
			}

			$html .= '</div>';
		}
	}

	if (count($filters)) $html .= '</div>';

	return $html;
}

/**
 * Default HTML render for posts using the Cards block
 * @param  array $settings - settings and posts data
 */
function render_posts($settings) {
	require_once \lqx\blocks\get_renderer('cards', $settings['render_cards']['preset']);

	// For settings we need to get the preset settings from cards.
	$cards_settings = \lqx\blocks\get_settings('cards', null, $settings['render_cards']['preset'], $settings['render_cards']['style']);

	// Change the hash to use the same as the filters
	$cards_settings['processed']['hash'] = $settings['hash'] . '-posts';

	return \lqx\blocks\cards\render($cards_settings, $settings['posts']);
}

/**
 * Default HTML render for the pagination, pagination details and posts per page selector
 * @param  array $settings - pagination settings
 */
function render_pagination($settings) {
	if ($settings['total_pages'] > 1) {
		$html = '<div class="pagination" id="' . $settings['hash'] . '-pagination">';

		$html .= '<ul class="pageslinks">';

		$html .= '<li class="page first" aria-label="First Page">First</li>';
		$html .= '<li class="page prev" aria-label="Previous Page">Prev</li>';

		$i = 1;
		while ($i <= $settings['total_pages']) {
			$html .= '<li data-page="' . $i . '" aria-label="Page ' . $i . '">' . $i . '</li>';
			$i++;
		}

		$html .= '<li class="page next" aria-label="Previous Page">Next</li>';
		$html .= '<li class="page last" aria-label="Last Page">Last</li>';

		$html .= '</ul>';

		$html .= '</div>';

		return $html;
	}
}

/**
 * Prepare the data for JSON
 * @param  array $settings - filters settings
 */
function prepare_json_data($s) {
	// TODO: make sure we have the information we need both on the initial render and on API calls
	$res = [
		'preset_name' => $s['preset_name'],
		'post_id' => $s['post_id'],
		'filters' => $s['render_mode'] == 'custom_js' ? $s['filters'] : \lqx\blocks\filters\render_filters($s),
		'posts' => $s['render_mode'] == 'custom_js' ? $s['posts'] : \lqx\blocks\filters\render_posts($s),
		'pagination' => $s['render_mode'] == 'custom_js' ? $s['pagination'] : \lqx\blocks\filters\render_pagination($s)
	];

	if ($s['render_mode'] == 'js') $res['show_search'] = $s['show_search'];

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
			'preset_name' => \lqx\util\schema_str_req_notemp,
			'post_id' => \lqx\util\schema_int_req,
			'filters' => [
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
 * @param  array $settings - filters settings
 * @param  array $payload - received payload
 */
function merge_settings($settings, $payload) {
	// TODO merge the payload with the settings
	// TODO use \lqx\blocks\merge_settings as a model
	// TODO the filters array need special handling
}

/**
 * Handle the API calls
 * @param  array $payload - received payload
 */
function handle_api_call($request) {
	// Get the payload
	$payload = $request->get_json_params();

	// Remove any keys that are not allowed
	foreach (array_keys($payload) as $k) {
		if (!in_array($k, ['preset_name', 'post_id', 'filters', 'search', 'page', 'pagination'])) unset($payload[$k]);

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

	// Get settings
	$s = \lqx\blocks\get_settings('filters', $p['post_id']);

	// Validate settings
	$s = \lqx\filters\validate_settings($s);

	// Initialize settings
	$s = \lqx\filters\init_settings($s);

	// Merge payload with settings
	$s = \lqx\filters\merge_settings($s, $p);

	// Get options
	$s = \lqx\filters\get_options($s);

	// Get the posts
	$post_info = \lqx\filters\get_posts_with_data($s);
	$s['posts'] = $post_info['posts'];
	$s['total_pages'] =  $post_info['total_pages'];

	// Load render functions from filters block
	if (file_exists(get_stylesheet_directory() . '/php/custom/blocks/filters/render.php')) {
		require_once get_stylesheet_directory() . '/php/custom/blocks/filters/render.php';
	} else {
		require_once get_stylesheet_directory() . '/php/blocks/filters/render.php';
	}

	// Prepare the JSON data
	return \lqx\filters\prepare_json_data($s);
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

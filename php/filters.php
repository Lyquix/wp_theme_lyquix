<?php

/**
 * filters.php - Utility functions and REST API for filters
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

namespace lqx\filters;

// Populate the Cards block styles and presets
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
        ],
        //Map
        [ // style and style_name fields
            'user' => 'field_67efef082dff9',
            'choice' => 'field_6697e27cc4d4b'
        ],
        [ // preset and preset_name fields
            'user' => 'field_67efeef72dff8',
            'choice' => 'field_6697e331c4d4f'
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
 *
 * @param array $field_details - ACF field details
 * @param array $choices - array to store the options
 * @param int $depth - depth of the field
 *
 * @return void
 */
function get_acf_fields_as_options($field_details, &$choices, $depth = 0)
{
    $key = $field_details['key'];
    $choices[$key] = str_repeat('- ', $depth) . ($field_details['label'] ? $field_details['label'] : $field_details['name']) . ' [' . $field_details['key'] . ']';

    if ($field_details['sub_fields'] ?? false) {
        foreach ($field_details['sub_fields'] as $sub_field_details) {
            get_acf_fields_as_options($sub_field_details, $choices, $depth + 1);
        }
    }
}

// Populate the Field Name fields in the Filters settings
add_filter('acf/load_field', function ($field) {
    $field_keys = [
        'field_65f1ea274754b' => null, // pref_filters > acf_field
        'field_6707cced1dfc9' => null, // pref_filters > acf_field (custom field value)
        'field_65f1ebb9ef068' => null, // filters > acf_field
        'field_65f248687356f' => null, // posts_order > acf_field
        'field_67f949359f162' => null, // change_order_options > acf_field
        'field_65f3010821d84' => null, // render_js > post_fields > acf_field
        'field_65f471bf7d99b' => 'text', // render_php > heading
        'field_65f475117fcd5' => 'text', // render_php > subheading
        'field_65f4752a7fcd6' => 'image', // render_php > image
        'field_65f4752f7fcd7' => 'image', // render_php > icon_image
        'field_65f475367fcd8' => 'link', // render_php > video_url
        'field_65f4753d7fcd9' => 'file', // render_php > video_upload
        'field_65f475457fcda' => 'text', // render_php > body
        'field_65f4754e7fcdb' => 'text', // render_php > labels
        'field_66393791fb81d' => 'link',  // render_php > url
        'field_67f959b5ada75' => 'relation', // controls > locations_field
        'field_67f959d6ada76' => 'map', // controls > address_field
        'field_67ffc50abf4de' => 'relation', // post order > locations_field
        'field_67ffc530bf4df' => 'map', // post order > address_field
        'field_67ffc53fbf4e0' => 'relation', // change order options > locations_field
        'field_67ffc561bf4e1' => 'map', // change order options > address_field
        'field_6813b24814413' => 'text', // pre_filters > region field
        'field_6813b2c514414' => 'text' // pre_filters > region field
    ];

    $field_types = [
        'text' => [
            'text', 'textarea', 'number', 'range', 'email', 'url', 'password', 'wysiwyg',
            'select', 'checkbox', 'radio', 'button_group', 'date_picker', 'date_time_picker',
            'time_picker', 'color_picker', 'acfe_phone_number'
        ],
        'image' => ['image'],
        'file' => ['file'],
        'link' => ['link'],
        'relation' => ['relationship'],
        'map' => ['google_map'],
    ];

    if (!array_key_exists($field['key'], $field_keys)) return $field;

    // Get all field groups
    $field_groups = acf_get_field_groups();

    // Filter out certain field groups
    $field_groups = array_filter($field_groups, function ($group) {
        if (str_contains($group['title'], 'Custom Post Type: ') || $group['title'] == 'Posts') return true;
    });

    if ($field['key'] == 'field_6707cced1dfc9') {
        $field['choices']['current'] = 'Current Post';
    }

    if ($field['key'] == 'field_65f1ea274754b') {
        $field['choices']['id'] = 'ID (dynamic type only)';
        $field['choices']['parent'] = 'Parent (dynamic type only)';
        $field['choices']['author'] = 'Author ID (dynamic type only)';
        $field['choices']['venue'] = 'Venue (dynamic type only)';
    }

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
 *
 * @param int $author_id - author ID
 * @param array $fields - fields to return
 *
 * @return array - author info
 */
function get_author_info($author_id, $fields = ['name', 'avatar'])
{
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
 *
 * @param int $post_id - post ID
 * @param array $sizes - thumbnail sizes
 *
 * @return array - thumbnails info
 */
function get_thumbnails_info($post_id, $sizes = ['Large'])
{
    $thumbnails = [];

    foreach ($sizes as $size) {
        $thumbnails[$size] = get_the_post_thumbnail_url($post_id, $size);
    }

    return $thumbnails;
}

/**
 * Perform the complete settings processing and get the posts with data
 *
 * @param array $settings - settings data
 *
 * @return array - processed settings data with posts and total pages
 */
function get_settings_and_posts($settings)
{
    // Validate settings / get processed settings
    $s = validate_settings($settings);

    // Initialize settings
    $s = init_settings($s);

    // Get options
    $s = get_options($s);

    $s = apply_filters('lyquix_filters_option_map', $s);

    // Get the posts
    $post_info = get_posts_with_data($s);
    $s['posts'] = $post_info['posts'];
    $s['pagination']['total_posts'] = $post_info['total_posts'];
    $s['pagination']['total_pages'] = $post_info['total_pages'];

    return $s;
}

/**
 * Validate the settings data and return the processed settings
 *
 * @param array $settings - settings data
 *
 * @return array - processed settings data
 */
function validate_settings($settings)
{
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
                            'allowed' => ['author', 'date', 'field', 'parentless', 'post_parent', 'taxonomy', 'venue', 'dynamic', 'meta_key', 'region', 'manual']
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
                            'default' => 0
                        ],
                        'manual' => [
                            'type' => 'array',
                            'required' => false,
                            'default' => [],
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
            'show_controls_on_no_results' => \lqx\util\schema_str_req_n,
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
                            'allowed' => ['taxonomy', 'field', 'distance', 'region']
                        ],
                        'taxonomy' => \lqx\util\schema_str_req_emp,
                        'acf_field' => \lqx\util\schema_str_req_emp,
                        'alias' => \lqx\util\schema_str_req_emp,
                        'presentation' => [
                            'type' => 'string',
                            'required' => true,
                            'default' => 'select',
                            'allowed' => ['select', 'checkbox', 'radio', 'list', 'distance', 'region']
                        ],
                        'order_by' => [
                            'type' => 'string',
                            'required' => true,
                            'default' => 'alpha',
                            'allowed' => ['alpha', 'count', 'custom', 'none']
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
                        ],
                        'narrow_options' => \lqx\util\schema_str_req_y,
                        'show_view_all' => \lqx\util\schema_str_req_y,
                        'view_all_label' => [
                            'type' => 'string',
                            'default' => 'View All'
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
                            'allowed' => ['date', 'title', 'name', 'author', 'rand', 'field', 'modified', 'meta_key', 'distance']
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
                'allowed' => ['php', 'js', 'maps-php', 'maps-js']
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
                    'label_fields' => [
                        'type' => 'array',
                        'keys' => [
                            'label_field' => \lqx\util\schema_str_req_emp,
                        ]
                    ],
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
            'change_order' => \lqx\util\schema_str_req_y,
            'show_no_results_message' => \lqx\util\schema_str_req_y,
            'no_results_message' => \lqx\util\schema_str_req_emp,
            'show_heading' => \lqx\util\schema_str_req_y,
            'default_heading' => \lqx\util\schema_str,
            'heading_style' => [
                'type' => 'string',
                'required' => true,
                'default' => 'p',
                'allowed' => ['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6']
            ],
            'heading_override' => \lqx\util\schema_str_req_emp,
            'callback' => \lqx\util\schema_str_req_emp
        ]
    ]);

    // If valid settings, use them, otherwise throw exception
    if ($s['isValid']) return $s['data'];
    if (\lqx\util\is_local_environment()) throw new \Exception('Invalid block settings: ' . var_export($s, true));
    return;
}

/**
 * Get settings for the filters block
 *
 * @param array $s - processed settings
 * @param int $post_id - post ID
 *
 * @return array - settings for the filters block
 */
function init_settings($s)
{
    // Clean pre-filters settings
    foreach ($s['pre_filters'] as $i => $pre_filter) {
        switch ($pre_filter['type']) {
            case 'date':
                foreach ([
                             'operator_simple',
                             'operator_advanced',
                             'taxonomy_term',
                             'post_parent',
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
                             'post_parent',
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
            case 'venue':
            case 'dynamic':
            case 'manual':
            case 'meta_key':
                foreach ([
                             'operator_simple',
                             'date_source',
                             'taxonomy_term',
                             'post_parent',
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
                             'post_parent',
                             'acf_field',
                             'anchor',
                             'unit',
                             'start',
                             'end'
                         ] as $k) {
                    unset($pre_filter[$k]);
                }

                break;

            case 'parentless':
                foreach ([
                             'operator_advanced',
                             'date_source',
                             'taxonomy_term',
                             'post_parent',
                             'acf_field',
                             'anchor',
                             'unit',
                             'start',
                             'end'
                         ] as $k) {
                    unset($pre_filter[$k]);
                }
                break;

            case 'post_parent':
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

            case 'region':
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
            'rand',
            'meta_key',
            'distance'
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

            case 'distance':
                foreach (explode(',', $control['miles_options']) as $option) {
                    // Trim the option
                    $option = trim($option);
                    if (!isset($control['label'])) $control['label'] = 'Distance';
                    // The default option contains an asterisk
                    if (strpos($option, '*') !== false) {
                        $option = str_replace('*', '', $option);
                        if (is_numeric($option)) {
                            $option = intval($option);
                            $control['options'][] = ['value' => $option, 'text' => $option . ' miles'];
                            $control['selected'] = $option;
                        }
                    } else if (is_numeric($option)) {
                        $control['options'][] = ['value' => $option, 'text' => $option . ' miles'];
                    }
                }
                break;

            case 'region':
                $control['label'] = 'Region';
                $control['options'][] = ['value' => 'this-region', 'text' => 'This Region'];
                $region = \lqx\regions\get_region();
                if ($region !== 'outside-region') {
                    $control['selected'] = 'this-region';
                }
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

    if ($s['render_mode'] == 'maps-js' || $s['render_mode'] == 'cards-js') {
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
                } else if (is_numeric($option)) {
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
        case 'maps-php':
        case 'php':
            unset($s['render_js']);
            break;

        case 'maps-js':
        case 'js':
            unset($s['render_php']);
            break;
    }

    return $s;
}

/**
 * Get the options for each control, from a given list of posts
 *        - Taxonomy controls will get the terms for the given taxonomy
 *        - Field controls will get the values for the given ACF field
 *
 * @param array $s - controls settings
 *        - controls: array of control settings
 *        - posts: array of post IDs
 *
 * @return array - controls settings with options
 */
function get_options($s)
{
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
                if ($control['narrow_options'] == 'y') {
                    $terms = get_terms([
                        'taxonomy' => $control['taxonomy'],
                        'object_ids' => $posts
                    ]);
                } else {
                    $terms = get_terms([
                        'taxonomy' => $control['taxonomy'],
                    ]);
                }

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
                $field = get_field_object($control['acf_field'], null, true, false, false);
                $id_statement = ($control['narrow_options'] == 'y' ? "AND `post_id` IN (" . implode(',', array_map('intval', $posts)) . ") " : "");

                // Check if this is a sub-field within a group or repeater
                // ACF sub-fields have a parent that starts with 'field_' (parent field key),
                // while top-level fields have a parent that is a field group post ID
                $is_sub_field = isset($field['parent']) && strpos($field['parent'], 'field_') === 0;

                if ($is_sub_field) {
                    // Sub-fields in repeaters are stored as parentname_0_fieldname, parentname_1_fieldname, etc.
                    // Sub-fields in groups are stored as parentname_fieldname
                    // Use LIKE with a wildcard prefix to match all variations
                    // Exclude keys starting with '_' to avoid ACF's internal reference entries
                    // (e.g., _authors_0_related_staff which stores the field key, not the value)
                    $escaped_name = $wpdb->esc_like('_' . $field['name']);
                    $like_key = '%' . $escaped_name;
                    $not_like_key = $wpdb->esc_like('_') . '%' . $escaped_name;
                    $sql = $wpdb->prepare(
                        "SELECT `meta_value`, COUNT(`post_id`) as `count`
						FROM {$wpdb->postmeta}
						WHERE `meta_key` LIKE %s
						AND `meta_key` NOT LIKE %s
						AND `meta_value` != ''
						$id_statement
						GROUP BY `meta_value`",
                        [$like_key, $not_like_key]
                    );
                } else {
                    $sql = $wpdb->prepare(
                        "SELECT `meta_value`, COUNT(`post_id`) as `count`
						FROM {$wpdb->postmeta}
						WHERE `meta_key` = %s
						AND `meta_value` != ''
						$id_statement
						GROUP BY `meta_value`",
                        [$field['name']]
                    );
                }
                // Execute the query
                $field_values = $wpdb->get_results($sql);

                if (is_array($field_values)) {
                    if ($control['field_type'] == 'relationship') {
                        foreach ($field_values as $field_value) {
                            // Relation fields return IDs - top-level fields store a serialized array,
                            // while sub-fields in repeaters store plain post IDs per row
                            $relation_values = @unserialize($field_value->meta_value);
                            if (!is_array($relation_values)) {
                                // Sub-field in a repeater: meta_value is a plain post ID
                                $relation_values = [$field_value->meta_value];
                            }
                            foreach ($relation_values as $relation_value) {
                                // Skip empty values
                                if (empty($relation_value)) continue;

                                if (!array_key_exists($relation_value, $options)) {
                                    $title = get_the_title($relation_value);
                                    if (empty($title)) continue;
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
                                $array = unserialize($field_value->meta_value);

                                if ($array) {
                                    foreach ($array as $value) {
                                        if (!array_key_exists($value, $options)) {
                                            $options[$value] = [
                                                'value' => $value,
                                                'slug' => \lqx\util\slugify($value),
                                                'text' => $value,
                                                'disabled' => false,
                                                'selected' => false,
                                                'count' => $field_value->count
                                            ];
                                        }
                                    }
                                } else {
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
                }
                break;
            case 'distance':
            case 'region':
                $options = $s['controls'][$i]['options'];
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

            case 'none';
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
 *        - Pre-filters are applied before the user interacts with the filters
 *        - Applied filters are applied after the user interacts with the filters
 *
 * @param array $s - processed settings
 *        - post_type: post type
 *        - pre_filters: array of pre-filter settings
 *        - controls: array of control settings
 *        - pagination: pagination settings
 *        - posts_order: array of post order settings
 *        - posts_per_page: number of posts per page
 *        - search: search string
 *        - controls: array of control settings
 * @param array $query - query arguments
 *
 * @return WP_Query - WP Query object
 */
function prepare_query($query, $s)
{
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
                        'operator' => ($pre_filter['operator_simple'] == '!=' ? 'NOT IN' : 'IN')
                    ];
                    // add the relevant query
                    if (isset($query['tax_query'])) {
                        $query['tax_query']['relation'] = 'AND';
                        $query['tax_query'][] = $tax_query;
                    } else {
                        $query['tax_query'] = [];
                        $query['tax_query'][] = $tax_query;
                    }
                }
                break;

            case 'field':
                $acf_meta_query = [
                    'key' => get_field_object($pre_filter['acf_field'])['name'],
                    'compare' => $pre_filter['operator_advanced'],
                    'value' => $pre_filter['value']
                ];

                if (isset($query['meta_query'])) {
                    $query['meta_query']['relation'] = 'AND';
                    $query['meta_query'][] = $acf_meta_query;
                } else {
                    $query['meta_query'] = [];
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
                        $after = date('Y-m-d', strtotime($anchor . ' -' . $pre_filter['start'] ?? '0' . ' years'));
                        break;
                }

                switch ($pre_filter['date_source']) {
                    case 'field':
                        $acf_meta_query = [
                            'relation' => 'AND',
                            [
                                'key' => get_field_object($pre_filter['acf_field'])['name'],
                                'value' => $after,
                                'compare' => '>=',
                                'type' => 'DATE'
                            ],
                            [
                                'key' => get_field_object($pre_filter['acf_field'])['name'],
                                'value' => $before,
                                'compare' => '<=',
                                'type' => 'DATE'
                            ]
                        ];

                        if (isset($query['meta_query'])) {
                            $query['meta_query']['relation'] = 'AND';
                            $query['meta_query'][] = $acf_meta_query;
                        } else {
                            $query['meta_query'] = [];
                            $query['meta_query'][] = $acf_meta_query;
                        }
                        break;
                    case 'meta_key':
                        $acf_meta_query = [
                            'relation' => 'AND',
                            [
                                'key' => $pre_filter['meta_key'],
                                'value' => $after,
                                'compare' => '>=',
                                'type' => 'DATE'
                            ],
                            [
                                'key' => $pre_filter['meta_key'],
                                'value' => $before,
                                'compare' => '<=',
                                'type' => 'DATE'
                            ]
                        ];

                        if (isset($query['meta_query'])) {
                            $query['meta_query']['relation'] = 'AND';
                            $query['meta_query'][] = $acf_meta_query;
                        } else {
                            $query['meta_query'] = [];
                            $query['meta_query'][] = $acf_meta_query;
                        }
                        break;
                    default:
                        $date_query = [[
                            'before' => $before,
                            'after' => $after,
                            // Inclusive scopes in the current date when dealing with the before/after system
                            'inclusive' => true,
                        ]];
                        $query['date_query'] = $date_query;
                        break;
                }
                break;

            case 'author':
                $query['author'] = $pre_filter['value'];
                break;

            case 'parentless':
                $query['post_parent'] = 0;
                break;

            case 'post_parent' :
                $query['post_parent__in'] = $pre_filter['post_parent'];
                break;

            case 'venue' :
                $acf_meta_query = [
                    'key' => '_EventVenueID',
                    'compare' => $pre_filter['operator_advanced'],
                    'value' => $pre_filter['value']
                ];

                if (isset($query['meta_query'])) {
                    $query['meta_query']['relation'] = 'AND';
                    $query['meta_query'][] = $acf_meta_query;
                } else {
                    $query['meta_query'] = [];
                    $query['meta_query'][] = $acf_meta_query;
                }
                break;

            case 'dynamic' :
                $map = [
                    'venue' => '_EventVenueID'
                ];

                $value = $pre_filter['value_field'] == 'current' ? $s['post_id'] : get_field($pre_filter['value_field'], $s['post_id']);

                if ($key = get_field_object($pre_filter['acf_field'])['name'] ?? $map[$pre_filter['acf_field']] ?? null) {
                    $acf_meta_query = [
                        'key' => $key,
                        'compare' => $pre_filter['operator_advanced'],
                        'value' => $value
                    ];

                    if (isset($query['meta_query'])) {
                        $query['meta_query']['relation'] = 'AND';
                        $query['meta_query'][] = $acf_meta_query;
                    } else {
                        $query['meta_query'] = [];
                        $query['meta_query'][] = $acf_meta_query;
                    }
                }

                if ($pre_filter['acf_field'] == 'parent') {
                    $query['post_parent'] = $value;
                }

                if ($pre_filter['acf_field'] == 'author') {
                    $query['author'] = $value;
                }

                if ($pre_filter['acf_field'] == 'id') {
                    $ids = get_field($pre_filter['value_field'], $s['post_id']);
                    $query['post__in'] = $ids;
                }

                break;

            case 'meta_key' :
                $acf_meta_query = [
                    'key' => $pre_filter['meta_key'],
                    'compare' => $pre_filter['operator_advanced'],
                    'value' => $pre_filter['value']
                ];

                if (isset($query['meta_query'])) {
                    $query['meta_query']['relation'] = 'AND';
                    $query['meta_query'][] = $acf_meta_query;
                } else {
                    $query['meta_query'] = [];
                    $query['meta_query'][] = $acf_meta_query;
                }
                break;

            case 'region' :
                if (isset($_COOKIE['selectedRegion']) == true) {
                    $region = \lqx\regions\get_region();
                    // we should probably do a mysql query to get all posts within the related region
                    // this is a repeater field that can have multiple values, and thus we need to check each one unless we want to rework this system
                    global $wpdb;

                    $region_query = 'SELECT p.ID
						FROM gqhmnv_posts as p, gqhmnv_postmeta as pm
						WHERE p.post_type = \'' . $s['post_type'] . '\'
							AND p.post_status = \'publish\'
							AND p.ID = pm.post_id
							AND pm.meta_key = \'related_regions\'
							AND pm.meta_value LIKE \'%\"' . $region . '"%\'';
                    $sql = $wpdb->remove_placeholder_escape($wpdb->prepare($region_query, $region));
                    $items = $wpdb->get_results($sql);

                    $final_list = [];

                    foreach ($items as $item) {
                        $final_list[] = $item->ID;
                    }

                    if (!empty($final_list)) {
                        $query['post__in'] = $final_list;
                    } else {
                        $query['post__in'] = [0];
                    }
                }
                break;
            case 'manual':
                if (isset($pre_filter['manual'])) {
                    $query['post__in'] = $pre_filter['manual'];
                }
                break;
            default:
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
                        $query['tax_query'] = [];
                        $query['tax_query'][] = $tax_query;
                    }
                    break;
                case 'field':
                    $field = get_field_object($control['acf_field'], null, true, false, false);
                    $is_sub_field = isset($field['parent']) && str_starts_with($field['parent'], 'field_');

                    $acf_meta_query = [
                        'value' => $control['selected'],
                        'compare' => 'LIKE',
                    ];

                    if ($is_sub_field) {
                        // Sub-fields in repeaters/groups: match meta keys like parentname_0_fieldname
                        // WP_Query wraps the key in %...% and escapes with esc_like() when compare_key is LIKE
                        $acf_meta_query['key'] = '_' . $field['name'];
                        $acf_meta_query['compare_key'] = 'LIKE';
                    } else {
                        $acf_meta_query['key'] = $field['name'];
                    }

                    if (isset($query['meta_query'])) {
                        $query['meta_query']['relation'] = 'AND';
                        $query['meta_query'][] = $acf_meta_query;
                    } else {
                        $query['meta_query'] = [];
                        $query['meta_query'][] = $acf_meta_query;
                    }
                    break;

                case 'distance':
                    //step 1: get the fields for location and address to use as the comparison.
                    //without lat or lon, break it.
                    if (!isset($control['lat']) || !isset($control['lng'])) {
                        break;
                    }
                    //step 2: we need to determine the source of our address field (direct or from a related item) and begin building a query
                    switch ($control['address_mode']) {
                        //for relationship we need to utilize a relation field and then get the lat/lng from the related address field
                        case 'relation':
                            //using the related_locations field we need to get the other end for future operations
                            //this is unique to the relationship mode
                            $related_locations = get_field_object($control['locations_field']);
                            $target_field = $related_locations['acfe_bidirectional']['acfe_bidirectional_related'][0];
                            $target_field_name = get_field_object($target_field)['name'];

                            $lat = $control['lat'];
                            $lng = $control['lng'];

                            $distance = intval($control['miles']);
                            //initialize arrays to store data for building comparison
                            //locations is for the query. we will add and remove data as needed. address is for narrowing ids.
                            $distance = $distance * 1609.34;
                            global $wpdb;
                            $loc_query = 'SELECT p.ID,
										lat_meta.meta_value AS latitude,
										lng_meta.meta_value AS longitude,
										ST_Distance_Sphere(
											POINT(lng_meta.meta_value, lat_meta.meta_value),
											POINT(%f, %f)
										) AS distance_m
							FROM gqhmnv_posts p
							JOIN gqhmnv_postmeta lat_meta ON p.ID = lat_meta.post_id AND lat_meta.meta_key = \'latitude\'
							JOIN gqhmnv_postmeta lng_meta ON p.ID = lng_meta.post_id AND lng_meta.meta_key = \'longitude\'
							WHERE p.post_type = \'locations\'
								AND p.post_status = \'publish\'
								HAVING distance_m < %f
							ORDER BY distance_m ASC
							LIMIT 20;';

                            $sql = $wpdb->prepare($loc_query, $lng, $lat, $distance);
                            $locations = $wpdb->get_results($sql);

                            $final_list = [];
                            //get the related posts for each location to create a new post__in to scope and order the final query
                            foreach ($locations as $location) {
                                $items = get_field($target_field_name, $location->ID);
                                foreach ($items as $item) {
                                    $final_list[] = $item;
                                }
                            }

                            //final step: if a related location within the distance exists, render
                            //if we don't have any relations, we need to kill the items list
                            if (empty($final_list)) {
                                $query['post__in'] = [0];
                                //otherwise, we add a query parameter
                            } else {
                                $query['post__in'] = $final_list;
                            }
                            break;
                        case 'direct':
                            //for address we need to check the addresses assigned to the post type and go from there
                            //$address_field = $control['address_field'];
                            //attempting to use the sql method outlined by Ruben here:
                            global $wpdb;
                            $distance = intval($control['miles']);
                            $distance = $distance * 1609.34;

                            $loc_query = 'SELECT p.ID,
										lat_meta.meta_value AS latitude,
										lng_meta.meta_value AS longitude,
										ST_Distance_Sphere(
											POINT(lng_meta.meta_value, lat_meta.meta_value),
											POINT(%f, %f)
										) AS distance_m
							FROM gqhmnv_posts p
							JOIN gqhmnv_postmeta lat_meta ON p.ID = lat_meta.post_id AND lat_meta.meta_key = \'latitude\'
							JOIN gqhmnv_postmeta lng_meta ON p.ID = lng_meta.post_id AND lng_meta.meta_key = \'longitude\'
							WHERE p.post_type = \'locations\'
								AND p.post_status = \'publish\'
								HAVING distance_m < %f
							ORDER BY distance_m ASC;';

                            $sql = $wpdb->prepare($loc_query, $control['lng'], $control['lat'], $distance);
                            $locations = $wpdb->get_results($sql);

                            $final_list = [];
                            foreach ($locations as $location) {
                                $final_list[] = $location->ID;
                            }

                            if (!empty($final_list)) {
                                $query['post__in'] = $final_list;
                            } else {
                                $query['post__in'] = [0];
                            }
                            break;
                    }
                    break;
                case 'region':
                    //var_dump($control);
                    if ($control['selected'] !== '') {
                        $region = \lqx\regions\get_region();
                        //var_dump($region);
                        // we should probably do a mysql query to get all posts within the related region
                        // this is a repeater field that can have multiple values, and thus we need to check each one unless we want to rework this system
                        /*global $wpdb;

                        $region_query = 'SELECT p.ID
                            FROM gqhmnv_posts as p, gqhmnv_postmeta as pm
                            WHERE p.post_type = \'physicians\'
                                AND p.post_status = \'publish\'
                                AND p.ID = pm.post_id
                                AND pm.meta_key LIKE \'related_regions_%_region\'
                                AND pm.meta_value = %s';

                        $sql = $wpdb -> remove_placeholder_escape($wpdb -> prepare($region_query, $region->title));

                        $items = $wpdb -> get_results($sql);

                        $final_list = [];

                        foreach($items as $item) {
                            $final_list[] = $item->ID;
                        }

                        if (!empty($final_list)) {
                            $query['post__in'] = $final_list;
                        } else {
                            $query['post__in'] = [0];
                        }*/
                        $acf_meta_query = [
                            'key' => 'related_regions',
                            'compare' => 'LIKE',
                            'value' => $region
                        ];

                        if (isset($query['meta_query'])) {
                            $query['meta_query']['relation'] = 'AND';
                            $query['meta_query'][] = $acf_meta_query;
                        } else {
                            $query['meta_query'] = [];
                            $query['meta_query'][] = $acf_meta_query;
                        }
                        break;
                    }
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
 * Prepares, translates, and executes a search query against the Typesense API.
 * This function is the central point of communication with Typesense.
 *
 * @param array $s The fully merged settings array from the API call.
 * @return array The raw search results from Typesense, or an array containing an error message.
 */

function prepare_typesense_query($s)
{
    $api_instance = \Codemanas\Typesense\Main\TypesenseAPI::getInstance();
    $collection_name = $s['post_type'] ?? 'posts';

    $ts_args = [
        's' => $s['search'] ?? '',
        'filters' => [],
        'query_by_fields' => [],
        'facets' => [],
        'sort' => '',
        'geofilter' => [],
        'posts_per_page' => ($s['pagination']['show_all'] ?? 'n') == 'n' ? ($s['pagination']['posts_per_page'] ?? 10) : 250,
        'paged' => ($s['pagination']['show_all'] ?? 'n') == 'n' ? ($s['pagination']['page'] ?? 1) : 1,
    ];

    $sort_by_parts = [];
    if (is_array($s['posts_order'])) {
        foreach ($s['posts_order'] as $order) {
            $direction = $order['order'] ?? 'desc';
            switch ($order['order_by']) {
                case 'rand':
                    $sort_by_parts = ['_eval(random()):desc'];
                    break 2;
                case 'distance':
                    continue 2;
                case 'field':
                    $sort_by_parts[] = get_field_object($order['acf_field'])['name'] . ":{$direction}";
                    break;
                case 'meta_key':
                    $sort_by_parts[] = "{$order['value']}:{$direction}";
                    break;
                case 'date':
                    $sort_by_parts[] = "sort_by_date:{$direction}";
                    break;
                case 'title':
                    $sort_by_parts[] = "post_title:{$direction}";
                    break;
            }
        }
    }
    if (!empty($sort_by_parts)) {
        $ts_args['sort'] = implode(',', $sort_by_parts);
    }

    $base_query_by_fields = ['post_title', 'post_content', 'post_excerpt'];
    $additional_query_by_fields = [];
    $all_filters = array_merge($s['pre_filters'] ?? [], $s['controls'] ?? []);

    foreach ($all_filters as $filter) {
        if (isset($filter['selected']) && ($filter['selected'] === '' || $filter['selected'] === false) && $filter['type'] !== 'distance') {
            continue;
        }

        switch ($filter['type']) {
            case 'taxonomy':
                $ts_args['filters'][] = ['field' => ($filter['taxonomy'] ?? $filter['taxonomy_term']->taxonomy) . '_id', 'compare' => $filter['operator_simple'] ?? 'IN', 'value' => $filter['selected'] ?? $filter['taxonomy_term']->term_id];
                $ts_args['facets'][] = ($filter['taxonomy'] ?? $filter['taxonomy_term']->taxonomy) . '_slug';
                break;

            case 'field':
            case 'meta_key':
                $key = get_field_object($filter['acf_field'])['name'] ?? $filter['meta_key'];
                $compare = $filter['operator_advanced'] ?? 'IN';
                $value = $filter['value'] ?? $filter['selected'];
                if ($field_object = get_field_object($filter['acf_field'])) {
                    if ($field_object['type'] == 'relationship' && $field_object['return_format'] == 'id') {
                        $value = get_the_title($filter['selected']);
                    }
                } else {
                    $value = $filter['value'] ?? $filter['selected'];
                }

                if ($compare === 'LIKE') {
                    $ts_args['s'] .= ' ' . $value;
                    $additional_query_by_fields[] = $key;
                } else {
                    $ts_args['filters'][] = ['field' => $key, 'compare' => $compare, 'value' => $value];
                }
                break;

            case 'date':
                $anchor_str = 'today';
                if (!empty($filter['anchor'])) {
                    switch ($filter['anchor']) {
                        case 'y':
                            $anchor_str = 'first day of this year';
                            break;
                        case 'm':
                            $anchor_str = 'first day of this month';
                            break;
                        case 'w':
                            $anchor_str = 'last sunday';
                            break;
                    }
                }
                $anchor_time = strtotime($anchor_str);
                $unit = $filter['unit'] ?? 'days';
                $after_timestamp = strtotime("-{$filter['start']} $unit", $anchor_time);
                $before_timestamp = strtotime("+{$filter['end']} $unit", $anchor_time);
                $date_field = 'sort_by_date';
                if (($filter['date_source'] ?? 'default') !== 'default') {
                    $date_field = get_field_object($filter['acf_field'])['name'] ?? $filter['meta_key'];
                }
                $ts_args['filters'][] = ['field' => $date_field, 'compare' => '>=', 'value' => $after_timestamp];
                $ts_args['filters'][] = ['field' => $date_field, 'compare' => '<=', 'value' => $before_timestamp];
                break;

            case 'author':
                $ts_args['filters'][] = ['field' => 'post_author_id', 'compare' => '=', 'value' => $filter['value']];
                break;
            case 'post_parent':
                $ts_args['filters'][] = ['field' => 'post_parent', 'compare' => 'IN', 'value' => $filter['post_parent']];
                break;
            case 'distance':
                $filter['miles'] = $filter['miles'] ?: 25;
                if (isset($filter['lat'], $filter['lng'], $filter['miles'])) {
                    $user_lat = (float)$filter['lat'];
                    $user_lng = (float)$filter['lng'];
                    $radius_miles = $filter['miles'];

                    $ts_args['geofilter'] = [
                        'field' => 'locations_geopoints',
                        'lat' => $user_lat,
                        'lng' => $user_lng,
                        'miles' => $radius_miles,
                    ];

                    $ts_args['sort'] = "locations_geopoints($user_lat, $user_lng):asc";
                }
            case 'region':
                if (isset($_COOKIE['selectedRegion'])) {
                    $region = json_decode(stripslashes($_COOKIE['selectedRegion']));
                    if ($region && !empty($region->title)) {
                        $ts_args['s'] .= ' ' . $region->title;
                        $additional_query_by_fields[] = 'related_regions';
                    }
                }
                break;
        }
    }

    $ts_args['s'] = trim($ts_args['s']);
    if (empty($ts_args['s'])) {
        $ts_args['s'] = '*';
    }

    if ($ts_args['s'] !== '*') {
        $final_query_by = array_merge($base_query_by_fields, $additional_query_by_fields);
        $ts_args['query_by_fields'] = array_unique($final_query_by);
    } else {
        $ts_args['query_by_fields'] = [];
    }

    if (!empty($ts_args['geofilter'])) {
        $geo = $ts_args['geofilter'];
        $ts_args['sort'] = "locations_geopoints({$geo['lat']}, {$geo['lng']}):asc";
    }

    $translator = new \WP_Typesense_Query_Translator();
    $search_parameters = $translator->translate($ts_args);

    try {
        $query_string = http_build_query($search_parameters);
        $endpoint = 'collections/' . $collection_name . '/documents/search?' . $query_string;
        $reflection = new \ReflectionClass($api_instance);
        $method = $reflection->getMethod('makeRequest');
        $method->setAccessible(true);
        $results = $method->invoke($api_instance, $endpoint, 'GET', null);

        if (is_wp_error($results)) {
            return ['found' => 0, 'hits' => [], 'error' => $results->get_error_message()];
        }
        if (is_string($results)) {
            $results = json_decode($results, true);
        } else {
            $results = json_decode(json_encode($results), true);
        }
        return $results;
    } catch (\ReflectionException $e) {
        return ['found' => 0, 'hits' => [], 'error' => 'Reflection Error: ' . $e->getMessage()];
    }
}

/**
 * Get the list of post IDs
 *        - Used to get the list of posts to filter
 *
 * @param array $s - processed settings
 *        - post_type: post type
 *
 * @return array - list of post IDs
 */
function get_post_ids($s)
{
    $query = [
        'post_type' => $s['post_type'],
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'tribe_suppress_query_filters' => true
    ];

    $query = prepare_query($query, $s);

    $posts = $query->posts;

    wp_reset_postdata();

    return $posts;
}

/**
 * Get a list of posts with the specified fields
 *
 * @param array $s - processed settings
 *        - post_type: post type
 *        - posts_order: array of post order settings
 *        - pagination: pagination settings
 *        - render_mode: render mode
 *        - render_js: JS render settings
 *        - render_php: PHP render settings
 * @param int $page - page number
 *        - default: 1
 *
 * @return array - list of posts with the specified fields
 */
function get_posts_with_data($s)
{
    $query = [
        'post_type' => $s['post_type'],
        'post_status' => 'publish',
        'posts_per_page' => $s['pagination']['show_all'] == 'n' ? $s['pagination']['posts_per_page'] : '-1',
        'paged' => $s['pagination']['show_all'] == 'n' ? $s['pagination']['page'] : false,
        'orderby' => [],
        'tribe_suppress_query_filters' => true
    ];

    // First things first, we need to iterate through post order to get each type and its priority.
    // orderby has to be formatted as orderby => [type => order] to work as an array.
    // rand and distance supercede all. We might want to rethink the latter at some point, but for now that will be the method.
    if (is_array($s['posts_order'])) {
        foreach ($s['posts_order'] as $order) {
            if ($order['order_by'] === 'field') {
                $query['orderby']['meta_value'] = $order['order'];
                // We need to get the name of the field, not its key!
                $field_name = get_field_object($order['acf_field'])['name'];
                $query['meta_key'] = $field_name;
            } else if ($order['order_by'] === 'meta_key') {
                $query['meta_key'] = $order['value'];
                $query['orderby']['meta_value'] = $order['order'];
            } else if ($order['order_by'] === 'rand') {
                $query['orderby'] = 'rand';
                break;
            } else if ($order['order_by'] === 'distance') {
                // due to the complexity of the distance ordering we should set this up later
                $query['orderby'] = 'rand';
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
                case 'maps-js':
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

                    if ($s['render_mode'] == 'maps-js') {
                        $post['lat'] = get_field('latitude', $post['id']);
                        $post['lon'] = get_field('lon', $post['id']);
                        $p['address'] = get_field('address', $p['id']);
                        $post['type'] = get_field('location_type', $post['id']);
                    }
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

                case 'maps-php':
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

                    if ($s['render_mode'] == 'maps-php') {
                        $p['lat'] = get_field('latitude', $p['id']);
                        $p['lon'] = get_field('longitude', $p['id']);
                        $p['address'] = get_field('address', $p['id']);
                        $p['type'] = get_field('location_type', $p['id']);
                        $p['infoWindow'] = "true";
                        $p['html'] = require \lqx\blocks\get_template('map', $s['render_map_php']['map_preset'], 'infowindow');
                    }

                    // Handle heading, subheading and body
                    foreach (['heading', 'subheading', 'body'] as $key) {
                        if ($s['render_php'][$key]) {
                            if (in_array($s['render_php'][$key], $wp_post_keys)) {
                                if ($s['render_php'][$key] == 'post_excerpt') {
                                    $p[$key] = '<p>' . $post->{$s['render_php'][$key]} . '</p>';
                                } else {
                                    $p[$key] = $post->{$s['render_php'][$key]};
                                }
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
                    if ($s['render_php']['use_post_url'] == 'n') {
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
                    } elseif ($s['render_php']['video_type'] == 'upload' && $video_upload) {
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
                            if (isset($s['render_php']['label_fields']) && is_array($s['render_php']['label_fields'])) {
                                foreach ($s['render_php']['label_fields'] as $field) {
                                    $label_field_object = get_field_object($field['label_field'], $post->ID);
                                    if ($label_field_object != false) {
                                        if (is_array($label_field_object['value'])) {
                                            foreach ($label_field_object['value'] as $value) {
                                                $p['labels'][] = [
                                                    'label' => $value,
                                                    'value' => \lqx\util\slugify($value)
                                                ];
                                            }
                                        } else {
                                            $p['labels'][] = [
                                                'label' => $label_field_object['value'],
                                                'value' => \lqx\util\slugify($label_field_object['value'])
                                            ];
                                        }
                                    }
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
 * Fetches post data using Typesense and formats it for the API response.
 * This is a drop-in replacement for the original WP_Query-based function.
 *
 * @param array $s The complete settings array from the API call.
 * @return array The formatted posts and pagination data.
 */
function get_posts_with_typesense_data($s)
{
    // 1. Get the complete search results from our new Typesense function
    $results = prepare_typesense_query($s);

    // 2. Handle cases where the search failed
    if (isset($results['error'])) {
        // Optional: Log the error for debugging
        // error_log('Typesense API Error: ' . $results['error']);
        return ['posts' => [], 'total_posts' => 0, 'total_pages' => 0];
    }

    // 3. Initialize variables for the response
    $posts = [];
    $total_posts = $results['found'];
    $per_page = ($s['pagination']['show_all'] ?? 'n') == 'n' ? ($s['pagination']['posts_per_page'] ?? 10) : $total_posts;
    $total_pages = ($per_page > 0 && $total_posts > 0) ? ceil($total_posts / max(1, $per_page)) : 1;

    if (!empty($results['hits'])) {
        // Loop through the hits directly from the Typesense response. NO MORE get_post()!
        foreach ($results['hits'] as $hit) {
            $document = $hit['document']; // This is our single, fast source of data for the post.

            // --- START: Rewritten Data Formatting Logic ---
            switch ($s['render_mode']) {
                case 'maps-js':
                case 'js':
                    $formatted_post = [
                        'id' => (int)$document['id'],
                        'author' => $document['post_author'], // Using the author name string from the index
                        'date' => gmdate('Y-m-d H:i:s', $document['sort_by_date']), // Convert timestamp to GMT string
                        'content' => $s['render_js']['post_content'] == 'y' ? ($document['post_content'] ?? '') : '',
                        'title' => $document['post_title'],
                        'excerpt' => $s['render_js']['post_excerpt'] == 'y' ? ($document['post_excerpt'] ?? '') : '',
                        'slug' => basename($document['permalink']), // Deriving slug from the indexed permalink
                        'modified' => $document['post_modified'],
                        'link' => $document['permalink'],
                        'link_style' => $s['render_js']['link_style'] ?? 'button',
                        'type' => $document['post_type'],
                        'thumbnail' => $s['render_js']['post_thumbnail'] == 'y' ? ($document['post_thumbnail_html'] ?? []) : [],

                        // Simplified taxonomy output directly from the indexed string arrays
                        'taxonomies' => [
                            'category' => $document['category'] ?? [],
                            'post_tag' => $document['tags'] ?? []
                        ],

                        // IMPORTANT: Assumes you have indexed your ACF fields into an 'acf_fields' object.
                        'fields' => $document['acf_fields'] ?? []
                    ];
                    break;

                case 'maps-php':
                case 'php':
                    $wp_post_keys = ['post_content', 'post_title', 'post_excerpt'];

                    // This is the data for all your custom ACF fields, which you will index.
                    $acf_fields = $document['acf_fields'] ?? [];

                    $formatted_post = [
                        'id' => (int)$document['id'],
                        'date' => gmdate('Y-m-d H:i:s', $document['sort_by_date']),
                        'slug' => basename($document['permalink']),
                        'modified' => $document['post_modified'],
                        'link' => ['url' => $document['permalink'], 'title' => $s['render_php']['link_title'], 'target' => $s['render_php']['link_target']],
                        'link_style' => $s['render_php']['link_style'] ?? 'button',
                        'image' => null,
                        'icon_image' => null,
                        'video' => ['type' => $s['render_php']['video_type'] ?? 'url'],
                        'labels' => [],
                        'heading' => null,
                        'subheading' => null,
                        'body' => null,
                    ];

                    // Dynamically assign heading, subheading, and body from either a core field or an ACF field
                    foreach (['heading', 'subheading', 'body'] as $key) {
                        if (!empty($s['render_php'][$key])) {
                            $field_name = $s['render_php'][$key];
                            if (in_array($field_name, $wp_post_keys)) {
                                $formatted_post[$key] = $document[$field_name] ?? '';
                            } else {
                                // Assumes the field_name is a key in our indexed acf_fields object
                                $formatted_post[$key] = $acf_fields[$field_name] ?? '';
                            }
                        }
                    }

                    // Handle image and icon_image
                    foreach (['image', 'icon_image'] as $key) {
                        if ($s['render_php'][$key]) {
                            if ($s['render_php'][$key] == 'thumbnail') {
                                if ($document['post_thumbnail_html']) {
                                    // TODO what is image_html and post_thumbnail_html?
                                    $formatted_post['image_html'] = $document['post_thumbnail_html'];
                                } else {
                                    $formatted_post[$key] = \lqx\util\get_thumbnail_image_object((int)$document['id']);
                                }
                            } else {
                                $formatted_post[$key] = get_field($s['render_php'][$key], (int)$document['id']);
                            }
                        }
                    }

                    // Handle custom link from ACF fields
                    if (($s['render_php']['use_post_url'] ?? 'y') == 'n' && !empty($s['render_php']['link'])) {
                        $formatted_post['link'] = $acf_fields[$s['render_php']['link']] ?? null;
                    }

                    // Handle video from ACF fields
                    $video_url_field = $s['render_php']['video_url'];
                    $video_upload_field = $s['render_php']['video_upload'];
                    if (($s['render_php']['video_type'] ?? 'url') == 'url' && !empty($acf_fields[$video_url_field])) {
                        $formatted_post['video'] = ['type' => 'url', 'url' => $acf_fields[$video_url_field]];
                    } elseif (($s['render_php']['video_type'] ?? 'url') == 'upload' && !empty($acf_fields[$video_upload_field])) {
                        $formatted_post['video'] = ['type' => 'upload', 'upload' => $acf_fields[$video_upload_field]];
                    }

                    // Handle labels from indexed taxonomies (much faster)
                    if (($s['render_php']['label_type'] ?? '') == 'taxonomy' && !empty($s['render_php']['label_taxonomies'])) {
                        foreach ($s['render_php']['label_taxonomies'] as $tax) {
                            // The plugin schema uses 'category' and 'tags' as keys
                            $tax_key_in_doc = ($tax === 'category') ? 'category' : (($tax === 'post_tag') ? 'tags' : $tax);
                            if (!empty($document[$tax_key_in_doc])) {
                                foreach ($document[$tax_key_in_doc] as $term_name) {
                                    $formatted_post['labels'][] = ['label' => $term_name, 'value' => $tax . ':' . \lqx\util\slugify($term_name)];
                                }
                            }
                        }
                    }
                    // (Handling labels from ACF fields would follow a similar pattern, reading from $acf_fields)

                    break;
            }

            $posts[] = $formatted_post;
        }
    }

    // 6. Return the final, formatted data structure
    return [
        'posts' => $posts,
        'total_posts' => $total_posts,
        'total_pages' => $total_pages
    ];
}

/**
 * Default HTML render for controls and search bar
 *        - The search bar is only rendered if the setting is enabled
 *
 * @param array $s - processed settings
 *        - controls: array of control settings
 *        - search: search string
 *        - show_search: show search bar
 *        - search_placeholder: search placeholder
 *
 * @return string - HTML for controls and search bar
 */
function render_controls($s)
{
    // Start output buffering
    ob_start();

    // Render the controls
    require \lqx\blocks\get_template('filters', $s['preset'], 'controls');

    // Return the output
    return ob_get_clean();
}

/**
 * Default HTML render for posts using the Cards block
 *        - The Cards block is used to render the posts
 *
 * @param array $s - processed settings and posts data
 *        - posts: array of post data
 *        - render_mode: render mode
 *
 * @return string - HTML for posts
 *        - If the render mode is PHP, the output is returned as a string
 *        - If the render mode is JS, the output is echoed
 */
function render_posts($s)
{
    // Start output buffering
    ob_start();

    // Render the posts
    if ($s['render_mode'] == 'php') {
        require \lqx\blocks\get_template('filters', $s['preset'], 'posts');
    } else if ($s['render_mode'] == 'maps-php') {
        require \lqx\blocks\get_template('filters', $s['preset'], 'map');
    }

    // Return the output
    return ob_get_clean();
}

/**
 * Default HTML render for the pagination, pagination details and posts per page selector
 *        - The pagination details and posts per page selector are only rendered if the settings are enabled
 *
 * @param array $s - processed settings
 *        - pagination: pagination settings
 *        - show_posts_per_page: show posts per page selector
 *        - show_all: show all posts
 *
 * @return string - HTML for the pagination, pagination details and posts per page selector
 */
function render_pagination($s)
{
    // Start output buffering
    ob_start();

    // Render the pagination
    require \lqx\blocks\get_template('filters', $s['preset'], 'pagination');

    // Return the output
    return ob_get_clean();
}

/**
 * Get the label of the selected option
 *        - Used to display the selected option in the control
 *
 * @param array $control - control settings
 *        - options: array of option settings
 *        - selected: selected option value
 *
 * @return string - label of the selected option
 */
function get_selected_option_label($control)
{
    $index = array_search($control['selected'], array_column($control['options'], 'value'));
    if ($index !== false) return $control['options'][$index]['text'];
    return '';
}

/**
 * Prepare the data for JSON
 *        - Removes keys that are not needed or that should not be disclosed
 * @param array $s - processed settings
 *        - render_mode: render mode
 *
 * @return array - processed settings with keys removed
 */
function prepare_json_data($s)
{
    $res = $s;

    // Remove keys that are not needed or that should not be disclosed
    foreach (['post_type', 'pre_filters', 'render_php'] as $key) unset($res[$key]);

    // Handle server-side rendering
    if ($s['render_mode'] == 'php' || $s['render_mode'] == 'maps-php') {
        foreach (['anchor', 'block', 'class', 'clear_label', 'posts', 'render_js',
                     'search_placeholder', 'show_clear', 'show_search'] as $key) unset($res[$key]);
    }

    return $res;
}

/**
 * Validate the payload
 *
 * @param array $payload - received payload
 *        - preset: preset name
 *        - post_id: post ID
 *        - controls: array of control settings
 *        - search: search string
 *        - page: page number
 *        - pagination: pagination settings
 *
 * @return array - validation result
 */
function validate_payload($payload)
{
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
 *        - The payload will override the settings
 *
 * @param array $s - processed settings
 *        - controls: array of control settings
 *        - pagination: pagination settings
 *        - posts_per_page: number of posts per page
 *        - search: search string
 * @param array $p - received payload
 *        - preset: preset name
 *        - post_id: post ID
 *        - controls: array of control settings
 *        - search: search string
 *        - pagination: pagination settings
 *
 * @return array - merged settings
 */
function merge_settings($s, $p)
{
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
            } // Associative arrays
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
 * Handle the API call
 *
 * @param WP_REST_Request $request - REST API request
 *        - JSON payload
 *
 * @return array - JSON data
 */
function handle_api_call($request)
{
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

    // Allow child themes/plugins to adjust control options (e.g., rename choices)
    $s = apply_filters('lyquix_filters_option_map', $s);

    // Get the posts
    $post_info = $s['typesense_search'] == 'y' ? \lqx\filters\get_posts_with_typesense_data($s) : \lqx\filters\get_posts_with_data($s);
    $s['posts'] = $post_info['posts'];
    $s['pagination']['total_posts'] = $post_info['total_posts'];
    $s['pagination']['total_pages'] = $post_info['total_pages'];

    // Prepare the JSON data
    $res = \lqx\filters\prepare_json_data($s);

    // Prepare JSON render
    if ($s['render_mode'] == 'php' || $s['render_mode'] == 'maps-php') {
        $res['render'] = [
            'items' => $post_info['posts'],
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

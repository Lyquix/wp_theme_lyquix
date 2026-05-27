<?php

/**
 * filters.php - Utility functions and REST API for filters
 *
 * @version     3.4.0
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
        ],
        // Related Items - cards_style and cards_preset selects in admin settings
        [ 'user' => 'field_68b00002a000c', 'choice' => 'field_658db3c35c5ac' ],
        [ 'user' => 'field_68b00002a000b', 'choice' => 'field_658db3c5e9695' ],
        // Related Items - style and preset selects in user settings
        [ 'user' => 'field_68b00003a0012', 'choice' => 'field_68b00003a0031' ],
        [ 'user' => 'field_68b00003a0013', 'choice' => 'field_68b00003a0033' ]
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
function get_acf_fields_as_options($field_details, &$choices, $depth = 0, $allowed_types = null)
{
    $key = $field_details['key'];
    $container_types = ['repeater', 'group', 'flexible_content'];
    $is_container = in_array($field_details['type'] ?? '', $container_types);

    // Add this field to choices unless a type filter is active and this is a container
    // (containers themselves aren't selectable when filtering by leaf type)
    if ($allowed_types === null || (!$is_container && in_array($field_details['type'], $allowed_types))) {
        $choices[$key] = str_repeat('- ', $depth) . ($field_details['label'] ?: $field_details['name']) . ' [' . $field_details['key'] . ']';
    }

    // Resolve sub_fields — they may be empty or contain unhydrated string keys
    $sub_fields = $field_details['sub_fields'] ?? [];

    if (!empty($sub_fields) && is_string($sub_fields[0])) {
        // sub_fields contains field keys as strings — resolve to full objects
        $sub_fields = array_filter(array_map('acf_get_field', $sub_fields));
    }

    if (empty($sub_fields) && $is_container) {
        // Explicitly load sub-fields for container types when not populated
        $sub_fields = acf_get_fields($key) ?: [];
    }

    foreach ($sub_fields as $sub_field_details) {
        get_acf_fields_as_options($sub_field_details, $choices, $depth + 1, $allowed_types);
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
        'field_6813b2c514414' => 'text', // pre_filters > region field
        'field_68b00001a0005' => null,   // group_by > acf_field (all field types)
        'field_68b00002a0007' => null,   // related-items > acf_field (all field types)
        'field_68b00002a000d' => 'text', // related-items > render_php > heading
        'field_68b00002a000e' => 'text', // related-items > render_php > subheading
        'field_68b00002a000f' => 'text', // related-items > render_php > body
        'field_68b00002a0010' => 'image', // related-items > render_php > image
        'field_68b00002a0011' => 'image', // related-items > render_php > icon_image
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

    if(in_array($field['key'], ['field_65f471bf7d99b', 'field_65f475117fcd5', 'field_65f475457fcda',
                                'field_68b00002a000d', 'field_68b00002a000e', 'field_68b00002a000f'])) {
        $field['choices']['post_title'] = 'Title';
        $field['choices']['post_name'] = 'Slug';
        $field['choices']['post_excerpt'] = 'Excerpt';
        $field['choices']['post_content'] = 'Content';
    }

    if(in_array($field['key'], ['field_65f4752a7fcd6', 'field_65f4752f7fcd7',
                                'field_68b00002a0010', 'field_68b00002a0011'])) {
        $field['choices']['thumbnail'] = 'Thumbnail';
    }

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
        // Get the field group fields
        $group['fields'] = acf_get_fields($group['key']);

        // Loop through fields in group
        // When a type filter is set, pass it to get_acf_fields_as_options so it can
        // recurse into containers (repeaters/groups) and include matching child fields
        $allowed_types = $field_keys[$field['key']] !== null ? ($field_types[$field_keys[$field['key']]] ?? null) : null;
        foreach ($group['fields'] as $field_details) {
            $is_container = in_array($field_details['type'] ?? '', ['repeater', 'group', 'flexible_content']);
            if ($allowed_types === null || in_array($field_details['type'], $allowed_types) || $is_container) {
                if(!isset($field['choices'][$group['title']])) $field['choices'][$group['title']] = [];
                \lqx\filters\get_acf_fields_as_options($field_details, $field['choices'][$group['title']], 0, $allowed_types);
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
    $s['featured_html'] = $post_info['featured_html'] ?? '';

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
                            'allowed' => ['author', 'date', 'field', 'parentless', 'post_parent', 'taxonomy', 'venue', 'dynamic', 'meta_key', 'region', 'manual', 'custom']
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
                        'custom_function' => \lqx\util\schema_str_req_emp,
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
                            'allowed' => ['taxonomy', 'field', 'distance', 'region', 'custom']
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
                        ],
                        'banner_field' => \lqx\util\schema_str_req_emp
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
            'typesense_search' => \lqx\util\schema_str_req_n,
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
            'group_by' => \lqx\util\schema_str_req_n,
            'group_by_source' => [
                'type' => 'string',
                'required' => true,
                'default' => 'acf_field',
                'allowed' => ['acf_field', 'post_meta', 'post_property', 'taxonomy']
            ],
            'group_by_acf_field' => \lqx\util\schema_str_req_emp,
            'group_by_post_property' => [
                'type' => 'string',
                'required' => true,
                'default' => 'post_date',
                'allowed' => ['post_date', 'post_title', 'post_author', 'post_status',
                              'post_name', 'post_parent', 'post_type', 'menu_order']
            ],
            'group_by_field_name' => \lqx\util\schema_str_req_emp,
            'group_by_taxonomy' => \lqx\util\schema_str_req_emp,
            'group_by_value_type' => [
                'type' => 'string',
                'required' => true,
                'default' => 'text',
                'allowed' => ['text', 'date', 'number']
            ],
            'group_by_date_key_format' => [
                'type' => 'string',
                'required' => true,
                'default' => 'Y-m'
            ],
            'group_by_date_label_format' => [
                'type' => 'string',
                'required' => true,
                'default' => 'F Y'
            ],
            'group_by_heading_tag' => [
                'type' => 'string',
                'required' => true,
                'default' => 'h3',
                'allowed' => ['h2', 'h3', 'h4', 'h5']
            ],
            'group_by_heading_template' => \lqx\util\schema_str_req_emp,
            'callback' => \lqx\util\schema_str_req_emp,
            'single_control_filter' => \lqx\util\schema_str_req_n,
            'featured' => [
                'type' => 'object',
                'required' => true,
                'default' => [],
                'keys' => [
                    'enabled' => \lqx\util\schema_str_req_n,
                    'cards_preset' => \lqx\util\schema_str_req_emp,
                    'cards_style' => \lqx\util\schema_str_req_emp,
                    'limit' => [
                        'type' => 'integer',
                        'required' => true,
                        'default' => 1,
                        'range' => [1, 10]
                    ]
                ]
            ]
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

            case 'custom':
                foreach (
                    [
                        'operator_simple',
                        'operator_advanced',
                        'date_source',
                        'taxonomy_term',
                        'post_parent',
                        'acf_field',
                        'value',
                        'anchor',
                        'unit',
                        'start',
                        'end'
                    ] as $k
                ) {
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
                if (!\lqx\regions\has_regions()) break;
                $control['label'] = 'Region';
                $control['options'][] = ['value' => 'this-region', 'text' => 'This Region'];
                $region = \lqx\regions\get_region();
                if ($region !== 'outside-region') {
                    $control['selected'] = 'this-region';
                }
                break;

            case 'custom':
                // Set the label from ACF or default
                if (!isset($control['label'])) $control['label'] = 'Custom Filter';

                // Remove non-custom fields
                unset($control['taxonomy']);
                unset($control['acf_field']);
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

    // Remove unused render mode data.
    // When typesense_search=y, preserve render_js even for php render_mode because
    // the browser needs it to render cards via Typesense directly.
    $is_typesense = ($s['typesense_search'] ?? 'n') === 'y';
    switch ($s['render_mode']) {
        case 'maps-php':
        case 'php':
            if (!$is_typesense) unset($s['render_js']);
            break;

        case 'maps-js':
        case 'js':
            unset($s['render_php']);
            break;
    }

    // When typesense_search is enabled the browser queries Typesense directly.
    // Embed the connection config (from plugin WP options) + a server-side locked
    // filter translated from pre_filters so the client can't bypass constraints.
    if ($is_typesense) {
        $ts_config = get_typesense_client_config();
        $ts_config['collection'] = $ts_config['collection'] ?: ($s['post_type'] ?? 'posts');
        $ts_config['locked_filter'] = build_locked_typesense_filter($s);

        // Pre-resolve default sort_by from posts_order (used as initial/fallback sort).
        $sort_parts = [];
        foreach ($s['posts_order'] ?? [] as $order) {
            $dir = $order['order'] ?? 'asc';
            switch ($order['order_by'] ?? '') {
                case 'rand':
                    $sort_parts = ['_eval(random()):desc'];
                    break 2;
                case 'date':
                    $sort_parts[] = "sort_by_date:{$dir}";
                    break;
                case 'distance':
                    // Resolved dynamically in JS once user lat/lng is known
                    break;
                case 'field':
                case 'meta_key':
                    if (!empty($order['acf_field'])) {
                        $field_obj = get_field_object($order['acf_field']);
                        $field_name = $field_obj['name'] ?? '';
                        if ($field_name) $sort_parts[] = "{$field_name}:{$dir}";
                    } elseif (!empty($order['value'])) {
                        $sort_parts[] = "{$order['value']}:{$dir}";
                    }
                    break;
            }
        }
        $ts_config['sort_by'] = implode(',', $sort_parts);

        // Build an ACF-key → Typesense-field-name lookup so JS can resolve sort
        // fields dynamically when the user changes the sort order (change_order feature).
        // Covers both the default posts_order and all change_order options.
        $acf_field_names = [];
        $order_entries = array_merge(
            $s['posts_order'] ?? [],
            array_map(function ($opt) {
                // order_options entries use order_by as {value, label} array from ACF
                return [
                    'order_by' => is_array($opt['order_by']) ? ($opt['order_by']['value'] ?? '') : ($opt['order_by'] ?? ''),
                    'order'    => $opt['order'] ?? 'asc',
                    'acf_field' => $opt['acf_field'] ?? '',
                ];
            }, $s['order_options'] ?? [])
        );
        foreach ($order_entries as $order) {
            $key = $order['acf_field'] ?? '';
            if (!empty($key) && !isset($acf_field_names[$key])) {
                $field_obj = get_field_object($key);
                $field_name = $field_obj['name'] ?? '';
                if ($field_name) $acf_field_names[$key] = $field_name;
            }
        }
        $ts_config['acf_field_names'] = $acf_field_names;

        // Pass link title for the card CTA button.
        // Prefer render_php.link_title (php render_mode), fall back to render_js.link_title.
        $ts_config['link_title'] = $s['render_php']['link_title']
            ?? $s['render_js']['link_title']
            ?? '';

        // Pass cards block style/preset so JS can rebuild the correct wrapper CSS classes
        // (e.g. 'physicians-cards') around the injected card_html items.
        $ts_config['cards_style']  = $s['render_php']['style']  ?? '';
        $ts_config['cards_preset'] = $s['render_php']['preset'] ?? '';

        // Allow child themes / plugins to extend the Typesense client config
        // (e.g. to add render_card_callback, custom fields, etc.).
        $ts_config = apply_filters('lqx_typesense_config', $ts_config, $s);

        $s['typesense_config'] = $ts_config;
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
                                $raw   = $field_value->meta_value;
                                $array = ($raw !== '' && $raw !== null) ? @unserialize($raw) : false;
                                if ($array === false && $raw !== 'b:0;') {
                                    $array = json_decode($raw, true);
                                }

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

            case 'custom':
                if (!empty($control['alias'])) {
                    $alias = preg_replace('/[^a-z0-9_]/', '_', strtolower($control['alias']));
                    $file = get_stylesheet_directory() . '/php/custom/filter-controls.php';
                    if (file_exists($file)) require_once $file;
                    $fn = 'lqx_filter_options_' . $alias;
                    if (function_exists($fn)) {
                        $options = $fn($control, $posts, $s);
                        if (!is_array($options)) $options = [];
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

            case 'none';
                break;
        }

        // Add banner_html to each option when a banner_field is configured
        if (!empty($control['banner_field'])) {
            foreach ($options as &$option) {
                $banner_html = '';
                if ($control['type'] === 'taxonomy') {
                    $banner_html = get_field($control['banner_field'], 'term_' . $option['value']);
                } elseif ($control['type'] === 'field') {
                    $banner_html = get_field($control['banner_field'], (int) $option['value']);
                }
                $option['banner_html'] = $banner_html ?: '';
            }
            unset($option);
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
                if (\lqx\regions\has_regions() && (isset($_COOKIE['selectedRegion']) || isset($_COOKIE['ipDetectedRegion']))) {
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
							AND pm.meta_value LIKE %s';
                    $sql = $wpdb->remove_placeholder_escape($wpdb->prepare($region_query, '%"' . $wpdb->esc_like($region) . '"%'));
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
            case 'custom':
                if (!empty($pre_filter['custom_function']) && is_callable($pre_filter['custom_function'])) {
                    $query = call_user_func($pre_filter['custom_function'], $query, $pre_filter, $s);
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
                case 'custom':
                    if (!empty($control['alias'])) {
                        $alias = preg_replace('/[^a-z0-9_]/', '_', strtolower($control['alias']));
                        $file = get_stylesheet_directory() . '/php/custom/filter-controls.php';
                        if (file_exists($file)) require_once $file;
                        $fn = 'lqx_filter_apply_' . $alias;
                        if (function_exists($fn)) {
                            $query = $fn($query, $control, $s);
                        }
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
 * Read Typesense client-side connection config from the cm-typesense plugin WP options.
 * Returns an array with host, port, protocol, search_only_key, and collection.
 *
 * Settings are stored in WP options:
 *   cm_typesense_search_config_settings  — server connection + search-only key
 *   cm_typesense_admin_settings          — collection name
 *   cm_typesense_plugin_activate         — whether the plugin is active
 *
 * @return array
 */
function get_typesense_client_config(): array {
    $admin_cfg = get_option('cm_typesense_admin_settings', []);

    if (is_string($admin_cfg)) $admin_cfg = maybe_unserialize($admin_cfg);
    if (!is_array($admin_cfg)) $admin_cfg = [];

    // protocol is stored as 'https://' — strip trailing colon/slashes
    $protocol = rtrim($admin_cfg['protocol'] ?? 'https', ':/ ');

    return [
        'host'            => $admin_cfg['node']            ?? '',
        'port'            => (string)($admin_cfg['port']   ?? '443'),
        'protocol'        => $protocol ?: 'https',
        'search_only_key' => $admin_cfg['search_api_key']  ?? '',
        'collection'      => '', // set per-preset from post_type in init_settings
    ];
}

/**
 * Translate pre_filters into a Typesense filter_by string that the client must always include.
 * This is a security measure: pre_filters constrain results (e.g., by region, date range, taxonomy)
 * and must not be alterable by the client.
 *
 * @param array $s The fully processed settings array
 * @return string The locked Typesense filter_by clause
 */
function build_locked_typesense_filter(array $s): string {
    $parts = [];

    foreach ($s['pre_filters'] ?? [] as $filter) {
        switch ($filter['type'] ?? '') {
            case 'taxonomy':
                if (!empty($filter['taxonomy_term']->taxonomy) && !empty($filter['taxonomy_term']->term_id)) {
                    $field = $filter['taxonomy_term']->taxonomy . '_id';
                    $op = ($filter['operator_simple'] ?? '=') === '!=' ? '!=' : '=';
                    $parts[] = $field . ':' . $op . $filter['taxonomy_term']->term_id;
                }
                break;

            case 'field':
            case 'meta_key':
                $key = !empty($filter['acf_field']) ? get_field_object($filter['acf_field'])['name'] : ($filter['meta_key'] ?? '');
                $value = $filter['value'] ?? '';
                if ($key !== '' && $value !== '') {
                    $compare = $filter['operator_advanced'] ?? '=';
                    // Map WP operators to Typesense
                    $ts_op = match($compare) {
                        '!=' => '!=',
                        '>' => '>',
                        '>=' => '>=',
                        '<' => '<',
                        '<=' => '<=',
                        default => '='
                    };
                    $parts[] = $key . ':' . $ts_op . $value;
                }
                break;

            case 'date':
                // Translate date pre-filters to a sort_by_date range filter
                $anchor = match($filter['anchor'] ?? 'd') {
                    'y' => date('Y') . '-01-01',
                    'm' => date('Y-m') . '-01',
                    'w' => date('Y-m-d', strtotime('sunday last week')),
                    default => date('Y-m-d')
                };
                $unit = match($filter['unit'] ?? 'd') {
                    'w' => 'weeks', 'm' => 'months', 'y' => 'years', default => 'days'
                };
                $after = strtotime($anchor . ' -' . ($filter['start'] ?? 0) . ' ' . $unit);
                $before = strtotime($anchor . ' +' . ($filter['end'] ?? 0) . ' ' . $unit);
                if ($after && $before) {
                    $parts[] = 'sort_by_date:[' . $after . '..' . $before . ']';
                }
                break;

            case 'author':
                if (!empty($filter['value'])) {
                    $parts[] = 'post_author_id:=' . $filter['value'];
                }
                break;
        }
    }

    return implode(' && ', $parts);
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

    // Build featured posts HTML (PHP render mode, page 1 only)
    // Uses _is_featured post meta registered by featured-posts.php
    $featured_html = '';
    if (
        in_array($s['render_mode'] ?? '', ['php', 'maps-php']) &&
        ($s['featured']['enabled'] ?? 'n') === 'y' &&
        ($s['pagination']['page'] ?? 1) == 1
    ) {
        $feat = $s['featured'];
        $feat_query_args = [
            'post_type' => $s['post_type'],
            'post_status' => 'publish',
            'posts_per_page' => $feat['limit'],
            'tribe_suppress_query_filters' => true,
            'meta_query' => [
                [
                    'key' => '_is_featured',
                    'value' => '1',
                    'compare' => '='
                ]
            ]
        ];
        $feat_query = new \WP_Query($feat_query_args);
        if ($feat_query->have_posts()) {
            $feat_posts = [];
            $feat_ids = [];
            while ($feat_query->have_posts()) {
                $feat_query->the_post();
                $feat_post = get_post(get_the_ID());
                $feat_ids[] = $feat_post->ID;
                $feat_posts[] = build_item_from_post($feat_post, $s);
            }
            wp_reset_postdata();
            $query['post__not_in'] = array_merge($query['post__not_in'] ?? [], $feat_ids);
            $cards_settings = \lqx\blocks\get_settings(
                'cards', null,
                $feat['cards_preset'] ?: ($s['render_php']['preset'] ?? ''),
                $feat['cards_style'] ?: ($s['render_php']['style'] ?? '')
            );
            $cards_settings['processed']['hash'] = $s['hash'] . '-featured';
            $cards_settings['processed']['class'] = 'posts featured ' . ($feat['cards_style'] ?: ($s['render_php']['style'] ?? ''));
            $cards_settings['processed']['preset'] = $cards_settings['processed']['preset'] ?: ($feat['cards_preset'] ?: $s['preset']);
            ob_start();
            \lqx\blocks\render_block($cards_settings, $feat_posts);
            $featured_html = ob_get_clean();
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
                    // Preserve the WP_Post object before reassigning $post to the data array
                    $wp_post_obj = $post;
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

                    // Pre-render card HTML via PHP template system (respects child theme / preset template inheritance)
                    if ($s['render_mode'] === 'js') {
                        $card_html = render_js_card($wp_post_obj, $s);
                        if ($card_html) $post['card_html'] = $card_html;
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
        'total_pages' => $total_pages,
        'featured_html' => $featured_html
    ];
}

/**
 * Build the card item data array from a WP_Post using a filter preset's render_php config.
 * Used to pre-render card HTML at Typesense index time.
 *
 * @param WP_Post $post - the post to build item data for
 * @param array   $s    - processed filter settings (with render_php sub-array)
 * @return array  - item data array ready for lqx\cards\schema validation
 */
function build_item_from_post($post, $s)
{
    $wp_post_keys = ['post_content', 'post_title', 'post_excerpt', 'post_name'];
    $render_php   = $s['render_php'];

    $p = [
        'id'               => $post->ID,
        'date'             => ($post->post_date_gmt !== '0000-00-00 00:00:00') ? $post->post_date_gmt : $post->post_date,
        'heading'          => null,
        'subheading'       => null,
        'slug'             => $post->post_name,
        'modified'         => $post->post_modified_gmt,
        'link'             => [
            'url'    => get_permalink($post->ID),
            'title'  => $render_php['link_title']  ?? null,
            'target' => $render_php['link_target'] ?? null,
        ],
        'link_style'       => $render_php['link_style'] ?? 'button',
        'body'             => null,
        'labels'           => [],
        'image'            => null,
        'icon_image'       => null,
        'video'            => ['type' => $render_php['video_type'] ?? 'url'],
        'additional_classes' => '',
        'item_id'          => '',
    ];

    foreach (['heading', 'subheading', 'body'] as $key) {
        if (!empty($render_php[$key])) {
            if (in_array($render_php[$key], $wp_post_keys)) {
                $p[$key] = $render_php[$key] === 'post_excerpt'
                    ? '<p>' . $post->{$render_php[$key]} . '</p>'
                    : $post->{$render_php[$key]};
            } else {
                $p[$key] = get_field($render_php[$key], $post->ID);
            }
        }
    }

    foreach (['image', 'icon_image'] as $key) {
        if (!empty($render_php[$key])) {
            $p[$key] = $render_php[$key] === 'thumbnail'
                ? \lqx\util\get_thumbnail_image_object($post->ID)
                : get_field($render_php[$key], $post->ID);
        }
    }

    if (($render_php['use_post_url'] ?? 'y') === 'n') {
        $p['link'] = !empty($render_php['link'])
            ? get_field($render_php['link'], $post->ID)
            : null;
    }

    switch ($render_php['label_type'] ?? '') {
        case 'taxonomy':
            foreach ($render_php['label_taxonomies'] ?? [] as $tax) {
                $terms = get_the_terms($post->ID, $tax);
                if ($terms && !is_wp_error($terms)) {
                    foreach ($terms as $term) {
                        $p['labels'][] = ['label' => $term->name, 'value' => $tax . ':' . $term->slug];
                    }
                }
            }
            break;
        case 'field':
            foreach ($render_php['label_fields'] ?? [] as $field_def) {
                $obj = get_field_object($field_def['label_field'], $post->ID);
                if ($obj) {
                    if (is_array($obj['value'])) {
                        foreach ($obj['value'] as $val) {
                            $p['labels'][] = ['label' => $val, 'value' => \lqx\util\slugify($val)];
                        }
                    } elseif ($obj['value']) {
                        $p['labels'][] = ['label' => $obj['value'], 'value' => \lqx\util\slugify($obj['value'])];
                    }
                }
            }
            break;
    }

    return $p;
}

/**
 * Render a single card <li> HTML for a post using a filter preset's card templates.
 * Intended for pre-rendering card HTML at Typesense index time so JS can inject it
 * directly without any server roundtrip.
 *
 * @param WP_Post $post   - the post to render
 * @param string  $preset - the filter preset name (e.g. 'physicians-archive')
 * @return string - the rendered <li> card HTML, or empty string on failure
 */
function render_card_for_preset($post, $preset)
{
    static $filter_settings_cache = [];

    // Cache filter settings per preset to avoid repeated ACF/DB lookups.
    // Only validate_settings is called — init_settings runs region/geolocation logic
    // that isn't needed for card rendering (and fails outside of HTTP context).
    if (!isset($filter_settings_cache[$preset])) {
        $settings = \lqx\blocks\get_settings('filters', null, $preset, '');
        $s        = validate_settings($settings);
        $filter_settings_cache[$preset] = $s;
    }
    $filter_s = $filter_settings_cache[$preset];

    // Only PHP render modes support template-based card rendering
    if (!in_array($filter_s['render_mode'], ['php', 'maps-php'])) return '';

    // Build the item data array from this post
    $item_data = build_item_from_post($post, $filter_s);

    // Validate item against the cards schema to get a clean $item with defaults
    $v = \lqx\util\validate_data($item_data, \lqx\cards\schema);
    if (!$v['isValid']) return '';
    $item = $v['data'];

    // Determine cards preset and style from the filter's render_php settings
    $cards_preset = $filter_s['render_php']['preset'] ?: $preset;
    $cards_style  = $filter_s['render_php']['style']  ?? '';

    // Get cards block settings and configure them for this context.
    // Must modify $cards_raw['processed'] directly — render_block uses that array.
    $cards_raw = \lqx\blocks\get_settings('cards', null, $cards_preset, $cards_style);
    $cards_raw['processed']['hash']     = 'ts-pre-' . $post->ID;
    $cards_raw['processed']['class']    = 'posts ' . $cards_style;
    $cards_raw['processed']['preset']   = $cards_preset;  // drives template resolution
    $cards_raw['processed']['group_by'] = $filter_s['group_by'] ?? 'n';
    if (($filter_s['group_by'] ?? 'n') === 'y') {
        foreach (['group_by_source', 'group_by_acf_field', 'group_by_post_property', 'group_by_field_name',
                  'group_by_taxonomy', 'group_by_value_type', 'group_by_date_key_format',
                  'group_by_date_label_format', 'group_by_heading_tag', 'group_by_heading_template'] as $_gb_key) {
            $cards_raw['processed'][$_gb_key] = $filter_s[$_gb_key] ?? '';
        }
    }

    // Render the full cards block HTML with a single item, capture output
    ob_start();
    \lqx\blocks\render_block($cards_raw, [$item_data]);
    $full_html = ob_get_clean();

    // Extract just the <li> element — the full output is section>div>ul>li
    // Note: the template may emit <li\n\t (newline+indent) rather than <li<space>
    $start = strpos($full_html, '<li');
    $end   = strrpos($full_html, '</li>');
    if ($start !== false && $end !== false) {
        return substr($full_html, $start, $end - $start + 5);
    }

    return '';
}

/**
 * Render a single card <li> HTML for JS render mode using the PHP template system.
 * Requires render_php to be configured on the filter settings so field mapping works.
 * Results are per-post unique (hash changes), but cards_raw settings are cached per preset.
 *
 * @param WP_Post $wp_post - the WP_Post object
 * @param array   $s       - processed filter settings (must include render_php)
 * @return string - rendered <li> HTML, or empty string on failure
 */
function render_js_card($wp_post, $s)
{
    static $cards_settings_cache = [];

    if (empty($s['render_php'])) return '';

    $preset    = $s['render_php']['preset'] ?: ($s['preset'] ?? '');
    $style     = $s['render_php']['style']  ?? '';
    $cache_key = $preset . '|' . $style;

    if (!isset($cards_settings_cache[$cache_key])) {
        $cards_raw = \lqx\blocks\get_settings('cards', null, $preset, $style);
        $cards_raw['processed']['class']  = 'posts ' . $style;
        $cards_raw['processed']['preset'] = $cards_raw['processed']['preset'] ?: $preset;
        $cards_settings_cache[$cache_key] = $cards_raw;
    }

    $cards_raw = $cards_settings_cache[$cache_key];
    $cards_raw['processed']['hash'] = 'js-' . $wp_post->ID;

    $item_data = build_item_from_post($wp_post, $s);

    ob_start();
    \lqx\blocks\render_block($cards_raw, [$item_data]);
    $full_html = ob_get_clean();

    $start = strpos($full_html, '<li');
    $end   = strrpos($full_html, '</li>');
    if ($start !== false && $end !== false) {
        return substr($full_html, $start, $end - $start + 5);
    }

    return '';
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
        // When typesense_search=y, keep render_js — the browser needs it for card rendering.
        $strip_render_js = ($s['typesense_search'] ?? 'n') !== 'y';
        $strip_keys = ['anchor', 'block', 'class', 'clear_label', 'posts',
                       'search_placeholder', 'show_clear', 'show_search'];
        if ($strip_render_js) $strip_keys[] = 'render_js';
        foreach ($strip_keys as $key) unset($res[$key]);
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
    // Minimal-surface validation of the security-sensitive keys. The payload
    // carries many additional keys that flow into merge_settings(); those are
    // re-validated downstream by validate_settings() with strict per-key schemas,
    // so the extra keys are intentionally not modeled here.
    //
    // Note: validate_data() silently drops array elements that fail their elem
    // schema. Because merge_settings() merges controls by index, the allowed
    // list for controls[].type MUST include every type the frontend can send
    // — otherwise valid controls would be dropped and the remaining controls
    // would merge into the wrong settings positions. The filter hook below lets
    // child themes register new control types without modifying this list.
    // Keep the default list in sync with validate_settings() (filters.php:421).
    return \lqx\util\validate_data($payload, [
        'type' => 'object',
        'required' => true,
        'keys' => [
            'preset' => \lqx\util\schema_str_req_notemp,
            'post_id' => [
                'type' => 'integer',
                'required' => true,
                'range' => [1, null]
            ],
            'style' => \lqx\util\schema_str_req_emp,
            'controls' => [
                'type' => 'array',
                'required' => true,
                'elems' => [
                    'type' => 'object',
                    'keys' => [
                        'type' => [
                            'type' => 'string',
                            'required' => true,
                            'allowed' => apply_filters(
                                'lqx_filters_payload_control_types',
                                ['taxonomy', 'field', 'distance', 'region', 'custom']
                            )
                        ],
                        'taxonomy' => \lqx\util\schema_str_req_emp,
                        'acf_field' => \lqx\util\schema_str_req_emp,
                        'selected' => \lqx\util\schema_str_req_emp
                    ]
                ]
            ],
            'search' => \lqx\util\schema_str_req_emp,
            'pagination' => [
                'type' => 'object',
                'required' => true,
                'keys' => [
                    'page' => [
                        'type' => 'integer',
                        'required' => true,
                        'range' => [1, null],
                        'default' => 1
                    ],
                    'posts_per_page' => [
                        'type' => 'integer',
                        'required' => true,
                        'range' => [1, 200],
                        'default' => 10
                    ]
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
                    // Guard: $s[$key] may be null/shorter if preset changed since last request
                    if (isset($s[$key]) && is_array($s[$key]) && array_key_exists($i, $s[$key])) {
                        $s[$key][$i] = merge_settings($s[$key][$i], $v);
                    } else {
                        $s[$key][$i] = $v;
                    }
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

    // Validate against the schema in validate_payload(). Reject invalid input
    // with a 400 rather than continuing — the prior behavior of accepting
    // arbitrary keys allowed deep-merging unvetted values into the block
    // settings tree downstream in merge_settings().
    $v = validate_payload($payload);
    if (!is_array($v) || empty($v['isValid'])) {
        return new \WP_Error('lqx_filters_invalid_payload', 'Invalid request payload', ['status' => 400]);
    }
    $p = $v['data'];

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
    $post_info = \lqx\filters\get_posts_with_data($s);
    $s['posts'] = $post_info['posts'];
    $s['pagination']['total_posts'] = $post_info['total_posts'];
    $s['pagination']['total_pages'] = $post_info['total_pages'];

    // Prepare the JSON data
    $res = \lqx\filters\prepare_json_data($s);

    // Prepare JSON render
    if (in_array($s['render_mode'], ['php', 'maps-php', 'js'])) {
        $res['render']['controls'] = \lqx\util\minify_html(render_controls($s));
    }

    if ($s['render_mode'] == 'php' || $s['render_mode'] == 'maps-php') {
        $res['render']['items']      = $post_info['posts'];
        $res['render']['posts']      = \lqx\util\minify_html(render_posts($s));
        $res['render']['pagination'] = \lqx\util\minify_html(render_pagination($s));
        $res['render']['featured']   = \lqx\util\minify_html($post_info['featured_html'] ?? '');
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

/**
 * Auto-populate card_html in Typesense documents.
 * Child themes declare their preset via the lqx_typesense_card_html_preset filter.
 * Priority 11 runs after child theme hooks (10) so we skip if already set.
 *
 * Example child theme registration:
 *   add_filter('lqx_typesense_card_html_preset', function($preset, $post_type) {
 *       return $post_type === 'blog' ? 'blog-archive' : $preset;
 *   }, 10, 2);
 */
add_filter('cm_typesense_data_before_entry', function ($data, $post, $post_type) {
    if (isset($data['card_html'])) return $data;
    $preset = apply_filters('lqx_typesense_card_html_preset', '', $post_type, $post);
    if (!$preset) return $data;
    $card_html = \lqx\filters\render_card_for_preset($post, $preset);
    if ($card_html) $data['card_html'] = $card_html;
    return $data;
}, 11, 3);

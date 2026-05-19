<?php

/**
 * util.php - Utility Functions for PHP
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

namespace lqx\util;

/**
 * Polyfill for PHP 7.3 array_is_list function
 */
function array_is_list(array $arr) {
	if ($arr === []) return true;
	return array_keys($arr) === range(0, count($arr) - 1);
}

/**
 * Utility function to get the data type of a variable as needed by validate_data
 *
 * @param mixed $data The variable to check
 * 		- boolean or integer or float etc.
 *
 * @return string The data type of the variable
 */
function get_data_type($data) {
	// Get the data type
	$type = gettype($data);

	// Convert the data type to a string that can be used by validate_data
	switch ($type) {
		case 'boolean':
		case 'integer':
		case 'string':
			break;

		case 'double':
			$type = 'float';
			break;

		case 'array':
			if (!array_is_list($data)) $type = 'object';
			break;

		default:
			$type = '';
	}

	return $type;
}

/**
 * Checks if the site is running in a local environment based on the WPCONFIG_ENVNAME environment variable or the .test TLD
 */
function is_local_environment() {
	return (getenv('WPCONFIG_ENVNAME') === 'local' || substr($_SERVER['HTTP_HOST'], -5) === '.test');
}

/**
 * Validates and processes data based on a provided schema.
 *
 * This function checks whether the given data conforms to the specified schema
 * and performs fixes when possible. It can be used to ensure that incoming data
 * adheres to expected formats and requirements.
 *
 * @param array $data    The data to be validated and processed. Must be an associative array.
 * @param string $field  The name of the field being validated. This is used to provide more detailed information
 * @param array $schema  The schema defines the expected structure and validation rules for the incoming data.
 *
 *              It should be an associative array where each key corresponds to a field in the incoming data,
 *              and the corresponding value is an array containing configuration options for that field.
 *
 *              The structure of each field configuration is as follows:
 *
 *              - 'type' (string, required): Specifies the expected data type for the field. It can be one of the
 *                following types:
 *                - 'string': A string data type.
 *                - 'integer': An integer data type.
 *                - 'float': A floating-point number data type.
 *                - 'boolean': A boolean data type (true or false).
 *                - 'array' : An array (a  numbered list of elements)
 *                - 'object' : An associative array
 *
 *              - 'allowed' (array, optional): Applicable only if 'type' is 'string', 'integer', or 'float;. Defines a list
 *                 of the allowed values. If the value is not one of the allowed values, it will be considered invalid.
 *
 *              - 'range' (array, optional): Applicable only if 'type' is 'integer' or 'float'. Defines the allowed range
 *                of values by setting a minimum and maximum. If the value is outside the range, it will be considered
 *                invalid. If either end of the range array is set to null, then the range only checks minimum/maximum.
 *
 *              - 'match' (string, optional): Applicable only if 'type' is 'string'. Defines a regular expression pattern.
 *                If the value doesn't match the regular expression, it will be considered invalid.
 *
 *              - 'default' (mixed, optional): Provides a default value for the field when it's required and it's either
 *                missing in the incoming data or the value is of the wrong type. If no default is provided for a required
 *                field, the data will not be considered fixed.
 *
 *              - 'required' (bool, optional): Indicates whether a key in an object is required. If set to true, the key
 *                must exist in the incoming data, or it will be considered missing. Default is false.
 *
 *              - 'keys' (array, optional): Applicable only if 'type' is 'object'. Defines the expected keys within the
 *                object. This is a nested schema that follows the same structure as the main `$schema` and is used to
 *                validate the keys of the object.
 *
 *              - 'elems' (array, optional): Applicable only if 'type' is 'array'. Defines the expected elements within the
 *                array. This is a nested schema that follows the same structure as the main `$schema` and is used to
 *
 * @return false if the schema is invalid.
 * @return array An array containing the validation results and possibly fixed data.
 *               - 'isValid': A boolean indicating whether the data is valid according to the schema.
 *               - 'isFixed': A boolean indicating whether any fixes were applied to the data.
 *               - 'missing': An array listing keys that are missing in the data but required by the schema.
 *               - 'mistyped': An array listing keys whose data types do not match the schema.
 *               - 'invalid': An array listing keys whose values are not allowed by the schema.
 *               - 'fixed': An array listing keys for which fixes were applied.
 *               - 'data': The processed data, which may include fixes if 'isFixed' is true.
 */

function validate_data($data, $schema, $field = 'root') {
	$missing = [];
	$mistyped = [];
	$invalid = [];
	$fixed = [];
	$isValid = true;
	$isFixed = false;

	// Check the keys of the schema
	foreach (array_keys($schema) as $key) {
		switch($key) {
			case 'type':
				if (!in_array($schema['type'], ['string', 'integer', 'float', 'boolean', 'array', 'object'])) {
					throw new \Exception('validate_data: invalid type ' . $schema['type'] . ' in schema');
				}
				break;

			case 'required':
				if (gettype($schema['required']) !== 'boolean') {
					throw new \Exception('validate_data: invalid required in schema');
				}
				break;

			case 'default':
				break;

			case 'keys':
				if (gettype($schema['keys']) !== 'array') {
					throw new \Exception('validate_data: invalid keys in schema');
				}
				break;

			case 'elems':
				if (gettype($schema['elems']) !== 'array') {
					throw new \Exception('validate_data: invalid elems in schema');
				}
				break;

			case 'allowed':
				if (gettype($schema['allowed']) !== 'array') {
					throw new \Exception('validate_data: invalid allowed in schema');
				}
				if (count($schema['allowed']) === 0) {
					throw new \Exception('validate_data: empty allowed array');
				}
				break;

			case 'range':
				if (gettype($schema['range']) !== 'array') {
					throw new \Exception('validate_data: invalid range in schema');
				}
				if (count($schema['range']) !== 2) {
					throw new \Exception('validate_data: invalid range in schema');
				}
				if ($schema['range'][0] !== null && gettype($schema['range'][0]) !== 'integer' && gettype($schema['range'][0]) !== 'float') {
					throw new \Exception('validate_data: invalid range in schema');
				}
				if ($schema['range'][1] !== null && gettype($schema['range'][1]) !== 'integer' && gettype($schema['range'][1]) !== 'float') {
					throw new \Exception('validate_data: invalid range in schema');
				}
				break;

			case 'match':
				if (gettype($schema['match']) !== 'string') {
					throw new \Exception('validate_data: invalid match in schema');
				}
				if (@preg_match($schema['match'], '') === false) {
					throw new \Exception('validate_data: invalid match in schema');
				}
				break;

			default:
				throw new \Exception('validate_data: unexpected key in schema: ' . $key);
		}
	}

	// Throw error if there's no type in schema
	if (!array_key_exists('type', $schema)) {
		throw new \Exception('validate_data: no type in schema');
	}

	// required defaults to false if not set
	if (!array_key_exists('required', $schema)) $schema['required'] = false;

	// Attempt to coerce the data to the expected type
	if (get_data_type($data) !== $schema['type']) {
		switch ($schema['type']) {
			case 'string':
				if (is_numeric($data)) {
					$data = strval($data);
					$isFixed = true;
				} elseif (is_bool($data)) {
					$data = $data ? 'true' : 'false';
					$isFixed = true;
				}
				break;

			case 'integer':
				if (is_numeric($data) && intval($data) == floatval($data)) {
					$data = intval($data);
					$isFixed = true;
				}
				break;

			case 'float':
				if (is_numeric($data)) {
					$data = floatval($data);
					$isFixed = true;
				}
				break;

			case 'boolean':
				if ($data === 0 || $data === '0' || $data === 'false') {
					$data = false;
					$isFixed = true;
				} elseif ($data === 1 || $data === '1' || $data === 'true') {
					$data = true;
					$isFixed = true;
				}
				break;
		}
	}

	// Check if the received data type matches the expected type
	if (get_data_type($data) !== $schema['type']) {
		// Add the key to the mistyped array
		$mistyped[] = $field;

		// Attempt to fix by using the default value if available
		if (array_key_exists('default', $schema)) {
			$data = $schema['default'];
			$fixed[] = $field;
			$isFixed = true;
		} else {
			$isValid = false;
		}
	} else {
		// Handle allowed values
		if (in_array($schema['type'], ['string', 'integer', 'float'])) {
			if (array_key_exists('allowed', $schema)) {
				if (!in_array($data, $schema['allowed'])) {
					// Add the key to the invalid array
					$invalid[] = $field;

					// Attempt to fix by using the default value if available
					if (array_key_exists('default', $schema)) {
						$data = $schema['default'];
						$fixed[] = $field;
						$isFixed = true;
					} else {
						$isValid = false;
					}
				}
			}
		}

		// Handle allowed range
		if (in_array($schema['type'], ['integer', 'float'])) {
			if (array_key_exists('range', $schema)) {
				// Check if the value is in the range
				$min = $schema['range'][0];
				$max = $schema['range'][1];

				if (
					($min === null && is_numeric($max) && $data > $max) ||
					($max === null && is_numeric($min) && $data < $min) ||
					(is_numeric($min) && is_numeric($max) && ($data < $min || $data > $max))
				) {
					// Add the key to the invalid array
					$invalid[] = $field;

					// Attempt to fix by using the default value if available
					if (array_key_exists('default', $schema)) {
						$data = $schema['default'];
						$fixed[] = $field;
						$isFixed = true;
					} else {
						$isValid = false;
					}
				}
			}
		}

		// Handle regular expression match
		if ($schema['type'] === 'string') {
			if (array_key_exists('match', $schema)) {
				if (!preg_match($schema['match'], $data)) {
					// Add the key to the invalid array
					$invalid[] = $field;

					// Attempt to fix by using the default value if available
					if (array_key_exists('default', $schema)) {
						$data = $schema['default'];
						$fixed[] = $field;
						$isFixed = true;
					} else {
						$isValid = false;
					}
				}
			}
		}

		// Handle arrays
		if ($schema['type'] === 'array') {
			if (array_key_exists('elems', $schema)) {
				// Array to store valid and fixed elements
				$arrayResult = [];
				foreach ($data as $i => $item) {
					// Handle array data by calling validate_data recursively
					$elemResult = validate_data($item, $schema['elems'], $field . '[' . $i . ']');

					if ($elemResult !== false) {
						foreach ($elemResult['missing'] as $f) $missing[] = $f;
						foreach ($elemResult['mistyped'] as $f) $mistyped[] = $f;
						foreach ($elemResult['invalid'] as $f) $invalid[] = $f;
						foreach ($elemResult['fixed'] as $f) $fixed[] = $f;

						if ($elemResult['isValid']) {
							if ($elemResult['isFixed']) {
								$isFixed = true;
							}
							$arrayResult[] = $elemResult['data'];
						}
						// Note: we do not make the entire result as invalid just because an element in an array is invalid
					}
				}
				// Update data with valid and fixed elements
				$data = $arrayResult;
			}
		}

		// Handle objects
		if ($schema['type'] === 'object') {
			if (array_key_exists('keys', $schema)) {
				foreach ($schema['keys'] as $key => $config) {
					// Check if the key exists in the received data
					if (!array_key_exists($key, $data)) {
						// required defaults to false if not set
						if (!array_key_exists('required', $config)) $config['required'] = false;

						// If the key is required, add it to the missing array
						if ($config['required']) {
							$missing[] = $field . '/' . $key;

							// Attempt to fix by using the default value if available
							if (array_key_exists('default', $config)) {
								$data[$key] = $config['default'];
								$fixed[] = $field . '/' . $key;
								$isFixed = true;
							} else {
								$isValid = false;
								continue;
							}
						}
					}

					// Handle object data by calling validate_data recursively
					$keyResult = false;
					if (array_key_exists($key, $data)) $keyResult = validate_data($data[$key], $config, $field . '/' . $key);

					if ($keyResult !== false) {
						foreach ($keyResult['missing'] as $f) $missing[] = $f;
						foreach ($keyResult['mistyped'] as $f) $mistyped[] = $f;
						foreach ($keyResult['invalid'] as $f) $invalid[] = $f;
						foreach ($keyResult['fixed'] as $f) $fixed[] = $f;

						if ($keyResult['isValid']) {
							if ($keyResult['isFixed']) {
								$isFixed = true;
								$data[$key] = $keyResult['data'];
							}
						} else {
							$isValid = false;
						}
					}
				}
			}
		}
	}

	return [
		'isValid' => $isValid,
		'isFixed' => $isFixed,
		'missing' => $missing,
		'mistyped' => $mistyped,
		'invalid' => $invalid,
		'fixed' => $fixed,
		'data' => $data
	];
}

// Schema: string
define('lqx\util\schema_str', ['type' => 'string']);
// Schema: string, required, no default
define('lqx\util\schema_str_req', ['type' => 'string', 'required' => true]);
// Schema: string, required, default=''
define('lqx\util\schema_str_req_emp', ['type' => 'string', 'required' => true, 'default' => '']);
// Schema: string, required, match: non empty
define('lqx\util\schema_str_req_notemp', ['type' => 'string', 'required' => true, 'match' => '/.+/']);
// Schema: string, required, default=y, allowed=[y,n]
define('lqx\util\schema_str_req_y', ['type' => 'string', 'required' => true, 'default' => 'y', 'allowed' => ['y', 'n']]);
// Schema: string, required, default=n, allowed=[y,n]
define('lqx\util\schema_str_req_n', ['type' => 'string', 'required' => true, 'default' => 'n', 'allowed' => ['y', 'n']]);
// Schema: integer
define('lqx\util\schema_int', ['type' => 'integer']);
// Schema: integer, required, no default
define('lqx\util\schema_int_req', ['type' => 'integer', 'required' => true]);
// Regex to match hex color strings
define('lqx\util\schema_hex_color', ['type' => 'string', 'default' => '', 'match' => '/^#([A-Fa-f0-9]{8}|[A-Fa-f0-9]{6}|[A-Fa-f0-9]{4}|[A-Fa-f0-9]{3})$/']);
// Schema: link
define('lqx\util\schema_data_link', [
	'title' => \lqx\util\schema_str_req_emp,
	'url' => \lqx\util\schema_str_req,
	'target' => \lqx\util\schema_str_req_emp
]);
// Schema: image
define('lqx\util\schema_data_image', [
	'title' => \lqx\util\schema_str_req_emp,
	'filename' => \lqx\util\schema_str_req_emp,
	'url' => \lqx\util\schema_str_req,
	'mime_type' => \lqx\util\schema_str_req_emp,
	'alt' => \lqx\util\schema_str_req,
	'width' => \lqx\util\schema_int,
	'height' => \lqx\util\schema_int,
	'sizes' => [
		'type'	=> 'object',
		'keys' => [
			'small' => \lqx\util\schema_str,
			'small-width' => \lqx\util\schema_int,
			'small-height' => \lqx\util\schema_int,
			'medium' => \lqx\util\schema_str,
			'medium-width' => \lqx\util\schema_int,
			'medium-height' => \lqx\util\schema_int,
			'large' => \lqx\util\schema_str,
			'large-width' => \lqx\util\schema_int,
			'large-height' => \lqx\util\schema_int,
			'thumbnail' => \lqx\util\schema_str,
			'thumbnail-width' => \lqx\util\schema_int,
			'thumbnail-height' => \lqx\util\schema_int
		]
	]
]);
// Schema: video (uploaded video file)
define('lqx\util\schema_data_video', [
	'title' => \lqx\util\schema_str_req_emp,
	'filename' => \lqx\util\schema_str_req_emp,
	'url' => \lqx\util\schema_str_req,
	'mime_type' => \lqx\util\schema_str_req_emp,
	'alt' => \lqx\util\schema_str_req_emp,
	'width' => \lqx\util\schema_int,
	'height' => \lqx\util\schema_int
]);

/**
 * Get the video player and thumbnail URLs from a YouTube or Vimeo URL
 *
 * @param string $url The YouTube or Vimeo URL
 *
 * @return array An array containing the video URL and thumbnail URL
 */
function get_video_urls($url) {
	// Check if the video is from YouTube
	if (preg_match('/^.*(?:(?:youtu\.be\/|v\/|vi\/|u\/\w\/|embed\/|live\/)|(?:(?:watch)?\?v(?:i)?=|\&v(?:i)?=))([^#\&\?]*).*/', $url, $match)) {
		$youtube_id = $match[1];
		if ($youtube_id) {
			$url = 'https://www.youtube-nocookie.com/embed/' . $youtube_id . '?rel=0&amp;autoplay=1&amp;mute=1&amp;modestbranding=1';
			$thumbnail = 'https://img.youtube.com/vi/' . $youtube_id . '/hqdefault.jpg';
		}
	}
	// Check if the video is from Vimeo
	elseif (preg_match('/^https?:\/\/(?:www\.)?vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/(?:[^\/]*)\/videos\/|video\/|)(\d+)(?:|\/\?)$/', $url, $match)) {
		$vimeo_id = $match[1];
		if ($vimeo_id) {
			$url = 'https://player.vimeo.com/video/' . $vimeo_id;
			$thumbnail = 'https://vumbnail.com/' . $vimeo_id . '.jpg';
		}
	} else {
		$url = '';
		$thumbnail = '';
	}

	// Return the video URL and thumbnail URL as an array
	return apply_filters('lqx_get_video_urls', ['url' => $url, 'thumbnail' => $thumbnail]);
}

/**
 * Get the breadcrumbs for a page
 * @param int $post_id The post ID for which to get the breadcrumbs. If null, the current post ID will be used.
 * @param string $type The type of breadcrumbs to get. Can be one of the following:
 * 	- 'parent': Parent page breadcrumbs
 * 	- 'category': Category breadcrumbs
 * 	- 'post-type': Post type archive breadcrumbs
 * 	- 'post-type-category': Post type archive and category breadcrumbs
 * @param int $depth The maximum depth of the breadcrumbs
 * @param bool $show_current Whether to show the current page in the breadcrumbs
 * @return array An array containing the breadcrumbs
 * 	- 'title': The title of the breadcrumb
 * 	- 'url': The URL of the breadcrumb
 */
function get_breadcrumbs($post_id = null, $type = 'parent', $depth = 3, $show_current = true) {
	// Get the post ID
	if ($post_id == null) {
		$post_id = get_the_ID();
	}

	// Show current item
	if ($show_current == 'y') {
		$breadcrumbs = [
			[
				'title' => get_the_title($post_id),
				'url' => null
			]
		];
	} else {
		$breadcrumbs = [];
	}

	switch ($type) {
		// Category breadcrumbs
		case 'category':
			$categories = get_the_category($post_id);
			$category = get_category($categories[0]->term_id);
			$parent_cat = $categories[0]->parent;
			for ($i = 1; $i <= $depth; $i++) {
				array_unshift($breadcrumbs, [
					'title' => $category->name,
					'url' => get_category_link($category->term_id)
				]);
				if ($parent_cat !== 0) {
					$category = get_category($parent_cat);
                    $parent_cat = $category->parent;
				} else {
					break;
				}
			}
			break;

		// Post type archive breadcrumbs
		case 'post-type':
			$post_type = get_post_type_object(get_post_type($post_id));
			array_unshift($breadcrumbs, [
				'title' => $post_type->label,
				'url' => get_post_type_archive_link($post_type->name)
			]);
			break;

		// Post type archive and category breadcrumbs
		case 'post-type-category':
			$categories = get_the_category($post_id);
			$category = get_category($categories[0]->term_id);
			$parent_cat = $categories[0]->parent;
			// Start this loop at 2 because of the presence of the post type in the breadcrumbs before this point
			for ($i = 2; $i <= $depth; $i++) {
				array_unshift($breadcrumbs, [
					'title' => $category->name,
					'url' => get_category_link($category->term_id)
				]);
				if ($parent_cat !== 0) {
					$category = get_category($parent_cat);
                    $parent_cat = $category->parent;
				} else {
					break;
				}
			}
			$post_type = get_post_type_object(get_post_type($post_id));
			array_unshift($breadcrumbs, [
				'title' => $post_type->label,
				'url' => get_post_type_archive_link($post_type->name)
			]);
			break;

		// Parent page breadcrumbs
		case 'parent':
		default:
			$parent_page_id = wp_get_post_parent_id($post_id);
			for ($i = 1; $i <= $depth; $i++) {
				if ($parent_page_id !== 0) {
					array_unshift($breadcrumbs, [
						'title' => get_the_title($parent_page_id),
						'url' => get_permalink($parent_page_id)
					]);
					$parent_page_id = wp_get_post_parent_id($parent_page_id);
				} else {
					break;
				}
			}
			break;
	}

	return $breadcrumbs;
}

/**
 * Get the thumbnail image object for a post
 *
 * @param int $post_id The post ID for which to get the thumbnail image object
 *
 * @return array The thumbnail image object
 */
function get_thumbnail_image_object($post_id) {
	// Get the thumbnail ID
	$post_thumbnail_id = get_post_thumbnail_id($post_id);

	// No thumbnail, return null
	if (!$post_thumbnail_id) return null;

	// Get the WP Post object for the thumbnail
	$post = get_post($post_thumbnail_id);

    // Validate post object exists
    if (!$post) {
        return null;
    }

    // Get file path and validate physical file exists
    $file_path = get_attached_file($post_thumbnail_id);
    $file_exists = $file_path && file_exists($file_path);

    // Get mime type once and validate
    $mime_type = get_post_mime_type($post_thumbnail_id);
    $mime_parts = $mime_type ? explode('/', $mime_type) : ['', ''];

	$image = [
		'ID' => $post_thumbnail_id,
		'id' => $post_thumbnail_id,
		'title' => $post->post_title,
        'filename' => $file_exists ? basename($file_path) : '',
        'filesize' => $file_exists ? filesize($file_path) : 0,
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
        'mime_type' => $mime_type,
        'type' => $mime_parts[0],
        'subtype' => $mime_parts[1],
        'icon' => wp_mime_type_icon($mime_type),
        'width' => 0,
        'height' => 0,
		'sizes' => []
	];

    // Get dimensions from full size image
    $full_image = wp_get_attachment_image_src($post_thumbnail_id, 'full');
    if ($full_image && is_array($full_image)) {
        $image['width'] = $full_image[1] ?? 0;
        $image['height'] = $full_image[2] ?? 0;
    }

    // Set the sizes - only if image source is available
    foreach (get_intermediate_image_sizes() as $size) {
        $size_data = wp_get_attachment_image_src($post_thumbnail_id, $size);

        if ($size_data && is_array($size_data)) {
            $image['sizes'][$size] = $size_data[0];
            $image['sizes'][$size . '-width'] = $size_data[1] ?? 0;
            $image['sizes'][$size . '-height'] = $size_data[2] ?? 0;
        }
    }

	return $image;
}

/**
 * Get the breakpoints for the theme
 *
 * @return array The breakpoints for the theme
 */
function get_breakpoints() {
	$breakpoints = json_decode(file_get_contents(get_stylesheet_directory() . '/css/tailwind/breakpoints.json'), true);
	if ($breakpoints === null) {
		$breakpoints = [
			'xs' => [
				'width' => 320,
				'height' => 720
			],
			'sm' => [
				'width' => 480,
				'height' => 1080
			],
			'md' => [
				'width' => 720,
				'height' => 1080
			],
			'lg' => [
				'width' => 1080,
				'height' => 1080
			],
			'xl' => [
				'width' => 1620,
				'height' => 1080
			]
		];
	}
	return $breakpoints;
}

/**
 * Get src, srcset, and sizes HTML attributes for responsive images
 *
 * Generates a string of HTML attributes for responsive image rendering.
 * The browser uses the sizes attribute to determine the expected display
 * width at each viewport, then picks the best srcset entry based on that
 * and the device pixel ratio.
 *
 * Available image crop sizes: xsmall (320), small (640), medium (1280),
 * large (2560), xlarge (3840), full (original).
 *
 * Usage:
 *   <img <?= \lqx\util\get_src_srcset_sizes_attribs($image, 'medium') ?> alt="...">
 *
 * @param array       $image        ACF image field array
 * @param string      $src_size     Image crop size name for the src attribute
 *   (e.g., 'xsmall', 'small', 'medium', 'large', 'xlarge', 'full').
 *   If the requested size is not available, falls back to progressively
 *   larger sizes before using the full original.
 * @param array|null  $size_map     Optional breakpoint => display size mapping.
 *   Values can be numbers (treated as vw, e.g., 50 becomes '50vw') or strings
 *   with units (e.g., '50vw', '800px'). Breakpoints not in the map default to
 *   '85vw', except the largest breakpoint which defaults to
 *   ceil(breakpoint_width x 1.5) in px.
 *
 * @return string HTML attributes string, or empty string on invalid input
 */
function get_src_srcset_sizes_attribs($image, $src_size = 'medium', $size_map = null) {
	if (!is_array($image) || empty($image['url'])) return '';

    if (isset($image['mime_type']) && $image['mime_type'] === 'image/svg+xml') {
        return 'src="' . esc_url($image['url']) . '"';
    }

	$breakpoints = get_breakpoints();
	$bp_names = array_keys($breakpoints);
	$crop_sizes = ['xsmall', 'small', 'medium', 'large', 'xlarge'];

	// Resolve display size for each breakpoint
	// Default: 85vw, except xl = ceil(xl_width × 1.5)px
	$bp_sizes = [];
	foreach ($bp_names as $bp) {
		if ($size_map !== null && isset($size_map[$bp])) {
			$bp_sizes[$bp] = is_numeric($size_map[$bp]) ? $size_map[$bp] . 'vw' : (string) $size_map[$bp];
		} else {
			$bp_sizes[$bp] = ($bp === 'xl') ? ceil($breakpoints['xl']['width'] * 1.5) . 'px' : '85vw';
		}
	}

	// Build sizes attribute — consolidate consecutive breakpoints with the same value
	// Iterates largest-to-smallest; the smallest group becomes the unconditional default
	$groups = [];
	$group_value = null;
	$group_bp = null;

	foreach (array_reverse($bp_names) as $bp) {
		if ($group_value === null || $bp_sizes[$bp] !== $group_value) {
			if ($group_value !== null) {
				$groups[] = ['bp' => $group_bp, 'value' => $group_value];
			}
			$group_value = $bp_sizes[$bp];
		}
		$group_bp = $bp;
	}
	$groups[] = ['bp' => $group_bp, 'value' => $group_value];

	$sizes_parts = [];
	foreach ($groups as $group) {
		if ($group['bp'] === 'xs') {
			$sizes_parts[] = $group['value'];
		} else {
			$sizes_parts[] = '(min-width: ' . $breakpoints[$group['bp']]['width'] . 'px) ' . $group['value'];
		}
	}

	// Build srcset — deduplicate by URL (WordPress serves the original when
	// the image is smaller than the target crop size, producing duplicate URLs)
	$srcset_parts = [];
	$seen_urls = [];

	foreach ($crop_sizes as $name) {
		if (!empty($image['sizes'][$name]) && !empty($image['sizes'][$name . '-width'])) {
			$url = $image['sizes'][$name];
			$width = (int) $image['sizes'][$name . '-width'];
			if ($width > 0 && !isset($seen_urls[$url])) {
				$srcset_parts[] = $url . ' ' . $width . 'w';
				$seen_urls[$url] = true;
			}
		}
	}

	if (!empty($image['url']) && !empty($image['width'])) {
		$width = (int) $image['width'];
		if ($width > 0 && !isset($seen_urls[$image['url']])) {
			$srcset_parts[] = $image['url'] . ' ' . $width . 'w';
		}
	}

	if (empty($srcset_parts)) return '';

	// Resolve src — try the requested size, then progressively larger, then full
	$src = '';
	if ($src_size === 'full') {
		$src = $image['url'];
	} else {
		$start = array_search($src_size, $crop_sizes);
		if ($start !== false) {
			for ($i = $start; $i < count($crop_sizes); $i++) {
				if (!empty($image['sizes'][$crop_sizes[$i]])) {
					$src = $image['sizes'][$crop_sizes[$i]];
					break;
				}
			}
		}
		if (empty($src)) $src = $image['url'];
	}

	return 'src="' . $src . '" srcset="' . implode(', ', $srcset_parts) . '" sizes="' . implode(', ', $sizes_parts) . '"';
}

/**
 * Returns the alt attribute (and role="presentation" when alt is empty) for an img element.
 *
 * Usage: <img <?= \lqx\util\get_src_srcset_sizes_attribs($image) ?> <?= \lqx\util\get_alt_attribs($image['alt']) ?>>
 *
 * @param string $alt The alt text value from an ACF image field or attachment meta.
 *
 * @return string HTML attribute string: alt="..." or alt="" role="presentation"
 */
function get_alt_attribs(string $alt = ''): string {
	$alt = trim($alt);
	if ($alt !== '') return 'alt="' . esc_attr($alt) . '"';
	return 'alt="" role="presentation"';
}

// Applies the same fallback to images rendered by WordPress (the_post_thumbnail, wp_get_attachment_image, etc.)
add_filter('wp_get_attachment_image_attributes', function(array $attr): array {
	if (empty(trim($attr['alt'] ?? ''))) {
		$attr['alt'] = '';
		$attr['role'] = 'presentation';
	}
	return $attr;
});

/**
 * Create a slug from a string
 *
 * @param string $string The string to convert to a slug
 *
 * @param string $delimiter The delimiter to use between words
 *
 * @return string The slug
 */
function slugify($string, $delimiter = '-') {
	// Get the string locale
	$locale = setlocale(LC_ALL, 0);
	// Set locale to UTF-8
	setlocale(LC_ALL, 'en_US.UTF-8');
	// Remove accents
	$slug = iconv('UTF-8', 'ASCII//TRANSLIT', $string);
	// Remove non-alphanumeric characters except spaces, dashes
	$slug = preg_replace('/[^a-zA-Z0-9\s-]/', '', $slug);
	// Replace spaces with delimeter
	$slug = preg_replace('/\s+/', $delimiter, $slug);
	// Convert to lowercase
	$slug = strtolower($slug);
	// Trim delimeter from beginning and end
	$slug = trim($slug, $delimiter);
	// Revert back to the old locale
	setlocale(LC_ALL, $locale);
	return $slug;
}

/**
 * Minify HTML code
 *
 * @param string $html The HTML code to minify
 *
 * @return string The minified HTML code
 */
function minify_html($html) {
	// Remove tabs, new lines, and extra white-spaces
	$html = preg_replace('/\s+/', ' ', $html);

	// Remove HTML comments
	$html = preg_replace('/<!--(.|\s)*?-->/', '', $html);

	// Remove spaces before and after tags
	$html = preg_replace('/\s+</', '<', $html);
	$html = preg_replace('/>\s+/', '>', $html);

	return $html;
}

function calculate_distance($lat, $lng, $address) {
	if ($address !== null) {
		$loc_lat = $address['lat'];
		$loc_lng = $address['lng'];

		//perform comparison
		$earthRadius = 3958.8; // Radius of the Earth in miles

		// Convert degrees to radians
		$lat1 = deg2rad($lat);
		$lng1 = deg2rad($lng);
		$lat2 = deg2rad($loc_lat);
		$lng2 = deg2rad($loc_lng);

		// Haversine formula
		$latDelta = $lat2 - $lat1;
		$lonDelta = $lng2 - $lng1;

		$a = sin($latDelta / 2) ** 2 +
				cos($lat1) * cos($lat2) *
				sin($lonDelta / 2) ** 2;

		$c = 2 * atan2(sqrt($a), sqrt(1 - $a));

		$distance = $earthRadius * $c;
		return $distance;
	}
}

/**
 * Compute the group key and display label for a post given group_by settings.
 *
 * @param int   $post_id   WordPress post ID
 * @param array $s  The full block settings array (group_by and group_by_* keys at top level)
 * @return array ['key' => string, 'label' => string]
 */
function get_group_by_data(int $post_id, array $s): array {
	if (($s['group_by'] ?? 'n') !== 'y') return ['key' => '', 'label' => ''];

	// 1. Fetch raw value from the configured source
	$raw = '';
	switch ($s['group_by_source'] ?? 'acf_field') {
		case 'acf_field':
			$raw = get_field($s['group_by_acf_field'] ?? '', $post_id);
			break;
		case 'post_meta':
			$raw = get_post_meta($post_id, $s['group_by_field_name'] ?? '', true);
			break;
		case 'post_property':
			$raw = get_post_field($s['group_by_post_property'] ?? 'post_date', $post_id);
			break;
		case 'taxonomy':
			$terms = wp_get_post_terms($post_id, $s['group_by_taxonomy'] ?? '', ['fields' => 'names']);
			$raw = (!is_wp_error($terms) && !empty($terms)) ? $terms[0] : '';
			break;
	}

	// 2. Format into key (for sorting/comparing) and label (for display)
	$key = '';
	$label = '';

	switch ($s['group_by_value_type'] ?? 'text') {
		case 'date':
			$ts = is_numeric($raw) ? (int) $raw : strtotime((string) $raw);
			if ($ts) {
				$key   = date($s['group_by_date_key_format']   ?? 'Y-m', $ts);
				$label = date($s['group_by_date_label_format'] ?? 'F Y', $ts);
			}
			break;
		case 'number':
			$key = $label = is_numeric($raw) ? (string) $raw : '';
			break;
		case 'text':
		default:
			$key   = sanitize_key((string) $raw);
			$label = (string) $raw;
			break;
	}

	if ($key === '') return ['key' => '', 'label' => ''];

	// 3. Apply heading template
	$tmpl = $s['group_by_heading_template'] ?? '';
	if ($tmpl !== '') {
		$label = str_replace('{label}', $label, $tmpl);
	}

	return ['key' => $key, 'label' => $label];
}

/**
 * Return the HTML data attribute string for group_by, ready to echo on a <li>.
 * Returns empty string if group_by is not enabled or no key resolved.
 *
 * @param int   $post_id  WordPress post ID
 * @param array $s        The full block settings array (group_by and group_by_* keys at top level)
 * @return string
 */
function get_group_by_attribs(int $post_id, array $s): string {
	$data = get_group_by_data($post_id, $s);
	if ($data['key'] === '') return '';
	return 'data-group-key="' . esc_attr($data['key']) . '" data-group-label="' . esc_attr($data['label']) . '"';
}

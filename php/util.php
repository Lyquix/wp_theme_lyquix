<?php

/**
 * util.php - Utility Functions for PHP
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
 * @param mixed $data The variable to check
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
 *                 of the allowed values. If the string is not one of the allowed values, it will be considered mistyped.
 *
 *              - 'range' (array, optional): Applicable only if 'type' is 'integer' or 'float'. Defines the allowed range
 *                of values by setting a minimum and maximum. If the value is outside the range, it will be considered
 *                mistyped.
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

	// Return false if there's no type in schema
	if (!array_key_exists('type', $schema)) {
		echo 'validate_data: no type in schema';
		return false;
	}

	// The only valid keys in schema are 'type', 'required', 'default', 'keys', and 'elems'
	// Return false if other keys are found
	foreach (array_keys($schema) as $key) {
		if (!in_array($key, ['type', 'required', 'default', 'keys', 'elems', 'allowed', 'range'])) {
			echo 'validate_data: invalid key in schema: ' . $key;
			return false;
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
				if ($data < $schema['range'][0] || $data > $schema['range'][1]) {
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
								$data[$i] = $elemResult['data'];
							}
						} else {
							$isValid = false;
						}
					}
				}
			}
		}

		// Handle objects
		if ($schema['type'] === 'object') {
			if (array_key_exists('keys', $schema)) {
				foreach ($schema['keys'] as $key => $config) {
					// Check if the key exists in the received data
					if (!array_key_exists($key, $data)) {
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
					$keyResult = validate_data($data[$key], $config, $field . '/' . $key);

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

define('LQX_VALIDATE_DATA_SCHEMA_REQUIRED_STRING', [
	'type' => 'string',
	'required' => true,
	'default' => ''
]);
define('LQX_VALIDATE_DATA_SCHEMA_REQUIRED_INTEGER', [
	'type' => 'integer',
	'required' => true,
	'default' => ''
]);
define('LQX_VALIDATE_DATA_SCHEMA_LINK', [
	'title' => LQX_VALIDATE_DATA_SCHEMA_REQUIRED_STRING,
	'url' => LQX_VALIDATE_DATA_SCHEMA_REQUIRED_STRING,
	'target' => LQX_VALIDATE_DATA_SCHEMA_REQUIRED_STRING
]);
define('LQX_VALIDATE_DATA_SCHEMA_IMAGE', [
	'title' => LQX_VALIDATE_DATA_SCHEMA_REQUIRED_STRING,
	'filename' => LQX_VALIDATE_DATA_SCHEMA_REQUIRED_STRING,
	'url' => LQX_VALIDATE_DATA_SCHEMA_REQUIRED_STRING,
	'mime_type' => LQX_VALIDATE_DATA_SCHEMA_REQUIRED_STRING,
	'alt' => LQX_VALIDATE_DATA_SCHEMA_REQUIRED_STRING,
	'width' => LQX_VALIDATE_DATA_SCHEMA_REQUIRED_INTEGER,
	'height' => LQX_VALIDATE_DATA_SCHEMA_REQUIRED_INTEGER,
	'sizes' => [
		'type'	=> 'object',
		'required' => true,
		'keys' => [
			'small' => LQX_VALIDATE_DATA_SCHEMA_REQUIRED_STRING,
			'small-width' => LQX_VALIDATE_DATA_SCHEMA_REQUIRED_INTEGER,
			'small-height' => LQX_VALIDATE_DATA_SCHEMA_REQUIRED_INTEGER,
			'medium' => LQX_VALIDATE_DATA_SCHEMA_REQUIRED_STRING,
			'medium-width' => LQX_VALIDATE_DATA_SCHEMA_REQUIRED_INTEGER,
			'medium-height' => LQX_VALIDATE_DATA_SCHEMA_REQUIRED_INTEGER,
			'large' => LQX_VALIDATE_DATA_SCHEMA_REQUIRED_STRING,
			'large-width' => LQX_VALIDATE_DATA_SCHEMA_REQUIRED_INTEGER,
			'large-height' => LQX_VALIDATE_DATA_SCHEMA_REQUIRED_INTEGER,
			'thumbnail' => LQX_VALIDATE_DATA_SCHEMA_REQUIRED_STRING,
			'thumbnail-width' => LQX_VALIDATE_DATA_SCHEMA_REQUIRED_INTEGER,
			'thumbnail-height' => LQX_VALIDATE_DATA_SCHEMA_REQUIRED_INTEGER
		]
	]
]);
define('LQX_VALIDATE_DATA_SCHEMA_VIDEO_UPLOAD', [
	'title' => LQX_VALIDATE_DATA_SCHEMA_REQUIRED_STRING,
	'filename' => LQX_VALIDATE_DATA_SCHEMA_REQUIRED_STRING,
	'url' => LQX_VALIDATE_DATA_SCHEMA_REQUIRED_STRING,
	'mime_type' => LQX_VALIDATE_DATA_SCHEMA_REQUIRED_STRING,
	'alt' => LQX_VALIDATE_DATA_SCHEMA_REQUIRED_STRING,
	'width' => LQX_VALIDATE_DATA_SCHEMA_REQUIRED_INTEGER,
	'height' => LQX_VALIDATE_DATA_SCHEMA_REQUIRED_INTEGER
]);

/**
 * Get the video player and thumbnail URLs from a YouTube or Vimeo URL
 * @param string $url The YouTube or Vimeo URL
 * @return array An array containing the video URL and thumbnail URL
 */
function get_video_urls($url) {
	// Check if the video is from YouTube
	if (preg_match('/^.*(?:(?:youtu\.be\/|v\/|vi\/|u\/\w\/|embed\/)|(?:(?:watch)?\?v(?:i)?=|\&v(?:i)?=))([^#\&\?]*).*/', $url, $match)) {
		$youtube_id = $match[1];
		if ($youtube_id) {
			$url = 'https://www.youtube.com/embed/' . $youtube_id . '?rel=0&amp;autoplay=1&amp;mute=1&amp;modestbranding=1';
			$thumbnail = 'https://img.youtube.com/vi/' . $youtube_id . '/hqdefauldepth$breadcrumbt.jpg';
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
	return ['url' => $url, 'thumbnail' => $thumbnail];
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
 * Create a slug from a string
 * @param string $string The string to convert to a slug
 * @param string $delimiter The delimiter to use between words
 * @return string The slug
 */
function slugify($string, $delimiter = '-') {
	// Get the string locale
	$locale = setlocale(LC_ALL, 0);

	// Set locale to UTF-8
	setlocale(LC_ALL, 'en_US.UTF-8');

	// Remove accents
	$slug = iconv('UTF-8', 'ASCII//TRANSLIT', $string);

	// Remove non-alphanumeric characters except spaces
	$slug = preg_replace('/[^a-zA-Z0-9\s]/', '', $slug);

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


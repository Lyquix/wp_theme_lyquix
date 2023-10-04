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

// Function to compare software version strings
function version_compare($v1, $v2) {
	$v1 = explode('.', $v1);
	$v2 = explode('.', $v2);

	$v1 = array_pad($v1, max(count($v1), count($v2)), 0);
	$v2 = array_pad($v2, max(count($v1), count($v2)), 0);

	for ($i = 0; $i < count($v1); $i++) {
		if ($v1[$i] > $v2[$i]) return 1; // Version 1 > Version 2
		elseif ($v1[$i] < $v2[$i]) return -1; // Version 1 < Version 2
	}

	// Versions 1 = Version
	return 0;
}

if (PHP_VERSION_ID < 80100) { // PHP 8.1.0
	function array_is_list(array $arr) {
		if ($arr === []) return true;
		return array_keys($arr) === range(0, count($arr) - 1);
	}
}

/**
 * Validates and processes data based on a provided schema.
 *
 * This function checks whether the given data conforms to the specified schema
 * and performs fixes when possible. It can be used to ensure that incoming data
 * adheres to expected formats and requirements.
 *
 * @param array $data    The data to be validated and processed. Must be an associative array.
 * @param array $schema  The schema defines the expected structure and validation rules for the incoming data.
 *
 *              It should be an associative array where each key corresponds to a field in the incoming data,
 *              and the corresponding value is an array containing configuration options for that field.
 *
 *              The structure of each field configuration is as follows:
 *
 *              - 'type' (string, required): Specifies the expected data type for the field. It can be one of the following types:
 *                - 'string': A string data type.
 *                - 'integer': An integer data type.
 *                - 'float': A floating-point number data type.
 *                - 'boolean': A boolean data type (true or false).
 *                - 'array': An array data type. To distinguish between list and associative arrays see the 'itemsType' and 'schema'
 *                   options below.
 *
 *              - 'required' (bool, optional): Indicates whether the field is required. If set to true, the field must exist in the
 *                incoming data, or it will be considered missing. Default is false.
 *
 *              - 'default' (mixed, optional): Provides a default value for the field if it's missing in the incoming data or
 *                the value is of the wrong type.
 *
 *              - 'itemsType' (string, optional): Applicable only if 'type' is 'array'. Specifies the expected data type for elements
 *                in the array. It can have the same data type options as 'type' (e.g., 'string', 'integer', 'boolean', etc.).
 *
 *              - 'schema' (array, optional): Applicable only if 'type' is 'array'. Defines a nested schema for elements within the array.
 *                This nested schema follows the same structure as the main `$schema` and is used to validate the elements within the array.
 *
 * @return array An array containing the validation results and possibly fixed data.
 *               - 'isValid': A boolean indicating whether the data is valid according to the schema.
 *               - 'isFixed': A boolean indicating whether any fixes were applied to the data.
 *               - 'missing': An array listing keys that are missing in the data but required by the schema.
 *               - 'mistyped': An array listing keys whose data types do not match the schema.
 *               - 'fixed': An array listing keys for which fixes were applied.
 *               - 'data': The processed data, which may include fixes if 'isFixed' is true.
 */

function validateData($data, $schema) {
	$missing = [];
	$mistyped = [];
	$fixed = [];
	$isValid = true;
	$isFixed = false;

	foreach ($schema as $key => $config) {
		// Check if the key exists in the received data
		if (!array_key_exists($key, $data)) {
			// If the key is required, add it to the missing array
			if ($config['required']) {
				$missing[] = $key;

				// Attempt to fix by using the default value if available
				if (array_key_exists('default', $config)) {
					$data[$key] = $config['default'];
					$fixed[] = $key;
					$isFixed = true;
				} else {
					$isValid = false;
					continue;
				}
			}
		}

		// Check if the received data type matches the expected type
		if (gettype($data[$key]) !== $config['type']) {
			// Add the key to the mistyped array
			$mistyped[] = $key;

			// Attempt to fix by using the default value if available
			if (array_key_exists('default', $config)) {
				$data[$key] = $config['default'];
				$fixed[] = $key;
				$isFixed = true;
			} else {
				$isValid = false;
				continue;
			}
		}

		// Check if the received data is an array
		if ($config['type'] === 'array') {

			// Handle arrays of primitive types
			if (isset($config['itemsType'])) {
				// Check if the array is a list
				if (!array_is_list($data[$key])) {
					$mistyped[] = $key;

					// Attempt to fix by using the default value if available
					if (array_key_exists('default', $config)) {
						$data[$key] = $config['default'];
						$fixed[] = $key;
						$isFixed = true;
					} else {
						$isValid = false;
						continue;
					}
				}

				// Handle arrays of primitive types
				foreach ($data[$key] as $i => $item) {
					if (gettype($item) !== $config['itemsType']) {
						$mistyped[] = $key . '[' . $i . ']';
						$isFixed = false;
						$isValid = false;
						continue;
					}
				}
			}
			// Handle associative arrays
			elseif (isset($config['schema'])) {
				// Check if the array is a list
				if (array_is_list($data[$key])) {
					$mistyped[] = $key;

					// Attempt to fix by using the default value if available
					if (array_key_exists('default', $config)) {
						$data[$key] = $config['default'];
						$fixed[] = $key;
						$isFixed = true;
					} else {
						$isValid = false;
						continue;
					}
				}

				// Handle nested associative arrays by calling validateData recursively
				$a = validateData($data[$key], $config['schema']);

				if (isset($a['missing'])) {
					foreach ($a['missing'] as $f) {
						$missing[] = $key . '/' . $f;
					}
				}

				if (isset($a['mistyped'])) {
					foreach ($a['mistyped'] as $f) {
						$mistyped[] = $key . '/' . $f;
					}
				}

				if (isset($a['fixed'])) {
					foreach ($a['fixed'] as $f) {
						$fixed[] = $key . '/' . $f;
					}
				}

				if ($a['isValid']) {
					if ($a['isFixed']) {
						$isFixed = true;
						$data[$key] = $a['data'];
					}
				} else {
					$isValid = false;
				}
			}
		}
	}

	return [
		'isValid' => $isValid,
		'isFixed' => $isFixed,
		'missing' => $missing,
		'mistyped' => $mistyped,
		'fixed' => $fixed,
		'data' => $data
	];

}

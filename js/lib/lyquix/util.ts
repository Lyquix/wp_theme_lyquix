/**
 * util.ts - Utility functions
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

import { vars, cfg, log } from './core';

export const util = (() => {

	const init = (customCfg?: object) => {
		// Run only once
		if (vars.util?.init) return;

		// Working variables
		vars.util = {
			init: false
		};

		// Configuration
		cfg.util = {
			enabled: true,
		};

		if (customCfg) cfg.util = jQuery.extend(true, cfg.util, customCfg);

		// Initialize only if enabled
		if (cfg.util.enabled) {
			log('Initializing util');
		}

		// Run only once
		vars.util.init = true;
	};

	/**
	 * Render the outdated browser alert
	 */
	const browserAlert = (browserData) => {
		if ('outdated' in browserData && browserData.outdated) {
			log('Outdated browser', browserData);

			// Load CSS stylesheet
			const css = document.createElement('link');
			css.rel = 'stylesheet';
			css.href = '<?php echo get_template_directory_uri(); ?>/css/browsers.css';
			document.body.appendChild(css);

			// Create alert element
			const elem = jQuery(
				`<section id="browser-alert">
					<h1>Please Update Your Browser</h1>
					<p><strong>You are using an outdated browser.</strong></p>
					<p>Outdated browsers can make your computer unsafe and may not properly work with this website.
					To ensure security, performance, and full functionality, please upgrade to an up-to-date browser.</p>
					<ul></ul>
				</section>`);

			// Cycle through the list of browsers
			Object.keys(browserData.info).forEach((browserCode) => {
				const browser = browserData.info[browserCode];
				const li = jQuery(
					`<li id="${browserCode}">
						<a href="${browser.url}" title="${browser.long_name}" target="_blank">
							<div class="icon"></div>
							<h2>${browser.name}</h2>
						</a>
						<p class="info"><em>${browser.info}</em></p>
						<p class="version">Latest Version: <strong>${browser.version}</strong></p>
						<p class="website"><a href="${browser.url}" title="${browser.long_name}" target="_blank">Visit Official Website</a></p>
					</li>`);
				elem.find('ul').append(li);
			});

			// Append alert to body
			jQuery('body').append(elem);
		}
	};

	/**
	* Function for handling cookies with ease
	* inspired by https://github.com/js-cookie/js-cookie and https://developer.mozilla.org/en-US/docs/Web/API/Document/cookie/Simple_document.cookie_framework
	* lqx.util.cookie(name) to get value of cookie name
	* lqx.util.cookie(name, value) to set cookie name=value
	* lqx.util.cookie(name, value, attributes) to set cookie with additional attributes
	* returns false if no name is passed, returns null if cookie doesn't exist
	* attributes is an array with any of the following keys:
	* maxAge: an integer, number of seconds
	* expires: a Date object
	* path: string
	* domain: string
	* secure: any non-false value
	* httpOnly: any non-false value
	*/
	const cookie = (name, value?, attributes?) => {
		if (!name && !value && !attributes) return false;

		// get cookie
		if (!value && !attributes) {
			return decodeURIComponent(document.cookie.replace(new RegExp('(?:(?:^|.*;)\\s*' + encodeURIComponent(name).replace(/[-\\.+*]/g, '\\$&') + '\\s*\\=\\s*([^;]*).*$)|^.*$'), '$1')) || null;
		}
		// set cookie
		let c = encodeURIComponent(name) + '=' + encodeURIComponent(value);
		if (typeof attributes == 'object') {
			if ('maxAge' in attributes) c += '; max-age=' + parseInt(attributes.maxAge);
			if ('expires' in attributes && attributes.expires instanceof Date) c += '; expires=' + attributes.expires.toUTCString();
			if ('path' in attributes) c += '; path=' + attributes.path;
			if ('domain' in attributes) c += '; domain=' + attributes.domain;
			if ('secure' in attributes) c += '; secure';
			if ('httpOnly' in attributes) c += '; httponly';
		}
		// set cookie
		document.cookie = c;
		return true;
	};

	// A simple hashing function based on FNV-1a (Fowler-Noll-Vo) algorithm
	const hash = (str) => {
		const FNV_PRIME = 0x01000193;
		let hashLow = 0x811c9dc5;
		let hashHigh = 0;

		for (let i = 0; i < str.length; i++) {
			hashLow ^= str.charCodeAt(i);
			hashLow *= FNV_PRIME;
			hashHigh ^= hashLow;
			hashHigh *= FNV_PRIME;
		}

		return (hashHigh >>> 0).toString(36) + (hashLow >>> 0).toString(36);
	};

	// Parses URL parameters
	const parseUrlParams = (url) => {
		const urlParams = {};
		const queryStr = (new URL(url, window.location.href)).search.substr(1);
		if (queryStr != '') {
			const params = queryStr.split('&');
			if (params.length) {
				params.forEach((param) => {
					const pair = param.split('=', 2);
					if (pair.length == 2) urlParams[pair[0]] = decodeURIComponent(pair[1].replace(/\+/g, ' '));
					else urlParams[pair[0]] = null;
				});
			}
		}
		return urlParams;
	};

	// Generates a random string of the specificed length
	const randomStr = (len?: number) => {
		let str = parseInt((Math.random() * 10e16).toString()).toString(36);
		if (typeof len == 'number') {
			while (str.length < len) str += parseInt((Math.random() * 10e16).toString()).toString(36);
			return str.substring(0, len);
		}
		return str;
	};

	/**
	 * Create a slug from a string
	 * @param str the string to slugify
	 * @param delimiter the delimiter to use between words
	 * @returns the slugified string
	 */
	const slugify = (str: string, delimiter: string = '-'): string => {
		// Remove accents
		let slug = str.normalize('NFD').replace(/[\u0300-\u036f]/g, '');

		// Remove non-alphanumeric characters except spaces
		slug = str.replace(/[^a-zA-Z0-9\s]/g, '');

		// Replace spaces with delimiter
		slug = slug.replace(/\s+/g, delimiter);

		// Convert to lowercase
		slug = slug.toLowerCase();

		// Trim delimiter from beginning and end
		slug = slug.replace(new RegExp(`^${delimiter}|${delimiter}$`, 'g'), '');

		return slug;
	};

	// Creates a string using current time - milliseconds
	const timeStr = () => {
		return (new Date()).getTime().toString(36);
	};

	// Creates a unique string using the time string and random string of the specified length
	const uniqueStr = (len?: number) => {
		return timeStr() + randomStr(len);
	};

	// add unique value to the query string of form's action URL, to avoid caching problem
	const uniqueUrl = (sel: string, attrib: string) => {
		const elems = jQuery(sel);
		if (elems.length) {
			log(`Setting unique URLs in ${attrib} for ${sel} ${elems.length} elements`);
			elems.each((index, elem) => {
				const url = jQuery(elem).attr(attrib);
				if (typeof url != 'undefined') {
					jQuery(elem).attr(attrib, url + (url.indexOf('?') !== -1 ? '&' : '?') + uniqueStr());
				}
			});
		}
	};

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
	 *                - 'object': An object data type. To distinguish between arrays and keyed objects see the 'itemsType' and 'schema'
	 *                   options below.
	 *
	 *              - 'required' (bool, optional): Indicates whether the field is required. If set to true, the field must exist in the
	 *                incoming data, or it will be considered missing. Default is false.
	 *
	 *              - 'default' (mixed, optional): Provides a default value for the field if it's missing in the incoming data or
	 *                the value is of the wrong type.
	 *
	 *              - 'itemsType' (string, optional): Applicable only if 'type' is 'object'. Specifies the expected data type for elements
	 *                in the array. It can have the same data type options as 'type' (e.g., 'string', 'integer', 'boolean', etc.).
	 *
	 *              - 'schema' (object, optional): Applicable only if 'type' is 'object'. Defines a nested schema for elements within the object.
	 *                This nested schema follows the same structure as the main `schema` and is used to validate the elements within the object.
	 *
	 * @return array An array containing the validation results and possibly fixed data.
	 *               - 'isValid': A boolean indicating whether the data is valid according to the schema.
	 *               - 'isFixed': A boolean indicating whether any fixes were applied to the data.
	 *               - 'missing': An array listing keys that are missing in the data but required by the schema.
	 *               - 'mistyped': An array listing keys whose data types do not match the schema.
	 *               - 'fixed': An array listing keys for which fixes were applied.
	 *               - 'data': The processed data, which may include fixes if 'isFixed' is true.
	 */

	const validateData = (data: object, schema: object) => {
		const missing: string[] = [];
		const mistyped: string[] = [];
		const fixed: string[] = [];
		let isValid: boolean = true;
		let isFixed: boolean = false;

		for (const key in schema) {
			const config = schema[key];

			// Check if the key exists in the received data
			if (!(key in data)) {
				// If the key is required, add it to the missing array
				if (config.required) {
					missing.push(key);

					// Attempt to fix by using the default value if available
					if (config.default !== undefined) {
						data[key] = config.default;
						fixed.push(key);
						isFixed = true;
					} else {
						isValid = false;
						continue;
					}
				}
			}

			// Check if the received data type matches the expected type
			if (typeof data[key] !== config.type) {
				// Add the key to the mistyped array
				mistyped.push(key);

				// Attempt to fix by using the default value if available
				if (config.default !== undefined) {
					data[key] = config.default;
					fixed.push(key);
					isFixed = true;
				} else {
					isValid = false;
					continue;
				}
			}

			// Check if the received data is an array
			if (config.type === 'object') {
				// Handle arrays of primitive types
				if (config.itemsType !== undefined) {
					// Check if the array is a list
					if (!Array.isArray(data[key])) {
						mistyped.push(key);

						// Attempt to fix by using the default value if available
						if (config.default !== undefined) {
							data[key] = config.default;
							fixed.push(key);
							isFixed = true;
						} else {
							isValid = false;
							continue;
						}
					}

					// Handle arrays of primitive types
					for (let i = 0; i < data[key].length; i++) {
						if (typeof data[key][i] !== config.itemsType) {
							mistyped.push(`${key}[${i}]`);

							// Attempt to fix by using the default value if available
							if (config.default !== undefined) {
								data[key][i] = config.default;
								fixed.push(`${key}[${i}]`);
								isFixed = false;
								isValid = false;
								continue;
							}
						}
					}
				}
				// Handle associative arrays
				else if (config.schema !== undefined) {
					// Check if the array is a list
					if (Array.isArray(data[key])) {
						mistyped.push(key);

						// Attempt to fix by using the default value if available
						if (config.default !== undefined) {
							data[key] = config.default;
							fixed.push(key);
							isFixed = true;
						} else {
							isValid = false;
							continue;
						}
					}

					// Handle nested associative arrays by calling validateData recursively
					const nestedResult = validateData(data[key], config.schema);

					nestedResult.missing.forEach(f => missing.push(`${key}/${f}`));

					nestedResult.mistyped.forEach(f => mistyped.push(`${key}/${f}`));

					nestedResult.fixed.forEach(f => fixed.push(`${key}/${f}`));

					if (nestedResult.isValid) {
						if (nestedResult.isFixed) {
							isFixed = true;
							data[key] = nestedResult.data;
						}
					} else {
						isValid = false;
					}
				}
			}
		}

		return {
			isValid,
			isFixed,
			missing,
			mistyped,
			fixed,
			data
		};
	};

	// Compares version strings
	// Returns:
	// 0: equal
	// 1: a > b
	// -1: a < b
	const versionCompare = (a, b) => {
		// If they are equal
		if (a === b) return 0;

		// Split into arrays and get the length of the shortest
		a = String(a).split('.');
		b = String(b).split('.');
		const len = Math.min(a.length, b.length);

		// Loop while the components are equal
		for (let i = 0; i < len; i++) {
			// A bigger than B
			if (parseInt(a[i]) > parseInt(b[i])) return 1;
			// B bigger than A
			if (parseInt(a[i]) < parseInt(b[i])) return -1;
		}

		// If they are still the same, the longer one is greater.
		if (a.length > b.length) return 1;
		if (a.length < b.length) return -1;

		// Otherwise they are the same.
		return 0;
	};

	return {
		init,
		browserAlert,
		cookie,
		hash,
		parseUrlParams,
		randomStr,
		slugify,
		timeStr,
		uniqueStr,
		uniqueUrl,
		validateData,
		versionCompare
	};

})();

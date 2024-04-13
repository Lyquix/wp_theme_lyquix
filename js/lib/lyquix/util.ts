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

import { vars, cfg, log, error } from './core';

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

	const getVideoUrls = (url) => {
		let match, youtubeId, vimeoId, thumbnail;
		const youtubeRegex = /^.*(?:(?:youtu\.be\/|v\/|vi\/|u\/\w\/|embed\/)|(?:(?:watch)?\?v(?:i)?=|\&v(?:i)?=))([^#\&\?]*).*/;
		const vimeoRegex = /^https?:\/\/(?:www\.)?vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/(?:[^\/]*)\/videos\/|video\/|)(\d+)(?:|\/\?)$/;

		// Check if the video is from YouTube
		if ((match = url.match(youtubeRegex))) {
			youtubeId = match[1];
			if (youtubeId) {
				url = 'https://www.youtube.com/embed/' + youtubeId + '?rel=0&amp;autoplay=1&amp;mute=1&amp;modestbranding=1';
				thumbnail = 'https://img.youtube.com/vi/' + youtubeId + '/hqdefault.jpg';
			}
		}
		// Check if the video is from Vimeo
		else if ((match = url.match(vimeoRegex))) {
			vimeoId = match[1];
			if (vimeoId) {
				url = 'https://player.vimeo.com/video/' + vimeoId;
				thumbnail = 'https://vumbnail.com/' + vimeoId + '.jpg';
			}
		} else {
			url = '';
			thumbnail = '';
		}

		// Return the video URL and thumbnail URL as an object
		return { url: url, thumbnail: thumbnail };
	};

	/**
	 * A simple hashing function based on FNV-1a (Fowler-Noll-Vo) algorithm
	 * @param str the string to hash
	 * @returns the hash of the string
	 * @see https://en.wikipedia.org/wiki/Fowler%E2%80%93Noll%E2%80%93Vo_hash_function
	 * Outputs a 128-bit hash (32 hex characters long)
	 */
	const hash = (str) => {
		const FNV_PRIME = 0x01000193;
		const h = [0x811c9dc5, 0x056892cd, 0x6b6b2d4f, 0x458e7388];
		str += 't%V7t6Bu0^sN5zrjAZF*%eAVd49H0DhL';

		for (let i = 0; i < str.length; i++) {
			for (let j = 0; j < 4; j++) {
				if (j == 0) h[j] ^= str.charCodeAt(i);
				else h[j] ^= h[j - 1];
				h[j] *= FNV_PRIME;
			}
		}

		return h.map(v => (v >>> 0).toString(16).padStart(8, '0')).join('');
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
		// Remove non-alphanumeric characters except spaces, dashes
		slug = str.replace(/[^a-zA-Z0-9\s-]/g, '');
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
	 * Utility function to get the data type of a variable as needed by validateData
	 * @param mixed data The variable to check
	 * @return string The data type of the variable
	 */
	const getDataType = (data: any) => {
		// Get the data type
		let type: string = typeof data;

		// Convert the data type to a string that can be used by validateData
		switch (type) {
			case 'boolean':
			case 'string':
				break;

			case 'bigint':
				type = 'integer';
				break;

			case 'number':
				if (data % 1 === 0) type = 'integer';
				else type = 'float';
				break;

			case 'object':
				if (Array.isArray(data)) type = 'array';
				break;

			default:
				type = '';
		}

		return type;
	};

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

	interface validateDataSchema {
		type: string;
		allowed?: any[];
		range?: number[];
		default?: any;
		required?: boolean;
		keys: { [key: string]: validateDataSchema };
		elems: validateDataSchema;
		match?: RegExp;
	}

	const validateData = (data: any, schema: validateDataSchema, field: string = 'root'): {
		isValid: boolean,
		isFixed: boolean,
		missing: string[],
		mistyped: string[],
		invalid: string[],
		fixed: string[],
		data: any
	} | false => {
		const missing: string[] = [];
		const mistyped: string[] = [];
		const invalid: string[] = [];
		const fixed: string[] = [];
		let isValid: boolean = true;
		let isFixed: boolean = false;

		Object.keys(schema).forEach(key => {
			switch (key) {
				case 'type':
					if (!['string', 'integer', 'float', 'boolean', 'array', 'object'].includes(schema.type)) {
						error(`validateData: invalid type ${schema.type} in schema`, schema);
						return false;
					}
					break;

				case 'required':
					if (typeof schema.required !== 'boolean') {
						error('validateData: invalid required value in schema', schema);
					}
					break;

				case 'default':
					break;

				case 'keys':
					if (typeof schema.keys !== 'object') {
						error('validateData: invalid keys value in schema', schema);
					}
					break;

				case 'elems':
					if (typeof schema.elems !== 'object') {
						error('validateData: invalid elems value in schema', schema);
					}
					break;

				case 'allowed':
					if (!Array.isArray(schema.allowed)) {
						error('validateData: invalid allowed value in schema', schema);
					}
					if (schema.allowed.length === 0) {
						error('validateData: empty allowed value in schema', schema);
					}
					break;

				case 'range':
					if (!Array.isArray(schema.range) || schema.range.length !== 2) {
						error('validateData: invalid range value in schema', schema);
					}
					if (schema.range[0] === null && typeof schema.range[1] !== 'number') {
						error('validateData: both range values are null in schema', schema);
					}
					if (schema.range[1] === null && typeof schema.range[0] !== 'number') {
						error('validateData: both range values are null in schema', schema);
					}
					break;

				case 'match':
					if (!(schema.match instanceof RegExp)) {
						error('validateData: invalid match value in schema', schema);
					}
					break;

				default:
					error(`validateData: unexpected key ${key} in schema`, schema);
			}
		});

		// Return false if there's no type in schema
		if (!('type' in schema)) {
			error('validateData: missing type in schema', schema);
		}

		// required defaults to false if not set
		if (!('required' in schema)) schema['required'] = false;

		// Attempt to coerce the data to the expected type
		if (getDataType(data) !== schema.type) {
			switch (schema.type) {
				case 'string':
					if (typeof data === 'number' || typeof data === 'bigint') {
						data = data.toString();
						isFixed = true;
					} else if (typeof data === 'boolean') {
						data = data ? 'true' : 'false';
						isFixed = true;
					}
					break;

				case 'integer':
					if (!isNaN(data) && parseInt(data) == parseFloat(data)) {
						data = parseInt(data);
						isFixed = true;
					}
					break;

				case 'float':
					if (!isNaN(data)) {
						data = parseFloat(data);
						isFixed = true;
					}
					break;

				case 'boolean':
					if (data === 0 || data === '0' || data === 'false') {
						data = false;
						isFixed = true;
					}
					if (data === 1 || data === '1' || data === 'true') {
						data = true;
						isFixed = true;
					}
					break;
			}
		}

		// Check if the received data type matches the expected type
		if (getDataType(data) !== schema.type) {
			// Add the key to the mistyped array
			mistyped.push(field);

			// Attempt to fix by using the default value if available
			if ('default' in schema) {
				data = schema.default;
				fixed.push(field);
				isFixed = true;
			} else {
				isValid = false;
			}
		}
		else {
			// Handle allowed values
			if (['string', 'integer', 'float'].includes(schema.type)) {
				if ('allowed' in schema) {
					// Check if the value is allowed
					if (!schema.allowed.includes(data)) {
						// Add the key to the invalid array
						invalid.push(field);

						// Attempt to fix by using the default value if available
						if ('default' in schema) {
							data = schema.default;
							fixed.push(field);
							isFixed = true;
						} else {
							isValid = false;
						}
					}
				}
			}

			// Handle allowed range
			if (['integer', 'float'].includes(schema.type)) {
				if ('range' in schema) {
					// Check if the value is in the range
					const min = schema.range[0];
					const max = schema.range[1];

					if (
						(min === null && !isNaN(max) && data > max) ||
						(max === null && !isNaN(min) && data < min) ||
						(!isNaN(min) && !isNaN(max) && (data < min || data > max))
					) {
						// Add the key to the invalid array
						invalid.push(field);

						// Attempt to fix by using the default value if available
						if ('default' in schema) {
							data = schema.default;
							fixed.push(field);
							isFixed = true;
						} else {
							isValid = false;
						}
					}
				}
			}

			// Handle regular expression match
			if (schema.type == 'string') {
				if ('match' in schema) {
					// Check if the value matches the regular expression
					if (!schema.match.test(data)) {
						// Add the key to the invalid array
						invalid.push(field);

						// Attempt to fix by using the default value if available
						if ('default' in schema) {
							data = schema.default;
							fixed.push(field);
							isFixed = true;
						} else {
							isValid = false;
						}
					}
				}
			}

			// Handle arrays
			if (schema.type === 'array') {
				if ('elems' in schema) {
					data.forEach((elem, index) => {
						// Handle array data by calling validateData recursively
						const elemResult = validateData(elem, schema.elems, `${field}[${index}]`);

						if (elemResult !== false) {
							elemResult.missing.forEach(f => missing.push(f));
							elemResult.mistyped.forEach(f => mistyped.push(f));
							elemResult.invalid.forEach(f => invalid.push(f));
							elemResult.fixed.forEach(f => fixed.push(f));

							if (elemResult.isValid) {
								if (elemResult.isFixed) {
									isFixed = true;
									data[index] = elemResult.data;
								}
							} else {
								isValid = false;
							}
						}
					});
				}
			}

			// Handle objects
			if (schema.type === 'object') {
				if ('keys' in schema) {
					for (const key in schema.keys) {
						// Check if the key exists in the received data
						if (!(key in data)) {
							// required defaults to false if not set
							if (!('required' in schema.keys[key])) schema.keys[key]['required'] = false;

							// If the key is required, add it to the missing array
							if (schema.keys[key].required) {
								missing.push(key);

								// Attempt to fix by using the default value if available
								if (schema.keys[key].default !== undefined) {
									data[key] = schema.keys[key].default;
									fixed.push(key);
									isFixed = true;
								} else {
									isValid = false;
									continue;
								}
							}
						}

						// Handle object data by calling validateData recursively
						let keyResult: any = false;
						if (key in data) keyResult = validateData(data[key], schema.keys[key], `${field}/${key}`);

						if (keyResult !== false) {
							keyResult.missing.forEach(f => missing.push(f));
							keyResult.mistyped.forEach(f => mistyped.push(f));
							keyResult.invalid.forEach(f => invalid.push(f));
							keyResult.fixed.forEach(f => fixed.push(f));

							if (keyResult.isValid) {
								if (keyResult.isFixed) {
									isFixed = true;
									data[key] = keyResult.data;
								}
							} else {
								isValid = false;
							}
						}
					}
				}
			}
		}

		return {
			isValid,
			isFixed,
			missing,
			mistyped,
			invalid,
			fixed,
			data
		};
	};

	// Schema: string
	const schemaStr = { type: 'string' };
	// Schema: string, required, no default
	const schemaStrReq = { type: 'string', required: true };
	// Schema: string, required, default=''
	const schemaStrReqEmp = { type: 'string', required: true, default: '' };
	// Schema: string, required, match: non empty
	const schemaStrReqNotEmp = { type: 'string', required: true, match: /.+/ };
	// Schema: string, required, default=y, allowed=[y,n]
	const schemaStrReqY = { type: 'string', required: true, default: 'y', allowed: ['y', 'n'] };
	// Schema: string, required, default=n, allowed=[y,n]
	const schemaStrReqN = { type: 'string', required: true, default: 'n', allowed: ['y', 'n'] };
	// Schema: integer
	const schemaInt = { type: 'integer' };
	// Schema: integer, required, no default
	const schemaIntReq = { type: 'integer', required: true };
	// Regex to match hex color strings
	const schemaHexColor = { type: 'string', default: '', match: /^#([A-Fa-f0-9]{8}|[A-Fa-f0-9]{6}|[A-Fa-f0-9]{4}|[A-Fa-f0-9]{3})$/ };

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
		cookie,
		getVideoUrls,
		hash,
		parseUrlParams,
		randomStr,
		slugify,
		timeStr,
		uniqueStr,
		uniqueUrl,
		validateData,
		versionCompare,
		schemaStr,
		schemaStrReq,
		schemaStrReqEmp,
		schemaStrReqNotEmp,
		schemaStrReqY,
		schemaStrReqN,
		schemaInt,
		schemaIntReq,
		schemaHexColor
	};

})();

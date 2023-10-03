/**
 * util.ts - Utility functions
 *
 * @version     2.3.3
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

	/*
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

	// Generates a random string of the specificed length
	const randomStr = (len?: number) => {
		let str = parseInt((Math.random() * 10e16).toString()).toString(36);
		if (typeof len == 'number') {
			while (str.length < len) str += parseInt((Math.random() * 10e16).toString()).toString(36);
			return str.substring(0, len);
		}
		return str;
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

	return {
		init,
		cookie,
		hash,
		randomStr,
		timeStr,
		uniqueStr,
		uniqueUrl,
		versionCompare,
		parseUrlParams
	};

})();

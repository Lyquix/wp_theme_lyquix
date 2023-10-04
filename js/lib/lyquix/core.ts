/**
 * core.ts - Common functions for lqx library
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

interface DynamicObject {
	[key: string]: any;
}

// Working variables
export const vars: DynamicObject = {
	window: jQuery(window),
	document: jQuery(document),
	html: jQuery('html'),
	body: jQuery('body'),
	siteURL: window.location.protocol + '//' + window.location.hostname + (window.location.port != '' ? ':' + window.location.port : ''),
	tmplURL: (() => {
		const a = document.createElement('a');
		a.href = jQuery('script[src*="js/lyquix.js"], script[src*="js/lyquix.min.js"]').attr('src') as string;
		return a.href.slice(0, a.href.indexOf('js/lyquix.'));
	})()
};

// Configuration
export const cfg: DynamicObject = {
	debug: 0
};

// Internal console log/warn/error functions
// Use instead of console.log(), console.warn() and console.error(), use lqx.opts.debug to enable/disable
export function log(...args: any[]) {
	if (cfg.debug > 2) {
		window.console.log(...args);
	}
}

export function warn(...args: any[]) {
	if (cfg.debug > 1) {
		window.console.warn(...args);
	}
}

export function error(...args: any[]) {
	if (cfg.debug > 0) {
		window.console.error(...args);
	}
}

// Return Variable
interface ValidJSON {
	data: unknown;
	err: boolean;
	missing: string[];
	wrong: string[];
	msg: string;
}

// Function to validate JSON data
export function validateData(schema: object, data: unknown, fix: boolean): ValidJSON {

	// Global Defalts
	const defaults = {
		number: 0,
		string: '',
		object: {},
		array: [],
		boolean: false,
	};

	const validJSON: ValidJSON = {
		data: {},
		err: false,
		missing: [],
		wrong: [],
		msg: '',
	};

	// Grab the keys present in the schema
	const schemaKeys = Object.keys(schema);

	// For each key in the schema, check if it is required
	for(const key of schemaKeys){

		// If the key is required and missing in the data, either flag it or fix it
		if(!data[key] && schema[key].hasOwnProperty('required') && schema[key]['required']){
			if(fix){
				schema[key].hasOwnProperty('default') ? data[key] = schema[key]['default'] : data[key] = defaults[schema[key]['type']];
			}
			else{
				validJSON.err = true;
				validJSON.missing.push(key);
			}
		}

		// If the key exists but is of the wrong type, warn the user
		else if(data[key] && typeof(data[key]) != schema[key]['type']){
			const warnmsg = key+' should be '+schema[key]['type']+' but is of type '+typeof(data[key]);
			validJSON.err = true;
			validJSON.wrong.push(warnmsg);
		}

		// If the key exists and is of the right type, but is an object, recursively check its keys
		else if(data[key] && typeof(data[key]) == 'object'){
			const subKeyCheck = validateData(schema[key]['sub_keys'], data[key], fix);
			validJSON.data = subKeyCheck.data;
			validJSON.err = subKeyCheck.err;
			validJSON.missing = validJSON.missing.concat(subKeyCheck.missing);
			validJSON.wrong = validJSON.wrong.concat(subKeyCheck.wrong);
		}
	}

	if(validJSON.err){
		if(validJSON.missing.length) validJSON.msg += 'ERROR: JSON missing key(s): ' + validJSON.missing.join(', ');
		if(validJSON.wrong.length) validJSON.msg += 'Warning: ' + validJSON.wrong.join('\n');
	}
	validJSON.data = data;

	// Return finalized data
	return validJSON;
}

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

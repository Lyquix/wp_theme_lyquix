/**
 * core.ts - Common functions for lqx library
 *
 * @version     3.0.0
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

interface DynamicObject {
	[key: string]: any;
}

// Working variables
export const vars: DynamicObject = {
	window: jQuery(window),
	document: jQuery(document),
	html: jQuery('html'),
	body: jQuery('body')
};

// Configuration
export const cfg: DynamicObject = {
	debug: 0,
	siteURL: (() => {
		let url = jQuery('script[src*="wp-content/themes/lyquix"], script[src*="wp-content/themes/wp_theme_lyquix"]').first().attr('src') ||
			jQuery('link[href*="wp-content/themes/lyquix"], link[href*="wp-content/themes/wp_theme_lyquix"]').first().attr('href') || '';
		if (!url) return null;
		url = (new URL(url, window.location.href)).href;
		return url.slice(0, url.indexOf('wp-content/themes/'));
	})(),
	tmplURL: (() => {
		let url = jQuery('script[src*="wp-content/themes/lyquix"], script[src*="wp-content/themes/wp_theme_lyquix"]').attr('src') ||
			jQuery('link[href*="wp-content/themes/lyquix"], link[href*="wp-content/themes/wp_theme_lyquix"]').attr('href') || '';
		if (!url) return null;
		url = (new URL(url, window.location.href)).href;
		const themeNameStart = url.indexOf('wp-content/themes/') + 'wp-content/themes/'.length;
		const themeNameEnd = url.indexOf('/', themeNameStart);
		return url.slice(0, themeNameEnd + 1);
	})()
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

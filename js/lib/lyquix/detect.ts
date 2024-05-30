/**
 * detect.ts - Detection of device, browser and O/S
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
import { util } from './util';
declare let MobileDetect: (a: string) => void;

/**
 * This module provides detection of device, browser and O/S
 * It exports an object with a method to initialize the detection module.
 *
 * @module detect
 *
 * @param {object} customCfg - Optional custom configuration for the detect module.
 *
 * returns {object} An object with a method to initialize the detection module.
 */
export const detect = (() => {

	const init = (customCfg?: object) => {
		// Run only once
		if (vars.detect?.init) return;

		// Working variables
		vars.detect = {
			init: false,
			mobile: null,
			browser: null,
			os: null,
			urlParams: {}
		};

		// Configuration
		cfg.detect = {
			enabled: true,
			mobile: true,
			browser: true,
			os: true,
			urlParams: true
		};

		if (customCfg) cfg.detect = jQuery.extend(true, cfg.detect, customCfg);

		// Initialize only if enabled
		if (cfg.detect.enabled) {
			log('Initializing detect');
			if (cfg.detect.mobile) detectMobile();
			if (cfg.detect.browser) detectBrowser();
			if (cfg.detect.os) detectOS();
			if (cfg.detect.urlParams) detectUrlParams();
		}

		// Run only once
		vars.detect.init = true;
	};

	// Helper functions to deal with common regex
	const getFirstMatch = (regex) => {
		const match = ua.match(regex);
		return (match && match.length > 1 && match[1]) || '';
	};

	const getSecondMatch = (regex) => {
		const match = ua.match(regex);
		return (match && match.length > 1 && match[2]) || '';
	};

	// Uses the mobile-detect.js library to detect if the browser is a mobile device
	// Adds the classes mobile, phone and tablet to the body tag if applicable
	const detectMobile = () => {
		if (typeof MobileDetect == 'function') {
			const md = new MobileDetect(window.navigator.userAgent);
			const r = {
				mobile: false,
				phone: false,
				tablet: false
			};

			for (const key of Object.keys(r)) {
				r[key] = (md[key]() !== null);
				if (r[key]) vars.body.addClass(key);
			}

			log('Detect mobile', r);
			vars.detect.mobile = r;
			return true;
		}
		else {
			log('MobileDetect library not loaded');
			return false;
		}
	};

	// Detects the browser name, type and version, and sets body classes
	// detects major browsers: IE, Edge, Firefox, Chrome, Safari, Opera, Android
	// based on: https://github.com/ded/bowser
	// list of user agen strings: http://www.webapps-online.com/online-tools/user-agent-strings/dv
	const detectBrowser = () => {
		const ua: string = window.navigator.userAgent;
		let browser;

		if (/opera|opr/i.test(ua)) {
			browser = {
				name: 'Opera',
				type: 'opera',
				version: getFirstMatch(/version\/(\d+(\.\d+)?)/i) || getFirstMatch(/(?:opera|opr)[\s/](\d+(\.\d+)?)/i)
			};
		}
		else if (/msie|trident/i.test(ua)) {
			browser = {
				name: 'Internet Explorer',
				type: 'msie',
				version: getFirstMatch(/(?:msie |rv:)(\d+(\.\d+)?)/i)
			};
		}
		else if (/chrome.+? edge/i.test(ua)) {
			browser = {
				name: 'Microsft Edge',
				type: 'msedge',
				version: getFirstMatch(/edge\/(\d+(\.\d+)?)/i)
			};
		}
		else if (/chrome|crios|crmo/i.test(ua)) {
			browser = {
				name: 'Google Chrome',
				type: 'chrome',
				version: getFirstMatch(/(?:chrome|crios|crmo)\/(\d+(\.\d+)?)/i)
			};
		}
		else if (/firefox/i.test(ua)) {
			browser = {
				name: 'Firefox',
				type: 'firefox',
				version: getFirstMatch(/(?:firefox)[ /](\d+(\.\d+)?)/i)
			};
		}
		else if (/safari/i.test(ua)) {
			browser = {
				name: 'Safari',
				type: 'safari',
				version: getFirstMatch(/safari\/(\d+(\.\d+)?)/i)
			};
		}
		else {
			browser = {
				name: getFirstMatch(/^(.*)\/(.*) /),
				version: getSecondMatch(/^(.*)\/(.*) /)
			};
			browser.type = browser.name.toLowerCase().replace(/\s/g, '');
		}

		// Add classes to body
		if (browser.type && browser.version) {
			// browser type
			vars.body.addClass(browser.type);
			// browser type and major version
			vars.body.addClass(browser.type + '-' + browser.version.split('.')[0]);
			// browser type and full version
			vars.body.addClass(browser.type + '-' + browser.version.replace(/\./g, '-'));
		}

		log('Detect browser', browser);
		vars.detect.browser = browser;

		return true;
	};

	// Detects the O/S name, type and version, and sets body classes
	// Detects major desktop and mobile O/S: Windows, Windows Phone, Mac, iOS, Android, Ubuntu, Fedora, ChromeOS
	// Based on bowser: https://github.com/ded/bowser
	// List of user agent strings: http://www.webapps-online.com/online-tools/user-agent-strings/dv
	const detectOS = () => {
		let os;

		if (/(ipod|iphone|ipad)/i.test(ua)) {
			os = {
				name: 'iOS',
				type: 'ios',
				version: getFirstMatch(/os (\d+([_\s]\d+)*) like mac os x/i).replace(/[_\s]/g, '.')
			};
		}
		else if (/windows phone/i.test(ua)) {
			os = {
				name: 'Windows Phone',
				type: 'windowsphone',
				version: getFirstMatch(/windows phone (?:os)?\s?(\d+(\.\d+)*)/i)
			};
		}
		else if (!(/like android/i.test(ua)) && /android/i.test(ua)) {
			os = {
				name: 'Android',
				type: 'android',
				version: getFirstMatch(/android[ /-](\d+(\.\d+)*)/i)
			};
		}
		else if (/windows nt/i.test(ua)) {
			os = {
				name: 'Windows',
				type: 'windows',
				version: getFirstMatch(/windows nt (\d+(\.\d+)*)/i)
			};
		}
		else if (/mac os x/i.test(ua)) {
			os = {
				name: 'Mac OS X',
				type: 'macosx',
				version: getFirstMatch(/mac os x (\d+([_\s]\d+)*)/i).replace(/[_\s]/g, '.')
			};
		}
		else if (/ubuntu/i.test(ua)) {
			os = {
				name: 'Ubuntu',
				type: 'ubuntu',
				version: getFirstMatch(/ubuntu\/(\d+(\.\d+)*)/i)
			};
		}
		else if (/fedora/i.test(ua)) {
			os = {
				name: 'Fedora',
				type: 'fedora',
				version: getFirstMatch(/fedora\/(\d+(\.\d+)*)/i)
			};
		}
		else if (/CrOS/.test(ua)) {
			os = {
				name: 'Chrome OS',
				type: 'chromeos',
				version: getSecondMatch(/cros (.+) (\d+(\.\d+)*)/i)
			};
		}

		// Add classes to body
		if (os.type && os.version) {
			// os type
			vars.body.addClass(os.type);
			// os type and major version
			vars.body.addClass(os.type + '-' + os.version.split('.')[0]);
			// os type and full version
			vars.body.addClass(os.type + '-' + os.version.replace(/\./g, '-'));
		}

		log('Detect O/S', os);
		vars.detect.os = os;

		return true;
	};

	// Parses URL parameters
	const detectUrlParams = () => {
		vars.detect.urlParams = util.parseUrlParams(window.location.href);
		log('Detect URL params', vars.detect.urlParams);
		return true;
	};

	return Object.defineProperties({
		init
	}, {
		// Set the cfg and vars properties as read-only
		mobile: {
			get() {
				return vars.detect.mobile;
			},
			set() {
				return undefined;
			}
		},
		browser: {
			get() {
				return vars.detect.browser;
			},
			set() {
				return undefined;
			}
		},
		os: {
			get() {
				return vars.detect.os;
			},
			set() {
				return undefined;
			}
		},
		urlParams: {
			get() {
				return vars.detect.urlParams;
			},
			set() {
				return undefined;
			}
		}
	}) as {
		init: (customCfg?: object) => void,
		mobile: string,
		browser: string,
		os: string,
		urlParams: object
	};

})();

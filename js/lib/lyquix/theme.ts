/**
 * theme.ts - Detect system theme (dark/light) and handle user preferences
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
import { store } from './store';

export const theme = (() => {

	const init = (customCfg?: object) => {
		// Run only once
		if (vars.theme?.init) return;

		vars.theme = {
			init: false,
			systemPref: '',
			userPref: ''
		};

		// Default module configuration
		cfg.theme = {
			enabled: true,
		};

		if (customCfg) cfg.theme = jQuery.extend(true, cfg.theme, customCfg);

		// Initialize only if enabled
		if (cfg.theme.enabled) {
			log('Initializing theme');

			// Get system theme preference
			const systemPref = window.matchMedia('(prefers-color-scheme: dark)');
			if (systemPref.matches) vars.theme.systemPref = 'dark';
			else vars.theme.systemPref = 'light';

			// Add listener for system theme changes
			systemPref.addEventListener('change', (e) => {
				if (e.matches) vars.theme.systemPref = 'dark';
				else vars.theme.systemPref = 'light';
				setAttribute();
			});

			// Get previously stored user preference
			const userPref = store.get('lqx.vars.theme.userPref');
			if (userPref) vars.theme.userPref = userPref;

			// Set theme attribute
			setAttribute();
		}

		// Run only once
		vars.theme.init = true;
	};

	const get = () => {
		return vars.theme.userPref ? vars.theme.userPref : vars.theme.systemPref;
	};

	const set = (userPref: 'dark' | 'light') => {
		vars.theme.userPref = userPref;
		setAttribute();
		store.set('lqx.vars.theme.userPref');
	};

	const reset = () => {
		vars.theme.userPref = '';
		setAttribute();
		store.unset('lqx.vars.theme.userPref');
	};

	const setAttribute = () => {
		vars.body.attr('theme', vars.theme.userPref ? vars.theme.userPref : vars.theme.systemPref);
	};

	return Object.defineProperties({
		init,
		get,
		set,
		reset
	}, {
		// Set the cfg and vars properties as read-only
		systemPref: {
			get() {
				return vars.theme.systemPref;
			},
			set() {
				return undefined;
			}
		},
		userPref: {
			get() {
				return vars.theme.userPref;
			},
			set() {
				return undefined;
			}
		}
	}) as {
		init: (customCfg?: object) => void,
		get: () => string,
		set: (userPref: 'dark' | 'light') => void,
		reset: () => void,
		systemPref: string,
		userPref: string
	};

})();

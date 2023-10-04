/**
 * lyquix.ts - Main file for lqx library
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

import { vars, cfg, log, warn, error } from './lib/lyquix/core';
import { analytics } from './lib/lyquix/analytics';
import { detect } from './lib/lyquix/detect';
import { geolocate } from './lib/lyquix/geolocate';
import { mutation } from './lib/lyquix/mutation';
import { store } from './lib/lyquix/store';
import { swipe } from './lib/lyquix/swipe';
import { responsive } from './lib/lyquix/responsive';
import { theme } from './lib/lyquix/theme';
import { util } from './lib/lyquix/util';

declare const lqx;

const version = '3.0.0';

const init = (customCfg) => {
	// Run only once
	if (vars.init) return;

	// Extend default config with custom config
	if (typeof customCfg !== 'object') customCfg = {};

	const mods = [ // Order of initialization is important
		'mutation',
		'store',
		'util',
		'detect',
		'responsive',
		'analytics',
		'geolocate',
		'swipe',
		'theme',
	];

	// Initialize core config
	Object.keys(cfg).forEach((k) => {
		if (k in customCfg) cfg[k] = customCfg[k];
	});

	// ASCII art generated at https://asciiart.club/
	console.log('%c\n' +
		'██      ▐█          ▐█    ▄█████▄▄    ▐█          █▌  █▌  █▄      ▄█▄\n' +
		'██       ██         ██  ██▀      ▀█▄  ▐█          █▌  █▌   ▀█▄   ██▀\n' +
		'██        ▀█▄▄  ▄▄██▀  █▌          █▌ ▐█          █▌  █▌     ██▄█▀\n' +
		'██           ▀██▀▀     █           ██ ▐█          █▌  █▌     ▄███\n' +
		'▐█▄           ▐█       ▀▌      █  ▄█   ██        ██   █▌   ▄██  ▀█▄\n' +
		'  ▀██▄▄▄▄██▀  ▐█        ▀█▄    ██▄▀     ▀██▄▄▄▄██▀    █▌  ██▀     ▀█▄\n' +
		'      ▀▀                        ▀█\n', 'color: #7FA53F');
	const debugLevelMap = ['None', 'Errors', 'Warnings', 'All'];
	console.log(`%cInitializing LYQUIX v${version}\nLogging Level: ${cfg.debug} (${debugLevelMap[cfg.debug]})`,
		'color: #7FA53F; font-size: 1.25em; font-weight: bold');
	log('Custom configuration', customCfg);

	// Initialize modules and pass extended config
	mods.forEach((mod) => {
		lqx[mod].init(mod in customCfg ? customCfg[mod] : {});
	});

	// Run only once
	vars.init = true;
};

const expObj = Object.defineProperties({
	init,
	log,
	warn,
	error,
	/* Modules */
	analytics,
	detect,
	geolocate,
	mutation,
	responsive,
	store,
	swipe,
	util,
	theme
}, {
	// Set the cfg and vars properties as read-only
	cfg: {
		get() {
			return cfg;
		},
		set() {
			return undefined;
		}
	},
	vars: {
		get() {
			return vars;
		},
		set() {
			return undefined;
		}
	},
	version: {
		get() {
			return version;
		},
		set() {
			return undefined;
		}
	}
});

export default expObj;

/**
 * scripts.ts - Main file for $lqx project scripts
 *
 * @version     3.0.0
 * @package     wp_theme_lyquix
 * @author      Lyquix
 * @copyright   Copyright (C) 2015 - 2024 Lyquix
 * @license     GNU General Public License version 2 or later
 * @link        https://github.com/Lyquix/wp_theme_lyquix
 */

import { vars, cfg } from './lib/scripts/core';
import { module } from './custom/scripts/module.dist'; // Remove the sample module
// Import all the custom modules from js/custom/scripts

declare const $lqx;
declare const lqx;

// Declare other variables as needed

const init = (customCfg) => {
	// Run only once
	if (vars.init) return;

	// Extend default config with custom config
	if (typeof customCfg !== 'object') customCfg = {};

	const mods = [
		'module' // Remove the sample module
		//Add all the custom modules in the appropriate order for initialization
	];

	console.log('Initializing scripts');
	lqx.log('Custom configuration', customCfg);

	// Initialize modules and pass extended config
	mods.forEach((mod) => {
		$lqx[mod].init(mod in customCfg ? customCfg[mod] : {});
	});

	// Trigger the $lqxready event
	lqx.vars.document.trigger('$lqxready');
	lqx.log('$lqxready Event');

	// Run only once
	vars.init = true;
};

// A ready utility function that works like jQuery(document).ready()
const ready = (callback) => {
	if (vars.init === true) {
		callback();
	} else {
		vars.document.on('$lqxready', callback);
	}
};

const expObj = Object.defineProperties({
	init,
	ready,
	/* Modules */
	module // Remove the sample module
	// Add all the custom modules
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
	}
});

export default expObj;

/**
 * module.dist.ts - my first module
 *
 * @version     3.0.0
 * @package     wp_theme_lyquix
 * @author      Lyquix
 * @copyright   Copyright (C) 2015 - 2018 Lyquix
 * @license     GNU General Public License version 2 or later
 * @link        https://github.com/Lyquix/wp_theme_lyquix
 */

import { vars, cfg } from './core';
/**
 * Import other modules as needed
 */

declare const lqx;
/**
 * Declare other variables as needed
 */

export const accordion = (() => { // Change the accordion name

	const init = (customCfg?: object) => {
		// Run only once
		if (vars.accordion?.init) return; // Change the module name

		vars.accordion = { // Change the module name
			init: false,
			/**
			 * Add working variables as needed
			 */
		};

		// Default module configuration
		cfg.accordion = { // Change the module name
			enabled: true
			/**
			 * Add default configuration as needed
			 */
		};

		if (customCfg) cfg.accordion = jQuery.extend(true, cfg.accordion, customCfg);

		// Initialize only if enabled
		if (cfg.accordion.enabled) { // Change the accordion name
			lqx.log('Initializing accordion'); // Change the module name

			/**
			 *
			 * Place here logic that needs to be executed
			 * when the module is first initialized
			 *
			 */
		}

		// Run only once
		vars.accordion.init = true; // Change the module name
	};

	/**
	 *
	 * Add other functions, constants and variables as needed by the module
	 *
	 */

	return {
		init
		/**
		 * Add other functions that need to be exposed
		 */
	};

})();


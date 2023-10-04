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

import { vars, cfg } from '../../lib/scripts/core';
/**
 * Import other modules as needed
 */

declare const lqx;
/**
 * Declare other variables as needed
 */

export const module = (() => { // Change the module name

	const init = (customCfg?: object) => {
		// Run only once
		if (vars.module?.init) return; // Change the module name

		vars.module = { // Change the module name
			init: false
			/**
			 * Add working variables as needed
			 */
		};

		// Default module configuration
		cfg.module = { // Change the module name
			enabled: true
			/**
			 * Add default configuration as needed
			 */
		};

		if (customCfg) cfg.module = jQuery.extend(true, cfg.module, customCfg);

		// Initialize only if enabled
		if (cfg.module.enabled) { // Change the module name
			lqx.log('Initializing module'); // Change the module name

			/**
			 *
			 * Place here logic that needs to be executed
			 * when the module is first initialized
			 *
			 */
		}

		// Run only once
		vars.module.init = true; // Change the module name
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


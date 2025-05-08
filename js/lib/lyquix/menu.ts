/**
 * menu.ts - Lyquix Menu Library
 *
 * @version     3.2.0
 * @package     wp_theme_lyquix
 * @author      Lyquix
 * @copyright   Copyright (C) 2015 - 2024 Lyquix
 * @license     GNU General Public License version 2 or later
 * @link        https://github.com/Lyquix/wp_theme_lyquix
 */

import { vars, cfg, log } from './core';

export const menu = (() => {
	const init = (customCfg?: object) => {
		if (vars.menu?.init) return;

		vars.menu = {
			init: false,
			type: 'basic'
		};

		cfg.menu = {
			enabled: true,
			mobileToggleSelector: '#menu-toggle',
		};

		// Copy default opts and vars
		if (customCfg) cfg.menu = jQuery.extend(true, cfg.menu, customCfg);

		// Initialize only if needed
		if (cfg.menu.enabled) {
			log('Initializing `menu`');

			// Initialize on document ready
			vars.document.ready(() => {
				// Setup listeners for the menu
				setup(jQuery(cfg.menu.mobileToggleSelector));
			});

			vars.menu.init = true;
		}
	};

	const setup = (elems) => {
		jQuery(elems).click(() => {
			jQuery('header').toggleClass('mobile-menu-open');
		});
	};
	return {
		init
	};
})();

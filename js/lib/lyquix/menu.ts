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
			headerSelector: 'header',
			mobileToggleSelector: '#menu-toggle',
			searchToggleSelector: '#search-toggle',
			megaMenuSelector: '.open-side-menu',
			accordionMenuSelector: '.accordion-menu',
			subMenuCloseSelector: '#sub-menu-close',
		};

		// Copy default opts and vars
		if (customCfg) cfg.menu = jQuery.extend(true, cfg.menu, customCfg);

		// Initialize only if needed
		if (cfg.menu.enabled) {
			log('Initializing `menu`');

			// Initialize on document ready
			vars.document.ready(() => {
				// Setup listeners for the menu
				setup(jQuery(cfg.menu.mobileToggleSelector), jQuery(cfg.menu.searchToggleSelector), jQuery(cfg.menu.megaMenuSelector), jQuery(cfg.menu.accordionMenuSelector), jQuery(cfg.menu.subMenuCloseSelector));
			});

			vars.window.on('scroll', function() {
				if (jQuery(this).scrollTop() > 1) {
					jQuery(cfg.menu.headerSelector).addClass('scrolled');
				} else {
					jQuery(cfg.menu.headerSelector).removeClass('scrolled');
					if (jQuery('body').attr('screen') == 'lg' || jQuery('body').attr('screen') == 'xl') {
						jQuery('header').removeClass('mobile-menu-open');
					}
				}
			});
			vars.window.on('screensizechange',function(){
				if (jQuery('body').attr('screen') == 'lg' || jQuery('body').attr('screen') == 'xl') {
					jQuery('header').removeClass('mobile-menu-open');
					jQuery('#search-container').removeClass('open');
					jQuery('header .menu li').removeClass('open');
					jQuery('header .menu li').removeClass('accordion-open');
				}
			});
			vars.menu.init = true;
		}
	};

	const setup = (menuElems) => {
		jQuery(menuElems).click(() => {
			jQuery('header').toggleClass('mobile-menu-open');
		});
		jQuery('#search-container button').click(function(){
			jQuery('#search-container').toggleClass('open');
		});
		jQuery('.open-submenu').click(function(){
			jQuery(this).closest('li').addClass('open');
		});
		jQuery('.return-link').click(function(){
			jQuery(this).closest('li').removeClass('open');
		});
		jQuery('.open-accordion-submenu').click(function(){
			jQuery(this).closest('li').toggleClass('accordion-open');
		});
	};
	return {
		init
	};
})();

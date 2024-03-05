/**
 * accordion.ts - Accordion block functionality
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
import { mutation } from './mutation';
import { analytics } from './analytics';
import { responsive } from './responsive';

export const accordion = (() => { // Change the accordion name

	const init = (customCfg?: object) => {
		// Run only once
		if (vars.accordion?.init) return;

		vars.accordion = {
			init: false
		};

		// Default module configuration
		cfg.accordion = {
			enabled: true,
			accordionSelector: '.lqx-block-accordion > .accordion',
			headerSelector: '.accordion-header',
			panelSelector: '.accordion-panel',
			analytics: {
				enabled: true,
				nonInteraction: true,
				onClose: true 	// Sends event on accordion close
			}
		};

		if (customCfg) cfg.accordion = jQuery.extend(true, cfg.accordion, customCfg);

		// Initialize only if enabled
		if (cfg.accordion.enabled) {
			log('Initializing accordion');

			// Disable analytics if the analytics module is not enabled
			cfg.accordion.analytics.enabled = cfg.analytics.enabled ? cfg.accordion.analytics.enabled : false;
			if (cfg.accordion.analytics.enabled) log('Setting accordions tracking');

			// Initialize accordions
			vars.document.ready(() => {
				// Setup accordions loaded initially on the page
				setup(jQuery(cfg.accordion.accordionSelector));

				// Add a mutation handler for accordions added to the DOM
				mutation.addHandler('addNode', cfg.accordion.accordionSelector, setup);
			});

			// TODO: Check URL hash and open matching accordion panel
		}

		// Run only once
		vars.accordion.init = true;
	};

	const setup = (elems) => {
		if (elems.length) {
			log('Setting up ' + elems.length + ' accordions', elems);

			elems.each((idx, accElem) => {
				// The accordion element
				accElem = jQuery(accElem);

				// Cycle through each panel
				accElem.find(cfg.accordion.headerSelector).each((idx, headerElem) => {
					// The header element
					headerElem = jQuery(headerElem);

					// The panel element
					const panelElem = jQuery('#' + headerElem.attr('id').replace('-header-', '-panel-'));
					// TODO Handle missing panel
					const panelId = panelElem.attr('id');

					// Add click listener
					headerElem.on('click', () => {
						// Open accordion
						if (panelElem.hasClass('closed')) open(panelId);
						// Close accordion
						else close(panelId);
					});
				});
			});
		}
	};

	const open = (panelId) => {
		log('Opening accordion', panelId);

		// The elements
		const panelElem = jQuery('#' + panelId);
		const headerElem = jQuery('#' + panelId.replace('-panel-', '-header-'));
		const accElem = panelElem.parent();
		// TODO Handle missing elements

		// Remove closed class
		panelElem.removeClass('closed');

		// Toggle aria-hidden
		panelElem.attr('aria-hidden', 'false');

		// Toggle aria-expanded
		headerElem.attr('aria-expanded', 'true');

		// Auto scroll top
		const autoScrollScreens = (accElem.attr('data-auto-scroll') || '').split(',');
		// TODO Handle invalid autoScrollScreens

		if (autoScrollScreens.includes(responsive.screen)) {
			// TODO: Auto Scroll functionality
		}

		// Browser history
		if (accElem.attr('data-browser-history') == 'y') {
			// TODO: Browser history functionality
		}

		// Open multiple
		if (accElem.attr('data-open-multiple') == 'n') {
			// Close all other panels
			accElem.find(cfg.accordion.panelSelector).not(panelElem).each((idx, elem) => {
				close(jQuery(elem).attr('id'));
			});
		}

		// Send event for accordion opened
		if (cfg.accordion.analytics.enabled) {
			analytics.sendGAEvent({
				'eventCategory': 'Accordion',
				'eventAction': 'Open',
				'eventLabel': headerElem.text(),
				'nonInteraction': cfg.accordion.analytics.nonInteraction
			});
		}
	};

	const close = function (panelId) {
		log('Closing accordion', panelId);

		// The elements
		const panelElem = jQuery('#' + panelId);
		const headerElem = jQuery('#' + panelId.replace('-panel-', '-header-'));
		// TODO Handle missing elements

		// Add closed class
		panelElem.addClass('closed');

		// Toggle aria-hidden
		panelElem.attr('aria-hidden', 'true');

		// Toggle aria-expanded
		headerElem.attr('aria-expanded', 'false');

		// Send event for accordion opened
		if (cfg.accordion.analytics.enabled && cfg.accordion.analytics.onClose) {
			analytics.sendGAEvent({
				'eventCategory': 'Accordion',
				'eventAction': 'Close',
				'eventLabel': headerElem.text(),
				'nonInteraction': cfg.accordion.analytics.nonInteraction
			});
		}
	};

	return {
		init,
		open,
		close,
		setup
	};

})();


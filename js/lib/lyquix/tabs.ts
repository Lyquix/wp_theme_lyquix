/**
 * tabs.ts - Tabs block functionality
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

declare const lqx;

export const tabs = (() => {

	const init = (customCfg?: object) => {
		// Run only once
		if (vars.tabs?.init) return;

		vars.tabs = {
			init: false
		};

		// Default module configuration
		cfg.tabs = {
			enabled: true,
			tabsSelector: '.lqx-block-tabs > .tabs',
			tabsListSelector: '.tabs-list',
			tabSelector: '.tab',
			tabPanelSelector: '.tab-panel',
			headerSelector: '.accordion-header',
			tabContentSelector: '.tab-content',
			analytics: {
				enabled: true,
				nonInteraction: true,
				onClose: true 	// Sends event on accordion close
			}
		};

		if (customCfg) cfg.tabs = jQuery.extend(true, cfg.tabs, customCfg);

		// Initialize only if enabled
		if (cfg.tabs.enabled) {
			lqx.log('Initializing tabs');

			// Disable analytics if the analytics module is not enabled
			cfg.tabs.analytics.enabled = cfg.analytics.enabled ? cfg.tabs.analytics.enabled : false;
			if (cfg.tabs.analytics.enabled) lqx.log('Setting tabs tracking');

			// Initialize tabs
			vars.document.ready(() => {
				// Setup tabs loaded initially on the page
				setup(jQuery(cfg.tabs.tabsSelector));

				// Add a mutation handler for tabss added to the DOM
				mutation.addHandler('addNode', cfg.tabs.tabsSelector, setup);
			});
		}

		// Run only once
		vars.tabs.init = true; // Change the module name
	};

	const setup = (elems) => {
		if (elems instanceof Node) {
			// Not an array, convert to an array
			elems = [elems];
		}
		else if (elems instanceof jQuery) {
			// Convert jQuery to array
			elems = elems.toArray();
		}

		if (elems.length) {
			log('Setting up ' + elems.length + ' tabs', elems);

			elems.forEach((tabsElem) => {
				// The tabs element
				tabsElem = jQuery(tabsElem);

				// Cycle through each panel
				tabsElem.find(cfg.tabs.headerSelector).forEach((tabElem) => {
					// The tab element
					tabElem = jQuery(tabElem);

					// The panel element
					const panelElem = jQuery('#' + tabElem.attr('id').replace('-tab-', '-panel-'));
					const panelId = panelElem.attr('id');

					// Add click listener
					jQuery(tabElem).on('click', () => {
						// Open tabs
						open(panelId);
					});

					// Accordion-tabs switcher
					if(tabsElem.attr('data-convert-to-accordion') != '') {
						const accordionScreens = (tabsElem.attr('data-convert-to-accordion') || '').split(',');

						// Add listener for screen change
						vars.window.on('screensizechange', () => {
							if(accordionScreens.includes(lqx.responsive.screen)) {
								// Convert to accordion
								// Toggle aria-hidden
								tabsElem.find(cfg.tabs.tabsListSelector).attr('aria-hiddent', 'true');
								tabsElem.find(cfg.tabs.headerSelector).attr('aria-hiddent', 'false');
							}
							else {
								// Convert to tabs
								// Toggle aria-hidden
								tabsElem.find(cfg.tabs.tabsListSelector).attr('aria-hiddent', 'false');
								tabsElem.find(cfg.tabs.headerSelector).attr('aria-hiddent', 'true');
							}
						});
					}
				});
			});
		}
	};

	const open = (panelId) => {
		log('Opening tabs', panelId);

		// The elements
		const panelElem = jQuery('#' + panelId);
		const tabElem = jQuery('#' + panelId.replace('-panel-', '-tab-'));
		const tabsElem = panelElem.parent();

		// Toggle aria-hidden
		panelElem.attr('aria-hidden', 'false');

		// Toggle tabindex
		tabElem.attr('tabindex', '');

		// Toggle aria-selected
		tabElem.attr('aria-selected', 'true');

		// Accordion behavior
		if(tabsElem.attr('data-convert-to-accordion') != '') {
			const accordionScreens = (tabsElem.attr('data-convert-to-accordion') || '').split(',');
			if(accordionScreens.includes(lqx.responsive.screen)) {
				// The elements
				const headerElem = jQuery('#' + panelId.replace('-panel-', '-header-'));
				const contentElem = jQuery('#' + panelId.replace('-panel-', '-content-'));

				// Toggle aria-hidden
				contentElem.attr('aria-hidden', 'false');

				// Toggle aria-expanded
				headerElem.attr('aria-expanded', 'true');
			}
		}

		// Scroll top
		if(tabsElem.attr('data-auto-scroll') != '') {
			const autoScrollScreens = (tabsElem.attr('data-auto-scroll') || '').split(',');
			if(autoScrollScreens.includes(lqx.responsive.screen)) {
				tabElem[0].scrollIntoView({ behavior: 'smooth', block: 'start' });
			}
		}

		// Close all other panels
		tabsElem.find(cfg.tabs.panelSelector).not(panelElem).each((id, elem) => {
			close(jQuery(elem).attr('id'));
		});

		// Send event for tabs opened
		if (cfg.tabs.analytics.enabled) {
			lqx.analytics.sendGAEvent({
				'eventCategory': 'tabs',
				'eventAction': 'Open',
				'eventLabel': tabElem.text(),
				'nonInteraction': cfg.tabs.analytics.nonInteraction
			});
		}
	};

	const close = (panelId) => {
		log('Closing tabs', panelId);

		// The elements
		const panelElem = jQuery('#' + panelId);
		const tabElem = jQuery('#' + panelId.replace('-panel-', '-header-'));
		const tabsElem = panelElem.parent();

		// Toggle aria-hidden
		panelElem.attr('aria-hidden', 'true');

		// Toggle tabindex
		tabElem.attr('tabindex', '-1');

		// Toggle aria-expanded
		tabElem.attr('aria-expanded', 'false');

		// Accordion behavior
		if(tabsElem.attr('data-convert-to-accordion') != '') {
			const accordionScreens = (tabsElem.attr('data-convert-to-accordion') || '').split(',');
			if(accordionScreens.includes(lqx.responsive.screen)) {
				// The elements
				const headerElem = jQuery('#' + panelId.replace('-panel-', '-header-'));
				const contentElem = jQuery('#' + panelId.replace('-panel-', '-content-'));

				// Toggle aria-hidden
				contentElem.attr('aria-hidden', 'true');

				// Toggle aria-expanded
				headerElem.attr('aria-expanded', 'false');
			}
		}

		// Send event for tabs opened
		if (cfg.tabs.analytics.enabled && cfg.tabs.analytics.onClose) {
			lqx.analytics.sendGAEvent({
				'eventCategory': 'tabs',
				'eventAction': 'Close',
				'eventLabel': tabElem.text(),
				'nonInteraction': cfg.tabs.analytics.nonInteraction
			});
		}
	};

	return {
		init,
		open,
		setup
	};

})();


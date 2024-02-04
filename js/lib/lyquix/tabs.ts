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
import { analytics } from './analytics';
import { responsive } from './responsive';

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
				nonInteraction: true
			}
		};

		if (customCfg) cfg.tabs = jQuery.extend(true, cfg.tabs, customCfg);

		// Initialize only if enabled
		if (cfg.tabs.enabled) {
			log('Initializing tabs');

			// Disable analytics if the analytics module is not enabled
			cfg.tabs.analytics.enabled = cfg.analytics.enabled ? cfg.tabs.analytics.enabled : false;
			if (cfg.tabs.analytics.enabled) log('Setting tabs tracking');

			// Initialize tabs
			vars.document.ready(() => {
				// Setup tabs loaded initially on the page
				setup(jQuery(cfg.tabs.tabsSelector));

				// Add a mutation handler for tabss added to the DOM
				mutation.addHandler('addNode', cfg.tabs.tabsSelector, setup);
			});

			// TODO: Check URL hash and open matching tabs panel
		}

		// Run only once
		vars.tabs.init = true;
	};

	const setup = (elems) => {
		if (elems.length) {
			log('Setting up ' + elems.length + ' tabs', elems);

			elems.each((idx, tabsElem) => {
				// The tabs element
				tabsElem = jQuery(tabsElem);

				// Cycle through each tab
				tabsElem.find(cfg.tabs.tabSelector).each((idx, tabElem) => {
					// The tab element
					tabElem = jQuery(tabElem);

					// The panel id
					const panelId = tabElem.attr('id').replace('-tab-', '-panel-');

					// Add click listener
					jQuery(tabElem).on('click', () => {
						// Open tabs
						open(panelId);
					});
				});

				// Convert to accordion?
				const accordionScreens = (tabsElem.attr('data-convert-to-accordion') || '').split(',');
				console.log(accordionScreens);

				if(accordionScreens.length) {
					// Function to enable/disable elements for accordion and tabs
					const accordionTabsSwitch = () => {
						if(accordionScreens.includes(responsive.screen)) {
							// Convert to accordion
							// Toggle aria-hidden
							tabsElem.find(cfg.tabs.tabsListSelector).attr('aria-hidden', 'true');
							tabsElem.find(cfg.tabs.headerSelector).attr('aria-hidden', 'false');
						}
						else {
							// Convert to tabs
							// Toggle aria-hidden
							tabsElem.find(cfg.tabs.tabsListSelector).attr('aria-hidden', 'false');
							tabsElem.find(cfg.tabs.headerSelector).attr('aria-hidden', 'true');
						}
					};

					// Add listener for screen change
					vars.window.on('screensizechange',accordionTabsSwitch);

					// Run the first time
					accordionTabsSwitch();

					// Cycle through each header
					tabsElem.find(cfg.tabs.headerSelector).each((idx, headerElem) => {
						// The tab element
						headerElem = jQuery(headerElem);

						// The panel element
						const panelId = headerElem.attr('id').replace('-header-', '-panel-');

						// Add click listener
						jQuery(headerElem).on('click', () => {
							// Open tabs
							open(panelId);
						});
					});
				}
			});
		}
	};

	const open = (panelId) => {
		log('Opening tab', panelId);

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
		const accordionScreens = (tabsElem.attr('data-convert-to-accordion') || '').split(',');

		if(accordionScreens.includes(responsive.screen)) {
			// The elements
			const headerElem = jQuery('#' + panelId.replace('-panel-', '-header-'));
			const contentElem = jQuery('#' + panelId.replace('-panel-', '-content-'));

			// Toggle aria-hidden
			contentElem.attr('aria-hidden', 'false');

			// Toggle aria-expanded
			headerElem.attr('aria-expanded', 'true');
		}

		// Auto scroll top
		const autoScrollScreens = (tabsElem.attr('data-auto-scroll') || '').split(',');

		if(autoScrollScreens.includes(responsive.screen)) {
			// TODO: Auto Scroll functionality
		}

		// Browser history
		if (tabsElem.attr('data-browser-history') == 'y') {
			// TODO: Browser history functionality
		}

		// Close all other panels
		tabsElem.find(cfg.tabs.tabPanelSelector).not(panelElem).each((idx, elem) => {
			close(jQuery(elem).attr('id'));
		});

		// Send event for tabs opened
		if (cfg.tabs.analytics.enabled) {
			analytics.sendGAEvent({
				'eventCategory': 'tabs',
				'eventAction': 'Open',
				'eventLabel': tabElem.text(),
				'nonInteraction': cfg.tabs.analytics.nonInteraction
			});
		}
	};

	const close = (panelId) => {
		log('Closing tab', panelId);

		// The elements
		const panelElem = jQuery('#' + panelId);
		const tabElem = jQuery('#' + panelId.replace('-panel-', '-tab-'));
		const tabsElem = panelElem.parent();

		console.log(panelId.replace('-panel-', '-tab-'), tabElem);

		// Toggle aria-hidden
		panelElem.attr('aria-hidden', 'true');

		// Toggle tabindex
		tabElem.attr('tabindex', '-1');

		// Toggle aria-selected
		tabElem.attr('aria-selected', 'false');

		// Accordion behavior
		const accordionScreens = (tabsElem.attr('data-convert-to-accordion') || '').split(',');

		if(accordionScreens.includes(responsive.screen)) {
			// The elements
			const headerElem = jQuery('#' + panelId.replace('-panel-', '-header-'));
			const contentElem = jQuery('#' + panelId.replace('-panel-', '-content-'));

			// Toggle aria-hidden
			contentElem.attr('aria-hidden', 'true');

			// Toggle aria-expanded
			headerElem.attr('aria-expanded', 'false');
		}

		// Send event for tabs opened
		if (cfg.tabs.analytics.enabled && cfg.tabs.analytics.onClose) {
			analytics.sendGAEvent({
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


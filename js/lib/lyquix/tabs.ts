/**
 * tabs.ts - Tabs block functionality
 *
 * @version     3.4.0
 * @package     wp_theme_lyquix
 * @author      Lyquix
 * @copyright   Copyright (C) 2015 - 2024 Lyquix
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

import { vars, cfg, log, warn } from './core';
import { mutation } from './mutation';
import { analytics } from './analytics';
import { responsive } from './responsive';

/**
 * Provides functionality for managing and controlling tabbed content on a web page.
 * Enables initialization, opening, closing, and setup of tab elements with customizable configurations.
 * Supports analytics tracking for tab interactions, dynamic conversion to accordion on specific
 * screen sizes, and handling of DOM mutations. Also provides functionality for auto-scrolling.
 *
 * @module tabs
 *
 * @param {object} customCfg - Optional custom configuration for the tabs module.
 *
 * @returns {object} An object with methods to initialize, open, and setup tabs.
 */
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
			subheadingSelector: '.accordion-subheading',
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
					const panelId = getPanel(tabElem).attr('id');
					if (!panelId) {
						warn('Tab panel element not found', tabElem.attr('id'));
						return;
					}

					// Add click listener
					jQuery(tabElem).on('click', () => {
						const closeOnClick = tabsElem.attr('data-close-on-click') === 'y';
						const isOpen = tabElem.attr('aria-selected') === 'true';
						if (closeOnClick && isOpen) {
							close(panelId);
							(tabElem.get(0) as HTMLElement).blur();
						} else open(panelId);
					});

					// Add keyboard navigation listener (WAI-ARIA APG tabs pattern:
					// Left/Right/Up/Down moves focus and activates, Home/End jump to first/last)
					jQuery(tabElem).on('keydown', (e) => {
						const tabsList = tabElem.closest(cfg.tabs.tabsListSelector);
						const allTabs = tabsList.find(cfg.tabs.tabSelector);
						const currentIndex = allTabs.index(tabElem.get(0));

						let newIndex;
						switch (e.key) {
							case 'ArrowRight':
							case 'ArrowDown':
								newIndex = (currentIndex + 1) % allTabs.length;
								break;
							case 'ArrowLeft':
							case 'ArrowUp':
								newIndex = (currentIndex - 1 + allTabs.length) % allTabs.length;
								break;
							case 'Home':
								newIndex = 0;
								break;
							case 'End':
								newIndex = allTabs.length - 1;
								break;
							default:
								return;
						}

						e.preventDefault();

						const newTabElem = jQuery(allTabs.get(newIndex));
						(newTabElem.get(0) as HTMLElement).focus();
						const newPanelId = getPanel(newTabElem).attr('id');
						if (newPanelId) open(newPanelId);
					});
				});

				// Convert to accordion?
				const accordionScreens = (tabsElem.attr('data-convert-to-accordion') || '').split(',').filter(x => ['xs', 'sm', 'md', 'lg', 'xl'].includes(x));

				if(accordionScreens.length) {
					// Function to enable/disable elements for accordion and tabs
					const accordionTabsSwitch = () => {
						if(accordionScreens.includes(responsive.screen)) {
							// Convert to accordion
							// Toggle aria-hidden
							tabsElem.find(cfg.tabs.tabPanelSelector).each((idx, panelElem) => {
								if (jQuery(panelElem).attr('aria-hidden') !== 'false') {
									jQuery(panelElem).find(cfg.tabs.tabContentSelector).attr('aria-hidden', 'true');
								} else {
									jQuery(panelElem).find(cfg.tabs.tabContentSelector).attr('aria-hidden', 'false');
								}
							});
							tabsElem.find(cfg.tabs.tabsListSelector).attr('aria-hidden', 'true');
							tabsElem.find(cfg.tabs.headerSelector).attr('aria-hidden', 'false');
							tabsElem.find(cfg.tabs.subheadingSelector).attr('aria-hidden', 'false');
						}	else {
							// Convert to tabs
							// Toggle aria-hidden
							tabsElem.find(cfg.tabs.tabsListSelector).attr('aria-hidden', 'false');
							tabsElem.find(cfg.tabs.headerSelector).attr('aria-hidden', 'true');
							tabsElem.find(cfg.tabs.subheadingSelector).attr('aria-hidden', 'true');
							tabsElem.find(cfg.tabs.tabPanelSelector).each((idx, panelElem) => {
								if (jQuery(panelElem).find(cfg.tabs.tabContentSelector).attr('aria-hidden') == undefined || jQuery(panelElem).find(cfg.tabs.tabContentSelector).attr('aria-hidden') == ''){
									if (idx == 0) {
										jQuery(panelElem).find(cfg.tabs.tabContentSelector).attr('aria-hidden', 'false');
									} else {
										jQuery(panelElem).find(cfg.tabs.tabContentSelector).attr('aria-hidden', 'true');
									}
								}
								if (jQuery(panelElem).find(cfg.tabs.tabContentSelector).attr('aria-hidden') !== 'false') {
									jQuery(panelElem).attr('aria-hidden', 'true');
								} else {
									jQuery(panelElem).attr('aria-hidden', 'false');
								}
							});
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
						const panelId = headerElem.closest(cfg.tabs.tabPanelSelector).attr('id');
						const panelElem = getAccordionElems(jQuery('#' + panelId)).contentElem;
						// Add click listener
						jQuery(headerElem).on('click', () => {
							// Open accordion, close if clicked on again
							if (panelElem.attr('aria-hidden') === 'true') open(panelId);
							else close(panelId);
						});

						// Add keyboard navigation listener (Down/Up moves focus between headers,
						// Home/End jump to first/last, automatically opening the newly focused
						// panel to match this component's click-to-open behavior)
						jQuery(headerElem).on('keydown', (e) => {
							const allHeaders = tabsElem.find(cfg.tabs.headerSelector);
							const currentIndex = allHeaders.index(headerElem.get(0));

							let newIndex;
							switch (e.key) {
								case 'ArrowDown':
									newIndex = (currentIndex + 1) % allHeaders.length;
									break;
								case 'ArrowUp':
									newIndex = (currentIndex - 1 + allHeaders.length) % allHeaders.length;
									break;
								case 'Home':
									newIndex = 0;
									break;
								case 'End':
									newIndex = allHeaders.length - 1;
									break;
								default:
									return;
							}

							e.preventDefault();

							const newHeaderElem = jQuery(allHeaders.get(newIndex));
							(newHeaderElem.get(0) as HTMLElement).focus();
							open(newHeaderElem.closest(cfg.tabs.tabPanelSelector).attr('id'));
						});
					});
				}
			});
		}
	};

	/**
	 * Find the panel of a tab: the element named by the tab's aria-controls, falling back to the id
	 * conventions of the templates: `{hash}-tab-{n}` -> `{hash}-panel-{n}`, and, for items with an
	 * Item ID, `{id}-tab` -> `{id}-panel`
	 * @param tabElem jQuery object of the tab
	 * @returns jQuery object of the panel (empty if not found)
	 */
	const getPanel = (tabElem) => {
		const tabId = tabElem.attr('id') || '';
		return findElem([
			tabElem.attr('aria-controls'),
			tabId.replace('-tab-', '-panel-'),
			tabId.replace(/-tab$/, '-panel')
		], cfg.tabs.tabPanelSelector);
	};

	/**
	 * Find the tab of a panel: the element named by the panel's aria-labelledby, falling back to the
	 * same id conventions as getPanel
	 * @param panelElem jQuery object of the panel
	 * @returns jQuery object of the tab (empty if not found)
	 */
	const getTab = (panelElem) => {
		const panelId = panelElem.attr('id') || '';
		return findElem([
			panelElem.attr('aria-labelledby'),
			panelId.replace('-panel-', '-tab-'),
			panelId.replace(/-panel$/, '-tab')
		], cfg.tabs.tabSelector);
	};

	/**
	 * Find the accordion header and content elements of a panel (used when tabs convert to accordion)
	 * @param panelElem jQuery object of the panel
	 * @returns object with the headerElem and contentElem jQuery objects (empty if not found)
	 */
	const getAccordionElems = (panelElem) => {
		return {
			headerElem: panelElem.children(cfg.tabs.headerSelector).first(),
			contentElem: panelElem.children(cfg.tabs.tabContentSelector).first()
		};
	};

	/**
	 * Return the first of the given ids that belongs to an element matching the selector
	 * @param ids list of candidate element ids
	 * @param selector selector the element must match
	 * @returns jQuery object of the element (empty if none matches)
	 */
	const findElem = (ids, selector) => {
		for (const id of ids) {
			if (!id) continue;
			const elem = jQuery(document.getElementById(id)).filter(selector);
			if (elem.length) return elem;
		}
		return jQuery();
	};

	const open = (panelId) => {
		log('Opening tab', panelId);

		// The elements
		const panelElem = jQuery('#' + panelId);
		const tabElem = getTab(panelElem);
		const tabsElem = panelElem.parent();
		if (!panelElem.length || !tabElem.length) {
			warn('Tab panel or tab element not found', panelId);
			return;
		}

		// Toggle aria-hidden
		panelElem.attr('aria-hidden', 'false');

		// Toggle tabindex
		tabElem.attr('tabindex', '');

		// Toggle aria-selected
		tabElem.attr('aria-selected', 'true');

		// Accordion behavior
		const accordionScreens = (tabsElem.attr('data-convert-to-accordion') || '').split(',').filter(x => ['xs', 'sm', 'md', 'lg', 'xl'].includes(x));

		if(accordionScreens.includes(responsive.screen)) {
			// The elements
			const { headerElem, contentElem } = getAccordionElems(panelElem);
			if (headerElem.length && contentElem.length) {
				// Toggle aria-hidden
				contentElem.attr('aria-hidden', 'false');

				// Toggle aria-expanded
				headerElem.attr('aria-expanded', 'true');
			}
		}

		// Auto scroll top
		const autoScrollScreens = (tabsElem.attr('data-auto-scroll') || '').split(',').filter(x => ['xs', 'sm', 'md', 'lg', 'xl'].includes(x));

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
		const tabElem = getTab(panelElem);
		const tabsElem = panelElem.parent();
		if (!panelElem.length || !tabElem.length) {
			warn('Tab panel or tab element not found', panelId);
			return;
		}

		// Toggle aria-hidden
		panelElem.attr('aria-hidden', 'true');

		// Toggle tabindex
		tabElem.attr('tabindex', '-1');

		// Toggle aria-selected
		tabElem.attr('aria-selected', 'false');

		// Accordion behavior
		const accordionScreens = (tabsElem.attr('data-convert-to-accordion') || '').split(',').filter(x => ['xs', 'sm', 'md', 'lg', 'xl'].includes(x));

		if(accordionScreens.includes(responsive.screen)) {
			// The elements
			const { headerElem, contentElem } = getAccordionElems(panelElem);
			if (headerElem.length && contentElem.length) {
				// Toggle aria-hidden
				contentElem.attr('aria-hidden', 'true');

				// Toggle aria-expanded
				headerElem.attr('aria-expanded', 'false');
			}
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


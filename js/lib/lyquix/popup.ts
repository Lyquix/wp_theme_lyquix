/**
 * popup.ts - Popup module functionality
 *
 * @version     3.2.0
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

import { vars, cfg, log, warn, error } from './core';
import { util } from './util';
import { analytics } from './analytics';

declare const dayjs, jQuery;

/**
 * This module provides functionality for popups in a web page.
 * It exports an object with methods to initialize, open, and close popups.
 *
 * @module popup
 *
 * @param {object} customCfg - Optional custom configuration for the popup module.
 *
 * The setup function sets up the popups by fetching the popups from the site options and adding them to the DOM.
 * It also adds click listeners to the close button and sets up the popups to open and close automatically.
 * When a popup is closed, a cookie is set to prevent it from showing again. It also sends analytics events if
 * analytics are enabled.
 *
 * The open function opens a popup by removing the closed class from the section element. It also sends an analytics
 * event if analytics are enabled and the onOpen option is set to true.
 *
 * The close function closes a popup by adding the closed class to the section element. It also sends an analytics
 * event if analytics are enabled and the onClose option is set to true.
 *
 * @returns {object} An object with methods to initialize, open, and close popups.
 */
export const popup = (() => {
	const init = (customCfg?: object) => {
		// Run only once
		if (vars.popup?.init) return;

		vars.popup = {};

		cfg.popup = {
			enabled: true,
			popupModuleSelector: '#lqx-module-popup > .popup',
			analytics: {
				enabled: true,
				nonInteraction: true,
				onOpen: true, // Sends event on popup open
				onClose: true // Sends event on popup dismissal
			}
		};

		// Copy default opts and vars
		if (customCfg) cfg.popup = jQuery.extend(true, cfg.popup, customCfg);

		// Initialize only if enabled
		if (cfg.popup.enabled) {
			log('Initializing `popup`');

			// Disable analytics if the analytics module is not enabled
			cfg.popup.analytics.enabled = cfg.analytics.enabled ? cfg.popup.analytics.enabled : false;
			if (cfg.popup.analytics.enabled) log('Setting popup tracking');

			// Initialize on document ready
			vars.document.ready(function () {
				setup();
			});
		}

		// Run only once
		vars.popup.init = true;
	};

	const setup = function () {
		// Get the DOM elements
		const popupModuleElem = jQuery(cfg.popup.popupModuleSelector);

		// If the popup module is not present, exit
		if (popupModuleElem.length == 0) return;

		log('Setting up popups', popupModuleElem);

		// Get the popup content
		jQuery.ajax({
			cache: false,
			data: {},
			dataType: 'json',
			error: function (xhr, status, errorMsg) {
				error('There has been an error trying to fetch popups from site options', status, errorMsg);
			},
			success: (data) => {
				if (data.length > 0) {
					// Get now
					const now = new Date().getTime();

					// Loop through the popup
					data.forEach((rawPopup) => {
						// Validate popup data
						const p = util.validateData(rawPopup, {
							type: 'object',
							keys: {
								id: util.schemaStrReqNotEmp,
								heading: util.schemaStrReqEmp,
								body: util.schemaStrReqEmp,
								expiration: util.schemaStrReqEmp,
								display_logic: { type: 'string', required: true, default: 'show', allowed: ['show', 'hide'] },
								display_exceptions: {
									type: 'array',
									required: true,
									default: [],
									elems: {
										type: 'object',
										keys: {
											url_pattern: util.schemaStrReqEmp
										}
									}
								},
								css_classes: util.schemaStrReqEmp,
								heading_style: { type: 'string', required: true, default: 'h3', allowed: ['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'] },
								show_delay: util.schemaStrReqEmp,
								hide_delay: util.schemaStrReqEmp,
								dismiss_duration: util.schemaStrReqEmp,
								links: {
									type: 'array',
									required: true,
									default: [],
									elems: {
										type: 'object',
										keys: {
											type: { type: 'string', required: true, default: 'readmore', allowed: ['button', 'readmore'] },
											link: {
												type: 'object',
												required: true,
												default: {},
												keys: {
													url: util.schemaStrReqEmp,
													target: util.schemaStrReqEmp,
													title: util.schemaStrReqEmp
												}
											}
										}
									}
								}
							}
						} as any);

						if (!p || !p.isValid) {
							warn('Invalid popup data, skipping', rawPopup);
							return;
						}
						const popup = p.data;

						// Skip if popup has been dismissed
						if (util.cookie(popup.id) !== null) return;

						// Skip if popup has expired
						if (popup.expiration != '' && now > dayjs(popup.expiration).valueOf()) return;

						// Skip if there's no content
						if (!popup.heading && !popup.body) return;

						// Skip if display logic and exceptions are not met
						let display = true;
						if (popup.display_logic == 'hide') display = false;
						if (Array.isArray(popup.display_exceptions)) {
							popup.display_exceptions.forEach((e) => {
								// Escape special characters and replace wildcard with regex
								const url_pattern = e.url_pattern.replace(/[.+?{}()|[\]\\]/g, '\\$&').replace('*', '.*');

								// Create regex
								const regex = new RegExp('^' + url_pattern + '$');

								// Check if current URL matches the regex
								if (regex.test(window.location.pathname)) display = !display;
							});
						}
						if (!display) return;

						// Prepare the HTML
						let html = `<section
							id="${popup.id}"
							class="closed ${popup.css_classes}"
							data-heading-style="${popup.heading_style}"
							data-show-delay="${popup.show_delay}"
							data-hide-delay="${popup.hide_delay}"
							data-dismiss-duration="${popup.dismiss_duration}">`;
						html += '<button class="close">Close</button>';
						if (popup.heading) {
							html += popup.heading_style == 'p' ? '<p class="title"><strong>' : `<${popup.heading_style}>`;
							html += popup.heading;
							html += popup.heading_style == 'p' ? '</strong></p>' : `</${popup.heading_style}>`;
						}
						html += popup.body;
						if (popup.links.length) {
							html += '<ul class="links">';
							popup.links.forEach((l) => {
								if (l.link.url) {
									html += '<li>';
									html += `<a href="${l.link.url}" class="${l.type == 'button' ? 'button' : 'readmore'}" ${l.link.target ? ' target="_blank"' : ''}>`;
									html += l.link.title ? l.link.title : 'Read More';
									html += '</a>';
									html += '</li>';
								}
							});
							html += '</ul>';
						}
						html += '</section>';

						// Create DOM element and append to popup module
						const popupElem = jQuery(html).appendTo(popupModuleElem);
						log('Popup added', popupElem);

						// Show delay
						if (isNaN(popup.show_delay)) open(popup.id);
						else if (parseInt(popup.show_delay) > 0) {
							window.setTimeout(() => {
								open(popup.id);
							}, parseInt(popup.show_delay) * 1000);
						}

						// Close button listener
						popupElem.find('.close').click(() => {
							close(popup.id);
						});
					});

					if (!popupModuleElem.children().length) {
						// All popups were expired or closed
						popupModuleElem.remove();
						log('All popups were expired or closed');
					}
				} else {
					// No popup
					popupModuleElem.remove();
					log('No popups to show');
				}

			},

			url: cfg.siteURL + '/wp-json/lyquix/v3/popup'
		});
	};

	const open = (popupId) => {
		const popupElem = jQuery('#' + popupId);
		if (!popupElem.length) {
			warn('Popup element not found', popupId);
			return;
		}

		// Popup opened
		popupElem.removeClass('closed');
		log('Popup opened');

		// Hide delay
		const hideDelay = popupElem.attr('data-hide-delay');
		if (hideDelay !== '' && !isNaN(hideDelay)) {
			window.setTimeout(() => {
				close(popupId);
			}, parseInt(hideDelay) * 1000);
		}

		// Send event for popup open
		if (cfg.popup.analytics.enabled && cfg.popup.analytics.onOpen) {
			// Get the heading
			const headingStyle = popupElem.attr('data-heading-style') || 'h3';
			const heading = popupElem.find(headingStyle == 'p' ? 'p.title strong' : headingStyle).text();

			// Send event
			analytics.sendGAEvent({
				'eventCategory': 'Popup',
				'eventAction': 'Open',
				'eventLabel': heading,
				'nonInteraction': cfg.popup.analytics.nonInteraction
			});
		}
	};

	/**
	 * Register a popup entirely from JavaScript — no WP admin entry needed.
	 *
	 * The popup is added to the DOM immediately but only shown automatically when
	 * show_delay >= 0. Call open(id) at any time to show it manually.
	 *
	 * @param data.id              Required. Unique element id (e.g. 'mailchimp-signup').
	 * @param data.heading         Optional heading text.
	 * @param data.body            Optional HTML body content.
	 * @param data.css_classes     Optional space-separated CSS classes.
	 * @param data.heading_style   Tag for the heading ('p'|'h1'–'h6'). Default 'h3'.
	 * @param data.show_delay      Seconds until auto-open. -1 = no auto-open (default).
	 * @param data.hide_delay      Seconds until auto-close. Omit to stay open.
	 * @param data.dismiss_duration Minutes to suppress after dismiss. Omit to always allow re-open.
	 * @param data.links           Optional array of link/button objects.
	 * @returns The jQuery element, or null on error.
	 */
	const register = (data: {
		id: string;
		heading?: string;
		body?: string;
		css_classes?: string;
		heading_style?: 'p' | 'h1' | 'h2' | 'h3' | 'h4' | 'h5' | 'h6';
		show_delay?: number;
		hide_delay?: number;
		dismiss_duration?: number;
		links?: Array<{
			type: 'button' | 'link';
			link: { url: string; target?: string; title?: string };
		}>;
	}) => {
		if (!data?.id) {
			warn('popup.register: id is required');
			return null;
		}

		const escaped = CSS.escape(data.id);
		if (jQuery('#' + escaped).length) {
			warn('popup.register: popup already registered', data.id);
			return jQuery('#' + escaped);
		}

		// Ensure the popup module wrapper exists (the parent setup() removes it when
		// there are no WP-configured popups, so we recreate it if needed)
		let container = jQuery(cfg.popup.popupModuleSelector);
		if (!container.length) {
			let wrapper = jQuery('#lqx-module-popup');
			if (!wrapper.length) {
				wrapper = jQuery('<section id="lqx-module-popup"></section>').appendTo(vars.body);
			}
			container = jQuery('<div class="popup"></div>').appendTo(wrapper);
		}

		const heading_style = data.heading_style || 'h3';
		const css_classes   = data.css_classes || '';
		const show_delay    = data.show_delay ?? -1;
		const hide_delay    = data.hide_delay != null ? String(data.hide_delay) : '';
		const dismiss       = data.dismiss_duration != null ? String(data.dismiss_duration) : '';

		let html = `<section
			id="${data.id}"
			class="closed ${css_classes}"
			data-heading-style="${heading_style}"
			data-show-delay="${show_delay}"
			data-hide-delay="${hide_delay}"
			data-dismiss-duration="${dismiss}">`;
		html += '<button class="close">Close</button>';
		if (data.heading) {
			html += heading_style === 'p' ? '<p class="title"><strong>' : `<${heading_style}>`;
			html += data.heading;
			html += heading_style === 'p' ? '</strong></p>' : `</${heading_style}>`;
		}
		html += data.body || '';
		if (data.links?.length) {
			html += '<ul class="links">';
			data.links.forEach((l) => {
				if (l.link?.url) {
					html += '<li>';
					html += `<a href="${l.link.url}" class="${l.type === 'button' ? 'button' : 'readmore'}"${l.link.target ? ' target="_blank"' : ''}>`;
					html += l.link.title || 'Read More';
					html += '</a></li>';
				}
			});
			html += '</ul>';
		}
		html += '</section>';

		const elem = jQuery(html).appendTo(container);
		log('Popup registered (JS)', data.id);

		elem.find('.close').on('click', () => close(data.id));

		if (show_delay >= 0) {
			if (show_delay === 0) {
				open(data.id);
			} else {
				window.setTimeout(() => open(data.id), show_delay * 1000);
			}
		}

		return elem;
	};

	const close = (popupId) => {
		const popupElem = jQuery('#' + popupId);
		if (!popupElem.length) {
			warn('Popup element not found', popupId);
			return;
		}

		// Skip if it is already closed
		if (popupElem.hasClass('closed')) return;

		const dismissDuration = popupElem.attr('data-dismiss-duration');

		// Set cookies for the closed popup
		util.cookie(popupId, '1', {
			path: '/',
			maxAge: dismissDuration ? dismissDuration * 60 : 60 * 60 * 24 * 365 // 1 year
		});

		// Popup closed
		popupElem.addClass('closed');
		log('Popup closed');

		// Send event for popup closed
		if (cfg.popup.analytics.enabled && cfg.popup.analytics.onClose) {
			// Get the heading
			const headingStyle = popupElem.attr('data-heading-style') || 'h3';
			const heading = popupElem.find(headingStyle == 'p' ? 'p.title strong' : headingStyle).text();

			// Send event
			analytics.sendGAEvent({
				'eventCategory': 'Popup',
				'eventAction': 'Close',
				'eventLabel': heading,
				'nonInteraction': cfg.popup.analytics.nonInteraction
			});
		}
	};

	return {
		init,
		open,
		close,
		register
	};
})();
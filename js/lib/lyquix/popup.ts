/**
 * popup.ts - Popup module functionality
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

import { vars, cfg, log, error } from './core';
import { util } from './util';
import { analytics } from './analytics';

declare const dayjs, jQuery;

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
			data: {},
			dataType: 'json',
			error: function (xhr, status, errorMsg) {
				error('There has been an error trying to fetch popups from site options', status, errorMsg);
			},
			success: (data) => {
				if (data.length > 0) {
					// TODO Data validation

					// Get now
					const now = new Date().getTime();

					// Loop through the popup
					data.forEach((popup) => {
						// Skip if popup has been dismissed
						if (util.cookie(popup.id) !== null) return;

						// Skip if popup has expired
						if (popup.expiration != '' && now > dayjs(popup.expiration).valueOf()) return;

						// TODO Skip if there's no content

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

			url: vars.siteURL + '/wp-json/wp/v2/options/popup'
		});
	};

	const open = (popupId) => {
		const popupElem = jQuery('#' + popupId);
		// TODO Handle element not found

		// Popup opened
		popupElem.removeClass('closed');
		log('Popup opened');

		// Hide delay
		const hideDelay = popupElem.attr('data-hide-delay');
		if (!isNaN(hideDelay)) {
			window.setTimeout(() => {
				close(popupId);
			}, parseInt(hideDelay) * 1000);
		}

		// Send event for popup open
		if (cfg.popup.analytics.enabled && cfg.popup.analytics.onOpen) {
			// Get the heading
			const headingStyle = popupElem.attr('data-heading-style');
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

	const close = (popupId) => {
		const popupElem = jQuery('#' + popupId);
		// TODO Handle element not found

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
			const headingStyle = popupElem.attr('data-heading-style');
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
		close
	};
})();


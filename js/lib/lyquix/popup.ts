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
					// Get settings
					const headingStyle = popupModuleElem.attr('data-heading-style');

					// Get now
					const now = new Date().getTime();

					// Initialize the popup counter
					let i = 0;

					// Loop through the popup
					data.forEach((popup) => {
						// Skip if popup has been dismissed
						if (util.cookie('popup-' + popup.hash) !== null) return;

						// Skip if popup has expired
						if (popup.expiration != '' && now > dayjs(popup.expiration).valueOf()) return;

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
							id="popup-${i}"
							class="closed ${popup.css_classes}"
							data-show-delay="${popup.show_delay}"
							data-hide-delay="${popup.hide_delay}"
							data-dismiss-duration="${popup.dismiss_duration}">`;
						html += '<button class="close">Close</button>';
						if (popup.heading) {
							html += headingStyle == 'p' ? '<p class="title"><strong>' : `<${headingStyle}>`;
							html += popup.heading;
							html += headingStyle == 'p' ? '</strong></p>' : `</${headingStyle}>`;
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

						// Show and hide delays
						window.setTimeout(() => {
							popupElem.removeClass('closed');
							if (popup.hide_delay != '') {
								window.setTimeout(() => {
									popupElem.addClass('closed');
								}, popup.hide_delay * 1000);
							}
						}, popup.show_delay * 1000);

						// Close button listener
						popupElem.find('.close').click(() => {
							// Set cookies for the closed popup
							util.cookie('popup-' + popup.hash, '1', {
								path: '/',
								maxAge: popup.dismiss_duration ? popup.dismiss_duration * 60 : 60 * 60 * 24 * 365 // 1 year
							});

							// Popup closed
							popupElem.addClass('closed');
							log('Popup closed');

							// Send event for popup closed
							if (cfg.popup.analytics.enabled) {
								analytics.sendGAEvent({
									'eventCategory': 'Popup',
									'eventAction': 'Close',
									'eventLabel': popup.heading,
									'nonInteraction': cfg.popup.analytics.nonInteraction
								});
							}
						});

						i++;
					});

					if (!i) {
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

			url: '/wp-json/wp/v2/options/popup'
		});
	};
	return {
		init: init
	};
})();


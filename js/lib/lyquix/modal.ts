/**
 * modal.ts - Modal module functionality
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

export const modal = (() => {
	const init = (customCfg?: object) => {
		// Run only once
		if (vars.modal?.init) return;

		vars.modal = {};

		cfg.modal = {
			enabled: true,
			modalModuleSelector: '#lqx-module-modal > .modal',
			analytics: {
				enabled: true,
				nonInteraction: true,
				onOpen: true, // Sends event on modal open
				onClose: true // Sends event on modal dismissal
			}
		};

		// Copy default opts and vars
		if (customCfg) cfg.modal = jQuery.extend(true, cfg.modal, customCfg);

		// Initialize only if enabled
		if (cfg.modal.enabled) {
			log('Initializing `modal`');

			// Disable analytics if the analytics module is not enabled
			cfg.modal.analytics.enabled = cfg.analytics.enabled ? cfg.modal.analytics.enabled : false;
			if (cfg.modal.analytics.enabled) log('Setting modal tracking');

			// Initialize on document ready
			vars.document.ready(function () {
				setup();
			});
		}

		// Run only once
		vars.modal.init = true;
	};

	const setup = function () {
		// Get the modal content
		jQuery.ajax({
			data: {},
			dataType: 'json',
			error: function (xhr, status, errorMsg) {
				error('There has been an error trying to fetch modals from site options', status, errorMsg);
			},
			success: (data) => {
				if (data.length > 0) {
					log('Setting up modals');

					// Get now
					const now = new Date().getTime();

					// Loop through the modal
					data.forEach((modal) => {
						// Skip if modal has been dismissed
						if (util.cookie(modal.id) !== null) return;

						// Skip if modal has expired
						if (modal.expiration != '' && now > dayjs(modal.expiration).valueOf()) return;

						// Skip if display logic and exceptions are not met
						let display = true;
						if (modal.display_logic == 'hide') display = false;
						if (Array.isArray(modal.display_exceptions)) {
							modal.display_exceptions.forEach((e) => {
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
						let html = `<dialog
							id="${modal.id}"
							class="${modal.css_classes}"
							data-heading-style="${modal.heading_style}"
							data-show-delay="${modal.show_delay}"
							data-hide-delay="${modal.hide_delay}"
							data-dismiss-duration="${modal.dismiss_duration}">`;
						html += '<button class="close">Close</button>';
						if (modal.heading) {
							html += modal.heading_style == 'p' ? '<p class="title"><strong>' : `<${modal.heading_style}>`;
							html += modal.heading;
							html += modal.heading_style == 'p' ? '</strong></p>' : `</${modal.heading_style}>`;
						}
						html += modal.body;
						if (modal.links.length) {
							html += '<ul class="links">';
							modal.links.forEach((l) => {
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
						html += '</dialog>';

						// Create DOM element and append to modal module
						const modalElem = jQuery(html).appendTo(vars.body);
						log('Modal added', modalElem);

						// Show and hide delays
						if (!modal.show_delay) open(modal.id);
						else if (modal.show_delay > 0) {
							window.setTimeout(() => {
								open(modal.id);
							}, parseInt(modal.show_delay) * 1000);
						}

						// Close button listener
						modalElem.find('.close').click(() => {
							close(modal.id);
						});
					});

					if (!jQuery('[id^=modal-]').length) {
						// All modals were expired or closed
						log('All modals were expired or closed');
					}
				} else {
					// No modal
					log('No modals to show');
				}

			},

			url: '/wp-json/wp/v2/options/modal'
		});
	};

	const open = (modalId) => {
		const modalElem = jQuery('#' + modalId);

		// Modal opened
		modalElem.get(0).showModal();
		log('Modal opened');

		// Hide delay
		const hideDelay = modalElem.attr('data-hide-delay');
		if (hideDelay != '') {
			window.setTimeout(() => {
				close(modalId);
			}, parseInt(hideDelay) * 1000);
		}

		// Send event for modal open
		if (cfg.modal.analytics.enabled && cfg.modal.analytics.onOpen) {
			// Get the heading
			const headingStyle = modalElem.attr('data-heading-style');
			const heading = modalElem.find(headingStyle == 'p' ? 'p.title strong' : headingStyle).text();

			// Send event
			analytics.sendGAEvent({
				'eventCategory': 'Modal',
				'eventAction': 'Open',
				'eventLabel': heading,
				'nonInteraction': cfg.modal.analytics.nonInteraction
			});
		}
	};

	const close = (modalId) => {
		const modalElem = jQuery('#' + modalId);

		// Skip if it is already closed
		if (modalElem.attr('open') === undefined) return;

		const dismissDuration = modalElem.attr('data-dismiss-duration');

		// Set cookies for the closed modal
		util.cookie(modalId, '1', {
			path: '/',
			maxAge: dismissDuration ? dismissDuration * 60 : 60 * 60 * 24 * 365 // 1 year
		});

		// Modal closed
		modalElem.get(0).close();
		log('Modal closed');

		// Send event for modal closed
		if (cfg.modal.analytics.enabled && cfg.modal.analytics.onClose) {
			// Get the heading
			const headingStyle = modalElem.attr('data-heading-style');
			const heading = modalElem.find(headingStyle == 'p' ? 'p.title strong' : headingStyle).text();

			// Send event
			analytics.sendGAEvent({
				'eventCategory': 'Modal',
				'eventAction': 'Close',
				'eventLabel': heading,
				'nonInteraction': cfg.modal.analytics.nonInteraction
			});
		}
	};

	return {
		init,
		open,
		close
	};
})();

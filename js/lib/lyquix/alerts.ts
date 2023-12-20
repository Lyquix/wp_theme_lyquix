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

import { vars, cfg, log, error } from './core';
import { util } from './util';

declare const dayjs, Swiper, jQuery;

export const alerts = (() => {
	const init = (customCfg?: object) => {
		// Run only once
		if (vars.alerts?.init) return;

		vars.alerts = {
			alerts: [],
		};

		cfg.alerts = {
			enabled: true
		};

		// Copy default opts and vars
		if (customCfg) cfg.alerts = jQuery.extend(true, cfg.alerts, customCfg);

		// Initialize only if enabled
		if (cfg.alerts.enabled) {
			log('Initializing `alerts`');

			// Initialize on document ready
			vars.window.ready(function () {
				setup();
			});
		}

		// Run only once
		vars.alerts.init = true;
	};

	const setup = function () {
		// Get the DOM elements
		const alertsModuleElem = jQuery('#lqx-module-alerts');

		// If the alerts module is not present, exit
		if (alertsModuleElem.length == 0) return;

		// Get the alerts content
		jQuery.ajax({
			data: {},
			dataType: 'json',
			error: function (xhr, status, errorMsg) {
				error('There has been an error trying to fetch alerts from site options', status, errorMsg);
			},
			success: (data) => {
				vars.alerts.alerts = data;
				if (data.length > 0) {
					// Get settings
					const autoplay = alertsModuleElem.children('.alerts').attr('data-autoplay') === 'y' ? true : false;
					const autoplayDelay = parseInt(alertsModuleElem.children('.alerts').attr('data-autoplay-delay'));
					const headingStyle = alertsModuleElem.children('.alerts').attr('data-heading-style');

					// Get now
					const now = new Date().getTime();

					// Initialize the alert counter
					let i = 0;

					// Loop through the alerts
					data.forEach((alert) => {
						// Skip if alert has been closed
						if (util.cookie('alert-' + alert.hash) !== null) return;

						// Skip if alert has expired
						if (alert.expiration === '' || now <= dayjs(alert.expiration).valueOf()) {
							let html = '<div id="alert-' + i + '" class="swiper-slide">';
							if (alert.heading) {
								html += headingStyle == 'p' ? '<p><strong>' : `<${headingStyle}>`;
								html += alert.heading;
								html += headingStyle == 'p' ? '</strong></p>' : `</${headingStyle}>`;
							}
							html += alert.body;
							if (alert.link.url) {
								html += `<a href="${alert.link.url}" ${alert.link.target ? ' target="_blank"' : ''}>`;
								html += alert.link.title ? alert.link.title : 'Read More';
								html += '</a>';
							}
							html += '</div>';
							jQuery(html).appendTo(alertsModuleElem.find('.swiper-wrapper'));
							log('Alert added: ' + alert.heading);
							i++;
						}
					});

					if (i > 0) {
						// Show the alerts module
						alertsModuleElem.removeClass('hidden');

						// Remove the controls if there's only one slide
						if (i === 1) {
							alertsModuleElem.find('.controls').remove();
						} else {
							// Swipper options
							const swiperOptions = {
								// Optional parameters
								direction: 'horizontal',
								loop: true,
								slidesPerView: 1,

								// Navigation arrows
								navigation: {
									prevEl: '.swiper-button-prev',
									nextEl: '.swiper-button-next'
								}
							};

							// Setup autoplay if enabled
							if (autoplay == true) {
								swiperOptions['autoplay'] = {
									delay: autoplayDelay,
									pauseOnMouseEnter: true
								};
							}

							// Construct the swiper
							new Swiper('#lqx-module-alerts .swiper', swiperOptions);
						}

						// Close button listener
						alertsModuleElem.find('.close').click(() => {
							// Set cookies for each alert
							vars.alerts.alerts.forEach((alert) => {
								util.cookie('alert-' + alert.hash, '1', { 'path': '/', 'expires': alert.expiration });
							});

							// Alerts closed
							alertsModuleElem.remove();
							log('Alerts closed');
						});

					} else {
						// All alerts were expired or closed
						alertsModuleElem.remove();
						log('All alerts were expired or closed');
					}
				} else {
					// No alerts
					alertsModuleElem.remove();
					log('No alerts to show');
				}

			},

			url: '/wp-json/wp/v2/options/alerts'
		});
	};
	return {
		init: init
	};
})();

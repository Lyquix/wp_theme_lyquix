/**
 * alerts.ts - Alerts module functionality
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

import { vars, cfg, log, warn, error } from './core';
import { util } from './util';
import { analytics } from './analytics';

declare const dayjs, Swiper, jQuery;

/**
 * This module provides functionality for alerts in a web page.
 * It exports an object with a method to initialize the alerts module.
 *
 * @module alerts
 *
 * @param {object} customCfg - Optional custom configuration for the alerts module.
 *
 * The setup function sets up the alerts by fetching the alerts from the site options and adding them to the DOM.
 * It also sets up the Swiper slider for the alerts and adds click listeners to the close and navigation buttons.
 * When an alert is closed, a cookie is set to prevent it from showing again. It also sends analytics events if
 * analytics are enabled.
 *
 * @returns {object} An object with a method to initialize the alerts module.
 */
export const alerts = (() => {
	const init = (customCfg?: object) => {
		// Run only once
		if (vars.alerts?.init) return;

		vars.alerts = {
			alerts: [],
		};

		cfg.alerts = {
			enabled: true,
			alertsModuleSelector: '#lqx-module-alerts > .alerts',
			swiperWrapperSelector: '.swiper-wrapper',
			swiperSelector: '.swiper',
			swiperSlideClass: 'swiper-slide',
			swiperNextSelector: '.swiper-button-next',
			swiperPrevSelector: '.swiper-button-prev',
			paginationSelector: '.swiper-pagination',
			analytics: {
				enabled: true,
				nonInteraction: true,
				onClose: true, // Sends event on alerts close
				onPrevNext: true, // Sends event on prev/next click
				onClick: true // Sends event on click on links and buttons inside an alert
			}
		};

		// Copy default opts and vars
		if (customCfg) cfg.alerts = jQuery.extend(true, cfg.alerts, customCfg);

		// Initialize only if enabled
		if (cfg.alerts.enabled) {
			log('Initializing `alerts`');

			// Disable analytics if the analytics module is not enabled
			cfg.alerts.analytics.enabled = cfg.analytics.enabled ? cfg.alerts.analytics.enabled : false;
			if (cfg.alerts.analytics.enabled) log('Setting alerts tracking');

			// Initialize on document ready
			vars.document.ready(function () {
				setup();
			});
		}

		// Run only once
		vars.alerts.init = true;
	};

	const setup = function () {
		// Get the DOM elements
		const alertsModuleElem = jQuery(cfg.alerts.alertsModuleSelector);

		// If the alerts module is not present, exit
		if (alertsModuleElem.length == 0) return;

		log('Setting up alerts', alertsModuleElem);

		// Validate settings from data attributes
		const s = util.validateData({
			autoplay: alertsModuleElem.attr('data-autoplay') ?? '',
			autoplayDelay: alertsModuleElem.attr('data-autoplay-delay') ?? '',
			pagination: alertsModuleElem.attr('data-show-pagination') ?? '',
			swiperOptionsOverride: alertsModuleElem.attr('data-swiper-options-override') ?? '',
			headingStyle: alertsModuleElem.attr('data-heading-style') ?? ''
		}, {
			type: 'object',
			keys: {
				autoplay: util.schemaStrReqY,
				autoplayDelay: {
					type: 'integer',
					required: true,
					default: 15,
					range: [0, 60]
				},
				pagination: util.schemaStrReqY,
				swiperOptionsOverride: util.schemaStrReqEmp,
				headingStyle: {
					type: 'string',
					required: true,
					default: 'h3',
					allowed: ['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6']
				}
			}
		} as any);

		if (!s || !s.isValid) {
			error('Invalid alerts settings', alertsModuleElem);
			return;
		}
		alertsModuleElem.data('settings', s.data);

		// Get the alerts content
		jQuery.ajax({
			cache: false,
			data: {},
			dataType: 'json',
			error: function (xhr, status, errorMsg) {
				error('There has been an error trying to fetch alerts from site options', status, errorMsg);
			},
			success: (data) => {
				if (data.length > 0) {
					// Get validated settings
					const settings = alertsModuleElem.data('settings');
					const autoplay = settings.autoplay === 'y';
					const autoplayDelay = settings.autoplayDelay;
					const pagination = settings.pagination === 'y';
					const headingStyle = settings.headingStyle;

					// Get now
					const now = new Date().getTime();

					// Loop through the alerts
					data.forEach((rawAlert) => {
						// Validate alert data
						const a = util.validateData(rawAlert, {
							type: 'object',
							keys: {
								id: util.schemaStrReqNotEmp,
								heading: util.schemaStrReqEmp,
								body: util.schemaStrReqEmp,
								start_date: util.schemaStrReqEmp,
								expiration: util.schemaStrReqEmp,
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
						} as any);

						if (!a || !a.isValid) {
							warn('Invalid alert data, skipping', rawAlert);
							return;
						}
						const alert = a.data;

						// Skip if alert has been closed
						if (util.cookie(alert.id) !== null) return;

						// Skip if alert hasn't started yet
						if (alert.start_date != '' && now < dayjs(alert.start_date).valueOf()) return;

						// Skip if alert has expired
						if (alert.expiration != '' && now > dayjs(alert.expiration).valueOf()) return;

						// Skip if alert has no content
						if (!alert.heading && !alert.body) return;

						// Prepare the HTML
						let html = `<section id="${alert.id}" class="${cfg.alerts.swiperSlideClass}">`;
						if (alert.heading) {
							html += headingStyle == 'p' ? '<p class="title"><strong>' : `<${headingStyle}>`;
							html += alert.heading;
							html += headingStyle == 'p' ? '</strong></p>' : `</${headingStyle}>`;
						}
						html += alert.body;
						if (alert.link.url) {
							html += `<a href="${alert.link.url}" ${alert.link.target ? ' target="_blank"' : ''}>`;
							html += alert.link.title ? alert.link.title : 'Read More';
							html += '</a>';
						}
						html += '</section>';

						// Append the alert to the swiper
						jQuery(html).appendTo(alertsModuleElem.find(cfg.alerts.swiperWrapperSelector));
						log('Alert added: ' + alert.heading);

						// Add the alert to the alerts array
						vars.alerts.alerts.push(alert);
					});

					// Get the number of alerts
					const count = alertsModuleElem.find(cfg.alerts.swiperWrapperSelector).children().length;

					if (count > 0) {
						// Show the alerts module
						alertsModuleElem.removeClass('hidden');

						// Remove the controls if there's only one slide
						if (count === 1) {
							alertsModuleElem.find('.controls').remove();
						} else {
							// Swipper options
							let swiperOptions = {
								// Optional parameters
								direction: 'horizontal',
								loop: true,
								slidesPerView: 1,

								// Navigation arrows
								navigation: {
									prevEl: alertsModuleElem.find(cfg.alerts.swiperPrevSelector)[0],
									nextEl: alertsModuleElem.find(cfg.alerts.swiperNextSelector)[0]
								}
							};

							// setup pagination if enabled
							if (pagination == true) {
								swiperOptions['pagination'] = {
                                    el: alertsModuleElem.find(cfg.alerts.paginationSelector)[0],
									clickable: true,
									renderBullet: function (index, className) {
										return '<span class="' + className + '">' + (index + 1) + '</span>';
									}
								}
							};

							// Setup autoplay if enabled
							if (autoplay == true) {
								swiperOptions['autoplay'] = {
									delay: autoplayDelay * 1000,
									pauseOnMouseEnter: true
								};
							}

							// Swiper options override
							if (settings.swiperOptionsOverride) {
								try {
									swiperOptions = jQuery.extend(true, swiperOptions, JSON.parse(settings.swiperOptionsOverride));
								} catch (e) {
									warn('Swiper options override is not valid JSON');
								}
							}

							// Construct the swiper
							new Swiper(cfg.alerts.alertsModuleSelector + ' ' + cfg.alerts.swiperSelector, swiperOptions);
						}

						// Close button listener
						alertsModuleElem.find('.close').click(() => {
							// Set cookies for each alert
							vars.alerts.alerts.forEach((alert) => {
								util.cookie(alert.id, '1', {
									path: '/',
									maxAge: alert.expiration ? dayjs(alert.expiration).diff(dayjs(), 'second') : 60 * 60 * 24 * 365 // 1 year
								});
							});

							// Alerts closed
							alertsModuleElem.remove();
							log('Alerts closed');

							// Send event for alerts closed
							if (cfg.alerts.analytics.enabled) {
								analytics.sendGAEvent({
									'eventCategory': 'Alerts',
									'eventAction': 'Close',
									'eventLabel': '',
									'nonInteraction': cfg.alerts.analytics.nonInteraction
								});
							}
						});

						// Prev/Next button listeners
						alertsModuleElem.find(cfg.alerts.swiperPrevSelector).click(() => {
							// Send event for alerts previous button click
							if (cfg.alerts.analytics.enabled && cfg.alerts.analytics.onPrevNext) {
								analytics.sendGAEvent({
									'eventCategory': 'Alerts',
									'eventAction': 'Previous',
									'eventLabel': '',
									'nonInteraction': cfg.alerts.analytics.nonInteraction
								});
							}
						});
						alertsModuleElem.find(cfg.alerts.swiperNextSelector).click(() => {
							// Send event for alerts next button click
							if (cfg.alerts.analytics.enabled && cfg.alerts.analytics.onPrevNext) {
								analytics.sendGAEvent({
									'eventCategory': 'Alerts',
									'eventAction': 'Next',
									'eventLabel': '',
									'nonInteraction': cfg.alerts.analytics.nonInteraction
								});
							}
						});

						// Link and button click listeners
						if (cfg.alerts.analytics.enabled && cfg.alerts.analytics.onClick) {
							alertsModuleElem.on('click', 'a[href], button:not(.close)', function () {
								// Get the heading of the alert that contains the clicked element
								const heading = jQuery(this)
									.closest('.' + cfg.alerts.swiperSlideClass)
									.find(headingStyle == 'p' ? 'p.title strong' : headingStyle)
									.text();

								// Send event for the click
								analytics.sendGAEvent({
									'eventCategory': 'Alerts',
									'eventAction': 'Click',
									'eventLabel': analytics.getClickEventLabel(this, heading),
									'nonInteraction': false
								});
							});
						}
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

			url: cfg.siteURL + '/wp-json/lyquix/v3/alerts'
		});
	};
	return {
		init
	};
})();

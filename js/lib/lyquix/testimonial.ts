/**
 * testimonial.ts - Testimonial module functionality
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

import { vars, cfg, log, warn } from './core';
import { mutation } from './mutation';
import { analytics } from './analytics';

// Declare external libraries
declare const jQuery, Swiper;

/**
 * This module provides functionality for galleries in a web page.
 * It exports an object with a method to initialize the testimonial module.
 *
 * @module testimonial
 *
 * @param {object} customCfg - Optional custom configuration for the testimonial module.
 *
 * The setup function sets up the galleries by fetching the galleries from the site options and adding them to the DOM.
 *
 * The init function initializes the testimonial module by setting up the galleries and adding a mutation handler for galleries added to the DOM.
 *
 * @returns {object} An object with a method to initialize the testimonial module.
 */
export const testimonial = (() => {
	const init = (customCfg?: object) => {
		// Run only once
		if (vars.testimonial?.init) return;

		vars.testimonial = {};

		cfg.testimonial = {
			enabled: true,
			testimonialBlockSelector: '.lqx-block-testimonial > .testimonial',
			swiperWrapperSelector: '.swiper-wrapper',
			swiperSelector: '.swiper',
			swiperSlideClass: 'swiper-slide',
			swiperNextSelector: '.swiper-button-next',
			swiperPrevSelector: '.swiper-button-prev',
			analytics: {
				enabled: true,
				nonInteraction: true,
				onPrevNext: true // Sends event on prev/next click
			}
		};
		// Copy default opts and vars
		if (customCfg) cfg.testimonial = jQuery.extend(true, cfg.testimonial, customCfg);

		// Initialize only if enabled
		if (cfg.testimonial.enabled) {
			log('Initializing `testimonial`');

			// Disable analytics if the analytics module is not enabled
			cfg.testimonial.analytics.enabled = cfg.analytics.enabled ? cfg.testimonial.analytics.enabled : false;
			if (cfg.testimonial.analytics.enabled) log('Setting testimonial analytics');

			// Initialize on document ready
			vars.document.ready(() => {
				setup(jQuery(cfg.testimonial.testimonialBlockSelector));

				// Add a mutation handler for galleries added to the DOM
				mutation.addHandler('addNode', cfg.testimonial.testimonialBlockSelector, setup);
			});

			// TODO: Check URL hash and open matching testimonial image
		}

		// Run only once
		vars.testimonial.init = true;
	};

	const setup = (elems) => {
		if (elems.length) {
			log('Setting up ' + elems.length + ' galleries', elems);

			elems.each((idx, testimonialElem) => {
				// The accordion element
				testimonialElem = jQuery(testimonialElem);

				// Skip if there isn't no swiper element
				if (testimonialElem.find(cfg.testimonial.swiperSelector).length == 0) return;

				// Swipper options
				let swiperOptions = {
					// Optional parameters
					direction: 'horizontal',
					loop: true,
					slidesPerView: 1,

					// Navigation arrows
					navigation: {
						prevEl: cfg.testimonial.swiperPrevSelector,
						nextEl: cfg.testimonial.swiperNextSelector
					}
				};

				// Swiper options override
				try {
					const swiperOptionsOverride = JSON.parse(testimonialElem.attr('data-swiper-options-override'));
					swiperOptions = jQuery.extend(true, swiperOptions, swiperOptionsOverride);
				} catch (e) {
					warn('Swiper options override is not valid JSON');
				}

				// Construct the swiper
				new Swiper('#' + testimonialElem.attr('id') + ' ' + cfg.testimonial.swiperSelector, swiperOptions);

				// Prev/Next button listeners
				testimonialElem.find(`${cfg.testimonial.swiperPrevSelector}, ${cfg.testimonial.swiperNextSelector}`).click((event) => {
					// Send event for testimonial prev/next button click
					if (cfg.testimonial.analytics.enabled && cfg.testimonial.analytics.onPrevNext) {
						analytics.sendGAEvent({
							'eventCategory': 'Testimonial',
							'eventAction': jQuery(event.target).hasClass(cfg.testimonial.swiperPrevSelector.replace('.', '')) ? 'Previous' : 'Next',
							'eventLabel': '',
							'nonInteraction': cfg.testimonial.analytics.nonInteraction
						});
					}

					// Browser history
					if (testimonialElem.attr('data-browser-history') == 'y') {
						// TODO: Browser history functionality
					}
				});
			});
		}
	};

	return {
		init
	};
})();

/**
 * slider.ts - Slider module functionality
 *
 * @version     3.0.0
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

declare const jQuery, Swiper;

/**
 * This module provides functionality for sliders in a web page.
 * It exports an object with a method to initialize the slider module.
 *
 * @module slider
 *
 * @param {object} customCfg - Optional custom configuration for the slider module.
 *
 * The setup function sets up the sliders by fetching the sliders from the site options and adding them to the DOM.
 *
 * The init function initializes the slider module by setting up the sliders and adding a mutation handler for sliders added to the DOM.
 *
 * @returns {object} An object with a method to initialize the slider module.
 */
export const slider = (() => {
	const init = (customCfg?: object) => {
		// Run only once
		if (vars.slider?.init) return;

		vars.slider = {
			slider: [],
		};

		cfg.slider = {
			enabled: true,
			sliderBlockSelector: '.lqx-block-slider > .slider',
			swiperWrapperSelector: '.swiper-wrapper',
			swiperSelector: '.swiper',
			swiperSlideClass: 'swiper-slide',
			swiperNextSelector: '.swiper-button-next',
			swiperPrevSelector: '.swiper-button-prev',
			swiperPaginationSelector: '.swiper-pagination',
			analytics: {
				enabled: true,
				nonInteraction: true,
				onPrevNext: true // Sends event on prev/next click
			}
		};

		// Copy default opts and vars
		if (customCfg) cfg.slider = jQuery.extend(true, cfg.slider, customCfg);

		// Initialize only if enabled
		if (cfg.slider.enabled) {
			log('Initializing `slider`');

			// Disable analytics if the analytics module is not enabled
			cfg.slider.analytics.enabled = cfg.analytics.enabled ? cfg.slider.analytics.enabled : false;
			if (cfg.slider.analytics.enabled) log('Setting slider analytics');

			// Initialize on document ready
			vars.document.ready(function () {
				setup(jQuery(cfg.slider.sliderBlockSelector));

				// Add a mutation handler for galleries added to the DOM
				mutation.addHandler('addNode', cfg.slider.sliderBlockSelector, setup);
			});
		}

		// Run only once
		vars.slider.init = true;
	};

	const setup = (elems) => {
		if (elems.length) {
			log('Setting up ' + elems.length + ' sliders', elems);

			elems.each((idx, sliderElem) => {
				sliderElem = jQuery(sliderElem);

				// Get the settings
				const autoplay = sliderElem.attr('data-autoplay') === 'y';
				const autoplayDelay = parseInt(sliderElem.attr('data-autoplay-delay'));
				// TODO Handle invalid autoplayDelay
				const loop = sliderElem.attr('data-loop') === 'y';
				const pagination = sliderElem.attr('data-pagination') === 'y';
				const navigation = sliderElem.attr('data-navigation') === 'y';

				let swiperOptions = {
					// Optional parameters
					direction: 'horizontal',
					loop: loop,
					slidesPerView: 1
				};

				if (autoplay == true) {
					swiperOptions['autoplay'] = {
						delay: autoplayDelay * 1000,
						pauseOnMouseEnter: true
					};
				}

				if (pagination == true) {
					swiperOptions['pagination'] = {
						enabled: true,
						el: cfg.slider.swiperPaginationSelector,
						clickable: true,
						renderBullet(index: number, className: string): string {
							const slideEl = this.slides[index];
							const teaserText = slideEl.getAttribute('data-slide-teaser');
							const thumbnail = slideEl.getAttribute('data-slide-thumbnail');
							// Returning the HTML string for the bullet, conditionally rendering either an image with the thumbnail or the teaser text
							return `<span class="${className}" role="button" aria-label="Go to slide ${index + 1}">` +
								(thumbnail ? `<img src="${thumbnail}" alt="" />` : teaserText) +
								'</span>';
						},
					};


					if (navigation == true) {
						swiperOptions['navigation'] = {
							enabled: true,
							prevEl: cfg.slider.swiperPrevSelector,
							nextEl: cfg.slider.swiperNextSelector
						};
					}

					// Swiper options override
					const swiperOptionsOverride = sliderElem.attr('data-swiper-options-override');
					if (swiperOptionsOverride) {
						try {
							swiperOptions = jQuery.extend(true, swiperOptions, JSON.parse(swiperOptionsOverride));
						} catch (e) {
							warn('Swiper options override is not valid JSON');
						}
					}

					new Swiper('#' + sliderElem.attr('id') + ' ' + cfg.slider.swiperSelector, swiperOptions);

					if (navigation == true) {
						// Prev/Next button listeners
						sliderElem.find(cfg.slider.swiperPrevSelector).click(() => {
							// Send event for alerts previous button click
							if (cfg.slider.analytics.enabled && cfg.slider.analytics.onPrevNext) {
								analytics.sendGAEvent({
									'eventCategory': 'Slider',
									'eventAction': 'Previous',
									'eventLabel': '',
									'nonInteraction': cfg.slider.analytics.nonInteraction
								});
							}
						});
						sliderElem.find(cfg.slider.swiperNextSelector).click(() => {
							// Send event for alerts next button click
							if (cfg.slider.analytics.enabled && cfg.slider.analytics.onPrevNext) {
								analytics.sendGAEvent({
									'eventCategory': 'Slider',
									'eventAction': 'Next',
									'eventLabel': '',
									'nonInteraction': cfg.slider.analytics.nonInteraction
								});
							}
						});
					}
				}
			});
		}
	};
	return {
		init
	};
})();

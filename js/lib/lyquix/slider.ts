/**
 * slider.ts - Slider module functionality
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

import { vars, cfg, log, warn } from './core';
import { mutation } from './mutation';
import { analytics } from './analytics';

declare const jQuery, Swiper;

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
				// Get the settings
				const autoplay = sliderElem.attr('data-autoplay') === 'y' ? true : false;
				const autoplayDelay = parseInt(sliderElem.attr('data-autoplay-delay'));
				const loop = sliderElem.attr('data-loop') === 'y' ? true : false;
				const pagination = sliderElem.attr('data-pagination') === 'y' ? true : false;
				const navigation = sliderElem.attr('data-navigation') === 'y' ? true : false;

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
						clickable: true
					};
				}

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

			});
		}
	};
	return {
		init: init
	};
})();

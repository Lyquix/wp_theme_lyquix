/**
 * cards.ts - Cards block functionality
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
 * This module provides functionality for cards in a web page.
 * It exports an object with a method to initialize the cards module.
 *
 * @module cards
 *
 * @param {object} customCfg - Optional custom configuration for the cards module.
 *
 * The module first checks if it has been initialized before. If not, it sets up the default configuration,
 * which can be overridden by the customCfg parameter. It then initializes the cards if they are enabled.
 *
 * The setup function sets up the cards by adding a Swiper slider to each cards block.
 * It also adds click listeners to the navigation buttons. When a button is clicked, an analytics event is sent
 * if analytics are enabled.
 *
 * @returns {object} An object with a method to initialize the cards module.
 */
export const cards = (() => {
	const init = (customCfg?: object) => {
		// Run only once
		if (vars.cards?.init) return;

		vars.cards = {};

		cfg.cards = {
			enabled: true,
			cardsBlockSelector: '.lqx-module-cards > .cards',
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
		if (customCfg) cfg.cards = jQuery.extend(true, cfg.alert, customCfg);

		// Initialize only if enabled
		if (cfg.cards.enabled) {
			log('Initializing `cards`');

			// Initialize on document ready
			vars.document.ready(() => {
				setup(jQuery(cfg.cards.cardsBlockSelector));

				// Add a mutation handler for galleries added to the DOM
				mutation.addHandler('addNode', cfg.cards.cardsBlockSelector, setup);
			});
		}

		// Run only once
		vars.cards.init = true;
	};

	const setup = (elems) => {
		if (elems.length) {
			log('Setting up ' + elems.length + ' galleries', elems);

			elems.each((idx, cardsElem) => {
				// The accordion element
				cardsElem = jQuery(cardsElem);

				// Skip if there isn't no swiper element
				if (cardsElem.find(cfg.cards.swiperSelector).length == 0) return;

				// Swipper options
				let swiperOptions = {
					direction: 'horizontal',
					loop: false,
					slidesPerView: 1,
					breakpoints: {},

					// Navigation arrows
					navigation: {
						prevEl: cfg.cards.swiperPrevSelector,
						nextEl: cfg.cards.swiperNextSelector
					}
				};

				// Slides per view
				const responsiveRules = JSON.parse(cardsElem.attr('data-responsive-rules'));
				// TODO Handle empty rules, invalid JSON
				responsiveRules.forEach((rule) => {
					rule.screens.forEach((screen) => {
						swiperOptions.breakpoints[cfg.responsive.breakPoints[cfg.responsive.sizes.indexOf(screen)]] = {
							slidesPerView: parseInt(rule.columns)
						};
					});
				});

				// Swiper options override
				try {
					const swiperOptionsOverride = JSON.parse(cardsElem.attr('data-swiper-options-override'));
					swiperOptions = jQuery.extend(true, swiperOptions, swiperOptionsOverride);
				} catch (e) {
					warn('Swiper options override is not valid JSON');
				}

				// Construct the swiper
				new Swiper('#' + cardsElem.attr('id') + ' ' + cfg.cards.swiperSelector, swiperOptions);

				// Prev/Next button listeners
				cardsElem.find(`${cfg.cards.swiperPrevSelector}, ${cfg.cards.swiperNextSelector}`).click((event) => {
					// Send event for cards prev/next button click
					if (cfg.cards.analytics.enabled && cfg.cards.analytics.onPrevNext) {
						analytics.sendGAEvent({
							'eventCategory': 'Cards',
							'eventAction': jQuery(event.target).hasClass(cfg.cards.swiperPrevSelector.replace('.', '')) ? 'Previous' : 'Next',
							'eventLabel': '',
							'nonInteraction': cfg.cards.analytics.nonInteraction
						});
					}
				});
			});
		}
	};

	return {
		init
	};
})();

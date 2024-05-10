/**
 * gallery.ts - Gallery module functionality
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

export const gallery = (() => {
	const init = (customCfg?: object) => {
		// Run only once
		if (vars.gallery?.init) return;

		vars.gallery = {};

		cfg.gallery = {
			enabled: true,
			galleryBlockSelector: '.lqx-block-gallery > .gallery',
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
		if (customCfg) cfg.gallery = jQuery.extend(true, cfg.gallery, customCfg);

		// Initialize only if enabled
		if (cfg.gallery.enabled) {
			log('Initializing `gallery`');

			// Disable analytics if the analytics module is not enabled
			cfg.gallery.analytics.enabled = cfg.analytics.enabled ? cfg.gallery.analytics.enabled : false;
			if (cfg.gallery.analytics.enabled) log('Setting gallery analytics');

			// Initialize on document ready
			vars.document.ready(() => {
				setup(jQuery(cfg.gallery.galleryBlockSelector));

				// Add a mutation handler for galleries added to the DOM
				mutation.addHandler('addNode', cfg.gallery.galleryBlockSelector, setup);
			});

			// TODO: Check URL hash and open matching gallery image
		}

		// Run only once
		vars.gallery.init = true;
	};

	const setup = (elems) => {
		if (elems.length) {
			log('Setting up ' + elems.length + ' galleries', elems);

			elems.each((idx, galleryElem) => {
				// The accordion element
				galleryElem = jQuery(galleryElem);

				// Skip if there isn't no swiper element
				if (galleryElem.find(cfg.gallery.swiperSelector).length == 0) return;

				// Swipper options
				let swiperOptions = {
					// Optional parameters
					direction: 'horizontal',
					loop: true,
					slidesPerView: 1,

					// Navigation arrows
					navigation: {
						prevEl: cfg.gallery.swiperPrevSelector,
						nextEl: cfg.gallery.swiperNextSelector
					}
				};

				// Swiper options override
				try {
					const swiperOptionsOverride = JSON.parse(galleryElem.attr('data-swiper-options-override'));
					swiperOptions = jQuery.extend(true, swiperOptions, swiperOptionsOverride);
				} catch (e) {
					warn('Swiper options override is not valid JSON');
				}

				// Construct the swiper
				new Swiper('#' + galleryElem.attr('id') + ' ' + cfg.gallery.swiperSelector, swiperOptions);

				// Prev/Next button listeners
				galleryElem.find(`${cfg.gallery.swiperPrevSelector}, ${cfg.gallery.swiperNextSelector}`).click((event) => {
					// Send event for gallery prev/next button click
					if (cfg.gallery.analytics.enabled && cfg.gallery.analytics.onPrevNext) {
						analytics.sendGAEvent({
							'eventCategory': 'Gallery',
							'eventAction': jQuery(event.target).hasClass(cfg.gallery.swiperPrevSelector.replace('.', '')) ? 'Previous' : 'Next',
							'eventLabel': '',
							'nonInteraction': cfg.gallery.analytics.nonInteraction
						});
					}

					// Browser history
					if (galleryElem.attr('data-browser-history') == 'y') {
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

/**
 * video.ts - Video performance functions
 *
 * @version     3.1.0
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

import { vars, cfg, log, error } from './core';
import { mutation } from './mutation';

/**
 * This module provides various helper functions and features
 * to assist with performance of html video elements across blocks
 *
 * @module videos
 *
 * @param {object} customCfg - Optional custom configuration for the video module.
 *
 * @returns {object} An object with methods for various video functions.
 */

export const video = (() => {
	const init = (customCfg?: object) => {
		// Run only once
		if (vars.video?.init) return;
		// Working variables
		vars.video = {
			init: false
		};

		// Configuration
		cfg.video = {
			enabled: true,
			lazyLoadSelector: 'video.lazyload-video',
			hoverPlaySelector: 'video.video-hover-play',
			viewportPlaySelector: 'video.video-viewport-play'
		};

		if (customCfg) cfg.video = jQuery.extend(true, cfg.video, customCfg);

		// Initialize only if enabled
		if (cfg.video.enabled) {
			log('Initializing util');
		}

		// Initialize on document ready
		vars.document.ready(() => {
			// run initial check and add mutation observer for all three cases:
			// Lazy Load videos
			lazyLoad(document.querySelectorAll(cfg.video.lazyLoadSelector));
			mutation.addHandler('addNode', cfg.video.lazyLoadSelector, lazyLoad);

			//Only play videos on hover
			hoverPlay(document.querySelectorAll(cfg.video.hoverPlaySelector));
			mutation.addHandler('addNode', cfg.video.hoverPlaySelector, lazyLoad);

			//Only play videos while they are in the viewport
			viewportPlay(document.querySelectorAll(cfg.video.viewportPlaySelector));
			mutation.addHandler('addNode', cfg.video.viewportPlaySelector, lazyLoad);
			// Add a mutation handler for galleries added to the DOM

		});
		// Run only once
		vars.video.init = true;
	};

	const lazyLoad = (elems: Array) => {
		const lazyObserver = new IntersectionObserver((elems, observer) => {

			elems.forEach(entry => {

				if (entry.isIntersecting) {

					const video = entry.target;
					console.log(video.dataset, video.src);
					if (!video.src && video.dataset.src) {

						video.src = video.dataset.src;

						video.load();

					}

					observer.unobserve(video);

				}

			});

		});

		elems.forEach(video => lazyObserver.observe(video));
	};

	const hoverPlay = (elems: Array) => {
		elems.forEach(video => {

			video.addEventListener('mouseenter', () => video.play());

			video.addEventListener('mouseleave', () => {

				video.pause();

				video.currentTime = 0;

			});
		});
	};

	const viewportPlay = (elems: Array) => {
		const viewportObserver = new IntersectionObserver((entries) => {

			entries.forEach(entry => {

				const video = entry.target;

				if (entry.isIntersecting) {

					video.play();

				} else {

					video.pause();

				}

			});

		}, {

			threshold: 0.5 // You can tweak this to control how much needs to be visible

		});

		elems.forEach(video => viewportObserver.observe(video));
	};

	return {
		init,
	};
})();

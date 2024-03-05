/**
 * swipe.ts - Swipe functionality
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

export const swipe = (() => {

	const init = (customCfg?: object) => {
		// Run only once
		if (vars.swipe?.init) return;

		// Working variables
		vars.swipe = {
			init: false,
			swipes: {}
		};

		// Configuration
		cfg.swipe = {
			enabled: true,
			minX: 30,
			minY: 30,
			maxTime: 1500,
			disableScroll: true,
			detectV: true,
			detectH: true
		};

		if (customCfg) cfg.swipe = jQuery.extend(true, cfg.swipe, customCfg);

		// Initialize only if enabled
		if (cfg.swipe.enabled) {
			log('Initializing swipe');
		}

		// Run only once
		vars.swipe.init = true;
	};

	// Enable swipe detection
	const add = (sel, callback, customCfg) => {
		log(`Setting up swipe detection for ${sel}`);

		// TODO Data validation

		// Create a swipes object for selector
		const swipes: {
			sel: string,
			elems: object[]
		} = {
			sel: sel,
			elems: []
		};

		jQuery(sel).each((index, elem) => {
			// Create swipe object for a single element
			const theSwipe = {
				elem,
				callback,
				cfg: jQuery.extend({}, cfg),
				startX: 0,
				startY: 0,
				startTime: 0,
				endX: 0,
				endY: 0,
				endTime: 0,
				dir: '',
				touchstart: (e) => {
					const t = e.originalEvent.touches[0];
					theSwipe.startX = theSwipe.endX = t.clientX;
					theSwipe.startY = theSwipe.endY = t.clientY;
					const d = new Date();
					theSwipe.startTime = theSwipe.endTime = d.getTime() + d.getMilliseconds() / 1000;
					theSwipe.dir = '';
				},
				touchmove: (e) => {
					if (theSwipe.cfg.disableScroll) e.preventDefault();
					const t = e.touches[0];
					theSwipe.endX = t.clientX;
					theSwipe.endY = t.clientY;
					const d = new Date();
					theSwipe.endTime = d.getTime() + d.getMilliseconds() / 1000;
				},
				touchend: (e) => {
					const t = e.originalEvent.touches[0];
					theSwipe.endX = t.clientX;
					theSwipe.endY = t.clientY;
					const d = new Date();
					theSwipe.endTime = d.getTime() + d.getMilliseconds() / 1000;

					// Only handle swipes that are no longer than swipe.cfg.maxTime
					if (theSwipe.endTime > 0 && theSwipe.endTime - theSwipe.startTime <= theSwipe.cfg.maxTime) {
						// Horizontal swipe
						if (theSwipe.cfg.detectH && Math.abs(theSwipe.endX - theSwipe.startX) > theSwipe.cfg.minX && theSwipe.endX > 0) {
							if (theSwipe.endX > theSwipe.startX) theSwipe.dir += 'r'; // right
							else theSwipe.dir += 'l'; // left
						}

						// Vertical swipe
						if (theSwipe.cfg.detectV && Math.abs(theSwipe.endY - theSwipe.startY) > theSwipe.cfg.minY && theSwipe.endY > 0) {
							if (theSwipe.endY > theSwipe.startY) theSwipe.dir += 'd'; // down
							else theSwipe.dir += 'u'; // up
						}

						// Swipe detected?
						if (theSwipe.dir != '') {
							log(`Detected swipe ${theSwipe.dir} for ${sel}(${index})`);
							callback({
								sel,
								index,
								elem,
								dir: theSwipe.dir
							});
						}
					}
				}
			};

			// Extend default config with custom config
			if (typeof customCfg == 'object') theSwipe.cfg = jQuery.extend(true, theSwipe.cfg, customCfg);

			// Add event listeners
			elem.addEventListener('touchstart', theSwipe.touchstart);
			elem.addEventListener('touchmove', theSwipe.touchmove, { passive: !theSwipe.cfg.disableScroll });
			elem.addEventListener('touchend', theSwipe.touchend);

			// Add element swipe to list of elems
			swipes.elems.push(theSwipe);
		});

		// Add swipes for selector to list of all swipes
		vars.swipe.swipes[sel] = swipes;
	};

	const update = (sel, callback, customCfg) => {
		// TODO Data validation

		// Remove current swipes
		swipe.remove(sel);

		// Create new swipes
		swipe.add(sel, callback, customCfg);
	};

	const remove = (sel) => {
		if (!(sel in vars.swipe.swipes)) {
			warn(`Selector ${sel} doesn't have an active swiper event`);
			return;
		}

		// Remove event listeners
		vars.swipe.swipes[sel].elems.forEach((swipe) => {
			swipe.elem.removeEventListener('touchstart', swipe.touchstart);
			swipe.elem.removeEventListener('touchmove', swipe.touchmove);
			swipe.elem.removeEventListener('touchend', swipe.touchend);
		});

		// Delete swipe object for selector
		delete (vars.swipe.swipes[sel]);
	};

	return {
		init,
		add,
		update,
		remove
	};

})();

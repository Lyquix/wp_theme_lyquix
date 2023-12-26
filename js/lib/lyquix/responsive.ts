/**
 * responsive.ts - Screen Responsiveness
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

import { vars, cfg, log } from './core';

export const responsive = (() => {

	const init = (customCfg?: object) => {
		// Run only once
		if (vars.responsive?.init) return;

		// Working variables
		vars.responsive = {
			init: false,
			screen: '',
			orientation: '',
			aspectRatio: ''
		};

		// Configuration
		cfg.responsive = {
			enabled: true,
			sizes: ['xs', 'sm', 'md', 'lg', 'xl'],
			breakPoints: [320, 640, 960, 1280, 1600]
		};

		if (customCfg) cfg.responsive = jQuery.extend(true, cfg.responsive, customCfg);

		// Initialize only if enabled
		if (cfg.responsive.enabled) {
			log('Initializing responsive');

			cfg.responsive.breakPoints.forEach((breakPoint, s) => {
				let cssQuery = '';
				if (s == 0) cssQuery = '(max-width: ' + (cfg.responsive.breakPoints[s + 1] - 1) + 'px)';
				else if (s == cfg.responsive.breakPoints.length - 1) cssQuery = '(min-width: ' + cfg.responsive.breakPoints[s] + 'px)';
				else cssQuery = '(min-width: ' + cfg.responsive.breakPoints[s] + 'px) and (max-width: ' + (cfg.responsive.breakPoints[s + 1] - 1) + 'px)';

				// Add listener
				const mm = window.matchMedia(cssQuery);
				mm.addEventListener('change', (e) => {
					if (e.matches) setScreen(s);
				}, { passive: true });

				// Check screen size for the first time
				if (mm.matches) setScreen(s);

			});

			if ('orientation' in window.screen) {
				// Check screen orientation for the first time
				setOrientation();

				// Listeners for setOrientation
				window.addEventListener('orientationchange', () => {
					// Update orientation attribute in body tag
					setOrientation();
				}, { passive: true });
			}

			// Check aspect ratio for the first time
			setAspectRatio();

			// Listner for setAspectRatio
			window.addEventListener('resize', () => {
				// Update aspect ratio attribute in body tag
				setAspectRatio();
			}, { passive: true });
			window.addEventListener('orientationchange', () => {
				// Update aspect ratio attribute in body tag
				setAspectRatio();
			}, { passive: true });
		}
		// Run only once
		vars.responsive.init = true;
	};

	const setScreen = (s: number) => {
		if (cfg.responsive.sizes[s] != vars.responsive.screen) {
			// Change the body screen attribute
			vars.body.attr('screen', cfg.responsive.sizes[s]);

			// Save the current screen size
			vars.responsive.screen = cfg.responsive.sizes[s];

			// Trigger custom event 'screensizechange'
			vars.document.trigger('screensizechange');
			log('Screen size changed', vars.responsive.screen);
		}
	};

	const setOrientation = () => {
		const o = window.screen.orientation.type;
		if (o.indexOf(vars.responsive.orientation) == -1) {
			switch (o) {
			case 'portrait-primary':
			case 'portrait-secondary':
				vars.responsive.orientation = 'portrait';
				vars.body.attr('orientation', 'portrait');
				break;
			case 'landscape-primary':
			case 'landscape-secondary':
				vars.responsive.orientation = 'landscape';
				vars.body.attr('orientation', 'landscape');
				break;
			}
			log('Screen orientation changed' + vars.responsive.orientation);
		}
		return true;
	};

	const setAspectRatio = () => {
		const arValue: number = window.innerWidth / window.innerHeight;
		let arLabel = '';
		if (arValue < 0.5) arLabel = 'ultranarrow';
		else if (arValue >= 0.5 && arValue < 1) arLabel = 'narrow';
		else if (arValue >= 1 && arValue < 1.5) arLabel = 'standard';
		else if (arValue >= 1.5 && arValue < 2) arLabel = 'wide';
		else if (arValue >= 2) arLabel = 'ultrawide';

		if (vars.responsive.aspectRatio != arLabel) {
			vars.responsive.aspectRatio = arLabel;

			// Set body attribute
			vars.body.attr('aspectratio', vars.responsive.aspectRatio);

			// Trigger custom event 'aspectratiochange'
			vars.document.trigger('aspectratiochange');
			log('Aspect ratio changed', vars.responsive.aspectRatio);
		}
	};

	return Object.defineProperties({
		init
	}, {
		// Set the cfg and vars properties as read-only
		screen: {
			get() {
				return vars.responsive.screen;
			},
			set() {
				return undefined;
			}
		},
		orientation: {
			get() {
				return vars.responsive.orientation;
			},
			set() {
				return undefined;
			}
		},
		aspectRatio: {
			get() {
				return vars.responsive.aspectRatio;
			},
			set() {
				return undefined;
			}
		}
	}) as {
		init: (customCfg?: object) => void,
		screen: string,
		orientation: string,
		aspectRatio: string
	};

})();

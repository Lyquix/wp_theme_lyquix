/**
 * a11y.ts - Accessibility fixes for third-party widgets
 *
 * @version     3.5.0
 * @package     wp_theme_lyquix
 * @author      Lyquix
 * @copyright   Copyright (C) 2015 - 2026 Lyquix
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

declare const jQuery;

/**
 * This module patches accessibility gaps left by third-party widgets.
 *
 * Currently it gives Swiper controls an accessible name: Swiper marks its
 * navigation arrows with role="button" but leaves aria-label empty, and its
 * default pagination bullets have no accessible name at all. Controls that
 * already carry a label (e.g. rendered by the slider block) are left untouched.
 *
 * Swiper builds and rebuilds its controls asynchronously (init, resize, loop),
 * so labels are re-applied through a MutationObserver whenever new controls
 * appear in the DOM.
 *
 * @module a11y
 *
 * @param {object} customCfg - Optional custom configuration for the a11y module.
 */
export const a11y = (() => {
	const NAV_LABELS: Record<string, string> = {
		'swiper-button-prev': 'Previous slide',
		'swiper-button-next': 'Next slide'
	};

	const SWIPER_CONTROL_SELECTOR =
		'.swiper-button-prev, .swiper-button-next, .swiper-pagination, .swiper-pagination-bullet';

	// Label Swiper navigation arrows that have no accessible name
	const labelNavButtons = (root: ParentNode = document) => {
		Object.keys(NAV_LABELS).forEach((cls) => {
			root.querySelectorAll<HTMLElement>('.' + cls).forEach((el) => {
				const current = (el.getAttribute('aria-label') || '').trim();
				if (!current) el.setAttribute('aria-label', NAV_LABELS[cls]);
			});
		});
	};

	// Label default Swiper pagination bullets with their position
	const labelPaginationBullets = (root: ParentNode = document) => {
		root.querySelectorAll<HTMLElement>('.swiper-pagination').forEach((pagination) => {
			const bullets = pagination.querySelectorAll<HTMLElement>('.swiper-pagination-bullet');
			bullets.forEach((bullet, idx) => {
				const current = (bullet.getAttribute('aria-label') || '').trim();
				if (!current) bullet.setAttribute('aria-label', `Go to slide ${idx + 1}`);
			});
		});
	};

	const applyLabels = (root?: ParentNode) => {
		labelNavButtons(root);
		labelPaginationBullets(root);
	};

	const isRelevantNode = (n: Node): boolean => {
		if (n.nodeType !== 1) return false;
		const el = n as Element;
		return (el.matches?.(SWIPER_CONTROL_SELECTOR) ?? false)
			|| !!el.querySelector?.(SWIPER_CONTROL_SELECTOR);
	};

	const init = (customCfg?: object) => {
		// Run only once
		if (vars.a11y?.init) return;

		vars.a11y = {
			init: false,
			observerBound: false
		};
		cfg.a11y = {
			enabled: true
		};

		if (customCfg) cfg.a11y = jQuery.extend(true, cfg.a11y, customCfg);

		// Initialize only if enabled
		if (cfg.a11y.enabled) {
			log('Initializing `a11y`');

			// Apply once now / on DOM ready, then again on full load to catch
			// Swiper instances that initialize late
			if (document.readyState === 'loading') {
				document.addEventListener('DOMContentLoaded', () => applyLabels(), { once: true });
			} else {
				applyLabels();
			}
			jQuery(window).on('load', () => applyLabels());

			// Re-apply labels whenever new Swiper controls appear in the DOM
			if (typeof MutationObserver !== 'undefined' && !vars.a11y.observerBound) {
				let queued = false;
				const observer = new MutationObserver((mutations) => {
					const relevant = mutations.some((m) =>
						Array.from(m.addedNodes).some(isRelevantNode)
					);
					if (!relevant || queued) return;
					queued = true;
					requestAnimationFrame(() => {
						queued = false;
						applyLabels();
					});
				});
				observer.observe(document.body, { childList: true, subtree: true });
				vars.a11y.observerBound = true;
			}
		}

		// Run only once
		vars.a11y.init = true;
	};

	return {
		init
	};
})();

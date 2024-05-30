/**
 * mutation.ts - Mutation observer and handler
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

/**
 * This module provides a mutation observer and handler for a web page.
 * It exports an object with methods to initialize the mutation observer and add handlers for nodes added, nodes removed,
 * and attributes modified.
 *
 * @module mutation
 *
 * @param {object} customCfg - Optional custom configuration for the mutation module.
 *
 * The init function initializes the mutation observer and sets up the mutation handler.
 *
 * The addHandler function adds a handler for nodes added, nodes removed, or attributes modified. The handler is called
 * when the mutation observer detects the specified type of mutation on the specified selector.
 *
 * @returns {object} An object with methods to initialize the mutation observer and add handlers for nodes added, nodes removed, and attributes modified.
 */
export const mutation = (() => {

	const init = (customCfg?: object) => {
		// Run only once
		if (vars.mutation?.init) return;

		// Working variables
		vars.mutation = {
			init: false,
			observer: null,
			addNode: [],
			removeNode: [],
			modAttrib: [],
		};

		// Configuration
		cfg.mutation = {
			enabled: true,
		};

		if (customCfg) cfg.mutation = jQuery.extend(true, cfg.mutation, customCfg);

		// Initialize only if enabled
		if (cfg.mutation.enabled) {
			log('Initializing mutation');

			// Create mutation observer object
			const mo = window.MutationObserver;

			// check for mutationObserver support , if exists, user the mutation observer object, if not use the listener method.
			if (typeof mo !== 'undefined') {
				vars.mutation.observer = new mo(handler);
				vars.mutation.observer.observe(document.body, { childList: true, subtree: true, attributes: true });
			}
		}

		// Run only once
		vars.mutation.init = true;
	};

	const addHandler = (type, selector, callback) => {
		// TODO Data validation
		vars.mutation[type].push({ 'selector': selector, 'callback': callback });
		log('Adding handler for mutation ' + type + ' for ' + selector);
	};

	const handler = (mutRecs) => {
		if (!(mutRecs instanceof Array)) {
			// Not an array, convert to an array
			mutRecs = [mutRecs];
		}
		mutRecs.forEach((mutRec) => {
			switch (mutRec.type) {
				case 'childList': {
					// Handle nodes added
					if (mutRec.addedNodes.length > 0) {
						const nodes = nodesArray(mutRec.addedNodes);
						nodes.forEach((e) => {
							vars.mutation.addNode.forEach((h) => {
								if (jQuery(e).is(h.selector)) h.callback(e);
							});
						});
					}

					// Handle nodes removed
					if (mutRec.removedNodes.length > 0) {
						const nodes = nodesArray(mutRec.removedNodes);
						nodes.forEach((e) => {
							vars.mutation.removeNode.forEach((h) => {
								if (jQuery(e).is(h.selector)) h.callback(e);
							});
						});
					}
					break;
				}

				case 'attributes':
					vars.mutation.modAttrib.forEach((h) => {
						if (mutRec.target.matches(h.selector)) h.callback(mutRec.target);
					});
					break;
			}
		});
	};

	const nodesArray = (nodes) => {
		let o = [];
		for (let i = 0; i < nodes.length; i++) {
			const n = jQuery(nodes[i]);
			o.push(n);
			const children = n.find('*').toArray();
			if (children.length) o = o.concat(children);
		}
		return o;
	};

	return {
		init,
		addHandler
	};

})();

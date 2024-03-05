/**
 * store.ts - Persistent data storage using localStorage
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

export const store = (() => {

	const init = (customCfg?: object) => {
		// Run only once
		if (vars.store?.init) return;

		// Working variables
		vars.store = {
			init: false,
			tracked: []
		};

		// Configuration
		cfg.store = {
			enabled: true,
			itemName: 'lqxStore',
			updateInterval: 15, // in seconds
		};

		if (customCfg) cfg.store = jQuery.extend(true, cfg.store, customCfg);

		// Initialize only if enabled
		if (cfg.store.enabled) {
			log('Initializing store');

			// Initialize item in localStorage
			let lqxStore: any = window.localStorage.getItem(cfg.store.itemName);
			if (lqxStore === null) window.localStorage.setItem(cfg.store.itemName, '{}');
			else {
				lqxStore = JSON.parse(lqxStore);
				// Regenerate vars.store.tracked
				Object.keys(lqxStore).forEach((objName) => {
					vars.store.tracked[objName] = {};
					Object.keys(lqxStore[objName]).forEach((prop) => {
						vars.store.tracked[objName][prop] = lqxStore[objName][prop];
					});
				});
			}

			// Add event listener
			window.addEventListener('beforeunload', store.update);

			// Add periodic update every 15 seconds
			window.setInterval(store.update, cfg.store.updateInterval * 1000);
		}

		// Run only once
		vars.store.init = true;
	};

	// Get a variable value
	const get = (objName) => {
		if (typeof objName == 'undefined') return undefined;

		// Get data from localStorage
		let lqxStore: any = window.localStorage.getItem(cfg.store.itemName);
		if (lqxStore) {
			lqxStore = JSON.parse(lqxStore);
			// TODO Handle invalid JSON
		}
		else lqxStore = {};

		if (!(objName in lqxStore)) return undefined;

		return lqxStore[objName];
	};

	// Get the value of an object
	const getObjValue = (objName) => {
		// Split the path into an array
		const objPath = objName.split('.');

		// if the first part is window, remove it
		if(objPath[0] === 'window') objPath.shift();

		// check if the object exists in window
		if(!(objPath[0] in window)) return undefined;

		// Get a pointer to the top path segment
		let objPointer = window[objPath[0]];

		// Cycle through the path to get a pointer to the object
		for (let i = 1; i < objPath.length; i++) {
			if(!(objPath[i] in objPointer)) return undefined;
			objPointer = objPointer[objPath[i]];
		}

		return objPointer;
	};

	// Set a variable value
	// objName: a string with the dot notation of the parent object, e.g. lqx.util.vars, $lqx.filters.vars
	// prop:
	const set = (objName) => {
		if (typeof objName == 'undefined') return undefined;

		// Get data from localStorage
		let lqxStore: any = window.localStorage.getItem(cfg.store.itemName);
		if (lqxStore) {
			lqxStore = JSON.parse(lqxStore);
			// TODO Handle invalid JSON
		}
		else lqxStore = {};

		// Set the value
		const objValue = getObjValue(objName);

		if(objValue === undefined) {
			warn(`Object ${objName} doesn't exist and could not be stored`);
			return undefined;
		}

		// save the data
		lqxStore[objName] = objValue;
		window.localStorage.setItem(cfg.store.itemName, JSON.stringify(lqxStore));

		// Add objName.prop to save on exit array
		if (!vars.store.tracked.includes(objName)) vars.store.tracked.push(objName);

		return objValue;
	};

	// Remove a prop or entire object
	// prop is optional
	const unset = (objName) => {
		if (typeof objName == 'undefined') return false;

		// Get data from localStorage
		let lqxStore: any = window.localStorage.getItem(cfg.store.itemName);
		if (lqxStore) {
			lqxStore = JSON.parse(lqxStore);
			// TODO Handle invalid JSON
		}
		else lqxStore = {};

		// Delete objName/prop from lqxStore and vars.store.tracked
		if(!(objName in lqxStore)) {
			warn(`Object ${objName} is not currently tracked`);
			return false;
		}

		// delete
		delete lqxStore[objName];
		const trackedIdx = vars.store.tracked.indexOf(objName);
		if(trackedIdx !== -1) vars.store.tracked.splice(trackedIdx, 1);

		// Save updated data
		window.localStorage.setItem(cfg.store.itemName, JSON.stringify(lqxStore));

		return true;
	};

	const update = () => {
		const lqxStore = {}, droppedObjs = [];

		// Get fresh values of all tracked objNames
		vars.store.tracked.forEach((objName) => {
			const objValue = getObjValue(objName);
			if(objValue === undefined) {
				warn(`Object ${objName} no longer exists, dropped from store`);
				droppedObjs.push[objName];
			}
			else lqxStore[objName] = objValue;
		});

		// Remove undefined objects from tracked
		if(droppedObjs.length) droppedObjs.forEach((objName) => {
			vars.store.tracked.splice(vars.store.tracked.indexOf(objName), 1);
		});

		// Save data
		window.localStorage.setItem(cfg.store.itemName, JSON.stringify(lqxStore));
	};

	return {
		init,
		get,
		set,
		unset,
		update
	};

})();

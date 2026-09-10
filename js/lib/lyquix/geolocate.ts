/**
 * geolocate.ts - Geolocation and regionalization functionality
 *
 * @version     3.4.0
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
import { util } from './util';
declare const jQuery;

/**
 * This module provides functionality for geolocation and regionalization in a web page.
 * It exports an object with methods to initialize and geolocate.
 *
 * @module geolocate
 *
 * @param {object} customCfg - Optional custom configuration for the geolocate module.
 *
 * Region definitions are loaded automatically from cfg.geolocate.regions (populated from ACF via PHP).
 * After geolocation completes, matchRegions() tests the user's coordinates against each region's GeoJSON
 * and populates vars.geolocate.regions with matching aliases before firing the geolocateready event.
 *
 * The regionDisplay function shows or hides elements based on the matched regions.
 *
 * @returns {object} An object with methods to initialize and geolocate.
 */
export const geolocate = (() => {

	const init = (customCfg?: object) => {
		// Run only once
		if (vars.geolocate?.init) return;

		// Working variables
		vars.geolocate = {
			location: {
				source: null,
				city: null,
				subdivision: null,
				country: null,
				continent: null,
				time_zone: null,
				lat: null,
				lon: null,
				radius: null,
				ip: null
			},
			regions: [],
			cookies: {
				ip: null,
				gps: null
			},
			status: {
				ip: null,
				gps: null
			},
			ready: false
		};

		// Configuration
		cfg.geolocate = {
			enabled: true,
			gps: false, // Set to true to enable GPS geolocation
			useCookies: true, // Set to true to use cookies to store geolocation data
			cookieExpirationIP: 900, // In seconds
			cookieExpirationGPS: 900, // In seconds
			handleNoRegionMatch: true, // Set to true to actively force show/display of unmatched elements, false to do nothing
			removeNoRegionMatch: true, // Set to true to remove from the DOM unmatched elements, set to false to hide them (display: none)
			regionDisplaySelector: '[data-region-display], [class*="region-name-"]', // CSS selector for elements to show/hide based on region
			regions: [] // Region definitions from ACF, populated via PHP
		};

		if (customCfg) cfg.geolocate = jQuery.extend(true, cfg.geolocate, customCfg);

		// Initialize only if enabled
		if (cfg.geolocate.enabled) {
			log('Initializing geolocate');

			// Start geolocation
			geoLocate();

			// Add a mutation handler for accordions added to the DOM
			mutation.addHandler('addNode', cfg.geolocate.regionDisplaySelector, regionDisplay);

			// Add a handler for geolocateready event to run regionDisplay on page elements
			vars.document.on('geolocateready', regionDisplay);
		}

		// Run only once
		vars.geolocate.init = true;
	};

    // Attempts to locate position of user by means of gps or ip address
    const geoLocate = () => {
        // Check for manually selected region cookie first — takes priority over IP/GPS detection
        const selectedRegion = util.cookie('selectedRegion');
        if (selectedRegion) {
            log('Region override from selectedRegion cookie:', selectedRegion);
            vars.geolocate.regions = [selectedRegion];
            vars.geolocate.status.ip = 'ready';
            vars.geolocate.status.gps = 'n/a';
            bodyGeoData();
            regionDisplay();
            return;
        }

        if (cfg.geolocate.useCookies) {
            log('Attempting to geolocate from cookies');

			// Get data from cookies
			vars.geolocate.cookies.ip = util.cookie('lqx.geolocate.cookies.ip');
			if (vars.geolocate.cookies.ip !== null) vars.geolocate.cookies.ip = JSON.parse(vars.geolocate.cookies.ip);

			if (vars.geolocate.cookies.ip !== null) {
				vars.geolocate.location = Object.assign({}, vars.geolocate.cookies.ip);
				vars.geolocate.location.source = 'ip2geo-cookie';
				vars.geolocate.status.ip = 'cookie';
			}
			else getIP();

			vars.geolocate.cookies.gps = util.cookie('lqx.geolocate.cookies.gps');
			if (vars.geolocate.cookies.gps !== null) vars.geolocate.cookies.gps = JSON.parse(vars.geolocate.cookies.gps);

			if (cfg.geolocate.gps && 'geolocation' in window.navigator) {
				if (vars.geolocate.cookies.gps !== null) {
					vars.geolocate.location = Object.assign({}, vars.geolocate.cookies.gps);
					vars.geolocate.location.source = 'gps-cookie';
					vars.geolocate.status.gps = 'cookie';
				}
				else getGPS();
			}
			else vars.geolocate.status.gps = 'n/a';

			bodyGeoData();
		}
		else {
			getIP();
			getGPS();
		}
	};

	// Geolocation from IP
	const getIP = () => {
		log('Attempting IP geolocation');
		vars.geolocate.status.ip = 'wait';
		// ip2geo to get location info
		jQuery.ajax({
			async: true,
			cache: false,
			dataType: 'json',
			url: cfg.siteURL + '/wp-json/lyquix/v3/ip2geo',
			success: (data) => {
				// Do not overwrite existing GPS location
				if (vars.geolocate.location.source !== 'gps' && vars.geolocate.location.source !== 'gps-cookie') {
					vars.geolocate.location = data;
					// TODO Data validation
					vars.geolocate.location.source = 'ip2geo';
				}

				vars.geolocate.status.ip = 'ready';
				console.log('succesful ipgeo');
				log('IP geolocation result', vars.geolocate.location);

				// Save cookie
				if (cfg.geolocate.useCookies) util.cookie('lqx.geolocate.locationIP', JSON.stringify(vars.geolocate.location), { maxAge: cfg.geolocate.cookieExpirationIP, path: '/', secure: true });

				bodyGeoData();
			},
			// Not named error: that would shadow the logger imported from core
			error: (xhr, ready, message) => {
				error('Geolocate error ' + ready + ' ' + message);
			}
		});
	};

	// Geolocation from GPS
	const getGPS = () => {
		if (cfg.geolocate.gps && 'geolocation' in window.navigator) {
			log('Attempting GPS geolocation');
			vars.geolocate.status.gps = 'wait';

			window.navigator.geolocation.getCurrentPosition((position) => {
				// TODO Data validation
				vars.geolocate.location.lat = position.coords.latitude;
				vars.geolocate.location.lon = position.coords.longitude;
				vars.geolocate.location.radius = position.coords.accuracy / 1000; // in km
				vars.geolocate.location.source = 'gps';
				vars.geolocate.status.gps = 'ready';

				log('GPS geolocation result', vars.geolocate.location);

				// Save cookie
				if (cfg.geolocate.useCookies) util.cookie('lqx.geolocate.locationGPS', JSON.stringify(vars.geolocate.location), { maxAge: cfg.geolocate.cookieExpirationGPS, path: '/', secure: true });

				bodyGeoData();
			});
		}
		else vars.geolocate.status.gps = 'n/a';
	};

	// Save results to body attributes and trigger geolocateready event
	const bodyGeoData = () => {
		if (['cookie', 'ready'].includes(vars.geolocate.status.ip) && ['cookie', 'ready', 'n/a'].includes(vars.geolocate.status.gps)) {
			// Add location attributes to body tag
			for (const key in vars.geolocate.location) {
				if (key == 'time_zone') {
					vars.body.attr('time-zone', vars.geolocate.location.time_zone);
				}
				else if (['source', 'ip'].indexOf(key) == -1) {
					vars.body.attr(key, vars.geolocate.location[key]);
				}
			}

            // Match regions before triggering event — skip if already set by selectedRegion cookie
            if (!vars.geolocate.regions.length) matchRegions();

            // Set body regions attribute
            vars.body.attr('regions', vars.geolocate.regions.join(','));

			// Trigger custom event 'geolocateready'
			log('geolocateready event');
			vars.geolocate.ready = true;
			vars.document.trigger('geolocateready');
		}
	};

	// A ready utility function that works like jQuery(document).ready()
	const ready = (callback) => {
		if (vars.geolocate.ready === true) {
			callback();
		} else {
			vars.document.on('geolocateready', callback);
		}
	};

	// Check if a point is inside a polygon ring (ray casting)
	// ring is a GeoJSON coordinate array: [[lon, lat], [lon, lat], ...]
	const inPolygon = (testLon: number, testLat: number, ring: number[][]): boolean => {
		if (ring.length < 3) return false;

		let j = ring.length - 1, oddNodes = false;

		for (let i = 0; i < ring.length; i++) {
			const iLon = ring[i][0], iLat = ring[i][1];
			const jLon = ring[j][0], jLat = ring[j][1];

			if ((iLat < testLat && jLat >= testLat) || (jLat < testLat && iLat >= testLat)) {
				if (iLon + (testLat - iLat) / (jLat - iLat) * (jLon - iLon) < testLon) {
					oddNodes = !oddNodes;
				}
			}
			j = i;
		}
		return oddNodes;
	};

	// Check if a point is inside a GeoJSON Polygon coordinates array (with hole support)
	// coords format: [outerRing, hole1, hole2, ...] where each ring is [[lon, lat], ...]
	const inPolygonCoords = (testLon: number, testLat: number, coords: number[][][]): boolean => {
		if (!coords[0] || coords[0].length < 3) return false;

		// Must be inside outer ring
		if (!inPolygon(testLon, testLat, coords[0])) return false;

		// Must NOT be inside any hole
		for (let i = 1; i < coords.length; i++) {
			if (coords[i] && inPolygon(testLon, testLat, coords[i])) return false;
		}

		return true;
	};

	// Check if a point is inside a GeoJSON object
	// Supports FeatureCollection, Feature, Polygon, MultiPolygon
	const pointInGeoJSON = (testLon: number, testLat: number, geojson: any): boolean => {
		const type = geojson?.type;

		// FeatureCollection: any feature matches
		if (type === 'FeatureCollection' && Array.isArray(geojson.features)) {
			for (const feature of geojson.features) {
				if (feature && pointInGeoJSON(testLon, testLat, feature)) return true;
			}
			return false;
		}

		// Feature: check geometry
		if (type === 'Feature') {
			if (!geojson.geometry) return false;
			return pointInGeoJSON(testLon, testLat, geojson.geometry);
		}

		// Polygon
		if (type === 'Polygon' && Array.isArray(geojson.coordinates)) {
			return inPolygonCoords(testLon, testLat, geojson.coordinates);
		}

		// MultiPolygon: any polygon matches
		if (type === 'MultiPolygon' && Array.isArray(geojson.coordinates)) {
			for (const polyCoords of geojson.coordinates) {
				if (Array.isArray(polyCoords) && inPolygonCoords(testLon, testLat, polyCoords)) return true;
			}
			return false;
		}

		return false;
	};

	// Match the user's location against configured regions
	const matchRegions = () => {
		const lat = vars.geolocate.location.lat;
		const lon = vars.geolocate.location.lon;

		cfg.geolocate.regions.forEach((region) => {
			if (!region.alias || !region.geojson) return;
			if (pointInGeoJSON(lon, lat, region.geojson) && !vars.geolocate.regions.includes(region.alias)) {
				vars.geolocate.regions.push(region.alias);
			}
		});

		// Set body tag attribute
		vars.body.attr('regions', vars.geolocate.regions.join(','));
	};

	// Show/hide elements based on region
	const regionDisplay = (elems?) => {
		/**
		 *
		 * Checks for elements with attribute data-region-display,or class names that start with region-
		 * region-alias
		 * region-action-show, region-action-hide
		 * region-display-block, region-display-inline, region-display-flex
		 * and shows/hides elements as needed
		 *
		 * [data-region-display] attribute includes a JSON string with the following structure:
		 *
		 * {
		 * 	regions: [			//  a string or an array of region aliases
		 * 		'nyc',
		 * 		'philly'
		 * 	],
		 * 	action: 'show',	// optional, defaults to 'show', set to 'hide' to hide matching elements instead of showing them
		 *  display: 'block' // optional, defaults to 'block', set to the desired CSS display type e.g. inline, flex, etc.
		 * }
		 *
		 * NOTE:
		 *  - if conflicting rules are found, only the first rule found will be applied
		 *  - rules are processed in this order: data-region-display rules, region-show- classes, region-hide- classes
		 *
		 */

        // Guard against being called as a jQuery event handler
        if (elems instanceof jQuery.Event) elems = undefined;

        if (elems == undefined) {
            elems = jQuery(cfg.geolocate.regionDisplaySelector);
        }

		if (elems instanceof Node) {
			// Not an array, convert to an array
			elems = [elems];
		}
		else if (elems instanceof jQuery) {
			// Convert jQuery to array
			elems = elems.toArray();
		}

        if (elems.length) {
            elems.forEach((elem) => {
                elem = jQuery(elem);

				let elemOpts = {
					regions: [],
					action: 'show',
					display: 'block'
				};
				let elemRegionMatch = false;

				// Get attribute options first
				let elemAttribOpts = elem.attr('data-region-display');
				if (typeof elemAttribOpts != 'undefined') {
					elemAttribOpts = JSON.parse(elemAttribOpts);
					if (typeof elemAttribOpts == 'object') {
						elemOpts = elemAttribOpts;
						if (typeof elemOpts.regions == 'string') elemOpts.regions = [elemOpts.regions];
						elemOpts.regions.forEach((region) => {
							if (vars.geolocate.regions.indexOf(region) != -1) elemRegionMatch = true;
						});
					}
				}

				// Get classes
				const elemClasses = elem.attr('class').split(/\s+/);
				elemClasses.forEach((elemClass) => {
					if (elemClass.indexOf('region-action-') == 0) elemOpts.action = elemClass.replace('region-action-', '');
					else if (elemClass.indexOf('region-display-') == 0) elemOpts.display = elemClass.replace('region-display-', '');
					else if (elemClass.indexOf('region-name-') == 0) elemOpts.regions.push(elemClass.replace('region-name-', ''));
				});
				elemOpts.regions.forEach((region) => {
					if (vars.geolocate.regions.indexOf(region) != -1) elemRegionMatch = true;
				});

				// Show/hide element
				if (!('action' in elemOpts)) elemOpts.action = 'show';
				if (!('display' in elemOpts)) elemOpts.display = 'block';
				if (elemRegionMatch) {
					if (elemOpts.action == 'show') elem.css('display', elemOpts.display);
					else if (elemOpts.action == 'hide') {
						if (cfg.geolocate.removeNoRegionMatch) elem.remove();
						else elem.css('display', 'none');
					}
				}
				else if (!elemRegionMatch && cfg.geolocate.handleNoRegionMatch) {
					if (elemOpts.action == 'show') {
						if (cfg.geolocate.removeNoRegionMatch) elem.remove();
						elem.css('display', 'none');
					}
					else if (elemOpts.action == 'hide') elem.css('display', elemOpts.display);
				}
			});
		}
	};

	return Object.defineProperties({
		init,
		ready,
        regionDisplay
	}, {
		// Set the cfg and vars properties as read-only
		location: {
			get() {
				return vars.geolocate.location;
			},
			set() {
				return undefined;
			}
		},
		regions: {
			get() {
				return vars.geolocate.regions;
			},
			set() {
				return undefined;
			}
		},
		status: {
			get() {
				return vars.geolocate.status;
			},
			set() {
				return undefined;
			}
		}
	}) as {
		init: (customCfg?: object) => void,
		ready: (callback: () => void) => void,
        regionDisplay: (elems?: any) => void,
		location: object,
		regions: string[],
		status: object
	};
})();

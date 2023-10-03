/**
 * geolocate.ts - Geolocation and regionalization functionality
 *
 * @version     2.3.3
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
import { mutation } from './mutation';
import { util } from './util';
declare const jQuery;

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
			ready: {
				ip: false,
				gps: false
			}
		};

		// Configuration
		cfg.geolocate = {
			gps: false, // Set to true to enable GPS geolocation
			useCookies: false, // Set to true to use cookies to store geolocation data
			cookieExpirationIP: 900, // In seconds
			cookieExpirationGPS: 900, // In seconds
			handleNoRegionMatch: true, // Set to true to actively force show/display of unmatched elements, false to do nothing
			removeNoRegionMatch: true // Set to true to remove from the DOM unmatched elements, set to false to hide them (display: none)
		};

		if (customCfg) cfg.geolocate = jQuery.extend(true, cfg.geolocate, customCfg);

		// Initialize only if enabled
		if (cfg.geolocate.enabled) {
			log('Initializing geolocate');

			// Start geolocation
			geoLocate();

			// Add a mutation handler for accordions added to the DOM
			mutation.addHandler('addNode', '[data-region-display], [class*="region-name-"]', regionDisplay);
		}

		// Run only once
		vars.geolocate.init = true;
	};

	// Attempts to locate position of user by means of gps or ip address
	const geoLocate = () => {
		if (cfg.geolocate.useCookies) {
			log('Attempting to geolocate from cookies');

			// Get data from cookies
			vars.cookies.ip = util.cookie('lqx.geolocate.cookies.ip');
			if (vars.cookies.ip !== null) vars.cookies.ip = JSON.parse(vars.cookies.ip);

			if (vars.cookies.ip !== null) {
				vars.location = Object.assign({}, vars.cookies.ip);
				vars.location.source = 'ip2geo-cookie';
				vars.ready.ip = true;
			}
			else getIP();

			vars.cookies.gps = util.cookie('lqx.geolocate.cookies.gps');
			if (vars.cookies.gps !== null) vars.cookies.gps = JSON.parse(vars.cookies.gps);

			if (cfg.geolocate.gps && 'geolocation' in window.navigator) {
				if (vars.cookies.gps !== null) {
					vars.location = Object.assign({}, vars.cookies.gps);
					vars.location.source = 'gps-cookie';
					vars.ready.gps = true;
				}
				else getGPS();
			}
			else vars.ready.gps = true;

			bodyGeoData();
		}
		else {
			getIP();
			if (cfg.geolocate.gps && 'geolocation' in window.navigator) getGPS();
		}
	};

	// Geolocation from IP
	const getIP = () => {
		log('Attempting IP geolocation');
		// ip2geo to get location info
		jQuery.ajax({
			async: true,
			cache: false,
			dataType: 'json',
			url: vars.tmplURL + '/php/ip2geo/',
			success: (data, ready, xhr) => {
				vars.location = data;
				vars.location.source = 'ip2geo';
				vars.ready.ip = true;

				log('IP geolocation result', vars.location);

				// Save cookie
				if (cfg.geolocate.useCookies) util.cookie('lqx.geolocate.locationIP', JSON.stringify(vars.location), { maxAge: cfg.geolocate.cookieExpirationIP, path: '/', secure: true });

				bodyGeoData();
			},
			error: (xhr, ready, error) => {
				error('Geolocate error ' + ready + ' ' + error);
			}
		});
	};

	// Geolocation from GPS
	const getGPS = () => {
		if ('geolocation' in window.navigator) {
			log('Attempting GPS geolocation');
			window.navigator.geolocation.getCurrentPosition((position) => {
				vars.location.lat = position.coords.latitude;
				vars.location.lon = position.coords.longitude;
				vars.location.radius = position.coords.accuracy / 1000; // in km
				vars.location.source = 'gps';
				vars.ready.gps = true;

				log('GPS geolocation result', vars.location);

				// Save cookie
				if (cfg.geolocate.useCookies) util.cookie('lqx.geolocate.locationGPS', JSON.stringify(vars.location), { maxAge: cfg.geolocate.cookieExpirationGPS, path: '/', secure: true });

				bodyGeoData();
			});
		}
		else vars.ready.gps = true;
	};

	// Save results to body attributes and trigger geolocateready event
	const bodyGeoData = () => {
		if (vars.ready.ip && vars.ready.gps) {
			// Add location attributes to body tag
			for (const key in vars.location) {
				if (key == 'time_zone') {
					vars.body.attr('time-zone', vars.location.time_zone);
				}
				else if (['source', 'ip'].indexOf(key) == -1) {
					vars.body.attr(key, vars.location[key]);
				}
			}

			// Trigger custom event 'geolocateready'
			log('geolocateready event');
			jQuery(document).trigger('geolocateready');
		}
	};

	// Check if a point is inside a circle
	const inCircle = (test, center, radius) => {
		/** Accepts:
		 * test: location to test, object with keys lat and lon
		 * center: circle center point, object with keys lat and lon
		 * radius: circle radius in kilometers
		 */
		const deg2rad = (deg) => { return deg * Math.PI / 180; };
		const dLat = deg2rad(test.lat - center.lat);
		const dLon = deg2rad(test.lon - center.lon);
		const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
			Math.cos(deg2rad(center.lat)) * Math.cos(deg2rad(test.lat)) * Math.sin(dLon / 2) * Math.sin(dLon / 2);
		const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
		const d = 6371 * c; // Distance in km
		return (d <= radius && true) || false;
	};

	// Check if a point is inside a square
	const inSquare = (test, corner1, corner2) => {
		/** Accepts:
		 * test: location to test, object with keys lat and lon
		 * corner1: a corner of the square, object with keys lat and lon
		 * corner2: opposite corner of the square, object with keys lat and lon
		 * Known limitation: doesn't handle squares that cross the poles or the international date line
		 */
		return test.lat <= Math.max(corner1.lat, corner2.lat) &&
			test.lat >= Math.min(corner1.lat, corner2.lat) &&
			test.lon <= Math.max(corner1.lon, corner2.lon) &&
			test.lon >= Math.min(corner1.lon, corner2.lon);
	};

	// Check if a point is inside a polygon
	const inPolygon = (test, poly) => {
		/** Accepts:
		 * test: location to test, object with keys lat and lon
		 * poly: defines the polygon, array of objects, each with keys lat and lon
		 * Based on http://alienryderflex.com/polygon/
		 * Known limitation: doesn't handle polygons that cross the poles or the international date line
		 */
		let i, j = poly.length - 1, oddNodes = false;

		for (i = 0; i < poly.length; i++) {
			if (poly[i].lat < test.lat && poly[j].lat >= test.lat || poly[j].lat < test.lat && poly[i].lat >= test.lat) {
				if (poly[i].lon + (test.lat - poly[i].lat) / (poly[j].lat - poly[i].lat) * (poly[j].lon - poly[i].lon) < test.lon) {
					oddNodes = !oddNodes;
				}
			}
			j = i;
		}
		return oddNodes;
	};

	const geoJSONtoRegions = (geoJSON) => {
		// Parse a geoJSON string and return an object with regions definition
		// geoJSON is a string in the format below
		// {
		// 	"type": "FeatureCollection",
		// 	"features": [
		// 		{
		// 			"type": "Feature",
		// 			"properties": {
		// 				"region": "nyc"
		// 			},
		// 			"geometry": {
		// 				"type": "Polygon",
		// 				"coordinates": [
		// 					[
		// 						[-74.259, 40.477],
		// 						[-73.700, 40.477],
		// 						[-73.700, 40.917],
		// 						[-74.259, 40.917],
		// 						[-74.259, 40.477]
		// 					]
		// 				]
		// 			}
		// 		},
		// 		{
		// 			"type": "Feature",
		// 			"properties": {
		// 				"region": "philly"
		// 			},
		// 			"geometry": {
		// 				"type": "Polygon",
		// 				"coordinates": [
		// 					[
		// 						[-75.280, 39.867],
		// 						[-74.959, 39.867],
		// 						[-74.959, 40.137],
		// 						[-75.280, 40.137],
		// 						[-75.280, 39.867]
		// 					]
		// 				]
		// 			}
		// 		}
		// 	]
		// }

		// Initialize regions object
		const regions = {};

		// Parse geoJSON
		geoJSON = JSON.parse(geoJSON);

		// Loop through features
		geoJSON.features.forEach((feature) => {
			// Get region name
			const region = feature.properties.region;

			// Initialize region if needed
			if (!(region in regions)) regions[region] = { polygons: [] };

			// Loop through coordinates
			feature.geometry.coordinates.forEach((polygon) => {
				const polygonPoints: { lat: number; lon: number }[] = [];
				polygon.forEach((point) => {
					polygonPoints.push({ lat: point[1], lon: point[0] });
				});

				// Remove the last point, as geoJSON makes it the same as the first point
				polygonPoints.pop();

				// Add polygon to region
				regions[region].polygons.push(polygonPoints);
			});
		});

		return regions;
	};

	// Process regions definition data and find the region of current location
	const setRegions = (regions) => {
		/**
		 * Receives the regions definition as an object in the format below
		 * and then calls regionDisplay()
		 * Should be called after geolocateready event
		 *
		 * {
		 *		region1: {
		 * 	 		circles: [
		 * 				{lat: centerLat, lon: centerLon, radius: circleTadius},
		 * 				...
		 * 				{lat: centerLat, lon: centerLon, radius: circleTadius}
		 * 			],
		 * 			squares: [
		 * 				{corner1: {lat: corner1Lat, lon: corner1Lon}, corner2: {lat: corner2Lat, lon: corner2Lon}},
		 * 				...
		 * 				{corner1: {lat: corner1Lat, lon: corner1Lon}, corner2: {lat: corner2Lat, lon: corner2Lon}}
		 * 			],
		 * 			polygons: [
		 * 				[{lat: point1Lat, lon: point1Lon},..., {lat: pointNLat, lon: pointNLon}].
		 * 				...
		 * 				[{lat: point1Lat, lon: point1Lon},..., {lat: pointNLat, lon: pointNLon}]
		 * 			]
		 * 		}
		 * }
		 */

		// Get current lat / lon
		const here = {
			lat: vars.location.lat,
			lon: vars.location.lon
		};

		// Check what regions match
		Object.keys(regions).forEach((region) => {
			// Check circles
			if ('circles' in regions[region]) {
				regions[region].circles.forEach((x) => {
					if (inCircle(here, { lat: x.lat, lon: x.lon }, x.radius) && !vars.regions.includes(region)) vars.regions.push(region);
				});
			}

			// Check squares
			if ('squares' in regions[region]) {
				regions[region].squares.forEach((x) => {
					if (inSquare(here, { lat: x.corner1.lat, lon: x.corner1.lon }, { lat: x.corner2.lat, lon: x.corner2.lon }) && !vars.regions.includes(region)) vars.regions.push(region);
				});
			}

			// Check polygons
			if ('polygons' in regions[region]) {
				regions[region].polygons.forEach((x) => {
					if (inPolygon(here, x) && !vars.regions.includes(region)) vars.regions.push(region);
				});
			}
		});

		// Set body tag attribute
		vars.body.attr('regions', vars.regions.join(','));

		// Return the array of regions of the current location
		return vars.regions;
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
		 * 	regions: [			//  a string or an array of region ids (names or numbers) as provided via setRegions function
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

		// If no elements are passed, then get the default list of elements
		if (elems == undefined) {
			elems = jQuery('[data-region-display], [class*="region-name-"]');
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

				let elemOpts;
				elemOpts.region = [];
				let elemRegionMatch = false;

				// Get attribute options first
				let elemAttribOpts = elem.attr('data-region-display');
				if (typeof elemAttribOpts != 'undefined') {
					elemAttribOpts = JSON.parse(elemAttribOpts);
					if (typeof elemAttribOpts == 'object') {
						elemOpts = elemAttribOpts;
						if (typeof elemOpts.regions == 'string') elemOpts.regions = [elemOpts.regions];
						elemOpts.regions.forEach((region) => {
							if (vars.regions.indexOf(region) != -1) elemRegionMatch = true;
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
					if (vars.regions.indexOf(region) != -1) elemRegionMatch = true;
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
		setRegions,
		regionDisplay,
		geoJSONtoRegions
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
		ready: {
			get() {
				return vars.geolocate.ready.ip && vars.geolocate.ready.gps;
			},
			set() {
				return undefined;
			}
		}
	});
})();

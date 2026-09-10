/**
 * map.ts - Map block functionality
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

import { vars, cfg, log, warn } from './core';
import { mutation } from './mutation';

declare const google, jQuery;
/**
 * @module map
 *
 * @param {object} customCfg - Optional custom configuration for the map module.
 *
 * @returns {object} An object with methods to initialize maps.
 */
export const map = (() => {

	const init = (customCfg?: object) => {
		// Run only once
		if (vars.map?.init) return;

		vars.map = {
			init: false,
			maps: [],
		};

		// Default module configuration
		cfg.map = {
			enabled: true,
			blockSelector: '.lqx-block-map',
			analytics: {
				enabled: true,
				nonInteraction: true,
			},
		};

		if (customCfg) cfg.map = jQuery.extend(true, cfg.map, customCfg);

		// Initialize only if enabled
		if (cfg.map.enabled) {
			log('Initializing map');

			// Disable analytics if the analytics module is not enabled
			cfg.map.analytics.enabled = cfg.analytics.enabled ? cfg.map.analytics.enabled : false;
			if (cfg.map.analytics.enabled) log('Setting maps tracking');

			// Initialize maps
			vars.document.ready(() => {
				// Setup maps loaded initially on the page
				setup(jQuery(cfg.map.blockSelector));

				mutation.addHandler('addNode', cfg.map.blockSelector, setup);
			});

		}

		// Run only once
		vars.map.init = true;
	};

	const setup = (elems) => {
		if (elems.length) {
			elems.each(function(block){
				log('Setting up ' + elems.length + ' maps', elems);

				let settings, styles, items;
				try {
					settings = JSON.parse(jQuery(elems[block]).attr('data-settings'));
				} catch (e) {
					warn('Map element has invalid JSON settings', elems[block]);
					return;
				}

				if (!settings?.google_maps_display_settings) {
					warn('Map element is missing google_maps_display_settings', elems[block]);
					return;
				}

				try {
					styles = JSON.parse(settings.google_maps_display_settings.snazzy_maps_styles);
				} catch (e) {
					styles = [];
				}

				try {
					items = JSON.parse(jQuery(elems[block]).attr('data-items'));
					if (!Array.isArray(items)) items = [];
				} catch (e) {
					warn('Map element has invalid JSON items', elems[block]);
					return;
				}

				const lqxMap = {
					map: null,
					options: {
						mapTypeId: settings.google_maps_display_settings.type_of_map,
						scrollWheel: (settings.google_maps_display_settings.enable_zoom == 'y' ? true : false),
						mapTypeControl: false,
						panControl: (settings.google_maps_display_settings.enable_pan == 'y' ? true : false),
						zoomControl: (settings.google_maps_display_settings.enable_zoom == 'y' ? true : false),
						gestureHandling: (settings.google_maps_display_settings.enable_pan == 'y' ? 'normal' : 'none'),
						streetViewControl: false,
						fullscreenControl: false,
						styles: styles,
						zoom:  parseInt(settings.google_maps_display_settings.default_zoom_level),
					},
					center: new google.maps.LatLng(0,0),
					bounds: new google.maps.LatLngBounds(),
					items: items,
					infoWindows: {},
					markers: {},
					openInfoWindow: null,
					groupedItems: [],
					settings: JSON.parse(jQuery(elems[block]).attr('data-settings'))
				};
				//initialize options based off of the json settings
				lqxMap.options.mapTypeId = google.maps.MapTypeId[lqxMap.options.mapTypeId];
				const mapSelector = jQuery(elems[block]).find('.map').attr('id');
				lqxMap.map = new google.maps.Map(document.getElementById(mapSelector), lqxMap.options, lqxMap.options);
				for (let i = 0; i < lqxMap.items.length; i++) {
					const itemLatLon = new google.maps.LatLng(lqxMap.items[i].lat, lqxMap.items[i].lon);
					lqxMap.bounds.extend(itemLatLon);
					const itemid = lqxMap.items[i].item_id;
					if (lqxMap.items[i].infoWindow == "true") lqxMap.infoWindows[itemid] = new google.maps.InfoWindow({ content: lqxMap.items[i].html });
					//const labelString = lqxMap.items[i].title;
					const infoWindowHTML = lqxMap.items[i].html;

                    const markerParams: google.maps.MarkerOptions & { html?: string } = {
						position: itemLatLon,
						map: lqxMap.map,
						title: lqxMap.items[i].title,
						html: infoWindowHTML,
						icon: lqxMap.items[i].icon,
						//for future work: the code below and commented out above pertains to labels on top of pins, which I don't believe we've used yet but could be useful going forward
						//label: (labelString == '' ? '' : { text: labelString.toString(), color: 'white' })
					};
                    if (lqxMap.items[i].icon) {
                        markerParams.icon = {
                            url: lqxMap.items[i].icon,
                            //scaledSize: new google.maps.Size(lqxMap.markerSize.scaledWidth,lqxMap.markerSize.scaledHeight)
                        };
                    }
					lqxMap.markers[itemid] = new google.maps.Marker(markerParams);
					if (lqxMap.items[i].infoWindow == "true") {
						google.maps.event.addListener(lqxMap.markers[itemid], 'click', function() {
							if (lqxMap.openInfoWindow !== null) {
								lqxMap.openInfoWindow.close();
							}
							lqxMap.openInfoWindow = lqxMap.infoWindows[itemid];
							lqxMap.infoWindows[itemid].setContent(this.html);
							lqxMap.infoWindows[itemid].open(lqxMap.map,this);
						});
					}
				}

				lqxMap.map.fitBounds(lqxMap.bounds);
				lqxMap.map.panToBounds(lqxMap.bounds);

				//once the map has finished loading, set the zoom level of the map
				google.maps.event.addListenerOnce(lqxMap.map, 'idle', () => {
					lqxMap.map.setZoom(lqxMap.options.zoom); // Adjust zoom after bounds are set
				});
				vars.map.maps.push(lqxMap);
			});
		}
	};
	const getLocation = function(currentLocation = false){
		if (currentLocation == false) {
			const geocoder = new google.maps.Geocoder();
			geocoder.geocode({'address': vars.regions.searchQuery, 'region': 'us'}, function(results, status) {
				if (status == google.maps.GeocoderStatus.OK) {
					searchLocations(results[0].geometry.location.lat(), results[0].geometry.location.lng());
				}
			});
		} else {
			navigator.geolocation.getCurrentPosition(function(loc) {
				lqx.log('navigator.done ', loc.coords.latitude, loc.coords.longitude);
				const searchQueryEl = jQuery(cfg.regions.searchSelector);
				searchQueryEl.attr('data-lat', loc.coords.latitude);
				searchQueryEl.attr('data-lon', loc.coords.longitude);
				searchLocations(loc.coords.latitude, loc.coords.longitude);
			});
		}
	};
	const searchLocations = function(lat1, lon1) {
		var locdis = [];
		const filterMiles = parseInt(jQuery(cfg.regions.milesSelector).val());
		vars.regions.map.bounds = new google.maps.LatLngBounds();
		const itemLatLon = new google.maps.LatLng(lat1, lon1);
		const deg2rad = function(deg) { return deg * (Math.PI / 180); };
		vars.regions.map.bounds.extend(itemLatLon);
		jQuery(cfg.regions.searchSelector).attr('data-lat', lat1);
		jQuery(cfg.regions.searchSelector).attr('data-lon', lon1);
		jQuery(vars.regions.mapItems).each(function(index) {
			var lat2 = jQuery(this).attr('data-lat');
			var lon2 = jQuery(this).attr('data-lon');
			var R = 3963.1676;
			var dLat = deg2rad(lat2 - lat1);
			var dLon = deg2rad(lon2 - lon1);
			var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) + Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) * Math.sin(dLon / 2) * Math.sin(dLon / 2);
			var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
			var d = R * c;
			jQuery(this).attr('data-distance', d);
			//lqx.log(d, parseFloat(filterMiles));
			jQuery(this).find('.distance').html(' (' + d.toFixed(1) + ' miles)');
			var type = jQuery('.menu-shared li.active').attr('data-key');
			// create newHref
			var newHref = 'http://maps.google.com/maps?saddr=' + lat1 + ',' + lon1 + '&daddr=' + lat2 + ',' + lon2;
			jQuery(this).find('a.get-direction').attr('href', newHref);
			if (d > parseFloat(filterMiles)) {
				jQuery(this).hide();
				vars.regions.map.markers[jQuery(this).attr('data-marker-index')].setVisible(false);
			} else {
					jQuery(this).show();
					var itemLatLon = new google.maps.LatLng(lat2, lon2);
					vars.regions.map.bounds.extend(itemLatLon);
					vars.regions.map.markers[jQuery(this).attr('data-marker-index')].setVisible(true);
				}
		});
		// sort array by distance
		// debugger;
		// lqx.log('starting the sort function for locdis');
		var list = jQuery('.regions .office-list');              // parent UL
		var items = list.children('li');     // child LIs

		items.sort(function(a, b) {
			return jQuery(a).data("distance") - jQuery(b).data("distance");
		});
		list.append(items);

		vars.regions.map.fitBounds(vars.regions.map.bounds);
		vars.regions.map.setCenter(new google.maps.LatLng(lat1, lon1));
		// place search marker
		if (vars.searchMarker) {
			vars.searchMarker.setMap(null);
		}
		vars.regions.searchMarker = new google.maps.Marker({
			position: new google.maps.LatLng(lat1, lon1),
			map: vars.regions.map,
			icon: {
				// gold star
				path: 'M 125,5 155,90 245,90 175,145 200,230 125,180 50,230 75,145 5,90 95,90 z',
				fillColor: 'gold',
				fillOpacity: 1,
				scale: 0.1,
				strokeColor: 'goldenrod',
				strokeWeight: 2
			}
		});
	};
	let updateItems = function(items) {
		let target = vars.map.maps[0];
		Object.keys(target.markers).forEach(function(marker) {
			target.markers[marker].setMap(null);
		});
		target.markers = {};
		target.bounds = new google.maps.LatLngBounds();
		target.items = items;
		target.infoWindows = {};
		target.items.forEach(function(item) {
			const itemLatLon = new google.maps.LatLng(item.lat, item.lon);
			target.bounds.extend(itemLatLon);
			const itemid = item.id;
			if (item.infoWindow == "true") target.infoWindows[itemid] = new google.maps.InfoWindow({ content: item.html });
			//const labelString = item.find('.title').text();
			const infoWindowHTML = item.html;
			const markerParams = {
				position: itemLatLon,
				map: target.map,
				title: item.title,
				html: infoWindowHTML,
				icon: target.settings['google_maps_display_settings']['pin_override'].url,
				//for future work: the code below and commented out above pertains to labels on top of pins, which I don't believe we've used yet but could be useful going forward
				//label: (labelString == '' ? '' : { text: labelString.toString(), color: 'white' })
			};
			target.markers[itemid] = new google.maps.Marker(markerParams);

			if (item.infoWindow == "true") {
				google.maps.event.addListener(target.markers[itemid], 'click', function() {
					if (target.openInfowWindow !== null) {
						target.openInfowWindow.close();
					}
					target.openInfowWindow = target.infoWindows[itemid];
					target.infoWindows[itemid].setContent(item.html);
					target.infoWindows[itemid].open(target.map,target.markers[itemid]);
				});
			}
		});
		target.map.fitBounds(target.bounds);
		target.map.panToBounds(target.bounds);
	};
	return {
		init,
		setup,
		getLocation,
		updateItems
	};

})();

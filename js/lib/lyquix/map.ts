/**
 * map.ts - Map block functionality
 *
 * @version     3.0.0
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

import { vars, cfg, log } from './core';

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
			mapSelector: '#lqx-map',
			analytics: {
				enabled: true,
				nonInteraction: true,
			}
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

			});

		}

		// Run only once
		vars.map.init = true;
	};

	const setup = (elems) => {
		if (elems.length) {
			elems.each(function(block){
				log('Setting up ' + elems.length + ' maps', elems);
				const settings = JSON.parse(jQuery(elems[block]).attr('data-settings'));
				const styles = JSON.parse(settings.google_maps_display_settings.snazzy_maps_styles);
				const lqx_map = {
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
					items: JSON.parse(jQuery(elems[block]).attr('data-items')),
					infoWindows: {},
					markers: {},
					groupedItems: [],
				};
				//initialize options based off of the json settings
				lqx_map.options.mapTypeId = google.maps.MapTypeId[lqx_map.options.mapTypeId];
				lqx_map.map = new google.maps.Map(document.getElementById('lqx-map'), lqx_map.options);
				for (let i = 0; i < lqx_map.items.length; i++) {
					const itemLatLon = new google.maps.LatLng(lqx_map.items[i].lat, lqx_map.items[i].lon);
					lqx_map.bounds.extend(itemLatLon);
					const itemid = lqx_map.items[i].id;
					if (lqx_map.items[i].infoWindow == 'true') lqx_map.infoWindows[itemid] = new google.maps.InfoWindow({ content: lqx_map.items[i].html });
					//const labelString = lqx_map.items[i].title;
					const infoWindowHTML = lqx_map.items[i].html;

					const markerParams = {
						position: itemLatLon,
						map: lqx_map.map,
						title: lqx_map.items[i].title,
						html: infoWindowHTML,
						icon: lqx_map.items[i].icon,
						//for future work: the code below and commented out above pertains to labels on top of pins, which I don't believe we've used yet but could be useful going forward
						//label: (labelString == '' ? '' : { text: labelString.toString(), color: 'white' })
					};
					if(lqx_map.items[i].icon != '') markerParams.icon = {
						url: lqx_map.items[i].icon,
						//scaledSize: new google.maps.Size(lqx_map.markerSize.scaledWidth,lqx_map.markerSize.scaledHeight)
					};
					lqx_map.markers[itemid] = new google.maps.Marker(markerParams);

					if (lqx_map.items[i].infoWindow == 'true') {

						google.maps.event.addListener(lqx_map.markers[itemid], 'click', function() {
							lqx_map.infoWindows[itemid].setContent(this.html);
							lqx_map.infoWindows[itemid].open(lqx_map.map,this);
						});
					}
				}
				lqx_map.map.fitBounds(lqx_map.bounds);
				lqx_map.map.panToBounds(lqx_map.bounds);
			});
		}
	};

	return {
		init,
		setup
	};

})();

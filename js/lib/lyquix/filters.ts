/**
 * filters.ts - Filters block functionality
 *
 * @version     3.2.0
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

import { vars, cfg, log, warn, error } from './core';
import { mutation } from './mutation';
import { analytics } from './analytics';
import { util } from './util';

declare const google;
/**
 * The below filters module provides functionality for managing filters on a web page, enabling users to refine content
 * based on various criteria such as categories, tags, search queries, and pagination. It includes methods
 * for initializing filters, handling user interactions, updating the UI based on filter selections, and
 * making API calls to retrieve filtered content. The module supports both JavaScript and PHP rendering modes,
 * hash-based navigation for preserving filter states, and analytics tracking for user interactions.
 *
 * @module filters
 *
 * @param {object} customCfg - Optional custom configuration for the filters module.
 *
 * returns {object} An object with methods to initialize filters, handle user interactions, update the UI, and make API calls.
 */
export const filters = (() => {

	const init = (customCfg?: object) => {
		// Run only once
		if (vars.filters?.init) return;

		vars.filters = {
			init: false,
			hash: null,
			filters: {},
			useHashFilterId: null,
			cache: {}
		};

		// Default module configuration
		cfg.filters = {
			enabled: true,
			filtersSelector: '.lqx-block-filters > .filters',
			// Controls
			controlsSelector: '.controls',
			openButtonSelector: '.open-close-wrapper > .open',
			closeButtonSelector: '.open-close-wrapper > .close',
			controlTabsSelector: '.control-tabs',
			controlWrapperSelector: '.control-wrapper',
			searchWrapperSelector: '.search-wrapper',
			searchInputSelector: '.search',
			searchButtonSelector: '.search-button',
			clearWrapperSelector: '.clear-wrapper',
			orderWrapperSelector: '.order-wrapper',
			clearButtonSelector: '.clear',
			// Posts
			postsSelector: '.posts',
			// Pagination
			paginationSelector: '.pagination',
			firstPageSelector: '.page-first',
			prevPageSelector: '.page-prev',
			nextPageSelector: '.page-next',
			lastPageSelector: '.page-last',
			pageNumberSelector: '.page-number',
			postsPerPageWrapperSelector: '.pagination > .posts-per-page-wrapper',
			postsPerPageSelector: '.posts-per-page',
			mapSelector: '.lqx-block-map',
			analytics: {
				enabled: true,
				nonInteraction: false
			}
		};

		if (customCfg) cfg.filters = jQuery.extend(true, cfg.filters, customCfg);

		// Initialize only if enabled
		if (cfg.filters.enabled) {
			log('Initializing filters');

			// Disable analytics if the analytics module is not enabled
			cfg.filters.analytics.enabled = cfg.analytics.enabled ? cfg.filters.analytics.enabled : false;
			if (cfg.filters.analytics.enabled) log('Setting filters tracking');

			// Initialize filterss
			vars.document.ready(() => {
				// Setup filterss loaded initially on the page
				setup(jQuery(cfg.filters.filtersSelector));

				// Check if there is a hash in the URL
				parseHash();

				// Add a hash change listener
				vars.window.on('hashchange', () => {
					if (vars.filters.useHashFilterId !== null && vars.filters.useHashFilterId in vars.filters.filters) parseHash();
				});

				// Add a mutation handler for filterss added to the DOM
				mutation.addHandler('addNode', cfg.filters.filtersSelector, setup);
			});

		}

		// Run only once
		vars.filters.init = true;
	};

	const setup = (elems) => {
		if (elems.length) {
			log('Setting up ' + elems.length + ' filters', elems);

			elems.each((idx, filterElem) => {
				// The filter element
				filterElem = jQuery(filterElem);

				// Get the ID of the filter element
				const id = jQuery(filterElem).attr('id');

				// Check if the filter element has an ID
				if (!id) {
					warn('Filter element does not have an ID', filterElem);
					return;
				}

				// Get the settings of the filter element
				let settings;
				try {
					settings = JSON.parse(jQuery(filterElem).attr('data-settings') || '{}');
				} catch (e) {
					warn('Filter element has invalid JSON settings', filterElem);
					return;
				}

				// Check if the filter element has settings
				if (!settings || typeof settings !== 'object') {
					warn('Filter element does not have settings', filterElem);
					return;
				}

				// Check if the filter element ID matches the settings.hash
				if (id != settings.hash) {
					warn('Filter element ID does not match settings.hash', filterElem);
					return;
				}

				// Check if the filter uses hash and there is no other filter using hash
				if (settings.use_hash == 'y' && !vars.filters.useHashFilterId) vars.filters.useHashFilterId = id;

				// Create the filter object
				const filterObj = {
					id,
					elem: filterElem,
					initial: jQuery.extend(true, {}, settings.controls), // Saves the initial controls, needed later for hashchange events
					...settings
				};

				// Save the filter element in the filters object
				vars.filters.filters[id] = filterObj;

				// Render the filter for the first time
				if (filterObj.render_mode == 'js') render(id);

				// Add listeners
				addListeners(id);
			});
		}
	};

	const render = (id) => {
		if (!(id in vars.filters.filters)) {
			warn('Filter ID not found', id);
			return;
		}

		renderControls(id);
		renderPosts(id);
		renderPagination(id);
		renderPills(id);

		// Remove loading class from the filter element
		vars.filters.filters[id].elem.removeClass('loading');
		if (typeof vars.filters.filters[id].callback === 'string' && vars.filters.filters[id].callback !==''){
			vars.filters.filters[id].callback
			.split('.')
			.reduce((o, k) => o?.[k], window)
			?.();
		}
	};

	const renderControls = (id) => {
		log('Filters renderControls');

		if (!(id in vars.filters.filters)) {
			warn('Filter ID not found', id);
			return;
		}

		const filterObj = vars.filters.filters[id];
		let controls;

		switch (filterObj.render_mode) {
			case 'js':
				// TODO handle JS rendering
				break;

			case 'php':
				// render.controls contains both .controls and .pills as siblings.
				// Extract only the .controls part so the existing pills element stays
				// in the DOM and renderPills() can update it in place.
				const $rendered = jQuery(filterObj.render.controls);
				const $newControls = $rendered.filter(cfg.filters.controlsSelector);
				controls = filterObj.elem.find(cfg.filters.controlsSelector);
				if (controls.length) controls.replaceWith($newControls.length ? $newControls : $rendered);
				else filterObj.elem.prepend($newControls.length ? $newControls : $rendered);
				break;
		}
	};

	const renderPosts = (id) => {
		log('Filters renderPosts');

		if (!(id in vars.filters.filters)) {
			warn('Filter ID not found', id);
			return;
		}

		const filterObj = vars.filters.filters[id];
		let posts;

		switch (filterObj.render_mode) {
			case 'js':
				// TODO handle JS rendering
				break;

			case 'php':
				// Also look for .no-results so it gets replaced when results return,
				// and so subsequent no-result renders replace rather than append.
				posts = filterObj.elem.find(`${cfg.filters.postsSelector}, .no-results`);
				if (posts.length) posts.replaceWith(filterObj.render.posts);
				else filterObj.elem.append(filterObj.render.posts);
				break;

			case 'maps-php':
				posts = filterObj.elem.find(cfg.filters.mapSelector);
				if (posts.length) posts.replaceWith(filterObj.render.posts);
				else filterObj.elem.append(filterObj.render.posts);
				let controls = filterObj.elem.find(cfg.filters.controlsSelector);
				if (controls.length) controls.replaceWith(filterObj.render.controls);
				else filterObj.elem.append(filterObj.render.controls);
				break;
		}


	};

	const renderPagination = (id) => {
		log('Filters renderPagination');

		if (!(id in vars.filters.filters)) {
			warn('Filter ID not found', id);
			return;
		}

		const filterObj = vars.filters.filters[id];
		let pagination;

		switch (filterObj.render_mode) {
			case 'js':
				// TODO handle JS rendering
				break;

			case 'php':
				// Check if there's an existing pagination element
				pagination = filterObj.elem.find(cfg.filters.paginationSelector);
				if (pagination.length) pagination.replaceWith(filterObj.render.pagination);
				else filterObj.elem.append(filterObj.render.pagination);
				break;
		}
	};

	const renderPills = (id) => {
		const filterObj = vars.filters.filters[id];
		if (!filterObj) return;

		const pillsWrapper = filterObj.elem.find(`#${id}-pills`);
		if (!pillsWrapper.length) return;

		pillsWrapper.empty();

		const selectedControls = filterObj.controls.filter((control) => control.selected !== '');
		if (!selectedControls.length) return;

		pillsWrapper.append(`<div class="clear-wrapper"><button class="pill clear">${filterObj.clear_label ?? 'Clear'}</button></div>`);

		selectedControls.forEach((control) => {
			const option = control.options.find((opt) => opt.value == control.selected);
			const label = option ? option.text : control.selected;
			pillsWrapper.append(`<button class="pill" data-control="${control.slug}" data-active-value="${control.selected}">${label}</button>`);
		});
	};

	// Locate filter-related elements even when they are rendered outside the controls container.
	const findFilterElems = (filterObj, selector: string) => {
		const insideFilter = filterObj.elem.find(selector);
		const idPrefix = filterObj.id + '-';
		const outsideFilter = jQuery(selector).filter((idx, el) => {
			const elemId = jQuery(el).attr('id');
			return typeof elemId === 'string' && elemId.startsWith(idPrefix);
		});
		return insideFilter.add(outsideFilter);
	};

	const addListeners = (id) => {
		log('Filters addListeners');

		if (!(id in vars.filters.filters)) {
			warn('Filter ID not found', id);
			return;
		}

		const filterObj = vars.filters.filters[id];

		// Open and close filter controls
		findFilterElems(filterObj, cfg.filters.openButtonSelector).off('click.lqxfilters').on('click.lqxfilters', (e) => {
			e.preventDefault();
			filterObj.elem.find(cfg.filters.controlsSelector).addClass('open');
		});

		findFilterElems(filterObj, cfg.filters.closeButtonSelector).off('click.lqxfilters').on('click.lqxfilters', (e) => {
			e.preventDefault();
			filterObj.elem.find(cfg.filters.controlsSelector).removeClass('open');
		});

		// Control tabs
		findFilterElems(filterObj, cfg.filters.controlTabsSelector).each((idx, controlTabs) => {
			controlTabs = jQuery(controlTabs);

			controlTabs.find('button').off('click.lqxfilters').on('click.lqxfilters', (e) => {
				e.preventDefault();
				// The button element
				const button = jQuery(e.target);

				// Remove the active class from all tabs and add it to the clicked tab
				controlTabs.find('li').removeClass('active');
				button.parent().addClass('active');

				const controlName = button.attr('data-control');
				findFilterElems(filterObj, cfg.filters.controlWrapperSelector).each((idx, controlWrapper) => {
					controlWrapper = jQuery(controlWrapper);
					if (controlWrapper.attr('data-control') == controlName) controlWrapper.addClass('active');
					else controlWrapper.removeClass('active');
				});
			});
		});

		// Controls
		findFilterElems(filterObj, cfg.filters.controlWrapperSelector).each((idx, controlWrapper) => {
			controlWrapper = jQuery(controlWrapper);
			controlWrapper.find('select, input[type="radio"], input[type="checkbox"], ul, button').each((idx, control) => {
				control = jQuery(control);
				// Get the control name
				const controlName = controlWrapper.attr('data-control');

				// Handle the different control element types
				switch (control.prop('tagName').toLowerCase()) {
					case 'select':
						control.off('change.lqxfilters').on('change.lqxfilters', () => {
							if (controlWrapper.attr('data-control-type') == 'distance'){
								const searchQueryEl = filterObj.elem.find('[data-control="distance"] .search');
								var geocoder = new google.maps.Geocoder();
								const address =  searchQueryEl.val();
								geocoder.geocode({'address': address, 'region': 'us'}, function(results, status) {
									if (status == google.maps.GeocoderStatus.OK) {
										const miles = controlWrapper.find('select[name=distance]').val();
										//build a value with the lat/lon, miles, and address searched for display in the summary and/or pills to clear
										const val = {'lat': results[0].geometry.location.lat(), 'lng': results[0].geometry.location.lng(), 'miles': miles, 'address': address};
										setDistance(id, controlName, val);
									}
								});
							} else {
								controlChange(id, controlName, control.val());
							}
						});
						break;

					case 'input':
						switch (control.attr('type')) {
							case 'radio':
								control.off('click.lqxfilters').on('click.lqxfilters', () => {
									// Check if this radio button is already checked
									const foundControl = filterObj.controls.find((curr) => curr.slug == controlName);
									const reduced = foundControl ? foundControl.selected : undefined;
									if (control.attr('value') == reduced) {
										controlChange(id, controlName, '');
										control.prop('checked', false);
									}
									else controlChange(id, controlName, control.val());
								});
								break;

							case 'checkbox':
								control.off('change.lqxfilters').on('change.lqxfilters', () => {
									// If this checkbox is now checked, uncheck all the others
									if (control.prop('checked')) {
										controlWrapper.find('input[type="checkbox"]').each((idx, checkbox) => {
											checkbox = jQuery(checkbox);
											if (checkbox.attr('name') == controlName && checkbox.attr('id') != control.attr('id')) {
												checkbox.prop('checked', false);
											}
										});
										controlChange(id, controlName, control.val());
									}
									else controlChange(id, controlName, '');
								});
								break;
						}
						break;

					case 'ul':
						control.find('li').each((idx, li) => {
							li = jQuery(li);
							li.off('click.lqxfilters').on('click.lqxfilters', () => {
								if (li.hasClass('selected')) {
									controlChange(id, controlName, '');
									control.find('li').removeClass('selected');
								} else {
									controlChange(id, controlName, li.attr('data-value'));
									control.find('li').removeClass('selected');
									li.addClass('selected');
								}
							});
						});
						// Open and close list
						controlWrapper.find('label').off('click.lqxfilters').on('click.lqxfilters', () => {
							let controlsSelector = findFilterElems(filterObj, cfg.filters.controlsSelector);
							controlsSelector = jQuery(controlsSelector);
							controlWrapper.siblings().removeClass('open');
							controlWrapper.toggleClass('open');
							if (controlWrapper.hasClass('open')) {
								controlsSelector.addClass('open-list');
							} else {
								controlsSelector.removeClass('open-list');
							}
						});
						break;
					case 'button':
						//we need listeners for both the search and locations buttons
						if (controlWrapper.attr('data-control-type') == 'distance'){
							control.off('click.lqxfilters').on('click.lqxfilters', (e) => {
								if (control.hasClass('search-button')){
									const searchQueryEl = filterObj.elem.find('[data-control="distance"] .search');
									var geocoder = new google.maps.Geocoder();
									const address =  searchQueryEl.val();
									geocoder.geocode({'address': address, 'region': 'us'}, function(results, status) {
										if (status == google.maps.GeocoderStatus.OK) {
											const miles = controlWrapper.find('select[name=distance]').val();
											//build a value with the lat/lon, miles, and address searched for display in the summary and/or pills to clear
											const val = {'lat': results[0].geometry.location.lat(), 'lng': results[0].geometry.location.lng(), 'miles': miles, 'address': address};
											setDistance(id, controlName, val);
										}
									});
								} else if (control.hasClass('location-button')) {
									navigator.geolocation.getCurrentPosition(function(loc) {
										const miles = controlWrapper.find('select[name=distance]').val();
										const val = {'lat': loc.coords['latitude'], 'lng': loc.coords['longitude'], 'miles': miles, 'address': 'Current Location'};
										setDistance(id, controlName, val);
									});
								}
							});
						}
						break;
				}
			});

		});

		// Search
		findFilterElems(filterObj, cfg.filters.searchWrapperSelector).each((idx, searchWrapper) => {
			searchWrapper = jQuery(searchWrapper);

			// Skip search inputs that belong to the distance control; they have their own handlers.
			const parentControl = searchWrapper.closest(cfg.filters.controlWrapperSelector);
			if (parentControl.length && parentControl.attr('data-control-type') === 'distance') return;

			const searchInput = searchWrapper.find(cfg.filters.searchInputSelector);

			searchInput.off('keyup.lqxfilters').on('keyup.lqxfilters', (e) => {
				if (e.keyCode == 13) {
					e.preventDefault();
					searchChange(id, searchInput.val());
				}
			});

			searchInput.off('focusout.lqxfilters').on('focusout.lqxfilters', () => {
				searchChange(id, searchInput.val());
			});

			const searchButton = searchWrapper.find(cfg.filters.searchButtonSelector);

			searchButton.off('click.lqxfilters').on('click.lqxfilters', (e) => {
				e.preventDefault();
				searchChange(id, searchInput.val());
			});
		});

		// Clear
		findFilterElems(filterObj, cfg.filters.clearWrapperSelector).each((idx, clearWrapper) => {
			clearWrapper = jQuery(clearWrapper);

			const clearButton = clearWrapper.find(cfg.filters.clearButtonSelector);

			clearButton.off('click.lqxfilters').on('click.lqxfilters', (e) => {
				e.preventDefault();
				reset(id);
			});
		});

		//reorder
		findFilterElems(filterObj, cfg.filters.orderWrapperSelector).each((idx, orderWrapper) => {
			orderWrapper = jQuery(orderWrapper);
			orderWrapper.find('.option').each((idx, option) => {
				option = jQuery(option);
				option.off('click.lqxfilters').on('click.lqxfilters', (e) => {
					e.preventDefault();
					vars.filters.filters[id].posts_order[0] = {'order_by': option.attr('data-value'), 'order': option.attr('data-order') };
					callAPI(id);
				});
			});
		});

		// Pagination
		filterObj.elem.find(cfg.filters.paginationSelector).each((idx, pagination) => {
			pagination = jQuery(pagination);

			pagination.find(cfg.filters.firstPageSelector).on('click', (e) => {
				e.preventDefault();
                if (jQuery(e.target).hasClass('inactive')) return;
				pageChange(id, 'first');
			});

			pagination.find(cfg.filters.prevPageSelector).on('click', (e) => {
				e.preventDefault();
                if (jQuery(e.target).hasClass('inactive')) return;
				pageChange(id, 'prev');
			});

			pagination.find(cfg.filters.nextPageSelector).on('click', (e) => {
				e.preventDefault();
                if (jQuery(e.target).hasClass('inactive')) return;
				pageChange(id, 'next');
			});

			pagination.find(cfg.filters.lastPageSelector).on('click', (e) => {
				e.preventDefault();
                if (jQuery(e.target).hasClass('inactive')) return;
				pageChange(id, 'last');
			});

			pagination.find(cfg.filters.pageNumberSelector).on('click', (e) => {
				e.preventDefault();
				pageChange(id, parseInt(jQuery(e.target).attr('data-page')));
			});
		});

		// Posts per page
		filterObj.elem.find(cfg.filters.postsPerPageWrapperSelector).each((idx, postsPerPageWrapper) => {
			postsPerPageWrapper = jQuery(postsPerPageWrapper);

			const postsPerPage = postsPerPageWrapper.find(cfg.filters.postsPerPageSelector);

			postsPerPage.off('change.lqxfilters').on('change.lqxfilters', () => {
				postsPerPageChange(id, postsPerPage.val());
			});
		});

		// Close controls when clicking outside
		jQuery('body').off('click.lqxfilters-' + id).on('click.lqxfilters-' + id, (e) => {
			const clickedControl = jQuery(e.target).closest(cfg.filters.controlWrapperSelector).filter((idx, el) => {
				const elemId = jQuery(el).attr('id');
				return typeof elemId === 'string' && elemId.startsWith(filterObj.id + '-');
			});

			if (!clickedControl.length) {
				findFilterElems(filterObj, cfg.filters.controlWrapperSelector).each((idx, controlWrapper) => {
					jQuery(controlWrapper).removeClass('open').removeClass('active');
					findFilterElems(filterObj, cfg.filters.controlsSelector).removeClass('open-list');
				});
			}
		});

		//pills

		filterObj.elem.find('.pills .pill').each((idx, pill) => {
			pill = jQuery(pill);
			pill.off('click.lqxfilters').on('click.lqxfilters', (e) => {
				const pillEl = jQuery(e.currentTarget);
				if (pillEl.hasClass('clear')) {
					reset(id);
					return;
				}
				const controlName = pillEl.attr('data-control');
				const controlWrappers = findFilterElems(filterObj, `${cfg.filters.controlWrapperSelector}[data-control="${controlName}"]`);
				controlWrappers.find('select').val('');
				controlWrappers.find('input[type="radio"], input[type="checkbox"]').prop('checked', false);
				controlWrappers.find('ul li').removeClass('selected');
				controlChange(id, controlName, '');
			});
		});
	};

	// TODO do we need a way to change multiple controls, search, page at once?

	const controlChange = (id, controlName, controlValue) => {
		log('Filters controlChange', id, controlName, controlValue);

		if (!(id in vars.filters.filters)) {
			warn('Filter ID not found', id);
			return;
		}

		const filterObj = vars.filters.filters[id];

		// Find the control
		const control = filterObj.controls.find((control) => control.slug == controlName);

		// Check if the control and option are valid
		if (!control) {
			warn('Invalid control name', controlName);
			return;
		}

		if (controlValue !== '') {
			// Find the option
			const option = control.options.find((option) => option.value == controlValue);

			// Check if the control and option are valid
			if (!option) {
				warn('Invalid option value', controlValue);
				return;
			}
		}

		// Update the control selected value
		control.selected = controlValue;

		// Return to page 1
		filterObj.pagination.page = 1;

		// Update the hash
		if (id == vars.filters.useHashFilterId) updateHash();

		// Call the API
		callAPI(id);

		// Send analytics event
		if (cfg.filters.analytics.enabled) {
			analytics.sendGAEvent({
				'eventCategory': 'Filters',
				'eventAction': 'Control',
				'eventLabel': `${controlName}:${controlValue}`,
				'nonInteraction': cfg.filters.analytics.nonInteraction
			});
		}
	};

	const setDistance = (id, controlName, controlValue) => {
		if (!(id in vars.filters.filters)) {
			warn('Filter ID not found', id);
			return;
		}

		const filterObj = vars.filters.filters[id];

		// Find the control
		const control = filterObj.controls.find((control) => control.slug == controlName);

		// Update the control selected value
		control.miles = controlValue.miles;
		control.lat = controlValue.lat;
		control.lng = controlValue.lng;
		control.address = controlValue.address;

		// Return to page 1
		filterObj.pagination.page = 1;

		// Update the hash
		if (id == vars.filters.useHashFilterId) updateHash();

		// Call the API
		callAPI(id);

		// Send analytics event
		if (cfg.filters.analytics.enabled) {
			analytics.sendGAEvent({
				'eventCategory': 'Filters',
				'eventAction': 'Control',
				'eventLabel': `${controlName}:${controlValue}`,
				'nonInteraction': cfg.filters.analytics.nonInteraction
			});
		}
	}

	const searchChange = (id, query) => {
		log('Filters searchChange', id, query);

		if (!(id in vars.filters.filters)) {
			warn('Filter ID not found', id);
			return;
		}

		const filterObj = vars.filters.filters[id];

		// Update the filter search
		filterObj.search = query;

		// Return to page 1
		filterObj.pagination.page = 1;

		// Update the hash
		if (id == vars.filters.useHashFilterId) updateHash();

		// Call the API
		callAPI(id);

		// Send analytics event
		if (cfg.filters.analytics.enabled) {
			analytics.sendGAEvent({
				'eventCategory': 'Filters',
				'eventAction': 'Search',
				'eventLabel': query,
				'nonInteraction': cfg.filters.analytics.nonInteraction
			});
		}
	};

	const pageChange = (id, page) => {
		log('Filters pageChange', id, page);

		if (!(id in vars.filters.filters)) {
			warn('Filter ID not found', id);
			return;
		}

		const filterObj = vars.filters.filters[id];

		if (typeof page == 'string' && isNaN(parseInt(page))) {
			switch (page) {
				case 'first':
					page = 1;
					break;

				case 'prev':
					page = filterObj.pagination.page - 1;
					if (page < 1) page = 1;
					break;

				case 'next':
					page = filterObj.pagination.page + 1;
					if (page > filterObj.pagination.total_pages) page = filterObj.pagination.total_pages;
					break;

				case 'last':
					page = filterObj.pagination.total_pages;
					break;

				default:
					warn('Invalid page keyword', page);
					return;
			}
		}

		if (typeof page == 'string') page = parseInt(page);

		if (isNaN(page) || page < 1 || page > filterObj.pagination.total_pages) {
			warn('Invalid page number', page);
			return;
		}

		// Update the filter page
		filterObj.pagination.page = page;

		// Add the "current" class to the selected page number
		filterObj.elem.find(cfg.filters.pageNumberSelector).removeClass('current');
		filterObj.elem.find(cfg.filters.pageNumberSelector + '[data-page="' + page + '"]').addClass('current');

		// Update the hash
		if (id == vars.filters.useHashFilterId) updateHash();

		// Call the API
		callAPI(id);

		// Send analytics event
		if (cfg.filters.analytics.enabled) {
			analytics.sendGAEvent({
				'eventCategory': 'Filters',
				'eventAction': 'Page',
				'eventLabel': page.toString(),
				'nonInteraction': cfg.filters.analytics.nonInteraction
			});
		}
	};

	const postsPerPageChange = (id, postsPerPage) => {
		log('Filters postsPerPageChange', id, postsPerPage);

		if (!(id in vars.filters.filters)) {
			warn('Filter ID not found', id);
			return;
		}

		const filterObj = vars.filters.filters[id];

		postsPerPage = parseInt(postsPerPage);

		if (isNaN(postsPerPage) || !filterObj.pagination.posts_per_page_options.includes(postsPerPage)) {
			warn('Invalid posts per page number', postsPerPage);
			return;
		}

		// Update the filter search
		filterObj.pagination.posts_per_page = postsPerPage;

		// Return to page 1
		filterObj.pagination.page = 1;

		// Update the hash
		if (id == vars.filters.useHashFilterId) updateHash();

		// Call the API
		callAPI(id);

		// Send analytics event
		if (cfg.filters.analytics.enabled) {
			analytics.sendGAEvent({
				'eventCategory': 'Filters',
				'eventAction': 'Posts per Page',
				'eventLabel': postsPerPage.toString(),
				'nonInteraction': cfg.filters.analytics.nonInteraction
			});
		}
	};

	const reset = (id) => {
		log('Filters reset', id);

		if (!(id in vars.filters.filters)) {
			warn('Filter ID not found', id);
			return;
		}

		const filterObj = vars.filters.filters[id];

		// Reset the controls
		filterObj.controls.forEach((control) => {
			control.selected = '';
			if (control.type == 'distance') {
				control.miles = '';
				control.address = '';
				control.lat = '';
				control.lon = '';
			}
		});

		// Update the control
		findFilterElems(filterObj, cfg.filters.controlWrapperSelector).each((idx, controlWrapper) => {
			controlWrapper = jQuery(controlWrapper);

			controlWrapper.find('select, input[type="radio"], input[type="checkbox"], ul').each((idx, control) => {
				control = jQuery(control);

				switch (control.prop('tagName').toLowerCase()) {
					case 'select':
						control.val('');
						break;

					case 'input':
						control.prop('checked', false);
						break;

					case 'ul':
						control.find('li').removeClass('selected');
						break;
				}
			});
		});

		// Reset the search
		filterObj.search = '';

		// Clear search input
		findFilterElems(filterObj, cfg.filters.searchWrapperSelector).find(cfg.filters.searchInputSelector).val('');

		// Reset the page
		filterObj.pagination.page = 1;

		// Add the "current" class to page 1
		filterObj.elem.find(cfg.filters.pageNumberSelector).removeClass('current');
		filterObj.elem.find(cfg.filters.pageNumberSelector + '[data-page="1"]').addClass('current');

		// Update the hash
		if (id == vars.filters.useHashFilterId) updateHash();

		// Call the API
		callAPI(id);
	};

	const parseHash = () => {
		log('Filters parseHash');

		/**
		 *  Assumes hash will be structured as follows:
		 *  #controlName:controlValue/controlName:controlValue/search:query/page-pagenumber
		 *  Controls, search and page separator: '/'
		 *  Control name/value separator: ':'
		 *  Search is the last or one to last in the format search:query
		 *  Page is always the last segment in the format page-N where N is the page number
		 */

		// Check if there is a filter using hash
		if (!(vars.filters.useHashFilterId in vars.filters.filters)) return;
		const filterObj = vars.filters.filters[vars.filters.useHashFilterId];

		// Get the raw hash
		const rawHash = window.location.hash.substring(1);

		// Check if there is a hash
		if (!rawHash.length) return;

		let updateNeeded = false;

		// Split the hash into segments
		const segments = rawHash.split('/');

		// Parse page number
		let page: any = segments[segments.length - 1].match(/page-(\d+)/);
		if (page !== null) {
			page = page[1];
			segments.pop();
			log('Page number from hash: ' + page);
		}
		else page = 1;
		if (page != filterObj.pagination.page) updateNeeded = true;
		filterObj.pagination.page = page;

		// Add the "current" class to the selected page number
		filterObj.elem.find(cfg.filters.pageNumberSelector).removeClass('current');
		filterObj.elem.find(cfg.filters.pageNumberSelector + '[data-page="' + page + '"]').addClass('current');

		// Parse search
		let search: any = '';
		if (segments.length) {
			search = segments[segments.length - 1].match(/search:(.+)/);
			if (search != null) {
				search = decodeURIComponent(search[1]);
				segments.pop();
				log('Search query from hash: ' + search);
			}
		}
		if (search != filterObj.search) updateNeeded = true;
		filterObj.search = search;

		// Add the query term to the search input
		findFilterElems(filterObj, cfg.filters.searchWrapperSelector).find(cfg.filters.searchInputSelector).val(search);

		// Parse controls
		if (segments.length) {
			segments.forEach((controlStr) => {
				// Parse control name and value
				const controlNameVal = controlStr.match(/([^:]+):(.+)/);

				if (controlNameVal == null) {
					warn('Invalid control segment in hash: ' + controlStr);
					return;
				}

				// Prepare a list of aliases for the controls (key: alias, value: slug)
				const controlAliases = filterObj.controls.reduce((acc, curr) => {
					if (curr.alias) acc[curr.alias] = curr.slug;
					return acc;
				}, {});

				// Check if the control name is an alias
				if (controlNameVal[1] in controlAliases) controlNameVal[1] = controlAliases[controlNameVal[1]];

				// Separate the control name and value
				const controlName = controlNameVal[1];
				const controlValueSlug = decodeURIComponent(controlNameVal[2]);

				// TODO in the future, handle multiple values for a control

				// Check if the control name is valid - use filterObj.initial to make sure we have all available options
				filterObj.controls = jQuery.extend(true, [], filterObj.initial);
				if (filterObj.controls.map((control) => control.slug).includes(controlName)) {
					// Match the control slug to get the control value
					let validControlValue = false;

					filterObj.controls.forEach((control) => {
						if (control.slug == controlName) {
							control.options.forEach((option) => {
								if (option.slug == controlValueSlug) {
									if (control.selected != option.value.toString()) updateNeeded = true;
									control.selected = option.value.toString();
									validControlValue = true;
									log('Control value from hash', controlStr, controlName, option.value);

									// Update the control
									findFilterElems(filterObj, cfg.filters.controlWrapperSelector + '[data-control="' + controlName + '"]').find('select, input[type="radio"], input[type="checkbox"], ul').each((idx, control) => {
										control = jQuery(control);

										switch (control.prop('tagName').toLowerCase()) {
											case 'select':
												control.val(option.value.toString());
												break;

											case 'input':
												control.prop('checked', control.val() == option.value.toString());
												break;

											case 'ul':
												control.find('li').each((idx, li) => {
													li = jQuery(li);
													if (li.attr('data-value') == option.value.toString()) li.addClass('selected');
													else li.removeClass('selected');
												});
												break;
										}
									});
								}
							});
						}
					});

					if (!validControlValue) warn('Invalid control value from hash: ' + controlStr);
				}
				else warn('Invalid filter name from hash: ' + controlName);
			});
		}

		if (updateNeeded) callAPI(filterObj.id);
	};

	const updateHash = () => {
		log('Filters updateHash');

		const filterObj = vars.filters.filters[vars.filters.useHashFilterId];

		const hash: string[] = [];

		// Add controls
		filterObj.controls.forEach((control) => {
			if (control.selected) {
				const option = control.options.find((option) => option.value == control.selected);
				if (option) {
					hash.push((control.alias ? control.alias : control.slug) + ':' + option.slug);
				}
			}
		});

		// Add search
		if (filterObj.search) hash.push('search:' + encodeURIComponent(filterObj.search));

		// Add page
		if (filterObj.pagination.page > 1) hash.push('page-' + filterObj.pagination.page);

		// Update the hash
		vars.filters.hash = hash.join('/');
		history.pushState(null, '', '#' + vars.filters.hash);
	};

	const callAPI = (id) => {
		log('Filters callAPI');

		if (!(id in vars.filters.filters)) {
			warn('Filter ID not found', id);
			return;
		}

		// Add loading class to the filter element
		vars.filters.filters[id].elem.addClass('loading');

		// Prepare the payload
		let payload = jQuery.extend(true, {}, vars.filters.filters[id]);

		// Remove unnecessary keys
		['elem', 'id', 'render_mode', 'use_hash'].forEach(key => delete payload[key]);
		['pagination', 'pagination_details', 'show_all',
			'show_posts_per_page', 'total_pages', 'total_posts'].forEach(key => delete payload.pagination[key]);
		payload.controls = payload.controls.map(control => {
			['custom_order', 'options', 'presentation', 'visible'].forEach(key => delete control[key]);
			return control;
		});

		payload = JSON.stringify(payload);

		// Check the cache
		const payloadHash = util.hash(payload);
		const data = getCache(payloadHash);

		if (data !== null) processAPIResponse(data);
		else {
			// Send the AJAX request
			jQuery.ajax({
				cache: false,
				contentType: 'application/json',
				data: payload,
				dataType: 'json',
				error: (xhr, status, errorMsg) => {
					error('There has been an error while connecting to the filters API', status, errorMsg);
				},
				method: 'POST',
				success: (data) => {
					setCache(payloadHash, data);
					processAPIResponse(data);
				},
				url: cfg.siteURL + '/wp-json/lyquix/v3/filters'
			});
		}
	};

	const processAPIResponse = (data) => {
		log('Filters processAPIResponse');

		// Validate the API response
		if (!data || typeof data !== 'object' || !data.hash) {
			warn('Invalid filters API response', data);
			return;
		}

		// Get the filter object
		const id = data.hash;
		const filterObj = vars.filters.filters[id];

		if (!filterObj) {
			warn('Filter ID from API response not found', id);
			return;
		}

		// Update the filter object with the received data
		vars.filters.filters[id] = jQuery.extend(true, filterObj, data);

		// Update the filter
		update(id);

	};

	const setCache = (hash, data) => {
		// Save the data in the cache
		vars.filters.cache[hash] = data;

		// Check if the cache is too large
		if (vars.filters.cache.length > 100) {
			// Remove the oldest hash from the cache
			vars.filters.cache.delete(vars.filters.cache.keys().next().value);
		}

		// Automatically remove this entry in 5 minutes
		setTimeout(() => delete vars.filters.cache[hash], 300_000);
	};

	const getCache = (hash) => {
		if (hash in vars.filters.cache) return vars.filters.cache[hash];
		else return null;
	};

	const update = (id) => {
		// Render the filter
		render(id);

		// Add listeners
		addListeners(id);

		// Scroll to top if necessary
		const postsElem = vars.filters.filters[id].elem.find(cfg.filters.postsSelector);
		if (postsElem.offset().top < jQuery(window).scrollTop() || postsElem.offset().top > jQuery(window).scrollTop() + jQuery(window).height() * 0.25) {
			jQuery('html, body').animate({
				scrollTop: postsElem.offset().top - jQuery(window).height() * 0.25
			}, 500);
		}
	};

	return {
		init,
		controlChange,
		searchChange,
		pageChange,
		postsPerPageChange,
		reset
	};

})();

/**
 * filters.ts - Filters block functionality
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
			cache: {},
			renderers: {},
			handlers: {}
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
			postsSelector: '.posts:not(.featured)',
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

				// For PHP-rendered filters with group_by enabled, call groupItems to insert group headings
				if (filterObj.render_mode == 'php' && filterObj.group_by === 'y') {
					groupItems(id);
				}
			});
		}
	};

	const render = (id) => {
		if (!(id in vars.filters.filters)) {
			warn('Filter ID not found', id);
			return;
		}

		renderFeatured(id);
		renderControls(id);
		renderPosts(id);
		renderPagination(id);
		renderPills(id);
		groupItems(id);
		renderBanners(id);

		// Remove loading class from the filter element
		vars.filters.filters[id].elem.removeClass('loading');
		if (typeof vars.filters.filters[id].callback === 'string' && vars.filters.filters[id].callback !==''){
			vars.filters.filters[id].callback
			.split('.')
			.reduce((o, k) => o?.[k], window)
			?.();
		}

		fireHandlers(id, 'after-render');
	};

	const renderControls = (id) => {
		log('Filters renderControls');

		if (!(id in vars.filters.filters)) {
			warn('Filter ID not found', id);
			return;
		}

		const filterObj = vars.filters.filters[id];

		const customRenderer = getRenderer(id, 'controls');
		if (customRenderer) { customRenderer(filterObj.elem, filterObj); return; }

		let controls;

		switch (filterObj.render_mode) {
			case 'js': {
				// After the first API call, server returns pre-rendered controls HTML.
				// On initial page load render.controls is absent — fall back to client-side builder.
				const controlsSource = filterObj.render?.controls;
				if (controlsSource) {
					// Same extraction logic as PHP mode: keep pills element in place
					const $rendered = jQuery(controlsSource);
					const $newControls = $rendered.filter(cfg.filters.controlsSelector);
					controls = filterObj.elem.find(cfg.filters.controlsSelector);
					if (controls.length) controls.replaceWith($newControls.length ? $newControls : $rendered);
					else filterObj.elem.prepend($newControls.length ? $newControls : $rendered);
				} else {
					const controlsHtml = buildControlsHtml(id);
					if (!controlsHtml) break;
					controls = filterObj.elem.find(cfg.filters.controlsSelector);
					if (controls.length) controls.replaceWith(controlsHtml);
					else filterObj.elem.prepend(controlsHtml);
				}
				break;
			}

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

		const customRenderer = getRenderer(id, 'posts');
		if (customRenderer) { customRenderer(filterObj.elem, filterObj); return; }

		let posts;

		switch (filterObj.render_mode) {
			case 'js': {
				const postsHtml = buildJsPostsHtml(id);
				posts = filterObj.elem.find(`${cfg.filters.postsSelector}, .no-results`);
				if (posts.length) posts.replaceWith(postsHtml);
				else filterObj.elem.append(postsHtml);
				break;
			}

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

		const customRenderer = getRenderer(id, 'pagination');
		if (customRenderer) { customRenderer(filterObj.elem, filterObj); return; }

		let pagination;

		// Typesense path: pagination state was updated in processTypesenseResponse,
		// build pagination HTML client-side (no PHP response available for this path).
		if (filterObj.typesense_search === 'y') {
			const p = filterObj.pagination;
			const paginationHtml = buildPaginationHtml(id, p);
			pagination = filterObj.elem.find(cfg.filters.paginationSelector);
			if (pagination.length) pagination.replaceWith(paginationHtml);
			else filterObj.elem.append(paginationHtml);
			return;
		}

		switch (filterObj.render_mode) {
			case 'js': {
				const paginationHtml = buildPaginationHtml(id, filterObj.pagination);
				pagination = filterObj.elem.find(cfg.filters.paginationSelector);
				if (pagination.length) pagination.replaceWith(paginationHtml);
				else filterObj.elem.append(paginationHtml);
				break;
			}

			case 'php':
				// Check if there's an existing pagination element
				pagination = filterObj.elem.find(cfg.filters.paginationSelector);
				if (pagination.length) pagination.replaceWith(filterObj.render.pagination);
				else filterObj.elem.append(filterObj.render.pagination);
				break;
		}
	};

	/**
	 * Build pagination HTML client-side — mirrors the PHP default-pagination.tmpl.php template.
	 * Used by the Typesense path which has no PHP pagination response.
	 */
	const buildPaginationHtml = (id, p): string => {
		if (!p || p.pagination !== 'y' || (p.total_pages || 0) <= 1) {
			return `<div class="pagination" id="${id}-pagination"></div>`;
		}

		const page       = p.page || 1;
		const totalPages = p.total_pages || 1;
		const pageNumbers = p.page_numbers || '3';

		let pageLinks = '';

		if (pageNumbers === '1') {
			if (page > 1) pageLinks += `<li class="page-ellipsis">&ctdot;</li>`;
			pageLinks += `<li class="page-number current" data-page="${page}" aria-label="Page ${page}">${page}</li>`;
			if (page < totalPages) pageLinks += `<li class="page-ellipsis">&ctdot;</li>`;
		} else if (pageNumbers === 'all') {
			for (let i = 1; i <= totalPages; i++) {
				pageLinks += `<li class="page-number${i === page ? ' current' : ''}" data-page="${i}" aria-label="Page ${i}">${i}</li>`;
			}
		} else {
			const n = parseInt(pageNumbers) || 3;
			let low  = page - Math.floor(n / 2);
			let high = page + Math.floor(n / 2);
			if (low < 1)           { low = 1; high = Math.min(totalPages, n); }
			if (high > totalPages) { high = totalPages; low = Math.max(1, high - n + 1); }
			if (low > 1)           pageLinks += `<li class="page-ellipsis">&ctdot;</li>`;
			for (let i = low; i <= high; i++) {
				pageLinks += `<li class="page-number${i === page ? ' current' : ''}" data-page="${i}" aria-label="Page ${i}">${i}</li>`;
			}
			if (high < totalPages) pageLinks += `<li class="page-ellipsis">&ctdot;</li>`;
		}

		const prevPage = page > 1 ? page - 1 : 1;
		const nextPage = page < totalPages ? page + 1 : totalPages;

		return `<div class="pagination" id="${id}-pagination">
			<ul class="pageslinks">
				<li class="page-first${page === 1 ? ' inactive' : ''}" data-page="1" aria-label="First Page">First</li>
				<li class="page-prev${page === 1 ? ' inactive' : ''}" data-page="${prevPage}" aria-label="Previous Page">Prev</li>
				${pageLinks}
				<li class="page-next${page === totalPages ? ' inactive' : ''}" data-page="${nextPage}" aria-label="Next Page">Next</li>
				<li class="page-last${page === totalPages ? ' inactive' : ''}" data-page="${totalPages}" aria-label="Last Page">Last</li>
			</ul>
		</div>`;
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

	const groupItems = (id) => {
		const filterObj = vars.filters.filters[id];
		if (filterObj?.group_by !== 'y') return;

		const tag = filterObj.group_by_heading_tag || 'h3';
		const container = filterObj.elem.find(cfg.filters.postsSelector);
		if (!container.length) return;

		// Remove previously injected headings
		container.find('.lqx-group-heading').remove();

		// Insert headings at group boundaries
		// Item order is controlled by posts_order — not re-sorted here
		let lastKey = '';
		container.find('[data-group-key]').each(function () {
			const key   = jQuery(this).attr('data-group-key') ?? '';
			const label = jQuery(this).attr('data-group-label') ?? key;
			if (key !== lastKey) {
				jQuery(this).before(`<${tag} class="lqx-group-heading">${label}</${tag}>`);
				lastKey = key;
			}
		});
	};

	// Escape a value for safe use inside an HTML attribute
	const escAttr = (s: any): string =>
		String(s ?? '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');

	// Build controls HTML for JS render mode — mirrors default-controls.tmpl.php
	const buildControlsHtml = (id): string => {
		const s = vars.filters.filters[id];
		if (!s) return '';

		const hasContent = s.show_search === 'y' || s.controls?.length;
		const hasPosts = s.posts?.length;
		if (!hasContent || (!hasPosts && s.show_controls_on_no_results !== 'y')) return '';

		let html = `<div class="controls" id="${id}-controls">`;

		if (s.show_open_close === 'y') {
			html += `<div class="open-close-wrapper">` +
				`<button id="${id}-open" class="open">${s.open_label || ''}</button>` +
				`<button id="${id}-close" class="close">${s.close_label || ''}</button>` +
				`</div>`;
		}

		if (s.show_search === 'y') {
			html += `<div class="search-wrapper">` +
				`<label for="${id}-search">${s.search_placeholder || ''}</label>` +
				`<input class="search" id="${id}-search" placeholder="${escAttr(s.search_placeholder)}" value="${escAttr(s.search)}">` +
				`<button class="search-button" id="${id}-search-button"></button>` +
				`</div>`;
		}

		if (s.controls?.length) {
			if (s.layout === 'tabbed') {
				html += `<div class="control-tabs-wrapper"><ul class="control-tabs" id="${id}-control-tabs" role="tablist">`;
				s.controls.forEach((ctrl, j) => {
					if (ctrl.visible !== 'y') return;
					html += `<li role="presentation" class="${j === 0 ? 'active' : ''}">` +
						`<button id="${id}-control-tab-${j}" class="control-tab" role="tab"` +
						` aria-controls="${id}-control-wrapper-${j}" aria-selected="${j === 0 ? 'true' : 'false'}"` +
						` data-control="${escAttr(ctrl.slug)}" data-control-type="${escAttr(ctrl.type)}"` +
						` tabindex="${j === 0 ? '' : '-1'}">${ctrl.label || ''}</button></li>`;
				});
				html += `</ul></div><div class="control-panels-wrapper">`;
			}

			s.controls.forEach((ctrl, j) => {
				if (ctrl.visible !== 'y') return;
				const opts = ctrl.options || [];
				const isSelected = ctrl.selected !== false && ctrl.selected !== '';
				const selectedLabel = opts.find(o => String(o.value) === String(ctrl.selected))?.text || '';
				const activeClass = s.layout === 'tabbed' && j === 0 ? ' active' : '';

				html += `<div class="control-wrapper${isSelected ? ' selected' : ''}${activeClass}"` +
					` id="${id}-control-wrapper-${j}"` +
					` data-control="${escAttr(ctrl.slug)}" data-control-type="${escAttr(ctrl.type)}">`;

				const baseId = `${id}-control-${j}`;

				switch (ctrl.presentation) {
					case 'select': {
						const viewAllOpt = ctrl.show_view_all === 'y'
							? `<option value=""${ctrl.selected === '' ? ' selected' : ''}>${ctrl.view_all_label || 'View All'}</option>`
							: '';
						const optHtml = opts.map(o =>
							`<option value="${escAttr(o.value)}"${String(ctrl.selected) === String(o.value) ? ' selected' : ''}>${o.text}</option>`
						).join('');
						html += `<label for="${baseId}">` +
							`<span class="label">${ctrl.label || ''}</span>` +
							`<span class="selected">${selectedLabel}</span>` +
							`<select name="${escAttr(ctrl.slug)}" id="${baseId}">${viewAllOpt}${optHtml}</select>` +
							`</label>`;
						break;
					}
					case 'checkbox':
					case 'radio': {
						const inputType = ctrl.presentation;
						const viewAllInput = ctrl.show_view_all === 'y'
							? `<label for="${baseId}-all"><input type="${inputType}" id="${baseId}-all" name="${escAttr(ctrl.slug)}" value=""${ctrl.selected === '' ? ' checked' : ''}><span>${ctrl.view_all_label || 'View All'}</span></label>`
							: '';
						const inputsHtml = opts.map((o, i) =>
							`<label for="${baseId}-${i}"><input type="${inputType}" id="${baseId}-${i}" name="${escAttr(ctrl.slug)}" value="${escAttr(o.value)}"${String(ctrl.selected) === String(o.value) ? ' checked' : ''}><span>${o.text}</span></label>`
						).join('');
						html += `<fieldset>` +
							`<legend><span class="label">${ctrl.label || ''}</span><span class="selected">${selectedLabel}</span></legend>` +
							viewAllInput + inputsHtml +
							`</fieldset>`;
						break;
					}
					case 'list': {
						const viewAllLi = ctrl.show_view_all === 'y'
							? `<li id="${baseId}-all" class="option${ctrl.selected === '' ? ' selected' : ''}" data-value="">${ctrl.view_all_label || 'View All'}</li>`
							: '';
						const liHtml = opts.map((o, i) =>
							`<li id="${baseId}-${i}" class="option${String(ctrl.selected) === String(o.value) ? ' selected' : ''}" data-value="${escAttr(o.value)}">${o.text}</li>`
						).join('');
						html += `<label id="${baseId}-label">` +
							`<span class="label">${ctrl.label || ''}</span>` +
							`<span class="selected">${selectedLabel}</span>` +
							`</label>` +
							`<ul class="control-list" id="${baseId}" role="combobox" aria-labelledby="${baseId}-label">` +
							viewAllLi + liHtml + `</ul>`;
						break;
					}
					case 'distance': {
						const distOpts = (ctrl.show_view_all === 'y'
							? `<option value=""${ctrl.selected === '' ? ' selected' : ''}>${ctrl.view_all_label || 'View All'}</option>`
							: '') +
							opts.map(o => `<option value="${escAttr(o.value)}"${String(ctrl.selected) === String(o.value) ? ' selected' : ''}>${o.text}</option>`).join('');
						html += `<label for="${baseId}">` +
							`<span class="label">${ctrl.label || ''}</span>` +
							`<input name="${baseId}-search" type="text" class="search" id="${baseId}-search" placeholder="${escAttr(s.search_placeholder)}" value="${escAttr(ctrl.address || '')}">` +
							`<button class="search-button" id="${baseId}-search-button">Go</button>` +
							`<select name="${escAttr(ctrl.slug)}" id="${baseId}">${distOpts}</select>` +
							`<button class="location-button" id="${baseId}-location-button">Use my current location</button>` +
							`</label>`;
						break;
					}
					case 'region':
						html += `<fieldset>` +
							`<legend><span class="label">Region</span><span class="selected">${selectedLabel}</span></legend>` +
							`<label for="${baseId}-region"><input type="radio" id="${baseId}-region" name="${escAttr(ctrl.slug)}" value="this-region"${ctrl.selected !== '' ? ' checked' : ''}><span>This Region</span></label>` +
							`<label for="${baseId}-all"><input type="radio" id="${baseId}-all" name="${escAttr(ctrl.slug)}" value=""${ctrl.selected === '' ? ' checked' : ''}><span>All Regions</span></label>` +
							`</fieldset>`;
						break;
				}

				html += `</div>`;
			});

			if (s.layout === 'tabbed') html += `</div>`;
		}

		if (hasContent && s.show_clear === 'y') {
			html += `<div class="clear-wrapper"><button id="${id}-clear" class="clear">${s.clear_label || 'Clear'}</button></div>`;
		}

		if (s.change_order === 'y' && s.order_options?.length) {
			html += `<div class="order-wrapper">` +
				s.order_options.map(o =>
					`<div class="option" data-value="${escAttr(o.order_by?.value)}" data-order="${escAttr(o.order)}">${o.order_by?.label || ''}</div>`
				).join('') +
				`</div>`;
		}

		html += `</div>`;

		if (s.use_pills === 'y') html += `<div class="pills" id="${id}-pills"></div>`;

		return html;
	};

	// Build posts HTML for JS render mode.
	// Prefers PHP-pre-rendered card_html per post (template inheritance preserved).
	// Falls back to generic client-side card building when card_html is absent.
	const buildJsPostsHtml = (id): string => {
		const filterObj = vars.filters.filters[id];
		if (!filterObj) return '';

		const posts = filterObj.posts || [];
		const renderJs = filterObj.render_js || {};
		const preset = (filterObj.render_php?.preset || filterObj.preset || '');
		const style  = (filterObj.render_php?.style  || renderJs.style || '');

		if (!posts.length) {
			return filterObj.show_no_results_message === 'y'
				? `<div class="no-results">${filterObj.no_results_message || ''}</div>`
				: '';
		}

		const sectionClass = ['lqx-block-cards', 'posts', style].filter(Boolean).join(' ');
		const wrapCards = (cardsHtml: string) =>
			`<section class="${sectionClass}" data-preset="${escAttr(preset)}">` +
			`<div class="cards" id="${id}-posts" data-slider="n">` +
			`<ul class="cards-wrapper">${cardsHtml}</ul>` +
			`</div></section>`;

		// Prefer PHP-pre-rendered card HTML (set by render_js_card() on the server)
		if (posts.every(p => p.card_html)) {
			return wrapCards(posts.map(p => p.card_html).join(''));
		}

		// Fallback: generic client-side card builder (used when render_php is not configured)
		const linkStyle = renderJs.link_style || 'button';
		const linkTitle = renderJs.link_title || 'Read More';
		const headingTag = renderJs.heading_tag || 'h3';

		const cards = posts.map(post => {
			// Use pre-rendered HTML for this individual post if available
			if (post.card_html) return post.card_html;

			let labelsHtml = '';
			if (renderJs.post_taxonomies?.length) {
				const labels: string[] = [];
				renderJs.post_taxonomies.forEach(tax => {
					const terms = post.taxonomies?.[tax];
					if (terms) Object.values(terms).forEach((term: any) => labels.push(`<span class="label">${term.text}</span>`));
				});
				if (labels.length) labelsHtml = `<div class="labels">${labels.join('')}</div>`;
			}

			let imageHtml = '';
			if (renderJs.post_thumbnail === 'y' && post.thumbnail?.length) {
				const thumb = post.thumbnail[0];
				const srcset = post.thumbnail.slice(1).map(t => `${escAttr(t.url)} ${t.width}w`).join(', ');
				imageHtml = `<div class="image"><img src="${escAttr(thumb.url)}" alt="${escAttr(post.title)}"` +
					(srcset ? ` srcset="${srcset}"` : '') +
					(thumb.width ? ` width="${thumb.width}"` : '') +
					(thumb.height ? ` height="${thumb.height}"` : '') +
					`></div>`;
			}

			const linkHtml = post.link
				? `<div class="link"><a class="${linkStyle}" href="${escAttr(post.link)}">${linkTitle}</a></div>`
				: '';

			return `<li class="card" data-id="${post.id}">` +
				labelsHtml + imageHtml +
				`<div class="text">` +
				(post.title ? `<${headingTag} class="heading"><a href="${escAttr(post.link || '#')}">${post.title}</a></${headingTag}>` : '') +
				(renderJs.post_excerpt === 'y' && post.excerpt ? `<div class="body"><p>${post.excerpt}</p></div>` : '') +
				(renderJs.post_content === 'y' && post.content ? `<div class="body">${post.content}</div>` : '') +
				linkHtml + `</div></li>`;
		}).join('');

		return wrapCards(cards);
	};

	// Register a custom renderer for a specific filter (or '*' for all)
	const registerRenderer = (id: string, type: 'controls'|'posts'|'pagination'|'card', fn: Function) => {
		if (!vars.filters.renderers[id]) vars.filters.renderers[id] = {};
		vars.filters.renderers[id][type] = fn;
	};

	// Register an event handler for a specific filter (or '*' for all)
	const registerHandler = (id: string, event: string, fn: Function) => {
		if (!vars.filters.handlers[id]) vars.filters.handlers[id] = {};
		if (!vars.filters.handlers[id][event]) vars.filters.handlers[id][event] = [];
		vars.filters.handlers[id][event].push(fn);
	};

	// Fire all registered handlers for an event; returns false if any handler returns false
	const fireHandlers = (id: string, event: string, data?: any): boolean => {
		const handlers = [
			...(vars.filters.handlers['*']?.[event] ?? []),
			...(vars.filters.handlers[id]?.[event] ?? [])
		];
		for (const fn of handlers) {
			if (fn(vars.filters.filters[id], data) === false) return false;
		}
		return true;
	};

	// Get a registered renderer for a specific filter and type
	const getRenderer = (id: string, type: string): Function|null => {
		return vars.filters.renderers[id]?.[type] ?? vars.filters.renderers['*']?.[type] ?? null;
	};

	// Render featured posts section (PHP mode, page 1 only)
	const renderFeatured = (id) => {
		const filterObj = vars.filters.filters[id];
		if (!filterObj) return;

		const featuredHtml = filterObj.render?.featured ?? '';
		const postsElem = filterObj.elem.find(`${cfg.filters.postsSelector}, .no-results`);
		const featuredElem = filterObj.elem.find('.posts.featured');

		if (featuredHtml) {
			if (featuredElem.length) featuredElem.replaceWith(featuredHtml);
			else if (postsElem.length) postsElem.before(featuredHtml);
			else filterObj.elem.append(featuredHtml);
		} else if (filterObj.pagination?.page > 1) {
			featuredElem.remove();
		}
	};

	// Render banner from selected option's banner_html
	const renderBanners = (id) => {
		const filterObj = vars.filters.filters[id];
		if (!filterObj) return;

		let bannerHtml = '';
		for (const control of filterObj.controls) {
			if (!control.banner_field || !control.selected || !control.options) continue;
			const opt = control.options.find(o => String(o.value) === String(control.selected));
			if (opt?.banner_html) {
				bannerHtml = opt.banner_html;
				break;
			}
		}

		let bannerElem = filterObj.elem.find('.filter-banner');
		if (!bannerElem.length) {
			const postsElem = filterObj.elem.find(cfg.filters.postsSelector);
			if (postsElem.length) postsElem.before('<div class="filter-banner"></div>');
			else filterObj.elem.append('<div class="filter-banner"></div>');
			bannerElem = filterObj.elem.find('.filter-banner');
		}

		if (bannerHtml) bannerElem.html(bannerHtml).show();
		else bannerElem.empty().hide();
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

		// If single_control_filter is enabled, clear all other controls first
		if (filterObj.single_control_filter === 'y') {
			filterObj.controls.forEach(c => { if (c !== control) c.selected = ''; });
		}

		// Update the control selected value
		control.selected = controlValue;

		fireHandlers(id, 'control-change', { control, value: controlValue });

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

		fireHandlers(id, 'search-change', { query });

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

		fireHandlers(id, 'page-change', { page });

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

		// For region controls: after unchecking all radios, select "All Regions" (value="")
		// so the UI reflects that no region filter is active.
		filterObj.controls.forEach((control) => {
			if (control.type === 'region') {
				findFilterElems(filterObj, `${cfg.filters.controlWrapperSelector}[data-control="${control.slug}"]`)
					.find('input[type="radio"][value=""]')
					.prop('checked', true);
			}
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

		fireHandlers(id, 'reset');

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

		const filterObj = vars.filters.filters[id];

		// Allow handlers to cancel the fetch
		if (!fireHandlers(id, 'before-fetch')) return;

		// Add loading class to the filter element
		filterObj.elem.addClass('loading');

		// When typesense_search is enabled the browser queries Typesense directly,
		// using credentials embedded in typesense_config by PHP at page render time.
		if (filterObj.typesense_search === 'y') {
			callTypesense(id);
			return;
		}

		// Prepare the payload
		let payload = jQuery.extend(true, {}, filterObj);

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

	/**
	 * Build a Typesense filter_by string from the active control selections.
	 */
	const buildTypesenseFilterBy = (filterObj): string => {
		const parts: string[] = [];

		// Always include the locked pre-filter from server
		const locked = filterObj.typesense_config?.locked_filter;
		if (locked) parts.push(locked);

		// Build filters from active controls
		filterObj.controls.forEach((control) => {
			const sel = control.selected;
			if (sel === '' || sel === null || sel === undefined) return;
			// Skip empty arrays
			if (Array.isArray(sel) && sel.length === 0) return;

			switch (control.type) {
				case 'taxonomy': {
					// Taxonomy controls store term IDs; Typesense indexes taxonomy slugs
					const taxField = `${control.taxonomy}_slug`;
					const selectedIds = Array.isArray(sel) ? sel : [sel];
					// Resolve IDs to slugs via control.options
					const slugs = selectedIds.map(id => {
						const opt = control.options?.find(o => String(o.value) === String(id));
						return opt ? (opt.slug || opt.text || String(id)) : String(id);
					}).filter(Boolean);
					if (slugs.length) parts.push(`${taxField}:=[${slugs.join(',')}]`);
					break;
				}
				case 'field': {
					const fieldName = control.field_name || control.slug;
					if (!fieldName) break;
					const selectedVals = Array.isArray(sel) ? sel : [sel];

					// Relationship fields store post IDs in controls but post titles in Typesense.
					// All other field types (button_group, select, etc.) store the raw ACF value in both.
					const isRelationship = control.field_type === 'relationship';

					const filterVals = selectedVals.map(val => {
						if (isRelationship) {
							const opt = control.options?.find(o => String(o.value) === String(val));
							return opt ? (opt.text || String(val)) : String(val);
						}
						// Raw value — Typesense stores exactly what ACF returns (e.g. "m", "f", "y", "n")
						return String(val);
					}).filter(Boolean);
					if (filterVals.length) parts.push(`${fieldName}:=[${filterVals.join(',')}]`);
					break;
				}
				case 'region': {
					// Region control: when "this-region" is selected, filter by the user's
					// detected region alias. Check geolocate module first, then fall back to
					// the selectedRegion cookie which stores the alias as a plain string
					// (e.g. "greater-philadelphia"), NOT as a JSON object.
					if (sel === 'this-region') {
						const regions: string[] = (window as any).lqx?.vars?.geolocate?.regions || [];
						let regionAlias = regions[0] || '';
						if (!regionAlias) {
							regionAlias = document.cookie
								.split('; ')
								.find(c => c.startsWith('selectedRegion=') || c.startsWith('ipDetectedRegion='))
								?.split('=').slice(1).join('=') || '';
						}
						if (regionAlias) parts.push(`related_regions:=[${regionAlias}]`);
					}
					break;
				}
				case 'distance':
					// Distance filtering via Typesense geo_point
					if (control.lat && control.lng && control.miles) {
						const radiusKm = parseFloat(control.miles) * 1.60934;
						parts.push(`locations_geopoints:(${control.lat}, ${control.lng}, ${radiusKm} km)`);
					}
					break;
			}
		});

		return parts.filter(Boolean).join(' && ');
	};

	/**
	 * Build a Typesense sort_by string from the posts_order settings.
	 *
	 * Builds dynamically from the current filterObj.posts_order so that change_order
	 * selections take effect immediately. ACF field keys are resolved via the
	 * typesense_config.acf_field_names map pre-built server-side.  When posts_order
	 * is missing an acf_field (e.g. after a change_order click), the matching entry
	 * in order_options is consulted to retrieve it.
	 */
	const buildTypesenseSortBy = (filterObj): string => {
		const acfFieldNames: Record<string, string> = filterObj.typesense_config?.acf_field_names || {};
		const parts: string[] = [];

		if (!Array.isArray(filterObj.posts_order) || filterObj.posts_order.length === 0) {
			// No dynamic order set — fall back to server pre-resolved sort string
			return filterObj.typesense_config?.sort_by || '';
		}

		for (const order of filterObj.posts_order) {
			const dir = order.order || 'asc';
			switch (order.order_by) {
				case 'rand':
					return '_eval(random()):desc';
				case 'date':
				case 'modified':
					parts.push(`sort_by_date:${dir}`);
					break;
				case 'distance': {
					// Geo sort: resolve lat/lng from the active distance control
					const distCtrl = filterObj.controls?.find(c => c.type === 'distance' && c.lat && c.lng);
					if (distCtrl) parts.push(`locations_geopoints(${distCtrl.lat}, ${distCtrl.lng}):asc`);
					break;
				}
				case 'field':
				case 'meta_key': {
					// Prefer acf_field from the order entry; if missing (e.g. after
					// change_order click), find matching entry in order_options
					let acfKey = order.acf_field || '';
					if (!acfKey && Array.isArray(filterObj.order_options)) {
						const matchOpt = filterObj.order_options.find(o => {
							const obVal = typeof o.order_by === 'object' ? (o.order_by?.value || '') : (o.order_by || '');
							return obVal === order.order_by && o.order === dir;
						});
						acfKey = matchOpt?.acf_field || '';
					}
					const fieldName = acfKey ? (acfFieldNames[acfKey] || '') : (order.value || '');
					if (fieldName) parts.push(`${fieldName}:${dir}`);
					break;
				}
			}
		}

		if (parts.length) return parts.join(',');

		// Nothing resolved — fall back to server pre-resolved sort string
		return filterObj.typesense_config?.sort_by || '';
	};

	/**
	 * Build facet_by string for Typesense to get control option counts.
	 */
	const buildTypesenseFacetBy = (filterObj): string => {
		const facets: string[] = [];

		filterObj.controls.forEach((control) => {
			switch (control.type) {
				case 'taxonomy':
					facets.push(`${control.taxonomy}_slug`);
					break;
				case 'field':
					if (control.field_name) facets.push(control.field_name);
					break;
			}
		});

		return facets.join(',');
	};

	/**
	 * Render a single card from a Typesense hit document.
	 *
	 * Priority order:
	 * 1. typesense_config.card_html_field — use pre-rendered HTML stored in the Typesense document.
	 *    PHP renders the card at index time using the exact PHP templates, stores it in this field.
	 *    JS just injects it — zero server calls, always matches templates exactly.
	 * 2. typesense_config.render_card_callback — delegate to a named JS function.
	 * 3. Generic renderer — builds basic card HTML from render_js settings.
	 */
	const renderCardFromHit = (hit, filterObj): string => {
		const tsConfig = filterObj.typesense_config || {};

		// 0. Check for registered card renderer
		const cardRenderer = getRenderer(filterObj.id, 'card');
		if (cardRenderer) return cardRenderer(hit, filterObj);

		// 1. Use pre-rendered HTML — explicit card_html_field config or standard 'card_html' field
		const htmlField = tsConfig.card_html_field || 'card_html';
		if (hit.document[htmlField]) {
			return hit.document[htmlField];
		}

		// 2. Delegate to custom callback if configured
		const cbPath = tsConfig.render_card_callback;
		if (cbPath) {
			const cb = cbPath.split('.').reduce((o, k) => o?.[k], window as any);
			if (typeof cb === 'function') return cb(hit, filterObj);
		}

		const doc = hit.document;
		const renderJs = filterObj.render_js || {};
		const linkStyle = renderJs.link_style || 'button';
		const linkTitle = tsConfig.link_title || renderJs.link_title || 'Read More';

		const title = doc.post_title || '';
		const excerpt = renderJs.post_excerpt === 'y' ? (doc.post_excerpt || '') : '';
		const link = doc.permalink || '#';
		// Show thumbnail whenever the document has one (post_thumbnail_html is pre-rendered
		// with srcset/sizes by the indexer and is always display-safe)
		const thumbnail = doc.post_thumbnail_html || '';

		// Build labels from taxonomies
		let labelsHtml = '';
		if (renderJs.post_taxonomies && Array.isArray(renderJs.post_taxonomies)) {
			const labels: string[] = [];
			renderJs.post_taxonomies.forEach((tax) => {
				const taxKey = tax === 'category' ? 'category' : (tax === 'post_tag' ? 'tags' : tax);
				if (doc[taxKey] && Array.isArray(doc[taxKey])) {
					doc[taxKey].forEach((name) => labels.push(`<span class="label">${name}</span>`));
				}
			});
			if (labels.length) labelsHtml = `<div class="labels">${labels.join('')}</div>`;
		}

		const linkHtml = link !== '#'
			? `<div class="link"><a class="${linkStyle}" href="${link}">${linkTitle}</a></div>`
			: '';

		return `<li class="card">
			${labelsHtml}
			${thumbnail ? `<div class="image">${thumbnail}</div>` : ''}
			<div class="text">
				${title ? `<h3 class="heading"><a href="${link}">${title}</a></h3>` : ''}
				${excerpt ? `<div class="body"><p>${excerpt}</p></div>` : ''}
				${linkHtml}
			</div>
		</li>`;
	};

	/**
	 * Call Typesense Search API directly from the browser.
	 * All card data is pre-indexed in Typesense (including pre-rendered card_html).
	 * JS renders cards purely client-side — no server roundtrip needed after filtering.
	 */
	const callTypesense = (id) => {
		log('Filters callTypesense');

		const filterObj = vars.filters.filters[id];
		const ts = filterObj.typesense_config;

		if (!ts?.host || !ts?.search_only_key || !ts?.collection) {
			warn('Typesense configuration incomplete — check cm_typesense_search_config_settings and cm_typesense_admin_settings WP options', ts);
			filterObj.elem.removeClass('loading');
			return;
		}

		const filterBy   = buildTypesenseFilterBy(filterObj);
		const sortBy     = buildTypesenseSortBy(filterObj);
		const facetBy    = buildTypesenseFacetBy(filterObj);
		const searchQuery = filterObj.search || '*';
		const perPage    = filterObj.pagination?.posts_per_page || 10;
		const page       = filterObj.pagination?.page || 1;

		const queryByFields = searchQuery !== '*'
			? 'post_title,post_content,post_excerpt'
			: 'post_title';

		const params = new URLSearchParams({
			q:        searchQuery,
			query_by: queryByFields,
			per_page: perPage.toString(),
			page:     page.toString(),
		});

		if (filterBy) params.set('filter_by', filterBy);
		if (sortBy)   params.set('sort_by',   sortBy);
		if (facetBy)  params.set('facet_by',  facetBy);

		const url = `${ts.protocol}://${ts.host}:${ts.port}/collections/${ts.collection}/documents/search?${params.toString()}`;

		const cacheKey = util.hash(url);
		const cached   = getCache(cacheKey);

		if (cached !== null) {
			processTypesenseResponse(id, cached);
			return;
		}

		fetch(url, {
			method:  'GET',
			headers: { 'X-TYPESENSE-API-KEY': ts.search_only_key }
		})
		.then(response => {
			if (!response.ok) throw new Error(`Typesense HTTP ${response.status}`);
			return response.json();
		})
		.then(data => {
			setCache(cacheKey, data);
			processTypesenseResponse(id, data);
		})
		.catch(err => {
			error('Typesense search error', err);
			filterObj.elem.removeClass('loading');
		});
	};

	/**
	 * Process a Typesense search response: render cards client-side and update pagination/pills.
	 * Uses pre-rendered card HTML from Typesense (via typesense_config.card_html_field) if indexed,
	 * otherwise falls back to renderCardFromHit for generic rendering.
	 */
	const processTypesenseResponse = (id, data) => {
		log('Filters processTypesenseResponse');

		const filterObj = vars.filters.filters[id];
		if (!filterObj) return;

		const hits = data.hits || [];
		const totalFound = data.found || 0;
		const perPage = filterObj.pagination?.posts_per_page || 10;
		const totalPages = Math.ceil(totalFound / perPage);

		// Update pagination state
		filterObj.pagination.total_posts = totalFound;
		filterObj.pagination.total_pages = totalPages;

		// Render cards from hits
		let postsHtml = '';
		if (hits.length) {
			const cards = hits.map(hit => renderCardFromHit(hit, filterObj)).join('');

			// Build the same wrapper structure that PHP render_block() produces so the card grid
			// CSS (targeting .lqx-block-cards, .cards-wrapper, .card) continues to work.
			const tsConfig     = filterObj.typesense_config || {};
			const cardsStyle   = tsConfig.cards_style   || '';
			const cardsPreset  = tsConfig.cards_preset  || filterObj.preset || '';
			const sectionClass = ['lqx-block-cards', 'posts', cardsStyle].filter(Boolean).join(' ');

			postsHtml = `<section id="" class="${sectionClass}" data-preset="${cardsPreset}">` +
				`<div class="cards" id="${id}-posts" data-slider="n" data-swiper-options-override="" ` +
				`data-heading-style="h3" data-subheading-style="p" data-heading-clickable="y" ` +
				`data-image-clickable="y" data-responsive-rules="[]">` +
				`<ul class="cards-wrapper">${cards}</ul>` +
				`</div></section>`;
		} else if (filterObj.show_no_results_message === 'y') {
			postsHtml = `<div class="no-results">${filterObj.no_results_message || 'No results found.'}</div>`;
		}

		// Inject posts HTML
		const postsContainer = filterObj.elem.find(`${cfg.filters.postsSelector}, .no-results`);
		if (postsContainer.length) postsContainer.replaceWith(postsHtml);
		else filterObj.elem.append(postsHtml);

		// Update facet counts on control options if facet data is returned
		if (data.facet_counts && Array.isArray(data.facet_counts)) {
			data.facet_counts.forEach(facet => {
				const fieldName = facet.field_name;
				filterObj.controls.forEach(control => {
					const ctrlField = control.type === 'taxonomy'
						? control.taxonomy + '_slug'
						: control.field_name;
					if (ctrlField === fieldName && control.options) {
						control.options.forEach(opt => {
							const facetValue = facet.counts.find(c => c.value === opt.slug || c.value === opt.text);
							opt.count = facetValue ? facetValue.count : 0;
						});
					}
				});
			});
		}

		// Re-render pagination, pills, and group items
		renderPagination(id);
		renderPills(id);
		groupItems(id);
		renderFeatured(id);
		renderBanners(id);

		// Remove loading class
		filterObj.elem.removeClass('loading');

		// Re-add listeners
		addListeners(id);

		// Fire callback
		if (typeof filterObj.callback === 'string' && filterObj.callback !== '') {
			filterObj.callback
				.split('.')
				.reduce((o, k) => o?.[k], window)
				?.();
		}

		fireHandlers(id, 'after-render');
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
		render,
		registerRenderer,
		registerHandler,
		controlChange,
		searchChange,
		pageChange,
		postsPerPageChange,
		reset
	};

})();

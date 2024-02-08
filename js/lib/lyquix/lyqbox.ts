/**
 * lyqbox.js - LyqBox - Lyquix lightbox functionality
 *
 * @version     2.4.1
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
import { analytics } from './analytics';
import { mutation } from './mutation';
import { swipe } from './swipe';
import { util } from './util';

/**
 * LyqBox - Lyquix lightbox functionality
 *
 * Provides 2 modes of operation
 * - Single Slide
 * - Gallery
 *
 * Supports 5 types of content
 * - Image (loaded from URL)
 * - Video (YouTube and Vimeo players)
 * - HTML (passed as a string)
 * - URL (embdedded in an iframe)
 * - DOM (gets outer HTML of existing DOM element, and removes the element)
 *
 * Options passed via the data-lyqbox attribute in JSON format
 * (options marked with ** are required, other options may be required depending on the type)
 *
 * 	** name: Unique identifier for the lightbox, all the slides in a gallery must use the same name
 * 	template: HTML template for the slide (optional, only the value in the first slide of a gallery is used, if not present, uses the default template)
 *  useHash: Use hash in URL to identify the slide (optional, default is true)
 * 	** type: Type of content for the slide [ image | video | html | url | dom ]
 * 	url: URL for the content (required for image, video and url types)
 * 	html: HTML content for the slide (required for html type)
 *  selector: CSS selector for the content (required for dom type)
 *  alt: Alternative text for image slides (required for image type)
 * 	title: Title for the slide (optional)
 * 	caption: Caption for the slide (optional)
 * 	credit: Credit line for the slide (optional)
 * 	class: Additional CSS class to add to the slide (optional)
 * 	slug: Used to identify the slide in the URL (required if useHash is true)
 * 	thumb: URL for the slide thumbnail (optional, only in use for Gallery mode)
 * 	teaser: Teaser text for the slide (optional, only in use for Gallery mode)
 */
export const lyqbox = (() => {

	// Initialize LyqBox
	const init = (customCfg?: object) => {
		// Run only once
		if (vars.lyqbox?.init) return;

		vars.lyqbox = {
			init: false,
			lightboxes: {}
		};

		cfg.lyqbox = {
			enabled: true,
			selector: '[data-lyqbox]',
			analytics: {
				enabled: true,
				nonInteraction: true,
			},
			template: `
				<dialog class="lyqbox" aria-labeledby aria-describedby aria-modal="true" aria-hidden="true">
					<section class="wrapper">
						<div class="content"></div>
						<div class="info">
							<h2 class="title"></h2>
							<div class="caption"></div>
							<div class="credit"></div>
						</div>
					</section>
					<section class="wrapper">
						<div class="content"></div>
						<div class="info">
							<h2 class="title"></h2>
							<div class="caption"></div>
							<div class="credit"></div>
						</div>
					</section>
					<button class="close" autofocus></button>
					<div class="prev"></div>
					<div class="next"></div>
					<div class="counter">
						<span class="current"></span> / <span class="total"></span>
					</div>
					<ul class="navigation">
					</ul>
				</dialog>`
		};

		// Copy default opts and vars
		if (customCfg) cfg.lyqbox = jQuery.extend(true, cfg.lyqbox, customCfg);

		// Initialize only if enabled
		if (cfg.lyqbox.enabled) {
			log('Initializing `lyqbox`');

			// Disable analytics if the analytics module is not enabled
			cfg.lyqbox.analytics.enabled = cfg.analytics.enabled ? cfg.lyqbox.analytics.enabled : false;
			if (cfg.lyqbox.analytics.enabled) log('Setting LyqBox tracking');

			// Initialize on document ready
			vars.document.ready(() => {
				// Setup lightboxes loaded initially on the page
				setup(jQuery(cfg.lyqbox.selector));

				// Add a mutation handler for lightboxes added to the DOM
				mutation.addHandler('addNode', cfg.lyqbox.selector, setup);

				// Show lightbox from URL hash
				showHash();
			});
		}

		// Run only once
		vars.lyqbox.init = true;
	};

	// Set up lightboxes
	const setup = (elems) => {
		if (elems.length) {
			log('Setting up ' + elems.length + ' lightboxes', elems);

			elems.each((idx, slide) => {
				slide = jQuery(slide);

				let mode = 'single';
				let opts = slide.attr('data-lyqbox');
				if (opts) opts = JSON.parse(opts);
				else {
					warn('Invalid JSON in data-lyquix attribute', slide);
					return;
				}

				// Check if the name is present
				if (!('name' in opts) || !opts.name) {
					warn('`name` is a required option in data-lyquix', slide);
					return;
				}

				// Slugify the name
				opts.name = util.slugify(opts.name);

				// Check if the name already exists
				if (!(opts.name in vars.lyqbox.lightboxes)) {
					// Create a new lightbox
					vars.lyqbox.lightboxes[opts.name] = {
						name: opts.name, // Lightbox name
						current: 0, // Current slide index
						elem: null, // Lightbox DOM element
						mode: mode, // single | gallery
						open: false, // Open status
						loading: false, // Loading status
						slides: [], // Slides data
						template: null, // HTML template
						useHash: null // Use hash in URL
					};
				}
				else {
					mode = 'gallery';
					vars.lyqbox.lightboxes[opts.name].mode = mode;
				}

				// Check the type
				if (!('type' in opts) || !opts.type) {
					warn('`type` is a required option in data-lyquix', slide);
					return;
				}
				if (!['image', 'video', 'html', 'url', 'dom'].includes(opts.type)) {
					warn('Invalid `type` in data-lyquix', slide, opts.type);
					return;
				}

				// Check for useHash if it has not been set
				if (vars.lyqbox.lightboxes[opts.name].useHash === null) {
					if ('useHash' in opts) vars.lyqbox.lightboxes[opts.name].useHash = !!opts.useHash;
					else vars.lyqbox.lightboxes[opts.name].useHash = true;
					delete opts.useHash;
				}

				// Get the slide index
				const index = vars.lyqbox.lightboxes[opts.name].slides.length;

				// Check the slug
				if (!('slug' in opts) || !opts.slug) {
					if (vars.lyqbox.lightboxes[opts.name].useHash) {
						warn('`slug` is a required option in data-lyquix when using hash', slide);
						return;
					}
					opts.slug = 'slide-' + index;
					log('No `slug` provided, generating one', slide);
				}

				// Checks by type
				switch (opts.type) {
					case 'image':
					case 'video':
					case 'url':
						if (!('url' in opts) || !opts.url) {
							warn('`url` is a required option in data-lyquix for type ' + opts.type, slide);
							return;
						}
						break;

					case 'html':
						if (!('html' in opts) || !opts.html) {
							warn('`html` is a required option in data-lyquix for type html', slide);
							return;
						}
						break;

					case 'dom':
						if (!('selector' in opts) || !opts.selector) {
							warn('`selector` is a required option in data-lyquix for type dom', slide);
							return;
						}
						if (jQuery(opts.selector).length == 0) {
							warn('DOM element not found for selector ' + opts.selector, slide);
							return;
						}
						// Get the HTML of the DOM element, and remove it
						opts.html = jQuery(opts.selector).get(0).outerHTML;
						jQuery(opts.selector).remove();
						break;

					default:
						break;
				}

				// Set empty values for title, caption, credit, class, thumb, teaser
				['title', 'caption', 'credit', 'class', 'thumb', 'teaser'].forEach((key) => {
					if (!(key in opts)) opts[key] = '';
				});

				// Check for alt
				if (opts.type == 'image' && (!('alt' in opts) || !opts.alt)) {
					if (opts.title) {
						log('No `alt` provided, using slide title', slide);
						opts.alt = opts.title;
					}
					warn('No `alt` provided for image slide', slide);
				}

				// Check for template if it has not been set
				if (vars.lyqbox.lightboxes[opts.name].template === null) {
					if ('template' in opts) {
						if (opts.template) vars.lyqbox.lightboxes[opts.name].template = opts.template;
						else vars.lyqbox.lightboxes[opts.name].template = cfg.lyqbox.template;
						delete opts.template;
					}
					else vars.lyqbox.lightboxes[opts.name].template = cfg.lyqbox.template;
				}

				// Add DOM element to opts
				opts.elem = slide;

				// Add slide to slides array
				vars.lyqbox.lightboxes[opts.name].slides.push(opts);

				// Add listener to slide
				slide.on('click', (e) => {
					e.preventDefault();
					open(opts.name, index);
				});
			});
		}
	};

	// Open the lightbox (id), on the slide (index)
	const open = (name, index = 0) => {
		if (!(name in vars.lyqbox.lightboxes)) {
			warn(`Lightbox ${name} not found`);
			return;
		}

		log(`Open lightbox ${name}`);

		// Lightbox object
		const lightbox = vars.lyqbox.lightboxes[name];

		// Construct the lightbox if it doesn't exist
		if (lightbox.elem === null) {
			// Create the DOM element
			const elem = jQuery(lightbox.template);

			// Set the id
			const id = 'id-' + util.hash(name);
			elem.attr('id', id);

			// Set the name
			elem.attr('data-lyqbox-name', name);

			// Assign active content container to the first .content box
			elem.find('.wrapper').first().addClass('active');

			// Add listener to close button
			elem.find('.close').on('click', () => {
				elem.get(0).close();
			});

			// Add listener to close event
			elem.on('close', () => {
				close(name);
			});

			// Gallery functionality
			if (lightbox.mode == 'gallery') {
				// Prev button click handling
				elem.find('.prev').on('click', () => {
					prev(name);
				});

				// Next button click handling
				elem.find('.next').on('click', () => {
					next(name);
				});

				// Add keyboard listener
				vars.document.on('keyup', (e) => {
					switch (e.key) {
						case 'ArrowLeft':
						case 'Left':
							prev(name);
							break;

						case 'ArrowRight':
						case 'Right':
							next(name);
							break;
					}
				});

				// Add swipe event handler, only on images and videos
				swipe.add(`#${id} .content`, (swp) => {
					if (swp.dir.indexOf('l') != -1) next(name); // Swipe to the left equals right arrow
					if (swp.dir.indexOf('r') != -1) prev(name); // Swipe to the right equals left arrow
				}, null);

				// Add thumbnails
				Object.keys(lightbox.slides).forEach((i) => {
					const slide = lightbox.slides[i];
					const thumbElem = `<li data-lyqbox-index="${i}">` +
						slide.thumb ? `<img src="${slide.thumb}" alt="Thumbnail for slide ${slide.title ? slide.title : i}">` : '' +
						slide.teaser ? `<span>${slide.teaser}</span>` : '' +
					'</li>';
					jQuery(thumbElem).appendTo(elem.find('.navigation')).on('click', () => {
						load(id, i);
					});

				});
			}
			else {
				// Hide unused elements in Single Slide mode
				elem.find('.wrapper:not(.active), .prev, .next, .counter, .navigation').remove();
			}

			// Append to the body
			elem.appendTo(vars.body);

			// Add element to the lightbox object
			lightbox.elem = elem;
		}

		// Open the lightbox
		lightbox.elem.get(0).showModal();
		lightbox.elem.attr('aria-hidden', 'false');

		// Load the slide
		load(name, index);
	};

	// Load the slide (index) for the lightbox (name)
	const load = (name, index) => {
		// Get the lightbox object
		if (!(name in vars.lyqbox.lightboxes)) {
			warn(`Lightbox ${name} not found`);
			return;
		}
		const lightbox = vars.lyqbox.lightboxes[name];

		// Get the slide
		if (index < 0 || index >= lightbox.slides.length) {
			warn(`Invalid slide index ${index} for lightbox ${name}`);
			return;
		}
		const slide = lightbox.slides[index];

		log(`Open slide ${index} on lightbox ${name}`);

		// Load the content
		let content;
		switch (slide.type) {
			case 'image':
				content = `<img src="${slide.url}" alt="${slide.alt}" />`;
				break;

			case 'video': {
				const video = util.getVideoUrls(slide.url);
				content = `<iframe src="${video.url || slide.url}" class="video"></iframe>`;
				break;
			}

			case 'url':
				content = `<iframe src="${slide.url}"></iframe>`;
				break;

			case 'html':
			case 'dom':
				content = slide.html;
				break;

			default:
				break;
		}
		content = jQuery(content);

		// Show loader while content is loading
		if (['image', 'video', 'url'].includes(slide.type)) {
			// Disable navigation while loading
			lightbox.loading = true;

			// Show loader
			lightbox.elem.addClass('loading');

			// Add load listener
			content.on('load', () => {
				// Enable  navigation
				lightbox.loading = false;

				// Hide loader
				lightbox.elem.removeClass('loading');
			});
		}

		// Content wrapper element
		let wrapper;

		if (lightbox.mode == 'gallery') {
			// Get inactive wrapper
			wrapper = lightbox.elem.find('.wrapper').not('.active');

			// Make the previous content inactive and remove video
			// TODO why not just empty the content?
			lightbox.elem.find('.wrapper.active').removeClass('active').find('.content iframe.video').remove();
		}
		else wrapper = lightbox.elem.find('.wrapper');

		// Append new content to wrapper
		wrapper.addClass('active').find('.content').empty().append(content);

		// Update the current slide index
		lightbox.current = index;

		// Update title, caption, credit
		wrapper.find('.info .title').html(slide.title).attr('id', `lightbox-${lightbox.name}-slide-${index}-title`);
		lightbox.elem.attr('aria-labeledby', `lightbox-${lightbox.name}-slide-${index}-title`);
		wrapper.find('.info .caption').html(slide.caption).attr('id', `lightbox-${lightbox.name}-slide-${index}-description`);
		lightbox.elem.attr('aria-describedby', `lightbox-${lightbox.name}-slide-${index}-description`);
		wrapper.find('.info .credit').html(slide.credit);

		// For galleries update counter and thumbnails
		if (lightbox.mode == 'gallery') {
			lightbox.elem.find('.counter .current').text(index + 1);
			lightbox.elem.find('.counter .total').text(lightbox.slides.length);
			lightbox.elem.find('.navigation').children().removeClass('active').eq(index).addClass('active');
		}

		// Set the hash
		if (lightbox.useHash) {
			let hash = '#' + encodeURIComponent(name);
			if (lightbox.mode == 'gallery') hash += '/' + index + '-' + encodeURIComponent(slide.slug);
			window.history.replaceState(null, '', hash);
		}

		// Send event for lightbox opened
		if (cfg.lyqbox.analytics.enabled && cfg.lyqbox.analytics.onOpen) {
			analytics.sendGAEvent({
				'eventCategory': 'Lightbox',
				'eventAction': 'Load',
				'eventLabel': `${name}|${index}|${slide.slug}`,
				'nonInteraction': cfg.lyqbox.analytics.nonInteraction
			});
		}
	};

	// Load the next slide
	const next = (name) => {
		// Get the lightbox object
		if (!(name in vars.lyqbox.lightboxes)) {
			warn(`Lightbox ${name} not found`);
			return;
		}
		const lightbox = vars.lyqbox.lightboxes[name];

		// No navigation on single slides, on closed lightboxes, or while loading
		if (lightbox.mode != 'gallery' || !lightbox.open || lightbox.loading) return;

		log(`Next slide on lightbox ${name}`);

		// Load the next slide or the first slide
		if (lightbox.current == lightbox.slides.length - 1) load(name, 0);
		else load(name, lightbox.current + 1);
	};

	// Load the previous slide
	const prev = (name) => {
		// Get the lightbox object
		if (!(name in vars.lyqbox.lightboxes)) {
			warn(`Lightbox ${name} not found`);
			return;
		}
		const lightbox = vars.lyqbox.lightboxes[name];

		// No navigation on single slides, on closed lightboxes, or while loading
		if (lightbox.mode != 'gallery' || !lightbox.open || lightbox.loading) return;

		log(`Previous slide on lightbox ${name}`);

		// Load the next slide or the first slide
		if (lightbox.current == 0) load(name, lightbox.slides.length - 1);
		else load(name, lightbox.current - 1);
	};

	// Close the lightbox
	const close = (name) => {
		// Get the lightbox object
		if (!(name in vars.lyqbox.lightboxes)) {
			warn(`Lightbox ${name} not found`);
			return;
		}
		const lightbox = vars.lyqbox.lightboxes[name];

		// Close lightbox
		lightbox.open = false;
		lightbox.elem.attr('aria-hidden', 'true');

		// Remove content
		lightbox.elem.find('.content').empty();

		// Remove active class from thumbnails
		lightbox.elem.find('.navigation').children().removeClass('active');

		// Remove hash
		if (lightbox.useHash) window.history.replaceState(null, '', '#');

		log(`Close lightbox ${name}`);
	};

	// Show the lightbox from the URL hash
	const showHash = () => {
		// Get the hash
		const hash = window.location.hash.substr(1);

		if (hash) {
			// Get the name and slug from the hash
			const hashParts = hash.split('/');

			if (hashParts.length == 1) {
				// Open single slide
				open(decodeURIComponent(hash));
			}
			else if (hashParts.length == 2) {
				const slideParts = hashParts[1].split('-');
				// Open gallery slide
				open(decodeURIComponent(hashParts[0]), parseInt(slideParts[0]));
			}
		}
	};

	return {
		init,
		open,
		prev,
		next,
		close
	};
})();

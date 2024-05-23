/**
 * analytics.ts - Analytics functionality
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

import { vars, cfg, log, error } from './core';
import { mutation } from './mutation';
import { util } from './util';

declare const gtag, YT, jQuery;

/**
 * This module provides analytics functionality
 * It exports an object with methods to initialize, send pageviews and events, and track various user interactions.
 *
 * @module analytics
 *
 * @param {object} customCfg - Optional custom configuration for the analytics module.
 *
 * The module first checks if it has been initialized before. If not, it sets up the default configuration,
 * which can be overridden by the customCfg parameter. It then initializes the analytics if they are enabled.
 *
 * The setup function sets up the analytics by tracking downloads, outbound links, errors, scroll depth, video, user active time,
 * rage clicks, and page performance. It also sends pageviews and events to Google Analytics and Microsoft Clarity.
 */
export const analytics = (() => {

	const init = (customCfg?: object) => {
		// Run only once
		if (vars.analytics?.init) return;

		// Working variables
		vars.analytics = {
			abTestGroup: null,
			scrollDepthMax: null,
			status: null, // wait, init, ready, n/a
			queue: [],
			youTubeIframeAPIReady: false,
			youtubePlayers: {},
			vimeoPlayers: {},
			userActive: null,
			errorHashes: [],
			clickEvents: []
		};

		// Configuration
		cfg.analytics = {
			enabled: true,
			downloads: {
				enabled: true,
				extensions: [
					// Images
					'gif', 'png', 'jpg', 'jpeg', 'tif', 'tiff', 'svg', 'webp', 'bmp',
					// Compressed
					'zip', 'rar', 'gzip', 'gz', '7z', 'tar',
					// Executables, installation, binaries
					'exe', 'msi', 'dmg', 'dll',
					// Documents
					'txt', 'log', 'pdf', 'rtf', 'doc', 'docx', 'dot', 'dotx', 'xls', 'xlsx', 'xlt', 'xltx', 'ppt', 'pptx', 'pot', 'potx',
					// Audio
					'aac', 'aiff', 'mp3', 'mp4', 'm4a', 'm4p', 'wav', 'wma',
					// Video
					'3gp', '3g2', 'mkv', 'vob', 'ogv', 'ogg', 'webm', 'wma', 'm2v', 'm4v', 'mpg', 'mp2', 'mpeg', 'mpe', 'mpv', 'mov', 'avi', 'wmv', 'flv', 'f4v', 'swf', 'qt',
					// Web code
					'xml', 'js', 'json', 'jsonp', 'css', 'less', 'sass', 'scss'
				],
				hitType: 'pageview', // pageview or event
				nonInteraction: false // for events only
			},
			errors: {
				enabled: false,
				maxErrors: 100
			},
			outbound: {
				enabled: true,
				exclude: [], // Array of domains to be excluded, not considered external sites
				nonInteraction: true
			},
			scrollDepth: {
				enabled: false
			},
			video: {
				enabled: true,
				nonInteraction: false
			},
			userActive: {
				enabled: false,
				idleTime: 5_000,	// idle time (ms) before user is set to inactive
				throttle: 250,	// throttle period (ms)
				refresh: 250,	// refresh period (ms)
				maxTime: 1_800_000 // max time when tracking stops (ms)
			},
			rageClicks: {
				enabled: true,
				minClicks: 3, // Look for 3 consecutive clicks or more...
				maxTime: 5, // ... within 5 seconds...
				maxDistance: 100 // within a 100x100 pixel area
			},
			performance: {
				enabled: false
			},
			// Google Analytics
			usingGTM: false,		// set to true if Google Analytics is loaded via GTM
			sendPageview: true,		// set to false if you don't want to send the Pageview (e.g. when sent via GTM)
			measurementId: null, 		// Google Analytics 4 measurement ID
			// A-B testing
			abTest: {
				name: null,			// Set a test name to activate A/B Testing Dimension
				dimension: null,		// Set the Google Analytics dimension number that will save the test name and assigned group
				split: 0.5, 		// Sets the percentage of users that will be assigned to group A, default if 50%
				removeNoMatch: true,		// Set to false to hide no-match elements instead of removing them from DOM
				cookieDays: 30,		// How long should the group assignment be saved in cookie, default 30 days
				displaySelector: '[data-abtest], [class*="abtest-"]'	// Change as needed
			},
			// Microsoft Clarity
			projectId: null		// Microsoft Clarity project ID
		};

		if (customCfg) cfg.analytics = jQuery.extend(true, cfg.analytics, customCfg);

		// Initialize only if enabled
		if (cfg.analytics.enabled) {
			log('Initializing analytics');

			// Check if there's any analytics account available
			if ((cfg.analytics.measurementId && !cfg.analytics.usingGTM) || cfg.analytics.usingGTM) {
				// Init A-B testing before loading GA code
				if (cfg.analytics.abTest.name && cfg.analytics.abTest.dimension) initABTesting();

				// Load Google Analytics 4
				if (!cfg.analytics.usingGTM && cfg.analytics.measurementId) {
					gtagCode(cfg.analytics.measurementId);
				}

				// Wait for GA to be ready before starting tracking functions
				vars.analytics.status = 'wait';
				log('Waiting for Google Analytics to be ready');
				checkGA();

				// Set YouTube API callback function
				window['onYouTubeIframeAPIReady'] = () => {
					onYouTubeIframeAPIReady();
				};
			}
			else {
				log('Analytics not available: no measurementId or GTM set');
				// Set status
				vars.analytics.status = 'n/a';
			}

			// Check if Microsoft Clarity is available
			if (cfg.analytics.projectId) {
				clarityCode();
			}
		}

		// Run only once
		vars.analytics.init = true;
	};

	// Add new gtag.js code
	const gtagCode = (tagId) => {
		log('Loading Google Analytics code (gtag.js)');

		// Create the script element
		let ga4Script = document.createElement('script');
		ga4Script.async = true;
		ga4Script.src = 'https://www.googletagmanager.com/gtag/js?id=' + tagId;

		// Append the first script element to the head
		document.head.appendChild(ga4Script);

		// Create the second script element
		ga4Script = document.createElement('script');
		ga4Script.innerHTML = `
			window.dataLayer = window.dataLayer || [];
			function gtag(){dataLayer.push(arguments);}
			gtag('js', new Date());` +
			(vars.analytics.abTestGroup ? `gtag('set', 'dimension' + '${cfg.analytics.abTest.dimension}', '${vars.analytics.abTestGroup}');` : '') +
			`gtag('config', '${tagId}', ${cfg.analytics.sendPageview ? '{}' : '{\'send_page_view\': false}'});`;

		// Append the second script element to the head
		document.head.appendChild(ga4Script);
	};

	const clarityCode = () => {
		log('Loading Microsoft Clarity code');

		// Create the script element
		const clarityScript = document.createElement('script');
		clarityScript.async = true;
		clarityScript.src = `https://www.clarity.ms/tag/${cfg.analytics.projectId}`;

		// Append the script element to the head
		document.head.appendChild(clarityScript);
	};

	const checkGA = (count?) => {
		if (!count) count = 0;

		// Check for gtag ready
		if ('gtag' in window && typeof window['gtag'] == 'function') {
			// Set status
			vars.analytics.status = 'init';
			initTracking();
		}
		else if (count < 600) setTimeout(() => {
			checkGA(count++);
		}, 100);
		else {
			vars.analytics.status = 'n/a';
			error('Google Analytics not available');
			if (vars.analytics.queue.length) error('Unable to send queued analytics', vars.analytics.queue);
		}
	};

	// Sends google analytics pageview to ga and gtag as available
	/**
	 *
	 * pageInfo = {
	 *  url
	 *  title
	 *  callback
	 * }
	 */
	const sendGAPageview = (pageInfo) => {
		if (vars.analytics.status == 'ready') {
			log('Sending page view to GA', pageInfo);

			// TODO Data validation

			if (pageInfo.url && pageInfo.title) {
				const url = new URL(pageInfo.url, window.location.href);

				const pageParams = {
					page_location: url.href,
					page_path: url.pathname + url.search,
					page_title: pageInfo.title
				};

				if ('callback' in pageInfo) pageParams['event_callback'] = pageInfo.callback;

				gtag('event', 'page_view', pageParams);
			}
			else gtag('event', 'page_view');
		}
		else if (vars.analytics.status == 'wait' || vars.analytics.status == 'init') {
			log('Queueing GA pageview to send later', pageInfo);
			vars.analytics.queue.push({ type: 'pageview', info: pageInfo });
		}
		else if (vars.analytics.status == 'n/a') log('Analytics not available, unable to send GA pageview', pageInfo);

	};

	// Sends google analytics events to ga and gtag as available
	/**
	 * eventInfo = {
	 * 	eventName - if none passed, eventAction will be used as eventName
	 * 	eventAction
	 * 	eventCategory
	 * 	eventLabel
	 * 	nonInteraction - boolean
	 * 	hitCallback - function
	 * }
	 */
	const sendGAEvent = (eventInfo) => {
		if (vars.analytics.status == 'ready') {
			log('Sending event to GA', eventInfo);

			const ga4PropMap = {
				eventCategory: 'event_category',
				eventLabel: 'event_label',
				eventAction: 'event_action',
				eventValue: 'value',
				nonInteraction: 'non_interaction',
				hitCallback: 'event_callback'
			};

			const eventParams = {};

			Object.keys(ga4PropMap).forEach((prop) => {
				if (prop in eventInfo && eventInfo[prop]) eventParams[ga4PropMap[prop]] = eventInfo[prop];
			});

			// TODO Data validation

			const eventName = ('eventName' in eventInfo && eventInfo.eventName) ? eventInfo.eventName : eventInfo.eventAction;

			// Add timeout
			eventParams['event_timeout'] = 2000;

			// send event
			gtag('event', eventName, eventParams);
		}
		else if (vars.analytics.status == 'wait' || vars.analytics.status == 'init') {
			log('Queueing GA event to send later', eventInfo);
			vars.analytics.queue.push({ type: 'event', info: eventInfo });
		}
		else if (vars.analytics.status == 'n/a') log('Analytics not available, unable to send GA event', eventInfo);
	};

	// Initialize tracking
	const initTracking = () => {
		// Track downloads and outbound links
		if (cfg.analytics.outbound.enabled || cfg.analytics.downloads.enabled) {
			log('Setting up outbound/download links tracking');

			const setup = (elems) => {
				if (elems instanceof Node) {
					// Not an array, convert to an array
					elems = [elems];
				}
				else if (elems instanceof jQuery) {
					// Convert jQuery to array
					elems = elems.toArray();
				}
				if (elems.length) {
					log('Setting up ' + elems.length + ' download links', elems);
					elems.forEach((elem) => {
						// check if it has an href attribute, otherwise it is just a page anchor
						if (elem.href) {
							elem = jQuery(elem);

							// get absolute url
							const url = new URL(elem.attr('href'), window.location.href);

							// determine if the link target is opening in a new window
							let newWindow = (elem.attr('target') && !elem.attr('target').match(/^_(self|parent|top)$/i));

							// check if it is an outbound link, track as event
							if (cfg.analytics.outbound.enabled && url.host != window.location.host && cfg.analytics.outbound.exclude.indexOf(url.host) == -1) {
								log('Found outbound link to ' + url.href);
								elem.on('click', (e) => {
									log('Outbound link to: ' + url.href);

									// links opens in a new window when user holds the ctrl key
									newWindow = newWindow || e.ctrlKey || e.shiftKey || e.metaKey;

									// Fallback function - open the link in case the event callback isn't triggered
									let timer;
									if (!newWindow) timer = setTimeout(() => {
										window.location.href = url.href;
									}, 2000);

									// set label
									const label = elem.attr('title') ? elem.attr('title') + ' [' + url.href + ']' : url.href;

									// send event
									sendGAEvent({
										eventCategory: 'Outbound Links',
										eventAction: 'click',
										eventLabel: label,
										nonInteraction: cfg.analytics.outbound.nonInteraction,
										hitCallback: newWindow ? null : () => {
											clearTimeout(timer); // cancel the fallback function
											window.location.href = url.href; // when opening in same window, wait for ga event to be sent
										}
									});

									// when opening in new window, allow the link to proceed, otherwise wait for ga event
									return newWindow;
								});
							}

							// check if it is a download link (not a webpage) and track as pageview
							if (cfg.analytics.downloads.enabled && url.href.match(new RegExp('\\.(' + cfg.analytics.downloads.extensions.join('|') + ')$', 'i')) !== null) {
								log('Found download link to ' + url.href);
								elem.on('click', (e) => {
									log('Download link to: ' + url.href);

									// links opens in a new window when user holds the ctrl key
									newWindow = newWindow || e.ctrlKey || e.shiftKey || e.metaKey;

									// Fallback function - open the link in case the event callback isn't triggered
									let timer;
									if (!newWindow) timer = setTimeout(() => {
										window.location.href = url.href;
									}, 2000);

									// set labels
									const loc = url.protocol + '//' + url.hostname + url.pathname + url.search;
									const page = url.pathname + url.search;
									const title = elem.attr('title') ? elem.attr('title') : 'Download: ' + page;
									const label = elem.attr('title') ? elem.attr('title') + ' [' + page + ']' : page;

									// send pageview
									if (cfg.analytics.downloads.hitType == 'pageview') {
										sendGAPageview({
											url: loc,
											title: title,
											callback: newWindow ? null : () => {
												clearTimeout(timer); // cancel the fallback function
												window.location.href = url.href; // when opening in same window, wait for ga event to be sent
											}
										});
									}

									// or send event
									else if (cfg.analytics.downloads.hitType == 'event') {
										sendGAEvent({
											eventCategory: 'Download Links',
											eventAction: 'click',
											eventLabel: label,
											nonInteraction: cfg.analytics.downloads.nonInteraction,
											hitCallback: newWindow ? null : () => {
												clearTimeout(timer); // cancel the fallback function
												window.location.href = url.href; // when opening in same window, wait for ga event to be sent
											}
										});
									}

									// when opening in new window, allow the link to proceed, otherwise wait for ga event
									return newWindow;
								});
							}
						}
					});
				}
			};

			// Find all a tags and cycle through them
			setup(jQuery('a[href]'));

			// Add a mutation handler for links added to the DOM
			mutation.addHandler('addNode', 'a[href]', setup);
		}

		// Track errors
		if (cfg.analytics.errors.enabled) {
			log('Setting JavaScript errors tracking');

			const sendJSError = (errStr) => {
				const errHash = util.hash(errStr);
				if (vars.analytics.errorHashes.indexOf(errHash) == -1 && vars.analytics.errorHashes.length < cfg.analytics.errors.maxErrors) {
					vars.analytics.errorHashes.push(errHash);
					sendGAEvent({
						eventCategory: 'JavaScript Errors',
						eventAction: 'error',
						eventLabel: errStr,
						nonInteraction: true
					});
				}
			};

			// Add listener to window element for javascript loading errors
			window.addEventListener('error', (e) => {
				const errStr = `${e.message} ${e.error ? `[${e.error.toString()}] ` : ' '}${e.filename}:${e.lineno}:${e.colno}`;
				sendJSError(errStr);
			});

			// Add handler function for runtime errors
			util.addErrorHandler((message, source, lineno, colno, error) => {
				const errStr = `${message} ${error ? `[${error.toString()}] ` : ' '}${source}:${lineno}:${colno}`;
				sendJSError(errStr);
			});
		}

		// Track scroll depth
		if (cfg.analytics.scrollDepth.enabled) {
			log('Setting up scroll depth tracking');

			const calcScrollDepth = () => {
				if (vars.analytics.scrollDepthMax < 100) {
					const scrollDepthRatio = (vars.window.scrollTop() + vars.window.height()) / vars.document.height();
					vars.analytics.scrollDepthMax = Math.floor(scrollDepthRatio * 10) * 10;
				}
			};

			// add listener to scroll event
			window.addEventListener('scroll', calcScrollDepth, { passive: true });

			// add listener to page unload
			vars.window.on('beforeunload', () => {
				calcScrollDepth();
				sendGAEvent({
					eventCategory: 'Scroll Depth',
					eventAction: 'Scroll Depth (%)',
					eventValue: vars.analytics.scrollDepthMax,
					nonInteraction: true
				});
			});
		}

		// Track video
		if (cfg.analytics.video.enabled) {
			log('Setting video tracking');

			// Set listeners for Vimeo videos
			window.addEventListener('message', vimeoReceiveMessage, false);

			// Initialize YouTube or Vimeo videos
			jQuery('iframe[src*="youtube.com/embed/"], iframe[src*="player.vimeo.com/video/"]').each((idx, elem) => {
				initVideoPlayerAPI(jQuery(elem));
			});

			// Add a mututation observer to handle new videos added to the DOM
			mutation.addHandler('addNode', 'iframe[src*="youtube.com/embed/"], iframe[src*="player.vimeo.com/video/"]', (e) => {
				initVideoPlayerAPI(jQuery(e));
			});
		}

		// Track active time
		if (cfg.analytics.userActive.enabled) {
			log('Setting active time tracking');
			initUserActive();

			// Add listener on page unload
			vars.window.on('beforeunload', () => {
				const userActiveRatio = vars.analytics.userActive.activeTime / (vars.analytics.userActive.activeTime + vars.analytics.userActive.inactiveTime);

				sendGAEvent({
					eventCategory: 'User Active Time',
					eventAction: 'Active Time (%)',
					eventValue: Math.floor(userActiveRatio * 10) * 10,
					nonInteraction: true
				});

				sendGAEvent({
					eventCategory: 'User Active Time',
					eventAction: 'Active Time (ms)',
					eventValue: parseInt(vars.analytics.userActive.activeTime),
					nonInteraction: true
				});

				sendGAEvent({
					eventCategory: 'User Active Time',
					eventAction: 'Inactive Time (ms)',
					eventValue: parseInt(vars.analytics.userActive.inactiveTime),
					nonInteraction: true
				});
			});
		}

		// Track rage clicks
		if (cfg.analytics.rageClicks.enabled) {
			log('Setting rage clicks tracking');
			jQuery('body').on('click', (e) => {
				// Save click event
				vars.analytics.clickEvents.push({
					event: e,
					time: (new Date()).getTime() / 1000
				});

				// Are there at least minClicks in the array?
				if (vars.analytics.clickEvents.length >= cfg.analytics.rageClicks.minClicks) {
					// Get index of last event
					const totalClicks = vars.analytics.clickEvents.length;
					const lastClick = totalClicks - 1;

					// Check if clicks within maxTime
					const timeDiff = vars.analytics.clickEvents[lastClick].time - vars.analytics.clickEvents[0].time;
					if (timeDiff <= cfg.analytics.rageClicks.maxTime) {
						// Find the max and min x and y coordinates of all clicks
						let minX = vars.analytics.clickEvents[0].event.clientX;
						let maxX = vars.analytics.clickEvents[0].event.clientX;
						let minY = vars.analytics.clickEvents[0].event.clientY;
						let maxY = vars.analytics.clickEvents[0].event.clientY;
						for (let i = 1; i <= lastClick; i++) {
							const x = vars.analytics.clickEvents[i].event.clientX;
							const y = vars.analytics.clickEvents[i].event.clientY;
							if (x < minX) minX = x;
							if (x > maxX) maxX = x;
							if (y < minY) minY = y;
							if (y > maxY) maxY = y;
						}

						// Check if clicks are within the maxDistance
						if ((maxX - minX <= cfg.analytics.rageClicks.maxDistance) && (maxY - minY <= cfg.analytics.rageClicks.maxDistance)) {
							// Round area of first click to closest 50 pixels to avoid to many differing values in the event
							minX = Math.floor(minX / 50) * 50;
							maxX = Math.ceil(maxX / 50) * 50;
							minY = Math.floor(minY / 50) * 50;
							maxY = Math.ceil(maxY / 50) * 50;
							sendGAEvent({
								eventCategory: 'Rage Click',
								eventAction: 'click',
								eventLabel: [minX, minY, maxX, maxY].join(','),
								nonInteraction: true
							});
						}
					}
					// Remove used Clicks
					vars.analytics.clickEvents.splice(0, totalClicks);
				}
			});
		}

		// Track page performance
		// Performance API https://developer.mozilla.org/en-US/docs/Web/API/Performance_API
		if (cfg.analytics.performance.enabled) {
			log('Setting performance tracking');

			vars.window.on('beforeunload', () => {
				const perfArr = window.performance.getEntriesByType('navigation');
				if (perfArr.length > 0) {
					const perf = perfArr[0] as PerformanceNavigationTiming;
					sendGAEvent({
						eventCategory: 'Performance',
						eventAction: 'Ready Time (ms)',
						eventValue: Math.floor(perf.domInteractive),
						nonInteraction: true,
					});
					sendGAEvent({
						eventCategory: 'Performance',
						eventAction: 'Load Time (ms)',
						eventValue: Math.floor(perf.duration),
						nonInteraction: true,
					});
				}
			});
		}

		// Set status and send queued pageviews and events
		vars.analytics.status = 'ready';
		vars.analytics.queue.forEach((item) => {
			if (item.type == 'pageview') sendGAPageview(item.info);
			else if (item.type == 'event') sendGAEvent(item.info);
		});
	};

	// initialize the js api for youtube and vimeo players
	const initVideoPlayerAPI = (elem) => {
		const src = elem.attr('src');

		if (typeof src != 'undefined') {
			let playerId = elem.attr('id');
			// Check youtube players
			if (src.indexOf('youtube.com/embed/') != -1) {
				if (!vars.analytics.youTubeIframeAPIReady) {
					// Load YouTube iframe API
					// Create the script element
					const ytScript = document.createElement('script');
					ytScript.async = true;
					ytScript.src = 'https://www.youtube.com/iframe_api';
					ytScript.onload = () => {
						vars.analytics.youTubeIframeAPIReady = true;
					};

					// Append the first script element to the head
					document.head.appendChild(ytScript);
				}

				// Add id if it doesn't have one
				if (!playerId) {
					playerId = 'youtubePlayer' + (Object.keys(vars.analytics.youtubePlayers).length);
					elem.attr('id', playerId);
				}

				// Reload with API support enabled
				if (src.indexOf('enablejsapi=1') == -1) {
					elem.attr('src', src + (src.indexOf('?') == -1 ? '?' : '&') + 'enablejsapi=1&version=3');
				}

				// Add to list of players
				if (!(playerId in vars.analytics.youtubePlayers)) {
					vars.analytics.youtubePlayers[playerId] = {};

					// add event callbacks to player
					onYouTubeIframeAPIReady();
				}
			}

			// Check vimeo players
			if (src.indexOf('player.vimeo.com/video/') != -1) {
				// Add id if it doesn't have one
				if (!playerId) {
					playerId = 'vimeoPlayer' + (Object.keys(vars.analytics.vimeoPlayers).length);
					elem.attr('id', playerId);
				}

				// Reload with API support enabled
				if (src.indexOf('api=1') == -1) {
					elem.attr('src', src + (src.indexOf('?') == -1 ? '?' : '&') + 'api=1&player_id=' + playerId);
				}

				// Add to list of players
				if (!(playerId in vars.analytics.vimeoPlayers)) {
					vars.analytics.vimeoPlayers[playerId] = {};
				}
			}
		}
	};

	const onYouTubeIframeAPIReady = (count?) => {
		if (!count) count = 0;

		if (vars.analytics.youTubeIframeAPIReady && (typeof YT !== 'undefined') && YT && ('Player' in YT)) {
			Object.keys(vars.analytics.youtubePlayers).forEach((playerId) => {
				if (!('playerObj' in vars.analytics.youtubePlayers[playerId])) {
					vars.analytics.youtubePlayers[playerId].playerObj = new YT.Player(playerId, {
						events: {
							onReady: (e) => {
								youtubePlayerReady(e, playerId);
							},
							onStateChange: (e) => {
								youtubePlayerStateChange(e, playerId);
							}
						}
					});
				}
			});
		}
		else {
			// keep track how many time we have attempted, retry unless it has been more than 30secs
			count++;
			if (count < 600) window.setTimeout(() => {
				onYouTubeIframeAPIReady(count);
			}, 100);
			else error('YouTube API not available');
		}
	};

	const youtubePlayerReady = (e, playerId) => {
		// check if iframe still exists
		if (jQuery('#' + playerId).length) {
			if (typeof vars.analytics.youtubePlayers[playerId].playerObj.getPlayerState != 'function') {
				//setTimeout(() =>{lqx.youtubePlayerReady(e, playerId)}, 100);
			}
			else {
				if (!('progress' in vars.analytics.youtubePlayers[playerId])) {
					// set player object variables
					vars.analytics.youtubePlayers[playerId].progress = 0;
					vars.analytics.youtubePlayers[playerId].start = false;
					vars.analytics.youtubePlayers[playerId].complete = false;

					// get video data
					const videoData = vars.analytics.youtubePlayers[playerId].playerObj.getVideoData();
					vars.analytics.youtubePlayers[playerId].title = videoData.title;
					vars.analytics.youtubePlayers[playerId].duration = vars.analytics.youtubePlayers[playerId].playerObj.getDuration();

					if (!vars.analytics.youtubePlayers[playerId].start) youtubePlayerStateChange(e, playerId);
				}
			}
		}
		else {
			// iframe no longer exists, remove it from array
			delete vars.analytics.youtubePlayers[playerId];
		}
	};

	const youtubePlayerStateChange = (e, playerId) => {
		// check if iframe still exists
		if (jQuery('#' + playerId).length) {
			// player events:
			// -1 (unstarted, player ready)
			// 0 (ended)
			// 1 (playing)
			// 2 (paused)
			// 3 (buffering)
			// 5 (video cued / video ready)
			let label;

			// video ended, make sure we track the complete event just once
			if (vars.analytics.youtubePlayers[playerId].playerObj.getPlayerState() === 0 && !vars.analytics.youtubePlayers[playerId].complete) {
				label = 'Complete';
				vars.analytics.youtubePlayers[playerId].complete = true;
			}

			// video playing
			if (vars.analytics.youtubePlayers[playerId].playerObj.getPlayerState() == 1) {
				// recursively call this function in 1s to keep track of video progress
				vars.analytics.youtubePlayers[playerId].timer = window.setTimeout(() => { youtubePlayerStateChange(e, playerId); }, 1000);

				// if this is the first time we get the playing status, track it as start
				if (!vars.analytics.youtubePlayers[playerId].start) {
					label = 'Start';
					vars.analytics.youtubePlayers[playerId].start = true;
				}

				else {
					const currentTime = vars.analytics.youtubePlayers[playerId].playerObj.getCurrentTime();

					if (Math.ceil(Math.ceil((currentTime / vars.analytics.youtubePlayers[playerId].duration) * 100) / 10) - 1 > vars.analytics.youtubePlayers[playerId].progress) {
						vars.analytics.youtubePlayers[playerId].progress = Math.ceil(Math.ceil((currentTime / vars.analytics.youtubePlayers[playerId].duration) * 100) / 10) - 1;

						if (vars.analytics.youtubePlayers[playerId].progress != 10) {
							label = (vars.analytics.youtubePlayers[playerId].progress * 10) + '%';
						}

						else {
							window.clearTimeout(vars.analytics.youtubePlayers[playerId].timer);
						}
					}
				}
			}

			// video buffering
			if (vars.analytics.youtubePlayers[playerId].playerObj.getPlayerState() == 3) {
				// recursively call this function in 1s to keep track of video progress
				vars.analytics.youtubePlayers[playerId].timer = window.setTimeout(() => { youtubePlayerStateChange(e, playerId); }, 1000);
			}

			// send event to GA if label was set
			if (label) {
				videoTrackingEvent(playerId, label, vars.analytics.youtubePlayers[playerId].title, vars.analytics.youtubePlayers[playerId].progress * 10);
			}
		}
		else {
			// iframe no longer exists, remove it from array
			delete vars.analytics.youtubePlayers[playerId];
		}
	};

	const vimeoReceiveMessage = (e) => {

		// check message is coming from vimeo
		if ((/^https?:\/\/player.vimeo.com/).test(e.origin)) {
			// parse the data
			const data = JSON.parse(e.data);
			const player = vars.analytics.vimeoPlayers[data.player_id];
			let label;

			switch (data.event) {

				case 'ready':
					// set player object variables
					player.progress = 0;
					player.start = false;
					player.complete = false;
					// set the listeners
					vimeoSendMessage(data.player_id, e.origin, 'addEventListener', 'play');
					vimeoSendMessage(data.player_id, e.origin, 'addEventListener', 'finish');
					vimeoSendMessage(data.player_id, e.origin, 'addEventListener', 'playProgress');
					break;

				case 'play':
					// if this is the first time we get the playing status, track it as start
					if (!player.start) {
						label = 'Start';
						player.start = true;
					}
					break;

				case 'playProgress': {
					const playerProgress = Math.floor(data.data.percent * 10) * 10;

					if (playerProgress > player.progress) {

						player.progress = playerProgress;

						if (player.progress != 10) {
							label = (player.progress * 10) + '%';
						}
					}
					break;
				}

				case 'finish':
					// make sure we capture finish event just once
					if (!player.complete) {
						label = 'Complete';
						player.complete = true;
					}
			}

			if (label) {
				videoTrackingEvent(data.player_id, label, 'No title', player.progress * 10); // vimeo doesn't provide a mechanism for getting the video title
			}
		}
	};

	const vimeoSendMessage = (playerId, origin, action, value) => {
		const data = {
			method: action
		};
		if (value) data['value'] = value;
		const playerElem = document.getElementById(playerId) as HTMLIFrameElement;
		if (playerElem !== null) playerElem.contentWindow?.postMessage(JSON.stringify(data), origin);
	};

	const videoTrackingEvent = (playerId, label, title, value) => {
		sendGAEvent({
			eventCategory: 'Video',
			eventAction: label,
			eventLabel: title + ' (' + jQuery('#' + playerId).attr('src').split('?')[0] + ')',
			eventValue: value,
			nonInteraction: cfg.analytics.video.nonInteraction
		});
	};

	// trigger events for user active/inactive and count active time
	const initUserActive = () => {
		// initialize the variables
		vars.analytics.userActive = {
			active: true,		// is user currently active
			timer: false,		// setTimeout timer
			throttle: false,	// is throttling currently active
			lastChangeTime: (new Date()).getTime(),
			activeTime: 0,
			inactiveTime: 0,
		};

		// add listeners to common user action events
		['resize', 'scroll', 'orientationchange'].forEach((e) => {
			window.addEventListener(e, userActive, { passive: true });
		});

		[
			'keydown', 'keyup', 'keypress',
			'mousewheel', 'wheel',
			'touchstart', 'touchmove', 'touchend', 'touchcancel'
		].forEach((e) => {
			document.addEventListener(e, userActive, { passive: true });
		});

		// add listener for window on focus in/out
		document.addEventListener('visibilitychange', () => {
			if (document.visibilityState === 'visible') userActive();
			else userInactive();
		});

		// refresh active and inactive time counters
		const timer = window.setInterval(() => {
			// Stop updating if maxTime is reached
			if (vars.analytics.userActive.activeTime + vars.analytics.userActive.inactiveTime >= cfg.analytics.userActive.maxTime) window.clearInterval(timer);
			// Update counters
			else {
				if (vars.analytics.userActive.active) {
					// update active time
					vars.analytics.userActive.activeTime += (new Date()).getTime() - vars.analytics.userActive.lastChangeTime;
				}
				else {
					// update inactive time
					vars.analytics.userActive.inactiveTime += (new Date()).getTime() - vars.analytics.userActive.lastChangeTime;
				}
				// update last change time
				vars.analytics.userActive.lastChangeTime = (new Date()).getTime();
			}
		}, cfg.analytics.userActive.refresh);

		// initialize active state
		userActive();
	};

	// function called to indicate user is currently active (heartbeat)
	const userActive = () => {
		// if no throttle
		if (!vars.analytics.userActive.throttle) {
			vars.analytics.userActive.throttle = true;
			window.setTimeout(() => { vars.analytics.userActive.throttle = false; }, cfg.analytics.userActive.throttle);
			// when changing from being inactive
			if (!vars.analytics.userActive.active) {
				// set state to active
				vars.analytics.userActive.active = true;
				// update inactive time
				vars.analytics.userActive.inactiveTime += (new Date()).getTime() - vars.analytics.userActive.lastChangeTime;
				// update last change time
				vars.analytics.userActive.lastChangeTime = (new Date()).getTime();
			}

			// set state to active
			vars.analytics.userActive.active = true;

			// after idle time turn inactive
			window.clearTimeout(vars.analytics.userActive.timer);
			vars.analytics.userActive.timer = window.setTimeout(() => { userInactive(); }, cfg.analytics.userActive.idleTime);
		}
	};

	// function called to indicate the user is currently inactive
	const userInactive = () => {
		// set state to inactive
		vars.analytics.userActive.active = false;
		// clear timer
		window.clearTimeout(vars.analytics.userActive.timer);
		// add active time
		vars.analytics.userActive.activeTime += (new Date()).getTime() - vars.analytics.userActive.lastChangeTime;
		// update last change time
		vars.analytics.userActive.lastChangeTime = (new Date()).getTime();
	};

	const initABTesting = () => {
		log('abTest params', cfg.analytics.abTest);

		// get a/b test group cookie
		vars.analytics.abTestGroup = util.cookie('abTestGroup');
		if (!vars.analytics.abTestGroup.test(new RegExp(`^${cfg.analytics.abTest.name}-[AB]$`))) {
			// set a/b test group
			if (Math.random() < cfg.analytics.abTest.split) vars.analytics.abTestGroup = cfg.analytics.abTest.name + '-A';
			else vars.analytics.abTestGroup = cfg.analytics.abTest.name + '-B';
		}

		// set cookie
		util.cookie('abTestGroup', vars.analytics.abTestGroup, { maxAge: cfg.analytics.abTest.cookieDays * 86400, path: '/' });

		// Set body attribute that can be used by css and js
		vars.body.attr('abtestgroup', vars.abTestGroup);

		// Show/hide elements based on their attributes and classes
		abTestDisplay(jQuery(cfg.analytics.abTest.displaySelector));
		mutation.addHandler('addNode', cfg.analytics.abTest.displaySelector, abTestDisplay);
	};

	// Show/hide element based on region
	const abTestDisplay = (elems) => {
		/**
		 *
		 * Checks for elements with attribute data-abtest with values 'testName-A' or 'testName-B',
		 * or class names 'abtest-testName-a' or 'abtest-testName-b'
		 *
		 */
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

				let elemGroupMatch = false;

				// Get attribute options first
				if (elem.attr('data-abtest') === vars.abTestGroup) elemGroupMatch = true;

				// Get classes
				if (elem.hasClass('abtest-' + vars.abTestGroup)) elemGroupMatch = true;

				// hide/remove element
				if (!elemGroupMatch) {
					if (cfg.analytics.abTest.removeNoMatch) elem.remove();
					else elem.css('display', 'none');
				}
			});
		}
	};

	return Object.defineProperties({
		init,
		sendGAEvent,
		sendGAPageview
	}, {
		// Set the status property as read-only
		status: {
			get() {
				return vars.analytics.status;
			},
			set() {
				return undefined;
			}
		}
	}) as {
		init: () => void,
		sendGAEvent: (eventInfo: {
			eventName?: string,
			eventAction: string,
			eventCategory: string,
			eventLabel?: string,
			eventValue?: number,
			nonInteraction?: boolean,
			hitCallback?: () => void
		}) => void,
		sendGAPageview: (pageInfo: {
			url: string,
			title: string,
			callback?: () => void
		}) => void,
		status: string
	};
})();

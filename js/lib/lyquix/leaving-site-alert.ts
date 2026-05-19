/**
 * leaving-site-alert.ts - Leaving site alert module functionality
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
import { util } from './util';

declare const jQuery;

export const leavingSiteAlert = (() => {
	let pendingHref: string | null = null;
	let pendingTarget: string | null = null;
	let dialogElem = null;
	let dialog: HTMLDialogElement | null = null;

	const init = (customCfg?: object) => {
		if (vars.leavingSiteAlert?.init) return;

		vars.leavingSiteAlert = {};

		cfg.leavingSiteAlert = {
			enabled: true,
			moduleSelector: '#lqx-module-leaving-site-alert',
		};

		if (customCfg) cfg.leavingSiteAlert = jQuery.extend(true, cfg.leavingSiteAlert, customCfg);

		if (cfg.leavingSiteAlert.enabled) {
			log('Initializing `leavingSiteAlert`');

			vars.document.ready(function () {
				setup();
			});
		}

		vars.leavingSiteAlert.init = true;
	};

	const setup = function () {
		const moduleElem = jQuery(cfg.leavingSiteAlert.moduleSelector);
		if (!moduleElem.length) return;

		log('Setting up leaving-site-alert', moduleElem);

		jQuery.ajax({
			cache: false,
			dataType: 'json',
			url: cfg.siteURL + '/wp-json/lyquix/v3/leaving-site-alert',
			success: (data) => {
				if (!data || !data.length) {
					moduleElem.remove();
					log('No leaving-site-alert rules configured');
					return;
				}

				const rules = [];

				data.forEach((rawRule) => {
					const r = util.validateData(rawRule, {
						type: 'object',
						keys: {
							id: util.schemaStrReqNotEmp,
							heading: util.schemaStrReqEmp,
							body: util.schemaStrReqEmp,
							confirm_label: { type: 'string', required: true, default: 'Leave Site' },
							cancel_label: { type: 'string', required: true, default: 'Stay on Page' },
							link_mode: { type: 'string', required: true, default: 'exclude', allowed: ['include', 'exclude'] },
							url_patterns: {
								type: 'array',
								required: true,
								default: [],
								elems: {
									type: 'object',
									keys: {
										url_pattern: util.schemaStrReqEmp
									}
								}
							},
							ui_type: { type: 'string', required: true, default: 'modal', allowed: ['browser', 'modal'] },
							heading_style: { type: 'string', required: true, default: 'h3', allowed: ['p', 'h2', 'h3', 'h4'] }
						}
					} as any);

					if (!r || !r.isValid) {
						warn('Invalid leaving-site-alert rule, skipping', rawRule);
						return;
					}

					rules.push(r.data);
				});

				if (!rules.length) {
					moduleElem.remove();
					return;
				}

				vars.leavingSiteAlert.rules = rules;

				// Create the dialog element in JS and append to module section
				dialogElem = jQuery(`
					<dialog aria-modal="true">
						<div class="title"></div>
						<div class="body"></div>
						<div class="buttons">
							<button class="cancel"></button>
							<button class="confirm"></button>
						</div>
					</dialog>
				`).appendTo(moduleElem);

				dialog = dialogElem[0] as HTMLDialogElement;

				dialogElem.find('.cancel').on('click', () => dialog.close());

				dialogElem.find('.confirm').on('click', () => {
					dialog.close();
					navigate();
				});

				// Backdrop click (clicking dialog element itself outside content) closes without navigating
				dialogElem.on('click', function (e) {
					if (e.target === this) dialog.close();
				});

				// Reset pending state on any close, including ESC key
				dialog.addEventListener('close', () => {
					pendingHref = null;
					pendingTarget = null;
					log('Leaving-site-alert dialog closed');
				});

				bindClickHandler();
			},
			error: (xhr, status, errorMsg) => {
				error('Error fetching leaving-site-alert rules', status, errorMsg);
			}
		});
	};

	const isExternal = (href: string): boolean => {
		try {
			const url = new URL(href, window.location.href);
			return url.hostname !== window.location.hostname;
		} catch {
			return false;
		}
	};

	const matchesPattern = (url: string, pattern: string): boolean => {
		if (!pattern) return false;
		const escaped = pattern.replace(/[.+?{}()|[\]\\]/g, '\\$&').replace(/\*/g, '.*');
		return new RegExp('^' + escaped + '$').test(url);
	};

	const findMatchingRule = (href: string) => {
		const rules = vars.leavingSiteAlert?.rules;
		if (!rules) return null;

		for (const rule of rules) {
			const patterns = (rule.url_patterns || []).map((p) => p.url_pattern).filter(Boolean);
			const matches = patterns.some((p) => matchesPattern(href, p));

			if (rule.link_mode === 'include' && matches) return rule;
			if (rule.link_mode === 'exclude' && !matches) return rule;
		}

		return null;
	};

	const bindClickHandler = () => {
		vars.document.on('click', 'a', function (e) {
			const anchor = jQuery(this);
			const href = anchor.attr('href');

			if (!href) return;
			if (href.startsWith('#') || href.startsWith('mailto:') || href.startsWith('tel:')) return;
			if (!isExternal(href)) return;

			const rule = findMatchingRule(href);
			if (!rule) return;

			e.preventDefault();

			pendingHref = href;
			pendingTarget = anchor.attr('target') || null;

			if (rule.ui_type === 'browser') {
				const bodyText = jQuery('<div>').html(rule.body).text();
				const message = rule.heading ? rule.heading + '\n\n' + bodyText : bodyText;
				if (window.confirm(message)) navigate();
			} else {
				showDialog(rule);
			}
		});
	};

	const showDialog = (rule) => {
		if (!dialog) {
			error('Leaving-site-alert: dialog not initialised');
			return;
		}

		const titleElem = dialogElem.find('.title');
		if (rule.heading) {
			const tag = rule.heading_style === 'p' ? 'p' : rule.heading_style;
			titleElem.html(`<${tag}>${rule.heading}</${tag}>`);
		} else {
			titleElem.empty();
		}

		dialogElem.find('.body').html(rule.body);
		dialogElem.find('.cancel').text(rule.cancel_label || 'Stay on Page');
		dialogElem.find('.confirm').text(rule.confirm_label || 'Leave Site');

		dialog.showModal();
		log('Leaving-site-alert dialog opened');
	};

	const navigate = () => {
		if (!pendingHref) return;
		if (pendingTarget === '_blank') {
			window.open(pendingHref, '_blank', 'noopener');
		} else {
			window.location.href = pendingHref;
		}
	};

	return { init };
})();

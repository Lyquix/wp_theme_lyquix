/**
 * redirects-metabox.js - Redirects manager for the Classic Editor meta box
 *
 * @version     3.2.0
 * @package     wp_theme_lyquix
 * @author      Lyquix
 * @copyright   Copyright (C) 2015 - 2024 Lyquix
 * @license     GNU General Public License version 2 or later
 * @link        https://github.com/Lyquix/wp_theme_lyquix
 */

(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {
		var container = document.getElementById('lqx-redirects-metabox');
		if (!container) return;

		var nonce   = (typeof lqxRedirects !== 'undefined') ? lqxRedirects.nonce   : '';
		var postId  = (typeof lqxRedirects !== 'undefined') ? lqxRedirects.postId  : 0;
		var restUrl = (typeof lqxRedirects !== 'undefined') ? lqxRedirects.restUrl : '';

		fetchRedirects();

		// Refresh button wired up in render, stored here for re-use
		function fetchRedirects() {
			container.innerHTML = '';
			container.appendChild(makeText('Loading…', '#666'));

			fetch(restUrl + '?post_id=' + postId, {
				headers: { 'X-WP-Nonce': nonce }
			})
				.then(function (r) { return r.json(); })
				.then(function (data) { render(data); })
				.catch(function () { renderError('Request failed.'); });
		}

		function render(data) {
			container.innerHTML = '';

			if (data.error) {
				renderError(data.error);
				return;
			}

			var chains  = data.chains || [];
			var direct  = chains.filter(function (c) { return c.length === 1; });
			var chained = chains.filter(function (c) { return c.length > 1; });

			if (chains.length === 0) {
				container.appendChild(makeText('No redirects found pointing to this post.', '#666', true));
			} else {
				if (direct.length > 0) {
					container.appendChild(makeSectionHeading('Redirects (' + direct.length + ')', false));
					direct.forEach(function (chain, i) {
						container.appendChild(buildChain(chain, data.post_path, i === direct.length - 1));
					});
				}

				if (chained.length > 0) {
					container.appendChild(makeSectionHeading('Redirect Chains (' + chained.length + ')', direct.length > 0));
					chained.forEach(function (chain, i) {
						container.appendChild(buildChain(chain, data.post_path, i === chained.length - 1));
					});
				}
			}

			// Add redirect form
			container.appendChild(buildAddForm());

			// Refresh button
			var btn = document.createElement('button');
			btn.type = 'button';
			btn.className = 'button button-secondary button-small';
			btn.textContent = 'Refresh';
			btn.style.marginTop = '8px';
			btn.addEventListener('click', fetchRedirects);
			container.appendChild(btn);
		}

		function buildAddForm() {
			var wrap = document.createElement('div');
			wrap.style.borderTop   = '1px solid #ddd';
			wrap.style.marginTop   = '12px';
			wrap.style.paddingTop  = '12px';

			var heading = document.createElement('p');
			heading.textContent    = 'Add Redirect';
			heading.style.margin   = '0 0 6px';
			heading.style.fontWeight = '600';
			heading.style.fontSize = '12px';
			wrap.appendChild(heading);

			var row = document.createElement('div');
			row.style.display      = 'flex';
			row.style.gap          = '6px';
			row.style.alignItems   = 'center';

			var input = document.createElement('input');
			input.type             = 'text';
			input.placeholder      = '/old-url';
			input.style.flex       = '1';
			input.style.minWidth   = '0';
			input.style.fontFamily = 'monospace';
			input.style.fontSize   = '11px';
			input.className        = 'widefat';

			var addBtn = document.createElement('button');
			addBtn.type        = 'button';
			addBtn.className   = 'button button-primary button-small';
			addBtn.textContent = 'Add';

			var errorMsg = document.createElement('p');
			errorMsg.style.color     = '#cc1818';
			errorMsg.style.fontSize  = '11px';
			errorMsg.style.margin    = '4px 0 0';
			errorMsg.style.display   = 'none';

			function doAdd() {
				var url = input.value.trim();
				if (!url) return;

				addBtn.disabled    = true;
				addBtn.textContent = 'Adding…';
				errorMsg.style.display = 'none';

				fetch(restUrl, {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-WP-Nonce': nonce
					},
					body: JSON.stringify({ post_id: postId, source_url: url })
				})
					.then(function (r) { return r.json(); })
					.then(function (data) {
						if (data.code) {
							errorMsg.textContent   = data.message || 'Failed to create redirect.';
							errorMsg.style.display = 'block';
							addBtn.disabled    = false;
							addBtn.textContent = 'Add';
						} else {
							input.value = '';
							render(data);
						}
					})
					.catch(function () {
						errorMsg.textContent   = 'Request failed.';
						errorMsg.style.display = 'block';
						addBtn.disabled    = false;
						addBtn.textContent = 'Add';
					});
			}

			addBtn.addEventListener('click', doAdd);
			input.addEventListener('keydown', function (e) {
				if (e.key === 'Enter') doAdd();
			});

			row.appendChild(input);
			row.appendChild(addBtn);
			wrap.appendChild(row);
			wrap.appendChild(errorMsg);

			return wrap;
		}

		function renderError(msg) {
			container.innerHTML = '';
			container.appendChild(makeText(msg, '#cc1818'));
		}

		/**
		 * Build a chain block: one row per redirect hop + terminal "This Post" row
		 */
		function buildChain(chain, postPath, isLast) {
			var wrap = document.createElement('div');
			wrap.style.borderBottom = isLast ? 'none' : '1px solid #eee';
			wrap.style.paddingBottom = isLast ? '0' : '10px';
			wrap.style.marginBottom  = isLast ? '0' : '10px';

			chain.forEach(function (item) {
				wrap.appendChild(buildStep(item));
			});

			// Terminal row
			var terminal = document.createElement('div');
			terminal.style.display     = 'flex';
			terminal.style.alignItems  = 'center';
			terminal.style.gap         = '6px';
			terminal.style.fontSize    = '11px';
			terminal.style.color       = '#666';
			terminal.style.paddingLeft = '13px';

			var arrow = document.createElement('span');
			arrow.textContent = '↳';

			var pathSpan = document.createElement('span');
			pathSpan.textContent        = postPath;
			pathSpan.style.fontFamily   = 'monospace';
			pathSpan.style.flex         = '1';
			pathSpan.style.minWidth     = '0';
			pathSpan.style.overflow     = 'hidden';
			pathSpan.style.textOverflow = 'ellipsis';
			pathSpan.style.whiteSpace   = 'nowrap';

			var badge = document.createElement('span');
			badge.textContent            = 'This Post';
			badge.style.background       = '#46b450';
			badge.style.color            = '#fff';
			badge.style.borderRadius     = '3px';
			badge.style.padding          = '1px 5px';
			badge.style.fontSize         = '10px';
			badge.style.flexShrink       = '0';

			terminal.appendChild(arrow);
			terminal.appendChild(pathSpan);
			terminal.appendChild(badge);
			wrap.appendChild(terminal);

			return wrap;
		}

		/**
		 * Build one hop row: status dot + source URL + code badge + hit count
		 */
		function buildStep(item) {
			var row = document.createElement('div');
			row.style.display       = 'flex';
			row.style.alignItems    = 'center';
			row.style.gap           = '6px';
			row.style.marginBottom  = '3px';
			row.style.fontSize      = '11px';
			row.style.lineHeight    = '1.5';

			// Status dot
			var dot = document.createElement('span');
			dot.title                = item.status === 'enabled' ? 'Enabled' : 'Disabled';
			dot.style.width          = '7px';
			dot.style.height         = '7px';
			dot.style.borderRadius   = '50%';
			dot.style.background     = item.status === 'enabled' ? '#46b450' : '#bbb';
			dot.style.flexShrink     = '0';

			// Source URL
			var urlSpan = document.createElement('a');
			urlSpan.textContent          = item.url;
			urlSpan.href                 = item.url;
			urlSpan.target               = '_blank';
			urlSpan.rel                  = 'noopener noreferrer';
			urlSpan.title                = item.url;
			urlSpan.style.flex           = '1';
			urlSpan.style.minWidth       = '0';
			urlSpan.style.fontFamily     = 'monospace';
			urlSpan.style.overflow       = 'hidden';
			urlSpan.style.textOverflow   = 'ellipsis';
			urlSpan.style.whiteSpace     = 'nowrap';
			urlSpan.style.color          = '#007cba';
			urlSpan.style.textDecoration = 'none';

			// Code badge
			var code = document.createElement('span');
			code.textContent         = item.action_code;
			code.style.background    = '#007cba';
			code.style.color         = '#fff';
			code.style.borderRadius  = '3px';
			code.style.padding       = '1px 5px';
			code.style.fontSize      = '10px';
			code.style.flexShrink    = '0';

			row.appendChild(dot);
			row.appendChild(urlSpan);
			row.appendChild(code);

			// Hit count (only if > 0)
			if (item.hits > 0) {
				var hits = document.createElement('span');
				hits.textContent     = item.hits.toLocaleString() + ' hits';
				hits.style.color     = '#888';
				hits.style.fontSize  = '10px';
				hits.style.flexShrink = '0';
				hits.style.whiteSpace = 'nowrap';
				row.appendChild(hits);
			}

			return row;
		}

		function makeSectionHeading(text, topMargin) {
			var p = document.createElement('p');
			p.textContent      = text;
			p.style.margin     = (topMargin ? '12px' : '0') + ' 0 8px';
			p.style.fontWeight = '600';
			p.style.fontSize   = '12px';
			return p;
		}

		function makeText(text, color, italic) {
			var p = document.createElement('p');
			p.textContent  = text;
			p.style.margin = '0';
			p.style.color  = color || '';
			p.style.fontSize = '12px';
			if (italic) p.style.fontStyle = 'italic';
			return p;
		}
	});
})();

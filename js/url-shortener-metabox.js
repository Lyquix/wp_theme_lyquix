/**
 * url-shortener-metabox.js - URL shortener for the Classic Editor meta box
 *
 * @version     3.4.0
 * @package     wp_theme_lyquix
 * @author      Lyquix
 * @copyright   Copyright (C) 2015 - 2024 Lyquix
 * @license     GNU General Public License version 2 or later
 * @link        https://github.com/Lyquix/wp_theme_lyquix
 */

(function ($) {
	'use strict';

	$(document).ready(function () {
		var container = document.getElementById('lqx-url-shortener-metabox');
		if (!container) return;

		var shortUrl = container.getAttribute('data-short-url');
		var qrEnabled = container.getAttribute('data-qr-enabled') === '1';

		if (shortUrl) {
			renderShortUrl(container, shortUrl, qrEnabled);
		} else {
			renderGenerateButton(container, qrEnabled);
		}
	});

	function renderGenerateButton(container, qrEnabled) {
		container.innerHTML = '';

		var generateBtn = document.createElement('button');
		generateBtn.type = 'button';
		generateBtn.className = 'button button-primary';
		generateBtn.textContent = 'Generate Short URL';
		generateBtn.addEventListener('click', function () {
			generateBtn.disabled = true;
			generateBtn.textContent = 'Generating…';

			var nonce = (typeof lqxUrlShortener !== 'undefined') ? lqxUrlShortener.nonce : '';
			var postId = (typeof lqxUrlShortener !== 'undefined') ? lqxUrlShortener.postId : 0;
			var restUrl = (typeof lqxUrlShortener !== 'undefined') ? lqxUrlShortener.restUrl : '';

			fetch(restUrl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': nonce
				},
				body: JSON.stringify({ post_id: postId })
			})
				.then(function (response) { return response.json(); })
				.then(function (data) {
					if (data && data.short_url) {
						container.innerHTML = '';
						renderShortUrl(container, data.short_url, qrEnabled);
					} else {
						var msg = (data && data.message) ? data.message : 'Failed to generate short URL.';
						showError(container, generateBtn, msg);
					}
				})
				.catch(function () {
					showError(container, generateBtn, 'Request failed. Check provider settings.');
				});
		});

		container.appendChild(generateBtn);
	}

	function showError(container, btn, message) {
		btn.disabled = false;
		btn.textContent = 'Generate Short URL';

		var existing = container.querySelector('.lqx-url-shortener-error');
		if (existing) existing.remove();

		var errorMsg = document.createElement('p');
		errorMsg.className = 'lqx-url-shortener-error';
		errorMsg.style.color = '#cc1818';
		errorMsg.style.marginTop = '8px';
		errorMsg.style.fontSize = '12px';
		errorMsg.textContent = message;
		container.appendChild(errorMsg);
	}

	function renderShortUrl(container, shortUrl, qrEnabled) {
		// Short URL display with copy button
		var urlContainer = document.createElement('div');
		urlContainer.style.display = 'flex';
		urlContainer.style.alignItems = 'center';
		urlContainer.style.gap = '8px';
		urlContainer.style.marginBottom = '12px';

		var urlCode = document.createElement('input');
		urlCode.type = 'text';
		urlCode.readOnly = true;
		urlCode.value = shortUrl;
		urlCode.style.flex = '1';
		urlCode.style.border = 'none';
		urlCode.style.background = '#f0f0f0';
		urlCode.style.fontFamily = 'monospace';
		urlCode.style.fontSize = '11px';
		urlCode.style.minWidth = '0';
		urlCode.style.boxSizing = 'border-box';

		var copyBtn = document.createElement('button');
		copyBtn.type = 'button';
		copyBtn.className = 'button button-secondary button-small';
		copyBtn.textContent = 'Copy';
		copyBtn.addEventListener('click', function () {
			navigator.clipboard.writeText(shortUrl).then(function () {
				copyBtn.textContent = 'Copied!';
				setTimeout(function () {
					copyBtn.textContent = 'Copy';
				}, 2000);
			});
		});

		urlContainer.appendChild(urlCode);
		urlContainer.appendChild(copyBtn);
		container.appendChild(urlContainer);

		// QR code section (if enabled and library available)
		if (qrEnabled && typeof qrcode !== 'undefined') {
			var qr = qrcode(0, 'M');
			qr.addData(shortUrl);
			qr.make();
			var svgMarkup = qr.createSvgTag({ cellSize: 4, margin: 4, scalable: true });

			var qrLayout = document.createElement('div');
			qrLayout.style.display = 'flex';
			qrLayout.style.alignItems = 'center';
			qrLayout.style.gap = '12px';

			var preview = document.createElement('div');
			preview.style.flex = '1 1 auto';
			preview.style.minWidth = '0';
			preview.innerHTML = svgMarkup;
			qrLayout.appendChild(preview);

			var btnContainer = document.createElement('div');
			btnContainer.style.display = 'flex';
			btnContainer.style.flexDirection = 'column';
			btnContainer.style.gap = '8px';
			btnContainer.style.flexShrink = '0';

			var pngBtn = document.createElement('button');
			pngBtn.type = 'button';
			pngBtn.className = 'button button-secondary';
			pngBtn.textContent = 'Download PNG';
			pngBtn.addEventListener('click', function () {
				var dataUrl = generatePng(shortUrl, 1024);
				downloadFile(dataUrl, 'qr-short-url.png');
			});

			var svgBtn = document.createElement('button');
			svgBtn.type = 'button';
			svgBtn.className = 'button button-secondary';
			svgBtn.textContent = 'Download SVG';
			svgBtn.addEventListener('click', function () {
				var svgContent = qr.createSvgTag({ cellSize: 4, margin: 4, scalable: true });
				var fullSvg = '<?xml version="1.0" encoding="UTF-8"?>\n' + svgContent;
				var blob = new Blob([fullSvg], { type: 'image/svg+xml' });
				var blobUrl = URL.createObjectURL(blob);
				downloadFile(blobUrl, 'qr-short-url.svg');
				URL.revokeObjectURL(blobUrl);
			});

			btnContainer.appendChild(pngBtn);
			btnContainer.appendChild(svgBtn);
			qrLayout.appendChild(btnContainer);
			container.appendChild(qrLayout);
		}
	}

	function generatePng(url, size) {
		var qr = qrcode(0, 'M');
		qr.addData(url);
		qr.make();

		var moduleCount = qr.getModuleCount();
		var margin = 4;
		var cellSize = Math.floor((size - margin * 2) / moduleCount);
		var totalSize = moduleCount * cellSize + margin * 2;

		var canvas = document.createElement('canvas');
		canvas.width = totalSize;
		canvas.height = totalSize;
		var ctx = canvas.getContext('2d');

		ctx.fillStyle = '#ffffff';
		ctx.fillRect(0, 0, totalSize, totalSize);

		ctx.fillStyle = '#000000';
		for (var row = 0; row < moduleCount; row++) {
			for (var col = 0; col < moduleCount; col++) {
				if (qr.isDark(row, col)) {
					ctx.fillRect(
						col * cellSize + margin,
						row * cellSize + margin,
						cellSize,
						cellSize
					);
				}
			}
		}

		return canvas.toDataURL('image/png');
	}

	function downloadFile(dataUrl, filename) {
		var a = document.createElement('a');
		a.href = dataUrl;
		a.download = filename;
		document.body.appendChild(a);
		a.click();
		document.body.removeChild(a);
	}
})(jQuery);

/**
 * qr-code-metabox.js - QR code generator for the Classic Editor meta box
 *
 * @version     3.2.0
 * @package     wp_theme_lyquix
 * @author      Lyquix
 * @copyright   Copyright (C) 2015 - 2024 Lyquix
 * @license     GNU General Public License version 2 or later
 * @link        https://github.com/Lyquix/wp_theme_lyquix
 */

(function ($) {
	'use strict';

	$(document).ready(function () {
		var container = document.getElementById('lqx-qr-code-metabox');
		if (!container) return;

		var url = container.getAttribute('data-url');

		if (!url) return;

		// Clear placeholder message
		container.innerHTML = '';

		// --- Permalink URL heading + QR code ---

		var permalinkHeading = document.createElement('p');
		permalinkHeading.style.margin = '0 0 8px';
		permalinkHeading.style.fontWeight = '600';
		permalinkHeading.textContent = 'Permalink URL';
		container.appendChild(permalinkHeading);

		var qr = qrcode(0, 'M');
		qr.addData(url);
		qr.make();
		var svgMarkup = qr.createSvgTag({ cellSize: 4, margin: 4, scalable: true });

		container.appendChild(buildQRRow(svgMarkup, function () {
			return generatePng(url, 1024);
		}, function () {
			return qr.createSvgTag({ cellSize: 4, margin: 4, scalable: true });
		}, ''));

		// --- UTM section ---

		container.appendChild(buildUtmSection(url));

		// --- Custom URL section ---

		container.appendChild(buildCustomUrlSection());
	});

	// Build a QR row: image left, stacked download buttons right
	function buildQRRow(svgMarkup, getPngData, getSvgContent, filenameSuffix) {
		var layout = document.createElement('div');
		layout.style.display = 'flex';
		layout.style.alignItems = 'center';
		layout.style.gap = '12px';

		var preview = document.createElement('div');
		preview.style.flex = '1 1 auto';
		preview.style.minWidth = '0';
		preview.innerHTML = svgMarkup;
		layout.appendChild(preview);

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
			downloadFile(getPngData(), 'qr-code' + filenameSuffix + '.png');
		});

		var svgBtn = document.createElement('button');
		svgBtn.type = 'button';
		svgBtn.className = 'button button-secondary';
		svgBtn.textContent = 'Download SVG';
		svgBtn.addEventListener('click', function () {
			var fullSvg = '<?xml version="1.0" encoding="UTF-8"?>\n' + getSvgContent();
			var blob = new Blob([fullSvg], { type: 'image/svg+xml' });
			var blobUrl = URL.createObjectURL(blob);
			downloadFile(blobUrl, 'qr-code' + filenameSuffix + '.svg');
			URL.revokeObjectURL(blobUrl);
		});

		btnContainer.appendChild(pngBtn);
		btnContainer.appendChild(svgBtn);
		layout.appendChild(btnContainer);

		return layout;
	}

	// Build the UTM section with form fields and live QR code
	function buildUtmSection(baseUrl) {
		var inputStyle = 'width:100%;margin-bottom:8px;box-sizing:border-box;';

		var section = document.createElement('div');
		section.style.borderTop = '1px solid #ddd';
		section.style.marginTop = '12px';
		section.style.paddingTop = '12px';

		var heading = document.createElement('p');
		heading.style.margin = '0 0 8px';
		heading.style.fontWeight = '600';
		heading.textContent = 'UTM Campaign URL';
		section.appendChild(heading);

		// Field builder
		function makeField(placeholder) {
			var inp = document.createElement('input');
			inp.type = 'text';
			inp.placeholder = placeholder;
			inp.setAttribute('style', inputStyle);
			inp.className = 'widefat';
			section.appendChild(inp);
			return inp;
		}

		var fCampaign = makeField('Campaign Name *');
		var fSource   = makeField('Campaign Source *');
		var fMedium   = makeField('Campaign Medium *');

		// Optional toggle
		var toggle = document.createElement('button');
		toggle.type = 'button';
		toggle.setAttribute('style', 'background:none;border:none;padding:0;cursor:pointer;color:#007cba;font-size:12px;margin-bottom:8px;');
		toggle.textContent = '▸ Optional parameters';
		section.appendChild(toggle);

		var optWrap = document.createElement('div');
		optWrap.style.display = 'none';
		section.appendChild(optWrap);

		function makeOptField(placeholder) {
			var inp = document.createElement('input');
			inp.type = 'text';
			inp.placeholder = placeholder;
			inp.setAttribute('style', inputStyle);
			inp.className = 'widefat';
			optWrap.appendChild(inp);
			return inp;
		}

		var fId      = makeOptField('Campaign ID');
		var fTerm    = makeOptField('Campaign Term');
		var fContent = makeOptField('Campaign Content');

		toggle.addEventListener('click', function () {
			var open = optWrap.style.display !== 'none';
			optWrap.style.display = open ? 'none' : 'block';
			toggle.textContent = (open ? '▸' : '▾') + ' Optional parameters';
		});

		// Resulting URL row
		var urlRow = document.createElement('div');
		urlRow.style.display = 'none';
		urlRow.style.marginTop = '8px';
		urlRow.style.marginBottom = '12px';

		var urlFlex = document.createElement('div');
		urlFlex.style.display = 'flex';
		urlFlex.style.gap = '6px';
		urlFlex.style.alignItems = 'center';

		var urlInput = document.createElement('input');
		urlInput.type = 'text';
		urlInput.readOnly = true;
		urlInput.setAttribute('style', 'flex:1 1 auto;min-width:0;font-size:11px;box-sizing:border-box;border:none;background:#f0f0f0;font-family:monospace;');
		urlInput.className = 'widefat';

		var copyBtn = document.createElement('button');
		copyBtn.type = 'button';
		copyBtn.className = 'button button-secondary button-small';
		copyBtn.textContent = 'Copy';
		copyBtn.style.flexShrink = '0';
		copyBtn.addEventListener('click', function () {
			navigator.clipboard.writeText(urlInput.value).then(function () {
				copyBtn.textContent = 'Copied!';
				setTimeout(function () { copyBtn.textContent = 'Copy'; }, 2000);
			});
		});

		urlFlex.appendChild(urlInput);
		urlFlex.appendChild(copyBtn);
		urlRow.appendChild(urlFlex);
		section.appendChild(urlRow);

		// QR code placeholder
		var qrWrap = document.createElement('div');
		section.appendChild(qrWrap);

		// Live update
		function update() {
			var campaign = fCampaign.value.trim();
			var source   = fSource.value.trim();
			var medium   = fMedium.value.trim();

			if (!campaign || !source || !medium) {
				urlRow.style.display = 'none';
				qrWrap.innerHTML = '';
				return;
			}

			var utmUrl = buildUtmUrl(baseUrl, {
				campaign: campaign,
				source:   source,
				medium:   medium,
				id:       fId.value.trim(),
				term:     fTerm.value.trim(),
				content:  fContent.value.trim()
			});

			urlInput.value = utmUrl;
			urlRow.style.display = 'block';

			var utmQr = qrcode(0, 'M');
			utmQr.addData(utmUrl);
			utmQr.make();
			var utmSvg = utmQr.createSvgTag({ cellSize: 4, margin: 4, scalable: true });

			qrWrap.innerHTML = '';
			qrWrap.appendChild(buildQRRow(utmSvg, function () {
				return generatePng(utmUrl, 1024);
			}, function () {
				return utmQr.createSvgTag({ cellSize: 4, margin: 4, scalable: true });
			}, '-utm'));
		}

		[fCampaign, fSource, fMedium, fId, fTerm, fContent].forEach(function (inp) {
			inp.addEventListener('input', update);
		});

		return section;
	}

	// Build the Custom URL section with a text input and live QR code
	function buildCustomUrlSection() {
		var section = document.createElement('div');
		section.style.borderTop = '1px solid #ddd';
		section.style.marginTop = '12px';
		section.style.paddingTop = '12px';

		var heading = document.createElement('p');
		heading.style.margin = '0 0 8px';
		heading.style.fontWeight = '600';
		heading.textContent = 'Custom URL';
		section.appendChild(heading);

		var urlInput = document.createElement('input');
		urlInput.type = 'text';
		urlInput.placeholder = 'https://example.com';
		urlInput.setAttribute('style', 'width:100%;margin-bottom:8px;box-sizing:border-box;');
		urlInput.className = 'widefat';
		section.appendChild(urlInput);

		var qrWrap = document.createElement('div');
		section.appendChild(qrWrap);

		urlInput.addEventListener('input', function () {
			var val = urlInput.value.trim();
			qrWrap.innerHTML = '';

			if (!val) return;

			try { new URL(val); } catch (e) { return; }

			var customQr = qrcode(0, 'M');
			customQr.addData(val);
			customQr.make();
			var customSvg = customQr.createSvgTag({ cellSize: 4, margin: 4, scalable: true });

			qrWrap.appendChild(buildQRRow(customSvg, function () {
				return generatePng(val, 1024);
			}, function () {
				return customQr.createSvgTag({ cellSize: 4, margin: 4, scalable: true });
			}, '-custom'));
		});

		return section;
	}

	function buildUtmUrl(baseUrl, utm) {
		var url = new URL(baseUrl);
		if (utm.campaign) url.searchParams.set('utm_campaign', utm.campaign);
		if (utm.source)   url.searchParams.set('utm_source',   utm.source);
		if (utm.medium)   url.searchParams.set('utm_medium',   utm.medium);
		if (utm.id)       url.searchParams.set('utm_id',       utm.id);
		if (utm.term)     url.searchParams.set('utm_term',     utm.term);
		if (utm.content)  url.searchParams.set('utm_content',  utm.content);
		return url.toString();
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

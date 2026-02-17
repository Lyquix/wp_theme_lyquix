/**
 * url-shortener-sidebar.js - URL shortener Gutenberg sidebar panel
 *
 * @version     3.2.0
 * @package     wp_theme_lyquix
 * @author      Lyquix
 * @copyright   Copyright (C) 2015 - 2024 Lyquix
 * @license     GNU General Public License version 2 or later
 * @link        https://github.com/Lyquix/wp_theme_lyquix
 */

const { PluginDocumentSettingPanel } = wp.editPost;
const { Button } = wp.components;
const { useSelect } = wp.data;
const { registerPlugin } = wp.plugins;
const { createElement, useState, useEffect, useRef, useCallback } = wp.element;

/**
 * Generate QR code SVG string from a URL
 */
function generateQRSvg(url) {
	const qr = qrcode(0, 'M');
	qr.addData(url);
	qr.make();
	return qr.createSvgTag({ cellSize: 4, margin: 4, scalable: true });
}

/**
 * Generate QR code as PNG data URL at specified size
 */
function generateQRPng(url, size) {
	const qr = qrcode(0, 'M');
	qr.addData(url);
	qr.make();

	const moduleCount = qr.getModuleCount();
	const margin = 4;
	const cellSize = Math.floor((size - margin * 2) / moduleCount);
	const totalSize = moduleCount * cellSize + margin * 2;

	const canvas = document.createElement('canvas');
	canvas.width = totalSize;
	canvas.height = totalSize;
	const ctx = canvas.getContext('2d');

	ctx.fillStyle = '#ffffff';
	ctx.fillRect(0, 0, totalSize, totalSize);

	ctx.fillStyle = '#000000';
	for (let row = 0; row < moduleCount; row++) {
		for (let col = 0; col < moduleCount; col++) {
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

/**
 * Trigger a file download from a data URL or blob URL
 */
function downloadFile(dataUrl, filename) {
	const a = document.createElement('a');
	a.href = dataUrl;
	a.download = filename;
	document.body.appendChild(a);
	a.click();
	document.body.removeChild(a);
}

const UrlShortenerPanel = () => {
	const [copyLabel, setCopyLabel] = useState('Copy');
	const [svgMarkup, setSvgMarkup] = useState('');

	const { shortUrl, postStatus } = useSelect((select) => {
		const editor = select('core/editor');
		const meta = editor.getEditedPostAttribute('meta') || {};
		return {
			shortUrl: meta['_short_url'] || '',
			postStatus: editor.getEditedPostAttribute('status')
		};
	});

	const qrEnabled = typeof lqxUrlShortener !== 'undefined' && lqxUrlShortener.qrEnabled;
	const qrAvailable = qrEnabled && typeof qrcode !== 'undefined';
	const isAutoDraft = postStatus === 'auto-draft';
	const isPublished = postStatus === 'publish';

	useEffect(() => {
		if (shortUrl && qrAvailable) {
			setSvgMarkup(generateQRSvg(shortUrl));
		} else {
			setSvgMarkup('');
		}
	}, [shortUrl, qrAvailable]);

	const handleCopy = useCallback(() => {
		if (!shortUrl) return;
		navigator.clipboard.writeText(shortUrl).then(() => {
			setCopyLabel('Copied!');
			setTimeout(() => setCopyLabel('Copy'), 2000);
		});
	}, [shortUrl]);

	const handleDownloadPng = useCallback(() => {
		if (!shortUrl) return;
		const dataUrl = generateQRPng(shortUrl, 1024);
		downloadFile(dataUrl, 'qr-short-url.png');
	}, [shortUrl]);

	const handleDownloadSvg = useCallback(() => {
		if (!shortUrl) return;
		const svgContent = generateQRSvg(shortUrl);
		const fullSvg = '<?xml version="1.0" encoding="UTF-8"?>\n' + svgContent;
		const blob = new Blob([fullSvg], { type: 'image/svg+xml' });
		const url = URL.createObjectURL(blob);
		downloadFile(url, 'qr-short-url.svg');
		URL.revokeObjectURL(url);
	}, [shortUrl]);

	let panelContent;

	if (isAutoDraft || !isPublished) {
		panelContent = createElement('p', {}, 'Publish the post to generate a short URL.');
	} else if (!shortUrl) {
		panelContent = createElement('p', {}, 'Short URL will be generated on save.');
	} else {
		const elements = [
			createElement('div', {
				key: 'url-display',
				style: { marginBottom: '12px' }
			},
				createElement('div', {
					style: {
						display: 'flex',
						alignItems: 'center',
						gap: '8px'
					}
				},
					createElement('input', {
						type: 'text',
						readOnly: true,
						value: shortUrl,
						style: {
							flex: '1',
							border: 'none',
							background: '#f0f0f0',
							fontFamily: 'monospace',
							fontSize: '11px',
							minWidth: 0
						},
						className: 'components-text-control__input'
					}),
					createElement(Button, {
						variant: 'secondary',
						onClick: handleCopy,
						className: 'is-small'
					}, copyLabel)
				)
			)
		];

		if (qrAvailable && svgMarkup) {
			elements.push(
				createElement('div', {
					key: 'qr-row',
					style: { display: 'flex', alignItems: 'center', gap: '12px' }
				},
					createElement('div', {
						dangerouslySetInnerHTML: { __html: svgMarkup },
						style: { flex: '1 1 auto', minWidth: 0 }
					}),
					createElement('div', {
						style: { display: 'flex', flexDirection: 'column', gap: '8px', flexShrink: 0 }
					},
						createElement(Button, {
							variant: 'secondary',
							onClick: handleDownloadPng
						}, 'Download PNG'),
						createElement(Button, {
							variant: 'secondary',
							onClick: handleDownloadSvg
						}, 'Download SVG')
					)
				)
			);
		}

		panelContent = createElement('div', { className: 'lqx-url-shortener-panel' }, ...elements);
	}

	return createElement(
		PluginDocumentSettingPanel,
		{ name: 'lqx-url-shortener', title: 'Short URL', className: 'lqx-url-shortener-panel' },
		panelContent
	);
};

registerPlugin('lqx-url-shortener', {
	render: UrlShortenerPanel,
	icon: 'admin-links'
});

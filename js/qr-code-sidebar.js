/**
 * qr-code-sidebar.js - QR code generator Gutenberg sidebar panel
 *
 * @version     3.4.0
 * @package     wp_theme_lyquix
 * @author      Lyquix
 * @copyright   Copyright (C) 2015 - 2024 Lyquix
 * @license     GNU General Public License version 2 or later
 * @link        https://github.com/Lyquix/wp_theme_lyquix
 */

const { PluginDocumentSettingPanel } = wp.editPost;
const { Button, TextControl } = wp.components;
const { useSelect } = wp.data;
const { registerPlugin } = wp.plugins;
const { createElement, useState, useEffect, useRef, useCallback } = wp.element;

/**
 * Generate QR code SVG string from a URL
 */
function generateQRSvg(url) {
	// Type 0 = auto-detect, Error correction level M
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

	// White background
	ctx.fillStyle = '#ffffff';
	ctx.fillRect(0, 0, totalSize, totalSize);

	// Draw modules
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

/**
 * Build a URL with UTM parameters appended
 */
function buildUtmUrl(baseUrl, utm) {
	const url = new URL(baseUrl);
	if (utm.campaign) url.searchParams.set('utm_campaign', utm.campaign);
	if (utm.source)   url.searchParams.set('utm_source',   utm.source);
	if (utm.medium)   url.searchParams.set('utm_medium',   utm.medium);
	if (utm.id)       url.searchParams.set('utm_id',       utm.id);
	if (utm.term)     url.searchParams.set('utm_term',     utm.term);
	if (utm.content)  url.searchParams.set('utm_content',  utm.content);
	return url.toString();
}

/**
 * QR code display row: image on left, download buttons stacked on right
 */
function QRRow(props) {
	const { url, filenameSuffix } = props;
	const svgMarkup = generateQRSvg(url);

	const handleDownloadPng = useCallback(() => {
		downloadFile(generateQRPng(url, 1024), 'qr-code' + filenameSuffix + '.png');
	}, [url]);

	const handleDownloadSvg = useCallback(() => {
		const fullSvg = '<?xml version="1.0" encoding="UTF-8"?>\n' + generateQRSvg(url);
		const blob = new Blob([fullSvg], { type: 'image/svg+xml' });
		const blobUrl = URL.createObjectURL(blob);
		downloadFile(blobUrl, 'qr-code' + filenameSuffix + '.svg');
		URL.revokeObjectURL(blobUrl);
	}, [url]);

	return createElement('div', { style: { display: 'flex', alignItems: 'center', gap: '12px' } },
		createElement('div', {
			dangerouslySetInnerHTML: { __html: svgMarkup },
			style: { flex: '1 1 auto', minWidth: 0 }
		}),
		createElement('div', {
			style: { display: 'flex', flexDirection: 'column', gap: '8px', flexShrink: 0 }
		},
			createElement(Button, { variant: 'secondary', onClick: handleDownloadPng }, 'Download PNG'),
			createElement(Button, { variant: 'secondary', onClick: handleDownloadSvg }, 'Download SVG')
		)
	);
}

/**
 * UTM QR code section
 */
function UtmSection(props) {
	const { permalink } = props;
	const [utm, setUtm] = useState({ campaign: '', source: '', medium: '', id: '', term: '', content: '' });
	const [optExpanded, setOptExpanded] = useState(false);
	const [copied, setCopied] = useState(false);

	const setField = (field) => (value) => setUtm((prev) => ({ ...prev, [field]: value }));

	const isReady = utm.campaign.trim() && utm.source.trim() && utm.medium.trim();
	const utmUrl = isReady ? buildUtmUrl(permalink, utm) : null;

	const handleCopy = useCallback(() => {
		navigator.clipboard.writeText(utmUrl).then(() => {
			setCopied(true);
			setTimeout(() => setCopied(false), 2000);
		});
	}, [utmUrl]);

	const inputStyle = { width: '100%', marginBottom: '8px' };
	const sectionStyle = { borderTop: '1px solid #ddd', marginTop: '12px', paddingTop: '12px' };

	return createElement('div', { style: sectionStyle },
		createElement('p', { style: { margin: '0 0 8px', fontWeight: 600 } }, 'UTM Campaign URL'),

		// Mandatory fields
		createElement('input', { type: 'text', value: utm.campaign, placeholder: 'Campaign Name *', onChange: (e) => setField('campaign')(e.target.value), style: inputStyle, className: 'components-text-control__input' }),
		createElement('input', { type: 'text', value: utm.source, placeholder: 'Campaign Source *', onChange: (e) => setField('source')(e.target.value), style: inputStyle, className: 'components-text-control__input' }),
		createElement('input', { type: 'text', value: utm.medium, placeholder: 'Campaign Medium *', onChange: (e) => setField('medium')(e.target.value), style: inputStyle, className: 'components-text-control__input' }),

		// Optional fields toggle
		createElement('button', {
			type: 'button',
			onClick: () => setOptExpanded((v) => !v),
			style: { background: 'none', border: 'none', padding: 0, cursor: 'pointer', color: '#007cba', fontSize: '12px', marginBottom: '8px' }
		}, (optExpanded ? '▾' : '▸') + ' Optional parameters'),

		optExpanded && createElement('div', {},
			createElement('input', { type: 'text', value: utm.id, placeholder: 'Campaign ID', onChange: (e) => setField('id')(e.target.value), style: inputStyle, className: 'components-text-control__input' }),
			createElement('input', { type: 'text', value: utm.term, placeholder: 'Campaign Term', onChange: (e) => setField('term')(e.target.value), style: inputStyle, className: 'components-text-control__input' }),
			createElement('input', { type: 'text', value: utm.content, placeholder: 'Campaign Content', onChange: (e) => setField('content')(e.target.value), style: inputStyle, className: 'components-text-control__input' })
		),

		// Resulting URL + copy button
		utmUrl && createElement('div', { style: { marginTop: '8px', marginBottom: '12px' } },
			createElement('div', { style: { display: 'flex', gap: '6px', alignItems: 'center' } },
				createElement('input', {
					type: 'text',
					readOnly: true,
					value: utmUrl,
					style: { flex: '1 1 auto', minWidth: 0, fontSize: '11px', border: 'none', background: '#f0f0f0', fontFamily: 'monospace' },
					className: 'components-text-control__input'
				}),
				createElement(Button, { variant: 'secondary', onClick: handleCopy, className: 'is-small', style: { flexShrink: 0 } }, copied ? 'Copied!' : 'Copy')
			)
		),

		// QR code
		utmUrl && createElement(QRRow, { url: utmUrl, filenameSuffix: '-utm' })
	);
}

/**
 * Custom URL QR code section
 */
function CustomUrlSection() {
	const [customUrl, setCustomUrl] = useState('');
	const sectionStyle = { borderTop: '1px solid #ddd', marginTop: '12px', paddingTop: '12px' };

	let isValid = false;
	try {
		if (customUrl.trim()) new URL(customUrl.trim());
		isValid = customUrl.trim().length > 0;
	} catch (e) {
		isValid = false;
	}

	return createElement('div', { style: sectionStyle },
		createElement('p', { style: { margin: '0 0 8px', fontWeight: 600 } }, 'Custom URL'),
		createElement('input', {
			type: 'text',
			value: customUrl,
			placeholder: 'https://example.com',
			onChange: (e) => setCustomUrl(e.target.value),
			style: { width: '100%', marginBottom: '8px' },
			className: 'components-text-control__input'
		}),
		isValid && createElement(QRRow, { url: customUrl.trim(), filenameSuffix: '-custom' })
	);
}

const QRCodePanel = () => {
	const containerRef = useRef(null);
	const [svgMarkup, setSvgMarkup] = useState('');

	const { permalink, postStatus } = useSelect((select) => {
		const editor = select('core/editor');
		return {
			permalink: editor.getPermalink(),
			postStatus: editor.getEditedPostAttribute('status')
		};
	});

	const hasUrl = permalink && postStatus !== 'auto-draft';

	useEffect(() => {
		if (hasUrl) {
			setSvgMarkup(generateQRSvg(permalink));
		} else {
			setSvgMarkup('');
		}
	}, [permalink, hasUrl]);

	const handleDownloadPng = useCallback(() => {
		if (!hasUrl) return;
		const dataUrl = generateQRPng(permalink, 1024);
		downloadFile(dataUrl, 'qr-code.png');
	}, [permalink, hasUrl]);

	const handleDownloadSvg = useCallback(() => {
		if (!hasUrl) return;
		const svgContent = generateQRSvg(permalink);
		// Wrap in proper SVG document
		const fullSvg = '<?xml version="1.0" encoding="UTF-8"?>\n' + svgContent;
		const blob = new Blob([fullSvg], { type: 'image/svg+xml' });
		const url = URL.createObjectURL(blob);
		downloadFile(url, 'qr-code.svg');
		URL.revokeObjectURL(url);
	}, [permalink, hasUrl]);

	const panelContent = hasUrl
		? createElement('div', { className: 'lqx-qr-code-panel' },
			createElement('p', { style: { margin: '0 0 8px', fontWeight: 600 } }, 'Permalink URL'),
			createElement('div', { style: { display: 'flex', alignItems: 'center', gap: '12px' } },
				createElement('div', {
					ref: containerRef,
					className: 'lqx-qr-code-preview',
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
			),
			createElement(UtmSection, { permalink }),
			createElement(CustomUrlSection, {})
		)
		: createElement('p', {},
			'Please save the post as a draft to generate its QR code.'
		);

	return createElement(
		PluginDocumentSettingPanel,
		{ name: 'lqx-qr-code', title: 'QR Code', className: 'lqx-qr-code-panel' },
		panelContent
	);
};

registerPlugin('lqx-qr-code', {
	render: QRCodePanel,
	icon: 'screenoptions'
});

/**
 * block.js - Instagram embed block
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

(function (blocks, element, components, blockEditor) {
	const { registerBlockType } = blocks;
	const { createElement: el, Fragment, useRef, useEffect } = element;
	const { Placeholder, ToolbarGroup, ToolbarButton } = components;
	const { useBlockProps, BlockControls } = blockEditor;

	const instagramIcon = el('svg', {
		xmlns: 'http://www.w3.org/2000/svg',
		viewBox: '0 0 24 24',
		width: 24,
		height: 24
	},
		el('defs', {},
			el('linearGradient', {
				id: 'instagram-gradient',
				x1: '0%',
				y1: '100%',
				x2: '100%',
				y2: '0%'
			},
				el('stop', { offset: '0%', stopColor: '#F58529' }),
				el('stop', { offset: '33%', stopColor: '#DD2A7B' }),
				el('stop', { offset: '66%', stopColor: '#8134AF' }),
				el('stop', { offset: '100%', stopColor: '#515BD4' })
			)
		),
		el('path', {
			fill: 'url(#instagram-gradient)',
			d: 'M7.75 2h8.5A5.75 5.75 0 0 1 22 7.75v8.5A5.75 5.75 0 0 1 16.25 22h-8.5A5.75 5.75 0 0 1 2 16.25v-8.5A5.75 5.75 0 0 1 7.75 2zm0 1.5A4.25 4.25 0 0 0 3.5 7.75v8.5A4.25 4.25 0 0 0 7.75 20.5h8.5A4.25 4.25 0 0 0 20.5 16.25v-8.5A4.25 4.25 0 0 0 16.25 3.5h-8.5zM12 7a5 5 0 1 1 0 10 5 5 0 0 1 0-10zm0 1.5a3.5 3.5 0 1 0 0 7 3.5 3.5 0 0 0 0-7zm5.25-2.5a1 1 0 1 1 0 2 1 1 0 0 1 0-2z'
		})
	);

	function normalizeInstagramUrl(inputUrl) {
		const regex = /^https?:\/\/(?:www\.)?instagram\.com\/(p|reels?|tv)\/([a-zA-Z0-9_-]+)\/?/;
		const match = inputUrl.match(regex);
		if (match && match[1]) {
			const type = match[1] === 'reels' ? 'reel' : match[1];
			return `https://www.instagram.com/${type}/${match[2]}/`;
		}
		return null;
	}

	registerBlockType('lqx/instagram', {
		title: 'Instagram Embed',
		icon: instagramIcon,
		category: 'embed',
		supports: {
			html: false,
			align: true,
			anchor: true,
			mode: true
		},
		attributes: {
			url: { type: 'string', default: '' },
			preview: { type: 'boolean', default: false },
		},
		edit: (props) => {
			const { attributes: { url, preview }, setAttributes } = props;
			const blockProps = useBlockProps();
			const embedRef = useRef(null);
			const normalizedUrl = normalizeInstagramUrl(url);

			useEffect(() => {
				if (preview && normalizedUrl && window.instgrm?.Embeds?.process) {
					window.instgrm.Embeds.process();
				}
			}, [normalizedUrl, preview]);

			const renderPreview = () => el('div', {
				ref: embedRef,
				key: 'preview',
				style: { width: '100%', marginTop: '1em' }
			}, el('blockquote', {
				className: 'instagram-media',
				'data-instgrm-permalink': normalizedUrl,
				'data-instgrm-version': '14',
				style: { marginTop: '1em', marginBottom: '1em' }
			}));

			const renderEdit = () => el(Placeholder, {
				icon: instagramIcon,
				label: 'Instagram Embed',
				instructions: 'Paste a link to an Instagram post or reel.'
			},
				el('div', {
					style: {
						display: 'flex',
						flexDirection: 'row',
						flexWrap: 'wrap',
						gap: '16px',
						justifyContent: 'flex-start',
						width: '100%'
					}
				},
					el('input', {
						type: 'url',
						value: url,
						placeholder: 'https://www.instagram.com/p/...',
						onChange: (e) => setAttributes({ url: e.target.value }),
						style: {
							flex: '1 1 auto',
							padding: '0 12px',
							fontSize: '13px',
							border: '1px solid #ccc',
							borderRadius: '2px'
						}
					}),
					el('button', {
						onClick: () => {
							if (normalizeInstagramUrl(url)) {
								setAttributes({ preview: true });
							}
						},
						style: {
							backgroundColor: '#2271b1',
							color: 'white',
							border: 'none',
							padding: '8px 12px',
							borderRadius: '4px',
							cursor: 'pointer'
						}
					}, 'Embed')
				),
				!normalizedUrl && url.length > 0 &&
				el('p', { style: { color: 'red', marginTop: '1em' } }, 'Invalid Instagram URL.')
			);

			return el(Fragment, {},
				el(BlockControls, {},
					el(ToolbarGroup, {},
						el(ToolbarButton, {
							icon: preview ? 'edit' : 'visibility',
							label: preview ? 'Switch to Edit' : 'Switch to Preview',
							onClick: () => setAttributes({ preview: !preview })
						})
					)
				),
				el('div', blockProps,
					preview && normalizedUrl ? renderPreview() : renderEdit()
				)
			);
		},
		save: () => null,
	});
})(
	window.wp.blocks,
	window.wp.element,
	window.wp.components,
	window.wp.blockEditor
);

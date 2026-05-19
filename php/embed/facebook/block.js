/**
 * block.js - Facebook embed block
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

	const facebookIcon = el('svg', {
		xmlns: 'http://www.w3.org/2000/svg',
		viewBox: '0 0 24 24',
		width: 24,
		height: 24
	},
		el('path', {
			fill: '#1877F2',
			d: 'M24 12.073C24 5.405 18.627 0 12 0S0 5.405 0 12.073c0 6.026 4.388 11.022 10.125 11.927v-8.437H7.078v-3.49h3.047V9.43c0-3.025 1.792-4.697 4.533-4.697 1.313 0 2.686.236 2.686.236v2.97h-1.513c-1.49 0-1.956.93-1.956 1.886v2.264h3.328l-.532 3.49h-2.796v8.437C19.612 23.095 24 18.1 24 12.073z'
		})
	);

	function normalizeFacebookUrl(inputUrl) {
		try {
			const url = new URL(inputUrl);
			if (!/^(www\.|m\.|web\.|mbasic\.)?facebook\.com$/.test(url.hostname)) {
				return null;
			}

			// Must have a meaningful path (more than just "/")
			if (url.pathname.length <= 1) {
				return null;
			}

			// Determine embed type
			var embedType = 'post';
			if (/\/(reel|reels|videos)\//i.test(url.pathname) ||
				/\/watch\b/i.test(url.pathname) ||
				url.pathname === '/video.php') {
				embedType = 'video';
			}

			// Query-based URLs: preserve required query params
			var queryPages = ['permalink.php', 'photo.php', 'video.php', 'watch'];
			var isQueryBased = queryPages.some(function (page) {
				return url.pathname.includes(page);
			});

			if (!isQueryBased) {
				url.search = '';
			}

			url.hash = '';

			return { url: url.toString(), embedType: embedType };
		} catch (e) {
			return null;
		}
	}

	registerBlockType('lqx/facebook', {
		title: 'Facebook Embed',
		icon: facebookIcon,
		category: 'embed',
		supports: {
			html: false,
			align: true,
			anchor: true,
			mode: true
		},
		attributes: {
			url: { type: 'string', default: '' },
			type: { type: 'string', default: 'post' },
			preview: { type: 'boolean', default: false },
		},
		edit: (props) => {
			const { attributes: { url, preview }, setAttributes } = props;
			const blockProps = useBlockProps();
			const embedRef = useRef(null);
			const result = normalizeFacebookUrl(url);
			const normalizedUrl = result ? result.url : null;

			useEffect(() => {
				if (preview && normalizedUrl && window.FB?.XFBML?.parse) {
					window.FB.XFBML.parse(embedRef.current);
				}
			}, [normalizedUrl, preview]);

			const renderPreview = () => {
				const embedClass = result && result.embedType === 'video' ? 'fb-video' : 'fb-post';
				return el('div', {
					ref: embedRef,
					key: 'preview',
					style: { width: '100%', marginTop: '1em' }
				}, el('div', {
					className: embedClass,
					'data-href': normalizedUrl,
					'data-width': '500'
				}));
			};

			const renderEdit = () => el(Placeholder, {
				icon: facebookIcon,
				label: 'Facebook Embed',
				instructions: 'Paste a link to a Facebook post, reel, or video.'
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
						placeholder: 'https://www.facebook.com/username/posts/1234567890',
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
							var r = normalizeFacebookUrl(url);
							if (r) {
								setAttributes({ preview: true, type: r.embedType });
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
				el('p', { style: { color: 'red', marginTop: '1em' } }, 'Invalid Facebook URL.')
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

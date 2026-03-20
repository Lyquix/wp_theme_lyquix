/**
 * redirects-sidebar.js - Redirects manager Gutenberg sidebar panel
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
const { createElement, useState, useEffect, useCallback } = wp.element;
const apiFetch = wp.apiFetch;

/**
 * Single redirect hop row: status dot + source URL + code badge + hits
 */
function RedirectStep({ item }) {
	const isEnabled = item.status === 'enabled';

	return createElement('div', {
		style: {
			display: 'flex',
			alignItems: 'center',
			gap: '6px',
			marginBottom: '3px',
			fontSize: '11px',
			lineHeight: '1.5'
		}
	},
		// Status dot
		createElement('span', {
			title: isEnabled ? 'Enabled' : 'Disabled',
			style: {
				width: '7px', height: '7px',
				borderRadius: '50%',
				background: isEnabled ? '#46b450' : '#bbb',
				flexShrink: 0
			}
		}),
		// Source URL
		createElement('a', {
			href: item.url,
			target: '_blank',
			rel: 'noopener noreferrer',
			title: item.url,
			style: {
				flex: 1, minWidth: 0,
				fontFamily: 'monospace',
				overflow: 'hidden',
				textOverflow: 'ellipsis',
				whiteSpace: 'nowrap',
				color: '#007cba',
				textDecoration: 'none'
			}
		}, item.url),
		// HTTP code badge
		createElement('span', {
			style: {
				background: '#007cba',
				color: '#fff',
				borderRadius: '3px',
				padding: '1px 5px',
				fontSize: '10px',
				flexShrink: 0
			}
		}, item.action_code),
		// Hit count
		item.hits > 0 && createElement('span', {
			style: { color: '#888', fontSize: '10px', flexShrink: 0, whiteSpace: 'nowrap' }
		}, item.hits.toLocaleString() + ' hits')
	);
}

/**
 * A full redirect chain ending at the current post
 */
function RedirectChain({ chain, postPath, isLast }) {
	return createElement('div', {
		style: {
			borderBottom: isLast ? 'none' : '1px solid #eee',
			paddingBottom: isLast ? 0 : '10px',
			marginBottom: isLast ? 0 : '10px'
		}
	},
		// One row per hop
		...chain.map((item) => createElement(RedirectStep, { key: item.id, item })),
		// Terminal row: "↳ /post-path  [This Post]"
		createElement('div', {
			style: {
				display: 'flex',
				alignItems: 'center',
				gap: '6px',
				fontSize: '11px',
				color: '#666',
				paddingLeft: '13px'
			}
		},
			createElement('span', {}, '↳'),
			createElement('span', {
				style: { fontFamily: 'monospace', flex: 1, minWidth: 0, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }
			}, postPath),
			createElement('span', {
				style: {
					background: '#46b450',
					color: '#fff',
					borderRadius: '3px',
					padding: '1px 5px',
					fontSize: '10px',
					flexShrink: 0
				}
			}, 'This Post')
		)
	);
}

const RedirectsPanel = () => {
	const [loading, setLoading] = useState(false);
	const [error, setError] = useState('');
	const [data, setData] = useState(null);
	const [addUrl, setAddUrl] = useState('');
	const [adding, setAdding] = useState(false);
	const [addError, setAddError] = useState('');

	const postId = useSelect((select) => select('core/editor').getCurrentPostId());

	const fetchData = useCallback(() => {
		if (!postId) return;
		setLoading(true);
		setError('');
		apiFetch({ path: '/lqx/v1/redirects?post_id=' + postId })
			.then((res) => { setData(res); setLoading(false); })
			.catch((err) => { setError(err.message || 'Failed to load redirects.'); setLoading(false); });
	}, [postId]);

	const handleAdd = useCallback(() => {
		if (!addUrl.trim()) return;
		setAdding(true);
		setAddError('');
		apiFetch({
			path: '/lqx/v1/redirects',
			method: 'POST',
			data: { post_id: postId, source_url: addUrl.trim() }
		})
			.then((res) => { setData(res); setAddUrl(''); setAdding(false); })
			.catch((err) => { setAddError(err.message || 'Failed to create redirect.'); setAdding(false); });
	}, [postId, addUrl]);

	const handleAddKeyDown = useCallback((e) => {
		if (e.key === 'Enter') handleAdd();
	}, [handleAdd]);

	useEffect(() => { fetchData(); }, [fetchData]);

	let content;

	if (loading) {
		content = createElement('p', { style: { color: '#666' } }, 'Loading…');
	} else if (error) {
		content = createElement('p', { style: { color: '#cc1818', fontSize: '12px' } }, error);
	} else if (!data) {
		content = null;
	} else if (data.error) {
		content = createElement('p', { style: { color: '#cc1818', fontSize: '12px' } }, data.error);
	} else if (!data.chains || data.chains.length === 0) {
		content = createElement('p', { style: { color: '#666', fontStyle: 'italic', fontSize: '12px' } },
			'No redirects found pointing to this post.');
	} else {
		const direct  = data.chains.filter((c) => c.length === 1);
		const chained = data.chains.filter((c) => c.length > 1);
		const headingStyle = (topMargin) => ({
			margin: (topMargin ? '12px' : '0') + ' 0 8px',
			fontWeight: 600,
			fontSize: '12px'
		});

		const sections = [];

		if (direct.length > 0) {
			sections.push(
				createElement('p', { key: 'dh', style: headingStyle(false) },
					'Redirects (' + direct.length + ')')
			);
			direct.forEach((chain, i) => sections.push(
				createElement(RedirectChain, {
					key: 'd' + i, chain, postPath: data.post_path, isLast: i === direct.length - 1
				})
			));
		}

		if (chained.length > 0) {
			sections.push(
				createElement('p', { key: 'ch', style: headingStyle(direct.length > 0) },
					'Redirect Chains (' + chained.length + ')')
			);
			chained.forEach((chain, i) => sections.push(
				createElement(RedirectChain, {
					key: 'c' + i, chain, postPath: data.post_path, isLast: i === chained.length - 1
				})
			));
		}

		content = createElement('div', {}, ...sections);
	}

	const addForm = createElement('div', { style: { borderTop: '1px solid #ddd', marginTop: '12px', paddingTop: '12px' } },
		createElement('p', { style: { margin: '0 0 6px', fontWeight: 600, fontSize: '12px' } }, 'Add Redirect'),
		createElement('div', { style: { display: 'flex', gap: '6px', alignItems: 'center' } },
			createElement('input', {
				type: 'text',
				value: addUrl,
				placeholder: '/old-url',
				onChange: (e) => setAddUrl(e.target.value),
				onKeyDown: handleAddKeyDown,
				disabled: adding,
				style: { flex: 1, minWidth: 0, fontFamily: 'monospace', fontSize: '11px' },
				className: 'components-text-control__input'
			}),
			createElement(Button, {
				variant: 'primary',
				onClick: handleAdd,
				isBusy: adding,
				disabled: adding || !addUrl.trim(),
				className: 'is-small'
			}, 'Add')
		),
		addError && createElement('p', { style: { color: '#cc1818', fontSize: '11px', margin: '4px 0 0' } }, addError)
	);

	return createElement(
		PluginDocumentSettingPanel,
		{ name: 'lqx-redirects', title: 'Redirects', className: 'lqx-redirects-panel' },
		content,
		addForm,
		createElement(Button, {
			variant: 'tertiary',
			onClick: fetchData,
			isBusy: loading,
			disabled: loading,
			style: { marginTop: '8px', fontSize: '11px' }
		}, 'Refresh')
	);
};

registerPlugin('lqx-redirects', {
	render: RedirectsPanel,
	icon: 'randomize'
});

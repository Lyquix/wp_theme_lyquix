/**
 * featured-posts.js - Add featured posts and filtering by them
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

const { PluginPostStatusInfo } = wp.editPost;
const { ToggleControl } = wp.components;
const { useSelect, useDispatch } = wp.data;
const { registerPlugin } = wp.plugins;
const { createElement } = wp.element;

const FeaturedPostToggle = () => {
	const metaKey = '_is_featured';

	const isFeatured = useSelect((select) =>
		!!select('core/editor').getEditedPostAttribute('meta')[metaKey]
	);

	const { editPost } = useDispatch('core/editor');

	return createElement(
		PluginPostStatusInfo,
		{ className: 'featured-post-toggle' },
		createElement(ToggleControl, {
			label: 'Featured',
			checked: isFeatured,
			onChange: (value) => editPost({ meta: { [metaKey]: value } }),
		})
	);
};

registerPlugin('featured-post-toggle', {
	render: FeaturedPostToggle,
});

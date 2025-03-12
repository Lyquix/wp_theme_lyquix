const { PluginDocumentSettingPanel } = wp.editPost;
const { TextControl } = wp.components;
const { useSelect, useDispatch } = wp.data;
const { registerPlugin } = wp.plugins;
const { createElement } = wp.element;

const CustomMetaField = () => {
	const metaKey = '_is_featured';

	const meta = useSelect((select) =>
		select('core/editor').getEditedPostAttribute('meta')[metaKey]
	);

	const { editPost } = useDispatch('core/editor');

	return createElement(
		PluginDocumentSettingPanel,
		{ name: 'featured', title: 'Custom Field', className: 'custom-meta-field' },
		createElement(TextControl, {
			label: 'Featured',
			value: meta || '',
			onChange: (value) => editPost({ meta: { [metaKey]: value } }),
		})
	);
};

registerPlugin('custom-meta-field', {
	render: CustomMetaField,
});

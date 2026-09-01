/**
 * editor.js - Tailwind CSS editor script
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

// This script is used to add Tailwind CSS classes to ACF fields in the editor
(function (wp, acf, $) {
	const { subscribe, select } = wp.data;
	var previousBlockId = null;
	var excludedTypes = ['tab', 'group', 'repeater', 'accordion'];
	var classPrefix = 'tailwind_';
	var localKey = 'tailwindClasses';
	localStorage.removeItem(localKey);

	$(document).ready(function () {
		setTimeout(function () {
			const blocks = select('core/block-editor').getBlocks();
			if (blocks.length) initBlocks(blocks);
		}, 300);
	});

	// Subscribe to block changes
	subscribe(function () {
		var selectedBlock = select('core/block-editor').getSelectedBlock();
		var selectedBlockId = selectedBlock ? selectedBlock.clientId : null;

		// Check if the selected block has changed
		if (selectedBlockId !== previousBlockId) {
			previousBlockId = selectedBlockId;

			if (selectedBlock && selectedBlock.name.includes('lqx')) {
				handleBlockChange(selectedBlockId);
			}
		}
	});

	// Initialize Tailwind classes for existing blocks
	function initBlocks(blocks) {
		blocks.forEach((block) => {
			if (block.name.includes('lqx')) {
				Object.entries(block.attributes.data).forEach(([key, value]) => {
					if (typeof key === 'string' &&  key.indexOf(classPrefix) === 0 && value) {
						value = key + value;
					}
					if (typeof value === 'string' && value.includes(classPrefix)) {
						addClassName(block.clientId, value.replace(classPrefix, ''));
					}
				});
			}
		});
	}

	// Handle block changes
	function handleBlockChange(blockId) {
		var tailwindClasses = getTailwindClasses();

		setTimeout(function () {
			var fields = acf.getFields({ parent: $('.interface-interface-skeleton__sidebar') });
			if (fields) {
				fields.forEach(function (field) {
					if (!excludedTypes.includes(field.data.type)) {
						var val = field.val();
						var name = field.data.name;
						// Initialize tailwindClasses for the selectedBlockId if it doesn't exist
						tailwindClasses[blockId] = tailwindClasses[blockId] || {};

						if (name && name.includes(classPrefix) && val) {
							val = name + val;
						}
						if (val && val.includes(classPrefix)) {
							tailwindClasses[blockId][field.cid] = val.replace(classPrefix, '');
						}

						onChangeField(field, tailwindClasses);
					}
				});
				saveTailwindClasses(tailwindClasses);
			}
		}, 300);
	}

	// Handle field changes
	function onChangeField(field, tailwindClasses) {
		field.off('change');
		field.on('change keyup', function () {
			var selectedBlock = select('core/block-editor').getSelectedBlock();
			var blockId = selectedBlock.clientId;
			var val = field.val();
			var key = field.cid;
			var name = field.data.name;
			if (name && name.includes(classPrefix) && val) {
				val = name + val;
			}

			// Initialize tailwindClasses for the selectedBlockId if it doesn't exist
			if (val && val.includes(classPrefix)) {
				val = val.replace(classPrefix, '');
				if (tailwindClasses[blockId][key] && val !== tailwindClasses[blockId][key]) {
					replaceClassName(blockId, tailwindClasses[blockId][key], val);
				} else {
					addClassName(blockId, val);
				}
				tailwindClasses[blockId][key] = val;
			} else {
				if (tailwindClasses[blockId][key] && val !== tailwindClasses[blockId][key]) {
					replaceClassName(blockId, tailwindClasses[blockId][key], '');
				}
				delete tailwindClasses[blockId][key];
			}

			saveTailwindClasses(tailwindClasses);
		});
	}

	// Local storage functions
	function getTailwindClasses() {
		return JSON.parse(localStorage.getItem(localKey)) || {};
	}

	// Save Tailwind classes to local storage
	function saveTailwindClasses(tailwindClasses) {
		localStorage.setItem(localKey, JSON.stringify(tailwindClasses));
	}

	// Replace class name in block
	function replaceClassName(blockId, oldValue, newValue) {
		var regex = new RegExp('(\\b' + oldValue + '\\b)(?![\\w-])', 'g');
		var block = $('[data-block=' + blockId + ']');
		block.attr('class', block.attr('class').replace(regex, newValue).replace(/\s+/g, ' ').trim());
	}

	// Add class name to block element
	function addClassName(blockId, newValue) {
		var block = $('[data-block=' + blockId + ']');
		block.attr('class', (block.attr('class') + ' ' + newValue).replace(/\s+/g, ' ').trim());
	}

	// Remove class name from block element
	$(window).unload(function () {
		localStorage.removeItem(localKey);
	});
})(wp, acf, jQuery);

// Fix: first lqx block inserted opens in preview mode in WP 7.0.
//
// Root cause (from ACF's minified source):
//   WP 7.0 creates an iframe[name="editor-canvas"] during editor init. ACF's block
//   class component setup() detects it via I() and directly mutates the Gutenberg
//   store's attributes object (same JS reference — no setAttributes call) to
//   mode:'preview'. The iframe is removed before useEffect fires, so detecting it
//   here is always too late. Instead: check the store directly after mount. If mode
//   is 'preview' for an edit-only block (supports.mode:false), it was set incorrectly.
//   Dispatching mode:'edit' is a real change ('preview'→'edit'), creates a fresh
//   attributes object, and triggers a re-render where ACF reads mode:'edit' from props
//   (not the mutated object) and renders correctly in edit mode.
(function (wp) {
	if (!wp.hooks || !wp.compose || !wp.element || !wp.blocks || !wp.data) return;

	wp.hooks.addFilter(
		'editor.BlockEdit',
		'lyquix/force-edit-mode',
		wp.compose.createHigherOrderComponent(function (BlockEdit) {
			return function (props) {
			var editModeBlocks = ['lqx/accordion', 'lqx/banner', 'lqx/cards', 'lqx/filters', 'lqx/gallery', 'lqx/hero', 'lqx/logos', 'lqx/map', 'lqx/related-items', 'lqx/slider', 'lqx/tabs'];

				wp.element.useEffect(function () {
					if (editModeBlocks.indexOf(props.name) === -1) return;

					var block = wp.data.select('core/block-editor').getBlock(props.clientId);
					if (!block || block.attributes.mode !== 'preview') return;

					wp.data.dispatch('core/block-editor').updateBlockAttributes(props.clientId, { mode: 'edit' });
				}, []);

				return wp.element.createElement(BlockEdit, props);
			};
		}, 'withForceEditMode')
	);
})(wp);

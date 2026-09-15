/**
 * canvas.js - Block editor canvas helpers
 *
 * Loaded into the editor canvas iframe only (see the enqueue_block_assets hook in
 * php/blocks.php), so block previews look and behave as they do on the page.
 *
 * @version     3.5.0
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

(function () {
	// ACF renders a block's inner blocks inside a display: contents container, and wraps each
	// block preview in an element of its own
	var CONTAINER = '.acf-innerblocks-container';
	var WRAPPER = '.acf-block-preview';

	// Properties that place an element inside a grid or flex parent
	var PLACEMENT = ['grid-column-start', 'grid-column-end', 'grid-row-start', 'grid-row-end', 'justify-self', 'align-self', 'order', 'flex-grow', 'flex-shrink', 'flex-basis'];

	// Previews are live markup, so a click on a link or a form submit would navigate the canvas
	// iframe away from the editor, which then breaks on the cross-origin frame. Core only
	// intercepts #hash links. Capture phase and preventDefault only: block selection and theme
	// handlers (lyqbox, tabs) still receive the event.
	document.addEventListener('click', function (event) {
		var link = event.target.closest ? event.target.closest('a[href]') : null;
		if (!link || link.isContentEditable || link.getAttribute('href').charAt(0) === '#') return;
		event.preventDefault();
	}, true);

	document.addEventListener('submit', function (event) {
		event.preventDefault();
	}, true);

	// Split a selector on a character, ignoring it inside (), [] and quotes
	var splitTopLevel = function (text, separator) {
		var parts = [];
		var depth = 0;
		var quote = null;
		var start = 0;

		for (var i = 0; i < text.length; i++) {
			var c = text.charAt(i);
			if (quote) {
				if (c === '\\') i++;
				else if (c === quote) quote = null;
			} else if (c === '"' || c === "'") {
				quote = c;
			} else if (c === '(' || c === '[') {
				depth++;
			} else if (c === ')' || c === ']') {
				depth--;
			} else if (c === separator && depth === 0) {
				parts.push(text.slice(start, i).trim());
				start = i + 1;
			}
		}

		parts.push(text.slice(start).trim());
		return parts;
	};

	// Project styles are written against the page markup, where a block's inner blocks are
	// direct children of its root element. For every child combinator, also match through the
	// inner blocks container, and through a block preview wrapper. :where() keeps specificity,
	// and the copy goes right after the original, so the cascade is the same as on the page.
	var variants = function (selectorText) {
		var out = [];

		splitTopLevel(selectorText, ',').forEach(function (selector) {
			var parts = splitTopLevel(selector, '>');

			for (var i = 1; i < parts.length; i++) {
				if (!parts[i - 1] || !parts[i]) continue;
				var head = parts.slice(0, i).join(' > ');
				var tail = parts.slice(i).join(' > ');
				out.push(head + ' > :where(' + CONTAINER + ') > ' + tail);
				out.push(head + ' > :where(' + CONTAINER + ') > :where(' + WRAPPER + ') > ' + tail);
				out.push(head + ' > :where(' + WRAPPER + ') > ' + tail);
			}
		});

		return out;
	};

	// Child-position pseudo-classes (:first-child, :nth-child()...) count siblings, and in the
	// editor a block preview's root is the only child of its wrapper. Move those conditions onto
	// the wrapper: "A B:not(:first-child)" also becomes
	// "A :where(wrapper):not(:first-child) > B". -of-type is left alone, wrappers are all divs.
	var POSITIONAL = /^:(first-child|last-child|only-child|nth-child\(|nth-last-child\()|^:not\(\s*:(first-child|last-child|only-child|nth-child\(|nth-last-child\()/;

	// Split a compound selector into its simple parts: "li.card:not(:first-child)" into
	// ["li", ".card", ":not(:first-child)"]
	var simpleParts = function (compound) {
		var parts = [];
		var depth = 0;
		var start = 0;

		for (var i = 0; i < compound.length; i++) {
			var c = compound.charAt(i);
			if (c === '(' || c === '[') depth++;
			else if (c === ')' || c === ']') depth--;
			else if (depth === 0 && i > start && (c === '.' || c === '#' || c === '[' || (c === ':' && compound.charAt(i - 1) !== ':'))) {
				parts.push(compound.slice(start, i));
				start = i;
			}
		}

		parts.push(compound.slice(start));
		return parts;
	};

	var positionalVariants = function (selectorText) {
		var out = [];

		splitTopLevel(selectorText, ',').forEach(function (selector) {
			// Compounds separated by descendant, child and sibling combinators
			var tokens = selector.match(/(?:[^\s>+~()[\]]+|\([^)]*\)|\[[^\]]*\])+|\s*[>+~]\s*|\s+/g) || [];

			tokens.forEach(function (token, index) {
				if (/^\s*[>+~]?\s*$/.test(token)) return;
				var parts = simpleParts(token);
				var moved = parts.filter(function (part) { return POSITIONAL.test(part); });
				if (!moved.length) return;
				var kept = parts.filter(function (part) { return !POSITIONAL.test(part); }).join('') || '*';
				var copy = tokens.slice();
				copy[index] = ':where(' + WRAPPER + ')' + moved.join('') + ' > ' + kept;
				out.push(copy.join(''));
			});
		});

		return out;
	};

	// Sibling combinators relate block roots that, in the editor, are each inside their own
	// wrapper, so the siblings are the wrappers. For "L + R" (and ~) also match when either side
	// is a block preview: ":where(wrapper):has(> L) + :where(wrapper) > R" and the one-sided
	// forms. :has() counts only its argument, so specificity is unchanged.
	var siblingVariants = function (selectorText) {
		var out = [];

		splitTopLevel(selectorText, ',').forEach(function (selector) {
			var tokens = selector.match(/(?:[^\s>+~()[\]]+|\([^)]*\)|\[[^\]]*\])+|\s*[>+~]\s*|\s+/g) || [];

			tokens.forEach(function (token, index) {
				var combinator = token.trim();
				if (combinator !== '+' && combinator !== '~') return;
				var left = tokens[index - 1];
				var right = tokens[index + 1];
				if (!left || !right || /^\s*[>+~]?\s*$/.test(left) || /^\s*[>+~]?\s*$/.test(right)) return;
				// Only when the left compound starts the selector or follows a descendant combinator
				var before = tokens[index - 2];
				if (before !== undefined && before.trim() !== '') return;

				var head = tokens.slice(0, index - 1).join('');
				var tail = tokens.slice(index + 2).join('');
				var wrappedLeft = ':where(' + WRAPPER + '):has(> ' + left + ')';
				var wrappedRight = ':where(' + WRAPPER + ') > ' + right;
				out.push(head + wrappedLeft + ' ' + combinator + ' ' + wrappedRight + tail);
				out.push(head + left + ' ' + combinator + ' ' + wrappedRight + tail);
				out.push(head + wrappedLeft + ' ' + combinator + ' ' + right + tail);
			});
		});

		return out;
	};

	// The same for relational pseudo-classes: "A:has(+ B)" looks for a sibling of A, which in the
	// editor is a sibling of A's wrapper. Becomes ":where(wrapper):has(+ :where(wrapper) > B, + B)
	// > A" (B as a block preview or as a core block).
	var hasSiblingVariants = function (selectorText) {
		var out = [];

		splitTopLevel(selectorText, ',').forEach(function (selector) {
			// Nested rules (&) are copied with their parent rule
			if (selector.indexOf('&') !== -1) return;
			var tokens = selector.match(/(?:[^\s>+~()[\]]+|\([^)]*(?:\([^)]*\)[^)]*)*\)|\[[^\]]*\])+|\s*[>+~]\s*|\s+/g) || [];

			tokens.forEach(function (token, index) {
				if (/^\s*[>+~]?\s*$/.test(token) || token.indexOf(':has(') === -1) return;
				// Only when the compound starts the selector or follows a descendant combinator
				var before = tokens[index - 1];
				if (before !== undefined && before.trim() !== '') return;

				var match = token.match(/:has\(\s*([+~][^)]*(?:\([^)]*\)[^)]*)*)\)/);
				if (!match) return;
				var compound = token.replace(match[0], '') || '*';
				// Each relative selector carries its own combinator: ":has(~ .b, ~ .c)"
				var relative = splitTopLevel(match[1], ',').map(function (rel) {
					var parts = rel.match(/^([+~])\s*([\s\S]+)$/);
					if (!parts) return null;
					return parts[1] + ' :where(' + WRAPPER + ') > ' + parts[2] + ', ' + parts[1] + ' ' + parts[2];
				});
				if (relative.indexOf(null) !== -1) return;
				relative = relative.join(', ');

				var copy = tokens.slice();
				copy[index] = ':where(' + WRAPPER + '):has(' + relative + ') > ' + compound;
				out.push(copy.join(''));
			});
		});

		return out;
	};

	var addVariants = function (list) {
		for (var i = list.cssRules.length - 1; i >= 0; i--) {
			var rule = list.cssRules[i];

			if (rule instanceof CSSStyleRule) {
				var selectors = [];
				if (rule.selectorText.indexOf('>') !== -1) selectors = selectors.concat(variants(rule.selectorText));
				if (rule.selectorText.indexOf('-child') !== -1) selectors = selectors.concat(positionalVariants(rule.selectorText));
				if (rule.selectorText.indexOf('.content') !== -1) selectors = selectors.concat(pageVariants(rule.selectorText));
				if (/[+~]/.test(rule.selectorText)) selectors = selectors.concat(siblingVariants(rule.selectorText));
				if (/:has\(\s*[+~]/.test(rule.selectorText)) selectors = selectors.concat(hasSiblingVariants(rule.selectorText));
				if (!selectors.length) continue;
				try {
					// cssText carries the declarations and any nested rules
					list.insertRule(selectors.join(', ') + ' ' + rule.cssText.slice(rule.cssText.indexOf('{')), i + 1);
				} catch (e) {
					// A selector the browser rejects: leave the original alone
				}
			} else if (rule.cssRules) {
				// @media, @supports, @layer and friends
				addVariants(rule);
			}
		}
	};

	var processSheet = function (link) {
		if (link.dataset.lqxVariants) return;

		try {
			if (!link.sheet || !link.sheet.cssRules) return;
		} catch (e) {
			// Cross-origin stylesheet, rules not readable
			link.dataset.lqxVariants = 'skipped';
			return;
		}

		link.dataset.lqxVariants = 'done';
		addVariants(link.sheet);
		schedule();
	};

	// Classic themes get editor styles that pad the canvas and centre every block wrapper with auto
	// inline margins. The padding makes the content narrower than the page, and in a grid (the
	// page wrapper, grid boxes) auto margins shrink an item to its content. Drop those rules so
	// the theme's own spacing applies.
	var dropEditorCentering = function () {
		Array.prototype.forEach.call(document.styleSheets, function (sheet) {
			if (!/\/edit-post\/classic/.test(sheet.href || '')) return;
			try {
				for (var i = sheet.cssRules.length - 1; i >= 0; i--) {
					var rule = sheet.cssRules[i];
					if (!(rule instanceof CSSStyleRule)) continue;
					// Auto inline margins, and the 28px block margins and 840px width (unlayered, so they
					// also beat a Tailwind 4 preflight reset that sits in a layer)
					var centering = /\.wp-block/.test(rule.selectorText) && (rule.style.marginLeft === 'auto' || rule.style.marginRight === 'auto' || rule.style.marginTop || rule.style.marginBottom || rule.style.maxWidth);
					var padding = /^\s*\.editor-styles-wrapper\s*$/.test(rule.selectorText) && rule.style.padding;
					if (centering || padding) sheet.deleteRule(i);
				}
			} catch (e) {
				// Rules not readable
			}
		});
	};

	// wp-admin's own stylesheets (common, forms) reach the canvas and style content the page never
	// sees them on: .card gets a 520px max-width, list items and paragraphs get admin spacing and
	// font sizes. The editor's canvas styles are in wp-includes, so these can go.
	var disableAdminStyles = function () {
		document.querySelectorAll('link[rel="stylesheet"]').forEach(function (link) {
			if (/\/wp-admin\/css\//.test(link.href)) link.disabled = true;
		});
	};

	// The child theme's editor styles are a separate Tailwind build (base, utilities, editor
	// helpers) written for an editor without the site stylesheet. In the canvas they load after
	// it and put generic utility values back over the project's (.container widths, for one).
	// The canvas has the site stylesheet now, and the helpers it still needs are in
	// markLayoutBlocks, so turn them off.
	var disableChildEditorStyles = function () {
		Array.prototype.forEach.call(document.styleSheets, function (sheet) {
			if (sheet.href || !sheet.ownerNode || sheet.ownerNode.id === 'lqx-canvas-layout-blocks') return;
			try {
				var isEditorStyles = Array.prototype.some.call(sheet.cssRules, function (rule) {
					return rule instanceof CSSStyleRule && /\.acf-innerblocks-container\.block-editor-block-list__layout/.test(rule.selectorText);
				});
				if (isEditorStyles) sheet.disabled = true;
			} catch (e) {
				// Rules not readable
			}
		});
	};

	var processSheets = function () {
		disableAdminStyles();
		disableChildEditorStyles();
		dropEditorCentering();

		document.querySelectorAll('link[rel="stylesheet"][id^="lqx-canvas-"]').forEach(function (link) {
			if (link.sheet) processSheet(link);
			else link.addEventListener('load', function () { processSheet(link); }, { once: true });
		});
	};

	// Editor classes on a block wrapper, which stay
	var EDITOR_CLASS = /^(block-editor-|wp-block|acf-|is-|has-|align|rich-text|editor-|wp-elements-)/;

	// Inline sizing set on flex items by sizeFlexItems, cleared before each measurement
	var FLEX_WRAPPER_SIZING = ['display', 'flex', 'width', 'max-width', 'min-width', 'margin-top', 'margin-right', 'margin-bottom', 'margin-left'];
	var FLEX_ROOT_SIZING = ['width', 'max-width', 'min-width', 'margin'];

	// In a flex layout the item's size comes from its own width, basis and margins, often in
	// percentages of the layout. Inside a wrapper those resolve against the wrapper instead. Show
	// the roots as the layout's items for one measurement (wrappers as display: contents), then
	// give each wrapper that size and let the root fill it.
	var sizeFlexItems = function (wrappers) {
		wrappers.forEach(function (wrapper) {
			FLEX_WRAPPER_SIZING.forEach(function (property) { wrapper.style.removeProperty(property); });
			FLEX_ROOT_SIZING.forEach(function (property) { wrapper.firstElementChild.style.removeProperty(property); });
			wrapper.style.setProperty('display', 'contents');
		});

		var sizes = wrappers.map(function (wrapper) {
			var root = wrapper.firstElementChild;
			var style = getComputedStyle(root);
			return {
				width: root.getBoundingClientRect().width,
				margins: [style.marginTop, style.marginRight, style.marginBottom, style.marginLeft]
			};
		});

		wrappers.forEach(function (wrapper, i) {
			var root = wrapper.firstElementChild;
			wrapper.style.removeProperty('display');
			wrapper.style.setProperty('flex', '0 0 auto');
			wrapper.style.setProperty('width', sizes[i].width + 'px');
			wrapper.style.setProperty('max-width', 'none');
			wrapper.style.setProperty('min-width', '0');
			['margin-top', 'margin-right', 'margin-bottom', 'margin-left'].forEach(function (property, m) {
				wrapper.style.setProperty(property, sizes[i].margins[m]);
			});
			root.style.setProperty('width', '100%');
			root.style.setProperty('max-width', 'none');
			root.style.setProperty('min-width', '0');
			root.style.setProperty('margin', '0');
		});
	};

	var syncWrappers = function () {
		var flexGroups = [];

		document.querySelectorAll(WRAPPER).forEach(function (wrapper) {
			var root = wrapper.firstElementChild;
			if (!root) return;

			// ACF puts the block's Additional CSS classes on the wrapper as well as on the rendered
			// root, so project styles for those classes apply twice (a grid wrapping the grid)
			Array.prototype.slice.call(wrapper.classList).forEach(function (name) {
				if (!EDITOR_CLASS.test(name) && root.classList.contains(name)) wrapper.classList.remove(name);
			});

			// The wrapper is what sits in the parent's grid or flex layout
			var layout = wrapper.parentElement;
			while (layout && getComputedStyle(layout).display === 'contents') layout = layout.parentElement;
			if (!layout) return;
			var display = getComputedStyle(layout).display;

			if (/flex/.test(display)) {
				var group = flexGroups.filter(function (entry) { return entry.layout === layout; })[0];
				if (!group) flexGroups.push(group = { layout: layout, wrappers: [] });
				group.wrappers.push(wrapper);
				return;
			}

			if (!/grid/.test(display)) return;

			// Give the wrapper the grid placement its rendered root computes
			var style = getComputedStyle(root);
			PLACEMENT.forEach(function (property) {
				var value = style.getPropertyValue(property);
				if (wrapper.style.getPropertyValue(property) !== value) wrapper.style.setProperty(property, value);
			});
		});

		flexGroups.forEach(function (group) { sizeFlexItems(group.wrappers); });
	};

	// On the page, blocks sit inside <div class="content grid-container"> (singular.php): project
	// styles are often scoped to .content, and .grid-container puts every block in the container
	// column unless it is .full-width. Give the editor post content container the same classes,
	// and put them back whenever the editor re-renders the container.
	var PAGE_CLASSES = (window.lqxCanvasPage && window.lqxCanvasPage.contentClasses) || ['content', 'grid-container'];

	// The display project styles give the page wrapper (grid on most sites). The editor's own
	// layout rules for its container are more specific, so it is applied inline.
	var pageDisplay = null;
	var pageBackground = '';

	// Body classes and feature flags of the page (php/blocks.php computes them for the edited post)
	var page = window.lqxCanvasPage || {};
	var bodyClasses = (page.bodyClass || '').split(/\s+/).filter(Boolean);

	var tagContent = function () {
		bodyClasses.forEach(function (name) {
			if (!document.body.classList.contains(name)) document.body.classList.add(name);
		});
		if (page.features && document.body.getAttribute('data-features') !== page.features) document.body.setAttribute('data-features', page.features);

		var root = document.querySelector('.wp-block-post-content');
		if (!root) return;
		PAGE_CLASSES.forEach(function (name) {
			if (!root.classList.contains(name)) root.classList.add(name);
		});

		if (pageDisplay === null) {
			// Build the template's wrappers (main#content > article > .content) out of sight, so
			// styles written against that structure report the wrapper's display and background
			var main = document.createElement('main');
			main.id = 'content';
			main.style.cssText = 'position: absolute; visibility: hidden; width: 0; height: 0; overflow: hidden;';
			var article = document.createElement('article');
			var probe = document.createElement('div');
			probe.className = PAGE_CLASSES.join(' ');
			article.appendChild(probe);
			main.appendChild(article);
			document.body.appendChild(main);
			pageDisplay = getComputedStyle(probe).display;
			pageBackground = [probe, article, main].map(function (el) {
				return getComputedStyle(el).backgroundColor;
			}).filter(function (color) {
				return color && color !== 'transparent' && !/rgba\([^)]*,\s*0\)$/.test(color);
			})[0] || '';
			main.remove();
		}
		if (pageDisplay !== 'block' && root.style.display !== pageDisplay) root.style.display = pageDisplay;
		// The page shows its background through transparent blocks
		if (pageBackground && root.style.backgroundColor !== pageBackground) root.style.backgroundColor = pageBackground;
	};

	// Styles that reach the content wrapper through the page template ("#content > article >
	// .content") can't match in the canvas, where the post content container is the wrapper.
	// Copy them starting from .content: the template's own elements are dropped, and body
	// qualifiers are kept (the canvas body has the page's classes), so
	// "body.page-careers #content > article > .content > p" becomes
	// "body.page-careers .content > p". Any other qualified ancestor can't match in the canvas.
	var TEMPLATE_ELEMENT = /^(html|body|main|article|#content|#main)$/;

	var pageVariants = function (selectorText) {
		var out = [];

		splitTopLevel(selectorText, ',').forEach(function (selector) {
			var tokens = selector.match(/(?:[^\s>+~()[\]]+|\([^)]*\)|\[[^\]]*\])+|\s*[>+~]\s*|\s+/g) || [];
			var start = -1;
			for (var i = 0; i < tokens.length; i++) {
				if (/^\.content(?![\w-])/.test(tokens[i])) { start = i; break; }
			}
			if (start < 1) return;

			var kept = [];
			for (var k = 0; k < start; k++) {
				var token = tokens[k];
				if (/^\s*[>+~]?\s*$/.test(token)) {
					// Only descendant and child combinators lead to the wrapper
					if (/[+~]/.test(token)) return;
					continue;
				}
				if (TEMPLATE_ELEMENT.test(token)) continue;
				if (/^body[.[:#]/.test(token)) { kept.push(token); continue; }
				return;
			}

			out.push((kept.length ? kept.join(' ') + ' ' : '') + tokens.slice(start).join(''));
		});

		return out;
	};

	var frame = null;
	var schedule = function () {
		if (frame) return;
		frame = requestAnimationFrame(function () {
			frame = null;
			tagContent();
			syncWrappers();
		});
	};

	// The child theme's editor styles mark layout and container blocks with a dashed border, inner
	// padding and a label in the flow, which shifts and resizes their content against the page.
	// Keep the marking, drawn as an outline and an overlaid label that take no space.
	var LAYOUT_BLOCKS = ['box', 'center', 'cluster', 'container', 'cover', 'frame', 'grid', 'icon', 'imposter', 'reel', 'sidebar', 'stack', 'switcher', 'accordion-plus', 'accordion-item', 'tabs-plus', 'tab-item'];

	var markLayoutBlocks = function () {
		var selectors = LAYOUT_BLOCKS.map(function (name) {
			return 'html body.editor-styles-wrapper [data-type="lqx/' + name + '"]';
		});
		var style = document.createElement('style');
		style.id = 'lqx-canvas-layout-blocks';
		style.textContent = selectors.join(', ') + ' { border: 0 !important; padding: 0 !important; outline: 1px dashed #5d93ad; outline-offset: -1px; position: relative; }\n' +
			selectors.map(function (selector) { return selector + '::before'; }).join(', ') +
			' { content: attr(data-type); display: block; position: absolute !important; top: 0; left: 0; z-index: 30; margin: 0 !important; padding: 0 0.25em !important; font-size: 0.75em; color: #5d93ad; background: rgba(255, 255, 255, 0.85); pointer-events: none; opacity: 0; transition: opacity 0.15s; }\n' +
			// The label covers the block's first content, so only show it while the block is in focus
			selectors.map(function (selector) { return selector + ':hover::before, ' + selector + '.is-selected::before, ' + selector + '.has-child-selected::before'; }).join(', ') +
			' { opacity: 1; }\n' +
			// ACF's inner blocks container takes no box, so inner blocks sit in the parent's layout
			'.acf-innerblocks-container.block-editor-block-list__layout { display: contents; }\n' +
			// Blocks that take the page grid with grid-template-columns: inherit get it from their
			// parent, which in the editor is a wrapper: pass the parent's template through
			WRAPPER + ', ' + CONTAINER + ' { grid-template-columns: inherit; grid-template-rows: inherit; }';
		document.head.appendChild(style);
	};

	var start = function () {
		markLayoutBlocks();
		processSheets();
		schedule();
		new MutationObserver(schedule).observe(document.body, { childList: true, subtree: true, attributes: true, attributeFilter: ['class'] });
		window.addEventListener('resize', schedule);
	};

	// The canvas document is written with this script in its head and no <body>; the editor
	// renders its own body later, and document.body is null in between
	var ready = function () {
		return document.body && document.body.classList.contains('block-editor-iframe__body');
	};

	if (ready()) {
		start();
	} else {
		var observer = new MutationObserver(function () {
			if (!ready()) return;
			observer.disconnect();
			start();
		});
		observer.observe(document.documentElement, { childList: true });
	}
})();

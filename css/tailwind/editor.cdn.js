/**
 * editor.cdn.js - Tailwind CSS CDN configuration for editor
 *
 * @version     3.0.0
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

import tailwindContainerQueries from 'https://cdn.skypack.dev/@tailwindcss/container-queries';
import { tailwindLayouts, defaultOptions } from 'https://cdn.skypack.dev/tailwind-layouts';
import presets from './presets.js';
import theme from './theme.js';

// Export Tailwind CSS configuration
tailwind.config = {
	presets: [presets],
	theme: theme,
	important: true,
	plugins: [
		tailwindContainerQueries,
		tailwindLayouts({
			...defaultOptions,
			useGlobalMeasure: false,
		}),
	],
	corePlugins: {
		// Disable Preflight base styles in CSS targeting the editor.
		preflight: false,
	}
};

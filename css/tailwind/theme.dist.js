/**
 * theme.js - Tailwind CSS theme configuration
 *
 * @version     3.0.0
 * @package     wp_theme_lyquix
 * @author      Lyquix
 * @copyright   Copyright (C) 2015 - 2018 Lyquix
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

const extend = {
	extend: {
		colors: {
			/* Background */
			'bg': '#ffffff',
			/* Color Palette */
			'name-1': '#cc0000',
			'name-2': '#2a8691',
			'name-3': '#95675d',
			'name-4': '#ffcc00',
			'name-5': '#d1a10d',
			'name-6': '#7ea52e',
			'name-7': '#efb41d',
			'name-8': '#7ea52e',
			'name-9': '#efb41d',
			/* Typography */
			'color-base': '#303030',
			'color-headings': 'theme("colors.color-base")',
			'color-h1': 'theme("colors.color-headings")',
			'color-h2': 'theme("colors.color-headings")',
			'color-h3': 'theme("colors.color-headings")',
			'color-h4': 'theme("colors.color-headings")',
			'color-h5': 'theme("colors.color-headings")',
			'color-h6': 'theme("colors.color-headings")',
			/* Links */
			'link': 'theme("colors.blue.700")',
			'link-hover': 'theme("colors.blue.500")',
			'link-visited': 'theme("colors.purple.700")',
			/* Forms */
			'input': 'theme("colors.color-base")',
			'input-bg': 'theme("colors.bg")',
			'input-border': 'theme("colors.gray.500")',
			'button': 'theme("colors.color-base")',
			'button-bg': 'theme("colors.gray.300")',
			'button-bg-hover': 'theme("colors.gray.200")',
			'label': 'theme("colors.color-base")',
			'checkbox': 'theme("colors.gray.500")',
			'radio': 'theme("colors.gray.500")'
		},
		fontFamily: {
			/* Font families */
			'sans': 'sans-serif',
			'serif': 'serif',
			'mono': 'monospace',
			/* Typography */
			'base': 'theme("fontFamily.sans")',
			'headings': 'theme("fontFamily.base")',
			'h1': 'theme("fontFamily.headings")',
			'h2': 'theme("fontFamily.headings")',
			'h3': 'theme("fontFamily.headings")',
			'h4': 'theme("fontFamily.headings")',
			'h5': 'theme("fontFamily.headings")',
			'h6': 'theme("fontFamily.headings")',
			'input': 'theme("fontFamily.base")',
			'button': 'theme("fontFamily.base")',
			'label': 'theme("fontFamily.base")'
		},
		fontSize: {
			'size-base': '1.6rem',
			'size-base-xs': 'theme("fontSize.size-base")',
			'size-base-sm': 'theme("fontSize.size-base")',
			'size-base-md': 'theme("fontSize.size-base")',
			'size-base-lg': 'theme("fontSize.size-base")',
			'size-base-xl': 'theme("fontSize.size-base")',
			'size-h1': '3.2rem',
			'size-h1-xs': 'theme("fontSize.size-h1")',
			'size-h1-sm': 'theme("fontSize.size-h1")',
			'size-h1-md': 'theme("fontSize.size-h1")',
			'size-h1-lg': 'theme("fontSize.size-h1")',
			'size-h1-xl': 'theme("fontSize.size-h1")',
			'size-h2': '2.4rem',
			'size-h2-xs': 'theme("fontSize.size-h2")',
			'size-h2-sm': 'theme("fontSize.size-h2")',
			'size-h2-md': 'theme("fontSize.size-h2")',
			'size-h2-lg': 'theme("fontSize.size-h2")',
			'size-h2-xl': 'theme("fontSize.size-h2")',
			'size-h3': '1.872rem',
			'size-h3-xs': 'theme("fontSize.size-h3")',
			'size-h3-sm': 'theme("fontSize.size-h3")',
			'size-h3-md': 'theme("fontSize.size-h3")',
			'size-h3-lg': 'theme("fontSize.size-h3")',
			'size-h3-xl': 'theme("fontSize.size-h3")',
			'size-h4': '1.6rem',
			'size-h4-xs': 'theme("fontSize.size-h4")',
			'size-h4-sm': 'theme("fontSize.size-h4")',
			'size-h4-md': 'theme("fontSize.size-h4")',
			'size-h4-lg': 'theme("fontSize.size-h4")',
			'size-h4-xl': 'theme("fontSize.size-h4")',
			'size-h5': '1.328rem',
			'size-h5-xs': 'theme("fontSize.size-h5")',
			'size-h5-sm': 'theme("fontSize.size-h5")',
			'size-h5-md': 'theme("fontSize.size-h5")',
			'size-h5-lg': 'theme("fontSize.size-h5")',
			'size-h5-xl': 'theme("fontSize.size-h5")',
			'size-h6': '1.072rem',
			'size-h6-xs': 'theme("fontSize.size-h6")',
			'size-h6-sm': 'theme("fontSize.size-h6")',
			'size-h6-md': 'theme("fontSize.size-h6")',
			'size-h6-lg': 'theme("fontSize.size-h6")',
			'size-h6-xl': 'theme("fontSize.size-h6")'
		},
		fontWeight: {
			'weight-base': 'normal',
			'weight-headings': 'bold',
			'weight-h1': 'theme("fontWeight.weight-headings")',
			'weight-h2': 'theme("fontWeight.weight-headings")',
			'weight-h3': 'theme("fontWeight.weight-headings")',
			'weight-h4': 'theme("fontWeight.weight-headings")',
			'weight-h5': 'theme("fontWeight.weight-headings")',
			'weight-h6': 'theme("fontWeight.weight-headings")',
		},
		lineHeight: {
			'base': 'normal',
			'base-xs': 'theme("lineHeight.base")',
			'base-sm': 'theme("lineHeight.base")',
			'base-md': 'theme("lineHeight.base")',
			'base-lg': 'theme("lineHeight.base")',
			'base-xl': 'theme("lineHeight.base")',
			'h1': 'normal',
			'h1-xs': 'theme("lineHeight.h1")',
			'h1-sm': 'theme("lineHeight.h1")',
			'h1-md': 'theme("lineHeight.h1")',
			'h1-lg': 'theme("lineHeight.h1")',
			'h1-xl': 'theme("lineHeight.h1")',
			'h2': 'normal',
			'h2-xs': 'theme("lineHeight.h2")',
			'h2-sm': 'theme("lineHeight.h2")',
			'h2-md': 'theme("lineHeight.h2")',
			'h2-lg': 'theme("lineHeight.h2")',
			'h2-xl': 'theme("lineHeight.h2")',
			'h3': 'normal',
			'h3-xs': 'theme("lineHeight.h3")',
			'h3-sm': 'theme("lineHeight.h3")',
			'h3-md': 'theme("lineHeight.h3")',
			'h3-lg': 'theme("lineHeight.h3")',
			'h3-xl': 'theme("lineHeight.h3")',
			'h4': 'normal',
			'h4-xs': 'theme("lineHeight.h4")',
			'h4-sm': 'theme("lineHeight.h4")',
			'h4-md': 'theme("lineHeight.h4")',
			'h4-lg': 'theme("lineHeight.h4")',
			'h4-xl': 'theme("lineHeight.h4")',
			'h5': 'normal',
			'h5-xs': 'theme("lineHeight.h5")',
			'h5-sm': 'theme("lineHeight.h5")',
			'h5-md': 'theme("lineHeight.h5")',
			'h5-lg': 'theme("lineHeight.h5")',
			'h5-xl': 'theme("lineHeight.h5")',
			'h6': 'normal',
			'h6-xs': 'theme("lineHeight.h6")',
			'h6-sm': 'theme("lineHeight.h6")',
			'h6-md': 'theme("lineHeight.h6")',
			'h6-lg': 'theme("lineHeight.h6")',
			'h6-xl': 'theme("lineHeight.h6")'
		},
		margin: {
			/* Block margins */
			'xs': '5px',
			'sm': '5px',
			'md': '5px',
			'lg': '5px',
			'xl': '5px',
			/* Typography margins */
			'base': '1em',
			'headings': '0.67em',
			'input': '0.25em',
			/* Container margins */
			'container-xs': '10px',
			'container-sm': '30px',
			'container-md': '30px',
			'container-lg': '30px',
			'container-xl': '0px'
		},
		padding: {
			/* Block padding */
			'xs': '0px',
			'sm': '0px',
			'md': '0px',
			'lg': '0px',
			'xl': '0px',
			/* Typography padding */
			'base': '1em',
			'list': '1.2em',
			'input': '0.25em',
			'button-x': '0.5em',
			'button-y': '0.25em',
			'td': '0.5em'
		},
		maxWidth: {
			/* Container widths */
			'container-xs': 'none',
			'container-sm': 'none',
			'container-md': 'none',
			'container-lg': 'none',
			'container-xl': '1620px'
		}
	},
};

export default extend;

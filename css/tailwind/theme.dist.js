/**
 * theme.js - Sample Tailwind CSS theme configuration
 *
 * @version     3.2.0
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
//  Instead copy this file to css/tailwind/theme.js and edit it there

// Import the extend function
const extend = {
	extend: {
		colors: {
			/* Background */
			'bg': '#ffffff',
			/* Color Palette */
			'lyquix-green': '#6C9D0C',
			'lyquix-green-accessible': '#408316',
			'midnight-black': '#35393E',
			'background-fill': '#F6F6F6',
			'summer-yellow': '#F5C86F',
			'summer-yellow-accessible': '#AF7D18',
			'ocean-teal': '#17B8AE',
			'ocean-teal-accessible': '#13968D',
			'salmon-pink': '#F5C0B9',
			'salmon-pink-accessible': '#E35E4A',
			'light-gray': '#4A4A4A',
			'light-b-gray': '#A7A7A7',
			'gray-border': '#BEBEBE',
			'overlay-green': 'rgba(70,102,7,0.9)',
			'overlay-yellow': 'rgba(138,93,4,0.9)',
			'overlay-teal': 'rgba(21,103,97,0.9)',
			/* Typography */
			'color-base': '#595B60',
			'color-headings': 'theme("colors.midnight-black")',
			'color-h1': '#35393E',
			'color-h2': 'theme("colors.color-headings")',
			'color-h3': 'theme("colors.midnight-black")',
			'color-h4': 'theme("colors.color-base")',
			'color-h5': 'theme("colors.color-headings")',
			'color-h6': 'theme("colors.color-headings")',
			/* Links */
			'link': 'theme("colors.light-gray")',
			'link-hover': 'theme("colors.lyquix-green-accessible")',
			'link-visited': 'theme("colors.light-gray")',
			/* Forms */
			'input': 'theme("colors.color-base")',
			'input-bg': 'theme("colors.bg")',
			'input-border': 'theme("colors.gray-border")',
			'button': 'theme("colors.bg")',
			'button-bg': 'theme("colors.lyquix-green-accessible")',
			'button-bg-hover': 'theme("colors.midnight-black")',
			'label': 'theme("colors.color-base")',
			'checkbox': 'theme("colors.gray.500")',
			'radio': 'theme("colors.gray.500")',
		},
		fontFamily: {
			/* Font families */
			'sans': 'Public Sans, sans-serif',
			'sans-alt': 'Inter, sans-serif',
			'serif': 'serif',
			'mono': 'monospace',
			/* Typography */
			'base': 'theme("fontFamily.sans")',
			'headings': 'Inter, sans-serif',
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
			'size-base': '1.8rem',
			'size-base-xs': '1.6rem',
			'size-base-sm': 'theme("fontSize.size-base-xs")',
			'size-base-md': 'theme("fontSize.size-base-xs")',
			'size-base-lg': 'theme("fontSize.size-base")',
			'size-base-xl': 'theme("fontSize.size-base")',
			'size-h1': '6rem',
			'size-h1-xs': '4.5rem',
			'size-h1-sm': 'theme("fontSize.size-h1-xs")',
			'size-h1-md': 'theme("fontSize.size-h1")',
			'size-h1-lg': 'theme("fontSize.size-h1")',
			'size-h1-xl': 'theme("fontSize.size-h1")',
			'size-h2': '4.5rem',
			'size-h2-xs': '3.5rem',
			'size-h2-sm': 'theme("fontSize.size-h2-xs")',
			'size-h2-md': 'theme("fontSize.size-h2")',
			'size-h2-lg': 'theme("fontSize.size-h2")',
			'size-h2-xl': 'theme("fontSize.size-h2")',
			'size-h3': '2.2rem',
			'size-h3-xs': '2rem',
			'size-h3-sm': 'theme("fontSize.size-h3-xs")',
			'size-h3-md': 'theme("fontSize.size-h3")',
			'size-h3-lg': 'theme("fontSize.size-h3")',
			'size-h3-xl': 'theme("fontSize.size-h3")',
			'size-h4': '1.8rem',
			'size-h4-xs': '1.6rem',
			'size-h4-sm': 'theme("fontSize.size-h4-xs")',
			'size-h4-md': 'theme("fontSize.size-h4")',
			'size-h4-lg': 'theme("fontSize.size-h4")',
			'size-h4-xl': 'theme("fontSize.size-h4")',
			'size-h5': '1.3rem',
			'size-h5-xs': '1.1rem',
			'size-h5-sm': 'theme("fontSize.size-h5-xs")',
			'size-h5-md': 'theme("fontSize.size-h5")',
			'size-h5-lg': 'theme("fontSize.size-h5")',
			'size-h5-xl': 'theme("fontSize.size-h5")',
			'size-h6': '1.5rem',
			'size-h6-xs': '1.3rem',
			'size-h6-sm': 'theme("fontSize.size-h6-xs")',
			'size-h6-md': 'theme("fontSize.size-h6")',
			'size-h6-lg': 'theme("fontSize.size-h6")',
			'size-h6-xl': 'theme("fontSize.size-h6")',
			'size-breadcrumb': 'theme("fontSize.size-base-xs")',
			'size-breadcrumb-xs': '1.4rem'
		},
		fontWeight: {
			'weight-base': 'normal',
			'weight-headings': 'bold',
			'weight-h1': '800',
			'weight-h2': 'theme("fontWeight.weight-headings")',
			'weight-h3': '600',
			'weight-h4': '400',
			'weight-h5': '500',
			'weight-h6': '500',
		},
		lineHeight: {
			'base': '1.555',
			'base-xs': '1.563',
			'base-sm': 'theme("lineHeight.base-xs")',
			'base-md': 'theme("lineHeight.base-xs")',
			'base-lg': 'theme("lineHeight.base")',
			'base-xl': 'theme("lineHeight.base")',
			'h1': '1.25',
			'h1-xs': '1.178',
			'h1-sm': 'theme("lineHeight.h1-xs")',
			'h1-md': 'theme("lineHeight.h1")',
			'h1-lg': 'theme("lineHeight.h1")',
			'h1-xl': 'theme("lineHeight.h1")',
			'h2': '1.333',
			'h2-xs': '1.2',
			'h2-sm': 'theme("lineHeight.h2-xs")',
			'h2-md': 'theme("lineHeight.h2")',
			'h2-lg': 'theme("lineHeight.h2")',
			'h2-xl': 'theme("lineHeight.h2")',
			'h3': '1.455',
			'h3-xs': '1.5',
			'h3-sm': 'theme("lineHeight.h3-xs")',
			'h3-md': 'theme("lineHeight.h3")',
			'h3-lg': 'theme("lineHeight.h3")',
			'h3-xl': 'theme("lineHeight.h3")',
			'h4': '1.555',
			'h4-xs': '1.625',
			'h4-sm': 'theme("lineHeight.h4-xs")',
			'h4-md': 'theme("lineHeight.h4")',
			'h4-lg': 'theme("lineHeight.h4")',
			'h4-xl': 'theme("lineHeight.h4")',
			'h5': '1.462',
			'h5-xs': '1.727',
			'h5-sm': 'theme("lineHeight.h5-xs")',
			'h5-md': 'theme("lineHeight.h5")',
			'h5-lg': 'theme("lineHeight.h5")',
			'h5-xl': 'theme("lineHeight.h5")',
			'h6': '1.8',
			'h6-xs': '1.769',
			'h6-sm': 'theme("lineHeight.h6-xs")',
			'h6-md': 'theme("lineHeight.h6")',
			'h6-lg': 'theme("lineHeight.h6")',
			'h6-xl': 'theme("lineHeight.h6")',
			'breadcrumb' : '2'
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
			'input': '0.25em'
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
		}
	},
};

module.exports = extend;
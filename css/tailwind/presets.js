/**
 * presets.js - Tailwind CSS presets
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

export default {
	theme: {
		extend: {
			colors: {
				'base-bg': '#fff',
				copy: '#303030',
				primary: {
					DEFAULT: '#7ea52e',
					lighter: '#96c537',
				},
				secondary: '#8c8d88',
				'accent-1': '#c00',
				'accent-2': '#2a8691',
				'accent-3': '#95675d',
				'accent-4': '#fc0',
				'accent-5': '#d1a10d',
				headings: 'theme(\'colors.primary.DEFAULT\')',
				link: 'theme(\'colors.primary.DEFAULT\')',
				input: {
					DEFAULT: 'theme(\'colors.copy\')',
					bg: 'theme(\'colors.base-bg\')',
					border: 'theme(\'colors.gray.500\')',
				},
				button: {
					DEFAULT: 'theme(\'colors.gray.500\')',
					bg: {
						DEFAULT: 'theme(\'colors.primary.DEFAULT\')',
						hover: 'theme(\'colors.primary.lighter\')',
					},
				},
				label: 'theme(\'colors.copy\')',
				checkbox: 'theme(\'colors.primary.DEFAULT\')',
				radio: 'theme(\'colors.primary.DEFAULT\')',
				hr: 'theme(\'colors.copy\')',
				blockquote: 'theme(\'colors.copy\')',
				td: {
					DEFAULT: 'theme(\'colors.copy\')',
					border: 'theme(\'colors.gray.500\')',
					bg: 'theme(\'colors.base-bg\')',
				},
				th: {
					DEFAULT: 'theme(\'colors.copy\')',
					border: 'theme(\'colors.gray.500\')',
					bg: 'theme(\'colors.gray.500\')',
				},
				bullets: 'theme(\'colors.primary.DEFAULT\')',
			},
			spacing: {
				base: '1em',
				heading: '0.67em',
				input: '0.25em',
			},
			fontFamily: {
				headings: 'theme(\'fontFamily.sans\')',
				copy: 'theme(\'fontFamily.sans\')',
				input: 'theme(\'fontFamily.copy\')',
				label: 'theme(\'fontFamily.copy\')',
				button: 'theme(\'fontFamily.copy\')',
				blockquote: 'theme(\'fontFamily.copy\')',
				th: 'theme(\'fontFamily.copy\')',
				td: 'theme(\'fontFamily.copy\')',
			},
			fontWeight: {
				base: 'normal',
				'w-headings': 'bold',
			},
			lineHeight: {
				normal: 'normal',
			},
			fontSize: {
				base: '16px',
				h1: '32px',
				h2: '24px',
				h3: '1.3em',
				h4: '16px',
				h5: '.8em',
				h6: '.7em',
			},
			container: {
				center: true,
			},
			screens: {
				xs: '0px',
				sm: '640px',
				md: '960px',
				lg: '1280px',
				xl: '1600px'
			},
		},
	},
};

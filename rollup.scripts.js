/**
 * rollup.scripts.js - Rollup configuration for custom scripts
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

// Rollup plugins
import typescript from '@rollup/plugin-typescript';

// Rollup configuration
export default {
	input: 'js/scripts.ts',
	output: [
		{
			file: 'js/scripts.js',
			format: 'iife',
			name: '$lqx',
			sourcemap: false
		}
	],
	plugins: [
		typescript({
			tsconfig: 'tsconfig.scripts.json',
		})
	],
	watch: {
		include: ['js/scripts.ts', 'js/custom/scripts/*.ts'],
		clearScreen: false
	}
};

<?php

/**
 * modules.php - Lyquix theme modules
 *
 * @version     3.0.0
 * @package     wp_theme_lyquix
 * @author      Lyquix
 * @copyright   Copyright (C) 2015 - 2017 Lyquix
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
//  Instead add directories under /php/custom/blocks and use block.json files

namespace lqx\modules;

// Get directories under php/modules
$modules = array_map(function($dir) {
	return basename($dir);
}, array_filter(glob(get_template_directory() . '/php/modules/*'), 'is_dir'));

// Load each module once
foreach ($modules as $module) {
	require_once get_template_directory() . '/php/modules/' . $module . '/' . $module . '.php';
}

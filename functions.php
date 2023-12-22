<?php

/**
 * functions.php - Theme main functions file
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
//  If you need to add custom:
//  - functions, use /php/custom/functions.php
//  - menu positions, use /php/custom/menus.php
//	- widget positions, use /php/custom/widgets.php
//	- shortcodes, use /php/custom/shortcodes.php
//	- blocks, use /php/custom/blocks.php
//	- site option pages, use /php/custom/options.php

// Do not allow browsers to cache WordPress pages
nocache_headers();

// Utility Functions
require_once get_template_directory() . '/php/util.php';

// Remove comments
require_once get_template_directory() . '/php/comments.php';

// Theme setup
require_once get_template_directory() . '/php/setup.php';

// Menu positions
require_once get_template_directory() . '/php/menus.php';

// Widget positions
require_once get_template_directory() . '/php/widgets.php';

// Theme customizer
require_once get_template_directory() . '/php/customizer.php';

// Blocks
require_once get_template_directory() . '/php/blocks.php';

// Layouts
require_once get_template_directory() . '/php/layouts.php';

// Modules
require_once get_template_directory() . '/php/modules.php';

// Tailwind
require_once get_template_directory() . '/php/tailwind.php';

// Custom functions.php
if (file_exists(get_template_directory() . '/php/custom/functions.php')) {
	require_once get_template_directory() . '/php/custom/functions.php';
}

// Shortcodes
if (file_exists(get_template_directory() . '/php/custom/shortcodes.php')) {
	require_once get_template_directory() . '/php/custom/shortcodes.php';
}

// Updates checker
require_once get_template_directory() . '/php/update.php';

// Prepare meta tags
require_once get_template_directory() . '/php/meta.php';

// Enqueue CSS
require_once get_template_directory() . '/php/css.php';

// Enqueue JS
require_once get_template_directory() . '/php/js.php';

// Render favicons
require_once get_template_directory() . '/php/favicon.php';

// Prepare body classes
require_once get_template_directory() . '/php/body.php';

// Template router
require_once get_template_directory() . '/php/router.php';

// Outdated browser alert
require_once get_template_directory() . '/php/browsers.php';

// Livereload
require_once get_template_directory() . '/php/livereload.php';

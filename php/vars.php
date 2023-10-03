<?php

/**
 * vars.php - Initialize variables
 *
 * @version     2.3.3
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

$site_abs_url = get_site_url();
$site_rel_url = wp_make_link_relative($site_abs_url);
if (!$site_rel_url) $site_rel_url = '/';
$tmpl_url = $site_rel_url . 'wp-content/themes/' . get_template();
$tmpl_path = get_template_directory();

// Check if we are on the home page
$home = is_front_page();

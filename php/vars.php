<?php
/**
 * vars.php - Initialize variables
 *
 * @version     2.4.0
 * @package     wp_theme_lyquix
 * @author      Lyquix
 * @copyright   Copyright (C) 2015 - 2018 Lyquix
 * @license     GNU General Public License version 2 or later
 * @link        https://github.com/Lyquix/wp_theme_lyquix
 */

$site_abs_url = get_site_url();
$site_rel_url = wp_make_link_relative($site_abs_url);
if(!$site_rel_url) $site_rel_url = '/';
$tmpl_url = $site_rel_url . 'wp-content/themes/' . get_template();
$tmpl_path = get_template_directory();
$cdnjs_url = 'https://cdnjs.cloudflare.com/ajax/libs/';

// Check if we are on the home page
$home = is_front_page();

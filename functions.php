<?php
/**
 * functions.php - Theme main functions file
 *
 * @version     2.5.1
 * @package     wp_theme_lyquix
 * @author      Lyquix
 * @copyright   Copyright (C) 2015 - 2018 Lyquix
 * @license     GNU General Public License version 2 or later
 * @link        https://github.com/Lyquix/wp_theme_lyquix
 */

// Do not allow browsers to cache WordPress pages
nocache_headers();

// Remove comments
if (get_theme_mod('feat_disable_comments', '1') === '1') {
	require_once get_template_directory() . '/php/comments.php';
}

require get_template_directory() . '/php/setup.php';
add_action('after_setup_theme', 'lqx_setup');

require get_template_directory() . '/php/widgets.php';
add_action('widgets_init', 'lqx_widgets');

require get_template_directory() . '/php/customizer.php';
add_action('customize_register', 'lqx_customizer_add');

if(file_exists(get_template_directory() . '/php/custom/functions.php')) {
	require get_template_directory() . '/php/custom/functions.php';
}

require get_template_directory() . '/php/critical.php';

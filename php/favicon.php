<?php

/**
 * favicon.php - Includes favicons
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

// Use http://www.favicon-generator.org/ to generate all these favicon versions

namespace lqx\favicon;

function render() {
	$favicons_sizes = [57, 60, 72, 76, 114, 120, 144, 152, 180];

	foreach ($favicons_sizes as $favicon_size) {
		$favicon_size = $favicon_size . 'x' . $favicon_size;
		if (file_exists(get_template_directory() . '/images/favicon/apple-icon-' . $favicon_size . '.png')) {
			echo '<link rel="apple-touch-icon" sizes="' . $favicon_size . '" href="' . get_template_directory_uri() . '/images/favicon/apple-icon-' . $favicon_size . '.png">' . "\n";
		}
	}

	if (file_exists(get_template_directory() . '/images/favicon/android-icon-192x192.png')) : ?>
		<link rel="icon" type="image/png" sizes="192x192" href="<?php echo get_template_directory_uri(); ?>/images/favicon/android-icon-192x192.png">
	<?php endif;

	if (file_exists(get_template_directory() . '/images/favicon/favicon.ico')) : ?>
		<link rel="shortcut icon" type="image/vnd.microsoft.icon" href="<?php echo get_template_directory_uri(); ?>/images/favicon/favicon.ico">
	<?php endif;

	if (file_exists(get_template_directory() . '/images/favicon/favicon-32x32.png')) : ?>
		<link rel="icon" type="image/png" sizes="32x32" href="<?php echo get_template_directory_uri(); ?>/images/favicon/favicon-32x32.png">
	<?php endif;

	if (file_exists(get_template_directory() . '/images/favicon/favicon-96x96.png')) : ?>
		<link rel="icon" type="image/png" sizes="96x96" href="<?php echo get_template_directory_uri(); ?>/images/favicon/favicon-96x96.png">
	<?php endif;

	if (file_exists(get_template_directory() . '/images/favicon/favicon-16x16.png')) : ?>
		<link rel="icon" type="image/png" sizes="16x16" href="<?php echo get_template_directory_uri(); ?>/images/favicon/favicon-16x16.png">
<?php endif;
}

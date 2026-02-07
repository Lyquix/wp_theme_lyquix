<?php

/**
 * favicon.php - Includes favicons
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

// Use http://www.favicon-generator.org/ to generate all these favicon versions

namespace lqx\favicon;

/**
 * Renders the favicon links based on the available favicon images.
 *
 * Generates the HTML code for the favicon links based on the available favicon images in the theme directory.
 * It checks for different sizes of favicon images and adds the corresponding link tags to the HTML output.
 *
 * @return void
 */
function favicon_path($filename) {
	if (file_exists(get_stylesheet_directory() . '/images/favicon/' . $filename)) {
		return get_stylesheet_directory_uri() . '/images/favicon/' . $filename;
	}
	if (file_exists(get_template_directory() . '/images/favicon/' . $filename)) {
		return get_template_directory_uri() . '/images/favicon/' . $filename;
	}
	return false;
}

function render() {
	$favicons_sizes = [57, 60, 72, 76, 114, 120, 144, 152, 180];

	foreach ($favicons_sizes as $favicon_size) {
		$favicon_size = $favicon_size . 'x' . $favicon_size;
		$url = favicon_path('apple-icon-' . $favicon_size . '.png');
		if ($url) {
			echo '<link rel="apple-touch-icon" sizes="' . $favicon_size . '" href="' . $url . '">' . "\n";
		}
	}

	$url = favicon_path('android-icon-192x192.png');
	if ($url) : ?>
		<link rel="icon" type="image/png" sizes="192x192" href="<?php echo $url; ?>">
	<?php endif;

	$url = favicon_path('favicon.ico');
	if ($url) : ?>
		<link rel="shortcut icon" type="image/vnd.microsoft.icon" href="<?php echo $url; ?>">
	<?php endif;

	$url = favicon_path('favicon-32x32.png');
	if ($url) : ?>
		<link rel="icon" type="image/png" sizes="32x32" href="<?php echo $url; ?>">
	<?php endif;

	$url = favicon_path('favicon-96x96.png');
	if ($url) : ?>
		<link rel="icon" type="image/png" sizes="96x96" href="<?php echo $url; ?>">
	<?php endif;

	$url = favicon_path('favicon-16x16.png');
	if ($url) : ?>
		<link rel="icon" type="image/png" sizes="16x16" href="<?php echo $url; ?>">
<?php endif;
}

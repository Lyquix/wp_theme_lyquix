<?php

/**
 * meta.php - Render meta tags
 *
 * @version     3.4.0
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

namespace lqx\meta;

/**
 * Render meta tags
 *
 * @return void
 *		Echoes meta tags
 */
function render() {
?>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<?php
	// Adds search engine domain validation strings to home page only
	if (is_front_page()) {
		foreach ([
			'google_site_verification' => 'google-site-verification',
			'msvalidate' => 'msvalidate.01',
			'p_domain_verify' => 'p:domain_verify'
		] as $theme_option_name => $meta_tag_name) {
			$theme_option_value = get_theme_mod($theme_option_name, '');
			if ($theme_option_value) echo '<meta name="' . $meta_tag_name . '" content="' . esc_attr($theme_option_value) . '" />' . "\n";
		}
	}

	// Add preconnect tags
	$rel_preconnect = explode("\n", get_theme_mod('rel_preconnect', implode("\n", [
		'https://cdn.jsdelivr.net',
		'https://www.google.com',
		'https://www.google-analytics.com',
		'https://www.googletagmanager.com',
		'https://www.gstatic.com',
		'https://fonts.gstatic.com',
		'https://fonts.googleapis.com'
	])));
	foreach ($rel_preconnect as $rel_preconnect_url) {
		$rel_preconnect_url = trim($rel_preconnect_url);
		if ($rel_preconnect_url) echo '<link rel="preconnect" href="' . $rel_preconnect_url . '" />' . "\n";
	}

	// Additional meta tags
	if (get_theme_mod('add_meta_tags', '')) echo get_theme_mod('add_meta_tags', '') . "\n";
	?>
	<script>
		// Add class js to html element
		(function(html) {
			html.className = html.className.replace(/\bno-js\b/, 'js')
		})(document.documentElement);
	</script>
<?php
}

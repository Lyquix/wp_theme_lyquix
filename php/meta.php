<?php

/**
 * meta.php - Render meta tags
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

namespace lqx\meta;

// Render meta tags
function render() {
?>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<?php
	// Adds search engine domain validation strings to home page only
	if (is_front_page()) {
		echo get_theme_mod('google_site_verification', '') ? '<meta name="google-site-verification" content="' . get_theme_mod('google_site_verification', '') . '" />' . "\n" : '';
		echo get_theme_mod('msvalidate', '') ? '<meta name="msvalidate.01" content="' . get_theme_mod('msvalidate', '') . '" />' . "\n" : '';
		echo get_theme_mod('p_domain_verify', '') ? '<meta name="p:domain_verify" content="' . get_theme_mod('p_domain_verify', '') . '"/>' . "\n" : '';
	}
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

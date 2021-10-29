<?php
/**
 * meta.php - Includes meta tags
 *
 * @version     2.3.3
 * @package     wp_theme_lyquix
 * @author      Lyquix
 * @copyright   Copyright (C) 2015 - 2017 Lyquix
 * @license     GNU General Public License version 2 or later
 * @link        https://github.com/Lyquix/wp_theme_lyquix
 */

// Remove WordPress generator meta tag
remove_action('wp_head', 'wp_generator');
?>
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<?php
if(get_theme_mod('polyfill', '1')): ?>
<script src="https://cdn.polyfill.io/v2/polyfill<?php echo get_theme_mod('non_min_js', '0') ? '' : '.min'; ?>.js?features=default,Math.imul"></script>
<?php endif;
// Adds search engine domain validation strings to home page only
if($home) {
	echo get_theme_mod('google_site_verification', '') ? '<meta name="google-site-verification" content="' . get_theme_mod('google_site_verification', '') . '" />' . "\n" : '';
	echo get_theme_mod('msvalidate', '') ? '<meta name="msvalidate.01" content="' . get_theme_mod('msvalidate', '') . '" />' . "\n" : '';
	echo get_theme_mod('p_domain_verify', '') ? '<meta name="p:domain_verify" content="' . get_theme_mod('p_domain_verify', '') . '"/>' . "\n" : '';
}
?>
<script>(function(html){html.className = html.className.replace(/\bno-js\b/,'js')})(document.documentElement);</script>

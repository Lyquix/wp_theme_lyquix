<?php 
/**
 * head-top.inc.php - Includes for top of <head> tag
 *
 * @version     1.0.1
 * @package     tpl_lyquix
 * @author      Lyquix
 * @copyright   Copyright (C) 2015 - 2017 Lyquix
 * @license     GNU General Public License version 2 or later
 * @link        https://github.com/Lyquix/tpl_lyquix
 */
?>
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta charset="<?php bloginfo( 'charset' ); ?>">
<?php
// Adds search engine domain validation strings to home page only
if($home) {
		echo get_theme_mod('google_site_verification') ? '<meta name="google-site-verification" content="' . get_theme_mod('google_site_verification') . '" />' . "\n" : '';
		echo get_theme_mod('msvalidate') ? '<meta name="msvalidate.01" content="' . get_theme_mod('msvalidate') . '" />' . "\n" : '';
		echo get_theme_mod('p_domain_verify') ? '<meta name="p:domain_verify" content="' . get_theme_mod('p_domain_verify') . '"/>' . "\n" : '';
} 
?>
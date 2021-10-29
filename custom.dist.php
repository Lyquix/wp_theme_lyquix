<?php
/**
 * custom.dist.php - Base template
 *
 * @version     2.3.3
 * @package     wp_theme_lyquix
 * @author      Lyquix
 * @copyright   Copyright (C) 2015 - 2018 Lyquix
 * @license     GNU General Public License version 2 or later
 * @link        https://github.com/Lyquix/wp_theme_lyquix
 */

// Initialize variables
require get_template_directory() . '/php/vars.php';

?><!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
<head>
<?php
// Meta tags
require get_template_directory() . '/php/meta.php';

// Prepare CSS
require get_template_directory() . '/php/css.php';

// Prepare JavaScript
require get_template_directory() . '/php/js.php';

// WordPress head
wp_head();

// Render CSS and JS
lqx_render_css();
lqx_render_js();

// Favicons
require get_template_directory() . '/php/favicon.php';

// head-scripts widget area
dynamic_sidebar('head-scripts');
?>
</head>
<?php
// Prepare <body> classes
require get_template_directory() . '/php/body.php';
?>
<body class="<?php echo implode(' ', $body_classes); ?>">
<?php
// Skip header area for blank page template
if($lqx_page_template != 'blank'): ?>
<header>
	<?php if(is_active_sidebar('header')) dynamic_sidebar('header'); ?>

	<?php if(has_nav_menu('primary-menu')): ?>
	<nav>
		<?php wp_nav_menu(array('menu' => 'primary-menu')); ?>
	</nav>
	<?php endif; ?>
</header>

<main>
	<?php if(is_active_sidebar('top')): ?>
	<section class="top">
	<?php dynamic_sidebar('top'); ?>
	</section>
	<?php endif; ?>

	<?php if(is_active_sidebar('left')): ?>
	<aside class="left">
	<?php dynamic_sidebar('left'); ?>
	</aside>
	<?php endif; ?>

	<article>
		<?php
		if(is_active_sidebar('before')) dynamic_sidebar('before');

		// End skip of header area for blank page template
		endif;

		// Template router
		require get_template_directory() . '/php/router.php';

		// Skip header area for blank page template
		if($lqx_page_template != 'blank'):

		if(is_active_sidebar('after')) dynamic_sidebar('after'); ?>
	</article>

	<?php if(is_active_sidebar('right')): ?>
	<aside class="right">
	<?php dynamic_sidebar('right'); ?>
	</aside>
	<?php endif; ?>

	<?php if(is_active_sidebar('bottom')): ?>
	<section class="bottom">
	<?php dynamic_sidebar('bottom'); ?>
	</section>
	<?php endif; ?>
</main>

<footer>
	<?php if(is_active_sidebar('footer')) dynamic_sidebar('footer'); ?>

	<?php if(has_nav_menu('footer-menu')): ?>
	<nav>
		<?php wp_nav_menu(array('menu' => 'footer-menu')); ?>
	</nav>
	<?php endif; ?>
</footer>
<?php
// Include IE alerts
require get_template_directory() . '/php/ie-alert.php';
// Add body closing tag code
require get_template_directory() . '/php/body-bottom.php';
?>
<?php
if(is_active_sidebar('body-scripts')) dynamic_sidebar('body-scripts');

// End skip of header area for blank page template
endif; ?>
<?php wp_footer(); ?>
</body>
</html>

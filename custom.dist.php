<?php
/**
 * custom.dist.php - Base template
 *
 * @version     2.0.0
 * @package     wp_theme_lyquix
 * @author      Lyquix
 * @copyright   Copyright (C) 2015 - 2018 Lyquix
 * @license     GNU General Public License version 2 or later
 * @link        https://github.com/Lyquix/wp_theme_lyquix
 */

// Initialize variables
require __DIR__ . '/php/vars.php';

?><!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
<head>
<?php
// Meta tags
require __DIR__ . '/php/meta.php';

// Prepare CSS
require __DIR__ . '/php/css.php';

// Prepare JavaScript
require __DIR__ . '/php/js.php';

// WordPress head
wp_head();

// Render CSS and JS
lqx_render_css();
lqx_render_js();

// Favicons
require __DIR__ . '/php/favicon.php';

// head-scripts widget area
dynamic_sidebar('head-scripts');
?>
</head>
<?php
// Prepare <body> classes
require __DIR__ . '/php/body.php';
?>
<body class="<?php echo implode(' ', $body_classes); ?>">
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

		// Template router
		require __DIR__ . '/php/router.php';

		if(is_active_sidebar('after')) dynamic_sidebar('after');
		?>
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
require __DIR__ . '/php/ie-alert.php';
?>
<?php if(is_active_sidebar('body-scripts')) dynamic_sidebar('body-scripts'); ?>
</body>
</html>

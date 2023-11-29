<?php

require(get_stylesheet_directory() . '/php/blocks/layout/layout.php');

if (file_exists(get_stylesheet_directory() . '/php/custom/blocks/layout/container/render.php')) {
	require_once get_stylesheet_directory() . '/php/custom/blocks/layout/container/render.php';
	\lqx\blocks\layout\container\render($classes);
} else {
?>
	<div class="container <?= $classes ?>">
		<InnerBlocks />
	</div>
<?php
}

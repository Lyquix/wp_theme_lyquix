<?php

require(get_stylesheet_directory() . '/php/blocks/layout/layout.php');

if (file_exists(get_stylesheet_directory() . '/php/custom/blocks/layout/box/render.php')) {
	require_once get_stylesheet_directory() . '/php/custom/blocks/layout/box/render.php';
	\lqx\blocks\layout\box\render($classes);
} else {
?>
	<div class="box-l <?= $classes ?>">
		<InnerBlocks />
	</div>
<?php
}

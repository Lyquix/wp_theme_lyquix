<?php

require(get_stylesheet_directory() . '/php/blocks/layout/layout.php');

if (file_exists(get_stylesheet_directory() . '/php/custom/blocks/layout/grid/render.php')) {
	require_once get_stylesheet_directory() . '/php/custom/blocks/layout/grid/render.php';
	\lqx\blocks\layout\grid\render($classes);
} else {
?>
	<div class="grid-l <?= $classes ?>">
		<InnerBlocks />
	</div>
<?php
}

<?php

require(get_stylesheet_directory() . '/php/blocks/layout/layout.php');

if (file_exists(get_stylesheet_directory() . '/php/custom/blocks/layout/cover/render.php')) {
	require_once get_stylesheet_directory() . '/php/custom/blocks/layout/cover/render.php';
	\lqx\blocks\layout\cover\render($classes);
} else {
?>
	<div class="cover-l <?= $classes ?>">
		<InnerBlocks />
	</div>
<?php
}

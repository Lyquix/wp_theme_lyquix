<?php

require(get_stylesheet_directory() . '/php/blocks/layout/layout.php');

if (file_exists(get_stylesheet_directory() . '/php/custom/blocks/layout/reel/render.php')) {
	require_once get_stylesheet_directory() . '/php/custom/blocks/layout/reel/render.php';
	\lqx\blocks\layout\reel\render($classes);
} else {
?>
	<div class="reel-l <?= $classes ?>">
		<InnerBlocks />
	</div>
<?php
}

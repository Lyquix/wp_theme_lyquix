<?php

require(get_stylesheet_directory() . '/php/blocks/layout/layout.php');

if (file_exists(get_stylesheet_directory() . '/php/custom/blocks/layout/frame/render.php')) {
	require_once get_stylesheet_directory() . '/php/custom/blocks/layout/frame/render.php';
	\lqx\blocks\layout\frame\render($classes);
} else {
?>
	<div class="frame-l <?= $classes ?>">
		<InnerBlocks />
	</div>
<?php
}

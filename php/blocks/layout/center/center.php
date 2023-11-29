<?php

require(get_stylesheet_directory() . '/php/blocks/layout/layout.php');

if (file_exists(get_stylesheet_directory() . '/php/custom/blocks/layout/center/render.php')) {
	require_once get_stylesheet_directory() . '/php/custom/blocks/layout/center/render.php';
	\lqx\blocks\layout\center\render($classes);
} else {
?>
	<div class="center-l <?= $classes ?>">
		<InnerBlocks />
	</div>
<?php
}

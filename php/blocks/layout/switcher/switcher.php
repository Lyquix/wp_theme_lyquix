<?php

require(get_stylesheet_directory() . '/php/blocks/layout/layout.php');

if (file_exists(get_stylesheet_directory() . '/php/custom/blocks/layout/switcher/render.php')) {
	require_once get_stylesheet_directory() . '/php/custom/blocks/layout/switcher/render.php';
	\lqx\blocks\layout\switcher\render($classes);
} else {
?>
	<div class="switcher-l <?= $classes ?>">
		<InnerBlocks />
	</div>
<?php
}

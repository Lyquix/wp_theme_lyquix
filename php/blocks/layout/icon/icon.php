<?php

require(get_stylesheet_directory() . '/php/blocks/layout/layout.php');

if (file_exists(get_stylesheet_directory() . '/php/custom/blocks/layout/icon/render.php')) {
	require_once get_stylesheet_directory() . '/php/custom/blocks/layout/icon/render.php';
	\lqx\blocks\layout\icon\render($classes);
} else {
?>
	<span class="icon-l <?= $classes ?>">
		<InnerBlocks />
	</span>
<?php
}

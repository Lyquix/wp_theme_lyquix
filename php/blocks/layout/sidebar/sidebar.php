<?php

require(get_stylesheet_directory() . '/php/blocks/layout/layout.php');

if (file_exists(get_stylesheet_directory() . '/php/custom/blocks/layout/sidebar/render.php')) {
	require_once get_stylesheet_directory() . '/php/custom/blocks/layout/sidebar/render.php';
	\lqx\blocks\layout\sidebar\render($classes);
} else {
?>
	<div class="sidebar-l <?= $classes ?>">
		<InnerBlocks />
	</div>
<?php
}

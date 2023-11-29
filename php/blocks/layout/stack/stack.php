<?php

require(get_stylesheet_directory() . '/php/blocks/layout/layout.php');

if (file_exists(get_stylesheet_directory() . '/php/custom/blocks/layout/stack/render.php')) {
	require_once get_stylesheet_directory() . '/php/custom/blocks/layout/stack/render.php';
	\lqx\blocks\layout\stack\render($classes);
} else {
?>
	<div class="stack-l <?= $classes ?>">
		<InnerBlocks />
	</div>
<?php
}

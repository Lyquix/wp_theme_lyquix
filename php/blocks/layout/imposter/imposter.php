<?php

require(get_stylesheet_directory() . '/php/blocks/layout/layout.php');

if (file_exists(get_stylesheet_directory() . '/php/custom/blocks/layout/imposter/render.php')) {
	require_once get_stylesheet_directory() . '/php/custom/blocks/layout/imposter/render.php';
	\lqx\blocks\layout\imposter\render($classes);
} else {
?>
	<div class="imposter-l <?= $classes ?>">
		<InnerBlocks />
	</div>
<?php
}

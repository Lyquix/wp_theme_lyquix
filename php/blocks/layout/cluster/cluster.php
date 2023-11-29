<?php

require(get_stylesheet_directory() . '/php/blocks/layout/layout.php');

if (file_exists(get_stylesheet_directory() . '/php/custom/blocks/layout/cluster/render.php')) {
	require_once get_stylesheet_directory() . '/php/custom/blocks/layout/cluster/render.php';
	\lqx\blocks\layout\cluster\render($classes);
} else {
?>
	<div class="cluster-l <?= $classes ?>">
		<InnerBlocks />
	</div>
<?php
}

<?php

require(get_stylesheet_directory() . '/php/blocks/layout/layout.php');
?>
<div id="<?= esc_attr($block['anchor']) ?>" class="center-l <?= $classes ?>">
	<InnerBlocks />
</div>

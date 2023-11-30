<?php

require(get_stylesheet_directory() . '/php/blocks/layout/layout.php');
?>
<span id="<?= esc_attr($block['anchor']) ?>" class="icon-l <?= $classes ?>">
	<InnerBlocks />
</span>

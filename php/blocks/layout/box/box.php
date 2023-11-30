<?php

require(get_stylesheet_directory() . '/php/blocks/layout/layout.php');

echo '<pre>' . print_r($block, true) . '</pre>';
?>
<div id="<?= esc_attr($block['anchor']) ?>" class="box-l <?= $classes ?>">
	<InnerBlocks />
</div>

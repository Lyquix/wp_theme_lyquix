<div class="text">

	<?php if ($item['heading']) include \lqx\blocks\get_template('cards', $preset, 'heading'); ?>

	<?php if ($s['show_subheading'] == 'y' && $item['subheading'])  include \lqx\blocks\get_template('cards', $preset, 'subheading'); ?>

	<?= $item['body'] ?>

	<?php if (count($item['links'])) include \lqx\blocks\get_template('cards', $preset, 'links'); ?>

</div>

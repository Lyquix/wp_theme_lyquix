<li
	class="<?= $s['slider'] == 'y' ? 'swiper-slide' : 'card' ?>"
	id="<?= $s['hash'] . '-' . $idx ?>">

	<?php if (!empty($item['labels'])) include \lqx\blocks\get_template('cards', $preset, 'labels'); ?>

	<?php if ($item['image']) include \lqx\blocks\get_template('cards', $preset, 'image'); ?>

	<?php if ($item['icon_image']) include \lqx\blocks\get_template('cards', $preset, 'icon-image'); ?>

	<?php include \lqx\blocks\get_template('cards', $preset, 'text'); ?>

</li>

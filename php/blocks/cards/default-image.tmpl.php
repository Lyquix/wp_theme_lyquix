<div class="image" <?= $video_attrs ?>>
	<?php if ($item['video']['type'] == 'upload' && $item['video']['upload']) : ?>
		<video
			autoplay loop muted playsinline
			poster="<?= $item['image']['sizes']['large'] ?>">
			<source
				src="<?= esc_attr($item['video']['upload']['url']) ?>"
				type="<?= esc_attr($item['video']['upload']['mime_type']) ?>">
		</video>
	<?php else: ?>
		<?= $s['image_clickable'] == 'y' && $first_link ? '<a href="' . esc_attr($first_link) . '">' : '' ?>
		<img
			src="<?= esc_attr($item['image']['sizes']['large']) ?>"
			alt="<?= esc_attr($item['image']['alt']) ?>">
		<?= $s['image_clickable'] == 'y' && $first_link ? '</a>' : '' ?>
	<?php endif; ?>
</div>

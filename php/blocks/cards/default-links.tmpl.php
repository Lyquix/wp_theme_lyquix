<ul class="links">
	<?php foreach ($item['links'] as $link) : ?>
		<li>
			<a
				class="<?= $link['type'] == 'button' ? 'button' : 'readmore' ?>"
				href="<?= esc_attr($link['link']['url']) ?>"
				target="<?= $link['link']['target'] ?>">
				<?= $link['link']['title'] ?>
			</a>
		</li>
	<?php endforeach; ?>
</ul>

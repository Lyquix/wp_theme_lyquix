<ul class="labels">
	<?php foreach ($item['labels'] as $label) : ?>
		<li
			data-label-value="<?= $label['value'] ? $label['value'] : \lqx\util\slugify($label['label']) ?>">
			<?= $label['label'] ?>
		</li>
	<?php endforeach; ?>
</ul>

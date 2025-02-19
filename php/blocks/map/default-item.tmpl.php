<?php if ($item['image']): ?>
	<div class="image">
	<?= ($s['items_display_settings']['image_clickable'] == 'y' && $item['link'] !== '' ? '<a href="' . $item['link']['url'] . '">' : '')?>
		<img
			src="<?= esc_attr($item['image']['url']) ?>"
			alt="<?= esc_attr($item['image']['alt']) ?>"
		/>
		<?= ($s['items_display_settings']['image_clickable'] == 'y' && $item['link'] !=='' ? '</a>' : '')?>
	</div>
<?php endif;?>
<div class="text">
	<<?= $s['items_display_settings']['heading_style'] ?>><?= ($s['items_display_settings']['heading_clickable'] == 'y' && $item['link'] !== '' ? '<a href="' . $item['link']['url'] . '">' : '')?><?= $item['title'] ?><?= ($s['items_display_settings']['heading_clickable'] == 'y' && $item['link'] !== '' ? '</a>' : '')?></<?= $s['items_display_settings']['heading_style'] ?>>
	<?php if ($item['subtitle'] != ''): ?><<?= $s['items_display_settings']['subtitle_style'] == 'p' ? 'p class="subheading"><strong' : $s['items_display_settings']['subtitle_style'] ?>>
		<?= $item['subtitle'] ?>
	</<?= $s['items_display_settings']['subtitle_style'] == 'p' ? 'strong></p' : $s['items_display_settings']['subtitle_style'] ?>>
	<?php endif; ?>
	<div class="address"><?= ($item['display_address'] !== '' ? $item['display_address'] : $item['address']) ?></div>
	<div class="phone-numbers">
		<?php foreach($item['phone_numbers'] as $phone): ?>
			<div><?= $phone['label']?>: <a href=tel:"<?= $phone['phone_number'] ?>"><?= $phone['phone_number'] ?></a></div>
		<?php endforeach; ?>
	</div>
	<?= $item['description'] ?>
	<?php if (is_array($item['business_hours']) && count($item['business_hours']) > 0) require \lqx\blocks\get_template('map', $s['preset'], 'office-hours'); ?>
	<?php	if ($s['show_get_directions_link'] == 'y'): ?><a href="https://maps.google.com/?q=<?=URLEncode($item['address'])?>">Get Directions</a><?php endif;?>
</div>

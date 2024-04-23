<section
	id="<?= esc_attr($s['anchor']) ?>"
	class="lqx-block-cards <?= esc_attr($s['class']) ?>">

	<div
		class="cards <?= implode(' ', $css_classes) ?>"
		id="<?= esc_attr($s['hash']) ?>"
		data-slider="<?= $s['slider'] ?>"
		data-swiper-options-override="<?= esc_attr($s['swiper_options_override']) ?>"
		data-heading-style="<?= $s['heading_style'] ?>"
		data-subheading-style="<?= $s['subheading_style'] ?>"
		data-heading-clickable="<?= $s['heading_clickable'] ?>"
		data-image-clickable="<?= $s['image_clickable'] ?>"
		data-responsive-rules="<?= esc_attr(json_encode($s['responsive_rules'])) ?>">

		<?= $s['slider'] == 'y' ? '<div class="swiper">' : '' ?>

			<ul class="<?= $s['slider'] == 'y' ? 'swiper-wrapper' : 'cards-wrapper' ?>">

				<?php foreach ($c as $idx => $item) :
					// Video attributes
					$video_attrs = '';
					if ($item['video']['type'] == 'url' && $item['video']['url']) {
						$video = \lqx\util\get_video_urls($item['video']['url']);
						if ($video['url']) $video_attrs = sprintf('data-lyqbox="%s"', htmlentities(json_encode([
							'name' => str_replace('id-', 'card-video-', $s['hash']) . '-' . $idx,
							'type' => 'video',
							'url' => $video['url'],
							'useHash' => false
						])));
					}

					// First link URL
					$first_link = '';
					if (count($item['links'])) {
						$first_link = $item['links'][0]['link']['url'];
					}
				?>

				<?php include \lqx\blocks\get_template('cards', $preset, 'item'); ?>

				<?php endforeach; ?>

			</ul>

			<?php if ($s['slider'] == 'y') include \lqx\blocks\get_template('cards', $preset, 'controls'); ?>

		<?= $s['slider'] == 'y' ? '</div>' : '' ?>

	</div>

</section>

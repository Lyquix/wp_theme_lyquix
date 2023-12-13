<?php

/**
 * Hero Banner Block Template
 *
 * @param array $block The block settings and attributes.
 *
 *
 */

namespace lqx\blocks\hero;

function render($settings, $content) {
	// Processed settings
	$s = $settings['processed'];

	// Video attributes
	$video_attrs = '';
	switch ($content['video']['type']) {
		case 'upload':
			if ($content['video']['upload']) {
				$video_attrs = ' data-lyqbox data-lyqbox-type="video" data-lyqbox-url="' . $content['video']['upload'] . '"';
			}
			break;
		case 'url':
			if ($content['video']['url']) {
				$video_data = \lqx\util\get_video_urls($content['video']['url']);
				if ($video_data['url']) $video_attrs = ' data-lyqbox data-lyqbox-type="video" data-lyqbox-url="' . $video_data['url'] . '"';
			}
			break;
	}

	// Breadcrumbs
	$breadcrumbs = '';
	if ($s['breadcrumbs']['show_breadcrumbs'] == 'y') {
		$breadcrumbs = '<div class="breadcrumbs">';
		if ($content['breadcrumbs_override'] !== '') {
			$breadcrumbs .= $content['breadcrumbs_override'];
		} else {
			$breadcrumbs .= implode(' &raquo; ', array_map(function ($b) {
				if ($b['url']) return sprintf('<a href="%s">%s</a>', $b['url'], $b['title']);
				else return $b['title'];
			}, \lqx\util\get_breadcrumbs(get_the_ID(), $s['breadcrumbs']['type'], $s['breadcrumbs']['depth'], $s['breadcrumbs']['show_current'])));
		}
		$breadcrumbs .= '</div>';
	}
?>
	<section
		id="<?= $s['anchor'] ?>"
		class="lqx-hero">
		<div
			class="hero"
			id="<?= $s['hash'] ?>"
			data-show-image="<?= $s['show_image'] ?>"
			data-show-breadcrumbs="<?= $s['breadcrumbs']['show_breadcrumbs'] ?>"
			data-breadcrumbs-type="<?= $s['breadcrumbs']['type'] ?>"
			data-breadcrumbs-depth="<?= $s['breadcrumbs']['depth'] ?>"
			data-breadcrumbs-show-current="<?= $s['breadcrumbs']['show_current'] ?>"
			>
			<div class="text">
				<?= $breadcrumbs ?>
				<h1 class="title"><?= $content['heading_override'] !== '' ? $content['heading_override'] : get_the_title() ?></h1>
				<div class="intro"><?= $content['intro_text'] ?></div>
				<?php if (count($content['links'])) : ?>
					<ul class="links">
						<?php foreach ($content['links'] as $link) : ?>
							<li>
								<a
									class="btn <?= $link['type'] == 'button' ? 'common-button' : 'readmore' ?>"
									href="<?= $link['link']['url'] ?>"
									target="<?= $link['link']['target'] ?>">
									<?php echo $link['link']['title']; ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
			<?php if ($s['show_image'] == 'y') : ?>
				<div class="image" <?= $video_attrs ?>>
					<?php if (is_array($content['image_override'])) : ?>
						<img
							src="<?= $content['image_override']['url'] ?>"
							alt="<?= $content['image_override']['alt'] ?>"
							class="<?= $content['image_mobile'] !== false ? 'xs-hide' : '' ?>" />
					<?php else :
						the_post_thumbnail('post-thumbnail', ['class' => $content['image_mobile'] !== false ? 'xs-hide' : '']);
					endif; ?>
					<?php if (is_array($content['image_mobile'])) : ?>
						<img
							src="<?php echo $content['image_mobile']['url']; ?>"
							alt="<?php echo $content['image_mobile']['alt']; ?>"
							class="hide xs-show" />
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</section>
<?php
}
?>

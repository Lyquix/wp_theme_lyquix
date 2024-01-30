<?php
/**
 * Hero Banner Block Template
 *
 * @param array $block The block settings and attributes.
 *
 *
 */
namespace lqx\modules\cta;

function render($settings = null, $content = null) {
	if ($settings == null) $settings = get_field('cta_module_settings', 'option');
	if ($content == null) $content = get_field('cta_module_content', 'option');

	// Check if there are any CTAs configured
	if (empty($content)) return;

	// Array to hold the active CTA
	$cta = null;

	// Iterate through CTAs and find the one that matches the current page
	foreach($content as $c) {
		// Skip if not enabled
		if ($c['enabled'] != 'y') continue;

		// Skip if display logic and exceptions are not met
		$display = true;
		if($c['display_logic'] == 'hide') $display = false;

		if (is_array($c['display_exceptions'])) {
			foreach($c['display_exceptions'] as $e) {
				// Escape the URL for use in regex
				$url_pattern = preg_replace('/[.+?{}()|[\]\\]/g', '\\$&', $e['url_pattern']);
				// Replace * with .*
				$url_pattern = str_replace('*', '.*', $url_pattern);
				// Check if the URL matches the pattern
				if (preg_match('^' . $url_pattern . '$', $_SERVER['REQUEST_URI'])) $display = !$display;
			}
		}
		if (!$display) continue;

		// Save the first CTA that matches the current page
		$cta = $c;
		break;
	}

	?>
	<section class="lqx-module-cta">
		<div class="cta <?= $cta['slim_cta'] == 'y' ? 'slim' : '' ?> <?= $cta['style'] ?>">
			<div class="image">
				<img src="<?php echo $cta['image']['url'];?>" alt="<?php echo $cta['image']['alt'];?>"/>
			</div>
			<div class="content">
				<<?= $cta['heading_style'] == 'p' ? 'p class="title"><strong' : $cta['heading_style'] ?>>
					<?= $cta['heading'] ?>
				</<?= $cta['heading_style'] == 'p' ? 'strong></p' : $cta['heading_style'] ?>>
				<?= $cta['content'] ?>
				<?php if (is_array($cta['links'])): ?>
					<ul class="links">
						<?php foreach($cta['links'] as $link):?>
							<li>
								<a
									class="<?= $link['type'] == 'button' ? 'button': 'readmore' ?>"
									href="<?= $link['link']['url'] ?>"
									target="<?= $link['link']['target'] ?>">
									<?= $link['link']['title'] ?>
								</a>
							</li>
						<?php endforeach;?>
					</ul>
				<?php endif;?>
			</div>
		</div>
	</section>
	<?php
}
?>

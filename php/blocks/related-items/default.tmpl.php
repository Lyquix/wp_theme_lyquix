<?php

/**
 * default.tmpl.php - Default template for the Lyquix Related Items block
 *
 * @version     3.2.0
 * @package     wp_theme_lyquix
 * @author      Lyquix
 * @copyright   Copyright (C) 2015 - 2024 Lyquix
 * @license     GNU General Public License version 2 or later
 * @link        https://github.com/Lyquix/wp_theme_lyquix
 */

?>
<section
	id="<?= esc_attr($s['anchor'] ?: $s['hash']) ?>"
	class="lqx-block-related-items <?= esc_attr($s['class']) ?>">

	<?php if (!empty($s['heading'])): ?>
		<<?= $s['heading_tag'] ?> class="related-items-heading"><?= esc_html($s['heading']) ?></<?= $s['heading_tag'] ?>>
	<?php endif; ?>

	<?php
	// Reuse the cards block to render the items
	$cards_settings = \lqx\blocks\get_settings('cards', null, $s['cards_preset'], $s['cards_style']);
	$cards_settings['processed']['hash'] = $s['hash'] . '-items';
	$cards_settings['processed']['class'] = 'posts ' . ($s['cards_style'] ?: '');
	$cards_settings['processed']['preset'] = $cards_settings['processed']['preset'] ?: $s['cards_preset'];

	\lqx\blocks\render_block($cards_settings, $posts);
	?>

</section>

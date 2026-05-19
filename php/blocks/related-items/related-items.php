<?php

/**
 * related-items.php - Lyquix Related Items block
 *
 * @version     3.2.0
 * @package     wp_theme_lyquix
 * @author      Lyquix
 * @copyright   Copyright (C) 2015 - 2024 Lyquix
 * @license     GNU General Public License version 2 or later
 * @link        https://github.com/Lyquix/wp_theme_lyquix
 */

// Get block settings
$settings = \lqx\blocks\get_settings($block);
$content = \lqx\blocks\get_content($block);

// Render the block
\lqx\blocks\render_block($settings, $content);

<?php

/**
 * render.php - Server-side render for the lqx/video block
 *
 * Receives $attributes, $content, $block from register_block_type via the
 * `render` field in block.json. Outputs lyqbox markup (URL videos or upload+
 * openInLyqbox) or an inline <video> (upload without openInLyqbox).
 *
 * @version     3.2.0
 * @package     wp_theme_lyquix
 * @author      Lyquix
 * @copyright   Copyright (C) 2015 - 2024 Lyquix
 * @license     GNU General Public License version 2 or later
 * @link        https://github.com/Lyquix/wp_theme_lyquix
 */

//    .d8888b. 88888888888 .d88888b.  8888888b.   888
//   d88P  Y88b    888    d88P" "Y88b 888   Y88b  888
//   Y88b.         888    888     888 888    888  888
//    "Y888b.      888    888     888 888   d88P  888
//       "Y88b.    888    888     888 8888888P"   888
//         "888    888    888     888 888         Y8P
//   Y88b  d88P    888    Y88b. .d88P 888          "
//   "Y8888P"     888     "Y88888P"  888         888
//
//  DO NOT MODIFY THIS FILE!

$video_type      = $attributes['videoType']      ?? 'url';
$video_url       = $attributes['videoUrl']       ?? '';
$upload_url      = $attributes['videoUploadUrl'] ?? '';
$upload_mime     = $attributes['videoUploadMime'] ?? '';
$poster_url      = $attributes['posterUrl']      ?? '';
$caption         = $attributes['caption']        ?? '';
$open_in_lyqbox  = !empty($attributes['openInLyqbox']);

$is_url    = $video_type === 'url';
$is_upload = $video_type === 'upload';
$is_lyqbox = $is_url || ($is_upload && $open_in_lyqbox);

// Determine the URL that will be opened (either YouTube/Vimeo URL or the upload URL)
$source_url = $is_url ? $video_url : $upload_url;

if ($source_url === '') return; // Nothing to render

$caption_html = $caption !== '' ? '<figcaption>' . wp_kses_post($caption) . '</figcaption>' : '';

if ($is_lyqbox) {
	// Poster: explicit override, or derived YouTube/Vimeo thumbnail (URL mode only)
	$poster = $poster_url;
	if (!$poster && $is_url) {
		$video_info = \lqx\util\get_video_urls($video_url);
		$poster = $video_info['thumbnail'] ?? '';
	}

	$lyqbox_data = htmlentities(json_encode([
		'name'    => 'lqx-video-' . substr(md5($source_url . uniqid('', true)), 0, 8),
		'type'    => 'video',
		'url'     => $source_url,
		'useHash' => false
	]));

	$poster_markup = $poster
		? '<img class="video-poster" src="' . esc_url($poster) . '" alt="" />'
		: '<span class="video-poster-placeholder" aria-hidden="true"></span>';

	$wrapper_attrs = get_block_wrapper_attributes([
		'class' => 'lqx-video-lyqbox'
	]);

	echo '<figure ' . $wrapper_attrs . ' data-lyqbox="' . $lyqbox_data . '">'
		. $poster_markup
		. $caption_html
		. '</figure>';
	return;
}

// Inline upload mode
$lazy_load     = !empty($attributes['lazyLoad']);
$hover_play    = !empty($attributes['hoverPlay']);
$viewport_play = !empty($attributes['viewportPlay']);
$controls      = !empty($attributes['controls']);
$autoplay      = !empty($attributes['autoplay']);
$loop          = !empty($attributes['loop']);
$muted         = !empty($attributes['muted']);

$video_classes = [];
if ($lazy_load)     $video_classes[] = 'lazyload-video';
if ($hover_play)    $video_classes[] = 'video-hover-play';
if ($viewport_play) $video_classes[] = 'video-viewport-play';

$bool_attrs = [];
if ($controls) $bool_attrs[] = 'controls';
if ($autoplay) $bool_attrs[] = 'autoplay';
if ($loop)     $bool_attrs[] = 'loop';
if ($muted)    $bool_attrs[] = 'muted';
$bool_attrs[] = 'playsinline';

$src_attr = $lazy_load ? 'data-src' : 'src';

$video_tag = '<video';
if (!empty($video_classes)) $video_tag .= ' class="' . esc_attr(implode(' ', $video_classes)) . '"';
$video_tag .= ' ' . implode(' ', $bool_attrs);
$video_tag .= ' preload="auto"';
if ($poster_url) $video_tag .= ' poster="' . esc_url($poster_url) . '"';
$video_tag .= ' ' . $src_attr . '="' . esc_url($upload_url) . '"';
if ($upload_mime) $video_tag .= ' type="' . esc_attr($upload_mime) . '"';
$video_tag .= '></video>';

$wrapper_attrs = get_block_wrapper_attributes([
	'class' => 'lqx-video-upload'
]);

echo '<figure ' . $wrapper_attrs . '>' . $video_tag . $caption_html . '</figure>';

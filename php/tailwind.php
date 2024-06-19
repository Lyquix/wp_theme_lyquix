<?php

/**
 * tailwind.php - Tailwind CSS integration
 *
 * @version     3.0.0
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
//    "Y8888P"     888     "Y88888P"  888         888
//
//  DO NOT MODIFY THIS FILE!

/**
 * Get Tailwind CSS classes from Gutenberg blocks
 *
 * @param array $block - The Gutenberg block
 * 		- Example: ['blockName' => 'core/paragraph', 'attrs' => ['data' => ['tailwind_text-center' => '1']]]
 * @param array $classes - The Tailwind CSS classes
 * 		- Example: ['text-center', 'bg-blue-500']
 *
 * @return array - The Tailwind CSS classes
 */
function collectClasses($block, $classes) {
	if (count($block['innerBlocks'])) {
		foreach ($block['innerBlocks'] as $innerBlock) {
			$classes = collectClasses($innerBlock, $classes);
		}
	}
	if (isset($block['attrs']['data'])) {
		foreach ($block['attrs']['data'] as $name => $val) {
			if (is_string($val)) {
				if (strpos($val, 'tailwind_') === 0) {
					$classes[] = str_replace('tailwind_', '', $val);
				} elseif (strpos($name, 'tailwind_') === 0 && $val) {
					$classes[] = str_replace('tailwind_', '', $name) . $val;
				}
			}
		}
	}
	return $classes;
}

if (get_theme_mod('feat_tailwind', '1') === '1') {
	//Enqueue tailwind cdn assets in the Editor.
	add_action('enqueue_block_editor_assets', function () {
		if (is_admin()) {
			wp_enqueue_script(
				'tailwind-cdn',
				'https://cdn.tailwindcss.com'
			);
			wp_enqueue_script(
				'tailwind-config',
				get_template_directory_uri() . '/css/tailwind/editor.cdn.js'
			);
			wp_enqueue_script(
				'tailwind-editor',
				get_template_directory_uri() . '/css/tailwind/editor.js',
				['wp-blocks', 'wp-data', 'wp-edit-post', 'acf-input', 'jquery']
			);
		}
	});

	/**
	 * Save Tailwind classes from Gutenberg to whitelist.html.
	 *
	 * @param int $post_id The ID of the post being saved.
	 * 		- The post ID is used to create a unique div ID in whitelist.html
	 * 		- The post content is used to extract Tailwind classes
	 * 		- The Tailwind classes are saved to whitelist.html
	 * 		- The whitelist.html file is used to generate the Tailwind CSS file
	 * 		- The Tailwind CSS file is loaded in the front-end
	 * 		- The Tailwind CSS file is used to style the Gutenberg blocks
	 * 		- The Tailwind CSS file is used to style the front-end
	 *
	 * @return void
	 * 		Saves Tailwind classes to whitelist.html
	 */
	add_action('save_post', function ($post_ID) {
		// Check if it's a revision, autosave, or if the save is an AJAX request (like Quick Edit)
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
		if (defined('DOING_AJAX') && DOING_AJAX) return;
		if (wp_is_post_revision($post_ID)) return;

		// Get the content of the post
		$post = get_post($post_ID);
		$content = $post->post_content;

		if ($content) {
			// Ensure it's a post type that uses Gutenberg
			if (!in_array($post->post_type, ['post', 'page'])) return;

			// Load the content into a DOMDocument
			$doc = new DOMDocument();
			@$doc->loadHTML($content);  // The '@' suppresses warnings that might be thrown for invalid HTML

			// Initialize an array to hold the classes
			$classes = [];

			// Find all the elements with a class attribute
			$xpath = new DOMXPath($doc);
			$elements = $xpath->query('//*[@class]');

			// Extract the classes from the block json

			$blocks = parse_blocks($content);

			foreach ($blocks as $block) {
				$classes = collectClasses($block, $classes);
			}

			// Extract the classes and add them to the array
			foreach ($elements as $element) {
				$class_string = $element->getAttribute('class');
				$class_array = explode(' ', $class_string);
				$classes = array_merge($classes, $class_array);
			}

			// Match the "className" attribute in all Gutenberg blocks
			preg_match_all('/"className":"(.*?)"/', $content, $matches);

			if (isset($matches[1])) {
				foreach ($matches[1] as $match) {
					// Split the classes and merge with our result array
					$classes = array_merge($classes, explode(' ', $match));
				}
			}

			// Remove duplicate classes
			$classes = array_unique($classes);
			// Create the string for the class attribute
			$class_string = implode(' ', $classes);

			// Load the existing whitelist.html content
			$whitelistPath = get_template_directory() . '/css/tailwind/whitelist.html';
			$whitelistContent = file_exists($whitelistPath) ? file_get_contents($whitelistPath) : '';

			// Create the new div string
			$newDiv = count($classes) ? "<div id=\"{$post_ID}\" class=\"{$class_string}\"></div>" : '';

			// Regex to find an existing div for this post
			$regex = "/<div id=\"{$post_ID}\" class=\"[^\"]*\"><\/div>/";

			if (preg_match($regex, $whitelistContent)) {
				// If the div exists, replace it
				$updatedContent = preg_replace($regex, $newDiv, $whitelistContent);
			} else {
				// If the div doesn't exist, append the new div
				$updatedContent = $whitelistContent . $newDiv;
			}

			// Save the updated HTML back to whitelist.html
			file_put_contents($whitelistPath, $updatedContent);
		}
	});

	/**
	 * Delete Tailwind classes from whitelist.html when a post is deleted.
	 *
	 * @param int $post_id The ID of the post being deleted.
	 * 		- The post ID is used to find the div in whitelist.html
	 * 		- The div is removed from whitelist.html
	 * 		- The Tailwind CSS file is regenerated
	 *
	 * @return void
	 * 		Removes Tailwind classes from whitelist.html
	 */
	add_action('delete_post', function ($post_ID) {
		// Load the existing whitelist.html content
		$whitelistPath = get_template_directory() . '/css/tailwind/whitelist.html';
		$whitelistContent = file_exists($whitelistPath) ? file_get_contents($whitelistPath) : '';

		// Regex to find the div for this post
		$regex = "/<div data-post=\"{$post_ID}\" class=\"[^\"]*\"><\/div>/";

		// Remove the div (if it exists)
		$updatedContent = preg_replace($regex, '', $whitelistContent);

		// Save the updated HTML back to whitelist.html
		file_put_contents($whitelistPath, $updatedContent);
	});

	//Remove version from Tailwind script
	add_filter('script_loader_tag', function ($tag, $handle, $src) {

		if (in_array($handle, ['tailwind-cdn'])) {
			// Use regular expression to remove the 'ver' query parameter
			$tag = preg_replace("/(\?|&amp;|&)ver=[^&'\" ]+/", "", $tag);
		}

		if (in_array($handle, ['tailwind-config'])) {
			// Modify script tag type attr to 'module'
			$tag = '<script type="module" src="' . $src . '"></script>';
		}

		return $tag;
	}, 10, 3);
}

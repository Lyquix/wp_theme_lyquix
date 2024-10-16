<?php

/**
 * search.dist.php - Default template for the search page
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
//  Instead, copy it to /php/custom/templates/search.php to override it

?>
<section class="search-content">
	<h1>Search Results</h1>
	<form role="search" method="get" class="search-form" action="<?php echo esc_url(home_url('/')); ?>">
		<input id="s" type="search" class="search-field" placeholder="Search" value="<?php echo get_search_query(); ?>" name="s" aria-label="Search" />
		<button type="submit" class="submit-button">Search</button>
	</form>

	<div class="search-results">
		<?php
		$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
		$posts_per_page = 10;
		$offset = ($paged - 1) * $posts_per_page;
		$args = array(
			's' => get_search_query(),
			'posts_per_page' => $posts_per_page,
			'paged' => $paged
		);
		$search_query = new WP_Query($args);
		$total_results = $search_query->found_posts;
		$start_result = $offset + 1;
		$end_result = min(($offset + $posts_per_page), $total_results);

		if ($search_query->have_posts()) : ?>
			<?php while ($search_query->have_posts()) : $search_query->the_post();
			?>
				<div class="search-result">
					<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
					<?php
					// If there is an excerpt, render it, otherwise render the first paragraph of the content
					if (has_excerpt()) {
						the_excerpt();
					} else {
						$content = get_the_content();
						$content = apply_filters('the_content', $content);
						$content = strip_shortcodes($content);
						$content = strip_tags($content);
						$content = mb_substr($content, 0, 300, 'UTF-8');
						if ($content) echo $content . '&hellip;';
					}
					?>
				</div>
			<?php
			endwhile;

			// Pagination
			$total_pages = $search_query->max_num_pages;
			if ($total_pages > 1) {

				the_posts_pagination(array(
					'format' => '?paged=%#%',
					'prev_text' => '&laquo;',
					'next_text' => '&raquo;'
				));
			} ?>

		<?php else : ?>
			<h2>Nothing Found</h2>
			<p>Sorry, but nothing matched your search terms. Please try again with some different keywords.</p>
			<p>Or visit our <a href="<?= get_bloginfo('url'); ?>">homepage</a>.</p>
		<?php
		endif;

		wp_reset_postdata();
		?>
	</div>
</section>

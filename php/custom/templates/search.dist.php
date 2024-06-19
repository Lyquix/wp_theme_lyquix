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
	<div class="search-form-wrapper">
		<?php get_search_form(); ?>
	</div>

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
			<h2>Showing <?= $start_result; ?>-<?= $end_result; ?> of <?= $total_results; ?> results for &lsquo;<?= get_search_query(); ?>&rsquo;</h2>
			<?php while ($search_query->have_posts()) : $search_query->the_post();
			?>
				<div class="search-result">
					<h3"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
					<?php the_excerpt(); ?>
				</div>
			<?php
			endwhile;

			// Pagination
			$total_pages = $search_query->max_num_pages;
			if ($total_pages > 1) {

				the_posts_pagination(array(
					'prev_text' => __(''),
					'next_text' => __(''),
				));
			} ?>

		<?php else : ?>
			<h2 class="h3">No results found for "<?= get_search_query(); ?>"</h2>
		<?php
		endif;

		wp_reset_postdata();
		?>
	</div>
</section>

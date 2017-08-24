<?php get_header(); ?>

	<section id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

		<?php if ( have_posts() ) : ?>

			<div class="page-header">
				<h1 class="page-title"><?php printf( __( 'Search Results for: %s', 'lqx' ), '<span>' . esc_html( get_search_query() ) . '</span>' ); ?></h1>
			</div><!-- .page-header -->

			<?php
			// Start the loop.
			while ( have_posts() ) : the_post();

				/**
				 * Run the loop for the search to output the results.
				 * If you want to overload this in a child theme then include a file
				 * called content-search.php and that will be used instead.
				 */
				get_template_part( 'template-parts/content', 'search' );

			// End the loop.
			endwhile;

			// Previous/next page navigation.
			the_posts_pagination( array(
				'prev_text'          => __( 'Previous page', 'lqx' ),
				'next_text'          => __( 'Next page', 'lqx' ),
				'before_page_number' => '<span class="meta-nav screen-reader-text">' . __( 'Page', 'lqx' ) . ' </span>',
			) );

		// If no content, include the "No posts found" template.
		else :
			get_template_part( 'template-parts/content', 'none' );

		endif;
		?>

		</main><!-- .site-main -->
	</section><!-- .content-area -->

<?php 
get_sidebar();
get_footer();
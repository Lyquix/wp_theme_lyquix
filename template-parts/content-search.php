<?php
/**
 * The template part for displaying results in search pages
 *
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<div class="entry-header">
		<?php the_title( sprintf( '<h2 class="entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' ); ?>
	</div><!-- .entry-header -->

	<?php lqx_post_thumbnail(); ?>

	<?php lqx_excerpt(); ?>

	<?php if ( 'post' === get_post_type() ) : ?>

		<div class="entry-footer">
			<?php lqx_entry_meta(); ?>
			<?php
				edit_post_link(
					sprintf(
						/* translators: %s: Name of current post */
						__( 'Edit<span class="screen-reader-text"> "%s"</span>', 'lqx' ),
						get_the_title()
					),
					'<span class="edit-link">',
					'</span>'
				);
			?>
		</div><!-- .entry-footer -->

	<?php else : ?>

		<?php
			edit_post_link(
				sprintf(
					/* translators: %s: Name of current post */
					__( 'Edit<span class="screen-reader-text"> "%s"</span>', 'lqx' ),
					get_the_title()
				),
				'<footer class="entry-footer"><span class="edit-link">',
				'</span></footer><!-- .entry-footer -->'
			);
		?>

	<?php endif; ?>
</article><!-- #post-## -->


<?php
/**
 * The template for displaying pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages and that
 * other "pages" on your WordPress site will use a different template.
 *
 * @package WordPress
 * @subpackage Twenty_Sixteen
 * @since Twenty Sixteen 1.0
 */
if(file_exists(__DIR__ . '/page-custom.php')) :
	include __DIR__ . '/page-custom.php'; 
else : 
get_header();?>
<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">
		<?php
		// Start the loop.
		while ( have_posts() ) : the_post();

			// Include the page content template.
			get_template_part( 'template-parts/content', 'page' );

			// If comments are open or we have at least one comment, load up the comment template.
			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}

			// End of the loop.
		endwhile;
		?>

	</main><!-- .site-main -->
	<?php $my_wp_query = new WP_Query();
	$post = get_post();
	$all_wp_pages = $my_wp_query->query(array('post_type' => 'page'));
	//print_r($all_wp_pages);
	get_page_children($post->ID , $all_wp_pages[0]); ?>
	<?php get_sidebar( 'content-bottom' ); ?>
	

</div><!-- .content-area -->

<?php get_sidebar(); ?>
<?php get_footer(); 
endif;?>

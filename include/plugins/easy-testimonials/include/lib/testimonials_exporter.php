<?php
class TestimonialsPlugin_Exporter
{	
	public function output_form()
	{
		?>
		<form method="POST" action="">			
			<p>Click the "Export My Testimonials" button below to download a CSV file of your testimonials.</p>
			<input type="hidden" name="_easy_t_do_export" value="_easy_t_do_export" />
			<p class="submit">
				<input type="submit" class="button" value="Export My testimonials" />
			</p>
		</form>
		<?php
	}
	
	public function process_export($filename = "testimonials-export.csv")
	{
		//load testimonials
		$args = array(
			'posts_per_page'   => -1,
			'offset'           => 0,
			'category'         => '',
			'category_name'    => '',
			'orderby'          => 'post_date',
			'order'            => 'DESC',
			'include'          => '',
			'exclude'          => '',
			'meta_key'         => '',
			'meta_value'       => '',
			'post_type'        => 'testimonial',
			'post_mime_type'   => '',
			'post_parent'      => '',
			'post_status'      => 'publish',
			'suppress_filters' => true 				
		);
		
		$testimonials = get_posts($args);
		
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header('Content-Description: File Transfer');
		header("Content-type: text/csv");
		header("Content-Disposition: attachment; filename={$filename}");
		header("Expires: 0");
		header("Pragma: public");
		
		
		// open file handle to STDOUT
		$fh = @fopen( 'php://output', 'w' );
		
		// output the headers first
		fputcsv($fh, array('Title','Body','Client Name','Position / Location / Other','Location / Product / Other','Rating','HTID'));
			
		// now output one row for each testimonial
		foreach($testimonials as $testimonial)
		{
			$title = $testimonial->post_title;
			$body = $testimonial->post_content;
			$client_name = get_post_meta( $testimonial->ID, '_ikcf_client', true);
			$position_location_other = get_post_meta( $testimonial->ID, '_ikcf_position', true);
			$location_product_other = get_post_meta( $testimonial->ID, '_ikcf_other', true);
			$rating = get_post_meta( $testimonial->ID, '_ikcf_rating', true);
			$htid = get_post_meta( $testimonial->ID, '_ikcf_htid', true);
			//TBD: category, image
			
			fputcsv($fh, array($title, $body, $client_name, $position_location_other, $location_product_other, $rating, $htid));		
		}
		
		// Close the file handle
		fclose($fh);
	}
}
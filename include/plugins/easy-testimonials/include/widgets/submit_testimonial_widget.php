<?php
/*
This file is part of Easy Testimonials.

Easy Testimonials is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Easy Testimonials is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Easy Testimonials.  If not, see <http://www.gnu.org/licenses/>.

Shout out to http://www.makeuseof.com/tag/how-to-create-wordpress-widgets/ for the help
*/

class submitTestimonialWidget extends WP_Widget
{
	function __construct(){
		$widget_ops = array('classname' => 'submitTestimonialWidget', 'description' => 'Displays a Testimonial Submission Form.' );
		parent::__construct('submitTestimonialWidget', 'Easy Testimonials Submit a Testimonial', $widget_ops);		
	}
		
	function submitTestimonialWidget()
	{
		$this->__construct();
	}

	function form($instance){	
		if(isValidKey()){			
			$instance = wp_parse_args( 
				(array) $instance, 
				array( 
					'title' => '',
					'submit_to_category' => ''
				) 
			);
			
			$title = $instance['title'];
			$submit_to_category = $instance['submit_to_category'];
					
			$testimonial_categories = get_terms( 'easy-testimonial-category', 'orderby=title&hide_empty=0' );
			?>
				<p class="hide_in_popup"><label for="<?php echo $this->get_field_id('title'); ?>">Widget Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" data-shortcode-hidden="1" /></label></p>
				<p>
					<label for="<?php echo $this->get_field_id('submit_to_category'); ?>">Submit to Category:</label><br/>			
					<select name="<?php echo $this->get_field_name('submit_to_category'); ?>" id="<?php echo $this->get_field_id('submit_to_category'); ?>">
						<option value="">No Category</option>
						<?php foreach($testimonial_categories as $cat):?>
						<option value="<?php echo $cat->slug; ?>" <?php if($submit_to_category == $cat->slug):?>selected="SELECTED"<?php endif; ?>><?php echo htmlentities($cat->name); ?></option>
						<?php endforeach; ?>
					</select>
					<br/>
					<p class="description">New Testimonial submissions will be automatically placed into this category when approved.</p>
					<em><a href="<?php echo admin_url('edit-tags.php?taxonomy=easy-testimonial-category&post_type=testimonial'); ?>">Manage Categories</a></em>
				</p>
			<?php
		} else {
			?>
			<p><strong>Please Note:</strong><br/> This Feature Requires Easy Testimonials Pro.</p>
			<p><a href="https://goldplugins.com/our-plugins/easy-testimonials-details/upgrade-to-easy-testimonials-pro/?utm_source=submit_testimonials_widget&utm_campaign=up
				grade" target="_blank">Upgrade to Pro</a></p>
			<?php
		}
	}

	function update($new_instance, $old_instance){
		$instance = $old_instance;
		$instance['title'] = $new_instance['title'];
		$instance['submit_to_category'] = $new_instance['submit_to_category'];		
		return $instance;
	}

	function widget($args, $instance){
		extract($args, EXTR_SKIP);

		echo $before_widget;
		$title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
		$submit_to_category = empty($instance['submit_to_category']) ? ' ' : $instance['submit_to_category'];

		if (!empty($title)){
			echo $before_title . $title . $after_title;;
		}
		
		$atts = array(
			'submit_to_category' => ( isset($submit_to_category) && strlen($submit_to_category) > 1 ) ? $submit_to_category : '',
		);
		
		echo submitTestimonialForm($atts);

		echo $after_widget;
	} 
}
?>
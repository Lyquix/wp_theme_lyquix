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

class randomTestimonialWidget extends WP_Widget
{
	function __construct(){
		$widget_ops = array('classname' => 'randomTestimonialWidget', 'description' => 'Displays a Random Testimonial.' );
		parent::__construct('randomTestimonialWidget', 'Easy Testimonials Random Testimonial', $widget_ops);		
	}
		
	function randomTestimonialWidget()
	{
		$this->__construct();
	}

	function form($instance){
		
		// load config
		$curr_dir = dirname(dirname(__FILE__));
		$config_path = $curr_dir . "/lib/config.php";
		include ( $config_path );
		$defaults = array(
			'title' => '',
			'count' => 5,
			'show_title' => 0,
			'category' => '',
			'use_excerpt' => 0,
			'show_rating' => false,
			'show_date' => false,
			'width' => false,
			'show_testimonial_image' => 0,
			'order' => 'ASC',
			'order_by' => 'date',
			'show_other' => 0,
			'theme' => get_option('testimonials_style', 'default_style'),
			'hide_view_more' => 0
		);
		$instance = wp_parse_args( (array) $instance, $defaults );
		$title = $instance['title'];
		$count = $instance['count'];
		$show_title = $instance['show_title'];
		$show_rating = $instance['show_rating'];
		$use_excerpt = $instance['use_excerpt'];
		$category = $instance['category'];
		$show_date = $instance['show_date'];
		$width = $instance['width'];
		$show_testimonial_image = $instance['show_testimonial_image'];
		$order = $instance['order'];
		$order_by = $instance['order_by'];
		$show_other = $instance['show_other'];
		$theme = $instance['theme'];
		$testimonial_categories = get_terms( 'easy-testimonial-category', 'orderby=title&hide_empty=0' );
		$hide_view_more = $instance['hide_view_more'];				
		$ip = isValidKey();
		?>
		<div class="gp_widget_form_wrapper">
			<p class="hide_in_popup">
				<label for="<?php echo $this->get_field_id('title'); ?>">Widget Title:</label>
				<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" data-shortcode-hidden="1" />
			</p>			
		
			<p>
				<label for="<?php echo $this->get_field_id('theme'); ?>">Theme:</label><br/>
				<select name="<?php echo $this->get_field_name('theme'); ?>" id="<?php echo $this->get_field_id('theme'); ?>">	
					<optgroup label="Free Themes">
					<?php foreach($free_theme_array as $key => $theme_name): ?>
						<option value="<?php echo $key ?>" <?php if($theme == $key): echo 'selected="SELECTED"'; endif; ?>><?php echo htmlentities($theme_name); ?></option>					
					<?php endforeach; ?>
					</optgroup>
					<?php foreach($pro_theme_array as $group_key => $theme_group): ?>
						<?php $group_label = $this->get_theme_group_label($theme_group); ?>
							<?php if (!$ip): ?>
							<optgroup  disabled="disabled" label="<?php echo htmlentities($group_label);?> (Pro)">
							<?php else: ?>
							<optgroup  label="<?php echo htmlentities($group_label);?>">
							<?php endif; ?>
							<?php foreach($theme_group as $key => $theme_name): ?>
								<?php if (!$ip): ?>
								<option disabled="disabled" value="<?php echo $key ?>" <?php if($theme == $key): echo 'selected="SELECTED"'; endif; ?>><?php echo htmlentities($theme_name); ?></option>
								<?php else: ?>
								<option value="<?php echo $key ?>" <?php if($theme == $key): echo 'selected="SELECTED"'; endif; ?>><?php echo htmlentities($theme_name); ?></option>
								<?php endif; ?>
							<?php endforeach; ?>
						</optgroup>
					<?php endforeach; ?>
				</select>
				<?php if (!$ip): ?>
				<br />
				<em><a target="_blank" href="http://goldplugins.com/our-plugins/easy-testimonials-details/upgrade-to-easy-testimonials-pro/?utm_source=wp_widgets&utm_campaign=widget_themes">Upgrade To Unlock All 75+ Pro Themes!</a></em>
				<?php endif; ?>
			</p>
			
			<p>
				<label for="<?php echo $this->get_field_id('width'); ?>">Width: </label><br />
				<input class="widefat" id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>" type="text" value="<?php echo esc_attr($width); ?>" /></label>
				<br/>
				<em>(e.g. 100px or 25%)</em>
			</p>

			<fieldset class="radio_text_input">
				<legend>Filter Testimonials:</legend> &nbsp;
				<div class="bikeshed bikeshed_radio">
					<p>
						<label for="<?php echo $this->get_field_id('category'); ?>">Category:</label><br/>			
						<select name="<?php echo $this->get_field_name('category'); ?>" id="<?php echo $this->get_field_id('category'); ?>">
							<option value="">All Categories</option>
							<?php foreach($testimonial_categories as $cat):?>
							<option value="<?php echo $cat->slug; ?>" <?php if($category == $cat->slug):?>selected="SELECTED"<?php endif; ?>><?php echo htmlentities($cat->name); ?></option>
							<?php endforeach; ?>
						</select>
						<br/>
						<em><a href="<?php echo admin_url('edit-tags.php?taxonomy=easy-testimonial-category&post_type=testimonial'); ?>">Manage Categories</a></em>
					</p>
					
					<p>
						<label for="<?php echo $this->get_field_id('count'); ?>">Count:</label>
						<input class="widefat" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>" type="text" value="<?php echo esc_attr($count); ?>" /></label>
						<br />
						<em>The number of Testimonials to display.  Set to -1 to display All Testimonials.</em>
					</p>
					
				</div>
			</fieldset>

		
			<fieldset class="radio_text_input">
				<legend>Fields To Display:</legend> &nbsp;
				<div class="bikeshed_radio">
					<p>
						<input class="widefat" id="<?php echo $this->get_field_id('show_title'); ?>" name="<?php echo $this->get_field_name('show_title'); ?>" type="checkbox" value="1" <?php if($show_title){ ?>checked="CHECKED"<?php } ?>/>
						<label for="<?php echo $this->get_field_id('show_title'); ?>">Show Testimonial Title</label>
					</p>
					
					<p>
						<input class="widefat" id="<?php echo $this->get_field_id('use_excerpt'); ?>" name="<?php echo $this->get_field_name('use_excerpt'); ?>" type="checkbox" value="1" <?php if($use_excerpt){ ?>checked="CHECKED"<?php } ?>/>
						<label for="<?php echo $this->get_field_id('use_excerpt'); ?>">Use Testimonial Excerpt</label>
					</p>	
					
					<p>
						<input class="widefat" id="<?php echo $this->get_field_id('show_testimonial_image'); ?>" name="<?php echo $this->get_field_name('show_testimonial_image'); ?>" type="checkbox" value="1" <?php if($show_testimonial_image){ ?>checked="CHECKED"<?php } ?> data-shortcode-key="show_thumbs" />
						<label for="<?php echo $this->get_field_id('show_testimonial_image'); ?>">Show Featured Image</label>
					</p>
					
					<p>
						<input class="widefat" id="<?php echo $this->get_field_id('show_date'); ?>" name="<?php echo $this->get_field_name('show_date'); ?>" type="checkbox" value="1" <?php if($show_date){ ?>checked="CHECKED"<?php } ?>/>
						<label for="<?php echo $this->get_field_id('show_date'); ?>">Show Testimonial Date</label>
					</p>
					
					<p>
						<input class="widefat" id="<?php echo $this->get_field_id('show_other'); ?>" name="<?php echo $this->get_field_name('show_other'); ?>" type="checkbox" value="1" <?php if($show_other){ ?>checked="CHECKED"<?php } ?>/>
						<label for="<?php echo $this->get_field_id('show_other'); ?>">Show "Location Reviewed / Product Reviewed / Item Reviewed" Field</label>
					</p>
					
					<p>
						<input class="widefat" id="<?php echo $this->get_field_id('hide_view_more'); ?>" name="<?php echo $this->get_field_name('hide_view_more'); ?>" type="checkbox" value="1" <?php if($hide_view_more){ ?>checked=""<?php } ?> data-shortcode-value-if-unchecked="0" />
						<label for="<?php echo $this->get_field_id('hide_view_more'); ?>">Hide View More Testimonials Link</label>
					</p>
				</div>
			</fieldset>

			<fieldset class="radio_text_input">
					<legend>Show Rating:</legend> &nbsp;
						<div class="bikeshed bikeshed_radio">
							<div class="radio_wrapper">
								<p class="radio_option"><label><input class="tog" name="<?php echo $this->get_field_name('show_rating'); ?>" type="radio" value="before" <?php if($show_rating=='before'){ ?>checked="checked"<?php } ?> > Before Testimonial</label></p>
								<p class="radio_option"><label><input class="tog" name="<?php echo $this->get_field_name('show_rating'); ?>" type="radio" value="after" <?php if($show_rating=='after'){ ?>checked="checked"<?php } ?> > After Testimonial</label></p>
								<p class="radio_option"><label><input class="tog" name="<?php echo $this->get_field_name('show_rating'); ?>" type="radio" value="stars" <?php if($show_rating=='stars'){ ?>checked="checked"<?php } ?> > As Stars</label></p>
								<p class="radio_option"><label><input class="tog" name="<?php echo $this->get_field_name('show_rating'); ?>" type="radio" value="" <?php if($show_rating==''){ ?>checked="checked"<?php } ?> > Do Not Show</label></p>
							</div>
						</div>						
						<br />
						<span style="padding-left:0px" class="description">Whether to show Ratings, and How.  If you are using a custom theme, make sure you follow the recommended settings here.</span>
					</p>
			</fieldset>
		</div>
		<?php
	}

	function update($new_instance, $old_instance){
		$instance = $old_instance;
		$instance['title'] = $new_instance['title'];
		$instance['count'] = $new_instance['count'];
		$instance['show_title'] = $new_instance['show_title'];
		$instance['use_excerpt'] = $new_instance['use_excerpt'];
		$instance['category'] = $new_instance['category'];
		$instance['show_rating'] = $new_instance['show_rating'];
		$instance['show_date'] = $new_instance['show_date'];
		$instance['width'] = $new_instance['width'];		
		$instance['show_testimonial_image'] = $new_instance['show_testimonial_image'];
		$instance['show_other'] = $new_instance['show_other'];
		$instance['theme'] = $new_instance['theme'];
		$instance['hide_view_more'] = $new_instance['hide_view_more'];
		
		return $instance;
	}

	function widget($args, $instance){
		global $easy_t_in_widget;
		$easy_t_in_widget = true;
		
		extract($args, EXTR_SKIP);

		echo $before_widget;
		$title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
		$count = empty($instance['count']) ? 1 : $instance['count'];
		$show_title = empty($instance['show_title']) ? 0 : $instance['show_title'];
		$use_excerpt = empty($instance['use_excerpt']) ? 0 : $instance['use_excerpt'];
		$category = empty($instance['category']) ? '' : $instance['category'];
		$show_rating = empty($instance['show_rating']) ? false : $instance['show_rating'];
		$show_date = empty($instance['show_date']) ? false : $instance['show_date'];
		$width = empty($instance['width']) ? false : $instance['width'];
		$show_testimonial_image = empty($instance['show_testimonial_image']) ? 0 : $instance['show_testimonial_image'];
		$show_other = empty($instance['show_other']) ? 0 : $instance['show_other'];
		$theme = empty($instance['theme']) ? '' : $instance['theme'];
		$hide_view_more = empty($instance['hide_view_more']) ? 0 : $instance['hide_view_more'];

		if (!empty($title)){
			echo $before_title . $title . $after_title;;
		}
		
		$args = array(
			'testimonials_link' => get_option('testimonials_link'),
			'count' => $count,
			'show_title' => $show_title,
			'category' => $category,
			'use_excerpt' => $use_excerpt,
			'show_rating' => $show_rating,
			'show_date' => $show_date,
			'width' => $width,
			'show_other' => $show_other,
			'show_thumbs' => $show_testimonial_image,
			'theme' => $theme,
			'hide_view_more' => $hide_view_more
		);
		echo outputRandomTestimonial( $args );

		echo $after_widget;
		
		$easy_t_in_widget = false;
	} 
	
	function get_theme_group_label($theme_group)
	{
		reset($theme_group);
		$first_key = key($theme_group);
		$group_label = $theme_group[$first_key];
		if ( ($dash_pos = strpos($group_label, ' -')) !== FALSE && ($avatar_pos = strpos($group_label, 'Avatar')) === FALSE ) {
			$group_label = substr($group_label, 0, $dash_pos);
		}
		return $group_label;
	}
}
?>
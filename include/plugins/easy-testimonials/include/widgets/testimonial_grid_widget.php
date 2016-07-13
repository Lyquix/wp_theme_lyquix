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

class TestimonialsGridWidget extends WP_Widget
{
	function __construct() {
		$widget_ops = array('classname' => 'TestimonialsGridWidget', 'description' => 'Display a Grid of your Testimonials.' );
		parent::__construct('TestimonialsGridWidget', 'Easy Testimonials Grid', $widget_ops);		
	}
	
	function TestimonialsGridWidget()
	{
		$this->__construct();
	}

	function form($instance) {
		
		// load config
		$curr_dir = dirname(dirname(__FILE__));
		$config_path = $curr_dir . "/lib/config.php";
		include ( $config_path );
		$defaults = array(
			'title' => '',
			'count' => 10,
			'show_title' => 0,
			'category' => '',
			'use_excerpt' => 0,
			'show_rating' => false,
			'show_date' => false,
			'cols' => 3,
			'show_testimonial_image' => 0,
			'order' => 'ASC',
			'order_by' => 'date',
			'show_other' => 0,
			'theme' => get_option('testimonials_style', 'default_style'),
			'paginate' => false,
			'testimonials_per_page' => 10,
			'hide_view_more' => 1
		);
		$instance = wp_parse_args( (array) $instance, $defaults );
		$title = $instance['title'];
		$count = $instance['count'];
		$show_title = $instance['show_title'];
		$show_rating = $instance['show_rating'];
		$use_excerpt = $instance['use_excerpt'];
		$category = $instance['category'];
		$show_date = isset($instance['show_date']) ? $instance['show_date'] : 1;
		$show_testimonial_image = isset($instance['show_testimonial_image']) ? $instance['show_testimonial_image'] : 1;
		$order = $instance['order'];
		$order_by = $instance['order_by'];
		$show_other = isset($instance['show_other']) ? $instance['show_other'] : 0;
		$theme = $instance['theme'];
		$paginate = $instance['paginate'];
		$testimonials_per_page = $instance['testimonials_per_page'];
		$testimonial_categories = get_terms( 'easy-testimonial-category', 'orderby=title&hide_empty=0' );				
		$cols = $instance['cols'];
		$grid_width = isset($instance['grid_width']) ? $instance['grid_width'] : '';
		$grid_spacing = isset($instance['grid_spacing']) ? $instance['grid_spacing'] : '';
		$grid_class = isset($instance['grid_class']) ? $instance['grid_class'] : '';
		$cell_width = isset($instance['cell_width']) ? $instance['cell_width'] : '';
		$responsive = isset($instance['responsive']) ? $instance['responsive'] : 1;
		$equal_height_rows = isset($instance['equal_height_rows']) ? $instance['equal_height_rows'] : 0;
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
				<label for="<?php echo $this->get_field_id('cols'); ?>">Number Of Columns: </label><br />				
				<select class="widefat" id="<?php echo $this->get_field_id('cols'); ?>" name="<?php echo $this->get_field_name('cols'); ?>">
				<?php foreach(range(1, 10) as $iCol): ?>
					<?php $sel_attr = ($cols == $iCol) ? 'selected="selected"' : ''; ?>
					<?php printf('<option value="%d" %s>%d</option>', $iCol, $sel_attr, $iCol); ?>
				<?php endforeach; ?>
				</select>
				<br/>
			</p>

			<fieldset class="radio_text_input">
				<legend>Advanced Options</legend> &nbsp;
				<div class="bikeshed bikeshed_radio">
					<p>
						<label for="<?php echo $this->get_field_id('grid_width'); ?>">Width of the Grid: </label><br />
						<input class="widefat" id="<?php echo $this->get_field_id('grid_width'); ?>" name="<?php echo $this->get_field_name('grid_width'); ?>" type="text" value="<?php echo esc_attr($grid_width); ?>" /></label>
						<br/>
						<em>e.g. 100px or 25%. Leave blank to use the default width.</em>
					</p>

					<p>
						<label for="<?php echo $this->get_field_id('cell_width'); ?>">Width of Each Cell: </label><br />
						<input class="widefat" id="<?php echo $this->get_field_id('cell_width'); ?>" name="<?php echo $this->get_field_name('cell_width'); ?>" type="text" value="<?php echo esc_attr($cell_width); ?>" /></label>
						<br/>
						<em>e.g. 100px or 25%. Leave blank to use the default width.</em>
					</p>
				
					<p>
						<label for="<?php echo $this->get_field_id('grid_spacing'); ?>">Spacing between each cell:</label><br />
						<input class="widefat" id="<?php echo $this->get_field_id('grid_spacing'); ?>" name="<?php echo $this->get_field_name('grid_spacing'); ?>" type="text" value="<?php echo esc_attr($grid_spacing); ?>" /></label>
						<br/>
						<em>e.g. 100px or 25%. Leave blank to use the default spacing.</em>
					</p>


					<p>
						<label for="<?php echo $this->get_field_id('grid_class'); ?>">CSS classes: </label><br />
						<input class="widefat" id="<?php echo $this->get_field_id('grid_class'); ?>" name="<?php echo $this->get_field_name('grid_class'); ?>" type="text" value="<?php echo esc_attr($grid_class); ?>" /></label>
						<br/>
						<em>Extra CSS classes to be applied to this grid.</em>
					</p>

					<p>
						<input name="<?php echo $this->get_field_name('responsive'); ?>" type="hidden" value="0" />
						<input class="widefat" id="<?php echo $this->get_field_id('responsive'); ?>" name="<?php echo $this->get_field_name('responsive'); ?>" type="checkbox" value="1" <?php if($responsive){ ?>checked="CHECKED"<?php } ?>/>
						<label for="<?php echo $this->get_field_id('responsive'); ?>">Responsive</label>
					</p>

					<p>
						<input name="<?php echo $this->get_field_name('equal_height_rows'); ?>" type="hidden" value="0" />
						<input class="widefat" id="<?php echo $this->get_field_id('equal_height_rows'); ?>" name="<?php echo $this->get_field_name('equal_height_rows'); ?>" type="checkbox" value="1" <?php if($equal_height_rows){ ?>checked="CHECKED"<?php } ?>/>
						<label for="<?php echo $this->get_field_id('equal_height_rows'); ?>">Make testimonials in each row the same height</label>
					</p>
				</div>
			</fieldset>

			<fieldset class="radio_text_input">
				<legend>Testimonials Per Page</legend> &nbsp;
				<div class="bikeshed bikeshed_radio">
					<p>
						<label>
							<input type="radio" name="<?php echo $this->get_field_name('paginate'); ?>" value="all" class="tog" <?php echo ($paginate == 'all' ? 'checked="checked"' : '');?>>All On One Page
						</label>
						<br/>
						<em>No pagination links will be displayed and all testimonials will be shown.</em>
					</p>
					<p>
						<label>
							<input type="radio" name="<?php echo $this->get_field_name('paginate'); ?>" value="max" class="tog" <?php echo ($paginate == 'max' ? 'checked="checked"' : '');?>>Max Per Page: 
						</label>
						<input type="text" name="<?php echo $this->get_field_name('testimonials_per_page'); ?>" id="<?php echo $this->get_field_id('testimonials_per_page'); ?>" class="small-text" value="<?php echo esc_attr($testimonials_per_page); ?>">
						<br/>
						<em>Pagination links will be displayed with this many testimonials shown per page.</em>
					</p>
					<p>
						<label>
							<input type="radio" name="<?php echo $this->get_field_name('paginate'); ?>" value="0" class="tog" <?php echo ($paginate == false ? 'checked="checked"' : '');?>>Specific Amount: 
						</label>
						<input type="text" name="<?php echo $this->get_field_name('count'); ?>" id="<?php echo $this->get_field_id('count'); ?>" class="small-text" value="<?php echo esc_attr($count); ?>">
						<br/>
						<em>No pagination links will be displayed and we will try to load exactly this many testimonials.</em>
					</p>
				</div>
			</fieldset>
			
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
						<label for="<?php echo $this->get_field_id('order'); ?>">Order:</label><br/>
						<select id="<?php echo $this->get_field_id('order_by'); ?>" name="<?php echo $this->get_field_name('order_by'); ?>" class="multi_left" data-shortcode-key="orderby">
							<option value="title" <?php if($order_by == "title"): ?>selected="SELECTED"<?php endif; ?>>Title</option>
							<option value="rand" <?php if($order_by == "rand"): ?>selected="SELECTED"<?php endif; ?>>Random</option>
							<option value="id" <?php if($order_by == "id"): ?>selected="SELECTED"<?php endif; ?>>ID</option>
							<option value="author" <?php if($order_by == "author"): ?>selected="SELECTED"<?php endif; ?>>Author</option>
							<option value="name" <?php if($order_by == "name"): ?>selected="SELECTED"<?php endif; ?>>Name</option>
							<option value="date" <?php if($order_by == "date"): ?>selected="SELECTED"<?php endif; ?>>Date</option>
							<option value="last_modified" <?php if($order_by == "last_modified"): ?>selected="SELECTED"<?php endif; ?>>Last Modified</option>
							<option value="parent_id" <?php if($order_by == "parent_id"): ?>selected="SELECTED"<?php endif; ?>>Parent ID</option>
						</select>
						<select id="<?php echo $this->get_field_id('order'); ?>" name="<?php echo $this->get_field_name('order'); ?>" class="multi_right">
							<option value="ASC" <?php if($order == "ASC"): ?>selected="SELECTED"<?php endif; ?>>Ascending (ASC)</option>
							<option value="DESC" <?php if($order == "DESC"): ?>selected="SELECTED"<?php endif; ?>>Descending (DESC)</option>
						</select>
					</p>
				</div>
			</fieldset>

		
			<fieldset class="radio_text_input">
				<legend>Fields To Display:</legend> &nbsp;
				<div class="bikeshed_radio">
					<p>
						<input name="<?php echo $this->get_field_name('show_title'); ?>" type="hidden" value="0" />
						<input class="widefat" id="<?php echo $this->get_field_id('show_title'); ?>" name="<?php echo $this->get_field_name('show_title'); ?>" type="checkbox" value="1" <?php if($show_title){ ?>checked="CHECKED"<?php } ?>/>
						<label for="<?php echo $this->get_field_id('show_title'); ?>">Show Testimonial Title</label>
					</p>
					
					<p>
						<input name="<?php echo $this->get_field_name('use_excerpt'); ?>" type="hidden" value="0" />
						<input class="widefat" id="<?php echo $this->get_field_id('use_excerpt'); ?>" name="<?php echo $this->get_field_name('use_excerpt'); ?>" type="checkbox" value="1" <?php if($use_excerpt){ ?>checked="CHECKED"<?php } ?>/>
						<label for="<?php echo $this->get_field_id('use_excerpt'); ?>">Use Testimonial Excerpt</label>
					</p>	
					
					<p>
						<input name="<?php echo $this->get_field_name('show_testimonial_image'); ?>" type="hidden" value="0" />
						<input class="widefat" id="<?php echo $this->get_field_id('show_testimonial_image'); ?>" name="<?php echo $this->get_field_name('show_testimonial_image'); ?>" type="checkbox" value="1" <?php if($show_testimonial_image){ ?>checked="CHECKED"<?php } ?> data-shortcode-key="show_thumbs" />
						<label for="<?php echo $this->get_field_id('show_testimonial_image'); ?>">Show Featured Image</label>
					</p>
					
					<p>
						<input name="<?php echo $this->get_field_name('show_date'); ?>" type="hidden" value="0" />
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
		$instance['show_rating'] = $new_instance['show_rating'];
		$instance['use_excerpt'] = $new_instance['use_excerpt'];
		$instance['category'] = $new_instance['category'];
		$instance['show_date'] = $new_instance['show_date'];	
		$instance['show_testimonial_image'] = $new_instance['show_testimonial_image'];
		$instance['order'] = $new_instance['order'];
		$instance['order_by'] = $new_instance['order_by'];
		$instance['show_other'] = $new_instance['show_other'];
		$instance['theme'] = $new_instance['theme'];
		$instance['cols'] = $new_instance['cols'];
		$instance['grid_width'] = isset($new_instance['grid_width']) ? $new_instance['grid_width'] : '';
		$instance['grid_spacing'] = isset($new_instance['grid_spacing']) ? $new_instance['grid_spacing'] : '';
		$instance['grid_class'] = isset($new_instance['grid_class']) ? $new_instance['grid_class'] : '';
		$instance['cell_width'] = isset($new_instance['cell_width']) ? $new_instance['cell_width'] : '';
		$instance['responsive'] = isset($new_instance['responsive']) ? $new_instance['responsive'] : 1;
		$instance['equal_height_rows'] = isset($new_instance['equal_height_rows']) ? $new_instance['equal_height_rows'] : 0;
		$instance['paginate'] = $new_instance['paginate'];
		$instance['testimonials_per_page'] = $new_instance['testimonials_per_page'];
		$instance['hide_view_more'] = $new_instance['hide_view_more'];
		
		return $instance;
	}

	function widget($args, $instance){
		global $easy_t_in_widget;
		$easy_t_in_widget = true;
		
		extract($args, EXTR_SKIP);

		echo $before_widget;
		$title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
		$count = empty($instance['count']) ? -1 : $instance['count'];
		$show_title = empty($instance['show_title']) ? 0 : $instance['show_title'];
		$show_rating = empty($instance['show_rating']) ? false : $instance['show_rating'];
		$use_excerpt = empty($instance['use_excerpt']) ? 0 : $instance['use_excerpt'];
		$category = empty($instance['category']) ? '' : $instance['category'];
		$show_date = empty($instance['show_date']) ? false : $instance['show_date'];
		$show_testimonial_image = empty($instance['show_testimonial_image']) ? 0 : $instance['show_testimonial_image'];
		$order = empty($instance['order']) ? 'ASC' : $instance['order'];
		$order_by = empty($instance['order_by']) ? 'date' : $instance['order_by'];
		$show_other = empty($instance['show_other']) ? 0 : $instance['show_other'];
		$theme = empty($instance['theme']) ? '' : $instance['theme'];
		$testimonials_link = empty($instance['testimonials_link']) ? get_option('testimonials_link') : $instance['testimonials_link'];
		$cols = empty($instance['cols']) ? 3 : $instance['cols'];
		$grid_width = isset($instance['grid_width']) ? $instance['grid_width'] : false;
		$grid_spacing = isset($instance['grid_spacing']) ? $instance['grid_spacing'] : false;
		$grid_class = isset($instance['grid_class']) ? $instance['grid_class'] : false;
		$cell_width = isset($instance['cell_width']) ? $instance['cell_width'] : false;
		$responsive = isset($instance['responsive']) ? $instance['responsive'] : false;
		$equal_height_rows = isset($instance['equal_height_rows']) ? $instance['equal_height_rows'] : false;
		$paginate =  empty($instance['paginate']) ? false : $instance['paginate'];
		$testimonials_per_page =  empty($instance['testimonials_per_page']) ? 10 : $instance['testimonials_per_page'];
		$hide_view_more = empty($instance['hide_view_more']) ? 1 : $instance['hide_view_more'];

		if (!empty($title)){
			echo $before_title . $title . $after_title;;
		}
		
		$cols = intval($cols);
		if ($cols < 1) {
				$cols = 1;
		}
		
		if ($cols > 10) {
			$cols = 10;
		}

		// ensure width has a unit
		if( strpos($grid_width, 'px') === FALSE && strpos($grid_width, 'em') === FALSE && strpos($grid_width, '%') === FALSE && strlen($grid_width) > 0 ) {
			$grid_width .= 'px';
		}
		
		$args = array(
			'testimonials_link' => $testimonials_link, 
			'count' => $count,
			'show_title' => $show_title,
			'category' => $category,
			'use_excerpt' => $use_excerpt,
			'show_rating' => $show_rating,
			'show_date' => $show_date,
			'show_other' => $show_other,
			'order' => $order,
			'orderby' => $order_by,
			'show_thumbs' => $show_testimonial_image,
			'theme' => $theme,
			'cols' => $cols,
			'grid_width' => $grid_width,
			'grid_spacing' => $grid_spacing,
			'grid_class' => $grid_class,
			'cell_width' => $cell_width,
			'responsive' => $responsive,
			'equal_height_rows' => $equal_height_rows,
			'paginate' => $paginate,
			'testimonials_per_page' => $testimonials_per_page,
			'hide_view_more' => $hide_view_more
		);
		echo easy_t_testimonials_grid_shortcode( $args );

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
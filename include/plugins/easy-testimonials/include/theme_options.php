<?php	
	//free and pro theme arrays are stored in config.php
	include("lib/config.php");
	
	//some functions for theme output
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
	
	//check for pro
	$ip = isValidKey();
	
	//load currently selected theme
	$theme = get_option('testimonials_style');
?>
		
		<form method="post" action="options.php"><?php
		
		if(!$ip): ?>
			<p class="plugin_is_not_registered"><a href="https://goldplugins.com/our-plugins/easy-testimonials-details/upgrade-to-easy-testimonials-pro/?utm_source=themes"><?php _e('Upgrade to Easy Testimonials Pro now', 'easy-testimonials');?></a> <?php _e('to unlock all 75+ themes!', 'easy-testimonials');?> </p>
		<?php endif; ?>
				
		<?php settings_fields( 'easy-testimonials-style-settings-group' ); ?>	
		
		<h3>Style &amp; Theme Options</h3>
		<p class="description">Select which style you want to use.  If 'No Style' is selected, only your Theme's CSS, and any Custom CSS you've added, will be used.</p>
				
		<table class="form-table easy_t_options">
			<tr>
				<td>
					<fieldset>
						<legend>Select Your Theme</legend>
						<select name="testimonials_style" id="testimonials_style">	
							<optgroup label="Free Themes">
							<?php foreach($free_theme_array as $key => $theme_name): ?>
								<option value="<?php echo $key ?>" <?php if($theme == $key): echo 'selected="SELECTED"'; endif; ?>><?php echo htmlentities($theme_name); ?></option>					
							<?php endforeach; ?>
							</optgroup>
							<?php foreach($pro_theme_array as $group_key => $theme_group): ?>
								<?php $group_label = get_theme_group_label($theme_group); ?>
									<?php if (!$ip): ?>
									<optgroup  label="<?php echo htmlentities($group_label);?> (Pro Required)">
									<?php else: ?>
									<optgroup  label="<?php echo htmlentities($group_label);?>">
									<?php endif; ?>
									<?php foreach($theme_group as $key => $theme_name): ?>
										<?php if (!$ip): ?>
										<option value="<?php echo $key ?>-disabled" <?php if($theme == $key): echo 'selected="SELECTED"'; endif; ?>><?php echo htmlentities($theme_name); ?></option>
										<?php else: ?>
										<option value="<?php echo $key ?>" <?php if($theme == $key): echo 'selected="SELECTED"'; endif; ?>><?php echo htmlentities($theme_name); ?></option>
										<?php endif; ?>
									<?php endforeach; ?>
								</optgroup>
							<?php endforeach; ?>
						</select>
					</fieldset>
					
					<h4>Preview Selected Theme</h4>
					<p class="description">Please note: your Theme's CSS may impact the appearance.</p>
					<p><strong>Current Saved Theme Selection:</strong>  <?php echo ucwords(str_replace('-', ' - ', str_replace('_',' ', str_replace('-style', '', $theme)))); ?></p>
					<div id="easy_t_preview" class="easy_t_preview">
						<p class="plugin_is_not_registered" style="display: none; margin-bottom: 20px;"><a href="https://goldplugins.com/our-plugins/easy-testimonials-details/upgrade-to-easy-testimonials-pro/?utm_source=themes_preview"><?php _e('This Theme Requires Pro! Upgrade to Easy Testimonials Pro now', 'easy-testimonials');?></a> <?php _e('to unlock all 75+ themes!', 'easy-testimonials');?> </p>
						<div class="style-<?php echo str_replace('-style', '', $theme); ?> easy_t_single_testimonial">
							<blockquote itemprop="review" itemscope itemtype="http://schema.org/Review" class="easy_testimonial" style="">
								<img class="attachment-easy_testimonial_thumb wp-post-image easy_testimonial_mystery_man" src="<?php echo plugins_url('/img/mystery_man.png', __FILE__);?>" />		
								<p itemprop="name" class="easy_testimonial_title">Support is second to none</p>	
								<div class="testimonial_body" itemprop="description">
									<p>Easy Testimonials is just what I have been looking for. A breeze to install, feature rich and simple to use in order to deliver what looks really sophisticated. What’s more, their support is second to none. I had a question with my install and the perfect answer came back in less than an hour.</p>
									<a href="https://goldplugins.com/testimonials/" class="easy_testimonials_read_more_link">Read More Testimonials</a>			
								</div>	
								<p class="testimonial_author">
									<cite>
										<span class="testimonial-client" itemprop="author" style="">Tom Evans</span>
										<span class="testimonial-position" style="">www.tomevans.co</span>
										<span class="testimonial-other" itemprop="itemReviewed">Easy Testimonials&nbsp;</span>
										<span class="date" itemprop="datePublished" content="Oct. 15, 2015" style="">May 28, 2015&nbsp;</span>
										<span itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating" class="stars">
											<meta itemprop="worstRating" content="1"/>
											<meta itemprop="ratingValue" content="5"/>
											<meta itemprop="bestRating" content="5"/>
											<span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span>			
										</span>	
									</cite>
								</p>	
							</blockquote>
						</div>
						<div class="easy-t-cycle-controls">				
							<div class="cycle-prev easy-t-cycle-prev">&lt;&lt; Previous</div>							<div class="easy-t-cycle-pager"><span class="">•</span><span class="">•</span><span class="">•</span><span class="cycle-pager-active">•</span><span class="">•</span></div>
										<div class="cycle-next easy-t-cycle-next">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Next &gt;&gt;</div>			
						</div>
					</div>
				</td>
			</tr>
		</table>
		
		<p class="submit">
			<input type="submit" id="easy_t_save_options" class="button-primary" value="<?php _e('Save Changes', 'easy-testimonials') ?>" />
		</p>
		
		</form>
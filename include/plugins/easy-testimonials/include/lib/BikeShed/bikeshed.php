<?php
// turning off namespace until WordPress bumps its requirements up to PHP 5.3
// namespace GoldPlugins;
if (!class_exists('Easy_Testimonials_GoldPlugins_BikeShed'))
{
	class Easy_Testimonials_GoldPlugins_BikeShed
	{
		var $font_sizes = array();
		var $font_families = array(
			"Arial, Helvetica, sans-serif;" 						=> 'Arial',
			"'Arial Narrow', sans-serif;" 							=> 'Arial Narrow',
			"'Arial Black', Gadget, sans-serif;" 					=> 'Arial Black',
			"Century Gothic, sans-serif;" 							=> 'Century Gothic',
			"Copperplate, Copperplate Gothic Light, sans-serif;" 	=> 'Copperplate',
			"'Courier New', Courier, monospace;" 					=> 'Courier New',
			"Georgia, serif;" 										=> 'Georgia',
			"Gill Sans, Gill Sans MT, sans-serif;" 					=> 'Gill Sans',
			"Impact, Charcoal, sans-serif;" 						=> 'Impact',
			"'Lucida Console', Monaco, monospace;" 					=> 'Lucida Console',
			"'Lucida Sans Unicode', 'Lucida Grande', sans-serif;" 	=> 'Lucida Sans Unicode',
			"Tahoma, Geneva, sans-serif;" 							=> 'Tahoma',
			"'Times New Roman', Times, serif;" 						=> 'Times New Roman',
			"'Trebuchet MS', Helvetica, sans-serif;" 				=> 'Trebuchet MS',
			"Verdana, Geneva, sans-serif;"							=> 'Verdana',
		);
		var $font_styles = array( 'Regular', 'Bold', 'Italic', 'Bold Italic' );
		var $google_fonts_api_key = '';

		function __construct()
		{
			$this->add_hooks();
			$this->init_font_sizes();
		}
		
		function add_hooks()
		{
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		}
		
		function init_font_sizes()
		{
			foreach (range(6, 72) as $font_size) {
				$this->font_sizes[] = $font_size;
			}		
		}

		function admin_enqueue_scripts($hook)
		{			
			//RWG: only enqueue scripts and styles on Easy T pages
			if(strpos($hook,'easy-testimonials')!==false){				
				wp_enqueue_script( 'wp-color-picker' );
				wp_enqueue_style( 'wp-color-picker' );
				
				$chosen_js = plugins_url('chosen/chosen.jquery.min.js', __FILE__);
				$chosen_css = plugins_url('chosen/chosen.min.css', __FILE__);

				$bikeshed_js = plugins_url('js/bikeshed.js', __FILE__);
				$bikeshed_css = plugins_url('css/bikeshed.css', __FILE__);

				wp_enqueue_script( 'chosen', $chosen_js, array( 'jquery' ), '1.0' );
				wp_enqueue_style( 'chosen', $chosen_css );

				wp_enqueue_script( 'bikeshed_js', $bikeshed_js, array( 'jquery', 'chosen' ), '1.0' );
				wp_enqueue_style( 'bikeshed_css', $bikeshed_css );			
			}
		}	

		function start_row($label = '', $label_for = '', $plain_label = false)
		{
		?>
				<tr valign="top">
					<th scope="row">
						<?php if (!empty($label) && !$plain_label):?>
						<label <?php echo (!empty($label_for) ? 'for="' . $label_for . '"' : '');?>><?php _e($label);?></label>
						<?php elseif (!empty($label) && $plain_label):?>
						<?php _e($label);?>
						<?php else: ?>
						&nbsp;
						<?php endif; ?>
					</th>
					<td>
		<?php						
		}

		function end_row()
		{
		?>
					</td>
				</tr>		
		<?php
		}

		function get_name_and_id($options, $key = '')
		{
			if (strlen($key) > 0) {
				$wildcard = (strpos($options['name'], '*') !== FALSE);
				$field_name = $wildcard ? ( str_replace('*', $key, $options['name']) ) : $options['name'] . '[' . $key . ']';
			}
			else {
				$field_name = $options['name'];
			}
			$field_id = str_replace(array('[',']'), '_', $field_name);
			return array($field_name, $field_id);
		}		

		// looks for inline inputs, of the form: {{text|FIELD_NAME}} or {{text|FIELD_NAME|VALUE}}
		// returns an array containing 2 values, $name and $inline_input
		// $name is the $input, with the inline input removed  (if one was found)
		// $inline_input is the inline input, reformatted as an HTML <input type="text" /> tag (if one was found)		
		// if no inline input is detected, $name will simply be $input, and $inline_input will be an empty string
		function extract_inline_input($input)
		{
			// init our return vars
			$name = $input;
			$inline_input = '';
			
			// look for inline inputs, of the form: {{text|FIELD_NAME}} or {{text|FIELD_NAME|VALUE}}
			$start_pos = strpos($name, '{{text|');
			$end_pos = ( $start_pos !== FALSE ? strpos($name, '}}', $start_pos) : FALSE );
			if ($start_pos !== FALSE && $end_pos !== FALSE)
			{
				// parse out the field name, and the value (value is optional)
				$inline_field_name = substr( $name, $start_pos + 7, $end_pos - ($start_pos + 7) );
				$parts = explode('|', $inline_field_name);
				
				// if a value was specified, break it out
				if (count($parts) > 1) {
					$inline_field_name = $parts[0];
					$value = $parts[1];
				} else {
					// value not specified; default it to ""
					$value = '';
				}
			
				// build the input (which we'll insert after we close the <label> tag)
				$inline_input .= '<input type="text" name="' . $inline_field_name . '" value="' . $value . '" />';
				
				// change the $name that will be output, removing the inline input tag
				$name = substr($name, 0, $start_pos) . substr($name, $end_pos + 2);
			}
			
			return array($name, $inline_input);
		}

		function merge_options_with_defaults($options, $defaults = false)
		{
			if ($defaults == FALSE) {
				$defaults = array( 'name' 			=> '',
								   'id'				=> '',
								   'label'			=> '',
								   'inline_label'	=> '',
								   'checked'		=> false,
								   'value'			=> '',
								   'values'			=> array(),
								   'default_color'	=> '',
								   'class'			=> '',
								   'disabled'		=> '',
							);
			}
			if (!is_array($options)) {
				$options = array('name' => $options);
			}
			return array_merge($defaults, $options);
		}
		
		// TODO: Load fonts from Google using their API. Will require a key.
		function load_google_fonts()
		{
			$fonts = get_transient('google_font_list');
			if ($fonts === FALSE) {
				$font_list = (array) json_decode(file_get_contents(dirname(__FILE__) . '/data/font_list.json'));
				$fonts = array_keys($font_list);
				set_transient( 'google_font_list', $fonts );
			}
			return $fonts;
		}
		
		function checkbox($options = array())
		{
			// init
			$options = $this->merge_options_with_defaults($options);
			list($field_name, $field_id) = $this->get_name_and_id($options);
			
			// start output
			$this->start_row($options['label'], '', true);
?>	
			<div class="bikeshed bikeshed_checkbox">
				<div class="checkbox_wrapper">
					<label for="<?php echo $field_name; ?>">
						<input type="checkbox" value="<?php echo $options['value']?>" name="<?php echo $field_name; ?>" id="<?php echo $field_id; ?>" <?php echo ($options['checked'] ? 'checked="checked"' : ''); ?> <?php echo ($options['disabled'] ? 'disabled="disabled"' : ''); ?>>
						<?php echo $options['inline_label']; ?>
					</label>
					<?php if(!empty($options['description'])): ?>
					<p class="description"><?php _e($options['description']);?></p>
					<?php endif; ?>				
				</div>
			</div>
<?php
			$this->end_row();
		}

		function radio($options = array())
		{
			// init
			$options = $this->merge_options_with_defaults($options);
			list($field_name, $field_id) = $this->get_name_and_id($options);
			
			// start output
			$this->start_row($options['label']);
?>	
			<div class="bikeshed bikeshed_radio">
				<div class="radio_wrapper">
					<?php foreach($options['options'] as $value => $name): ?>
					<?php list($name, $inline_input) = $this->extract_inline_input($name); ?>
					<p class="radio_option">
						<label>
							<input type="radio" <?php if($options['value'] == $value): echo 'checked="checked"'; endif; ?> class="tog" value="<?php echo $value ?>" name="<?php echo $field_name; ?>"  <?php echo ($options['disabled'] ? 'disabled="disabled"' : ''); ?>/>
							<?php echo $name; ?>
						</label>
						<?php echo $inline_input; ?>
					</p>
					<?php endforeach; ?>				
				</div>
				<?php if(!empty($options['description'])): ?>
				<p class="description"><?php _e($options['description']);?></p>
				<?php endif; ?>				
			</div>
<?php
			$this->end_row();
		}

		function textarea($options = array())
		{
			// init
			$options = $this->merge_options_with_defaults($options);
			list($field_name, $field_id) = $this->get_name_and_id($options);
			
			// start output
			$this->start_row($options['label']);
?>	
			<div class="bikeshed bikeshed_textarea">
				<div class="textarea_wrapper">
					<textarea name="<?php echo $field_name; ?>" id="<?php echo $field_id; ?>" rows="4" <?php echo ($options['disabled'] ? 'disabled="disabled"' : ''); ?>><?php echo htmlentities($options['value']); ?></textarea>
				</div>
				<?php if(!empty($options['description'])): ?>
				<p class="description"><?php _e($options['description']);?></p>
				<?php endif; ?>				
			</div>
<?php
			$this->end_row();
		}

		function text($options = array())
		{
			// init
			$options = $this->merge_options_with_defaults($options);
			list($field_name, $field_id) = $this->get_name_and_id($options);
			
			// start output
			$this->start_row($options['label']);
?>	
			<div class="bikeshed bikeshed_text">
				<div class="text_wrapper">
					<?php
						$class_str = implode(' ', explode(',', $options['class']));
						$class_attr = sprintf('class="%s"', $class_str);
					?>
					<input type="text" name="<?php echo $field_name; ?>" id="<?php echo $field_id; ?>" value="<?php echo htmlentities($options['value']); ?>" <?php echo $class_attr; ?> <?php echo ($options['disabled'] ? 'disabled="disabled"' : ''); ?> />
				</div>
				<?php if(!empty($options['description'])): ?>
				<p class="description"><?php _e($options['description']);?></p>
				<?php endif; ?>				
			</div>
<?php
			$this->end_row();
		}

		function select($options = array())
		{
			if (!is_array($options)) {
				$options = array('name' => $options);
			}
			$defaults = array( 'name' 	=> '',
							   'id'		=> '',
							   'label'	=> '',
							   'disabled'	=> false,
						);
			$options = array_merge($defaults, $options);
			
			$wildcard = (strpos($options['name'], '*') !== FALSE);
			$select_name = $wildcard ? ( str_replace('*', 'color', $options['name']) ) : $options['name'];
			$select_id = str_replace(array('[',']'), '_', $select_name);
			
			$this->start_row($options['label']);
?>	
			<div class="bikeshed bikeshed_select">
				<div class="select_wrapper">
					<select name="<?php echo $select_name; ?>" id="<?php echo $select_id; ?>" <?php echo ($options['disabled'] ? 'disabled="disabled"' : ''); ?>>	
						<?php foreach($options['options'] as $value => $name): ?>
						<option value="<?php echo $value; ?>" <?php if($options['value'] == $value): echo 'selected="selected"'; endif; ?>><?php echo $name; ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<?php if(!empty($options['description'])): ?>
				<p class="description"><?php _e($options['description']);?></p>
				<?php endif; ?>				
			</div>
<?php
			$this->end_row();
		}

		function color($options = array())
		{
			// init
			$options = $this->merge_options_with_defaults($options);
			list($color_name, $color_id) = $this->get_name_and_id($options);
			
			$this->start_row($options['label']);
?>	
			<div class="bikeshed bikeshed_color">
				<div class="color_picker_wrapper">
					<input data-default-color="<?php echo $options['default_color']; ?>" type="text" name="<?php echo $color_name; ?>" id="<?php echo $color_id; ?>" class="wp-color-picker" value="<?php echo $options['value']; ?> " <?php echo ($options['disabled'] ? 'disabled="disabled"' : ''); ?>/>
				</div>
				<?php if(!empty($options['description'])): ?>
				<p class="description"><?php _e($options['description']);?></p>
				<?php endif; ?>				
			</div>
<?php	
			$this->end_row();
		}
	
		function typography($options = array())
		{
			// init
			$options = $this->merge_options_with_defaults($options);
			
			list($font_size_name, $font_size_id) = $this->get_name_and_id($options, 'font_size');
			list($font_family_name, $font_family_id) = $this->get_name_and_id($options, 'font_family');
			list($font_style_name, $font_style_id) = $this->get_name_and_id($options, 'font_style');
			list($font_color_name, $font_color_id) = $this->get_name_and_id($options, 'font_color');
			
			// determine the current values			
			$values['font_size'] = !empty($options['values']['font_size'])? $options['values']['font_size'] : '';
			$values['font_family'] = !empty($options['values']['font_family'])? $options['values']['font_family'] : '';
			$values['font_style'] = !empty($options['values']['font_style'])? $options['values']['font_style'] : '';
			$values['font_color'] = !empty($options['values']['font_color'])? $options['values']['font_color'] : '';
			
			// we will need to propagate the "disabled" setting to all 4 inputs, so store it in a string
			$disabled_attr = $options['disabled'] ? 'disabled="disabled"' : '';
			
			$this->start_row($options['label']);
?>	
			<div class="bikeshed bikeshed_typography">
				<div class="select_wrapper font_size_select_wrapper">
					<select name="<?php echo $font_size_name?>" id="<?php echo $font_size_id?>" class="font_size_select" <?php echo $disabled_attr; ?>>
						<option value="" <?php echo (empty($font_size) ? ' selected="selected"' : ''); ?>>--</option>
						<?php foreach($this->font_sizes as $font_size): ?>
						<?php $selected = ($font_size == ($values['font_size'].'px')) ? ' selected="selected"' : ''; ?>
						<option value="<?=$font_size?>" <?=$selected?>><?=$font_size?>px</option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="select_wrapper font_family_select_wrapper">
					<select name="<?php echo $font_family_name?>" id="<?php echo $font_family_id?>" class="font_family_select" <?php echo $disabled_attr; ?>>
						<option value="" <?php echo (empty($font_family_name) ? ' selected="selected"' : ''); ?>>--</option>
						<?php if($options['google_fonts']): ?>
						<optgroup label="Standard Fonts">
						<?php endif; ?>
							<?php foreach($this->font_families as $family): ?>
							<?php $selected = ($family == $values['font_family']) ? ' selected="selected"' : ''; ?>
							<option value="<?=$family?>" <?=$selected?>><?=$family?></option>
							<?php endforeach; ?>
						<?php if($options['google_fonts']): ?>
						</optgroup>
						<?php endif; ?>

						<?php if($options['google_fonts']): ?>
						<optgroup label="Google Fonts">
							<?php foreach($this->load_google_fonts() as $family): ?>
							<?php $selected = ( 'google:'.$family == $values['font_family'] ) ? ' selected="selected"' : ''; ?>
							<option value="google:<?=$family?>" <?=$selected?>><?=$family?></option>
							<?php endforeach; ?>
						</optgroup>
						<?php endif; ?>
					</select>
				</div>
				<div class="select_wrapper font_style_select_wrapper">
					<select name="<?php echo $font_style_name?>" id="<?php echo $font_style_id?>" class="font_style_select" <?php echo $disabled_attr; ?>>
						<option value="" <?php echo (empty($font_style_name) ? ' selected="selected"' : ''); ?>>--</option>
						<?php foreach($this->font_styles as $style): ?>
						<?php $selected = ($style == $values['font_style']) ? ' selected="selected"' : ''; ?>
						<option value="<?=$style?>" <?=$selected?>><?=$style?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="color_picker_wrapper">
					<?php if (empty($options['default_color'])): ?>
					<input type="text" name="<?php echo $font_color_name; ?>" id="<?php echo $font_color_id; ?>" class="wp-color-picker" value="<?php echo $values['font_color']; ?>" <?php echo $disabled_attr; ?> />
					<?php else: ?>
					<input data-default-color="<?php echo $options['default_color']; ?>" type="text" name="<?php echo $font_color_name; ?>" id="<?php echo $font_color_id; ?>" class="wp-color-picker" value="<?php echo $values['font_color']; ?>" <?php echo $disabled_attr; ?> />
					<?php endif; ?>
				</div>
				<?php if(!empty($options['description'])): ?>
				<p class="description"><?php _e($options['description']);?></p>
				<?php endif; ?>				
			</div>
<?php	
			$this->end_row();
		}
	
	}

} // endif class_exists('Easy_Testimonials_GoldPlugins_BikeShed')
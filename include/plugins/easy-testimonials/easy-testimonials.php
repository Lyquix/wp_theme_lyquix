<?php
/*
Plugin Name: Easy Testimonials
Plugin URI: https://goldplugins.com/our-plugins/easy-testimonials-details/
Description: Easy Testimonials - Provides custom post type, shortcode, sidebar widget, and other functionality for testimonials.
Author: Gold Plugins
Version: 1.36.1
Author URI: https://goldplugins.com
Text Domain: easy-testimonials

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
along with Easy Testimonials .  If not, see <http://www.gnu.org/licenses/>.
*/

global $easy_t_footer_css_output;

require_once('include/lib/lib.php');
require_once('include/lib/BikeShed/bikeshed.php');
require_once("include/lib/testimonials_importer.php");
require_once("include/lib/testimonials_exporter.php");
require_once("include/lib/GP_Media_Button/gold-plugins-media-button.class.php");
require_once("include/lib/GP_Janus/gp-janus.class.php");

//setup JS
function easy_testimonials_setup_js() {
	$disable_cycle2 = get_option('easy_t_disable_cycle2');
	$use_cycle_fix = get_option('easy_t_use_cycle_fix');

	// register the recaptcha script, but only enqueue it later, when/if we see the submit_testimonial shortcode
	$recaptcha_lang = get_option('easy_t_recaptcha_lang', '');
	$recaptcha_js_url = 'https://www.google.com/recaptcha/api.js' . ( !empty($recaptcha_lang) ? '?hl='.urlencode($recaptcha_lang) : '' );
	wp_register_script(
			'g-recaptcha',
			$recaptcha_js_url
	);

	// register the grid-height script, but only enqueue it later, when/if we see the testimonials_grid shortcode with the auto_height option on
	wp_register_script(
			'easy-testimonials-grid',
			plugins_url('include/js/easy-testimonials-grid.js', __FILE__),
			array( 'jquery' )
	);
	
	if(!$disable_cycle2){
		wp_enqueue_script(
			'gp_cycle2',
			plugins_url('include/js/jquery.cycle2.min.js', __FILE__),
			array( 'jquery' ),
			false,
			true
		);  
		
		if(isValidKey()){  
			wp_enqueue_script(
				'easy-testimonials',
				plugins_url('include/js/easy-testimonials.js', __FILE__),
				array( 'jquery' ),
				false,
				true
			);
			wp_enqueue_script(
				'rateit',
				plugins_url('include/js/jquery.rateit.min.js', __FILE__),
				array( 'jquery' ),
				false,
				true
			);
		}
		
		if($use_cycle_fix){
			wp_enqueue_script(
				'easy-testimonials-cycle-fix',
				plugins_url('include/js/easy-testimonials-cycle-fix.js', __FILE__),
				array( 'jquery' ),
				false,
				true
			);
		}
	}
}

//add Testimonial CSS to header
function easy_testimonials_setup_css() {
	wp_register_style( 'easy_testimonial_style', plugins_url('include/css/style.css', __FILE__) );
	
	$cache_key = '_easy_t_testimonial_style';
	$style = get_transient($cache_key);
	if ($style == false) {
		$style = get_option('testimonials_style', 'x');
		set_transient($cache_key, $style);
	}

	// enqueue the base style unless "no_style" has been specified
	if($style != 'no_style') {
		wp_enqueue_style( 'easy_testimonial_style' );
	}

	// enqueue Pro CSS files
	if(isValidKey()) {
		easy_t_register_pro_themes();
	}	
}

function easy_t_register_pro_themes(){
	//five star ratings
	wp_register_style( 'easy_testimonial_rateit_style', plugins_url('include/css/rateit.css', __FILE__) );
	wp_enqueue_style( 'easy_testimonial_rateit_style' );
	
	//register and enqueue pro style
	wp_register_style( 'easy_testimonials_pro_style', plugins_url('include/css/easy_testimonials_pro.css', __FILE__) );
	wp_enqueue_style( 'easy_testimonials_pro_style' );
}

function easy_t_send_notification_email($submitted_testimonial = array()){
	//get e-mail address from post meta field
	//TBD: logic to use comma-separated e-mail addresses
	$email_addresses = explode(",", get_option('easy_t_submit_notification_address', get_bloginfo('admin_email')));
 
	$subject = "New Easy Testimonial Submission on " . get_bloginfo('name');
	
	//see if option is set to include testimonial in e-mail
	if(get_option('easy_t_submit_notification_include_testimonial')){ //option is set, build message containing testimonial
		$body = "You have received a new submission with Easy Testimonials on your site, " . get_bloginfo('name') . ".  Login to approve or trash it! \r\n\r\n";		
		
		$body .= "Title: {$submitted_testimonial['post']['post_title']} \r\n";
		$body .= "Body: {$submitted_testimonial['post']['post_content']} \r\n";
		$body .= "Name: {$submitted_testimonial['the_name']} \r\n";
		$body .= "Position/Web Address/Other: {$submitted_testimonial['the_other']} \r\n";
		$body .= "Location/Product Reviewed/Other: {$submitted_testimonial['the_other_other']} \r\n";
		$body .= "Rating: {$submitted_testimonial['the_rating']} \r\n";
	} else { //option isn't set, use default message
		$body = "You have received a new submission with Easy Testimonials on your site, " . get_bloginfo('name') . ".  Login and see what they had to say!";
	}
 
	//use this to set the From address of the e-mail
	$headers = 'From: ' . get_bloginfo('name') . ' <'.get_bloginfo('admin_email').'>' . "\r\n";
	
	//loop through available e-mail addresses and fire off the e-mails!
	foreach($email_addresses as $email_address){
		if(wp_mail($email_address, $subject, $body, $headers)){
			//mail sent!
		} else {
			//failure!
		}
	}
}
	
function easy_t_check_captcha() {
	
	
	if ( !class_exists('ReallySimpleCaptcha') && !easy_testimonials_use_recaptcha() ) {
		// captcha's cannot possibly be checked, so return true
		return true;
	} else {
		$captcha_correct = false; // false until proven correct		
	}
	
	// look for + verify a reCAPTCHA first
	if ( !empty($_POST["g-recaptcha-response"]) ) 
	{
		if ( !class_exists('EZT_ReCaptcha') ) {
			require_once ('include/lib/ezt_recaptchalib.php');
		}
		$secret = get_option('easy_t_recaptcha_secret_key', '');
		$response = null;
		if ( !empty($secret)  )
		{
			$reCaptcha = new EZT_ReCaptcha($secret);
			$response = $reCaptcha->verifyResponse(
				$_SERVER["REMOTE_ADDR"],
				$_POST["g-recaptcha-response"]
			);
			$captcha_correct = ($response != null && $response->success);
		}
	}
	else if ( !empty ($_POST['captcha_prefix']) && class_exists('ReallySimpleCaptcha') )
	{
		$captcha = new ReallySimpleCaptcha();
		// This variable holds the CAPTCHA image prefix, which corresponds to the correct answer
		$captcha_prefix = $_POST['captcha_prefix'];
		// This variable holds the CAPTCHA response, entered by the user
		$captcha_code = $_POST['captcha_code'];
		// This variable will hold the result of the CAPTCHA validation. Set to 'false' until CAPTCHA validation passes
		$captcha_correct = false;
		// Validate the CAPTCHA response
		$captcha_check = $captcha->check( $captcha_prefix, $captcha_code );
		// Set to 'true' if validation passes, and 'false' if validation fails
		$captcha_correct = $captcha_check;
		// clean up the tmp directory
		$captcha->remove($captcha_prefix);
		$captcha->cleanup();			
	}
	
	return $captcha_correct;
}	
	
function easy_t_outputCaptcha()
{
	if ( easy_testimonials_use_recaptcha() ) {
		?>
			<div class="g-recaptcha" data-sitekey="<?php echo htmlentities(get_option('easy_t_recaptcha_api_key', '')); ?>"></div>
			<br />		
		<?php
	}
	else if ( class_exists('ReallySimpleCaptcha') )
	{
		// Instantiate the ReallySimpleCaptcha class, which will handle all of the heavy lifting
		$captcha = new ReallySimpleCaptcha();
		 
		// ReallySimpleCaptcha class option defaults.
		// Changing these values will hav no impact. For now, these are here merely for reference.
		// If you want to configure these options, see "Set Really Simple CAPTCHA Options", below
		$captcha_defaults = array(
			'chars' => 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789',
			'char_length' => '4',
			'img_size' => array( '72', '24' ),
			'fg' => array( '0', '0', '0' ),
			'bg' => array( '255', '255', '255' ),
			'font_size' => '16',
			'font_char_width' => '15',
			'img_type' => 'png',
			'base' => array( '6', '18'),
		);
		 
		/**************************************
		* All configurable options are below  *
		***************************************/
		 
		//Set Really Simple CAPTCHA Options
		$captcha->chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
		$captcha->char_length = '4';
		$captcha->img_size = array( '100', '50' );
		$captcha->fg = array( '0', '0', '0' );
		$captcha->bg = array( '255', '255', '255' );
		$captcha->font_size = '16';
		$captcha->font_char_width = '15';
		$captcha->img_type = 'png';
		$captcha->base = array( '6', '18' );
		 
		/********************************************************************
		* Nothing else to edit.  No configurable options below this point.  *
		*********************************************************************/
		 
		// Generate random word and image prefix
		$captcha_word = $captcha->generate_random_word();
		$captcha_prefix = mt_rand();
		// Generate CAPTCHA image
		$captcha_image_name = $captcha->generate_image($captcha_prefix, $captcha_word);
		// Define values for CAPTCHA fields
		$captcha_image_url =  get_bloginfo('wpurl') . '/wp-content/plugins/really-simple-captcha/tmp/';
		$captcha_image_src = $captcha_image_url . $captcha_image_name;
		$captcha_image_width = $captcha->img_size[0];
		$captcha_image_height = $captcha->img_size[1];
		$captcha_field_size = $captcha->char_length;
		// Output the CAPTCHA fields
		?>
		<div class="easy_t_field_wrap">
			<img src="<?php echo $captcha_image_src; ?>"
			 alt="captcha"
			 width="<?php echo $captcha_image_width; ?>"
			 height="<?php echo $captcha_image_height; ?>" /><br/>
			<label for="captcha_code"><?php echo get_option('easy_t_captcha_field_label','Captcha'); ?></label><br/>
			<input id="captcha_code" name="captcha_code"
			 size="<?php echo $captcha_field_size; ?>" type="text" />
			<p class="easy_t_description"><?php echo get_option('easy_t_captcha_field_description','Enter the value in the image above into this field.'); ?></p>
			<input id="captcha_prefix" name="captcha_prefix" type="hidden"
			 value="<?php echo $captcha_prefix; ?>" />
		</div>
		<?php
	}
}

//handle file upload for image in front end submission form
function easy_t_upload_user_file( $file = array(), $post_id ) {
    
    require_once( ABSPATH . 'wp-admin/includes/admin.php' );
    
    $file_return = wp_handle_upload( $file, array('test_form' => false ) );
    
	// Set an array containing a list of acceptable formats
	$allowed_file_types = array('image/jpg','image/jpeg','image/gif','image/png');
	
    if( isset( $file_return['error'] ) || isset( $file_return['upload_error_handler'] ) ) {
        return false;
    } else {
	
		//only uploaded file types that are allowed
		if(in_array($file_return['type'], $allowed_file_types)) {
        
			$filename = $file_return['file'];
			
			$attachment = array(
				'post_mime_type' => $file_return['type'],
				'post_title' => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
				'post_content' => '',
				'post_status' => 'inherit',
				'guid' => $file_return['url']
			);
			
			$attachment_id = wp_insert_attachment( $attachment, $file_return['url'] );
			
			require_once (ABSPATH . 'wp-admin/includes/image.php' );
			$attachment_data = wp_generate_attachment_metadata( $attachment_id, $filename );
			wp_update_attachment_metadata( $attachment_id, $attachment_data );
			
			if( 0 < intval( $attachment_id ) ) {
				//make this the testimonial's featured image
				set_post_thumbnail( $post_id, $attachment_id );
				
				return $attachment_id;
			}
		} else {
			return false;
		}
    }
    
    return false;
}

function easy_testimonials_use_recaptcha()
{
	return ( 
		get_option('easy_t_use_captcha', 0)
		&& strlen( get_option('easy_t_recaptcha_api_key', '') ) > 0
		&& strlen( get_option('easy_t_recaptcha_secret_key', '') ) > 0
	);
}
	
//submit testimonial shortcode
function submitTestimonialForm($atts){
	//load shortcode attributes into an array
	$atts = shortcode_atts( array(
		'submit_to_category' => false,
		'testimonial_author_id' => get_option('easy_t_testimonial_author', 1),
	), $atts );
	
	extract($atts);

	// enqueue reCAPTCHA JS if needed
	if( easy_testimonials_use_recaptcha() ) {
		wp_enqueue_script('g-recaptcha');			
	}
	ob_start();
	
	// process form submissions
	$inserted = false;
   
	if( 'POST' == $_SERVER['REQUEST_METHOD'] && !empty( $_POST['action'] ) && $_POST['action'] == "post_testimonial" ) {
		if(isValidKey()){  
			$do_not_insert = false;
			
			if (isset ($_POST['the-title']) && strlen($_POST['the-title']) > 0) {
					$title =  wp_strip_all_tags($_POST['the-title']);
			} else {
					$title_error = '<p class="easy_t_error">' . get_option('easy_t_title_field_error','Please give ' . strtolower(get_option('easy_t_body_content_field_label','your testimonial')) . ' a ' . strtolower(get_option('easy_t_title_field_label','title')) . '.') . '</p>';
					$do_not_insert = true;
			}
		   
			if (isset ($_POST['the-body']) && strlen($_POST['the-body']) > 0) {
					$body = $_POST['the-body'];
			} else {
					$body_error = '<p class="easy_t_error">' . get_option('easy_t_body_field_error', 'Please enter ' . strtolower(get_option('easy_t_body_content_field_label','your testimonial')) . '.') . '</p>';
					$do_not_insert = true;
			}			
			
			if( get_option('easy_t_use_captcha',0) ){ 
				$correct = easy_t_check_captcha(); 
				if(!$correct){
					$captcha_error = '<p class="easy_t_error">' . get_option('easy_t_captcha_field_error', 'Captcha did not match.') . '</p>';
					$do_not_insert = true;
				}
			}
			
			if(isset($captcha_error) || isset($body_error) || isset($title_error)){
				echo '<p class="easy_t_error">' . get_option('easy_t_general_error', 'There was an error with your submission.  Please check the fields and try again.') . '</p>';
			}
		   
			if(!$do_not_insert){
				//snag custom fields
				$the_other = isset($_POST['the-other']) ? $_POST['the-other'] : '';
				$the_other_other = isset($_POST['the-other-other']) ? $_POST['the-other-other'] : '';
				$the_name = isset($_POST['the-name']) ? $_POST['the-name'] : '';
				$the_rating = isset($_POST['the-rating']) ? $_POST['the-rating'] : '';
				$the_email = isset($_POST['the-email']) ? $_POST['the-email'] : '';
				$the_category = isset($_POST['the-category']) ? $_POST['the-category'] : "";
				
				$tags = array();
			   
				$post = array(
					'post_title'    => $title,
					'post_content'  => $body,
					'post_category' => array(),  // custom taxonomies too, needs to be an array
					'tags_input'    => $tags,
					'post_status'   => 'pending',
					'post_type'     => 'testimonial',
					'post_author' 	=> $testimonial_author_id
				);
			
				$new_id = wp_insert_post($post);
				
				//set the testimonial category
				//TBD: handle multiple categories (should just be an array of term id's)
				
				//load the term id by the passed slug
				//this prevents someone from passing in a slug of their own creation and having that create a newly corresponding category
				//instead, it will load the id of the desired term and add that,
				//if no matching term is found, we just don't add this testimonial to a category!
				/* 
					Warning: string vs integer confusion! Field values, including term_id are returned in string format. Before further use, typecast numeric values to actual integers, otherwise WordPress will mix up term_ids and slugs which happen to have only numeric characters! 
				*/
				$testimonial_category_id = get_term_by('slug', $the_category, 'easy-testimonial-category');
				if( isset($testimonial_category_id->term_id) ){
					wp_set_object_terms($new_id, (int)$testimonial_category_id->term_id, 'easy-testimonial-category');
				}
			   
				//set the custom fields
				update_post_meta( $new_id, '_ikcf_client', $the_name );
				update_post_meta( $new_id, '_ikcf_position', $the_other );
				update_post_meta( $new_id, '_ikcf_other', $the_other_other );
				update_post_meta( $new_id, '_ikcf_rating', $the_rating );
				update_post_meta( $new_id, '_ikcf_email', $the_email );
			   
			   //collect info for notification e-mail
			   $submitted_testimonial = array(
					'post' => $post,
					'the_name' => $the_name,
					'the_other' => $the_other,
					'the_other_other' => $the_other_other,
					'the_rating' => $the_rating,
					'the_email' => $the_email
			   );
			   
				$inserted = true;
				
				//if the user has submitted a photo with their testimonial, handle the upload
				if( ! empty( $_FILES ) ) {
					foreach( $_FILES as $file ) {
						if( is_array( $file ) ) {
							$attachment_id = easy_t_upload_user_file( $file, $new_id );
						}
					}
				}
			}
		} else {
			echo "You must have a valid key to perform this action.";
		}
	}       
   
	$content = '';
   
	if(isValidKey()){ 		
		if($inserted){
			$redirect_url = get_option('easy_t_submit_success_redirect_url','');
			easy_t_send_notification_email($submitted_testimonial);
			if(strlen($redirect_url) > 2){
				echo '<script type="text/javascript">window.location.replace("'.$redirect_url.'");</script>';
			} else {					
				echo '<p class="easy_t_submission_success_message">' . get_option('easy_t_submit_success_message','Thank You For Your Submission!') . '</p>';
			}
		} else { ?>
		<!-- New Post Form -->
		<div id="postbox">
				<form id="new_post" class="easy-testimonials-submission-form" name="new_post" method="post" enctype="multipart/form-data" >
						<div class="easy_t_field_wrap <?php if(isset($title_error)){ echo "easy_t_field_wrap_error"; }//if a title wasn't entered add the wrap error class ?>">
							<?php if(isset($title_error)){ echo $title_error; }//if a title wasn't entered display a message ?>
							<label for="the-title"><?php echo get_option('easy_t_title_field_label','Title'); ?></label>
							<input type="text" id="the-title" value="<?php echo ( !empty($_POST['the-title']) ? htmlentities($_POST['the-title']) : ''); ?>" tabindex="1" size="20" name="the-title" />
							<p class="easy_t_description"><?php echo get_option('easy_t_title_field_description','Please give your Testimonial a Title.  *Required'); ?></p>
						</div>
						<?php if(!get_option('easy_t_hide_name_field',false)): ?>
						<div class="easy_t_field_wrap">
							<label for="the-name"><?php echo get_option('easy_t_name_field_label','Name'); ?></label>
							<input type="text" id="the-name" value="<?php echo ( !empty($_POST['the-name']) ? htmlentities($_POST['the-name']) : ''); ?>" tabindex="2" size="20" name="the-name" />
							<p class="easy_t_description"><?php echo get_option('easy_t_name_field_description','Please enter your Full Name.'); ?></p>
						</div>
						<?php endif; ?>
						<?php if(!get_option('easy_t_hide_email_field',false)): ?>
						<div class="easy_t_field_wrap">
							<label for="the-email"><?php echo get_option('easy_t_email_field_label','Your E-Mail Address'); ?></label>
							<input type="text" id="the-email" value="<?php echo ( !empty($_POST['the-email']) ? htmlentities($_POST['the-email']) : ''); ?>" tabindex="2" size="20" name="the-email" />
							<p class="easy_t_description"><?php echo get_option('easy_t_email_field_description','Please enter your e-mail address.  This information will not be publicly displayed.'); ?></p>
						</div>
						<?php endif; ?>
						<?php if(!get_option('easy_t_hide_position_web_other_field',false)): ?>
						<div class="easy_t_field_wrap">
							<label for="the-other"><?php echo get_option('easy_t_position_web_other_field_label','Position / Web Address / Other'); ?></label>
							<input type="text" id="the-other" value="<?php echo ( !empty($_POST['the-other']) ? htmlentities($_POST['the-other']) : ''); ?>" tabindex="3" size="20" name="the-other" />
							<p class="easy_t_description"><?php echo get_option('easy_t_position_web_other_field_description','Please enter your Job Title or Website address.'); ?></p>
						</div>
						<?php endif; ?>
						<?php if(!get_option('easy_t_hide_other_other_field',false)): ?>
						<div class="easy_t_field_wrap">
							<label for="the-other-other"><?php echo get_option('easy_t_other_other_field_label','Location / Product Reviewed / Other'); ?></label>
							<input type="text" id="the-other-other" value="<?php echo ( !empty($_POST['the-other-other']) ? htmlentities($_POST['the-other-other']) : ''); ?>" tabindex="3" size="20" name="the-other-other" />
							<p class="easy_t_description"><?php echo get_option('easy_t_other_other_field_description','Please enter your the name of the item you are Reviewing.');?>
						</div>
						<?php endif; ?>
						<?php //RWG: if set, add a hidden input for the submit_to_category value and hide the choice from the user ?>
						<?php if( isset($submit_to_category) && strlen($submit_to_category) > 2 ){ ?>
							<input type="hidden" id="the-category" name="the-category" value="<?php echo $submit_to_category; ?>" />
						<?php } else { ?>
						<?php $testimonial_categories = get_terms( 'easy-testimonial-category', 'orderby=title&hide_empty=0' ); ?>
						<?php if( !empty($testimonial_categories) && !get_option('easy_t_hide_category_field',false) ): ?>
						<div class="easy_t_field_wrap">
							<label for="the-category"><?php echo get_option('easy_t_category_field_label','Category'); ?></label>
							<select id="the-category" name="the-category">
								<?php
								foreach($testimonial_categories as $cat) {
									$sel_attr = ( !empty($_POST['the-category']) && $_POST['the-category'] == $cat->slug) ? 'selected="selected"' : '';
									printf('<option value="%s" %s>%s</option>', $cat->slug, $sel_attr, $cat->name);
								}
								?>
							</select>
							<p class="easy_t_description"><?php echo get_option('easy_t_category_field_description','Please select the Category that best matches your Testimonial.'); ?></p>
						</div>
						<?php endif; ?>
						<?php }//end check for sc attribute ?>
						<?php if(get_option('easy_t_use_rating_field',false)): ?>
						<div class="easy_t_field_wrap">
							<label for="the-rating"><?php echo get_option('easy_t_rating_field_label','Your Rating'); ?></label>
							<select id="the-rating" class="the-rating" tabindex="4" size="20" name="the-rating" >
								<?php 
								foreach(range(1, 5) as $rating) {
									$sel_attr = ( !empty($_POST['the-rating']) && $_POST['the-rating'] == $rating) ? 'selected="selected"' : '';
									printf('<option value="%d" %s>%d</option>', $rating, $sel_attr, $rating);
								}
								?>
							</select>
							<div class="rateit" data-rateit-backingfld=".the-rating" data-rateit-min="0"></div>
							<p class="easy_t_description"><?php echo get_option('easy_t_rating_field_description','1 - 5 out of 5, where 5/5 is the best and 1/5 is the worst.'); ?></p>
						</div>
						<?php endif; ?>
						<div class="easy_t_field_wrap <?php if(isset($body_error)){ echo "easy_t_field_wrap_error"; }//if a testimonial wasn't entered add the wrap error class ?>">
							<?php if(isset($body_error)){ echo $body_error; }//if a testimonial wasn't entered display a message ?>
							<label for="the-body"><?php echo get_option('easy_t_body_content_field_label','Your Testimonial'); ?></label>
							<textarea id="the-body" name="the-body" cols="50" tabindex="5" rows="6"><?php echo ( !empty($_POST['the-body']) ? htmlentities($_POST['the-body']) : ''); ?></textarea>
							<p class="easy_t_description"><?php echo get_option('easy_t_body_content_field_description','Please enter your Testimonial.  *Required'); ?></p>
						</div>							
						<?php if(get_option('easy_t_use_image_field',false)): ?>
						<div class="easy_t_field_wrap">
							<label for="the-image"><?php echo get_option('easy_t_image_field_label','Testimonial Image'); ?></label>
							<input type="file" id="the-image" value="" tabindex="6" size="20" name="the-image" />
							<p class="easy_t_description"><?php echo get_option('easy_t_image_field_description','You can select and upload 1 image along with your Testimonial.  Depending on the website\'s settings, this image may be cropped or resized.  Allowed file types are .gif, .jpg, .png, and .jpeg.'); ?></p>
						</div>
						<?php endif; ?>
						
						<?php 
							if( get_option('easy_t_use_captcha',0) ) {
								?><div class="easy_t_field_wrap <?php if(isset($captcha_error)){ echo "easy_t_field_wrap_error"; }//if a captcha wasn't correctly entered add the wrap error class ?>"><?php
								//if a captcha was entered incorrectly (or not at all) display message
								if(isset($captcha_error)){ echo $captcha_error; }
								easy_t_outputCaptcha();
								?></div><?php
							}
						?>
						
						<div class="easy_t_field_wrap"><input type="submit" value="<?php echo get_option('easy_t_submit_button_label','Submit Testimonial'); ?>" tabindex="7" id="submit" name="submit" /></div>
						<input type="hidden" name="action" value="post_testimonial" />
						<?php wp_nonce_field( 'new-post' ); ?>
				</form>
		</div>
		<!--// New Post Form -->
		<?php }
	   
		$content = ob_get_contents();
		ob_end_clean(); 
	}
   
	return apply_filters('easy_t_submission_form', $content);
}

//add Custom CSS
function easy_testimonials_setup_custom_css() {
	//use this to track if css has been output
	global $easy_t_footer_css_output;
	
	if($easy_t_footer_css_output){
		return;
	} else {
		echo '<style type="text/css" media="screen">' . get_option('easy_t_custom_css') . "</style>";
		$easy_t_footer_css_output = true;
	}
}

//display Testimonial Count
//$category is the slug of the category you want a count from
//if nothing is passed, displays count of all testimonials
//$status is the status of the testimonials to be included in the count
//defaults to published testimonials only
//if $aggregate_rating is set to true, this will output the aggregate rating markup for the counted testimonials
function easy_testimonials_count($category = '', $status = 'publish', $show_aggregate_rating = false){
	$tax_query = array();	
	
	//if a category slug was passed
	//only count testimonials within that category
	if(strlen($category)>0){
		$tax_query = array(
			array(
				'taxonomy' => 'easy-testimonial-category',
				'field' => 'slug',
				'terms' => $category
			)
		);
	}
	
	$args = array (
		'post_type' => 'testimonial',
		'tax_query' => $tax_query,
		'post_status' => $status,
		'nopaging' => true
	);
		
	$count_query = new WP_Query( $args );
	
	//if the option to show aggregate rating is toggling
	//construct and return the aggregate rating output
	//instead of just returning the numerical count
	if($show_aggregate_rating){		
		
		//calculate average review value
		$total_rating = 0;
		$total_rated_testimonials = 0;//only want to divide by the number of testimonials with actual ratings
		
		//TBD: allow control over item rating is displayed about
		$item_reviewed = get_option('easy_t_global_item_reviewed','');
		
		foreach ($count_query->posts as $testimonial){
			$testimonial_rating = get_post_meta($testimonial->ID, '_ikcf_rating', true);
			
			if(intval($testimonial_rating) > 0){				
				$total_rated_testimonials ++;
				$total_rating += $testimonial_rating;
			}
		}

		$average_rating = $total_rating / $total_rated_testimonials;
		
		$output = '
			<div class="easy_t_aggregate_rating_wrapper" itemscope itemtype="http://schema.org/Product">
				<span class="easy_t_aggregate_rating_item" itemprop="name">' . $item_reviewed . '</span>
				<div class="easy_t_aggregate_rating" itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">Rated <span class="easy_t_aggregate_rating_top_count" itemprop="ratingValue">' . round($average_rating, 2) . '</span>/5 based on <span itemprop="reviewCount" class="easy_t_aggregate_rating_review_count" >' . $total_rated_testimonials . '</span> customer reviews</div>		
			</div>
		';
		
		return apply_filters('easy_t_aggregate_rating', $output, $count_query);
	}
	
	//if we are down here, we aren't doing an aggregate rating
	//so return the count
	return apply_filters('easy_t_testimonials_count', $count_query->found_posts, $count_query);
}

//shortcode mapping function for easy_testimonials_count
//accepts three attributes, category and status and show_aggregate_rating
function outputTestimonialsCount($atts){
	//load shortcode attributes into an array
	extract( shortcode_atts( array(
		'category' => '',
		'status' => 'publish',
		'show_aggregate_rating' => false
	), $atts ) );
	
	return easy_testimonials_count($category, $status, $show_aggregate_rating);
}

if(!function_exists('word_trim')):
	function word_trim($string, $count, $ellipsis = FALSE)
	{
		$words = explode(' ', $string);
		if (count($words) > $count)
		{
			array_splice($words, $count);
			$string = implode(' ', $words);
			// trim of punctionation
			$string = rtrim($string, ',;.');	

			// add ellipsis if needed
			if (is_string($ellipsis)) {
				$string .= $ellipsis;
			} elseif ($ellipsis) {
				$string .= '&hellip;';
			}			
		}
		return $string;
	}
endif;

//load proper language pack based on current language
function easy_t_load_textdomain() {
	$plugin_dir = basename(dirname(__FILE__));
	load_plugin_textdomain( 'easy-testimonials', false, $plugin_dir . '/languages' );
}

//setup custom post type for testimonials
function easy_testimonials_setup_testimonials(){
	//include custom post type code
	include('include/lib/ik-custom-post-type.php');
	//include options code
	include('include/easy_testimonial_options.php');	
	$easy_testimonial_options = new easyTestimonialOptions();
			
	//setup post type for testimonials
	$postType = array('name' => 'Testimonial', 'plural' =>'Testimonials', 'slug' => 'testimonial', 'exclude_from_search' => !get_option('easy_t_show_in_search', true));
	$fields = array(); 
	$fields[] = array('name' => 'client', 'title' => 'Client Name', 'description' => "Name of the Client giving the testimonial.  Appears below the Testimonial.", 'type' => 'text');
	$fields[] = array('name' => 'email', 'title' => 'E-Mail Address', 'description' => "The client's e-mail address.  This field is used to check for a Gravatar, if that option is enabled in your settings.", 'type' => 'text'); 
	$fields[] = array('name' => 'position', 'title' => 'Position / Web Address / Other', 'description' => "The information that appears below the client's name.", 'type' => 'text');  
	$fields[] = array('name' => 'other', 'title' => 'Location Reviewed / Product Reviewed / Item Reviewed', 'description' => "The information that appears below the second custom field, Position / Web Address / Other.  Display of this field is required for proper structured data output.", 'type' => 'text');  
	$fields[] = array('name' => 'rating', 'title' => 'Rating', 'description' => "The client's rating, if submitted along with their testimonial.  This can be displayed below the client's position, or name if the position is hidden, or it can be displayed above the testimonial text.", 'type' => 'text');  
	$myCustomType = new ikTestimonialsCustomPostType($postType, $fields);
	register_taxonomy( 'easy-testimonial-category', 'testimonial', array( 'hierarchical' => true, 'label' => __('Testimonial Category', 'easy-testimonials'), 'rewrite' => array('slug' => 'testimonial-category', 'with_front' => true) ) ); 
	
	//load list of current posts that have featured images	
	$supportedTypes = get_theme_support( 'post-thumbnails' );
	
	//none set, add them just to our type
    if( $supportedTypes === false ){
        add_theme_support( 'post-thumbnails', array( 'testimonial' ) );       
		//for the testimonial thumb images    
	}
	//specifics set, add ours to the array
    elseif( is_array( $supportedTypes ) ){
        $supportedTypes[0][] = 'testimonial';
        add_theme_support( 'post-thumbnails', $supportedTypes[0] );
		//for the testimonial thumb images
    }
	//if neither of the above hit, the theme in general supports them for everything.  that includes us!
	
	add_image_size( 'easy_testimonial_thumb', 50, 50, true );
		
	add_action( 'admin_menu', 'easy_t_add_meta_boxes'); // add our custom meta boxes
}

function easy_t_add_meta_boxes(){
	add_meta_box( 'testimonial_shortcodes', 'Shortcodes', 'easy_t_display_shortcodes_meta_box', 'testimonial', 'side', 'default' );
}

//from http://codex.wordpress.org/Function_Reference/get_intermediate_image_sizes
function easy_t_output_image_options(){
	global $_wp_additional_image_sizes;
	$sizes = array();
	foreach( get_intermediate_image_sizes() as $s ){
		$sizes[ $s ] = array( 0, 0 );
		if( in_array( $s, array( 'thumbnail', 'medium', 'large' ) ) ){
			$sizes[ $s ][0] = get_option( $s . '_size_w' );
			$sizes[ $s ][1] = get_option( $s . '_size_h' );
		}else{
			if( isset( $_wp_additional_image_sizes ) && isset( $_wp_additional_image_sizes[ $s ] ) )
				$sizes[ $s ] = array( $_wp_additional_image_sizes[ $s ]['width'], $_wp_additional_image_sizes[ $s ]['height'], );
		}
	}

	$current_size = get_option('easy_t_image_size');
	
	foreach( $sizes as $size => $atts ){
		$disabled = '';
		$selected = '';
		$register = '';
		
		if(!isValidKey()){
			$disabled = 'disabled="DISABLED"';
			$current_size = 'easy_testimonial_thumb';
			$register = " - Register to Enable!";
		}
		if($current_size == $size){
			$selected = 'selected="SELECTED"';
			$disabled = '';
			$register = '';
		}
		echo "<option value='".$size."' ".$disabled . " " . $selected.">" . ucwords(str_replace("-", " ", str_replace("_", " ", $size))) . ' ' . implode( 'x', $atts ) . $register . "</option>";
	}
}
 
//this is the heading of the new column we're adding to the testimonial posts list
function easy_t_column_head($defaults) {  
	$defaults = array_slice($defaults, 0, 2, true) +
    array("single_shortcode" => "Shortcode") +
    array_slice($defaults, 2, count($defaults)-2, true);
    return $defaults;  
}  

//this content is displayed in the testimonial post list
function easy_t_columns_content($column_name, $post_ID) {  
    if ($column_name == 'single_shortcode') {  
		echo "<input type=\"text\" value=\"[single_testimonial id={$post_ID}]\" />";
    }  
} 

//this is the heading of the new column we're adding to the testimonial category list
function easy_t_cat_column_head($defaults) {  
	$defaults = array_slice($defaults, 0, 2, true) +
    array("single_shortcode" => "Shortcode") +
    array_slice($defaults, 2, count($defaults)-2, true);
    return $defaults;  
}  

//this content is displayed in the testimonial category list
function easy_t_cat_columns_content($value, $column_name, $tax_id) {  

	$category = get_term_by('id', $tax_id, 'easy-testimonial-category');
	
	return "<textarea>[testimonials category='{$category->slug}']</textarea>"; 
}

//load testimonials into an array and output a random one
function outputRandomTestimonial($atts){
	//load shortcode attributes into an array
	$atts = shortcode_atts( array(
		'testimonials_link' => get_option('testimonials_link'),
		'count' => 1,
		'word_limit' => false,
		'body_class' => 'testimonial_body',
		'author_class' => 'testimonial_author',
		'show_title' => 0,
		'short_version' => false,
		'use_excerpt' => false,
		'category' => '',
		'show_thumbs' => get_option('testimonials_image'),
		'show_rating' => false,
		'theme' => '',
		'show_date' => false,
		'show_other' => false,
		'width' => false,
		'hide_view_more' => 0
	), $atts );
	
	extract($atts);
		
	ob_start();
	
	//load testimonials into an array and output to the buffer
	$loop = new WP_Query(array( 'post_type' => 'testimonial','posts_per_page' => $count, 'easy-testimonial-category' => $category, 'orderby' => 'rand'));
	while($loop->have_posts()) : $loop->the_post();
		$postid = get_the_ID();	
		echo easy_t_get_single_testimonial_html($postid, $atts);
	endwhile;
	wp_reset_postdata();
	
	$content = ob_get_contents();
	ob_end_clean();
	
	return apply_filters('easy_t_random_testimonials_html', $content);
}

//output specific testimonial
function outputSingleTestimonial($atts){ 
	//load shortcode attributes into an array
	$atts = shortcode_atts( array(
		'testimonials_link' => get_option('testimonials_link'),
		'show_title' => 0,
		'body_class' => 'testimonial_body',
		'author_class' => 'testimonial_author',
		'id' => '',
		'use_excerpt' => false,
		'show_thumbs' => get_option('testimonials_image'),
		'short_version' => false,
		'word_limit' => false,
		'show_rating' => false,
		'theme' => '',
		'show_date' => false,
		'show_other' => false,
		'width' => false,
		'hide_view_more' => 0
	), $atts );
	
	extract($atts);
	
	ob_start();
	
	echo easy_t_get_single_testimonial_html($id, $atts);
	
	$content = ob_get_contents();
	ob_end_clean();	
	
	return apply_filters( 'easy_t_single_testimonial_html', $content);
}

//output all testimonials
function outputTestimonials($atts){ 
	
	//load shortcode attributes into an array
	$atts = shortcode_atts( array(	
		'testimonials_link' => '',//get_option('testimonials_link'),
		'show_title' => 0,
		'count' => -1,
		'body_class' => 'testimonial_body',
		'author_class' => 'testimonial_author',
		'id' => '',
		'use_excerpt' => false,
		'category' => '',
		'show_thumbs' => get_option('testimonials_image'),
		'short_version' => false,
		'orderby' => 'date',//'none','ID','author','title','name','date','modified','parent','rand','menu_order'
		'order' => 'ASC',//'DESC'
		'show_rating' => false,
		'paginate' => false,
		'testimonials_per_page' => 10,
		'theme' => '',
		'show_date' => false,
		'show_other' => false,
		'width' => false,
		'hide_view_more' => true
	), $atts );
	
	extract($atts);
			
	//if a bad value is passed for count, set it to -1 to load all testimonials
	//if $paginate is set to "all", this shortcode was made from a widget 
	//and we need to set the count to -1 to load all testimonials
	if(!is_numeric($count) || $paginate == "all"){
		$count = -1;
	}
	
	//if we are paging the testimonials, set the $count to the number of testimonials per page
	//sometimes $paginate is set, but is set to "all" (from the Widget) -
	//this indicates that we want to show every testimonial and not page them
	if($paginate && $paginate != "all"){
		$count = $testimonials_per_page;
	}
	
	ob_start();
	
	$i = 0;
	
	//query args
	$args = array( 'post_type' => 'testimonial','posts_per_page' => $count, 'easy-testimonial-category' => $category, 'orderby' => $orderby, 'order' => $order);
	
	// handle paging
	$nopaging = ($testimonials_per_page <= 0);

	$testimonial_page = 1;
	if ( get_query_var('testimonial_page') ) {
		$testimonial_page = get_query_var('testimonial_page');
	}	
	$paged = $testimonial_page;
	
	if (!$nopaging && $paginate && $paginate != "all") {
		//if $nopaging is false and $paginate is true, or max (but not "all"), then $testimonials_per_page is greater than 0 and the user is trying to paginate them
		//sometimes paginate is true, or 1, or max -- they all indicate the same thing.  "max" comes from the widget, true or 1 come from the shortcode / old instructions
		$args['posts_per_page'] = $testimonials_per_page;
		$args['paged'] = $paged;
	}
	
	//load testimonials into an array
	$loop = new WP_Query($args);
	while($loop->have_posts()) : $loop->the_post();
		$postid = get_the_ID();	
		echo easy_t_get_single_testimonial_html($postid, $atts);
	endwhile;	
	
	//output the pagination links, if instructed to do so
	//TBD: make all labels controllable via settings
	if($paginate){
		$pagination_link_template = get_pagination_link_template('testimonial_page');
		
		?>
		<div class="easy_t_pagination">                               
			<?php
			echo paginate_links( array(
				'base' => $pagination_link_template,
				'format' => '?testimonial_page=%#%',
				'current' => max( 1, $paged ),
				'total' => $loop->max_num_pages
			) );
			?>
		</div>  
		<?php
	}
	
	wp_reset_postdata();
	
	$content = ob_get_contents();
	ob_end_clean();	
	
	return apply_filters('easy_t_testimonials_html', $content);
}

function easy_t_add_pagination_query_var($query_vars)
{
	$query_vars[] = 'testimonial_page';		
	return $query_vars;
}	

/* 
 * Returns an URL template that can be passed as the 'base' param 
 * to WP's paginate_links function
 * 
 * Note: This function is based on WordPress' get_pagenum_link. 
 * It allows the query string argument to changed from 'paged'
 */
function get_pagination_link_template( $arg = 'testimonial_page' )
{
	$request = remove_query_arg( $arg );
	
	$home_root = parse_url(home_url());
	$home_root = ( isset($home_root['path']) ) ? $home_root['path'] : '';
	$home_root = preg_quote( $home_root, '|' );

	$request = preg_replace('|^'. $home_root . '|i', '', $request);
	$request = preg_replace('|^/+|', '', $request);

	$base = trailingslashit( get_bloginfo( 'url' ) );

	$result = add_query_arg( $arg, '%#%', $base . $request );
	$result = apply_filters( 'easy_t_get_pagination_link_template', $result );
	
	return esc_url_raw( $result );
}	

/*
 * Displays a grid of testimonials, with the requested number of columns
 *
 * @param array $atts Shortcode options. These include the [testimonial]
					  shortcode attributes, which are passed through.
 *
 * @return string HTML representing the grid of testimonials.
 */
function easy_t_testimonials_grid_shortcode($atts)
{
	// load shortcode attributes into an array
	// note: these are mostly the same attributes as [testimonials] shortcode
	$atts = shortcode_atts( array(
		'testimonials_link' => '',//get_option('testimonials_link'),
		'show_title' => 0,
		'count' => -1,
		'body_class' => 'testimonial_body',
		'author_class' => 'testimonial_author',
		'id' => '',
		'ids' => '', // i've heard it both ways
		'use_excerpt' => false,
		'category' => '',
		'show_thumbs' => NULL,
		'short_version' => false,
		'orderby' => 'date',//'none','ID','author','title','name','date','modified','parent','rand','menu_order'
		'order' => 'ASC',//'DESC'
		'show_rating' => false,
		'paginate' => false,
		'testimonials_per_page' => 10,
		'theme' => '',
		'show_date' => false,
		'show_other' => false,
		'width' => false,
		'cols' => 3, // 1-10
		'grid_width' => false,
		'grid_spacing' => false,
		'grid_class' => '',
		'cell_width' => false,
		'responsive' => true,
		'equal_height_rows' => false,
		'hide_view_more' => 0
	), $atts );
	
	extract( $atts );
	
	// allow ids or id to be passed in
	if ( empty($id) && !empty($ids) ) {
		$id = $ids;
	}
			
	//if a bad value is passed for count, set it to -1 to load all testimonials
	//if $paginate is set to "all", this shortcode was made from a widget 
	//and we need to set the count to -1 to load all testimonials
	if(!is_numeric($count) || $paginate == "all"){
		$count = -1;
	}
	
	//if we are paging the testimonials, set the $count to the number of testimonials per page
	//sometimes $paginate is set, but is set to "all" (from the Widget) -
	//this indicates that we want to show every testimonial and not page them
	if($paginate && $paginate != "all"){
		$count = $testimonials_per_page;
	}
	
	$testimonials_output = '';
	$col_counter = 1;
	$row_counter = 0;
	
	if ($equal_height_rows) {
		wp_enqueue_script('easy-testimonials-grid');
	}
	
	if ( empty($rows) ) {
		$rows  = -1;
	}
	
	// make sure $cols is between 1 and 10
	$cols = max( 1, min( 10, intval($cols) ) );
	
	// create CSS for cells (will be same on each cell)
	$cell_style_attr = '';
	$cell_css_rules = array();

	if ( !empty($grid_spacing) && intval($grid_spacing) > 0 ) {
		$coefficient = intval($grid_spacing) / 2;
		$unit = ( strpos($grid_spacing, '%') !== false ) ? '%' : 'px';
		$cell_margin = $coefficient . $unit;			
		$cell_css_rules[] = sprintf('margin-left: %s', $cell_margin);
		$cell_css_rules[] = sprintf('margin-right: %s', $cell_margin);			
	}

	if ( !empty($cell_width) && intval($cell_width) > 0 ) {
		$cell_css_rules[] = sprintf('width: %s', $cell_width);
	}

	$cell_style_attr = !empty($cell_css_rules) ? sprintf('style="%s"', implode(';', $cell_css_rules) ) : '';
	
	// combine the rules into a re-useable opening <div> tag to be used for each cell
	$cell_div_start = sprintf('<div class="easy_testimonials_grid_cell" %s>', $cell_style_attr);
	
	// grab all requested testimonials and build one cell (in HTML) for each
	// note: using WP_Query instead of get_posts in order to respect pagination
	//    	 more info: http://wordpress.stackexchange.com/a/191934
	$args = array(
		'post_type' => 'testimonial',
		'posts_per_page' => $count,
		'easy-testimonial-category' => $category,
		'orderby' => $orderby,
		'order' => $order
	);
	
	// handle paging
	$nopaging = ($testimonials_per_page <= 0);
	$paged = !empty($_REQUEST['testimonial_page']) && intval($_REQUEST['testimonial_page']) > 0 ? intval($_REQUEST['testimonial_page']) : 1;
	if (!$nopaging && $paginate && $paginate != "all") {
		//if $nopaging is false and $paginate is true, or max (but not "all"), then $testimonials_per_page is greater than 0 and the user is trying to paginate them
		//sometimes paginate is true, or 1, or max -- they all indicate the same thing.  "max" comes from the widget, true or 1 come from the shortcode / old instructions
		$args['posts_per_page'] = $testimonials_per_page;
		$args['paged'] = $paged;
	}
	
	// restrict to specific posts if requested
	if ( !empty($id) ) {
		$args['post__in'] = array_map('intval', explode(',', $id));
	}
	
	$loop = new WP_Query($args);
	$in_row = false;
	while( $loop->have_posts() ) {
		$loop->the_post();

		if ($col_counter == 1) {
			$in_row = true;
			$row_counter++;
			$testimonials_output .= sprintf('<div class="easy_testimonials_grid_row easy_testimonials_grid_row_%d">', $row_counter);
		}
				
		$testimonials_output .= $cell_div_start;
	
		$postid = get_the_ID();
		$testimonials_output .= easy_t_get_single_testimonial_html($postid, $atts);
		
		$testimonials_output .= '</div>';

		if ($col_counter == $cols) {
			$in_row = false;
			$testimonials_output .= '</div><!--easy_testimonials_grid_row-->';
			$col_counter = 1;
		} else {
			$col_counter++;
		}
	} // endwhile;
	
	// close any half finished rows
	if ($in_row) {
		$testimonials_output .= '</div><!--easy_testimonials_grid_row-->';
	}
	
	//output the pagination links, if instructed to do so
	//TBD: make all labels controllable via settings
	if($paginate){
		$pagination_link_template = get_pagination_link_template('testimonial_page');
		
		$testimonials_output .= '<div class="easy_t_pagination">';                           
		$testimonials_output .= paginate_links(array(
									'base' => $pagination_link_template,
									'format' => '?testimonial_page=%#%',
									'current' => max( 1, $paged ),
									'total' => $loop->max_num_pages
								));
		$testimonials_output .= '</div>  ';
	}
	
	// restore globals to their original values (i.e, $post and friends)
	wp_reset_postdata();
		
	// setup the grid's CSS, insert the grid of testimonials (the cells) 
	// into the grid, add a clearing div, and return the whole thing
	$grid_classes = array(
		'easy_testimonials_grid',
		'easy_testimonials_grid_' . $cols
	);
	
	if ($responsive) {
		$grid_classes[] = 'easy_testimonials_grid_responsive';
	}
	
	if ($equal_height_rows) {
		$grid_classes[] = 'easy_testimonials_grid_equal_height_rows';
	}	

	// add any grid classes specified by the user
	if ( !empty($grid_class) ) {
		$grid_classes = array_merge( $grid_classes, explode(' ', $grid_class) );
	}
	
	// combine all classes into an class attribute
	$grid_class_attr = sprintf( 'class="%s"', implode(' ', $grid_classes) );
	
	// add all style rules for the grid (currently, only specifies width)
	$grid_css_rules = array();
	if ( !empty($grid_width) && intval($grid_width) > 0 ) {
		$grid_css_rules[] = sprintf('width: %s', $grid_width);
	}
	
	// combine all CSS rules into an HTML style attribute
	$grid_style_attr = sprintf( 'style="%s"', implode(';', $grid_css_rules) );
		
	// add classes and CSS rules to the grid, insert cells, return result
	$grid_template = '<div %s %s>%s</div>';
	$grid_html = sprintf($grid_template, $grid_class_attr, $grid_style_attr, $testimonials_output);
	return $grid_html;
}

//output a single testimonial for each theme_array
//useful for demoing all of the themes or testing compatibility on a given website
//output all testimonials
function outputAllThemes($atts){ 	
	//load options
	include("include/lib/config.php");	
	
	//load shortcode attributes into an array
	$atts = shortcode_atts( array(	
		'testimonials_link' => '',//get_option('testimonials_link'),
		'show_title' => 0,
		'count' => 1,
		'body_class' => 'testimonial_body',
		'author_class' => 'testimonial_author',
		'id' => '',
		'use_excerpt' => false,
		'category' => '',
		'show_thumbs' => get_option('testimonials_image'),
		'short_version' => false,
		'orderby' => 'date',//'none','ID','author','title','name','date','modified','parent','rand','menu_order'
		'order' => 'ASC',//'DESC'
		'show_rating' => false,
		'paginate' => false,
		'testimonials_per_page' => 10,
		'theme' => '',
		'show_date' => false,
		'show_other' => false,
		'show_free_themes' => false,
		'width' => false
	), $atts );
			
	extract($atts);
	
	if($show_free_themes){
		foreach($free_theme_array as $theme_slug => $theme_name){	
			
			$atts['theme'] = $theme_slug;
			
			ob_start();
			
			//load testimonials into an array
			$loop = new WP_Query(array( 'post_type' => 'testimonial','posts_per_page' => $count, 'easy-testimonial-category' => $category, 'orderby' => $orderby, 'order' => $order, 'paged' => get_query_var( 'paged' )));
			while($loop->have_posts()) : $loop->the_post();
				echo "<h4>$theme_name</h4>";
				$postid = get_the_ID();			
				echo easy_t_get_single_testimonial_html($postid, $atts);			
			endwhile;	
			
			wp_reset_postdata();
		}
	}
			
	foreach($pro_theme_array as $theme_set => $theme_set_array){
		foreach($theme_set_array as $theme_slug => $theme_name){
			$atts['theme'] = $theme_slug;
			
			ob_start();
			
			//load testimonials into an array
			$loop = new WP_Query(array( 'post_type' => 'testimonial','posts_per_page' => $count, 'easy-testimonial-category' => $category, 'orderby' => $orderby, 'order' => $order, 'paged' => get_query_var( 'paged' )));
			while($loop->have_posts()) : $loop->the_post();
				echo "<h4>$theme_name</h4>";
				$postid = get_the_ID();			
				echo easy_t_get_single_testimonial_html($postid, $atts);			
			endwhile;	
			
			wp_reset_postdata();
		}
	}
	
	$content = ob_get_contents();
	ob_end_clean();	
	
	return apply_filters('easy_t_testimonials_html', $content);
}

//output all testimonials for use in JS widget
function outputTestimonialsCycle($atts){ 	
	//load shortcode attributes into an array
	$atts = shortcode_atts( array(
		'testimonials_link' => get_option('testimonials_link'),
		'show_title' => 0,
		'count' => -1,
		'transition' => 'scrollHorz',
		'show_thumbs' => get_option('testimonials_image'),
		'timer' => '2000',
		'container' => false,//deprecated, use auto_height instead
		'use_excerpt' => false,
		'auto_height' => false,
		'category' => '',
		'body_class' => 'testimonial_body',
		'author_class' => 'testimonial_author',
		'random' => '',
		'orderby' => 'date',//'none','ID','author','title','name','date','modified','parent','rand','menu_order'
		'order' => 'ASC',//'DESC'
		'pager' => false,
		'show_pager_icons' => false,
		'show_rating' => false,
		'testimonials_per_slide' => 1,
		'theme' => '',
		'show_date' => false,
		'show_other' => false,
		'pause_on_hover' => false,
		'prev_next' => false,
		'width' => false,
		'paused' => false,
		'display_pagers_above' => false,
		'hide_view_more' => 0
	), $atts );

	extract($atts);
			
	if(!is_numeric($count)){
		$count = -1;
	}
	
	ob_start();
	
	$i = 0;
	
	if(!isValidKey() && !in_array($transition, array('fadeOut','fade','scrollHorz'))){
		$transition = 'fadeout';
	}
	
	//use random WP query to be sure we aren't just randomly sorting a chronologically queried set of testimonials
	//this prevents us from just randomly ordering the same 5 testimonials constantly!
	if($random){
		$orderby = "rand";
	}

	//determine if autoheight is set to container or to calculate
	//not sure why i did this so backwards to begin with!  oh well...
	if($container){
		$container = "container";
	}
	if($auto_height == "calc"){
		$container = "calc";
	} else if($auto_height == "container"){
		$container = "container";
	}
	
	//generate a random number to have a unique wrapping class on each slideshow
	//this should prevent controls that effect more than one slideshow on a page
	$target = rand();
	
	//use the width for the slideshow wrapper, to keep the previous/next buttons and pager icons within the desired layout
	$width = $width ? 'style="width: ' . $width . '"' : 'style="width: ' . get_option('easy_t_width','') . '"';
	
	//load testimonials into an array
	$loop = new WP_Query(array( 'post_type' => 'testimonial','posts_per_page' => $count, 'orderby' => $orderby, 'order' => $order, 'easy-testimonial-category' => $category));
	$count = $loop->post_count;//for tracking number of testimonials in this loop
	
	?>
	<div class="easy-t-slideshow-wrap <?php echo "easy-t-{$target}";?>" <?php echo $width; ?>>
	
		<?php //only display cycle controls if there is more than one testimonial ?>
		<?php if($display_pagers_above && $count > 1): ?>
		<div class="easy-t-cycle-controls">				
			<?php if($prev_next):?><div class="cycle-prev easy-t-cycle-prev"><?php echo get_option('easy_t_previous_text', '<< Prev'); ?></div><?php endif; ?>
			<?php if($pager || $show_pager_icons ): ?>
				<div class="easy-t-cycle-pager"></div>
			<?php endif; ?>
			<?php if($prev_next):?><div class="cycle-next easy-t-cycle-next"><?php echo get_option('easy_t_next_text', 'Next >>'); ?></div><?php endif; ?>			
		</div>	
		<?php endif; ?>
			
		<div class="cycle-slideshow" 
			data-cycle-fx="<?php echo $transition; ?>" 
			data-cycle-timeout="<?php echo $timer; ?>"
			data-cycle-slides="> div.testimonial_slide"
			<?php if($container): ?> data-cycle-auto-height="<?php echo $container; ?>" <?php endif; ?>
			<?php if($random): ?> data-cycle-random="true" <?php endif; ?>
			<?php if($pause_on_hover): ?> data-cycle-pause-on-hover="true" <?php endif; ?>
			<?php if($paused): ?> data-cycle-paused="true" <?php endif; ?>
			<?php if($prev_next): ?> data-cycle-prev=".easy-t-<?php echo $target;?> .easy-t-cycle-prev"  data-cycle-next=".easy-t-<?php echo $target;?> .easy-t-cycle-next" <?php endif; ?>
			<?php if($pager || $show_pager_icons ): ?> data-cycle-pager=".easy-t-<?php echo $target;?> .easy-t-cycle-pager" <?php endif; ?>
		>
		<?php
		
		$counter = 0;
		
		//iterate through testimonials loop
		while($loop->have_posts()) : $loop->the_post();		
			if($counter == 0){
				$testimonial_display = '';
			} else {
				$testimonial_display = 'style="display:none;"';
			}
			
			if($counter%$testimonials_per_slide == 0){
				echo "<div {$testimonial_display} class=\"testimonial_slide\">";
			}
			
			$counter ++;
		
			$postid = get_the_ID();
			
			echo easy_t_get_single_testimonial_html($postid, $atts);
			
			if($counter%$testimonials_per_slide == 0){
				echo "</div>";
			}
			
		endwhile;	
		wp_reset_postdata();
		
		?>
		</div>
		
		<?php //only display cycle controls if there is more than one testimonial ?>
		<?php if(!$display_pagers_above && $count > 1): ?>
		<div class="easy-t-cycle-controls">				
			<?php if($prev_next):?><div class="cycle-prev easy-t-cycle-prev"><?php echo get_option('easy_t_previous_text', '<< Prev'); ?></div><?php endif; ?>
			<?php if($pager || $show_pager_icons ): ?>
				<div class="easy-t-cycle-pager"></div>
			<?php endif; ?>
			<?php if($prev_next):?><div class="cycle-next easy-t-cycle-next"><?php echo get_option('easy_t_next_text', 'Next >>'); ?></div><?php endif; ?>			
		</div>	
		<?php endif; ?>
		
	</div><!-- end slideshow wrap --><?php
	
	$content = ob_get_contents();
	ob_end_clean();	
	
	return apply_filters( 'easy_t_testimonials_cyle_html', $content);
}

//runs when viewing a single testimonial's page (ie, you clicked on the continue reading link from the excerpt)
function single_testimonial_content_filter($content){
	global $easy_t_in_widget;
	global $post;
			
	// Save the post data in a variable before resetting it. It *shouldn't* matter,
	// but some plugins might be depending on the global $post being left in whatever
	// state it was when we got here
	$old_post = $post;
	wp_reset_postdata();
	
	//not running in a widget, is running in a single view or archive view such as category, tag, date, the post type is a testimonial
	if ( empty($easy_t_in_widget) && (is_single() || is_archive()) && get_post_type( $post->ID ) == 'testimonial' ) {				
		//load needed data
		$postid = get_the_ID();
		
		//build array of default attributes
		//since this is the single testimonial view, go ahead and display all data until we build some options to allow you to set the default display of these items
		$atts = array(
			'testimonials_link' => get_option('testimonials_link'),
			'count' => 1,
			'word_limit' => false,
			'body_class' => 'testimonial_body',
			'author_class' => 'testimonial_author',
			'show_title' => 1,
			'short_version' => false,
			'use_excerpt' => false,
			'category' => '',
			'show_thumbs' => get_option('testimonials_image'),
			'show_rating' => "stars",
			'theme' => '',
			'show_date' => 1,
			'show_other' => 1,
			'width' => '100%'
		);
				
		//build and return the single testimonial html		
		$content = easy_t_get_single_testimonial_html($postid, $atts, true);
	}

	// restore post data to its previous, possibly borked, form
	$post = $old_post;
	
	return $content;
}

//passed an array of acceptable shortcode attributes
//this function will build a string of classes representing the chosen attributes
//returns string ready for echoing as classes
function easy_t_build_classes_from_atts($atts = array()){
	$class_string = "";
		
	foreach ($atts as $key => $value){
		$class_string .= " " . $value . "_" . $key;
	}
	
	return $class_string;
}

function easy_t_get_the_excerpt( $post_id )
{
	//preserve the old post data for other plugins/themes/etc.
	global $post;  
	$save_post = $post;
  
	//run our own excerpt function that trims the excerpt without applying the content filter
	$post = get_post($post_id);
	if ( !empty($post->post_excerpt) ) {
		$excerpt_more = easy_t_excerpt_more( '' , $post );
		$post_excerpt = apply_filters( 'easy_t_get_the_excerpt', $post->post_excerpt . $excerpt_more, $post );
	} else {
		$post_excerpt = '';
	}
	$output = easy_t_trim_excerpt($post_excerpt , $post);
  
	//reset global postdata to saved postdata
	$post = $save_post;
  
	return $output;
}


/*
 * Generates and returns the HTML for a given testimonial, 
 * considering the shortcode attributess provided.
 *
 * @param integer $postid The post ID of the testimonial
 * @param array $atts The shortcode attributes to use for build this testimonial
 *
 * @return string The HTML output for this testimonial
 */
function easy_t_get_single_testimonial_html($postid, $atts, $is_single = false)
{
	//for use in the filter
	$atts['is_single'] = $is_single;
	
	//if this is being loaded from the single post view
	//then we already have the post data setup (we are in The Loop)
	//so skip this step
	if(!$is_single){
		global $post; 
		$post = get_post( $postid, OBJECT );
		setup_postdata( $post );
	}

	extract($atts);
	
	ob_start();
	
	$testimonial['id'] = $postid;
	
	$testimonial['date'] = get_the_date('M. j, Y');
	if($use_excerpt){
		$testimonial['content'] = easy_t_get_the_excerpt( $postid );
	} else {				
		$testimonial['content'] = get_post_field('post_content', $postid);
	}
	
	//load rating
	//if set, append english text to it
	$testimonial['rating'] = get_post_meta($postid, '_ikcf_rating', true); 
	$testimonial['num_stars'] = ''; //reset num stars (Thanks Steve@IntegrityConsultants!)
	if(strlen($testimonial['rating'])>0){	
		$rating_css = easy_testimonials_build_typography_css('easy_t_rating_');
	
		$testimonial['num_stars'] = $testimonial['rating'];
		$testimonial['rating'] = '<p class="easy_t_ratings" itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating" style="' . $rating_css . '"><meta itemprop="worstRating" content = "1"/><span itemprop="ratingValue" >' . $testimonial['rating'] . '</span>/<span itemprop="bestRating">5</span> Stars.</p>';
	}	
	
	//if nothing is set for the short content, use the long content
	if(strlen($testimonial['content']) < 2){
		//$temp_post_content = get_post($postid); 			
		$testimonial['content'] = $post->post_excerpt;
		if($use_excerpt){
			if($testimonial['content'] == ''){
				$testimonial['content'] = wp_trim_excerpt($post->post_content);
			}
		} else {				
			$testimonial['content'] = $post->post_content;
		}
	}
		
	if(strlen($show_rating)>2){
		if($show_rating == "before"){
			$testimonial['content'] = $testimonial['rating'] . ' ' . $testimonial['content'];
		}
		if($show_rating == "after"){
			$testimonial['content'] =  $testimonial['content'] . ' ' . $testimonial['rating'];
		}
	}
	
	if ($show_thumbs) {		
		$testimonial['image'] = build_testimonial_image($postid);
	}
	
	$testimonial['client'] = get_post_meta($postid, '_ikcf_client', true); 	
	$testimonial['position'] = get_post_meta($postid, '_ikcf_position', true); 
	$testimonial['other'] = get_post_meta($postid, '_ikcf_other', true); 	

	//default $hide_view_more to false, if it hasn't been set by now
	if(!isset($hide_view_more)){
		$hide_view_more = false;
	}
	
	build_single_testimonial($testimonial,$show_thumbs,$show_title,$postid,$author_class,$body_class,$testimonials_link,$theme,$show_date,$show_rating,$show_other,$width,$is_single,$hide_view_more);
	
	wp_reset_postdata();	
	$content = ob_get_contents();
	ob_end_clean();	
	return apply_filters('easy_t_get_single_testimonial_html', $content, $testimonial, $atts, $postid);
}

//given a full set of data for a testimonial
//assemble the html for that testimonial
//taking into account current options
function build_single_testimonial($testimonial,$show_thumbs=false,$show_title=false,$postid,$author_class,$body_class,$testimonials_link,$theme,$show_date=false,$show_rating=false,$show_other=true,$width=false,$is_single=false,$hide_view_more=false){
/* scheme.org example
 <div itemprop="review" itemscope itemtype="http://schema.org/Review">
    <span itemprop="name">Not a happy camper</span> -
    by <span itemprop="author">Ellie</span>,
    <meta itemprop="datePublished" content="2011-04-01">April 1, 2011
    <div itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating">
      <meta itemprop="worstRating" content = "1">
      <span itemprop="ratingValue">1</span>/
      <span itemprop="bestRating">5</span>stars
    </div>
    <span itemprop="description">The lamp burned out and now I have to replace
    it. </span>
  </div>
 */
 
	//if this testimonial doesn't have a value for the item being reviewed
	//and if the use global item reviewed setting is checked
	//use the global item reviewed value in for the current testimonial
	if( (strlen($testimonial['other'])<2) && get_option('easy_t_use_global_item_reviewed',false) ){
		$testimonial['other'] = get_option('easy_t_global_item_reviewed','');
	}
 
	//load a list of of easy testimonial categories associated with this testimonial
	//loop through list and build a string of category slugs
	//we will append these to the wrapping HTML of the single testimonial for advanced customization
	$terms = wp_get_object_terms( $testimonial['id'], 'easy-testimonial-category');
	$term_list = '';
	foreach($terms as $term){
		$term_list .= "easy-t-category-" . $term->slug . " ";
	}
 
	$atts = array(
		'thumbs' => ($show_thumbs) ? 'show' : 'hide',
		'title' => ($show_title) ? 'show' : 'hide',
		'date' => ($show_date) ? 'show' : 'hide',
		'rating' => $show_rating,
		'other' => ($show_other) ? 'show' : 'hide'
	);
	$attribute_classes = easy_t_build_classes_from_atts($atts);
	
	//add the category slugs to the list of classes to output
	//make sure to include the extra space so we aren't butting classes up against each other
	$attribute_classes .= " " . $term_list;
 
	$output_theme = easy_t_get_theme_class($theme);
	$testimonial_body_css = easy_testimonials_build_typography_css('easy_t_body_');	
	$width = $width ? 'style="width: ' . $width . '"' : 'style="width: ' . get_option('easy_t_width','') . '"';
	
	//if the "Show View More Testimonials Link" option is checked
	//and the hide_view_more attribute is not set
	//then set $show_view_more to true
	//else set to false
	$show_view_more = (get_option('easy_t_show_view_more_link',false) && !$hide_view_more) ? true : false;
	
?>
	<div class="<?php echo $output_theme; ?> <?php echo $attribute_classes; ?> easy_t_single_testimonial" <?php echo $width; ?>>
		<blockquote itemscope itemtype="http://schema.org/Review" class="easy_testimonial" style="<?php echo $testimonial_body_css; ?>">
			<?php if ($show_thumbs) {
				echo $testimonial['image'];
			} ?>		
			<?php if ($show_title) {
				echo '<p itemprop="name" class="easy_testimonial_title">' . get_the_title($postid) . '</p>';
			} ?>	
			<?php if(get_option('meta_data_position')) {
				easy_testimonials_build_metadata_html($testimonial, $author_class, $show_date, $show_rating, $show_other);	
			} ?>
			<div class="<?php echo $body_class; ?>" itemprop="description">
				<?php //$is_single is passed from the single_testimonial_content_filter function - if we are looking at an individual single testimonial, we should not apply the_content filter to prevent endless loops ?>
				<?php if(get_option('easy_t_apply_content_filter',false) && !$is_single): ?>
					<?php echo apply_filters('the_content',$testimonial['content']); ?>
				<?php else:?>
					<?php echo wpautop($testimonial['content']); ?>
				<?php endif;?>
				<?php if($show_view_more):?><a href="<?php echo $testimonials_link; ?>" class="easy_testimonials_read_more_link"><?php echo get_option('easy_t_view_more_link_text', 'Read More Testimonials'); ?></a><?php endif; ?>
			</div>	
			<?php if(!get_option('meta_data_position')) {	
				easy_testimonials_build_metadata_html($testimonial, $author_class, $show_date, $show_rating, $show_other);	
			} ?>
		</blockquote>
	</div>
<?php
}

/*
 * Assemble the HTML for the Testimonial Image taking into account current options
 */		
function build_testimonial_image($postid){
	//load image size settings
	$testimonial_image_size = isValidKey() ? get_option('easy_t_image_size') : "easy_testimonial_thumb";
	if(strlen($testimonial_image_size) < 2){
		$testimonial_image_size = "easy_testimonial_thumb";		
		$width = 50;
        $height = 50;
	} else {		
		//one of the default sizes, load using get_option
		if( in_array( $testimonial_image_size, array( 'thumbnail', 'medium', 'large' ) ) ){
			$width = get_option( $testimonial_image_size . '_size_w' );
			$height = get_option( $testimonial_image_size . '_size_h' );
		//size added by theme, user, or plugin
		//load using additional image sizes global
		}else{
			global $_wp_additional_image_sizes;
			
			if( isset( $_wp_additional_image_sizes ) && isset( $_wp_additional_image_sizes[ $testimonial_image_size ] ) ){
				$width = $_wp_additional_image_sizes[ $testimonial_image_size ]['width'];
				$height = $_wp_additional_image_sizes[ $testimonial_image_size ]['height'];
			}
		}
	}
	
	//use whichever of the two dimensions is larger
	$size = ($width > $height) ? $width : $height;

	//load testimonial's featured image
	$image = get_the_post_thumbnail($postid, $testimonial_image_size);
	
	//if no featured image is set
	if (strlen($image) < 2){ 
		//if use mystery man is set
		if (get_option('easy_t_mystery_man', 1)){
			//check and see if gravatars are enabled
			if(get_option('easy_t_gravatar', 1)){
				//if so, set image path to match desired gravatar with the mystery man as a fallback
				$client_email = get_post_meta($postid, '_ikcf_email', true); 
				$gravatar = md5(strtolower(trim($client_email)));
				$mystery_man = urlencode(plugins_url('include/img/mystery_man.png', __FILE__));
				
				$image = '<img class="attachment-easy_testimonial_thumb wp-post-image easy_testimonial_gravatar" alt="default gravatar" src="//www.gravatar.com/avatar/' . $gravatar . '?d=' . $mystery_man . '&s=' . $size . '" />';
			} else {
				//if not, just use the mystery man
				$image = '<img class="attachment-easy_testimonial_thumb wp-post-image easy_testimonial_mystery_man" alt="default image" src="' . plugins_url('include/img/mystery_man.png', __FILE__) . '" />';
			}
		//else if gravatar is set
		} else if(get_option('easy_t_gravatar', 1)){
			//if set, set image path to match gravatar without using the mystery man as a fallback
			$client_email = get_post_meta($postid, '_ikcf_email', true); 
			$gravatar = md5(strtolower(trim($client_email)));
			$mystery_man = urlencode(plugins_url('include/img/mystery_man.png', __FILE__));
			
			$image = '<img class="attachment-easy_testimonial_thumb wp-post-image easy_testimonial_gravatar" alt="user gravatar" src="//www.gravatar.com/avatar/' . $gravatar . '?s=' . $size . '" />';
		}
	}
	
	return $image;
}
 
/*
 *  Assemble the html for the testimonials metadata taking into account current options
 */
function easy_testimonials_build_metadata_html($testimonial, $author_class, $show_date, $show_rating, $show_other)
{
	$date_css = easy_testimonials_build_typography_css('easy_t_date_');
	$position_css = easy_testimonials_build_typography_css('easy_t_position_');
	$client_css = easy_testimonials_build_typography_css('easy_t_author_');
	$other_css = easy_testimonials_build_typography_css('easy_t_other_');
	$rating_css = easy_testimonials_build_typography_css('easy_t_rating_', 'stars');//only build the stars CSS, ie the font color only, as the rating displayed by the metadata function is only ever stars
	
	//set the following variables to true if the option to display the associated item is true and the associated item has content in it (preventing outputting blank items that insert whitespace)
	$show_the_client = (strlen($testimonial['client'])>0) ? true : false;
	$show_the_position = (strlen($testimonial['position'])>0) ? true : false;
	$show_the_other = (strlen($testimonial['other'])>0 && $show_other) ? true : false;
	$show_the_date = (strlen($testimonial['date'])>0 && $show_date) ? true : false;
	$show_the_rating = (strlen($testimonial['num_stars'])>0 && ($show_rating == "stars")) ? true : false;
?>
	<p class="<?php echo $author_class; ?>">
		<?php //if any of the items have data and are set to be displayed, construct the html ?>
		<?php if($show_the_client || $show_the_position || $show_the_other || $show_the_date || $show_rating == "stars" ): ?>
		<cite>
			<?php if($show_the_client): ?>
				<span class="testimonial-client" itemprop="author" style="<?php echo $client_css; ?>"><?php echo $testimonial['client'];?></span>
			<?php endif; ?>
			<?php if($show_the_position): ?>
				<span class="testimonial-position" style="<?php echo $position_css; ?>"><?php echo $testimonial['position'];?></span>
			<?php endif; ?>
			<?php if($show_the_other): ?>
				<span class="testimonial-other" style="<?php echo $other_css; ?>" itemprop="itemReviewed"><?php echo $testimonial['other'];?></span>
			<?php endif; ?>
			<?php if($show_the_date): ?>
				<span class="date" itemprop="datePublished" content="<?php echo $testimonial['date'];?>" style="<?php echo $date_css; ?>"><?php echo $testimonial['date'];?></span>
			<?php endif; ?>
			<?php if($show_the_rating): ?>
				<?php if(strlen($testimonial['num_stars'])>0): ?>
				<span itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating" class="stars">
				<meta itemprop="worstRating" content="1"/>
				<meta itemprop="ratingValue" content="<?php echo $testimonial['num_stars']; ?>"/>
				<meta itemprop="bestRating" content="5"/>
				<?php			
					$x = 5; //total available stars
					//output dark stars for the filled in ones
					for($i = 0; $i < $testimonial['num_stars']; $i ++){
						echo '<span class="dashicons dashicons-star-filled" style="' . $rating_css . '"></span>';
						$x--; //one less star available
					}
					//fill out the remaining empty stars
					for($i = 0; $i < $x; $i++){
						echo '<span class="dashicons dashicons-star-filled empty"></span>';
					}
				?>			
				</span>	
				<?php endif; ?>
			<?php endif; ?>
		</cite>
		<?php endif; ?>					
	</p>	
<?php
}

//passed a string
//finds a matching theme or loads the theme currently selected on the options page
//returns appropriate class name string to match theme
//if return_theme_base is true, returns the base string of the theme (without the style modifier)
function easy_t_get_theme_class($theme_string, $return_theme_base = false){	
	$the_theme = get_option('testimonials_style', 'default_style');
	
	//load options
	include("include/lib/config.php");			
	
	//if the theme string is passed
	if(strlen($theme_string)>2){
		//if the theme string is valid
		if(in_array($theme_string, $theme_array)){			
			//if returning theme base for pro themes, go ahead and do so now
			if( $return_theme_base ){
				//loop through the pro theme array
				foreach( $pro_theme_array as $pro_theme_base => $this_pro_theme_array ) {
					//if a matching key to our specific pro theme is found
					if(isset($this_pro_theme_array[$theme_string])){
						//return the base string of that pro theme, from the array
						return $pro_theme_base;
					}
				}
			}
			
			//use the theme string
			$the_theme = $theme_string;
		}
	}
	
	//remove style from the middle of our theme options and place it as a prefix
	//matching our CSS files
	$the_theme = str_replace('-style', '', $the_theme);
	$the_theme = "style-" . $the_theme;
	
	return $the_theme;
}

//only do this once
function easy_testimonials_rewrite_flush() {
    easy_testimonials_setup_testimonials();
	
    flush_rewrite_rules();
}

//register any widgets here
function easy_testimonials_register_widgets() {
	include('include/widgets/random_testimonial_widget.php');
	include('include/widgets/single_testimonial_widget.php');
	include('include/widgets/testimonial_cycle_widget.php');
	include('include/widgets/testimonial_list_widget.php');
	include('include/widgets/testimonial_grid_widget.php');
	include('include/widgets/submit_testimonial_widget.php');

	register_widget( 'randomTestimonialWidget' );
	register_widget( 'cycledTestimonialWidget' );
	register_widget( 'listTestimonialsWidget' );
	register_widget( 'singleTestimonialWidget' );
	register_widget( 'submitTestimonialWidget' );
	register_widget( 'TestimonialsGridWidget' );
}

function easy_testimonials_admin_init($hook)
{	
	//RWG: only enqueue scripts and styles on Easy T admin pages or widgets page
	$screen = get_current_screen();
	
	if ( 	strpos($hook,'easy-testimonials')!==false || 
			$screen->id === "widgets" || 
			(function_exists('is_customize_preview') && is_customize_preview()))
	{
		wp_register_style( 'easy_testimonials_admin_stylesheet', plugins_url('include/css/admin_style.css', __FILE__) );
		wp_enqueue_style( 'easy_testimonials_admin_stylesheet' );
		wp_enqueue_script(
			'easy-testimonials-admin',
			plugins_url('include/js/easy-testimonials-admin.js', __FILE__),
			array( 'jquery' ),
			false,
			true
		); 
		wp_enqueue_script(
			'gp-admin_v2',
			plugins_url('include/js/gp-admin_v2.js', __FILE__),
			array( 'jquery' ),
			false,
			true
		);	
	}
	
	//RWG: include pro styles on Theme Selection screen, for preview purposes
	if(strpos($hook,'easy-testimonials-style-settings')!==false){
		//basic styling
		wp_register_style( 'easy_testimonial_style', plugins_url('include/css/style.css', __FILE__) );
		wp_enqueue_style( 'easy_testimonial_style' );
		
		//register and enqueue pro themes for preview purposes
		easy_t_register_pro_themes();
		wp_enqueue_style( 'bubble_style' );
		wp_enqueue_style( 'avatar-right-style' );
		wp_enqueue_style( 'avatar-right-style-50x50' );
		wp_enqueue_style( 'avatar-left-style' );
		wp_enqueue_style( 'avatar-left-style-50x50' );
		wp_enqueue_style( 'card_style' );
		wp_enqueue_style( 'elegant_style' );
		wp_enqueue_style( 'business_style' );
		wp_enqueue_style( 'modern_style' );
		wp_enqueue_style( 'notepad_style' );
	}
	
	// also include some styles on *all* admin pages
	wp_register_style( 'easy_testimonials_admin_stylesheet_global', plugins_url('include/css/admin_style_global.css', __FILE__) );
	wp_enqueue_style( 'easy_testimonials_admin_stylesheet_global' );

	
}

//check for installed plugins with known conflicts
//if any are found, display appropriate messaging with suggested steps
//currently only checks for woothemes testimonials
function easy_testimonials_conflict_check($hook_suffix){
	/* WooThemes Testimonials Check */
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );	
	$woothemes_testimonials = "testimonials-by-woothemes/woothemes-testimonials.php";
	
	if(is_plugin_active($woothemes_testimonials)){//woothemes testimonials found		
		if (strpos($hook_suffix,'easy-testimonials') !== false) {
			add_action('admin_notices', 'easy_t_woothemes_testimonials_admin_notice');
		}
	}
	
	/* Avada Check */
	$my_theme = wp_get_theme();
	if( strpos( $my_theme->get('Name'), "Avada" ) === 0 ) {
		// looks like we are using Avada! 
		// make sure we have avada compatibility enabled. If not, show a warning!
		if(!get_option('easy_t_avada_filter_override', false)){
			add_action('admin_notices', 'easy_t_avada_admin_notice');
		}
	}
}

//output warning message about woothemes testimonials conflicts
function easy_t_woothemes_testimonials_admin_notice(){
	echo '<div class="error"><p>';
	echo '<strong>ALERT:</strong> We have detected that Testimonials by WooThemes is installed.<br/><br/>  This plugin has known conflicts with Easy Testimonials. To prevent any issues, we recommend deactivating Testimonials by WooThemes while using Easy Testimonials.';
	echo "</p></div>";
}

//output warning message about avada conflicts
function easy_t_avada_admin_notice() {
	echo '<div class="error"><p>';
	echo '<strong>ALERT:</strong> Easy Testimonials has detected that Avada by Theme Fusion is installed.<br/><br/>  To ensure compatibility, please <a href="?page=easy-testimonials-settings#compatibility_options">visit our Compatibility Options</a> on the Basic Settings tab and verify that "Override Avada Blog Post Content Filter on Testimonials" is checked.';
	echo "</p></div>";
}

//add an inline link to the settings page, before the "deactivate" link
function add_settings_link_to_plugin_action_links($links) { 
  $settings_link = '<a href="admin.php?page=easy-testimonials-settings">Settings</a>';
  array_unshift($links, $settings_link); 
  return $links; 
}

// add inline links to our plugin's description area on the Plugins page
function add_custom_links_to_plugin_description($links, $file) { 

	/** Get the plugin file name for reference */
	$plugin_file = plugin_basename( __FILE__ );
 
	/** Check if $plugin_file matches the passed $file name */
	if ( $file == $plugin_file )
	{		
		$new_links['settings_link'] = '<a href="admin.php?page=easy-testimonials-settings">Settings</a>';
		$new_links['support_link'] = '<a href="https://goldplugins.com/contact/?utm-source=plugin_menu&utm_campaign=support&utm_banner=bananaphone" target="_blank">Get Support</a>';
			
		if(!isValidKey()){
			$new_links['upgrade_to_pro'] = '<a href="https://goldplugins.com/our-plugins/easy-testimonials-details/upgrade-to-easy-testimonials-pro/?utm_source=plugin_menu&utm_campaign=upgrade" target="_blank">Upgrade to Pro</a>';
		}
		
		$links = array_merge( $links, $new_links);
	}
	return $links; 
}
	
/* Displays a meta box with the shortcodes to display the current testimonial */
function easy_t_display_shortcodes_meta_box() {
	global $post;
	echo "<strong>To display this testimonial</strong>, add this shortcode to any post or page:<br />";	
	$ex_shortcode = sprintf('[single_testimonial id="%d"]', $post->ID);	
	printf('<textarea class="gp_highlight_code">%s</textarea>', $ex_shortcode);
}

/* CSV import / export */
	
/* Looks for a special POST value, and if its found, outputs a CSV of testimonials */
function process_export()
{
	// look for an Export command first
	if (isset($_POST['_easy_t_do_export']) && $_POST['_easy_t_do_export'] == '_easy_t_do_export') {
		$exporter = new TestimonialsPlugin_Exporter();
		$exporter->process_export();
		exit();
	}
}

/* hello t integration */

//open up the json
//determine which testimonials are new, or assume we have loaded only new testimonials
//parse object and insert new testimonials
function add_hello_t_testimonials(){	
	$the_time = time();
	
	$url = get_option('easy_t_hello_t_json_url') . "?last=" . get_option('easy_t_hello_t_last_time', 0);
	
	$response = wp_remote_get( $url, array('sslverify' => false ));
			
	if(@isset($response['body'])){
		$response = json_decode($response['body']);
		
		if(isset($response->testimonials)){
			$testimonial_author_id = get_option('easy_t_testimonial_author', 1);
			
			foreach($response->testimonials as $testimonial){				
				
				//look for a testimonial with the same HTID
				//if not found, insert this one
				$args = array(
					'post_type' => 'testimonial',
					'meta_query' => array(
						array(
							'key' => '_ikcf_htid',
							'value' => $testimonial->id,
						)
					)
				 );
				$postslist = get_posts( $args );
				
				//if this is empty, a match wasn't found and therefore we are safe to insert
				if(empty($postslist)){				
					//insert the testimonials
					
					//defaults
					$the_name = isset( $testimonial->name ) ? $testimonial->name : '';
					$the_rating = isset( $testimonial->rating ) ? $testimonial->rating : 5;
					$the_position = isset( $testimonial->position ) ? $testimonial->position : '';
					$the_item_reviewed = isset( $testimonial->item_reviewed ) ? $testimonial->item_reviewed : '';
					$the_email = isset( $testimonial->email ) ? $testimonial->email : '';
					
					$tags = array();
				   
					$post = array(
						'post_title'    => $testimonial->name,
						'post_content'  => $testimonial->body,
						'post_category' => array(1),  // custom taxonomies too, needs to be an array
						'tags_input'    => $tags,
						'post_status'   => 'publish',
						'post_type'     => 'testimonial',
						'post_date'		=> $testimonial->publish_time,
						'post_author' 	=> $testimonial_author_id
					);
				
					$new_id = wp_insert_post($post);
				   
					update_post_meta( $new_id,	'_ikcf_client',		$the_name );
					update_post_meta( $new_id,	'_ikcf_rating',		$the_rating );
					update_post_meta( $new_id,	'_ikcf_htid',		$testimonial->id );
					update_post_meta( $new_id,	'_ikcf_position',	$the_position );
					update_post_meta( $new_id,	'_ikcf_other',		$the_item_reviewed );
					update_post_meta( $new_id,	'_ikcf_email',		$the_email );
				   
					$inserted = true;
					
					//update the last inserted id
					update_option( 'easy_t_hello_t_last_time', $the_time );
				}
			}
		}
	}
}

function hello_t_nag_ignore() {
	global $current_user;
	$user_id = $current_user->ID;
	/* If user clicks to ignore the notice, add that to their user meta */
	if ( isset($_GET['hello_t_nag_ignore']) && '0' == $_GET['hello_t_nag_ignore'] ) {
		 add_user_meta($user_id, 'hello_t_nag_ignore', 'true', true);
	}
}

//activate the cron job
function hello_t_cron_activate(){
	wp_schedule_event( time(), 'hourly', 'hello_t_subscription');
}

//deactivate the cron job when the plugin is deactivated
function hello_t_cron_deactivate(){
	wp_clear_scheduled_hook('hello_t_subscription');
}

add_action('hello_t_subscription', 'add_hello_t_testimonials');

//this runs a function when this plugin is deactivated
register_deactivation_hook( __FILE__, 'hello_t_cron_deactivate' );

/* end hello t integration */

/* Styling Functions */
/*
* Builds a CSS string corresponding to the values of a typography setting
*
* @param $prefix The prefix for the settings. We'll append font_name,
* font_size, etc to this prefix to get the actual keys
*
* @returns string The completed CSS string, with the values inlined
*/
function easy_testimonials_build_typography_css($prefix, $extra = '')
{
	$css_rule_template = ' %s: %s;';
	$output = '';
	if (!isValidKey()) {
		return $output;
	}
	/*
	* Font Family
	*/
	$option_val = get_option($prefix . 'font_family', '');
	if (!empty($option_val)) {
		// strip off 'google:' prefix if needed
		$option_val = str_replace('google:', '', $option_val);
		// wrap font family name in quotes
		$option_val = '\'' . $option_val . '\'';
		$output .= sprintf($css_rule_template, 'font-family', $option_val);
	}
	/*
	* Font Size
	*/
	$option_val = get_option($prefix . 'font_size', '');
	if (!empty($option_val)) {
		// append 'px' if needed
		if ( is_numeric($option_val) ) {
			$option_val .= 'px';
		}
		$output .= sprintf($css_rule_template, 'font-size', $option_val);
	}
	/*
	* Font Style - add font-style and font-weight rules
	* NOTE: in this special case, we are adding 2 rules!
	*/
	$option_val = get_option($prefix . 'font_style', '');
	// Convert the value to 2 CSS rules, font-style and font-weight
	// NOTE: we lowercase the value before comparison, for simplification
	switch(strtolower($option_val))
	{
		case 'regular':
			// not bold not italic
			$output .= sprintf($css_rule_template, 'font-style', 'normal');
			$output .= sprintf($css_rule_template, 'font-weight', 'normal');
		break;
		case 'bold':
			// bold, but not italic
			$output .= sprintf($css_rule_template, 'font-style', 'normal');
			$output .= sprintf($css_rule_template, 'font-weight', 'bold');
		break;
		case 'italic':
			// italic, but not bold
			$output .= sprintf($css_rule_template, 'font-style', 'italic');
			$output .= sprintf($css_rule_template, 'font-weight', 'normal');
		break;
		case 'bold italic':
			// bold and italic
			$output .= sprintf($css_rule_template, 'font-style', 'italic');
			$output .= sprintf($css_rule_template, 'font-weight', 'bold');
		break;
		default:
			// empty string or other invalid value, ignore and move on
		break;
	}
	/*
	* Font Color
	* RWG: Moved this after other options so that, for Stars display 
	*      we can empty $output and start over with just the font color
	*      preventing the user from accidentally doing crazy things with their stars
	*/
	//RWG: if this is the Rating and extra is set to Stars, only apply the chosen color (ie, wipe out the output string and start anew -- this prevents the user from accidentally breaking their stars display)
	if($prefix == "easy_t_rating_" && $extra == "stars"){
		$output = "";
	}
	$option_val = get_option($prefix . 'font_color', '');
	if (!empty($option_val)) {
		$output .= sprintf($css_rule_template, 'color', $option_val);
	}
	
	// return the completed CSS string
	return trim($output);
}

function list_required_google_fonts()
{
	// check each typography setting for google fonts, and build a list
	$option_keys = array(
		'easy_t_body_font_family',
		'easy_t_author_font_family',
		'easy_t_position_font_family',
		'easy_t_date_font_family',
		'easy_t_rating_font_family'		
	);  
	$fonts = array();
	foreach ($option_keys as $option_key) {
		$option_value = get_option($option_key);
		if (strpos($option_value, 'google:') !== FALSE) {
			$option_value = str_replace('google:', '', $option_value);
			
			//only add the font to the array if it was in fact a google font
			$fonts[$option_value] = $option_value;				
		}
	}
	return $fonts;
}
	
// Enqueue any needed Google Web Fonts
function enqueue_webfonts()
{
	$cache_key = '_easy_t_webfont_str';
	$font_str = get_transient($cache_key);
	if ($font_str == false) {
		$font_list = list_required_google_fonts();
		if ( !empty($font_list) ) {
			$font_list_encoded = array_map('urlencode', $font_list);
			$font_str = implode('|', $font_list_encoded);
		} else {
			$font_str = 'x';
		}
		set_transient($cache_key, $font_str);		
	}
	
	//don't register this unless a font is set to register
	if(strlen($font_str)>2){
		$protocol = is_ssl() ? 'https:' : 'http:';
		$font_url = $protocol . '//fonts.googleapis.com/css?family=' . $font_str;
		wp_register_style( 'easy_testimonials_webfonts', $font_url);
		wp_enqueue_style( 'easy_testimonials_webfonts' );
	}
}

/* add customized continue reading link to testimonials, if set */
function easy_t_excerpt_more( $more, $the_post = false ) {
	global $post;
	
	if ( empty($the_post) ) {
		$the_post = $post;
	}

	if(get_option('easy_t_link_excerpt_to_full', false)){
		return ' <a class="more-link" href="' . get_permalink( $the_post->ID ) . '">' . get_option('easy_t_excerpt_text') . '</a>';
	} else {
		return ' ' . get_option('easy_t_excerpt_text');
	}			
}
//checks to see if this is a testimonial
//if it is, loads custom excerpt length and uses it
//otherwise use current wordpress setting
function easy_t_excerpt_length( $length ) {
	global $post;
	
	//if this is a testimonial, use our customization
	if($post->post_type == 'testimonial'){
		return get_option('easy_t_excerpt_length',55);
	}
	
	return $length;
}

// Dashboard Widget Yang

/**
 * Add a widget to the dashboard.
	*
 * This function is hooked into the 'wp_dashboard_setup' action below.
 */
function easy_t_add_dashboard_widget() {
	wp_add_dashboard_widget(
		'easy_t_submissions_dashboard_widget',         // Widget slug.
		'Easy Testimonials Pro - Recent Submissions',         // Title.
		'easy_t_output_dashboard_widget' // Display function.
	);	
}

/**
 * Create the function to output the contents of our Dashboard Widget.
 */
function easy_t_output_dashboard_widget()
{
	
	$recent_submissions = '';
	
	$recent_submissions = get_posts('post_type=testimonial&posts_per_page=10&post_status=pending');
	
	if (is_array($recent_submissions)) {
		//also output a panel of stats (ie, # of pending submissions)
		
		echo '<table id="easy_t_recent_submissions" class="widefat">';
		echo '<thead>';
		echo '<tr>';
		echo '<th>Date</th>';
		echo '<th>Summary</th>';
		echo '<th>Rating</th>';
		echo '<th>Action</th>';
		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';
		foreach($recent_submissions as $i => $submission)
		{
			$row_class = ($i % 2 == 0) ? 'alternate' : '';
			echo '<tr class="'.$row_class.'">';
			
			$action_url = get_admin_url() . "post.php?post=$submission->ID&action=edit";
			$action_links = '<p><a href="'.$action_url.'" class="edit_testimonial" id="'.$submission->ID.'" title="Edit Testimonial"><span class="dashicons dashicons-edit"></span>Edit</a></p>';
			$action_links .= '<p><a class="approve_testimonial" id="'.$submission->ID.'" title="Approve Testimonial"><span class="dashicons dashicons-yes"></span>Approve</a></p>';
			$action_links .= '<p><a class="trash_testimonial" id="'.$submission->ID.'" title="Trash Testimonial"><span class="dashicons dashicons-no"></span>Trash</a></p>';
			
			$rating = get_post_meta($submission->ID, '_ikcf_rating', true); 
			$rating = !empty($rating) ? $rating . "/5" : "No Rating";
			
			$friendly_time = date('Y-m-d H:i:s', strtotime($submission->post_date));
			printf ('<td>%s</td>', htmlentities($friendly_time));
			
			printf ('<td>%s</td>', wp_trim_words($submission->post_content, 25));
			printf ('<td>%s</td>', htmlentities($rating));
			printf ('<td class="action_links">%s</td>', $action_links);

			echo '</tr>';				
		}
		echo '</tbody>';
		echo '</table>';
		
		$view_all_testimonials_url= '/wp-admin/edit.php?post_type=testimonial';
		$link_text = 'View All Testimonials';
		printf ('<p class="view_all_testimonials"><a href="%s">%s &raquo;</a></p>', $view_all_testimonials_url, $link_text);
	}
}	

//admin ajax yang for dashboard widget
function easy_t_action_javascript($action) {
    ?>
    <script type="text/javascript" >
    jQuery(document).ready(function($) {
        jQuery('.action_links a').on('click', function() {
            var $this = jQuery(this);
			var	data = {action: 'easy_t_action', my_action: $this.attr('class'), my_postid: $this.attr('id')};
			
			if($this.attr('class') != "edit_testimonial"){//no ajax on edit, take visitor to edit screen instead
				jQuery.post(ajaxurl, data, function(response) {
					if($this.attr('class') == "approve_testimonial"){
						$this.parent().parent().html("<p>Approved!</p>").parent().addClass("updated");
					} else if($this.attr('class') == "trash_testimonial"){
						$this.parent().parent().html("<p>Trashed!</p>").parent().addClass("updated");
					}
				});
				
				return false;
			}
        });
     });
     </script>
     <?php
}

function easy_t_action_callback() {
    $action = $_POST['my_action'];
    $id = $_POST['my_postid'];
	$response = "";
	
    switch($action) {
            case 'approve_testimonial':
                $testimonial = array(
					'ID' => $id,
					'post_status' => 'publish'
				);
				
                $response = wp_update_post($testimonial);//returns 0 if error, otherwise ID of the updated testimonial
	 
				if($response != 0){
					echo $response;
				} else {
					//error, do something
				}
            break;

            case 'trash_testimonial':				
                $response = wp_trash_post($id);//returns false if error
				
				if(!$response){
					//error, do something
				} else {
					echo $id;
				}
            break;
     }
	 
     die();
}
//end admin ajax yang for dashboard widget
	
// End Dashboard Widget Yang

//checks for registered shortcodes and displays alert on settings screen if there are any current conflicts
function easy_testimonials_shortcode_checker(array $atts){
	//TBD
}

//search form shortcode
function easy_t_search_form_shortcode()
{
	add_filter('get_search_form', 'easy_t_restrict_search_to_custom_post_type', 10);
	$search_html = get_search_form();
	remove_filter('get_search_form', 'easy_t_restrict_search_to_custom_post_type');
	return $search_html;
}

function easy_t_restrict_search_to_custom_post_type($search_html)
{
	$post_type = 'testimonial';
	$hidden_input = sprintf('<input type="hidden" name="post_type" value="%s">', $post_type);
	$replace_with = $hidden_input . '</form>';
	return str_replace('</form>', $replace_with, $search_html);
}


//"Construct"

//load any custom shortcodes
$random_testimonial_shortcode = get_option('ezt_random_testimonial_shortcode', 'random_testimonial');
$single_testimonial_shortcode = get_option('ezt_single_testimonial_shortcode', 'single_testimonial');
$testimonials_shortcode = get_option('ezt_testimonials_shortcode', 'testimonials');
$submit_testimonial_shortcode = get_option('ezt_submit_testimonial_shortcode', 'submit_testimonial');
$testimonials_cycle_shortcode = get_option('ezt_cycle_testimonial_shortcode', 'testimonials_cycle');
$testimonials_count_shortcode = get_option('ezt_testimonials_count_shortcode', 'testimonials_count');
$testimonials_grid_shortcode = get_option('ezt_testimonials_grid_shortcode', 'testimonials_grid');

//check for shortcode conflicts
$shortcodes = array();
easy_testimonials_shortcode_checker($shortcodes);

//create shortcodes
add_shortcode($random_testimonial_shortcode, 'outputRandomTestimonial');
add_shortcode($single_testimonial_shortcode, 'outputSingleTestimonial');
add_shortcode($testimonials_shortcode, 'outputTestimonials');
add_shortcode($submit_testimonial_shortcode, 'submitTestimonialForm');
add_shortcode($testimonials_cycle_shortcode , 'outputTestimonialsCycle');
add_shortcode($testimonials_count_shortcode , 'outputTestimonialsCount');
add_shortcode('output_all_themes', 'outputAllThemes');
add_shortcode('easy_t_search_testimonials', 'easy_t_search_form_shortcode');
add_shortcode($testimonials_grid_shortcode, 'easy_t_testimonials_grid_shortcode');

//dashboard widget ajax functionality 
add_action('admin_head', 'easy_t_action_javascript');
add_action('wp_ajax_easy_t_action', 'easy_t_action_callback');

//CSV export
add_action('admin_init', 'process_export');

//add JS
add_action( 'wp_enqueue_scripts', 'easy_testimonials_setup_js', 9999 );
		
// add Google web fonts if needed
add_action( 'wp_enqueue_scripts', 'enqueue_webfonts');

//add CSS
add_action( 'wp_enqueue_scripts', 'easy_testimonials_setup_css' );

//add Custom CSS
add_action( 'wp_head', 'easy_testimonials_setup_custom_css');

//register sidebar widgets
add_action( 'widgets_init', 'easy_testimonials_register_widgets' );

//do stuff
add_action( 'init', 'easy_testimonials_setup_testimonials' );
add_action( 'admin_enqueue_scripts', 'easy_testimonials_admin_init' );
add_action( 'admin_enqueue_scripts', 'easy_testimonials_conflict_check' );
add_action('plugins_loaded', 'easy_t_load_textdomain');

add_filter('manage_testimonial_posts_columns', 'easy_t_column_head', 10);  
add_action('manage_testimonial_posts_custom_column', 'easy_t_columns_content', 10, 2); 

add_filter('manage_edit-easy-testimonial-category_columns', 'easy_t_cat_column_head', 10);  
add_action('manage_easy-testimonial-category_custom_column', 'easy_t_cat_columns_content', 10, 3); 


// add media buttons to admin
$cur_post_type = ( isset($_GET['post']) ? get_post_type(intval($_GET['post'])) : '' );
if( is_admin() && ( empty($_REQUEST['post_type']) || $_REQUEST['post_type'] !== 'testimonial' ) && ($cur_post_type !== 'testimonial') )
{
	global $EasyT_MediaButton;
	$EasyT_MediaButton = new Gold_Plugins_Media_Button('Testimonials', 'testimonial');
	$EasyT_MediaButton->add_button('Single Testimonial', $single_testimonial_shortcode, 'singletestimonialwidget', 'testimonial');
	$EasyT_MediaButton->add_button('Random Testimonial', $random_testimonial_shortcode, 'randomtestimonialwidget', 'testimonial');
	$EasyT_MediaButton->add_button('List of Testimonials',  $testimonials_shortcode, 'listtestimonialswidget', 'testimonial');
	$EasyT_MediaButton->add_button('Grid of Testimonials',  $testimonials_grid_shortcode, 'testimonialsgridwidget', 'testimonial');
	$EasyT_MediaButton->add_button('Testimonial Cycle',  $testimonials_cycle_shortcode, 'cycledtestimonialwidget', 'testimonial');
	if (isValidKey()) {
		$EasyT_MediaButton->add_button('Testimonial Form',  $submit_testimonial_shortcode, 'submittestimonialwidget', 'testimonial');
	}
}

// load Janus
if (class_exists('GP_Janus')) {
	$easy_t_Janus = new GP_Janus();
}

//add our custom links for Settings and Support to various places on the Plugins page
$plugin = plugin_basename(__FILE__);
add_filter( "plugin_action_links_{$plugin}", 'add_settings_link_to_plugin_action_links' );
add_filter( 'plugin_row_meta', 'add_custom_links_to_plugin_description', 10, 2 );	

//add our function to customize the excerpt, if enabled
//add_filter( 'excerpt_more', 'easy_t_excerpt_more', 9999 );
add_filter( 'excerpt_length', 'easy_t_excerpt_length', 9999 );

//override content filter on single testimonial pages 
//to load the proper HTML structure and content for displaying a testimonial
add_filter('the_content', 'single_testimonial_content_filter');

//dashboard widget for pro users
if (isValidKey()) {
	add_action( 'wp_dashboard_setup', 'easy_t_add_dashboard_widget');		
}

//add query var for paging
add_filter( 'query_vars', 'easy_t_add_pagination_query_var' );

//flush rewrite rules - only do this once!
register_activation_hook( __FILE__, 'easy_testimonials_rewrite_flush' );

/* Avada Compatibility */
// maybe also an alert like "hey it looks like you are using avada maybe you should enable this...."
// first override blog post content function by avada to prevent it running on testimonials
// then apply our own content filter instead
if(get_option('easy_t_avada_filter_override', false)){
	add_action('avada_blog_post_content', 'easy_t_avada_content_filter');//attach our custom content filter to their action that will use our styling
	
	//make our own version of the avada blog post content function that doesn't run if the current post type is a testimonial
	//since avada uses !function_exists correctly, our function will be declared first and will win!
	if ( ! function_exists( 'avada_render_blog_post_content' ) ) {
		function avada_render_blog_post_content() {
			global $post;
			if($post->post_type != "testimonial"){
				if ( is_search() && Avada()->settings->get( 'search_excerpt' ) ) {
					return;
				}
				echo fusion_get_post_content();
			}
		}
	}
	
	//make our own version of the avada post title function that doesn't run if the current post type is a testimonial
	if ( ! function_exists( 'avada_render_post_title' ) ) {
		function avada_render_post_title( $post_id = '', $linked = TRUE, $custom_title = '', $custom_size = '2' ) {
			global $post;
			if($post->post_type != "testimonial"){
				$entry_title_class = '';

				// Add the entry title class if rich snippets are enabled
				if ( ! Avada()->settings->get( 'disable_date_rich_snippet_pages' ) ) {
					$entry_title_class = ' class="entry-title"';
				}

				// If we have a custom title, use it
				if ( $custom_title ) {
					$title = $custom_title;
				// Otherwise get post title
				} else {
					$title = get_the_title( $post_id );
				}

				// If the post title should be linked at the markup
				if ( $linked ) {
					$link_target = '';
					if( fusion_get_page_option( 'link_icon_target', $post_id ) == 'yes' ||
						fusion_get_page_option( 'post_links_target', $post_id ) == 'yes' ) {
						$link_target = ' target="_blank"';
					}

					$title = sprintf( '<a href="%s"%s>%s</a>', get_permalink( $post_id ), $link_target, $title );
				}

				// Setup the HTML markup of the post title
				$html = sprintf( '<h%s%s>%s</h%s>', $custom_size, $entry_title_class, $title, $custom_size );


				return $html;
			}
		}
	}
}
function easy_t_avada_content_filter(){
	global $post;
	
	if($post->post_type == 'testimonial'){
		the_content();
	}
}

	/* excerpt update 4.14 */
	/* Keep the extra info we've added with the_content filter from appearing in the excerpt*/
	add_filter('get_the_excerpt', 'easy_t_fix_testimonial_excerpts');

	function easy_t_fix_testimonial_excerpts($excerpt)
	{
		global $post;
	
		$post = get_post();
		
		// if not a testimonial, move on
		if ( empty( $post ) || $post->post_type !== 'testimonial' ) {
			return $excerpt;
		}
		
		return easy_t_trim_excerpt($excerpt, $post);
	}

	/**
	 * Our own version of wp_trim_excerpt that:
	*    1) can be run on any post (instead of only the global)
	*    2) doesn't run the_content filter
	*
	*  Else all is the same (runs all the normal filters, etc).
	*
	*  @param	$text	Excerpt, which will likely be empty. If empty, 
	*					it wil be generated in the normal way, except 
	*					without running the_content filter.
	*
	*  @param	$post	The post to use for the excerpt. If not provided, 
	*					global $post is used
	*
	*  @return	string	The excerpt (after wp_trim_excerpt has been applied).
	*
	*/
	function easy_t_trim_excerpt( $text = '', $post = false ) {
		if (!$post) {
			$post = get_post();
		}
		
        $raw_excerpt = $text;
        if ( '' == $text ) {
                $text = $post->post_content;

                $text = strip_shortcodes( $text );

                /** This filter is documented in wp-includes/post-template.php */
                //$text = apply_filters( 'the_content', $text );
                $text = str_replace(']]>', ']]&gt;', $text);

                /**
                 * Filter the number of words in an excerpt.
                 *
                 * @since 2.7.0
                 *
                 * @param int $number The number of words. Default 55.
                 */
                $excerpt_length = apply_filters( 'excerpt_length', 55 );
                /**
                 * Filter the string in the "more" link displayed after a trimmed excerpt.
                 *
                 * @since 2.9.0
                 *
                 * @param string $more_string The string shown within the more link.
                 */
				add_filter( 'excerpt_more', 'easy_t_excerpt_more', 9999, 2 );
				$excerpt_more = easy_t_excerpt_more( '' , $post );
                $excerpt_more = apply_filters( 'excerpt_more', $excerpt_more );
                $text = wp_trim_words( $text, $excerpt_length, $excerpt_more );
				remove_filter( 'excerpt_more', 'easy_t_excerpt_more', 9999 );
        }
        /**	
         * Filter the trimmed excerpt string.
         *
         * @since 2.8.0
         *
         * @param string $text        The trimmed text.
         * @param string $raw_excerpt The text prior to trimming.
         */
        return apply_filters( 'wp_trim_excerpt', $text, $raw_excerpt );
	}
	
// create an instance of BikeShed that we can use later
if (is_admin()) {
	global $EasyT_BikeShed;
	$EasyT_BikeShed = new Easy_Testimonials_GoldPlugins_BikeShed();
}

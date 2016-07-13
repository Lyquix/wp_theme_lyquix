<h3>Pro Registration</h3>			
<?php if(isValidKey()): ?>	
<p class="plugin_is_registered">✓ Easy Testimonials Pro is registered and activated. Thank you!</p>
<?php else: ?>
<p class="easy_testimonials_not_registered_box">Easy Testimonials Pro is not activated. You will not be able to use the Pro features until you activate the plugin. <br /><br /><a class="button" href="https://goldplugins.com/our-plugins/easy-testimonials-details/upgrade-to-easy-testimonials-pro/?utm_campaign=registration&utm_source=easy_testimonials_settings" target="_blank">Click Here To Upgrade To Pro</a> <br /> <br /><em>When you upgrade, you'll unlock powerful new features including over 75 professionally designed themes, advanced styling options, and a Testimonial Submission form.</em></p>
<?php endif; ?>	

<?php if(!isValidKey()): ?><p>If you have purchased Easy Testimonials Pro, please complete the following fields to activate additional features such as Front-End Testimonial Submission.</p><?php endif; ?>

<?php if(!isValidMSKey()): ?>
<table class="form-table">
	<tr valign="top">
		<th scope="row"><label for="easy_t_registered_name">Email Address</label></th>
		<td><input type="text" name="easy_t_registered_name" id="easy_t_registered_name" value="<?php echo get_option('easy_t_registered_name'); ?>"  style="width: 250px" />
		<p class="description">This is the e-mail address that you used when you registered the plugin.</p>
		</td>
	</tr>
</table>
	
<table class="form-table">
	<tr valign="top">
		<th scope="row"><label for="easy_t_registered_key">API Key</label></th>
		<td><input type="text" name="easy_t_registered_key" id="easy_t_registered_key" value="<?php echo get_option('easy_t_registered_key'); ?>"  style="width: 250px" />
		<p class="description">This is the API Key that you received after registering the plugin.</p>
		</td>
	</tr>
</table>
<?php endif; ?>
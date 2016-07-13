function ezt_link_upgrade_labels()
{
	if (jQuery('.plugin_is_not_registered').length == 0) {
		return;
	}
	jQuery('.easy-t-radio-button').each(function (index) {
		var my_radio = jQuery(this).find('input[type=radio]');
		if (my_radio)
		{
			var disabled = (my_radio.attr('disabled') && my_radio.attr('disabled').toLowerCase() == 'disabled');
			if (disabled) {
				var my_em = jQuery(this).find('label em:first');
				var my_img = jQuery(this).find('label img');
				if (my_em.length > 0 || my_img.length > 0) {
					var my_id = my_radio.attr('id');
					var buy_url = 'https://goldplugins.com/our-plugins/easy-testimonials-details/upgrade-to-easy-testimonials-pro/?utm_campaign=upgrade_themes&utm_source=theme_selection&utm_banner=' + my_id;
					var link_template = '<a href="@buy_url" target="_blank"></a>';
					var link = link_template.replace(/@buy_url/g, buy_url);
					my_em.wrap(link);
					my_img.wrap(link);
				}				
			}
		}
	});
}

jQuery(document).ready(function() {
  ezt_theme_preview_swap();
});
function ezt_theme_preview_swap()
{
	jQuery('#testimonials_style').change(function(){
		var new_theme = jQuery(this).val();
		var pro_required = 0;
		
		if (new_theme.indexOf("-disabled") >= 0){
			new_theme = new_theme.replace("-disabled", "");
			pro_required = 1;
		}
		
		new_theme = new_theme.replace("-style","");
		
		jQuery('#easy_t_preview > div.easy_t_single_testimonial').removeClass().addClass('style-' + new_theme + ' easy_t_single_testimonial');
		
		if(pro_required){
			jQuery('#easy_t_preview .plugin_is_not_registered').show();
			jQuery('#easy_t_save_options').prop('disabled', true);
		} else {
			jQuery('#easy_t_preview .plugin_is_not_registered').hide();
			jQuery('#easy_t_save_options').prop('disabled', false);
		}
	});
}
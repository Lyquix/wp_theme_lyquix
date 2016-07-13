function gp_loadMediaButtonPopup(widget_name, shortcode, widget_title)
{
	gp_hide_all_widget_popups();
    var tag = jQuery('<div id="gold_plugins_popup"></div>');
	var params = { 
		action: 'gold_plugins_insert_widget_popup',
		widget_name: widget_name,
		shortcode: shortcode
	};
	
	if (typeof(widget_title) == 'undefined') {
		widget_title = '';
	}
	
    jQuery.ajax({
      url: ajaxurl,
	  data: params,
      success: function(data) {
			dialog_options = {
				modal: true,
				width: 450,
				title: widget_title
			};
			tag.html(data).dialog(dialog_options).dialog('open');
      }
    });
	gp_hide_all_media_button_menus();
	//gp_close_popup_on_outside_click();
}

function gp_insertWidgetIntoPost()
{
	// get the real names of all fields
	var frm = jQuery('#gold_plugins_popup form');
	var shortcode = frm.data('shortcode');
	if (shortcode.length == 0) {
		return; // invalid shortcode, abort
	}
	var output = '[' + shortcode;
	frm.find(':input').each(function ()
	{
		var val = jQuery(this).val();
		// skip values that are marked 'shortcode-hidden'
		if (jQuery(this).data('shortcode-hidden')) {
			return true;
		}
		
		// skip checkboxes and radio buttons that are not checked
		if (jQuery(this).attr('type') == 'radio' && jQuery(this).attr('checked') != 'checked' ) {
			return true;
		}
		else if (jQuery(this).attr('type') == 'checkbox' && jQuery(this).attr('checked') != 'checked' ) {
			if ( typeof( jQuery(this).data('shortcode-value-if-unchecked') ) == 'undefined' ) {
				return true;
			} else {
				// if this is a checkbox and it has the value if unchecked data attribute
				// set the value used in the composed string to the value of this attribute
				val = jQuery(this).data('shortcode-value-if-unchecked');
			}
		}
		else if (jQuery(this).attr('type') == 'hidden') {
			return true;
		}
		
		var old_name = jQuery(this).attr('name');
		var override_name = jQuery(this).data('shortcode-key');
		var real_name = '';
		
		if (override_name) {
			real_name = override_name;
		} else {
			var pos = old_name.indexOf('[__i__]');			
			if (pos > 0) {
				var real_name = old_name.substr(pos + 8);
				real_name = real_name.substr(0, real_name.length - 1); // chip off trailing '['			
			}			
		}

		if ( real_name && !gp_value_is_empty(val) ) {
			output += ' ' + real_name + '="' + val + '"';
		}
		
	});
	output += ']'; // close the shortcode
	wp.media.editor.insert(output)
	
	gp_hide_all_widget_popups();
	gp_hide_all_media_button_menus();
}

var gp_value_is_empty = function (val) {
	
	// undefined and null are always considered empty
	if(typeof(val) == 'undefined' || val === null) {
		return true;
	}
 	
	// numbers and bools can never be empty
	if(typeof(val) == 'number' || typeof(val) == 'boolean') { 
		return false;
	}
	
	// if the var has a length value, check it
	if(typeof(val.length) != 'undefined') {
		return val.length == 0;
	}
	
	// check for empty array
	for(var i in val)
	{
		if(val.hasOwnProperty(i))
		{
			// array is not empty
			return false; 
		}
	}
	
	// no values in the array, so value is empty
	return true;
};

var gp_hide_all_media_button_menus = function (button_group) {
	jQuery('.gp_media_button_group_dropdown').css('display', 'none');
};

var gp_hide_all_widget_popups = function (button_group) {
	jQuery('#gold_plugins_popup').each(function () {
		jQuery(this).dialog('destroy');
	});
};

var gp_toggle_media_button_menu = function (button_group) {
	button_group = jQuery(button_group);
	if (!button_group.is('.gp_media_button_group')) {
		button_group = button_group.parents('.gp_media_button_group:first');
	}
	
	var dd = button_group.find('.gp_media_button_group_dropdown');
	if (dd.is(':visible')) {
		dd.css('display', 'none');
	} else {
		dd.css('display', 'block');			
	}
};

jQuery(function () {
	var groups = jQuery('.gp_media_button_group');
	groups.on('click', '.gp_media_button_group_toggle', function () {	
		gp_toggle_media_button_menu(this);
	});
});
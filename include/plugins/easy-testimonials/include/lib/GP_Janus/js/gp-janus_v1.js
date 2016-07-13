/* JANUS v1.0 */
jQuery(function () {
	(function() {
		
		// safe logging function (makes sure console is defined)
		var log = function (msg) {
			if(typeof('console') !== 'undefined') {
				console.log(msg);
			}
		};
		
		// get all un-initialized forms
		var get_forms = function() {
			return jQuery('.gp_widget_form_wrapper').not(function () {
				return (jQuery(this).find('.gp_janus').length > 0);
			});
		};

		// wire up all forms
		var init_forms = function () {
			get_forms().each(init_fieldsets);
			recenter_dialogs();
		};

		// initialize fieldsets
		var init_fieldsets = function(index, me) {
			var fieldsets = jQuery(me).find('fieldset');
			fieldsets.addClass('gp_janus')
			fieldsets.each( function () {
				collapse_fieldset(this);
			});
		};
		
		// show/hide the specified fieldset
		var toggle_fieldset = function (me) {
			var fs = jQuery(me);
			if( fs.hasClass('gp_janus_open') ) {
				collapse_fieldset(fs, true);
			} else {
				expand_fieldset(fs, true);
			}
		};
			
		var expand_fieldset = function (fieldset, animated) {
			if (typeof(animated) == 'undefined') {
				animated = false;
			}
			var duration = animated ? 500 : 0;			
			
			// show all elements inside the fieldset
			jQuery(fieldset).find("*:not('legend')")
							.show();
					
			// determine height when all elements are shown
			jQuery(fieldset).css('height', 'auto');
			var auto_height = jQuery(fieldset).height();
			jQuery(fieldset).css('height', '1px');
			
			// animate to the full height
			jQuery(fieldset).removeClass('gp_janus_closed')
							.addClass('gp_janus_open')						
							.animate({'height': auto_height}, function () {
								jQuery(this).trigger("update");
							});
		};
		
		var collapse_fieldset = function(fieldset, animated) {
			if (typeof(animated) == 'undefined') {
				animated = false;
			}			
			var duration = animated ? 500 : 0;
			
			// shrink fieldset to 1px tall and hide elements inside it
			jQuery(fieldset).animate({'height': '1px'}, duration, function () {
								jQuery(this).find("*:not('legend')")
											.hide();
								jQuery(this).removeClass('gp_janus_open')
											.addClass('gp_janus_closed')
											.trigger("update");
							});
		};
		
		var recenter_dialogs = function () {
			if (typeof(jQuery.dialog) !== 'undefined') {
				jQuery('#gold_plugins_popup').dialog('option', 'position', { my: "top", at: "top+40", of: window });
			}
		};

		/* main */
		(function () {
			init_forms();
			jQuery('.ui-droppable').bind('drop', function () {
				init_forms();
			});
			
			jQuery(document).on('widget-added expanded ajaxComplete', function () {
				init_forms();
			});
			
			jQuery(document).on('click', '.gp_janus legend', function () {
				var p = jQuery(this).parents('fieldset:first');
				toggle_fieldset(p);
			});
			
			// one for good measure
			setTimeout(init_forms, 1000);
			
		})();
		
	})();
	
}); // jq ready
var gold_plugins_init_coupon_box = function () {
	var $form = jQuery('#mc-embedded-subscribe-form');
	if ($form.length > 0) {
	
		var btn = $form.find('.smallBlueButton');
		
		//if already subscribed, cut to the chase
		if ( (jQuery('#gold_plugins_already_subscribed').val() == 1) ) {
			gold_plugins_ml_ajax_success($form, btn, 0);
		}
	
		// bind to form's submit action to reveal coupon box
		$form.bind('submit', function () {
			
			var btn = jQuery(this).find('.smallBlueButton');
			btn.val('Sending Now...');
			var $ajax_url = 'https://goldplugins.com/list-manage/ajax.php';
			var $ajax_data = $form.serialize();
			jQuery.ajax(
			{
				url: $ajax_url,
				data: $ajax_data,
				dataType: 'jsonp',
				success: function (dat) {
					setTimeout(function () {
						gold_plugins_ml_ajax_success($form, btn);
						// tell wordpress to always show the "after" state from now on
						setUserSetting( '_gp_ml_has_subscribed', '1' );
					}, 300);
				},
				error: function () {
					// reset the box, so they can at least try again
					setUserSetting( '_gp_ml_has_subscribed', '0' );
				}
			});
			
			// stop the form's normal submit process
			return false;
		});
	}
};

var gold_plugins_ml_ajax_success = function ($form, btn, speed) {
	if(typeof(speed) == 'undefined') {
		speed = 400;
	}
	$form.find('.fields_wrapper').slideUp(speed);
	$cpn_box = gold_plugins_get_coupon_box_new();
	btn.val('Coupon sent!');
	$cpn_box.fadeIn((speed * 2));
	btn.after($cpn_box);
};

var gold_plugins_get_coupon_box_new = function () {
	var coupon_html = 
	'<div id="mc-show-coupon-codes" class="modern">' + 
		'<h3>Your Coupon Code Is On The Way!</h3>' +
		'<p class="thx">We\'ve sent you an email with your coupon code for 10% off @plugin_name! If you don\'t see it within a few minutes, you might want to look in your Junk Mail folder.</p>' + 
		'<h4><strong>Ready to buy now?</strong></h4>' +
		'<p class="thx">If you\'re ready to buy now, <a href="@personal_url" target="_blank">click here to visit the pricing page</a>. Your coupon code will be applied automatically.</p>' +
	'</div>';
	
	// replace links in the HTML before inserting it
	$plugin_name = jQuery('#mc-upgrade-plugin-name').val();
	$personal_url = jQuery('#mc-upgrade-link-per').val();
	$biz_url = jQuery('#mc-upgrade-link-biz').val();
	$dev_url = jQuery('#mc-upgrade-link-dev').val();
	coupon_html = coupon_html.replace(/@plugin_name/g, $plugin_name);
	coupon_html = coupon_html.replace(/@personal_url/g, $personal_url);
	coupon_html = coupon_html.replace(/@biz_url/g, $biz_url);
	coupon_html = coupon_html.replace(/@dev_url/g, $dev_url);						
	var coupon_div = jQuery(coupon_html);

	// make the whole buttons clickable
	coupon_div.on('click', '.upgrade_link', function (e) {
		if( !jQuery("a").is(e.target) ) {
			$href = jQuery(this).find('a:first').attr('href');
			// try to open in a new tab
			window.open(
			  $href,
			  '_blank'
			);
			return false;			
		}
		return true;
	});	
	return coupon_div;
};
<?php
/**
 * head-pre.inc.php - Includes before the <head> tag
 *
 * @version     1.0.1
 * @package     tpl_lyquix
 * @author      Lyquix
 * @copyright   Copyright (C) 2015 - 2017 Lyquix
 * @license     GNU General Public License version 2 or later
 * @link        https://github.com/Lyquix/tpl_lyquix
 */
 
// set some base vars
$site_abs_url = get_site_url();
$site_rel_url = wp_make_link_relative($site_abs_url);
$tmpl_url = get_template_directory_uri();
$tmpl_path = get_template_directory();
$cdnjs_url = '//cdnjs.cloudflare.com/ajax/libs/';

// Enable jQuery
if(get_theme_mod('jQuery') == 0){  
	wp_deregister_script("jquery");
}
else {
	wp_enqueue_script("jquery");
}

// Enable jQuery UI
if(get_theme_mod('jQuery_ui') == 0){  
	wp_deregister_script("jquery-ui-core");
	wp_deregister_script("jquery-ui-sortable");
} 
else { 
	wp_enqueue_script("jquery-ui-core");
	if(get_theme_mod('jQuery_ui') == 2) wp_enqueue_script("jquery-ui-sortable");
}

// declare some variables
$mobile = $phone = $tablet = false;

// Check if we are on the home page
$home = is_front_page();

// Check if we are on a mobile device, whether smartphone or tablet
if(get_theme_mod('mobiledetect_method', 'php') == 'php') {
	require_once($tmpl_path . '/php/Mobile_Detect.php');
	$detect = new Mobile_Detect;
	if($detect->isMobile()){
		$mobile = true;
		if($detect->isTablet()){ $tablet = true; }
		if($detect->isPhone()){ $phone = true; }
	}
}
?>
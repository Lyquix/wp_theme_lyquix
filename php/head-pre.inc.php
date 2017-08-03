<?php
$remove_css_libraries = explode("\n", get_theme_mod('remove_css_js_libraries', ''));
foreach($remove_css_libraries as $css_url) {
	$css_url = trim($css_url);
	if($css_url) {
        wp_deregister_style($css_url);
	}
}
$remove_js_libraries = explode("\n", get_theme_mod('remove_js_libraries', ''));
foreach($remove_js_libraries as $js_url) {
	$js__url = trim($js_url);
	if($js_url) {
        wp_deregister_script($js_url);
	}
}

if(get_theme_mod('jQuery') == 0){  
	wp_deregister_script("jquery");
}
if(get_theme_mod('jQuery_ui') !== 0){  
	wp_deregister_script("jQuery UI");
} else { 
	print_r('ui enabled');
	wp_enqueue_script("jquery-ui-core");
	if(get_theme_mod('jQuery_ui') == 2) wp_enqueue_script("jquery-ui-sortable");
}
$home = $mobile = $phone = $tablet = false;
if(get_theme_mod('mobiledetect_method', 'php') == 'php') {
	require_once(__DIR__ . '/Mobile_Detect.php');
	$detect = new Mobile_Detect;
	if($detect->isMobile()){
		$mobile = true;
		if($detect->isTablet()){ $tablet = true; }
		if($detect->isPhone()){ $phone = true; }
	}
}
?>		
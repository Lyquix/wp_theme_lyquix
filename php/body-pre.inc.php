<?php 
if(@!is_array($body_classes)) $body_classes = array();
if($home) $body_classes[] = 'home';
if($phone) $body_classes[] = 'phone';
if($tablet) $body_classes[] = 'tablet';

if(is_array(get_theme_mod('fluid_screen')) && ((get_theme_mod('fluid_device', 'any') == 'any') || (get_theme_mod('fluid_device') == 'mobile' && $mobile) || (get_theme_mod('fluid_device') == 'phone' && $phone) || (get_theme_mod('fluid_device') == 'tablet' && $tablet) )) {
	foreach(get_theme_mod('fluid_screen') as $fluid_screen){
		$body_classes[] = ' blkfluid-' . $fluid_screen;
	}
}
?>
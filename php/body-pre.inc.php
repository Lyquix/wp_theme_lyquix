<?php 
/**
 * body-pre.inc.php - Includes before the <body> tag
 *
 * @version     1.0.1
 * @package     tpl_lyquix
 * @author      Lyquix
 * @copyright   Copyright (C) 2015 - 2017 Lyquix
 * @license     GNU General Public License version 2 or later
 * @link        https://github.com/Lyquix/tpl_lyquix
 */

// Prepare array of classes for body tag
if(@!is_array($body_classes)) $body_classes = array();
if($home) $body_classes[] = 'home';
if($phone) $body_classes[] = 'phone';
if($tablet) $body_classes[] = 'tablet';

if(is_array(get_theme_mod('fluid_screen')) && 
	((get_theme_mod('fluid_device', 'any') == 'any') || 
	(get_theme_mod('fluid_device') == 'mobile' && $mobile) || 
	(get_theme_mod('fluid_device') == 'phone' && $phone) || 
	(get_theme_mod('fluid_device') == 'tablet' && $tablet) )) {
	foreach(get_theme_mod('fluid_screen') as $fluid_screen){
		$body_classes[] = ' blkfluid-' . $fluid_screen;
	}
}
?>
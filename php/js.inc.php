<?php
/**
 * js.inc.php - Includes JavaScript libraries
 *
 * @version     1.0.12
 * @package     wp_theme_lyquix
 * @author      Lyquix
 * @copyright   Copyright (C) 2015 - 2017 Lyquix
 * @license     GNU General Public License version 2 or later
 * @link        https://github.com/Lyquix/wp_theme_lyquix
 */

if(get_theme_mod('angularjs', 0)): ?>
<script src="<?php echo $cdnjs_url; ?>angular.js/1.6.1/angular<?php echo get_theme_mod('non_min_js') ? '' : '.min'; ?>.js"></script>
<?php endif;
if(get_theme_mod('lodash', 0)): ?>
<script src="<?php echo $cdnjs_url; ?>lodash.js/4.17.4/lodash<?php echo get_theme_mod('non_min_js') ? '' : '.min'; ?>.js"></script>
<?php endif;
if(get_theme_mod('smoothscroll', 0)): ?>
<script src="<?php echo $cdnjs_url; ?>smoothscroll/1.4.6/SmoothScroll<?php echo get_theme_mod('non_min_js') ? '' : '.min'; ?>.js"></script>
<?php endif;
if(get_theme_mod('momentjs', 0)): ?>
<script src="<?php echo $cdnjs_url; ?>moment.js/2.18.1/moment<?php echo get_theme_mod('non_min_js') ? '' : '.min'; ?>.js"></script>
<?php endif;
if(get_theme_mod('dotdotdot', 0)): ?>
<script src="<?php echo $cdnjs_url; ?>jQuery.dotdotdot/1.7.4/jquery.dotdotdot<?php echo get_theme_mod('non_min_js') ? '' : '.min'; ?>.js"></script>
<?php endif;
if(get_theme_mod('mobiledetect_method', 'php') == 'js'): ?>
<script src="<?php echo $cdnjs_url; ?>mobile-detect/1.3.6/mobile-detect<?php echo get_theme_mod('non_min_js') ? '' : '.min'; ?>.js"></script>
<?php endif;
$add_js_libraries = explode("\n", trim(get_theme_mod('add_js_libraries', '')));
foreach($add_js_libraries as $jsurl) {
	$jsurl = trim($jsurl);
	if($jsurl) {
		echo '<script src="' . $jsurl . '"></script>';
	}
}
?>
<script src="<?php echo $tmpl_url; ?>/js/lyquix<?php echo get_theme_mod('non_min_js') ? '' : '.min'; ?>.js?v=<?php echo date("YmdHis", filemtime($tmpl_path . '/js/lyquix' . (get_theme_mod('non_min_js') ? '' : '.min') . '.js')); ?>"></script>
<?php if(file_exists($tmpl_path . '/js/scripts.js')): ?>
<script src="<?php echo $tmpl_url; ?>/js/scripts<?php echo get_theme_mod('non_min_js') ? '' : '.min'; ?>.js?v=<?php echo date("YmdHis", filemtime($tmpl_path . '/js/scripts' . (get_theme_mod('non_min_js') ? '' : '.min') . '.js')); ?>"></script>
<?php endif;

// Set lqx options
$lqx_options = [
    'bodyScreenSize' => [
        'min' => get_theme_mod('min_screen', 0),
        'max' => get_theme_mod('max_screen', 4)
    ]
];

if(get_theme_mod('analytics_account', '') || get_theme_mod('ga4_account', '')) {
    $lqx_options['ga'] = [
        'createParams' => [
            'default' => [
                'trackingId' => get_theme_mod('analytics_account'),
                'measurementId' => get_theme_mod('ga4_account'),
                'cookieDomain' => 'auto'
            ]
        ]
    ];
}

if(get_theme_mod('using_gtm', 0)) $lqx_options['usingGTM'] = true;

// Merge with options from template settings
$lqx_options = array_replace_recursive($lqx_options, json_decode(get_theme_mod('lqx_options', '{}'), true));

?>
<script>lqx.setOptions(<?php echo json_encode($lqx_options); ?>);</script>

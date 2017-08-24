<?php
if(get_theme_mod('angularjs', 0)): ?>
<script src="<?php echo $cdnjs_url; ?>angular.js/1.6.1/angular<?php echo get_theme_mod('non_min_js') ? '' : '.min'; ?>.js"></script>
<?php endif;
if(get_theme_mod('lodash', 0)): ?>
<script src="<?php echo $cdnjs_url; ?>lodash.js/4.17.4/lodash<?php echo get_theme_mod('non_min_js') ? '' : '.min'; ?>.js"></script>
<?php endif;
if(get_theme_mod('smoothscroll', 0)): ?>
<script src="<?php echo $cdnjs_url; ?>smoothscroll/1.4.6/SmoothScroll<?php echo get_theme_mod('non_min_js') ? '' : '.min'; ?>.js"></script>
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
<script src="<?php echo $tmpl_url; ?>/js/scripts<?php echo get_theme_mod('non_min_js') ? '' : '.min'; ?>.js?v=<?php echo date("YmdHis", filemtime($tmpl_path . '/js/scripts' . (get_theme_mod('non_min_js') ? '' : '.min') . '.js')); ?>"></script>
<script>lqx.setOptions({
	bodyScreenSize: {min: <?php echo get_theme_mod('min_screen', 0); ?>, max: <?php echo get_theme_mod('max_screen', 4); ?>}<?php if(get_theme_mod('ga_account')) : ?>,
	ga: {createParams: {default: {trackingId: '<?php echo get_theme_mod('ga_account'); ?>', cookieDomain: 'auto'}}}<?php endif; ?>
});</script>
<?php if(get_theme_mod('lqx_options', '{}') != '{}') : ?>
<script>lqx.setOptions(<?php echo get_theme_mod('lqx_options', '{}'); ?>);</script>
<?php endif; ?>
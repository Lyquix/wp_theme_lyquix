<?php if(get_theme_mod('lessjs')): ?>
	<script src="<?php echo $tmpl_url; ?>/js/less<?php echo get_theme_mod('non_min_js') ? '' : '.min'; ?>.js"></script>
	<?php endif;
	if(get_theme_mod('es5_shim', 0) || get_theme_mod('es5_es6_shim', 0)): ?>
	<script src="<?php echo $tmpl_url; ?>/js/es5-shim<?php echo get_theme_mod('non_min_js') ? '' : '.min'; ?>.js?v=<?php echo date("YmdHis", filemtime($tmpl_path . '/js/es5-shim' . (get_theme_mod('non_min_js') ? '' : '.min') . '.js')); ?>"></script>
	<script src="<?php echo $tmpl_url; ?>/js/es5-sham<?php echo get_theme_mod('non_min_js') ? '' : '.min'; ?>.js?v=<?php echo date("YmdHis", filemtime($tmpl_path . '/js/es5-sham' . (get_theme_mod('non_min_js') ? '' : '.min') . '.js')); ?>"></script>
	<?php endif;
	if(get_theme_mod('es5_es6_shim', 0)): ?>
	<script src="<?php echo $tmpl_url; ?>/js/es6-shim<?php get_theme_mod('non_min_js') ? '' : '.min'; ?>.js?v=<?php echo date("YmdHis", filemtime($tmpl_path . '/js/es6-shim' . (get_theme_mod('non_min_js') ? '' : '.min') . '.js')); ?>"></script>
	<script src="<?php echo $tmpl_url; ?>/js/es6-sham<?php get_theme_mod('non_min_js') ? '' : '.min'; ?>.js?v=<?php echo date("YmdHis", filemtime($tmpl_path . '/js/es6-sham' . (get_theme_mod('non_min_js') ? '' : '.min') . '.js')); ?>"></script>
	<?php endif;
	if(get_theme_mod('json3', 0)): ?>
	<script src="<?php echo $tmpl_url; ?>/js/json3<?php echo get_theme_mod('non_min_js') ? '' : '.min'; ?>.js?v=<?php echo date("YmdHis", filemtime($tmpl_path . '/js/json3' . (get_theme_mod('non_min_js') ? '' : '.min') . '.js')); ?>"></script>
	<?php endif;
	if(get_theme_mod('angularjs', 0)): ?>
	<script src="<?php echo $tmpl_url; ?>/js/angular<?php echo get_theme_mod('non_min_js') ? '' : '.min'; ?>.js?v=<?php echo date("YmdHis", filemtime($tmpl_path . '/js/angular' . (get_theme_mod('non_min_js') ? '' : '.min') . '.js')); ?>"></script>
	<?php endif;
	if(get_theme_mod('lodash', 0)): ?>
	<script src="<?php echo $tmpl_url; ?>/js/lodash<?php echo get_theme_mod('non_min_js') ? '' : '.min'; ?>.js?v=<?php echo date("YmdHis", filemtime($tmpl_path . '/js/lodash' . (get_theme_mod('non_min_js') ? '' : '.min') . '.js')); ?>"></script>
	<?php endif;
	if(get_theme_mod('mobiledetect_method', 'php') == 'js'): ?>
	<script src="<?php echo $tmpl_url; ?>/js/mobile-detect<?php echo get_theme_mod('non_min_js') ? '' : '.min'; ?>.js?v=<?php echo date("YmdHis", filemtime($tmpl_path . '/js/mobile-detect' . (get_theme_mod('non_min_js') ? '' : '.min') . '.js')); ?>"></script>
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
	<?php echo get_theme_mod('lqx_options') ? '<script>lqx.setOptions(' . get_theme_mod('lqx_options') . ');</script>' : '';
	echo '<script>lqx.setOptions({bodyScreenSize: {min: ' . get_theme_mod('min_screen', 0) . ', max: ' . get_theme_mod('max_screen', 4) . '}});</script>'; ?>
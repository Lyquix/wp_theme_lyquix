<?php
$add_css_libraries = explode("\n", trim(get_theme_mod('add_css_libraries', '')));
foreach($add_css_libraries as $cssurl) {
	$cssurl = trim($cssurl);
	if($cssurl) {
		echo '<link href="' . $cssurl . '" rel="stylesheet" />';
	}
}

if(get_theme_mod('bootstrap', 0)):?>
<link rel="stylesheet" href="<?php echo $cdnjs_url; ?>twitter-bootstrap/3.3.7/css/bootstrap<?php echo get_theme_mod('non_min_js') ? '' : '.min'; ?>.css">
<link rel="stylesheet" href="<?php echo $cdnjs_url; ?>twitter-bootstrap/3.3.7/css/bootstrap-theme<?php echo get_theme_mod('non_min_js') ? '' : '.min'; ?>.css">
<script src="<?php echo $cdnjs_url; ?>twitter-bootstrap/3.3.7/js/bootstrap<?php echo get_theme_mod('non_min_js') ? '' : '.min'; ?>.js"></script>
<?php endif; ?>

<link href="<?php echo $tmpl_url; ?>/css/styles.<?php echo get_theme_mod('lessjs') ? 'less' : 'css'; ?>?v=<?php echo date("YmdHis", filemtime($tmpl_path . '/css/styles.' . (get_theme_mod('lessjs') ? 'less' : 'css'))); ?>" rel="stylesheet" <?php echo get_theme_mod('lessjs') ? 'type="text/less" ' : ''; ?>/>

<?php if(get_theme_mod('lessjs')): ?>
<script src="<?php echo $cdnjs_url; ?>less.js/2.7.2/less<?php echo get_theme_mod('non_min_js') ? '' : '.min'; ?>.js"></script>
<?php endif; ?>

<?php if(file_exists($tmpl_path . '/css/styles.0.css')): ?>
<!--[if lte IE 9]>
<script>
// Unload main styles.css file
(function() {
	var stylesheet = document.getElementById('stylesheet');
	stylesheet.parentNode.removeChild(stylesheet);
})();
</script>
<?php
$styles_idx = 0; 
while(file_exists($tmpl_path . '/css/styles.' . $styles_idx . '.css')) {
	echo '<link href="' . $tmpl_url . '/css/styles.' . $styles_idx . '.css?v=' . date("YmdHis", filemtime($tmpl_path . '/css/styles.' . $styles_idx . '.css')) . '" rel="stylesheet" />';
	$styles_idx++;
}
?>
<![endif]-->
<?php endif; 
if(file_exists($tmpl_path . '/css/ie9.css')): ?>
<!--[if lte IE 9]>
<link href="<?php echo $tmpl_url; ?>/css/ie9.css?v=<?php echo date("YmdHis", filemtime($tmpl_path . '/css/ie9.css')); ?>" rel="stylesheet" />
<![endif]-->
<?php endif; ?>
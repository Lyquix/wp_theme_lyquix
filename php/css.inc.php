<link href="<?php echo $tmpl_url; ?>/css/styles.<?php echo get_theme_mod('lessjs') ? 'less' : 'css'; ?>?v=<?php echo date("YmdHis", filemtime($tmpl_path . '/css/styles.' . (get_theme_mod('lessjs') ? 'less' : 'css'))); ?>" rel="stylesheet" <?php echo get_theme_mod('lessjs') ? 'type="text/less" ' : ''; ?>/>

<?php

$styles_idx = 0; 
while(file_exists($tmpl_path . '/css/styles.' . $styles_idx . '.css')) {
	echo '<link href="' . $tmpl_url . '/css/styles.' . $styles_idx . '.css?v=' . date("YmdHis", filemtime($tmpl_path . '/css/styles.' . $styles_idx . '.css')) . '" rel="stylesheet" />';
	$styles_idx++;
}

$add_css_libraries = explode("\n", trim(get_theme_mod('add_css_libraries', '')));
	foreach($add_css_libraries as $cssurl) {
		$cssurl = trim($cssurl);
		if($cssurl) {
			echo '<link href="' . $cssurl . '" rel="stylesheet" />';
		}
	}

?>		
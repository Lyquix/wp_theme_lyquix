<link href="<?php echo $tmpl_url; ?>/css/styles.<?php echo get_theme_mod('lessjs') ? 'less' : 'css'; ?>?v=<?php echo date("YmdHis", filemtime($tmpl_path . '/css/styles.' . (get_theme_mod('lessjs') ? 'less' : 'css'))); ?>" rel="stylesheet" <?php echo get_theme_mod('lessjs') ? 'type="text/less" ' : ''; ?>/>
<link href="<?php echo $tmpl_url; ?>/css/icons.<?php echo get_theme_mod('lessjs') ? 'less' : 'css'; ?>?v=<?php echo date("YmdHis", filemtime($tmpl_path . '/css/icons.' . (get_theme_mod('lessjs') ? 'less' : 'css'))); ?>" rel="stylesheet" <?php echo get_theme_mod('lessjs') ? 'type="text/less" ' : ''; ?>/>
<?php

$add_css_libraries = explode("\n", trim(get_theme_mod('add_css_libraries', '')));
	foreach($add_css_libraries as $cssurl) {
		$cssurl = trim($cssurl);
		if($cssurl) {
			echo '<link href="' . $cssurl . '" rel="stylesheet" />';
		}
	}

?>		
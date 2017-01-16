<script>
lqx.bodyScreenSize();
lqx.vars.siteURL = '<?php echo $site_abs_url; ?>';
lqx.vars.tmplURL = '<?php echo $site_abs_url; ?>';
<?php if(get_theme_mod('mobiledetect_method', 'php') == 'js'): ?>lqx.mobileDetect = lqx.mobileDetect();
<?php endif;
if(get_theme_mod('mobiledetect_method', 'php') == 'php'){
	echo 'lqx.mobileDetect = {mobile: ' . ($mobile ? 'true' : 'false') . ',phone: ' . ($phone ? 'true' : 'false') . ',tablet: ' . ($tablet ? 'true' : 'false') . "};\n";
}?>
</script>
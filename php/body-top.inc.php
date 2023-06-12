<?php
	$site_abs_url = get_site_url();
?>
<script>
lqx.bodyScreenSize();
lqx.vars.siteURL = '<?php echo $site_abs_url; ?>';
lqx.vars.tmplURL = '<?php echo $site_abs_url."/wp-content/themes/lyquix/"; ?>';
<?php
if(get_theme_mod('mobiledetect_method', 'php') == 'js') echo "lqx.mobileDetect = lqx.mobileDetect();\n";
if(get_theme_mod('mobiledetect_method', 'php') == 'php'){
	echo 'lqx.mobileDetect = {mobile: ' . ($mobile ? 'true' : 'false') . ',phone: ' . ($phone ? 'true' : 'false') . ',tablet: ' . ($tablet ? 'true' : 'false') . "};\n";
}
?>
</script>
<?php
echo get_theme_mod('gtm_account') ? "<!-- Google Tag Manager (noscript) -->
<noscript><iframe src=\"https://www.googletagmanager.com/ns.html?id=" . get_theme_mod('gtm_account') . "\"
height=\"0\" width=\"0\" style=\"display:none;visibility:hidden\"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->" : "";
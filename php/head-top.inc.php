<?php

if(get_theme_mod('bootstrap', 0)):?>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

<!-- Optional theme -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

<?php endif; 
if($home) {
		echo get_theme_mod('google_site_verification') ? '<meta name="google-site-verification" content="' . get_theme_mod('google_site_verification') . '" />' . "\n" : '';
		echo get_theme_mod('msvalidate') ? '<meta name="msvalidate.01" content="' . get_theme_mod('msvalidate') . '" />' . "\n" : '';
		echo get_theme_mod('p_domain_verify') ? '<meta name="p:domain_verify" content="' . get_theme_mod('p_domain_verify') . '"/>' . "\n" : '';
} 
?>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
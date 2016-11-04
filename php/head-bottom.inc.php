<?php
$analytics_account = get_theme_mod('analytics_account');
	if (!empty($analytics_account)):
		echo "<script>
		(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		})(window,document,'script','//www.google-analytics.com/analytics.js','ga');
		ga('create', '";
		echo get_theme_mod('analytics_account')."', 'auto');
		ga('send', 'pageview');
		</script>";
	endif;
	echo get_theme_mod('addthis_pubid') ? '<script src="//s7.addthis.com/js/300/addthis_widget.js#pubid=' . get_theme_mod('addthis_pubid') . '"></script>' : '';
?>	
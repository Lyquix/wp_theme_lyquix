<?php
/**
 * The template for displaying the header
 *
 * Displays all of the head element and everything up until the "site-content" div.
 *
 * @package WordPress
 * @subpackage Lyquix Theme
 * @since Lyquix Theme 1.0
 */
//Check for override 
if(file_exists(__DIR__ . '/header-custom.php')) :
	include __DIR__ . '/header-custom.php'; 
else : 
$tmpl_url = get_template_directory_uri();
$home = $mobile = $phone = $tablet = false;
if(get_theme_mod('mobiledetect_method', 'php') == 'php') {
	require_once(__DIR__ . '/php/Mobile_Detect.php');
	$detect = new Mobile_Detect;
	if($detect->isMobile()){
		$mobile = true;
		if($detect->isTablet()){ $tablet = true; }
		if($detect->isPhone()){ $phone = true; }
	}
} 
?><!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
<head>
	<?php if($home) {
		echo get_theme_mod('google_site_verification') ? '<meta name="google-site-verification" content="' . get_theme_mod('google_site_verification') . '" />' . "\n" : '';
		echo get_theme_mod('msvalidate') ? '<meta name="msvalidate.01" content="' . get_theme_mod('msvalidate') . '" />' . "\n" : '';
		echo get_theme_mod('p_domain_verify') ? '<meta name="p:domain_verify" content="' . get_theme_mod('p_domain_verify') . '"/>' . "\n" : '';
	} ?>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php 
	$remove_css_js_libraries = explode("\n", get_theme_mod('remove_css_js_libraries', ''));
		foreach($remove_css_js_libraries as $js_css_url) {
			$js_css_url = trim($js_css_url);
			if($js_css_url) {
		        $ext = pathinfo($js_css_url, PATHINFO_EXTENSION);
		        if($ext == 'css')
		            unset($doc->_styleSheets[$js_css_url]);
		        else if ($ext == 'js')
		            unset($doc->_scripts[$js_css_url]);
			}
	}
	?>
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<?php if ( is_singular() && pings_open( get_queried_object() ) ) : ?>
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
	<?php endif;
	wp_head();
	$add_css_libraries = explode("\n", trim(get_theme_mod('add_css_libraries', '')));
	foreach($add_css_libraries as $cssurl) {
		$cssurl = trim($cssurl);
		if($cssurl) {
			echo '<link href="' . $cssurl . '" rel="stylesheet" />';
		}
	}?>
	<link href="<?php echo $tmpl_url; ?>/css/styles.<?php echo get_theme_mod('lessjs') ? 'less' : 'css'; ?>?v=<?php echo date("YmdHis", filemtime($tmpl_path . '/css/styles.' . (get_theme_mod('lessjs') ? 'less' : 'css'))); ?>" rel="stylesheet" <?php echo get_theme_mod('lessjs') ? 'type="text/less" ' : ''; ?>/>
	<link href="<?php echo $tmpl_url; ?>/css/icons.<?php echo get_theme_mod('lessjs') ? 'less' : 'css'; ?>?v=<?php echo date("YmdHis", filemtime($tmpl_path . '/css/icons.' . (get_theme_mod('lessjs') ? 'less' : 'css'))); ?>" rel="stylesheet" <?php echo get_theme_mod('lessjs') ? 'type="text/less" ' : ''; ?>/>
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
	print_r(get_theme_mod('lodash'));
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
	<script src="<?php echo $tmpl_url; ?>/js/functions<?php echo get_theme_mod('non_min_js') ? '' : '.min'; ?>.js?v=<?php echo date("YmdHis", filemtime($tmpl_path . '/js/lyquix.'.get_theme_mod('non_min_js')? '' : '.min'.'js')); ?>"></script>
	<script src="<?php echo $tmpl_url; ?>/js/lyquix<?php echo get_theme_mod('non_min_js') ? '' : '.min'; ?>.js?v=<?php echo date("YmdHis", filemtime($tmpl_path . '/js/lyquix.'.get_theme_mod('non_min_js')? '' : '.min'.'js')); ?>"></script>
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
	echo '<script>lqx.setOptions({bodyScreenSize: {min: ' . get_theme_mod('min_screen', 0) . ', max: ' . get_theme_mod('max_screen', 4) . '}});</script>'; 
	echo get_theme_mod('lqx_options') ? '<script>lqx.setOptions(' . get_theme_mod('lqx_options') . ');</script>' : '';
	
	?>
</head>
<body class="<?php 
echo ($home ? 'home ' : '').
	($mobile ? 'mobile ' : '').
	($phone ? 'phone ' : '').
	($tablet ? 'tablet ' : '');
if(is_array(get_theme_mod('fluid_screen')) && ((get_theme_mod('fluid_device', 'any') == 'any') || (get_theme_mod('fluid_device') == 'mobile' && $mobile) || (get_theme_mod('fluid_device') == 'phone' && $phone) || (get_theme_mod('fluid_device') == 'tablet' && $tablet) )) {
	foreach(get_theme_mod('fluid_screen') as $fluid_screen){
		echo ' blkfluid-' . $fluid_screen;
	}
}
?>">
<script>
lqx.bodyScreenSize();
<?php if(get_theme_mod('mobiledetect_method', 'php') == 'js'): ?>lqx.mobileDetect = lqx.mobileDetect();
<?php endif;
if(get_theme_mod('mobiledetect_method', 'php') == 'php'){
	echo 'lqx.mobileDetect = {mobile: ' . ($mobile ? 'true' : 'false') . ',phone: ' . ($phone ? 'true' : 'false') . ',tablet: ' . ($tablet ? 'true' : 'false') . "};\n";
}
if(get_theme_mod('ie9_alert',0)): ?>
<!--<link href="<?php echo $tmpl_url; ?>/css/ie9-alert.css" rel="stylesheet" />
<div class="ie9-alert">You are using an unsupported version of Internet Explorer. To ensure security, performance, and full functionality, <a href="http://browsehappy.com/?locale=<?php get_locale(); ?>">please upgrade to an up-to-date browser.</a><i></i></div>
<script>jQuery('.ie9-alert i').click(function(){jQuery('.ie9-alert').hide();});</script>-->
<?php endif;
echo get_theme_mod('disqus_shortname') ? '<script src="//' . get_theme_mod('disqus_shortname') . '.disqus.com/embed.js"></script>' : ''; ?>
</script>
<div id="page" class="site">
	<div class="site-inner">
		<!--<a class="skip-link screen-reader-text" href="#content"><?php _e( 'Skip to content', 'twentysixteen' ); ?></a>-->

		<header id="masthead" class="site-header" role="banner">
			<div class="site-header-main">
				<div class="site-branding">
					<?php twentysixteen_the_custom_logo(); ?>

					<?php if ( is_front_page() && is_home() ) : ?>
						<h1 class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
					<?php else : ?>
						<p class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></p>
					<?php endif;

					$description = get_bloginfo( 'description', 'display' );
					if ( $description || is_customize_preview() ) : ?>
						<p class="site-description"><?php echo $description; ?></p>
					<?php endif; ?>
				</div><!-- .site-branding -->

				<?php if ( has_nav_menu( 'primary' ) || has_nav_menu( 'social' ) ) : ?>
					<button id="menu-toggle" class="menu-toggle"><?php _e( 'Menu', 'twentysixteen' ); ?></button>

					<div id="site-header-menu" class="site-header-menu <?php echo get_theme_mod('main_nav_class')?>">
						<?php if ( has_nav_menu( 'primary' ) ) : ?>
							<nav id="site-navigation" class="main-navigation" role="navigation" aria-label="<?php esc_attr_e( 'Primary Menu', 'twentysixteen' ); ?>">
								<?php
									wp_nav_menu( array(
										'theme_location' => 'primary',
										'menu_class'     => 'primary-menu',
									 ) );
								?>
							</nav><!-- .main-navigation -->
						<?php endif; ?>

						<?php if ( has_nav_menu( 'social' ) ) : ?>
							<nav id="social-navigation" class="social-navigation" role="navigation" aria-label="<?php esc_attr_e( 'Social Links Menu', 'twentysixteen' ); ?>">
								<?php
									wp_nav_menu( array(
										'theme_location' => 'social',
										'menu_class'     => 'social-links-menu',
										'depth'          => 1,
										'link_before'    => '<span class="screen-reader-text">',
										'link_after'     => '</span>',
									) );
								?>
							</nav><!-- .social-navigation -->
						<?php endif; ?>
					</div><!-- .site-header-menu -->
					<?php if ( get_header_image() ) : ?>
				<?php
					/**
					 * Filter the default twentysixteen custom header sizes attribute.
					 *
					 * @since Twenty Sixteen 1.0
					 *
					 * @param string $custom_header_sizes sizes attribute
					 * for Custom Header. Default '(max-width: 709px) 85vw,
					 * (max-width: 909px) 81vw, (max-width: 1362px) 88vw, 1200px'.
					 */
					$custom_header_sizes = apply_filters( 'twentysixteen_custom_header_sizes', '(max-width: 709px) 85vw, (max-width: 909px) 81vw, (max-width: 1362px) 88vw, 1200px' );
				?>
				<div class="header-image">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
						<img src="<?php header_image(); ?>" srcset="<?php echo esc_attr( wp_get_attachment_image_srcset( get_custom_header()->attachment_id ) ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>">
					</a>
				</div><!-- .header-image -->
			<?php endif; // End header image check. ?>
				<?php endif; ?>
			</div><!-- .site-header-main -->
		</header><!-- .site-header -->
		<?php $main_class = get_theme_mod('main_wrapper_class');?>
		<div id="content" class="site-content <?php echo $main_class;?>">
<?php endif;

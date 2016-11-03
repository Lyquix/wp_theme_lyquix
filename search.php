<?php
/**
 * The template for displaying search results pages
 *
 */
$tmpl_url = get_template_directory_uri();
if(file_exists(__DIR__ . '/search-custom.php')) :
	include __DIR__ . '/search-custom.php'; 
else : 
?><!DOCTYPE html>
<html <?php language_attributes(); ?> >
	<head>
		<jdoc:include type="head" />
	</head>
	<body>
		File <span style="font-family: monospace;"><?php echo $tmp_url;?>/search-custom.php</span> not found.
	</body>
</html>
<?php endif;?>

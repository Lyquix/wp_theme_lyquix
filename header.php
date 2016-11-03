<?php
/**
 * The template for displaying the header
 *
 * Displays all of the head element and everything up until the "site-content" div.
 *
 * NOTICE: Do not modify this file!
 * If you need to customize your template, create a file named custom-index.php
**/

//Check for override 
if(file_exists(__DIR__ . '/header-custom.php')) :
	include __DIR__ . '/header-custom.php'; 
else : 
?><!DOCTYPE html>
<html <?php language_attributes(); ?> >
	<head>
		<jdoc:include type="head" />
	</head>
	<body>
		File <span style="font-family: monospace;"><?php echo JPATH_BASE . '/templates/' . $this->template; ?>/header-custom.php</span> not found.
	</body>
</html>
<?php endif;?>
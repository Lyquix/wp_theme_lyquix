<?php
/**
 * The template for displaying search results pages
 *
 */
if(file_exists(__DIR__ . '/search-custom.php')) :
	include __DIR__ . '/search-custom.php'; 
else : 
?><!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" >
	<head>
		<jdoc:include type="head" />
	</head>
	<body>
		File <span style="font-family: monospace;"><?php echo JPATH_BASE . '/templates/' . $this->template; ?>/search-custom.php</span> not found.
	</body>
</html>
<?php endif;?>

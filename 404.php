<?php

/**
*** NOTICE: Do not modify this file!
*** If you need to customize your template, create a file named custom-index.php
**/

if(file_exists(__DIR__ . '/404-custom.php')) :
	include __DIR__ . '/404-custom.php'; 
else : 
?><!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" >
	<head>
		<jdoc:include type="head" />
	</head>
	<body>
		File <span style="font-family: monospace;"><?php echo JPATH_BASE . '/templates/' . $this->template; ?>/404-custom.php</span> not found.
	</body>
</html>
<?php endif;?>

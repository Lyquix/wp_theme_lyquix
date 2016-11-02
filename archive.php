<?php

if(file_exists(__DIR__ . '/archive-custom.php')) :
	include __DIR__ . '/archive-custom.php'; 
else : 
?><!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" >
	<head>
		<jdoc:include type="head" />
	</head>
	<body>
		File <span style="font-family: monospace;"><?php echo JPATH_BASE . '/templates/' . $this->template; ?>/archive-custom.php</span> not found.
	</body>
</html>
<?php endif;?>
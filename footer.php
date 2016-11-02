<?php

 //Check for override 
if(file_exists(__DIR__ . '/footer-custom.php')) :
	include __DIR__ . '/footer-custom.php'; 
else : 
?>
<p>File <span style="font-family: monospace;"><?php echo JPATH_BASE . '/templates/' . $this->template; ?>/footer-custom.php</span> not found.</p>
<?php endif;?>

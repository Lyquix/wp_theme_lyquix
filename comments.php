<?php

if(file_exists(__DIR__ . '/comments-custom.php')) :
	include __DIR__ . '/comments-custom.php'; 
else : 
?>
<p>File <span style="font-family: monospace;"><?php echo JPATH_BASE . '/templates/' . $this->template; ?>/comments-custom.php</span> not found.</p>
<?php endif;?>

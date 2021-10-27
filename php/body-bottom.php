<?php
/**
 * body-bottom.php - Code loaded at the end of the <body> tag
 *
 * @version     2.3.2
 * @package     wp_theme_lyquix
 * @author      Lyquix
 * @copyright   Copyright (C) 2015 - 2018 Lyquix
 * @license     GNU General Public License version 2 or later
 * @link        https://github.com/Lyquix/wp_theme_lyquix
 */

 // Load GTM body code
if(get_theme_mod('gtm_account', '')): ?>
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo get_theme_mod('gtm_account'); ?>" height="0" width="0" style="display:none; visibility:hidden"></iframe></noscript>
<?php endif;
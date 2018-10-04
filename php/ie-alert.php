<?php
/**
 * ie-alert.php - Includes alerts for IE
 *
 * @version     2.0.0
 * @package     wp_theme_lyquix
 * @author      Lyquix
 * @copyright   Copyright (C) 2015 - 2017 Lyquix
 * @license     GNU General Public License version 2 or later
 * @link        https://github.com/Lyquix/wp_theme_lyquix
 */

$ie9 = get_theme_mod('ie9_alert');
$ie10 = get_theme_mod('ie10_alert');
$ie11 = get_theme_mod('ie11_alert');

if($ie9 || $ie10 || $ie11) : ?>
<link href="<?php echo $tmpl_url; ?>/css/ie-alert.css" rel="preload" as="style" onload="this.rel='stylesheet'" />
<div class="ie-alert<?php echo ($ie9 ? ' ie9' : '') . ($ie10 ? ' ie10' : '') . ($ie11 ? ' ie11' : ''); ?>">You are using an unsupported version of Internet Explorer. To ensure security, performance, and full functionality, <a href="http://browsehappy.com/?locale=en">please upgrade to an up-to-date browser.</a><i></i></div>
<script>
document.querySelector('.ie-alert i').addEventListener('click', function(){document.querySelector('.ie-alert').style.display = 'none';});
</script>
<?php endif;

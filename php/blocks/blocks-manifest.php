<?php
// Auto-generated — re-run `wp lqx regenerate-block-manifest` after editing block.json files.
// WP 6.7+ block metadata collection: opcache-friendly alternative to per-request glob+JSON decode.
return array (
  'accordion' => 
  array (
    '//' => 
    array (
      0 => '/**',
      1 => '* block.json - Lyquix accordion block definition',
      2 => '*',
      3 => '* @version     3.4.0',
      4 => '* @package     wp_theme_lyquix',
      5 => '* @author      Lyquix',
      6 => '* @copyright   Copyright (C) 2015 - 2024 Lyquix',
      7 => '* @license     GNU General Public License version 2 or later',
      8 => '* @link        https://github.com/Lyquix/wp_theme_lyquix',
      9 => '*',
      10 => '*    .d8888b. 88888888888 .d88888b.  8888888b.   888',
      11 => '*   d88P  Y88b    888    d88P   Y88b 888   Y88b  888',
      12 => '*   Y88b.         888    888     888 888    888  888',
      13 => '*     Y888b.      888    888     888 888   d88P  888',
      14 => '*        Y88b.    888    888     888 8888888P    888',
      15 => '*          888    888    888     888 888         Y8P',
      16 => '*   Y88b  d88P    888    Y88b. .d88P 888            ',
      17 => '*     Y8888P      888      Y88888P   888         888',
      18 => '*',
      19 => '*  DO NOT MODIFY THIS FILE!',
      20 => '*/',
    ),
    'apiVersion' => 3,
    'name' => 'lqx/accordion',
    'title' => 'Lyquix Accordion',
    'description' => 'An Accordion block',
    'category' => 'lqx-content-blocks',
    'icon' => 'menu',
    'keywords' => 
    array (
      0 => 'accordion',
    ),
    'supports' => 
    array (
      'align' => false,
      'anchor' => true,
    ),
    'acf' => 
    array (
      'renderTemplate' => 'accordion.php',
      'blockVersion' => 3,
    ),
  ),
  'accordion-item' => 
  array (
    '//' => 
    array (
      0 => '/**',
      1 => '* block.json - Lyquix accordion item block definition',
      2 => '*',
      3 => '* @version     3.1.0',
      4 => '* @package     wp_theme_lyquix',
      5 => '* @author      Lyquix',
      6 => '* @copyright   Copyright (C) 2015 - 2024 Lyquix',
      7 => '* @license     GNU General Public License version 2 or later',
      8 => '* @link        https://github.com/Lyquix/wp_theme_lyquix',
      9 => '*',
      10 => '*    .d8888b. 88888888888 .d88888b.  8888888b.   888',
      11 => '*   d88P  Y88b    888    d88P   Y88b 888   Y88b  888',
      12 => '*   Y88b.         888    888     888 888    888  888',
      13 => '*     Y888b.      888    888     888 888   d88P  888',
      14 => '*        Y88b.    888    888     888 8888888P    888',
      15 => '*          888    888    888     888 888         Y8P',
      16 => '*   Y88b  d88P    888    Y88b. .d88P 888            ',
      17 => '*     Y8888P      888      Y88888P   888         888',
      18 => '*',
      19 => '*  DO NOT MODIFY THIS FILE!',
      20 => '*/',
    ),
    'apiVersion' => 3,
    'name' => 'lqx/accordion-item',
    'title' => 'Lyquix Accordion Item',
    'description' => 'An Accordion Item block',
    'category' => 'lqx-content-blocks',
    'icon' => 'menu',
    'keywords' => 
    array (
      0 => 'accordion',
      1 => 'accordion-item',
    ),
    'parent' => 
    array (
      0 => 'lqx/accordion-plus',
    ),
    'supports' => 
    array (
      'align' => false,
      'anchor' => true,
      'jsx' => true,
    ),
    'usesContext' => 
    array (
      0 => 'acf/fields',
    ),
    'acf' => 
    array (
      'renderTemplate' => 'accordion-item.php',
      'blockVersion' => 3,
    ),
  ),
  'accordion-plus' => 
  array (
    '//' => 
    array (
      0 => '/**',
      1 => '* block.json - Lyquix accordion plus block definition',
      2 => '*',
      3 => '* @version     3.1.0',
      4 => '* @package     wp_theme_lyquix',
      5 => '* @author      Lyquix',
      6 => '* @copyright   Copyright (C) 2015 - 2024 Lyquix',
      7 => '* @license     GNU General Public License version 2 or later',
      8 => '* @link        https://github.com/Lyquix/wp_theme_lyquix',
      9 => '*',
      10 => '*    .d8888b. 88888888888 .d88888b.  8888888b.   888',
      11 => '*   d88P  Y88b    888    d88P   Y88b 888   Y88b  888',
      12 => '*   Y88b.         888    888     888 888    888  888',
      13 => '*     Y888b.      888    888     888 888   d88P  888',
      14 => '*        Y88b.    888    888     888 8888888P    888',
      15 => '*          888    888    888     888 888         Y8P',
      16 => '*   Y88b  d88P    888    Y88b. .d88P 888            ',
      17 => '*     Y8888P      888      Y88888P   888         888',
      18 => '*',
      19 => '*  DO NOT MODIFY THIS FILE!',
      20 => '*/',
    ),
    'apiVersion' => 3,
    'name' => 'lqx/accordion-plus',
    'title' => 'Lyquix Accordion Plus',
    'description' => 'An Accordion Plus block',
    'category' => 'lqx-content-blocks',
    'icon' => 'menu',
    'keywords' => 
    array (
      0 => 'accordion',
      1 => 'plus',
    ),
    'supports' => 
    array (
      'align' => false,
      'anchor' => true,
    ),
    'providesContext' => 
    array (
      'acf/fields' => 'data',
    ),
    'acf' => 
    array (
      'renderTemplate' => 'accordion-plus.php',
      'blockVersion' => 3,
    ),
  ),
  'banner' => 
  array (
    '//' => 
    array (
      0 => '/**',
      1 => '* block.json - Lyquix banner block definition',
      2 => '*',
      3 => '* @version     3.4.0',
      4 => '* @package     wp_theme_lyquix',
      5 => '* @author      Lyquix',
      6 => '* @copyright   Copyright (C) 2015 - 2024 Lyquix',
      7 => '* @license     GNU General Public License version 2 or later',
      8 => '* @link        https://github.com/Lyquix/wp_theme_lyquix',
      9 => '*',
      10 => '*    .d8888b. 88888888888 .d88888b.  8888888b.   888',
      11 => '*   d88P  Y88b    888    d88P   Y88b 888   Y88b  888',
      12 => '*   Y88b.         888    888     888 888    888  888',
      13 => '*     Y888b.      888    888     888 888   d88P  888',
      14 => '*        Y88b.    888    888     888 8888888P    888',
      15 => '*          888    888    888     888 888         Y8P',
      16 => '*   Y88b  d88P    888    Y88b. .d88P 888            ',
      17 => '*     Y8888P      888      Y88888P   888         888',
      18 => '*',
      19 => '*  DO NOT MODIFY THIS FILE!',
      20 => '*/',
    ),
    'apiVersion' => 3,
    'name' => 'lqx/banner',
    'title' => 'Lyquix Banner',
    'description' => 'A banner block',
    'category' => 'lqx-content-blocks',
    'icon' => 'format-image',
    'keywords' => 
    array (
      0 => 'banner',
    ),
    'supports' => 
    array (
      'align' => false,
      'anchor' => true,
    ),
    'acf' => 
    array (
      'renderTemplate' => 'banner.php',
      'blockVersion' => 3,
    ),
  ),
  'cards' => 
  array (
    '//' => 
    array (
      0 => '/**',
      1 => '* block.json - Lyquix cards block definition',
      2 => '*',
      3 => '* @version     3.4.0',
      4 => '* @package     wp_theme_lyquix',
      5 => '* @author      Lyquix',
      6 => '* @copyright   Copyright (C) 2015 - 2024 Lyquix',
      7 => '* @license     GNU General Public License version 2 or later',
      8 => '* @link        https://github.com/Lyquix/wp_theme_lyquix',
      9 => '*',
      10 => '*    .d8888b. 88888888888 .d88888b.  8888888b.   888',
      11 => '*   d88P  Y88b    888    d88P   Y88b 888   Y88b  888',
      12 => '*   Y88b.         888    888     888 888    888  888',
      13 => '*     Y888b.      888    888     888 888   d88P  888',
      14 => '*        Y88b.    888    888     888 8888888P    888',
      15 => '*          888    888    888     888 888         Y8P',
      16 => '*   Y88b  d88P    888    Y88b. .d88P 888            ',
      17 => '*     Y8888P      888      Y88888P   888         888',
      18 => '*',
      19 => '*  DO NOT MODIFY THIS FILE!',
      20 => '*/',
    ),
    'apiVersion' => 3,
    'name' => 'lqx/cards',
    'title' => 'Lyquix Cards',
    'description' => 'An cards block',
    'category' => 'lqx-content-blocks',
    'icon' => 'screenoptions',
    'keywords' => 
    array (
      0 => 'cards',
    ),
    'supports' => 
    array (
      'align' => false,
      'anchor' => true,
    ),
    'acf' => 
    array (
      'renderTemplate' => 'cards.php',
      'blockVersion' => 3,
    ),
  ),
  'filters' => 
  array (
    '//' => 
    array (
      0 => '/**',
      1 => '* block.json - Lyquix cards block definition',
      2 => '*',
      3 => '* @version     3.4.0',
      4 => '* @package     wp_theme_lyquix',
      5 => '* @author      Lyquix',
      6 => '* @copyright   Copyright (C) 2015 - 2024 Lyquix',
      7 => '* @license     GNU General Public License version 2 or later',
      8 => '* @link        https://github.com/Lyquix/wp_theme_lyquix',
      9 => '*',
      10 => '*    .d8888b. 88888888888 .d88888b.  8888888b.   888',
      11 => '*   d88P  Y88b    888    d88P   Y88b 888   Y88b  888',
      12 => '*   Y88b.         888    888     888 888    888  888',
      13 => '*     Y888b.      888    888     888 888   d88P  888',
      14 => '*        Y88b.    888    888     888 8888888P    888',
      15 => '*          888    888    888     888 888         Y8P',
      16 => '*   Y88b  d88P    888    Y88b. .d88P 888            ',
      17 => '*     Y8888P      888      Y88888P   888         888',
      18 => '*',
      19 => '*  DO NOT MODIFY THIS FILE!',
      20 => '*/',
    ),
    'apiVersion' => 3,
    'name' => 'lqx/filters',
    'title' => 'Lyquix Filters',
    'description' => 'A list of items with filters',
    'category' => 'lqx-content-blocks',
    'icon' => 'list-view',
    'keywords' => 
    array (
      0 => 'cards',
    ),
    'supports' => 
    array (
      'align' => false,
      'anchor' => true,
    ),
    'acf' => 
    array (
      'renderTemplate' => 'filters.php',
      'blockVersion' => 3,
    ),
  ),
  'gallery' => 
  array (
    '//' => 
    array (
      0 => '/**',
      1 => '* block.json - Lyquix gallery block definition',
      2 => '*',
      3 => '* @version     3.4.0',
      4 => '* @package     wp_theme_lyquix',
      5 => '* @author      Lyquix',
      6 => '* @copyright   Copyright (C) 2015 - 2024 Lyquix',
      7 => '* @license     GNU General Public License version 2 or later',
      8 => '* @link        https://github.com/Lyquix/wp_theme_lyquix',
      9 => '*',
      10 => '*    .d8888b. 88888888888 .d88888b.  8888888b.   888',
      11 => '*   d88P  Y88b    888    d88P   Y88b 888   Y88b  888',
      12 => '*   Y88b.         888    888     888 888    888  888',
      13 => '*     Y888b.      888    888     888 888   d88P  888',
      14 => '*        Y88b.    888    888     888 8888888P    888',
      15 => '*          888    888    888     888 888         Y8P',
      16 => '*   Y88b  d88P    888    Y88b. .d88P 888            ',
      17 => '*     Y8888P      888      Y88888P   888         888',
      18 => '*',
      19 => '*  DO NOT MODIFY THIS FILE!',
      20 => '*/',
    ),
    'apiVersion' => 3,
    'name' => 'lqx/gallery',
    'title' => 'Lyquix Gallery',
    'description' => 'A media gallery block with lightbox support',
    'category' => 'lqx-content-blocks',
    'icon' => 'format-gallery',
    'keywords' => 
    array (
      0 => 'media',
      1 => 'gallery',
      2 => 'lightbox',
      3 => 'image',
      4 => 'video',
    ),
    'supports' => 
    array (
      'align' => false,
      'anchor' => true,
    ),
    'acf' => 
    array (
      'renderTemplate' => 'gallery.php',
      'blockVersion' => 3,
    ),
  ),
  'hero' => 
  array (
    '//' => 
    array (
      0 => '/**',
      1 => '* block.json - Lyquix hero banner block definition',
      2 => '*',
      3 => '* @version     3.4.0',
      4 => '* @package     wp_theme_lyquix',
      5 => '* @author      Lyquix',
      6 => '* @copyright   Copyright (C) 2015 - 2024 Lyquix',
      7 => '* @license     GNU General Public License version 2 or later',
      8 => '* @link        https://github.com/Lyquix/wp_theme_lyquix',
      9 => '*',
      10 => '*    .d8888b. 88888888888 .d88888b.  8888888b.   888',
      11 => '*   d88P  Y88b    888    d88P   Y88b 888   Y88b  888',
      12 => '*   Y88b.         888    888     888 888    888  888',
      13 => '*     Y888b.      888    888     888 888   d88P  888',
      14 => '*        Y88b.    888    888     888 8888888P    888',
      15 => '*          888    888    888     888 888         Y8P',
      16 => '*   Y88b  d88P    888    Y88b. .d88P 888            ',
      17 => '*     Y8888P      888      Y88888P   888         888',
      18 => '*',
      19 => '*  DO NOT MODIFY THIS FILE!',
      20 => '*/',
    ),
    'apiVersion' => 3,
    'name' => 'lqx/hero',
    'title' => 'Lyquix Hero',
    'description' => 'A banner block meant for the top of the page',
    'category' => 'lqx-content-blocks',
    'icon' => 'cover-image',
    'keywords' => 
    array (
      0 => 'banner',
    ),
    'supports' => 
    array (
      'align' => false,
      'anchor' => true,
    ),
    'acf' => 
    array (
      'renderTemplate' => 'hero.php',
      'blockVersion' => 3,
    ),
  ),
  'logos' => 
  array (
    '//' => 
    array (
      0 => '/**',
      1 => '* block.json - Lyquix Logos block definition',
      2 => '*',
      3 => '* @version     3.4.0',
      4 => '* @package     wp_theme_lyquix',
      5 => '* @author      Lyquix',
      6 => '* @copyright   Copyright (C) 2015 - 2024 Lyquix',
      7 => '* @license     GNU General Public License version 2 or later',
      8 => '* @link        https://github.com/Lyquix/wp_theme_lyquix',
      9 => '*',
      10 => '*    .d8888b. 88888888888 .d88888b.  8888888b.   888',
      11 => '*   d88P  Y88b    888    d88P   Y88b 888   Y88b  888',
      12 => '*   Y88b.         888    888     888 888    888  888',
      13 => '*     Y888b.      888    888     888 888   d88P  888',
      14 => '*        Y88b.    888    888     888 8888888P    888',
      15 => '*          888    888    888     888 888         Y8P',
      16 => '*   Y88b  d88P    888    Y88b. .d88P 888            ',
      17 => '*     Y8888P      888      Y88888P   888         888',
      18 => '*',
      19 => '*  DO NOT MODIFY THIS FILE!',
      20 => '*/',
    ),
    'apiVersion' => 3,
    'name' => 'lqx/logos',
    'title' => 'Lyquix Logos',
    'description' => 'An logos block',
    'category' => 'lqx-content-blocks',
    'icon' => 'schedule',
    'keywords' => 
    array (
      0 => 'logos',
    ),
    'supports' => 
    array (
      'align' => false,
      'anchor' => true,
    ),
    'acf' => 
    array (
      'renderTemplate' => 'logos.php',
      'blockVersion' => 3,
    ),
  ),
  'map' => 
  array (
    '//' => 
    array (
      0 => '/**',
      1 => '* block.json - Lyquix map block definition',
      2 => '*',
      3 => '* @version     3.4.0',
      4 => '* @package     wp_theme_lyquix',
      5 => '* @author      Lyquix',
      6 => '* @copyright   Copyright (C) 2015 - 2024 Lyquix',
      7 => '* @license     GNU General Public License version 2 or later',
      8 => '* @link        https://github.com/Lyquix/wp_theme_lyquix',
      9 => '*',
      10 => '*    .d8888b. 88888888888 .d88888b.  8888888b.   888',
      11 => '*   d88P  Y88b    888    d88P   Y88b 888   Y88b  888',
      12 => '*   Y88b.         888    888     888 888    888  888',
      13 => '*     Y888b.      888    888     888 888   d88P  888',
      14 => '*        Y88b.    888    888     888 8888888P    888',
      15 => '*          888    888    888     888 888         Y8P',
      16 => '*   Y88b  d88P    888    Y88b. .d88P 888            ',
      17 => '*     Y8888P      888      Y88888P   888         888',
      18 => '*',
      19 => '*  DO NOT MODIFY THIS FILE!',
      20 => '*/',
    ),
    'apiVersion' => 3,
    'name' => 'lqx/map',
    'title' => 'Lyquix Map',
    'description' => 'An locations block with support for a map and items list',
    'category' => 'lqx-content-blocks',
    'icon' => 'screenoptions',
    'keywords' => 
    array (
      0 => 'locations',
      1 => 'map',
    ),
    'supports' => 
    array (
      'align' => false,
      'anchor' => true,
    ),
    'acf' => 
    array (
      'renderTemplate' => 'map.php',
      'blockVersion' => 3,
    ),
  ),
  'related-items' => 
  array (
    'apiVersion' => 3,
    'name' => 'lqx/related-items',
    'title' => 'Lyquix Related Items',
    'description' => 'Display related posts based on shared taxonomy, field value, or parent relationship',
    'category' => 'lqx-content-blocks',
    'icon' => 'networking',
    'supports' => 
    array (
      'align' => false,
      'anchor' => true,
    ),
    'acf' => 
    array (
      'renderTemplate' => 'related-items.php',
      'blockVersion' => 3,
    ),
  ),
  'slider' => 
  array (
    '//' => 
    array (
      0 => '/**',
      1 => '* block.json - Lyquix slider block definition',
      2 => '*',
      3 => '* @version     3.4.0',
      4 => '* @package     wp_theme_lyquix',
      5 => '* @author      Lyquix',
      6 => '* @copyright   Copyright (C) 2015 - 2024 Lyquix',
      7 => '* @license     GNU General Public License version 2 or later',
      8 => '* @link        https://github.com/Lyquix/wp_theme_lyquix',
      9 => '*',
      10 => '*    .d8888b. 88888888888 .d88888b.  8888888b.   888',
      11 => '*   d88P  Y88b    888    d88P   Y88b 888   Y88b  888',
      12 => '*   Y88b.         888    888     888 888    888  888',
      13 => '*     Y888b.      888    888     888 888   d88P  888',
      14 => '*        Y88b.    888    888     888 8888888P    888',
      15 => '*          888    888    888     888 888         Y8P',
      16 => '*   Y88b  d88P    888    Y88b. .d88P 888            ',
      17 => '*     Y8888P      888      Y88888P   888         888',
      18 => '*',
      19 => '*  DO NOT MODIFY THIS FILE!',
      20 => '*/',
    ),
    'apiVersion' => 3,
    'name' => 'lqx/slider',
    'title' => 'Lyquix Slider',
    'description' => 'A slider block',
    'category' => 'lqx-content-blocks',
    'icon' => 'slides',
    'keywords' => 
    array (
      0 => 'slider',
    ),
    'supports' => 
    array (
      'align' => false,
      'anchor' => true,
    ),
    'acf' => 
    array (
      'renderTemplate' => 'slider.php',
      'blockVersion' => 3,
    ),
  ),
  'tab-item' => 
  array (
    '//' => 
    array (
      0 => '/**',
      1 => '* block.json - Lyquix tab item block definition',
      2 => '*',
      3 => '* @version     3.1.0',
      4 => '* @package     wp_theme_lyquix',
      5 => '* @author      Lyquix',
      6 => '* @copyright   Copyright (C) 2015 - 2024 Lyquix',
      7 => '* @license     GNU General Public License version 2 or later',
      8 => '* @link        https://github.com/Lyquix/wp_theme_lyquix',
      9 => '*',
      10 => '*    .d8888b. 88888888888 .d88888b.  8888888b.   888',
      11 => '*   d88P  Y88b    888    d88P   Y88b 888   Y88b  888',
      12 => '*   Y88b.         888    888     888 888    888  888',
      13 => '*     Y888b.      888    888     888 888   d88P  888',
      14 => '*        Y88b.    888    888     888 8888888P    888',
      15 => '*          888    888    888     888 888         Y8P',
      16 => '*   Y88b  d88P    888    Y88b. .d88P 888            ',
      17 => '*     Y8888P      888      Y88888P   888         888',
      18 => '*',
      19 => '*  DO NOT MODIFY THIS FILE!',
      20 => '*/',
    ),
    'apiVersion' => 3,
    'name' => 'lqx/tab-item',
    'title' => 'Lyquix Tab Item',
    'description' => 'A Tab Item block',
    'category' => 'lqx-content-blocks',
    'icon' => 'block-default',
    'keywords' => 
    array (
      0 => 'tabs',
      1 => 'tab-item',
    ),
    'parent' => 
    array (
      0 => 'lqx/tabs-plus',
    ),
    'supports' => 
    array (
      'align' => false,
      'anchor' => true,
      'jsx' => true,
    ),
    'usesContext' => 
    array (
      0 => 'acf/fields',
    ),
    'acf' => 
    array (
      'renderTemplate' => 'tab-item.php',
      'blockVersion' => 3,
    ),
  ),
  'tabs' => 
  array (
    '//' => 
    array (
      0 => '/**',
      1 => '* block.json - Lyquix tabs block definition',
      2 => '*',
      3 => '* @version     3.4.0',
      4 => '* @package     wp_theme_lyquix',
      5 => '* @author      Lyquix',
      6 => '* @copyright   Copyright (C) 2015 - 2024 Lyquix',
      7 => '* @license     GNU General Public License version 2 or later',
      8 => '* @link        https://github.com/Lyquix/wp_theme_lyquix',
      9 => '*',
      10 => '*    .d8888b. 88888888888 .d88888b.  8888888b.   888',
      11 => '*   d88P  Y88b    888    d88P   Y88b 888   Y88b  888',
      12 => '*   Y88b.         888    888     888 888    888  888',
      13 => '*     Y888b.      888    888     888 888   d88P  888',
      14 => '*        Y88b.    888    888     888 8888888P    888',
      15 => '*          888    888    888     888 888         Y8P',
      16 => '*   Y88b  d88P    888    Y88b. .d88P 888            ',
      17 => '*     Y8888P      888      Y88888P   888         888',
      18 => '*',
      19 => '*  DO NOT MODIFY THIS FILE!',
      20 => '*/',
    ),
    'apiVersion' => 3,
    'name' => 'lqx/tabs',
    'title' => 'Lyquix Tabs',
    'description' => 'A Tabs block',
    'category' => 'lqx-content-blocks',
    'icon' => 'block-default',
    'keywords' => 
    array (
      0 => 'tabs',
    ),
    'supports' => 
    array (
      'align' => false,
      'anchor' => true,
    ),
    'acf' => 
    array (
      'renderTemplate' => 'tabs.php',
      'blockVersion' => 3,
    ),
  ),
  'tabs-plus' => 
  array (
    '//' => 
    array (
      0 => '/**',
      1 => '* block.json - Lyquix tabs plus block definition',
      2 => '*',
      3 => '* @version     3.1.0',
      4 => '* @package     wp_theme_lyquix',
      5 => '* @author      Lyquix',
      6 => '* @copyright   Copyright (C) 2015 - 2024 Lyquix',
      7 => '* @license     GNU General Public License version 2 or later',
      8 => '* @link        https://github.com/Lyquix/wp_theme_lyquix',
      9 => '*',
      10 => '*    .d8888b. 88888888888 .d88888b.  8888888b.   888',
      11 => '*   d88P  Y88b    888    d88P   Y88b 888   Y88b  888',
      12 => '*   Y88b.         888    888     888 888    888  888',
      13 => '*     Y888b.      888    888     888 888   d88P  888',
      14 => '*        Y88b.    888    888     888 8888888P    888',
      15 => '*          888    888    888     888 888         Y8P',
      16 => '*   Y88b  d88P    888    Y88b. .d88P 888            ',
      17 => '*     Y8888P      888      Y88888P   888         888',
      18 => '*',
      19 => '*  DO NOT MODIFY THIS FILE!',
      20 => '*/',
    ),
    'apiVersion' => 3,
    'name' => 'lqx/tabs-plus',
    'title' => 'Lyquix Tabs Plus',
    'description' => 'A Tabs Plus block',
    'category' => 'lqx-content-blocks',
    'icon' => 'block-default',
    'keywords' => 
    array (
      0 => 'tabs',
    ),
    'supports' => 
    array (
      'align' => false,
      'anchor' => true,
    ),
    'providesContext' => 
    array (
      'acf/fields' => 'data',
    ),
    'acf' => 
    array (
      'renderTemplate' => 'tabs-plus.php',
      'blockVersion' => 3,
    ),
  ),
  'testimonial' => 
  array (
    '//' => 
    array (
      0 => '/**',
      1 => '* block.json - Lyquix testimonial block definition',
      2 => '*',
      3 => '* @version     3.4.0',
      4 => '* @package     wp_theme_lyquix',
      5 => '* @author      Lyquix',
      6 => '* @copyright   Copyright (C) 2015 - 2024 Lyquix',
      7 => '* @license     GNU General Public License version 2 or later',
      8 => '* @link        https://github.com/Lyquix/wp_theme_lyquix',
      9 => '*',
      10 => '*    .d8888b. 88888888888 .d88888b.  8888888b.   888',
      11 => '*   d88P  Y88b    888    d88P   Y88b 888   Y88b  888',
      12 => '*   Y88b.         888    888     888 888    888  888',
      13 => '*     Y888b.      888    888     888 888   d88P  888',
      14 => '*        Y88b.    888    888     888 8888888P    888',
      15 => '*          888    888    888     888 888         Y8P',
      16 => '*   Y88b  d88P    888    Y88b. .d88P 888            ',
      17 => '*     Y8888P      888      Y88888P   888         888',
      18 => '*',
      19 => '*  DO NOT MODIFY THIS FILE!',
      20 => '*/',
    ),
    'apiVersion' => 3,
    'name' => 'lqx/testimonial',
    'title' => 'Lyquix Testimonial',
    'description' => 'A Testimonial block',
    'category' => 'lqx-content-blocks',
    'icon' => 'format-quote',
    'keywords' => 
    array (
      0 => 'testimonial',
    ),
    'supports' => 
    array (
      'align' => false,
      'anchor' => true,
    ),
    'acf' => 
    array (
      'renderTemplate' => 'testimonial.php',
      'blockVersion' => 3,
    ),
  ),
  'video' => 
  array (
    '$schema' => 'https://schemas.wp.org/trunk/block.json',
    'apiVersion' => 3,
    'name' => 'lqx/video',
    'title' => 'Lyquix Video',
    'description' => 'Video block supporting YouTube/Vimeo URLs (via Lyqbox) and uploaded video files (inline or Lyqbox) with optional lazy load, hover play, and viewport play.',
    'category' => 'media',
    'icon' => 'format-video',
    'textdomain' => 'lyquix',
    'attributes' => 
    array (
      'videoType' => 
      array (
        'type' => 'string',
        'default' => 'url',
      ),
      'videoUrl' => 
      array (
        'type' => 'string',
        'default' => '',
      ),
      'videoUploadId' => 
      array (
        'type' => 'number',
        'default' => 0,
      ),
      'videoUploadUrl' => 
      array (
        'type' => 'string',
        'default' => '',
      ),
      'videoUploadMime' => 
      array (
        'type' => 'string',
        'default' => '',
      ),
      'posterId' => 
      array (
        'type' => 'number',
        'default' => 0,
      ),
      'posterUrl' => 
      array (
        'type' => 'string',
        'default' => '',
      ),
      'caption' => 
      array (
        'type' => 'string',
        'default' => '',
      ),
      'openInLyqbox' => 
      array (
        'type' => 'boolean',
        'default' => false,
      ),
      'lazyLoad' => 
      array (
        'type' => 'boolean',
        'default' => false,
      ),
      'hoverPlay' => 
      array (
        'type' => 'boolean',
        'default' => false,
      ),
      'viewportPlay' => 
      array (
        'type' => 'boolean',
        'default' => false,
      ),
      'controls' => 
      array (
        'type' => 'boolean',
        'default' => true,
      ),
      'autoplay' => 
      array (
        'type' => 'boolean',
        'default' => false,
      ),
      'loop' => 
      array (
        'type' => 'boolean',
        'default' => false,
      ),
      'muted' => 
      array (
        'type' => 'boolean',
        'default' => false,
      ),
    ),
    'supports' => 
    array (
      'align' => 
      array (
        0 => 'wide',
        1 => 'full',
      ),
      'html' => false,
      'anchor' => true,
    ),
    'editorScript' => 'file:./editor.js',
    'render' => 'file:./render.php',
  ),
);

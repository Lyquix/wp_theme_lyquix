# Developer Guide

This guide covers the development workflow, creating custom components, and the hooks and filters available for extending the theme.

## Development Workflow

### Starting Development

```bash
cd wp-content/themes/lyquix-child
bun run watch
```

This launches three parallel processes:
1. **Bun** watches TypeScript and recompiles the Lyquix library
2. **Bun** watches TypeScript and recompiles the Scripts library
3. **Gulp** watches SCSS/PHP/config files, recompiles CSS, minifies JS, and triggers LiveReload

### LiveReload

LiveReload automatically refreshes the browser when files change. It runs on port 35729 and is enabled by default (toggle in Theme Customizer > Theme Features > Enable LiveReload).

The LiveReload script is injected by `\lqx\livereload\render()` at the bottom of `custom.php`.

### Debug Mode

Set the debug level in Theme Customizer > JS > Enable lqx debug:

| Level | Output |
|-------|--------|
| 0 | No debug output |
| 1 | Errors only |
| 2 | Errors + Warnings |
| 3 | Errors + Warnings + Info |

### Non-Minified Assets

On local environments (hostname ending in `.test` or `WPCONFIG_ENVNAME=local`), the theme automatically serves non-minified CSS and JS files for easier debugging.

## Creating Custom Blocks

### 1. Create the Block Directory

```
php/custom/blocks/my-block/
├── block.json
├── default.php
└── default.tmpl.php
```

### 2. Define block.json

```json
{
    "name": "lqx/my-block",
    "title": "My Custom Block",
    "description": "A custom block",
    "category": "lqx-content-blocks",
    "icon": "admin-generic",
    "acf": {
        "mode": "preview",
        "renderCallback": "my_block_render"
    },
    "supports": {
        "anchor": true,
        "className": true
    }
}
```

### 3. Create the Renderer

```php
<?php
// php/custom/blocks/my-block/default.php
$settings = \lqx\blocks\get_settings($block);
$content = \lqx\blocks\get_content($block);
require \lqx\blocks\get_template($settings['processed']['block'], $settings['processed']['preset']);
```

### 4. Create the Template

```php
<?php
// php/custom/blocks/my-block/default.tmpl.php
$s = $settings['processed'];
?>
<div id="<?= $s['anchor'] ?: $s['hash'] ?>"
     class="lqx-block-my-block <?= $s['class'] ?> <?= $s['style'] ?>">
    <h2><?= $content['heading'] ?></h2>
    <div class="content">
        <?= $content['body'] ?>
    </div>
</div>
```

### 5. Add ACF Fields

Create an ACF field group for your block with fields following the naming convention:
- `my-block_block_content` - Content fields
- `my-block_block_global` - Global settings
- `my-block_block_user` - User settings (style, preset)
- `my-block_block_admin` - Admin override settings

Export the field group JSON to `acf-json/` for version control.

## Creating Custom Modules

### 1. Create the Module Directory

```
php/custom/modules/my-module/
├── my-module.php
├── default.php
└── default.tmpl.php
```

### 2. Define the Module

```php
<?php
// php/custom/modules/my-module/my-module.php
namespace lqx\modules\my_module;

function render() {
    require \lqx\modules\get_renderer('my-module');
}
```

### 3. Call from custom.php

Add the render call in your `custom.php`:

```php
<?php \lqx\modules\my_module\render(); ?>
```

## Creating Custom Templates

### Page Templates

Create a PHP file in `php/custom/templates/`:

```php
<?php
// php/custom/templates/page-services.php
?>
<div class="services-page">
    <?php the_content(); ?>
    <!-- Custom layout for services page -->
</div>
```

This template is automatically used for a page with the slug "services".

### Custom Post Type Templates

```php
<?php
// php/custom/templates/portfolio.php
// Used for all 'portfolio' custom post type single views
?>
<div class="portfolio-single">
    <h1><?php the_title(); ?></h1>
    <?php the_content(); ?>
</div>
```

### Archive Templates

```php
<?php
// php/custom/templates/archive-portfolio.php
?>
<div class="portfolio-archive">
    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
        <article>
            <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
            <?php the_excerpt(); ?>
        </article>
    <?php endwhile; endif; ?>
</div>
```

## Creating Custom Page Templates (Gutenberg)

To add selectable page templates in the Gutenberg editor:

1. Copy `page-templates/template.dist.php` to `page-templates/my-template.php`
2. Add the template header:

```php
<?php
/**
 * Template Name: My Custom Template
 */
```

3. Create a matching template file in `php/custom/templates/my-template.php`

## Hooks and Filters Reference

### WordPress Actions Used

| Hook | File | Purpose |
|------|------|---------|
| `after_setup_theme` | `setup.php` | Theme initialization |
| `wp_enqueue_scripts` | `css.php`, `js.php` | Asset enqueuing |
| `wp_head` | `setup.php` | Global WordPress styles |
| `wp_footer` | `css.php` | Critical CSS async loader |
| `admin_init` | `setup.php` | Image sizes, user roles |
| `admin_menu` | `blocks.php` | Reset Global Settings page |
| `admin_head` | `setup.php` | Admin UI customizations |
| `admin_notices` | `setup.php` | Required plugins alerts |
| `customize_register` | `customizer.php` | Customizer settings |
| `acf/init` | `blocks.php` | Block field display logic |
| `init` | `blocks.php`, `layouts.php` | Block registration |
| `rest_api_init` | `blocks.php`, `critical.php` | REST API endpoints |
| `add_meta_boxes` | `setup.php` | Excerpt field repositioning |

### WordPress Filters Used

| Filter | File | Purpose |
|--------|------|---------|
| `acf/load_field` | `blocks.php`, `modules.php` | Dynamic field population |
| `acf/blocks/wrap_frontend_innerblocks` | `layouts.php` | Disable inner block wrapping |
| `block_categories_all` | `blocks.php`, `layouts.php` | Add block categories |
| `run_wptexturize` | `setup.php` | Disable smart quotes |
| `upload_mimes` | `setup.php` | Allow SVG uploads |
| `intermediate_image_sizes_advanced` | `setup.php` | Custom image sizes |
| `intermediate_image_sizes` | `setup.php` | Available sizes list |
| `auto_update_plugin` | `setup.php` | Disable auto-updates |
| `auto_update_theme` | `setup.php` | Disable auto-updates |
| `acf/settings/load_json` | `blocks.php` (child) | ACF JSON load paths |

### REST API Endpoints

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/lyquix/v3/get-options` | GET | Get block styles/presets options |
| `/lyquix/v3/search-posts` | GET | Search posts by block and setting |
| `/lyquix/v3/critical` | GET | Critical CSS configuration |

### AJAX Endpoints

| Action | Purpose |
|--------|---------|
| `reset_global_settings` | Reset block/module global settings to defaults |
| `get_acf_field` | Retrieve ACF field values for editor display logic |
| `dismiss_required_plugins_alert` | Dismiss the required plugins notice |

## Utility Functions

The `\lqx\util` namespace provides helper functions:

| Function | Purpose |
|----------|---------|
| `validate_data()` | Comprehensive data validation with type coercion |
| `array_is_list()` | Check if an array is a sequential list (not associative) |
| `breadcrumbs()` | Generate breadcrumb navigation |
| `slugify()` | Convert strings to URL-friendly slugs |
| `minify_html()` | Minify HTML output |
| `parse_video_url()` | Extract video IDs from YouTube/Vimeo URLs |

## Image Sizes

When **Enable Image Sizes** is on (default), the theme configures:

| Size | Dimensions | Crop |
|------|-----------|------|
| thumbnail | 150x150 | Yes |
| xsmall | 320x320 | No |
| small | 640x640 | No |
| medium | 1280x1280 | No |
| large | 2560x2560 | No |
| xlarge | 3840x3840 | No |

The standard `medium_large`, `1536x1536`, and `2048x2048` sizes are removed.

## Code Quality Tools

### ESLint

Configuration: `.eslintrc.json`

```bash
npx eslint js/**/*.ts
```

### StyleLint

Configuration: `.stylelintrc.json`

```bash
npx stylelint css/**/*.scss
```

### Prettier

Configuration: Uses `prettier-plugin-tailwindcss` for automatic Tailwind class sorting.

```bash
npx prettier --write .
```

### EditorConfig

Configuration: `.editorconfig` ensures consistent formatting across editors.

## Version Control

### Recommended .gitignore

The child theme's `.gitignore` excludes:

```
.sass-cache/
node_modules/
bun.lockb
package.lock
php/browsers/browsers.json
php/ip2geo/GeoLite2-City.*
php/update.json
```

### What to Commit

- All files in `css/custom/`
- All files in `js/custom/`
- `js/scripts.ts`
- `custom.php`
- All files in `php/custom/`
- `css/tailwind/theme.js`
- `css/tailwind/whitelist.html`
- `package.json`
- `gulpfile.js`
- Compiled CSS/JS (for deployment without build tools)

### What NOT to Commit

- `node_modules/`
- GeoLite2 database files
- Browser detection JSON
- Theme update metadata

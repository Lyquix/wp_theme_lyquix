# Content Blocks

The Lyquix theme provides 14 custom Gutenberg content blocks powered by Advanced Custom Fields PRO. Each block features a three-level settings system, configurable presets and styles, and child theme override capability.

## Available Blocks

| Block | Description |
|-------|-------------|
| **Accordion** | Collapsible content sections with optional images, subheadings, and custom classes |
| **Accordion Plus** | Enhanced accordion with additional features |
| **Banner** | Hero-style banner with text/image overlay and optional breadcrumbs |
| **Cards** | Content cards with images, icons, videos, labels, and links |
| **Filters** | AJAX-powered post filtering by category, tags, taxonomies, and ACF fields |
| **Gallery** | Image/video gallery with filtering, thumbnails, teasers, and lightbox |
| **Hero** | Large hero with breadcrumbs, heading override, intro text, images, video, and CTAs |
| **Logos** | Logo grid/carousel with optional links, titles, and padding |
| **Map** | Google Maps with markers, info windows, business hours, phone numbers, and labels |
| **Slider** | Carousel with text, images, mobile images, video, thumbnails, and teaser text |
| **Tabs** | Tabbed content interface with images and subheadings |
| **Tabs Plus** | Enhanced tabs with additional features |
| **Testimonial** | Testimonial carousel with images and custom colors |

All blocks are registered under the **Lyquix Content Blocks** category in the Gutenberg editor.

## Three-Level Settings System

Every content block uses a hierarchical settings system:

```
Global Settings (site-wide defaults for this block type)
    ↓
Preset Override (saved configuration applied to this instance)
    ↓
User Settings (per-instance settings set by the editor)
    ↓
Admin Settings (per-instance overrides set by administrators)
    ↓
Final Rendered Output
```

### Global Settings

Configured in the WordPress admin under **Site Settings > [Block Name]**. These are the default values for every instance of a block type across the entire site. For example, you might set the default number of columns for all Cards blocks.

### Styles

Named visual variations for a block. Styles are defined globally and selected per-instance. When a style is selected, its CSS class is added to the block's wrapper element. Examples: "dark", "compact", "full-width".

### Presets

Saved configurations that bundle multiple settings together. A preset overrides global settings with predefined values. For example, a "Blog Cards" preset might set specific column counts, image sizes, and label visibility.

Presets are defined globally under **Site Settings > [Block Name]** and selected per-instance in the editor.

### User Settings

Per-instance settings that the content editor can modify. These include selecting a style, choosing a preset, and any block-specific options.

### Admin Settings

Per-instance overrides that only administrators can see and modify. These use an "override group" pattern where each setting has a checkbox to enable the override and a field for the override value. Admin settings take the highest priority.

## Settings Merge Logic

The settings are merged using these functions from `\lqx\blocks`:

1. **`process_overrides()`** - Extracts override values from `_override_group` fields
2. **`remove_empty_settings()`** - Strips null/empty values to avoid overwriting with blanks
3. **`merge_settings()`** - Recursively merges settings, with later values taking priority

The merge order is:
```php
$processed = global_settings;
$processed = merge(processed, preset_settings);     // if preset selected
$processed = merge(processed, admin_settings);       // if admin overrides set
```

## Block File Structure

Each block in `php/blocks/` follows this structure:

```
php/blocks/{block-name}/
├── block.json          # WordPress block registration
├── default.php         # Default render function
├── default.tmpl.php    # Default render template
└── (optional preset/sub-template files)
```

### block.json

Standard WordPress block registration file. Defines the block name, title, category, icon, and ACF field associations.

### default.php (Renderer)

The renderer loads settings and content, then includes the template:

```php
<?php
$settings = \lqx\blocks\get_settings($block);
$content = \lqx\blocks\get_content($block);
require \lqx\blocks\get_template($settings['processed']['block'], $settings['processed']['preset']);
```

### default.tmpl.php (Template)

The template contains the actual HTML output using `$settings` and `$content` variables.

## Overriding Blocks in the Child Theme

To customize a block's rendering, create files in `php/custom/blocks/{block-name}/`:

### Override Resolution Order

For the **renderer**:
1. `php/custom/blocks/{block-name}/{preset}.php` (preset-specific renderer)
2. `php/custom/blocks/{block-name}/default.php` (custom default renderer)
3. `php/blocks/{block-name}/default.php` (parent theme default)

For the **template**:
1. `php/custom/blocks/{block-name}/{preset}.tmpl.php` (preset-specific template)
2. `php/custom/blocks/{block-name}/default.tmpl.php` (custom default template)
3. `php/blocks/{block-name}/default.tmpl.php` (parent theme default)

### Example: Override Cards Template

Create `php/custom/blocks/cards/default.tmpl.php`:

```php
<?php
// Custom cards rendering
$s = $settings['processed'];
$items = $content['items'] ?? [];
?>
<div id="<?= $s['anchor'] ?: $s['hash'] ?>"
     class="lqx-block-cards <?= $s['class'] ?>">
    <?php foreach ($items as $item) : ?>
        <div class="card">
            <!-- Your custom card HTML -->
        </div>
    <?php endforeach; ?>
</div>
```

### Example: Preset-Specific Override

Create `php/custom/blocks/cards/blog-cards.php` for a preset named "blog-cards":

```php
<?php
$settings = \lqx\blocks\get_settings($block);
$content = \lqx\blocks\get_content($block);
// Custom logic for blog cards preset
require \lqx\blocks\get_template($settings['processed']['block'], $settings['processed']['preset']);
```

## Field Display Logic

The theme includes a JavaScript file (`php/blocks/field-display.js`) that controls field visibility in the Gutenberg editor. Fields can be conditionally shown or hidden based on the value of "controller" fields in the global settings.

For example, if the global setting `show_image` is set to "n", the `image` field is hidden in the editor for all instances of that block. This is configured through a rules array:

```javascript
{
    "field": "image",           // Field to show/hide
    "controller": "show_image", // Controller field name
    "operator": "==",           // Comparison operator
    "value": "y"                // Value that makes the field visible
}
```

## Registering Custom Blocks

To add entirely new blocks, create a directory in `php/custom/blocks/{block-name}/` with:

1. `block.json` - Block registration following the WordPress Block API
2. `default.php` - Render function
3. `default.tmpl.php` - Render template

The parent theme automatically discovers and registers blocks from both `php/blocks/*/block.json` (parent) and `php/custom/blocks/*/block.json` (child).

## Block API Functions

### `\lqx\blocks\get_settings($block, $post_id, $forced_preset, $forced_style)`

Returns the complete settings array for a block instance, including global, preset, user, and admin settings merged together.

**Parameters:**
- `$block` - Block array (from Gutenberg) or block name string
- `$post_id` - Optional post ID (defaults to current post)
- `$forced_preset` - Optional preset name to force
- `$forced_style` - Optional style name to force

**Returns:** Array with keys `global`, `styles`, `presets`, `local`, and `processed`

### `\lqx\blocks\get_content($block, $post_id)`

Returns the content fields for a block instance.

### `\lqx\blocks\render_block($settings, $content)`

Renders a block using the appropriate renderer based on preset and overrides.

### `\lqx\blocks\get_renderer($block_name, $preset)`

Returns the file path to the block's renderer PHP file.

### `\lqx\blocks\get_template($block_name, $preset, $sub_template)`

Returns the file path to the block's template PHP file.

## Reset Global Settings

The theme provides an admin page under **Site Settings > Reset Global Settings** that allows administrators to reset global settings for any or all blocks and modules back to their default values. This is useful when settings have been modified and you want to start fresh.

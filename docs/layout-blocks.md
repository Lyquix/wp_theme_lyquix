# Layout Blocks

The Lyquix theme includes 13 layout blocks based on CSS layout primitives. These blocks provide structural containers that wrap inner blocks (Gutenberg InnerBlocks) and apply CSS layout patterns using Tailwind CSS classes.

## Available Layout Blocks

| Block | CSS Pattern | Purpose |
|-------|-------------|---------|
| **Box** | Padding/spacing | Basic container with configurable padding |
| **Center** | `margin-inline: auto` | Horizontally center content with max-width |
| **Cluster** | Flexbox | Group items with flexible wrapping and gap |
| **Container** | Max-width + padding | Full-width container with centered content |
| **Cover** | Flexbox + min-height | Full-height cover layout with centered content |
| **Frame** | Aspect ratio | Maintain aspect ratio for media content |
| **Grid** | CSS Grid | Multi-column grid layout |
| **Icon** | Flexbox | Icon paired with text content |
| **Imposter** | Position: absolute | Overlay/floating element positioning |
| **Reel** | Overflow: auto | Horizontal scrolling container |
| **Sidebar** | Flexbox | Two-column layout with main content and sidebar |
| **Stack** | Flexbox column | Vertical spacing between child elements |
| **Switcher** | Flexbox wrap | Responsive layout that switches from row to column |

All layout blocks are registered under the **Lyquix Layout Blocks** category in the Gutenberg editor.

## How Layout Blocks Work

Layout blocks serve as wrappers for WordPress InnerBlocks. They:

1. Provide a structural container element
2. Apply CSS layout classes (primarily Tailwind utilities)
3. Allow configuration through ACF fields
4. Support nesting for complex layouts

### ACF to Tailwind Class Conversion

Layout blocks use a special convention: ACF fields prefixed with `tailwind_` are automatically converted to CSS utility classes. This is handled by `\lqx\layouts\get_tailwind_classes()`:

```php
// If an ACF field has:
//   key: "tailwind_p-"
//   value: "4"
// It generates the class: "p-4"

// If an ACF field has:
//   value: "tailwind_text-center"
// It generates the class: "text-center"
```

The function walks through all ACF fields for the block and:
- For **string values** starting with `tailwind_`: strips the prefix and uses the remainder as a class
- For **string keys** starting with `tailwind_` with a truthy value: strips the prefix from the key, appends the value, and uses the result as a class

## Block Structure

Each layout block in `php/layouts/` contains:

```
php/layouts/{block-name}/
├── block.json         # Block registration
└── default.php        # Render function
```

The render function typically:
1. Gets the Tailwind classes from ACF fields
2. Renders the inner blocks within a wrapper element
3. Applies the CSS classes to the wrapper

### Example: Stack Layout

```html
<div class="stack gap-4 p-2">
    <!-- InnerBlocks content -->
    <div>Block 1</div>
    <div>Block 2</div>
    <div>Block 3</div>
</div>
```

## Using Layout Blocks

### In the Gutenberg Editor

1. Add a layout block from the **Lyquix Layout Blocks** category
2. Configure its settings (spacing, alignment, etc.) in the block sidebar
3. Add inner blocks inside the layout block
4. Layout blocks can be nested for complex structures

### Common Patterns

**Centered content with max-width:**
```
Container > Center > Stack > [Content blocks]
```

**Two-column layout:**
```
Sidebar > [Main content] + [Sidebar content]
```

**Card grid:**
```
Grid > [Card 1] + [Card 2] + [Card 3] + ...
```

**Hero with overlay:**
```
Cover > [Background image] + [Text content]
```

**Horizontal scrolling gallery:**
```
Reel > [Image 1] + [Image 2] + [Image 3] + ...
```

## Layout Block Details

### Box
Adds padding around its content. Configure padding values through Tailwind utility fields.

Like every layout block, Box supports named styles — see **Layout block styles** below.

### Center
Centers content horizontally with a configurable max-width. Uses `margin-inline: auto`.

### Cluster
Groups items using flexbox with wrapping. Items flow horizontally and wrap to new rows. Configure gap, alignment, and justification.

### Container
A full-width wrapper with horizontal padding and centered content. Commonly used as the outermost layout block.

### Cover
Creates a full-height section with vertically centered content. Useful for hero sections and banners. Set a minimum height and center content within.

### Frame
Maintains a specific aspect ratio. Useful for embedding media (video, maps) where you need consistent proportions.

### Grid
CSS Grid layout with configurable columns. Set the number of columns, gap, and responsive behavior.

### Icon
Pairs an icon (image or SVG) with text content side by side. Configure icon size and alignment.

### Imposter
Positions content absolutely over its parent. Used for overlays, tooltips, and floating elements. Configure position offsets.

### Reel
Creates a horizontal scrolling container. Items are laid out in a row and overflow horizontally. Useful for horizontal card carousels without JavaScript.

### Sidebar
A two-panel layout with a main content area and a sidebar. Configure which side the sidebar appears on and its width.

### Stack
Adds consistent vertical spacing between child elements. The most commonly used layout block. Configure the gap between items.

### Switcher
A responsive layout that shows items in a row when there's enough space, and switches to a column layout when the viewport is narrow. Configure the threshold and number of items.

## Tailwind Layouts Plugin

The layout blocks are powered by the `tailwind-layouts` package, which provides Tailwind CSS utilities specifically designed for these layout patterns. The plugin is configured in `css/tailwind/config.js`:

```javascript
plugins: [
    require('@tailwindcss/container-queries'),
    require('@tailwindcss/aspect-ratio'),
    require('tailwind-layouts'),
]
```

## Inner Blocks Wrapping

By default, ACF wraps inner blocks in an additional `<div>`. The Lyquix theme disables this for all `lqx/` blocks to give you full control over the HTML structure:

```php
add_filter('acf/blocks/wrap_frontend_innerblocks', function ($wrap, $name) {
    if (str_contains($name, 'lqx/')) {
        return false;
    }
    return true;
}, 10, 2);
```

## Layout block styles

Every layout block has a **Style** dropdown in its block settings. The available styles are defined once per block under **Site Settings > Layout**, which has one tab per layout block (Box, Center, Cluster, Container, Cover, Frame, Grid, Icon, Imposter, Reel, Sidebar, Stack, Switcher) holding a *Styles* repeater of style slugs (`<block>_block_styles`). The selected slug is appended as a CSS class on the block's root element (for example `box-l my-style`) for the child theme to target. Style values are included in the Block Settings Sync export, so they travel with the project repo like the content blocks' styles.

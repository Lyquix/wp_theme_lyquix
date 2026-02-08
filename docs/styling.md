# Styling

The Lyquix theme uses a hybrid CSS architecture combining SCSS (organized by SMACSS) with Tailwind CSS. This gives you both the structure of component-based SCSS and the speed of utility-first development.

## CSS Architecture Overview

```
SCSS Partials (css/custom/)     →  custom.css
                                      ↓
                              Tailwind Processing
                                      ↓
                                  styles.css
                                      ↓
                              PostCSS (cssnano + autoprefixer)
                                      ↓
                                styles.min.css
```

## SMACSS File Structure

All custom styles live in `css/custom/` in the child theme, organized by the SMACSS (Scalable and Modular Architecture for CSS) methodology:

```
css/custom/
├── abstracts/
│   ├── _index.scss       # Imports all abstract partials
│   ├── _variables.scss   # Color, font, spacing variables
│   └── _mixins.scss      # Reusable SCSS mixins
├── base/
│   ├── _index.scss       # Imports all base partials
│   ├── _reset.scss       # CSS reset/normalize
│   ├── _typography.scss  # Base typography styles
│   ├── _forms.scss       # Form element styles
│   ├── _tables.scss      # Table styles
│   └── _print.scss       # Print styles
├── components/
│   ├── _index.scss       # Imports all component partials
│   ├── _accordion.scss   # Accordion block styles
│   ├── _alerts.scss      # Alert module styles
│   ├── _banner.scss      # Banner block styles
│   ├── _buttons.scss     # Button styles
│   ├── _cards.scss       # Cards block styles
│   ├── _cta.scss         # CTA module styles
│   ├── _filters.scss     # Filters block styles
│   ├── _gallery.scss     # Gallery block styles
│   ├── _hero.scss        # Hero block styles
│   ├── _logos.scss        # Logos block styles
│   ├── _modal.scss       # Modal module styles
│   ├── _popup.scss       # Popup module styles
│   ├── _slider.scss      # Slider block styles
│   ├── _social.scss      # Social module styles
│   └── _tabs.scss        # Tabs block styles
├── layouts/
│   ├── _index.scss       # Imports all layout partials
│   ├── _header.scss      # Site header styles
│   ├── _footer.scss      # Site footer styles
│   └── _layout.scss      # Overall layout structure
├── pages/
│   ├── _index.scss       # Imports all page partials
│   ├── _404.scss         # 404 page styles
│   └── _search.scss      # Search page styles
├── themes/
│   ├── _index.scss       # Imports all theme partials
│   └── _theme.scss       # Theme variations (dark mode, etc.)
├── vendors/
│   ├── _index.scss       # Imports all vendor partials
│   └── _swiper.scss      # Swiper carousel overrides
└── custom.scss           # Main entry point (imports all _index files)
```

### Adding New SCSS Files

You can add new `.scss` partials to any SMACSS category to keep styles organized. Follow the existing naming convention (prefix with `_`) and import the new file in the corresponding `_index.scss`.

For example, to add styles for a custom post type called "projects":

1. Create `css/custom/pages/_projects.scss`
2. Add `@import 'projects';` to `css/custom/pages/_index.scss`

Recommended uses for new files:

- **pages/**: Distinct pages or custom post type archives (e.g., `_blog.scss`, `_events.scss`)
- **components/**: Custom components or block variations (e.g., `_testimonials.scss`, `_pricing.scss`)
- **layouts/**: Structural sections (e.g., `_sidebar.scss`)
- **vendors/**: Overrides for third-party plugin styles

### The Entry Point: custom.scss

The `custom.scss` file is the main SCSS entry point. It imports Tailwind directives and all SMACSS category index files:

```scss
@tailwind base;
@tailwind components;
@tailwind utilities;

@import 'abstracts/index';
@import 'base/index';
@import 'components/index';
@import 'layouts/index';
@import 'pages/index';
@import 'themes/index';
@import 'vendors/index';
```

## Tailwind CSS Configuration

### Breakpoints

The theme uses custom breakpoints that differ from Tailwind's defaults:

| Name | Width | Usage |
|------|-------|-------|
| `xs` | 0px | Mobile (default) |
| `sm` | 480px | Large phones |
| `md` | 720px | Tablets |
| `lg` | 1080px | Laptops/desktops |
| `xl` | 1620px | Large screens |

Breakpoints are defined in a single shared file: `css/tailwind/breakpoints.json` (copied from the parent theme's `breakpoints.dist.json` during `postinstall`). This JSON file is the single source of truth consumed by:

- **Tailwind CSS** via `css/tailwind/presets.js` (for responsive utility classes)
- **TypeScript** via `js/lib/lyquix/responsive.ts` (for runtime screen detection)
- **PHP** via `php/customizer.php` and `php/critical.php` (for critical CSS viewports)

To customize the breakpoints for your project, edit `css/tailwind/breakpoints.json` in the child theme. Each entry defines a `width` and `height`:

```json
{
  "xs": { "width": 320, "height": 720 },
  "sm": { "width": 480, "height": 1080 },
  "md": { "width": 720, "height": 1080 },
  "lg": { "width": 1080, "height": 1080 },
  "xl": { "width": 1620, "height": 1080 }
}
```

The `width` values are used as CSS media query breakpoints. The `height` values are used for critical path CSS viewport dimensions.

### Theme Customization

The Tailwind theme is configured in `css/tailwind/theme.js` (copied from `theme.dist.js`). This is where you define your project's:

- Colors
- Font families
- Font sizes
- Spacing scale
- Border radius
- Any other Tailwind theme values

```javascript
// css/tailwind/theme.js
export default {
    colors: {
        primary: '#1a73e8',
        secondary: '#34a853',
        // ...
    },
    fontFamily: {
        sans: ['Inter', 'sans-serif'],
        serif: ['Georgia', 'serif'],
    },
    // ...
};
```

### Plugins

The Tailwind configuration includes these plugins:

- **`@tailwindcss/container-queries`** - Container query support (`@container`)
- **`@tailwindcss/aspect-ratio`** - Aspect ratio utilities
- **`tailwind-layouts`** - CSS layout primitives used by layout blocks

### Content Scanning

Tailwind scans these paths for utility classes:

- Parent and child theme JS files
- Parent and child theme PHP files
- Page templates
- Tribe Events templates (if using The Events Calendar)
- `custom.php`
- `css/tailwind/whitelist.html`

### Whitelist

The `css/tailwind/whitelist.html` file is **auto-generated** by the theme. You should not edit it directly. The `tailwind.php` module detects any Tailwind utility classes used in post and page content and exports them to `whitelist.html` whenever a post or page is saved or updated. This ensures that classes applied directly in the WordPress editor are included in the Tailwind build.

However, this mechanism is intended as a development convenience, not a production-ready solution. Classes detected this way may not survive the full build pipeline (e.g., purging in production). Any Tailwind classes used in the editor should eventually be moved into the appropriate SCSS files to ensure they are reliably included.

## Adding Custom Styles

### Using SCSS

Edit the appropriate partial in `css/custom/`. For example, to style the header:

```scss
// css/custom/layouts/_header.scss
header {
    background: theme('colors.primary');
    padding: theme('spacing.4');

    .menu.main {
        display: flex;
        gap: theme('spacing.2');

        a {
            color: white;
            text-decoration: none;
        }
    }
}
```

### Using Tailwind Utilities

Apply utility classes directly in your PHP templates:

```php
<header class="bg-primary p-4">
    <nav class="flex gap-2">
        <?php wp_nav_menu(['menu' => 'main-menu']); ?>
    </nav>
</header>
```

### Mixing Both Approaches

The hybrid approach lets you use Tailwind utilities for quick prototyping and simple styles, while using SCSS for complex, reusable component styles. You can reference Tailwind theme values in SCSS using the `theme()` function.

## Editor Styles

The theme generates separate editor styles (`css/editor.css`) using a dedicated Tailwind configuration (`css/tailwind/editor.config.js`) that:

- Disables Preflight (base styles) to avoid conflicts with the WordPress admin
- Only scans `whitelist.html` for content
- Imports `css/custom/editor.css` for additional editor-specific styles

Editor styles are loaded via `add_editor_style('css/editor.css')` in the theme setup.

## Critical Path CSS

The theme supports critical path CSS for improved performance:

### How It Works

1. Critical CSS is generated per post type (and optionally per page) using `node critical.js`
2. CSS files are saved to `css/critical/{post-type}.css` and `css/critical/page-{slug}.css`
3. On page load, the critical CSS is inlined in `<style>` tags in the `<head>`
4. The full stylesheet is loaded asynchronously with `media="print"` and switched to `media="all"` via JavaScript after page load

### Configuration

In the Theme Customizer under **CSS**:

| Setting | Purpose |
|---------|---------|
| Load Critical Path CSS | Enable/disable critical CSS |
| Viewports for Critical Path CSS | Viewport dimensions for each breakpoint |
| Exclude Post Types | Skip critical CSS for specific post types |
| Exclude Pages | Skip critical CSS for specific pages |

### Generating Critical CSS

```bash
node critical.js
```

This prompts for the site URL and optional HTTP credentials, then fetches the critical CSS configuration from the WordPress REST API and generates optimized CSS for each template.

## CSS Enqueuing

The theme handles CSS enqueuing through `php/css.php`:

1. Dequeues any CSS libraries listed in "Remove CSS Libraries" (customizer)
2. Enqueues Swiper CSS from CDN (if Swiper is enabled)
3. Enqueues any additional CSS libraries (customizer)
4. Enqueues the main `styles.css` (or `styles.min.css`)
5. If critical CSS is available, switches stylesheets to async loading

### Non-Minified CSS

The customizer includes a toggle for **Use Original CSS (non-minified)**. On local environments (hostname ending in `.test` or `WPCONFIG_ENVNAME=local`), non-minified CSS is always served automatically.

### Adding/Removing CSS Libraries

In the Theme Customizer under **CSS**:

- **Additional CSS Libraries**: Add URLs (one per line) for CSS files to load. Supports both absolute URLs and relative paths.
- **Remove CSS Libraries**: Add URLs of registered stylesheets to dequeue.

## Per-Page Custom CSS

The theme supports per-page custom CSS through an ACF field called `custom_css`. If this field has content, it is rendered as an inline `<style>` tag in the page footer.

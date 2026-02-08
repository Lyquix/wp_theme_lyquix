# Architecture

## Design Philosophy

The Lyquix theme is built on four principles:

1. **Best practices** in HTML, CSS, JavaScript, SEO, accessibility, security, and performance
2. **Code reuse and consistency** for efficiency and reduced errors
3. **Unopinionated approach** allowing fully custom designs without theme interference
4. **Easy updates** without affecting customizations

The theme intentionally ships with minimal default styling. There is no opinionated `theme.json`, no preset color palettes, and no default page layouts. Everything is yours to define.

## Parent/Child Relationship

The theme uses a strict parent/child architecture:

```
lyquix/          (Parent Theme - DO NOT MODIFY)
lyquix_child/    (Child Theme - All customizations go here)
```

**The parent theme is the framework.** It provides:
- PHP infrastructure (routing, blocks, modules, utilities)
- TypeScript/JavaScript library
- SCSS base files and Tailwind configuration
- ACF field group definitions
- Gutenberg block registrations
- Build tooling configuration

**The child theme is the project.** It contains:
- All site-specific customizations
- Custom styles (SCSS)
- Custom scripts (TypeScript)
- Template overrides
- Block rendering overrides
- The main HTML template (`custom.php`)

### The "Do Not Modify" Rule

Every file in the parent theme is marked with a prominent ASCII art banner:

```
DO NOT MODIFY THIS FILE!
```

This is enforced by convention, not by code. The parent theme is designed to be replaced wholesale when updates are released. Any modifications to parent files will be lost on update.

## Extension Points

All customization is channeled through well-defined extension points in the child theme:

| Extension Point | Location | Purpose |
|----------------|----------|---------|
| Main template | `custom.php` | Site HTML structure (header, footer, nav) |
| Custom functions | `php/custom/functions.php` | WordPress hooks, filters, custom PHP |
| Menu positions | `php/custom/menus.php` | Register additional menu locations |
| Widget areas | `php/custom/widgets.php` | Register additional widget areas |
| Shortcodes | `php/custom/shortcodes.php` | Define custom shortcodes |
| Feature flags | `php/custom/features.php` | Toggle custom features |
| Template routing | `php/custom/router.php` | Custom template routing logic |
| Option pages | `php/custom/options.php` | Custom ACF admin pages |
| Page templates | `php/custom/templates/` | PHP template files |
| Block overrides | `php/custom/blocks/` | Block rendering overrides |
| Module overrides | `php/custom/modules/` | Module rendering overrides |
| Custom styles | `css/custom/` | SCSS files organized by SMACSS |
| Custom scripts | `js/scripts.ts` | TypeScript entry point |
| Breakpoints | `css/tailwind/breakpoints.json` | Screen breakpoints (shared by CSS, JS, PHP) |
| Tailwind theme | `css/tailwind/theme.js` | Colors, fonts, spacing |

## The Dist File Pattern

The parent theme uses `.dist` files as templates for child theme customization:

```
.htaccess                        → .htaccess
custom.dist.php                  → custom.php
css/custom/custom.dist.scss      → css/custom/custom.scss
css/tailwind/breakpoints.dist.json → css/tailwind/breakpoints.json
css/tailwind/presets.dist.js     → css/tailwind/presets.js
css/tailwind/theme.dist.js       → css/tailwind/theme.js
js/scripts.dist.ts               → js/scripts.ts
js/custom/scripts/module.dist.ts → js/custom/scripts/module.ts
php/custom/templates/404.dist.php    → php/custom/templates/404.php
php/custom/templates/search.dist.php → php/custom/templates/search.php
css/lib/*/_*.dist.scss               → css/custom/*/_*.scss
```

When you run `bun install`, the `postinstall.sh` script copies these dist files into the child theme **only if they don't already exist**. This ensures:

- New installations get a working starting point
- Existing customizations are never overwritten
- Theme updates can add new dist files without disrupting existing projects

## PHP Namespace Structure

All PHP code is namespaced under `lqx\*`:

| Namespace | File | Purpose |
|-----------|------|---------|
| `lqx\setup` | `php/setup.php` | Theme initialization and features |
| `lqx\customizer` | `php/customizer.php` | Theme customizer settings |
| `lqx\router` | `php/router.php` | Template routing engine |
| `lqx\util` | `php/util.php` | Utility functions |
| `lqx\blocks` | `php/blocks.php` | Block system (settings, rendering, presets) |
| `lqx\layouts` | `php/layouts.php` | Layout block registration |
| `lqx\modules` | `php/modules.php` | Module loading and rendering |
| `lqx\cards` | `php/cards.php` | Card component utilities |
| `lqx\css` | `php/css.php` | CSS enqueuing and critical path |
| `lqx\js` | `php/js.php` | JavaScript enqueuing and GTM |
| `lqx\filters` | `php/filters.php` | Post filtering system |
| `lqx\fields` | `php/fields.php` | ACF field utilities |
| `lqx\meta` | `php/meta.php` | Meta tag rendering |
| `lqx\favicon` | `php/favicon.php` | Favicon rendering |
| `lqx\body` | `php/body.php` | Body class generation |
| `lqx\ip2geo` | `php/ip2geo.php` | IP geolocation |
| `lqx\browsers` | `php/browsers.php` | Outdated browser detection |
| `lqx\modules\alerts` | `php/modules/alerts/` | Alert messages |
| `lqx\modules\cta` | `php/modules/cta/` | Call-to-action |
| `lqx\modules\modal` | `php/modules/modal/` | Modal dialogs |
| `lqx\modules\popup` | `php/modules/popup/` | Popup notifications |
| `lqx\modules\share` | `php/modules/share/` | Social sharing |
| `lqx\modules\social` | `php/modules/social/` | Social media icons |

## Loading Order

The `functions.php` file loads all PHP modules in this order:

1. `util.php` - Utility functions (available to everything else)
2. `comments.php` - Comment handling
3. `setup.php` - Theme setup and feature registration
4. `menus.php` - Menu position registration
5. `widgets.php` - Widget area registration
6. `customizer.php` - Theme customizer settings
7. `blocks.php` - Gutenberg blocks system
8. `cards.php` - Card component utilities
9. `layouts.php` - Layout blocks
10. `modules.php` - Module system
11. `tailwind.php` - Tailwind integration
12. `meta.php` - Meta tag preparation
13. `css.php` - CSS enqueuing
14. `js.php` - JavaScript enqueuing
15. `favicon.php` - Favicon rendering
16. `body.php` - Body class preparation
17. `router.php` - Template routing
18. `ip2geo.php` - IP geolocation
19. `browsers.php` - Browser detection
20. `filters.php` - Post filtering
21. `livereload.php` - Development livereload
22. `featured-posts.php` - Featured posts
23. `fields.php` - ACF field utilities
24. **`php/custom/functions.php`** - Child theme custom functions
25. **`php/custom/shortcodes.php`** - Child theme shortcodes
26. `update.php` - Theme update checker
27. `critical.php` - Critical path CSS endpoint
28. `misc.php` - Miscellaneous functions

Note that `custom/functions.php` and `custom/shortcodes.php` are loaded from the **child theme** (`get_stylesheet_directory()`), while all other files are loaded from the **parent theme** (`get_template_directory()`).

## Main HTML Template

The site's HTML structure is defined in `custom.php` (copied from `custom.dist.php`). This file controls:

- Document `<head>` (meta tags, GTM, wp_head, favicons)
- Body structure (header, main, footer)
- Navigation menus
- Module placement (alerts, CTAs, popups, social)
- Footer scripts (wp_footer, Lyquix options, livereload)

The default template includes:
- Skip-to-content accessibility link
- Header with alerts, top/main/utility/logged-in menus
- Main content area with template router output
- CTA module after main content
- Footer with bottom/footer menus, social icons, sharing
- Popup module
- Browser alert
- LiveReload

### Chromeless Template

The theme supports a "chromeless" page template that renders only the router output without header, footer, or navigation. This is useful for pages embedded in iframes or API responses.

## ACF Field Groups

The theme stores ACF field group definitions as JSON files in `acf-json/`. There are 40+ field groups covering:

- Block global settings (per block type)
- Block user settings (per block instance)
- Block admin settings (per block instance override)
- Block styles and presets
- Block content fields
- Module settings
- Filter configuration

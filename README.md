# Lyquix WordPress Theme 3.0.0

`@version     3.0.0`

Full documentation coming soon...

Lyquix started releasing its own starter theme in 2016 with the goals of:

- Following best practices in HTML, CSS, JavaScript, SEO, accessibility, security, and performance.
- Promoting code reuse and consistency for efficiency, reduced user errors, and developer quality of life.
- Allowing for custom designs and functionality without interference from the theme (unopinionated approach).
- Enabling easy updates without affecting customizations.

This new 3.0.0 release continues to uphold these same goals.

## Highlights of Changes and New Features

### CSS
- Implements new breakpoints: 480, 720, 1080, 1620px.
- Migrates to Tailwind.

### JavaScript
- Migrates all JavaScript code to TypeScript.
- Removes unused functionality from the Lyquix library.
- Migrates from MomentJS to DayJS.
- Adds support for Microsoft Clarity.
- Adds analytics event tracking for fine-grained user interaction tracking.
- Detects light/dark theme from the operating system and provides an API to track user selection.

### WordPress
- **Gutenberg Blocks**
 - Accordion
 - Banner
 - Cards
 - Gallery
 - Hero Banner
 - Layouts: box, center, cluster, container, cover, frame, grid, icon, imposter, reel, sidebar, stack, switcher
 - Logos
 - Slider
 - Tabs
 - All blocks include:
   - Default settings and Global settings override
   - Ability to create new settings presets for block insertion
   - Ability to define a list of available styles for block insertion

- **Modules**
 - Alerts
 - CTAs
 - Modals
 - Popups
 - Social sharing
 - Social icons

- **Filters**
 - Extensive functionality to render posts with filtering
 - Filters by category, tags, custom taxonomies, and any ACF field
 - Configure pre-filters (custom post type, date ranges, categories tags, custom taxonomies, and ACF fields)
 - Configure sorting and pagination
 - AJAX-enabled
 - Leverage Cards block for rendering

- Browser detection
- IP geolocation
- Disable comments functionality
- Check for required plugins, provide one-click installation and activation
- Check for theme updates

### Developer Tooling
- Migrates from Node.js to Bun.
- Faster SCSS processing and TypeScript compilation.
- Auto-compilation of CSS and JavaScript with auto-reload.

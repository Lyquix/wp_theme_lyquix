# Lyquix WordPress Theme

`@version 3.2.0`

A modern, unopinionated WordPress starter theme designed for agencies and developers who need maximum flexibility without fighting theme defaults.

## Philosophy

Lyquix started releasing its own starter theme in 2016 with the goals of:

- Following best practices in HTML, CSS, JavaScript, SEO, accessibility, security, and performance.
- Promoting code reuse and consistency for efficiency, reduced user errors, and developer quality of life.
- Allowing for custom designs and functionality without interference from the theme (unopinionated approach).
- Enabling easy updates without affecting customizations.

## Key Features

- **14 Content Blocks** - Accordion, Banner, Cards, Filters, Gallery, Hero, Logos, Map, Slider, Tabs, Testimonial, and more
- **13 Layout Blocks** - CSS layout primitives (Box, Center, Cluster, Container, Cover, Frame, Grid, Icon, Imposter, Reel, Sidebar, Stack, Switcher)
- **6 Modules** - Alerts, CTAs, Modal, Popup, Social Sharing, Social Icons
- **Three-Level Block Settings** - Global defaults, saved presets, per-instance user/admin overrides
- **Tailwind CSS + SMACSS** - Hybrid styling with utility-first and component-based approaches
- **TypeScript Library** - 28 modules for detection, analytics, UI components, and utilities
- **Custom Template Router** - Flexible routing engine replacing the standard WordPress template hierarchy
- **60+ Customizer Options** - CSS, JS, analytics, geolocation, browser detection, and feature flags
- **Critical Path CSS** - Per-post-type inline critical CSS with async stylesheet loading
- **Modern Build Chain** - Bun + Gulp + Sass + Tailwind + PostCSS with LiveReload

## Quick Start

```bash
# 1. Copy both themes to wp-content/themes/
cp -r lyquix wp-content/themes/
cp -r lyquix_child wp-content/themes/

# 2. Install dependencies (from child theme directory)
cd wp-content/themes/lyquix_child
bun install

# 3. Activate the child theme in WordPress admin

# 4. Install required plugins (ACF PRO, ACF Extended PRO)

# 5. Start development
bun run watch
```

## Required Plugins

- [Advanced Custom Fields PRO](https://www.advancedcustomfields.com/pro/)
- [ACF Extended PRO](https://www.acf-extended.com/)

## Documentation

### Getting Started
- **[Installation](docs/installation.md)** - Prerequisites, setup, and initial configuration
- **[Architecture](docs/architecture.md)** - Parent/child relationship, design philosophy, extension points, PHP namespaces

### Core Systems
- **[Template System](docs/template-system.md)** - Custom router, template resolution, creating page templates
- **[Content Blocks](docs/blocks.md)** - All 14 blocks, three-level settings, presets, styles, overrides
- **[Layout Blocks](docs/layout-blocks.md)** - 13 CSS layout primitives, Tailwind integration, ACF-to-class conversion
- **[Modules](docs/modules.md)** - Alerts, CTAs, Modal, Popup, Share, Social - configuration and overrides

### Styling and Scripts
- **[Styling](docs/styling.md)** - SMACSS + Tailwind hybrid, SCSS structure, breakpoints, critical CSS
- **[JavaScript](docs/javascript.md)** - lqx and $lqx libraries, TypeScript modules, adding custom scripts

### Configuration
- **[Theme Customizer](docs/customizer.md)** - All 60+ options: CSS, JS, analytics, geolocation, features
- **[Integrations](docs/integrations.md)** - Google Maps, GA4, GTM, Clarity, MaxMind, browser detection

### Development
- **[Build System](docs/build-system.md)** - Bun, Gulp, Sass, Tailwind, PostCSS pipeline
- **[Developer Guide](docs/developer-guide.md)** - Workflow, creating blocks/modules/templates, hooks/filters reference

## Technology Stack

| Layer | Technology |
|-------|-----------|
| CSS | Tailwind CSS 3.4, Sass, PostCSS, cssnano, autoprefixer |
| JavaScript | TypeScript 5.4, Bun, Rollup |
| PHP | Namespaced PHP 7.4+, ACF PRO |
| Build | Bun, Gulp 4, LiveReload |
| Libraries | Swiper 11, Day.js, MobileDetect, Vue.js 3 (optional) |

## License

GNU General Public License version 2 or later

## Links

- [GitHub Repository](https://github.com/Lyquix/wp_theme_lyquix)

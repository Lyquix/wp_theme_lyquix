# JavaScript

The Lyquix theme includes a comprehensive TypeScript library (`lqx`) and a customizable scripts library (`$lqx`). Both are compiled to browser-ready JavaScript using Bun.

## Two Libraries

### lqx (Lyquix Library)

The core framework library providing device detection, analytics, UI components, and utilities. Source: `js/lib/lyquix/` (parent theme). Compiled to `js/lyquix.js`.

**Do not modify** - this is part of the parent theme.

### $lqx (Scripts Library)

The project-specific scripts library for custom JavaScript. Source: `js/scripts.ts` (child theme). Compiled to `js/scripts.js`.

**This is where you add custom functionality.**

## Initialization

Both libraries initialize via inline scripts rendered in the page footer by `\lqx\js\render_lyquix_options()`:

```javascript
// Lyquix library
lqx.init(options);  // or waits for 'lqxload' event

// Scripts library
$lqx.init(options); // or waits for '$lqxload' event
```

Options are passed as base64-encoded JSON for security. The initialization options come from the Theme Customizer settings (**Lyquix Library Options** and **Scripts Options**).

## TypeScript Modules

The Lyquix library consists of 28 TypeScript modules:

### Core Infrastructure

| Module | Purpose |
|--------|---------|
| `core.ts` | Main initialization, configuration management, debug levels |
| `util.ts` | Utility functions (hashing, DOM manipulation) |
| `detect.ts` | Device and browser detection using MobileDetect |
| `store.ts` | Client-side state management |
| `mutation.ts` | DOM mutation observation |

### UI Components

| Module | Purpose |
|--------|---------|
| `menu.ts` | Mobile menu toggle and responsive navigation |
| `tabs.ts` | Tabbed content interface |
| `accordion.ts` | Collapsible accordion sections |
| `modal.ts` | Modal dialog management |
| `alerts.ts` | Dismissible alert notifications |
| `slider.ts` | Carousel/slider powered by Swiper |
| `cards.ts` | Card component interactions |

### Advanced Features

| Module | Purpose |
|--------|---------|
| `gallery.ts` | Image/video gallery with LyqBox lightbox |
| `lyqbox.ts` | Custom lightbox implementation |
| `map.ts` | Google Maps integration with markers and info windows |
| `filters.ts` | AJAX-powered post filtering |
| `testimonial.ts` | Testimonial carousel |
| `theme.ts` | Light/dark mode detection from OS preferences |
| `analytics.ts` | GA4/GTM event tracking for user interactions |
| `swipe.ts` | Touch swipe gesture detection |

## Adding Custom Scripts

### The scripts.ts Entry Point

Edit `js/scripts.ts` in the child theme:

```typescript
import { core } from '../../lyquix/js/lib/scripts/core';

// Import your custom modules
import { myModule } from './custom/scripts/my-module';

const modules: { [key: string]: any } = {
    myModule
};

export const $lqx = {
    init(customCfg: { [key: string]: any } = {}) {
        core.init(customCfg, modules);
    },
    ready(callback: () => void) {
        core.ready(callback);
    },
    get cfg() { return core.cfg; },
    get vars() { return core.vars; },
};

document.dispatchEvent(new Event('$lqxload'));
(window as any).$lqx = $lqx;
```

### Creating a Custom Module

Create a file in `js/custom/scripts/`:

```typescript
// js/custom/scripts/my-module.ts

export const myModule = (() => {
    const cfg = {
        // Default configuration
        enabled: true,
        selector: '.my-component'
    };

    const vars = {
        // Runtime variables
    };

    function init(customCfg: object = {}) {
        Object.assign(cfg, customCfg);
        if (!cfg.enabled) return;

        // Initialization logic
        setup();
    }

    function setup() {
        const elements = document.querySelectorAll(cfg.selector);
        elements.forEach(el => {
            // Component logic
        });
    }

    return { init, cfg, vars };
})();
```

### Passing Options from PHP

You can pass configuration to your scripts via the Theme Customizer's **Scripts Options** field (JSON format):

```json
{
    "myModule": {
        "enabled": true,
        "selector": ".custom-selector"
    }
}
```

Or pass options directly in a template:

```php
<script>
    $lqx.ready(function() {
        // Custom initialization
    });
</script>
```

## Third-Party Libraries

### jQuery

Enabled by default. Can be toggled in Theme Customizer > JS. Version bundled with WordPress.

- `enable_jquery` - Enable/disable jQuery
- `enable_jquery_migrate` - Enable/disable jQuery Migrate
- `enable_jquery_ui` - Off / Core / Core + Sortable

### MobileDetect

Always loaded from CDN. Provides device detection (mobile, tablet, desktop) and is used by the `detect.ts` module.

### Swiper

Carousel/slider library loaded from CDN. Version 11. Used by the Slider, Testimonial, and Logos blocks.

- Toggle: Theme Customizer > JS > Swiper library
- CSS: `https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css`
- JS: `https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js`

### Day.js

Lightweight date manipulation library loaded from CDN. Replaces Moment.js.

- Toggle: Theme Customizer > JS > Day.js library
- JS: `https://cdn.jsdelivr.net/npm/dayjs@1/dayjs.min.js`
- Locale: `https://cdn.jsdelivr.net/npm/dayjs@1/locale/en.js`

### Vue.js 3

Optional frontend framework. If a `js/vue.js` file exists in the child theme, Vue.js 3 is loaded from CDN and the custom Vue bundle is loaded after it.

- CDN (dev): `https://cdn.jsdelivr.net/npm/vue@3/dist/vue.global.js`
- CDN (prod): `https://cdn.jsdelivr.net/npm/vue@3/dist/vue.global.prod.js`

## Analytics Event Tracking

The `analytics.ts` module provides automatic event tracking for GA4/GTM:

- Tracks fine-grained user interactions
- Sends events via `gtag()` or `dataLayer.push()`
- Configurable through the Lyquix Library Options

Configure analytics in the Theme Customizer:
- **GA4 Measurement ID** - Your Google Analytics 4 measurement ID
- **Send Pageview** - Whether to send automatic pageview events
- **GA via GTM** - Whether GA is loaded through Google Tag Manager

## JS Enqueuing

The theme handles JavaScript enqueuing through `php/js.php`:

1. Removes any JS libraries listed in "Remove JS Libraries" (customizer)
2. Configures jQuery, jQuery Migrate, jQuery UI based on settings
3. Enqueues MobileDetect from CDN
4. Enqueues Day.js from CDN (if enabled)
5. Enqueues Swiper from CDN (if enabled)
6. Enqueues additional JS libraries (customizer)
7. Enqueues the Lyquix library
8. Enqueues Vue.js (if present)
9. Enqueues the Scripts library

All scripts are loaded in the footer with `true` as the `$in_footer` parameter.

### Non-Minified JS

The customizer includes a toggle for **Use Original JS (non-minified)**. On local environments (hostname ending in `.test` or `WPCONFIG_ENVNAME=local`), non-minified JS is always served automatically.

### Debug Levels

The Lyquix library supports 4 debug levels configurable in the Theme Customizer:

| Level | Output |
|-------|--------|
| 0 | None |
| 1 | Errors only |
| 2 | Errors + Warnings |
| 3 | Errors + Warnings + Info |

### Adding/Removing JS Libraries

In the Theme Customizer under **JS**:

- **Additional JS Libraries**: Add URLs (one per line) for JS files to load
- **Remove JS Libraries**: Add handles of registered scripts to dequeue

## Per-Page Custom JS

The theme supports per-page custom JavaScript through an ACF field called `custom_js`. If this field has content, it is rendered as an inline `<script>` tag in the page footer.

## Global Objects

After initialization, these objects are available on `window`:

- `window.lqx` - The Lyquix library (core framework)
- `window.$lqx` - The Scripts library (project-specific)

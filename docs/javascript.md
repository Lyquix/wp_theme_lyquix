# JavaScript

The Lyquix theme includes a comprehensive TypeScript library (`lqx`) and a customizable scripts library (`$lqx`). Both are compiled to browser-ready JavaScript using Bun.

## Two Libraries

### lqx (Lyquix Library)

The core framework library providing device detection, analytics, UI components, and utilities. Source: `js/lib/lyquix/` (parent theme). Compiled to `js/lyquix.js`.

**Do not modify** - this is part of the parent theme.

### $lqx (Scripts Library)

The project-specific scripts library for custom JavaScript. Source: `js/scripts.ts` (child theme). Compiled to `js/scripts.js`.

**This is where you add custom functionality.**

## Initialization & Lifecycle

Both libraries follow the same lifecycle:

1. **Script loads** — the library file is parsed, all modules are defined (but not yet initialized)
2. **`lqxload` / `$lqxload` event** — dispatched immediately after the library is assigned to `window.lqx` / `window.$lqx`
3. **`init(options)` called** — PHP renders an inline script in the footer that calls `lqx.init(options)` with Theme Customizer settings (base64-encoded JSON)
4. **Modules initialize in order** — each module's `init()` is called with its portion of the options object
5. **`lqxready` / `$lqxready` event** — dispatched after all modules have initialized

```javascript
// Wait for lqx to be ready, then run custom code
lqx.ready(function() {
    console.log('Screen size:', lqx.responsive.screen);
    console.log('Is mobile:', lqx.detect.mobile);
});

// Or listen for the event directly
jQuery(document).on('lqxready', function() {
    // lqx is fully initialized
});
```

### Module Initialization Order

Modules initialize in a specific order because some depend on others:

1. `mutation` — DOM observer (needed by nearly everything)
2. `store` — persistent storage
3. `util` — utility functions
4. `detect` — device/browser detection
5. `responsive` — breakpoint detection
6. `analytics` — event tracking
7. `geolocate` — geolocation
8. `swipe` — gesture detection
9. `lyqbox` — lightbox
10. `theme` — dark/light mode
11. `menu` — navigation
12. `video` — video helpers
13. Block modules: `accordion`, `alerts`, `cards`, `filters`, `gallery`, `map`, `modal`, `popup`, `tabs`, `slider`, `testimonial`

### Configuration via Theme Customizer

Options are passed from PHP via the Theme Customizer settings (**Lyquix Library Options** and **Scripts Options**). Each module receives its own configuration key:

```json
{
    "debug": 2,
    "siteURL": "https://example.com",
    "responsive": { "enabled": true },
    "analytics": { "enabled": true, "gaId": "G-XXXXXXX" },
    "detect": { "mobile": true, "browser": true }
}
```

### Module Pattern

Every module in the library follows the same IIFE (Immediately Invoked Function Expression) pattern:

```typescript
export const myModule = (() => {
    const init = (customCfg?: object) => {
        // Prevent double initialization
        if (vars.myModule?.init) return;

        // Runtime variables
        vars.myModule = { init: false };

        // Default configuration
        cfg.myModule = { enabled: true };

        // Merge custom config
        if (customCfg) cfg.myModule = jQuery.extend(true, cfg.myModule, customCfg);

        // Module logic...
        if (cfg.myModule.enabled) {
            log('Initializing myModule');
            // setup...
        }

        vars.myModule.init = true;
    };

    return { init };
})();
```

Key conventions:
- **`cfg.moduleName`** — configuration object, set once during init, merged with custom options
- **`vars.moduleName`** — runtime state, mutable during the page lifecycle
- **`vars.moduleName.init`** — boolean flag preventing double initialization
- Read-only properties are exposed via `Object.defineProperties()` with no-op setters

## TypeScript Modules

The Lyquix library consists of 24 TypeScript modules organized in three categories. Each module follows the same IIFE pattern with `init()`, `cfg`, and `vars`. See the detailed reference pages for full API documentation.

### Core Infrastructure

| Module | Purpose |
|--------|---------|
| `core.ts` | Global `cfg`/`vars`, logging (`log`, `warn`, `error`), debug levels |
| `util.ts` | Cookies, URL parsing, hashing, data validation, string manipulation |
| `detect.ts` | Browser, OS, and mobile detection — adds CSS classes to `<body>` |
| `store.ts` | Persistent client-side storage via `localStorage` with auto-save |
| `mutation.ts` | DOM mutation observer — register callbacks for added/removed/changed nodes |
| `responsive.ts` | Breakpoint detection, orientation, aspect ratio — sets `<body>` attributes |

([Detailed reference](js-core-modules.md))

### UI Components

| Module | Purpose |
|--------|---------|
| `menu.ts` | Mobile menu toggle with `aria-expanded` |
| `tabs.ts` | Tabbed interface with responsive accordion conversion |
| `accordion.ts` | Collapsible panels with URL hash support and multi-open option |
| `modal.ts` | Modal dialogs via HTML5 `<dialog>` with AJAX loading, cookies, display logic |
| `alerts.ts` | Alert notification slider (Swiper) with AJAX loading and dismissal cookies |
| `slider.ts` | Image/content carousel (Swiper) with pagination thumbnails |
| `cards.ts` | Responsive card grid (Swiper) with breakpoint-based column rules |
| `popup.ts` | Popup notifications with CSS-based visibility, cookies, display logic |

([Detailed reference](js-ui-components.md))

### Advanced Features

| Module | Purpose |
|--------|---------|
| `gallery.ts` | Image/video gallery with LyqBox lightbox integration |
| `lyqbox.ts` | Custom lightbox supporting images, videos, iframes, and inline HTML |
| `map.ts` | Google Maps with markers, info windows, and style customization |
| `filters.ts` | AJAX-powered post filtering with URL state management |
| `testimonial.ts` | Testimonial carousel (Swiper) |
| `theme.ts` | Light/dark mode detection from OS `prefers-color-scheme` |
| `analytics.ts` | GA4/GTM pageview and event tracking |
| `swipe.ts` | Touch swipe gesture detection with configurable thresholds |
| `geolocate.ts` | IP and GPS geolocation with region-based element visibility |
| `video.ts` | Video lazy loading, hover-play, and viewport-play via IntersectionObserver |

([Detailed reference](js-advanced-modules.md))

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

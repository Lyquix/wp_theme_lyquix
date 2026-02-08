# Core Modules Reference

Detailed API reference for the core infrastructure modules of the Lyquix JavaScript library.

## core

**File:** `js/lib/lyquix/core.ts`

The foundation module that provides the shared `cfg` and `vars` objects and the logging system used by all other modules.

### Shared Objects

```typescript
// Global configuration — each module adds its own key (e.g., cfg.detect, cfg.responsive)
export const cfg = {
    debug: 0,       // Debug level (0=none, 1=errors, 2=+warnings, 3=+info)
    siteURL: '',    // Site URL, set during init from PHP options
    tmplURL: ''     // Theme URL, set during init from PHP options
};

// Global runtime variables — each module adds its own key
export const vars = {
    window: jQuery(window),
    document: jQuery(document),
    html: null,     // Set to jQuery('html') on document ready
    body: null,     // Set to jQuery('body') on document ready
    init: false     // Set to true after init completes
};
```

### Logging Functions

All logging functions check `cfg.debug` before outputting. They accept any number of arguments.

| Function | Debug Level | Console Method |
|----------|------------|----------------|
| `error(...args)` | >= 1 | `console.error()` |
| `warn(...args)` | >= 2 | `console.warn()` |
| `log(...args)` | >= 3 | `console.log()` |

```javascript
// These are available on the global lqx object
lqx.log('Info message', someData);
lqx.warn('Warning message');
lqx.error('Error message', errorObj);
```

### Global API

After initialization, `window.lqx` exposes:

| Property | Type | Description |
|----------|------|-------------|
| `lqx.cfg` | object (read-only) | Global configuration |
| `lqx.vars` | object (read-only) | Global runtime variables |
| `lqx.version` | string (read-only) | Library version |
| `lqx.init(options)` | function | Initialize the library |
| `lqx.ready(callback)` | function | Run callback when library is ready |
| `lqx.log/warn/error` | functions | Logging |
| `lqx.{moduleName}` | object | Access any module |

---

## util

**File:** `js/lib/lyquix/util.ts`

Utility functions for cookies, URL parsing, hashing, data validation, and string manipulation.

### Configuration

```typescript
cfg.util = {
    enabled: true
};
```

### Functions

#### `cookie(name, value?, options?)`

Read, set, or delete cookies.

```javascript
// Read a cookie
const value = lqx.util.cookie('myCookie');

// Set a cookie
lqx.util.cookie('myCookie', 'value', {
    maxAge: 86400,    // seconds (1 day)
    path: '/',
    secure: true,
    sameSite: 'Lax'
});

// Delete a cookie
lqx.util.cookie('myCookie', '', { maxAge: 0, path: '/' });
```

**Parameters:**
- `name` (string) — cookie name
- `value` (string, optional) — if provided, sets the cookie; if omitted, reads it
- `options` (object, optional) — `maxAge`, `path`, `domain`, `secure`, `sameSite`

**Returns:** Cookie value (string) when reading, or `undefined` when setting.

#### `parseUrlParams(url)`

Parse URL query parameters into an object.

```javascript
const params = lqx.util.parseUrlParams('https://example.com?page=2&filter=active&sort');
// { page: '2', filter: 'active', sort: null }
```

#### `hash(str)`

Generate a simple numeric hash from a string.

```javascript
const h = lqx.util.hash('some string');
// Returns: integer hash value
```

#### `uniqueUrl(url)`

Append a unique timestamp parameter to a URL to prevent caching.

```javascript
const fresh = lqx.util.uniqueUrl('/api/data');
// '/api/data?t=1706123456789'
```

#### `validateData(data, schema)`

Validate and sanitize a data object against a schema definition. Used internally by blocks and modules to validate AJAX responses and data attributes.

```javascript
const schema = {
    name: { type: 'string', required: true },
    count: { type: 'integer', default: 0 },
    enabled: { type: 'string', allowed: ['y', 'n'], default: 'y' }
};

const result = lqx.util.validateData(data, schema);
// Returns validated/sanitized object, or null if validation fails
```

**Schema properties:**
- `type` — `'string'`, `'integer'`, `'float'`, `'boolean'`, `'array'`, `'object'`
- `required` — if `true`, field must be present
- `default` — default value when field is missing
- `allowed` — array of allowed values
- `match` — regex pattern string must match
- `keys` — for objects, schema for nested keys
- `elems` — for arrays, schema for array elements

#### `camelCase(str)`

Convert a string to camelCase.

```javascript
lqx.util.camelCase('my-component-name');  // 'myComponentName'
lqx.util.camelCase('my_component_name');  // 'myComponentName'
```

#### `dashCase(str)`

Convert a string to dash-case (kebab-case).

```javascript
lqx.util.dashCase('myComponentName');  // 'my-component-name'
```

---

## store

**File:** `js/lib/lyquix/store.ts`

Persistent client-side storage using `localStorage`. Tracks specified objects and automatically persists them on `beforeunload` and at regular intervals.

### Configuration

```typescript
cfg.store = {
    enabled: true,
    saveInterval: 5000  // Auto-save every 5 seconds (ms)
};
```

### Functions

#### `set(key, data)`

Save data to localStorage immediately.

```javascript
lqx.store.set('userPrefs', { theme: 'dark', fontSize: 14 });
```

#### `get(key)`

Retrieve data from localStorage.

```javascript
const prefs = lqx.store.get('userPrefs');
// { theme: 'dark', fontSize: 14 }
```

#### `remove(key)`

Remove a key from localStorage.

```javascript
lqx.store.remove('userPrefs');
```

#### `track(key, obj)`

Register an object for automatic persistence. The store saves the object to localStorage on a timer and before page unload.

```javascript
const state = { count: 0, items: [] };
lqx.store.track('appState', state);

// Later, modify the object directly — it auto-saves
state.count++;
state.items.push('new item');
// Saved automatically on interval and on page unload
```

#### `untrack(key)`

Stop tracking an object.

```javascript
lqx.store.untrack('appState');
```

### How It Works

1. On init, sets up a `setInterval` timer (every `saveInterval` ms) that persists all tracked objects
2. On `beforeunload`, saves all tracked objects immediately
3. Data is stored as JSON strings in `localStorage`

---

## mutation

**File:** `js/lib/lyquix/mutation.ts`

Wraps the browser's `MutationObserver` API to let modules register callbacks for DOM changes. This is critical for handling dynamically loaded content (AJAX, Gutenberg blocks added after page load, etc.).

### Configuration

```typescript
cfg.mutation = {
    enabled: true
};
```

### Functions

#### `addHandler(type, selector, callback)`

Register a callback that fires when a matching DOM mutation occurs.

**Parameters:**
- `type` (string) — one of `'addNode'`, `'removeNode'`, or `'modAttrib'`
- `selector` (string) — CSS selector to match elements
- `callback` (function) — called with the matching element

```javascript
// React to dynamically loaded content
lqx.mutation.addHandler('addNode', '.product-card', function(element) {
    // Initialize a plugin on the new element
    jQuery(element).find('.price').formatCurrency();
});

// Clean up when elements are removed
lqx.mutation.addHandler('removeNode', '.carousel', function(element) {
    // Destroy carousel plugin
});

// React to attribute changes
lqx.mutation.addHandler('modAttrib', '[data-status]', function(element) {
    const status = element.getAttribute('data-status');
    console.log('Status changed to:', status);
});
```

### Observer Configuration

The observer watches `document.body` with:
- `childList: true` — detect added/removed nodes
- `subtree: true` — watch all descendants
- `attributes: true` — detect attribute changes

When a parent node is added, handlers fire for the parent **and all its descendants**.

### How Blocks Use This

Most block modules (accordion, tabs, slider, cards, etc.) register mutation handlers during their `init()` so that blocks added dynamically after page load are automatically initialized:

```typescript
// Inside accordion.ts init()
mutation.addHandler('addNode', cfg.accordion.accordionBlockSelector, setup);
```

---

## detect

**File:** `js/lib/lyquix/detect.ts`

Detects the user's browser, operating system, mobile device type, and URL parameters. Sets CSS classes and attributes on `<body>` for styling hooks.

### Configuration

```typescript
cfg.detect = {
    enabled: true,
    mobile: true,     // Enable mobile detection (requires MobileDetect library)
    browser: true,    // Enable browser detection
    os: true,         // Enable OS detection
    urlParams: true   // Enable URL parameter parsing
};
```

### Read-Only Properties

```javascript
lqx.detect.mobile     // { mobile: bool, phone: bool, tablet: bool } or null
lqx.detect.browser    // { name: string, type: string, version: string }
lqx.detect.os         // { name: string, type: string, version: string }
lqx.detect.urlParams  // { key: 'value', flag: null }
```

### CSS Classes Added to `<body>`

**Mobile:** `.mobile`, `.phone`, `.tablet`

**Browser:** `.chrome`, `.firefox`, `.safari`, `.msedge`, `.opera`, `.msie`
Plus version classes: `.chrome-120`, `.chrome-120-0-6`

**OS:** `.windows`, `.macosx`, `.ios`, `.android`, `.ubuntu`, `.fedora`, `.chromeos`
Plus version classes: `.windows-11`, `.macosx-14-2`

### Usage in CSS

```css
/* Mobile-only styles */
.phone .desktop-nav { display: none; }

/* Browser-specific fix */
.safari .flex-container { -webkit-flex-wrap: wrap; }

/* Platform-specific */
.ios .scroll-container { -webkit-overflow-scrolling: touch; }
```

### Usage in JavaScript

```javascript
if (lqx.detect.mobile?.phone) {
    initMobileVersion();
}

if (lqx.detect.urlParams.debug === 'true') {
    enableDebugMode();
}
```

### Detected Browsers

Opera, Internet Explorer (`msie`), Microsoft Edge (`msedge`), Chrome, Firefox, Safari.

### Detected Operating Systems

iOS, Windows Phone, Android, Windows, Mac OS X, Ubuntu, Fedora, Chrome OS.

---

## responsive

**File:** `js/lib/lyquix/responsive.ts`

Monitors screen size, orientation, and aspect ratio. Detects which breakpoint is active and sets attributes on `<body>`. Fires custom events when values change. Breakpoints are loaded from the shared `breakpoints.json` file.

### Configuration

```typescript
cfg.responsive = {
    enabled: true,
    sizes: ['xs', 'sm', 'md', 'lg', 'xl'],      // From breakpoints.json keys
    breakPoints: [320, 480, 720, 1080, 1620]       // From breakpoints.json widths
};
```

### Read-Only Properties

```javascript
lqx.responsive.screen        // Current breakpoint name: 'xs', 'sm', 'md', 'lg', 'xl'
lqx.responsive.orientation   // 'portrait' or 'landscape'
lqx.responsive.aspectRatio   // 'ultranarrow', 'narrow', 'standard', 'wide', 'ultrawide'
```

### Body Attributes

```html
<body screen="lg" orientation="landscape" aspectratio="wide">
```

### Events

| Event | Target | When |
|-------|--------|------|
| `screensizechange` | `document` | Active breakpoint changes |
| `aspectratiochange` | `document` | Aspect ratio category changes |

```javascript
jQuery(document).on('screensizechange', function() {
    if (lqx.responsive.screen === 'xs' || lqx.responsive.screen === 'sm') {
        initMobileMenu();
    } else {
        initDesktopNav();
    }
});
```

### Aspect Ratio Categories

| Category | Ratio | Typical Device |
|----------|-------|----------------|
| `ultranarrow` | < 0.5 | Very tall/thin windows |
| `narrow` | 0.5 – 1.0 | Phones in portrait |
| `standard` | 1.0 – 1.5 | Tablets, near-square |
| `wide` | 1.5 – 2.0 | Desktop, laptop |
| `ultrawide` | >= 2.0 | Ultra-wide monitors |

### CSS Usage

```css
/* Responsive styles using body attributes */
body[screen="xs"] .sidebar { display: none; }
body[screen="lg"] .grid { grid-template-columns: repeat(3, 1fr); }
body[orientation="portrait"] .hero { min-height: 80vh; }
body[aspectratio="ultrawide"] .container { max-width: 1600px; }
```

### Media Query Setup

The module creates `matchMedia` listeners for each breakpoint:

- **xs** (first): `(max-width: 479px)` — from 0 to next breakpoint minus 1
- **sm–lg** (middle): `(min-width: Xpx) and (max-width: Ypx)` — between breakpoints
- **xl** (last): `(min-width: 1620px)` — from last breakpoint upward

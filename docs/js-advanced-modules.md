# Advanced Modules Reference

Detailed API reference for the advanced feature modules of the Lyquix JavaScript library.

## analytics

**File:** `js/lib/lyquix/analytics.ts`

Google Analytics 4 (GA4) and Google Tag Manager (GTM) integration for pageview and event tracking.

### Configuration

```typescript
cfg.analytics = {
    enabled: true,
    gaId: '',              // GA4 Measurement ID (e.g., 'G-XXXXXXX')
    sendPageview: true,    // Send automatic pageview on init
    gaViaGTM: false        // Whether GA is loaded through GTM
};
```

### Functions

#### `sendGAPageview(pageInfo?)`

Send a pageview event to GA4.

```javascript
// Default — sends pageview for current page
lqx.analytics.sendGAPageview();

// Custom pageview
lqx.analytics.sendGAPageview({
    page_title: 'Custom Page',
    page_location: '/custom/path'
});
```

**Parameters:**
- `pageInfo` (object, optional) — custom page properties. Must be a valid object.

**Behavior:** Calls `gtag('event', 'page_view', ...)` or pushes to `dataLayer` depending on `gaViaGTM` setting.

#### `sendGAEvent(eventData)`

Send a custom event to GA4.

```javascript
lqx.analytics.sendGAEvent({
    eventAction: 'click',          // Required
    eventName: 'cta_click',        // Required
    eventCategory: 'CTA',
    eventLabel: 'Hero Button',
    nonInteraction: false
});
```

**Parameters:**
- `eventData.eventAction` (string, required) — the action type
- `eventData.eventName` (string, required) — the GA4 event name
- Other properties are passed through as event parameters

### How Other Modules Use It

Block modules call `sendGAEvent` for user interactions:

```typescript
// Inside accordion.ts when a panel is opened
analytics.sendGAEvent({
    eventName: 'accordion',
    eventAction: 'Open',
    eventCategory: 'Accordion',
    eventLabel: headerText,
    nonInteraction: false
});
```

---

## theme

**File:** `js/lib/lyquix/theme.ts`

Detects the user's OS color scheme preference (light/dark mode) and allows programmatic override.

### Configuration

```typescript
cfg.theme = {
    enabled: true
};
```

### Variables

```typescript
vars.theme = {
    userPref: '',     // User override: 'dark' or 'light' (empty = follow OS)
    osPref: ''        // Detected OS preference: 'dark' or 'light'
};
```

### Functions

#### `set(userPref)`

Override the color scheme.

```javascript
lqx.theme.set('dark');   // Force dark mode
lqx.theme.set('light');  // Force light mode
```

**Validation:** Only accepts `'dark'` or `'light'`. Logs a warning for invalid values.

### Body Attribute

Sets `color-scheme` attribute on `<body>`:

```html
<body color-scheme="dark">
```

### CSS Usage

```css
body[color-scheme="dark"] {
    background: #1a1a1a;
    color: #ffffff;
}
body[color-scheme="light"] {
    background: #ffffff;
    color: #333333;
}
```

### Detection

Uses `window.matchMedia('(prefers-color-scheme: dark)')` and listens for changes so the theme updates automatically if the user changes their OS setting.

---

## swipe

**File:** `js/lib/lyquix/swipe.ts`

Touch swipe gesture detection. Register callbacks for swipe events on specific elements.

### Configuration

```typescript
cfg.swipe = {
    enabled: true,
    minDistance: 50,    // Minimum swipe distance in pixels
    maxTime: 300       // Maximum swipe duration in milliseconds
};
```

### Functions

#### `add(selector, callback, customCfg?)`

Register a swipe handler on elements matching the selector.

```javascript
lqx.swipe.add('.swipeable', function(direction, element) {
    console.log('Swiped', direction, 'on', element);
    // direction: 'left', 'right', 'up', or 'down'
}, {
    minDistance: 30,
    maxTime: 500
});
```

**Parameters:**
- `selector` (string) — CSS selector
- `callback` (function) — receives `(direction, element)`
- `customCfg` (object, optional) — override `minDistance` and `maxTime`

**Validation:** Both `selector` (string) and `callback` (function) are required. Logs warning on invalid input.

#### `remove(selector)`

Remove a swipe handler.

```javascript
lqx.swipe.remove('.swipeable');
```

#### `update(selector, callback, customCfg?)`

Replace a swipe handler (removes then re-adds).

```javascript
lqx.swipe.update('.swipeable', newCallback, { minDistance: 100 });
```

### How It Works

1. Attaches `touchstart` listener to capture start position/time
2. Attaches `touchend` listener to calculate distance and direction
3. If distance >= `minDistance` and time <= `maxTime`, calls the callback
4. Direction is determined by which axis has the larger delta

---

## geolocate

**File:** `js/lib/lyquix/geolocate.ts`

IP-based and GPS-based geolocation with region detection and element visibility control.

### Configuration

```typescript
cfg.geolocate = {
    enabled: true,
    gps: false,                     // Enable GPS geolocation (asks user permission)
    useCookies: true,               // Cache results in cookies
    cookieExpirationIP: 86400,      // IP cookie TTL (seconds, default: 1 day)
    cookieExpirationGPS: 3600,      // GPS cookie TTL (seconds, default: 1 hour)
    handleNoRegionMatch: true,      // Handle elements when no region matches
    removeNoRegionMatch: false      // Remove (vs hide) elements on no match
};
```

### Read-Only Properties

```javascript
lqx.geolocate.location   // { lat, lon, city, region, country, ... , source }
lqx.geolocate.regions    // ['nyc', 'east-coast'] — matched region names
lqx.geolocate.status     // { ip: 'ready'|'wait'|'cookie', gps: 'ready'|'wait'|'n/a'|'cookie' }
```

### Functions

#### `ready(callback)`

Run a callback when geolocation data is available.

```javascript
lqx.geolocate.ready(function() {
    console.log('Location:', lqx.geolocate.location);
    console.log('City:', lqx.geolocate.location.city);
});
```

#### `setRegions(regions)`

Define geographic regions and check which ones the user is in.

```javascript
lqx.geolocate.ready(function() {
    const matched = lqx.geolocate.setRegions({
        'nyc': {
            polygons: [
                [
                    { lat: 40.477, lon: -74.259 },
                    { lat: 40.477, lon: -73.700 },
                    { lat: 40.917, lon: -73.700 },
                    { lat: 40.917, lon: -74.259 }
                ]
            ]
        },
        'philly': {
            circles: [
                { lat: 39.952, lon: -75.164, radius: 30 }  // 30km radius
            ]
        }
    });
    console.log('User is in regions:', matched);
});
```

**Region definition supports:**
- `circles` — array of `{ lat, lon, radius }` (radius in km)
- `squares` — array of `{ corner1: {lat, lon}, corner2: {lat, lon} }`
- `polygons` — array of point arrays `[{lat, lon}, ...]`

#### `geoJSONtoRegions(geoJSON)`

Convert a GeoJSON string to the regions format used by `setRegions`.

```javascript
const regions = lqx.geolocate.geoJSONtoRegions(geoJSONString);
lqx.geolocate.setRegions(regions);
```

#### `regionDisplay(elements?)`

Show/hide elements based on the user's detected region. Called automatically, but can be called manually for dynamically added elements.

### Geometry Helpers

#### `inCircle(test, center, radius)`

Check if a point is within a circle (Haversine formula).

#### `inSquare(test, corner1, corner2)`

Check if a point is within a rectangle.

#### `inPolygon(test, poly)`

Check if a point is inside a polygon (ray casting algorithm).

### Region-Based Element Visibility

Elements can be shown/hidden based on the user's region using data attributes or CSS classes:

```html
<!-- Show only for NYC users -->
<div data-region-display='{"regions": ["nyc"], "action": "show"}'>
    NYC-specific content
</div>

<!-- Hide for Philly users -->
<div data-region-display='{"regions": ["philly"], "action": "hide"}'>
    Content hidden from Philly
</div>

<!-- Using CSS classes instead -->
<div class="region-name-nyc region-action-show region-display-flex">
    Shown as flex for NYC users
</div>
```

### Events

| Event | Target | When |
|-------|--------|------|
| `geolocateready` | `document` | Geolocation data is available |

### Body Attributes

After geolocation, attributes are set on `<body>`:

```html
<body city="New York" region="NY" country="US" lat="40.71" lon="-74.00" regions="nyc,east-coast">
```

### Geolocation Flow

1. Check cookies for cached location data
2. If no cookie: call `{siteURL}/wp-json/lyquix/v3/ip2geo` for IP-based location
3. If GPS enabled: request browser geolocation (GPS overrides IP data)
4. Set body attributes and trigger `geolocateready` event
5. Cache results in cookies for configured duration

---

## gallery

**File:** `js/lib/lyquix/gallery.ts`

Image and video gallery with LyqBox lightbox integration. Groups gallery items so they can be navigated as a set in the lightbox.

### Configuration

```typescript
cfg.gallery = {
    enabled: true,
    galleryBlockSelector: '.lqx-block-gallery > .gallery'
};
```

### Behavior

1. Finds all gallery blocks on page load
2. For each gallery, finds image and video items
3. Sets up click handlers that open items in LyqBox
4. Items within a gallery are grouped for prev/next navigation in the lightbox
5. Registers a mutation handler for dynamically added galleries

---

## lyqbox

**File:** `js/lib/lyquix/lyqbox.ts`

Custom lightbox implementation supporting images, videos (YouTube, Vimeo, HTML5), iframes, and inline HTML content. Features navigation between grouped items, keyboard controls, and swipe gestures.

### Configuration

```typescript
cfg.lyqbox = {
    enabled: true,
    lyqboxSelector: 'a[data-lyqbox]'   // Elements that trigger the lightbox
};
```

### Data Attributes (on trigger elements)

| Attribute | Description |
|-----------|-------------|
| `data-lyqbox` | Group name — items with the same value form a navigable set |
| `data-lyqbox-type` | Content type: `'image'`, `'video'`, `'iframe'`, `'html'` |
| `data-lyqbox-url` | URL to load (for image, video, iframe) |
| `data-lyqbox-html` | HTML content (for inline type) |
| `data-lyqbox-title` | Caption text |
| `data-lyqbox-width` | Custom width |
| `data-lyqbox-height` | Custom height |

### Usage

```html
<!-- Single image -->
<a href="large.jpg" data-lyqbox="gallery1" data-lyqbox-type="image">
    <img src="thumb.jpg" alt="Photo">
</a>

<!-- YouTube video -->
<a href="#" data-lyqbox="gallery1" data-lyqbox-type="video"
   data-lyqbox-url="https://www.youtube.com/watch?v=dQw4w9WgXcQ">
    Watch Video
</a>

<!-- Inline HTML -->
<a href="#" data-lyqbox="info" data-lyqbox-type="html"
   data-lyqbox-html="<h2>Hello</h2><p>Inline content</p>">
    Show Info
</a>
```

### Keyboard Controls

| Key | Action |
|-----|--------|
| Escape | Close lightbox |
| Left Arrow | Previous item |
| Right Arrow | Next item |

### Swipe Support

Uses the `swipe` module for touch navigation — swipe left/right to navigate between items.

---

## map

**File:** `js/lib/lyquix/map.ts`

Google Maps integration with markers, info windows, and custom styling.

### Configuration

```typescript
cfg.map = {
    enabled: true,
    mapBlockSelector: '.lqx-block-map > .map'
};
```

### Data Attributes

Map settings are read from data attributes on the map block element, including map center, zoom level, map styles, and marker data.

### Features

- Custom map styles via JSON
- Multiple markers with custom info windows
- Marker clustering (if the library is loaded)
- Click-to-open info windows
- Responsive — recalculates on screen size change

### Dependencies

Requires the Google Maps JavaScript API to be loaded with a valid API key.

---

## filters

**File:** `js/lib/lyquix/filters.ts`

AJAX-powered post filtering with URL state management. Provides a UI for filtering WordPress posts by taxonomy, search, date, and custom fields without page reload.

### Configuration

```typescript
cfg.filters = {
    enabled: true,
    filtersBlockSelector: '.lqx-block-filters > .filters'
};
```

### Features

- Filter by taxonomy terms, search query, date range, and custom fields
- AJAX loading of filtered results (no page reload)
- URL state management — filter state is reflected in the URL for bookmarking and sharing
- Pagination support
- Loading indicator during AJAX requests
- Registers mutation handler for dynamically added filter blocks

### Behavior

1. Reads filter configuration from data attributes and hidden fields
2. Listens for changes on filter form elements
3. On change, makes AJAX request to WordPress with filter parameters
4. Replaces the results area with the AJAX response
5. Updates the URL with the current filter state using `history.pushState()`
6. Handles browser back/forward navigation via `popstate` event

---

## testimonial

**File:** `js/lib/lyquix/testimonial.ts`

Testimonial carousel powered by Swiper. Simple slider specifically configured for testimonial content.

### Configuration

```typescript
cfg.testimonial = {
    enabled: true,
    testimonialBlockSelector: '.lqx-block-testimonial > .testimonial',
    analytics: {
        enabled: true,
        nonInteraction: true,
        onPrevNext: true
    }
};
```

### Data Attributes

| Attribute | Values | Default | Description |
|-----------|--------|---------|-------------|
| `data-autoplay` | `y`, `n` | `n` | Enable autoplay |
| `data-autoplay-delay` | number | `15` | Seconds between slides |
| `data-loop` | `y`, `n` | `n` | Infinite loop |
| `data-pagination` | `y`, `n` | `n` | Show pagination |
| `data-navigation` | `y`, `n` | `n` | Show prev/next buttons |
| `data-swiper-options-override` | JSON string | — | Custom Swiper options |

### Behavior

Follows the same pattern as the slider module — reads settings from data attributes, builds Swiper configuration, initializes Swiper, and tracks navigation analytics.

---

## video

**File:** `js/lib/lyquix/video.ts`

Video performance helpers using IntersectionObserver. Provides three behaviors: lazy loading, hover-to-play, and viewport-play.

### Configuration

```typescript
cfg.video = {
    enabled: true,
    lazyLoadSelector: 'video.lazyload-video',
    hoverPlaySelector: 'video.video-hover-play',
    viewportPlaySelector: 'video.video-viewport-play'
};
```

### Lazy Loading

Videos with `data-src` instead of `src` only load when they enter the viewport.

```html
<video class="lazyload-video" data-src="video.mp4" muted playsinline></video>
```

**Behavior:** IntersectionObserver watches the video. When visible, copies `data-src` to `src` and calls `.load()`. Unobserves after loading.

### Hover Play

Videos play on mouse enter and pause/reset on mouse leave.

```html
<video class="video-hover-play" src="preview.mp4" muted playsinline></video>
```

### Viewport Play

Videos automatically play when visible in the viewport and pause when scrolled out of view.

```html
<video class="video-viewport-play" src="background.mp4" muted playsinline loop></video>
```

**Behavior:** IntersectionObserver with `threshold: 0.5` — the video must be at least 50% visible to play.

### Mutation Support

All three behaviors register mutation handlers so dynamically added videos are automatically processed.

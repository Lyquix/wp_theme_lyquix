# UI Components Reference

Detailed API reference for the UI component modules of the Lyquix JavaScript library.

## Common Patterns

All UI component modules share these patterns:

- **Mutation handling** — register via `mutation.addHandler('addNode', ...)` so dynamically loaded blocks are auto-initialized
- **Analytics integration** — send GA4 events for user interactions (configurable per module)
- **Data attribute configuration** — block settings are read from `data-*` attributes on the block's root element
- **Swiper integration** — slider, cards, alerts, and testimonial modules use the Swiper library

---

## menu

**File:** `js/lib/lyquix/menu.ts`

Simple mobile menu toggle. Listens for click events on a menu button and toggles the open/closed state of the navigation.

### Configuration

```typescript
cfg.menu = {
    enabled: true,
    menuSelector: '.menu-toggle'    // Selector for the toggle button
};
```

### Behavior

- Toggles `aria-expanded` attribute (`'true'`/`'false'`) on the button
- Toggles a CSS class on the target navigation element
- Designed to work with CSS that shows/hides the menu based on these states

### HTML Structure

```html
<button class="menu-toggle" aria-expanded="false">Menu</button>
<nav class="main-nav">
    <!-- menu items -->
</nav>
```

---

## tabs

**File:** `js/lib/lyquix/tabs.ts`

Tabbed content interface that can automatically convert to accordion on smaller screens. Supports hash-based navigation and analytics.

### Configuration

```typescript
cfg.tabs = {
    enabled: true,
    tabsBlockSelector: '.lqx-block-tabs > .tabs',
    analytics: {
        enabled: true,
        nonInteraction: true
    }
};
```

### Data Attributes

| Attribute | Values | Default | Description |
|-----------|--------|---------|-------------|
| `data-convert-to-accordion` | screen names (e.g., `xs,sm`) | `''` | Convert to accordion at these breakpoints |

### Functions

#### `open(tabId)`

Open a tab by its panel element ID.

```javascript
lqx.tabs.open('tab-panel-2');
```

#### `close(tabId)`

Close a tab (relevant in accordion mode).

```javascript
lqx.tabs.close('tab-panel-2');
```

### ARIA Attributes

- Tab buttons: `aria-selected="true"/"false"`, `role="tab"`
- Tab panels: `aria-hidden="true"/"false"`, `role="tabpanel"`

### Events & Analytics

- Sends analytics event on tab open: Category `'Tabs'`, Action `'Open'`, Label: tab text
- Listens for `hashchange` to open tabs matching URL hash
- Listens for `screensizechange` to toggle accordion conversion

### HTML Structure

```html
<div class="lqx-block-tabs">
    <div class="tabs" data-convert-to-accordion="xs,sm">
        <ul class="tab-nav">
            <li class="tab-button" id="tab-1" role="tab" aria-selected="true">Tab 1</li>
            <li class="tab-button" id="tab-2" role="tab" aria-selected="false">Tab 2</li>
        </ul>
        <div class="tab-panel" id="panel-1" role="tabpanel" aria-hidden="false">Content 1</div>
        <div class="tab-panel" id="panel-2" role="tabpanel" aria-hidden="true">Content 2</div>
    </div>
</div>
```

---

## accordion

**File:** `js/lib/lyquix/accordion.ts`

Collapsible accordion panels with support for opening multiple panels simultaneously, auto-scroll to opened panels, URL hash navigation, and analytics.

### Configuration

```typescript
cfg.accordion = {
    enabled: true,
    accordionBlockSelector: '.lqx-block-accordion > .accordion',
    analytics: {
        enabled: true,
        nonInteraction: true,
        onClose: true
    }
};
```

### Data Attributes

| Attribute | Values | Default | Description |
|-----------|--------|---------|-------------|
| `data-open-multiple` | `y`, `n` | `n` | Allow multiple panels open at once |
| `data-auto-scroll` | `''` (empty) | — | If present, scroll to panel on open |

### Functions

#### `open(panelId)`

Open an accordion panel by its ID.

```javascript
lqx.accordion.open('accordion-panel-1');
```

#### `close(panelId)`

Close an accordion panel by its ID.

```javascript
lqx.accordion.close('accordion-panel-1');
```

### ARIA Attributes

- Headers: `aria-expanded="true"/"false"`
- Panels: `aria-hidden="true"/"false"`

### CSS Classes

- Panels have class `closed` when collapsed (removed when opened)

### Hash Navigation

The module listens for `hashchange`. If the URL hash matches an accordion item class (`.accordion-item{hash}`), that panel opens automatically. This allows direct linking to specific accordion panels.

### HTML Structure

```html
<div class="lqx-block-accordion">
    <div class="accordion" data-open-multiple="n">
        <div class="accordion-item-faq1">
            <h3 class="accordion-header" id="header-1" aria-expanded="false">Question?</h3>
            <div class="accordion-panel closed" id="panel-1" aria-hidden="true">Answer.</div>
        </div>
    </div>
</div>
```

---

## modal

**File:** `js/lib/lyquix/modal.ts`

Modal dialogs loaded via AJAX from the WordPress REST API. Uses the HTML5 `<dialog>` element. Supports expiration dates, display logic with URL pattern matching, auto-show/hide delays, and cookie-based dismissal.

### Configuration

```typescript
cfg.modal = {
    enabled: true,
    modalModuleSelector: '#lqx-module-modal > .modal',
    analytics: {
        enabled: true,
        nonInteraction: true,
        onOpen: true,
        onClose: true
    }
};
```

### Functions

#### `open(modalId)`

Open a modal by its ID.

```javascript
lqx.modal.open('modal-welcome');
```

#### `close(modalId)`

Close a modal by its ID. Sets a dismissal cookie to prevent re-display.

```javascript
lqx.modal.close('modal-welcome');
```

### AJAX Endpoint

`GET {siteURL}/wp-json/lyquix/v3/modal` — returns a JSON array of modal definitions.

### Modal Data Schema

| Field | Type | Description |
|-------|------|-------------|
| `id` | string (required) | Unique modal ID |
| `heading` | string | Modal heading |
| `body` | string | Modal body HTML |
| `expiration` | string | ISO date — modal hidden after this date |
| `display_logic` | `'show'` / `'hide'` | Default behavior (show or hide) |
| `display_exceptions` | array | URL patterns that toggle the default behavior |
| `show_delay` | string | Seconds before showing |
| `hide_delay` | string | Seconds before auto-closing |
| `dismiss_duration` | string | Cookie duration in minutes |
| `links` | array | Action links (`type: 'button'` or `'readmore'`) |

### Display Logic

- `display_logic='show'` + no exception match → modal shows
- `display_logic='show'` + exception matches current URL → modal hidden
- `display_logic='hide'` + exception matches current URL → modal shows
- URL patterns support wildcards (`*`)

### Cookie Behavior

On close, a cookie is set with the modal's ID. If the cookie exists on page load, the modal is skipped. Duration is controlled by `dismiss_duration` (minutes), defaulting to 1 year.

---

## alerts

**File:** `js/lib/lyquix/alerts.ts`

Alert notification bar loaded via AJAX, displayed as a Swiper slider. Supports autoplay, navigation, expiration, and dismissal.

### Configuration

```typescript
cfg.alerts = {
    enabled: true,
    alertsModuleSelector: '#lqx-module-alerts > .alerts',
    swiperSelector: '.swiper',
    analytics: {
        enabled: true,
        nonInteraction: true,
        onClose: true,
        onPrevNext: true
    }
};
```

### AJAX Endpoint

`GET {siteURL}/wp-json/lyquix/v3/alerts` — returns a JSON array of alert definitions.

### Data Attributes (on module element)

| Attribute | Values | Default | Description |
|-----------|--------|---------|-------------|
| `data-autoplay` | `y`, `n` | `n` | Auto-rotate alerts |
| `data-autoplay-delay` | `0`–`60` | `15` | Seconds between rotations |
| `data-heading-style` | `p`, `h1`–`h6` | `h3` | Heading element tag |

### Behavior

1. Fetches alerts from REST API on page load
2. Filters out expired alerts and previously dismissed alerts (via cookies)
3. Builds Swiper slides for remaining alerts
4. Shows the alert bar (removes `hidden` class)
5. If only 1 alert, removes navigation controls
6. On close, sets dismissal cookies for all visible alerts

---

## slider

**File:** `js/lib/lyquix/slider.ts`

Image/content carousel powered by Swiper. Supports autoplay, loop, pagination (with optional thumbnails or teaser text), and navigation arrows.

### Configuration

```typescript
cfg.slider = {
    enabled: true,
    sliderBlockSelector: '.lqx-block-slider > .slider',
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
| `data-pagination` | `y`, `n` | `n` | Show pagination bullets |
| `data-navigation` | `y`, `n` | `n` | Show prev/next buttons |
| `data-swiper-options-override` | JSON string | — | Custom Swiper options (merged) |

### Slide-Level Attributes

| Attribute | Description |
|-----------|-------------|
| `data-slide-teaser` | Text shown in pagination bullet |
| `data-slide-thumbnail` | Image URL shown in pagination bullet |

### Swiper Override

Pass a JSON string to override or extend Swiper options:

```html
<div class="slider" data-swiper-options-override='{"speed": 500, "effect": "fade"}'>
```

---

## cards

**File:** `js/lib/lyquix/cards.ts`

Responsive card grid using Swiper. Columns per breakpoint are controlled by responsive rules defined in data attributes.

### Configuration

```typescript
cfg.cards = {
    enabled: true,
    cardsBlockSelector: '.lqx-block-cards > .cards',
    analytics: {
        enabled: true,
        nonInteraction: true,
        onPrevNext: true
    }
};
```

### Data Attributes

| Attribute | Description |
|-----------|-------------|
| `data-responsive-rules` | JSON array of `{ screens: [...], columns: N }` rules |
| `data-swiper-options-override` | JSON string for custom Swiper options |

### Responsive Rules

```html
<div class="cards" data-responsive-rules='[
    {"screens": ["xs", "sm"], "columns": 1},
    {"screens": ["md"], "columns": 2},
    {"screens": ["lg", "xl"], "columns": 3}
]'>
```

The module maps these rules to Swiper's `breakpoints` configuration, using the breakpoint pixel values from `cfg.responsive.breakPoints`.

---

## popup

**File:** `js/lib/lyquix/popup.ts`

Popup notifications loaded via AJAX. Similar to modal but uses CSS class-based visibility (`closed` class) instead of the `<dialog>` element.

### Configuration

```typescript
cfg.popup = {
    enabled: true,
    popupModuleSelector: '#lqx-module-popup > .popup',
    analytics: {
        enabled: true,
        nonInteraction: true,
        onOpen: true,
        onClose: true
    }
};
```

### Functions

#### `open(popupId)`

Open a popup by removing the `closed` class.

#### `close(popupId)`

Close a popup by adding the `closed` class and setting a dismissal cookie.

### AJAX Endpoint

`GET {siteURL}/wp-json/lyquix/v3/popup`

### Key Differences from Modal

| | Modal | Popup |
|--|-------|-------|
| HTML element | `<dialog>` | `<section>` |
| Visibility | `showModal()` / `close()` | CSS class `closed` |
| ARIA | Full (`aria-modal`, etc.) | Basic |
| Backdrop | Native dialog backdrop | Custom CSS |

The data schema, display logic, cookie handling, and analytics are identical to the modal module.

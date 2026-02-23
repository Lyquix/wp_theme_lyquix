# Regionalization

The regionalization system controls which content is shown to users based on their geographic location. It provides server-side region detection, per-block region filtering, per-post region forcing, and client-side show/hide of HTML elements.

## Concepts

**Region** — A named geographic area defined by a GeoJSON polygon (or multi-polygon). Each region has:
- **Name** — human-readable label
- **Mobile Label** — shorter label for mobile navigation
- **Alias** — URL-safe slug used as the region identifier in code and cookies (e.g. `northeast`, `us-west`)
- **Address / Phone Number** — optional contact data associated with the region
- **Description** — optional descriptive text
- **GeoJSON** — a Polygon or MultiPolygon defining the geographic boundaries (supports holes)

## Configuration

Regions are configured under **Site Settings > Regions** in the WordPress admin (ACF options page).

### Options: Regions fields

| Field | Description |
|-------|-------------|
| **Regions** (repeater) | List of region definitions. Each row: Name, Mobile Label, Alias, Address, Phone Number, Description, GeoJSON |
| **Forced Region Post Types** | Post types where being on a post of that type forces the user's region to the post's associated region |
| **No User Region Meaning** | What to return when a user's region cannot be detected: `Outside Region` (default) or `Everywhere` |
| **No Content Region Meaning** | What to show when content has no regions assigned: `Everywhere` (default) or `None` |

## Region Detection Priority

The `\lqx\regions\get_region()` function resolves the current user's region by checking sources in this order, returning the first match:

1. **Post type** — If the current post type is listed in *Forced Region Post Types*, the post's `related_regions` field value is returned
2. **Post field** — If the current post has a `forced_region` field set, that value is returned
3. **Cookie** — If the user has a `selectedRegion` cookie (set by a region-selector UI), that value is returned
4. **IP geolocation** — The user's IP is resolved to lat/lon via the MaxMind GeoLite2 integration (`\lqx\ip2geo`), then checked against each region's GeoJSON polygon using a ray-casting algorithm
5. **Default** — Falls back to the *No User Region Meaning* setting (`outside-region` or `everywhere`)

## ACF Fields on Content

### On all blocks — "Regional Block Fields" group

Every Gutenberg block (all `lqx/*` blocks) gets a **Regions** tab in its ACF field group with a `regions` select field. Setting one or more regions on a block limits its visibility to users in those regions.

### On all post types — "Related Regions" group

Every post type gets a **Related Regions** select field. This is used when the post type is listed in *Forced Region Post Types* — being on a post of that type forces the user into the first selected region.

### Per-post forced region — `forced_region`

Individual posts can override region detection entirely via a `forced_region` ACF field, regardless of IP or cookie.

## PHP API

All functions live in the `lqx\regions` namespace (`php/regions.php`).

```php
// Get the current user's resolved region alias
$region = \lqx\regions\get_region();

// Override the "no user region" fallback for a specific call
$region = \lqx\regions\get_region('everywhere');

// Check if a user's region matches a set of content regions
$visible = \lqx\regions\is_region_match($content_regions);

// Provide user region explicitly, override the "no content region" fallback
$visible = \lqx\regions\is_region_match($content_regions, $user_region, 'none');
```

### `get_region(?string $no_user_region_meaning = null): string`

Returns the current user's region alias. Checks post type → post field → cookie → IP geolocation in order.

| Parameter | Description |
|-----------|-------------|
| `$no_user_region_meaning` | Optional override for the "no region detected" fallback. Accepts `'outside-region'` or `'everywhere'`. If `null`, reads the *No User Region Meaning* option. |

### `is_region_match(?array $content_regions, ?string $user_region = null, ?string $no_content_region_meaning = null): bool`

Returns `true` if the user's region matches the content's assigned regions.

| Parameter | Description |
|-----------|-------------|
| `$content_regions` | Array of region aliases assigned to the content. `null` or empty array triggers the "no content region" fallback. |
| `$user_region` | The user's region alias. If `null`, calls `get_region()` automatically. |
| `$no_content_region_meaning` | Fallback when content has no regions. Accepts `'everywhere'` or `'none'`. If `null`, reads the *No Content Region Meaning* option. |

## JavaScript API — Client-Side Region Display

The `geolocate` module in the Lyquix TypeScript library (`js/lib/lyquix/geolocate.ts`) provides client-side geolocation and region-based show/hide of HTML elements.

### How It Works

1. On page load, the module resolves the user's location via IP geolocation (AJAX call to `/wp-json/lyquix/v3/ip2geo`) and optionally via browser GPS.
2. The user's coordinates are matched against each configured region's GeoJSON polygon using a ray-casting algorithm.
3. Matching region aliases are set as a comma-separated `regions` attribute on the `<body>` element.
4. The `regionDisplay()` function finds all elements matching the selector `[data-region-display], [class*="region-name-"]` and shows or hides them based on the user's matched regions.
5. A `MutationObserver` watches for dynamically added elements and applies the same logic automatically.
6. A `geolocateready` event is fired on `document` when geolocation and region matching are complete.

### Configuration

Region data (names, aliases, GeoJSON polygons) is passed from PHP to JavaScript via `lqx.init()` options. The following settings control behavior:

| Option | Default | Description |
|--------|---------|-------------|
| `geolocate.enabled` | `true` | Enable/disable the geolocate module |
| `geolocate.gps` | `false` | Also use browser GPS geolocation |
| `geolocate.useCookies` | `true` | Cache geolocation results in cookies |
| `geolocate.cookieExpirationIP` | `900` | IP geolocation cookie TTL in seconds (15 min) |
| `geolocate.cookieExpirationGPS` | `900` | GPS geolocation cookie TTL in seconds (15 min) |
| `geolocate.handleNoRegionMatch` | `true` | Show/hide elements that don't match the user's region |
| `geolocate.removeNoRegionMatch` | `true` | Remove (rather than hide) non-matching elements from the DOM |

### Tagging Elements for Region Display

There are two ways to mark an HTML element for region-based visibility:

#### Data Attribute Method

Add a `data-region-display` attribute with a JSON object:

```html
<div data-region-display='{"regions": ["northeast", "southeast"], "action": "show", "display": "flex"}'>
  This content is only visible to users in the northeast or southeast regions.
</div>
```

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `regions` | string or array | (required) | Region alias(es) to match against |
| `action` | `"show"` or `"hide"` | `"show"` | Whether to show or hide the element when the region matches |
| `display` | string | `"block"` | CSS `display` value to use when showing the element |

#### CSS Class Method

Add CSS classes directly to the element:

```html
<div class="region-name-northeast region-name-southeast region-action-show region-display-flex">
  This content is only visible to users in the northeast or southeast regions.
</div>
```

| Class pattern | Description |
|---------------|-------------|
| `region-name-{alias}` | Specifies which region(s) the element targets (add multiple for multiple regions) |
| `region-action-show` / `region-action-hide` | Whether to show or hide the element on match (default: `show`) |
| `region-display-{value}` | CSS display value when showing (e.g. `region-display-flex`) |

### Visibility Logic

| Region matches? | Action | `removeNoRegionMatch` | Result |
|-----------------|--------|-----------------------|--------|
| Yes | `show` | — | Element is shown with the specified display value |
| Yes | `hide` | `true` | Element is removed from the DOM |
| Yes | `hide` | `false` | Element is hidden (`display: none`) |
| No | `show` | `true` | Element is removed from the DOM |
| No | `show` | `false` | Element is hidden (`display: none`) |
| No | `hide` | — | Element is shown with the specified display value |

If `handleNoRegionMatch` is `false`, non-matching elements are left untouched.

### Implementation

| File | Purpose |
|------|---------|
| `js/lib/lyquix/geolocate.ts` | Client-side geolocation, region matching, and element show/hide logic |
| `php/regions.php` | Server-side region detection and PHP API |
| `php/js.php` (`render_lyquix_options()`) | Serializes region data from ACF options into `lqx.init()` |

## GeoJSON Support

The polygon matching engine (`point_in_geojson()`) supports the following GeoJSON types:

| Type | Behavior |
|------|---------|
| `FeatureCollection` | Returns `true` if the point is inside any feature |
| `Feature` | Delegates to the feature's geometry |
| `Polygon` | Ray-casting algorithm with hole support |
| `MultiPolygon` | Returns `true` if the point is inside any polygon |

> **Known limitation:** The ray-casting algorithm does not handle polygons crossing the international date line or geographic poles.

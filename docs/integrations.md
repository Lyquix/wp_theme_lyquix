# Integrations

The Lyquix theme integrates with several third-party services and APIs for analytics, geolocation, maps, and browser detection.

## Google Analytics 4

### Setup

1. Go to Theme Customizer > **Analytics**
2. Enter your GA4 Measurement ID (e.g., `G-XXXXXXXXXX`)

### How It Works

The theme renders the GA4 tracking code through the Lyquix JavaScript library. Configuration is passed via `lqx.init()`:

```javascript
{
    analytics: {
        measurementId: 'G-XXXXXXXXXX',
        sendPageview: true,   // Send automatic pageviews
        usingGTM: false       // Set true if GA is loaded via GTM
    }
}
```

### Event Tracking

The `analytics.ts` module provides automatic event tracking for fine-grained user interactions. Events are sent via `gtag()` (direct GA4) or `dataLayer.push()` (GTM).

### Settings

| Setting | Purpose |
|---------|---------|
| GA4 Account (Measurement ID) | Your GA4 tracking ID |
| Send Google Analytics Pageview | Toggle automatic pageview tracking |
| Google Analytics loaded via GTM | Set to Yes if GA is already loaded by GTM |

## Google Tag Manager

### Setup

1. Go to Theme Customizer > **Analytics**
2. Enter your GTM Container ID (e.g., `GTM-XXXXXXX`)

### How It Works

The theme injects GTM code in two locations:
- **Head**: The GTM JavaScript snippet (in `<head>` before `wp_head()`)
- **Body**: The GTM noscript fallback (immediately after `<body>`)

Both are rendered by `\lqx\js\render_gtm_head_code()` and `\lqx\js\render_gtm_body_code()` in `custom.php`.

## Microsoft Clarity

### Setup

1. Go to Theme Customizer > **Analytics**
2. Enter your Clarity Project ID

### What It Provides

Microsoft Clarity provides:
- Session recording (replay user sessions)
- Heatmaps (click, scroll, and attention maps)
- Insights (dead clicks, rage clicks, excessive scrolling)

## MaxMind GeoLite2 (IP Geolocation)

### Setup

1. Create a free account at [MaxMind](https://www.maxmind.com/en/geolite2/signup)
2. Generate a license key
3. Go to Theme Customizer > **IP Geolocation**
4. Enter the license key

### How It Works

The theme includes a built-in MaxMind GeoLite2 reader (`php/ip2geo/`). When configured:

1. The GeoLite2 City database is downloaded automatically
2. The database is refreshed when older than the configured maximum age (default: 90 days)
3. A REST API endpoint is available for client-side geolocation lookups

### REST API

The geolocation data is exposed via a REST API endpoint for use in JavaScript.

### Settings

| Setting | Purpose |
|---------|---------|
| MaxMind GeoLite2 License Key | Required for database downloads |
| Maximum Database Age (days) | How often to refresh the database (default: 90) |
| IP Address HTTP Header | Which header contains the client IP. Use `REMOTE_ADDR` for direct connections, or proxy-specific headers like `HTTP_CF_CONNECTING_IP` (Cloudflare), `HTTP_FASTLY_CLIENT_IP` (Fastly), `HTTP_X_FORWARDED_FOR` (generic proxy) |
| Test IP Address | Override the detected IP for testing |

### Available Data

The geolocation lookup provides:
- Country (code and name)
- Region/state
- City
- Approximate coordinates

## Google Maps

### Setup

The Map block requires a Google Maps API key. This is configured through the block's ACF fields.

### Features

The Map block supports:
- Multiple markers with custom pin colors
- Info windows with:
  - Heading and subheading
  - Images
  - Description text
  - Phone numbers
  - Business hours
  - Labels
  - Links
- Map styling and configuration

### JavaScript

The `map.ts` module handles Google Maps initialization, marker placement, and info window interactions.

## Browser Detection

### How It Works

The theme detects outdated browsers and displays an alert modal. The system:

1. Uses `ua-parser` to identify the browser and version
2. Compares against the acceptable version threshold (configurable)
3. Displays a dismissible alert if the browser is outdated

### Settings

| Setting | Purpose |
|---------|---------|
| Enable Browser Alert | Turn the outdated browser alert on/off |
| Acceptable Browser Versions | How many versions back to accept (1-5) |

The alert is rendered by `\lqx\browsers\render()` at the bottom of `custom.php`.

## MobileDetect

The MobileDetect library is always loaded from CDN and provides device detection capabilities:

- Mobile vs. tablet vs. desktop detection
- OS detection (iOS, Android, Windows, etc.)
- Browser detection

Used internally by the `detect.ts` module, which exposes detection results through `lqx.detect`.

## Swiper

The Swiper library (v11) powers carousel/slider functionality:

- Used by: Slider, Testimonial, and Logos blocks
- CSS and JS loaded from CDN
- Toggle: Theme Customizer > JS > Swiper library

## Day.js

Lightweight date manipulation library (replaces Moment.js):

- Loaded from CDN with English locale
- Toggle: Theme Customizer > JS > Day.js library
- Available globally as `dayjs()`

## Vue.js 3

Optional frontend framework for building reactive components:

- Loaded from CDN only if `js/vue.js` exists in the child theme
- Development version served on `.test` domains
- Production version served in other environments

## Search Verification Tags

The theme supports verification meta tags for:

| Service | Setting |
|---------|---------|
| Google Search Console | `google-site-verification` |
| Bing Webmaster Tools | `msvalidate.01` |
| Pinterest | `p:domain_verify` |

Configure in Theme Customizer > **Meta Tags**.

## Preconnect URLs

The theme outputs `<link rel="preconnect">` tags for configured URLs to improve loading performance for third-party resources. Default URLs include Google services and jsDelivr CDN.

Configure in Theme Customizer > **Meta Tags > rel=preconnect URLs**.

# Theme Customizer

The Lyquix theme adds 60+ settings to the WordPress Theme Customizer, organized into 11 sections. Access them via **Appearance > Customize** in the WordPress admin.

## Branding Settings

| Setting | Type | Default | Description |
|---------|------|---------|-------------|
| Login Logo (wp-login.php) | Media Image | (none) | Select an image from the Media Library to replace the default WordPress logo on the login screen. The logo links to the site home URL and uses the site name as the title text. |

## Admin Bar Settings

| Setting | Type | Default | Description |
|---------|------|---------|-------------|
| Hide Admin Bar on Frontend for Roles | Checkbox Group | (none) | Select user roles that should not see the admin bar on the frontend. Users with any selected role will have the bar hidden. Does not affect the admin dashboard. |
| Enable Admin Bar Expand/Collapse (Notch) | Radio | No | Adds a small "notch" button that lets logged-in users collapse and expand the admin bar on the frontend. When collapsed, the admin bar slides up out of view and only the notch remains visible. |
| Notch Position | Radio | Center | Where to place the notch button: Left corner, Center, or Right corner. Only applies when the expand/collapse notch is enabled. |

## CSS Settings

| Setting | Type | Default | Description |
|---------|------|---------|-------------|
| Use Original CSS (non-minified) | Radio | No | Serve `styles.css` instead of `styles.min.css`. Auto-enabled on `.test` domains. |
| Additional CSS Libraries | Textarea | (empty) | URLs of CSS files to load (one per line). Supports absolute URLs and relative paths. |
| Remove CSS Libraries | Textarea | (empty) | URLs of registered CSS files to dequeue (one per line). |
| Load Critical Path CSS | Radio | Yes | Inline critical CSS and async-load the full stylesheet. |
| Viewports for Critical Path CSS | Viewports | xs:320x720, sm:480x1080, md:720x1080, lg:1080x1080, xl:1620x1080 | Viewport dimensions used for critical CSS generation. |
| Exclude Post Types from Critical Path CSS | Checkbox Group | (none) | Post types to skip when loading critical CSS. |
| Exclude Pages from Critical Path CSS | Checkbox Group | (none) | Specific pages to skip when loading critical CSS. |

## JS Settings

| Setting | Type | Default | Description |
|---------|------|---------|-------------|
| Enable jQuery | Radio | Yes | Load jQuery. |
| Enable jQuery Migrate | Radio | No | Load jQuery Migrate for backwards compatibility. |
| Enable jQuery UI | Radio | No | Off / Core / Core + Sortable. |
| Enable lqx debug | Radio | None | Debug output level: None, Errors, Errors+Warnings, All. |
| Use Original JS (non-minified) | Radio | No | Serve `.js` instead of `.min.js`. Auto-enabled on `.test` domains. |
| Lyquix Library Options | Textarea | (empty) | JSON object merged into `lqx.init()` options. |
| Scripts Options | Textarea | (empty) | JSON object merged into `$lqx.init()` options. |
| Day.js library | Radio | Yes | Load Day.js date library from CDN. |
| Swiper library | Radio | Yes | Load Swiper carousel library from CDN. |
| Additional JS Libraries | Textarea | (empty) | URLs of JS files to load (one per line). |
| Remove JS Libraries | Textarea | (empty) | Handles of registered scripts to dequeue (one per line). |

## PHP Settings

| Setting | Type | Default | Description |
|---------|------|---------|-------------|
| Suppress PHP Warnings and Notices | Radio | No | Suppress non-critical PHP error output on the frontend. |

## IP Geolocation Settings

| Setting | Type | Default | Description |
|---------|------|---------|-------------|
| MaxMind GeoLite2 License Key | Text | (empty) | License key for downloading the GeoLite2 database. |
| Maximum Database Age (days) | Text | 90 | Re-download the database after this many days. |
| IP Address HTTP Header | Radio | REMOTE_ADDR | Which HTTP header to use for the client IP. Options include headers for Cloudflare, Fastly, and other proxies/CDNs. |
| Test IP Address | Text | (empty) | Override the detected IP for testing geolocation. |

## Analytics Settings

| Setting | Type | Default | Description |
|---------|------|---------|-------------|
| Google Analytics 4 Account | Text | (empty) | GA4 Measurement ID (e.g., G-XXXXXXXX). |
| Send Google Analytics Pageview | Radio | Yes | Whether to send automatic pageview events. |
| Google Analytics loaded via GTM | Radio | No | Set to Yes if GA is loaded through Google Tag Manager. |
| Google Tag Manager Account | Text | (empty) | GTM Container ID (e.g., GTM-XXXXXXX). |
| Microsoft Clarity Project ID | Text | (empty) | Clarity project ID for session recording and heatmaps. |

## Meta Tags Settings

| Setting | Type | Default | Description |
|---------|------|---------|-------------|
| google-site-verification | Text | (empty) | Google Search Console verification meta tag value. |
| msvalidate.01 | Text | (empty) | Bing Webmaster Tools verification meta tag value. |
| p:domain_verify | Text | (empty) | Pinterest domain verification meta tag value. |
| rel=preconnect URLs | Textarea | (see below) | URLs for `<link rel="preconnect">` tags (one per line). |
| Additional Meta Tags | Textarea | (empty) | Raw HTML meta tags to add to `<head>`. Not sanitized. |

**Default preconnect URLs:**
- `https://cdn.jsdelivr.net`
- `https://www.google.com`
- `https://www.google-analytics.com`
- `https://www.googletagmanager.com`
- `https://www.gstatic.com`
- `https://fonts.gstatic.com`
- `https://fonts.googleapis.com`

## Browser Alert Settings

| Setting | Type | Default | Description |
|---------|------|---------|-------------|
| Enable Browser Alert | Radio | Yes | Show a warning to users with outdated browsers. |
| Acceptable Browser Versions | Radio | Last 3 | How many versions back to accept: Only Latest, Last 2-5. |

## Feature Flags

This section is populated from `php/custom/features.php` in the child theme. If no feature flags are defined, this section is hidden.

Feature flags appear as Yes/No toggles and their values are output in the `<body>` tag's `data-features` attribute, making them accessible to both CSS and JavaScript.

### Defining Feature Flags

Create `php/custom/features.php`:

```php
<?php
$feature_flags = [
    'dark-mode' => 'Dark Mode',
    'animations' => 'Animations',
    'beta-features' => 'Beta Features',
];
```

Each flag becomes a customizer toggle under **Feature Flags** with the setting name `feature-{code}`.

## QR Code Generator

| Setting | Type | Default | Description |
|---------|------|---------|-------------|
| Enable QR Code Generator | Radio | Yes | Show the QR Code sidebar panel and Classic Editor meta box on all public post types. |

## URL Shortener

| Setting | Type | Default | Description |
|---------|------|---------|-------------|
| Enable URL Shortener | Radio | No | Enable automatic short URL creation for published posts. |
| URL Shortener Provider | Radio | TinyURL | Select the provider: TinyURL or YOURLS. Only visible when the URL Shortener is enabled. |
| TinyURL API Token | Text | (empty) | Bearer token for the TinyURL API. Only visible when TinyURL is the selected provider. |
| YOURLS Base URL | Text | (empty) | Base URL of your YOURLS installation. Only visible when YOURLS is the selected provider. |
| YOURLS Signature Token | Text | (empty) | YOURLS API signature token. Only visible when YOURLS is the selected provider. |

## Theme Features

These toggles control various theme behaviors:

| Setting | Default | Description |
|---------|---------|-------------|
| Disable Comments | Yes | Remove comment support from all post types. |
| Enable Content Blocks | Yes | Register Lyquix Gutenberg content blocks. |
| Enable Layout Blocks | Yes | Register Lyquix Gutenberg layout blocks. |
| Enable Modules | Yes | Load and register theme modules. |
| Hide Alerts Module for Non Admins | No | Hide Alerts admin page from non-admin users. |
| Hide CTAs Module for Non Admins | No | Hide CTAs admin page from non-admin users. |
| Hide Modals Module for Non Admins | No | Hide Modals admin page from non-admin users. |
| Hide Popups Module for Non Admins | No | Hide Popups admin page from non-admin users. |
| Enable Tailwind | Yes | Process CSS through Tailwind. |
| Enable Theme Update | Yes | Check for parent theme updates. |
| Enable LiveReload | Yes | Inject LiveReload script for development. |
| Hide PHP Version Alert | Yes | Hide the PHP upgrade nag on the dashboard. |
| Hide Yoast Metabox | Yes | Hide the Yoast SEO meta box in the editor. |
| Allow SVG Upload | Yes | Allow SVG files in the media library. |
| Hide WP Generator Tag | Yes | Remove the WordPress version meta tag. |
| Hide Weak Password Confirmation | Yes | Remove the "confirm weak password" checkbox. |
| Enable Image Sizes | Yes | Use custom image sizes (thumbnail:150, xsmall: 320, small:640, medium:1280, large:2560, xlarge:3840). |
| Enable Required Plugins Alert | Yes | Show admin alerts for missing required plugins. |
| Enable User Management for Editor Role | Yes | Give editors the ability to manage users. |
| Enable Manager Role | No | Adds a custom Manager role that has the same permissions as Editors but with user management capabilities |
| Hide ACF Extension Menu Items | Yes | Hide less-used ACF Extended admin menu items. |
| Hide Activity Log for Non Admins | Yes | Hide the Activity Log menu from non-admin users. |
| Move Excerpt to after Content | Yes | Move the excerpt field to a more prominent position in the editor. |

## Custom Controls

The customizer includes three control types beyond the standard WordPress controls:

### Checkbox Group
A multi-select control that stores selected values as a JSON array. Used for "Exclude Post Types", "Exclude Pages", and "Hide Admin Bar for Roles" settings.

### Viewports
A control for configuring width and height pairs for each breakpoint. Used for critical path CSS viewport dimensions. Stores values as a JSON object.

### Media Image
Uses the built-in `WP_Customize_Media_Control` to provide a Media Library picker filtered to images. Stores the attachment ID (integer). Used for the login logo setting.

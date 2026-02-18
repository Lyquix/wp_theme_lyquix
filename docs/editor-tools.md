# Editor Tools

The Lyquix theme includes several tools that enhance the WordPress editor experience for content editors and developers.

## QR Code Generator

Generates a QR code for the current post or page URL directly inside the editor. Available as a **Gutenberg sidebar panel** ("QR Code") and a **Classic Editor meta box** on all public post types.

### Enabling

Controlled by the Customizer toggle **Theme Features > QR Code Generator** (`feat_qr_generator`, default: enabled).

### Base QR Code

Once a post has been saved (i.e. has a permalink), the QR code is automatically generated and displayed. It shows the plain permalink with no tracking parameters.

- **Download PNG** — exports a 1024×1024 px rasterized image (`qr-code.png`)
- **Download SVG** — exports a scalable vector file (`qr-code.svg`)

### UTM Campaign QR Code

Below the base QR code, a form lets editors generate a second QR code with UTM tracking parameters appended to the URL.

**Required fields** (marked `*`) — the QR code only appears when all three are filled:

| Field | UTM parameter |
|-------|--------------|
| Campaign Name | `utm_campaign` |
| Campaign Source | `utm_source` |
| Campaign Medium | `utm_medium` |

**Optional fields** (collapsed by default behind an expandable toggle):

| Field | UTM parameter |
|-------|--------------|
| Campaign ID | `utm_id` |
| Campaign Term | `utm_term` |
| Campaign Content | `utm_content` |

The QR code and resulting URL update **live** as you type — no submit button needed. The resulting URL is displayed in a read-only, monospace input field with a **Copy** button. Downloads are named `qr-code-utm.png` / `qr-code-utm.svg`.

### Implementation

| File | Purpose |
|------|---------|
| `php/qr-code.php` | Feature flag check, script enqueueing, meta box registration |
| `js/qr-code-generator.js` | QR code library (MIT, by Kazuhiko Arase) |
| `js/qr-code-sidebar.js` | Gutenberg React panel |
| `js/qr-code-metabox.js` | Classic Editor jQuery meta box |

The Gutenberg sidebar script is loaded as an ES module (`type="module"`). The QR library is enqueued as a shared dependency so it is not duplicated when both the QR code and URL shortener panels are active.

---

## URL Shortener

Automatically creates and manages a short URL for every published post/page. Available as a **Gutenberg sidebar panel** ("Short URL") and a **Classic Editor meta box** on all public post types.

### Enabling

Controlled by the Customizer toggle **Theme Features > URL Shortener** (`feat_url_shortener`, default: disabled). After enabling, select a provider and enter the required credentials in the Customizer.

### Providers

#### YOURLS (default)

A self-hosted URL shortener. Required settings:

| Customizer field | Description |
|-----------------|-------------|
| `url_shortener_yourls_url` | Base URL of your YOURLS installation |
| `url_shortener_yourls_api_key` | YOURLS signature token |

API calls use the `shorturl` action to create and the `update` action to update.

#### TinyURL

A hosted service. Required settings:

| Customizer field | Description |
|-----------------|-------------|
| `url_shortener_tinyurl_api_key` | TinyURL Bearer token |

API calls use `POST https://api.tinyurl.com/create` to create and `PATCH https://api.tinyurl.com/change` to update.

### How It Works

1. When a post is **first published**, the permalink is sent to the configured provider and the returned short URL and alias are stored as post meta (`_short_url`, `_short_url_alias`, `_short_url_target`).
2. If the **permalink changes** (slug or domain update), the existing alias is automatically updated to point to the new URL.
3. Autosaves and revisions are skipped.

### Editor Display

The short URL appears in the sidebar panel / meta box immediately after the post is published. It is shown in a read-only monospace input field with a **Copy** button.

If the QR Code Generator feature is also enabled, a QR code of the short URL is displayed below it with **Download PNG** and **Download SVG** buttons.

### Post Meta Fields

| Meta key | Visibility | Description |
|----------|-----------|-------------|
| `_short_url` | REST API (read) | The short URL displayed to editors |
| `_short_url_alias` | Private | The alias/keyword used to update the target |
| `_short_url_target` | Private | The permalink at time of last creation/update |

### Implementation

| File | Purpose |
|------|---------|
| `php/url-shortener.php` | Feature flag, post meta registration, provider API functions, `save_post` hook, script enqueueing, meta box registration |
| `js/url-shortener-sidebar.js` | Gutenberg React panel |
| `js/url-shortener-metabox.js` | Classic Editor jQuery meta box |

---

## Custom CSS and JS

Adds per-post **CSS** and **JS** code editor fields to the editor sidebar on all public post types. Editors can write custom styles and scripts that apply only to that specific post or page.

The fields appear as a tabbed sidebar panel with two tabs — **CSS** and **JS** — each containing an ACE-based code editor with syntax highlighting (provided by ACF Extended's `acfe_code_editor` field type). The field values are stored as post meta (`custom_css` and `custom_js`).

At render time, the custom CSS is output as an inline `<style>` tag and the custom JS as an inline `<script>` tag, both injected just before `</body>` — after `wp_footer()` — so they load last and can override any other styles or scripts on the page.

### Implementation

| File | Purpose |
|------|---------|
| `acf-json/group_651d94db438f5.json` | ACF field group definition (CSS and JS code editor fields) |
| `php/css.php` (`render_page_custom_css()`) | Outputs the `<style>` tag with the post's custom CSS |
| `php/js.php` (`render_page_custom_js()`) | Outputs the `<script>` tag with the post's custom JS |

---

## Featured Post

Adds a "Featured" toggle to the editor and a "Featured" column to the posts list screen for all public post types. This lets editors mark posts as featured and quickly see/filter featured posts.

### Block Editor

A **Featured** toggle appears in the **Status & Visibility** sidebar panel. Toggling it sets the `_is_featured` boolean post meta field on the post.

### Classic Editor

A **Featured** meta box with a checkbox appears in the editor sidebar.

### Posts List Screen

A **Featured** column is added to the admin posts list for every public post type. Each row shows a checkbox that updates the featured status via AJAX — no page reload required.

The column is sortable (click the column header to sort by featured status). For the Portfolio post type, a **Featured** view tab is added to filter the list to only featured posts.

### Customizer

Controlled by the toggle **Theme Features > Featured Posts** (`feat_featured_posts`, default: enabled).

### Implementation

| File | Purpose |
|------|---------|
| `php/featured-posts.php` | Meta field registration, meta box, admin column, AJAX handler, sorting/filtering hooks |
| `js/featured-posts.js` | Gutenberg sidebar toggle (PluginPostStatusInfo) |
| `js/featured-posts-list.js` | AJAX checkbox handler for the posts list column |

---

## Excerpt Field Repositioning

Moves the WordPress Excerpt field to a more prominent position directly below the content editor, instead of its default location further down the page.

### Classic Editor

The default `postexcerpt` meta box is removed and re-added with `high` priority in the `normal` context, pushing it to appear directly below the content editor for all public post types that support excerpts.

### Block Editor

Custom CSS is injected to hide the Excerpt dropdown from the editor sidebar panel, so editors use the standalone excerpt meta box instead.

### Customizer

Controlled by the toggle **Theme Features > Move Excerpt to after Content** (`feat_move_excerpt`, default: enabled).

### Implementation

| File | Purpose |
|------|---------|
| `php/setup.php` | Meta box removal/re-registration and sidebar CSS injection |

---

## Hide Yoast SEO Meta Box

Hides the Yoast SEO meta box (`#wpseo_meta`) from the Classic Editor post edit screen using a CSS `display:none` rule injected on `admin_head`. This reduces clutter in the editor without disabling Yoast functionality — Yoast's Block Editor sidebar panel is unaffected.

### Customizer

Controlled by the toggle **Theme Features > Hide Yoast Metabox** (`feat_hide_yoast_metabox`, default: enabled).

### Implementation

| File | Purpose |
|------|---------|
| `php/setup.php` | Conditional CSS injection on `admin_head` |

---

## Allow SVG Uploads

Enables SVG file uploads in the WordPress Media Library by adding the `image/svg+xml` MIME type to the allowed upload types via the `upload_mimes` filter.

> **Note:** WordPress does not sanitize SVG files on upload. Enabling this on sites with untrusted uploaders may carry XSS risk.

### Customizer

Controlled by the toggle **Theme Features > Allow SVG Upload** (`feat_allow_svg_upload`, default: enabled).

### Implementation

| File | Purpose |
|------|---------|
| `php/setup.php` | `upload_mimes` filter to add SVG MIME type |

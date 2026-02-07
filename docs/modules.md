# Modules

Modules are reusable site-wide components that render outside of the Gutenberg content area. Unlike blocks (which are placed inside post/page content), modules are called directly from the main template (`custom.php`) and configured through ACF option pages.

## Available Modules

| Module | Description | Default Placement |
|--------|-------------|-------------------|
| **Alerts** | Dismissible alert/notification bar | Top of header |
| **CTAs** | Call-to-action sections | After main content |
| **Modal** | Modal dialog overlays | Before closing body |
| **Popup** | Timed popup notifications | Before closing body |
| **Share** | Social sharing buttons | Footer |
| **Social** | Social media icon links | Footer |

## How Modules Work

### Loading

Modules are loaded automatically when the **Enable Modules** feature is turned on (Theme Customizer > Theme Features). The `modules.php` file:

1. Scans both `php/modules/` (parent) and `php/custom/modules/` (child) directories
2. Loads each module's PHP file
3. Registers ACF field filters for style presets

### Rendering

Each module provides a `render()` function in its namespace. These are called from `custom.php`:

```php
// In custom.php
\lqx\modules\alerts\render();   // In header
\lqx\modules\cta\render();      // After main content
\lqx\modules\social\render();   // In footer
\lqx\modules\share\render();    // In footer
\lqx\modules\popup\render();    // Before closing body
```

### Configuration

Module content and settings are configured through ACF option pages accessible in the WordPress admin sidebar (typically under a "Site Settings" menu).

## Module Details

### Alerts

Displays a notification bar at the top of the site. Useful for announcements, promotions, or important notices.

- Configured via admin option page
- Multiple alert items supported
- Dismissible by users
- Style presets available
- Can be hidden from non-admin users (Theme Customizer > Theme Features)

### CTAs (Call-to-Action)

Renders call-to-action sections after the main content area.

- Configured via admin option page
- Style presets for visual variations
- Can be hidden from non-admin users

### Modal

Displays modal dialog overlays triggered by user interaction or page load.

- Configured via admin option page
- Style presets available
- JavaScript-powered show/hide via the `lqx.modal` module
- Can be hidden from non-admin users

### Popup

Shows timed popup notifications that appear after a delay or on specific triggers.

- Configured via admin option page
- Style presets available
- JavaScript-powered timing and display via the `lqx` library
- Can be hidden from non-admin users

### Share

Renders social sharing buttons for the current page.

- Configured via admin option page
- Supports major social platforms
- Generates share URLs dynamically

### Social

Displays social media profile icon links.

- Configured via admin option page
- Supports major social platforms
- Icon-based display

## Style Presets

Modules that support style presets (CTAs, Popups, Modals) allow you to define named visual variations. These work similarly to block style presets:

1. Define styles in the module's global settings (admin option page)
2. Select a style when configuring an individual module item
3. The style name is applied as a CSS class for custom styling

## Overriding Modules

To customize a module's rendering, create files in `php/custom/modules/{module-name}/`:

### Override Resolution

For the **renderer**:
1. `php/custom/modules/{module-name}/default.php` (child theme override)
2. `php/modules/{module-name}/default.php` (parent theme default)

For the **template**:
1. `php/custom/modules/{module-name}/default.tmpl.php` (child theme override)
2. `php/modules/{module-name}/default.tmpl.php` (parent theme default)

For **sub-templates**:
1. `php/custom/modules/{module-name}/{sub-template}.tmpl.php` (child theme override)
2. `php/modules/{module-name}/{sub-template}.tmpl.php` (parent theme default)

### Example: Override Alerts Rendering

Create `php/custom/modules/alerts/default.tmpl.php`:

```php
<?php
// Custom alert rendering
foreach ($alerts as $alert) : ?>
    <div class="custom-alert <?= $alert['style'] ?>">
        <p><?= $alert['message'] ?></p>
        <button class="dismiss">Close</button>
    </div>
<?php endforeach; ?>
```

## Admin Visibility

Individual modules can be hidden from non-administrator users in the WordPress admin. This is controlled through the Theme Customizer under **Theme Features**:

| Setting | Module |
|---------|--------|
| Hide Alerts Module for Non Administrators | Alerts |
| Hide CTAs Module for Non Administrators | CTAs |
| Hide Modals Module for Non Administrators | Modal |
| Hide Popups Module for Non Administrators | Popup |

When enabled, the module's admin menu item is hidden from editors and other non-admin roles, preventing them from modifying site-wide module content.

## Module API Functions

### `\lqx\modules\get_renderer($module_name)`

Returns the file path to the module's renderer, checking child theme first.

### `\lqx\modules\get_template($module_name, $sub_template)`

Returns the file path to the module's template, checking child theme first. Accepts an optional sub-template name for modules with multiple template parts.

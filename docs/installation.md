# Installation

## Prerequisites

Before installing the Lyquix theme, ensure you have the following:

- **WordPress** 6.0 or later
- **PHP** 7.4 or later
- **Bun** runtime (used for dependency management, TypeScript compilation, and running build scripts)

## Required Plugins

The following plugins are required for full functionality:

| Plugin | Type | Purpose |
|--------|------|---------|
| **Advanced Custom Fields PRO** | Premium | Powers all block settings, presets, and content fields |
| **ACF Extended PRO** | Premium | Extends ACF with additional field types and features |

## Recommended Plugins

The theme checks for and recommends these plugins on first activation:

| Plugin | Purpose |
|--------|---------|
| Activity Log | Tracks admin activity |
| Advanced Editor Tools | Enhanced TinyMCE editor |
| Better Search Replace | Database search and replace |
| EWWW Image Optimizer | Image compression and optimization |
| Gravity Forms | Form builder |
| Post SMTP | Email delivery |
| Redirection | URL redirect management |
| Simple Custom Post Order | Drag-and-drop post ordering |
| Yoast SEO | Search engine optimization |
| Yoast Duplicate Post | Post/page duplication |
| Wordfence | Security |
| W3 Total Cache | Performance caching |
| Zero Spam | Spam protection |

## Step-by-Step Installation

### 1. Install the Parent Theme

Copy or clone the `lyquix` directory into your WordPress themes directory:

```
wp-content/themes/lyquix/
```

### 2. Install the Child Theme

Copy or clone the `lyquix_child` directory into the same themes directory:

```
wp-content/themes/lyquix_child/
```

### 3. Install Dependencies

Navigate to the **child theme** directory and run:

```bash
cd wp-content/themes/lyquix_child
bun install
```

This does two important things:

1. **Installs all dependencies** (Gulp, Tailwind, Sass, TypeScript, etc.)
2. **Runs `postinstall.sh`** which bootstraps the child theme by copying dist files

### 4. What postinstall.sh Creates

The post-install script copies template files from the parent theme into the child theme only if they don't already exist (it won't overwrite your customizations):

| Source (Parent) | Destination (Child) | Purpose |
|-----------------|---------------------|---------|
| `.htaccess` | `.htaccess` | Apache rewrite rules |
| `custom.dist.php` | `custom.php` | Main HTML template |
| `css/custom/custom.dist.scss` | `css/custom/custom.scss` | Main SCSS entry point |
| `css/tailwind/presets.dist.js` | `css/tailwind/presets.js` | Tailwind presets (breakpoints, spacing) |
| `css/tailwind/theme.dist.js` | `css/tailwind/theme.js` | Tailwind theme configuration |
| `js/scripts.dist.ts` | `js/scripts.ts` | TypeScript entry point for custom scripts |
| `js/custom/scripts/module.dist.ts` | `js/custom/scripts/module.ts` | Sample TypeScript module |
| `php/custom/templates/404.dist.php` | `php/custom/templates/404.php` | 404 page template |
| `php/custom/templates/search.dist.php` | `php/custom/templates/search.php` | Search results template |
| `css/lib/*/_*.dist.scss` | `css/custom/*/_*.scss` | SCSS partial stubs for every category |

The SCSS stubs are created for all categories: abstracts, base, components, layouts, pages, themes, and vendors.

### 5. Activate the Theme

In the WordPress admin:

1. Go to **Appearance > Themes**
2. Activate the **Lyquix Child** theme (not the parent)

### 6. Install Required Plugins

After activation, you will see an admin notice listing required plugins that are not installed or not active. Click the links to install and activate each one.

### 7. Start the Development Server

From the child theme directory, run:

```bash
bun run watch
```

This starts three parallel watchers:

- **Bun** watches and recompiles the Lyquix TypeScript library
- **Bun** watches and recompiles the Scripts TypeScript
- **Gulp** watches SCSS, PHP, and config files, recompiles CSS, minifies JS, and triggers LiveReload

## Verifying the Installation

After installation, verify everything is working:

1. Visit your site - you should see the basic theme structure rendered
2. Check the WordPress admin for any plugin alerts
3. Open browser DevTools - you should see `lyquix.min.js` and `styles.min.css` loaded
4. If LiveReload is enabled, changes to SCSS files should auto-refresh the browser

## Directory Structure After Installation

After `bun install`, your child theme should look like this:

```
lyquix_child/
├── css/
│   ├── custom/            # Your custom SCSS files
│   │   ├── abstracts/     # Variables, mixins
│   │   ├── base/          # Reset, typography, forms
│   │   ├── components/    # Block component styles
│   │   ├── layouts/       # Header, footer, layout
│   │   ├── pages/         # Page-specific styles
│   │   ├── themes/        # Theme variations
│   │   ├── vendors/       # Third-party styles
│   │   └── custom.scss    # Main SCSS entry point
│   ├── tailwind/          # Tailwind configuration
│   ├── styles.css         # Compiled output
│   └── styles.min.css     # Minified output
├── js/
│   ├── custom/scripts/    # Your custom TypeScript modules
│   ├── scripts.ts         # TypeScript entry point
│   ├── scripts.js         # Compiled output
│   └── scripts.min.js     # Minified output
├── php/
│   └── custom/            # Your custom PHP
│       └── templates/     # Page templates (404, search)
├── custom.php             # Main HTML template
├── functions.php          # Theme functions (DO NOT MODIFY)
├── style.css              # Theme header
├── package.json           # Build configuration
├── gulpfile.js            # Gulp tasks
└── node_modules/          # Dependencies
```

## Updating the Theme

The parent theme includes an update checker. When updates are available, you will see a notification in the WordPress admin. Updates to the parent theme will not affect your child theme customizations.

To update, replace the `lyquix/` directory with the new version. Then re-run `bun install` in the child theme to pick up any new dist file templates.

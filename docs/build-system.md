# Build System

The Lyquix theme uses a hybrid build pipeline combining Bun, Gulp, Sass, Tailwind CSS, and PostCSS.

## Quick Start

From the child theme directory:

```bash
# Install dependencies and bootstrap child theme
nvm use 18
bun install

# Start development watchers
bun run watch
```

## Scripts

Defined in the child theme's `package.json`:

| Script | Command | Purpose |
|--------|---------|---------|
| `postinstall` | `./postinstall.sh` | Runs automatically after `bun install` to copy dist files |
| `watch` | `run-p watch:**` | Starts all watchers in parallel |
| `watch:lyquix` | `bun build js/lyquix.ts ...` | Watches and compiles the Lyquix TypeScript library |
| `watch:scripts` | `bun build js/scripts.ts ...` | Watches and compiles the Scripts TypeScript |
| `watch:gulp` | `gulp` | Runs the Gulp default task (CSS compilation + JS minification + LiveReload) |

`run-p` is from the `npm-run-all` package and runs all matching scripts in parallel.

## CSS Build Pipeline

The CSS pipeline has several stages:

### 1. SCSS Compilation

```
css/custom/custom.scss  →  css/custom.css
```

The entry point `custom.scss` imports all SCSS partials from the SMACSS-organized directories. Compiled using Dart Sass.

### 2. Tailwind CSS Processing (Frontend)

```
css/custom.css  →  css/styles.css
```

Tailwind scans all PHP, JS, and template files for utility classes, then processes the CSS through the frontend configuration (`css/tailwind/config.js`).

### 3. Tailwind CSS Processing (Editor)

```
css/tailwind/editor.css  →  css/editor.css
```

A separate Tailwind pass generates editor-specific styles with Preflight disabled to avoid conflicts with the WordPress admin.

### 4. Theme Function Resolution

The build runs up to 5 additional Tailwind passes to resolve any `theme()` function calls that reference other theme values. This handles cases like:

```css
.my-class {
  color: theme('colors.primary');
}
```

### 5. PostCSS Minification

```
css/styles.css  →  css/styles.min.css  (+ source map)
```

Uses cssnano for minification and autoprefixer for vendor prefixes. Source maps are generated for debugging.

## JavaScript Build Pipeline

### TypeScript Compilation (Bun)

Bun compiles TypeScript to browser-ready JavaScript:

```
js/lyquix.ts   →  js/lyquix.js     (Lyquix library from parent theme)
js/scripts.ts  →  js/scripts.js    (Custom scripts)
```

Both are compiled as IIFE (Immediately Invoked Function Expression) format targeting browsers.

### JavaScript Minification (Gulp + Terser)

```
js/lyquix.js   →  js/lyquix.min.js   (+ source map)
js/scripts.js  →  js/scripts.min.js  (+ source map)
js/vue.js      →  js/vue.min.js      (+ source map, optional)
```

Terser strips comments and minifies. Source maps are generated.

## Gulp Tasks

The `gulpfile.js` defines these tasks:

| Task | Purpose |
|------|---------|
| `compile-css` | Full SCSS + Tailwind + PostCSS pipeline |
| `lyquixjs` | Minify the Lyquix library |
| `scriptsjs` | Minify the Scripts library |
| `vuejs` | Minify Vue.js bundle (disabled by default) |
| `livereload` | Start LiveReload server and file watchers |
| `default` | Run all tasks in parallel |

### Watched File Patterns

The LiveReload task watches these paths for changes:

- `css/custom/**/*.scss` - SCSS files
- `js/lyquix.js` - Compiled Lyquix library
- `js/scripts.js` - Compiled Scripts
- `page-templates/*.php` - Page templates
- `php/**/*.php` - All PHP files
- `custom.php` - Main HTML template
- `tribe/**/*.php` - Tribe Events templates
- `tribe-events/**/*.php` - Tribe Events templates
- `css/tailwind/whitelist.html` - Tailwind class whitelist

When any of these files change, the `compile-css` task runs and triggers a browser refresh via LiveReload on port 35729.

## Critical Path CSS Generation

The theme includes a standalone utility for generating critical path CSS:

```bash
bun critical.js
```

This interactive script:

1. Prompts for the website URL and optional HTTP credentials
2. Saves the configuration to `critical.json` for reuse
3. Fetches the critical CSS configuration from `/wp-json/lyquix/v3/critical`
4. Generates optimized above-the-fold CSS for each template type
5. Outputs files to `css/critical/[template-type].css`

Critical CSS is generated per post type, with optional per-page overrides for pages. See the [Styling Guide](styling.md) for more on critical path CSS.

## Dependencies

Key build dependencies (from `package.json`):

| Package | Version | Purpose |
|---------|---------|---------|
| `tailwindcss` | ^3.4.1 | CSS utility framework |
| `sass` | ^1.72.0 | SCSS compilation |
| `typescript` | ^5.4.2 | TypeScript support |
| `gulp` | ^4.0.2 | Task runner |
| `gulp-livereload` | ^4.0.2 | Browser auto-refresh |
| `gulp-postcss` | ^10.0.0 | PostCSS integration |
| `cssnano` | ^6.1.0 | CSS minification |
| `autoprefixer` | ^10.4.18 | Vendor prefixes |
| `gulp-terser` | ^2.1.0 | JS minification |
| `gulp-sourcemaps` | ^3.0.0 | Source map generation |
| `rollup` | ^4.13.0 | Module bundling |
| `vue` | ^3.4.21 | Vue.js framework (optional) |
| `tailwind-layouts` | ^0.3.0 | CSS layout primitives plugin |
| `@tailwindcss/container-queries` | ^0.1.1 | Container query support |
| `@tailwindcss/aspect-ratio` | ^0.4.2 | Aspect ratio utilities |
| `eslint` | ^8.57.0 | JavaScript linting |
| `stylelint` | ^16.2.1 | CSS/SCSS linting |
| `prettier` | ^3.1.1 | Code formatting |

## Environment Detection

The build system and theme automatically detect local development environments:

- If `WPCONFIG_ENVNAME` is set to `local`, or
- If the hostname ends in `.test`

Then non-minified CSS and JS files are served automatically, regardless of the customizer setting.

## Linting

The theme includes configurations for code quality:

- **ESLint** (`.eslintrc.json`) - JavaScript/TypeScript linting
- **StyleLint** (`.stylelintrc.json`) - SCSS/CSS linting
- **Prettier** with Tailwind plugin - Code formatting
- **EditorConfig** (`.editorconfig`) - Editor consistency

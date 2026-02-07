# Template System

The Lyquix theme replaces the standard WordPress template hierarchy with a custom routing engine. Instead of using WordPress theme files like `single.php`, `page.php`, or `archive.php`, all routing flows through a single entry point (`custom.php`) and a PHP router.

## How It Works

### The Entry Point: custom.php

The `custom.php` file (copied from `custom.dist.php` during installation) defines the entire HTML structure of your site:

```
<!DOCTYPE html>
<html>
<head>
    Meta tags, GTM, wp_head(), favicons
</head>
<body>
    <header>
        Alerts, navigation menus
    </header>
    <main>
        <article>
            Router output ← Page/post content rendered here
        </article>
        CTA module
    </main>
    <footer>
        Navigation, social icons, sharing
    </footer>
    Popups, browser alerts, LiveReload
</body>
</html>
```

The key line is `\lqx\router\render()` inside the `<article>` tag. This is where the router determines which template to load based on the current request.

### The Router: php/router.php

The router (`\lqx\router\render()`) checks the current WordPress request and loads the matching template file from `php/custom/templates/`.

## Template Resolution Order

The router follows a specific fallback hierarchy for each request type:

### Home / Front Page

1. `front-page.php` (if `is_front_page()`)
2. `home.php` (if `is_home()`)

### Error / Search

1. `404.php` (if `is_404()`)
2. `search.php` (if `is_search()`)

### Archives

For **custom post type archives** (`is_post_type_archive()`):
1. `archive-{post_type}.php`
2. `archive.php`

For **category archives** (`is_category()`):
1. `category-{slug}.php`
2. `category.php`

For **taxonomy archives** (`is_tax()`):
1. `taxonomy-{taxonomy}-{term}.php`
2. `taxonomy-{taxonomy}.php`
3. `taxonomy.php`

For **tag archives** (`is_tag()`):
1. `tag-{slug}.php`
2. `tag.php`

For **author archives** (`is_author()`):
1. `author-{name}.php`
2. `author.php`

For **date archives** (`is_date()`):
1. `day-{year}-{month}-{day}.php` / `day.php`
2. `month-{year}-{month}.php` / `month.php`
3. `year-{year}.php` / `year.php`
4. `date.php` (fallback for any date)

### Singular Posts

For **blog posts and custom post types** (`is_single()`):
1. `{post_type}-{slug}.php`
2. `{post_type}.php`

For **pages** (`is_page()`):
1. `page-{slug}.php`
2. `{page-template-slug}.php` (WordPress page template)
3. `page.php`

For **attachments** (`is_attachment()`):
1. `{mime_type}-{slug}.php`
2. `{mime_type}-{mime_subtype}.php`
3. `{mime_type}.php`
4. `attachment.php`

### Fallbacks

If no template is found:
- Archives fall back to `archive.php` in the child theme, then `php/archive.php` in the parent theme
- Singular posts fall back to `singular.php` in the child theme, then `php/singular.php` in the parent theme
- If nothing matches, the router displays a debug error page

## Creating Templates

All template files live in `php/custom/templates/` in the child theme. To create a new template:

### Basic Page Template

Create `php/custom/templates/page.php`:

```php
<?php
// Default page template
the_content();
```

### Post Type Template

Create `php/custom/templates/post.php`:

```php
<?php
// Blog post template
?>
<h1><?php the_title(); ?></h1>
<div class="meta">
    <time><?php the_date(); ?></time>
    <span><?php the_author(); ?></span>
</div>
<?php the_content(); ?>
```

### Specific Page Template

Create `php/custom/templates/page-about.php` for a page with slug "about":

```php
<?php
// About page with custom layout
?>
<div class="about-page">
    <?php the_content(); ?>
</div>
```

## Custom Router Logic

For complex routing needs that don't fit the standard hierarchy, you can add custom logic in `php/custom/router.php`. This file is loaded when the standard router doesn't find a matching template for archive or singular requests:

```php
<?php
// php/custom/router.php

// Example: Route events archive to a custom template
if (is_post_type_archive('event')) {
    $tmpl_name = 'events-archive';
}

// Example: Route by custom field value
if (is_singular('product') && get_field('product_type') === 'digital') {
    $tmpl_name = 'product-digital';
}
```

Set `$tmpl_name` to the filename (without `.php`) of the template in `php/custom/templates/`.

## Page Templates (Gutenberg)

The theme also supports WordPress page templates registered through the `page-templates/` directory:

| Template | Purpose |
|----------|---------|
| `chromeless.php` | Renders content without header, footer, or navigation |
| `raw.php` | Raw output template |

To create a new page template, copy `page-templates/template.dist.php` and modify it. The template will appear in the Page Attributes panel in Gutenberg.

When a page template is selected, the router resolves it by stripping the `page-templates/` prefix and `.php` extension to find a matching template in `php/custom/templates/`.

## Template Functions Available

Inside any template file, you have access to:

- All standard WordPress template functions (`the_content()`, `the_title()`, etc.)
- All Lyquix namespaced functions (`\lqx\util\*`, `\lqx\blocks\*`, etc.)
- ACF functions (`get_field()`, `the_field()`, etc.)
- The global `$wp_query` object

# Output Escaping

## The rule

**Escape at the point of output, chosen by the context you are printing into.**

Not when the value is read, not when it is stored — at the `<?= ?>` or `echo` that puts it
in the page. That is the only place where the surrounding context is visible, and context
is what decides which function to use.

| Where the value lands | Use | Example |
|---|---|---|
| An HTML attribute | `esc_attr()` | `class="<?= esc_attr($s['style']) ?>"` |
| A URL attribute (`href`, `src`, `action`, `poster`, `srcset`, `data-src`) | `esc_url()` | `href="<?= esc_url($item['link']['url']) ?>"` |
| Element text | `esc_html()` | `<span><?= esc_html($p['total_posts']) ?></span>` |
| A JavaScript string literal | `esc_js()` | `('<?= esc_js($token) ?>')` |
| Editor-authored rich text | `wp_kses_post()` | `<?= wp_kses_post($c['content']) ?>` |

## The exceptions, and why they are exceptions

Three kinds of output are deliberately **not** escaped. Each one is a place where the value
*is* markup, so escaping it would render tags as visible text:

1. **Functions that return markup or attribute strings.** `\lqx\util\get_src_srcset_sizes_attribs()`,
   `\lqx\util\get_alt_attribs()`, `\lqx\layouts\get_tailwind_classes()` and the block renderers
   build their own attributes and escape their own inputs. Escaping their return value breaks it.

2. **Editor-authored rich text.** WYSIWYG fields (`content`, `body`, `text`, `intro_text`,
   `teaser`, `description`) and headings that editors may style inline arrive as HTML on purpose.
   ACF and WordPress sanitize these on save. Use `wp_kses_post()` if a field should be limited;
   do not use `esc_html()`.

3. **Values used as element tag names** (`heading_style`, `subheading_style`, `heading_tag`).
   These are printed *as* the tag — `<<?= $s['heading_style'] ?>>` — and come from a fixed
   select. Escaping them would corrupt the tag. Constrain them with an allow-list if they ever
   become free text.

## Practical notes

- `esc_attr()` and `esc_html()` are not interchangeable. `esc_attr()` escapes quotes so a value
  cannot break out of an attribute; `esc_html()` escapes `<` and `>` so a value cannot open a tag.
- Escaping twice is a bug, not extra safety: it turns `&` into `&amp;amp;` and shows up as
  visible garbage. If a helper already escapes, don't escape its output again.
- Unquoted attributes are a bug even with escaping — `href=tel:"<?= ... ?>"` puts the quotes
  *inside* the value. Always quote the attribute, then escape what goes in it.

<<?= $s['heading_style'] ?>>
	<?= $s['heading_clickable'] == 'y' && $first_link ? sprintf('<a href="%s">%s</a>', esc_attr($first_link), $item['heading']) : $item['heading'] ?>
</<?= $s['heading_style'] ?>>

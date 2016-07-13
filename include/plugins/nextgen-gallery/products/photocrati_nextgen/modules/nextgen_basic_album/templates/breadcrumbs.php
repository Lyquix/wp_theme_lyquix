<ul class="ngg-breadcrumbs">
    <?php
    $end = end($breadcrumbs);
    reset($breadcrumbs);
    foreach ($breadcrumbs as $crumb) { ?>
        <li class="ngg-breadcrumb">
            <?php if (!is_null($crumb['url'])) { ?>
                <a href="<?php echo $crumb['url']; ?>"><?php esc_html_e($crumb['name']); ?></a>
            <?php } else { ?>
                <?php esc_html_e($crumb['name']); ?>
            <?php } ?>
            <?php if ($crumb !== $end) { ?>
                <span class="ngg-breadcrumb-divisor"><?php echo $divisor; ?></span>
            <?php } ?>
        </li>
    <?php } ?>
</ul>
<?php if (is_array($c) and count($c) > 0):?>
<ul class="lqx-map-items">
<?php foreach($processed_items as $item):?>
	<li class="lqx-map-item">
		<?php require \lqx\blocks\get_template('map', $s['preset'], 'item'); ?>
	</li>
<?php endforeach;?>
</ul>
<?php endif;?>

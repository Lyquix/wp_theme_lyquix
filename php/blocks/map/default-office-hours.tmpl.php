<div class="hours">
<?php foreach($item['business_hours'] as $hours):
	switch($hours['day_group_type']) {
		case 'single':
			echo $hours['single'] . ': ' . $hours['hours']['open'] . ' - ' . $hours['hours']['close'];
			break;
		case 'multiple':
			echo implode(', ', $hours['multiple']) . ': ' . $hours['hours']['open'] . ' - ' . $hours['hours']['close'];
			break;
		case 'range':
			echo $hours['range']['start'] . ' - ' . $hours['range']['end'] . ': ' . $hours['hours']['open'] . ' - ' . $hours['hours']['close'];
			break;
	}
endforeach;
?>
</div>

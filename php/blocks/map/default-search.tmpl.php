<div class="lqx-map-search">
	<div class="input-wrapper">
		<input class="search_query" id="lqx-map-search=-ield" name="search_query" type="text" placeholder="Enter an address or zip code" autocomplete="off">
		<button type="submit" name="submit" class="submit-button use-my-location" value=""></button>
	</div>
	<?php if ($s['show_distance_limit_drop_down'] == 'y'): ?>
		<select class="miles-from" id="miles-from">
			<option value="all">All locations</option>
			<option value="5">5 miles</option>
			<option value="10">10 miles</option>
			<option value="25">25 miles</option>
			<option value="50">50 miles</option>
			<option value="100">100 miles</option>
		</select>
	<?php endif;?>
	<div class="manual-container">
		<div class="manual-link">Use my current location</div>
	</div>
</div>

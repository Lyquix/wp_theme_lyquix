<?php

$tailwind_values = [];

// Define a callback function to use with array_walk_recursive
$callback = function ($value, $key) use (&$tailwind_values) {
	if (is_string($value) && str_starts_with($value, 'tailwind_')) {
		// Remove the prefix and add to the results array
		$tailwind_values[] = str_replace('tailwind_', '', $value);
	}
	elseif (is_string($key) && str_starts_with($key, 'tailwind_') && $value) {
		$tailwind_values[] = str_replace('tailwind_', '', $key) . $value;
	}
};

// Apply the callback to each element of the array
$fields = get_fields();

if ($fields) array_walk_recursive($fields, $callback);

$classes = implode(' ', $tailwind_values) . ' ' . $block['className'];

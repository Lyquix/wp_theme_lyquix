<?php

/**
 * typesense.php - Typesense functionality for wp_theme_lyquix
 *
 * @version     3.4.0
 * @package     wp_theme_lyquix
 * @author      Lyquix
 * @copyright   Copyright (C) 2015 - 2024 Lyquix
 * @license     GNU General Public License version 2 or later
 * @link        https://github.com/Lyquix/wp_theme_lyquix
 */

//    .d8888b. 88888888888 .d88888b.  8888888b.   888
//   d88P  Y88b    888    d88P" "Y88b 888   Y88b  888
//   Y88b.         888    888     888 888    888  888
//    "Y888b.      888    888     888 888   d88P  888
//       "Y88b.    888    888     888 8888888P"   888
//         "888    888    888     888 888         Y8P
//   Y88b  d88P    888    Y88b. .d88P 888          "
//    "Y8888P"     888     "Y88888P"  888         888
//
//  DO NOT MODIFY THIS FILE!

namespace lqx\typesense;

class WP_Typesense_Query_Translator
{
	public function translate($ts_args)
	{
		$typesense_params = [];

		$typesense_params['q'] = $ts_args['s'] ?? '*';
		if (!empty($ts_args['query_by_fields'])) {
			$typesense_params['query_by'] = implode(',', $ts_args['query_by_fields']);
		}

		$filters = $this->build_filters($ts_args['filters'] ?? []);
		$geo_filter = $this->build_geofilter($ts_args['geofilter'] ?? []);
		if ($geo_filter) {
			$filters[] = $geo_filter;
		}

		if (!empty($filters)) {
			$typesense_params['filter_by'] = implode(' && ', $filters);
		}

		if (!empty($ts_args['sort'])) {
			$typesense_params['sort_by'] = $ts_args['sort'];
		} else {
			$typesense_params['sort_by'] = '_text_match:desc, sort_by_date:desc';
		}

		if (!empty($ts_args['facets'])) {
			$typesense_params['facet_by'] = implode(',', array_unique($ts_args['facets']));
		}

		if (!empty($ts_args['posts_per_page']) && $ts_args['posts_per_page'] > 0) {
			$typesense_params['per_page'] = (int) $ts_args['posts_per_page'];
		}
		if (!empty($ts_args['paged']) && $ts_args['paged'] > 1) {
			$typesense_params['page'] = (int) $ts_args['paged'];
		}

		return $typesense_params;
	}

	private function build_filters(array $filters_array)
	{
		$filters = [];

		foreach ($filters_array as $condition) {
			$field = $condition['field'];
			$compare = strtoupper($condition['compare']);
			$value = $condition['value'];

			if (is_array($value)) {
				$quoted_values = array_map(function($v) { return is_numeric($v) ? $v : "`$v`"; }, $value);
				$operator = ($compare === 'NOT IN' || $compare === '!=') ? '!=' : ':=';
				$filters[] = "$field:$operator [" . implode(',', $quoted_values) . "]";
			} else {
				$operator = $compare;
				if ($compare === '=' || $compare === 'IN') $operator = ':=';

				$quoted_value = is_numeric($value) ? $value : "`$value`";
				$filters[] = "$field:$operator$quoted_value";
			}
		}
		return $filters;
	}

	private function build_geofilter(array $geofilter_array)
	{
		if (empty($geofilter_array)) {
			return '';
		}

		$field = $geofilter_array['field'];
		$lat = $geofilter_array['lat'];
		$lng = $geofilter_array['lng'];
		$radius_meters = $geofilter_array['miles'];

		return "{$field}:({$lat}, {$lng}, {$radius_meters} mi)";
	}
}

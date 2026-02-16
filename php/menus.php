<?php

/**
 * setup.php - Theme initial setup
 *
 * @version     3.2.0
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
//  Instead create a file called menus.php in /php/custom/ and add custom
//  menus to the $menus array in that file

namespace lqx\menus;

/**
 * Add menu positions to the theme
 *
 * @return void
 * 		Registers menu positions
 */
function add_menu_positions() {
	// Array of menu locations
	$menus = [
		// Header Menus
		'Top Menu',
		'Main Menu',
		'Utility Menu',
		'Logged-In Menu',

		// Footer menus
		'Bottom Menu',
		'Footer Menu',

		// Other
		'Hidden Menu',
	];

	// Add custom menu positions to $menus array
	if (file_exists(get_stylesheet_directory() . '/php/custom/menus.php')) {
		require get_stylesheet_directory() . '/php/custom/menus.php';
	}

	// Register menu locations
	foreach ($menus as $menu) {
		register_nav_menu(preg_replace('/[^a-z0-9]+/', '-', strtolower($menu)), __($menu, 'lyquix'));
	}
}

add_action('after_setup_theme', '\lqx\menus\add_menu_positions');

// menu walkers:

class Mega_Menu_Walker extends \Walker_Nav_Menu {

	public $current_region = '';

	public function __construct( $region = '' ) {
		$this->current_region = sanitize_key( $region );
	}

	public function start_el( &$output, $data_object, $depth = 0, $args = null, $current_object_id = 0 ) {
		// Restores the more descriptive, specific name for use within this method.
		$menu_item = $data_object;

		if ( isset( $args->item_spacing ) && 'discard' === $args->item_spacing ) {
			$t = '';
			$n = '';
		} else {
			$t = "\t";
			$n = "\n";
		}
		$indent = ( $depth ) ? str_repeat( $t, $depth ) : '';

		$classes   = empty( $menu_item->classes ) ? array() : (array) $menu_item->classes;
		$classes[] = 'menu-item-' . $menu_item->ID;

		/**
		* Filters the arguments for a single nav menu item.
		*/
		$args = apply_filters( 'nav_menu_item_args', $args, $menu_item, $depth );

		/**
		* Filters the CSS classes applied to a menu item's list item element.
		*/
		$class_names = implode( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $menu_item, $args, $depth ) );

		/**
		* Filters the ID attribute applied to a menu item's list item element.
		**/
		$id = apply_filters( 'nav_menu_item_id', 'menu-item-' . $menu_item->ID, $menu_item, $args, $depth );

		$li_atts          = array();
		$li_atts['id']    = ! empty( $id ) ? $id : '';
		$li_atts['class'] = ! empty( $class_names ) ? $class_names : '';
		$li_atts['role'] = 'treeitem';
		if ($depth > 0) $li_atts['tabindex'] = -1;
		/**
		* Filters the HTML attributes applied to a menu's list item element.
		**/
		$li_atts       = apply_filters( 'nav_menu_item_attributes', $li_atts, $menu_item, $args, $depth );
		$li_attributes = $this->build_atts( $li_atts );

		$output .= $indent . '<li' . $li_attributes . '>';

		$atts           = array();
		$atts['title']  = ! empty( $menu_item->attr_title ) ? $menu_item->attr_title : '';
		$atts['target'] = ! empty( $menu_item->target ) ? $menu_item->target : '';
		if ( '_blank' === $menu_item->target && empty( $menu_item->xfn ) ) {
			$atts['rel'] = 'noopener';
		} else {
			$atts['rel'] = $menu_item->xfn;
		}

		if ( ! empty( $menu_item->url ) ) {
			if ( get_privacy_policy_url() === $menu_item->url ) {
				$atts['rel'] = empty( $atts['rel'] ) ? 'privacy-policy' : $atts['rel'] . ' privacy-policy';
			}

			$atts['href'] = $menu_item->url;
		} else {
			$atts['href'] = '';
		}

		$atts['aria-current'] = $menu_item->current ? 'page' : '';

		/**
		* Filters the HTML attributes applied to a menu item's anchor element.
		*/
		$atts       = apply_filters( 'nav_menu_link_attributes', $atts, $menu_item, $args, $depth );
		$attributes = $this->build_atts( $atts );

		$title = apply_filters( 'the_title', $menu_item->title, $menu_item->ID );

		$title = apply_filters( 'nav_menu_item_title', $title, $menu_item, $args, $depth );

		$item_output  = $args->before;
		if ($depth == 0 && $args->walker->has_children == true) {
			$item_output .= '<button class="return-link" aria-label="Return to collapsed menu"><img src="'.get_stylesheet_directory_uri() . '/images/icons/arrow-blue-right.svg" alt="Open the submenu"/>Back</button><a' . $attributes . '>';
			$item_output .= $args->link_before . $title . $args->link_after;
			$item_output .= '</a><button class="open-submenu" aria-label="Open submenu"><img src="'.get_stylesheet_directory_uri() . '/images/icons/arrow-blue-right.svg" alt="Open the submenu"/></button>';
		} else if ($depth == 1 && $args->walker->has_children == true || get_field('mega_menu', $menu_item->ID) == 'y') {
			$item_output .= '<button class="open-accordion-submenu" aria-label="Open accordion menu"><img src="'.get_stylesheet_directory_uri() . '/images/icons/header-accordion-control.svg" alt="Open the submenu"/></button><a' . $attributes . '>';
			$item_output .= $args->link_before . $title . $args->link_after;
			$item_output .= '</a>';
			$posts = [];
			switch (get_field('items_type', $menu_item->ID)) {
				case 'post-type':
					$posts = get_posts(array('post_type'=> get_field('custom_post_type', $menu_item->ID), 'orderby'=>'menu_order', 'order'=>'ASC', 'posts_per_page'=>-1, 'post_status'=>'publish', 'post_parent'=>0));
					break;
				case 'dynamic-post-children':
                    $posts = get_posts(array('post_type'=> get_post_type($menu_item->object_id), 'post_parent'=>$menu_item->object_id, 'orderby'=>'menu_order', 'order'=>'ASC', 'posts_per_page'=>-1, 'post_status'=>'publish'));
					break;
				default:
					break;
			}

			if ($posts):
				$item_output .= '<div class="mega-menu"><ul class="sub-menu">';
				$region = $this->current_region;

				foreach ($posts as $post):
					$item_regions = get_field('related_regions', $post->ID);
					setup_postdata($post);
					if (is_array($item_regions) && !empty($item_regions[0]) && ($item_regions[0] !== '')) {
						if (!empty($region) && !in_array($region, $item_regions)) {
							continue;
						}
					}
                    $item_output .= '<li><a href="'.get_permalink($post->ID).'">'.get_the_title($post->ID).'</a>';
                    if (get_field('items_type', $menu_item->ID) == 'post-type'):
                        $children = get_children(array('post_parent' => $post->ID));
                        if (count($children) > 0):
                            $item_output .= '<ul class="sub-menu-children">';
                            foreach ($children as $child):
                                $item_output .= '<li><a href="'.get_permalink($child->ID).'">'.get_the_title($child->ID).'</a></li>';
                            endforeach;
                            $item_output .= '</ul>';
                        endif;
                    endif;
                    $item_output .= '</li>';
				endforeach;
				wp_reset_postdata();
				$item_output .= '</ul></div>';
			endif;
		} else {
			$item_output .= '<a' . $attributes . '>';
			$item_output .= $args->link_before . $title . $args->link_after;
			$item_output .= '</a>';
		}

		$item_output .= $args->after;

		$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $menu_item, $depth, $args );
	}
}

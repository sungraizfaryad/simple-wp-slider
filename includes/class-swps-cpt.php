<?php
/**
 * Custom Post Type registration for sliders.
 *
 * @package SimpleWPSlider
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers the swps_slider Custom Post Type.
 */
final class SWPS_CPT {

	const POST_TYPE = 'swps_slider';

	/**
	 * Register the post type. Hooked to `init`.
	 *
	 * @return void
	 */
	public static function register() {
		$labels = array(
			'name'               => __( 'Sliders', 'simple-wp-slider' ),
			'singular_name'      => __( 'Slider', 'simple-wp-slider' ),
			'add_new'            => __( 'Add New Slider', 'simple-wp-slider' ),
			'add_new_item'       => __( 'Add New Slider', 'simple-wp-slider' ),
			'edit_item'          => __( 'Edit Slider', 'simple-wp-slider' ),
			'view_item'          => __( 'View Slider', 'simple-wp-slider' ),
			'all_items'          => __( 'All Sliders', 'simple-wp-slider' ),
			'search_items'       => __( 'Search Sliders', 'simple-wp-slider' ),
			'not_found'          => __( 'No sliders found', 'simple-wp-slider' ),
			'not_found_in_trash' => __( 'No sliders found in trash', 'simple-wp-slider' ),
			'menu_name'          => __( 'Sliders', 'simple-wp-slider' ),
		);

		register_post_type(
			self::POST_TYPE,
			array(
				'labels'          => $labels,
				'public'          => false,
				'show_ui'         => true,
				'show_in_menu'    => true,
				'menu_icon'       => 'dashicons-images-alt2',
				'menu_position'   => 25,
				'show_in_rest'    => true,
				'rest_base'       => 'swps_sliders',
				'supports'        => array( 'title' ),
				'capability_type' => 'post',
				'map_meta_cap'    => true,
				'has_archive'     => false,
				'rewrite'         => false,
				'query_var'       => false,
			)
		);
	}
}

<?php
/**
 * Gutenberg block + pattern registration.
 *
 * @package SimpleWPSlider
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers the swps/slider block and the hero-slider starter pattern.
 */
final class SWPS_Block {

	/**
	 * Hook block + pattern registration.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ) );
		add_action( 'init', array( __CLASS__, 'register_patterns' ) );
	}

	/**
	 * Register the block from build output.
	 *
	 * @return void
	 */
	public static function register() {
		$block_dir = SWPS_DIR . 'assets/dist/block';
		if ( file_exists( $block_dir . '/block.json' ) ) {
			register_block_type( $block_dir );
		}
	}

	/**
	 * Register the "Sliders" pattern category and the hero-slider starter pattern.
	 *
	 * @return void
	 */
	public static function register_patterns() {
		if ( ! function_exists( 'register_block_pattern' ) ) {
			return;
		}

		register_block_pattern_category(
			'swps',
			array( 'label' => __( 'Sliders', 'simple-wp-slider' ) )
		);

		register_block_pattern(
			'swps/hero-slider',
			array(
				'title'       => __( 'Hero Slider (full-width)', 'simple-wp-slider' ),
				'description' => __( 'A full-width hero slider powered by Simple WP Slider.', 'simple-wp-slider' ),
				'categories'  => array( 'featured', 'media', 'swps' ),
				'content'     => '<!-- wp:swps/slider {"sliderId":0,"align":"full"} /-->',
			)
		);
	}
}

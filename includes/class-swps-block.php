<?php
/**
 * Gutenberg block registration.
 *
 * @package SimpleWPSlider
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers the swps/slider block from its build output.
 */
final class SWPS_Block {

	/**
	 * Hook block + pattern registration.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ) );
	}

	/**
	 * Register the block from assets/dist/block (created by wp-scripts build + CopyWebpackPlugin).
	 *
	 * @return void
	 */
	public static function register() {
		$block_dir = SWPS_DIR . 'assets/dist/block';
		if ( file_exists( $block_dir . '/block.json' ) ) {
			register_block_type( $block_dir );
		}
	}
}

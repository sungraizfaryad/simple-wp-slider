<?php
/**
 * Shortcode handler for [simplewpslider].
 *
 * @package SimpleWPSlider
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers the [simplewpslider] shortcode with a back-compat fallback for
 * the bare `[simplewpslider]` form used in v1.x — it renders the migrated
 * Default Slider via swps_legacy_default_slider option.
 */
final class SWPS_Shortcode {

	const TAG = 'simplewpslider';

	/**
	 * Register the shortcode hook.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ) );
	}

	/**
	 * Register the shortcode tag.
	 *
	 * @return void
	 */
	public static function register() {
		add_shortcode( self::TAG, array( __CLASS__, 'render' ) );
	}

	/**
	 * Render the shortcode.
	 *
	 * @param array|string $atts Shortcode attributes.
	 * @return string
	 */
	public static function render( $atts ) {
		$atts = shortcode_atts( array( 'id' => 0 ), $atts, self::TAG );
		$id   = (int) $atts['id'];

		if ( ! $id ) {
			$id = (int) get_option( 'swps_legacy_default_slider', 0 );
		}
		if ( ! $id || get_post_type( $id ) !== SWPS_CPT::POST_TYPE ) {
			return '';
		}
		return SWPS_Renderer::instance()->render( $id );
	}
}

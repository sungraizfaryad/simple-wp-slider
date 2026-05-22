<?php
/**
 * Server-side block render — delegates to SWPS_Renderer.
 *
 * @package SimpleWPSlider
 *
 * @var array $attributes Block attributes passed by WP.
 */

defined( 'ABSPATH' ) || exit;

$slider_id = isset( $attributes['sliderId'] ) ? (int) $attributes['sliderId'] : 0;
if ( ! $slider_id ) {
	return;
}
$wrapper = get_block_wrapper_attributes();
echo '<div ' . wp_kses_data( $wrapper ) . '>';
// SWPS_Renderer::render() escapes/sanitizes all output internally.
echo SWPS_Renderer::instance()->render( $slider_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- renderer escapes internally
echo '</div>';

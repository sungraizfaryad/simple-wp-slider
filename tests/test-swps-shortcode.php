<?php
/**
 * Tests for SWPS_Shortcode.
 *
 * @package SimpleWPSlider
 */

/**
 * Validates the [simplewpslider] shortcode + back-compat default.
 */
class Test_SWPS_Shortcode extends WP_UnitTestCase {

	public function test_renders_when_id_given() {
		$id = $this->factory()->post->create( array(
			'post_type'   => 'swps_slider',
			'post_status' => 'publish',
			'post_title'  => 'Hero',
		) );
		update_post_meta( $id, '_swps_slides', SWPS_Sanitizer::slides( array(
			array( 'type' => 'image', 'attachment_id' => 1, 'alt' => 'Hi' ),
		) ) );
		$html = do_shortcode( '[simplewpslider id="' . $id . '"]' );
		$this->assertStringContainsString( 'swps-slider', $html );
	}

	public function test_legacy_no_id_uses_default_slider_option() {
		$id = $this->factory()->post->create( array(
			'post_type'   => 'swps_slider',
			'post_status' => 'publish',
			'post_title'  => 'Default',
		) );
		update_post_meta( $id, '_swps_slides', SWPS_Sanitizer::slides( array(
			array( 'type' => 'image', 'attachment_id' => 1 ),
		) ) );
		update_option( 'swps_legacy_default_slider', $id, false );

		$html = do_shortcode( '[simplewpslider]' );
		$this->assertStringContainsString( 'data-swps-id="' . $id . '"', $html );
	}

	public function test_returns_empty_for_unknown_id() {
		$html = do_shortcode( '[simplewpslider id="999999"]' );
		$this->assertSame( '', $html );
	}
}

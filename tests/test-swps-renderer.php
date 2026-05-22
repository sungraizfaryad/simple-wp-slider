<?php
/**
 * Tests for SWPS_Renderer.
 *
 * @package SimpleWPSlider
 */

/**
 * Renderer integration tests.
 */
class Test_SWPS_Renderer extends WP_UnitTestCase {

	private $slider_id;

	public function set_up() {
		parent::set_up();
		$this->slider_id = $this->factory()->post->create( array(
			'post_type'   => 'swps_slider',
			'post_status' => 'publish',
			'post_title'  => 'My Hero',
		) );
	}

	public function test_renders_empty_when_no_slides() {
		$html = SWPS_Renderer::instance()->render( $this->slider_id );
		$this->assertSame( '', $html );
	}

	public function test_renders_image_slide_with_link_and_caption() {
		$att = $this->factory()->attachment->create_upload_object(
			DIR_TESTDATA . '/images/canola.jpg', $this->slider_id
		);
		update_post_meta( $this->slider_id, '_swps_slides', SWPS_Sanitizer::slides( array(
			array(
				'type'          => 'image',
				'attachment_id' => $att,
				'alt'           => 'Canola',
				'caption'       => 'Yellow field',
				'link_url'      => 'https://example.com',
				'link_target'   => '_blank',
			),
		) ) );

		$html = SWPS_Renderer::instance()->render( $this->slider_id );
		$this->assertStringContainsString( 'class="swps-slider"', $html );
		$this->assertStringContainsString( 'data-swps-id="' . $this->slider_id . '"', $html );
		$this->assertStringContainsString( 'aria-label="My Hero"', $html );
		$this->assertStringContainsString( '<a href="https://example.com"', $html );
		$this->assertStringContainsString( 'target="_blank"', $html );
		$this->assertStringContainsString( 'noopener', $html );
		$this->assertStringContainsString( 'noreferrer', $html );
		$this->assertStringContainsString( 'Yellow field', $html );
	}

	public function test_renders_legacy_url_fallback_when_no_attachment_id() {
		$slides = SWPS_Sanitizer::slides( array( array( 'type' => 'image' ) ) );
		$slides[0]['_legacy_url'] = 'https://cdn.example.com/old.jpg';
		update_post_meta( $this->slider_id, '_swps_slides', $slides );

		$html = SWPS_Renderer::instance()->render( $this->slider_id );
		$this->assertStringContainsString( 'https://cdn.example.com/old.jpg', $html );
	}

	public function test_escapes_caption_html() {
		update_post_meta( $this->slider_id, '_swps_slides', SWPS_Sanitizer::slides( array(
			array( 'type' => 'image', 'attachment_id' => 1, 'caption' => '<script>alert(1)</script>' ),
		) ) );
		$html = SWPS_Renderer::instance()->render( $this->slider_id );
		$this->assertStringNotContainsString( '<script>', $html );
	}
}

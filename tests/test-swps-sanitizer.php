<?php
/**
 * Tests for SWPS_Sanitizer.
 *
 * @package SimpleWPSlider
 */

/**
 * Validates settings + slide sanitization behavior.
 */
class Test_SWPS_Sanitizer extends WP_UnitTestCase {

	public function test_sanitize_settings_clamps_speed() {
		$out = SWPS_Sanitizer::settings( array( 'speed' => 999999 ) );
		$this->assertSame( 10000, $out['speed'] );

		$out = SWPS_Sanitizer::settings( array( 'speed' => 10 ) );
		$this->assertSame( 100, $out['speed'] );
	}

	public function test_sanitize_settings_casts_booleans() {
		$out = SWPS_Sanitizer::settings( array( 'autoplay' => '1', 'loop' => 0 ) );
		$this->assertTrue( $out['autoplay'] );
		$this->assertFalse( $out['loop'] );
	}

	public function test_sanitize_settings_enum_whitelist() {
		$out = SWPS_Sanitizer::settings( array( 'effect' => 'evil' ) );
		$this->assertSame( 'slide', $out['effect'] );

		$out = SWPS_Sanitizer::settings( array( 'effect' => 'fade' ) );
		$this->assertSame( 'fade', $out['effect'] );
	}

	public function test_sanitize_settings_fills_defaults() {
		$out = SWPS_Sanitizer::settings( array() );
		$this->assertArrayHasKey( 'autoplay', $out );
		$this->assertArrayHasKey( 'speed', $out );
		$this->assertArrayHasKey( 'effect', $out );
	}

	public function test_sanitize_slide_rejects_bad_type() {
		$out = SWPS_Sanitizer::slide( array( 'type' => 'malware' ) );
		$this->assertSame( 'image', $out['type'] );
	}

	public function test_sanitize_slide_regenerates_invalid_uuid() {
		$out = SWPS_Sanitizer::slide( array( 'id' => 'not-a-uuid' ) );
		$this->assertMatchesRegularExpression( '/^[0-9a-f-]{36}$/', $out['id'] );
	}

	public function test_sanitize_slide_forces_noopener_on_blank() {
		$out = SWPS_Sanitizer::slide( array(
			'type'        => 'image',
			'link_target' => '_blank',
			'link_rel'    => '',
		) );
		$this->assertStringContainsString( 'noopener', $out['link_rel'] );
		$this->assertStringContainsString( 'noreferrer', $out['link_rel'] );
	}
}

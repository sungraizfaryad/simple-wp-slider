<?php
/**
 * Tests for SWPS_Video URL parser.
 *
 * @package SimpleWPSlider
 */

/**
 * Validates YouTube + Vimeo URL parsing.
 */
class Test_SWPS_Video extends WP_UnitTestCase {

	public function test_parses_youtube_watch_url() {
		$out = SWPS_Video::parse( 'https://www.youtube.com/watch?v=dQw4w9WgXcQ' );
		$this->assertSame( 'youtube', $out['provider'] );
		$this->assertSame( 'dQw4w9WgXcQ', $out['id'] );
	}

	public function test_parses_youtube_short_url() {
		$out = SWPS_Video::parse( 'https://youtu.be/dQw4w9WgXcQ' );
		$this->assertSame( 'youtube', $out['provider'] );
		$this->assertSame( 'dQw4w9WgXcQ', $out['id'] );
	}

	public function test_parses_vimeo_url() {
		$out = SWPS_Video::parse( 'https://vimeo.com/76979871' );
		$this->assertSame( 'vimeo', $out['provider'] );
		$this->assertSame( '76979871', $out['id'] );
	}

	public function test_rejects_unknown_url() {
		$out = SWPS_Video::parse( 'https://example.com/foo' );
		$this->assertNull( $out );
	}
}

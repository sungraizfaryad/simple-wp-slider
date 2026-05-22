<?php
/**
 * Tests for SWPS_CPT.
 *
 * @package SimpleWPSlider
 */

/**
 * Verifies the swps_slider CPT registration.
 */
class Test_SWPS_CPT extends WP_UnitTestCase {

	public function test_post_type_registered() {
		SWPS_CPT::register();
		$this->assertTrue( post_type_exists( 'swps_slider' ) );
	}

	public function test_post_type_supports_only_title() {
		SWPS_CPT::register();
		$this->assertTrue( post_type_supports( 'swps_slider', 'title' ) );
		$this->assertFalse( post_type_supports( 'swps_slider', 'editor' ) );
		$this->assertFalse( post_type_supports( 'swps_slider', 'thumbnail' ) );
	}

	public function test_post_type_visible_in_rest() {
		SWPS_CPT::register();
		$pt = get_post_type_object( 'swps_slider' );
		$this->assertTrue( $pt->show_in_rest );
	}
}

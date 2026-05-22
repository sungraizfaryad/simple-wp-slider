<?php
/**
 * Tests for SWPS_Assets conditional enqueue.
 *
 * @package SimpleWPSlider
 */

/**
 * Validates that frontend assets enqueue only when a slider has been rendered
 * during the request (or when forced via filter).
 */
class Test_SWPS_Assets extends WP_UnitTestCase {

	public function set_up() {
		parent::set_up();
		// Reset enqueue state between tests.
		global $wp_scripts, $wp_styles;
		$wp_scripts = new WP_Scripts();
		$wp_styles  = new WP_Styles();
	}

	public function test_assets_not_enqueued_by_default() {
		SWPS_Assets::init();
		do_action( 'wp_enqueue_scripts' );
		do_action( 'wp_print_footer_scripts' );
		$this->assertFalse( wp_script_is( 'swps-frontend', 'enqueued' ) );
		$this->assertFalse( wp_style_is( 'swps-frontend', 'enqueued' ) );
	}

	public function test_assets_enqueued_when_marked_used() {
		SWPS_Assets::init();
		SWPS_Assets::mark_used();
		do_action( 'wp_enqueue_scripts' );
		do_action( 'wp_footer' );
		$this->assertTrue( wp_script_is( 'swps-frontend', 'enqueued' ) );
		$this->assertTrue( wp_style_is( 'swps-frontend', 'enqueued' ) );
	}

	public function test_force_enqueue_filter() {
		SWPS_Assets::init();
		add_filter( 'swps_force_enqueue', '__return_true' );
		do_action( 'wp_enqueue_scripts' );
		$this->assertTrue( wp_script_is( 'swps-frontend', 'enqueued' ) );
	}
}

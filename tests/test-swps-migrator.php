<?php
/**
 * Tests for SWPS_Migrator.
 *
 * @package SimpleWPSlider
 */

/**
 * Validates the v1 → v2 silent migration path.
 */
class Test_SWPS_Migrator extends WP_UnitTestCase {

	public function set_up() {
		parent::set_up();
		delete_option( 'wpss_basics' );
		delete_option( 'swps_db_version' );
		delete_option( 'swps_legacy_default_slider' );
	}

	public function test_no_legacy_option_does_nothing() {
		SWPS_Migrator::maybe_run();
		$found = get_posts( array( 'post_type' => 'swps_slider', 'fields' => 'ids' ) );
		$this->assertEmpty( $found );
	}

	public function test_creates_default_slider_post_from_legacy_urls() {
		update_option( 'wpss_basics', array(
			'wpss_image_upload' => array(
				'https://example.com/a.jpg',
				'https://example.com/b.jpg',
			),
		) );

		SWPS_Migrator::maybe_run();

		$found = get_posts( array(
			'post_type'      => 'swps_slider',
			'fields'         => 'ids',
			'meta_key'       => '_swps_migrated_from_v1',
			'meta_value'     => '1',
			'posts_per_page' => 1,
		) );
		$this->assertCount( 1, $found );

		$slides = get_post_meta( $found[0], '_swps_slides', true );
		$this->assertCount( 2, $slides );
		$this->assertSame( 'https://example.com/a.jpg', $slides[0]['_legacy_url'] );

		$this->assertSame( $found[0], (int) get_option( 'swps_legacy_default_slider' ) );
	}

	public function test_idempotent_second_run_deletes_legacy_option() {
		update_option( 'wpss_basics', array( 'wpss_image_upload' => array( 'https://example.com/a.jpg' ) ) );
		SWPS_Migrator::maybe_run();
		$this->assertNotEmpty( get_option( 'wpss_basics' ) );

		delete_option( 'swps_db_version' );
		SWPS_Migrator::maybe_run();
		$this->assertFalse( get_option( 'wpss_basics' ) );
	}

	public function test_single_string_image_upload_value_migrates() {
		update_option( 'wpss_basics', array( 'wpss_image_upload' => 'https://example.com/single.jpg' ) );
		SWPS_Migrator::maybe_run();
		$found = get_posts( array( 'post_type' => 'swps_slider', 'fields' => 'ids' ) );
		$this->assertCount( 1, $found );
		$slides = get_post_meta( $found[0], '_swps_slides', true );
		$this->assertCount( 1, $slides );
	}
}

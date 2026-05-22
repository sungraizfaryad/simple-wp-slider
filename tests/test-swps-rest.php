<?php
/**
 * Tests for SWPS_REST.
 *
 * @package SimpleWPSlider
 */

/**
 * REST endpoint tests for the swps/v1 namespace.
 */
class Test_SWPS_REST extends WP_UnitTestCase {

	private $admin_id;
	private $subscriber_id;

	public function set_up() {
		parent::set_up();
		$this->admin_id      = $this->factory()->user->create( array( 'role' => 'administrator' ) );
		$this->subscriber_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		do_action( 'rest_api_init' );
	}

	private function make_slider( $title = 'Test Slider' ) {
		return $this->factory()->post->create( array(
			'post_type'   => 'swps_slider',
			'post_status' => 'publish',
			'post_title'  => $title,
		) );
	}

	public function test_get_slider_unauth_returns_403() {
		$id = $this->make_slider();
		wp_set_current_user( $this->subscriber_id );
		$req = new WP_REST_Request( 'GET', '/swps/v1/sliders/' . $id );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 403, $res->get_status() );
	}

	public function test_get_slider_returns_payload() {
		$id = $this->make_slider( 'Hero' );
		wp_set_current_user( $this->admin_id );
		update_post_meta( $id, '_swps_slides', array() );
		update_post_meta( $id, '_swps_settings', SWPS_Sanitizer::default_settings() );

		$req = new WP_REST_Request( 'GET', '/swps/v1/sliders/' . $id );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$data = $res->get_data();
		$this->assertSame( $id, $data['id'] );
		$this->assertSame( 'Hero', $data['title'] );
		$this->assertSame( array(), $data['slides'] );
		$this->assertArrayHasKey( 'settings', $data );
	}

	public function test_get_slider_wrong_post_type_returns_404() {
		$page_id = $this->factory()->post->create( array( 'post_type' => 'page' ) );
		wp_set_current_user( $this->admin_id );
		$req = new WP_REST_Request( 'GET', '/swps/v1/sliders/' . $page_id );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 404, $res->get_status() );
	}
}

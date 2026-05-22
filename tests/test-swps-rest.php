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

	public function test_post_slider_saves_title_slides_settings() {
		$id = $this->make_slider();
		wp_set_current_user( $this->admin_id );

		$payload = array(
			'title'    => 'New Title',
			'slides'   => array(
				array( 'type' => 'image', 'attachment_id' => 1, 'alt' => 'Hi' ),
			),
			'settings' => array( 'autoplay' => true, 'speed' => 1234 ),
		);

		$req = new WP_REST_Request( 'POST', '/swps/v1/sliders/' . $id );
		$req->set_header( 'content-type', 'application/json' );
		$req->set_body( wp_json_encode( $payload ) );

		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );

		$this->assertSame( 'New Title', get_the_title( $id ) );
		$slides = get_post_meta( $id, '_swps_slides', true );
		$this->assertCount( 1, $slides );
		$this->assertSame( 'Hi', $slides[0]['alt'] );
		$settings = get_post_meta( $id, '_swps_settings', true );
		$this->assertTrue( $settings['autoplay'] );
		$this->assertSame( 1234, $settings['speed'] );
	}

	public function test_post_slider_rejects_invalid_payload_fields_silently() {
		$id = $this->make_slider();
		wp_set_current_user( $this->admin_id );

		$req = new WP_REST_Request( 'POST', '/swps/v1/sliders/' . $id );
		$req->set_header( 'content-type', 'application/json' );
		$req->set_body( wp_json_encode( array(
			'slides'   => array( array( 'type' => 'malware' ) ),
			'settings' => array( 'effect' => 'evil' ),
		) ) );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$data = $res->get_data();
		$this->assertSame( 'image', $data['slides'][0]['type'] );
		$this->assertSame( 'slide', $data['settings']['effect'] );
	}

	public function test_reorder_changes_slide_order() {
		$id = $this->make_slider();
		wp_set_current_user( $this->admin_id );

		$slides = array(
			array( 'id' => '11111111-1111-1111-1111-111111111111', 'type' => 'image' ),
			array( 'id' => '22222222-2222-2222-2222-222222222222', 'type' => 'image' ),
			array( 'id' => '33333333-3333-3333-3333-333333333333', 'type' => 'image' ),
		);
		update_post_meta( $id, '_swps_slides', SWPS_Sanitizer::slides( $slides ) );

		$req = new WP_REST_Request( 'POST', '/swps/v1/sliders/' . $id . '/reorder' );
		$req->set_header( 'content-type', 'application/json' );
		$req->set_body( wp_json_encode( array( 'order' => array(
			'33333333-3333-3333-3333-333333333333',
			'11111111-1111-1111-1111-111111111111',
			'22222222-2222-2222-2222-222222222222',
		) ) ) );

		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );

		$saved = get_post_meta( $id, '_swps_slides', true );
		$this->assertSame( '33333333-3333-3333-3333-333333333333', $saved[0]['id'] );
		$this->assertSame( '11111111-1111-1111-1111-111111111111', $saved[1]['id'] );
		$this->assertSame( '22222222-2222-2222-2222-222222222222', $saved[2]['id'] );
	}

	public function test_duplicate_creates_new_post() {
		$id = $this->make_slider( 'Original' );
		wp_set_current_user( $this->admin_id );
		update_post_meta( $id, '_swps_slides', SWPS_Sanitizer::slides( array(
			array( 'type' => 'image', 'attachment_id' => 5 ),
		) ) );

		$req = new WP_REST_Request( 'POST', '/swps/v1/sliders/' . $id . '/duplicate' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );

		$new_id = $res->get_data()['id'];
		$this->assertNotSame( $id, $new_id );
		$this->assertSame( 'Original (copy)', get_the_title( $new_id ) );
		$new_slides = get_post_meta( $new_id, '_swps_slides', true );
		$this->assertSame( 5, $new_slides[0]['attachment_id'] );
	}
}

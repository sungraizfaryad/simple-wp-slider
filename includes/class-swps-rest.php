<?php
/**
 * REST API for the swps_slider CPT.
 *
 * @package SimpleWPSlider
 */

defined( 'ABSPATH' ) || exit;

/**
 * Custom REST namespace swps/v1 with admin-gated routes.
 */
final class SWPS_REST {

	const REST_NAMESPACE = 'swps/v1';

	/**
	 * Register the rest_api_init hook.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	/**
	 * Register routes under swps/v1.
	 *
	 * @return void
	 */
	public static function register_routes() {
		register_rest_route(
			self::REST_NAMESPACE,
			'/sliders/(?P<id>\d+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( __CLASS__, 'get_slider' ),
					'permission_callback' => array( __CLASS__, 'can_edit_slider' ),
					'args'                => array(
						'id' => array(
							'validate_callback' => function ( $value ) {
								return is_numeric( $value );
							},
						),
					),
				),
			)
		);
	}

	/**
	 * Permission callback — checks the slider exists, has the right post type, and the user can edit it.
	 *
	 * @param WP_REST_Request $request Incoming request.
	 * @return bool|WP_Error
	 */
	public static function can_edit_slider( WP_REST_Request $request ) {
		$id = (int) $request['id'];
		if ( $id <= 0 ) {
			return new WP_Error( 'swps_invalid_id', __( 'Invalid slider ID.', 'simple-wp-slider' ), array( 'status' => 400 ) );
		}
		if ( SWPS_CPT::POST_TYPE !== get_post_type( $id ) ) {
			return new WP_Error( 'swps_not_found', __( 'Slider not found.', 'simple-wp-slider' ), array( 'status' => 404 ) );
		}
		if ( ! current_user_can( 'edit_post', $id ) ) {
			return new WP_Error( 'swps_forbidden', __( 'You cannot edit this slider.', 'simple-wp-slider' ), array( 'status' => 403 ) );
		}
		return true;
	}

	/**
	 * GET /swps/v1/sliders/{id}
	 *
	 * @param WP_REST_Request $request Incoming request.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function get_slider( WP_REST_Request $request ) {
		$id   = (int) $request['id'];
		$post = get_post( $id );
		if ( ! $post || SWPS_CPT::POST_TYPE !== $post->post_type ) {
			return new WP_Error( 'swps_not_found', __( 'Slider not found.', 'simple-wp-slider' ), array( 'status' => 404 ) );
		}

		$slides   = (array) get_post_meta( $id, SWPS_Meta::KEY_SLIDES, true );
		$settings = get_post_meta( $id, SWPS_Meta::KEY_SETTINGS, true );
		if ( ! is_array( $settings ) || empty( $settings ) ) {
			$settings = SWPS_Sanitizer::default_settings();
		} else {
			$settings = SWPS_Sanitizer::settings( $settings );
		}

		return rest_ensure_response(
			array(
				'id'       => $id,
				'title'    => get_the_title( $id ),
				'status'   => $post->post_status,
				'slides'   => SWPS_Sanitizer::slides( $slides ),
				'settings' => $settings,
			)
		);
	}
}

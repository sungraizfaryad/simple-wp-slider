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
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( __CLASS__, 'save_slider' ),
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

		register_rest_route(
			self::REST_NAMESPACE,
			'/sliders/(?P<id>\d+)/reorder',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( __CLASS__, 'reorder' ),
					'permission_callback' => array( __CLASS__, 'can_edit_slider' ),
					'args'                => array(
						'id'    => array(
							'validate_callback' => function ( $value ) {
																											return is_numeric( $value ); },
						),
						'order' => array( 'required' => true ),
					),
				),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE,
			'/oembed-resolve',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( __CLASS__, 'oembed_resolve' ),
					'permission_callback' => array( __CLASS__, 'can_edit_posts' ),
					'args'                => array(
						'url' => array(
							'required'          => true,
							'sanitize_callback' => 'esc_url_raw',
						),
					),
				),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE,
			'/sliders/(?P<id>\d+)/duplicate',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( __CLASS__, 'duplicate' ),
					'permission_callback' => array( __CLASS__, 'can_edit_slider' ),
					'args'                => array(
						'id' => array(
							'validate_callback' => function ( $value ) {
																											return is_numeric( $value ); },
						),
					),
				),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE,
			'/notices/dismiss',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( __CLASS__, 'dismiss_notice' ),
					'permission_callback' => array( __CLASS__, 'can_edit_posts' ),
					'args'                => array(
						'notice' => array(
							'required'          => true,
							'sanitize_callback' => 'sanitize_key',
						),
					),
				),
			)
		);
	}

	/**
	 * Permission callback for routes that just require edit_posts (no specific slider).
	 *
	 * @return bool
	 */
	public static function can_edit_posts() {
		return current_user_can( 'edit_posts' );
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

	/**
	 * POST /swps/v1/sliders/{id}
	 * Atomic save: updates title, slides meta, and settings meta in one round-trip.
	 * Returns the canonical saved state (via get_slider).
	 *
	 * @param WP_REST_Request $request Incoming request.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function save_slider( WP_REST_Request $request ) {
		$id     = (int) $request['id'];
		$params = $request->get_json_params();
		if ( ! is_array( $params ) ) {
			$params = $request->get_params();
		}

		if ( isset( $params['title'] ) ) {
			wp_update_post(
				array(
					'ID'         => $id,
					'post_title' => sanitize_text_field( wp_unslash( (string) $params['title'] ) ),
				)
			);
		}

		if ( isset( $params['slides'] ) ) {
			$clean_slides = SWPS_Sanitizer::slides( $params['slides'] );
			update_post_meta( $id, SWPS_Meta::KEY_SLIDES, $clean_slides );
		}

		if ( isset( $params['settings'] ) ) {
			$clean_settings = SWPS_Sanitizer::settings( $params['settings'] );
			update_post_meta( $id, SWPS_Meta::KEY_SETTINGS, $clean_settings );
		}

		update_post_meta( $id, SWPS_Meta::KEY_SCHEMA_VER, 2 );

		return self::get_slider( $request );
	}

	/**
	 * POST /swps/v1/sliders/{id}/reorder
	 * Reorders slides by UUID. Leftover slides not in the order array are appended.
	 *
	 * @param WP_REST_Request $request Incoming request.
	 * @return WP_REST_Response
	 */
	public static function reorder( WP_REST_Request $request ) {
		$id     = (int) $request['id'];
		$params = $request->get_json_params();
		$order  = isset( $params['order'] ) && is_array( $params['order'] ) ? $params['order'] : array();

		$slides = (array) get_post_meta( $id, SWPS_Meta::KEY_SLIDES, true );
		$byid   = array();
		foreach ( $slides as $s ) {
			if ( isset( $s['id'] ) ) {
				$byid[ (string) $s['id'] ] = $s;
			}
		}

		$reordered = array();
		foreach ( $order as $uuid ) {
			$uuid = (string) $uuid;
			if ( isset( $byid[ $uuid ] ) ) {
				$reordered[] = $byid[ $uuid ];
				unset( $byid[ $uuid ] );
			}
		}
		foreach ( $byid as $leftover ) {
			$reordered[] = $leftover;
		}

		update_post_meta( $id, SWPS_Meta::KEY_SLIDES, $reordered );

		return rest_ensure_response(
			array(
				'id'    => $id,
				'order' => array_column( $reordered, 'id' ),
			)
		);
	}

	/**
	 * POST /swps/v1/sliders/{id}/duplicate
	 * Clones a slider; regenerates slide UUIDs on the copy.
	 *
	 * @param WP_REST_Request $request Incoming request.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function duplicate( WP_REST_Request $request ) {
		$src_id = (int) $request['id'];
		$title  = get_the_title( $src_id );

		$new_id = wp_insert_post(
			array(
				'post_type'   => SWPS_CPT::POST_TYPE,
				'post_status' => 'draft',
				/* translators: %s: source slider title */
				'post_title'  => sprintf( __( '%s (copy)', 'simple-wp-slider' ), $title ),
			),
			true
		);

		if ( is_wp_error( $new_id ) ) {
			return $new_id;
		}

		$slides = (array) get_post_meta( $src_id, SWPS_Meta::KEY_SLIDES, true );
		foreach ( $slides as &$s ) {
			$s['id'] = wp_generate_uuid4();
		}
		unset( $s );

		update_post_meta( $new_id, SWPS_Meta::KEY_SLIDES, $slides );
		update_post_meta( $new_id, SWPS_Meta::KEY_SETTINGS, get_post_meta( $src_id, SWPS_Meta::KEY_SETTINGS, true ) );
		update_post_meta( $new_id, SWPS_Meta::KEY_SCHEMA_VER, 2 );

		return rest_ensure_response(
			array(
				'id'     => $new_id,
				'title'  => get_the_title( $new_id ),
				'status' => 'draft',
			)
		);
	}

	/**
	 * POST /swps/v1/notices/dismiss
	 * Records a notice as dismissed for the current user.
	 *
	 * @param WP_REST_Request $request Incoming request.
	 * @return WP_REST_Response
	 */
	public static function dismiss_notice( WP_REST_Request $request ) {
		$key  = (string) $request['notice'];
		$user = get_current_user_id();
		$list = (array) get_user_meta( $user, 'swps_notices', true );
		if ( ! in_array( $key, $list, true ) ) {
			$list[] = $key;
			update_user_meta( $user, 'swps_notices', $list );
		}
		return rest_ensure_response( array( 'dismissed' => $list ) );
	}

	/**
	 * GET /swps/v1/oembed-resolve?url=...
	 * Validates a YouTube/Vimeo URL and returns provider+id+thumbnail_url.
	 *
	 * @param WP_REST_Request $request Incoming request.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function oembed_resolve( WP_REST_Request $request ) {
		$url    = (string) $request['url'];
		$parsed = SWPS_Video::parse( $url );
		if ( ! $parsed ) {
			return new WP_Error( 'swps_unsupported_url', __( 'Unsupported video URL.', 'simple-wp-slider' ), array( 'status' => 400 ) );
		}

		$thumb = SWPS_Video::thumbnail_url( $parsed['provider'], $parsed['id'] );
		if ( '' === $thumb && 'vimeo' === $parsed['provider'] ) {
			$resp = wp_remote_get( 'https://vimeo.com/api/oembed.json?url=' . rawurlencode( $url ), array( 'timeout' => 5 ) );
			if ( ! is_wp_error( $resp ) && 200 === wp_remote_retrieve_response_code( $resp ) ) {
				$body = json_decode( wp_remote_retrieve_body( $resp ), true );
				if ( ! empty( $body['thumbnail_url'] ) ) {
					$thumb = esc_url_raw( $body['thumbnail_url'] );
				}
			}
		}

		return rest_ensure_response(
			array(
				'provider'      => $parsed['provider'],
				'id'            => $parsed['id'],
				'thumbnail_url' => $thumb,
			)
		);
	}
}

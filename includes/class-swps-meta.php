<?php
/**
 * Post meta registration for the swps_slider CPT.
 *
 * @package SimpleWPSlider
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers slides, settings, and schema-version meta with REST exposure
 * and an auth callback that defers to edit_post on the slider ID.
 */
final class SWPS_Meta {

	const POST_TYPE       = 'swps_slider';
	const KEY_SLIDES      = '_swps_slides';
	const KEY_SETTINGS    = '_swps_settings';
	const KEY_SCHEMA_VER  = '_swps_schema_version';
	const KEY_MIGRATED_V1 = '_swps_migrated_from_v1';

	/**
	 * Register all post meta keys. Hooked to `init`.
	 *
	 * @return void
	 */
	public static function register() {
		register_post_meta(
			self::POST_TYPE,
			self::KEY_SLIDES,
			array(
				'type'              => 'array',
				'single'            => true,
				'show_in_rest'      => array(
					'schema' => array(
						'type'  => 'array',
						'items' => array( 'type' => 'object' ),
					),
				),
				'sanitize_callback' => array( 'SWPS_Sanitizer', 'slides' ),
				'auth_callback'     => array( __CLASS__, 'auth_callback' ),
				'default'           => array(),
			)
		);

		register_post_meta(
			self::POST_TYPE,
			self::KEY_SETTINGS,
			array(
				'type'              => 'object',
				'single'            => true,
				'show_in_rest'      => array(
					'schema' => array(
						'type'                 => 'object',
						'additionalProperties' => true,
					),
				),
				'sanitize_callback' => array( 'SWPS_Sanitizer', 'settings' ),
				'auth_callback'     => array( __CLASS__, 'auth_callback' ),
				'default'           => SWPS_Sanitizer::default_settings(),
			)
		);

		register_post_meta(
			self::POST_TYPE,
			self::KEY_SCHEMA_VER,
			array(
				'type'         => 'integer',
				'single'       => true,
				'show_in_rest' => true,
				'default'      => 2,
			)
		);
	}

	/**
	 * Auth callback for the protected meta keys.
	 *
	 * @param bool   $allowed   Existing decision.
	 * @param string $meta_key  Meta key being checked.
	 * @param int    $object_id Post ID.
	 * @return bool
	 */
	public static function auth_callback( $allowed, $meta_key, $object_id ) {
		return current_user_can( 'edit_post', (int) $object_id );
	}
}

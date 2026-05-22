<?php
/**
 * V1 to v2 migration.
 *
 * @package SimpleWPSlider
 */

defined( 'ABSPATH' ) || exit;

/**
 * Detects the legacy `wpss_basics` option and converts it into a Default
 * Slider CPT post. Idempotent — safe to invoke on every plugins_loaded.
 */
final class SWPS_Migrator {

	const OPT_DB_VERSION = 'swps_db_version';
	const OPT_LEGACY     = 'wpss_basics';
	const OPT_DEFAULT_ID = 'swps_legacy_default_slider';

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'plugins_loaded', array( __CLASS__, 'maybe_run' ), 5 );
	}

	/**
	 * Run the migration if not already at the current SWPS_VERSION.
	 *
	 * @return void
	 */
	public static function maybe_run() {
		$installed = get_option( self::OPT_DB_VERSION, '0' );
		if ( version_compare( $installed, SWPS_VERSION, '>=' ) ) {
			return;
		}
		if ( version_compare( $installed, '2.0.0', '<' ) ) {
			self::migrate_to_2_0();
		}
		update_option( self::OPT_DB_VERSION, SWPS_VERSION, false );
	}

	/**
	 * V1 to v2 migration body.
	 *
	 * @return void
	 */
	private static function migrate_to_2_0() {
		$old = get_option( self::OPT_LEGACY );
		if ( empty( $old ) || empty( $old['wpss_image_upload'] ) ) {
			return;
		}

		// Idempotency guard.
		$existing = get_posts(
			array(
				'post_type'      => SWPS_CPT::POST_TYPE,
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'   => SWPS_Meta::KEY_MIGRATED_V1,
						'value' => '1',
					),
				),
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);
		if ( ! empty( $existing ) ) {
			delete_option( self::OPT_LEGACY );
			return;
		}

		$urls = $old['wpss_image_upload'];
		if ( is_string( $urls ) ) {
			$urls = array( $urls );
		}
		$urls = array_filter( array_map( 'esc_url_raw', (array) $urls ) );
		if ( empty( $urls ) ) {
			return;
		}

		$slides = array();
		foreach ( $urls as $url ) {
			$att_id = function_exists( 'attachment_url_to_postid' ) ? (int) attachment_url_to_postid( $url ) : 0;
			$alt    = $att_id ? (string) get_post_meta( $att_id, '_wp_attachment_image_alt', true ) : '';
			$slide  = SWPS_Sanitizer::slide(
				array(
					'type'          => 'image',
					'attachment_id' => $att_id,
					'alt'           => $alt,
				)
			);
			if ( 0 === $att_id ) {
				$slide['_legacy_url'] = esc_url_raw( $url );
			}
			$slides[] = $slide;
		}

		$post_id = wp_insert_post(
			array(
				'post_type'   => SWPS_CPT::POST_TYPE,
				'post_status' => 'publish',
				'post_title'  => __( 'Default Slider', 'simple-wp-slider' ),
			),
			true
		);

		if ( is_wp_error( $post_id ) ) {
			return;
		}

		$settings             = SWPS_Sanitizer::default_settings();
		$settings['autoplay'] = false;
		$settings['speed']    = 300;
		$settings['dots']     = true;
		$settings['arrows']   = true;
		$settings['loop']     = true;

		update_post_meta( $post_id, SWPS_Meta::KEY_SLIDES, $slides );
		update_post_meta( $post_id, SWPS_Meta::KEY_SETTINGS, $settings );
		update_post_meta( $post_id, SWPS_Meta::KEY_SCHEMA_VER, 2 );
		update_post_meta( $post_id, SWPS_Meta::KEY_MIGRATED_V1, '1' );

		update_option( self::OPT_DEFAULT_ID, (int) $post_id, false );

		do_action( 'swps_after_migration', $post_id ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
	}
}

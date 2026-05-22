<?php
/**
 * Admin host: hides unused metaboxes on the swps_slider edit screen.
 *
 * @package SimpleWPSlider
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin glue for the Sliders CPT. Slide manager React metabox is mounted in Task 25.
 */
final class SWPS_Admin {

	/**
	 * Register admin hooks.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'remove_unused_metaboxes' ) );
	}

	/**
	 * Remove default metaboxes from the swps_slider edit screen.
	 *
	 * Even though `supports => array('title')` already excludes most of these,
	 * some default metaboxes (slug, author, custom fields) can still appear.
	 *
	 * @return void
	 */
	public static function remove_unused_metaboxes() {
		$hidden = array(
			'slugdiv',
			'authordiv',
			'postcustom',
			'commentstatusdiv',
			'commentsdiv',
			'trackbacksdiv',
			'revisionsdiv',
		);
		foreach ( $hidden as $box ) {
			remove_meta_box( $box, SWPS_CPT::POST_TYPE, 'normal' );
			remove_meta_box( $box, SWPS_CPT::POST_TYPE, 'side' );
			remove_meta_box( $box, SWPS_CPT::POST_TYPE, 'advanced' );
		}
	}
}

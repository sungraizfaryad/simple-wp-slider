<?php
/**
 * Internationalization loader.
 *
 * @package SimpleWPSlider
 */

defined( 'ABSPATH' ) || exit;

/**
 * Loads the text domain for translations.
 */
final class SWPS_I18n {

	/**
	 * Load the plugin text domain. Hooked to plugins_loaded.
	 *
	 * @return void
	 */
	public static function load() {
		load_plugin_textdomain( 'simple-wp-slider', false, dirname( SWPS_BASENAME ) . '/languages' );
	}
}

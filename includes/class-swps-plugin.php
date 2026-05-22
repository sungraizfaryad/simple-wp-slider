<?php
/**
 * Plugin bootstrap singleton.
 *
 * @package SimpleWPSlider
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main plugin class. Instantiates subsystems on plugins_loaded.
 */
final class SWPS_Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var SWPS_Plugin|null
	 */
	private static $instance = null;

	/**
	 * Return (or create) the singleton instance.
	 *
	 * @return SWPS_Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
			self::$instance->boot();
		}
		return self::$instance;
	}

	/**
	 * Private constructor — use instance() instead.
	 */
	private function __construct() {}

	/**
	 * Prevent cloning of the singleton.
	 */
	private function __clone() {}

	/**
	 * Prevent unserialization of the singleton.
	 *
	 * @throws \LogicException Always.
	 */
	public function __wakeup() {
		throw new \LogicException( 'SWPS_Plugin singleton cannot be unserialized.' );
	}

	/**
	 * Boot subsystems. Subsystems are wired here in later tasks.
	 *
	 * @return void
	 */
	private function boot() {
		require_once SWPS_DIR . 'includes/class-swps-sanitizer.php';
		require_once SWPS_DIR . 'includes/class-swps-cpt.php';
		require_once SWPS_DIR . 'includes/class-swps-meta.php';
		require_once SWPS_DIR . 'includes/class-swps-i18n.php';

		add_action( 'plugins_loaded', array( 'SWPS_I18n', 'load' ) );
		add_action( 'init', array( 'SWPS_CPT', 'register' ) );
		add_action( 'init', array( 'SWPS_Meta', 'register' ) );

		if ( is_admin() ) {
			require_once SWPS_DIR . 'admin/class-swps-admin.php';
			SWPS_Admin::init();
		}

		require_once SWPS_DIR . 'includes/class-swps-video.php';
		require_once SWPS_DIR . 'includes/class-swps-rest.php';
		SWPS_REST::init();
		require_once SWPS_DIR . 'includes/class-swps-renderer.php';
		require_once SWPS_DIR . 'includes/class-swps-shortcode.php';
		SWPS_Shortcode::init();
	}
}

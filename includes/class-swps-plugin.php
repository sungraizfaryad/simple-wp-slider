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
		// Subsystems wire themselves via add_action in later tasks.
	}
}

<?php
/**
 * Conditional frontend enqueue for the Swiper bundle.
 *
 * @package SimpleWPSlider
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enqueues frontend assets only when a slider has been rendered during the
 * current request, or unconditionally when the swps_force_enqueue filter
 * returns true.
 */
final class SWPS_Assets {

	/**
	 * Whether a slider was rendered this request.
	 *
	 * @var bool
	 */
	private static $used = false;

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public static function init() {
		self::$used = false;
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'maybe_enqueue_frontend' ), 20 );
		add_action( 'wp_footer', array( __CLASS__, 'maybe_enqueue_frontend' ), 1 );
	}

	/**
	 * Flag the current request as needing frontend assets.
	 *
	 * Called from SWPS_Renderer::render() when a slider successfully renders.
	 *
	 * @return void
	 */
	public static function mark_used() {
		self::$used = true;
		// If wp_enqueue_scripts has already fired, enqueue right away.
		if ( did_action( 'wp_enqueue_scripts' ) ) {
			self::enqueue_frontend();
		}
	}

	/**
	 * Enqueue check hook.
	 *
	 * @return void
	 */
	public static function maybe_enqueue_frontend() {
		if ( ! self::$used && ! apply_filters( 'swps_force_enqueue', false ) ) {
			return;
		}
		self::enqueue_frontend();
	}

	/**
	 * Perform the actual enqueue.
	 *
	 * @return void
	 */
	private static function enqueue_frontend() {
		if ( wp_script_is( 'swps-frontend', 'enqueued' ) ) {
			return;
		}

		$asset_file = SWPS_DIR . 'assets/dist/frontend/index.asset.php';
		$asset      = file_exists( $asset_file )
			? require $asset_file
			: array(
				'dependencies' => array(),
				'version'      => SWPS_VERSION,
			);

		wp_enqueue_style(
			'swps-frontend',
			SWPS_URL . 'assets/dist/frontend/index.css',
			array(),
			$asset['version']
		);
		wp_enqueue_script(
			'swps-frontend',
			SWPS_URL . 'assets/dist/frontend/index.js',
			$asset['dependencies'],
			$asset['version'],
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);
		wp_set_script_translations( 'swps-frontend', 'simple-wp-slider', SWPS_DIR . 'languages' );
	}
}

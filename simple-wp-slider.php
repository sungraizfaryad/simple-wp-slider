<?php
/**
 * Plugin Name:       Simple WP Slider
 * Plugin URI:        https://wordpress.org/plugins/simple-wp-slider
 * Description:       Multi-slider WordPress plugin with shortcode, Gutenberg block, and Swiper-powered frontend.
 * Version:           2.0.0-dev
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Sungraiz Faryad
 * Author URI:        https://www.sungraizfaryad.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       simple-wp-slider
 * Domain Path:       /languages
 *
 * @package SimpleWPSlider
 */

defined( 'ABSPATH' ) || exit;

define( 'SWPS_VERSION', '2.0.0-dev' );
define( 'SWPS_FILE', __FILE__ );
define( 'SWPS_DIR', plugin_dir_path( __FILE__ ) );
define( 'SWPS_URL', plugin_dir_url( __FILE__ ) );
define( 'SWPS_BASENAME', plugin_basename( __FILE__ ) );

require_once SWPS_DIR . 'includes/class-swps-plugin.php';

add_action( 'plugins_loaded', array( 'SWPS_Plugin', 'instance' ), 10 );

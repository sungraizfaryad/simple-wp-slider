<?php

/**
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              www.sungraizfaryad.com
 * @since             1.0.0
 * @package           Simple_WP_Slider
 *
 * @wordpress-plugin
 * Plugin Name:       Simple WP Slider
 * Plugin URI:        https://wordpress.org/plugins/simple-wp-slider
 * Description:       This is a purpose oriented plugin which show slider using shortcode.
 * Version:           1.0.1
 * Author:            Sungraiz Faryad
 * Author URI:        www.sungraizfaryad.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:      simple-wp-slider
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'SIMPLE_WP_SLIDER_VERSION', '1.0.1' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-simple-wp-slider-activator.php
 */
function activate_simple_wp_slider() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-simple-wp-slider-activator.php';
	Simple_WP_Slider_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-simple-wp-slider-deactivator.php
 */
function deactivate_simple_wp_slider() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-simple-wp-slider-deactivator.php';
	Simple_WP_Slider_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_simple_wp_slider' );
register_deactivation_hook( __FILE__, 'deactivate_simple_wp_slider' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-simple-wp-slider.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_simple_wp_slider() {

	$plugin = new Simple_WP_Slider();
	$plugin->run();

}

run_simple_wp_slider();

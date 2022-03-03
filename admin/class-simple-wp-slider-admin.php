<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       www.sungraizfaryad.com
 * @since      1.0.0
 *
 * @package    Simple_WP_Slider
 * @subpackage Simple_WP_Slider/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Simple_WP_Slider
 * @subpackage Simple_WP_Slider/admin
 * @author     Sungraiz Faryad <sungraiz@gmail.com>
 */
class Simple_WP_Slider_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->load_dependencies();
		$this->wp_ss_register_shortcode();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Simple_WP_Slider_Settings. Orchestrates the settings of the plugin.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/simple-wp-slider-settings.php';

	}


	/**
	 * Load the shortcode for this plugin.
	 *
	 * - Simple_WP_Slider_Settings. Orchestrates the settings of the plugin.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	public function wp_ss_shortcode() {

		$options = get_option( 'wpss_basics' );

		if ( isset( $options['wpss_image_upload'] ) && ! empty( $options['wpss_image_upload'] ) && is_array( $options['wpss_image_upload'] ) ) {

			$slides = $options['wpss_image_upload'];
			wp_enqueue_style( 'slick', plugin_dir_url( __FILE__ ) . '../libs/slick/slick.css', array(), '1.8.0', 'all' );
			wp_enqueue_script( 'slick', plugin_dir_url( __FILE__ ) . '../libs/slick/slick.js', array( 'jquery' ), '1.8.0', false );

			$html = '<div class="wpss-slide-show">';

			foreach ( $slides as $slide ) {
				$html .= '<div><img src="' . esc_url( $slide ) . '" alt="Image"></div>';
			}

			$html .= '</div>';

			return $html;
		}


	}

	public function wp_ss_register_shortcode() {
		add_shortcode( 'simplewpslider', array( $this, 'wp_ss_shortcode' ) );
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Simple_WP_Slider_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Simple_WP_Slider_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/simple-wp-slider-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Simple_WP_Slider_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Simple_WP_Slider_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/simple-wp-slider-admin.js', array( 'jquery' ), $this->version, true );
		wp_enqueue_script( 'jquery-ui-sortable' );
	}
}


<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       www.sungraizfaryad.com
 * @since      1.0.0
 *
 * @package    Simple_WP_Slider
 * @subpackage Simple_WP_Slider/admin/partials
 */

/**
 *  Booking Settings Api.
 */
class Simple_WP_Slider_Settings {

	/**
	 * All Settings Saved.
	 *
	 * @var $settings_api array settings.
	 */
	private $settings_api;

	/**
	 * Call methods and variables on init.
	 */
	public function __construct() {
		$this->settings_api = new Simple_WP_Slider_Settings_API();
	}

	/**
	 * Init Booking Settings.
	 */
	public function wpss_settings_init() {

		// set the settings.
		$this->settings_api->set_sections( $this->get_settings_sections() );
		$this->settings_api->set_fields( $this->get_settings_fields() );

		// initialize settings.
		$this->settings_api->admin_init();
	}

	/**
	 * Adding options page under Settings.
	 */
	public function settings_menu() {
		add_submenu_page(
			'options-general.php',
			esc_html__( 'Simple WP Slider', 'simple-wp-slider' ),
			esc_html__( 'Simple WP Slider', 'simple-wp-slider' ),
			'manage_options',
			'wpss_settings_page',
			array( $this, 'wpss_settings_page' ),
		);
	}


	/**
	 * Returns all Sections for settings
	 *
	 * @return array section fields
	 */
	private function get_settings_sections() {
		$sections[] = array(
			'id'    => 'wpss_basics',
			'title' => esc_html__( 'Simple WP Slider Settings', 'simple-wp-slider' ),
		);

		return $sections;
	}

	/**
	 * Returns all the settings fields
	 *
	 * @return array settings fields
	 */
	private function get_settings_fields() {

		$settings_fields['wpss_basics'] = array(
			array(
				'name'  => 'wpss_image_upload',
				'label' => esc_html__( 'Upload Image', 'simple-wp-slider' ),
				'desc'  => esc_html__( 'Use the button to upload an image', 'simple-wp-slider' ),
				'type'  => 'file',
			),
		);

		return $settings_fields;
	}


	/**
	 * Returns all setting page
	 */
	public function wpss_settings_page() {
		echo wp_kses_post( '<div class="wrap">' );
		$this->settings_api->show_navigation();
		echo wp_kses_post( '<div id="rtu-settings-wrapper">' );
		$this->settings_api->show_forms();
		echo wp_kses_post( '</div>' );
		echo wp_kses_post( '</div>' );
	}

	/**
	 * Get all the pages
	 *
	 * @return array page names with key value pairs
	 */
	public function get_pages() {
		$pages         = get_pages();
		$pages_options = array();
		if ( $pages ) {
			foreach ( $pages as $page ) {
				$pages_options[ $page->ID ] = $page->post_title;
			}
		}

		return $pages_options;
	}
}

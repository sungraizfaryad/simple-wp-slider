<?php
defined( 'ABSPATH' ) || exit;

final class SWPS_Plugin {

    private static $instance = null;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
            self::$instance->boot();
        }
        return self::$instance;
    }

    private function __construct() {}

    private function boot() {
        // Subsystems wire themselves via add_action in later tasks.
    }
}

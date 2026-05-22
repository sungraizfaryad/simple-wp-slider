<?php
$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
    $_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}
require_once $_tests_dir . '/includes/functions.php';

function _swps_manually_load_plugin() {
    require dirname( __DIR__ ) . '/simple-wp-slider.php';
}
tests_add_filter( 'muplugins_loaded', '_swps_manually_load_plugin' );

require $_tests_dir . '/includes/bootstrap.php';

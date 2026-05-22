<?php
class Test_SWPS_Plugin extends WP_UnitTestCase {

    public function test_instance_returns_singleton() {
        $a = SWPS_Plugin::instance();
        $b = SWPS_Plugin::instance();
        $this->assertSame( $a, $b );
    }

    public function test_swps_version_defined() {
        $this->assertTrue( defined( 'SWPS_VERSION' ) );
        $this->assertNotEmpty( SWPS_VERSION );
    }
}

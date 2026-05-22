<?php
/**
 * Fired when the plugin is deleted via WP admin.
 *
 * Removes:
 *   - all swps_slider CPT posts (and their meta + revisions)
 *   - plugin options + legacy wpss_basics safety net
 *   - per-user notice dismiss meta
 *   - stray post meta with our _swps_ prefix
 *   - transients with the swps_ prefix
 *
 * @package SimpleWPSlider
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

// 1. Delete all slider CPT posts (and their meta + revisions).
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- uninstall cleanup
$slider_ids = get_posts(
	array(
		'post_type'      => 'swps_slider',
		'post_status'    => 'any',
		'posts_per_page' => -1,
		'fields'         => 'ids',
	)
);
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- uninstall cleanup
foreach ( $slider_ids as $sid ) {
	wp_delete_post( $sid, true );
}

// 2. Options.
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- uninstall cleanup
$options = array(
	'swps_settings',
	'swps_db_version',
	'swps_legacy_default_slider',
	'wpss_basics',
);
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- uninstall cleanup
foreach ( $options as $opt ) {
	delete_option( $opt );
}

// 3. User meta — notice dismiss flags.
delete_metadata( 'user', 0, 'swps_notices', '', true );

// 4. Stray post meta with our _swps_ prefix.
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- uninstall cleanup
$like = $wpdb->esc_like( '_swps_' ) . '%';
// phpcs:disable WordPress.DB.DirectDatabaseQuery -- uninstall cleanup
// phpcs:disable WordPress.DB.SlowDBQuery -- uninstall cleanup
$wpdb->query(
	$wpdb->prepare(
		"DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE %s",
		$like
	)
);

// 5. Transients with our prefix.
$wpdb->query(
	$wpdb->prepare(
		"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
		'_transient_swps_%',
		'_transient_timeout_swps_%'
	)
);
// phpcs:enable WordPress.DB.SlowDBQuery
// phpcs:enable WordPress.DB.DirectDatabaseQuery

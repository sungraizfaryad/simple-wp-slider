<?php
/**
 * Admin host: slide-manager React metabox + enqueue + migration notice.
 *
 * @package SimpleWPSlider
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin glue. Registers the Slides metabox host, enqueues the React
 * admin bundle on the slider edit screen, and shows the one-time
 * v1→v2 migration notice (dismissable via /swps/v1/notices/dismiss).
 */
final class SWPS_Admin {

	/**
	 * Register admin hooks.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'remove_unused_metaboxes' ) );
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_slides_metabox' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
		add_action( 'admin_notices', array( __CLASS__, 'render_migration_notice' ) );
	}

	/**
	 * Hide default metaboxes on the swps_slider edit screen.
	 *
	 * @return void
	 */
	public static function remove_unused_metaboxes() {
		$hidden = array(
			'slugdiv',
			'authordiv',
			'postcustom',
			'commentstatusdiv',
			'commentsdiv',
			'trackbacksdiv',
			'revisionsdiv',
		);
		foreach ( $hidden as $box ) {
			remove_meta_box( $box, SWPS_CPT::POST_TYPE, 'normal' );
			remove_meta_box( $box, SWPS_CPT::POST_TYPE, 'side' );
			remove_meta_box( $box, SWPS_CPT::POST_TYPE, 'advanced' );
		}
	}

	/**
	 * Register the Slides metabox.
	 *
	 * @return void
	 */
	public static function add_slides_metabox() {
		add_meta_box(
			'swps_slides',
			__( 'Slides', 'simple-wp-slider' ),
			array( __CLASS__, 'render_slides_metabox' ),
			SWPS_CPT::POST_TYPE,
			'normal',
			'high'
		);
	}

	/**
	 * Render the Slides metabox shell (React mount point).
	 *
	 * @param WP_Post $post Current post.
	 * @return void
	 */
	public static function render_slides_metabox( $post ) {
		printf(
			'<div id="swps-admin-root" data-slider-id="%d"></div>',
			(int) $post->ID
		);
	}

	/**
	 * Enqueue the admin bundle + media library on the slider edit screen only.
	 *
	 * @param string $hook Current admin page.
	 * @return void
	 */
	public static function enqueue( $hook ) {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || SWPS_CPT::POST_TYPE !== $screen->post_type ) {
			return;
		}
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		wp_enqueue_media();

		$asset_file = SWPS_DIR . 'assets/dist/admin/index.asset.php';
		$asset      = file_exists( $asset_file )
			? require $asset_file
			: array(
				'dependencies' => array(),
				'version'      => SWPS_VERSION,
			);

		wp_enqueue_style(
			'swps-admin',
			SWPS_URL . 'assets/dist/admin/index.css',
			array( 'wp-components' ),
			$asset['version']
		);
		// The webpack/wp-scripts pipeline outputs an additional style-index.css from scss imports.
		if ( file_exists( SWPS_DIR . 'assets/dist/admin/style-index.css' ) ) {
			wp_enqueue_style(
				'swps-admin-extra',
				SWPS_URL . 'assets/dist/admin/style-index.css',
				array( 'swps-admin' ),
				$asset['version']
			);
		}
		wp_enqueue_script(
			'swps-admin',
			SWPS_URL . 'assets/dist/admin/index.js',
			$asset['dependencies'],
			$asset['version'],
			true
		);
		wp_set_script_translations( 'swps-admin', 'simple-wp-slider', SWPS_DIR . 'languages' );
	}

	/**
	 * Render the one-time v1→v2 migration notice, dismissable per-user via REST.
	 *
	 * @return void
	 */
	public static function render_migration_notice() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}
		$dismissed = (array) get_user_meta( get_current_user_id(), 'swps_notices', true );
		if ( in_array( 'migration_v2', $dismissed, true ) ) {
			return;
		}
		$migrated_id = (int) get_option( 'swps_legacy_default_slider', 0 );
		if ( ! $migrated_id ) {
			return;
		}
		$edit_url = get_edit_post_link( $migrated_id );
		?>
		<div class="notice notice-success is-dismissible" data-swps-notice="migration_v2">
			<p>
				<strong><?php esc_html_e( 'Simple WP Slider 2.0', 'simple-wp-slider' ); ?>:</strong>
				<?php esc_html_e( 'Your existing images were imported into a new slider called "Default Slider". Your [simplewpslider] shortcode keeps working.', 'simple-wp-slider' ); ?>
				<?php if ( $edit_url ) : ?>
					<a href="<?php echo esc_url( $edit_url ); ?>"><?php esc_html_e( 'Open Default Slider', 'simple-wp-slider' ); ?></a>
				<?php endif; ?>
			</p>
		</div>
		<script>
		( function () {
			document.addEventListener( 'click', function ( e ) {
				var btn = e.target.closest( '.notice[data-swps-notice] .notice-dismiss' );
				if ( ! btn ) { return; }
				var notice = btn.closest( '.notice' );
				if ( window.wp && window.wp.apiFetch ) {
					window.wp.apiFetch( {
						path: '/swps/v1/notices/dismiss',
						method: 'POST',
						data: { notice: notice.dataset.swpsNotice },
					} );
				}
			} );
		} )();
		</script>
		<?php
	}
}

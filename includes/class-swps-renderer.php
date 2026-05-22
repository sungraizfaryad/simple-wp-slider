<?php
/**
 * Frontend HTML renderer for a slider post.
 *
 * @package SimpleWPSlider
 */

defined( 'ABSPATH' ) || exit;

/**
 * Builds Swiper-compatible HTML for a single slider post.
 */
final class SWPS_Renderer {

	/**
	 * Singleton instance.
	 *
	 * @var SWPS_Renderer|null
	 */
	private static $instance = null;

	/**
	 * Return the singleton.
	 *
	 * @return SWPS_Renderer
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Render a slider by post ID.
	 *
	 * @param int $slider_id swps_slider post ID.
	 * @return string HTML, empty string when nothing to render.
	 */
	public function render( $slider_id ) {
		$slider_id = (int) $slider_id;
		if ( $slider_id <= 0 || get_post_type( $slider_id ) !== SWPS_CPT::POST_TYPE ) {
			return '';
		}
		$post = get_post( $slider_id );
		if ( 'publish' !== $post->post_status && ! is_admin() ) {
			return '';
		}

		$raw_slides = get_post_meta( $slider_id, SWPS_Meta::KEY_SLIDES, true );
		$slides     = is_array( $raw_slides ) ? $raw_slides : array();
		$settings   = get_post_meta( $slider_id, SWPS_Meta::KEY_SETTINGS, true );
		if ( ! is_array( $settings ) || empty( $settings ) ) {
			$settings = SWPS_Sanitizer::default_settings();
		} else {
			$settings = SWPS_Sanitizer::settings( $settings );
		}

		if ( empty( $slides ) ) {
			return '';
		}

		if ( class_exists( 'SWPS_Assets' ) ) {
			SWPS_Assets::mark_used();
		}

		$total = count( $slides );
		$title = get_the_title( $slider_id );

		ob_start();
		?>
		<div class="swps-slider"
			data-swps-id="<?php echo esc_attr( (string) $slider_id ); ?>"
			data-swps-aspect="<?php echo esc_attr( (string) $settings['aspect_ratio'] ); ?>"
			data-swps-config="<?php echo esc_attr( (string) wp_json_encode( $settings ) ); ?>"
			role="region"
			aria-roledescription="carousel"
			aria-label="<?php echo esc_attr( $title ); ?>">
			<div class="swiper">
				<div class="swiper-wrapper">
					<?php
					$i = 0;
					foreach ( $slides as $slide ) :
						++$i;
						/* translators: 1: current slide index, 2: total slides */
						$slide_label = sprintf( __( '%1$d of %2$d', 'simple-wp-slider' ), $i, $total );
						?>
						<div class="swiper-slide"
							role="group"
							aria-roledescription="slide"
							aria-label="<?php echo esc_attr( $slide_label ); ?>">
							<?php $this->render_slide( $slide ); ?>
						</div>
					<?php endforeach; ?>
				</div>
				<?php if ( ! empty( $settings['dots'] ) ) : ?>
					<div class="swiper-pagination"></div>
				<?php endif; ?>
				<?php if ( ! empty( $settings['arrows'] ) ) : ?>
					<button class="swiper-button-prev" aria-label="<?php esc_attr_e( 'Previous slide', 'simple-wp-slider' ); ?>"></button>
					<button class="swiper-button-next" aria-label="<?php esc_attr_e( 'Next slide', 'simple-wp-slider' ); ?>"></button>
				<?php endif; ?>
			</div>
		</div>
		<?php
		$html = (string) ob_get_clean();
		return apply_filters( 'swps_renderer_html', $html, $slider_id, $slides, $settings );
	}

	/**
	 * Dispatch slide rendering by type.
	 *
	 * @param array $slide Slide record.
	 * @return void
	 */
	private function render_slide( $slide ) {
		switch ( $slide['type'] ) {
			case 'image':
				$this->render_image_slide( $slide );
				break;
			case 'video_self':
				$this->render_video_self( $slide );
				break;
			case 'video_youtube':
				$this->render_youtube_facade( $slide );
				break;
			case 'video_vimeo':
				$this->render_vimeo_facade( $slide );
				break;
		}
	}

	/**
	 * Image slide.
	 *
	 * @param array $slide Slide record.
	 * @return void
	 */
	private function render_image_slide( $slide ) {
		$att_id     = (int) $slide['attachment_id'];
		$alt        = (string) $slide['alt'];
		$link       = (string) $slide['link_url'];
		$target     = (string) $slide['link_target'];
		$rel        = (string) $slide['link_rel'];
		$legacy_url = isset( $slide['_legacy_url'] ) ? (string) $slide['_legacy_url'] : '';

		$img_html = '';
		if ( $att_id > 0 ) {
			$img_html = wp_get_attachment_image(
				$att_id,
				'large',
				false,
				array(
					'alt'     => $alt,
					'loading' => 'lazy',
					'sizes'   => '100vw',
				)
			);
		} elseif ( '' !== $legacy_url ) {
			$img_html = sprintf(
				'<img src="%s" alt="%s" loading="lazy">',
				esc_url( $legacy_url ),
				esc_attr( $alt )
			);
		}

		if ( '' === $img_html ) {
			return;
		}

		if ( '' !== $link ) {
			printf(
				'<a href="%s" target="%s" rel="%s">%s</a>',
				esc_url( $link ),
				esc_attr( $target ),
				esc_attr( $rel ),
				wp_kses_post( $img_html )
			);
		} else {
			echo wp_kses_post( $img_html );
		}

		if ( ! empty( $slide['caption'] ) ) {
			echo '<figcaption class="swps-caption">' . esc_html( (string) $slide['caption'] ) . '</figcaption>';
		}
	}

	/**
	 * Self-hosted video slide.
	 *
	 * @param array $slide Slide record.
	 * @return void
	 */
	private function render_video_self( $slide ) {
		$att_id = (int) $slide['attachment_id'];
		if ( $att_id <= 0 ) {
			return;
		}
		$src = wp_get_attachment_url( $att_id );
		if ( ! $src ) {
			return;
		}
		printf(
			'<video class="swps-video" muted playsinline preload="metadata" data-swps-autoplay-on-active="1"><source src="%s" type="%s"></video>',
			esc_url( $src ),
			esc_attr( (string) get_post_mime_type( $att_id ) )
		);
		if ( ! empty( $slide['caption'] ) ) {
			echo '<figcaption class="swps-caption">' . esc_html( (string) $slide['caption'] ) . '</figcaption>';
		}
	}

	/**
	 * YouTube facade.
	 *
	 * @param array $slide Slide record.
	 * @return void
	 */
	private function render_youtube_facade( $slide ) {
		$parsed = SWPS_Video::parse( (string) $slide['video_url'] );
		if ( ! $parsed || 'youtube' !== $parsed['provider'] ) {
			return;
		}
		$thumb = SWPS_Video::thumbnail_url( 'youtube', $parsed['id'] );
		$label = ! empty( $slide['caption'] )
			? (string) $slide['caption']
			: __( 'Play video', 'simple-wp-slider' );
		printf(
			'<button class="swps-yt-facade" data-yt-id="%s" aria-label="%s"><img src="%s" alt="" loading="lazy" width="480" height="360"><span class="swps-yt-play" aria-hidden="true">&#9658;</span></button>',
			esc_attr( $parsed['id'] ),
			esc_attr( $label ),
			esc_url( $thumb )
		);
	}

	/**
	 * Vimeo facade.
	 *
	 * @param array $slide Slide record.
	 * @return void
	 */
	private function render_vimeo_facade( $slide ) {
		$parsed = SWPS_Video::parse( (string) $slide['video_url'] );
		if ( ! $parsed || 'vimeo' !== $parsed['provider'] ) {
			return;
		}
		$thumb = isset( $slide['_vimeo_thumb'] ) ? (string) $slide['_vimeo_thumb'] : '';
		$label = ! empty( $slide['caption'] )
			? (string) $slide['caption']
			: __( 'Play video', 'simple-wp-slider' );
		$img   = '' !== $thumb
			? sprintf( '<img src="%s" alt="" loading="lazy">', esc_url( $thumb ) )
			: '';
		printf(
			'<button class="swps-vimeo-facade" data-vimeo-id="%s" aria-label="%s">%s<span class="swps-yt-play" aria-hidden="true">&#9658;</span></button>',
			esc_attr( $parsed['id'] ),
			esc_attr( $label ),
			wp_kses_post( $img )
		);
	}
}

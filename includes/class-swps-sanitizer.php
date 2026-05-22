<?php
/**
 * Sanitization helpers for settings + slide objects.
 *
 * @package SimpleWPSlider
 */

defined( 'ABSPATH' ) || exit;

/**
 * Pure sanitizers for slider settings and slide records.
 */
final class SWPS_Sanitizer {

	const SLIDE_TYPES  = array( 'image', 'video_self', 'video_youtube', 'video_vimeo' );
	const EFFECTS      = array( 'slide', 'fade' );
	const ASPECTS      = array( 'auto', '16:9', '4:3', '1:1' );
	const LINK_TARGETS = array( '_self', '_blank' );

	/**
	 * Sanitize a settings array. Missing keys are filled from defaults; unknown
	 * keys are dropped; known keys are coerced to their expected type and clamped.
	 *
	 * @param mixed $input Raw input (array or anything else).
	 * @return array Clean settings array.
	 */
	public static function settings( $input ) {
		$defaults = self::default_settings();
		$input    = is_array( $input ) ? $input : array();

		$out = $defaults;
		foreach ( $defaults as $k => $default ) {
			if ( ! array_key_exists( $k, $input ) ) {
				continue;
			}
			$v = $input[ $k ];
			switch ( $k ) {
				case 'autoplay':
				case 'loop':
				case 'arrows':
				case 'dots':
				case 'keyboard':
				case 'pause_on_hover':
				case 'lazy':
				case 'reduced_motion_disable_autoplay':
					$out[ $k ] = (bool) $v;
					break;
				case 'autoplay_delay':
					$out[ $k ] = max( 500, min( 60000, (int) $v ) );
					break;
				case 'speed':
					$out[ $k ] = max( 100, min( 10000, (int) $v ) );
					break;
				case 'slides_per_view':
					$out[ $k ] = max( 1, min( 10, (int) $v ) );
					break;
				case 'space_between':
					$out[ $k ] = max( 0, min( 200, (int) $v ) );
					break;
				case 'max_height':
					$out[ $k ] = max( 0, min( 10000, (int) $v ) );
					break;
				case 'effect':
					$out[ $k ] = in_array( $v, self::EFFECTS, true ) ? $v : 'slide';
					break;
				case 'aspect_ratio':
					$out[ $k ] = in_array( $v, self::ASPECTS, true ) ? $v : 'auto';
					break;
				case 'breakpoints':
					$out[ $k ] = self::sanitize_breakpoints( $v );
					break;
			}
		}
		return $out;
	}

	/**
	 * Sanitize the breakpoints sub-object.
	 *
	 * @param mixed $bp Raw breakpoints input.
	 * @return array|stdClass
	 */
	private static function sanitize_breakpoints( $bp ) {
		if ( ! is_array( $bp ) ) {
			return new stdClass();
		}
		$clean = array();
		foreach ( $bp as $px => $cfg ) {
			$px = (int) $px;
			if ( $px < 1 || $px > 4096 || ! is_array( $cfg ) ) {
				continue;
			}
			$entry = array();
			if ( isset( $cfg['slides_per_view'] ) ) {
				$entry['slides_per_view'] = max( 1, min( 10, (int) $cfg['slides_per_view'] ) );
			}
			if ( isset( $cfg['space_between'] ) ) {
				$entry['space_between'] = max( 0, min( 200, (int) $cfg['space_between'] ) );
			}
			$clean[ (string) $px ] = $entry;
		}
		return empty( $clean ) ? new stdClass() : $clean;
	}

	/**
	 * Sanitize a single slide record.
	 *
	 * @param mixed $input Raw slide input.
	 * @return array Clean slide.
	 */
	public static function slide( $input ) {
		$input = is_array( $input ) ? $input : array();

		$id = isset( $input['id'] ) ? (string) $input['id'] : '';
		if ( ! preg_match( '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $id ) ) {
			$id = wp_generate_uuid4();
		}

		$type = isset( $input['type'] ) ? (string) $input['type'] : 'image';
		if ( ! in_array( $type, self::SLIDE_TYPES, true ) ) {
			$type = 'image';
		}

		$link_target = isset( $input['link_target'] ) ? (string) $input['link_target'] : '_self';
		if ( ! in_array( $link_target, self::LINK_TARGETS, true ) ) {
			$link_target = '_self';
		}

		$link_rel = isset( $input['link_rel'] ) ? sanitize_text_field( (string) $input['link_rel'] ) : '';
		if ( '_blank' === $link_target ) {
			$rel_parts = array_filter( array_map( 'trim', explode( ' ', $link_rel ) ) );
			foreach ( array( 'noopener', 'noreferrer' ) as $force ) {
				if ( ! in_array( $force, $rel_parts, true ) ) {
					$rel_parts[] = $force;
				}
			}
			$link_rel = implode( ' ', array_unique( $rel_parts ) );
		}

		$out = array(
			'id'            => $id,
			'type'          => $type,
			'attachment_id' => isset( $input['attachment_id'] ) ? absint( $input['attachment_id'] ) : 0,
			'video_url'     => isset( $input['video_url'] ) ? esc_url_raw( (string) $input['video_url'] ) : '',
			'alt'           => isset( $input['alt'] ) ? sanitize_text_field( (string) $input['alt'] ) : '',
			'caption'       => isset( $input['caption'] ) ? sanitize_text_field( (string) $input['caption'] ) : '',
			'link_url'      => isset( $input['link_url'] ) ? esc_url_raw( (string) $input['link_url'] ) : '',
			'link_target'   => $link_target,
			'link_rel'      => $link_rel,
			'cta_text'      => isset( $input['cta_text'] ) ? sanitize_text_field( (string) $input['cta_text'] ) : '',
			'cta_url'       => isset( $input['cta_url'] ) ? esc_url_raw( (string) $input['cta_url'] ) : '',
		);

		// Preserve internal-only fields that survive REST round-trips (used by migration + Vimeo facade).
		if ( ! empty( $input['_legacy_url'] ) ) {
			$out['_legacy_url'] = esc_url_raw( (string) $input['_legacy_url'] );
		}
		if ( ! empty( $input['_vimeo_thumb'] ) ) {
			$out['_vimeo_thumb'] = esc_url_raw( (string) $input['_vimeo_thumb'] );
		}

		return $out;
	}

	/**
	 * Sanitize an array of slides.
	 *
	 * @param mixed $input Raw input.
	 * @return array Cleaned slides list.
	 */
	public static function slides( $input ) {
		if ( ! is_array( $input ) ) {
			return array();
		}
		$out = array();
		foreach ( $input as $slide ) {
			$out[] = self::slide( $slide );
		}
		return $out;
	}

	/**
	 * Default settings values, applied when input keys are missing.
	 *
	 * @return array
	 */
	public static function default_settings() {
		return array(
			'autoplay'                        => false,
			'autoplay_delay'                  => 5000,
			'loop'                            => true,
			'speed'                           => 600,
			'effect'                          => 'slide',
			'arrows'                          => true,
			'dots'                            => true,
			'keyboard'                        => true,
			'pause_on_hover'                  => true,
			'slides_per_view'                 => 1,
			'space_between'                   => 0,
			'breakpoints'                     => new stdClass(),
			'aspect_ratio'                    => 'auto',
			'max_height'                      => 0,
			'lazy'                            => true,
			'reduced_motion_disable_autoplay' => true,
		);
	}
}

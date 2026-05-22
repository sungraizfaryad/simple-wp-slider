<?php
/**
 * Video URL parser + thumbnail helper.
 *
 * @package SimpleWPSlider
 */

defined( 'ABSPATH' ) || exit;

/**
 * Parses YouTube and Vimeo URLs into provider+id pairs.
 */
final class SWPS_Video {

	/**
	 * Parse a video URL.
	 *
	 * @param string $url Raw URL.
	 * @return array|null Array with keys provider+id, or null when not recognized.
	 */
	public static function parse( $url ) {
		$url = trim( (string) $url );
		if ( '' === $url ) {
			return null;
		}

		if ( preg_match( '#^https?://youtu\.be/([A-Za-z0-9_-]{6,32})#i', $url, $m ) ) {
			return array(
				'provider' => 'youtube',
				'id'       => $m[1],
			);
		}

		if ( preg_match( '#^https?://(?:www\.|m\.)?youtube\.com/(?:watch\?(?:.+&)?v=|embed/|v/)([A-Za-z0-9_-]{6,32})#i', $url, $m ) ) {
			return array(
				'provider' => 'youtube',
				'id'       => $m[1],
			);
		}

		if ( preg_match( '#^https?://(?:www\.|player\.)?vimeo\.com/(?:video/)?(\d{6,12})#i', $url, $m ) ) {
			return array(
				'provider' => 'vimeo',
				'id'       => $m[1],
			);
		}

		return null;
	}

	/**
	 * Get a thumbnail URL for a parsed video.
	 *
	 * For Vimeo, returns empty string — the REST handler resolves via oEmbed.
	 *
	 * @param string $provider Provider key.
	 * @param string $id       Video ID.
	 * @return string
	 */
	public static function thumbnail_url( $provider, $id ) {
		if ( 'youtube' === $provider ) {
			return 'https://i.ytimg.com/vi/' . rawurlencode( $id ) . '/hqdefault.jpg';
		}
		return '';
	}
}

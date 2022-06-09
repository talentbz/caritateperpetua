<?php
/**
 * Embed class.
 */

namespace Jet_Gallery;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Embed {

	/**
	 * Provider match masks. Holds a list of supported providers with their URL structure in a regex format.
	 *
	 * @var array
	 */
	private static $provider_match_masks = [
		'youtube'     => '/^.*(?:youtu\.be\/|youtube(?:-nocookie)?\.com\/(?:(?:watch)?\?(?:.*&)?vi?=|(?:embed|v|vi|user)\/))([^\?&\"\'>]+)/',
		'vimeo'       => '/^.*vimeo\.com\/(?:[a-z]*\/)*([‌​0-9]{6,11})[?]?.*/',
		'dailymotion' => '/^.*dailymotion.com\/(?:video|hub)\/([^_]+)[^#]*(#video=([^_&]+))?/',
	];

	/**
	 * Embed patterns. Holds a list of supported providers with their embed patters.
	 *
	 * @var array
	 */
	private static $embed_patterns = [
		'youtube'     => 'https://www.youtube{NO_COOKIE}.com/embed/{VIDEO_ID}?feature=oembed',
		'vimeo'       => 'https://player.vimeo.com/video/{VIDEO_ID}#t={TIME}',
		'dailymotion' => 'https://dailymotion.com/embed/video/{VIDEO_ID}',
	];

	/**
	 * Get video properties. Retrieve the video properties for a given video URL.
	 *
	 * @param string $video_url
	 *
	 * @return null|array
	 */
	public static function get_video_properties( $video_url ) {

		foreach ( self::$provider_match_masks as $provider => $match_mask ) {
			preg_match( $match_mask, $video_url, $matches );

			if ( $matches ) {
				return [
					'provider' => $provider,
					'video_id' => $matches[1],
				];
			}
		}

		return null;

	}

	/**
	 * Get embed URL. Retrieve the embed URL for a given video.
	 *
	 * @param string $video_url
	 * @param array  $embed_url_params
	 * @param array  $options
	 *
	 * @return string
	 */
	public static function get_embed_url( $video_url = '', $embed_url_params = [], $options = [] ) {

		$video_properties = self::get_video_properties( $video_url );

		if ( ! $video_properties ) {
			return null;
		}

		$embed_pattern = self::$embed_patterns[ $video_properties['provider'] ];

		$replacements = [
			'{VIDEO_ID}' => $video_properties['video_id'],
		];

		if ( 'youtube' === $video_properties['provider'] ) {
			$replacements['{NO_COOKIE}'] = ! empty( $options['privacy'] ) ? '-nocookie' : '';
		} elseif ( 'vimeo' === $video_properties['provider'] ) {
			$time_text = '';

			if ( ! empty( $options['start'] ) ) {
				$time_text = date( 'H\hi\ms\s', $options['start'] );
			}

			$replacements['{TIME}'] = $time_text;
		}

		$embed_pattern = str_replace( array_keys( $replacements ), $replacements, $embed_pattern );

		return add_query_arg( $embed_url_params, $embed_pattern );

	}

	/**
	 * Get embed HTML. Retrieve the final HTML of the embedded URL.
	 *
	 * @param string $video_url
	 * @param array  $embed_url_params
	 * @param array  $options
	 * @param array  $frame_attributes
	 *
	 * @return string
	 */
	public static function get_embed_html( $video_url = '', $embed_url_params = [], $options = [], $frame_attributes = [] ) {

		$video_properties = self::get_video_properties( $video_url );

		$default_frame_attributes = [
			'class' => 'jet-gallery-video-iframe',
			'allowfullscreen',
			'title' => sprintf(
				__( '%s Video Player', 'jet-woo-product-gallery' ),
				$video_properties['provider']
			),
		];

		$video_embed_url = self::get_embed_url( $video_url, $embed_url_params, $options );

		if ( ! $video_embed_url ) {
			return null;
		}

		if ( ! $options['lazy_load'] ) {
			$default_frame_attributes['src'] = $video_embed_url;
		} else {
			$default_frame_attributes['data-lazy-load'] = $video_embed_url;
		}

		$frame_attributes     = array_merge( $default_frame_attributes, $frame_attributes );
		$attributes_for_print = [];

		foreach ( $frame_attributes as $attribute_key => $attribute_value ) {
			$attribute_value = esc_attr( $attribute_value );

			if ( is_numeric( $attribute_key ) ) {
				$attributes_for_print[] = $attribute_value;
			} else {
				$attributes_for_print[] = sprintf( '%1$s="%2$s"', $attribute_key, $attribute_value );
			}
		}

		$attributes_for_print = implode( ' ', $attributes_for_print );

		$iframe_html = "<iframe $attributes_for_print></iframe>";

		return apply_filters( 'oembed_result', $iframe_html, $video_url, $frame_attributes );

	}

}

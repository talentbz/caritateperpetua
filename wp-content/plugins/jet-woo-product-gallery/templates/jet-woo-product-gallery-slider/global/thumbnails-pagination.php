<?php
/**
 * JetGallery Slider thumbnails template.
 */

if ( $with_featured_image && has_post_thumbnail( $post_id ) ) {
	array_unshift( $attachment_ids, intval( get_post_thumbnail_id( $post_id ) ) );
}

$thumbs_video_placeholder_html = '';
$thumbs_html                   = '';

if ( $this->gallery_has_video() && 'content' === $settings['video_display_in'] ) {
	if ( $this->video_has_custom_placeholder( $settings ) ) {
		$video_thumbnail_id = jet_woo_gallery_video_integration()->get_video_custom_placeholder( $settings );
		$first_place_video ? array_unshift( $attachment_ids, $video_thumbnail_id ) : array_push( $attachment_ids, $video_thumbnail_id );
	} else {
		$video_placeholder_url         = jet_woo_product_gallery()->plugin_url( 'assets/images/video-thumbnails-placeholder.png' );
		$thumbs_video_placeholder_html = sprintf(
			'<div data-thumb="%s" class="jet-woo-swiper-control-thumbs__item swiper-slide"><div class="jet-woo-swiper-control-thumbs__item-image"><img width="300" height="300" src="%s" ></div></div>',
			$video_placeholder_url, $video_placeholder_url
		);
	}
}

if ( 'content' === $settings['video_display_in'] && $first_place_video ) {
	$thumbs_html .= $thumbs_video_placeholder_html;
}

if ( $with_featured_image && ! has_post_thumbnail( $post_id ) ) {
	$thumbs_html .= sprintf(
		'<div class="jet-woo-product-gallery__image-item featured no-image swiper-slide"><div class="jet-woo-product-gallery__image image-with-placeholder"><img src="%s" alt="%s" class="wp-post-image"></div></div>',
		Elementor\Utils::get_placeholder_image_src(),
		__( 'Placeholder', 'jet-woo-product-gallery' )
	);
}

if ( $attachment_ids ) {
	foreach ( $attachment_ids as $attachment_id ) {
		$image_src   = wp_get_attachment_image_src( $attachment_id, 'full' );
		$image       = wp_get_attachment_image( $attachment_id, $settings['thumbs_image_size'], false );
		$thumbs_html .= sprintf(
			'<div data-thumb="%s" class="jet-woo-swiper-control-thumbs__item swiper-slide"><div class="jet-woo-swiper-control-thumbs__item-image">%s</div></div>',
			esc_url( $image_src[0] ),
			$image
		);
	}
}

if ( 'content' === $settings['video_display_in'] && ! $first_place_video ) {
	$thumbs_html .= $thumbs_video_placeholder_html;
}
?>
<div class="jet-gallery-swiper-thumb">
	<div class="jet-woo-swiper-control-nav jet-woo-swiper-gallery-thumbs swiper-container">
		<div class="swiper-wrapper">
			<?php echo $thumbs_html; ?>
		</div>
		<?php if ( $thumb_slider_navigation ) {
			echo $this->get_slider_navigation( 'pagination_thumbnails_slider_arrow_prev', 'pagination_thumbnails_slider_arrow_next' );
		} ?>
	</div>
</div>

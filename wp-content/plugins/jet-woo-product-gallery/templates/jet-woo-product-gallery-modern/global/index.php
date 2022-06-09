<?php
/**
 * JetGallery Modern template.
 */

$images_size         = $settings['image_size'];
$enable_gallery      = filter_var( $settings['enable_gallery'], FILTER_VALIDATE_BOOLEAN );
$gallery_trigger     = isset( $settings['gallery_trigger_type'] ) ? $settings['gallery_trigger_type'] : '';
$trigger_class       = $enable_gallery && 'image' === $gallery_trigger ? 'jet-woo-product-gallery__trigger' : '';
$zoom                = filter_var( $settings['enable_zoom'], FILTER_VALIDATE_BOOLEAN ) ? 'jet-woo-product-gallery__image--with-zoom' : '';
$gallery             = '[jet-gallery]';
$video_thumbnail_url = $this->get_video_thumbnail_url();
$video_type          = jet_woo_gallery_video_integration()->get_video_type( $settings );
$video               = $this->get_video_html();
$first_place_video   = filter_var( $settings['first_place_video'], FILTER_VALIDATE_BOOLEAN );
$wrapper_classes     = $this->get_wrapper_classes( [ 'jet-woo-product-gallery__content' ], $settings );
?>
<div class="<?php echo implode( ' ', $wrapper_classes ); ?>" data-featured-image="<?php echo $with_featured_image; ?>">
	<div class="jet-woo-product-gallery-modern">
		<?php
		if ( 'content' === $settings['video_display_in'] && $first_place_video ) {
			include $this->get_global_template( 'video' );
		}

		if ( $with_featured_image ) {
			if ( has_post_thumbnail( $post_id ) ) {
				include $this->get_global_template( 'image' );
			} else {
				printf(
					'<div class="jet-woo-product-gallery__image-item featured no-image"><div class="jet-woo-product-gallery__image image-with-placeholder"><img src="%s" alt="%s" class="%s"></div></div>',
					$this->get_featured_image_placeholder(),
					__( 'Placeholder', 'jet-woo-product-gallery' ),
					'wp-post-image'
				);
			}
		}

		if ( $attachment_ids ) {
			foreach ( $attachment_ids as $attachment_id ) {
				include $this->get_global_template( 'thumbnails' );
			}
		}

		if ( 'content' === $settings['video_display_in'] && ! $first_place_video ) {
			include $this->get_global_template( 'video' );
		}
		?>
	</div>

	<?php if ( 'popup' === $settings['video_display_in'] ) {
		include $this->get_global_template( 'popup-video' );
	} ?>
</div>
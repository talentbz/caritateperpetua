<?php
/**
 * JetGallery Modern main image template.
 */

$thumbnail_id = get_post_thumbnail_id( $post_id );
$image_src    = wp_get_attachment_image_src( $thumbnail_id, 'full' );
$image        = $this->get_gallery_featured_image( $post_id, $thumbnail_id, $images_size, $image_src );
?>

<div class="jet-woo-product-gallery__image-item featured">
	<div class="jet-woo-product-gallery__image <?php echo $zoom ?>">
		<?php
		printf(
			'<a class="jet-woo-product-gallery__image-link %s" href="%s" itemprop="image" title="%s" rel="prettyPhoto%s">%s</a>',
			$trigger_class,
			esc_url( $image_src[0] ),
			esc_attr( get_post_field( 'post_title', $thumbnail_id ) ),
			$gallery,
			$image
		);

		if ( $enable_gallery && 'button' === $gallery_trigger ) {
			$this->get_gallery_trigger_button( $this->render_icon( 'gallery_button_icon', '%s', '', false ) );
		}
		?>
	</div>
</div>
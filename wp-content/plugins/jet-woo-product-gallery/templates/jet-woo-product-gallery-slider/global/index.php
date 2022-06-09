<?php
/**
 * JetGallery Slider template.
 */

$images_size             = $settings['image_size'];
$enable_gallery          = filter_var( $settings['enable_gallery'], FILTER_VALIDATE_BOOLEAN );
$gallery_trigger         = isset( $settings['gallery_trigger_type'] ) ? $settings['gallery_trigger_type'] : '';
$trigger_class           = $enable_gallery && 'image' === $gallery_trigger ? 'jet-woo-product-gallery__trigger' : '';
$zoom                    = filter_var( $settings['enable_zoom'], FILTER_VALIDATE_BOOLEAN ) ? 'jet-woo-product-gallery__image--with-zoom' : '';
$equal_slides_height     = filter_var( $settings['slider_equal_slides_height'], FILTER_VALIDATE_BOOLEAN );
$gallery                 = '[jet-gallery]';
$dir                     = is_rtl() ? 'rtl' : 'ltr';
$slider_direction        = 'jet-woo-swiper-horizontal';
$slider_navigation       = filter_var( $settings['slider_show_nav'], FILTER_VALIDATE_BOOLEAN );
$thumb_slider_navigation = filter_var( $settings['slider_show_thumb_nav'], FILTER_VALIDATE_BOOLEAN );
$video_type              = jet_woo_gallery_video_integration()->get_video_type( $settings );
$video_thumbnail_url     = $this->get_video_thumbnail_url();
$video                   = $this->get_video_html();
$first_place_video       = filter_var( $settings['first_place_video'], FILTER_VALIDATE_BOOLEAN );
$pagination              = filter_var( $settings['slider_show_pagination'], FILTER_VALIDATE_BOOLEAN );
$pagination_type         = isset( $settings['slider_pagination_type'] ) ? $settings['slider_pagination_type'] : 'bullets';
$pagination_control_type = isset( $settings['slider_pagination_controller_type'] ) ? $settings['slider_pagination_controller_type'] : 'bullets';
$pagination_direction    = isset( $settings['slider_pagination_direction'] ) ? $settings['slider_pagination_direction'] : 'horizontal';
$pagination_v_position   = isset( $settings['slider_pagination_v_position'] ) ? $settings['slider_pagination_v_position'] : 'start';
$pagination_h_position   = isset( $settings['slider_pagination_h_position'] ) ? $settings['slider_pagination_h_position'] : 'bottom';

if ( $pagination && 'vertical' === $pagination_direction ) {
	$pagination_position = 'jet-gallery-swiper-v-pos-' . $pagination_v_position;
	$slider_direction    = 'jet-woo-swiper-vertical';
} else {
	$pagination_position = 'jet-gallery-swiper-h-pos-' . $pagination_h_position;
}

$gallery_swiper_slider_wrapper = $this->get_wrapper_classes( [ 'jet-woo-swiper', $slider_direction, $pagination_position, ], $settings );
?>

<div class="<?php echo implode( ' ', $gallery_swiper_slider_wrapper ); ?>" data-featured-image="<?php echo $with_featured_image; ?>">
	<div class="jet-gallery-swiper-slider">
		<div class="jet-woo-product-gallery-slider swiper-container" <?php echo $this->get_slider_data_settings(); ?> dir="<?php echo $dir; ?>">
			<div class="swiper-wrapper">
				<?php
				if ( 'content' === $settings['video_display_in'] && $first_place_video ) {
					include $this->get_global_template( 'video' );
				}

				if ( $with_featured_image ) {
					if ( has_post_thumbnail( $post_id ) ) {
						include $this->get_global_template( 'image' );
					} else {
						printf(
							'<div class="jet-woo-product-gallery__image-item featured no-image swiper-slide"><div class="jet-woo-product-gallery__image image-with-placeholder"><img src="%s" alt="%s" class="%s"></div></div>',
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

			<?php
			if ( $slider_navigation ) {
				echo $this->get_slider_navigation( 'slider_nav_arrow_prev', 'slider_nav_arrow_next' );
			}
			?>

			<?php if ( $pagination && 'thumbnails' !== $pagination_type ) : ?>
				<?php if ( 'progressbar' === $pagination_control_type ) : ?>
					<div class="swiper-pagination swiper-pagination-progressbar">
						<span class="swiper-pagination-progressbar-fill placeholder"></span>
					</div>
				<?php elseif ( 'fraction' === $pagination_control_type ) : ?>
					<div class="swiper-pagination swiper-pagination-fraction">
						<span class="swiper-pagination-current placeholder">1</span>
						/
						<span class="swiper-pagination-total placeholder">3</span>
					</div>
				<?php else : ?>
					<div class="swiper-pagination swiper-pagination-bullets">
						<span class="swiper-pagination-bullet swiper-pagination-bullet-active placeholder"></span>
						<span class="swiper-pagination-bullet placeholder"></span>
						<span class="swiper-pagination-bullet placeholder"></span>
					</div>
				<?php endif; ?>
			<?php endif; ?>
		</div>
	</div>

	<?php
	if ( $pagination && 'thumbnails' === $pagination_type ) {
		if ( count( $attachment_ids ) > 1 || $with_featured_image && ! empty( $attachment_ids ) || $with_featured_image && $this->gallery_has_video() || ! empty( $attachment_ids ) && $this->gallery_has_video() ) {
			include $this->get_global_template( 'thumbnails-pagination' );
		}
	}

	if ( 'popup' === $settings['video_display_in'] ) {
		include $this->get_global_template( 'popup-video' );
	}
	?>

</div>

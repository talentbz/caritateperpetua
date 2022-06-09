<?php
/**
 * JetGallery Anchor Nav video template.
 */

if ( ! $this->gallery_has_video() ) {
	return null;
}

$ratio_classes    = [];
$overlay_styles   = '';
$play_button_html = '';

if ( 'self_hosted' !== $video_type ) {
	$ratio_classes = [
		'jet-woo-product-video-aspect-ratio',
		'jet-woo-product-video-aspect-ratio--' . $settings['aspect_ratio'],
	];
}

if ( '' !== $video_thumbnail_url ) {
	$overlay_styles = 'background-image: url(' . $video_thumbnail_url . ');';
}

if ( filter_var( $settings['show_play_button'], FILTER_VALIDATE_BOOLEAN ) ) {
	$play_button_html = '<div class="jet-woo-product-video__play-button" role="button">';

	switch ( $settings['play_button_type'] ) {
		case 'icon' :
			$play_button_html .= sprintf(
				'<span class="jet-woo-product-video__play-button-icon jet-product-gallery-icon">%s</span>',
				$this->render_icon( 'play_button_icon', '%s', '', false )
			);
			break;
		case 'image':
			$play_button_html .= $this->get_image_by_url(
				$settings['play_button_image']['url'],
				array(
					'class' => 'jet-woo-product-video__play-button-image',
					'alt'   => __( 'Play Video', 'jet-woo-product-gallery' ),
				)
			);
	}

	$play_button_html .= sprintf(
		'<span class="screen-reader-text">%s</span>',
		__( 'Play Video', 'jet-woo-product-gallery' )
	);

	$play_button_html .= '</div>';
}

if ( $this->gallery_has_video() && $first_place_video ) {
	$anchor_nav_controller_id = $anchor_nav_controller_ids[0];
} else {
	$anchor_nav_controller_id = $this->get_unique_controller_id();
}
?>

<div class="jet-woo-product-gallery__image-item" id="<?php echo $anchor_nav_controller_id; ?>">
	<div class="jet-woo-product-gallery__image jet-woo-product-gallery--with-video">
		<?php
		printf( '<div class="jet-woo-product-video %s">%s</div>', implode( ' ', $ratio_classes ), $video );
		printf( '<div class="jet-woo-product-video__overlay" style="%s">%s</div>', $overlay_styles, $play_button_html );
		?>
	</div>
</div>
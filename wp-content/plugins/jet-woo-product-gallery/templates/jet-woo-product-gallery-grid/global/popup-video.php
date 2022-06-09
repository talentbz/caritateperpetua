<?php
/**
 * Gallery Grid popup video template.
 */

if ( ! $this->gallery_has_video() ) {
	return null;
}

$vertical_ratio_class = '';
$ratio_classes        = [];

if ( 'self_hosted' !== $video_type ) {
	if ( '9-16' === $settings['aspect_ratio'] || '2-3' === $settings['aspect_ratio'] ) {
		$vertical_ratio_class = 'jet-woo-vertical-aspect-ratio--' . $settings['aspect_ratio'];
	}

	$ratio_classes = [
		'jet-woo-product-video-aspect-ratio',
		'jet-woo-product-video-aspect-ratio--' . $settings['aspect_ratio'],
	];
}
?>

<div class="jet-woo-product-video__popup-wrapper">
	<div class="jet-woo-product-video__popup-button" role="button">
		<span class="jet-woo-product-video__popup-button-icon jet-product-gallery-icon" aria-hidden="true">
			<?php echo $this->render_icon( 'popup_button_icon', '%s', '', false ); ?>
		</span>
		<span class="screen-reader-text">
			<?php echo __( 'Open popup with video', 'jet-woo-product-gallery' ); ?>
		</span>
	</div>
	<div class="jet-woo-product-video__popup-content">
		<div class="jet-woo-product-video__popup-overlay"></div>
		<div class="jet-woo-product-video__popup <?php echo $vertical_ratio_class; ?>">
			<?php printf( '<div class="jet-woo-product-video %s">%s</div>', implode( ' ', $ratio_classes ), $video ); ?>
		</div>
	</div>
</div>
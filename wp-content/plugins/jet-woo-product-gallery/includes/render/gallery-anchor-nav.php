<?php
/**
 * Gallery Anchor Nav widget views manager.
 */

// If this file is called directly, abort.$this->attributes_variation_images
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Gallery_Anchor_Nav' ) ) {

	/**
	 * Define Jet_Gallery_Anchor_Nav class.
	 */
	class Jet_Gallery_Anchor_Nav extends Jet_Gallery_Render_Base {

		public function get_name() {
			return 'jet-woo-product-gallery-anchor-nav';
		}

		public function default_settings() {

			$default_settings = [
				'image_size' => 'thumbnail',
			];

			return array_merge( parent::default_settings(), $default_settings );

		}

		/**
		 * Anchor gallery unique id for controllers.
		 *
		 * @return string
		 */
		public function get_unique_controller_id() {
			return uniqid( 'controller-item-id-', true );
		}

		public function render() {
			jet_woo_product_gallery_assets()->enqueue_scripts();
			$this->get_render_gallery_content();
		}

	}

}
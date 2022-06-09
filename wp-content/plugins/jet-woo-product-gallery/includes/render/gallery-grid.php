<?php
/**
 * Gallery Grid widget views manager.
 */

// If this file is called directly, abort.$this->attributes_variation_images
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Gallery_Grid' ) ) {

	/**
	 * Define Jet_Gallery_Grid class.
	 */
	class Jet_Gallery_Grid extends Jet_Gallery_Render_Base {

		public function get_name() {
			return 'jet-woo-product-gallery-grid';
		}

		public function default_settings() {

			$default_settings = [
				'image_size'     => 'thumbnail',
				'columns'        => 4,
				'columns_tablet' => 3,
				'columns_mobile' => 2,
			];

			return array_merge( parent::default_settings(), $default_settings );

		}

		public function render() {
			jet_woo_product_gallery_assets()->enqueue_scripts();
			$this->get_render_gallery_content();
		}

	}

}
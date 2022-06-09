<?php
/**
 * JetGallery Blocks Views Editor.
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Gallery_Blocks_Views_Editor' ) ) {

	/**
	 * Define Jet_Gallery_Blocks_Views_Editor class.
	 */
	class Jet_Gallery_Blocks_Views_Editor {

		/**
		 * Constructor for the class.
		 */
		public function __construct() {
			add_action( 'enqueue_block_editor_assets', [ $this, 'blocks_assets' ] );
		}

		/**
		 * Register blocks assets.
		 */
		public function blocks_assets() {

			wp_enqueue_script(
				'jet-gallery-blocks-views',
				jet_woo_product_gallery()->plugin_url( 'assets/js/admin/blocks-views/blocks.js' ),
				[ 'wp-plugins', 'wp-element', 'lodash' ],
				jet_woo_product_gallery()->get_version() . time(),
				true
			);

			$gallery_sources = jet_woo_product_gallery_tools()->get_gallery_source_options();
			$sources         = [];

			foreach ( $gallery_sources as $value => $label ) {
				$sources[] = [
					'value' => $value,
					'label' => $label,
				];
			}

			$config = [
				'gallerySources' => $sources,
				'imageSizes'     => jet_woo_product_gallery_tools()->get_image_sizes( 'blocks' ),
			];

			wp_localize_script(
				'jet-gallery-blocks-views',
				'JetGalleryBlocksData',
				apply_filters( 'jet-gallery/blocks-views/editor/config', $config )
			);

			wp_enqueue_style(
				'jet-gallery-blocks-views',
				jet_woo_product_gallery()->plugin_url( 'assets/css/blocks-views.css' ),
				false,
				jet_woo_product_gallery()->get_version()
			);

			jet_woo_product_gallery_assets()->enqueue_styles();

		}

	}

}
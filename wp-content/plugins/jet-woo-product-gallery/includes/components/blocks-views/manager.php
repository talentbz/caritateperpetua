<?php
/**
 * JetGallery Blocks Views Manager.
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Gallery_Blocks_Views' ) ) {

	/**
	 * Define Jet_Gallery_Blocks_Views class.
	 */
	class Jet_Gallery_Blocks_Views {

		public $editor;
		public $block_types;

		/**
		 * Constructor for the class.
		 */
		function __construct() {

			add_filter( 'block_categories_all', [ $this, 'add_gallery_category' ] );

			if ( is_admin() ) {
				require $this->component_path( 'editor.php' );

				$this->editor = new Jet_Gallery_Blocks_Views_Editor();
			}

			require $this->component_path( 'block-types.php' );

			$this->block_types = new Jet_Gallery_Blocks_Views_Types();

		}

		/**
		 * Register gallery category for blocks editor.
		 *
		 * @param $block_categories
		 *
		 * @return mixed
		 */
		public function add_gallery_category( $block_categories ) {
			return array_merge(
				$block_categories,
				[
					[
						'slug'  => 'jet-gallery',
						'title' => __( 'JetGallery', 'jet-woo-product-gallery' ),
					],
				]
			);
		}

		/**
		 * Return path to file inside component.
		 *
		 * @param  $path
		 *
		 * @return string
		 */
		public function component_path( $path ) {
			return jet_woo_product_gallery()->plugin_path( 'includes/components/blocks-views/' . $path );
		}

	}

}
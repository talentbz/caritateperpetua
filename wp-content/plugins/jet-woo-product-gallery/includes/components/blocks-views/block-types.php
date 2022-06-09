<?php
/**
 * JetGallery Block Views Types.
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Gallery_Blocks_Views_Types' ) ) {

	/**
	 * Define Jet_Gallery_Blocks_Views_Types class.
	 */
	class Jet_Gallery_Blocks_Views_Types {

		// Gallery block types holder.
		private $_types = [];

		/**
		 * Constructor for the class.
		 */
		public function __construct() {
			add_action( 'init', [ $this, 'register_block_types' ], 99 );
		}

		/**
		 * Register block types.
		 *
		 * @return void
		 */
		public function register_block_types() {

			$types_dir = jet_woo_product_gallery()->plugin_path( 'includes/components/blocks-views/block-types/' );

			require $types_dir . 'base.php';
			require $types_dir . 'gallery-anchor-nav.php';
			require $types_dir . 'gallery-grid.php';
			require $types_dir . 'gallery-modern.php';
			require $types_dir . 'gallery-slider.php';

			$types = [
				new Jet_Gallery_Blocks_Views_Type_Anchor_Nav(),
				new Jet_Gallery_Blocks_Views_Type_Grid(),
				new Jet_Gallery_Blocks_Views_Type_Modern(),
				new Jet_Gallery_Blocks_Views_Type_Slider(),
			];

			foreach ( $types as $type ) {
				$this->_types[ $type->get_name() ] = $type;
			}

		}

	}

}
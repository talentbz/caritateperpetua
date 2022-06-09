<?php
/**
 * Product Gallery assets class
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Woo_Product_Gallery_Assets' ) ) {

	/**
	 * Define Jet_Woo_Product_Gallery_Assets class
	 */
	class Jet_Woo_Product_Gallery_Assets {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		private $did_scripts = false;

		/**
		 * Initial class function.
		 */
		public function init() {

			add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_missed_assets' ] );
			add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_styles' ] );

			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

		}

		/**
		 * Enqueue admin assets.
		 *
		 * @return void
		 */
		public function enqueue_admin_assets() {
			wp_enqueue_style(
				'jet-woo-product-gallery-admin',
				jet_woo_product_gallery()->plugin_url( 'assets/css/jet-woo-product-gallery-admin.css' ),
				false,
				jet_woo_product_gallery()->get_version()
			);
		}

		/**
		 * Enqueue public-facing stylesheets.
		 *
		 * @return void
		 */
		public function enqueue_styles() {
			wp_enqueue_style(
				'jet-woo-product-gallery',
				jet_woo_product_gallery()->plugin_url( 'assets/css/jet-woo-product-gallery.css' ),
				$this->get_gallery_style_frontend_dependencies(),
				jet_woo_product_gallery()->get_version()
			);
		}

		/**
		 * Enqueue public-facing scripts.
		 *
		 * @return void
		 */
		public function enqueue_scripts() {

			if ( $this->did_scripts ) {
				return;
			}

			$this->did_scripts = true;

			wp_enqueue_script(
				'jet-woo-product-gallery',
				jet_woo_product_gallery()->plugin_url( 'assets/js/jet-woo-product-gallery' . $this->suffix() . '.js' ),
				$this->get_gallery_script_frontend_dependencies(),
				jet_woo_product_gallery()->get_version(),
				true
			);

			wp_localize_script(
				'jet-woo-product-gallery',
				'jetWooProductGalleryData',
				apply_filters( 'jet-woo-product-gallery/frontend/localize-data', [] )
			);

		}

		/**
		 * Return frontend styles dependencies.
		 *
		 * @return array
		 */
		public function get_gallery_style_frontend_dependencies() {

			$dependencies = [];

			if ( is_admin() ) {
				return $dependencies;
			}

			if ( ! wp_style_is(  'mediaelement' ) ) {
				$dependencies[] = 'mediaelement';
			}

			if ( ! wp_style_is(  'photoswipe' ) ) {
				$dependencies[] = 'photoswipe';
			}

			if ( ! wp_style_is(  'photoswipe-default-skin' ) ) {
				$dependencies[] = 'photoswipe-default-skin';
			}

			return $dependencies;

		}

		/**
		 * Return frontend script dependencies.
		 *
		 * @return array
		 */
		public function get_gallery_script_frontend_dependencies() {

			$dependencies = [ 'jquery' ];

			if ( is_admin() ) {
				return $dependencies;
			}

			if ( ! wp_script_is(  'swiper' ) ) {
				$dependencies[] = 'swiper';
			}

			if ( ! wp_script_is(  'mediaelement' ) ) {
				$dependencies[] = 'mediaelement';
			}

			if ( ! wp_script_is(  'zoom' ) ) {
				$dependencies[] = 'zoom';
			}

			if ( ! wp_script_is(  'photoswipe' ) ) {
				$dependencies[] = 'photoswipe';
			}

			if ( ! wp_script_is(  'photoswipe-ui-default' ) ) {
				$dependencies[] = 'photoswipe-ui-default';
			}

			return $dependencies;

		}

		/**
		 * Enqueue needed scripts and styles when they missing.
		 *
		 * @return void
		 */
		public function enqueue_missed_assets() {

			if ( ! wp_script_is( 'swiper', 'registered' ) ) {
				wp_register_script(
					'swiper',
					jet_woo_product_gallery()->plugin_url( 'assets/lib/swiper/swiper' . $this->suffix() . '.js' ),
					[],
					jet_woo_product_gallery()->get_version(),
					true
				);
			}

			if ( ! class_exists( 'WooCommerce' ) ) {
				wp_register_script(
					'zoom',
					jet_woo_product_gallery()->plugin_url( 'assets/lib/zoom/jquery.zoom' . $this->suffix() . '.js' ),
					[ 'jquery' ],
					jet_woo_product_gallery()->get_version(),
					true
				);

				wp_register_script(
					'photoswipe',
					jet_woo_product_gallery()->plugin_url( 'assets/lib/photoswipe/js/photoswipe' . $this->suffix() . '.js' ),
					[],
					jet_woo_product_gallery()->get_version(),
					true
				);

				wp_register_script(
					'photoswipe-ui-default',
					jet_woo_product_gallery()->plugin_url( 'assets/lib/photoswipe/js/photoswipe-ui-default' . $this->suffix() . '.js' ),
					[ 'photoswipe' ],
					jet_woo_product_gallery()->get_version(),
					true
				);

				wp_register_style(
					'photoswipe',
					jet_woo_product_gallery()->plugin_url( 'assets/lib/photoswipe/css/photoswipe.min.css' ),
					[],
					jet_woo_product_gallery()->get_version()
				);

				wp_register_style(
					'photoswipe-default-skin',
					jet_woo_product_gallery()->plugin_url( 'assets/lib/photoswipe/css/default-skin/default-skin.min.css' ),
					[ 'photoswipe' ],
					jet_woo_product_gallery()->get_version()
				);
			}

		}

		/**
		 * Add suffix to scripts
		 *
		 * @return string
		 */
		public function suffix() {
			return defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		}

		/**
		 * Returns the instance.
		 *
		 * @return object
		 * @since  1.0.0
		 */
		public static function get_instance() {

			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}

			return self::$instance;

		}

	}

}

/**
 * Returns instance of Jet_Woo_Product_Gallery_Assets
 *
 * @return object
 */
function jet_woo_product_gallery_assets() {
	return Jet_Woo_Product_Gallery_Assets::get_instance();
}

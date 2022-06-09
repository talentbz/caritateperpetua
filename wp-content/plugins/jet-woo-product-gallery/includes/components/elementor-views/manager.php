<?php
/**
 * JetGallery Elementor views manager.
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Gallery_Elementor_Views' ) ) {

	/**
	 * Define Jet_Gallery_Elementor_Views class.
	 */
	class Jet_Gallery_Elementor_Views {

		// Check if processing elementor widget.
		private $is_elementor_ajax = false;

		/**
		 * Constructor for the class.
		 */
		function __construct() {

			add_action( 'elementor/elements/categories_registered', [ $this, 'register_category' ] );
			add_action( 'elementor/widgets/widgets_registered', [ $this, 'register_widgets' ], 10 );
			add_action( 'wp_ajax_elementor_render_widget', [ $this, 'set_elementor_ajax' ], 10, -1 );

			add_action( 'elementor/preview/enqueue_scripts', [ $this, 'preview_scripts' ] );
			add_action( 'elementor/editor/after_enqueue_styles', [ $this, 'editor_styles' ] );

		}

		/**
		 * Enqueue preview scripts.
		 */
		public function preview_scripts() {
			jet_woo_product_gallery_assets()->enqueue_scripts();
		}

		/**
		 * Enqueue editor styles.
		 *
		 * @return void
		 */
		public function editor_styles() {
			wp_enqueue_style(
				'jet-gallery-icons',
				jet_woo_product_gallery()->plugin_url( 'assets/css/jet-gallery-icons.css' ),
				[],
				jet_woo_product_gallery()->get_version()
			);
		}

		/**
		 * Set $this->is_elementor_ajax to true on Elementor AJAX processing.
		 *
		 * @return  void
		 */
		public function set_elementor_ajax() {
			$this->is_elementor_ajax = true;
		}

		/**
		 * Check if we currently in Elementor mode.
		 *
		 * @return void
		 */
		public function in_elementor() {

			$result = false;

			if ( wp_doing_ajax() ) {
				$result = $this->is_elementor_ajax;
			} elseif ( Elementor\Plugin::instance()->editor->is_edit_mode() || Elementor\Plugin::instance()->preview->is_preview_mode() ) {
				$result = true;
			}

			return apply_filters( 'jet-woo-product-gallery/in-elementor', $result );

		}

		/**
		 * Register plugin widgets.
		 *
		 * @param object $widgets_manager Elementor widgets manager instance.
		 *
		 * @return void
		 */
		public function register_widgets( $widgets_manager ) {

			$gallery_available_widgets = jet_woo_product_gallery_settings()->get( 'product_gallery_available_widgets' );

			require jet_woo_product_gallery()->plugin_path( 'includes/components/elementor-views/widget-base.php' );

			foreach ( glob( jet_woo_product_gallery()->plugin_path( 'includes/widgets/' ) . '*.php' ) as $file ) {
				$slug    = basename( $file, '.php' );
				$enabled = isset( $gallery_available_widgets[ $slug ] ) ? $gallery_available_widgets[ $slug ] : '';

				if ( filter_var( $enabled, FILTER_VALIDATE_BOOLEAN ) || ! $gallery_available_widgets ) {
					$this->register_widget( $file, $widgets_manager );
				}
			}

		}

		/**
		 * Register addon by file name.
		 *
		 * @param string $file            File name.
		 * @param object $widgets_manager Widgets manager instance.
		 *
		 * @return void
		 */
		public function register_widget( $file, $widgets_manager ) {

			$base  = basename( str_replace( '.php', '', $file ) );
			$class = ucwords( str_replace( '-', ' ', $base ) );
			$class = str_replace( ' ', '_', $class );
			$class = sprintf( 'Elementor\%s', $class );

			require $file;

			if ( class_exists( $class ) ) {
				$widgets_manager->register_widget_type( new $class );
			}

		}

		/**
		 * Register category for elementor if not exists.
		 *
		 * @return void
		 */
		public function register_category() {

			$elements_manager = Elementor\Plugin::instance()->elements_manager;
			$jet_gallery_cat  = 'jet-woo-product-gallery';

			$elements_manager->add_category(
				$jet_gallery_cat,
				[
					'title' => esc_html__( 'JetGallery', 'jet-woo-product-gallery' ),
					'icon'  => 'font',
				]
			);

		}

	}

}
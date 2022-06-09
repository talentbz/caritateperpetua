<?php
/**
 * Class JetGallery Base.
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Gallery_Base' ) ) {

	/**
	 * Define Jet_Gallery_Base class.
	 */
	class Jet_Gallery_Base {

		/**
		 * Renderers list.
		 *
		 * @var array
		 */
		private $_renderers = [];

		/**
		 * Components list.
		 *
		 * @var array
		 */
		private $_components = [];

		/**
		 * Constructor for the class.
		 */
		function __construct() {

			add_action( 'init', [ $this, 'register_renderers' ] );
			add_action( 'init', [ $this, 'register_components' ], -2 );
			add_action( 'init', [ $this, 'init_components' ], -1 );

		}

		/**
		 * Register renderers classes.
		 */
		public function register_renderers() {

			$default_renderers = [
				'gallery-anchor-nav' => 'Jet_Gallery_Anchor_Nav',
				'gallery-grid'       => 'Jet_Gallery_Grid',
				'gallery-modern'     => 'Jet_Gallery_Modern',
				'gallery-slider'     => 'Jet_Gallery_Slider',
			];

			foreach ( $default_renderers as $render_name => $render_class ) {
				$render_data = [
					'class_name' => $render_class,
					'path'       => jet_woo_product_gallery()->plugin_path( 'includes/render/' . $render_name . '.php' ),
				];

				$this->register_render_class( $render_name, $render_data );
			}

		}

		/**
		 * Register components before run init to allow unregister before init.
		 *
		 * @return void
		 */
		public function register_components() {

			$default_components = [
				'elementor_views' => [
					'filepath'   => jet_woo_product_gallery()->plugin_path( 'includes/components/elementor-views/manager.php' ),
					'class_name' => 'Jet_Gallery_Elementor_Views',
				],
				'blocks_views'    => [
					'filepath'   => jet_woo_product_gallery()->plugin_path( 'includes/components/blocks-views/manager.php' ),
					'class_name' => 'Jet_Gallery_Blocks_Views',
				],
			];

			foreach ( $default_components as $component_slug => $component_data ) {
				$this->register_component( $component_slug, $component_data );
			}

		}

		/**
		 * Register JetGallery component.
		 *
		 * @param string $slug Component slug
		 * @param array  $data Component data
		 *
		 * @return void
		 */
		public function register_component( $slug = '', $data = array() ) {
			$this->_components[ $slug ] = $data;
		}

		/**
		 * Check if passed component is active.
		 *
		 * @param string $slug
		 *
		 * @return boolean
		 */
		public function is_component_active( $slug = '' ) {
			if ( ! $slug ) {
				return false;
			} else {
				return null !== jet_woo_product_gallery()->$slug;
			}
		}

		/**
		 * Initialize main components.
		 */
		public function init_components() {
			foreach ( $this->_components as $slug => $data ) {
				if ( ( empty( $data['class_name'] ) || ! class_exists( $data['class_name'] ) ) && file_exists( $data['filepath'] ) ) {
					$class_name = ! empty( $data['class_name'] ) ? $data['class_name'] : false;

					require_once $data['filepath'];

					if ( $class_name ) {
						jet_woo_product_gallery()->$slug = new $class_name();
					}
				}
			}
		}

		/**
		 * Unregister JetEngine component.
		 *
		 * @param string $slug Component slug
		 *
		 * @return void
		 */
		public function deregister_component( $slug = '' ) {
			if ( isset( $this->_components[ $slug ] ) ) {
				unset( $this->_components[ $slug ] );
			}
		}

		/**
		 * Register render class.
		 *
		 * @param string $name       Render gallery name
		 * @param array  $data       {
		 *    Array of arguments for registering a render class.
		 *
		 *    @type string  $class_name Class name.
		 *    @type string  $path       File path.
		 * }
		 */
		public function register_render_class( $name, $data ) {
			$this->_renderers[ $name ] = $data;
		}

		/**
		 * Render new gallery.
		 *
		 * @param null   $gallery
		 * @param array  $settings
		 * @param string $type
		 *
		 * @return void
		 */
		public function render_gallery( $gallery = null, $settings = [], $type = '' ) {

			$instance = $this->get_render_instance( $gallery, $settings, $type );

			$instance->render_content();

		}

		/**
		 * Returns current render instance.
		 *
		 * @param null   $gallery
		 * @param array  $settings
		 * @param string $type
		 *
		 * @return object|void
		 */
		public function get_render_instance( $gallery = null, $settings = [], $type = '' ) {

			$current_renderer = isset( $this->_renderers[ $gallery ] ) ? $this->_renderers[ $gallery ] : false;

			if ( ! $current_renderer ) {
				return;
			}

			if ( empty( $current_renderer['class_name'] ) || empty( $current_renderer['path'] ) ) {
				return;
			}

			if ( ! class_exists( 'Jet_Gallery_Render_Base' ) ) {
				require jet_woo_product_gallery()->plugin_path( 'includes/render/base.php' );
			}

			$renderer_class = $current_renderer['class_name'];

			if ( ! class_exists( $renderer_class ) ) {
				require $current_renderer['path'];
			}

			return new $renderer_class( $settings, $type );

		}

	}

}

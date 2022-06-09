<?php
/**
 * Jet Woo Product Gallery Settings Class
 *
 * @package   JetWooProductGallery
 * @author    Crocoblock
 * @license   GPL-2.0+
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Woo_Product_Gallery_Settings' ) ) {

	/**
	 * Define Jet_Woo_Product_Gallery_Settings class
	 */
	class Jet_Woo_Product_Gallery_Settings {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since  1.0.0
		 * @access private
		 * @var    object
		 */
		private static $instance = null;

		/**
		 * Contain settings key
		 *
		 * @var string
		 */
		public $key = 'jet-woo-product-gallery-settings';

		/**
		 * Settings holder
		 *
		 * @var null
		 */
		public $settings = null;

		/**
		 * Init page
		 */
		public function init() {
		}

		/**
		 * Returns localized data
		 *
		 * @return array
		 */
		public function get_localize_data() {

			$product_gallery_available_widgets = [];
			$default_product_gallery_widgets   = [];

			foreach ( glob( jet_woo_product_gallery()->plugin_path( 'includes/widgets/' ) . '*.php' ) as $file ) {
				$data = get_file_data( $file, array( 'class' => 'Class', 'name' => 'Name', 'slug' => 'Slug' ) );

				$slug = basename( $file, '.php' );

				$product_gallery_available_widgets[] = array(
					'label' => $data['name'],
					'value' => $slug,
				);

				$default_product_gallery_widgets[ $slug ] = 'true';
			}

			$rest_api_url = apply_filters( 'jet-woo-product-gallery/rest/frontend/url', get_rest_url() );

			return array(
				'messages'       => array(
					'saveSuccess' => esc_html__( 'Saved', 'jet-woo-builder' ),
					'saveError'   => esc_html__( 'Error', 'jet-woo-builder' ),
				),
				'settingsApiUrl' => $rest_api_url . 'jet-woo-product-gallery-api/v1/plugin-settings',
				'settingsData'   => array(
					'product_gallery_available_widgets' => array(
						'value'   => $this->get( 'product_gallery_available_widgets', $default_product_gallery_widgets ),
						'options' => $product_gallery_available_widgets,
					),
				),
			);

		}

		/**
		 * Return settings page URL
		 *
		 * @return string
		 */
		public function get_settings_page_link() {
			return add_query_arg(
				array(
					'page' => $this->key,
				),
				esc_url( admin_url( 'admin.php' ) )
			);
		}

		/**
		 * Returns settings
		 *
		 * @param $setting
		 * @param boolean $default
		 *
		 * @return bool|mixed
		 */
		public function get( $setting = '', $default = false ) {

			if ( null === $this->settings ) {
				$this->settings = get_option( $this->key, array() );
			}

			return isset( $this->settings[ $setting ] ) ? $this->settings[ $setting ] : $default;

		}


		/**
		 * Returns the instance.
		 *
		 * @return object
		 * @since  1.0.0
		 * @access public
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
 * Returns instance of Jet_Woo_Product_Gallery_Settings
 *
 * @return object
 */
function jet_woo_product_gallery_settings() {
	return Jet_Woo_Product_Gallery_Settings::get_instance();
}

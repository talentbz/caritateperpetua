<?php

namespace Jet_Woo_Product_Gallery;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Define Controller class
 */
class Settings {

	/**
	 * A reference to an instance of this class.
	 *
	 * @since 1.0.0
	 * @var   object
	 */
	private static $instance = null;

	/**
	 * Contain modules subpages
	 *
	 * @var array
	 */
	public $subpage_modules = array();

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

	// Here initialize our namespace and resource name.
	public function __construct() {

		$this->subpage_modules = apply_filters( 'jet-woo-product-gallery/settings/registered-subpage-modules', array(
			'jet-woo-product-gallery-avaliable-addons' => array(
				'class' => '\\Jet_Woo_Product_Gallery\\Settings\\Avaliable_Addons',
				'args'  => array(),
			),
		) );

		add_action( 'init', array( $this, 'register_settings_category' ), 10 );

		add_action( 'init', array( $this, 'init_plugin_subpage_modules' ), 10 );
	}

	/**
	 * Contain modules subpages
	 *
	 * @return void
	 */
	public function register_settings_category() {
		\Jet_Dashboard\Dashboard::get_instance()->module_manager->register_module_category( array(
			'name'     => esc_html__( 'JetWooProductGallery', 'jet-woo-product-gallery' ),
			'slug'     => 'jet-woo-product-gallery-settings',
			'priority' => 1,
		) );
	}

	/**
	 * Initialize plugin subpages modules
	 *
	 * @return void
	 */
	public function init_plugin_subpage_modules() {

		require jet_woo_product_gallery()->plugin_path( 'includes/settings/jet-dashboard-settings/subpage-modules/avaliable-addons.php' );

		foreach ( $this->subpage_modules as $subpage => $subpage_data ) {
			\Jet_Dashboard\Dashboard::get_instance()->module_manager->register_subpage_module( $subpage, $subpage_data );
		}

	}

}


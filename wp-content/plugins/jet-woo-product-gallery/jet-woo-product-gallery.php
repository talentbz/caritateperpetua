<?php
/**
 * Plugin Name: JetProductGallery For Elementor
 * Plugin URI:  https://crocoblock.com/plugins/jetproductgallery/
 * Description: Your perfect tool for creating WooCommerce Single Product Gallery
 * Version:     2.1.2
 * Author:      Crocoblock
 * Author URI:  https://crocoblock.com/
 * Text Domain: jet-woo-product-gallery
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /languages
 *
 * Elementor tested up to: 3.4
 * Elementor Pro tested up to: 3.5
 *
 * WC tested up to: 5.9
 * WC requires at least: 3.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}

// If class `Jet_Woo_Product_Gallery` doesn't exists yet.
if ( ! class_exists( 'Jet_Woo_Product_Gallery' ) ) {

	/**
	 * Sets up and initializes the plugin.
	 */
	class Jet_Woo_Product_Gallery {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since  1.0.0
		 * @access private
		 * @var    object
		 */
		private static $instance = null;

		/**
		 * Plugin version
		 *
		 * @var string
		 */
		private $version = '2.1.2';

		/**
		 * Require Elementor Version
		 *
		 * @var string Elementor version required to run the plugin.
		 */
		private static $require_elementor_version = '3.0.0';

		/**
		 * Holder for base plugin path
		 *
		 * @since  1.0.0
		 * @access private
		 * @var    string
		 */
		private $plugin_path = null;

		/**
		 * Holder for base plugin URL
		 *
		 * @since  1.0.0
		 * @access private
		 * @var    string
		 */
		private $plugin_url = null;

		/**
		 * Framework loader instance
		 *
		 * @var object
		 */
		public $module_loader;

		/**
		 * Plugin base instance
		 *
		 * @var Jet_Gallery_Base
		 */
		public $base = null;

		/**
		 * Sets up needed actions/filters for the plugin to initialize.
		 *
		 * @return void
		 * @since  1.0.0
		 * @access public
		 */
		public function __construct() {

			// Check if Elementor installed and activated.
			if ( did_action( 'elementor/loaded' ) ) {
				// Check for required Elementor version
				if ( ! version_compare( ELEMENTOR_VERSION, self::$require_elementor_version, '>=' ) ) {
					add_action( 'admin_notices', [ $this, 'admin_notice_required_elementor_version' ] );
					return;
				}
			}

			// Load the core functions/classes required by the rest of the plugin.
			add_action( 'after_setup_theme', array( $this, 'module_loader' ), -20 );
			// Internationalize the text strings used.
			add_action( 'init', array( $this, 'lang' ), -999 );
			// Load files.
			add_action( 'init', array( $this, 'init' ), -999 );
			// Jet Dashboard Init
			add_action( 'init', array( $this, 'jet_dashboard_init' ), -999 );

			// Register activation and deactivation hook.
			register_activation_hook( __FILE__, array( $this, 'activation' ) );
			register_deactivation_hook( __FILE__, array( $this, 'deactivation' ) );

		}

		/**
		 * Load plugin framework
		 */
		public function module_loader() {

			require $this->plugin_path( 'includes/modules/loader.php' );

			$this->module_loader = new Jet_Woo_Product_Gallery_CX_Loader(
				array(
					$this->plugin_path( 'includes/modules/interface-builder/cherry-x-interface-builder.php' ),
					$this->plugin_path( 'includes/modules/post-meta/cherry-x-post-meta.php' ),
					$this->plugin_path( 'includes/modules/vue-ui/cherry-x-vue-ui.php' ),
					$this->plugin_path( 'includes/modules/jet-dashboard/jet-dashboard.php' ),
				)
			);

		}

		/**
		 * Returns plugin version
		 *
		 * @return string
		 */
		public function get_version() {
			return $this->version;
		}

		/**
		 * Manually init required modules.
		 *
		 * @return void
		 */
		public function init() {

			$this->load_files();

			jet_woo_product_gallery_assets()->init();
			jet_woo_gallery_video_integration()->init();
			jet_woo_product_gallery_settings()->init();

			$this->base = new Jet_Gallery_Base();

			add_action( 'wp_footer', array( $this, 'photoswipe_template' ) );

			//Init Rest Api
			new \Jet_Woo_Product_Gallery\Rest_Api();

			if ( is_admin() ) {
				//Init JetWooProductGallery Settings
				new \Jet_Woo_Product_Gallery\Settings();
			}

		}

		/**
		 * JetDashboard module initialize
		 *
		 * @return void
		 */
		public function jet_dashboard_init() {
			if ( is_admin() ) {

				$jet_dashboard_module_data = $this->module_loader->get_included_module_data( 'jet-dashboard.php' );
				$jet_dashboard             = \Jet_Dashboard\Dashboard::get_instance();

				$jet_dashboard->init(
					array(
						'path'           => $jet_dashboard_module_data['path'],
						'url'            => $jet_dashboard_module_data['url'],
						'cx_ui_instance' => array( $this, 'jet_dashboard_ui_instance_init' ),
						'plugin_data'    => array(
							'slug'         => 'jet-woo-product-gallery',
							'file'         => 'jet-woo-product-gallery/jet-woo-product-gallery.php',
							'version'      => $this->get_version(),
							'plugin_links' => array(
								array(
									'label'  => esc_html__( 'Go to settings', 'jet-woo-product-gallery' ),
									'url'    => add_query_arg( array( 'page' => 'jet-dashboard-settings-page', 'subpage' => 'jet-woo-product-gallery-avaliable-addons' ), admin_url( 'admin.php' ) ),
									'target' => '_self',
								),
							),
						),
					)
				);
			}
		}

		/**
		 * JetDashboard module UI instance initialize
		 *
		 * @return object
		 */
		public function jet_dashboard_ui_instance_init() {

			$cx_ui_module_data = $this->module_loader->get_included_module_data( 'cherry-x-vue-ui.php' );

			return new CX_Vue_UI( $cx_ui_module_data );

		}


		/**
		 * Require photoswipe template.
		 *
		 * @return void
		 */
		public function photoswipe_template() {
			require $this->plugin_path( 'templates/photoswipe.php' );
		}

		/**
		 * Returns utility instance
		 *
		 * @return object
		 */
		public function utility() {

			$utility = $this->get_core()->modules['cherry-utility'];

			return $utility->utility;

		}

		/**
		 * Load required files.
		 *
		 * @return void
		 */
		public function load_files() {

			require $this->plugin_path( 'includes/class-jet-woo-product-gallery-assets.php' );
			require $this->plugin_path( 'includes/class-jet-woo-product-gallery-tools.php' );

			require $this->plugin_path( 'includes/core/base.php' );

			require $this->plugin_path( 'includes/classes/embed.php' );

			require $this->plugin_path( 'includes/integrations/class-jet-woo-gallery-video-integration.php' );

			require $this->plugin_path( 'includes/settings/class-jet-woo-product-gallery-settings.php' );
			require $this->plugin_path( 'includes/settings/jet-dashboard-settings/manager.php' );

			require $this->plugin_path( 'includes/rest-api/rest-api.php' );
			require $this->plugin_path( 'includes/rest-api/endpoints/base.php' );
			require $this->plugin_path( 'includes/rest-api/endpoints/plugin-settings.php' );

		}

		/**
		 * Returns path to file or dir inside plugin folder
		 *
		 * @param string $path Path inside plugin dir.
		 *
		 * @return string
		 */
		public function plugin_path( $path = null ) {

			if ( ! $this->plugin_path ) {
				$this->plugin_path = trailingslashit( plugin_dir_path( __FILE__ ) );
			}

			return $this->plugin_path . $path;

		}

		/**
		 * Returns url to file or dir inside plugin folder
		 *
		 * @param string $path Path inside plugin dir.
		 *
		 * @return string
		 */
		public function plugin_url( $path = null ) {

			if ( ! $this->plugin_url ) {
				$this->plugin_url = trailingslashit( plugin_dir_url( __FILE__ ) );
			}

			return $this->plugin_url . $path;

		}

		/**
		 * Loads the translation files.
		 *
		 * @return void
		 * @since  1.0.0
		 * @access public
		 */
		public function lang() {
			load_plugin_textdomain( 'jet-woo-product-gallery', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		}

		/**
		 * Get the template path.
		 *
		 * @return string
		 */
		public function template_path() {
			return apply_filters( 'jet-woo-product-gallery/template-path', 'jet-woo-product-gallery/' );
		}

		/**
		 * Returns path to template file.
		 *
		 * @return string|bool
		 */
		public function get_template( $name = null ) {

			$template = locate_template( $this->template_path() . $name );

			if ( ! $template ) {
				$template = $this->plugin_path( 'templates/' . $name );
			}

			if ( file_exists( $template ) ) {
				return $template;
			} else {
				return false;
			}

		}

		/**
		 * Admin notice
		 *
		 * Warning when the site doesn't have a minimum required Elementor version.
		 *
		 * @access public
		 */
		public function admin_notice_required_elementor_version() {

			if ( isset( $_GET['activate'] ) ) {
				unset( $_GET['activate'] );
			}

			$message = sprintf(
			/* translators: 1: Plugin name 2: Elementor 3: Required Elementor version */
				esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'jet-woo-product-gallery' ),
				'<strong>' . esc_html__( 'JetProductGallery', 'jet-woo-product-gallery' ) . '</strong>',
				'<strong>' . esc_html__( 'Elementor', 'jet-woo-product-gallery' ) . '</strong>',
				self::$require_elementor_version
			);

			printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

		}

		/**
		 * Do some stuff on plugin activation
		 *
		 * @return void
		 * @since  1.0.0
		 */
		public function activation() {
		}

		/**
		 * Do some stuff on plugin activation
		 *
		 * @return void
		 * @since  1.0.0
		 */
		public function deactivation() {
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

if ( ! function_exists( 'jet_woo_product_gallery' ) ) {

	/**
	 * Returns instance of the plugin class.
	 *
	 * @return object
	 * @since  1.0.0
	 */
	function jet_woo_product_gallery() {
		return Jet_Woo_Product_Gallery::get_instance();
	}

}

jet_woo_product_gallery();

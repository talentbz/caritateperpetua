<?php

namespace Jet_Woo_Product_Gallery\Settings;

use Jet_Dashboard\Base\Page_Module as Page_Module_Base;
use Jet_Dashboard\Dashboard as Dashboard;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Avaliable_Addons extends Page_Module_Base {

	/**
	 * Returns module slug
	 *
	 * @return void
	 */
	public function get_page_slug() {
		return 'jet-woo-product-gallery-avaliable-addons';
	}

	/**
	 * Returns parent slug
	 *
	 * @return string
	 */
	public function get_parent_slug() {
		return 'settings-page';
	}

	/**
	 * Returns page name
	 *
	 * @return string
	 */
	public function get_page_name() {
		return esc_html__( 'Widgets', 'jet-woo-product-gallery' );
	}

	/**
	 * Returns category
	 *
	 * @return string
	 */
	public function get_category() {
		return 'jet-woo-product-gallery-settings';
	}

	/**
	 * Returns page link
	 *
	 * @return string
	 */
	public function get_page_link() {
		return Dashboard::get_instance()->get_dashboard_page_url( $this->get_parent_slug(), $this->get_page_slug() );
	}

	/**
	 * Enqueue module-specific assets
	 *
	 * @return void
	 */
	public function enqueue_module_assets() {

		wp_enqueue_style(
			'jet-woo-product-gallery-admin-css',
			jet_woo_product_gallery()->plugin_url( 'assets/css/jet-woo-product-gallery-admin.css' ),
			false,
			jet_woo_product_gallery()->get_version()
		);

		wp_enqueue_script(
			'jet-woo-product-gallery-admin-vue-components',
			jet_woo_product_gallery()->plugin_url( 'assets/js/admin-vue-components.min.js' ),
			array( 'cx-vue-ui' ),
			jet_woo_product_gallery()->get_version(),
			true
		);

		wp_localize_script(
			'jet-woo-product-gallery-admin-vue-components',
			'JetWooProductGallerySettingsPageConfig',
			apply_filters( 'jet-woo-builder/admin/settings-page/localized-config', jet_woo_product_gallery_settings()->get_localize_data() )
		);

	}

	/**
	 * License page config
	 *
	 * @param array $config
	 * @param bool  $page
	 * @param bool  $subpage
	 *
	 * @return array
	 */
	public function page_config( $config = array(), $page = false, $subpage = false ) {

		$config['pageModule']    = $this->get_parent_slug();
		$config['subPageModule'] = $this->get_page_slug();

		return $config;

	}

	/**
	 * Add page templates
	 *
	 * @param array $templates
	 * @param bool  $page
	 * @param bool  $subpage
	 *
	 * @return array
	 */
	public function page_templates( $templates = array(), $page = false, $subpage = false ) {

		$templates['jet-woo-product-gallery-avaliable-addons'] = jet_woo_product_gallery()->plugin_path( 'templates/admin-templates/avaliable-addons.php' );

		return $templates;

	}

}

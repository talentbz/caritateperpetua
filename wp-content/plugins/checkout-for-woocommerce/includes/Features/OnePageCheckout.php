<?php

namespace Objectiv\Plugins\Checkout\Features;

use Objectiv\Plugins\Checkout\Admin\Pages\PageAbstract;
use Objectiv\Plugins\Checkout\Managers\SettingsManager;

class OnePageCheckout extends FeaturesAbstract {
	protected function run_if_cfw_is_enabled() {
		add_action( 'template_redirect', array( $this, 'one_page_checkout_layout' ), 0 );
		add_filter( 'cfw_event_data', array( $this, 'add_localized_settings' ) );
		add_filter( 'cfw_checkout_main_container_classes', array( $this, 'add_class_to_main_container' ) );
	}

	public function one_page_checkout_layout() {
		// Remove breadcrumbs
		remove_action( 'cfw_checkout_before_order_review', 'cfw_breadcrumb_navigation', 10 );
		remove_action( 'cfw_checkout_main_container_start', 'futurist_breadcrumb_navigation', 10 );

		// Remove customer info tab nav
		remove_action( 'cfw_checkout_customer_info_tab', 'cfw_customer_info_tab_nav', 50 );

		// Remove shipping address review
		remove_action( 'cfw_checkout_shipping_method_tab', 'cfw_shipping_method_address_review_pane', 10 );

		// Remove shipping tab nav
		remove_action( 'cfw_checkout_shipping_method_tab', 'cfw_shipping_method_tab_nav', 30 );

		// Remove payment tab address review
		remove_action( 'cfw_checkout_payment_method_tab', 'cfw_payment_method_address_review_pane', 0 );

		// Remove payment tab navigation
		remove_action( 'cfw_checkout_payment_method_tab', 'cfw_payment_tab_nav', 50 );
		add_action( 'cfw_checkout_payment_method_tab', 'cfw_payment_tab_nav_one_page_checkout', 50 );
	}

	/**
	 * @param array $event_data
	 * @return array
	 */
	public function add_localized_settings( array $event_data ): array {
		$event_data['settings']['enable_one_page_checkout'] = true;

		return $event_data;
	}

	/**
	 * @param string $classes
	 * @return string
	 */
	public function add_class_to_main_container( string $classes ): string {
		$classes .= ' cfw-one-page-checkout';

		return $classes;
	}

	public function init() {
		parent::init();

		add_action( 'cfw_do_plugin_activation', array( $this, 'run_on_plugin_activation' ) );
		add_action( 'cfw_checkout_after_main_admin_page_controls', array( $this, 'output_settings' ) );
	}

	/**
	 * @param PageAbstract $checkout_admin_page
	 */
	public function output_settings( PageAbstract $checkout_admin_page ) {
		if ( ! $this->available ) {
			$notice = $checkout_admin_page->get_upgrade_required_notice( $this->required_plans_list );
		}

		$checkout_admin_page->output_checkbox_row(
			'enable_one_page_checkout',
			cfw__( 'One Page Checkout', 'checkout-wc' ),
			cfw__( 'Enable one page checkout.', 'checkout-wc' ),
			cfw__( 'Show all checkout fields on one tab. Useful for digital stores or stores that force the billing and shipping address to be the same. (Cannot be used with Order Review Step)', 'checkout-wc' ),
			$this->available,
			$notice ?? ''
		);
	}

	public function run_on_plugin_activation() {
		SettingsManager::instance()->add_setting( 'enable_one_page_checkout', 'no' );
	}
}

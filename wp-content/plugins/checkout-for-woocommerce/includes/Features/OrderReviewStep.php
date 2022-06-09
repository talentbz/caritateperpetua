<?php

namespace Objectiv\Plugins\Checkout\Features;

use Objectiv\Plugins\Checkout\Admin\Pages\PageAbstract;
use Objectiv\Plugins\Checkout\Managers\SettingsManager;

class OrderReviewStep extends FeaturesAbstract {
	protected function run_if_cfw_is_enabled() {
		add_action( 'template_redirect', array( $this, 'order_review_tab_layout' ), 0 );
	}

	public function unhook() {
		remove_action( 'template_redirect', array( $this, 'order_review_tab_layout' ), 0 );
	}

	public function order_review_tab_layout() {
		if ( defined( 'CFW_SUPPRESS_ORDER_REVIEW_TAB' ) ) {
			return;
		}

		// Move payment tab nav and terms and conditions to order review
		remove_action( 'cfw_checkout_payment_method_tab', 'cfw_payment_tab_nav', 50 );
		remove_action( 'cfw_checkout_payment_method_tab', 'cfw_payment_tab_content_terms_and_conditions', 40 );

		// Add new payment tab nav
		add_action( 'cfw_checkout_payment_method_tab', 'cfw_payment_method_tab_review_nav', 50, 0 );

		// Add order review to breadcrumbs
		add_filter( 'cfw_breadcrumbs', 'cfw_add_order_review_step_breadcrumb' );

		// Add order review tab
		add_action( 'cfw_checkout_tabs', 'cfw_add_order_review_step_tab', 40 );

		/**
		 * Order Review Tab Content
		 */
		add_action( 'cfw_checkout_order_review_tab', 'cfw_order_review_tab_heading', 10 );
		add_action( 'cfw_checkout_order_review_tab', 'cfw_order_review_step_review_pane', 20 );
		add_action( 'cfw_checkout_order_review_tab', 'cfw_order_review_step_totals_review_pane', 30 );
		add_action( 'cfw_checkout_order_review_tab', 'cfw_payment_tab_content_terms_and_conditions', 40, 0 );
		add_action( 'cfw_checkout_order_review_tab', 'cfw_order_review_tab_nav', 50, 0 );
	}

	public function init() {
		parent::init();

		add_action( 'cfw_do_plugin_activation', array( $this, 'run_on_plugin_activation' ) );
		add_action( 'cfw_checkout_after_main_admin_page_controls', array( $this, 'output_admin_settings' ) );
	}

	public function output_admin_settings( PageAbstract $checkout_admin_page ) {
		if ( ! $this->available ) {
			$notice = $checkout_admin_page->get_upgrade_required_notice( $this->required_plans_list );
		}

		$checkout_admin_page->output_checkbox_row(
			'enable_order_review_step',
			cfw__( 'Order Review Step', 'checkout-wc' ),
			cfw__( 'Enable Order Review Step.', 'checkout-wc' ),
			cfw__( 'Adds a review step after payment information before finalizing order. Useful for jurisdictions which require additional confirmation before order submission. (Cannot be used with One Page Checkout)', 'checkout-wc' ),
			$this->available,
			$notice ?? ''
		);
	}

	public function run_on_plugin_activation() {
		SettingsManager::instance()->add_setting( 'enable_order_review_step', 'no' );
	}
}

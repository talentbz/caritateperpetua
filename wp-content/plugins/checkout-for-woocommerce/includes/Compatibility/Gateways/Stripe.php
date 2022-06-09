<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Gateways;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class Stripe extends CompatibilityAbstract {

	protected $stripe_request_button_height = '35';

	public function is_available(): bool {
		return defined( 'WC_STRIPE_VERSION' ) && version_compare( WC_STRIPE_VERSION, '4.0.0' ) >= 0;
	}

	public function pre_init() {
		// If this filter returns true, override the btn height settings in 2 places
		/**
		 * Filters whether to override Stripe payment request button heights
		 *
		 * @since 2.0.0
		 *
		 * @param bool $override Whether to override Stripe payment request button heights
		 */
		if ( apply_filters( 'cfw_stripe_compat_override_request_btn_height', true ) ) {
			add_filter( 'option_woocommerce_stripe_settings', array( $this, 'override_btn_height_settings_on_update' ), 10, 1 );
			add_filter( 'wc_stripe_settings', array( $this, 'filter_default_settings' ), 1 );
		}

		/**
		 * Filters whether to override Stripe payment request button heights
		 *
		 * @since 4.3.3
		 *
		 * @param bool $allow Whether to ignore shipping phone requirement during payment requests
		 */
		if ( apply_filters( 'cfw_stripe_payment_requests_ignore_shipping_phone', true ) ) {
			add_action( 'wc_ajax_wc_stripe_create_order', array( $this, 'process_payment_request_ajax_checkout' ), 1 );
		}
	}

	public function run() {
		// Apple Pay
		$this->add_payment_request_buttons();
	}

	public function override_btn_height_settings_on_update( $value ) {
		$value['payment_request_button_height'] = $this->stripe_request_button_height;

		return $value;
	}

	public function filter_default_settings( $settings ) {
		$settings['payment_request_button_height']['default'] = $this->stripe_request_button_height;

		return $settings;
	}

	public function add_payment_request_buttons() {
		// Setup Apple Pay
		if ( class_exists( '\\WC_Stripe_Payment_Request' ) && cfw_is_checkout() ) {
			$stripe_payment_request = \WC_Stripe_Payment_Request::instance();

			add_filter( 'wc_stripe_show_payment_request_on_checkout', '__return_true' );

			// Remove default stripe request placement
			remove_action( 'woocommerce_checkout_before_customer_details', array( $stripe_payment_request, 'display_payment_request_button_html' ), 1 );
			remove_action( 'woocommerce_checkout_before_customer_details', array( $stripe_payment_request, 'display_payment_request_button_separator_html' ), 2 );

			// Add our own stripe requests
			add_action( 'cfw_payment_request_buttons', array( $stripe_payment_request, 'display_payment_request_button_html' ), 1 );
			add_action( 'cfw_checkout_customer_info_tab', array( $this, 'add_payment_request_separator' ), 12 ); // This should be 12, which is after 11, which is the hook other gateways use
		}
	}

	public function add_payment_request_separator() {
		cfw_add_separator( '', 'wc-stripe-payment-request-button-separator', 'text-align: center;' );
	}

	public function process_payment_request_ajax_checkout() {
		$payment_request_type = isset( $_POST['payment_request_type'] ) ? wc_clean( $_POST['payment_request_type'] ) : '';

		// Disable shipping phone validation when using payment request
		if ( ! empty( $payment_request_type ) ) {
			add_filter(
				'woocommerce_checkout_fields',
				function( $fields ) {
					$shipping_phone = $fields['shipping']['shipping_phone'];

					if ( $shipping_phone ) {
						$fields['shipping']['shipping_phone']['required'] = false;
						$fields['shipping']['shipping_phone']['validate'] = array();
					}

					return $fields;
				},
				1
			);
		}
	}

	public function typescript_class_and_params( array $compatibility ): array {
		$compatibility[] = array(
			'class'  => 'Stripe',
			'params' => array(),
		);

		return $compatibility;
	}
}

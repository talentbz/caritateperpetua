<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class SkyVergeCheckoutAddons extends CompatibilityAbstract {
	public function is_available(): bool {
		return function_exists( 'init_woocommerce_checkout_add_ons' ) || class_exists( '\\WC_Checkout_Add_Ons_Loader' );
	}

	public function run_immediately() {
		add_filter( 'cfw_use_floating_label', array( $this, 'disable_floating_label' ), 10, 2 );

		$types = array(
			'wc_checkout_add_ons_multicheckbox',
			'wc_checkout_add_ons_multiselect',
			'wc_checkout_add_ons_radio',
			'wc_checkout_add_ons_file',
			'wc_checkout_add_ons_checkbox',
		);

		foreach ( $types as $type ) {
			add_filter( 'cfw_form_field_element_' . $type, '__return_empty_string', 0 );
		}
	}

	public function run() {
		add_filter( 'wc_checkout_add_ons_position', array( $this, 'set_checkout_add_ons_position' ) );
	}

	public function disable_floating_label( $use, $type ) {
		$types = array(
			'wc_checkout_add_ons_multicheckbox',
			'wc_checkout_add_ons_multiselect',
			'wc_checkout_add_ons_radio',
			'wc_checkout_add_ons_file',
			'wc_checkout_add_ons_checkbox',
		);

		if ( in_array( $type, $types, true ) ) {
			$use = false;
		}

		return $use;
	}

	public function set_checkout_add_ons_position() {
		return 'cfw_checkout_before_payment_method_terms_checkbox';
	}
}

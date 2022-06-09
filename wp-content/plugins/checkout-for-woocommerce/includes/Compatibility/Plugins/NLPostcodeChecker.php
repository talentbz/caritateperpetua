<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;
use Objectiv\Plugins\Checkout\FormAugmenter;

class NLPostcodeChecker extends CompatibilityAbstract {
	public function is_available(): bool {
		return class_exists( '\\WPO_WCNLPC_Checkout' );
	}

	public function run() {
		$this->disable_nl_hooks();

		remove_filter( 'woocommerce_get_country_locale', array( FormAugmenter::instance(), 'prevent_postcode_sort_change' ) );

		add_filter( 'woocommerce_default_address_fields', array( $this, 'modify_fields' ), 100001, 1 ); // run after our normal hook
		add_filter( 'woocommerce_get_country_locale', array( $this, 'prevent_postcode_sort_change' ), 100 );
		add_filter( 'cfw_enable_zip_autocomplete', '__return_false' );

		// Fix shipping preview
		add_filter( 'cfw_get_shipping_details_address', array( $this, 'fix_shipping_preview' ), 10, 2 );
	}

	public function disable_nl_hooks() {
		global $wp_filter;

		$existing_hooks = $wp_filter['woocommerce_billing_fields'];

		if ( $existing_hooks[100] ) {
			foreach ( $existing_hooks[100] as $key => $callback ) {
				if ( false !== stripos( $key, 'nl_billing_fields' ) ) {
					global $WPO_WCNLPC_Checkout;

					$WPO_WCNLPC_Checkout = $callback['function'][0];
				}
			}
		}

		$priority = apply_filters( 'nl_checkout_fields_priority', 9 );

		if ( $existing_hooks[ $priority ] ) {
			foreach ( $existing_hooks[ $priority ] as $key => $callback ) {
				if ( false !== stripos( $key, 'nl_billing_fields' ) ) {
					global $WC_NLPostcode_Fields;

					$WC_NLPostcode_Fields = $callback['function'][0];
				}
			}
		}

		if ( ! empty( $WPO_WCNLPC_Checkout ) ) {
			remove_filter( 'woocommerce_billing_fields', array( $WPO_WCNLPC_Checkout, 'nl_billing_fields' ), 100 );
			remove_filter( 'woocommerce_shipping_fields', array( $WPO_WCNLPC_Checkout, 'nl_shipping_fields' ), 100 );
		}

		if ( ! empty( $WC_NLPostcode_Fields ) ) {
			remove_filter( 'woocommerce_billing_fields', array( $WC_NLPostcode_Fields, 'nl_billing_fields' ), $priority );
			remove_filter( 'woocommerce_shipping_fields', array( $WC_NLPostcode_Fields, 'nl_shipping_fields' ), $priority );
			remove_action( 'woocommerce_checkout_update_order_meta', array( &$WC_NLPostcode_Fields, 'merge_street_number_suffix' ), 20 );
		}
	}

	public function modify_fields( $fields ) {
		// Adjust postcode field
		$fields['postcode']['priority'] = 11;

		// Add street name
		$fields['street_name'] = array(
			'label'             => cfw__( 'Street name', 'wpo_wcnlpc' ),
			'placeholder'       => cfw_esc_attr__( 'Street name', 'wpo_wcnlpc' ),
			'required'          => true,
			'class'             => array(),
			'autocomplete'      => '',
			'input_class'       => array( 'garlic-auto-save' ),
			'priority'          => 14,
			'columns'           => 12,
			'custom_attributes' => array(
				'data-parsley-trigger' => 'change focusout',
			),
		);

		// Then add house number
		$fields['house_number'] = array(
			'label'             => cfw__( 'Nr.', 'wpo_wcnlpc' ),
			'placeholder'       => cfw_esc_attr__( 'Nr.', 'wpo_wcnlpc' ),
			'required'          => true,
			'class'             => array(),
			'autocomplete'      => '',
			'input_class'       => array( 'garlic-auto-save' ),
			'priority'          => 12,
			'columns'           => 4,
			'custom_attributes' => array(
				'data-parsley-trigger' => 'change focusout',
			),
		);

		// Then house number suffix
		$fields['house_number_suffix'] = array(
			'label'             => cfw_x( 'Suffix', 'full string', 'wpo_wcnlpc' ),
			'placeholder'       => cfw_esc_attr_x( 'Suffix', 'full string', 'wpo_wcnlpc' ),
			'required'          => false,
			'class'             => array(),
			'autocomplete'      => '',
			'input_class'       => array( 'garlic-auto-save' ),
			'priority'          => 13,
			'columns'           => 4,
			'custom_attributes' => array(
				'data-parsley-trigger' => 'change focusout',
			),
		);

		$fields['state']['columns'] = 8;

		// Set address 1 / address 2 to hidden
		$fields['address_1']['type']  = 'hidden';
		$fields['address_1']['start'] = false;
		unset( $fields['address_1']['custom_attributes'] );
		unset( $fields['address_1']['input_class'] );
		$fields['address_2']['type'] = 'hidden';
		$fields['address_2']['end']  = false;
		unset( $fields['address_2']['custom_attributes'] );
		unset( $fields['address_2']['input_class'] );

		return $fields;
	}

	public function prevent_postcode_sort_change( $locales ) {
		foreach ( $locales as $key => $value ) {
			if ( ! empty( $value['postcode'] ) && ! empty( $value['postcode']['priority'] ) ) {
				$locales[ $key ]['postcode']['priority'] = 11;
			}
		}

		return $locales;
	}

	public function fix_shipping_preview( $address, $checkout ) {
		$address['address_1'] = $checkout->get_value( 'shipping_street_name' ) . ' ' . $checkout->get_value( 'shipping_house_number' );

		if ( ! empty( $checkout->get_value( 'shipping_house_number_suffix' ) ) ) {
			$address['address_1'] = $address['address_1'] . '-' . $checkout->get_value( 'shipping_house_number_suffix' );
		}

		return $address;
	}


	public function typescript_class_and_params( array $compatibility ): array {
		$compatibility[] = array(
			'class'  => 'NLPostcodeChecker',
			'params' => array(),
		);

		return $compatibility;
	}
}

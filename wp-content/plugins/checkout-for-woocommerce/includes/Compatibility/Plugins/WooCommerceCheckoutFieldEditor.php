<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;
use Objectiv\Plugins\Checkout\Admin;
use Objectiv\Plugins\Checkout\Managers\SettingsManager;

class WooCommerceCheckoutFieldEditor extends CompatibilityAbstract {
	public function is_available(): bool {
		return defined( 'WC_CHECKOUT_FIELD_EDITOR_VERSION' );
	}

	public function pre_init() {
		add_action( 'cfw_admin_integrations_settings', array( $this, 'admin_integration_settings' ) );
		add_filter( 'woocommerce_custom_checkout_position', array( $this, 'add_additional_field_positions' ) );

		if ( SettingsManager::instance()->get_setting( 'allow_checkout_field_editor_address_modification' ) !== 'yes' ) {
			// Add styles for WooCommerce Checkout Field Editor admin page
			add_action( 'admin_head', array( $this, 'output_custom_styles' ) );
			add_action( 'admin_init', array( $this, 'maybe_redirect_to_additional_fields_tab' ) );
		}
	}

	public function run_immediately() {
		add_filter( 'woocommerce_enable_order_notes_field', array( $this, 'enable_notes_field' ) );

		if ( SettingsManager::instance()->get_setting( 'allow_checkout_field_editor_address_modification' ) === 'yes' ) {
			add_filter( 'option_wc_fields_billing', array( $this, 'cleanup_classes' ) );
			add_filter( 'option_wc_fields_shipping', array( $this, 'cleanup_classes' ) );
		} else {
			remove_filter( 'woocommerce_billing_fields', 'wc_checkout_fields_modify_billing_fields', 1 );
			remove_filter( 'woocommerce_shipping_fields', 'wc_checkout_fields_modify_shipping_fields', 1 );
		}
	}

	public function run() {
		remove_filter( 'woocommerce_form_field_date', 'wc_checkout_fields_date_picker_field', 10 );
		remove_action( 'wp_enqueue_scripts', 'wc_checkout_fields_dequeue_address_i18n', 15 );

		add_filter( 'cfw_form_field_element_date', array( $this, 'date_field_element' ), 10, 4 );

		add_filter( 'woocommerce_form_field_multiselect', array( $this, 'fix_fields' ), 100, 5 );
		add_filter( 'woocommerce_form_field_radio', array( $this, 'fix_fields' ), 100, 5 );
	}

	/**
	 * @param Admin\Pages\PageAbstract $integrations
	 */
	public function admin_integration_settings( Admin\Pages\PageAbstract $integrations ) {
		if ( ! $this->is_available() ) {
			return;
		}

		$settings = SettingsManager::instance();
		?>
		<tr>
			<th scope="row" valign="top">
				<label for="<?php echo $settings->get_field_name( 'allow_checkout_field_editor_address_modification' ); ?>"><?php cfw_e( 'Checkout Field Editor', 'checkout-wc' ); ?></label>
			</th>
			<td>
				<input type="hidden" name="<?php echo $settings->get_field_name( 'allow_checkout_field_editor_address_modification' ); ?>" value="no" />
				<label><input type="checkbox" name="<?php echo $settings->get_field_name( 'allow_checkout_field_editor_address_modification' ); ?>" id="<?php echo$settings->get_field_name( 'allow_checkout_field_editor_address_modification' ); ?>" value="yes" <?php echo $settings->get_setting( 'allow_checkout_field_editor_address_modification' ) === 'yes' ? 'checked' : ''; ?> /> <?php cfw_e( 'Enable Checkout Field Editor address field overrides.', 'checkout-wc' ); ?></label>
				<p><span class="description"><?php cfw_e( 'Allow WooCommerce Checkout Field Editor to modify billing and shipping address fields. (Not Recommended)', 'checkout-wc' ); ?></span></p>
			</td>
		</tr>
		<?php
	}

	public function cleanup_classes( $address_fields ) {
		foreach ( $address_fields as $field_key => $field ) {
			if ( is_array( $field['class'] ) ) {
				// Only allow one (and the last) col-lg-* class
				$col_class_indexes = array();
				$last_index        = 0;

				foreach ( $field['class'] as $index => $class ) {
					if ( stripos( $class, 'col-lg-' ) !== false ) {
						$col_class_indexes[] = $index;
						$last_index          = $index;
					}
				}

				foreach ( $col_class_indexes as $index_to_remove ) {
					if ( $last_index !== $index_to_remove ) {
						unset( $field['class'][ $index_to_remove ] );
					}
				}

				// Remove duplicate classes from WooCommerce Checkout Field Editor
				$field['class'] = array_unique( $field['class'] );
			}

			// Update field array
			$address_fields[ $field_key ] = $field;
		}

		return $address_fields;
	}

	public function fix_fields( $field, $key, $args, $value, $row_wrap ) {
		if ( in_array( $args['type'], array( 'multiselect', 'radio' ), true ) && isset( $row_wrap ) ) {
			$row_wrap = str_replace( 'form-row ', 'cfw-input-wrap cfw-floating-label ', $row_wrap );
			$field    = str_replace( array( 'form-row-first', 'form-row-last' ), 'col-lg-12', $field );
			$field    = $row_wrap . $field . '</div>';
		}

		return $field;
	}

	public function add_additional_field_positions( $positions ) {
		return array(
			'cfw-col-3'      => '25% Width',
			'cfw-col-4'      => '33% Width',
			'form-row-first' => '50% Width',
			'cfw-col-8'      => '67% Width',
			'cfw-col-9'      => '75% Width',
			'form-row-wide'  => cfw__( 'Full-width', 'woocommerce-checkout-field-editor' ),
		);
	}

	public function enable_notes_field() {
		return  'yes' == get_option( 'woocommerce_enable_order_comments', 'yes' );
	}

	public function date_field_element( $element, $key, $value, $args ) {
		return '<input type="text" class="checkout-date-picker input-text" name="' . esc_attr( $key ) . '" id="' . esc_attr( $key ) . '" placeholder="' . $args['placeholder'] . '" ' . $args['maxlength'] . ' value="' . esc_attr( $value ) . '" />';
	}

	public function output_custom_styles() {
		if ( empty( $_GET['page'] ) || $_GET['page'] !== 'checkout_field_editor' ) {
			return;
		}
		?>
		<style type="text/css">
			/* Hide Billing and Shipping Fields */
			.woo-nav-tab-wrapper a:nth-child(1), .woo-nav-tab-wrapper a:nth-child(2) {
				display: none;
			}
		</style>
		<?php
	}

	public function maybe_redirect_to_additional_fields_tab() {
		if ( ! empty( $_GET['page'] ) && 'checkout_field_editor' === $_GET['page'] && ( empty( $_GET['tab'] ) || 'additional' !== $_GET['tab'] ) ) {
			wp_safe_redirect( 'admin.php?page=checkout_field_editor&tab=additional' );
			exit();
		}
	}
}

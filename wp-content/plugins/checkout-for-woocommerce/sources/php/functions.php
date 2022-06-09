<?php

use Objectiv\Plugins\Checkout\Adapters\ItemsAdapter;
use Objectiv\Plugins\Checkout\FormAugmenter;
use Objectiv\Plugins\Checkout\Model\Item;
use Objectiv\Plugins\Checkout\Model\Template;
use Objectiv\Plugins\Checkout\Main;
use Objectiv\Plugins\Checkout\Managers\SettingsManager;
use Objectiv\Plugins\Checkout\Managers\UpdatesManager;
use Objectiv\Plugins\Checkout\Managers\PlanManager;
use Objectiv\Plugins\Checkout\Managers\AssetManager;
use Objectiv\Plugins\Checkout\Loaders\Content;
use Objectiv\Plugins\Checkout\Loaders\Redirect;

/**
 * @deprecated
 * @return Main|null
 */
function cfw_get_main() {
	_deprecated_function( 'cfw_get_main', 'CheckoutWC 5.0.0' );
	return Main::instance();
}

/**
 * Outputs a checkout/address form field.
 *
 * @param string $key
 * @param mixed $args
 * @param mixed $value (default: null)
 *
 * @return mixed|void
 */
function cfw_form_field( string $key, $args, $value = null ) {
	$defaults = array(
		'type'              => 'text',
		'label'             => '',
		'description'       => '',
		'placeholder'       => '',
		'maxlength'         => false,
		'required'          => false,
		'autocomplete'      => false,
		'id'                => $key,
		'class'             => array(),
		'label_class'       => array(),
		'input_class'       => array(),
		'return'            => false,
		'options'           => array(),
		'custom_attributes' => array(),
		'validate'          => array(),
		'default'           => '',
		'autofocus'         => '',
		'priority'          => '',
		'wrap'              => '',
		'columns'           => 12,
	);

	$key_sans_type    = cfw_strip_key_type( $key );
	$ship_or_bill_key = explode( '_', $key )[0];

	$args = wp_parse_args( $args, $defaults );
	$args = apply_filters( 'woocommerce_form_field_args', $args, $key, $value );

	if ( in_array( $ship_or_bill_key, array( 'shipping', 'billing' ), true ) && empty( $args['custom_attributes']['data-parsley-group'] ) ) {
		$args['custom_attributes']['data-parsley-group'] = $ship_or_bill_key;
	}

	$required = '';

	// If we don't have a placeholder, use label
	if ( empty( $args['placeholder'] ) ) {
		$args['placeholder'] = $args['label'];
	}

	$args['placeholder'] = strip_tags( $args['placeholder'] );

	// If we don't have a wrap, add one and set start and end to a presumed true
	if ( empty( $args['wrap'] ) ) {
		$args = FormAugmenter::instance()->calculate_wrap( $args, true );
	}

	if ( $args['required'] ) {
		$args['class'][] = 'validate-required';
	} else {
		$required = '&nbsp;<span class="optional">(' . cfw_esc_html__( 'optional', 'woocommerce' ) . ')</span>';

		// We don't need to do this for address_2 because
		// it is handled by address-i18n.js
		if (
			'address_2' !== $key_sans_type &&
			stripos( $args['placeholder'], cfw_esc_html__( 'optional', 'woocommerce' ) ) === false &&
			apply_filters( 'cfw_form_field_append_optional_to_placeholder', true, $key )
		) {
			$args['placeholder'] = $args['placeholder'] . ' (' . cfw_esc_html__( 'optional', 'woocommerce' ) . ')';
		}
	}

	// If label contains HTML tag, add normal label class to container classes
	if ( strpos( $args['label'], '<' ) !== false ) {
		$args['class'][] = 'cfw-label-style-normal';
	}

	if ( is_string( $args['label_class'] ) ) {
		$args['label_class'] = array( $args['label_class'] );
	}

	if ( is_null( $value ) ) {
		$value = $args['default'];
	} else {
		$args['custom_attributes']['data-persist'] = 'false';
	}

	// Custom attribute handling
	$custom_attributes = array();

	/**
	 * Normally WooCommerce nukes empty attributes
	 * We can't do that because of our field syncing stuff,
	 * so we are going to specifically fix readonly attributes here
	 */
	if ( isset( $args['custom_attributes']['readonly'] ) && strlen( $args['custom_attributes']['readonly'] ) === 0 ) {
		unset( $args['custom_attributes']['readonly'] );
	}

	if ( $args['maxlength'] ) {
		$args['custom_attributes']['maxlength'] = absint( $args['maxlength'] );
	}

	if ( ! empty( $args['autocomplete'] ) ) {
		if ( 0 === stripos( $key, 'billing_' ) ) {
			$args['autocomplete'] = "billing {$args['autocomplete']}";
		} elseif ( 0 === stripos( $key, 'shipping_' ) ) {
			$args['autocomplete'] = "shipping {$args['autocomplete']}";
		}

		$args['custom_attributes']['autocomplete'] = $args['autocomplete'];
	}

	if ( true === $args['autofocus'] ) {
		$args['custom_attributes']['autofocus'] = 'autofocus';
	}

	if ( $args['description'] ) {
		$args['custom_attributes']['aria-describedby'] = $args['id'] . '-description';
	}

	if ( ! empty( $args['custom_attributes'] ) && is_array( $args['custom_attributes'] ) ) {
		foreach ( $args['custom_attributes'] as $attribute => $attribute_value ) {
			$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
		}
	}

	if ( ! empty( $args['validate'] ) ) {
		foreach ( $args['validate'] as $validate ) {
			$args['class'][] = 'validate-' . $validate;
		}
	}

	$field                 = '';
	$label_id              = $args['id'];
	$field_container_start = '';

	if ( isset( $args['wrap'] ) && ! empty( $args['wrap'] ) ) {
		$field_container_start = $args['wrap']->start . $args['wrap']->end;
	}

	if ( $args['required'] ) {
		$parsley_out = 'data-parsley-required="true"';
	} else {
		$parsley_out = 'data-parsley-required="false"';
	}

	if ( 'hidden' === $args['type'] ) {
		$args['start'] = false;
		$args['end']   = false;
	}

	switch ( $args['type'] ) {
		case 'country':
			$countries = 'shipping_country' === $key ? WC()->countries->get_shipping_countries() : WC()->countries->get_allowed_countries();

			$field = '<select field_key="' . $key_sans_type . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" class="country_to_state ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" ' . implode( ' ', $custom_attributes ) . $parsley_out . '>' . '<option value="">' . esc_html__( 'Select a country', 'checkout-wc' ) . '&hellip;</option>';

			/**
			 * Filter highlighted countries
			 *
			 * @since 2.0.0
			 *
			 * @param array $highlighted_countries Highlighted countries in country dropdown
			 */
			$highlighted_countries = array_flip( apply_filters( 'cfw_highlighted_countries', array() ) );
			$count                 = 1;

			if ( ! empty( $highlighted_countries ) ) {
				$countries = array_merge( $highlighted_countries, $countries );
			}

			foreach ( $countries as $ckey => $cvalue ) {
				$field .= '<option value="' . esc_attr( $ckey ) . '" ' . selected( $value, $ckey, false ) . '>' . $cvalue . '</option>';

				if ( ! empty( $highlighted_countries ) && count( $highlighted_countries ) === $count ) {
					$field .= '<option disabled="disabled" value="---">---</option>';
				}

				$count++;
			}

			$field .= '</select>';

			$field .= '<noscript><input type="submit" name="woocommerce_checkout_update_totals" value="' . cfw_esc_attr__( 'Update country', 'woocommerce' ) . '" /></noscript>';

			break;
		case 'state':
			/* Get Country */
			$country_key = 'billing_state' === $key ? 'billing_country' : 'shipping_country';
			$current_cc  = WC()->checkout()->get_value( $country_key );
			$states      = WC()->countries->get_states( $current_cc );

			if ( is_array( $states ) && empty( $states ) ) {

				$field_container_start = '<div class="col-lg-4 address-field %3$s" id="%1$s"><div class="cfw-input-wrap">%2$s</div></div>';

				$field .= '<input type="hidden" field_key="' . $key_sans_type . '" class="hidden" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" value="" ' . implode( ' ', $custom_attributes ) . ' placeholder="' . esc_attr( $args['placeholder'] ) . '" />';

			} elseif ( ! is_null( $current_cc ) && is_array( $states ) ) {

				$field .= '<select field_key="' . $key_sans_type . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" class="' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" ' . implode( ' ', $custom_attributes ) . $parsley_out . ' data-placeholder="' . esc_attr( $args['placeholder'] ) . '">
                    <option value="">' . cfw_esc_html__( 'Select an option&hellip;', 'woocommerce' ) . '</option>';

				foreach ( $states as $ckey => $cvalue ) {
					$field .= '<option value="' . esc_attr( $ckey ) . '" ' . selected( $value, $ckey, false ) . '>' . $cvalue . '</option>';
				}

				$field .= '</select>';

			} else {

				$field .= '<input ' . $parsley_out . ' field_key="' . $key_sans_type . '" type="text" class="input-text ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" value="' . esc_attr( $value ) . '"  placeholder="' . esc_attr( $args['placeholder'] ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" ' . implode( ' ', $custom_attributes ) . ' />';

			}

			break;
		case 'textarea':
			$field .= '<textarea name="' . esc_attr( $key ) . '" class="input-text ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" id="' . esc_attr( $args['id'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '" ' . ( empty( $args['custom_attributes']['rows'] ) ? ' rows="2"' : '' ) . ( empty( $args['custom_attributes']['cols'] ) ? ' cols="5"' : '' ) . implode( ' ', $custom_attributes ) . $parsley_out . '>' . esc_textarea( $value ) . '</textarea>';

			break;
		case 'checkbox':
			$field = '<label class="checkbox ' . implode( ' ', $args['label_class'] ) . '" ' . implode( ' ', $custom_attributes ) . '>
                    <input type="' . esc_attr( $args['type'] ) . '" class="input-checkbox ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" value="1" ' . checked( $value, 1, false ) . implode( ' ', $custom_attributes ) . ' />'
					 . $args['label'] . $required . '</label>';

			break;
		case 'password':
		case 'text':
		case 'hidden':
		case 'email':
		case 'tel':
		case 'number':
			$field .= '<input type="' . esc_attr( $args['type'] ) . '" field_key="' . $key_sans_type . '" class="input-text ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '"  value="' . esc_attr( $value ) . '" ' . implode( ' ', $custom_attributes ) . $parsley_out . ' />';

			break;
		case 'select':
			$options = '';
			$field   = '';

			if ( ! empty( $args['options'] ) ) {
				foreach ( $args['options'] as $option_key => $option_text ) {
					if ( '' === $option_key ) {
						// If we have a blank option, select2 needs a placeholder
						if ( empty( $args['placeholder'] ) ) {
							$args['placeholder'] = $option_text ? $option_text : cfw__( 'Choose an option', 'woocommerce' );
						}
						$custom_attributes[] = 'data-allow_clear="true"';
					}
					$options .= '<option value="' . esc_attr( $option_key ) . '" ' . selected( $value, $option_key, false ) . '>' . esc_attr( $option_text ) . '</option>';
				}

				$field .= '<select field_key="' . $key_sans_type . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" class="select ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" ' . implode( ' ', $custom_attributes ) . ' data-placeholder="' . esc_attr( $args['placeholder'] ) . '" ' . $parsley_out . '>' . $options . '</select>';
			}

			break;
		case 'radio':
			$label_id = current( array_keys( $args['options'] ) );

			if ( ! empty( $args['options'] ) ) {
				$count = 0;
				foreach ( $args['options'] as $option_key => $option_text ) {
					if ( 0 === $count ) {
						$field .= '<input type="radio" class="input-radio ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" value="' . esc_attr( $option_key ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '_' . esc_attr( $option_key ) . '"' . checked( $value, $option_key, false ) . $parsley_out . ' />';
					} else {
						$field .= '<input type="radio" class="input-radio ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" value="' . esc_attr( $option_key ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '_' . esc_attr( $option_key ) . '"' . checked( $value, $option_key, false ) . ' />';
					}
					$field .= '<label for="' . esc_attr( $args['id'] ) . '_' . esc_attr( $option_key ) . '" class="radio ' . implode( ' ', $args['label_class'] ) . '">' . $option_text . '</label>';

					$count++;
				}
			}

			break;
		default:
			/**
			 * Filter form field element
			 *
			 * @since 2.0.0
			 *
			 * @param string $field Form field element
			 */
			$field .= apply_filters( 'cfw_form_field_element_' . $args['type'], '<input type="' . esc_attr( $args['type'] ) . '" field_key="' . $key_sans_type . '" class="input-text ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '"  value="' . esc_attr( $value ) . '" ' . implode( ' ', $custom_attributes ) . $parsley_out . ' />', $key, $value, $args );
			break;
	}

	$row_wrap = '';

	if ( ! empty( $field ) ) {

		$field_html = '';

		if ( $args['label'] && 'checkbox' !== $args['type'] && 'hidden' !== $args['type'] ) {
			$field_html .= '<label for="' . esc_attr( $label_id ) . '" class="' . esc_attr( implode( ' ', $args['label_class'] ) ) . '">' . $args['label'] . $required . '</label>';
		}

		$field_html .= $field;

		$container_class = esc_attr( implode( ' ', $args['class'] ) );
		$container_id    = esc_attr( $args['id'] ) . '_field';

		if ( isset( $args['start'] ) && $args['start'] ) {
			$row_wrap = '<div class="row cfw-input-wrap-row">';
		}

		/**
		 * Filter input row wrap
		 *
		 * @since 2.0.0
		 *
		 * @param string $row_wrap Input row wrap
		 */
		$row_wrap = apply_filters( 'cfw_input_row_wrap', $row_wrap, $key, $args, $value );

		if ( ! empty( $field_container_start ) ) {
			$field = $row_wrap . @sprintf( $field_container_start, $container_id, $field_html, $container_class ); // phpcs:ignore
		}

		if ( isset( $args['end'] ) && $args['end'] ) {
			$field .= '</div>';
		}
	}

	$field = apply_filters( 'woocommerce_form_field_' . $args['type'], $field, $key, $args, $value, $row_wrap );

	if ( $args['return'] ) {
		return $field;
	} else {
		echo $field;
	}
}

function cfw_strip_key_type( $key ) {
	$key_exp = explode( '_', $key );
	return implode( '_', array_slice( $key_exp, 1, count( $key_exp ) - 1, true ) );
}

/**
 * @param WC_Checkout $checkout
 */
function cfw_get_shipping_checkout_fields( WC_Checkout $checkout ) {
	/**
	 * Filters shipping address checkout fields
	 *
	 * @since 2.0.0
	 *
	 * @param array $shipping_checkout_fields Shipping address checkout fields
	 */
	$shipping_checkout_fields = apply_filters( 'cfw_get_shipping_checkout_fields', $checkout->get_checkout_fields( 'shipping' ) );

	foreach ( $shipping_checkout_fields as $key => $field ) {
		cfw_form_field( $key, $field, $checkout->get_value( $key ) );
	}
}

/**
 * @param WC_Checkout $checkout
 */
function cfw_get_billing_checkout_fields( WC_Checkout $checkout ) {
	/**
	 * Filters billing address checkout fields
	 *
	 * @since 2.0.0
	 *
	 * @param array $billing_checkout_fields Billing address checkout fields
	 */
	$billing_checkout_fields = apply_filters( 'cfw_get_billing_checkout_fields', $checkout->get_checkout_fields( 'billing' ) );

	foreach ( $billing_checkout_fields as $key => $field ) {
		// Don't output billing email or native billing phone
		// This logic is ugly, but basically we're saying:
		//   - If the field is billing phone and our wrap isn't present, skip the field
		//   - If the field is billing email, skip it
		//   - Otherwise, output it
		if ( 'billing_phone' === $key && ! isset( $field['wrap'] ) ) {
			continue;
		}

		if ( 'billing_email' === $key ) {
			continue;
		}

		$field['custom_attributes']['data-saved-value'] = $checkout->get_value( $key ) ?? '';

		cfw_form_field( $key, $field, $checkout->get_value( $key ) );
	}
}

/**
 * @param WC_Checkout $checkout
 *
 * @return string
 */
function cfw_get_review_pane_shipping_address( WC_Checkout $checkout ): string {
	return WC()->countries->get_formatted_address(
		/**
		 * Filters review pane shipping address
		 *
		 * @since 2.0.0
		 *
		 * @param array $shipping_details_address Review pane shipping address
		 */
		apply_filters(
			'cfw_get_shipping_details_address',
			array(
				'company'   => isset( $_POST['s_company'] ) ? $_POST['s_company'] : $checkout->get_value( 'shipping_company' ),
				'address_1' => $_POST['s_address_1'] ?? $checkout->get_value( 'shipping_address_1' ),
				'address_2' => $_POST['s_address_2'] ?? $checkout->get_value( 'shipping_address_2' ),
				'city'      => $_POST['s_city'] ?? $checkout->get_value( 'shipping_city' ),
				'state'     => $_POST['s_state'] ?? $checkout->get_value( 'shipping_state' ),
				'postcode'  => $_POST['s_postcode'] ?? $checkout->get_value( 'shipping_postcode' ),
				'country'   => $_POST['s_country'] ?? $checkout->get_value( 'shipping_country' ),
			),
			$checkout
		),
		', '
	);
}

/**
 * @param WC_Checkout $checkout
 *
 * @return string
 */
function cfw_get_review_pane_billing_address( WC_Checkout $checkout ): string {
	return WC()->countries->get_formatted_address(
		/**
		 * Filters review pane billing address
		 *
		 * @since 2.0.0
		 *
		 * @param array $billing_details_address Review pane billing address
		 */
		apply_filters(
			'cfw_get_review_pane_billing_address',
			array(
				'company'   => isset( $_POST['company'] ) ? $_POST['company'] : $checkout->get_value( 'billing_company' ),
				'address_1' => $_POST['address_1'] ?? $checkout->get_value( 'billing_address_1' ),
				'address_2' => $_POST['address_2'] ?? $checkout->get_value( 'billing_address_2' ),
				'city'      => $_POST['city'] ?? $checkout->get_value( 'billing_city' ),
				'state'     => $_POST['state'] ?? $checkout->get_value( 'billing_state' ),
				'postcode'  => $_POST['postcode'] ?? $checkout->get_value( 'billing_postcode' ),
				'country'   => $_POST['country'] ?? $checkout->get_value( 'billing_country' ),
			),
			$checkout
		),
		', '
	);
}

function cfw_get_shipping_methods_html() {
	ob_start();

	cfw_shipping_methods_html();

	return ob_get_clean();
}

function cfw_shipping_methods_html() {
	$packages = WC()->shipping->get_packages();

	foreach ( $packages as $i => $package ) {
		$chosen_method = WC()->session->chosen_shipping_methods[ $i ] ?? '';
		$product_names = array();

		if ( sizeof( $packages ) > 1 ) {
			foreach ( $package['contents'] as $item_id => $values ) {
				$product_names[ $item_id ] = $values['data']->get_name() . ' &times;' . $values['quantity'];
			}
			$product_names = apply_filters( 'woocommerce_shipping_package_details_array', $product_names, $package );
		}

		$available_methods    = $package['rates'];
		$show_package_details = sizeof( $packages ) > 1;
		$package_details      = implode( ', ', $product_names );
		$package_name         = apply_filters( 'woocommerce_shipping_package_name', sprintf( cfw_nx( 'Shipping', 'Shipping %d', ( $i + 1 ), 'shipping packages', 'woocommerce' ), ( $i + 1 ) ), $i, $package );
		$index                = $i;

		// Next section ripped straight from cart-shipping and edited for now
		if ( count( $available_methods ) > 0 ) : ?>
			<?php if ( 1 < count( $packages ) ) : ?>
				<h4 class="cfw-shipping-package-title"><?php echo $package_name; ?></h4>
			<?php endif; ?>

			<?php cfw_before_shipping(); ?>

			<ul id="shipping_method" class="cfw-shipping-methods-list">
				<?php
				foreach ( $available_methods as $method ) :
					ob_start();
					do_action( 'woocommerce_after_shipping_rate', $method, $index );
					$after_shipping_method = ob_get_clean();
					?>
					<li>
						<div class="cfw-shipping-method-inner">
							<?php printf( '<input type="radio" name="shipping_method[%1$d]" data-index="%1$d" id="shipping_method_%1$d_%2$s" value="%3$s" class="shipping_method" %4$s />', $index, esc_attr( sanitize_title( $method->id ) ), esc_attr( $method->id ), checked( $method->id, $chosen_method, false ) ); // WPCS: XSS ok. ?>
							<?php printf( '<label for="shipping_method_%1$s_%2$s">%3$s</label>', $index, esc_attr( sanitize_title( $method->id ) ), wc_cart_totals_shipping_method_label( $method ) ); // WPCS: XSS ok. ?>
						</div>
						<?php
						if ( ! empty( trim( $after_shipping_method ) ) && preg_match( '/<thead|<tbody|<tfoot|<th|<tr/', $after_shipping_method ) && substr( trim( $after_shipping_method ), 0, 6 ) !== '<table' ) :
							?>
							<table>
								<?php echo $after_shipping_method; ?>
							</table>
						<?php else : ?>
							<?php echo $after_shipping_method; ?>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>

			<?php cfw_after_shipping(); ?>
		<?php else : ?>
			<div class="shipping-message">
				<?php echo apply_filters( 'woocommerce_no_shipping_available_html', '<div class="cfw-alert cfw-alert-error"><div class="message">' . wpautop( cfw__( 'There are no shipping options available. Please ensure that your address has been entered correctly, or contact us if you need any help.', 'woocommerce' ) ) . '</div></div>' ); ?>
			</div>
		<?php endif; ?>

		<?php if ( $show_package_details ) : ?>
			<?php echo '<p class="woocommerce-shipping-contents"><small>' . esc_html( $package_details ) . '</small></p>'; ?>
		<?php endif; ?>
		<?php
	}
}

function cfw_has_valid_shipping_methods(): bool {
	foreach ( WC()->shipping->get_packages() as $package ) {
		if ( count( $package['rates'] ) === 0 ) {
			return false;
		}
	}

	return true;
}

function cfw_before_shipping() {
	if ( has_action( 'woocommerce_review_order_before_shipping' ) ) :
		?>
		<table id="cfw-before-shipping">
			<?php do_action( 'woocommerce_review_order_before_shipping' ); ?>
		</table>
		<?php
	endif;
}

function cfw_after_shipping() {
	if ( has_action( 'woocommerce_review_order_after_shipping' ) ) :
		?>
		<table id="cfw-after-shipping">
			<?php
			/**
			 * Fires after shipping methods in table
			 *
			 * @since 2.0.0
			 */
			do_action( 'cfw_after_shipping_methods' );
			?>
			<?php do_action( 'woocommerce_review_order_after_shipping' ); ?>
		</table>
		<?php
	endif;
}

function cfw_get_payment_methods_html( $available_gateways = false ) {
	/**
	 * Fires before payment methods html is fetched
	 *
	 * @since 2.0.0
	 */
	do_action( 'cfw_get_payment_methods_html' );

	$available_gateways = empty( $available_gateways ) ? WC()->payment_gateways->get_available_payment_gateways() : (array) $available_gateways;
	WC()->payment_gateways()->set_current_gateway( $available_gateways );

	ob_start();
	?>
	<ul class="wc_payment_methods payment_methods methods cfw-radio-reveal-group">
	<?php
	/**
	 * Fires at start of payment methods UL
	 *
	 * @since 2.0.0
	 */
	do_action( 'cfw_payment_methods_ul_start' );

	if ( ! empty( $available_gateways ) ) {
		$count = 0;
		foreach ( $available_gateways as $gateway ) {
			// Prevent fatal errors when no gateway is available
			// OR when gateway isn't actually a gateway
			if ( is_a( $gateway, 'stdClass' ) ) {
				continue;
			}

			/**
			 * Filters whether to show gateway in list of gateways
			 *
			 * @since 2.0.0
			 *
			 * @param bool $show Show gateway output
			 */
			if ( apply_filters( "cfw_show_gateway_{$gateway->id}", true ) ) :
				/**
				 * Filters gateway order button text
				 *
				 * @since 2.0.0
				 *
				 * @param string $gateway_order_button_text The gateway order button text
				 */
				$gateway_order_button_text = apply_filters( 'cfw_gateway_order_button_text', $gateway->order_button_text, $gateway );

				/**
				 * Filters gateway order button text
				 *
				 * @since 2.0.0
				 *
				 * @param string $icons The gateway icon HTML
				 * @param \WC_Payment_Gateway
				 */
				$icons = apply_filters( 'cfw_get_gateway_icons', $gateway->get_icon(), $gateway );

				$title              = $gateway->get_title();
				$is_active_class    = $gateway->chosen ? 'cfw-active' : '';
				$li_class_attribute = apply_filters( 'cfw_payment_method_li_class', "wc_payment_method cfw-radio-reveal-li $is_active_class payment_method_{$gateway->id}" );
				?>
				<li class="<?php echo $li_class_attribute; ?>">
					<div class="payment_method_title_wrap cfw-radio-reveal-title-wrap">
						<input id="payment_method_<?php echo $gateway->id; ?>" type="radio" class="input-radio" name="payment_method" value="<?php echo esc_attr( $gateway->id ); ?>" <?php checked( $gateway->chosen, true ); ?> data-order_button_text="<?php echo esc_attr( $gateway_order_button_text ); ?>"/>

						<label class="payment_method_label cfw-radio-reveal-label" for="payment_method_<?php echo $gateway->id; ?>">
							<div>
								<?php if ( $title ) : ?>
									<span class="payment_method_title cfw-radio-reveal-title">
										<?php echo $title; ?>
									</span>
								<?php endif; ?>

								<?php if ( $icons ) : ?>
									<span class="payment_method_icons">
										<?php echo $icons; ?>
									</span>
								<?php endif; ?>
							</div>
						</label>
					</div>
				<?php
				/**
				 * Filters whether to show gateway content
				 *
				 * @since 2.0.0
				 *
				 * @param bool $show Show gateway content
				 */
				if ( apply_filters( "cfw_payment_gateway_{$gateway->id}_content", $gateway->has_fields() || $gateway->get_description() ) ) :
					?>
						<div class="payment_box payment_method_<?php echo $gateway->id; ?> cfw-radio-reveal-content" <?php echo ! $gateway->chosen ? 'style="display:none;"' : ''; ?>>
							<?php
							ob_start();

							remove_filter( 'woocommerce_form_field', array( FormAugmenter::instance(), 'cfw_form_field' ), 10 );

							$gateway->payment_fields();

							add_filter( 'woocommerce_form_field', array( FormAugmenter::instance(), 'cfw_form_field' ), 10, 4 );

							$field_html = ob_get_clean();

							/**
							 * Gateway Compatibility Patches
							 */
							// Expiration field fix
							$field_html = str_ireplace( 'js-sv-wc-payment-gateway-credit-card-form-expiry', 'js-sv-wc-payment-gateway-credit-card-form-expiry  wc-credit-card-form-card-expiry', $field_html );
							$field_html = str_ireplace( 'js-sv-wc-payment-gateway-credit-card-form-account-number', 'js-sv-wc-payment-gateway-credit-card-form-account-number  wc-credit-card-form-card-number', $field_html );

							// Credit Card Field Placeholders
							$field_html = str_ireplace( '•••• •••• •••• ••••', 'Card Number', $field_html );
							$field_html = str_ireplace( '&bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull;', 'Card Number', $field_html );

							/**
							 * Filters gateway payment field output HTML
							 *
							 * @since 2.0.0
							 *
							 * @param string $gateway_output Payment gateway output HTML
							 */
							echo apply_filters( "cfw_payment_gateway_field_html_{$gateway->id}", $field_html );
							?>
						</div>
					<?php endif; ?>
				</li>

				<?php
				else :
					/**
					 * Fires after payment method LI to allow alternate / additional output
					 *
					 * @since 2.0.0
					 */
					do_action_ref_array( "cfw_payment_gateway_list_{$gateway->id}_alternate", array( $count ) );
				endif;

				$count++;
		}
	} else {
		echo '<li class="woocommerce-notice woocommerce-notice--info woocommerce-info">' . apply_filters( 'woocommerce_no_available_payment_methods_message', cfw__( 'Sorry, it seems that there are no available payment methods for your location. Please contact us if you require assistance or wish to make alternate arrangements.', 'woocommerce' ) ) . '</li>';
	}

	/**
	 * Fires after bottom of payment methods UL
	 *
	 * @since 2.0.0
	 */
	do_action( 'cfw_payment_methods_ul_end' );
	?>
	</ul>
	<?php

	return ob_get_clean();
}

/**
 * @return string
 */
function cfw_get_checkout_item_summary_table(): string {
	$cart          = WC()->cart;
	$items_adapter = new ItemsAdapter( $cart );
	$items         = $items_adapter->get_items();

	/**
	 * Filters whether to link cart items to products
	 *
	 * @since 1.0.0
	 *
	 * @param bool $link_cart_items Link cart items to products
	 */
	$link_items = apply_filters( 'cfw_link_cart_items', SettingsManager::instance()->get_setting( 'cart_item_link' ) === 'enabled' );
	ob_start();
	?>
	<table id="cfw-cart" class="cfw-module">
		<?php
		/**
		 * Fires at start of cart table
		 *
		 * @since 2.0.0
		 */
		do_action( 'cfw_cart_html_table_start' );

		/** @var Item $item */
		foreach ( $items as $item ) :
			$item_thumb = $item->get_thumbnail();
			?>
			<tr class="cart-item-row cart-item-<?php echo $item->get_item_key(); ?> <?php echo $item->get_row_class(); ?>">
				<?php if ( $item_thumb ) : ?>
					<td class="cfw-cart-item-image">
						<div class="cfw-cart-item-image-wrap">
							<?php if ( $link_items ) : ?>
								<a target="_blank" href="<?php echo $item->get_url(); ?>">
									<?php echo $item_thumb; ?>
								</a>
							<?php else : ?>
								<?php echo $item_thumb; ?>
							<?php endif; ?>

							<span class="cfw-cart-item-quantity-bubble">
								<?php echo $item->get_quantity(); ?>
							</span>
						</div>
					</td>
				<?php endif; ?>

				<th class="cfw-cart-item-description" <?php echo empty( $item_thumb ) ? 'colspan="2" style="padding-left: 0; ?>"' : ''; ?> >
					<div class="cfw-cart-item-title">
						<?php if ( $link_items ) : ?>
							<a target="_blank" href="<?php echo $item->get_url(); ?>">
								<?php echo $item->get_title(); ?>
							</a>
						<?php else : ?>
							<?php echo $item->get_title(); ?>
						<?php endif; ?>
					</div>
					<?php
					/**
					 * Filters whether to show cart item discount on cart item
					 *
					 * @since 2.0.0
					 *
					 * @param bool $show_cart_item_discount Show cart item discount on cart item
					 */
					if ( apply_filters( 'cfw_show_cart_item_discount', false ) ) {
						echo '<div class="cfw-items-summary-item-discount">';
						echo apply_filters( 'cfw_cart_item_discount', $item->get_product()->is_on_sale() ? $item->get_product()->get_price_html() : '', $item->get_raw_item(), $item->get_product() );
						echo '</div>';
					}

					cfw_display_item_data( $item );

					/**
					 * Fires after cart item data output
					 *
					 * @since 2.0.0
					 */
					do_action( 'cfw_cart_item_after_data', $item->get_raw_item(), $item->get_item_key(), $item );
					?>
				</th>

				<td class="cfw-cart-item-quantity visually-hidden">
					<?php echo $item->get_quantity(); ?>
				</td>

				<td class="cfw-cart-item-subtotal">
					<?php do_action( 'cfw_before_cart_item_subtotal', $item ); ?>
					<?php echo $item->get_subtotal(); ?>
				</td>
			</tr>
			<?php
			/**
			 * Fires after cart item row <tr/> is outputted
			 *
			 * @since 2.0.0
			 */
			do_action( 'cfw_after_cart_item_row', $item->get_raw_item(), $item->get_item_key() );
		endforeach;
		?>
	</table>
	<?php
	$return = ob_get_clean();
	/**
	 * After cart html table output
	 *
	 * @since 4.3.4
	 */
	do_action_deprecated( 'cfw_after_cart_html', null, 'CheckoutWC 5.4.0', 'cfw_after_items_summary_table' );

	/**
	 * Filters cart output HTML
	 *
	 * @since 1.0.0
	 *
	 * @param string $cart_html Cart output HTML
	 */
	$return = apply_filters_deprecated( 'cfw_cart_html', array( $return ), 'CheckoutWC 5.4.0', 'cfw_items_summary_table_html' );

	return (string) apply_filters( 'cfw_items_summary_table_html', $return, 'checkout' );
}

/**
 * @param WC_Order $object
 * @return string
 */
function cfw_get_order_item_summary_table( WC_Order $object ): string {
	$items_adapter = new ItemsAdapter( $object );
	$items         = $items_adapter->get_items();
	ob_start();
	?>
	<table id="cfw-cart" class="cfw-module">
		<?php
		/** @var Item $item */
		foreach ( $items as $item ) :
			$item_thumb = $item->get_thumbnail();
			?>
			<tr class="cart-item-row cart-item-<?php echo $item->get_item_key(); ?> <?php echo $item->get_row_class(); ?>">
				<?php if ( $item_thumb ) : ?>
					<td class="cfw-cart-item-image">
						<div class="cfw-cart-item-image-wrap">
							<?php echo $item_thumb; ?>

							<span class="cfw-cart-item-quantity-bubble">
								<?php echo $item->get_quantity(); ?>
							</span>
						</div>
					</td>
				<?php endif; ?>

				<th class="cfw-cart-item-description" <?php echo empty( $item_thumb ) ? 'colspan="2" style="padding-left: 0; ?>"' : ''; ?> >
					<div class="cfw-cart-item-title">
						<?php
						/**
						 * Filters whether to link cart items to products
						 *
						 * @since 1.0.0
						 *
						 * @param bool $link_cart_items Link cart items to products
						 */
						if ( apply_filters( 'cfw_link_cart_items', SettingsManager::instance()->get_setting( 'cart_item_link' ) === 'enabled' ) ) :
							?>
							<a target="_blank" href="<?php echo $item->get_url(); ?>">
								<?php echo $item->get_title(); ?>
							</a>
						<?php else : ?>
							<?php echo $item->get_title(); ?>
						<?php endif; ?>
					</div>
					<?php
					cfw_display_item_data( $item );
					?>
				</th>

				<td class="cfw-cart-item-quantity visually-hidden">
					<?php echo $item->get_quantity(); ?>
				</td>

				<td class="cfw-cart-item-subtotal">
					<?php do_action( 'cfw_before_cart_item_subtotal', $item ); ?>
					<?php echo $item->get_subtotal(); ?>
				</td>
			</tr>
			<?php endforeach; ?>
	</table>
	<?php
	$return = ob_get_clean();

	/**
	 * Filters order cart HTML output
	 *
	 * @since 1.0.0
	 *
	 * @param string $order_cart_html Order cart HTML output
	 */
	$return = apply_filters_deprecated( 'cfw_order_cart_html', array( $return ), 'CheckoutWC 5.4.0', 'cfw_items_summary_table_html' );

	return (string) apply_filters( 'cfw_items_summary_table_html', $return, 'order' );
}

/**
 * @return string
 */
function cfw_get_side_cart_item_summary_table(): string {
	$cart          = WC()->cart;
	$items_adapter = new ItemsAdapter( $cart );
	$items         = $items_adapter->get_items();
	ob_start();
	?>
	<table id="cfw-cart" class="cfw-module">
		<?php
		/** @var Item $item */
		foreach ( $items as $item ) :
			$link_item    = apply_filters( 'cfw_side_cart_link_item', true, $item );
			$link_pattern = '<a href="' . $item->get_url() . '">%s</a>';
			$item_thumb   = $item->get_thumbnail();
			?>
			<tr class="cart-item-row cart-item-<?php echo $item->get_item_key(); ?> <?php echo $item->get_row_class(); ?>">
				<?php if ( $item_thumb ) : ?>
					<td class="cfw-cart-item-image">
						<div class="cfw-cart-item-image-wrap">
							<?php echo $link_item ? sprintf( $link_pattern, $item_thumb ) : $item_thumb; ?>

							<span class="cfw-cart-item-quantity-bubble">
								<?php echo $item->get_quantity(); ?>
							</span>
						</div>
					</td>
				<?php endif; ?>

				<th class="cfw-cart-item-description" <?php echo empty( $item_thumb ) ? 'colspan="2" style="padding-left: 0; ?>"' : ''; ?> >
					<div class="cfw-cart-item-title">
						<?php echo $link_item ? sprintf( $link_pattern, $item->get_title() ) : $item->get_title(); ?>
					</div>
					<?php
					/**
					 * Filters whether to show cart item discount on cart item
					 *
					 * @since 2.0.0
					 *
					 * @param bool $show_cart_item_discount Show cart item discount on cart item
					 */
					if ( apply_filters( 'cfw_show_cart_item_discount', false ) ) {
						echo '<div class="cfw-items-summary-item-discount">';
						echo apply_filters( 'cfw_cart_item_discount', $item->get_product()->is_on_sale() ? $item->get_product()->get_price_html() : '', $item->get_raw_item(), $item->get_product() );
						echo '</div>';
					}

					cfw_display_item_data( $item );

					echo cfw_get_cart_item_quantity_control( $item->get_raw_item(), $item->get_item_key(), $item->get_product() );
					?>
				</th>

				<td class="cfw-cart-item-quantity visually-hidden">
					<?php echo $item->get_quantity(); ?>
				</td>

				<td class="cfw-cart-item-subtotal">
					<?php do_action( 'cfw_before_cart_item_subtotal', $item ); ?>
					<?php echo $item->get_subtotal(); ?>
				</td>
			</tr>
			<?php endforeach; ?>
	</table>
	<?php
	$return = ob_get_clean();

	return (string) apply_filters( 'cfw_items_summary_table_html', $return, 'side_cart' );
}

function cfw_get_cart_html() {
	_deprecated_function( 'cfw_get_cart_html', 'CheckoutWC 5.4.0', 'cfw_get_item_summary_table' );

	return cfw_get_checkout_item_summary_table();
}

/**
 * @param WC_Order $order
 */
function cfw_order_cart_html( WC_Order $order ) {
	_deprecated_function( 'cfw_order_cart_html', 'CheckoutWC 5.4.0', 'cfw_get_item_summary_table' );

	echo cfw_get_order_item_summary_table( $order );
}

/**
 * @param WC_Order $order
 *
 * @return mixed|void
 */
function cfw_get_order_cart_html( WC_Order $order ) {
	_deprecated_function( 'cfw_get_order_cart_html', 'CheckoutWC 5.4.0', 'cfw_get_item_summary_table' );

	return cfw_get_order_item_summary_table( $order );
}

/**
 * @param Item $item
 */
function cfw_get_item_data_output( Item $item ): string {
	if ( apply_filters( 'cfw_cart_item_data_expanded', SettingsManager::instance()->get_setting( 'cart_item_data_display' ) === 'woocommerce' ) ) {
		if ( is_array( $item->get_raw_item() ) ) {
			$output = wc_get_formatted_cart_item_data( $item->get_raw_item() );

			return str_replace( ' :', ':', $output );
		} else {
			return wc_display_item_meta( $item->get_raw_item(), array( 'echo' => false ) );
		}
	}

	$item_data = $item->get_data();

	if ( empty( $item_data ) ) {
		return '';
	}

	$display_outputs = array();

	foreach ( $item_data as $raw_key => $raw_value ) {
		$key               = wp_kses_post( $raw_key );
		$value             = strip_tags( $raw_value );
		$display_outputs[] = "$key: $value";
	}

	return join( ' / ', $display_outputs );
}

/**
 * @param Item $item
 */
function cfw_display_item_data( Item $item ) {
	$output = $item->get_formatted_data();

	if ( $output ) {
		echo '<div class="cfw-cart-item-data">' . $output . '</div>';
	}
}

function cfw_get_totals_html() {
	ob_start();

	/**
	 * Filters cart element ID
	 *
	 * @since 3.0.0
	 *
	 * @param string $cart_element_id Cart element ID
	 */
	$template_cart_el_id = apply_filters( 'cfw_template_cart_el', 'cfw-totals-list' );
	?>
	<div id="<?php echo $template_cart_el_id; ?>" class="cfw-module">
		<table class="cfw-module">
			<?php
			/**
			 * Fires at start of cart summary totals table
			 *
			 * @since 2.0.0
			 */
			do_action( 'cfw_before_cart_summary_totals' );
			?>

			<tr class="cart-subtotal">
				<th><?php cfw_e( 'Subtotal', 'woocommerce' ); ?></th>
				<td><?php wc_cart_totals_subtotal_html(); ?></td>
			</tr>

			<?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
				<tr class="cart-discount coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
					<th><?php wc_cart_totals_coupon_label( $coupon ); ?></th>
					<td><?php wc_cart_totals_coupon_html( $coupon ); ?></td>
				</tr>
			<?php endforeach; ?>

			<?php if ( cfw_show_shipping_total() ) : ?>
				<?php cfw_cart_totals_shipping_html(); ?>
			<?php endif; ?>

			<?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
				<tr class="fee">
					<th><?php echo esc_html( $fee->name ); ?></th>
					<td><?php wc_cart_totals_fee_html( $fee ); ?></td>
				</tr>
			<?php endforeach; ?>

			<?php if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) : ?>
				<?php if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) : ?>
					<?php foreach ( WC()->cart->get_tax_totals() as $code => $tax ) : ?>
						<tr class="tax-rate tax-rate-<?php echo sanitize_title( $code ); ?>">
							<th><?php echo esc_html( $tax->label ); ?></th>
							<td><?php echo wp_kses_post( $tax->formatted_amount ); ?></td>
						</tr>
					<?php endforeach; ?>
				<?php else : ?>
					<tr class="tax-total">
						<th><?php echo esc_html( WC()->countries->tax_or_vat() ); ?></th>
						<td><?php wc_cart_totals_taxes_total_html(); ?></td>
					</tr>
				<?php endif; ?>
			<?php endif; ?>

			<?php
			/**
			 * Fires before totals are output in cart summary totals table
			 *
			 * @since 2.0.0
			 */
			do_action( 'woocommerce_review_order_before_order_total' );
			?>

			<tr class="order-total">
				<th><?php cfw_e( 'Total', 'woocommerce' ); ?></th>
				<td><?php wc_cart_totals_order_total_html(); ?></td>
			</tr>

			<?php
			/**
			 * Fires after totals are output in cart summary totals table
			 *
			 * @since 2.0.0
			 */
			do_action( 'woocommerce_review_order_after_order_total' );
			?>

			<?php
			/**
			 * Fires at end of cart summary totals table before </table> tag
			 *
			 * @since 2.0.0
			 */
			do_action( 'cfw_after_cart_summary_totals' );
			?>
		</table>
	</div>
	<?php

	/**
	 * Filters cart totals HTML
	 *
	 * @since 2.0.0
	 *
	 * @param string $totals_html Cart totals HTML
	 */
	return apply_filters( 'cfw_totals_html', ob_get_clean() );
}

/**
 * Get shipping methods.
 *
 * @see wc_cart_totals_shipping_html()
 */
function cfw_cart_totals_shipping_html() {
	?>
	<tr class="woocommerce-shipping-totals">
		<th>
			<?php
			/**
			 * Filters cart totals shipping label
			 *
			 * @since 2.0.0
			 *
			 * @param string $cart_totals_shipping_label Cart totals shipping label
			 */
			echo apply_filters( 'cfw_cart_totals_shipping_label', cfw_esc_html__( 'Shipping', 'woocommerce' ) );
			?>
		</th>
		<td>
			<?php echo cfw_get_shipping_total(); ?>
		</td>
	</tr>
	<?php
}

function cfw_all_packages_have_available_shipping_methods( array $packages ): bool {
	foreach ( $packages as $package ) {
		if ( empty( $package['rates'] ) ) {
			return false;
		}
	}

	return true;
}

function cfw_get_shipping_total(): string {
	$small_format = '<span class="cfw-small">%s</span>';

	$has_calculated_shipping = WC()->customer->has_calculated_shipping();
	$address_required        = get_option( 'woocommerce_shipping_cost_requires_address' ) === 'yes';
	$missing_address         = $address_required && ! $has_calculated_shipping;

	if ( $missing_address ) {
		/**
		 * Filters shipping total address required text
		 *
		 * @param string $address_required_text Shipping total address required text
		 * @since 2.0.0
		 */
		return sprintf( $small_format, wp_kses_post( apply_filters( 'cfw_shipping_total_address_required_text', cfw__( 'Enter your address to view shipping options.', 'woocommerce' ) ) ) );
	}

	$packages = WC()->shipping()->get_packages();

	if ( ! cfw_all_packages_have_available_shipping_methods( $packages ) ) {
		/**
		 * Filters shipping total text when no shipping methods are available
		 *
		 * @param string $new_shipping_total_not_available_text Shipping total text when no shipping methods are available
		 * @since 2.0.0
		 */
		return sprintf( $small_format, wp_kses_post( apply_filters( 'cfw_shipping_total_not_available_text', __( 'No shipping methods available', 'checkout-wc' ) ) ) );
	}

	if ( has_filter( 'woocommerce_shipping_chosen_method' ) && ! cfw_all_packages_have_selected_shipping_methods( $packages ) ) {
		return apply_filters( 'cfw_no_shipping_method_selected_message', '' );
	}

	$total = cfw_calculate_packages_shipping( $packages, WC()->session, WC()->cart );

	if ( 0 < $total ) {
		return wc_price( $total );
	}

	return apply_filters( 'cfw_shipping_free_text', cfw__( 'Free!', 'woocommerce' ) );
}

function cfw_all_packages_have_selected_shipping_methods( $packages ): bool {
	foreach ( $packages as $i => $package ) {
		$default = wc_get_default_shipping_method_for_package( $i, $package, false );
		$session = WC()->session->chosen_shipping_methods[ $i ] ?? false;

		if ( false === $default && false === $session ) {
			return false;
		}
	}

	return true;
}

function cfw_calculate_packages_shipping( array $packages, $wc_session, $wc_cart ) {
	$total = 0;

	foreach ( $packages as $i => $package ) {
		$chosen_method     = $wc_session->chosen_shipping_methods[ $i ] ?? '';
		$available_methods = empty( $package['rates'] ) ? array() : $package['rates'];

		foreach ( $available_methods as $method ) {
			if ( (string) $method->id !== (string) $chosen_method ) { // WC_Shipping_Method::id is defined as a string type, so we need to make sure we're comparing it as a string
				continue;
			}

			if ( 0 >= $method->cost ) {
				continue;
			}

			$total += $method->cost;

			if ( $wc_cart->display_prices_including_tax() ) {
				$total += $method->get_shipping_tax();
			}
		}
	}

	return $total;
}

/**
 * Get shipping methods.
 *
 * @see wc_cart_totals_shipping_html()
 */
function cfw_order_review_pane_shipping_totals() {
	?>
	<li>
		<div class="inner cfw-no-border">
			<div role="rowheader" class="cfw-review-pane-label">
				<?php
				/**
				 * Filters cart totals shipping label
				 *
				 * @param string $cart_totals_shipping_label Cart totals shipping label
				 * @since 3.0.0
				 */
				echo apply_filters( 'cfw_cart_totals_shipping_label', cfw_esc_html__( 'Shipping', 'woocommerce' ) );
				?>
			</div>
		</div>
		<div role="cell" class="cfw-review-pane-content cfw-review-pane-right cfw-no-border">
			<?php echo cfw_get_shipping_total(); ?>
		</div>
	</li>
	<?php
}

/**
 * @param WC_Order $order
 */
function cfw_order_totals_html( WC_Order $order ) {
	echo cfw_get_order_totals_html( $order );
}

/**
 * @param WC_Order $order
 *
 * @return mixed|void
 */
function cfw_get_order_totals_html( WC_Order $order ) {
	$totals = $order->get_order_item_totals();

	ob_start();

	/**
	 * Filters order totals element ID
	 *
	 * @since 2.0.0
	 *
	 * @param string $order_totals_list_element_id Order totals element ID
	 */
	$order_totals_list_element_id = apply_filters( 'cfw_template_cart_el', 'cfw-totals-list' );
	?>
	<div id="<?php echo $order_totals_list_element_id; ?>" class="cfw-module">
		<table class="cfw-module">
			<?php
			/**
			 * Fires at start of cart summary totals table
			 *
			 * @since 2.0.0
			 */
			do_action( 'cfw_before_cart_summary_totals' );
			?>

			<?php
			foreach ( $totals as $key => $total ) :
				if ( 'payment_method' === $key ) {
					continue;
				}
				?>
				<tr class="cart-subtotal <?php echo ( 'order_total' === $key ) ? 'order-total' : ''; ?>">
					<th><?php echo $total['label']; ?></th>
					<td><?php echo $total['value']; ?></td>
				</tr>
			<?php endforeach; ?>

			<?php do_action( 'woocommerce_review_order_after_order_total' ); ?>

			<?php
			/**
			 * Fires at end of cart summary totals table before </table> tag
			 *
			 * @since 2.0.0
			 */
			do_action( 'cfw_after_cart_summary_totals' );
			?>
		</table>
	</div>
	<?php

	/**
	 * Filters order totals HTML
	 *
	 * @since 2.0.0
	 *
	 * @param string $order_totals_html Cart totals HTML
	 */
	return apply_filters( 'cfw_order_totals_html', ob_get_clean() );
}

function cfw_address_class_wrap( $shipping = true ) {
	// If __field-wrapper class isn't there, Amazon Pay nukes our address fields :-(
	$result = 'woocommerce-billing-fields woocommerce-billing-fields__field-wrapper';

	if ( true === $shipping ) {
		$result = 'woocommerce-shipping-fields woocommerce-shipping-fields__field-wrapper';
	}

	echo $result;
}

function cfw_get_place_order( $order_button_text = false ) {
	ob_start();

	$order_button_text = ! $order_button_text ? apply_filters( 'woocommerce_order_button_text', __( 'Complete Order', 'checkout-wc' ) ) : $order_button_text;

	/**
	 * Filters place order button container classes
	 *
	 * @since 2.0.0
	 *
	 * @param array $place_order_button_container_classes Place order button container classes
	 */
	$place_order_button_container_class = join( ' ', apply_filters( 'cfw_place_order_button_container_classes', array( 'place-order' ) ) );
	?>
	<div class="<?php echo $place_order_button_container_class; ?>" data-total="<?php echo WC()->cart->get_total( 'checkoutwc' ); ?>" id="cfw-place-order">
		<?php echo apply_filters( 'woocommerce_order_button_html', '<button type="submit" class="cfw-primary-btn cfw-next-tab validate" name="woocommerce_checkout_place_order" id="place_order" formnovalidate="formnovalidate" value="' . esc_attr( $order_button_text ) . '" data-value="' . esc_attr( $order_button_text ) . '">' . esc_html( $order_button_text ) . '</button>' ); // @codingStandardsIgnoreLine ?>

		<?php do_action( 'woocommerce_review_order_after_submit' ); ?>

		<?php wp_nonce_field( 'woocommerce-process_checkout', 'woocommerce-process-checkout-nonce' ); ?>
		<input type="hidden" name="cfw_update_cart" value="false" />
	</div>
	<?php
	if ( ! is_ajax() ) {
		do_action( 'woocommerce_review_order_after_payment' );
	}

	return ob_get_clean();
}

function cfw_place_order( $order_button_text = false ) {
	echo cfw_get_place_order( $order_button_text );
}

function cfw_get_payment_methods( $available_gateways = false, $object = false, $show_title = true, $payment_methods_html = false ) {
	if ( false === $payment_methods_html ) {
		$payment_methods_html = cfw_get_payment_methods_html( $available_gateways );
	}

	$object = ! $object ? WC()->cart : $object;

	ob_start();
	?>
	<div id="cfw-billing-methods" class="cfw-module cfw-accordion">
		<?php
		/**
		 * Fires above the payment method heading
		 *
		 * @since 5.1.1
		 */
		do_action( 'cfw_before_payment_method_heading' );

		if ( $show_title ) :
			?>
			<h3>
				<?php
				/**
				 * Filters payment methods heading
				 *
				 * @since 2.0.0
				 *
				 * @param string $payment_methods_heading Payment methods heading
				 */
				echo apply_filters( 'cfw_payment_method_heading', esc_html__( 'Payment', 'checkout-wc' ) );
				?>
			</h3>
		<?php endif; ?>

		<?php
		/**
		 * Fires after payment methods heading and before transaction are encrypted statement
		 *
		 * @since 2.0.0
		 */
		do_action( 'cfw_checkout_before_payment_methods' );
		?>

		<?php if ( $object->needs_payment() ) : ?>
			<div class="cfw-payment-method-information-wrap">
				<h4 class="cfw-small secure-notice">
					<?php
					/**
					 * Filters payment methods transactions are encrypted statement
					 *
					 * @since 2.0.0
					 *
					 * @param string $transactions_encrypted_statement Payment methods transactions are encrypted statement
					 */
					echo apply_filters( 'cfw_transactions_encrypted_statement', esc_html__( 'All transactions are secure and encrypted.', 'checkout-wc' ) );
					?>
				</h4>

				<div class="cfw-payment-methods-wrap">
					<div id="payment" class="woocommerce-checkout-payment">
						<?php echo $payment_methods_html; ?>
					</div>
				</div>
			</div>
		<?php else : ?>
			<div class="cfw-no-payment-method-wrap">
				<span class="cfw-small">
					<?php
					/**
					 * Filters no payment required text
					 *
					 * @since 2.0.0
					 *
					 * @param string $no_payment_required_text No payment required text
					 */
					echo apply_filters( 'cfw_no_payment_required_text', esc_html__( 'Your order is free. No payment is required.', 'checkout-wc' ) );
					?>
				</span>
			</div>
		<?php endif; ?>

		<?php
		/**
		 * Fires at end of payment methods container before </div> tag
		 *
		 * @since 2.0.0
		 */
		do_action( 'cfw_checkout_after_payment_methods' );
		?>
	</div>
	<?php

	return ob_get_clean();
}

function cfw_billing_address_radio_group() {
	/**
	 * Fires before billing address radio group is output
	 *
	 * @since 2.0.0
	 */
	do_action( 'cfw_checkout_before_billing_address' );

	/**
	 * Filters whether to force displaying the billing address (no accordion)
	 *
	 * @since 2.0.0
	 *
	 * @param bool $force_display_billing_address Force displaying billing address
	 */
	if ( ! apply_filters( 'cfw_force_display_billing_address', false ) ) :
		?>
		<div id="cfw-shipping-same-billing" class="cfw-module cfw-accordion">
			<ul class="cfw-radio-reveal-group">
				<li class="cfw-radio-reveal-li cfw-no-reveal">
					<div class="cfw-radio-reveal-title-wrap">
						<input type="radio" name="bill_to_different_address" id="billing_same_as_shipping_radio" value="same_as_shipping" class="garlic-auto-save" checked="checked" />

						<label for="billing_same_as_shipping_radio" class="cfw-radio-reveal-label">
							<div>
								<span class="cfw-radio-reveal-title"><?php esc_html_e( 'Same as shipping address', 'checkout-wc' ); ?></span>
							</div>
						</label>

						<?php
						/**
						 * Fires after same as shipping address label
						 *
						 * @since 2.0.0
						 */
						do_action( 'cfw_after_same_as_shipping_address_label' );
						?>
					</div>
				</li>
				<li class="cfw-radio-reveal-li">
					<div class="cfw-radio-reveal-title-wrap">
						<input type="radio" name="bill_to_different_address" id="shipping_dif_from_billing_radio" value="different_from_shipping" class="garlic-auto-save" />

						<label for="shipping_dif_from_billing_radio" class="cfw-radio-reveal-label">
							<div>
								<span class="cfw-radio-reveal-title"><?php esc_html_e( 'Use a different billing address', 'checkout-wc' ); ?></span>
							</div>
						</label>
					</div>
					<div id="cfw-billing-fields-container" class="cfw-radio-reveal-content <?php cfw_address_class_wrap( false ); ?>" style="display: none">
						<?php
						/**
						 * Fires before billing address inside billing address container
						 *
						 * @since 2.0.0
						 */
						do_action( 'cfw_start_billing_address_container' );

						cfw_get_billing_checkout_fields( WC()->checkout() );

						/**
						 * Fires after billing address inside billing address container
						 *
						 * @since 2.0.0
						 */
						do_action( 'cfw_end_billing_address_container' );
						?>
					</div>
				</li>
			</ul>
		</div>
	<?php else : ?>
		<input type="hidden" name="bill_to_different_address" id="billing_same_as_shipping_radio" value="different_from_shipping" />
		<?php cfw_get_billing_checkout_fields( WC()->checkout() ); ?>
	<?php endif; ?>

	<!-- wrapper required for compatibility with Pont shipping for Woocommerce -->
	<div class="cfw-force-hidden">
		<div id="ship-to-different-address">
			<input id="ship-to-different-address-checkbox" type="checkbox" name="ship_to_different_address" value="<?php echo WC()->cart->needs_shipping_address() ? 1 : 0; ?>" checked="checked" />
		</div>
	</div>

	<?php
	/**
	 * Fires after billing address
	 *
	 * @since 2.0.0
	 */
	do_action( 'cfw_checkout_after_billing_address' );
}

/**
 * Get all approved WooCommerce order notes.
 *
 * @param int|string $order_id The order ID.
 * @param string $status_search
 *
 * @return bool|string
 */
function cfw_order_status_date( $order_id, $status_search ) {
	remove_filter( 'comments_clauses', array( 'WC_Comments', 'exclude_order_comments' ) );

	$notes = get_comments(
		array(
			'post_id' => $order_id,
			'orderby' => 'comment_ID',
			'order'   => 'DESC',
			'approve' => 'approve',
			'type'    => 'order_note',
		)
	);

	add_filter( 'comments_clauses', array( 'WC_Comments', 'exclude_order_comments' ) );

	$pattern = sprintf( cfw__( 'Order status changed from %1$s to %2$s.', 'woocommerce' ), 'X', $status_search );

	$pieces         = explode( ' ', $pattern );
	$last_two_words = implode( ' ', array_splice( $pieces, -2 ) );

	foreach ( $notes as $note ) {
		if ( false !== stripos( $note->comment_content, $last_two_words ) ) {
			return $note->comment_date_gmt;
		}
	}

	return false;
}

/**
 * @param \WC_Order $order
 */
function cfw_maybe_output_tracking_numbers( $order ) {
	$output = '';

	if ( defined( 'WC_SHIPMENT_TRACKING_VERSION' ) ) {
		$tracking_items = \WC_Shipment_Tracking_Actions::get_instance()->get_tracking_items( $order->get_id(), true );
		$label_suffix   = cfw__( 'Tracking Number:', 'woocommerce-shipment-tracking' );

		foreach ( $tracking_items as $tracking_item ) {
			/**
			 * Filters tracking link header on thank you page
			 *
			 * @since 3.14.0
			 *
			 * @param string $shipment_tracking_header Tracking link header
			 * @param string $tracking_provider The shipping provider for tracking link
			 */
			$output .= apply_filters( 'cfw_thank_you_shipment_tracking_header', "<h4>{$tracking_item['formatted_tracking_provider']} {$label_suffix}</h4>", $tracking_item['formatted_tracking_provider'] );

			/**
			 * Filters tracking link output on thank you page
			 *
			 * @since 3.14.0
			 *
			 * @param string $shipment_tracking_link Tracking link output
			 * @param string $tracking_link The tracking link
			 * @param string $tracking_number The tracking number
			 */
			$output .= apply_filters( 'cfw_thank_you_shipment_tracking_link', "<p><a class=\"tracking-number\" target=\"_blank\" href=\"{$tracking_item['formatted_tracking_link']}\">{$tracking_item['tracking_number']}</a></p>", $tracking_item['formatted_tracking_link'], $tracking_item['tracking_number'] );
		}
	} elseif ( function_exists( 'wc_advanced_shipment_tracking' ) ) {
		ob_start();
		$wc_advanced_shipment_tracking_actions = \WC_Advanced_Shipment_Tracking_Actions::get_instance();
		$wc_advanced_shipment_tracking_actions->show_tracking_info_order( $order->get_id() );

		$output = ob_get_clean();
	} elseif ( has_filter( 'cfw_thank_you_tracking_numbers' ) ) {
		/**
		 * Filter to handle custom shipment tracking links output on thank you page
		 *
		 * @since 3.0.0
		 *
		 * @param string $custom_tracking_numbers_output The tracking numbers output
		 * @param \WC_Order $order The order object
		 */
		$output = apply_filters( 'cfw_thank_you_tracking_numbers', '', $order );
	}

	if ( ! empty( $output ) ) {
		echo '<div class="inner cfw-padded">';

		/**
		 * Filter tracking numbers output on thank you page
		 *
		 * @since 3.0.0
		 *
		 * @param string $tracking_numbers_output The tracking numbers output HTML
		 * @param \WC_Order $order The order object
		 */
		echo apply_filters( 'cfw_maybe_output_tracking_numbers', $output, $order );

		echo '</div>';
	}
}

function cfw_return_to_cart_link() {
	if ( ! apply_filters( 'cfw_show_return_to_cart_link', true ) ) {
		return;
	}

	/**
	 * Filter return to cart link URL
	 *
	 * @since 2.0.0
	 *
	 * @param string $return_to_cart_link_url Return to cart link URL
	 */
	$return_to_cart_link_url = apply_filters( 'cfw_return_to_cart_link_url', wc_get_cart_url() );

	/**
	 * Filter return to cart link text
	 *
	 * @since 2.0.0
	 *
	 * @param string $return_to_cart_link_text Return to cart link text
	 */
	$return_to_cart_link_text = apply_filters( 'cfw_return_to_cart_link_text', esc_html__( 'Return to cart', 'checkout-wc' ) );

	/**
	 * Filter return to cart link
	 *
	 * @since 2.0.0
	 *
	 * @param string $cart_link Return to cart link
	 */
	echo apply_filters( 'cfw_return_to_cart_link', sprintf( '<a href="%s" class="cfw-prev-tab">« %s</a>', $return_to_cart_link_url, $return_to_cart_link_text ) );
}

/**
 * @param string $label The pre-translated button label
 * @param array $classes Any extra classes to add
 */
function cfw_continue_to_shipping_button( string $label = '', array $classes = array() ) {
	$new_classes = array_merge(
		array(
			'cfw-primary-btn' => 'cfw-primary-btn',
			'cfw-next-tab',
			'cfw-continue-to-shipping-btn',
		),
		$classes
	);

	if ( in_array( 'cfw-secondary-btn', $classes, true ) ) {
		unset( $new_classes['cfw-primary-btn'] );
	}

	/**
	 * Filter continue to shipping method button label
	 *
	 * @since 3.0.0
	 *
	 * @param string $continue_to_shipping_method_label Continue to shipping method button label
	 */
	$continue_to_shipping_method_label = ! empty( $label ) ? $label : apply_filters( 'cfw_continue_to_shipping_method_label', esc_html__( 'Continue to shipping', 'checkout-wc' ) );

	/**
	 * Filter continue to shipping method button
	 *
	 * @since 3.0.0
	 *
	 * @param string $shipping_method_button Continue to shipping method button
	 */
	echo apply_filters( 'cfw_continue_to_shipping_button', sprintf( '<a href="javascript:" data-tab="#cfw-shipping-method" class="%s">%s</a>', join( ' ', $new_classes ), $continue_to_shipping_method_label ) );
}

/**
 * @param string $label The pre-translated button label
 * @param array $classes Any extra classes to add to the button
 */
function cfw_continue_to_payment_button( string $label = '', array $classes = array() ) {
	$new_classes = array_merge(
		$classes,
		array(
			'cfw-primary-btn' => 'cfw-primary-btn',
			'cfw-next-tab',
			'cfw-continue-to-payment-btn',
		)
	);

	if ( in_array( 'cfw-secondary-btn', $classes, true ) ) {
		unset( $new_classes['cfw-primary-btn'] );
	}

	/**
	 * Filter continue to payment method button label
	 *
	 * @since 3.0.0
	 *
	 * @param string $continue_to_payment_method_label Continue to payment method button label
	 */
	$continue_to_payment_method_label = ! empty( $label ) ? $label : apply_filters( 'cfw_continue_to_payment_method_label', esc_html__( 'Continue to payment', 'checkout-wc' ) );

	/**
	 * Filter continue to payment method button
	 *
	 * @since 3.0.0
	 *
	 * @param string $payment_method_button Continue to payment method button
	 */
	echo apply_filters( 'cfw_continue_to_payment_button', sprintf( '<a href="javascript:" data-tab="#cfw-payment-method" class="%s">%s</a>', join( ' ', $new_classes ), $continue_to_payment_method_label ) );
}

function cfw_continue_to_order_review_button() {
	/**
	 * Filter continue to order review button label
	 *
	 * @since 3.0.0
	 *
	 * @param string $continue_to_order_review_label Continue to order review button label
	 */
	$continue_to_order_review_label = apply_filters( 'cfw_continue_to_order_review_label', esc_html__( 'Review order', 'checkout-wc' ) );

	/**
	 * Filter continue to order review button
	 *
	 * @since 3.0.0
	 *
	 * @param string $order_review_button Continue to order review button
	 */
	echo apply_filters( 'cfw_continue_to_order_review_button', sprintf( '<a href="javascript:" data-tab="#cfw-order-review" class="cfw-primary-btn cfw-next-tab cfw-continue-to-order-review-btn">%s</a>', $continue_to_order_review_label ) );
}

function cfw_return_to_customer_information_link() {
	/**
	 * Filter return to customer information tab label
	 *
	 * @since 3.0.0
	 *
	 * @param string $return_to_customer_info_label Return to customer information tab label
	 */
	$return_to_customer_info_label = apply_filters( 'cfw_return_to_customer_info_label', esc_html__( 'Return to information', 'checkout-wc' ) );

	/**
	 * Filter return to customer information tab link
	 *
	 * @since 3.0.0
	 *
	 * @param string $return_to_customer_info_link Return to customer information tab link
	 */
	echo apply_filters( 'cfw_return_to_customer_information_link', sprintf( '<a href="javascript:" data-tab="#cfw-customer-info" class="cfw-prev-tab cfw-return-to-information-btn">« %s</a>', $return_to_customer_info_label ) );
}

function cfw_return_to_shipping_method_link() {
	/**
	 * Filter return to shipping method tab label
	 *
	 * @since 3.0.0
	 *
	 * @param string $return_to_shipping_method_label Return to shipping method tab label
	 */
	$return_to_shipping_method_label = apply_filters( 'cfw_return_to_shipping_method_label', esc_html__( 'Return to shipping', 'checkout-wc' ) );

	/**
	 * Filter return to shipping method tab link
	 *
	 * @since 3.0.0
	 *
	 * @param string $return_to_shipping_method_link Return to shipping method tab link
	 */
	echo apply_filters( 'cfw_return_to_shipping_method_link', sprintf( '<a href="javascript:" data-tab="#cfw-shipping-method" class="cfw-prev-tab cfw-return-to-shipping-btn">« %s</a>', $return_to_shipping_method_label ) );
}

function cfw_return_to_payment_method_link() {
	/**
	 * Filter return to payment method tab label
	 *
	 * @since 3.0.0
	 *
	 * @param string $return_to_payment_method_label Return to payment method tab label
	 */
	$return_to_payment_method_label = apply_filters( 'cfw_return_to_payment_method_label', esc_html__( 'Return to payment', 'checkout-wc' ) );

	/**
	 * Filter return to payment method tab link
	 *
	 * @since 3.0.0
	 *
	 * @param string $return_to_payment_method_link Return to payment method tab link
	 */
	echo apply_filters( 'cfw_return_to_payment_method_link', sprintf( '<a href="javascript:" data-tab="#cfw-payment-method" class="cfw-prev-tab cfw-return-to-payment-btn">« %s</a>', $return_to_payment_method_label ) );
}

/**
 * @return bool
 */
function cfw_show_customer_information_tab(): bool {
	/**
	 * Filters whether to show customer information tab
	 *
	 * @since 3.0.0
	 *
	 * @param bool $show_customer_information_tab Show customer information tab
	 */
	return apply_filters( 'cfw_show_customer_information_tab', true );
}

function cfw_breadcrumb_navigation() {
	$show_customer_info_tab = apply_filters( 'cfw_show_customer_information_tab', true );

	$default_breadcrumbs = array(
		'cart'            => array(
			/**
			 * Filters breadcrumb cart link URL
			 *
			 * @since 3.0.0
			 *
			 * @param string $breadcrumb_cart_link_url Breadcrumb cart link URL
			 */
			'href'     => apply_filters( 'cfw_breadcrumb_cart_url', wc_get_cart_url() ),

			/**
			 * Filters breadcrumb cart link label
			 *
			 * @since 3.0.0
			 *
			 * @param string $breadcrumb_cart_link_label Breadcrumb cart link label
			 */
			'label'    => apply_filters( 'cfw_breadcrumb_cart_label', cfw_esc_html__( 'Cart', 'woocommerce' ) ),
			'priority' => 10,
		),
		'information-tab' => array(
			'href'     => '#cfw-customer-info',

			/**
			 * Filters customer information breadcrumb label
			 *
			 * @since 3.0.0
			 *
			 * @param string $customer_information_breadcrumb_label Customer information breadcrumb label
			 */
			'label'    => apply_filters( 'cfw_breadcrumb_customer_info_label', esc_html__( 'Information', 'checkout-wc' ) ),
			'priority' => 20,
		),
		'shipping-tab'    => array(
			'href'     => '#cfw-shipping-method',

			/**
			 * Filters shipping method breadcrumb label
			 *
			 * @since 3.0.0
			 *
			 * @param string $shipping_method_breadcrumb_label Shipping method breadcrumb label
			 */
			'label'    => apply_filters( 'cfw_breadcrumb_shipping_label', esc_html__( 'Shipping', 'checkout-wc' ) ),
			'priority' => 30,
		),
		'payment-tab'     => array(
			'href'     => '#cfw-payment-method',

			/**
			 * Filters payment method breadcrumb label
			 *
			 * @since 3.0.0
			 *
			 * @param string $payment_method_breadcrumb_label Payment method breadcrumb label
			 */
			'label'    => apply_filters( 'cfw_breadcrumb_payment_label', esc_html__( 'Payment', 'checkout-wc' ) ),
			'priority' => 40,
		),
	);

	if ( ! $show_customer_info_tab ) {
		unset( $default_breadcrumbs['customer_info'] );
	}

	/**
	 * Filters breadcrumbs
	 *
	 * @since 3.0.0
	 *
	 * @param string $breadcrumbs Breadcrumbs
	 */
	$breadcrumbs = apply_filters( 'cfw_breadcrumbs', $default_breadcrumbs );

	// Order by priority
	uasort( $breadcrumbs, 'cfw_breadcrumb_uasort_comparison' );

	/**
	 * Fires before breadcrumb navigation is output
	 *
	 * @since 2.0.0
	 */
	do_action( 'cfw_before_breadcrumb_navigation' );
	?>
	<ul id="cfw-breadcrumb" class="etabs">
		<?php foreach ( $breadcrumbs as $id => $breadcrumb ) : ?>
		<li class="<?php echo ( 'cart' !== $id ) ? 'tab' : ''; ?> <?php echo $id; ?>">
			<a href="<?php echo esc_attr( $breadcrumb['href'] ); ?>" class="cfw-small"><?php echo esc_html( $breadcrumb['label'] ); ?></a>
		</li>
		<?php endforeach; ?>
	</ul>
	<?php

	/**
	 * Fires after breadcrumb navigation is output
	 *
	 * @since 2.0.0
	 */
	do_action( 'cfw_after_breadcrumb_navigation' );
}

/**
 * User to sort breadcrumbs based on priority with uasort.
 *
 * @since 3.5.1
 * @param array $a First breadcrumb to compare.
 * @param array $b Second breadcrumb to compare.
 * @return int
 */
function cfw_breadcrumb_uasort_comparison( $a, $b ) {
	/*
	 * We are not guaranteed to get a priority
	 * setting. So don't compare if they don't
	 * exist.
	 */
	if ( ! isset( $a['priority'], $b['priority'] ) ) {
		return 0;
	}

	return wc_uasort_comparison( $a['priority'], $b['priority'] );
}

/**
 * @param $name
 *
 * @return mixed|null
 */
function cfw_constant( $name ) {
	if ( ! defined( $name ) ) {
		return null;
	}

	return constant( $name );
}

function cfw_main_container_classes( $context = 'checkout' ) {
	$classes = array();

	$classes[] = 'container';
	$classes[] = 'context-' . $context;
	$classes[] = 'checkoutwc';

	if ( is_admin_bar_showing() ) {
		$classes[] = 'admin-bar';
	}

	if ( SettingsManager::instance()->get_setting( 'label_style' ) === 'normal' ) {
		$classes[] = 'cfw-label-style-normal';
	}

	/**
	 * Filters main container classes
	 *
	 * @since 3.0.0
	 *
	 * @param string $classes Main container classes
	 */
	return apply_filters( "cfw_{$context}_main_container_classes", join( ' ', $classes ) );
}

/**
 * @param callable $function
 * @return false|string
 */
function cfw_return_function_output( callable $function ) {
	ob_start();

	$function();

	return ob_get_clean();
}

function cfw_count_filters( $filter ): int {
	global $wp_filter;
	$count = 0;

	if ( isset( $wp_filter[ $filter ] ) ) {
		foreach ( $wp_filter[ $filter ]->callbacks as $callbacks ) {
			$count += (int) count( $callbacks );
		}
	}

	return $count;
}

/**
 * @return bool
 */
function cfw_is_checkout(): bool {
	/**
	 * Filter cfw_is_checkout()
	 *
	 * @since 3.0.0
	 *
	 * @param bool $is_checkout Whether or not we are on the checkout page
	 */
	return apply_filters(
		'cfw_is_checkout',
		( function_exists( 'is_checkout' ) && is_checkout() ) &&
		! is_order_received_page() &&
		! is_checkout_pay_page()
	);
}

/**
 * @return bool
 */
function cfw_is_checkout_pay_page(): bool {
	/**
	 * Filter is_checkout_pay_page()
	 *
	 * @since 3.0.0
	 *
	 * @param bool $is_checkout_pay_page Whether or not we are on the checkout pay page
	 */
	return apply_filters(
		'cfw_is_checkout_pay_page',
		function_exists( 'is_checkout_pay_page' ) &&
		is_checkout_pay_page() &&
		cfw_get_active_template()->supports( 'order-pay' ) &&
		PlanManager::can_access_feature( 'enable_order_pay', PlanManager::PLUS )
	);
}

/**
 * @return bool
 */
function cfw_is_order_received_page(): bool {
	/**
	 * Filter is_order_received_page()
	 *
	 * @since 3.0.0
	 *
	 * @param bool $is_order_received_page Whether or not we are on the order received page
	 */
	return apply_filters(
		'cfw_is_order_received_page',
		function_exists( 'is_order_received_page' ) &&
		is_order_received_page() &&
		cfw_get_active_template()->supports( 'order-received' ) &&
		PlanManager::can_access_feature( 'enable_thank_you_page', PlanManager::PLUS )
	);
}

/**
 * @return bool
 */
function is_cfw_page(): bool {
	return cfw_is_checkout() || cfw_is_checkout_pay_page() || cfw_is_order_received_page();
}

/**
 * Determines whether CheckoutWC templates can load on the frontend
 *
 * @return bool
 */
function cfw_is_enabled(): bool {
	$valid_license       = UpdatesManager::instance()->license_is_valid();
	$templates_enabled   = SettingsManager::instance()->get_setting( 'enable' ) === 'yes';
	$is_admin            = current_user_can( 'manage_options' );
	$user_can_access     = ( $valid_license && $templates_enabled ) || $is_admin;
	$forcefully_disabled = defined( 'CFW_DISABLE_TEMPLATES' ) || isset( $_COOKIE['CFW_DISABLE_TEMPLATES'] ) || isset( $_GET['bypass-cfw'] );

	return $user_can_access && ! $forcefully_disabled;
}

/**
 * Get phone field setting
 *
 * @return boolean
 */
function cfw_is_phone_fields_enabled(): bool {
	return 'hidden' !== get_option( 'woocommerce_checkout_phone_field', 'required' );
}

/**
 * Match new guest order to existing account if it exists
 *
 * @param $order_id
 */
function cfw_maybe_match_new_order_to_user_account( $order_id ) {
	$order = wc_get_order( $order_id );
	$user  = $order->get_user();

	if ( ! $user ) {
		$user_data = get_user_by( 'email', $order->get_billing_email() );

		if ( ! empty( $user_data->ID ) ) {
			try {
				$order->set_customer_id( $user_data->ID );
				$order->save();
			} catch ( \WC_Data_Exception $e ) {
				error_log( "CheckoutWC: Error matching {$order_id} to customer {$user_data->ID}" );
			}
		}
	}
}

/**
 * Match old guest orders to new account if they exist
 *
 * @param $user_id
 */
function cfw_maybe_link_orders_at_registration( $user_id ) {
	wc_update_new_customer_past_orders( $user_id );
}

function cfw_get_plugin_template_path(): string {
	return CFW_PATH_BASE . '/templates';
}

function cfw_get_user_template_path(): string {
	return get_stylesheet_directory() . '/checkout-wc';
}

function cfw_get_active_template(): Template {
	$active_template_slug = SettingsManager::instance()->get_setting( 'active_template' );
	return new Template( empty( $active_template_slug ) ? 'default' : $active_template_slug );
}

/**
 * @return Template[]
 */
function cfw_get_available_templates(): array {
	return Template::get_all_available();
}

function cfw_frontend() {
	// Enqueue Assets
	( new AssetManager() )->init();

	if ( ! is_cfw_page() ) {
		return;
	}

	// Output Templates
	if ( SettingsManager::instance()->get_setting( 'template_loader' ) === 'content' ) {
		Content::checkout();
		Content::order_pay();
		Content::order_received();

		return;
	}

	add_action(
		'template_redirect',
		function() {
			Redirect::template_redirect();
		},
		apply_filters( 'cfw_template_redirect_priority', 11 )
	);
}

/**
 * @return bool|\WC_Order|\WC_Order_Refund
 */
function cfw_get_order_received_order() {
	global $wp;

	$order_id = $wp->query_vars['order-received'];
	$order    = false;

	$order_id  = apply_filters( 'woocommerce_thankyou_order_id', absint( $order_id ) );
	$order_key = apply_filters( 'woocommerce_thankyou_order_key', empty( $_GET['key'] ) ? '' : wc_clean( wp_unslash( $_GET['key'] ) ) ); // WPCS: input var ok, CSRF ok.

	if ( $order_id > 0 ) {
		$order = wc_get_order( $order_id );
		if ( ! $order || ! hash_equals( $order->get_order_key(), $order_key ) ) {
			$order = false;
		}
	}

	return $order;
}

/**
 * @return FormAugmenter|null
 */
function cfw_get_form(): FormAugmenter {
	return FormAugmenter::instance();
}

/**
 * @return bool
 */
function cfw_is_thank_you_page_active(): bool {
	return PlanManager::can_access_feature( 'enable_thank_you_page', PlanManager::PLUS );
}

/**
 * @return bool
 */
function cfw_is_thank_you_view_order_page_active(): bool {
	return cfw_is_thank_you_page_active() && PlanManager::can_access_feature( 'override_view_order_template', PlanManager::PLUS );
}

/**
 * @return false|string
 */
function cfw_get_logo_url() {
	$logo_attachment_id = SettingsManager::instance()->get_setting( 'logo_attachment_id' );

	return wp_get_attachment_url( $logo_attachment_id );
}

function cfw_logo() {
	/**
	 * Filters header logo / title link URL
	 *
	 * @since 3.0.0
	 *
	 * @param string $url The link URL
	 */
	$url = apply_filters( 'cfw_header_home_url', get_home_url() );

	/**
	 * Filters header logo / title link URL
	 *
	 * @since 5.3.0
	 *
	 * @param string $url The link URL
	 */
	$blog_name = apply_filters( 'cfw_header_blog_name', get_bloginfo( 'name' ) );

	$logo_url = cfw_get_logo_url();
	?>
	<div class="cfw-logo">
		<a title="<?php echo html_entity_decode( $blog_name, ENT_QUOTES ); ?>" href="<?php echo $url; ?>" class="<?php echo ! empty( $logo_url ) ? 'logo' : ''; ?>">
			<?php if ( empty( $logo_url ) ) : ?>
				<?php echo html_entity_decode( $blog_name, ENT_QUOTES ); ?>
			<?php endif; ?>
		</a>
	</div>
	<?php
}

/**
 * Add WP theme styles to list of blocked style handles.
 *
 * @param $styles
 *
 * @return array
 */
function cfw_remove_theme_styles( $styles ): array {
	global $wp_styles;

	$theme_directory_uri = get_theme_root_uri();
	$theme_directory_uri = str_replace( array( 'http:', 'https:' ), '', $theme_directory_uri ); // handle both http/https/and relative protocol URLs

	foreach ( $wp_styles->registered as $wp_style ) {
		if ( ! empty( $wp_style->src ) && stripos( $wp_style->src, $theme_directory_uri ) !== false && stripos( $wp_style->src, '/checkout-wc/' ) === false ) {
			$styles[] = $wp_style->handle;
		}
	}

	return $styles;
}

/**
 * Add WP theme styles to list of blocked style handles.
 *
 * @param $scripts
 *
 * @return array
 */
function cfw_remove_theme_scripts( $scripts ): array {
	global $wp_scripts;

	$theme_directory_uri = get_theme_root_uri();
	$theme_directory_uri = str_replace( array( 'http:', 'https:' ), '', $theme_directory_uri ); // handle both http/https/and relative protocol URLs

	foreach ( $wp_scripts->registered as $wp_script ) {
		if ( ! empty( $wp_script->src ) && stripos( $wp_script->src, $theme_directory_uri ) !== false && stripos( $wp_script->src, '/checkout-wc/' ) === false ) {
			$scripts[] = $wp_script->handle;
		}
	}

	return $scripts;
}

/**
 * For gateways that add buttons above checkout form
 *
 * @param string $class
 * @param string $id
 * @param string $style
 */
function cfw_add_separator( $class = '', $id = '', $style = '' ) {
	if ( ! defined( 'CFW_PAYMENT_BUTTON_SEPARATOR' ) ) {
		define( 'CFW_PAYMENT_BUTTON_SEPARATOR', true );
	} else {
		return;
	}
	?>
	<div id="payment-info-separator-wrap" class="<?php echo $class; ?>">
		<p <?php echo ( $id ) ? "id='{$id}'" : ''; ?> <?php echo ( $style ) ? "style='{$style}'" : ''; ?> class="pay-button-separator">
			<span>
				<?php
				/**
				 * Filters payment request button separator text
				 *
				 * @since 2.0.0
				 *
				 * @param string $separator_label The separator label (default: Or)
				 */
				echo esc_html( apply_filters( 'cfw_express_pay_separator_text', __( 'Or', 'checkout-wc' ) ) );
				?>
			</span>
		</p>
	</div>
	<?php
}

/**
 * @param string $hook
 * @param string $function_name
 * @param int $priority
 * @return false|mixed
 */
function cfw_get_hook_instance_object( string $hook, string $function_name, int $priority = 10 ) {
	global $wp_filter;

	$existing_hooks = $wp_filter[ $hook ];

	if ( $existing_hooks[ $priority ] ) {
		foreach ( $existing_hooks[ $priority ] as $key => $callback ) {
			if ( false !== stripos( $key, $function_name ) ) {
				return $callback['function'][0];
			}
		}
	}

	return false;
}

/**
 * @return bool
 */
function cfw_is_login_at_checkout_allowed(): bool {
	return 'yes' === get_option( 'woocommerce_enable_checkout_login_reminder' );
}

/**
 * @return bool
 */
function cfw_is_enhanced_login_enabled(): bool {
	$enable_enhanced_login = apply_filters_deprecated( 'cfw_suppress_default_login_form', array( SettingsManager::instance()->get_setting( 'login_style' ) !== 'woocommerce' ), 'CheckoutWC 5.3.7', 'cfw_enable_enhanced_login' );

	return apply_filters( 'cfw_enable_enhanced_login', $enable_enhanced_login ) && cfw_is_login_at_checkout_allowed();
}

/**
 * @param array $cart_data
 * @return bool
 */
function cfw_update_cart( array $cart_data ): bool {
	try {
		foreach ( $cart_data as $cart_item_key => $value ) {
			$cart_item = WC()->cart->get_cart_item( $cart_item_key );

			if ( null === $cart_item ) {
				WC()->cart->remove_cart_item( $cart_item_key );
				continue;
			}

			/** @var \WC_Product $cart_item_product */
			$cart_item_product = $cart_item['data'];

			if ( ! $cart_item_product || ! $cart_item_product->exists() ) {
				continue;
			}

			$max_quantity = apply_filters( 'woocommerce_quantity_input_max', $cart_item_product->get_max_purchase_quantity() > 0 ? $cart_item_product->get_max_purchase_quantity() : PHP_INT_MAX, $cart_item_product );

			if ( $value['qty'] > $max_quantity ) {
				$value['qty'] = $max_quantity;
			}

			/**
			 * Remove items from the cart contents
			 * Ensures things like subscriptions update their output properly
			 * Note: Using strval() here instead of intval to handle partial quantities like 0.5
			 *
			 * We don't use WC()->cart->set_quantity()'s understanding of setting a 0 quantity because it causes a bug we can't remember.
			 */
			if ( '0' === strval( $value['qty'] ) ) {
				WC()->cart->remove_cart_item( $cart_item_key );
			} elseif ( floatval( WC()->cart->cart_contents[ $cart_item_key ]['quantity'] ) !== floatval( $value['qty'] ) ) {
				WC()->cart->set_quantity( $cart_item_key, $value['qty'], false );
			}
		}
	} catch ( Exception $e ) {
		return false;
	}

	// Calculate shipping before totals. This will ensure any shipping methods that affect things like taxes are chosen prior to final totals being calculated. Ref: #22708.
	// Without these lines, changes aren't saved
	WC()->cart->calculate_shipping();
	WC()->cart->calculate_totals();

	do_action( 'cfw_cart_updated' );

	return true;
}

/**
 * @param array $cart_item
 * @param string $cart_item_key
 * @param Item $item
 * @return string
 */
function cfw_get_cart_item_quantity_control( array $cart_item, string $cart_item_key, WC_Product $product ): string {
	if ( empty( $cart_item_key ) ) {
		return '';
	}

	/**
	 * Get the output of the cart quantity control to determine if it's being modified
	 *
	 * Output filtering is required because some very stupid YITH plugins echo on the filter instead of returning something.
	 */
	$product_quantity = woocommerce_quantity_input(
		array(
			'input_name'   => "cart[{$cart_item_key}][qty]",
			'input_value'  => $cart_item['quantity'],
			'max_value'    => $product->get_max_purchase_quantity(),
			'min_value'    => '0',
			'product_name' => $product->get_name(),
		),
		$product,
		false
	);

	ob_start();

	$woocommerce_core_cart_quantity = apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item ); // PHPCS: XSS ok.

	$filter_output = ob_get_clean();

	if ( ! empty( $filter_output ) ) {
		$woocommerce_core_cart_quantity = $filter_output;
	}

	$max_quantity = apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity() > 0 ? $product->get_max_purchase_quantity() : PHP_INT_MAX, $product );
	$maxed        = $cart_item['quantity'] >= $max_quantity || $product->is_sold_individually();

	/**
	 * Filters cart item minimum quantity
	 *
	 * @since 2.0.0
	 *
	 * @param int $min_quantity Cart item minimum quantity
	 * @param array $cart_item The cart item
	 * @param string $cart_item_key The cart item key
	 */
	$min_quantity = apply_filters( 'cfw_cart_item_quantity_min_value', 1, $cart_item, $cart_item_key );

	/**
	 * Filters cart item quantity step
	 *
	 * Determines how much to increment or decrement by
	 *
	 * @since 2.0.0
	 *
	 * @param int $quantity_step Cart item quantity step amount
	 * @param array $cart_item The cart item
	 * @param string $cart_item_key The cart item key
	 */
	$quantity_step = apply_filters( 'cfw_cart_item_quantity_step', 1, $cart_item, $cart_item_key );

	ob_start();
	if ( $woocommerce_core_cart_quantity === $product_quantity ) {
		?>
		<div class="cfw-edit-item-quantity-control-wrap">
			<div class="cfw-quantity-stepper">
				<input type="hidden" data-min-value="<?php echo $min_quantity; ?>" data-step="<?php echo $quantity_step; ?>" data-max-quantity="<?php echo $max_quantity; ?>" class="cfw-edit-item-quantity-value" name="cart[<?php echo $cart_item_key; ?>][qty]" value="<?php echo $cart_item['quantity']; ?>" />
				<div class="cfw-quantity-stepper-btn-minus"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path d="M376 232H8c-4.42 0-8 3.58-8 8v32c0 4.42 3.58 8 8 8h368c4.42 0 8-3.58 8-8v-32c0-4.42-3.58-8-8-8z"/></svg></div>
				<div class="cfw-quantity-stepper-btn-plus <?php echo $maxed ? 'maxed' : ''; ?>">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path d="M376 232H216V72c0-4.42-3.58-8-8-8h-32c-4.42 0-8 3.58-8 8v160H8c-4.42 0-8 3.58-8 8v32c0 4.42 3.58 8 8 8h160v160c0 4.42 3.58 8 8 8h32c4.42 0 8-3.58 8-8V280h160c4.42 0 8-3.58 8-8v-32c0-4.42-3.58-8-8-8z"/></svg>
				</div>
			</div>
			<a href="javascript:" data-quantity="<?php echo esc_attr( $cart_item['quantity'] ); ?>" class="cfw-quantity-bulk-edit cfw-xtra-small"><?php cfw_e( 'Edit', 'woocommerce' ); ?></a>
		</div>

		<?php
		return (string) ob_get_clean();
	}
	?>
	<div class="cfw-edit-item-quantity-control-wrap">
		<input type="hidden" data-min-value="<?php echo $min_quantity; ?>" data-step="<?php echo $quantity_step; ?>" data-max-quantity="<?php echo $max_quantity; ?>" class="cfw-edit-item-quantity-value" name="cart[<?php echo $cart_item_key; ?>][qty]" value="<?php echo $cart_item['quantity']; ?>" />
		<a href="javascript:" data-quantity="<?php echo esc_attr( $cart_item['quantity'] ); ?>" class="cfw-quantity-remove-item cfw-xtra-small"><?php cfw_e( 'Remove', 'woocommerce' ); ?></a>
	</div>
	<?php
	return (string) ob_get_clean();
}

function cfw_get_woocommerce_notices(): array {
	/**
	 * Set notices
	 */
	$all_notices = WC()->session->get( 'wc_notices', array() );

	// Filter out empty messages
	foreach ( $all_notices as $key => $notice ) {
		if ( empty( array_filter( $notice ) ) ) {
			unset( $all_notices[ $key ] );
		}
	}

	/** This filter is documented in woocommerce/includes/wc-notice-functions.php **/
	$notice_types = apply_filters( 'woocommerce_notice_types', array( 'error', 'success', 'notice' ) );
	$notices      = array();

	foreach ( $notice_types as $notice_type ) {
		if ( wc_notice_count( $notice_type ) > 0 && isset( $all_notices[ $notice_type ] ) ) {
			$notices[ $notice_type ] = array();

			// In WooCommerce 3.9+, messages can be an array with two properties:
			// - notice
			// - data
			foreach ( $all_notices[ $notice_type ] as $notice ) {
				$notices[ $notice_type ][] = $notice['notice'] ?? $notice;
			}
		}
	}

	wc_clear_notices();

	return $notices;
}

function cfw_remove_add_to_cart_notice( $product_id, $quantity ) {
	$add_to_cart_notice = wc_add_to_cart_message( array( $product_id => $quantity ), true, true );

	if ( wc_has_notice( $add_to_cart_notice ) ) {
		$notices                  = wc_get_notices();
		$add_to_cart_notice_index = array_search( $add_to_cart_notice, $notices['success'], true );

		unset( $notices['success'][ $add_to_cart_notice_index ] );
		wc_set_notices( $notices );
	}
}
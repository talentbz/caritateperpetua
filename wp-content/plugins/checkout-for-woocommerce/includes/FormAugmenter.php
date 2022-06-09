<?php

namespace Objectiv\Plugins\Checkout;

/**
 * @link checkoutwc.com
 * @since 1.0.0
 * @package Objectiv\Plugins\Checkout\Core
 * @author Brandon Tassone <brandontassone@gmail.com>
 */
class FormAugmenter extends SingletonAbstract {
	/**
	 * @var bool
	 */
	private $debug = false;

	private $priorities = array(
		'first_name' => 10,
		'last_name'  => 20,
		'address_1'  => 30,
		'address_2'  => 40,
		'company'    => 50,
		'country'    => 60,
		'postcode'   => 70,
		'state'      => 80,
		'city'       => 90,
		'phone'      => 100,
	);

	/**
	 * @since 1.1.5
	 * @access private
	 * @var string Is the phone enabled in the settings?
	 */
	private $phone_enabled;

	public function init() {
		$this->phone_enabled = cfw_is_phone_fields_enabled();

		// Setup address field defaults
		add_filter( 'woocommerce_default_address_fields', array( $this, 'get_custom_default_address_fields' ), 100000, 1 );
		add_filter( 'woocommerce_get_country_locale', array( $this, 'enforce_field_priorities' ), 100000, 1 );
		add_filter( 'woocommerce_get_country_locale', array( $this, 'sync_label_and_placeholder' ), 100000, 1 );

		// Fix billing email field
		add_filter( 'woocommerce_billing_fields', array( $this, 'update_billing_email_field' ), 100000 );

		// Handle fields injected into billing / shipping fields
		add_filter( 'woocommerce_billing_fields', array( $this, 'handle_injected_fields' ), 100000 );
		add_filter( 'woocommerce_shipping_fields', array( $this, 'handle_injected_fields' ), 100000 );
		add_filter( 'woocommerce_checkout_fields', array( $this, 'handle_injected_order_fields' ), 100000 );

		// Calculate rows
		if ( is_cfw_page() ) {
			add_filter( 'woocommerce_checkout_fields', array( $this, 'calculate_rows' ), 200000 ); // seriously, run this last
		}

		if ( $this->phone_enabled ) {
			add_action( 'woocommerce_checkout_create_order', array( $this, 'update_shipping_phone_on_order_create' ), 10, 2 );
			add_filter( 'woocommerce_billing_fields', array( $this, 'add_billing_phone_to_address_fields' ), 10, 1 );
		}

		// Run all fields through our function instead
		add_filter( 'woocommerce_form_field', array( $this, 'cfw_form_field' ), 100, 4 );

		/**
		 * Filter address field priorities
		 *
		 * @since 2.0.0
		 *
		 * @param array $priorities The address field priorities keyed by field key
		 */
		$this->priorities = apply_filters( 'cfw_address_field_priorities', $this->priorities );
	}

	/**
	 * @since 1.1.5
	 * @param $address_fields
	 * @param $country
	 *
	 * @return mixed
	 */
	public function add_billing_phone_to_address_fields( $address_fields ) {
		$fields = WC()->countries->get_default_address_fields();

		if ( ! empty( $fields['phone'] ) ) {
			$address_fields['billing_phone'] = $fields['phone'];
		}

		return $address_fields;
	}

	/**
	 * @since 1.1.5
	 * @param $order
	 * @param $data
	 */
	public function update_shipping_phone_on_order_create( $order, $data ) {
		if ( ! empty( $_POST['shipping_phone'] ) ) {
			$order->update_meta_data( '_shipping_phone', sanitize_text_field( $_POST['shipping_phone'] ) );
		}
	}

	/**
	 * @param $fields
	 * @return array
	 */
	public function get_custom_default_address_fields( $fields ) {
		// First Name
		$fields['first_name']['placeholder']       = $fields['first_name']['label'];
		$fields['first_name']['class']             = array();
		$fields['first_name']['autocomplete']      = 'given-name';
		$fields['first_name']['input_class']       = array( 'garlic-auto-save' );
		$fields['first_name']['priority']          = $this->priorities['first_name'];
		$fields['first_name']['columns']           = 6;
		$fields['first_name']['custom_attributes'] = array(
			'data-parsley-trigger' => 'change focusout',
		);

		// Last Name
		$fields['last_name']['placeholder']       = $fields['last_name']['label'];
		$fields['last_name']['class']             = array();
		$fields['last_name']['autocomplete']      = 'family-name';
		$fields['last_name']['input_class']       = array( 'garlic-auto-save' );
		$fields['last_name']['priority']          = $this->priorities['last_name'];
		$fields['last_name']['columns']           = 6;
		$fields['last_name']['custom_attributes'] = array(
			'data-parsley-trigger' => 'change focusout',
		);

		// Address 1
		$fields['address_1']['placeholder']       = $fields['address_1']['label'];
		$fields['address_1']['class']             = array( 'address-field' );
		$fields['address_1']['autocomplete']      = 'address-line1';
		$fields['address_1']['input_class']       = array( 'garlic-auto-save' );
		$fields['address_1']['priority']          = $this->priorities['address_1'];
		$fields['address_1']['columns']           = 12;
		$fields['address_1']['custom_attributes'] = array(
			'data-parsley-trigger' => 'change focusout',
		);

		// Address 2
		if ( isset( $fields['address_2'] ) ) {
			$fields['address_2']['label']        = cfw__( 'Apartment, suite, unit, etc.', 'woocommerce' );
			$fields['address_2']['label_class']  = '';
			$fields['address_2']['placeholder']  = $fields['address_2']['label'];
			$fields['address_2']['class']        = array( 'address-field' );
			$fields['address_2']['autocomplete'] = 'address-line2';
			$fields['address_2']['input_class']  = array( 'garlic-auto-save' );
			$fields['address_2']['priority']     = $this->priorities['address_2'];
			$fields['address_2']['columns']      = 12;
		}

		// Company
		if ( isset( $fields['company'] ) ) {
			$fields['company']['placeholder']  = $fields['company']['label'];
			$fields['company']['class']        = array();
			$fields['company']['autocomplete'] = 'organization';
			$fields['company']['input_class']  = array( 'garlic-auto-save', 'update_totals_on_change' );
			$fields['company']['priority']     = $this->priorities['company'];
			$fields['company']['columns']      = 12;
		}

		// Country
		$fields['country']['type']         = 'country';
		$fields['country']['class']        = array( 'address-field', 'update_totals_on_change' );
		$fields['country']['autocomplete'] = 'country';
		$fields['country']['input_class']  = array( 'garlic-auto-save' );
		$fields['country']['priority']     = 60;
		$fields['country']['columns']      = 4;

		// Postcode
		$fields['postcode']['placeholder']       = $fields['postcode']['label'];
		$fields['postcode']['class']             = array( 'address-field' );
		$fields['postcode']['validate']          = array( 'postcode' );
		$fields['postcode']['autocomplete']      = 'postal-code';
		$fields['postcode']['input_class']       = array( 'garlic-auto-save' );
		$fields['postcode']['priority']          = $this->priorities['postcode'];
		$fields['postcode']['columns']           = 4;
		$fields['postcode']['custom_attributes'] = array(
			'data-parsley-length'  => '[2,12]',
			'data-parsley-trigger' => 'keyup change focusout',
		);

		// State
		$fields['state']['type']              = 'state';
		$fields['state']['placeholder']       = $fields['state']['label'];
		$fields['state']['class']             = array( 'address-field' );
		$fields['state']['validate']          = array( 'state' );
		$fields['state']['autocomplete']      = 'address-level1';
		$fields['state']['priority']          = $this->priorities['state'];
		$fields['state']['columns']           = 4;
		$fields['state']['input_class']       = array( 'garlic-auto-save' );
		$fields['state']['custom_attributes'] = array(
			'data-parsley-trigger' => 'keyup change focusout',
		);

		// City
		$fields['city']['placeholder']       = $fields['city']['label'];
		$fields['city']['class']             = array( 'address-field' );
		$fields['city']['autocomplete']      = 'address-level2';
		$fields['city']['input_class']       = array( 'garlic-auto-save' );
		$fields['city']['priority']          = $this->priorities['city'];
		$fields['city']['columns']           = 12;
		$fields['city']['custom_attributes'] = array(
			'data-parsley-trigger' => 'change focusout',
		);

		// Phone
		if ( $this->phone_enabled ) {
			$fields['phone'] = array(
				'type'              => 'tel',
				'label'             => cfw__( 'Phone', 'woocommerce' ),
				'placeholder'       => cfw__( 'Phone', 'woocommerce' ),
				'required'          => 'required' === get_option( 'woocommerce_checkout_phone_field', 'required' ),
				'autocomplete'      => 'tel',
				'input_class'       => array( 'garlic-auto-save' ),
				'priority'          => $this->priorities['phone'],
				'columns'           => 12,
				'validate'          => array( 'phone' ),
				'custom_attributes' => array(
					'data-parsley-trigger' => 'keyup change focusout',
				),
			);
		}

		return $fields;
	}

	public function update_billing_email_field( $billing_fields ) {
		if ( ! empty( $billing_fields['billing_email'] ) ) {
			$billing_fields['billing_email']['custom_attributes']['data-parsley-group'] = 'account';
			$billing_fields['billing_email']['custom_attributes']['data-parsley-type']  = 'email';
		}

		return $billing_fields;
	}

	public function handle_injected_fields( $checkout_fields ) {
		foreach ( $checkout_fields as $field_key => $field ) {

			if ( isset( $field['class'] ) && is_array( $field['class'] ) ) {

				// Swap form-row classes with equivalent column classes
				if ( in_array( 'form-row-first', $field['class'], true ) || in_array( 'form-row-last', $field['class'], true ) ) {
					$field['columns'] = 6;
				} elseif ( in_array( 'form-row-wide', $field['class'], true ) ) {
					$field['columns'] = 12;
				} elseif ( in_array( 'cfw-col-3', $field['class'], true ) ) {  // For Checkout Field Editor
					$field['columns'] = 3;
				} elseif ( in_array( 'cfw-col-4', $field['class'], true ) ) {  // For Checkout Field Editor
					$field['columns'] = 4;
				} elseif ( in_array( 'cfw-col-8', $field['class'], true ) ) {  // For Checkout Field Editor
					$field['columns'] = 8;
				} elseif ( in_array( 'cfw-col-9', $field['class'], true ) ) {  // For Checkout Field Editor
					$field['columns'] = 9;
				} elseif ( ! isset( $field['columns'] ) ) {
					$field['columns'] = 12;
				}

				// Remove form-row classes
				$classes_to_remove = array( 'form-row-first', 'form-row-last', 'form-row-wide' );

				foreach ( $classes_to_remove as $class ) {
					if ( ( $class_key = array_search( $class, $field['class'], true ) ) !== false ) { // phpcs:ignore
						unset( $field['class'][ $class_key ] );
					}
				}
			}

			if ( ! isset( $field['input_class'] ) ) {
				$field['input_class'] = array();
			}

			if ( is_string( $field['input_class'] ) ) {
				$field['input_class'] = $this->convert_string_to_array( $field['input_class'] );
			}

			if ( ! in_array( 'garlic-auto-save', $field['input_class'], true ) ) {
				$field['input_class'][] = 'garlic-auto-save';
			}

			if ( ! isset( $field['custom_attributes'] ) || ! is_array( $field['custom_attributes'] ) ) {
				$field['custom_attributes'] = array();
			}

			if ( ! in_array( 'data-parsley-trigger', $field['custom_attributes'], true ) ) {
				$field['custom_attributes']['data-parsley-trigger'] = 'keyup change focusout';
			}

			$checkout_fields[ $field_key ] = $field;
		}

		return $checkout_fields;
	}

	public function handle_injected_order_fields( $fields ) {
		if ( ! empty( $fields['order'] ) ) {
			$fields['order'] = $this->handle_injected_fields( $fields['order'] );
		}

		return $fields;
	}

	public function convert_string_to_array( $value ) {
		if ( is_string( $value ) ) {
			$value = array( $value );
		} elseif ( empty( $value ) ) {
			$value = array();
		}

		return $value;
	}

	public function calculate_wrap( $field, $start_end = null ) {
		if ( empty( $field['wrap'] ) ) {
			// Convert to field types for wrap
			if ( ! empty( $field['type'] ) && in_array( $field['type'], array( 'state', 'country' ), true ) ) {
				$wrap_type = 'select';
			} elseif ( empty( $field['type'] ) ) {
				$wrap_type = 'text';
			} else {
				$wrap_type = $field['type'];
			}

			if ( ! isset( $field['columns'] ) ) {
				$field['columns'] = 12;
			}

			if ( ! isset( $field['priority'] ) ) {
				$field['priority'] = 1000; // don't assume high priority (lower number)
			}

			// Add our wrap
			$field['wrap'] = $this->input_wrap( $wrap_type, $field['columns'], $field['priority'] );

			/**
			 * If neither start or end are set and $start_end is a boolean value,
			 * init both values to passed in $start_end value
			 */
			if ( is_bool( $start_end ) ) {
				if ( ! isset( $field['start'] ) ) {
					$field['start'] = $start_end;
				}

				if ( ! isset( $field['end'] ) ) {
					$field['end'] = $start_end;
				}
			}
		}

		return $field;
	}

	public function calculate_rows( $fieldsets ) {

		foreach ( $fieldsets as $fieldset => $fields ) {
			// Native WooCommerce sort
			if ( function_exists( 'wc_checkout_fields_uasort_comparison' ) ) {
				uasort( $fields, 'wc_checkout_fields_uasort_comparison' );
			}

			$start              = true;
			$summed_column_size = 0;
			$max_size           = 12;
			$previous_index     = false;
			$field_keys         = array_keys( $fields );
			$last_index         = array_pop( $field_keys );

			foreach ( $fields as $index => $field ) {
				// Clean up our state
				unset( $fields[ $index ]['start'] );
				unset( $fields[ $index ]['end'] );
				unset( $fields[ $index ]['wrap'] );

				// Set our wrap
				$fields[ $index ] = $this->calculate_wrap( $field );

				if ( $this->debug ) {
					$fields[ $index ]['label']       = $fields[ $index ]['label'] . ': ' . $summed_column_size;
					$fields[ $index ]['placeholder'] = $fields[ $index ]['placeholder'] . ': ' . $summed_column_size;
				}

				// If we flagged this field in the last loop iteration to be
				// the start of a row, or we are on the first iteration, set start to true
				if ( true === $start ) {
					$fields[ $index ]['start'] = true;

					// Make sure the last field was an end, if this is a start
					if ( false !== $previous_index ) {
						$fields[ $previous_index ]['end'] = true;
					}

					// Set start to null
					$start = null;
				}

				/**
				 * If the field is the max possible size, it should be the start and end of the row
				 *
				 * OR if the summed column size + this field is over the max size, set to start of row
				 * and set last item to end of row
				 *
				 * OR if summed column size + this field is under the max size, set end to false
				 */
				if ( isset( $fields[ $index ]['columns'] ) && $fields[ $index ]['columns'] === $max_size ) {
					$fields[ $index ]['start'] = true;
					$fields[ $index ]['end']   = true;

					// Next field should be start of row
					$start = true;

					// Since this is the start, last field should be the end
					if ( false !== $previous_index ) {
						$fields[ $previous_index ]['end'] = true;
					}
				} elseif ( isset( $fields[ $index ]['columns'] ) && $summed_column_size + $fields[ $index ]['columns'] > $max_size ) {
					$fields[ $index ]['start'] = true;

					// Since this is the start, last field should be the end
					if ( false !== $previous_index ) {
						$fields[ $previous_index ]['end'] = true;
					}

					// Reset size counter
					$summed_column_size = 0;
				} elseif ( isset( $fields[ $index ]['columns'] ) && $summed_column_size + $fields[ $index ]['columns'] < $max_size ) {
					if ( $this->debug ) {
						var_dump( $index . ' ' . $summed_column_size . ' ' . $field['columns'] );
					}

					// Add to summed size
					$summed_column_size = $summed_column_size + $field['columns'];

					// Not the end, so set to false
					$fields[ $index ]['end'] = false;
				} elseif ( isset( $fields[ $index ]['columns'] ) && $summed_column_size + $fields[ $index ]['columns'] === $max_size ) {
					// Reset summed size to 0
					$summed_column_size = 0;

					// This is the end
					$fields[ $index ]['end'] = true;

					// So the next field is logically the beginning
					$start = true;
				}

				/**
				 * Detect when a field is the last field and set it to the end
				 */
				if ( $index === $last_index ) {
					$fields[ $index ]['end'] = true;
				}

				/**
				 * If for some reason neither start or end are set above,
				 * Init both values to false
				 */
				if ( ! isset( $fields[ $index ]['start'] ) ) {
					$fields[ $index ]['start'] = false;
				}

				if ( ! isset( $fields[ $index ]['end'] ) ) {
					$fields[ $index ]['end'] = false;
				}

				// Store this index so we can use it for backwards lookups later
				$previous_index = $index;
			}

			$fieldsets[ $fieldset ] = $fields;
		}

		return $fieldsets;
	}

	/**
	 * @since 1.0.0
	 * @access public
	 * @param $type
	 * @param $col
	 * @param $priority
	 * @return object
	 */
	public function input_wrap( $type, $col, $priority ) {

		if ( 'hidden' === $type ) {
			return (object) array(
				'start' => '',
				'end'   => '',
			);
		}

		switch ( $type ) {

			case 'number':
				$inner_start = '<div class="cfw-input-wrap cfw-number-input">';
				$inner_end   = '</div>';
				break;
			case 'password':
				$inner_start = '<div class="cfw-input-wrap cfw-password-input">';
				$inner_end   = '</div>';
				break;
			case 'select':
			case 'multiselect':
				$inner_start = '<div class="cfw-input-wrap cfw-select-input">';
				$inner_end   = '</div>';
				break;
			case 'textarea':
				$inner_start = '<div class="cfw-input-wrap cfw-textarea-input">';
				$inner_end   = '</div>';
				break;
			case 'tel':
				$inner_start = '<div class="cfw-input-wrap cfw-tel-input">';
				$inner_end   = '</div>';
				break;
			case 'checkbox':
			case 'radio':
				$inner_start = '<div class="cfw-input-wrap cfw-check-input">';
				$inner_end   = '</div>';
				break;
			default:
				$inner_start = '<div class="cfw-input-wrap cfw-text-input">';
				$inner_end   = '</div>';
				break;
		}

		$priority = esc_attr( $priority );

		$start = '<div class="col-lg-' . $col . ' %3$s" id="%1$s" data-priority="' . $priority . '">' . $inner_start . '%2$s';

		$end = "$inner_end</div>";

		/**
		 * Filters input wrap start including column and field type wrapping html
		 *
		 * @since 2.0.0
		 *
		 * @param string $start The input wrap start
		 */
		$start = apply_filters( 'cfw_input_wrap_start', $start, $type, $col, $priority );

		/**
		 * Filters input wrap end wrapping html
		 *
		 * @since 2.0.0
		 *
		 * @param string $end The input wrap end
		 */
		$end = apply_filters( 'cfw_input_wrap_end', $end, $type, $col, $priority );

		return (object) array(
			'start' => $start,
			'end'   => $end,
		);
	}

	public function cfw_form_field( $field, $key, $args, $value ) {
		if ( ! is_cfw_page() ) {
			return $field;
		}

		$args['return']      = true;
		$args['placeholder'] = empty( $args['placeholder'] ) ? $args['label'] : $args['placeholder'];

		if ( ! isset( $args['start'] ) ) {
			$args['start'] = true;
		}

		if ( ! isset( $args['end'] ) ) {
			$args['end'] = true;
		}

		if ( ! isset( $args['columns'] ) ) {
			$args['columns'] = 12;
		}

		if ( is_string( $args['input_class'] ) ) {
			$args['input_class'] = array( $args['input_class'] );
		}

		$args['input_class'][] = 'garlic-auto-save';

		/**
		 * Handle form-row-first/last classes
		 */
		if ( in_array( 'form-row-first', $args['class'], true ) || in_array( 'form-row-last', $args['class'], true ) ) {
			$args['columns'] = 6;
		}

		if ( in_array( 'form-row-first', $args['class'], true ) ) {
			$args['start'] = true;
			$args['end']   = false;
		}

		if ( in_array( 'form-row-last', $args['class'], true ) ) {
			$args['start'] = false;
			$args['end']   = true;
		}

		$classes_to_remove = array( 'form-row-first', 'form-row-last', 'form-row-wide', 'form-row' );

		foreach ( $classes_to_remove as $class ) {
			if ( ( $class_key = array_search( $class, $args['class'], true ) ) !== false ) {
				unset( $args['class'][ $class_key ] );
			}
		}

		if ( empty( $value ) && ! empty( $_POST[ $key ] ) ) {
			$value = $_POST[ $key ];
		}

		return cfw_form_field( $key ?? '', $args, $value );
	}

	/**
	 * @param array $locales
	 * @return array
	 */
	public function enforce_field_priorities( array $locales ): array {
		foreach ( $locales as $country => $locale ) {
			foreach ( $locale as $field_key => $field_data ) {
				if ( isset( $field_data['priority'] ) ) {
					$locales[ $country ][ $field_key ]['priority'] = $this->priorities[ $field_key ];
				}
			}
		}

		return $locales;
	}

	public function sync_label_and_placeholder( array $locales ): array {
		foreach ( $locales as $country => $locale ) {
			foreach ( $locale as $field_key => $field_data ) {
				if ( isset( $field_data['label'] ) ) {
					$locales[ $country ][ $field_key ]['placeholder'] = $field_data['label'];
				}
			}
		}

		return $locales;
	}
}

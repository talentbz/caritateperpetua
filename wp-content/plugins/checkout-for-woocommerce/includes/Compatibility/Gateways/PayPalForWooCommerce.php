<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Gateways;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class PayPalForWooCommerce extends CompatibilityAbstract {
	public function is_available(): bool {
		return class_exists( '\\Angelleye_PayPal_Express_Checkout_Helper' );
	}

	public function typescript_class_and_params( array $compatibility ): array {
		$compatibility[] = array(
			'class'  => 'PayPalForWooCommerce',
			'params' => array(),
		);

		return $compatibility;
	}

	public function run() {
		if ( version_compare( VERSION_PFW, '1.5.7', '>=' ) ) {
			$angelleye_paypal_express_checkout_helper = \Angelleye_PayPal_Express_Checkout_Helper::instance();

			if ( 'no' === $angelleye_paypal_express_checkout_helper->enabled ) {
				return;
			}

			add_filter( 'angelleye_ec_checkout_page_buy_now_nutton', array( $this, 'modify_payment_button_output' ), 10, 1 );

			if ( ! empty( $angelleye_paypal_express_checkout_helper ) && ! empty( $angelleye_paypal_express_checkout_helper->show_on_checkout ) && ( 'top' === $angelleye_paypal_express_checkout_helper->show_on_checkout || 'both' === $angelleye_paypal_express_checkout_helper->show_on_checkout ) ) {
				add_action( 'cfw_payment_request_buttons', array( $this, 'add_paypal_express_to_checkout' ) );
			}

			// Remove top of checkout message
			remove_action( 'woocommerce_before_checkout_form', array( $angelleye_paypal_express_checkout_helper, 'checkout_message' ), 5 );

			if ( $angelleye_paypal_express_checkout_helper->function_helper->ec_is_express_checkout() ) {
				do_action( 'cfw_angelleye_paypal_ec_is_express_checkout' );

				// Restore default login
				add_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_login_form', 10 );

				// Set posted data from PayPal session
				// This normally happens on woocommerce_checkout_billing, which we of course don't call
				\Angelleye_PayPal_Express_Checkout_Helper::instance()->ec_set_checkout_post_data();

				wc_maybe_define_constant( 'CFW_PAYMENT_BUTTON_SEPARATOR', true );

				// Hide customer info tab
				add_filter( 'cfw_show_customer_information_tab', '__return_false' );

				// Hide shipping method tab
				add_filter( 'cfw_show_shipping_tab', '__return_false' );

				// Remove Breadcrumbs
				remove_action( 'cfw_checkout_before_order_review', 'cfw_breadcrumb_navigation', 10 );

				// Unhook Customer Information Tab Pieces
				remove_action( 'cfw_checkout_customer_info_tab', 'cfw_payment_request_buttons', 10 );
				remove_action( 'cfw_checkout_customer_info_tab', 'cfw_customer_info_tab_heading', 20 );
				remove_action( 'cfw_checkout_customer_info_tab', 'cfw_customer_info_tab_login', 30 );
				remove_action( 'cfw_checkout_customer_info_tab', 'cfw_customer_info_address', 40 );
				remove_action( 'cfw_checkout_customer_info_tab', 'cfw_customer_info_tab_nav', 50 );

				// Unhook Shipping Method Tab pieces
				remove_action( 'cfw_checkout_shipping_method_tab', 'cfw_shipping_method_address_review_pane', 10 );
				remove_action( 'cfw_checkout_shipping_method_tab', 'cfw_shipping_methods', 20 );
				remove_action( 'cfw_checkout_shipping_method_tab', 'cfw_shipping_method_tab_nav', 30 );

				// Remove Billing Address from payment tab
				remove_action( 'cfw_checkout_payment_method_tab', 'cfw_payment_tab_content_billing_address', 20 );

				/**
				 * Now set back up the payment tab the way we want it.
				 */

				// Add heading
				add_action(
					'cfw_checkout_payment_method_tab',
					function() use ( $angelleye_paypal_express_checkout_helper ) {
						echo '<h1>' . $angelleye_paypal_express_checkout_helper->review_title_page . '</h1>';
					},
					5
				);

				// Remove extra heading
				remove_all_actions( 'cfw_checkout_before_shipping_address' );
				remove_all_actions( 'cfw_checkout_before_billing_address' );

				// Add shipping address
				add_action( 'cfw_checkout_payment_method_tab', 'cfw_customer_info_address', 6 );

				if ( WC()->cart->needs_shipping() ) {
					// Shipping methods
					add_action(
						'cfw_checkout_payment_method_tab',
						function() {
							cfw_shipping_methods();
						},
						7
					);
				}

				add_action(
					'cfw_checkout_payment_method_tab',
					function() {
						if ( ! WC()->cart->needs_shipping() ) {
							return;
						}
						?>
					<h3>
						<?php
						/**
						 * Filters billing address heading
						 *
						 * @param string $billing_address_heading Billing address heading
						 * @since 2.0.0
						 */
						echo apply_filters( 'cfw_billing_address_heading', esc_html__( 'Billing address', 'checkout-wc' ) );
						?>
					</h3>
						<?php
					},
					8
				);

				add_action( 'cfw_checkout_payment_method_tab', array( $angelleye_paypal_express_checkout_helper, 'ec_formatted_billing_address' ), 9 );

				add_action(
					'cfw_checkout_payment_method_tab',
					function() {
						?>
						<div id="cfw-billing-fields-container" class="cfw-radio-reveal-content <?php cfw_address_class_wrap( false ); ?>">
							<div class="cfw-input-wrap-row">
								<?php
								$billing_fields = WC()->checkout()->get_checkout_fields( 'billing' );
								$email_field    = $billing_fields['billing_email'];

								$Angelleye_PayPal_Express_Checkout_Helper = \Angelleye_PayPal_Express_Checkout_Helper::instance();
								$shipping_details                         = $Angelleye_PayPal_Express_Checkout_Helper->ec_get_session_data( 'shipping_details' );
								$email                                    = WC()->checkout->get_value( 'billing_email' );

								if ( empty( $email ) ) {
									$email = ! empty( $shipping_details['email'] ) ? $shipping_details['email'] : '';
								}

								cfw_form_field( 'billing_email', $email_field, $email );
								?>
							</div>
							<?php
							if ( WC()->cart->needs_shipping() ) {
								cfw_get_billing_checkout_fields( WC()->checkout() );
							}
							?>

							<div class="cfw-force-hidden">
								<div id="ship-to-different-address">
									<input id="ship-to-different-address-checkbox" type="checkbox" name="ship_to_different_address" value="<?php echo WC()->cart->needs_shipping_address() ? 1 : 0; ?>" checked="checked" />
								</div>
							</div>
						</div>
						<?php
					},
					10
				);

				// Add style overrides
				add_action(
					'cfw_checkout_payment_method_tab',
					function() {
						?>
						<style type="text/css">
							.cfw-review-pane-link a {
								display: none;
							}

							#cfw-breadcrumb {
								display: none !important;
							}

							#cfw-payment-action {
								display: block;
							}

							#cfw-payment-method {
								display: block !important;
								opacity: 1 !important;
							}

							#cfw-place-order {
								display: flex;
								align-items: center;
								justify-content: space-between;
							}

							#cfw-place-order > * {
								width: auto !important;
							}

							.angelleye_cancel {
								float: none !important;
							}
							.angelleye_smart_button_checkout_bottom {
								display: none !important;
							}

							#place_order {
								display: block !important;
							}

							#cfw-shipping-same-billing {
								display: none !important;
							}

							.cfw-return-to-shipping-btn {
								display: none;
							}

							#cfw-billing-methods {
								display: none;
							}

							.secure-notice {
								display: none;
							}
						</style>
						<?php
					},
					100
				);
			}
		}
	}

	public function modify_payment_button_output( $button_output ) {
		$content_strings_to_remove = array(
			'<div style="clear:both; margin-bottom:10px;"></div>',
			'<div class="clear"></div>',
		);

		// Remove unwanted strings
		foreach ( $content_strings_to_remove as $content_str ) {
			$button_output = str_replace( $content_str, '', $button_output );
		}

		return $button_output;
	}

	public function add_paypal_express_to_checkout() {
		// This is required because it's used down below in anonymous functions
		global $Angelleye_PayPal_Express_Checkout_Helper;

		if ( cfw_is_checkout() ) {
			$Angelleye_PayPal_Express_Checkout_Helper = \Angelleye_PayPal_Express_Checkout_Helper::instance();

			add_action(
				'cfw_checkout_after_payment_methods',
				function () {
					global $Angelleye_PayPal_Express_Checkout_Helper;

					echo '<p class="paypal-cancel-wrapper">' . $Angelleye_PayPal_Express_Checkout_Helper->angelleye_woocommerce_order_button_html( '' ) . '</p>';
				}
			);

			$Angelleye_PayPal_Express_Checkout_Helper->checkout_message();

			if ( empty( $Angelleye_PayPal_Express_Checkout_Helper ) ) {
				return;
			}

			if ( ! $Angelleye_PayPal_Express_Checkout_Helper->function_helper->ec_is_express_checkout() ) {
				add_action( 'cfw_checkout_customer_info_tab', 'cfw_add_separator', 11 );
			} else {
				add_action( 'cfw_checkout_before_customer_info_tab', array( $this, 'add_notice' ), 10 );
			}
		}
	}

	public function add_notice() {
		?>
		<div class="woocommerce-info">
			<?php _e( 'Logged in with PayPal. Please continue your order below.', 'checkout-wc' ); ?>
		</div>
		<?php
	}

	public function hidden_email_field() {
		ob_start();

		do_action( 'woocommerce_checkout_billing' );

		ob_get_clean();

		$billing_fields = WC()->checkout()->get_checkout_fields( 'billing' );
		$email_field    = $billing_fields['billing_email'];

		$Angelleye_PayPal_Express_Checkout_Helper = \Angelleye_PayPal_Express_Checkout_Helper::instance();
		$shipping_details                         = $Angelleye_PayPal_Express_Checkout_Helper->ec_get_session_data( 'shipping_details' );
		$email                                    = WC()->checkout->get_value( 'billing_email' );

		if ( empty( $email ) ) {
			$email = ! empty( $shipping_details['email'] ) ? $shipping_details['email'] : '';
		}

		echo '<div style="display: none;">';
		cfw_form_field( 'billing_email', $email_field, $email );
		echo '</div>';
	}
}

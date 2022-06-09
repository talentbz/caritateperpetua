<?php

namespace Objectiv\Plugins\Checkout\Admin\Pages;

/**
 * @link checkoutwc.com
 * @since 5.0.0
 * @package Objectiv\Plugins\Checkout\Admin\Pages
 * @author Clifton Griffin <clif@checkoutwc.com>
 */
class CartSummary extends PageAbstract {
	public function __construct() {
		parent::__construct( cfw__( 'Cart Summary', 'checkout-wc' ), 'manage_options', 'cart-summary' );
	}
	public function output() {
		$this->output_form_open();
		?>
		<table class="form-table">
			<tbody>
				<?php
				/**
				 * Fires at the top of the cart summary admin page settings table inside <tbody>
				 *
				 * @since 5.0.0
				 *
				 * @param CartSummary $cart_summary_admin_page The cart summary admin page
				 */
				do_action( 'cfw_cart_summary_before_admin_page_controls', $this );

				$this->output_radio_group_row(
					'cart_item_link',
					cfw__( 'Cart Item Links', 'checkout-wc' ),
					'disabled',
					array(
						'disabled' => cfw__( 'Disabled (Recommended)', 'checkout-wc' ),
						'enabled'  => cfw__( 'Enabled', 'checkout-wc' ),
					),
					array(
						cfw__( 'Disabled: Do not link cart items to single product page. (Recommended)', 'checkout-wc' ),
						cfw__( 'Enabled: Link each cart item to product page.', 'checkout-wc' ),
					)
				);

				$this->output_radio_group_row(
					'cart_item_data_display',
					cfw__( 'Cart Item Data Display', 'checkout-wc' ),
					'short',
					array(
						'short'       => cfw__( 'Short (Recommended)', 'checkout-wc' ),
						'woocommerce' => cfw__( 'WooCommerce Default', 'checkout-wc' ),
					),
					array(
						cfw__( 'Short: Display only variation values. For example, Size: XL, Color: Red is displayed as XL / Red. (Recommended)', 'checkout-wc' ),
						cfw__( 'WooCommerce Default: Each variation is displayed on a separate line using this format: Label: Value', 'checkout-wc' ),
					)
				);

				/**
				 * Fires at the top of the cart summary admin page settings table inside <tbody>
				 *
				 * @since 5.0.0
				 *
				 * @param CartSummary $cart_summary_admin_page The cart summary admin page
				 */
				do_action( 'cfw_cart_summary_after_admin_page_controls', $this );
				?>
			</tbody>
		</table>

		<?php
		/**
		 * Fires before form close on cart summary admin page
		 *
		 * @since 5.0.0
		 *
		 * @param CartSummary $cart_summary_admin_page The cart summary admin page
		 */
		do_action( 'cfw_cart_summary_before_admin_page_form_close', $this );
		$this->output_form_close();
	}
}

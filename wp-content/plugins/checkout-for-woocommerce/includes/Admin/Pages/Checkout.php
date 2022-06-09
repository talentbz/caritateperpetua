<?php

namespace Objectiv\Plugins\Checkout\Admin\Pages;

use Objectiv\Plugins\Checkout\Managers\PlanManager;
use Objectiv\Plugins\Checkout\Managers\SettingsManager;

/**
 * @link checkoutwc.com
 * @since 5.0.0
 * @package Objectiv\Plugins\Checkout\Admin\Pages
 * @author Clifton Griffin <clif@checkoutwc.com>
 */
class Checkout extends PageAbstract {
	protected $google_api_key_settings_page_url;

	/**
	 * Checkout constructor.
	 * @param string $google_api_key_settings_page_url
	 */
	public function __construct( string $google_api_key_settings_page_url ) {
		$this->google_api_key_settings_page_url = $google_api_key_settings_page_url;

		parent::__construct( cfw__( 'Checkout', 'checkout-wc' ), 'manage_options', 'checkout' );
	}

	public function output() {
		$settings                  = SettingsManager::instance();
		$login_style_enable        = ! has_filter( 'cfw_suppress_default_login_form' ) && ! has_filter( 'cfw_enable_enhanced_login' );
		$registration_style_enable = ! has_filter( 'cfw_registration_generate_password' );
		$order_notes_enable        = ! has_filter( 'woocommerce_enable_order_notes_field' ) || ( $settings->get_setting( 'enable_order_notes' ) === 'yes' && 1 === cfw_count_filters( 'woocommerce_enable_order_notes_field' ) );

		$order_notes_notice_replacement_text = '';

		if ( ! $order_notes_enable && defined( 'WC_CHECKOUT_FIELD_EDITOR_VERSION' ) ) {
			$order_notes_notice_replacement_text = cfw__( 'This setting is overridden by WooCommerce Checkout Field Editor.', 'checkout-wc' );
		}

		$this->output_form_open();
		?>

		<table class="form-table">
			<tbody>
			<?php
			$this->output_checkbox_row(
				'enable_order_notes',
				cfw__( 'Order Notes', 'checkout-wc' ),
				cfw__( 'Enable Order Notes field', 'checkout-wc' ),
				cfw__( 'Enable or disable order notes field. Disabled by default.', 'checkout-wc' ),
				$order_notes_enable,
				'',
				false === $order_notes_enable,
				$order_notes_notice_replacement_text
			);

			$this->output_checkbox_row(
				'skip_cart_step',
				cfw__( 'Add to Cart Redirect', 'checkout-wc' ),
				cfw__( 'Skip cart step', 'checkout-wc' ),
				cfw__( 'Enable to skip the cart and redirect customers directly to checkout after adding a product to the cart.', 'checkout-wc' )
			);

			$this->output_checkbox_row(
				'skip_shipping_step',
				cfw__( 'Skip Shipping Step', 'checkout-wc' ),
				cfw__( 'Skip shipping step', 'checkout-wc' ),
				cfw__( 'Enable to hide the shipping method step. Useful if you only have one shipping option for all orders.', 'checkout-wc' )
			);

			$this->output_checkbox_row(
				'enable_coupon_code_link',
				cfw__( 'Hide Coupon Code', 'checkout-wc' ),
				cfw__( 'Enable to hide coupon code until link is clicked.', 'checkout-wc' ),
				cfw__( 'Initially hide coupon code field until "Have a coupon code?" link is clicked.', 'checkout-wc' )
			);

			$this->output_radio_group_row(
				'login_style',
				cfw__( 'Login Style', 'checkout-wc' ),
				'enhanced',
				array(
					'enhanced'    => cfw__( 'Enhanced (Recommended)', 'checkout-wc' ),
					'woocommerce' => cfw__( 'WooCommerce Default', 'checkout-wc' ),
				),
				array(
					cfw__( 'Enhanced: Automatically show and hide login fields depending on whether the entered email address matches an account. (Recommended)', 'checkout-wc' ),
					cfw__( 'WooCommerce Default: Show a login reminder in a banner above the checkout form.', 'checkout-wc' ),
				),
				$login_style_enable,
				'',
				false === $login_style_enable
			);

			$this->output_radio_group_row(
				'registration_style',
				cfw__( 'Registration Style', 'checkout-wc' ),
				'enhanced',
				array(
					'enhanced'    => cfw__( 'Enhanced (Recommended)', 'checkout-wc' ),
					'woocommerce' => cfw__( 'WooCommerce Default', 'checkout-wc' ),
				),
				array(
					cfw__( 'Enhanced: Automatically generate a username and password and email it to the customer using the native WooCommerce functionality. (Recommended)', 'checkout-wc' ),
					cfw__( 'WooCommerce Default: A password field is provided for the customer to select their own password. Not recommended.', 'checkout-wc' ),
				),
				$registration_style_enable,
				'',
				false === $registration_style_enable
			);

			if ( ! PlanManager::has_required_plan( PlanManager::PLUS ) ) {
				$notice = $this->get_upgrade_required_notice( PlanManager::get_english_list_of_required_plans_html( PlanManager::PLUS ) );
			}

			$this->output_radio_group_row(
				'user_matching',
				cfw__( 'User Matching', 'checkout-wc' ),
				'enabled',
				array(
					'enabled'     => cfw__( 'Enabled (Recommended)', 'checkout-wc' ),
					'woocommerce' => cfw__( 'WooCommerce Default', 'checkout-wc' ),
				),
				array(
					cfw__( 'Enabled: Automatically matches guest orders to user accounts on new purchase as well as on registration of a new user. (Recommended)', 'checkout-wc' ),
					cfw__( 'WooCommerce Default: Guest orders will not be linked to matching accounts.', 'checkout-wc' ),
				),
				PlanManager::has_required_plan( PlanManager::PLUS ),
				$notice ?? ''
			);

			/**
			 * Fires at the bottom of the cart summary admin page main settings table inside <tbody>
			 *
			 * @since 5.0.0
			 *
			 * @param Checkout $checkout_admin_page The checkout settings admin page
			 */
			do_action( 'cfw_checkout_after_main_admin_page_controls', $this );
			?>
			</tbody>
		</table>

		<h2><?php cfw_e( 'Mobile Options', 'checkout-wc' ); ?></h2>
		<hr />

		<table class="form-table">
			<tbody>
			<?php
			$this->output_checkbox_row(
				'show_mobile_coupon_field',
				cfw__( 'Mobile Coupon Field', 'checkout-wc' ),
				cfw__( 'Show coupon field above payment options on mobile.', 'checkout-wc' ),
				cfw__( 'Show coupon field above payment gateways on mobile devices. Helps customers find the coupon field without expanding the cart summary.', 'checkout-wc' )
			);

			$this->output_checkbox_row(
				'show_logos_mobile',
				cfw__( 'Show Credit Card Logos', 'checkout-wc' ),
				cfw__( 'Show credit card logos on mobile', 'checkout-wc' ),
				cfw__( 'Show the credit card logos on mobile. Note: Many gateway logos cannot be rendered properly on mobile. It is recommended you test before enabling. Default: Off', 'checkout-wc' )
			);

			$this->output_text_input_row(
				'cart_summary_mobile_label',
				cfw__( 'Cart Summary Mobile Label', 'checkout-wc' ),
				cfw__( 'Example: Show order summary and coupons', 'checkout-wc' ) . '<br/>' . cfw__( 'If left blank, this default will be used: ', 'checkout-wc' ) . cfw__( 'Show order summary', 'checkout-wc' )
			);
			?>
			</tbody>
		</table>
		<?php
		$this->output_form_close();
	}
}

<?php

namespace Objectiv\Plugins\Checkout\Admin\Pages;

use Objectiv\Plugins\Checkout\Managers\PlanManager;
use Objectiv\Plugins\Checkout\Managers\SettingsManager;
use Symfony\Component\Finder\Finder;

/**
 * @link checkoutwc.com
 * @since 5.0.0
 * @package Objectiv\Plugins\Checkout\Admin\Pages
 * @author Clifton Griffin <clif@checkoutwc.com>
 */
class SideCart extends PageAbstract {
	public function __construct() {
		parent::__construct( cfw__( 'Side Cart', 'checkout-wc' ), 'manage_options', 'side-cart' );
	}
	public function output() {
		$this->output_form_open();
		?>
		<table class="form-table">
			<tbody>
			<?php
			if ( ! PlanManager::has_required_plan( PlanManager::PRO ) ) {
				$notice = $this->get_upgrade_required_notice( PlanManager::get_english_list_of_required_plans_html( PlanManager::PRO ) );
			}

			if ( ! empty( $notice ) ) {
				echo $notice;
			}

			$this->output_toggle_checkbox(
				'enable_side_cart',
				cfw__( 'Enable Side Cart', 'checkout-wc' ),
				cfw__( 'Replace your cart page with a beautiful side cart that slides in from the right when items are added to the cart.', 'checkout-wc' )
			);

			$icon_options = array();
			$finder       = new Finder();
			$finder->files()->depth( 0 )->in( CFW_PATH . '/assets/images/cart-icons' )->name( '*.svg' )->sortByName();

			foreach ( $finder as $icon ) {
				$icon_options[ $icon->getFilename() ] = $icon->getContents();
			}

			$this->output_radio_group_row(
				'side_cart_icon',
				'Icon',
				'cart-outline.svg',
				$icon_options
			);

			$icon_bg_color_field_name    = SettingsManager::instance()->get_field_name( 'side_cart_icon_color' );
			$icon_bg_color_saved_value   = SettingsManager::instance()->get_setting( 'side_cart_icon_color' );
			$icon_bg_color_default_value = '#222';
			?>
			<tr>
				<th scope="row" valign="top">
					<label for="<?php echo $icon_bg_color_field_name; ?>">
						<?php cfw_e( 'Side Cart Icon Color', 'checkout-wc' ); ?>
					</label>
				</th>
				<td>
					<input class="cfw-admin-color-picker" type="text" id="side_cart_free_shipping_progress_indicator_color" name="<?php echo $icon_bg_color_field_name; ?>" value="<?php echo empty( $icon_bg_color_saved_value ) ? $icon_bg_color_default_value : $icon_bg_color_saved_value; ?>" data-default-color="<?php echo $icon_bg_color_default_value; ?>" />
				</td>
			</tr>
			<?php

			$this->output_number_input_row(
				'side_cart_icon_width',
				cfw__( 'Icon Width', 'checkout-wc' ),
				cfw__( 'The width of the icon in pixels. Default: 34', 'checkout-wc' )
			);

			$this->output_checkbox_row(
				'enable_floating_cart_button',
				cfw__( 'Enable Floating Cart Button', 'checkout-wc' ),
				cfw__( 'Enable floating cart button on the bottom right of pages.', 'checkout-wc' ),
				cfw__( 'For a custom button use this CSS class: <code>cfw-side-cart-open-trigger</code>', 'checkout-wc' )
			);

			$this->output_number_input_row(
				'floating_cart_button_right_position',
				cfw__( 'Floating Cart Button Right Position', 'checkout-wc' ),
				cfw__( 'The position from the right side of the screen in pixels. Default: 20', 'checkout-wc' )
			);

			$this->output_number_input_row(
				'floating_cart_button_bottom_position',
				cfw__( 'Floating Cart Button Bottom Position', 'checkout-wc' ),
				cfw__( 'The position from the bottom of the screen in pixels. Default: 20', 'checkout-wc' )
			);

			$this->output_checkbox_row(
				'hide_floating_cart_button_empty_cart',
				cfw__( 'Hide Button If Empty Cart', 'checkout-wc' ),
				cfw__( 'Hide floating cart button if cart is empty.', 'checkout-wc' )
			);

			$this->output_checkbox_row(
				'enable_ajax_add_to_cart',
				cfw__( 'Enable AJAX Add to Cart', 'checkout-wc' ),
				cfw__( 'Use AJAX on archive and single product pages to add items to cart.', 'checkout-wc' ),
				cfw__( 'By default, WooCommerce requires a full form submit with page reload. Enabling this option uses AJAX to add items to the cart.', 'checkout-wc' )
			);

			$this->output_checkbox_row(
				'enable_order_bumps_on_side_cart',
				cfw__( 'Enable Order Bumps', 'checkout-wc' ),
				cfw__( 'Enable order bumps that are set to display below cart items to appear in side cart.', 'checkout-wc' )
			);

			$this->output_checkbox_row(
				'enable_promo_codes_on_side_cart',
				cfw__( 'Enable Coupons', 'checkout-wc' ),
				cfw__( 'Enable customers to apply coupons from the side cart.', 'checkout-wc' )
			);

			$this->output_checkbox_row(
				'enable_free_shipping_progress_bar',
				cfw__( 'Enable Free Shipping Progress Bar', 'checkout-wc' ),
				cfw__( 'Enable Free Shipping progress bar to show customers how close they are to obtaining free shipping.', 'checkout-wc' ),
				cfw__( 'Uses your shipping settings to determine limits. To override, specify amount below.', 'checkout-wc' )
			);

			$this->output_text_input_row(
				'side_cart_free_shipping_threshold',
				cfw__( 'Free Shipping Threshold', 'checkout-wc' ),
				cfw__( 'Cart subtotal required to qualify for free shipping. To use automatic detection based on shipping configuration, leave blank.', 'checkout-wc' )
			);

			$this->output_text_input_row(
				'side_cart_amount_remaining_message',
				cfw__( 'Amount Remaining Message', 'checkout-wc' ),
				cfw__( 'The amount remaining to qualify for free shipping message. Leave blank for default. Default: You\'re %s away from free shipping!', 'checkout-wc' )
			);

			$this->output_text_input_row(
				'side_cart_free_shipping_message',
				cfw__( 'Free Shipping Message', 'checkout-wc' ),
				cfw__( 'The free shipping message. Leave blank for default. Default: Congrats! You get free standard shipping.', 'checkout-wc' )
			);

			$progress_indicator_color_field_name    = SettingsManager::instance()->get_field_name( 'side_cart_free_shipping_progress_indicator_color' );
			$progress_indicator_color_saved_value   = SettingsManager::instance()->get_setting( 'side_cart_free_shipping_progress_indicator_color' );
			$progress_indicator_color_default_value = cfw_get_active_template()->get_default_setting( 'button_color' );
			?>
			<tr>
				<th scope="row" valign="top">
					<label for="<?php echo $progress_indicator_color_field_name; ?>">
						<?php cfw_e( 'Side Cart Free Shipping Progress Indicator Color', 'checkout-wc' ); ?>
					</label>
				</th>
				<td>
					<input class="cfw-admin-color-picker" type="text" id="side_cart_free_shipping_progress_indicator_color" name="<?php echo $progress_indicator_color_field_name; ?>" value="<?php echo empty( $progress_indicator_color_saved_value ) ? $progress_indicator_color_default_value : $progress_indicator_color_saved_value; ?>" data-default-color="<?php echo $progress_indicator_color_default_value; ?>" />
				</td>
			</tr>
			<?php
			$progress_bg_color_field_name    = SettingsManager::instance()->get_field_name( 'side_cart_free_shipping_progress_bg_color' );
			$progress_bg_color_saved_value   = SettingsManager::instance()->get_setting( 'side_cart_free_shipping_progress_bg_color' );
			$progress_bg_color_default_value = '#f5f5f5';
			?>
			<tr>
				<th scope="row" valign="top">
					<label for="<?php echo $progress_bg_color_field_name; ?>">
						<?php cfw_e( 'Side Cart Free Shipping Progress Background Color', 'checkout-wc' ); ?>
					</label>
				</th>
				<td>
					<input class="cfw-admin-color-picker" type="text" id="side_cart_free_shipping_progress_indicator_color" name="<?php echo $progress_bg_color_field_name; ?>" value="<?php echo empty( $progress_bg_color_saved_value ) ? $progress_bg_color_default_value : $progress_bg_color_saved_value; ?>" data-default-color="<?php echo $progress_bg_color_default_value; ?>" />
				</td>
			</tr>

			</tbody>
		</table>
		<?php
		$this->output_form_close();
	}
}

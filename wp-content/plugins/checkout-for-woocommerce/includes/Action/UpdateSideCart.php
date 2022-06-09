<?php

namespace Objectiv\Plugins\Checkout\Action;

use Objectiv\Plugins\Checkout\Managers\SettingsManager;

/**
 * @link checkoutwc.com
 * @since 5.4.0
 * @package Objectiv\Plugins\Checkout\Action
 * @author Clifton Griffin <clif@objectiv.co>
 */
class UpdateSideCart extends CFWAction {

	public function __construct() {
		parent::__construct( 'update_side_cart', false );
	}


	public function action() {
		check_ajax_referer( 'cfw-update-side-cart', 'security' );

		parse_str( wp_unslash( $_POST['cart_data'] ), $cart_data );

		if ( ! empty( $cart_data['cfw-promo-code'] ) ) {
			WC()->cart->apply_coupon( sanitize_text_field( $cart_data['cfw-promo-code'] ) );
		}

		do_action( 'cfw_before_update_side_cart_action', $cart_data );

		$this->out(
			array(
				'result' => cfw_update_cart( $cart_data['cart'] ?? array() ),
			)
		);
	}
}

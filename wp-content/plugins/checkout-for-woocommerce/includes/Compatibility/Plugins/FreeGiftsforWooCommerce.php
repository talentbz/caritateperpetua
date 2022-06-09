<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

class FreeGiftsforWooCommerce {
	public function init() {
		add_action( 'cfw_cart_updated', array( $this, 'update_cart_gifts' ) );
	}

	public function update_cart_gifts() {
		if ( ! defined( 'FGF_PLUGIN_FILE' ) ) {
			return;
		}

		\FGF_Rule_Handler::reset();
		\FGF_Gift_Products_Handler::automatic_gift_product( false );
		\FGF_Gift_Products_Handler::bogo_gift_product( false );
		\FGF_Gift_Products_Handler::coupon_gift_product( false );
		\FGF_Gift_Products_Handler::remove_gift_products();
	}
}
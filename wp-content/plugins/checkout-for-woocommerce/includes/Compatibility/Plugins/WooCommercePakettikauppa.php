<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class WooCommercePakettikauppa extends CompatibilityAbstract {
	public function is_available(): bool {
		return class_exists( '\\Wc_Pakettikauppa' );
	}

	public function run() {
		$instance = \Wc_Pakettikauppa::get_instance()->frontend;

		remove_filter( 'woocommerce_checkout_fields', array( $instance, 'add_checkout_fields' ) );
	}
}

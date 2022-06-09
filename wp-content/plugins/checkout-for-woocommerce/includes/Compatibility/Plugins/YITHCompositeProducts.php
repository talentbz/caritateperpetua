<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class YITHCompositeProducts extends CompatibilityAbstract {
	public function is_available(): bool {
		return defined( 'YITH_WCP_VERSION' );
	}

	public function run_immediately() {
		add_filter( 'cfw_cart_item_row_class', array( $this, 'add_class_to_composite_items' ), 10, 2 );
	}

	public function add_class_to_composite_items( $classes, $cart_item ): string {
		if ( isset( $cart_item['yith_wcp_child_component_data'] ) ) {
			return $classes . ' yith-composite-product-component';
		}

		return '';
	}
}

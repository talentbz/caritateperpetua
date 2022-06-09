<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class WPWebWooCommerceSocialLogin extends CompatibilityAbstract {
	public function is_available(): bool {
		return defined( 'WOO_SLG_VERSION' );
	}

	public function run() {
		add_action( 'cfw_checkout_customer_info_tab', array( $this, 'maybe_show_social_buttons' ), 29 );
	}

	public function maybe_show_social_buttons() {
		global $woo_slg_options, $woo_slg_render;

		if ( ! empty( $woo_slg_render ) && ! is_user_logged_in() && woo_slg_check_social_enable() && 'no' !== $woo_slg_options['woo_slg_enable_on_checkout_page'] ) {
			$woo_slg_render->woo_slg_social_login();
		}
	}
}

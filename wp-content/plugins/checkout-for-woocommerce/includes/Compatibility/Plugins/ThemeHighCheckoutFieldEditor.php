<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use _WP_Dependency;
use Objectiv\Plugins\Checkout\Admin\Pages\PageAbstract;
use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;
use Objectiv\Plugins\Checkout\Managers\SettingsManager;

class ThemeHighCheckoutFieldEditor extends CompatibilityAbstract {
	public function is_available(): bool {
		return defined( 'THWCFD_VERSION' );
	}

	public function pre_init() {
		add_action( 'cfw_admin_integrations_settings', array( $this, 'admin_integration_settings' ) );
	}

	public function run() {
		add_action( 'wp_enqueue_scripts', array( $this, 'cleanup_select_woo' ), 1000 );

		// Stop modifying address fields
		$hp_billing_fields  = apply_filters( 'thwcfd_billing_fields_priority', 1000 );
		$hp_shipping_fields = apply_filters( 'thwcfd_shipping_fields_priority', 1000 );

		$instance = cfw_get_hook_instance_object( 'woocommerce_billing_fields', 'billing_fields', $hp_billing_fields );

		if ( $instance && SettingsManager::instance()->get_setting( 'allow_thcfe_address_modification' ) !== 'yes' ) {
			remove_filter( 'woocommerce_billing_fields', array( $instance, 'billing_fields' ), $hp_billing_fields );
			remove_filter( 'woocommerce_shipping_fields', array( $instance, 'shipping_fields' ), $hp_shipping_fields );
		}
	}

	public function cleanup_select_woo() {
		$wp_scripts = wp_scripts();

		/** @var _WP_Dependency */
		$wp_scripts->registered['thwcfd-checkout-script']->deps = array( 'jquery' );

		$key = array_search( 'selectWoo', $wp_scripts->queue, true );

		if ( false !== $key ) {
			unset( $wp_scripts->queue[ $key ] );
		}
	}

	/**
	 * @param PageAbstract $integrations
	 */
	public function admin_integration_settings( PageAbstract $integrations ) {
		if ( ! $this->is_available() ) {
			return;
		}

		$settings = SettingsManager::instance();
		?>
		<tr>
			<th scope="row" valign="top">
				<label for="<?php echo $settings->get_field_name( 'allow_thcfe_address_modification' ); ?>"><?php cfw_e( 'ThemeHigh Checkout Field Editor', 'checkout-wc' ); ?></label>
			</th>
			<td>
				<input type="hidden" name="<?php echo $settings->get_field_name( 'allow_thcfe_address_modification' ); ?>" value="no" />
				<label><input type="checkbox" name="<?php echo $settings->get_field_name( 'allow_thcfe_address_modification' ); ?>" id="<?php echo$settings->get_field_name( 'allow_thcfe_address_modification' ); ?>" value="yes" <?php echo $settings->get_setting( 'allow_thcfe_address_modification' ) === 'yes' ? 'checked' : ''; ?> /> <?php cfw_e( 'Enable ThemeHigh Checkout Field Editor address field overrides.', 'checkout-wc' ); ?></label>
				<p><span class="description"><?php cfw_e( 'Allow ThemeHigh Checkout Field Editor to modify billing and shipping address fields. (Not Recommended)', 'checkout-wc' ); ?></span></p>
			</td>
		</tr>
		<?php
	}
}

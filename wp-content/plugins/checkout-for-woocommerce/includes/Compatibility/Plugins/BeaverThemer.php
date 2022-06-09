<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;
use Objectiv\Plugins\Checkout\Admin;
use Objectiv\Plugins\Checkout\Managers\SettingsManager;

class BeaverThemer extends CompatibilityAbstract {
	public function is_available(): bool {
		return class_exists( '\\FLThemeBuilderLayoutRenderer' );
	}

	public function pre_init() {
		add_action( 'cfw_admin_integrations_settings', array( $this, 'admin_integration_setting' ) );
	}

	public function run() {
		if ( SettingsManager::instance()->get_setting( 'enable_beaver_themer_support' ) === 'yes' ) {
			add_action( 'cfw_custom_header', 'FLThemeBuilderLayoutRenderer::render_header' );
			add_action( 'cfw_custom_footer', 'FLThemeBuilderLayoutRenderer::render_footer' );
		}
	}

	/**
	 * @param Admin\Pages\PageAbstract $integrations
	 */
	public function admin_integration_setting( Admin\Pages\PageAbstract $integrations ) {
		if ( ! $this->is_available() ) {
			return;
		}

		$settings = SettingsManager::instance();
		?>
		<tr>
			<th scope="row" valign="top">
				<label for="<?php echo $settings->get_field_name( 'enable_beaver_themer_support' ); ?>"><?php cfw_e( 'Beaver Themer', 'checkout-wc' ); ?></label>
			</th>
			<td>
				<input type="hidden" name="<?php echo $settings->get_field_name( 'enable_beaver_themer_support' ); ?>" value="no" />
				<label><input type="checkbox" name="<?php echo $settings->get_field_name( 'enable_beaver_themer_support' ); ?>" id="<?php echo $settings->get_field_name( 'enable_beaver_themer_support' ); ?>" value="yes" <?php echo $settings->get_setting( 'enable_beaver_themer_support' ) === 'yes' ? 'checked' : ''; ?> /> <?php cfw_e( 'Enable Beaver Themer support.', 'checkout-wc' ); ?></label>
				<p><span class="description"><?php cfw_e( 'Allow Beaver Themer to replace header and footer.', 'checkout-wc' ); ?></span></p>
			</td>
		</tr>
		<?php
	}
}

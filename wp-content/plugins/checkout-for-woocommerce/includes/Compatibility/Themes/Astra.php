<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Themes;

use Objectiv\Plugins\Checkout\Admin\Pages\PageAbstract;
use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;
use Objectiv\Plugins\Checkout\Managers\SettingsManager;

class Astra extends CompatibilityAbstract {
	public function is_available(): bool {
		return defined( 'ASTRA_THEME_VERSION' );
	}

	public function pre_init() {
		add_action( 'cfw_admin_integrations_settings', array( $this, 'admin_integration_setting' ) );
	}

	public function run() {
		add_action( 'cfw_admin_integrations_settings', array( $this, 'admin_integration_setting' ) );
		$this->remove_astra_scripts();

		if ( SettingsManager::instance()->get_setting( 'enable_astra_support' ) === 'yes' ) {
			add_action( 'cfw_custom_header', array( $this, 'astra_header' ) );
			add_action( 'cfw_custom_footer', array( $this, 'astra_footer' ) );

			// Add back Astra's styles
			remove_filter( 'cfw_blocked_style_handles', 'cfw_remove_theme_styles', 10, 1 );

			// Allow Astra Addon to load its assets
			add_filter( 'astra_addon_enqueue_assets', '__return_true', 100 );
		}
	}

	public function run_on_thankyou() {
		$this->run();
	}

	function astra_header() {
		astra_header_before();

		astra_header();

		astra_header_after();
	}

	function astra_footer() {
        astra_footer_before();

        astra_footer();

        astra_footer_after();
    }

	public function remove_astra_scripts() {
		if ( cfw_is_checkout() ) {
			remove_all_actions( 'astra_get_js_files' );
		}
	}

	/**
	 * @param PageAbstract $integrations
	 */
	public function admin_integration_setting( PageAbstract $integrations ) {
		if ( ! $this->is_available() ) {
			return;
		}

		$settings = SettingsManager::instance();
		?>
		<tr>
			<th scope="row" valign="top">
				<label for="<?php echo $settings->get_field_name( 'enable_astra_support' ); ?>"><?php cfw_e( 'Astra', 'checkout-wc' ); ?></label>
			</th>
			<td>
				<input type="hidden" name="<?php echo $settings->get_field_name( 'enable_astra_support' ); ?>" value="no" />
				<label><input type="checkbox" name="<?php echo $settings->get_field_name( 'enable_astra_support' ); ?>" id="<?php echo $settings->get_field_name( 'enable_astra_support' ); ?>" value="yes" <?php echo $settings->get_setting( 'enable_astra_support' ) === 'yes' ? 'checked' : ''; ?> /> <?php cfw_e( 'Enable Astra support. (Beta)', 'checkout-wc' ); ?></label>
				<p><span class="description"><?php cfw_e( 'Allow Astra to replace header and footer. Allows Astra / Astra Addon to load its styles and scripts on the checkout page.', 'checkout-wc' ); ?></span></p>
			</td>
		</tr>
		<?php
	}
}

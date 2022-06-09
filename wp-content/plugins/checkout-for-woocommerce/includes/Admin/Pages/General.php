<?php

namespace Objectiv\Plugins\Checkout\Admin\Pages;

use Objectiv\Plugins\Checkout\Managers\SettingsManager;
use Objectiv\Plugins\Checkout\Managers\UpdatesManager;

/**
 * @link checkoutwc.com
 * @since 5.0.0
 * @package Objectiv\Plugins\Checkout\Admin\Pages
 * @author Clifton Griffin <clif@checkoutwc.com>
 */
class General extends PageAbstract {
	public function __construct() {
		parent::__construct( cfw__( 'General', 'checkout-wc' ), 'manage_options' );
	}

	public function init() {
		parent::init();

		add_action( 'admin_bar_menu', array( $this, 'add_parent_node' ), 100 );
		add_action( 'admin_menu', array( $this, 'setup_main_menu_page' ), $this->priority - 5 );
	}

	public function setup_menu() {
		add_submenu_page( self::$parent_slug, $this->title, $this->title, $this->capability, $this->slug, null, $this->priority );
	}

	public function setup_main_menu_page() {
		add_menu_page( 'CheckoutWC', 'CheckoutWC', 'manage_options', self::$parent_slug, array( $this, 'output_with_wrap' ), 'data:image/svg+xml;base64,' . base64_encode( file_get_contents( CFW_PATH . '/assets/admin/images/icon.svg' ) ) );
	}

	public function output() {
		$settings = SettingsManager::instance();

		?>
		<div class="cfw-no-padding-left">
			<div class="cfw-admin-screen-general-wrap">
				<div class="cfw-admin-screen-general-content">
					<?php
					$this->output_form_open();
					?>
					<table class="form-table">
						<tbody>
						<tr>
							<td colspan="2" class="cfw-toggle-column-wrap">
								<?php
								$this->output_toggle_checkbox(
									'enable',
									cfw__( 'Activate CheckoutWC Templates', 'checkout-wc' ),
									cfw__( 'Requires a valid and active license key. CheckoutWC Templates are always activated for admin users, even without a valid license.', 'checkout-wc' )
								);
								?>
							</td>
						</tr>
						<?php
						UpdatesManager::instance()->admin_page_fields();

						$this->output_text_input_row(
							'google_places_api_key',
							cfw__( 'Google API Key', 'checkout-wc' ),
							cfw__( 'Used by Address Autocomplete and Thank You Page Maps Embed.' ) . '<br/>' . sprintf( '%s <a target="_blank" href="https://developers.google.com/places/web-service/get-api-key">Google Cloud Platform Console</a>.', cfw__( 'Available in the', 'checkout-wc' ) )
						);

						$this->output_checkbox_row(
							'hide_admin_bar_button',
							cfw__( 'Hide Admin Menu Bar Button', 'checkout-wc' ),
							cfw__( 'Hide CheckoutWC admin menu bar button on general pages.', 'checkout-wc' ),
							cfw__( 'Hide the CheckoutWC admin menu bar button unless you are on the checkout page, or one of the checkout endpoints such as thank you and order pay.', 'checkout-wc' )
						);
						?>
						<tr>
							<?php
							/**
							 * This field is a StatCollection concern and should be moved to that class.
							 */
							$tracking_field_name = $settings->get_field_name( 'allow_tracking' );
							$tracking_value      = $settings->get_setting( 'allow_tracking' );
							?>
							<th scope="row" valign="top">
								<label for="<?php echo $tracking_field_name; ?>"><?php cfw_e( 'Enable Usage Tracking', 'checkout-wc' ); ?></label>
							</th>
							<td>
								<input type="hidden" name="<?php echo $tracking_field_name; ?>" value="0" />
								<label for="<?php echo $tracking_field_name; ?>">
									<input type="checkbox" name="<?php echo $tracking_field_name; ?>" id="<?php echo $tracking_field_name; ?>" value="<?php echo md5( trailingslashit( home_url() ) ); ?>" <?php echo md5( trailingslashit( home_url() ) ) === $tracking_value ? 'checked' : ''; ?> />
									<?php cfw_e( 'Allow Checkout for WooCommerce to track plugin usage?', 'checkout-wc' ); ?>
								</label>
							</td>
						</tr>

						<?php do_action( 'cfw_general_admin_page_after_fields' ); ?>
						</tbody>
					</table>
					<?php
					$this->output_form_close();
					?>
				</div>
				<div class="cfw-admin-screen-general-sidebar" class="recommended-plugins">
					<h2>
						<?php cfw_e( 'Recommended Plugins', 'checkout-wc' ); ?>
					</h2>

					<?php
					$plugins   = array();
					$plugins[] = array(
						'slug'        => 'paypal-for-woocommerce',
						'url'         => 'https://www.angelleye.com/product/woocommerce-paypal-plugin/',
						'name'        => 'PayPal for WooCommerce',
						'description' => 'Upgrade the WooCommerce PayPal Gateway options available to your buyers for FREE!',
						'author'      => 'Angell EYE, LLC',
						'image'       => 'https://www.angelleye.com/wp-content/uploads/2014/02/paypal-for-woocommerce-thumbnail.jpg',
					);

					$plugins[] = array(
						'slug'        => 'wp-sent-mail',
						'url'         => 'https://www.wpsentmail.com',
						'name'        => 'WP Sent Mail',
						'description' => 'A sent mail folder for WordPress. View every email your store sends, track opens, and re-send right from the dashboard.',
						'author'      => 'Objectiv',
						'image'       => 'https://www.checkoutwc.com/wp-content/uploads/2019/07/Smaller-Square-WPSM.jpg',
					);
					foreach ( $plugins as $plugin_info ) {
						$this->recommended_plugin_card( $plugin_info );
					}
					?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * @param array $plugin_info
	 */
	public function recommended_plugin_card( array $plugin_info ) {
		?>
		<div class="plugin-card plugin-card-<?php echo $plugin_info['slug']; ?>">
			<div class="plugin-card-top">
				<div class="name column-name">
					<h3>
						<a target="_blank" href="<?php echo $plugin_info['url']; ?>">
							<?php echo $plugin_info['name']; ?> <img src="<?php echo $plugin_info['image']; ?>" class="plugin-icon" alt="">
						</a>
					</h3>
				</div>
				<div class="action-links">
					<ul class="plugin-action-buttons">
						<li>
							<a class="button" target="_blank"  href="<?php echo $plugin_info['url']; ?>" role="button"><?php cfw_e( 'More Info' ); ?></a></li>
						</li>
					</ul>
				</div>
				<div class="desc column-description">
					<p><?php echo $plugin_info['description']; ?></p>
					<p class="authors"> <cite><?php echo sprintf( cfw__( 'By %s' ), $plugin_info['author'] ); ?></cite></p>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * @param \WP_Admin_Bar $admin_bar
	 */
	public function add_parent_node( \WP_Admin_Bar $admin_bar ) {
		if ( ! $this->can_show_admin_bar_button() ) {
			return;
		}

		if ( cfw_is_checkout() ) {
			// Remove irrelevant buttons
			$admin_bar->remove_node( 'new-content' );
			$admin_bar->remove_node( 'updates' );
			$admin_bar->remove_node( 'edit' );
			$admin_bar->remove_node( 'comments' );
		}

		$url = $this->get_url();

		$admin_bar->add_node(
			array(
				'id'     => self::$parent_slug,
				'title'  => '<span class="ab-icon dashicons dashicons-cart"></span>' . cfw__( 'CheckoutWC', 'checkout-wc' ),
				'href'   => $url,
				'parent' => false,
			)
		);
	}

	/**
	 * @param \WP_Admin_Bar $admin_bar
	 */
	public function add_admin_bar_menu_node( \WP_Admin_Bar $admin_bar ) {
		if ( ! apply_filters( 'cfw_do_admin_bar', true ) ) {
			return;
		}

		$admin_bar->add_node(
			array(
				'id'     => $this->slug . '-general',
				'title'  => $this->title,
				'href'   => $this->get_url(),
				'parent' => self::$parent_slug,
			)
		);
	}
}

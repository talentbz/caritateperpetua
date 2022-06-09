<?php

namespace Objectiv\Plugins\Checkout\Admin\Pages;

/**
 * @link checkoutwc.com
 * @since 5.0.0
 * @package Objectiv\Plugins\Checkout\Admin\Pages
 * @author Clifton Griffin <clif@checkoutwc.com>
 */
class Support extends PageAbstract {
	public function __construct() {
		parent::__construct( cfw__( 'Support', 'checkout-wc' ), 'manage_options', 'support' );
	}

	public function output() {
		?>
		<h3><?php cfw_e( 'Before Contacting Support', 'checkout-wc' ); ?></h3>

		<p><?php cfw_e( 'You can find the answer to most questions in our <a href="https://kb.checkoutwc.com" target="_blank">knowledge base</a>.', 'checkout-wc' ); ?></p>
		<p><?php cfw_e( 'Here are some popular guides:', 'checkout-wc' ); ?></p>
		<ul>
			<li><a target="_blank" href="https://kb.checkoutwc.com/article/35-getting-started">Getting Started</a></li>
			<li><a target="_blank" href="https://kb.checkoutwc.com/article/36-troubleshooting">Troubleshooting</a></li>
			<li><a target="_blank" href="https://kb.checkoutwc.com/article/53-upgrading-your-license">Upgrading Your License</a></li>
			<li><a target="_blank" href="https://kb.checkoutwc.com/article/69-how-to-enable-billing-and-shipping-phone-fields">How To Enable Billing and Shipping Phone Fields</a></li>
			<li><a target="_blank" href="https://kb.checkoutwc.com/article/70-how-to-enable-cart-editing">How To Enable Cart Editing</a></li>
			<li><a target="_blank" href="https://kb.checkoutwc.com/article/86-how-to-get-and-configure-your-google-api-key">How To Register and Configure Your Google API Key</a></li>
			<li><a target="_blank" href="https://kb.checkoutwc.com/article/49-how-to-add-a-custom-field">How To Add a Custom Field to Checkout for WooCommerce</a></li>
			<li><a target="_blank" href="https://kb.checkoutwc.com/article/34-how-to-enable-the-woocommerce-notes-field">How to Enable The WooCommerce Notes Field</a></li>
		</ul>

		<h3><?php cfw_e( 'Still Need Help?', 'checkout-wc' ); ?></h3>
		<p><?php cfw_e( 'If you still need help after searching our knowledge base, we would be happy to assist you.', 'checkout-wc' ); ?></p>

		<?php submit_button( cfw__( 'Contact Support', 'checkout-wc' ), 'primary', false, false, array( 'id' => 'checkoutwc-support-button' ) ); ?>

		<script>
			jQuery("#checkoutwc-support-button").on( 'click', function() {
				Beacon("open");
			});
		</script>
		<?php
	}
}

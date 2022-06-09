<?php

namespace Objectiv\Plugins\Checkout\Admin\Pages;

use Objectiv\Plugins\Checkout\Managers\PlanManager;
use Objectiv\Plugins\Checkout\Managers\SettingsManager;

/**
 * @link checkoutwc.com
 * @since 5.0.0
 * @package Objectiv\Plugins\Checkout\Admin\Pages
 * @author Clifton Griffin <clif@checkoutwc.com>
 */
class ThankYou extends PageAbstract {
	public function __construct() {
		parent::__construct( cfw__( 'Thank You', 'checkout-wc' ), 'manage_options', 'thank-you' );
	}

	public function output() {
		$settings                 = SettingsManager::instance();
		$thank_you_order_statuses = false === $settings->get_setting( 'thank_you_order_statuses' ) ? array() : $settings->get_setting( 'thank_you_order_statuses' );

		$this->output_form_open();
		?>
		<table class="form-table">
			<tbody>
				<?php
				if ( ! PlanManager::has_required_plan( PlanManager::PLUS ) ) {
					$notice = $this->get_upgrade_required_notice( PlanManager::get_english_list_of_required_plans_html( PlanManager::PLUS ) );
				}

				$this->output_checkbox_row(
					'enable_thank_you_page',
					cfw__( 'Thank You Page', 'checkout-wc' ),
					cfw__( 'Enable thank you page template.', 'checkout-wc' ),
					cfw__( 'Enable thank you page / order received template.', 'checkout-wc' ),
					PlanManager::has_required_plan( PlanManager::PLUS ),
					$notice ?? ''
				);
				?>
				<!--- Order Statuses -->
				<tr>
					<th scope="row" valign="top">
						<label for="thank_you_order_statuses"><?php cfw_e( 'Order Statuses', 'checkout-wc' ); ?></label>
					</th>
					<td>
						<input type="hidden" name="<?php echo $settings->get_field_name( 'thank_you_order_statuses' ); ?>" value="no" />
						<label>
							<select multiple class="wc-enhanced-select" name="<?php echo $settings->get_field_name( 'thank_you_order_statuses' ); ?>[]" id="thank_you_order_statuses" <?php echo ! PlanManager::has_required_plan( PlanManager::PLUS ) ? 'disabled="disabled"' : ''; ?> >
								<?php if ( is_array( wc_get_order_statuses() ) ) : ?>
									<?php foreach ( wc_get_order_statuses() as $key => $status ) : ?>
										<option value="<?php echo esc_attr( $key ); ?>" <?php echo ( is_array( $thank_you_order_statuses ) && in_array( $key, $thank_you_order_statuses, true ) ) ? 'selected' : ''; ?> >
											<?php echo esc_html( $status ); ?>
										</option>
									<?php endforeach; ?>
								<?php endif; ?>
							</select>
						</label>
						<p><span class="description"><?php cfw_e( 'The statuses to show on the thank you page.', 'checkout-wc' ); ?></span></p>
					</td>
				</tr>
				<?php
				$this->output_checkbox_row(
					'enable_map_embed',
					cfw__( 'Map Embed', 'checkout-wc' ),
					cfw__( 'Enable map embed.', 'checkout-wc' ),
					cfw__( 'Enable or disable map embed on thank you page. Requires Google API key.', 'checkout-wc' ),
					PlanManager::has_required_plan( PlanManager::PLUS )
				);

				$this->output_checkbox_row(
					'override_view_order_template',
					cfw__( 'My Account', 'checkout-wc' ),
					cfw__( 'Use Thank You page template for viewing orders in My Account.', 'checkout-wc' ),
					cfw__( 'When checked, viewing orders in My Account will use the Thank You page template.', 'checkout-wc' ),
					PlanManager::has_required_plan( PlanManager::PLUS )
				);
				?>
			</tbody>
		</table>
		<?php
		$this->output_form_close();
	}
}






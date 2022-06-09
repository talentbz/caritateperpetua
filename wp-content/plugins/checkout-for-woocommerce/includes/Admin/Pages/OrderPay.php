<?php

namespace Objectiv\Plugins\Checkout\Admin\Pages;

use Objectiv\Plugins\Checkout\Managers\PlanManager;

/**
 * @link checkoutwc.com
 * @since 5.0.0
 * @package Objectiv\Plugins\Checkout\Admin\Pages
 * @author Clifton Griffin <clif@checkoutwc.com>
 */
class OrderPay extends PageAbstract {
	public function __construct() {
		parent::__construct( cfw__( 'Order Pay', 'checkout-wc' ), 'manage_options', 'order-pay' );
	}

	public function output() {
		$this->output_form_open();
		?>
		<table class="form-table">
			<tbody>
				<?php
				if ( ! PlanManager::has_required_plan( PlanManager::PLUS ) ) {
					$notice = $this->get_upgrade_required_notice( PlanManager::get_english_list_of_required_plans_html( PlanManager::PLUS ) );
				}
				$this->output_checkbox_row(
					'enable_order_pay',
					cfw__( 'Order Pay Page', 'checkout-wc' ),
					cfw__( 'Enable support for order pay page template.', 'checkout-wc' ),
					cfw__( 'Use checkout template for order pay page.', 'checkout-wc' ),
					PlanManager::has_required_plan( PlanManager::PLUS ),
					$notice ?? ''
				);
				?>
			</tbody>
		</table>
		<?php
		$this->output_form_close();
	}
}

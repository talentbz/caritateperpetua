<?php

namespace Objectiv\Plugins\Checkout\Features;

use Objectiv\Plugins\Checkout\Admin\Pages\PageAbstract;
use Objectiv\Plugins\Checkout\Interfaces\SettingsGetterInterface;
use Objectiv\Plugins\Checkout\Managers\SettingsManager;

/**
 * @link checkoutwc.com
 * @since 5.0.0
 */
class TrustBadges extends FeaturesAbstract {
	protected $trust_badges_field_name;

	/**
	 * TrustBadges constructor.
	 * @param bool $enabled
	 * @param bool $available
	 * @param string $required_plans_list
	 * @param SettingsGetterInterface $settings_getter
	 * @param string $trust_badges_field_name
	 */
	public function __construct( bool $enabled, bool $available, string $required_plans_list, SettingsGetterInterface $settings_getter, string $trust_badges_field_name ) {
		$this->trust_badges_field_name = $trust_badges_field_name;

		parent::__construct( $enabled, $available, $required_plans_list, $settings_getter );
	}

	protected function run_if_cfw_is_enabled() {
		add_action( 'cfw_checkout_cart_summary', array( $this, 'output_trust_badges' ), 71 );
	}

	public function output_trust_badges() {
		$trust_badge_items = $this->settings_getter->get_setting( 'trust_badges' );

		if ( count( $trust_badge_items ) === 0 ) {
			return;
		}
		?>
		<div id="cfw_trust_badges" class="cfw-module">
			<h4><?php echo do_shortcode( $this->settings_getter->get_setting( 'trust_badges_title' ) ); ?></h4>

			<?php
			foreach ( $trust_badge_items as $badge ) :
				$image_only = empty( $badge['title'] ) && empty( $badge['description'] );
				$image      = wp_get_attachment_image( $badge['badge_attachment_id'], $image_only ? 'full' : 'cfw_trust_badge_thumb' );
				?>
				<div class="cfw-badge row">
					<?php if ( ! empty( $image ) ) : ?>
					<div class="<?php echo $image_only ? 'col-12' : 'col-3'; ?>">
						<?php echo $image; ?>
					</div>
					<?php endif; ?>

					<div class="<?php echo empty( $image ) ? 'col-12' : 'col-9'; ?> cfw-small">
						<h5>
							<?php echo do_shortcode( $badge['title'] ); ?>
						</h5>
						<p>
							<?php echo do_shortcode( $badge['description'] ); ?>
						</p>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<?php
	}


	public function init() {
		parent::init();

		add_action( 'cfw_do_plugin_activation', array( $this, 'run_on_plugin_activation' ) );
		add_action( 'cfw_cart_summary_before_admin_page_form_close', array( $this, 'output_admin_page_settings' ), 10, 1 );
	}

	public function output_admin_page_settings( PageAbstract $cart_summary_admin_page ) {
		?>
		<h2><?php cfw_e( 'Trust Badges', 'checkout-wc' ); ?></h2>
		<hr />

		<input type="hidden" name="<?php echo $this->trust_badges_field_name; ?>" value="" />

		<table class="form-table">
			<tbody>
			<?php
			$trust_badge_items = $this->settings_getter->get_setting( 'trust_badges' );

			if ( ! $this->available ) {
				$notice = $cart_summary_admin_page->get_upgrade_required_notice( $this->required_plans_list );
			}

			// Only do the notice here because if it's not available,
			// the rest of the UI doesn't show up and one notice suffices for the whole section
			$cart_summary_admin_page->output_checkbox_row(
				'enable_trust_badges',
				cfw__( 'Enable Trust Badges', 'checkout-wc' ),
				cfw__( 'Enable trust badges on templates.', 'checkout-wc' ),
				cfw__( 'Enable trust badges on CheckoutWC templates. Uncheck to hide badges.', 'checkout-wc' ),
				$this->available,
				$notice ?? ''
			);

			$cart_summary_admin_page->output_text_input_row(
				'trust_badges_title',
				cfw__( 'Introduction', 'checkout-wc' ),
				cfw__( 'Example: Why choose us?', 'checkout-wc' )
			);

			$this->trust_badge_row();
			?>
			<?php
			$row = 0;
			if ( $trust_badge_items ) {
				foreach ( (array) $trust_badge_items as $badge ) :
					$this->trust_badge_row( $row, (int) $badge['badge_attachment_id'], $badge['title'], $badge['description'] );
					$row++;
				endforeach;
			}
			?>
			</tbody>
		</table>
		<a href="javascript;" class="cfw-admin-add-trust-badge-row-button button">Add Trust Badge</a>
		<?php
	}

	public function trust_badge_row( int $index = null, int $badge_attachment_id = null, string $title = null, string $description = null ) {
		$field_name   = $this->trust_badges_field_name;
		$image_source = wp_get_attachment_url( $badge_attachment_id );
		?>
		<tr class="<?php echo is_null( $index ) ? 'cfw-admin-trust-badge-template-row' : ''; ?> cfw-admin-trust-badge-row">
			<th valign="top" style="padding-top: 15px">
				<h4>Badge</h4>
				<div class='cfw-admin-image-preview-wrapper'>
					<img class="cfw-admin-image-preview" src='<?php echo $image_source; ?>' width='100' style='max-height: 100px; width: 100px;'>
				</div>
				<input class="cfw-admin-image-picker-button button" type="button" value="<?php cfw_e( 'Upload Badge Image' ); ?>" />
				<input type='hidden' name='<?php echo $field_name; ?>[<?php echo is_null( $index ) ? 'placeholder' : $index; ?>][badge_attachment_id]' value="<?php echo $badge_attachment_id; ?>">

				<a class="delete-custom-img button secondary-button"><?php cfw_e( 'Clear Badge', 'checkout-wc' ); ?></a>
			</th>
			<td>
				<h4>Title</h4>
				<input type="text" value="<?php echo esc_attr( $title ); ?>" name="<?php echo esc_attr( $field_name ); ?>[<?php echo is_null( $index ) ? 'placeholder' : $index; ?>][title]" id="<?php echo esc_attr( $field_name ); ?>" />
				<p>
					<span class="description">
						<?php cfw_e( 'Example: Satisfaction Guaranteed', 'checkout-wc' ); ?>
					</span>
				</p>

				<h4>Description</h4>
				<textarea rows="4" name="<?php echo esc_attr( $field_name ); ?>[<?php echo is_null( $index ) ? 'placeholder' : $index; ?>][description]"><?php echo esc_attr( $description ); ?></textarea>
				<p class="description">
					<?php cfw_e( 'Example: Every product we sell comes with a 30 day money back guarantee. Have a problem? Let us know and we\'ll make it right!', 'checkout-wc' ); ?>
				</p>
				<p class="description">
					<?php cfw_e( 'Also accepts HTML for embedded trust seals from third parties.', 'checkout-wc' ); ?>
				</p>

				<p>
					<a href="javascript;" class="cfw-admin-trust-badge-remove-row-button">Remove Trust Badge</a>
				</p>
			</td>
		</tr>
		<?php
	}

	public function run_on_plugin_activation() {
		SettingsManager::instance()->add_setting( 'enable_trust_badges', 'no' );
	}
}

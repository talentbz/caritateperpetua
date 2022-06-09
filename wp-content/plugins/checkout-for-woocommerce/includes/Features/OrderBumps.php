<?php

namespace Objectiv\Plugins\Checkout\Features;

use Objectiv\Plugins\Checkout\Admin\Pages\PageAbstract;
use Objectiv\Plugins\Checkout\Interfaces\SettingsGetterInterface;
use Objectiv\Plugins\Checkout\Model\Bump;

class OrderBumps extends FeaturesAbstract {
	public function __construct( bool $available, string $required_plans_list, SettingsGetterInterface $settings_getter ) {
		parent::__construct( true, $available, $required_plans_list, $settings_getter );
	}

	public function init() {
		parent::init();

		Bump::init( PageAbstract::get_parent_slug() );
	}

	protected function run_if_cfw_is_enabled() {
		add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'handle_order_meta' ) );
		add_action( 'cfw_checkout_cart_summary', array( $this, 'output_cart_summary_bumps' ), 41 );
		add_action( 'cfw_checkout_payment_method_tab', array( $this, 'output_payment_tab_bumps' ), 38 );
		add_action( 'cfw_checkout_payment_method_tab', array( $this, 'output_mobile_bumps' ), 38 );
		add_action( 'woocommerce_update_order_review_fragments', array( $this, 'add_bumps_to_update_checkout' ) );
		add_action( 'cfw_checkout_update_order_review', array( $this, 'handle_adding_order_bump_to_cart' ) );
		add_action( 'woocommerce_cart_item_subtotal', array( $this, 'maybe_update_cart_item_subtotal' ), 100000, 2 );
		add_action( 'woocommerce_before_calculate_totals', array( $this, 'maybe_update_cart_price' ), 100000 );
		add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'save_bump_meta_to_order_items' ), 10, 4 );
		add_filter( 'cfw_cart_item_discount', array( $this, 'show_bump_discount_on_cart_item' ), 10, 3 );
		add_action( 'restrict_manage_posts', array( $this, 'admin_filter_select' ), 60 );

		// Add filter to queries on admin orders screen to filter on order type. To avoid WC overriding our query args, we have to hook at 11+
		add_filter( 'request', array( $this, 'filter_orders_query' ), 11 );
	}

	public function unhook_order_bumps_output() {
		remove_action( 'cfw_checkout_cart_summary', array( $this, 'output_cart_summary_bumps' ), 41 );
		remove_action( 'cfw_checkout_payment_method_tab', array( $this, 'output_payment_tab_bumps' ), 38 );
		remove_action( 'cfw_checkout_payment_method_tab', array( $this, 'output_mobile_bumps' ), 38 );
		remove_action( 'woocommerce_update_order_review_fragments', array( $this, 'add_bumps_to_update_checkout' ) );
	}

	/**
	 * @param int $order_id
	 * @throws \Exception
	 */
	public function handle_order_meta( int $order_id ) {
		$purchased_bump_ids = $this->get_purchased_bump_ids( $order_id );

		if ( ! empty( $purchased_bump_ids ) ) {
			$order = \wc_get_order( $order_id );

			$order->add_meta_data( 'cfw_has_bump', true );

			foreach ( $purchased_bump_ids as $purchased_bump_id ) {
				$order->add_meta_data( 'cfw_bump_' . $purchased_bump_id, true );
			}

			$order->save();
		}

		$this->record_bump_stats( $purchased_bump_ids );
	}

	/**
	 * @throws \Exception
	 */
	public function record_bump_stats( array $purchased_bump_ids ) {
		foreach ( $purchased_bump_ids as $purchased_bump_id ) {
			$bump = Bump::get( $purchased_bump_id );

			if ( ! $bump ) {
				continue;
			}

			$bump->increment_displayed_on_purchases_count();
			$bump->increment_purchased_count();
			$bump->update_conversion_rate();

			$offer_product = $bump->get_offer_product();

			if ( ! $offer_product ) {
				continue;
			}

			if ( $bump->is_valid_upsell() ) {
				$base_product = wc_get_product( $bump->get_products()[0] );

				if ( ! $base_product ) {
					continue;
				}

				$new_revenue = $base_product->get_price() - $bump->get_offer_product_sale_price();
			} else {
				$new_revenue = $bump->get_offer_product_sale_price();
			}

			$bump->add_captured_revenue( $new_revenue );
		}

		$raw_displayed_bump_ids = $_POST['cfw_displayed_order_bump'] ?? array();
		$displayed_bump_ids     = array_unique( $raw_displayed_bump_ids );

		foreach ( $displayed_bump_ids as $displayed_bump_id ) {
			$bump = Bump::get( (int) $displayed_bump_id );

			if ( ! $bump ) {
				continue;
			}

			$bump->increment_displayed_on_purchases_count();
			$bump->update_conversion_rate();
		}
	}

	protected function get_purchased_bump_ids( $order_id ): array {
		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return array();
		}

		$items = $order->get_items();

		if ( empty( $items ) ) {
			return array();
		}

		$ids = array();

		foreach ( $items as $item ) {
			$bump_id = $item->get_meta( '_cfw_order_bump_id', true );

			if ( ! $bump_id ) {
				continue;
			}

			$ids[] = $bump_id;
		}

		return $ids;
	}

	/**
	 * @throws \Exception
	 */
	public function output_cart_summary_bumps() {
		$this->output_bumps( 'below_cart_items' );
	}

	/**
	 * @throws \Exception
	 */
	public function output_payment_tab_bumps() {
		$this->output_bumps( 'above_terms_and_conditions' );
	}

	/**
	 * @throws \Exception
	 */
	public function output_mobile_bumps() {
		$this->output_bumps( 'all', 'cfw-order-bumps-mobile' );
	}

	/**
	 * @throws \Exception
	 */
	public function output_bumps( string $location = 'all', string $container_class = '' ) {
		$bumps = Bump::get_all();

		ob_start();
		foreach ( $bumps as $bump ) {
			$display_bump = $bump->is_displayable() && ( 'all' === $location || $bump->get_display_location() === $location );
			$display_bump = apply_filters( 'cfw_display_bump', $display_bump, $bump, $location );

			if ( $display_bump ) {
				$this->display_bump( $bump );
			}
		}

		$bump_content    = ob_get_clean();
		$has_bumps_class = ! empty( $bump_content ) ? 'cfw-has-bumps' : '';

		// Output a div whether or not we have content since it's dynamically refreshed with fragments
		echo "<div id=\"cfw_order_bumps_{$location}\" class=\"cfw-order-bumps {$container_class} {$has_bumps_class}\">";
		echo $bump_content;
		echo '</div>';
	}

	/**
	 * @param Bump $bump
	 */
	protected function display_bump( Bump $bump ) {
		?>
		<div class="cfw-order-bump cfw-module">
			<input type="hidden" name="cfw_displayed_order_bump[]" value="<?php echo $bump->get_id(); ?>" />

			<div class="cfw-order-bump-header">
				<label class="woocommerce-form__label-for-checkbox">
					<input type="checkbox" class="cfw_order_bump_check" name="cfw_order_bump[]" value="<?php echo $bump->get_id(); ?>" />
					<span>
						<?php echo do_shortcode( $bump->get_offer_language() ); ?>
					</span>
				</label>
			</div>
			<div class="cfw-order-bump-body">
				<div class="row">
					<div class="col-2">
						<?php echo $bump->get_offer_product()->get_image( 'cfw_cart_thumb' ); ?>
					</div>
					<div class="col-10">
						<?php echo do_shortcode( $bump->get_offer_description() ); ?>

						<div class="cfw-order-bump-total">
							<?php echo $bump->get_offer_product_price(); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * @param array $fragments
	 * @return array
	 */
	public function add_bumps_to_update_checkout( array $fragments ): array {
		ob_start();
		$this->output_cart_summary_bumps();
		$fragments['#cfw_order_bumps_below_cart_items'] = ob_get_clean();

		ob_start();
		$this->output_payment_tab_bumps();
		$fragments['#cfw_order_bumps_above_terms_and_conditions'] = ob_get_clean();

		ob_start();
		$this->output_mobile_bumps();
		$fragments['#cfw_order_bumps_all'] = ob_get_clean();

		return $fragments;
	}

	/**
	 * @param $post_data
	 * @throws \Exception
	 */
	public function handle_adding_order_bump_to_cart( $post_data ) {
		// turn the string of post data into an array
		// We don't use the $_POST object because $post_data here is preprocessed for us.
		if ( ! is_array( $post_data ) ) {
			parse_str( $post_data, $post_data );
		}

		if ( ! isset( $post_data['cfw_order_bump'] ) || empty( $post_data['cfw_order_bump'] ) ) {
			return;
		}

		foreach ( $post_data['cfw_order_bump'] as $bump_id ) {
			$this->add_bump_to_cart( $bump_id );
		}
	}

	/**
	 * @throws \Exception
	 */
	protected function add_bump_to_cart( $bump_id ): bool {
		$bump             = Bump::get( $bump_id );
		$product          = $bump->get_offer_product();
		$discounted_price = $bump->get_offer_product_sale_price();
		$variation_id     = $product->is_type( 'variable' ) ? $product->get_id() : null;
		$product_id       = $product->is_type( 'variable' ) ? $product->get_parent_id() : $product->get_id();
		$variation_data   = null;
		$metadata         = array(
			'cfw_order_bump_price' => strval( $discounted_price ),
			'_cfw_order_bump_id'   => $bump_id,
		);

		/**
		 * We're not sure what this solves but we know it solves something.
		 */
		if ( $product->is_type( 'variation' ) ) {
			$variation_data = array();

			foreach ( $product->get_variation_attributes() as $taxonomy => $term_names ) {
				$taxonomy                                = str_replace( 'attribute_', '', $taxonomy );
				$attribute_label_name                    = str_replace( 'attribute_', '', wc_attribute_label( $taxonomy ) );
				$variation_data[ $attribute_label_name ] = $term_names;
			}
		}

		$quantity = 1;

		if ( $bump->is_valid_upsell() ) {
			$search_product = array_values( $bump->get_products() )[0];
			$quantity       = $bump->quantity_of_product_in_cart( $search_product );

			$this->remove_product_from_cart( $search_product );
		}

		if ( WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation_data, $metadata ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @param $subtotal
	 * @param $cart_item
	 * @return string
	 * @throws \Exception
	 */
	public function maybe_update_cart_item_subtotal( $subtotal, $cart_item ) {
		if ( ! isset( $cart_item['_cfw_order_bump_id'] ) ) {
			return $subtotal;
		}

		$bump = Bump::get( $cart_item['_cfw_order_bump_id'] );

		if ( ! $bump || ! $bump->is_cart_bump_valid() ) {
			return $subtotal;
		}

		// This was added for TM Extra Product Options
		// Which aggressively tries to override the display of the cart item price
		// We are operating on the assumption that there are other, equally evil plugins out there
		// But we don't know for sure - so we should circle back to this
		// TODO: Should we specifically solve this in a compatibility class or leave this as protective code for n+1 situations?
		return isset( $cart_item['cfw_order_bump_price'] ) ? wc_price( $cart_item['cfw_order_bump_price'] * $cart_item['quantity'] ) : $subtotal;
	}

	/**
	 * @param \WC_Cart $cart
	 * @throws \Exception
	 */
	public function maybe_update_cart_price( \WC_Cart $cart ) {
		foreach ( $cart->get_cart_contents() as $cart_item_key => $cart_item ) {
			if ( ! isset( $cart_item['_cfw_order_bump_id'] ) || ! isset( $cart_item['cfw_order_bump_price'] ) ) {
				continue;
			}

			$bump = Bump::get( $cart_item['_cfw_order_bump_id'] );

			if ( ! $bump || ! $bump->is_cart_bump_valid() ) {
				continue;
			}

			if ( ! $cart_item['data'] instanceof \WC_Product ) {
				continue;
			}

			$cart->cart_contents[ $cart_item_key ]['cfw_order_bump_regular_price'] = $cart_item['data']->get_price();
			$cart_item['data']->set_price( $cart_item['cfw_order_bump_price'] );
		}

		WC()->cart->set_session();
	}

	/**
	 * @param $item
	 * @param $cart_item_key
	 * @param array $values
	 * @throws \Exception
	 */
	public function save_bump_meta_to_order_items( $item, $cart_item_key, array $values ) {
		if ( ! isset( $values['_cfw_order_bump_id'] ) ) {
			return;
		}

		$bump = Bump::get( $values['_cfw_order_bump_id'] );

		if ( ! $bump || ! $bump->is_cart_bump_valid() ) {
			return;
		}

		$item->update_meta_data( '_cfw_order_bump_id', $values['_cfw_order_bump_id'] );
	}

	/**
	 * @param int $needle_product_id
	 * @return bool
	 */
	public function remove_product_from_cart( int $needle_product_id ): bool {
		$needle_product = wc_get_product( $needle_product_id );

		if ( ! $needle_product ) {
			return false;
		}

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$cart_item_variation_id = ! empty( $cart_item['variation_id'] ) ? $cart_item['variation_id'] : 0;
			$cart_item_parent_id    = $cart_item_variation_id ? wp_get_post_parent_id( $cart_item_variation_id ) : 0;
			$possible_ids           = array( $cart_item_parent_id, $cart_item_variation_id, $cart_item['product_id'] );
			$in_cart                = in_array( $needle_product_id, $possible_ids, true );

			if ( $in_cart ) {
				WC()->cart->remove_cart_item( $cart_item_key );

				return true;
			}
		}

		return false;
	}

	/**
	 * @param string $price_html
	 * @param array $cart_item
	 * @param \WC_Product $product
	 * @return string
	 * @throws \Exception
	 */
	public function show_bump_discount_on_cart_item( string $price_html, array $cart_item, \WC_Product $product ): string {
		if ( ! isset( $cart_item['_cfw_order_bump_id'] ) ) {
			return $price_html;
		}

		$bump = Bump::get( $cart_item['_cfw_order_bump_id'] );

		if ( ! $bump || ! $bump->is_cart_bump_valid() ) {
			return $price_html;
		}

		return $bump->get_offer_product_price();
	}

	public function admin_filter_select() {
		global $typenow;

		if ( 'shop_order' !== $typenow ) {
			return;
		}

		// TODO: get_all() only returns published bumps. This should probably get them all.
		$all_bumps = Bump::get_all();

		if ( count( $all_bumps ) === 0 ) {
			return;
		}

		?>
		<select name="cfw_order_bump_filter" id="cfw_order_bump_filter">
			<option value=""><?php cfw_esc_html_e( 'All orders', 'woocommerce-subscriptions' ); ?></option>
			<?php
			$bump_filters = array(
				'any' => cfw__( 'Contains Any Order Bump', 'checkout-wc' ),
			);

			foreach ( $all_bumps as $bump ) {
				$bump_filters[ $bump->get_id() ] = sprintf( cfw__( 'Has Bump: %s' ), $bump->get_title() );
			}

			foreach ( $bump_filters as $bump_key => $bump_filter_description ) {
				echo '<option value="' . esc_attr( $bump_key ) . '"';

				if ( isset( $_GET['cfw_order_bump_filter'] ) && $_GET['cfw_order_bump_filter'] ) {
					selected( $bump_key, $_GET['cfw_order_bump_filter'] );
				}

				echo '>' . esc_html( $bump_filter_description ) . '</option>';
			}
			?>
		</select>
		<?php
	}

	/**
	 * @param $vars
	 * @return array
	 */
	public static function filter_orders_query( $vars ): array {
		global $typenow;

		if ( 'shop_order' === $typenow && ! empty( $_GET['cfw_order_bump_filter'] ) ) {
			$vars['meta_query']['relation'] = 'AND';

			if ( 'any' === $_GET['cfw_order_bump_filter'] ) {
				$vars['meta_query'][] = array(
					'key'     => 'cfw_has_bump',
					'compare' => 'EXISTS',
				);
			} else {
				$vars['meta_query'][] = array(
					'key'     => 'cfw_bump_' . (int) $_GET['cfw_order_bump_filter'],
					'compare' => 'EXISTS',
				);
			}
		}

		return $vars;
	}
}

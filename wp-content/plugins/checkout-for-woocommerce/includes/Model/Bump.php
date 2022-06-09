<?php

namespace Objectiv\Plugins\Checkout\Model;

class Bump {
	protected $id;
	protected $title;
	protected $display_for;
	protected $products;
	protected $categories;
	protected $any_product = false;
	protected $location;
	protected $discount_type;
	protected $offer_product;
	protected $offer_discount;
	protected $offer_language;
	protected $offer_description;
	protected $upsell = false;

	public function __construct() {}

	/**
	 * @param int $id
	 * @throws \Exception
	 */
	public function load( int $id ): bool {
		$bump = get_post( $id );

		if ( empty( $bump ) ) {
			return false;
		}

		$this->id                = $id;
		$this->title             = $bump->post_title;
		$this->display_for       = get_post_meta( $this->id, 'cfw_ob_display_for', true );
		$this->products          = get_post_meta( $this->id, 'cfw_ob_products', true );
		$this->categories        = get_post_meta( $this->id, 'cfw_ob_categories', true );
		$this->any_product       = get_post_meta( $this->id, 'cfw_ob_any_product', true ) === 'yes';
		$this->location          = get_post_meta( $this->id, 'cfw_ob_display_location', true );
		$this->discount_type     = get_post_meta( $this->id, 'cfw_ob_discount_type', true );
		$this->offer_product     = get_post_meta( $this->id, 'cfw_ob_offer_product', true );
		$this->offer_discount    = get_post_meta( $this->id, 'cfw_ob_offer_discount', true );
		$this->offer_language    = get_post_meta( $this->id, 'cfw_ob_offer_language', true );
		$this->offer_description = get_post_meta( $this->id, 'cfw_ob_offer_description', true );
		$this->upsell            = get_post_meta( $this->id, 'cfw_ob_upsell', true ) === 'yes';

		return true;
	}

	/**
	 * @return string
	 */
	public function get_title(): string {
		return $this->title;
	}

	/**
	 * @return bool
	 */
	public function is_displayable(): bool {
		$offer_product = $this->get_offer_product();

		// Is the bump in stock?
		if ( ! $this->can_offer_product_be_added_to_the_cart() ) {
			return false;
		}

		// Is it in the cart already?
		if ( $this->quantity_of_product_in_cart( $this->offer_product ) ) {
			return false;
		}

		// Is it a valid upsell (setup correctly) and are there enough units of the offer product to match the cart product?
		// TODO: Technically this disallows upsell offer products that are backordered. Bug or feature? YOU DECIDE.
		if ( $this->is_valid_upsell() && $offer_product->get_manage_stock() && $this->quantity_of_normal_product_in_cart( array_values( $this->products )[0] ) > $offer_product->get_stock_quantity() ) {
			return false;
		}

		// If by this point we passed all checks and the product is set to display on all products, we can display
		if ( 'all_products' === $this->display_for ) {
			return true;
		}

		if ( 'specific_products' === $this->display_for ) {
			$matching_products_in_cart = 0;

			// Count matching products in the cart
			foreach ( $this->products as $product ) {
				if ( $this->quantity_of_normal_product_in_cart( (int) $product ) ) {
					$matching_products_in_cart++;
				}
			}

			// If all products must match and we have fewer products in the cart than in our matching list, return false
			if ( ! $this->any_product && count( $this->products ) > $matching_products_in_cart ) {
				return false;
			}

			// If we get here, matching rule is set to any product, so we can
			// use the number of matching products to determine if we have a match
			return boolval( $matching_products_in_cart );
		}

		if ( 'specific_categories' === $this->display_for ) {
			// Count matching products in the cart
			foreach ( $this->categories as $category ) {
				if ( $this->quantity_of_normal_cart_items_in_category( $category ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * @return bool
	 */
	public function is_cart_bump_valid(): bool {
		$offer_product = $this->get_offer_product();

		// Is it a valid upsell (setup correctly) and are there enough units of the offer product to match the cart product?
		if ( $this->is_valid_upsell() && $this->quantity_of_product_in_cart( array_values( $this->products )[0] ) > $offer_product->get_stock_quantity() ) {
			return false;
		}

		// If the bump is valid for all products, make sure we have at least one other product in the cart
		if ( 'all_products' === $this->display_for && WC()->cart->get_cart_contents_count() > $this->quantity_of_product_in_cart( $this->offer_product ) ) {
			return true;
		}

		if ( 'specific_products' === $this->display_for ) {
			$matching_products_in_cart = 0;

			// Count matching products in the cart
			foreach ( $this->products as $product ) {
				if ( $this->quantity_of_product_in_cart( (int) $product ) ) {
					$matching_products_in_cart++;
				}
			}

			// If all products must match and we have fewer products in the cart than in our matching list, return false
			if ( ! $this->any_product && count( $this->products ) > $matching_products_in_cart ) {
				return false;
			}

			// If we get here, matching rule is set to any product, so we can
			// use the number of matching products to determine if we have a match
			return boolval( $matching_products_in_cart );
		}

		if ( 'specific_categories' === $this->display_for ) {
			// Count matching products in the cart
			foreach ( $this->categories as $category ) {
				if ( $this->quantity_of_normal_cart_items_in_category( $category ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * @return bool
	 */
	public function is_valid_upsell(): bool {
		return $this->upsell && 'specific_products' === $this->display_for && count( $this->products ) === 1;
	}

	public function can_offer_product_be_added_to_the_cart(): bool {
		$product = $this->get_offer_product();

		return $product && $product->is_purchasable() && ( $product->is_in_stock() || $product->backorders_allowed() );
	}

	/**
	 * @param int $needle_product_id
	 * @return int
	 */
	public function quantity_of_product_in_cart( int $needle_product_id ): int {
		$needle_product = wc_get_product( $needle_product_id );

		if ( ! $needle_product ) {
			return 0;
		}

		foreach ( WC()->cart->get_cart() as $cart_item ) {
			$cart_item_variation_id = ! empty( $cart_item['variation_id'] ) ? $cart_item['variation_id'] : 0;
			$cart_item_parent_id    = $cart_item_variation_id ? wp_get_post_parent_id( $cart_item_variation_id ) : 0;
			$possible_ids           = array( $cart_item_parent_id, $cart_item_variation_id, $cart_item['product_id'] );
			$in_cart                = in_array( $needle_product_id, $possible_ids, true );

			if ( $in_cart ) {
				return $cart_item['quantity'];
			}
		}

		return 0;
	}

	/**
	 * @param int $needle_product_id
	 * @return int
	 */
	public function quantity_of_normal_product_in_cart( int $needle_product_id ): int {
		$needle_product = wc_get_product( $needle_product_id );

		if ( ! $needle_product ) {
			return 0;
		}

		foreach ( WC()->cart->get_cart() as $cart_item ) {
			if ( isset( $cart_item['_cfw_order_bump_id'] ) ) {
				continue;
			}

			$cart_item_variation_id = ! empty( $cart_item['variation_id'] ) ? $cart_item['variation_id'] : 0;
			$cart_item_parent_id    = $cart_item_variation_id ? wp_get_post_parent_id( $cart_item_variation_id ) : 0;
			$possible_ids           = array( $cart_item_parent_id, $cart_item_variation_id, $cart_item['product_id'] );
			$in_cart                = in_array( $needle_product_id, $possible_ids, true );

			if ( $in_cart ) {
				return $cart_item['quantity'];
			}
		}

		return 0;
	}

	/**
	 * @param string $needle_category_slug
	 * @return int
	 */
	public function quantity_of_normal_cart_items_in_category( string $needle_category_slug ): int {
		$needle_category = get_term_by( 'slug', $needle_category_slug, 'product_cat' );

		if ( ! $needle_category ) {
			return 0;
		}

		$found = 0;

		foreach ( WC()->cart->get_cart() as $cart_item ) {
			if ( isset( $cart_item['_cfw_order_bump_id'] ) ) {
				continue;
			}

			$cart_item_terms = wp_get_post_terms( $cart_item['product_id'], 'product_cat' );

			/** @var \WP_Term $cart_item_term */
			foreach ( $cart_item_terms as $cart_item_term ) {
				if ( $cart_item_term->slug === $needle_category_slug ) {
					$found++;
				}
			}
		}

		return $found;
	}

	/**
	 * @return int
	 */
	public function get_id(): int {
		return $this->id;
	}

	/**
	 * @return mixed
	 */
	public function get_offer_language() {
		return $this->offer_language;
	}

	/**
	 * @return mixed
	 */
	public function get_offer_description() {
		return $this->offer_description;
	}

	/**
	 * @return false|\WC_Product|null
	 */
	public function get_offer_product() {
		return wc_get_product( $this->offer_product );
	}

	/**
	 * @return string
	 */
	public function get_offer_product_price(): string {
		$product              = $this->get_offer_product();
		$price                = wc_get_price_to_display( $product, array( 'price' => $product->get_price( 'view' ) ) );
		$sale_price           = wc_get_price_to_display( $product, array( 'price' => $this->get_offer_product_sale_price() ) );
		$sale_price_formatted = wc_price( $sale_price );

		if ( $price > $sale_price ) {
			return wc_format_sale_price( $price, $sale_price );
		}

		return $sale_price_formatted;
	}

	/**
	 * @return float|int
	 */
	public function get_offer_product_sale_price() {
		$product = $this->get_offer_product();

		$discount_type = $this->discount_type;
		$discount      = $this->offer_discount;

		if ( wc_prices_include_tax() ) {
			$price = wc_get_price_including_tax( $product );
		} else {
			$price = wc_get_price_excluding_tax( $product );
		}

		$discount_value = 'percent' === $discount_type ? ( $price / 100 ) * $discount : $discount;

		return $price - $discount_value;
	}

	public function get_display_location(): string {
		return $this->location ?? 'below_cart_items';
	}

	/**
	 * Get Displayed On Purchases Count
	 *
	 * The number of times this bump was displayed and a purchase was subsequently made.
	 *
	 * @return integer
	 */
	private function get_displayed_on_purchases_count(): int {
		return intval( get_post_meta( $this->id, 'times_bump_displayed_on_purchases', true ) );
	}

	/**
	 * Get Purchase Count
	 *
	 * The number of times this bump was added to the cart and purchased.
	 *
	 * @return integer
	 */
	public function get_purchase_count(): int {
		return intval( get_post_meta( $this->id, 'times_bump_purchased', true ) );
	}

	public function increment_displayed_on_purchases_count() {
		update_post_meta( $this->id, 'times_bump_displayed_on_purchases', $this->get_displayed_on_purchases_count() + 1 );
	}

	public function increment_purchased_count() {
		update_post_meta( $this->id, 'times_bump_purchased', $this->get_purchase_count() + 1 );
	}

	public function add_captured_revenue( float $new_revenue ) {
		$captured_revenue = max( (float) get_post_meta( $this->id, 'captured_revenue', true ), 0.0 );

		update_post_meta( $this->id, 'captured_revenue', $captured_revenue + $new_revenue );
	}

	public function update_conversion_rate() {
		$purchase_count  = $this->get_purchase_count();
		$displayed_count = $this->get_displayed_on_purchases_count();
		$not_calculable  = min( $purchase_count, $displayed_count ) < 1;

		$value = $not_calculable ? 0 : round( $purchase_count / $displayed_count * 100, 2 );

		update_post_meta( $this->id, 'conversion_rate', $value );
	}

	public function get_conversion_rate() {
		$value = get_post_meta( $this->id, 'conversion_rate', true );

		return '' === $value ? '--' : floatval( $value ) . '%';
	}

	public function get_estimated_revenue(): float {
		$offer_product = $this->get_offer_product();

		if ( ! $offer_product ) {
			return 0.0;
		}

		if ( $this->is_valid_upsell() ) {
			$base_product = wc_get_product( $this->get_products()[0] );

			if ( ! $base_product ) {
				return 0.0;
			}

			return ( $base_product->get_price() - $this->get_offer_product_sale_price() ) * $this->get_purchase_count();
		}

		return $this->get_purchase_count() * $this->get_offer_product_sale_price();
	}

	public function get_captured_revenue(): float {
		return floatval( get_post_meta( $this->id, 'captured_revenue', true ) );
	}

	/**
	 * @return array
	 */
	public function get_products(): array {
		return (array) $this->products;
	}

	/**
	 * @throws \Exception
	 */
	static public function get( int $post_id ) {
		$self = new self();

		return $self->load( $post_id ) ? $self : false;
	}

	static public function get_post_type(): string {
		return 'cfw_order_bumps';
	}

	static public function init( $parent_menu_slug ) {
		$post_type = self::get_post_type();

		add_action(
			'init',
			function() use ( $post_type, $parent_menu_slug ) {
				register_post_type(
					$post_type,
					array(
						'labels'             => array(
							'name'               => cfw__( 'Order Bumps', 'checkout-wc' ),
							'singular_name'      => cfw__( 'Order Bump', 'checkout-wc' ),
							'add_new'            => cfw__( 'Add New', 'checkout-wc' ),
							'add_new_item'       => cfw__( 'Add New Order Bump', 'checkout-wc' ),
							'edit_item'          => cfw__( 'Edit Order Bump', 'checkout-wc' ),
							'new_item'           => cfw__( 'New Order Bump', 'checkout-wc' ),
							'view_item'          => cfw__( 'View Order Bump', 'checkout-wc' ),
							'search_items'       => cfw__( 'Find Order Bump', 'checkout-wc' ),
							'not_found'          => cfw__( 'No order bumps were found.', 'checkout-wc' ),
							'not_found_in_trash' => cfw__( 'Not found in trash', 'checkout-wc' ),
							'menu_name'          => cfw__( 'Order Bumps', 'checkout-wc' ),
						),
						'public'             => false,
						'publicly_queryable' => true,
						'show_ui'            => true,
						'show_in_menu'       => $parent_menu_slug,
						'query_var'          => false,
						'rewrite'            => false,
						'capability_type'    => 'post',
						'has_archive'        => false,
						'hierarchical'       => false,
						'supports'           => array( 'title' ),
					)
				);
			}
		);

		add_filter(
			"manage_{$post_type}_posts_columns",
			function( $columns ) {
				$date = array_pop( $columns );

				$columns['conversion_rate'] = 'Conversion Rate' . wc_help_tip( 'Conversion Rate tracks how often a bump is added to an actual completed purchase. If 20 orders are placed and a bump was displayed on 10 of those orders and the bump was purchased 5 times, the conversion rate is 50%.' );
				$columns['revenue']         = 'Revenue' . wc_help_tip( 'The additional revenue that an Order Bump has captured. When configured as an upsell, it calculates the relative value between the offer product and the product being replaced. Revenues incurred before version 6.1.4 are estimated.' );
				$columns['date']            = $date;

				return $columns;
			}
		);

		add_action(
			"manage_{$post_type}_posts_custom_column",
			function( $column, $post_id ) {
				if ( 'conversion_rate' === $column ) {
					$bump = self::get( $post_id );
					echo $bump->get_conversion_rate();
				}

				if ( 'revenue' === $column ) {
					$bump = self::get( $post_id );

					if ( 0.0 === $bump->get_captured_revenue() ) {
						echo '--';
					} else {
						echo wc_price( $bump->get_captured_revenue() );
					}
				}
			},
			10,
			2
		);
	}

	/**
	 * @return Bump[]
	 * @throws \Exception
	 */
	static public function get_all(): array {
		$posts = get_posts(
			array(
				'post_type'        => self::get_post_type(),
				'numberposts'      => -1,
				'suppress_filters' => true,
			)
		);

		$bumps = array();

		foreach ( $posts as $post ) {
			$bumps[] = self::get( $post->ID );
		}

		return array_filter( $bumps );
	}
}

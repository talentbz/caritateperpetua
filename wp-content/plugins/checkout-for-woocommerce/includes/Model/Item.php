<?php

namespace Objectiv\Plugins\Checkout\Model;

use Objectiv\Plugins\Checkout\Managers\SettingsManager;
use WC_Order_Item;
use WC_Product;

class Item {
	protected $thumbnail;
	protected $quantity;
	protected $title;
	protected $url;
	protected $subtotal;
	protected $row_class;
	protected $item_key;
	protected $raw_item;
	protected $product;
	protected $data;
	protected $formatted_data;

	/**
	 * @param array|WC_Order_Item $item
	 */
	public function __construct( $item ) {
		if ( is_array( $item ) ) {
			$this->ingest_cart_item( $item );
		} else {
			$this->ingest_order_item( $item );
		}
	}

	/**
	 * @param array $cart_item
	 */
	protected function ingest_cart_item( array $cart_item ) {
		// Some of our callbacks rely on cart_item_key being a string
		// Since PHP coerces scalar types to strings for typed function arguments,
		// we just have to handle the situation where the key is null, which is
		// for some reason not coerced due to ancient secret PHP knowledge
		$cart_item_key = $cart_item['key'] ?? '';

		/** @var WC_Product $_product */
		$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );

		$woocommerce_filtered_cart_item_row_class = esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) );
		$this->thumbnail                          = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image( 'cfw_cart_thumb' ), $cart_item, $cart_item_key );
		$this->quantity                           = floatval( $cart_item['quantity'] );
		$this->title                              = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
		$this->url                                = get_permalink( $cart_item['product_id'] );
		$this->subtotal                           = apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key );
		$this->row_class                          = apply_filters( 'cfw_cart_item_row_class', $woocommerce_filtered_cart_item_row_class, $cart_item );
		$this->item_key                           = $cart_item_key;
		$this->raw_item                           = $cart_item;
		$this->product                            = $_product;
		$this->data                               = $this->get_cart_item_data( $cart_item );
		$this->formatted_data                     = $this->get_formatted_cart_data();
	}

	/**
	 * @param WC_Order_Item $item
	 */
	protected function ingest_order_item( WC_Order_Item $item ) {
		$order         = wc_get_order( $item->get_order_id() );
		$item_product  = $item->get_product();
		$item_subtotal = $order->get_formatted_line_subtotal( $item );

		$this->thumbnail      = $item_product ? $item_product->get_image( 'cfw_cart_thumb' ) : '';
		$this->quantity       = $item->get_quantity();
		$this->title          = $item->get_name();
		$this->url            = $item_product ? get_permalink( $item->get_product_id() ) : '';
		$this->subtotal       = ! empty( $item_subtotal ) ? $item_subtotal : wc_price( $item->get_subtotal() );
		$this->row_class      = apply_filters( 'cfw_order_item_row_class', '', $item );
		$this->item_key       = $item->get_id();
		$this->raw_item       = $item;
		$this->product        = $item_product;
		$this->data           = $this->get_order_item_data( $item );
		$this->formatted_data = $this->get_formatted_order_item_data();
	}

	/**
	 * @return string
	 */
	public function get_thumbnail(): string {
		return strval( $this->thumbnail );
	}

	/**
	 * @return int
	 */
	public function get_quantity(): float {
		return floatval( $this->quantity );
	}

	/**
	 * @return string
	 */
	public function get_title(): string {
		return strval( $this->title );
	}

	/**
	 * @return string
	 */
	public function get_url(): string {
		return strval( $this->url );
	}

	/**
	 * @return string
	 */
	public function get_subtotal(): string {
		return strval( $this->subtotal );
	}

	/**
	 * @return string
	 */
	public function get_row_class(): string {
		return strval( $this->row_class );
	}

	/**
	 * @return string
	 */
	public function get_item_key(): string {
		return strval( $this->item_key );
	}

	/**
	 * @return array|WC_Order_Item
	 */
	public function get_raw_item() {
		// TODO: Eliminate the necessity of this workaround in a future major version
		return $this->raw_item;
	}

	/**
	 * @return WC_Product
	 */
	public function get_product(): WC_Product {
		return $this->product;
	}

	/**
	 * @return array
	 */
	public function get_data(): array {
		return $this->data ?? array();
	}

	public function get_formatted_data(): string {
		return $this->formatted_data;
	}

	/**
	 * @param array $item
	 * @return array
	 */
	protected function get_cart_item_data( array $item ): array {
		$item_data = array();

		// Variation values are shown only if they are not found in the title as of 3.0.
		// This is because variation titles display the attributes.
		if ( $item['data']->is_type( 'variation' ) && is_array( $item['variation'] ) ) {
			foreach ( $item['variation'] as $name => $value ) {
				$taxonomy = wc_attribute_taxonomy_name( str_replace( 'attribute_pa_', '', urldecode( $name ) ) );

				if ( taxonomy_exists( $taxonomy ) ) {
					// If this is a term slug, get the term's nice name.
					$term = get_term_by( 'slug', $value, $taxonomy );
					if ( ! is_wp_error( $term ) && $term && $term->name ) {
						$value = $term->name;
					}
					$label = wc_attribute_label( $taxonomy );
				} else {
					// If this is a custom option slug, get the options name.
					$value = apply_filters( 'woocommerce_variation_option_name', $value, null, $taxonomy, $item['data'] );
					$label = wc_attribute_label( str_replace( 'attribute_', '', $name ), $item['data'] );
				}

				// Check the nicename against the title.
				if ( '' === $value || wc_is_attribute_in_product_name( $value, $item['data']->get_name() ) ) {
					continue;
				}

				$item_data[] = array(
					'key'   => $label,
					'value' => $value,
				);
			}
		}

		// Filter item data to allow 3rd parties to add more to the array.
		$item_data = apply_filters( 'woocommerce_get_item_data', $item_data, $item );
		$data      = array();

		foreach ( $item_data as $item_datum ) {
			$data[ $item_datum['key'] ] = $item_datum['value'];
		}

		return $data;
	}

	protected function get_formatted_cart_data() {
		if ( apply_filters( 'cfw_cart_item_data_expanded', SettingsManager::instance()->get_setting( 'cart_item_data_display' ) === 'woocommerce' ) ) {
			$output = wc_get_formatted_cart_item_data( $this->get_raw_item() );

			return str_replace( ' :', ':', $output );
		}

		$item_data = $this->get_data();

		if ( empty( $item_data ) ) {
			return '';
		}

		$display_outputs = array();

		foreach ( $item_data as $raw_key => $raw_value ) {
			if ( is_null( $raw_value ) ) {
				continue;
			}

			$key               = wp_kses_post( $raw_key );
			$value             = strip_tags( $raw_value );
			$display_outputs[] = "$key: $value";
		}

		return join( ' / ', $display_outputs );
	}

	/**
	 * @param WC_Order_Item $item
	 * @return array
	 */
	protected function get_order_item_data( WC_Order_Item $item ): array {
		$data = array();

		foreach ( $item->get_formatted_meta_data() as $meta ) {
			$data[ $meta->display_key ] = $meta->display_value;
		}

		return $data;
	}

	protected function get_formatted_order_item_data() {
		if ( apply_filters( 'cfw_cart_item_data_expanded', SettingsManager::instance()->get_setting( 'cart_item_data_display' ) === 'woocommerce' ) ) {
			return wc_display_item_meta( $this->get_raw_item(), array( 'echo' => false ) );
		}

		$item_data = $this->get_data();

		if ( empty( $item_data ) ) {
			return '';
		}

		$display_outputs = array();

		foreach ( $item_data as $raw_key => $raw_value ) {
			$key               = wp_kses_post( $raw_key );
			$value             = strip_tags( $raw_value );
			$display_outputs[] = "$key: $value";
		}

		return join( ' / ', $display_outputs );
	}
}

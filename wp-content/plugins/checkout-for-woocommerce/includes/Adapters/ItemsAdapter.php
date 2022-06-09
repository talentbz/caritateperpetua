<?php

namespace Objectiv\Plugins\Checkout\Adapters;

use Objectiv\Plugins\Checkout\Model\Item;
use WC_Cart;
use WC_Order;

class ItemsAdapter {
	protected $items = array();

	/**
	 * @param WC_Cart|WC_Order $object
	 */
	public function __construct( $object ) {
		if ( is_a( $object, '\\WC_Cart' ) ) {
			$this->ingest_cart( $object );
		} else {
			$this->ingest_order( $object );
		}
	}

	/**
	 * @param WC_Cart $cart
	 */
	public function ingest_cart( WC_Cart $cart ) {
		foreach ( $cart->get_cart() as $cart_item ) {
			// Some of our callbacks rely on cart_item_key being a string
			// Since PHP coerces scalar types to strings for typed function arguments,
			// we just have to handle the situation where the key is null, which is
			// for some reason not coerced due to ancient secret PHP knowledge
			$cart_item_key = $cart_item['key'] ?? '';

			/** @var \WC_Product $_product */
			$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );

			$is_ingestible = $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key );

			if ( ! $is_ingestible ) {
				continue;
			}

			$this->items[] = new Item( $cart_item );
		}
	}

	/**
	 * @param WC_Order $order
	 */
	public function ingest_order( WC_Order $order ) {
		foreach ( $order->get_items() as $item ) {
			if ( ! apply_filters( 'woocommerce_order_item_visible', true, $item ) ) {
				continue;
			}

			$this->items[] = new Item( $item );
		}
	}

	/**
	 * @return array
	 */
	public function get_items(): array {
		return $this->items;
	}
}

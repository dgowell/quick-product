<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_Phone_Orders_Custom_Products_Controller_Pro {
	const CART_ITEM_KEY = 'wpo_custom_product_changes';
	const ORDER_ITEM_META_KEY = 'wpo_custom_product_changes';
	const OPTION_KEY_CUSTOM_PRODUCT_ID = 'wpo_custom_product_id';

	/**
	 * @var WC_Phone_Orders_Custom_Products_Controller
	 */
	protected $base_custom_product_controller;

	/**
	 * @var WC_Product_Simple
	 */
	static protected $custom_product;

	/**
	 * @var int|false
	 */
	protected $custom_product_id;

	public function __construct() {
		$this->custom_product_id = get_option( self::OPTION_KEY_CUSTOM_PRODUCT_ID );
		$this->base_custom_product_controller = new WC_Phone_Orders_Custom_Products_Controller();
	}

	/**
	 * @return WC_Product_Simple
	 */
	public function load_product() {
		if ( isset( self::$custom_product ) ) {
			return self::$custom_product;
		}

		if ( $this->custom_product_id ) {
			self::$custom_product = wc_get_product( $this->custom_product_id );
			if ( self::$custom_product AND 'trash' === self::$custom_product->get_status() ) {
				self::$custom_product = false;
			}
		}

		if ( ! self::$custom_product ) {
			self::$custom_product = $this->create_custom_product();
			update_option( self::OPTION_KEY_CUSTOM_PRODUCT_ID, self::$custom_product->get_id() );
			$this->custom_product_id = self::$custom_product->get_id();
		}

		return self::$custom_product;
	}

	/**
	 * @return WC_Product_Simple
	 */
	public function create_custom_product() {
		return $this->base_custom_product_controller->create_custom_product();
	}

	/**
	 * @param int|WC_Product $the_product
	 *
	 * @return bool
	 */
	public function is_custom_product( $the_product ) {
		if ( $this->custom_product_id === false ) {
			return false;
		}

		if ( is_numeric( $the_product ) ) {
			return intval( $the_product ) === intval( $this->custom_product_id );
		} elseif ( $the_product instanceof WC_Product ) {
			return intval( $the_product->get_id() ) === intval( $this->custom_product_id );
		}

		return false;
	}

	/**
	 * @param WC_Cart $wc_cart
	 * @param WC_Product $product
	 * @param int|float $quantity
	 *
	 * @return string
	 */
	public function add_to_cart( $wc_cart, $product, $quantity ) {
		$product      = clone $product;
		$product_id   = $product->get_id();
		$variation_id = 0;
		$variation    = array();
		$quantity     = floatval( $quantity );

		$cart_item_data = array(
			self::CART_ITEM_KEY => $product->get_changes(),
		);

		$cart_id       = $wc_cart->generate_cart_id( $product->get_id(), $variation_id, $variation, $cart_item_data );
		$cart_item_key = $wc_cart->find_product_in_cart( $cart_id );

		if ( $cart_item_key ) {
			$wc_cart->cart_contents[ $cart_item_key ]['quantity'] += $quantity;
		} else {
			$cart_item_key = $cart_id;

			$wc_cart->cart_contents[ $cart_item_key ] = array_merge(
				$cart_item_data,
				array(
					'key'          => $cart_item_key,
					'product_id'   => $product_id,
					'variation_id' => $variation_id,
					'variation'    => $variation,
					'quantity'     => $quantity,
					'data'         => $product,
					'data_hash'    => wc_get_cart_item_data_hash( $product ),
				)
			);
		}

		return $cart_item_key;
	}

	/**
	 * @param WC_Cart $wc_cart
	 * @param array $cart_item
	 *
	 * @return WC_Product_Simple
	 */
	public function restore_product_from_cart( $wc_cart, $cart_item ) {
		$product = clone $this->load_product();
		$changes = isset( $cart_item[ self::CART_ITEM_KEY ] ) ? $cart_item[ self::CART_ITEM_KEY ] : array();

		$product->set_props( $changes );

		return $product;
	}

	/**
	 * @param array $cart_item
	 * @param WC_Product $product
	 */
	public function store_product_in_cart_item( &$cart_item, $product ) {
		$cart_item[ self::CART_ITEM_KEY ] = $product->get_changes();
	}

	/**
	 * @param WC_Product $product
	 * @param WC_Order_Item $order_item
	 */
	public function store_product_in_order_item( $product, $order_item ) {
		$order_item->update_meta_data( self::ORDER_ITEM_META_KEY, $product->get_changes() );
	}

	/**
	 * @param WC_Order_Item $order_item
	 *
	 * @return WC_PRoduct|false
	 */
	public function restore_product_from_order_item( $order_item ) {
		$product = clone $this->load_product();
		$changes = $order_item->get_meta( self::ORDER_ITEM_META_KEY );

		if ( ! $changes ) {
			return false;
		}

		$product->set_props( $changes );

		return $product;
	}
}
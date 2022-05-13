<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_Phone_Orders_Custom_Products_Hooks_Pro {

	/**
	 * @var WC_Phone_Orders_Custom_Products_Controller
	 */
	protected $custom_prod_control;

	public function __construct() {
		$this->custom_prod_control = new WC_Phone_Orders_Custom_Products_Controller_Pro();
	}

	public function make_custom_product_always_purchasable() {
		add_filter( 'woocommerce_is_purchasable', function ( $purchasable, $product ) {
			if ( $this->custom_prod_control->is_custom_product( $product ) ) {
				$purchasable = true;
			}

			return $purchasable;
		}, 10, 2 );

		add_filter( 'woocommerce_product_is_in_stock', function ( $stock_status, $product ) {
			if ( $this->custom_prod_control->is_custom_product( $product ) ) {
				$stock_status = true;
			}

			return $stock_status;
		}, 10, 2 );
	}

	public function allow_to_store_custom_product_in_order_item() {
		add_action( 'woocommerce_checkout_create_order_line_item', function ( $item, $cart_item_key, $values, $order ) {
			if ( $this->custom_prod_control->is_custom_product( $values['data'] ) ) {
				$this->custom_prod_control->store_product_in_order_item( $values['data'], $item );
			}
		}, 10, 4 );
	}

	public function install_action_to_load_from_session() {
		add_action( 'woocommerce_cart_loaded_from_session', function ( $cart ) {
			foreach ( $cart->cart_contents as &$cart_content ) {
				$product_id = $cart_content['variation_id'] ? $cart_content['variation_id'] : $cart_content['product_id'];

				if ( $this->custom_prod_control->is_custom_product( $product_id ) ) {
					$cart_content['data'] = $this->custom_prod_control->restore_product_from_cart( WC()->cart, $cart_content );
				}
			}
		}, 10, 2 );
	}

	public function remove_product_edit_link_in_order_edit_page() {
		$remove_callback = function ( $product, $item ) {
			if ( $this->custom_prod_control->is_custom_product( $product ) ) {
				return false;
			}

			return $product;
		};

		add_action( 'woocommerce_order_item_line_item_html', function () use ( $remove_callback ) {
			remove_action( 'woocommerce_order_item_product', $remove_callback, 10 );
		} );

		add_action( 'woocommerce_before_order_item_line_item_html', function () use ( $remove_callback ) {
			add_filter( 'woocommerce_order_item_product', $remove_callback, 10, 2 );
		} );
	}
}
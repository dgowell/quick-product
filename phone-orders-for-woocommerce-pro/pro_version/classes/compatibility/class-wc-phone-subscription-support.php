<?php
// class loaded in ADMIN areay only

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly


class WC_Phone_Subscription_Support {

    public function __construct() {

	add_action('wp_loaded', function () {

	    if ( ! class_exists( 'WC_Subscriptions' ) || ! class_exists( 'WC_Subscriptions_Product' ) ) {
		return;
	    }

	    add_filter('wpo_update_cart_cart_item_meta', array($this, 'add_update_cart_item_meta'), 10, 3);
	    add_filter('wpo_update_cart_loaded_product', array($this, 'get_update_cart_loaded_product'), 10, 2);

	    add_filter('wpo_get_item_by_product', array($this, 'get_item_by_product'), 10, 3);

	    add_filter('woocommerce_add_cart_item', array($this, 'add_fields_to_cart_product_meta'));
	    add_filter('woocommerce_get_cart_item_from_session', array($this, 'add_fields_to_cart_product_meta'));

	    add_filter('wpo_load_order_loaded_product', array($this, 'get_load_order_loaded_product'), 10, 4);

	    add_filter('wcs_recurring_cart_next_payment_date', array($this, 'get_recurring_cart_next_payment_date'), 10, 3);
	});
    }

    public function add_update_cart_item_meta($cart_item_meta, $item, $items) {

	if ( ! empty( $item['wpo_subscription_fields'] ) ) {
	    $cart_item_meta['wpo_subscription_fields'] = $item['wpo_subscription_fields'];
	}

	return $cart_item_meta;
    }

    public function get_recurring_cart_next_payment_date($next_payment_date, $recurring_cart, $product) {

	foreach ($recurring_cart->cart_contents as $cart_item) {
	    if ( ! empty( $cart_item['wpo_subscription_fields']['next_payment_date_timestamp_utc'] ) && ! empty( $cart_item['wpo_subscription_fields']['next_payment_date_timestamp_utc_changed_manually'] ) ) {
		return gmdate('Y-m-d H:i:s', $cart_item['wpo_subscription_fields']['next_payment_date_timestamp_utc']);
	    }
	}

	return $next_payment_date;
    }

    public function get_update_cart_loaded_product($loaded_product, $cart_item) {

	if ( empty( $loaded_product['is_subscribed'] ) ) {
	    return $loaded_product;
	}

	foreach (WC()->cart->recurring_carts as $recurring_cart) {
	    if ( ! empty ( $recurring_cart->cart_contents[$cart_item['wpo_cart_item_key']] ) ) {
		break;
	    }
	}

	if ( empty( $recurring_cart ) ) {
	    return $loaded_product;
	}

	$subscription_fields = empty($cart_item['wpo_subscription_fields']) ? array() : $cart_item['wpo_subscription_fields'];

	$subscription_fields['billing_period']	    = wcs_cart_pluck( $recurring_cart, 'subscription_period' );
	$subscription_fields['billing_interval']    = wcs_cart_pluck( $recurring_cart, 'subscription_period_interval' );
	$subscription_fields['sign_up_fee']	    = wcs_cart_pluck( $recurring_cart, 'subscription_sign_up_fee' );

	$subscription_fields['next_payment_date_timestamp_utc'] = strtotime($recurring_cart->next_payment_date);

	$loaded_product['wpo_subscription_fields'] = $subscription_fields;

	return $loaded_product;
    }

    public function get_item_by_product($item, $item_data, $product) {

	if ( empty( $item['is_subscribed'] ) ) {
	    return $item;
	}

	$subscription_fields = array();

	$subscription_fields['billing_period']	    = WC_Subscriptions_Product::get_period($product);
	$subscription_fields['billing_interval']    = WC_Subscriptions_Product::get_interval($product);
	$subscription_fields['sign_up_fee']         = WC_Subscriptions_Product::get_sign_up_fee($product);

	$subscription_fields['next_payment_date_timestamp_utc'] = strtotime(WC_Subscriptions_Product::get_first_renewal_payment_date( $product, gmdate( 'Y-m-d H:i:s' ) ));

	$item['wpo_subscription_fields'] = $subscription_fields;

	return $item;
    }

    public function add_fields_to_cart_product_meta($cart_item) {

	if ( ! empty( $cart_item['wpo_subscription_fields'] ) ) {

	    if ( ! empty( $cart_item['wpo_subscription_fields']['billing_period_changed_manually'] ) || ! empty( $cart_item['wpo_subscription_fields']['billing_interval_changed_manually'] ) ) {
		$cart_item['data']->add_meta_data('_subscription_period', $cart_item['wpo_subscription_fields']['billing_period'], true);
		$cart_item['data']->add_meta_data('_subscription_period_interval', $cart_item['wpo_subscription_fields']['billing_interval'], true);
	    }

	    if ( ! empty( $cart_item['wpo_subscription_fields']['sign_up_fee_changed_manually'] ) ) {
		$cart_item['data']->add_meta_data('_subscription_sign_up_fee', $cart_item['wpo_subscription_fields']['sign_up_fee'], true);
	    }

	}

	return $cart_item;
    }

    public function get_load_order_loaded_product($loaded_product, $order_item, $order, $is_edit) {

	if ( empty( $loaded_product['is_subscribed'] ) || ! function_exists('wcs_get_subscriptions_for_order') ) {
	    return $loaded_product;
	}

	$order_items_product_id  = wcs_get_canonical_product_id( $order_item );
	$order_item_subscription = null;

	foreach ( wcs_get_subscriptions_for_order( $order, array( 'order_type' => 'parent' ) ) as $subscription ) {
	    // We want the billing period for a specific item (so we need to find if this subscription contains that item)
	    foreach ( $subscription->get_items() as $line_item ) {
		    if ( wcs_get_canonical_product_id( $line_item ) == $order_items_product_id ) {
			    $order_item_subscription = $subscription;
			    break 2;
		    }
	    }
	}

	if ( empty( $order_item_subscription ) ) {
	    return $loaded_product;
	}

	$loaded_product['wpo_subscription_fields'] = array(
	    'billing_period'			=> $order_item_subscription->get_billing_period(),
	    'billing_period_changed_manually'   => true,
	    'billing_interval'			=> $order_item_subscription->get_billing_interval(),
	    'billing_interval_changed_manually' => true,
	    'sign_up_fee'			=> $order_item_subscription->get_sign_up_fee(),
	    'sign_up_fee_changed_manually'	=> true,
	    'next_payment_date_timestamp_utc'   => $order_item_subscription->get_time('next_payment'),
	    'next_payment_date_timestamp_utc_changed_manually' => $is_edit,
	);

	return $loaded_product;
    }

}


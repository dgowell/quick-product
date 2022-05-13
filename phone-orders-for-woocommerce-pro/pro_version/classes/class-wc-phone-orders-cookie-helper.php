<?php
defined( 'ABSPATH' ) || exit;

/**
 * Session handler class.
 */
class WC_Phone_Orders_Cookie_Helper {

    /**
     * @param $customer_id int
     *
     * @return boolean|WP_Error
     */
    public static function set_payment_cookie( $customer_id, $cart_id = null, $current_user_id = null, array $referrer = array() ) {
	    $expiration = time() + apply_filters('wpo_payment_cookie_timeout_sec', 172800); // 48 hours

	    $cookie          = wp_generate_auth_cookie( $customer_id, $expiration, 'original_user' );
	    $customer_result = setcookie( WC_PHONE_CUSTOMER_COOKIE, json_encode( $cookie ), $expiration, COOKIEPATH );
	    if ( ! $customer_result ) {
		    return new WP_Error( 'cookie_is_not_set', __('Customer cookie set error', 'phone-orders-for-woocommerce') );
	    }

	    if ($cart_id) {
		$cart_result = setcookie( WC_PHONE_CART_COOKIE, base64_encode(json_encode( array( 'cart_id' => $cart_id ) ) ), $expiration, COOKIEPATH );
		if ( ! $cart_result ) {
			setcookie( WC_PHONE_CUSTOMER_COOKIE, json_encode( $cookie ), time() - 3600, COOKIEPATH );

			return new WP_Error( 'cookie_is_not_set', __('Current user cookie set error', 'phone-orders-for-woocommerce') );
		}
	    }

	    if ($current_user_id) {
		$cookie              = wp_generate_auth_cookie( $current_user_id, $expiration, 'original_user' );
		$current_user_result = setcookie( WC_PHONE_ADMIN_COOKIE, json_encode( $cookie ), $expiration, COOKIEPATH );
		if ( ! $current_user_result ) {
			setcookie( WC_PHONE_CUSTOMER_COOKIE, json_encode( $cookie ), time() - 3600, COOKIEPATH );
			setcookie( WC_PHONE_CART_COOKIE, json_encode( $cookie ), time() - 3600, COOKIEPATH );

			return new WP_Error( 'cookie_is_not_set', __('Current user cookie set error', 'phone-orders-for-woocommerce') );
		}
	    }

	    if ($referrer) {

		$current_user_result = setcookie( WC_PHONE_ADMIN_REFERRER_COOKIE, base64_encode(json_encode( $referrer )), $expiration, COOKIEPATH );

		if ( ! $current_user_result ) {
		    return new WP_Error( 'cookie_is_not_set', __('Current user referrer cookie set error', 'phone-orders-for-woocommerce') );
		}
	    }


	    return true;
    }

}

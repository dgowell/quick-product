<?php
defined( 'ABSPATH' ) || exit;

/**
 * Session handler class.
 */
class WC_Phone_Orders_Session_Handler_Base extends WC_Session_Handler {
	protected $original_customer;

	protected $customer_session_keys = array(
		'id',
		'date_modified',
		'billing_postcode',
		'billing_city',
		'billing_address_1',
		'billing_address',
		'billing_address_2',
		'billing_state',
		'billing_country',
		'shipping_postcode',
		'shipping_city',
		'shipping_address_1',
		'shipping_address',
		'shipping_address_2',
		'shipping_state',
		'shipping_country',
		'is_vat_exempt',
		'calculated_shipping',
		'billing_first_name',
		'billing_last_name',
		'billing_company',
		'billing_phone',
		'billing_email',
		'shipping_first_name',
		'shipping_last_name',
		'shipping_company',
	);

	/**
	 * Constructor for the session class.
	 */
	public function __construct() {
//		$this->_cookie = apply_filters( 'woocommerce_cookie', 'wp_woocommerce_session_' . COOKIEHASH );
		$this->_table  = $GLOBALS['wpdb']->prefix . 'woocommerce_sessions';

		// Try to get temporary customer id from admin cookie ONLY when shop as customer.
		// Otherwise, "WC_Phone_Orders_Switch_User::get_customer_id()" returns null so
		// we have to call "set_customer_id" again before init() to replace generated one with correct
		$fronted_customer_id_from_admin_session = WC_Phone_Orders_Switch_User::get_customer_id();
		if ( $fronted_customer_id_from_admin_session ) {
			$this->set_customer_id( $fronted_customer_id_from_admin_session );
		}
	}

	public function init() {
		parent::init();
		remove_action( 'woocommerce_set_cart_cookies', array( $this, 'set_customer_session_cookie' ), 10 );
		$this->original_customer = $this->get( 'customer' );
	}

	public function generate_customer_id() {
		$customer_id = $this->_customer_id;
		if ( ! $customer_id ) {
			require_once ABSPATH . 'wp-includes/class-phpass.php';
			$hasher      = new PasswordHash( 8, false );
			$customer_id = md5( $hasher->get_random_bytes( 32 ) );
		}

		return $customer_id;
	}

	public function set_customer_id( $customer_id ) {
		if ( $customer_id ) {
			$this->_customer_id = $customer_id;
			$this->_has_cookie         = true;
		} else {
			$this->_customer_id = $this->generate_customer_id();
		}
	}

	/**
	 * @param WC_Customer $customer Customer object.
	 */
	public function set_original_customer( $customer ) {
		$data = array();
		foreach ( $this->customer_session_keys as $session_key ) {
			$function_key = $session_key;
			if ( 'billing_' === substr( $session_key, 0, 8 ) ) {
				$session_key = str_replace( 'billing_', '', $session_key );
			}
			$data[ $session_key ] = (string) $customer->{"get_$function_key"}( 'edit' );
		}

		$this->original_customer = $data;
	}

}

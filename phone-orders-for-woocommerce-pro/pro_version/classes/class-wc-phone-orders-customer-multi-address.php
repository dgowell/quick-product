<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_Phone_Orders_Customer_Multi_Address {

    const KEY = '_wcmca_additional_addresses';

    const ADDRESS_TYPE_KEY = 'type';
    const ADDRESS_ID_KEY = 'address_id';
    const ADDRESS_INTERNAL_NAME_KEY = 'address_internal_name';

    public static function get_address_list($user_id, $billing_email, $address_type) {

	$result = self::get_user_multi_addresses($user_id);

	$result = array_filter($result, function ($v) use ($address_type) {
	    return $v['type'] === $address_type;
	});

    if ( empty( $result ) ) {
	    $result = self::load_addresses_from_previous_orders( $user_id, $billing_email, $address_type );
    }

	usort($result, function ($a, $b) {

	    $key = 'address_internal_name';

	    if ($a[$key] === $b[$key]) {
		return 0;
	    }

	    return $a[$key] > $b[$key] ? 1 : -1;
	});

	$formatted_result = array();

	$default_address = self::get_default_address($address_type);

	    // multiaddress internal fields
	    $default_address = array_merge( array(
		    'type'                  => "",
		    'user_id'               => "",
		    'address_id'            => "",
		    'address_internal_name' => "",
	    ), $default_address );

	foreach ($result as $address) {

	    $_address = $default_address;

	    foreach ($address as $key => $value) {
		$_key	         = str_replace($address_type . '_', '', $key);
		    if ( isset( $_address[ $_key ] ) ) {
			    $_address[ $_key ] = $value;
		    }
	    }

	    $formatted_result[] = $_address;
	}

	return $formatted_result;
    }

	public static function generate_address_id() {
		return uniqid();
	}

    public static function save_new_address($user_id, $address_type, array $address) {

	$addresses  = self::get_user_multi_addresses($user_id);
	$address_id = self::generate_address_id();

	$default_address = self::get_default_address($address_type);

	$formatted_address = array();

	foreach ($default_address as $key => $value) {
	    $formatted_address[$address_type.'_'.$key] = isset($address[$key]) ? $address[$key] : $value;
	}

	$formatted_address = array_merge(array(
		self::ADDRESS_TYPE_KEY          => $address_type,
		self::ADDRESS_ID_KEY            => $address_id,
		self::ADDRESS_INTERNAL_NAME_KEY => self::create_internal_name( array_merge( $default_address, $address ) ),
	), $formatted_address);

	$_address = array();

	foreach ($formatted_address as $key => $value) {
	    $_address[str_replace($address_type . '_', '', $key)] = $value;
	}

	foreach ($addresses as $address) {
	    if ($address['address_internal_name'] === $formatted_address['address_internal_name']) {
		return $_address;
	    }
	}

	$addresses[] = $formatted_address;

	self::update_user_multi_addresses($user_id, $addresses);

	return $_address;
    }

    public static function update_address($user_id, $address_type, $address_internal_name, array $address) {

	$addresses = self::get_user_multi_addresses($user_id);

	$default_address = self::get_default_address($address_type);

	$formatted_address = array();

	foreach ($default_address as $key => $value) {
	    $formatted_address[$address_type.'_'.$key] = isset($address[$key]) ? $address[$key] : $value;
	}

	$formatted_address = array_merge(array(
	    'address_internal_name' => self::create_internal_name(array_merge($default_address, $address)),
	), $formatted_address);

	$updated_address = null;

	foreach ($addresses as &$_address) {

	    if ($_address['type'] !== $address_type || $_address['address_internal_name'] != $address_internal_name) {
		continue;
	    }

	    $_address	     = array_merge($_address, $formatted_address);
	    $updated_address = $_address;
	}

	self::update_user_multi_addresses($user_id, $addresses);

	$_address = array();

	if (is_array($updated_address)) {
	    foreach ($updated_address as $key => $value) {
		$_address[str_replace($address_type . '_', '', $key)] = $value;
	    }
	}

	return $_address;
    }

    public static function delete_address($user_id, $address_type, $address_internal_name) {

	$addresses = self::get_user_multi_addresses($user_id);

	$addresses = array_filter($addresses, function ($v) use ($address_internal_name, $address_type) {
	    return $v['type'] !== $address_type || $v['address_internal_name'] != $address_internal_name;
	});

	self::update_user_multi_addresses($user_id, $addresses);
    }

    protected static function get_user_multi_addresses($user_id) {
	    if ( ! $user_id ) {
		    return array();
	    }

	$result = get_user_meta($user_id, self::KEY, true);

	return is_array($result) ? $result : array();
    }

    protected static function get_default_address($type) {

	$default = array(
	    'first_name' => '',
	    'last_name'	 => '',
	    'company'	 => '',
	    'state'	 => '',
	    'country'	 => '',
	    'address_1'	 => '',
	    'address_2'	 => '',
	    'city'	 => '',
	    'postcode'	 => '',
	);

	if ($type === 'billing') {
	    $default = array_merge($default, array(
		'phone' => '',
		'email' => '',
	    ));
	}

	return $default;
    }

    protected static function create_internal_name(array $address) {
	return implode(' ', array_filter([
	    implode(' ', array_filter([
		$address['first_name'],
		$address['last_name'],
	    ])),
	    isset($address['email']) ? $address['email'] : '',
	    isset($address['phone']) ? $address['phone'] : '',
	    $address['company'],
	    implode(' ', array_filter([
		$address['address_1'],
		$address['address_2'],
	    ])),
	    $address['city'],
	    $address['state'],
	    $address['country'],
	    $address['postcode'],
	], function ($v) {
	    return $v !== '';
	}));
    }

    protected static function update_user_multi_addresses($user_id, array $addresses) {
	update_user_meta($user_id, self::KEY, $addresses);
    }

	protected static function load_addresses_from_previous_orders( $user_id, $billing_email, $address_type, $limit = 10 ) {
		$list = array();

		$args = array( 'limit' => $limit );
		if ( $user_id ) {
			$args['customer'] = $user_id;
		} elseif ( $billing_email && is_email( $billing_email ) ) {
			$args['billing_email'] = $billing_email;
			$args['customer']      = 0;
		} else {
			return array();
		}

		$default_address = self::get_default_address( $address_type );
		$orders          = wc_get_orders( $args );

		foreach ( $orders as $order ) {
			$address = $order->get_address( $address_type );
			if ( empty( $address ) ) {
				continue;
			}

			$hash = md5( json_encode( $address ) );

			$address = array_merge( array(
				self::ADDRESS_TYPE_KEY          => $address_type,
				self::ADDRESS_ID_KEY            => self::generate_address_id(),
				self::ADDRESS_INTERNAL_NAME_KEY => self::create_internal_name( array_merge( $default_address, $address ) ),
			), $address );

			$list[ $hash ] = $address;
		}

		return apply_filters( "wpo_load_addresses_from_previous_orders", array_values( $list ), $user_id, $billing_email, $address_type, $limit );
	}

}
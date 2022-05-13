<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_Phone_Order_Shipping_Package_Mod_Strategy_Pro extends WC_Phone_Order_Shipping_Package_Mod_Strategy implements WC_Phone_Order_Shipping_Package_Mod_Strategy_Interface {
	public function set_package_hashes( $packages ) {
		$packages = parent::set_package_hashes( $packages );

		// shipping method is included only when used
		if ( class_exists( "WC_Phone_Shipping_Method_Custom_Price" ) ) {
			foreach ( $packages as &$package ) {
				$package_hash         = WC_Phone_Orders_Cart_Shipping_Processor::calculate_package_hash( $package );
				$cost_key             = WC_Phone_Shipping_Method_Custom_Price::PACKAGE_PRICE_KEY;
				$cost                 = isset( $this->tmp_prices_shipping[ $package_hash ] ) ? $this->tmp_prices_shipping[ $package_hash ] : null;
				$package[ $cost_key ] = $cost;
			}
		}

		return $packages;
	}
}
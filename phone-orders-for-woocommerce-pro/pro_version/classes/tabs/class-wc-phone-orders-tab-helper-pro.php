<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


class WC_Phone_Orders_Tabs_Helper_Pro {

	public static function init_tabs( $tabs ) {
		foreach ( glob( self::get_classes_path_pro() . 'class-*.php' ) as $filename ) {
			include_once $filename;
		}

		$tabs['add-order'] = new WC_Phone_Orders_Add_Order_Page_Pro();

		if( is_super_admin() ) {
			$tabs['settings']  = new WC_Phone_Orders_Settings_Page_Pro();
			$tabs['license']   = new WC_Phone_Orders_License_Page_Pro();
		}

		$settings_option_handler = WC_Phone_Orders_Settings::getInstance();

		if ( ! is_super_admin() && $settings_option_handler->get_option('hide_tabs') ) {
		    $tabs = array(
			'add-order' => $tabs['add-order'],
		    );
		}

		return $tabs;
	}

	public static function get_classes_path_pro() {
		return WC_PHONE_ORDERS_PLUGIN_PATH . 'pro_version/classes/tabs/';
	}

	public static function get_views_path_pro() {
		return WC_PHONE_ORDERS_PLUGIN_PATH . 'pro_version/views/';
	}
}
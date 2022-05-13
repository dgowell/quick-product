<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_Report_Phone_Order_Report_Register {
	public static function init() {
		add_filter( 'woocommerce_admin_reports', function ( $reports ) {
			$reports['orders']['reports']['phone_orders_sales_by_date'] = array(
				'title'       => __( 'Sales by Phone', 'phone-orders-for-woocommerce' ),
				'description' => '',
				'hide_title'  => true,
				'callback'    => array( __CLASS__, 'get_report' ),
			);

			$reports['orders']['reports']['phone_orders_sales_by_date_exclude'] = array(
				'title'       => __( 'Sales Online', 'phone-orders-for-woocommerce' ),
				'description' => '',
				'hide_title'  => true,
				'callback'    => array( __CLASS__, 'get_report' ),
			);

			$reports['orders']['reports']['phone_orders_sales_by_date_per_creator'] = array(
				'title'       => __( 'Sales by Phone, per creator', 'phone-orders-for-woocommerce' ),
				'description' => '',
				'hide_title'  => true,
				'callback'    => array( __CLASS__, 'get_report' ),
			);

			return $reports;
		} );
	}

	public static function get_report( $name ) {
		$name  = sanitize_title( str_replace( '_', '-', $name ) );
		$class = 'WC_Report_' . str_replace( '-', '_', $name );

		include_once WC()->plugin_path() . '/includes/admin/reports/class-wc-report-sales-by-date.php';
		include_once "reports/class-wc-{$name}-report.php";

		if ( ! class_exists( $class ) ) {
			return;
		}

		$report = new $class();
		$report->output_report();
	}


}
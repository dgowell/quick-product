<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_Report_Phone_Orders_Sales_By_Date extends WC_Report_Sales_By_Date {
	public function get_order_report_data( $args = array() ) {
		add_filter( 'woocommerce_reports_get_order_report_query', array( $this, 'apply_meta_query' ), 10 );
		$result = parent::get_order_report_data( $args );
		remove_filter( 'woocommerce_reports_get_order_report_query', array( $this, 'apply_meta_query' ), 10 );

		return $result;
	}

	public function apply_meta_query( $query ) {
		$meta_query     = array(
			'relation' => 'AND',
			array(
				'key'     => WC_Phone_Orders_Loader::$meta_key_order_creator,
				'compare' => 'EXISTS',
			)
		);
		$meta_query_obj = new WP_Meta_Query( $meta_query );
		$sql            = $meta_query_obj->get_sql( 'post', 'posts', 'ID', $query );
		$query['join']  .= $sql['join'];
		$query['where'] .= $sql['where'];

		return $query;
	}
}
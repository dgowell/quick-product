<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class WC_Report_Phone_Orders_Sales_By_Date_Per_Creator extends WC_Admin_Report {

	protected $order_creator_meta_key;

	public function __construct() {
	    $this->order_creator_meta_key = WC_Phone_Orders_Loader::$meta_key_order_creator;
	}

	public function get_chart_legend() {
		return array();
	}

	/**
	 * Output an export link.
	 */
	public function get_export_button() {

		$current_range = ! empty( $_GET['range'] ) ? sanitize_text_field( wp_unslash( $_GET['range'] ) ) : 'last_month';
		?>
		<a
			href="#"
			download="report-<?php echo esc_attr( $current_range ); ?>-<?php echo esc_attr( date_i18n( 'Y-m-d', current_time( 'timestamp' ) ) ); ?>.csv"
			class="export_csv"
			data-export="table"
		>
			<?php esc_html_e( 'Export CSV', 'woocommerce' ); ?>
		</a>
		<?php
	}

	/**
	 * Output the report.
	 */
	public function output_report() {

		$ranges = array(
			'year'       => __( 'Year', 'woocommerce' ),
			'last_month' => __( 'Last month', 'woocommerce' ),
			'month'      => __( 'This month', 'woocommerce' ),
			'7day'       => __( 'Last 7 days', 'woocommerce' ),
		);

		$current_range = ! empty( $_GET['range'] ) ? sanitize_text_field( wp_unslash( $_GET['range'] ) ) : '7day';

		if ( ! in_array( $current_range, array( 'custom', 'year', 'last_month', 'month', '7day' ) ) ) {
			$current_range = '7day';
		}

		$this->check_current_range_nonce( $current_range );
		$this->calculate_current_range( $current_range );

		$hide_sidebar = true;

		include WC()->plugin_path() . '/includes/admin/views/html-report-by-date.php';
	}

	/**
	 * Get the main chart.
	 */
	public function get_main_chart() {

		global $wpdb;

		$refund_query_data = array(
		    $this->order_creator_meta_key => array(
			'type'            => 'parent_meta',
			'function'        => '',
			'name'            => 'creator_id',
		    ),
		    '_refund_amount'      => array(
			'type'     => 'meta',
			'function' => '',
			'name'     => 'total_refund',
		    ),
		);

		$refund_query_where = array(
		    array(
			'key'      => "parent_meta_{$this->order_creator_meta_key}.meta_value",
			'value'    => '',
			'operator' => '!=',
		    ),
		);

		// We exclude on-hold orders as they are still pending payment.
		$orders = (array)$this->get_order_report_data(
		    array(
			'data'         => array(
			    $this->order_creator_meta_key => array(
				'type'            => 'meta',
				'function'        => '',
				'name'            => 'creator_id',
			    ),
			    'ID' => array(
				'type'     => 'post_data',
				'function' => 'COUNT',
				'name'     => 'total_orders',
				'distinct' => true,
			    ),
			    '_order_total'        => array(
				'type'     => 'meta',
				'function' => 'SUM',
				'name'     => 'total_amount',
			    ),
			),
			'where'        => array(
			    array(
				'key'      => "meta_{$this->order_creator_meta_key}.meta_value",
				'value'    => '',
				'operator' => '!=',
			    ),
			),
			'group_by'     => 'creator_id',
			'order_by'     => 'posts.post_date ASC',
			'query_type'   => 'get_results',
			'filter_range' => true,
			'order_types'  => wc_get_order_types( 'sales-reports' ),
			'order_status' => array( 'completed', 'processing', 'refunded' ),
		    )
		);

		$partial_refunds = (array)$this->get_order_report_data(
		    array(
			'data'                => $refund_query_data,
			'where'               => $refund_query_where,
			'group_by'	      => 'creator_id',
			'order_by'            => 'posts.post_date ASC',
			'query_type'          => 'get_results',
			'filter_range'        => true,
			'order_types'         => array( 'shop_order_refund' ),
			'parent_order_status' => array( 'completed', 'processing' ), // Partial refunds inside refunded orders should be ignored.
		    )
		);

		$full_refunds = (array)$this->get_order_report_data(
		    array(
			'data'                => $refund_query_data,
			'where'               => $refund_query_where,
			'group_by'	      => 'creator_id',
			'order_by'            => 'posts.post_date ASC',
			'query_type'          => 'get_results',
			'filter_range'        => true,
			'order_types'         => array( 'shop_order_refund' ),
			'parent_order_status' => array( 'refunded' ),
		    )
		);

		// Merge.
		$rows = array();

		//to fix object cache modify
		$_partial_refunds = array();
		foreach ($partial_refunds as $row) {
		    $_partial_refunds[$row->creator_id] = (object)(array)$row;
		}

		$_full_refunds = array();
		foreach ($full_refunds as $row) {
		    $_full_refunds[$row->creator_id] = (object)(array)$row;
		}

		$_orders = array();
		foreach ($orders as $row) {
		    $_orders[$row->creator_id] = (object)(array)$row;
		}

		foreach ( $_orders as $row ) {

		    if ( isset( $_partial_refunds[$row->creator_id] ) ) {
			$row->total_amount -= $_partial_refunds[$row->creator_id]->total_refund;
		    }

		    if ( isset( $_full_refunds[$row->creator_id] ) ) {
			$row->total_amount -= $_full_refunds[$row->creator_id]->total_refund;
		    }

		    $rows[$row->creator_id] = $row;
		}

		uasort($rows, function ($a, $b) {
		    if ($a->total_amount === $b->total_amount) {
			return 0;
		    }
		    return $a->total_amount > $b->total_amount ? -1 : 1;
		});

		?>
		<table class="widefat">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Display Name', 'phone-orders-for-woocommerce' ); ?></th>
					<th class="total_row"><?php esc_html_e( 'Total sales', 'phone-orders-for-woocommerce' ); ?> <?php echo wc_help_tip( __( 'This is the count of the orders.', 'phone-orders-for-woocommerce' ) ); ?></th>
					<th class="total_row"><?php esc_html_e( 'Total amount', 'phone-orders-for-woocommerce' ); ?> <?php echo wc_help_tip( __( 'This is the sum of the orders.', 'phone-orders-for-woocommerce' ) ); ?></th>
				</tr>
			</thead>
			<?php if ( ! empty( $rows ) ) : ?>
				<tbody>
					<?php
					foreach ( $rows as $user_id => $row ) {
						$user = get_user_by('id', $user_id);
						?>
						<tr>
							<th scope="row"><?php echo wp_kses_post( $user->display_name ); ?></th>
							<td class="total_row"><?php echo esc_html( intval( $row->total_orders ) ); ?></td>
							<td class="total_row"><?php echo wc_price( floatval( $row->total_amount ) ); // phpcs:ignore ?></td>
						</tr>
						<?php
					}
					?>
				</tbody>
				<tfoot>
				    <tr>
					    <th scope="row"><?php esc_html_e( 'Total', 'phone-orders-for-woocommerce' ); ?></th>
					    <th class="total_row"><?php echo array_sum( wp_list_pluck( (array) $rows, 'total_orders' ) ); ?></th>
					    <th class="total_row"><strong><?php echo wc_price( array_sum( wp_list_pluck( (array) $rows, 'total_amount' ) ) ); ?></strong></th>
				    </tr>
				</tfoot>
			<?php else : ?>
				<tbody>
					<tr>
						<td><?php esc_html_e( 'No orders found in this period', 'phone-orders-for-woocommerce' ); ?></td>
					</tr>
				</tbody>
			<?php endif; ?>
		</table>
		<?php
	}
}
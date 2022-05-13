<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_Phone_Orders_Gateway extends WC_Payment_Gateway {
	const ENABLE_AT_ADMIN_OPTION = 'enable_at_admin_context';
	
	protected $enable_at_admin_context;
	protected $enabled_at_runtime = false;

	public function __construct() {
		// Setup general properties.
		$this->setup_properties();

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Get settings.
		$this->title              = $this->get_option( 'title' );
		$this->description        = $this->get_option( 'description' );

		$this->enable_at_admin_context = $this->get_option( self::ENABLE_AT_ADMIN_OPTION, 'yes' ) === 'yes';

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
	}

	protected function setup_properties() {
		$this->id                 = 'po';
		$this->icon               = apply_filters( 'wpo_payment_method_icon', '' );
		$this->method_title       = __( 'Phone orders', 'phone-orders-for-woocommerce' );
		$this->method_description = __( 'This method will be shown only for orders created using plugin "Phone Orders"', 'phone-orders-for-woocommerce' );
		$this->has_fields         = false;
		$this->supports           = array( 'products', 'refunds' );
	}

	public function disable() {
		$this->enabled_at_runtime = false;
	}

	public function enable() {
		$this->enabled_at_runtime = true;
	}

	/**
	 * @return bool
	 */
	public function is_enable_at_admin() {
		return $this->get_option( self::ENABLE_AT_ADMIN_OPTION ) === 'yes';
	}

	public function init_form_fields() {
		$statuses = array();
		foreach ( wc_get_is_paid_statuses() as $status ) {
			$statuses[ $status ] = wc_get_order_status_name( $status );
		}
		
		$this->form_fields = array(
			'enabled'                       => array(
				'title'       => __( 'Enable/Disable', 'phone-orders-for-woocommerce' ),
				'label'       => __( 'Enable method', 'phone-orders-for-woocommerce' ),
				'type'        => 'checkbox',
				'description' => '',
				'default'     => 'no',
			),
			'title'                         => array(
				'title'       => __( 'Title', 'phone-orders-for-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Payment method description that the customer will see on your checkout.', 'phone-orders-for-woocommerce' ),
				'default'     => __( 'Phone orders', 'phone-orders-for-woocommerce' ),
				'desc_tip'    => true,
			),
			self::ENABLE_AT_ADMIN_OPTION => array(
				'title'   => __( 'Enable at admin', 'phone-orders-for-woocommerce' ),
				'label'   => __( 'Show method if use button "Pay as Customer"', 'phone-orders-for-woocommerce' ),
				'type'    => 'checkbox',
				'default' => 'yes',
			),
			'payed_order_status'            => array(
				'title'             => __( 'Order status after payment', 'phone-orders-for-woocommerce' ),
				'type'              => 'select',
				'class'             => 'wc-enhanced-select',
				'css'               => 'width: 400px;',
				'default'           => 'processing',
				'description'       => __( '', 'phone-orders-for-woocommerce' ),
				'options'           => $statuses,
				'desc_tip'          => true,
				'custom_attributes' => array(
					'data-placeholder' => __( 'Select order status', 'phone-orders-for-woocommerce' ),
				),
			),
		);
	}

	/**
	 * @param int $order_id Order ID.
	 * @return array
	 */
	public function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );

		$order->payment_complete();

		$order->update_status( $this->get_option( 'payed_order_status' ) );

		// Remove cart.
		WC()->cart->empty_cart();

		// Return thankyou redirect.
		return array(
			'result'   => 'success',
			'redirect' => $this->get_return_url( $order ),
		);
	}

	public function process_refund( $order_id, $amount = null, $reason = '' ) {
		return true;
	}

	/**
	 * Check if the gateway is available for use.
	 *
	 * @return bool
	 */
	public function is_available() {
		return parent::is_available() && $this->enabled_at_runtime;
	}
}
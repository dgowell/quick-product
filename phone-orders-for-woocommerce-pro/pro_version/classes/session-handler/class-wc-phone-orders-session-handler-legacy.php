<?php
defined( 'ABSPATH' ) || exit;

/**
 * Session handler class.
 */
class WC_Phone_Orders_Session_Handler_Legacy extends WC_Phone_Orders_Session_Handler_Base {

	public function save_data() {
		// prevent to change customer by WC
		// change customer only through 'set_original_customer' method
		$this->set( 'customer', $this->original_customer );
		parent::save_data();
	}

}

<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


class WC_Phone_Orders_Helper_Pro {

	public function __construct() {
		if ( class_exists( 'WC_Phone_Orders_Settings' ) ) {
			$option_handler = WC_Phone_Orders_Settings::getInstance();
			$option_handler->add_default_settings( $this->get_default_settings() );
			$option_handler->add_definitions( $this->set_definitions() );
		}
	}

	public function get_default_settings() {
		$default_settings_pro = array(

			'hide_tabs'				      => false,

			'frontend_page'				      => false,
			'frontend_page_url'			      => get_home_url(null, 'phone-orders-frontend-page'),
			'frontend_hide_theme_header'		      => false,
			'frontend_hide_theme_footer'		      => false,
			'allow_to_configure_product'		      => true,
			'allow_to_use_minified_page_configure_product' => false,
			'allow_to_add_products_from_shop_page' => false,

			'cache_customers_timeout'                     => 0,
			'cache_customers_session_key'                 => 'no-cache',
			'search_customer_in_orders'                   => false,
			'search_all_customer_fields'                  => false,
			'number_of_customers_to_show'                 => 20,
			'default_customer_id'                         => 0,
			'update_customers_profile_after_create_order' => false,
			'hide_tax_exempt'			      => false,
			'hide_shipping_section'                       => false,
			'do_not_submit_on_enter_last_field'           => false,
			'allow_multiple_addresses'		      => false,
			'use_shipping_phone'			      => false,
			'support_field_vat'			      => false,
			'update_wp_user_first_last_name'	      => false,
			'show_order_history_customer'		      => true,
			'use_payment_delivery_last_order'	      => false,
			'limit_orders_of_search_customer'	      => 0,
			'customer_hide_fields'		              => array(),
			'customer_required_fields'		      => array('first_name','last_name','email'),
			'customer_show_role_field'		      => false,
			'customer_show_language_field'		      => false,

			'disable_creating_customers'                  => false,
			'newcustomer_show_password_field'             => false,
			'newcustomer_show_username_field'             => false,
			'newcustomer_show_role_field'                 => false,
			'newcustomer_show_language_field'             => false,
			'newcustomer_hide_first_name'				  => false,
			'newcustomer_hide_last_name'				  => false,
			'newcustomer_hide_email'                      => false,
			'newcustomer_hide_company'                    => false,
			'newcustomer_hide_address_1'                  => false,
			'newcustomer_hide_address_2'                  => false,
			'newcustomer_hide_city'                       => false,
			'newcustomer_hide_postcode'                   => false,
			'newcustomer_hide_country'                    => false,
			'newcustomer_hide_state'                      => false,
			'default_city'                                => '',
			'default_postcode'                            => '',
			'default_country'                             => '',
			'default_state'                               => '',
			'default_role'                                => 'customer',
			'disable_new_user_email'                      => false,
			'dont_fill_shipping_address_for_new_customer' => false,
			'allowed_roles_new_customer'		      => array(),
			'create_customer_base_on_existing_order'      => false,
			'newcustomer_required_fields'		      => array('first_name','last_name','email'),

			'cache_products_timeout'		              => 0,
			'cache_products_session_key'		          => 'no-cache',
			'search_by_sku'                               => false,
			'verbose_search'                              => false,
			'search_by_cat_and_tag'                       => false,
			'number_of_products_to_show'                  => 25,
			'sort_products_by_relevancy'		      => true,
			'autocomplete_product_hide_image'             => false,
			'autocomplete_product_hide_status'            => false,
			'autocomplete_product_hide_qty'               => false,
			'autocomplete_product_hide_price'             => false,
			'autocomplete_product_hide_sku'               => false,
			'autocomplete_product_hide_name'              => false,
			'show_long_attribute_names'                   => true,
			'allow_duplicate_products'                    => false,
			'hide_products_with_no_price'                 => true,
			'sale_backorder_product'                      => false,
			'sell_disable_variation'                      => false,
			'allow_to_rename_cart_items'				  => false,
			'add_product_to_top_of_the_cart'              => false,
			'is_readonly_price'                           => false,
			'item_price_precision'                        => 2,
			'item_default_selected'                       => array(),
			'dont_refresh_cart_item_item_meta'            => true,
			'disable_edit_meta'                           => false,
			'hide_item_meta'                              => false,
			'item_default_search_result'		      => array(),
			'display_search_result_as_grid'		      => true,
			'show_qty_input_advanced_search'              => false,
			'show_price_input_advanced_search'            => false,
			'action_click_on_title_product_item_in_cart'  => 'edit_product',
			'action_click_on_title_product_item_in_search_products'  => 'add_product_to_cart',
			'barcode_mode'				      => false,

			'is_featured'                                 => 'no',

			'disable_adding_products'                     => false,
			'new_product_ask_sku'                         => false,
			'product_visibility'                          => 'hidden',
			'new_product_ask_tax_class'                   => false,
			'item_tax_class'                              => '',
			'edit_created_product_in_new_tab'             => false,
			'show_product_category'			      => false,
			'new_product_show_weight'		      => false,
			'new_product_show_length'		      => false,
			'new_product_show_width'		      => false,
			'new_product_show_height'		      => false,
			'new_product_create_woocommerce_product'      => 'dont_show_checkbox__create_product',
            'create_private_product'              => false,

			'hide_add_fee'                                => false,
			'default_fee_name'                            => 'Fee',
			'default_fee_amount'                          => 0,
			'fee_tax_class'                               => '',
			'allow_to_use_zero_amount'		      => false,

			'hide_add_discount'                           => false,
			'allow_to_edit_coupon_name'		      => false,

			'cache_orders_timeout'		                  => 0,
			'cache_orders_session_key'		              => 'no-cache',
			'button_for_find_orders'                      => true,
			'set_current_price_in_copied_order'           => true,
			'set_current_price_shipping_in_copied_order'  => false,
			'hide_find_orders'                            => false,
			'seek_only_orders_with_statuses'              => array(),
			'dont_allow_edit_order_have_status_list'      => array('wc-processing', 'wc-completed', 'wc-cancelled'),
			'show_orders_current_user'		      => false,

			'show_duplicate_order_button'                 => false,
			'show_edit_order_in_wc'                       => true,
			'show_creator_in_orders_list'                 => false,
			'hide_add_order_in_orders_list'		      => false,
			'show_phone_orders_in_order_page'	      => true,
			'show_order_type_filter_in_order_page'	      => false,
			'hide_button_pay_as_customer'                 => false,
			'show_button_pay'                             => false,
			'hide_button_view_order'		      => false,
			'hide_button_create_order'                    => false,
			'hide_button_put_on_hold'                     => false,
			'hide_button_full_refund'                     => false,
			'hide_button_send_invoice'                    => false,
			'show_additional_product_column'	      => false,
			'additional_product_column_title'	      => "Information",
			'show_discount_amount_in_order'               => false,
			'show_product_description'                    => false,
			'show_product_description_preview_size'       => 60,
			'show_view_invoice'	                          => false,
			'show_view_invoice_draft_orders'                  => false,
			'show_mark_as_paid'							  => false,
			'mark_as_paid_status'						  => 'wc-processing',
			'uses_empty_address_ship_different'	      => false,
			'open_popup_ship_different_address'	      => true,
			'use_english_interface'					  => false,
			'hide_private_note'			      => false,
			'hide_add_gift_card'			      => false,
			'show_cart_weight'			      => false,
			'hide_taxes_if_tax_exempt'		      => false,
			'hide_coupon_warning'			      => false,
			'hide_add_coupon'			      => false,
			'show_all_coupons_in_autocomplete'	      => false,
            'select_optimal_shipping'         => false,
			'hide_add_shipping'			      => false,

			'customer_custom_fields'                      => "",
			'order_custom_fields'                         => "",
			'replace_order_with_customer_custom_fields'   => true,
			'customer_custom_fields_header_text'          => __('Custom fields', 'phone-orders-for-woocommerce'),
			'add_formatted_custom_fields'                 => false,
			'customer_custom_fields_header_text_at_top'   => __('Custom fields', 'phone-orders-for-woocommerce'),
			'customer_custom_fields_at_top'		      => "",
			'show_custom_fields_in_order_email'	      => false,
			'order_custom_fields_columns_by_line'	      => "",

			'show_go_to_cart_button'                      => false,
			'show_go_to_checkout_button'                  => false,
			'show_checkout_link_button'                   => false,
			'show_payment_link_button'                    => false,
			'override_customer_payment_link_in_order_page'=> true,
			'override_product_price_in_cart'              => true,
			'override_email_pay_order_link'               => false,
		);

		return $default_settings_pro;
	}

	public function set_definitions() {
		$definitions_pro = array(

			'hide_tabs'				      => FILTER_VALIDATE_BOOLEAN,

			'frontend_page'				      => FILTER_VALIDATE_BOOLEAN,
			'frontend_page_url'			      => FILTER_SANITIZE_STRING,
			'frontend_hide_theme_header'		      => FILTER_VALIDATE_BOOLEAN,
			'frontend_hide_theme_footer'		      => FILTER_VALIDATE_BOOLEAN,
			'allow_to_configure_product'		      => FILTER_VALIDATE_BOOLEAN,
			'allow_to_use_minified_page_configure_product' => FILTER_VALIDATE_BOOLEAN,
			'allow_to_add_products_from_shop_page'    => FILTER_VALIDATE_BOOLEAN,

			'cache_customers_timeout'                     => FILTER_SANITIZE_NUMBER_INT,
			'cache_customers_session_key'                 => FILTER_SANITIZE_STRING,
			'search_customer_in_orders'                   => FILTER_VALIDATE_BOOLEAN,
			'search_all_customer_fields'                  => FILTER_VALIDATE_BOOLEAN,
			'number_of_customers_to_show'                 => FILTER_SANITIZE_NUMBER_INT,
			'default_customer_id'                         => FILTER_SANITIZE_STRING,
			'update_customers_profile_after_create_order' => FILTER_VALIDATE_BOOLEAN,
			'hide_tax_exempt'			      => FILTER_VALIDATE_BOOLEAN,
			'hide_shipping_section'                       => FILTER_VALIDATE_BOOLEAN,
			'do_not_submit_on_enter_last_field'           => FILTER_VALIDATE_BOOLEAN,
			'allow_multiple_addresses'		      => FILTER_VALIDATE_BOOLEAN,
			'use_shipping_phone'			      => FILTER_VALIDATE_BOOLEAN,
			'support_field_vat'			      => FILTER_VALIDATE_BOOLEAN,
			'update_wp_user_first_last_name'	      => FILTER_VALIDATE_BOOLEAN,
			'show_order_history_customer'		      => FILTER_VALIDATE_BOOLEAN,
			'use_payment_delivery_last_order'	      => FILTER_VALIDATE_BOOLEAN,
			'limit_orders_of_search_customer'	      => FILTER_SANITIZE_NUMBER_INT,
			'customer_hide_fields'		      => array(
				'filter'  => FILTER_SANITIZE_STRING,
				'flags'   => FILTER_REQUIRE_ARRAY,
				'default' => array(),
			),
			'customer_required_fields'		      => array(
				'filter'  => FILTER_SANITIZE_STRING,
				'flags'   => FILTER_REQUIRE_ARRAY,
				'default' => array(),
			),
			'customer_show_role_field'		      => FILTER_VALIDATE_BOOLEAN,
			'customer_show_language_field'		      => FILTER_VALIDATE_BOOLEAN,

			'disable_creating_customers'                  => FILTER_VALIDATE_BOOLEAN,
			'newcustomer_show_password_field'             => FILTER_VALIDATE_BOOLEAN,
			'newcustomer_show_username_field'             => FILTER_VALIDATE_BOOLEAN,
			'newcustomer_show_role_field'                 => FILTER_VALIDATE_BOOLEAN,
			'newcustomer_show_language_field'             => FILTER_VALIDATE_BOOLEAN,
			'newcustomer_hide_first_name'				  => FILTER_VALIDATE_BOOLEAN,
			'newcustomer_hide_last_name'				  => FILTER_VALIDATE_BOOLEAN,
			'newcustomer_hide_email'                      => FILTER_VALIDATE_BOOLEAN,
			'newcustomer_hide_company'                    => FILTER_VALIDATE_BOOLEAN,
			'newcustomer_hide_address_1'                  => FILTER_VALIDATE_BOOLEAN,
			'newcustomer_hide_address_2'                  => FILTER_VALIDATE_BOOLEAN,
			'newcustomer_hide_city'                       => FILTER_VALIDATE_BOOLEAN,
			'newcustomer_hide_postcode'                   => FILTER_VALIDATE_BOOLEAN,
			'newcustomer_hide_country'                    => FILTER_VALIDATE_BOOLEAN,
			'newcustomer_hide_state'                      => FILTER_VALIDATE_BOOLEAN,
			'default_city'                                => FILTER_SANITIZE_STRING,
			'default_postcode'                            => FILTER_SANITIZE_STRING,
			'default_country'                             => FILTER_SANITIZE_STRING,
			'default_state'                               => FILTER_SANITIZE_STRING,
			'default_role'                                => FILTER_SANITIZE_STRING,
			'disable_new_user_email'                      => FILTER_VALIDATE_BOOLEAN,
			'dont_fill_shipping_address_for_new_customer' => FILTER_VALIDATE_BOOLEAN,
			'allowed_roles_new_customer'		      => array(
				'filter'  => FILTER_SANITIZE_STRING,
				'flags'   => FILTER_REQUIRE_ARRAY,
				'default' => array(),
			),
			'create_customer_base_on_existing_order'      => FILTER_VALIDATE_BOOLEAN,
			'newcustomer_required_fields'		      => array(
				'filter'  => FILTER_SANITIZE_STRING,
				'flags'   => FILTER_REQUIRE_ARRAY,
				'default' => array(),
			),

			'cache_products_timeout'                      => FILTER_SANITIZE_NUMBER_INT,
			'cache_products_session_key'                  => FILTER_SANITIZE_STRING,
			'search_by_sku'                               => FILTER_VALIDATE_BOOLEAN,
			'verbose_search'                              => FILTER_VALIDATE_BOOLEAN,
			'search_by_cat_and_tag'                       => FILTER_VALIDATE_BOOLEAN,
			'number_of_products_to_show'                  => array(
				'filter'  => FILTER_VALIDATE_INT,
				'options' => array(
					'min_range' => 1,
					'default'   => $this->get_default_settings()['number_of_products_to_show'],
				)
				,
			),
			'sort_products_by_relevancy'		      => FILTER_VALIDATE_BOOLEAN,
			'autocomplete_product_hide_image'             => FILTER_VALIDATE_BOOLEAN,
			'autocomplete_product_hide_status'            => FILTER_VALIDATE_BOOLEAN,
			'autocomplete_product_hide_qty'               => FILTER_VALIDATE_BOOLEAN,
			'autocomplete_product_hide_price'             => FILTER_VALIDATE_BOOLEAN,
			'autocomplete_product_hide_sku'               => FILTER_VALIDATE_BOOLEAN,
			'autocomplete_product_hide_name'              => FILTER_VALIDATE_BOOLEAN,
			'show_long_attribute_names'                   => FILTER_VALIDATE_BOOLEAN,
			'allow_duplicate_products'                    => FILTER_VALIDATE_BOOLEAN,
			'hide_products_with_no_price'                 => FILTER_VALIDATE_BOOLEAN,
			'sale_backorder_product'                      => FILTER_VALIDATE_BOOLEAN,
			'sell_disable_variation'                      => FILTER_VALIDATE_BOOLEAN,
			'allow_to_rename_cart_items'				  => FILTER_VALIDATE_BOOLEAN,
			'add_product_to_top_of_the_cart'              => FILTER_VALIDATE_BOOLEAN,
			'is_readonly_price'                           => FILTER_VALIDATE_BOOLEAN,
			'item_price_precision'                        => array(
				'filter'  => FILTER_VALIDATE_INT,
				'options' => array(
					'min_range' => 0,
					'default'   => $this->get_default_settings()['item_price_precision'],
				)
				,
			),
			'item_default_selected' => array(
				'filter'  => FILTER_VALIDATE_INT,
				'flags'   => FILTER_REQUIRE_ARRAY,
				'default' => array(),
			),
			'is_featured'                                 => FILTER_SANITIZE_STRING,
			'dont_refresh_cart_item_item_meta'            => FILTER_VALIDATE_BOOLEAN,
			'disable_edit_meta'                           => FILTER_VALIDATE_BOOLEAN,
			'hide_item_meta'			      => FILTER_VALIDATE_BOOLEAN,

			'item_default_search_result' => array(
				'filter'  => FILTER_VALIDATE_INT,
				'flags'   => FILTER_REQUIRE_ARRAY,
				'default' => array(),
			),

			'display_search_result_as_grid'		      => FILTER_VALIDATE_BOOLEAN,
			'show_qty_input_advanced_search'              => FILTER_VALIDATE_BOOLEAN,
			'show_price_input_advanced_search'            => FILTER_VALIDATE_BOOLEAN,
			'action_click_on_title_product_item_in_cart'  => FILTER_SANITIZE_STRING,
			'action_click_on_title_product_item_in_search_products' => FILTER_SANITIZE_STRING,
			'barcode_mode'				      => FILTER_VALIDATE_BOOLEAN,

			'disable_adding_products'                     => FILTER_VALIDATE_BOOLEAN,
			'new_product_ask_sku'                         => FILTER_VALIDATE_BOOLEAN,
			'product_visibility'                          => FILTER_SANITIZE_STRING,
			'new_product_ask_tax_class'                   => FILTER_VALIDATE_BOOLEAN,
			'item_tax_class'                              => FILTER_SANITIZE_STRING,
			'edit_created_product_in_new_tab'	      => FILTER_VALIDATE_BOOLEAN,
			'show_product_category'			      => FILTER_VALIDATE_BOOLEAN,
			'new_product_show_weight'		      => FILTER_VALIDATE_BOOLEAN,
			'new_product_show_length'		      => FILTER_VALIDATE_BOOLEAN,
			'new_product_show_width'		      => FILTER_VALIDATE_BOOLEAN,
			'new_product_show_height'		      => FILTER_VALIDATE_BOOLEAN,
			'new_product_create_woocommerce_product'      => FILTER_SANITIZE_STRING,
            'create_private_product'              => FILTER_VALIDATE_BOOLEAN,

			'hide_add_fee'                                => FILTER_VALIDATE_BOOLEAN,
			'default_fee_name'                            => FILTER_SANITIZE_STRING,
			'default_fee_amount'                          => FILTER_VALIDATE_FLOAT,
			'fee_tax_class'                               => FILTER_SANITIZE_STRING,
			'allow_to_use_zero_amount'		      => FILTER_VALIDATE_BOOLEAN,

			'hide_add_discount'                           => FILTER_VALIDATE_BOOLEAN,
			'allow_to_edit_coupon_name'		      => FILTER_VALIDATE_BOOLEAN,

			'cache_orders_timeout'                        => FILTER_SANITIZE_NUMBER_INT,
			'cache_orders_session_key'                    => FILTER_SANITIZE_STRING,
			'button_for_find_orders'                      => FILTER_VALIDATE_BOOLEAN,
			'set_current_price_in_copied_order'           => FILTER_VALIDATE_BOOLEAN,
			'set_current_price_shipping_in_copied_order'  => FILTER_VALIDATE_BOOLEAN,
			'hide_find_orders'                            => FILTER_VALIDATE_BOOLEAN,
			'seek_only_orders_with_statuses'              => array(
				'filter'  => FILTER_SANITIZE_STRING,
				'flags'   => FILTER_REQUIRE_ARRAY,
				'options' => array(
				    'default' => array(),
				),
			),
			'dont_allow_edit_order_have_status_list'      => array(
				'filter'  => FILTER_SANITIZE_STRING,
				'flags'   => FILTER_REQUIRE_ARRAY,
				'options' => array(
				    'default' => array(),
				),
			),
			'show_orders_current_user'		      => FILTER_VALIDATE_BOOLEAN,

			'show_duplicate_order_button'                 => FILTER_VALIDATE_BOOLEAN,
			'show_edit_order_in_wc'                       => FILTER_VALIDATE_BOOLEAN,
			'show_creator_in_orders_list'                 => FILTER_VALIDATE_BOOLEAN,
			'hide_add_order_in_orders_list'		      => FILTER_VALIDATE_BOOLEAN,
			'show_phone_orders_in_order_page'			  => FILTER_VALIDATE_BOOLEAN,
			'show_order_type_filter_in_order_page'	      => FILTER_VALIDATE_BOOLEAN,
			'hide_button_pay_as_customer'                 => FILTER_VALIDATE_BOOLEAN,
			'show_button_pay'                             => FILTER_VALIDATE_BOOLEAN,
			'hide_button_view_order'		      => FILTER_VALIDATE_BOOLEAN,
			'hide_button_create_order'                    => FILTER_VALIDATE_BOOLEAN,
			'hide_button_put_on_hold'                     => FILTER_VALIDATE_BOOLEAN,
			'hide_button_full_refund'                     => FILTER_VALIDATE_BOOLEAN,
			'hide_button_send_invoice'                    => FILTER_VALIDATE_BOOLEAN,
			'show_additional_product_column'	      => FILTER_VALIDATE_BOOLEAN,
			'additional_product_column_title'	      => FILTER_SANITIZE_STRING,
			'show_discount_amount_in_order'               => FILTER_VALIDATE_BOOLEAN,
			'show_product_description'                    => FILTER_VALIDATE_BOOLEAN,
			'show_product_description_preview_size'       => array(
				'filter'  => FILTER_VALIDATE_INT,
				'options' => array(
					'min_range' => 10,
					'default'   => $this->get_default_settings()['show_product_description_preview_size'],
				)
				,
			),
			'show_view_invoice'	                          => FILTER_VALIDATE_BOOLEAN,
			'show_view_invoice_draft_orders'                  => FILTER_VALIDATE_BOOLEAN,
			'show_mark_as_paid'							  => FILTER_VALIDATE_BOOLEAN,
			'mark_as_paid_status'						  => FILTER_SANITIZE_STRING,
			'uses_empty_address_ship_different'	      => FILTER_VALIDATE_BOOLEAN,
			'open_popup_ship_different_address'	      => FILTER_VALIDATE_BOOLEAN,
			'use_english_interface'					  => FILTER_VALIDATE_BOOLEAN,
			'hide_private_note'			      => FILTER_VALIDATE_BOOLEAN,
			'hide_add_gift_card'			      => FILTER_VALIDATE_BOOLEAN,
			'show_cart_weight'			      => FILTER_VALIDATE_BOOLEAN,
			'hide_taxes_if_tax_exempt'		      => FILTER_VALIDATE_BOOLEAN,
			'hide_coupon_warning'			      => FILTER_VALIDATE_BOOLEAN,
			'hide_add_coupon'			      => FILTER_VALIDATE_BOOLEAN,
			'show_all_coupons_in_autocomplete'	      => FILTER_VALIDATE_BOOLEAN,
            'select_optimal_shipping'         => FILTER_VALIDATE_BOOLEAN,
			'hide_add_shipping'			      => FILTER_VALIDATE_BOOLEAN,

			'customer_custom_fields' => array(
				'filter'  => FILTER_CALLBACK,
				'options' => array( $this, 'sanitize_custom_fields' ),
			),
			'order_custom_fields'    => array(
				'filter'  => FILTER_CALLBACK,
				'options' => array( $this, 'sanitize_custom_fields' ),
			),
			'replace_order_with_customer_custom_fields'      => FILTER_VALIDATE_BOOLEAN,
			'customer_custom_fields_header_text'          => FILTER_SANITIZE_STRING,
			'add_formatted_custom_fields'                 => FILTER_VALIDATE_BOOLEAN,
			'customer_custom_fields_header_text_at_top'   => FILTER_SANITIZE_STRING,
			'customer_custom_fields_at_top' => array(
			    'filter'  => FILTER_CALLBACK,
			    'options' => array( $this, 'sanitize_custom_fields' ),
			),
			'show_custom_fields_in_order_email'	      => FILTER_VALIDATE_BOOLEAN,
			'order_custom_fields_columns_by_line'	      => array(
			    'filter'  => FILTER_CALLBACK,
			    'options' => array( $this, 'sanitize_custom_fields_columns_by_line' ),
			),

			'show_go_to_cart_button'                      => FILTER_VALIDATE_BOOLEAN,
			'show_go_to_checkout_button'                  => FILTER_VALIDATE_BOOLEAN,
			'show_checkout_link_button'                   => FILTER_VALIDATE_BOOLEAN,
			'show_payment_link_button'                    => FILTER_VALIDATE_BOOLEAN,
			'override_customer_payment_link_in_order_page'=> FILTER_VALIDATE_BOOLEAN,
			'override_product_price_in_cart'              => FILTER_VALIDATE_BOOLEAN,
			'override_email_pay_order_link'               => FILTER_VALIDATE_BOOLEAN,
		);

		return $definitions_pro;
	}

	public function sanitize_custom_fields( $value ) {
		if ( ! $value ) {
			return "";
		}

		$new_value = array();
		foreach ( preg_split( "/((\r?\n)|(\r\n?))/", $value ) as $line ) {
			$line        = explode( '|', $line );
			$line        = array_map( 'trim', $line );
			$new_value[] = implode( "|", $line );
		}

		return implode( PHP_EOL, $new_value );
	}

	public function sanitize_custom_fields_columns_by_line( $value ) {
		if ( ! $value ) {
			return "";
		}

		$new_value = array();
		foreach ( preg_split( "/((\r?\n)|(\r\n?))/", $value ) as $line ) {
		    $new_value[] = (int)$line;
		}

		return implode( PHP_EOL, $new_value );
	}
}

new WC_Phone_Orders_Helper_Pro();
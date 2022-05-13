<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_Phone_Orders_Add_Order_Page_Pro extends WC_Phone_Orders_Add_Order_Page {

	protected $meta_key_tax_exempt = 'is_vat_exempt';

	public function __construct() {

            parent::__construct();

            $render_hooks = array(
                'wpo_after_order_items',
                'wpo_after_customer_details',
                'wpo_find_order',
                'wpo_order_footer_left_side',
                'wpo_before_search_items_field',
                'wpo_add_fee',
                'wpo_footer_buttons',
            );

            array_map( function ( $hook_name ) {
                add_action( $hook_name, function () {
                    $method = sprintf('%s_action_hook_render', current_action());
                    if (method_exists($this, $method)) {
                        call_user_func_array(array($this, $method), array());
                    }
                } );
            }, $render_hooks );


            add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'woocommerce_checkout_update_order_meta' ),
                    10, 2 );

            if ( $this->option_handler->get_option( 'update_customers_profile_after_create_order' ) ) {
                    add_action( 'wpo_order_created', function( $order, $data ) {
                            $this->save_customer_data( $data['customer'] );
                    }, 10, 2 );
            }

        // apply custom fields
		add_action( 'wpo_after_create_customer', function ( $user_id, $data ) {
			if ( ! empty( $data['custom_fields'] ) ) {
				foreach ( $data['custom_fields'] as $key => $value ) {
					update_user_meta( $user_id, $key, $value );
				}
			}
		}, 10, 2 );

		add_action( "wpo_set_cart_customer", function ( $cart_customer, $id, $customer_data ) {
			$customer_custom_fields_options = array_merge(
			    $this->extract_field_from_option( $this->option_handler->get_option( 'customer_custom_fields_at_top' ) ),
			    $this->extract_field_from_option( $this->option_handler->get_option( 'customer_custom_fields' ) )
			);

			foreach ( array_keys( $customer_custom_fields_options ) as $key ) {
				if ( isset( $customer_data['custom_fields'][ $key ] ) ) {
					if ( is_callable( array( WC()->customer, "set_$key" ) ) ) {
						WC()->customer->{"set_$key"}( $customer_data['custom_fields'][ $key ] );
					} else {
						WC()->customer->update_meta_data( $key, $customer_data['custom_fields'][ $key ] );
					}
				} else {
					WC()->customer->delete_meta_data( $key );
				}
			}

                        WC()->customer->apply_changes();

		}, 10, 3 );

		add_filter( "wpo_after_update_customer", function ( $customer ) {
			$custom_fields                  = array();
			$customer_custom_fields_options = array_merge(
			    $this->extract_field_from_option( $this->option_handler->get_option( 'customer_custom_fields_at_top' ) ),
			    $this->extract_field_from_option( $this->option_handler->get_option( 'customer_custom_fields' ) )
			);

			foreach ( array_keys( $customer_custom_fields_options ) as $key ) {
				if ( is_callable( array( WC()->customer, "get_$key" ) ) ) {
					$value = WC()->customer->{"get_$key"}();
				} else {
					$value = WC()->customer->get_meta( $key );
				}
				$custom_fields[ $key ] = $value;
            }
			$customer['custom_fields'] = $custom_fields;

			return $customer;
		}, 10, 1 );

		add_filter( "wpo_after_get_customer_by_id", array($this, 'get_customer_custom_fields'), 10, 2 );

		add_filter( "wpo_after_get_customer_by_order", array($this, 'get_customer_custom_fields'), 10, 2 );

		if ( $this->option_handler->get_option( 'add_formatted_custom_fields' ) ) {
			add_filter( "wpo_get_customer_by_array_data", array( $this, 'add_formatted_custom_fields_to_billing_address' ), 10, 1 );
			add_filter( "wpo_after_update_customer", array( $this, 'add_formatted_custom_fields_to_billing_address' ), 10, 1 );
        }

		// end apply custom fields

		add_filter( 'wpo_init_order_default_custom_fields_values', function ( $custom_fields_options ) {
			$default_customer = $this->get_customer_by_id( $this->option_handler->get_option( 'default_customer_id' ) );
			if ( isset( $default_customer['custom_fields'] ) && $this->option_handler->get_option( 'replace_order_with_customer_custom_fields' ) ) {
				$custom_fields_options = array_merge( $custom_fields_options, apply_filters('wpo_customer_custom_fields', $default_customer['custom_fields']) );
			}

			return $custom_fields_options;
		}, 10, 1 );

		if ($this->option_handler->get_option( 'sort_products_by_relevancy' )) {
		    add_filter( 'wpo_search_products_and_variations_results', function ( $result, $term ) {

			$_term = preg_replace('/[^A-Za-z0-9]/', '', $term);

			foreach ($result as &$item) {

			    $relevancy = 0;

			    similar_text(
				preg_replace('/[^A-Za-z0-9]/', '', $item['product']->get_name()),
				$_term,
				$relevancy
			    );

			    $item['relevancy'] = $relevancy;
			}

			usort( $result, function ( $a, $b ) {

			    if ($a['relevancy'] == $b['relevancy']) {
				return 0;
			    }

			    return $a['relevancy'] > $b['relevancy'] ? -1 : 1;
			} );

			return $result;

		    }, 10, 2 );
		}

		add_filter( 'wpo_customer_addition_full_keys', function ( $fields ) {

		    if ( $this->option_handler->get_option( 'use_shipping_phone' ) ) {
			$fields[] = 'shipping_phone';
		    }

		    if ( ! $this->option_handler->get_option( 'support_field_vat' ) ) {
			$fields = array_filter($fields, function ($field) {
			    return !in_array($field, array('billing_vat_number', 'shipping_vat_number'));
			});
		    }

		    return $fields;

		}, 10, 1 );

		if ( $this->option_handler->get_option( 'uses_empty_address_ship_different' ) ) {
		    add_filter( "wpo_after_update_customer", array( $this, 'clear_shipping_address' ), 10, 2 );
		}

		if ( $this->option_handler->get_option( 'show_order_history_customer' ) ) {
		    add_filter( "wpo_get_customer_by_array_data", array($this, 'customer_order_history_summary'), 10, 2);
		    add_filter( "wpo_after_update_customer", array($this, 'customer_order_history_summary'), 10, 2);
		}

		if ( $this->option_handler->get_option( 'sell_disable_variation' ) ) {
		    add_action('wpo_before_update_cart', function ($cart_data) {
				add_filter( 'woocommerce_variation_is_purchasable', function ($is_purchasable, $variation) {
					return true;
				},10,2);
		    });
		}

		add_filter('wpo_search_products_result_item', function ($result_item, $product_id, $product) {

		    $permalink = get_permalink($product_id);

		    $minified_product_page_link = add_query_arg(array(
			'action'     => 'wpo-product-page',
			'product_id' => $product_id,
		    ), home_url());

		    $result_item['configure_product_page_link'] = $this->option_handler->get_option( 'allow_to_use_minified_page_configure_product' ) ? $minified_product_page_link : $permalink;

		    return $result_item;
		}, 10, 3);

		$this->custom_prod_control = new WC_Phone_Orders_Custom_Products_Controller_Pro();
	}

	public function add_formatted_custom_fields_to_billing_address( $customer_data ) {
		$customer_custom_fields_options = array_merge(
		    WC_Phone_Orders_Add_Order_Page_Pro::full_extract_field_from_option( $this->option_handler->get_option( 'customer_custom_fields_at_top' ) ),
		    WC_Phone_Orders_Add_Order_Page_Pro::full_extract_field_from_option( $this->option_handler->get_option( 'customer_custom_fields' ) )
		);
		$formatted_custom_fields        = array();
		foreach ( $customer_custom_fields_options as $customer_custom_field ) {
			if ( isset( $customer_data['custom_fields'][ $customer_custom_field['name'] ] ) ) {
				$value = $customer_data['custom_fields'][ $customer_custom_field['name'] ];
				if ( is_array( $value ) ) {
					$value = implode( ",", $value );
				}
				$formatted_custom_fields[] = esc_html( $customer_custom_field['label'] . ": " . $value );
			}
		}

		$customer_data['formatted_billing_address'] .= '<hr>' . implode( '<br/>', $formatted_custom_fields );

		return $customer_data;
	}

	protected function make_customer_fields_to_show() {
	    $fields = parent::make_customer_fields_to_show();
	    $additional_fields = array(
		    'password' => array(
			    'label' => __( 'Password', 'phone-orders-for-woocommerce' ),
			    'value' => '',
		    ),
		    'username' => array(
			    'label' => __( 'Username', 'phone-orders-for-woocommerce' ),
			    'value' => '',
		    ),
		    'role' => array(
			    'label' => __( 'Role', 'phone-orders-for-woocommerce' ),
			    'value' => $this->option_handler->get_option( 'default_role' ),
		    ),
		    'locale' => array(
			    'label' => __( 'Language', 'phone-orders-for-woocommerce' ),
			    'value' => 'site-default',
		    ),
        );
	    $fields['common']['fields'] = array_merge($fields['common']['fields'], $additional_fields);

	    return $fields;
    }

        protected function wpo_after_order_items_action_hook_render() {
            ?>

            <clear-cart slot="wpo-after-order-items" v-bind="<?php
                echo esc_attr(json_encode(array(
                    'buttonLabel' => __('Clear cart', 'phone-orders-for-woocommerce'),
                )))
            ?>"></clear-cart>

            <?php
        }

        protected function wpo_after_customer_details_action_hook_render() {
            ?>

            <save-to-customer slot="save-to-customer" v-bind="<?php
                echo esc_attr(json_encode(array(
                    'buttonTitle' => __('Save to customer', 'phone-orders-for-woocommerce'),
                    'tabName'     => 'add-order',
                )))
            ?>"></save-to-customer>

            <tax-exempt slot="tax-exempt" v-bind="<?php
            echo esc_attr( json_encode( array(
	            'title'        => __( 'Tax exempt', 'phone-orders-for-woocommerce' ),
	            'tabName'      => 'add-order',
	            'isTaxEnabled' => wc_tax_enabled(),
            ) ) )
	        ?>"></tax-exempt>

            <?php
        }

        protected function wpo_find_order_action_hook_render() {
            ?>

            <find-existing-order slot="find-order" v-bind="<?php
                echo esc_attr(json_encode(array(
                    'title'                           => __('Find existing order', 'phone-orders-for-woocommerce'),
                    'editOrderTitle'			=> __('Find existing order', 'phone-orders-for-woocommerce'),
                    'copyOrderTitle'			=> __('Duplicate existing order', 'phone-orders-for-woocommerce'),
                    'copyButtonForFindOrdersLabel'        => __('Copy order', 'phone-orders-for-woocommerce'),
                    'editButtonForFindOrdersLabel'        => __('Edit order', 'phone-orders-for-woocommerce'),
                    'viewButtonForFindOrdersLabel'        => __('View order', 'phone-orders-for-woocommerce'),
                    'noticeLoadedLabel'               => __('Current order was copied from order', 'phone-orders-for-woocommerce'),
                    'noticeViewLabel'                 => __('You view order', 'phone-orders-for-woocommerce'),
                    'noticeEditedLabel'               => __('You edit order', 'phone-orders-for-woocommerce'),
                    'noticeDraftedLabel'              => __('You edit unfinished order', 'phone-orders-for-woocommerce'),
                    'selectExistingOrdersPlaceholder' => _x('Type to search', 'search existing orders placeholder', 'phone-orders-for-woocommerce'),
                    'noResultLabel'                   => __('Oops! No elements found. Consider changing the search query.', 'phone-orders-for-woocommerce'),
                    'tabName'                         => 'add-order',
                    'multiSelectSearchDelay'          => $this->multiselect_search_delay,
                    'noOptionsTitle'                  => __( 'List is empty.', 'phone-orders-for-woocommerce' ),
                    'isEditOrderDefaultAction'	      => apply_filters('wpo_find_order_is_edit_order_default_action', false),
                )))
            ?>"></find-existing-order>

	    <customer-custom-fields slot="edit-customer-address" v-bind="<?php
		echo esc_attr( json_encode( array(
			'dateFormat'		    => $this->convertPHPToMomentFormat( wc_date_format() ),
			'empty'			    => false,
			'noOptionsTitle'	    => __( 'List is empty.', 'phone-orders-for-woocommerce' ),
			'selectOptionPlaceholder'   => __( 'Select option', 'phone-orders-for-woocommerce' ),
			'fileUrlPrefix'		    => apply_filters('wpo_custom_file_url_prefix', ''),
		) ) )
		?>"></customer-custom-fields>

	    <customer-custom-fields slot="add-customer-address" v-bind="<?php
		echo esc_attr( json_encode( array(
			'dateFormat'		    => $this->convertPHPToMomentFormat( wc_date_format() ),
			'empty'			    => false,
			'noOptionsTitle'	    => __( 'List is empty.', 'phone-orders-for-woocommerce' ),
			'selectOptionPlaceholder'   => __( 'Select option', 'phone-orders-for-woocommerce' ),
			'fileUrlPrefix'		    => apply_filters('wpo_custom_file_url_prefix', ''),
		) ) )
		?>"></customer-custom-fields>

	    <customer-custom-fields-at-top slot="edit-customer-address-header" v-bind="<?php
		echo esc_attr( json_encode( array(
			'dateFormat'		    => $this->convertPHPToMomentFormat( wc_date_format() ),
			'empty'			    => false,
			'noOptionsTitle'	    => __( 'List is empty.', 'phone-orders-for-woocommerce' ),
			'selectOptionPlaceholder'   => __( 'Select option', 'phone-orders-for-woocommerce' ),
			'fileUrlPrefix'		    => apply_filters('wpo_custom_file_url_prefix', ''),
		) ) )
		?>"></customer-custom-fields-at-top>

	    <customer-custom-fields-at-top slot="add-customer-address-header" v-bind="<?php
		echo esc_attr( json_encode( array(
			'dateFormat'		    => $this->convertPHPToMomentFormat( wc_date_format() ),
			'empty'			    => false,
			'noOptionsTitle'	    => __( 'List is empty.', 'phone-orders-for-woocommerce' ),
			'selectOptionPlaceholder'   => __( 'Select option', 'phone-orders-for-woocommerce' ),
			'fileUrlPrefix'		    => apply_filters('wpo_custom_file_url_prefix', ''),
		) ) )
		?>"></customer-custom-fields-at-top>

	    <div slot="multi-addresses-select" slot-scope="slotProps">
            <multi-addresses-select v-bind="<?php
		    echo esc_attr( json_encode( array(
			    'selectPlaceholder'        => __( 'Select address book entry', 'phone-orders-for-woocommerce' ),
			    'deleteAddressLabel'       => __( 'Delete entry', 'phone-orders-for-woocommerce' ),
			    'deleteAddressPromptLabel' => __( 'Are you sure?', 'phone-orders-for-woocommerce' ),
			    'tabName'                  => 'add-order',
			    'noOptionsTitle'           => __( 'List is empty.', 'phone-orders-for-woocommerce' ),
		    ) ) )
		    ?>" :slot-props="slotProps"></multi-addresses-select>
        </div>

            <span slot="multi-addresses-buttons" slot-scope="slotProps">
		<multi-addresses-buttons v-bind="<?php
		echo esc_attr( json_encode( array(
			'saveAddressLabel'       => __( 'Save as new entry', 'phone-orders-for-woocommerce' ),
			'updateAddressLabel'     => __( 'Update entry', 'phone-orders-for-woocommerce' ),
			'savedNewAddressMessage' => __( 'Saved', 'phone-orders-for-woocommerce' ),
			'updatedAddressMessage'  => __( 'Updated', 'phone-orders-for-woocommerce' ),
			'tabName'                => 'add-order',
		) ) )
		?>" :slot-props="slotProps"></multi-addresses-buttons>
	    </span>

	        <?php
        }

	private function convertPHPToMomentFormat( $format ) {
		$replacements = [
			'd' => 'DD',
			'D' => 'ddd',
			'j' => 'D',
			'l' => 'dddd',
			'N' => 'E',
			'S' => 'o',
			'w' => 'e',
			'z' => 'DDD',
			'W' => 'W',
			'F' => 'MMMM',
			'm' => 'MM',
			'M' => 'MMM',
			'n' => 'M',
			't' => '', // no equivalent
			'L' => '', // no equivalent
			'o' => 'YYYY',
			'Y' => 'YYYY',
			'y' => 'YY',
			'a' => 'a',
			'A' => 'A',
			'B' => '', // no equivalent
			'g' => 'h',
			'G' => 'H',
			'h' => 'hh',
			'H' => 'HH',
			'i' => 'mm',
			's' => 'ss',
			'u' => 'SSS',
			'e' => 'zz', // deprecated since version 1.6.0 of moment.js
			'I' => '', // no equivalent
			'O' => '', // no equivalent
			'P' => '', // no equivalent
			'T' => '', // no equivalent
			'Z' => '', // no equivalent
			'c' => '', // no equivalent
			'r' => '', // no equivalent
			'U' => 'X',
		];
		$momentFormat = strtr( apply_filters('wpo_convert_php_to_moment_format_get_format', $format), $replacements );

		return $momentFormat;
	}

	protected function wpo_order_footer_left_side_action_hook_render() {
		?>

        <order-custom-fields slot="order-footer-left-side" v-bind="<?php
		echo esc_attr( json_encode( array(
			'dateFormat'		    => $this->convertPHPToMomentFormat( wc_date_format() ),
			'noOptionsTitle'	    => __( 'List is empty.', 'phone-orders-for-woocommerce' ),
			'selectOptionPlaceholder'   => __( 'Select option', 'phone-orders-for-woocommerce' ),
			'fileUrlPrefix'		    => apply_filters('wpo_custom_file_url_prefix', ''),
		) ) )
		?>"></order-custom-fields>

		<?php
	}

        protected function wpo_before_search_items_field_action_hook_render() {

        ?>

            <products-category-tags-filter slot="before-search-items-field" v-bind="<?php
                echo esc_attr(json_encode(array(
                    'categoryLabel'                     => __('Category', 'phone-orders-for-woocommerce'),
                    'selectProductsCategoryPlaceholder' => __('Select a category', 'phone-orders-for-woocommerce'),
                    'tagLabel'                          => __('Tag', 'phone-orders-for-woocommerce'),
                    'selectProductsTagPlaceholder'      => __('Select a tag', 'phone-orders-for-woocommerce'),
                    'tabName'                           => 'add-order',
                    'noOptionsTitle'                    => __( 'List is empty.', 'phone-orders-for-woocommerce' ),
                )))
            ?>"></products-category-tags-filter>

            <?php
        }

        protected function get_terms_hierarchical($terms, array $output = array(), $parent_id = 0, $level = 0) {

            foreach ($terms as $term) {
                if ($parent_id == $term->parent) {

                    $output[] = array(
			'id'		=> $term->term_id,
                        'slug'		=> $term->slug,
                        'title'		=> str_pad('', $level * 12, '&nbsp;&nbsp;') . $term->name,
                        'filter_title'	=> str_pad('', $level * 12, '&nbsp;&nbsp;') . $term->name . ' (' . $term->count . ')',
                    );

					if( apply_filters( "wpo_output_categories_at_level", true, $level + 1) )
						$output = $this->get_terms_hierarchical($terms, $output, $term->term_id, $level + 1);
                }
            }

            return $output;
        }

        protected function ajax_get_products_tags_list( $data ) {

            $all_tags  = get_terms(array('taxomony' => 'product_tag'));
            $tags_list = array();

            foreach ($all_tags as $tag) {
                $tags_list[] = array(
                    'value' => $tag->slug,
                    'title' => $tag->name . ' (' . $tag->count . ')',
                );
            }

            array_walk_recursive($tags_list, function (&$item, $key) {
                $item = mb_convert_encoding($item, 'UTF-8', mb_detect_encoding($item));
            });

            return $this->wpo_send_json_success(array(
                'tags_list' => $tags_list,
            ));
        }

        protected function ajax_get_products_categories_list( $data ) {

            $categories = get_terms(array(
                'hierarchical' => 1,
                'orderby'      => 'name',
                'taxonomy'     => 'product_cat',
            ));

            $categories_list = $this->get_terms_hierarchical($categories);

            array_walk_recursive($categories_list, function (&$item, $key) {
                $item = mb_convert_encoding($item, 'UTF-8', mb_detect_encoding($item));
            });

            return $this->wpo_send_json_success(array(
                'categories_list' => $categories_list,
            ));
        }

        protected function wpo_add_fee_action_hook_render() {
            ?>

            <add-fee slot="add-fee" v-bind="<?php
                echo esc_attr(json_encode(array(
                    'addFeeButtonLabel' => __( 'Add fee', 'phone-orders-for-woocommerce' ),
                )))
            ?>"></add-fee>

            <?php
        }

        protected function wpo_footer_buttons_action_hook_render() {
            ?>

            <footer-buttons-1 slot="pro-version-buttons-1" v-bind="<?php
                echo esc_attr(json_encode(array(
					'putOnHoldButtonLabel'          => __('Create draft', 'phone-orders-for-woocommerce'),
					'updateDraftButtonLabel'		=> __('Update draft', 'phone-orders-for-woocommerce'),
                    'goToCartPageLabel'		    => __('Go to Cart', 'phone-orders-for-woocommerce'),
                    'goToCheckoutPageLabel'	    => __('Go to Checkout', 'phone-orders-for-woocommerce'),
                    'checkoutLinkButtonLabel'	    => __('Checkout link', 'phone-orders-for-woocommerce'),
                    'checkoutLinkCopiedButtonLabel' => __('Url has been copied to clipboard', 'phone-orders-for-woocommerce'),
                    'disabledCheckoutLinkTitle'	    => __('This button disabled for admins', 'phone-orders-for-woocommerce'),
                    'updateOrderButtonLabel'        => __('Update order', 'phone-orders-for-woocommerce'),
                    'cancelUpdateOrderButtonLabel'  => __('Cancel', 'phone-orders-for-woocommerce'),
                    'clearAllButtonLabel'           => __('Clear all', 'phone-orders-for-woocommerce'),
                    'payOrderButtonLabel'           => __('Pay order as the customer', 'phone-orders-for-woocommerce'),
                    'payButtonLabel'                => __('Pay', 'phone-orders-for-woocommerce'),
					'orderIsCompletedTitle'         => __('Order completed', 'phone-orders-for-woocommerce'),
					'markAsPaidLabel'				=> __('Mark as paid', 'phone-orders-for-woocommerce'),
                    'tabName'                       => 'add-order',
                )))
            ?>"></footer-buttons-1>

            <footer-buttons-2 slot="pro-version-buttons-2" v-bind="<?php
                echo esc_attr(json_encode(array(
                    'editCreatedOrderButtonLabel' => __( 'Edit created order', 'phone-orders-for-woocommerce' ),
                    'orderIsCompletedTitle'       => __('Order completed', 'phone-orders-for-woocommerce'),
                )))
            ?>"></footer-buttons-2>

            <footer-buttons-3 slot="pro-version-buttons-3" v-bind="<?php
	        echo esc_attr(json_encode(array(
		        'viewInvoiceLabel' => __( 'View invoice', 'phone-orders-for-woocommerce' ),
		        'viewInvoicePath'  => urldecode(apply_filters('wpo_view_invoice_url', add_query_arg( array('order_id' => '%order_id', 'nonce' => '%nonce'), get_home_url( null, 'wpo-view-invoice' )), '%order_id')),
	        )))
	        ?>"></footer-buttons-3>

            <footer-buttons-4 slot="pro-version-buttons-4" v-bind="<?php
	        echo esc_attr(json_encode(array(
			'tabName'		    => 'add-order',
			'refundOrderButtonLabel'    => __('Full refund', 'phone-orders-for-woocommerce'),
			'refundOrderNoticeMessage'  => __('Are you sure ?', 'phone-orders-for-woocommerce'),
			'paymentLinkButtonLabel'    => __('Payment link', 'phone-orders-for-woocommerce'),
			'orderIsCompletedTitle'	    => __('Order completed', 'phone-orders-for-woocommerce'),
	        )))
	        ?>"></footer-buttons-4>

            <?php
        }

	public function woocommerce_checkout_update_order_meta( $order_id, $data ) {
		if ( isset( $_REQUEST['cart']['custom_fields'] ) ) {
			$custom_fields = $_REQUEST['cart']['custom_fields'];
			$order         = wc_get_order( $order_id );
			foreach ( $custom_fields as $key => $value ) {
				if ( is_callable( array( $order, "set_$key" ) ) ) {
					$order->{"set_$key"}( is_array($value) ? implode('|', $value) : $value );
				} else {
					$order->update_meta_data( $key, is_array($value) ? implode('|', $value) : $value );
				}
			}
			$order->save();
		}

		if ( isset( $_REQUEST['cart']['customer'] ) ) {
			$customer_data = $_REQUEST['cart']['customer'];
			$order         = wc_get_order( $order_id );

			$customer = array();
			foreach ( $this->customer_addition_full_keys() as $key ) {
				$customer[ $key ] = ! empty( $customer_data[ $key ] ) ? $customer_data[ $key ] : '';
			}

			foreach ( $customer as $key => $value ) {
			    $order->update_meta_data( '_wpo_customer_' . $key, $value );
			}
			$order->save();
		}

		if ( isset( $_REQUEST['cart']['customer']['custom_fields'] ) ) {
			$customer_custom_fields_options = array_merge(
			    $this->extract_field_from_option( $this->option_handler->get_option( 'customer_custom_fields_at_top' ) ),
			    $this->extract_field_from_option( $this->option_handler->get_option( 'customer_custom_fields' ) )
			);
			$custom_fields = $_REQUEST['cart']['customer']['custom_fields'];
			$order         = wc_get_order( $order_id );

            foreach ( $custom_fields as $key => $value ) {
	            if ( in_array( $key, array_keys( $customer_custom_fields_options ) ) ) {
		            $order->update_meta_data( '_wpo_customer_meta_' . $key, $value );
	            };
            }
			$order->save();
		}
	}

	public function enqueue_scripts() {
            parent::enqueue_scripts();
	}

	protected function ajax_save_customer_data( $data ) {
		$this->save_customer_data( $data['customer_data'] );

                $cart = $data['cart'];

                $result = $this->get_calculated_cart( $cart );

                if ( $result instanceof WC_Data_Exception ) {
                    return $this->wpo_send_json_error( $result->getMessage() );
                }

		return $this->wpo_send_json_success( array('cart' => $result) );
	}

	protected function save_customer_data( $customer_data ) {
		if ( empty( $customer_data['id'] ) ) {
			return false;
		}
		$customer      = new WC_Customer( $customer_data['id'] );

		array_walk(
			$customer_data,
			function ( $value, $key ) {
				if ( stripos( $key, 'billing_' ) OR stripos( $key, 'shipping_' ) ) {
					return $value;
				}
			}
		);
		$errors = $customer->set_props( $customer_data );
		if ( ! empty( $customer_data['is_vat_exempt'] ) ) {
			$tax_exempt = wc_string_to_bool($customer_data['is_vat_exempt']) ? 'yes' : 'no';
			$customer->update_meta_data( 'is_vat_exempt', $tax_exempt );
			$customer->save_meta_data();
		}
		if ( ! empty( $customer_data['custom_fields'] ) ) {
			foreach ( $customer_data['custom_fields'] as $key => $value ) {
				if ( is_callable( array( $customer, "set_$key" ) ) ) {
					$customer->{"set_$key"}( $value );
				} else {
					$customer->update_meta_data( $key, $value );
				}
			}
			$customer->save_meta_data();
		}

		foreach ( $this->customer_addition_full_keys() as $key ) {
			if ( is_callable( array( $customer, "set_$key" ) ) ) {
				$customer->{"set_$key"}( ! empty( $customer_data[ $key ] ) ? $customer_data[ $key ] : '' );
			} else {
				$customer->update_meta_data( $key, ! empty( $customer_data[ $key ] ) ? $customer_data[ $key ] : '' );
			}
		}

		if ( $this->option_handler->get_option( 'update_wp_user_first_last_name' ) ) {
		    $customer->set_first_name($customer_data['billing_first_name']);
		    $customer->set_last_name($customer_data['billing_last_name']);
		}


		if ( $this->option_handler->get_option( 'customer_show_role_field' ) && ! empty ( $customer_data['id'] ) ) {
		    $user = new WP_User( $customer_data['id'] );
		    $user->set_role( $customer_data['role'] );
		}

		if ( $this->option_handler->get_option( 'customer_show_language_field' ) && ! empty ( $customer_data['id'] ) ) {
		    $user = new WP_User( $customer_data['id'] );
                    if ( isset( $customer_data['locale'] ) ) {
		        $locale = sanitize_text_field( $customer_data['locale'] );
                        if ( 'site-default' === $locale ) {
                                $locale = '';
                        } elseif ( '' === $locale ) {
                                $locale = 'en_US';
                        } elseif ( ! in_array( $locale, get_available_languages(), true ) ) {
                                $locale = '';
                        }
                        $user->locale = $locale;
                    }
                    wp_update_user($user);
		}

		$customer->save();

		return true;
	}

	private function search_orders( $term, $seek_in_statuses = array() ) {
		global $wpdb;

		$search_fields = array_map(
			'wc_clean', apply_filters(
				'woocommerce_shop_order_search_fields', array(
					'_billing_address_index',
					'_shipping_address_index',
					'_billing_last_name',
					'_billing_email',
				)
			)
		);
		$order_ids     = array();

		if ( is_numeric( $term ) ) {
			$order_ids[] = absint( $term );
		}

		$where_statuses = "";

		if ( ! empty( $seek_in_statuses ) ) {
			$where_statuses = " AND p2.post_status IN ('" . implode( "','", array_map( 'esc_sql', $seek_in_statuses ) ) . "')";
		}

		if ( ! empty( $search_fields ) ) {
			$date_depth = apply_filters( 'wpo_search_orders_date_depth', '-5 years');
			$order_ids = array_unique(
				array_merge(
					$order_ids,
					$wpdb->get_col(
						$wpdb->prepare(
							"SELECT DISTINCT p1.post_id FROM {$wpdb->postmeta} p1
INNER JOIN {$wpdb->posts} as p2 on p1.post_id = p2.ID
WHERE p1.meta_value LIKE %s AND p1.meta_key IN ('" . implode( "','", array_map( 'esc_sql', $search_fields ) ) . "')" . $where_statuses . " AND p2.post_modified > %s LIMIT 100", // @codingStandardsIgnoreLine
							'%' . $wpdb->esc_like( wc_clean( $term ) ) . '%',
                            gmdate( 'Y-m-d H:i:s', ( strtotime( $date_depth ) + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) ) )
						)
					),
					$wpdb->get_col(
						$wpdb->prepare(
							"SELECT order_id
							FROM {$wpdb->prefix}woocommerce_order_items as order_items
							INNER JOIN {$wpdb->posts} as p2 on order_items.order_id = p2.ID
							WHERE order_item_name LIKE %s" . $where_statuses . " AND p2.post_modified > %s LIMIT 100",
							'%' . $wpdb->esc_like( wc_clean( $term ) ) . '%',
							gmdate( 'Y-m-d H:i:s', ( strtotime( $date_depth ) + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) ) )
						)
					)
				)
			);
		}

		return apply_filters( 'woocommerce_shop_order_search_results', $order_ids, $term, $search_fields );
	}

	protected function ajax_find_orders( $data ) {

                $term               = $data['term'];
                $option_handler     = $this->option_handler;
                $allowed_post_types = array('shop_order');
                $seek_statuses      = $option_handler->get_option( 'seek_only_orders_with_statuses' );
		$orders_ids = $this->search_orders( str_replace( 'Order #', '', wc_clean( $term ) ), $seek_statuses );
                rsort( $orders_ids );

		$limit  = (int)apply_filters('wpo_find_orders_limit', 20);
                $result = array();

		$dontEditOrderStatusList = $option_handler->get_option( 'dont_allow_edit_order_have_status_list' );

		foreach ( $orders_ids as $order_id ) {

                        $order = wc_get_order( $order_id );

                        if ( ! $order || ! in_array($order->get_type(), $allowed_post_types) ) {
				continue;
			}

			if ( $option_handler->get_option( 'show_orders_current_user' ) && (int)$order->get_meta( WC_Phone_Orders_Loader::$meta_key_order_creator ) !== get_current_user_id() ) {
				continue;
			}

                        if ( ! empty( $seek_statuses )
                                && ! in_array( 'wc-' . $order->get_status(), $seek_statuses )
                                && ! in_array( $order->get_status(), $seek_statuses )
                        ) {
                               continue;
                        }

			if ( ! wc_is_order_status('wc-' . $order->get_status()) AND 'draft' != $order->get_status()){
				continue;
			}

			if($order->get_billing_first_name() != '' && $order->get_billing_last_name() != '') {
				$billing_info = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
			}
			else {
				$billing_info = $order->get_billing_company();
			}

			$formated_output_array = array(
				__( 'order # ', 'phone-orders-for-woocommerce' ) . $order->get_order_number(),
				$billing_info,
				$this->render_order_date_column( $order->get_date_created() ),
				get_woocommerce_currency_symbol() . ' ' . $order->get_total(),
				wc_get_order_status_name( $order->get_status() )
			);

			$formated_output = implode( " | ", $formated_output_array );

			if ( 'draft' == $order->get_status() ) {
				$copy_button_value = '';
				$edit_button_value = __( 'Resume order', 'phone-orders-for-woocommerce' );
				$view_button_value = __( 'View order', 'phone-orders-for-woocommerce' );
			} else {
				$copy_button_value = __( 'Copy order', 'phone-orders-for-woocommerce' );
				$edit_button_value = __( 'Edit order', 'phone-orders-for-woocommerce' );
				$view_button_value = __( 'View order', 'phone-orders-for-woocommerce' );
			}

			$result[] = array(
				'formated_output'  => $formated_output,
				'loaded_order_url' => $order->get_edit_order_url(),
				'loaded_order_id'  => $order->get_id(),
				'allow_edit'	   => $this->get_allow_refund_order($order) && (!$dontEditOrderStatusList || (!in_array('wc-' . $order->get_status(), $dontEditOrderStatusList) && !in_array( $order->get_status(), $dontEditOrderStatusList))),
				'allow_view'	   => !($this->get_allow_refund_order($order) && (!$dontEditOrderStatusList || (!in_array('wc-' . $order->get_status(), $dontEditOrderStatusList) && !in_array( $order->get_status(), $dontEditOrderStatusList)))),
				'copy_button_value'     => $copy_button_value,
				'edit_button_value'     => $edit_button_value,
				'view_button_value'     => $view_button_value,
			);

			if( count($result) > $limit )
				break;
		}

		wp_send_json( $result );
	}

	protected function ajax_load_order( $data ) {
		$order_id = isset( $data['order_id'] ) ? $data['order_id'] : '';
		$mode = isset( $data['mode'] ) ? $data['mode'] : 'copy';
		if ( $order_id ) {
			$response = $this->load_order( $order_id, $mode );
			$response['log_row_id'] = uniqid();
			$result = $this->update_cart( $response['cart'] );
			if ( $result instanceof WC_Data_Exception ) {
				return $this->wpo_send_json_error( $result->getMessage() );
			}
			WC_Phone_Orders_Tabs_Helper::add_log( $response['log_row_id'], $result, $order_id );

			// fix missing shipping when autorecalculate is disabled
			if ( isset( $result['shipping'] ) ) {
				$response['cart']['shipping'] = $result['shipping'];
			}

			$response = apply_filters('wpo_load_order_response', $response, $result, $order_id, $mode === 'edit');

			return $this->wpo_send_json_success( $response );
		} else {
			return $this->wpo_send_json_error();
		}
	}

	private function load_order( $order_id, $mode = 'copy' ) {
	    	$custom_prod_control = new WC_Phone_Orders_Custom_Products_Controller_Pro();
		$option_handler = $this->option_handler;

		do_action('wpo_load_order_before', $order_id, $mode === 'edit');

		$order = wc_get_order( $order_id );

		if ( ! $order_id ){
			return false;
		}

		$cart                 = array();
		$cart['loaded_order'] = true;

		$hidden_order_itemmeta = apply_filters(
			'woocommerce_hidden_order_itemmeta', array(
				'_qty',
				'_tax_class',
				'_product_id',
				'_variation_id',
				'_line_subtotal',
				'_line_subtotal_tax',
				'_line_total',
				'_line_tax',
				'method_id',
				'cost',
				'_reduced_stock',
				$custom_prod_control::ORDER_ITEM_META_KEY,
			)
		);

		//order id
		$deleted_items = $out_of_stock_items = array();
		$post_statuses = current_user_can( 'edit_private_products' ) ? array( 'private', 'publish' ) : array( 'publish' );
                // items
		foreach ( $order->get_items() as $key => $order_item ) {
            if ( apply_filters('wpo_load_order_skip_item', false, $order_item, $order) ) {
                continue;
            }

			$order_item_qty = (float) $order_item->get_quantity();

			if ( $order_item_qty <= 0 ) {
				continue;
			}

                        $item_data  = $order_item->get_data();

                        $product_id = ( $item_data['variation_id'] ) ? $item_data['variation_id'] : $item_data['product_id'];

			if ( $custom_prod_control->is_custom_product( $product_id ) ) {
				$_product = $custom_prod_control->restore_product_from_order_item( $order_item );
			} else {
				$_product = wc_get_product( $product_id );
			}


                        if( !$_product ) {
							$deleted_items[] = array(
											'id'   => $product_id,
											'name' => $item_data['name'],
                            );
							continue;
						}

                        $item_custom_meta_fields = array();

                        $product_attrs = $_product->get_attributes();

                        if (isset($item_data['meta_data']) && is_array($item_data['meta_data'])) {
                            foreach ($item_data['meta_data'] as $meta) {
								if ( in_array( $meta->key, $hidden_order_itemmeta, true ) ) {
									continue;
								}

								$d = $meta->get_data();
								if (isset($product_attrs[$d['key']])) {
									continue;
								}
                                $item_custom_meta_fields[] = array(
                                    'id'         => $d['id'],
                                    'meta_key'   => $d['key'],
                                    'meta_value' => $d['value'],
                                );
                            }
                        }

			if ( ! in_array($_product->get_status(), $post_statuses) ) {
				$deleted_items[] = array(
                                    'id'   => $product_id,
                                    'name' => $item_data['name'],
                                );
				continue;
			}
			if ( ! $_product->is_in_stock() AND ! $option_handler->get_option( 'sale_backorder_product' ) ) {
				$out_of_stock_items[] = array(
                                    'id'   => $product_id,
                                    'name' => $item_data['name'],
                                );
				continue;
			};

			$cost_updated_manually = false;

			if ( $option_handler->get_option( 'set_current_price_in_copied_order' ) && ($mode === 'copy' OR apply_filters("wpo_set_current_price_for_all_modes", true)) ) {
				$item_cost     = $_product->get_price();
				$line_subtotal = $item_cost * $order_item_qty;
				$cost_updated_manually = false;
			} else {
				if ( $order->get_prices_include_tax() ) {
					if ( $order->get_meta( $this->meta_key_tax_exempt ) == "yes") {
						/**
                         * For tax exempt order "$item_data['subtotal_tax']" will be empty
                         * So we calculate product tax by ourselves
						 */
						$tax_rates = WC_Tax::get_rates( $_product->get_tax_class(), ( new WC_Customer( $order->get_customer_id() ) ) );
						$tax       = array_sum( WC_Tax::calc_tax( $item_data['subtotal'], $tax_rates, ! $order->get_prices_include_tax() ) );
					} else {
						$tax = array_sum( $item_data['taxes']['subtotal'] );
					}

					$item_cost = ( $item_data['total'] + $tax ) / $order_item_qty;
				} else {
					$item_cost = $item_data['total'] / $order_item_qty;
				}

				$line_subtotal = $item_data['subtotal'];
				$cost_updated_manually = true;
			}

                    $loaded_product = $this->get_item_by_product($_product, array_merge($item_data, array(
                        'item_cost'          => $item_cost,
                        'line_subtotal'      => $line_subtotal,
                        'custom_meta_fields' => $item_custom_meta_fields,
                    )));

			$loaded_product['cost_updated_manually'] = apply_filters('wpo_load_order_cost_updated_manually', $cost_updated_manually, $_product, $item_data, $mode);

			if ( $mode === 'edit' ) {
				$loaded_product['order_item_id'] = $order_item->get_id();
			}

			$custom_prod_control->store_product_in_cart_item($loaded_product, $_product);

			$loaded_product['formatted_variation_data'] = isset($loaded_product['variation_data']) ?
				static::get_formatted_variation_data($loaded_product['variation_data'], $_product) : array();
			$loaded_product['custom_name'] = $item_data['name'];

			$cart['items'][] = apply_filters('wpo_load_order_loaded_product', $loaded_product, $order_item, $order, $mode === 'edit');
                };

		if ( ! isset( $cart['items'] ) ) {
			$cart['items'] = array();
		}


                // customer
		$cart['customer'] = $this->get_customer_by_order( $order );

		// fee
		$cart['fee']     = array();
		$cart['fee_ids'] = array();
		foreach ( $order->get_fees() as $key => $fee_data ) {
                    	$cart['fee'][] = array(
				'name'   => $fee_data->get_name(),
				'amount' => $fee_data->get_amount(),
                                'original_amount' => wc_prices_include_tax() ? $fee_data->get_amount() + $fee_data->get_total_tax() : $fee_data->get_amount(),
			);
                    	$cart['fee_ids'][] = sanitize_title( $fee_data->get_name() );
		}

		// discount in coupons
		$cart['discount'] = null;
		$discount         = get_post_meta( $order_id, $option_handler->get_option( 'manual_coupon_title' ), true );
		if ( $discount ) {
			$cart['discount'] = array(
				'type'   => isset( $discount['type'] ) ? $discount['type'] : $discount['discount_type'],
				'amount' => $discount['amount'],
			);
		}

		// coupons
		$cart['coupons'] = array();

		$coupons = method_exists($order, 'get_coupon_codes') ? $order->get_coupon_codes() : $order->get_used_coupons();

		foreach ( $coupons as $index => $value ) {
			if ( isset( $discount['code'] ) ) {
				$code = $discount['code'];
			} elseif ( isset( $discount['discount_code'] ) ) {
				$code = $discount['discount_code'];
			} else {
				$code = '';
			}
			if ( $value === $code ) {
				continue;
			}

			$cart['coupons'][] = array(
                'title' => $value,
            );
		}

		$current_time	      = current_time('timestamp', true);
		$order_date_timestamp = $current_time;

		// shipping
		$cart['shipping'] = WC_Phone_Orders_Cart_Shipping_Processor::make_shipping_from_order( $order, $option_handler, $mode === 'edit' );

		// customer_note
		$cart['customer_note'] = $order->get_customer_note();

		// private note
		$cart['private_note'] = apply_filters('wpo_load_order_private_note', get_post_meta( $order_id, $this->meta_key_private_note, true ), $order_id, $order);

		$message = '';
		if ( 'draft' == $order->get_status() ) {
			$cart['drafted_order_id']   = $order->get_id();
			$cart['allow_refund_order'] = $this->get_allow_refund_order($order);
			$message                  =
				'<h2>
					<span class="">' . __( 'You edit unfinished order', 'phone-orders-for-woocommerce' ) . '</span>
				</h2>';
		} elseif ( $mode === 'edit' ) {
			$cart['edit_order_id']	    = $order->get_id();
			$cart['edit_order_number']  = $order->get_order_number();
			$cart['allow_refund_order'] = $this->get_allow_refund_order($order);
			$message               =
				'<h2>
					<span class="">' . __( 'You edit order', 'phone-orders-for-woocommerce' ) . '</span>
					<a id="loaded_order_url" href="' . $order->get_edit_order_url() . '" target="_blank">#' . $order->get_order_number() . '</a>
				</h2>';

			$order_date_timestamp = $order->get_date_created()->getTimestamp();
		} elseif ( $mode === 'view' ) {
			$cart['view_order_id']	    = $order->get_id();
			$cart['view_order_number']  = $order->get_order_number();
			$message               =
				'<h2>
					<span class="">' . __( 'You view order', 'phone-orders-for-woocommerce' ) . '</span>
					<a id="loaded_order_url" href="' . $order->get_edit_order_url() . '" target="_blank">#' . $order->get_order_number() . '</a>
				</h2>';

			$order_date_timestamp = $order->get_date_created()->getTimestamp();

		} else {
			$cart['loaded_order_id']     = $order->get_id();
			$cart['loaded_order_number'] = $order->get_order_number();
			$message                 =
				'<h2>
					<span class="">' . __( 'Current order was copied from order', 'phone-orders-for-woocommerce' ) . '</span>
					<a id="loaded_order_url" href="' . $order->get_edit_order_url() . '" target="_blank">#' . $order->get_order_number() . '</a>
				</h2>';
		}

		// custom fields
		$custom_fields_options          = $this->extract_field_from_option( $option_handler->get_option( 'order_custom_fields' ) );
		$customer_custom_fields_options = array_merge(
		    $this->extract_field_from_option( $this->option_handler->get_option( 'customer_custom_fields_at_top' ) ),
		    $this->extract_field_from_option( $this->option_handler->get_option( 'customer_custom_fields' ) )
		);

		/**
		 * Do not use $order->get_meta_data() !
		 * The method does not returns internal order meta values
		 * @see WC_Order::get_meta_data()
		 * @see WC_Order_Data_Store_CPT::$internal_meta_keys
		 * @see WC_Data_Store_WP::read_meta()
		 * @see WC_Data_Store_WP::exclude_internal_meta_keys()
		 *
		 * But we can use get_meta() method, right?
		 * @see WC_Order::get_meta()
		 * Not really.
		 *
		 * E.g. '_order_currency'.
		 * No doubt the key is internal, but WC_Data::is_internal_meta_key check fails because
		 * setter 'set__order_currency' or getter 'get__order_currency' not existing. So, you cannot get value.
		 * Ofc, this will not work for a key without '_' prefix.
		 * @see WC_Data::is_internal_meta_key()
		 *
		 * To get currency you should call $order->get_currency() ( $order->get_order_currency() is deprecated ).
		 * @see WC_Order::get_currency()
		 * @see WC_Order::get_order_currency()
		 */
		foreach ( array_keys( $custom_fields_options ) as $key ) {
			$value = get_post_meta( $order_id, $key, true );

			if ( $value ) {
				$cart['custom_fields'][ $key ] = $value;
			}
		}

		foreach ( $order->get_meta_data() as $meta ) {
			if ( in_array( str_replace( '_wpo_customer_meta_', '', $meta->key ), array_keys( $customer_custom_fields_options ) ) ) {
				$cart['customer']['custom_fields'][ str_replace( '_wpo_customer_meta_', '', $meta->key ) ] = $meta->value;
			} elseif ( in_array( str_replace( '_wpo_customer_', '', $meta->key ), $this->customer_addition_full_keys() ) ) {
				$cart['customer'][ str_replace( '_wpo_customer_', '', $meta->key ) ] = $meta->value;
			};
		}

                if (!isset($cart['custom_fields'])) {
                    $cart['custom_fields'] = array();
                }

		if ( isset( $cart['custom_fields'], $cart['customer']['custom_fields'] ) && $option_handler->get_option('replace_order_with_customer_custom_fields') ) {
			$cart['custom_fields'] = array_merge( $cart['custom_fields'], apply_filters('wpo_customer_custom_fields', $cart['customer']['custom_fields']) );
		}


                $cart['custom_fields_values'] = array();

		if ( $order->get_status() !== self::ORDER_STATUS_COMPLETED ) {
			$cart['order_payment_url'] = $order->get_checkout_payment_url();
		}

		if ( $mode === 'edit' || $mode === 'view' ) {
			$order_status = $order->get_status();
			if ( wc_is_order_status( 'wc-' . $order_status ) ) {
				$order_status = 'wc-' . $order_status;
			}
		} else {
			$order_status = $this->option_handler->get_option( 'order_status' );
		}

		$cart['payment_method'] = $order->get_payment_method();

                $result = array(
			'message'            => $message,
			'loaded_order_id'    => $order_id,
			'loaded_order_number'=> $order->get_order_number(),
			'cart'               => $cart,
			'deleted_items'      => $deleted_items,
			'out_of_stock_items' => $out_of_stock_items,
			'order_date_timestamp' => $order_date_timestamp,
			'order_status'       => $order_status,
		);

		return apply_filters("wpo_load_order_data", $result, $order, $mode === 'edit');
	}

	protected function render_order_date_column( $date ) {
		$order_timestamp = $date->getTimestamp();

		if ( $order_timestamp > strtotime( '-1 day', current_time( 'timestamp', true ) ) ) {
			$show_date = sprintf(
			/* translators: %s: human-readable time difference */
				_x( '%s ago', '%s = human-readable time difference', 'woocommerce' ),
				human_time_diff( $date->getTimestamp(), current_time( 'timestamp', true ) )
			);
		} else {
			$show_date = $date->date_i18n( apply_filters( 'woocommerce_admin_order_date_format',
				__( 'M j, Y', 'woocommerce' ) ) );
		}

		return $show_date;
	}

	protected function ajax_put_on_draft( $data ) {
                $result_update = $this->update_cart( $data['cart'] );
		if ( $result_update instanceof WC_Data_Exception ) {
			return $this->wpo_send_json_error( $result_update->getMessage() );
		}
		if ( isset( $data['cart']['drafted_order_id'] ) && $data['cart']['drafted_order_id'] ) {
			$order_id = $data['cart']['drafted_order_id'];
			$created_date_time = ! empty( $data['created_date_time'] ) ? $data['created_date_time'] : '';
			$message = $this->update_order( $order_id, $data['cart'], 'draft', $created_date_time );
			if ( ! $message ) {
                                die;
				return $this->wpo_send_json_error();
			}
		} else {
			$result = $this->create_order( $data, $set_status = false );
			if ( is_array( $result ) && isset( $result['success'] ) && $result['success'] == false ) {
				return $result;
			}
			$order_id = $result;

			$order    = wc_get_order( $order_id );
			$message  = sprintf( __( 'Order #%s created and put on hold', 'phone-orders-for-woocommerce' ),
				$order->get_order_number() );
			wp_update_post( array(
				'ID'          => $order_id,
				'post_status' => 'draft',
				)
			);
		}
		$loaded_order = $this->load_order($order_id);

		$result = array(
			'drafted_order_id' => $order_id,
			'order_number'	   => $loaded_order['loaded_order_number'],
			'message'          => $message,
			'cart'             => $loaded_order['cart'],
			'order_message'    => $loaded_order['message'],
		);

		return $this->wpo_send_json_success( $result );
	}

	protected function update_order( $order_id, $cart, $new_status = '', $created_date_time = '' ) {
//		$cart = $data['cart'];

//		$order_id = $cart['drafted_order_id'];
		$order    = wc_get_order( $order_id );
		if ( !$order ) {
			return false;
		}
		$available_gateways = WC()->payment_gateways->get_available_payment_gateways();

		$this->disable_email_notifications($_enabled = true);

		$checkout = WC()->checkout();

		$cart_hash = md5( json_encode( wc_clean( WC()->cart->get_cart_for_session() ) ) . WC()->cart->total );

		$billing_address  = array();
		$shipping_address = array();
		$use_shipping_address = ( isset( $cart['customer']['ship_different_address'] ) AND 'true' == $cart['customer']['ship_different_address'] );
		foreach ( $cart['customer'] as $key => $value ) {
			if ( stripos( $key, 'billing_' ) !== false ) {
				$billing_address[ str_replace( 'billing_', '', $key ) ] = $value;
				if ( ! $use_shipping_address )
				{
					$shipping_address[ str_replace( 'billing_', '', $key ) ] = $value;
				}
			} elseif ( $use_shipping_address AND stripos( $key, 'shipping_' ) !== false ) {
				$shipping_address[ str_replace( 'shipping_', '', $key ) ] = $value;
			}
		}
		$order->set_customer_id( $cart['customer']['id'] );
		$order->set_address( $billing_address, 'billing' );
		$order->set_address( $shipping_address, 'shipping' );
		$this->maybe_update_tax_exempt( $order, $cart );

		$order->set_cart_hash( $cart_hash );
		$order->set_currency( get_woocommerce_currency() );

		$order->set_customer_note( isset( $cart['customer_note'] ) ? $cart['customer_note'] : '' );
		$private_note = get_post_meta( $order_id, $this->meta_key_private_note, true );
		if ( isset( $cart['private_note'] ) AND $cart['private_note'] != $private_note ) {
			$order->add_order_note( $cart['private_note'], false, true );
			update_post_meta( $order_id, $this->meta_key_private_note, $cart['private_note'] );
		}

		$order->set_payment_method( isset( $available_gateways[ $cart['payment_method'] ] ) ? $available_gateways[ $cart['payment_method'] ] : $cart['payment_method'] );
		$order->set_shipping_total( WC()->cart->get_shipping_total() );
		$order->set_discount_total( WC()->cart->get_discount_total() );
		$order->set_discount_tax( WC()->cart->get_discount_tax() );
		$order->set_cart_tax( WC()->cart->get_cart_contents_tax() + WC()->cart->get_fee_tax() );
		$order->set_shipping_tax( WC()->cart->get_shipping_tax() );
		$order->set_total( WC()->cart->get_total( 'edit' ) );


		$order->remove_order_items( 'tax' );
		$order->remove_order_items( 'shipping' );
		$order->remove_order_items( 'fee' );
		$order->remove_order_items( 'coupon' );

		do_action('wpo_gift_card_remove_order_lines', $order);

		$cart_order_item_ids = array_filter( array_map( function ( $cart_item ) {
			return isset( $cart_item['order_item_id'] ) ? $cart_item['order_item_id'] : false;
		}, WC()->cart->get_cart() ) );

		$qty_change_order_notes = array();

		foreach ( $order->get_items() as $item ) {
			if ( ! in_array( $item->get_id(), $cart_order_item_ids ) ) {
				$changed_stock = wc_maybe_adjust_line_item_product_stock( $item, 0 );
				if ( $changed_stock && ! is_wp_error( $changed_stock ) ) {
					$qty_change_order_notes[] = $item->get_name() . ' &ndash; ' . $changed_stock['from'] . '&rarr;' . $changed_stock['to'];
				}
				$order->remove_item( $item->get_id() );
			}
		}

                add_action('woocommerce_checkout_create_order_line_item', array($this, 'action_woocommerce_checkout_create_order_line_item'), 10, 4);
        add_filter( 'woocommerce_checkout_create_order_line_item_object', array($this, 'woocommerce_checkout_replace_order_item'), 10, 4 );

		$checkout->create_order_line_items( $order, WC()->cart );

                remove_action('woocommerce_checkout_create_order_line_item', array($this, 'action_woocommerce_checkout_create_order_line_item'));
		remove_filter( 'woocommerce_checkout_create_order_line_item_object', array($this, 'woocommerce_checkout_replace_order_item'), 10);

                $checkout->create_order_fee_lines( $order, WC()->cart );
		$checkout->create_order_shipping_lines( $order, WC()->session->get( 'chosen_shipping_methods' ),
			WC()->shipping->get_packages() );
		$checkout->create_order_tax_lines( $order, WC()->cart );
		$checkout->create_order_coupon_lines( $order, WC()->cart );

		do_action('wpo_gift_card_create_order_lines', $order, WC()->cart);

		foreach ( WC()->cart->get_coupons() as $code => $coupon ) {
			if ( $code == $this->option_handler->get_option( 'manual_coupon_title' ) ) {
				$result                    = array(
					'code'   => $this->option_handler->get_option( 'manual_coupon_title' ),
					'type'   => $coupon->get_discount_type(),
					'amount' => $coupon->get_amount(),
					'id'     => - 1,
				);
				if ( ! add_post_meta( $order->get_id(), $this->option_handler->get_option( 'manual_coupon_title' ), $result, true ) ) {
					update_post_meta( $order->get_id(), $this->option_handler->get_option( 'manual_coupon_title' ), $result );
				}
				break;
			}
		}
		$created_date_time = (int)$created_date_time;
		if ( ! empty( $created_date_time ) && is_integer( $created_date_time ) ) {
			$order->set_date_created( $created_date_time );
		}
		$order->save();

		$this->clear_cart_for_switch_user( $cart['customer']['id'] );

		do_action( 'woocommerce_checkout_update_order_meta', $order_id, array() );

		if ( $new_status AND $new_status != get_post_status($order_id) ) {

			$old_status  = get_post_status($order_id);
			$_new_status = wc_is_order_status( 'wc-' . get_post_status( $order_id ) ) ? 'wc-' . $new_status : $new_status;

			$order->add_order_note( sprintf(__( 'Order status changed in Phone Orders from %s to %s.', 'phone-orders-for-woocommerce' ), wc_get_order_status_name($old_status), wc_get_order_status_name($new_status)), false, true );

			$order->set_status($_new_status);

			$order->save();
		} else {
		    $order->add_order_note( __( 'Order edited in Phone Orders.', 'phone-orders-for-woocommerce' ), false, true );
		}

		$wc_order = wc_get_order($order_id);

		if ( in_array( $wc_order->get_status(), array( 'processing', 'completed', 'on-hold' ) ) ) {
		    foreach ( $wc_order->get_items() as $item ) {
			    $changed_stock = wc_maybe_adjust_line_item_product_stock( $item );
			    if ( $changed_stock && ! is_wp_error( $changed_stock ) ) {
				    $qty_change_order_notes[] = $item->get_name() . ' (' . $changed_stock['from'] . '&rarr;' . $changed_stock['to'] . ')';
			    }
		    }
		}

		if ( ! empty( $qty_change_order_notes ) ) {
			/* translators: %s item name. */
			$order->add_order_note( sprintf( __( 'Adjusted stock: %s', 'woocommerce' ), implode( ', ', $qty_change_order_notes ) ), false, true );
		}

		do_action( 'wpo_order_updated', wc_get_order($order_id) ,$cart );

		$this->disable_email_notifications($_enabled = false);

		$message = sprintf( __( 'Order #%s updated', 'phone-orders-for-woocommerce' ),
			$order->get_order_number() );

		return $message;
	}

	public function woocommerce_checkout_replace_order_item( $item, $cart_item_key, $values, $order ) {
		if ( isset( $values['order_item_id'] ) ) {
			$item = new WC_Order_Item_Product( $values['order_item_id'] );
		}

		return $item;
	}

	/**
	 * @param $order WC_order
	 * @param $cart array
	 */
	protected function maybe_update_tax_exempt( $order, $cart ) {
		if ( isset( $cart['customer']['is_vat_exempt'] ) ) {
			$tax_exempt = $cart['customer']['is_vat_exempt'] ? 'yes' : 'no';
			$order->update_meta_data( $this->meta_key_tax_exempt, $tax_exempt );
			$order->save_meta_data();
		}
	}

	protected function create_order( $data, $set_status = true ) {
		$result = parent::create_order( $data, $set_status );
		if ( is_array( $result ) && isset( $result['success'] ) && $result['success'] == false ) {
			return $result;
		}
		$order_id = $result;

		if ( ! is_integer($order_id) ) {
		    return $order_id;
        }

		$order    = wc_get_order( $order_id );
		if ( ! $order ) {
		    return false;
        }

		if ( ! empty( $data['cart'] ) ) {
			$this->maybe_update_tax_exempt( $order, $data['cart'] );
		}

		do_action( 'wpo_order_created_pro', $order, $data['cart'] );

		return $order_id;
	}

	protected function ajax_update_order( $data ) {
		$result_cart_update = $this->update_cart( $data['cart'] );
		if ( $result_cart_update instanceof WC_Data_Exception ) {
			return $this->wpo_send_json_error( $result_cart_update->getMessage() );
		}
		if ( isset( $data['order_id'] ) ) {
			$order_id = $data['order_id'];
			$created_date_time = ! empty( $data['created_date_time'] ) ? $data['created_date_time'] : '';
			$order_status = ! empty( $data['order_status'] ) ? $data['order_status'] : '';
			$message = $this->update_order( $order_id, $data['cart'], $order_status, $created_date_time );
			if ( ! $message ) {
				return $this->wpo_send_json_error();
			}
			$loaded_order = $this->load_order($order_id);
			unset($loaded_order['cart']['loaded_order']);
			unset($loaded_order['cart']['loaded_order_id']);
			$loaded_order['cart']['edit_order_id'] = (int)$order_id;
			$result = array(
				'order_id'      => $order_id,
				'cart'          => $loaded_order['cart'],
				'order_message' => $loaded_order['message'],
				'message'       => $message,
			);

			return $this->wpo_send_json_success( $result );
		}
		return $this->wpo_send_json_error();
	}

	protected function ajax_move_from_draft( $data ) {
		$order_id = isset($data['drafted_order_id']) ? $data['drafted_order_id'] : 0;
		if ( ! $order_id ) {
			return $this->wpo_send_json_error();
		}

		$result_update = $this->update_cart( $data['cart'] );

		if ( $result_update instanceof WC_Data_Exception ) {
			return $this->wpo_send_json_error( $result_update->getMessage() );
		}

		$created_date_time = ! empty( $data['created_date_time'] ) ? $data['created_date_time'] : '';
		$message = $this->update_order( $order_id, $data['cart'], 'draft', $created_date_time );

		if ( ! $message ) {
			die;
			return $this->wpo_send_json_error();
		}

		$order       = wc_get_order( $order_id );
		$payment_url = $order->get_checkout_payment_url();

		$created_date_time = ! empty( $data['created_date_time'] ) ? (int) $data['created_date_time'] : '';
		if ( $created_date_time && is_integer( $created_date_time ) ) {
			$order->set_date_created( $created_date_time );
		}
		$order->save();

		$new_order_status = ! empty( $data['order_status'] ) ? $data['order_status'] : 'wc-pending';
		$wc_order_statuses = wc_get_order_statuses();
		$new_order_status_title = isset( $wc_order_statuses[ $new_order_status ] ) ? $wc_order_statuses[ $new_order_status ] : $new_order_status;

		$message = sprintf( __( 'Order #%s has status "%s"', 'phone-orders-for-woocommerce' ), $order_id, $new_order_status_title );

		$order->set_status($new_order_status);
		$order->save();

		$result = array(
			'order_id'	     => $order_id,
			'order_number'	     => $order->get_order_number(),
			'message'	     => $message,
			'payment_url'	     => $payment_url,
			'allow_refund_order' => $this->get_allow_refund_order($order),
		);

		return $this->wpo_send_json_success( $result );
	}

	protected function ajax_set_payment_cookie( $data ) {
		$order_id = isset( $data['order_id'] ) ? $data['order_id'] : 0;
		if ( ! $order_id ) {
			return $this->wpo_send_json_error( new WP_Error( 'incorrect_parameter', __('Incorrect order ID', 'phone-orders-for-woocommerce') ) );
		}

		$order = wc_get_order($order_id);
		if ( ! $order ) {
			return $this->wpo_send_json_error( new WP_Error( 'error_get_order', __('Error when getting order', 'phone-orders-for-woocommerce') ) );
        }

		$referrer = array(
		    'is_frontend' => $data['is_frontend'],
		    'url'	  => $data['referrer'],
		);

		$cart_id	 = uniqid();
		$current_user_id = get_current_user_id();

		$result = WC_Phone_Orders_Cookie_Helper::set_payment_cookie( $order->get_customer_id(), $cart_id, $current_user_id, $referrer );
        if ( $result === true ) {
		    return $this->wpo_send_json_success();
        } elseif ( is_wp_error($result) ) {
		    return $this->wpo_send_json_error( $result );
        }
	}

	protected function ajax_mark_as_paid( $data ) {
		$order_id = isset( $data['order_id'] ) ? $data['order_id'] : 0;
		if ( ! $order_id ) {
			return $this->wpo_send_json_error( new WP_Error( 'incorrect_parameter', __('Incorrect order ID', 'phone-orders-for-woocommerce') ) );
		}

		$order = wc_get_order($order_id);
		if ( ! $order ) {
			return $this->wpo_send_json_error( new WP_Error( 'error_get_order', __('Error when getting order', 'phone-orders-for-woocommerce') ) );
		}

		$result = $order->payment_complete();
		if(is_wp_error($result)) {
			return $this->wpo_send_json_error($result);
		}

		$mark_as_paid_status = $this->option_handler->get_option('mark_as_paid_status');
		$result = $order->update_status($mark_as_paid_status);
		if( $result === true ) {
			$message = sprintf( __( 'Order #%s marked as paid', 'phone-orders-for-woocommerce' ), $order->get_order_number() );
			$data['order_status'] = $mark_as_paid_status;
			$data['message']	  = $message;
			return $this->wpo_send_json_success($data);
		} elseif ( is_wp_error($result) ) {
			return $this->wpo_send_json_error( $result );
		}
	}

	protected function create_additional_query_args( $data ) {
		$additional_query_args = array();
		$params = isset( $data['additional_parameters'] ) ? $data['additional_parameters'] : array();
		if ( isset( $params ) ) {
			if ( isset( $params['category_slug'] ) ) {
				$additional_query_args['category'] = array( $params['category_slug'] );
			}
			if ( isset( $params['tag_slug'] ) ) {
				$additional_query_args['tag'] = array( $params['tag_slug'] );
			}
		}

		return $additional_query_args;
	}

	protected function ajax_prepare_to_redirect( $data ) {
		WC_Phone_Orders_Loader_Pro::disable_object_cache();
		$where = isset($data['where']) ? $data['where'] : false;
		if ( ! $where ) {
			return $this->wpo_send_json_error( new WP_Error( 'incorrect_parameter', __('Incorrect redirect destination', 'phone-orders-for-woocommerce') ) );
		}

		$cart = isset($data['cart']) ? $data['cart'] : false;
		if ( ! $cart ) {
			return $this->wpo_send_json_error( new WP_Error( 'incorrect_parameter', __('Incorrect cart data', 'phone-orders-for-woocommerce') ) );
		}

		//refresh cart
		$result = $this->update_cart( $cart );
		if ( $result instanceof WC_Data_Exception ) {
			return $this->wpo_send_json_error( $result->getMessage() );
		}
		if ( count( $result['deleted_items'] ) ) {
			return false;
		}

		$customer_id = (integer)$cart['customer']['id'];
		//green buttons save Customer too!
		if ( $this->option_handler->get_option( 'update_customers_profile_after_create_order' ) ) {
			$this->save_customer_data( $cart['customer'] );
		}

		$referrer = array(
		    'is_frontend' => $data['is_frontend'],
		    'url'	  => $data['referrer'],
		);

		$cart_id	 = uniqid();
		$current_user_id = get_current_user_id();

		if ($where !== 'checkout_link') {
		    $result = WC_Phone_Orders_Cookie_Helper::set_payment_cookie( $customer_id, $cart_id, $current_user_id, $referrer );
		    if ( $result !== true ) {
			    return $this->wpo_send_json_error();
		    } elseif ( is_wp_error($result) || $result !== true) {
			    return $this->wpo_send_json_error( $result );
		    }
		}

		$result = set_transient( $cart_id . '_temp_cart', $cart );

		if ( $result ) {
		    $data = array();
		    if ( $where == 'cart' ) {
		        $data['url'] = wc_get_cart_url();
            } elseif ( $where == 'checkout' ) {
			    $data['url'] = wc_get_checkout_url();
            } elseif ( $where == 'checkout_link' ) {
		    $data['url'] = $customer_id && is_super_admin($customer_id) ? '' : add_query_arg( array('wpo_checkout_link' => $cart_id), home_url() );
            }
			return $this->wpo_send_json_success($data);
        } else {
		    return $this->wpo_send_json_error();
        }

    }

	/**
	 * @param array $data
	 *
	 * @return WC_Product_Simple
	 * @throws WC_Data_Exception
	 */
	protected function create_item( $data ) {
		$create = ! empty( $data['create_product'] );

		if ( $create ) {
			$product = $this->custom_prod_control->create_custom_product();
			$this->set_created_item_props( $product, $data );
            if ($this->option_handler->get_option('create_private_product')) {
                $product->set_status('private');
            }
			$product->save();
		} else {
			$product = $this->custom_prod_control->load_product();
			$this->set_created_item_props( $product, $data );
		}

		do_action( 'wpo_create_custom_product', $product->get_id(), $product );

		return $product;
	}

	/**
	 * @param WC_Product_Simple $product
	 * @param array $data
	 *
	 * @throws WC_Data_Exception
	 */
	protected function set_created_item_props( &$product, $data ) {
		parent::set_created_item_props( $product, $data );

		if ( isset( $data['tax_class']['slug'] ) && $this->option_handler->get_option( 'new_product_ask_tax_class' ) ) {
			$tax_class = $data['tax_class']['slug'];
		} else {
			$tax_class = $this->option_handler->get_option( 'item_tax_class' );
		}

		if ( ! $tax_class ) {
			$product->set_tax_status( 'none' );
		} else {
			$product->set_tax_class( $tax_class );
		}

		if ( isset ( $data['category']['id'] ) && $this->option_handler->get_option( 'show_product_category' ) ) {
			$product->set_category_ids( array( $data['category']['id'] ) );
		} else {
			$product->set_category_ids( array( 0 ) );
		}

		if ( isset ( $data['weight'] ) && $this->option_handler->get_option( 'new_product_show_weight' ) ) {
			$product->set_weight( $data['weight'] );
		}

		if ( isset ( $data['length'] ) && $this->option_handler->get_option( 'new_product_show_length' ) ) {
			$product->set_length( $data['length'] );
		}

		if ( isset ( $data['width'] ) && $this->option_handler->get_option( 'new_product_show_width' ) ) {
			$product->set_width( $data['width'] );
		}

		if ( isset ( $data['height'] ) && $this->option_handler->get_option( 'new_product_show_height' ) ) {
			$product->set_height( $data['height'] );
		}
	}

	protected function create_customer( $request ) {
		if ( $this->option_handler->get_option( 'disable_creating_customers' ) ) {
			return new WP_Error( 'creating_customers_is_disabled',
				__( 'Creating customers is disabled', 'phone-orders-for-woocommerce' ) );
		}

		if ( $this->option_handler->get_option( 'disable_new_user_email' ) ) {
			remove_action( 'woocommerce_created_customer', array( 'WC_Emails', 'send_transactional_email' ), 10 );
		}

		$data = $request['data'];
		$user_id = parent::create_customer( $request );
		if ( is_wp_error( $user_id ) ) {
			return $user_id;
		}

		$allowedCustomerRoles = $this->option_handler->get_option( 'allowed_roles_new_customer' );

		if ( ! empty( $data['role'] ) && ( ! $allowedCustomerRoles || in_array($data['role'], $allowedCustomerRoles) ) ) {
			$user = new WP_User( $user_id );
			$user->set_role( $data['role'] );
		}


		if ( isset ( $data['locale'] ) && $this->option_handler->get_option( 'newcustomer_show_language_field' ) ) {
			$user = new WP_User( $user_id );
                        $locale = sanitize_text_field( $data['locale'] );
                        if ( 'site-default' === $locale ) {
                                $locale = '';
                        } elseif ( '' === $locale ) {
                                $locale = 'en_US';
                        } elseif ( ! in_array( $locale, get_available_languages(), true ) ) {
                                $locale = '';
                        }
                        $user->locale = $locale;
                        wp_update_user($user);
		}

		if ( $this->option_handler->get_option( 'dont_fill_shipping_address_for_new_customer' ) ) {
			$shipping_fields = array(
				'first_name',
				'last_name',
				'company',
				'address_1',
				'address_2',
				'city',
				'postcode',
				'country',
				'state',
			);

			foreach ( $shipping_fields as $field ) {
				update_user_meta( $user_id, 'shipping_' . $field, '' );
			}

		}

		return $user_id;
	}

	protected function extract_field_from_option( $option_value ) {
		$full_options = WC_Phone_Orders_Add_Order_Page_Pro::full_extract_field_from_option( $option_value );

		$custom_fields_options = array();
		foreach ( $full_options as $option ) {
			$custom_fields_options[ $option['name'] ] = $option['value'];
		}

		return $custom_fields_options;
	}

	public static function full_extract_field_from_option( $option_value ) {
		$custom_fields_options = array();

		if ( $option_value ) {
			foreach ( preg_split( "/((\r?\n)|(\r\n?))/", $option_value ) as $line ) {
				$line = explode( '|', $line );

				// set default type if argument is missing
				if ( ! isset( $line[2] ) ) {
					$line[2] = 'text';
				}

				if ( ! isset( $line[1] ) ) {
					$line[1] = isset($line[0]) ? $line[0] : '';
				}

				if ( count( $line ) > 2 ) {
					$label = $line[0];
					$name  = $line[1];
					$type  = $line[2];
					$value = array();

					if ( "text" === $type || "time" === $type || "hidden" === $type  || "file" === $type || "date" === $type) {
					    $value = '';
					}

					if ( isset( $line[3] ) ) {
						if ( "text" === $type || "time" === $type || "hidden" === $type  || "file" === $type || "date" === $type) {
							$value = $line[3];
						} elseif ( "select" === $type || "radio" === $type || "checkbox" === $type ) {
							$values = array_slice( $line, 3 );

							foreach ( $values as $select_data ) {
								$select_data = explode('=', $select_data);

								if ( count( $select_data ) < 1 ) {
									continue;
								}

								$select_value = $select_data[0];

								if ( substr( $select_value, 0, strlen( "*" ) ) == "*" ) {
									$value[] = substr( $select_value, 1 );
								}
                            }

							if ( "checkbox" !== $type ) {
								$value = count( $value ) ? reset( $value ) : "";
							}
						}
					}

					$custom_fields_options[] = array(
						'label' => $label,
						'name'  => $name,
						'type'  => $type,
						'value' => apply_filters('wpo_custom_field_default_value', $value, $name, $type, $label),
					);
				}
			}
		}

		return $custom_fields_options;
	}

	protected function ajax_add_configured_products( $data ) {

	    $option_handler = $this->option_handler;

	    $hidden_order_itemmeta = apply_filters(
		    'woocommerce_hidden_order_itemmeta', array(
			    '_qty',
			    '_tax_class',
			    '_product_id',
			    '_variation_id',
			    '_line_subtotal',
			    '_line_subtotal_tax',
			    '_line_total',
			    '_line_tax',
			    'method_id',
			    'cost',
			    '_reduced_stock',
		    )
	    );

	    $hidden_order_itemmeta = array();

	    foreach ( WC()->cart->get_cart() as $key => $item_data ) {

		    if (apply_filters('wpo_add_configured_products_skip_item', false, $item_data)) {
			continue;
		    }

		    $product_id = ( $item_data['variation_id'] ) ? $item_data['variation_id'] : $item_data['product_id'];

		    $_product = wc_get_product( $product_id );

		    $item_custom_meta_fields = array();

		    $product_attrs = $_product->get_attributes();

		    if (isset($item_data['meta_data']) && is_array($item_data['meta_data'])) {
			foreach ($item_data['meta_data'] as $meta) {
							    if ( in_array( $meta->key, $hidden_order_itemmeta, true ) ) {
								    continue;
							    }

							    $d = $meta->get_data();
							    if (isset($product_attrs[$d['key']])) {
								    continue;
							    }
			    $item_custom_meta_fields[] = array(
				'id'         => $d['id'],
				'meta_key'   => $d['key'],
				'meta_value' => $d['value'],
			    );
			}
		    }

            $line_subtotal = ! empty( $item_data['subtotal'] ) ? $item_data['subtotal'] : $item_data['line_subtotal'];

            if ( wc_prices_include_tax() ) {
                $line_subtotal_tax = ! empty( $item_data['subtotal_tax'] ) ? $item_data['subtotal_tax'] : $item_data['line_subtotal_tax'];
                $item_cost         = ( $line_subtotal + $line_subtotal_tax ) / $item_data['quantity'];
            } else {
                $item_cost = $line_subtotal / $item_data['quantity'];
            }

		    $_item_data = array_merge($item_data, array(
			'item_cost'		 => $item_cost,
			'line_subtotal'		 => $line_subtotal,
			'custom_meta_fields'	 => $item_custom_meta_fields,
		    ));

		    $_item_data = apply_filters('wpo_add_configured_products_item_data', $_item_data, $item_data, $key, $_product);

		$data['cart']['items'][] = $this->get_item_by_product($_product, $_item_data);
	    };

	    $this->clear_cart_for_switch_user( $data['cart']['customer']['id'] );

	    return $this->ajax_recalculate( $data );
	}

	protected function ajax_multi_address_get_list( $data ) {
	    return $this->wpo_send_json_success(array(
		'list' => WC_Phone_Orders_Customer_Multi_Address::get_address_list(
		    isset($data['customer_id']) ? (int)$data['customer_id'] : null,
		    isset($data['billing_email']) ? $data['billing_email'] : null,
		    $data['address_type']
		),
	    ));
	}

	protected function ajax_multi_addresses_save_new_address( $data ) {

	    $address = WC_Phone_Orders_Customer_Multi_Address::save_new_address(
		$data['customer_id'],
		$data['address_type'],
		$data['address']
	    );

	    return $this->wpo_send_json_success(array(
		'address' => $address,
	    ));
	}

	protected function ajax_multi_addresses_update_address( $data ) {

	    $address = WC_Phone_Orders_Customer_Multi_Address::update_address(
		$data['customer_id'],
		$data['address_type'],
		$data['address_internal_name'],
		$data['address']
	    );

	    return $this->wpo_send_json_success(array(
		'address' => $address,
	    ));
	}

	protected function ajax_multi_addresses_delete_address( $data ) {

	    WC_Phone_Orders_Customer_Multi_Address::delete_address(
		$data['customer_id'],
		$data['address_type'],
		$data['address_internal_name']
	    );

	    return $this->wpo_send_json_success();
	}

	public function clear_shipping_address($customer, $request) {

	    if ( !isset($request['checked_ship_different_address']) || !$request['checked_ship_different_address'] ) {
		return $customer;
	    }

	    foreach ($customer as $key => &$value) {

		if (!preg_match('/(^|\_)shipping\_/i', $key)) {
		    continue;
		}

		$value = '';
	    }

	    return $customer;
	}

	protected function ajax_get_order_history_customer( $data ) {

	    $items = array();

	    $orders = wc_get_orders(array(
		'limit'    => -1,
		'customer' => isset($data['customer_id']) ? $data['customer_id'] : 0,
	    ));

	    $no_transactions = 0;
	    $total_paid	     = 0;
	    $total	     = 0;

	    foreach ($orders as $wc_order) {

		$_items = array();

		foreach( $wc_order->get_items() as $_item) {

		    $line = $_item['name'];

		    if($_item['qty'] > 1) {
			$line .= ' x '. $_item['qty'];
		    }

		    $_items[] = $line;
		}

		$items[] = apply_filters('wpo_order_history_customer_table_row', array(
		    'order_number'	    => $wc_order->get_order_number(),
		    'date'	    => $wc_order->get_date_created()->date_i18n( apply_filters( 'woocommerce_admin_order_date_format', __( 'M j, Y H:i', 'woocommerce' ) ) ),
		    'items'	    => join(", ", $_items),
		    'status'	    => wc_get_order_status_name($wc_order->get_status()),
		    'payment_type'  => method_exists($wc_order, 'get_payment_method_title') ? $wc_order->get_payment_method_title() : '',
		    'total'	    => $wc_order->get_formatted_order_total(),
		), $wc_order);

		$no_transactions++;

		if ( method_exists($wc_order, 'is_paid') && $wc_order->is_paid() ) {
		    $total_paid += $wc_order->get_total();
		}

		$total += $wc_order->get_total();
	    }

	    return $this->wpo_send_json_success(array(
		'items'   => $items,
		'summary' => array(
		    'no_transactions' => $no_transactions,
		    'total_paid'      => wc_price($total_paid),
		    'total'	      => wc_price($total),
		),
	    ));
	}

	protected function ajax_load_find_products( $data ) {

	    do_action('wpo_before_load_find_products', $data);

	    $items = array();

	    $item_ids = isset($data['ids']) ? $data['ids'] : array();

	    $delimiter = apply_filters( 'wpo_autocomplete_product_fields_delimiter', '|' );
	    $hide_image = $this->option_handler->get_option( 'autocomplete_product_hide_image' );

	    $customer_id = isset($data['customer_id']) ? $data['customer_id'] : 0;

	    $old_user_id = false;

	    if ( $customer_id AND apply_filters( 'wpo_must_switch_cart_user', $this->option_handler->get_option( 'switch_customer_while_calc_cart' ) ) ) {
		$old_user_id = get_current_user_id();
		wp_set_current_user( $customer_id );
		do_action( 'wdp_after_switch_customer_while_calc' );
	    }

	    foreach ( $item_ids as $iid ) {

		$item = wc_get_product( $iid );

		if ( ! $item ) {
		    continue;
		}

		$title = $this->format_row_product( $item, $delimiter );

		$image_url = "";

		if( ! $hide_image ) {
		    $image_url = $this->get_thumbnail_src_by_product($item);
		}

		$items[$iid] = apply_filters('wpo_search_products_result_item', array(
		    'title'	    => $title,
		    'product_id'    => $iid,
		    'img'	    => $image_url,
		    'permalink'	    => get_permalink($iid),
		    'product_link'  => admin_url( 'post.php?post=' . absint( $iid ) . '&action=edit' ),
		), $iid, $item);
	    }

	    //switch back to admin
	    if ( $old_user_id ) {
		wp_set_current_user( $old_user_id );
	    }

	    return $this->wpo_send_json_success($items);
	}

	protected function ajax_full_refund_order( $data ) {

	    $order_id = $data['order_id'];

	    try {
		$order = wc_get_order( $order_id );

		$refund_amount = $order->get_remaining_refund_amount();

		// Prepare line items which we are refunding.
		$line_items = array();

		$line_item_types = array( 'line_item', 'fee', 'shipping' );

		$order_taxes = $order->get_taxes();

		foreach ($line_item_types as $type) {
		    foreach ( $order->get_items( $type ) as $item_id => $line_item ) {

			$qty = $type === 'line_item' ? $line_item->get_quantity() - $order->get_qty_refunded_for_item($item_id, $type) : 0;

			$refund_tax = array();
			$tax_data   = wc_tax_enabled() ? $line_item->get_taxes() : false;

			if ($tax_data) {
			    foreach ( $order_taxes as $tax_item ) {
				$tax_item_id       = $tax_item->get_rate_id();
				$tax_item_total    = isset( $tax_data['total'][ $tax_item_id ] ) ? $tax_data['total'][ $tax_item_id ] : '';
				$refund_tax[$tax_item_id] = $tax_item_total;
			    }
			}

			$line_items[ $item_id ] = array(
			    'qty'          => $qty,
			    'refund_total' => wc_format_decimal( $line_item->get_total() - $order->get_total_refunded_for_item($item_id, $type) ),
			    'refund_tax'   => array_filter( array_map( 'wc_format_decimal', $refund_tax ) ),
			);
		    }
		}

		// Create the refund object.
		$refund = wc_create_refund(
		    array(
			'amount'         => $refund_amount,
			'reason'         => '',
			'order_id'       => $order_id,
			'line_items'     => $line_items,
			'refund_payment' => false,
			'restock_items'  => true,
		    )
		);

		if ( is_wp_error( $refund ) ) {
			throw new Exception( $refund->get_error_message() );
		}

	    } catch ( Exception $e ) {
		    return wp_send_json_error( array( 'error' => $e->getMessage() ) );
	    }

	    $order = wc_get_order( $order_id );

	    $message = sprintf( __( 'Order #%s was  refunded', 'phone-orders-for-woocommerce' ), $order->get_order_number() );

	    $result = array(
		'order_id'	     => $order_id,
		'order_number'	     => $order->get_order_number(),
		'order_status'	     => 'wc-' . $order->get_status(),
		'message'	     => $message,
		'allow_refund_order' => $this->get_allow_refund_order($order),
	    );

	    return $this->wpo_send_json_success($result);
	}

	protected function ajax_restore_cart( $data ) {

	    $cart_data = WC_Phone_Orders_Switch_User::get_data_from_cookie_name( WC_PHONE_CART_COOKIE );

	    $cart_id = isset($cart_data['cart_id']) ? $cart_data['cart_id'] : 0;

	    if (!$cart_id) {
		return $this->wpo_send_json_error();
	    }

	    $cart = get_transient($cart_id . '_temp_cart');

	    WC_Phone_Orders_Switch_User::clear_cart_cookie_data();

	    if (!$cart) {
		return $this->wpo_send_json_error();
	    }

	    if (isset($cart['applied_shipping'])) {
		unset($cart['applied_shipping']);
	    }

	    if (isset($cart['applied_payment_method'])) {
		unset($cart['applied_payment_method']);
	    }

	    $response		    = array();
	    $response['cart']	    = $cart;
	    $response['log_row_id'] = uniqid();

	    $result = $this->update_cart( $response['cart'] );

	    if ( $result instanceof WC_Data_Exception ) {
		return $this->wpo_send_json_error( $result->getMessage() );
	    }

	    WC_Phone_Orders_Tabs_Helper::add_log( $response['log_row_id'], $result);

	    return $this->wpo_send_json_success( $response );
	}

	protected function ajax_find_orders_customers( $data ) {

                $term               = $data['term'];
                $allowed_post_types = array('shop_order');

		$orders_ids = $this->search_orders( str_replace( 'Order #', '', wc_clean( $term ) ));
                rsort( $orders_ids );

		$limit  = (int)apply_filters('wpo_find_orders_limit', 20);
                $result = array();

		foreach ( $orders_ids as $order_id ) {

                        $order = wc_get_order( $order_id );

                        if ( ! $order || ! in_array($order->get_type(), $allowed_post_types) ) {
				continue;
			}

			if ( $this->option_handler->get_option( 'show_orders_current_user' ) && (int)$order->get_meta( WC_Phone_Orders_Loader::$meta_key_order_creator ) !== get_current_user_id() ) {
				continue;
			}

			if ( ! wc_is_order_status('wc-' . $order->get_status()) AND 'draft' != $order->get_status()){
				continue;
			}

			$formated_output_array = array(
				__( 'order # ', 'phone-orders-for-woocommerce' ) . $order->get_order_number(),
				$order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
				$this->render_order_date_column( $order->get_date_created() ),
				get_woocommerce_currency_symbol() . ' ' . $order->get_total(),
				wc_get_order_status_name( $order->get_status() )
			);

			$formated_output = implode( " | ", $formated_output_array );

			$result[] = array(
				'formated_output'	=> $formated_output,
				'loaded_order_url'	=> $order->get_edit_order_url(),
				'loaded_order_id'	=> $order->get_id(),
				'loaded_order_number'   => $order->get_order_number(),
			);

			if( count($result) > $limit )
				break;
		}

		wp_send_json( $result );
	}

	public function get_customer_custom_fields($customer_data, $customer_obj) {

	    $custom_fields                  = array();
	    $customer_custom_fields_options = array_merge(
		$this->extract_field_from_option( $this->option_handler->get_option( 'customer_custom_fields_at_top' ) ),
		$this->extract_field_from_option( $this->option_handler->get_option( 'customer_custom_fields' ) )
	    );

	    foreach ( $customer_custom_fields_options as $key => $default_value ) {
		if ( $customer_obj && $customer_obj->meta_exists( $key ) ) {
			if ( is_callable( array( $customer_obj, "get_$key" ) ) ) {
				$value = $customer_obj->{"get_$key"}();
			} else {
				$value = $customer_obj->get_meta( $key );
			}
                } else {
			        $value = $default_value;
                }

		    $custom_fields[ $key ] = $value;
	    }
	    $customer_data['custom_fields'] = $custom_fields;

	    return $customer_data;
	}

	public function customer_order_history_summary($customer_data) {

	    if (!$customer_data['id']) {
		return $customer_data;
	    }

	    $orders = wc_get_orders(array(
		'limit'    => -1,
		'customer' => $customer_data['id'],
	    ));

	    $no_transactions = 0;
	    $total_paid	 = 0;

	    foreach ($orders as $wc_order) {

		$no_transactions++;

		if ( method_exists($wc_order, 'is_paid') && $wc_order->is_paid() ) {
		    $total_paid += $wc_order->get_total();
		}
	    }

	    $customer_data['order_history_summary'] = array(
		'no_transactions' => $no_transactions,
		'total_paid'      => wc_price($total_paid),
	    );

	    return $customer_data;
	}

}
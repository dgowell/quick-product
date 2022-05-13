<?php

class WC_Phone_Orders_Switch_User {

	private $clear_cookie_get_parameter_key = 'wpo_back_to_admin';
	private $settings;

	public function __construct() {

		add_action( 'plugins_loaded', function ()  {
		    include_once WC_PHONE_ORDERS_PRO_VERSION_PATH . 'classes/class-wc-phone-orders-settings-pro.php';
		    $this->settings = WC_Phone_Orders_Settings::getInstance();
		});

		/**
		 * We don't need 'login gate' during CLI
		 *
		 * @see https://www.php.net/manual/en/function.php-sapi-name.php
		 * @see https://www.php.net/manual/en/function.php-sapi-name.php#89858
		 */
		if ( php_sapi_name() !== 'cli' ) {
			add_action( 'init', array( $this, 'login_gate' ) );
		}
		add_filter( 'woocommerce_thankyou_order_received_text', function ( $text ) {
			if ( isset( $_COOKIE[ WC_PHONE_ADMIN_COOKIE ] ) ) {
				$text = $this->get_html_back_to_admin_area() . $text;
			}

			return $text;
		} );

		add_action( 'woocommerce_before_cart', function () {
			if ( isset( $_COOKIE[ WC_PHONE_ADMIN_COOKIE ] ) ) {
				echo $this->get_html_restore_cart();
				echo $this->get_html_back_to_admin_area();
			}
		} );

		add_action( 'woocommerce_cart_is_empty', function () {
			if ( isset( $_COOKIE[ WC_PHONE_ADMIN_COOKIE ] ) ) {
				echo $this->get_html_back_to_admin_area();
			}
		} );

		add_action( 'woocommerce_before_checkout_form', function ( $checkout ) {
			if ( isset( $_COOKIE[ WC_PHONE_ADMIN_COOKIE ] ) ) {
				echo $this->get_html_restore_cart();
				echo $this->get_html_back_to_admin_area();
			}
		} );

		add_action( 'wp_logout', function () {
			$this->clear_admin_cookie();
			self::clear_cart_cookie_data();
		} );
		add_filter( 'before_woocommerce_pay', function () {
			if ( isset( $_COOKIE[ WC_PHONE_ADMIN_COOKIE ] ) ) {
				echo $this->get_html_edit_order();
				echo $this->get_html_back_to_admin_area();
			}
		} );

		add_action('load-woocommerce_page_phone-orders-for-woocommerce', function() {
			$this->clear_admin_cookie();
			if ( ! isset( $_GET['restore_cart'] ) || ! $_GET['restore_cart'] ) {
			    self::clear_cart_cookie_data();
			}
		});

		add_action( 'before_woocommerce_init', function () {
			if ( isset( $_COOKIE[ WC_PHONE_ADMIN_COOKIE ] ) && ! ( is_admin() && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) ) {
				add_filter( 'woocommerce_session_handler', function () {
					return WC_Phone_Orders_Loader_Pro::get_session_handler_class();
				} );
				add_filter( 'woocommerce_persistent_cart_enabled', '__return_false', 1 );
			}
		});

		add_action( 'woocommerce_load_cart_from_session', function () {
		    if ( isset( $_COOKIE[ WC_PHONE_CART_COOKIE ] ) ) {
			if ($this->settings->get_option( 'sell_disable_variation' )) {
			    add_filter( 'woocommerce_variation_is_purchasable', function ($is_purchasable, $variation) {
				    return true;
			    },10,2);
			}
		    }
		} );

		add_action('parse_request', array($this, 'apply_checkout_link'));
	}

	private function get_html_back_to_admin_area() {
		$admin_url = admin_url( 'admin.php' );
		$admin_url = esc_url( add_query_arg( array(
			'page'          => 'phone-orders-for-woocommerce',
			$this->clear_cookie_get_parameter_key => 'yes',
		), $admin_url ) );

		$is_frontend	= 0;
		$referrer_data  = self::get_data_from_cookie_name(WC_PHONE_ADMIN_REFERRER_COOKIE);

		if (is_array($referrer_data) && isset($referrer_data['is_frontend'])) {
		    $is_frontend = $referrer_data['is_frontend'];
		}

		$title =  __( 'Back to admin area.', 'phone-orders-for-woocommerce' );

		if ($is_frontend) {
		    $title =  __( 'Back to frontend page.', 'phone-orders-for-woocommerce' );
		}

		return "<a href='$admin_url'>" . $title . "</a><hr><br>";
	}

	private function get_html_edit_order() {
		global $wp;
		$order_id = $wp->query_vars['order-pay'];

		$edit_order_page_url = admin_url( 'admin.php' );
		$edit_order_page_url = esc_url( add_query_arg( array(
			'page'				      => 'phone-orders-for-woocommerce',
			$this->clear_cookie_get_parameter_key => 'yes',
			'wpo_edit_order_id'		      => $order_id,
		), $edit_order_page_url ) );

		return "<a href='$edit_order_page_url'>" . __( 'Edit order', 'phone-orders-for-woocommerce' ) . "</a><hr><br>";
	}

	private function get_html_restore_cart() {
		global $wp;

		$edit_order_page_url = admin_url( 'admin.php' );
		$edit_order_page_url = esc_url( add_query_arg( array(
		    'page'				    => 'phone-orders-for-woocommerce',
		    $this->clear_cookie_get_parameter_key   => 'yes',
		    'wpo_restore_cart'			    => 1,
		), $edit_order_page_url ) );

		return "<a href='$edit_order_page_url'>" . __( 'Edit order', 'phone-orders-for-woocommerce' ) . "</a><hr><br>";
	}

	private function clear_admin_cookie() {
		setcookie( WC_PHONE_CUSTOMER_COOKIE, '', time() - 31536000, COOKIEPATH );
		setcookie( WC_PHONE_ADMIN_COOKIE, '', time() - 31536000, COOKIEPATH );
		setcookie( WC_PHONE_ADMIN_REFERRER_COOKIE, '', time() - 31536000, COOKIEPATH );
		do_action('wpo_switch_customer_clear_admin_cookie');
	}

	public static function clear_cart_cookie_data() {

	    $cart_data = self::get_data_from_cookie_name( WC_PHONE_CART_COOKIE );

	    $cart_id = isset($cart_data['cart_id']) ? $cart_data['cart_id'] : 0;

	    if ($cart_id) {
		delete_transient( $cart_id . '_temp_cart' );
	    }

	    setcookie( WC_PHONE_CART_COOKIE, '', time() - 31536000, COOKIEPATH );
	}

	public function login_gate() {
		$current_user_id = get_current_user_id();
		if ( is_admin() AND ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			if ( isset( $_COOKIE[ WC_PHONE_ADMIN_COOKIE ] ) ) {
				$admin_id = self::get_id_from_cookie_name( WC_PHONE_ADMIN_COOKIE );

				$referrer = null;

				$referrer_data = self::get_data_from_cookie_name(WC_PHONE_ADMIN_REFERRER_COOKIE);

				if (is_array($referrer_data) && isset($referrer_data['url'])) {
				    $referrer = $referrer_data['url'];
				}

				$current_url = $referrer ? $referrer : "//" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

				if ( isset( $_GET['wpo_edit_order_id'] ) ) {
				    $current_url = add_query_arg( array(
					'edit_order_id' => $_GET['wpo_edit_order_id'],
				    ), $current_url );
				}

				if ( isset( $_GET['wpo_restore_cart'] ) ) {
				    $current_url = add_query_arg( array(
					'restore_cart' => $_GET['wpo_restore_cart'],
				    ), $current_url );
				}

				$current_url = remove_query_arg( array($this->clear_cookie_get_parameter_key, 'wpo_edit_order_id', 'wpo_restore_cart'), $current_url );
				$current_url = apply_filters("wpo_url_back_to_admin_area", $current_url );
				if ( isset( $_GET[ $this->clear_cookie_get_parameter_key ] ) AND 'yes' === $_GET[ $this->clear_cookie_get_parameter_key ] ) {
					$this->clear_admin_cookie();
				}

				if ( ! isset( $_GET['wpo_restore_cart'] ) || ! $_GET['wpo_restore_cart'] ) {
				    self::clear_cart_cookie_data();
				}

				if ( $admin_id ) {
					if ( $current_user_id != $admin_id ) {
						do_action("wpo_before_switch_to_customer", $admin_id, $current_url);
						if ( $this->switch_user( $admin_id ) ) { // redirect admin after relogin!
							wp_redirect( $current_url );
							die();
						}
					} else {
						$this->clear_admin_cookie();
					}
				}

				wp_redirect( $current_url );
				exit;
			}
		} elseif ( ! is_admin() ) {
			$current_url = "//" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			//must set user cookies?
			if ( isset( $_COOKIE[ WC_PHONE_CUSTOMER_COOKIE ] ) ) {
				$customer_id = self::get_id_from_cookie_name( WC_PHONE_CUSTOMER_COOKIE );
				if ( $current_user_id != $customer_id ) {
					do_action("wpo_before_switch_to_customer", $customer_id, $current_url);
					$this->switch_user( $customer_id );
					//do redirect
					wp_redirect( $current_url );
					die();
				}
			}
		}

		if ( isset( $_COOKIE[ WC_PHONE_CART_COOKIE ] ) && ! is_admin() ) {
			$this->apply_cart_data();
		}
	}

	public static function get_customer_id() {
		WC_Phone_Orders_Loader_Pro::disable_object_cache();
		$admin_id = self::get_id_from_cookie_name( WC_PHONE_ADMIN_COOKIE );
		$trans_name       = $admin_id . '_temp_customer_id';
		$temp_customer_id = get_transient($trans_name);

		return $temp_customer_id;
	}

	private static function get_id_from_cookie_name( $cookie_name ) {
		$cookie = isset( $_COOKIE[ $cookie_name ] ) ? json_decode( stripslashes( $_COOKIE[ $cookie_name ] ) ) : false;
		if ( $cookie ) {
			return wp_validate_auth_cookie( $cookie, 'original_user' );
		} else {
			return false;
		}
	}

	public static function get_data_from_cookie_name( $cookie_name ) {
	    return isset( $_COOKIE[ $cookie_name ] ) ? json_decode( stripslashes( base64_decode($_COOKIE[ $cookie_name ]) ), true ) : false;
	}

	private function switch_user( $user_id ) {
		// known user?
		if ( $user_id AND ( $user = get_userdata( $user_id ) ) ) {
			wp_set_auth_cookie( $user_id );
			wp_set_current_user( $user_id, $user->user_login );
			do_action( 'wp_login', $user->user_login, $user );

			return true;
		} else {
			// do not put before wp_set_auth_cookie() because of many 'Set-Cookie' which causes Nginx error 502 Bad Gateway
			wp_clear_auth_cookie();
		}

		return false;
	}

	/**
	 * @param boolean $is_frontend
	 */
	protected function enable_payment_gateway( $is_frontend ) {
		$gateway = null;
		foreach ( WC()->payment_gateways()->payment_gateways() as $id => $available_gateway ) {
			if ( class_exists( "WC_Phone_Orders_Gateway" ) && $available_gateway instanceof WC_Phone_Orders_Gateway ) {
				$gateway = $available_gateway;
				break;
			}
		}

		if ( empty( $gateway ) ) {
			return;
		}

		if ( $gateway->is_enable_at_admin() || $is_frontend ) {
			$gateway->enable();
		}
	}

	private function apply_cart_data() {
		// prevent to merge with the cart stored in the user meta
		delete_user_meta( get_current_user_id(), '_woocommerce_load_saved_cart_after_login' );

		include_once WC_PHONE_ORDERS_PLUGIN_PATH . 'classes/tabs/helpers/class-wc-phone-orders-shipping-rate-mod.php';

	    WC_Phone_Orders_Loader_Pro::disable_object_cache();

	    $cart_data = self::get_data_from_cookie_name( WC_PHONE_CART_COOKIE );

	    $cart_id = isset($cart_data['cart_id']) ? $cart_data['cart_id'] : 0;

	    if (!$cart_id) {
		return;
	    }

	    $phone_order_cart = get_transient($cart_id . '_temp_cart');

	    $admin_id = $this->get_id_from_cookie_name(WC_PHONE_ADMIN_COOKIE);

	    do_action('wpo_before_switch_customer_apply_cart_data', $phone_order_cart);

		add_action( 'wp_loaded', function () {
			$referrer_data = self::get_data_from_cookie_name( WC_PHONE_ADMIN_REFERRER_COOKIE );
			if ( is_array( $referrer_data ) && isset( $referrer_data['is_frontend'] ) ) {
				$this->enable_payment_gateway( wc_string_to_bool( $referrer_data['is_frontend'] ) );
			}
		}, 1 );

	    if (!empty($phone_order_cart)) {

		$settings = $this->settings;

		$items			 = isset($phone_order_cart['items']) ? $phone_order_cart['items'] : array();
		$override_items_price	 = $settings->get_option('override_product_price_in_cart');

		if ($override_items_price && $items) {
		    add_filter('woocommerce_get_cart_item_from_session', function($session_data, $values, $key) use ($items) {

			foreach ($items as $item) {
			    if ( (isset($item['key']) && isset($values['wpo_key']) && $values['wpo_key'] == $item['key']) || (isset($item['wpo_cart_item_key']) && isset($values['key']) && $values['key'] == $item['wpo_cart_item_key']) ) {

				$session_data['data']->set_price($item['item_cost']);

				return $session_data;
			    }
			}

			return $session_data;
		    }, 10, 3);
		}

		if ($settings->get_option('allow_to_rename_cart_items') && $items) {
		    add_filter('woocommerce_get_cart_item_from_session', function($session_data, $values, $key) use ($items) {

			foreach ($items as $item) {
			    if ( ! empty($item['custom_name']) && (isset($item['key']) && isset($values['wpo_key']) && $values['wpo_key'] == $item['key']) || (isset($item['wpo_cart_item_key']) && isset($values['key']) && $values['key'] == $item['wpo_cart_item_key']) ) {

				$session_data['data']->set_name($item['custom_name']);

				return $session_data;
			    }
			}

			return $session_data;
		    }, 10, 3);
		}

	    // SET UP SHIPPING
	    // register 'custom price' shipping method only when it has been selected in admin side and shipping was not remove
	    if ( isset( $phone_order_cart['shipping']['packages'] ) ) {
		    foreach ( $phone_order_cart['shipping']['packages'] as $package ) {
			    if ( isset( $package['chosen_rate']['id'] ) && preg_match( '/^phone_orders_custom_price:\d+$/', $package['chosen_rate']['id'] ) == 1 ) {
				    //for shipping method
				    add_action( 'woocommerce_shipping_init', array(
					    'WC_Phone_Orders_Loader_Pro',
					    'woocommerce_shipping_init',
				    ) );
				    add_filter( 'woocommerce_shipping_methods', array(
					    'WC_Phone_Orders_Loader_Pro',
					    'woocommerce_shipping_methods',
				    ) );
			    }

			    if ( isset( $package['chosen_rate']['id'] ) && preg_match( '/^phone_orders:\d+$/', $package['chosen_rate']['id'] ) == 1 ) {
				    add_action( 'woocommerce_shipping_init', function ( $methods ) {
					    include_once WC_PHONE_ORDERS_PLUGIN_PATH . "/classes/class-wc-phone-shipping-method.php";
				    } );

				    add_filter( 'woocommerce_shipping_methods', function ( $methods ) {
					    $methods['phone_orders'] = 'WC_Phone_Shipping_Method';
					    return $methods;
				    } );
			    }
		    }
	    }

			// set selected shipping and custom meta data
		    add_action( 'woocommerce_cart_loaded_from_session', function () use ( $phone_order_cart ) {
			    $this->set_shipping_from_transient_cart( $phone_order_cart );

				add_action( 'woocommerce_checkout_create_order_line_item', function ( $item, $cart_item_key, $values, $order ) use ( $phone_order_cart ) {
					foreach( $phone_order_cart['items'] as $po_item ) {
						if( (isset($po_item['key']) && isset($values['wpo_key']) && $values['wpo_key'] == $po_item['key']) ||
						(isset($po_item['wpo_cart_item_key']) && isset($values['key']) && $values['key'] == $po_item['wpo_cart_item_key']) ) {
							foreach( $po_item['custom_meta_fields'] as $meta ) {
								if ( is_callable( array( $item, "set_" . $meta['meta_key'] ) ) ) {
									$item->{"set_" . $meta['meta_key']}( $meta['meta_value'] );
								} else {
									$item->update_meta_data( $meta['meta_key'], $meta['meta_value'] );
								}
							}
						}
					}
				}, 10, 4 );

				add_filter( 'woocommerce_get_item_data', function ( $item_data, $cart_item ) use ( $phone_order_cart ) {
					foreach( $phone_order_cart['items'] as $po_item ) {
						if( (isset($po_item['key']) && isset($cart_item['wpo_key']) && $cart_item['wpo_key'] == $po_item['key']) ||
						(isset($po_item['wpo_cart_item_key']) && isset($cart_item['key']) && $cart_item['key'] == $po_item['wpo_cart_item_key']) ) {
							foreach( $po_item['custom_meta_fields'] as $meta ) {
								$item_data[] = array( 'key' => $meta['meta_key'], 'value' => $meta['meta_value'] );
							}
						}
					}

					return $item_data;
				}, 10, 2);
		    }, 1000, 0 );
		    add_action( 'woocommerce_check_cart_items', function () use ( $phone_order_cart ) {
			    $this->set_shipping_from_transient_cart( $phone_order_cart );
		    }, 10, 0 );

		    add_action( 'woocommerce_checkout_order_review', function () use ( $phone_order_cart ) {
			    $this->set_payment_method_from_transient_cart( $phone_order_cart );
		    }, 5, 0 );

			// remove shipping from transient cart after changing in cart page
		    add_action( 'check_ajax_referer', function ( $action, $result ) use ( $cart_id ) {
			    if ( 'update-shipping-method' === $action ) {
				    $phone_order_cart = get_transient( $cart_id . '_temp_cart' );
				    unset( $phone_order_cart['shipping'] );
				    set_transient( $cart_id . '_temp_cart', $phone_order_cart );
			    }
		    }, 10, 2 );

		    // remove shipping from transient cart after changing on the checkout page
		    add_action( 'woocommerce_checkout_update_order_review', function () use ($cart_id) {
			    $chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods' );
			    $posted_shipping_methods = isset( $_POST['shipping_method'] ) ? wc_clean( wp_unslash( $_POST['shipping_method'] ) ) : array();
			    $drop = !is_array($chosen_shipping_methods) OR count( $chosen_shipping_methods ) !== count( $posted_shipping_methods );
			    if ( ! $drop ) {
				    for ( $i = 0; $i < count( $chosen_shipping_methods ); $i ++ ) {
					    $chosen_shipping_method = isset( $chosen_shipping_methods[ $i ] ) ? $chosen_shipping_methods[ $i ] : null;
					    $posted_shipping_method = isset( $posted_shipping_methods[ $i ] ) ? $posted_shipping_methods[ $i ] : null;

					    if ( $chosen_shipping_method !== $posted_shipping_method ) {
						    $drop = true;
						    break;
					    }
				    }
			    }

			    if ( $drop ) {
				    $phone_order_cart = get_transient( $cart_id . '_temp_cart' );
				    unset( $phone_order_cart['shipping'] );
				    set_transient( $cart_id . '_temp_cart', $phone_order_cart );
			    }
		    }, 10, 2 );

		    // set price for 'custom_price' shipping method if enabled
		   /* add_filter( 'woocommerce_package_rates', function ( $rates, $package ) use ( $phone_order_cart ) {
			    if ( isset( $phone_order_cart['shipping']['packages'] ) ) {
				    foreach ( $phone_order_cart['shipping']['packages'] as $po_package ) {
					    if ( $po_package['hash'] === WC_Phone_Orders_Cart_Shipping_Processor::calculate_package_hash( $package ) ) {
						    if ( isset( $po_package['chosen_rate']['cost'] ) ) {
							    $rates[ $po_package['chosen_rate']['id'] ]->cost = (float) $po_package['chosen_rate']['cost'];
						    }
					    }
				    }
			    }

			    return $rates;
		    }, 10, 2 );*/
			// FINISH SET UP SHIPPING


		$fee_tax_class = $settings->get_option('fee_tax_class');

		$fees_data = isset($phone_order_cart['fee']) ? $phone_order_cart['fee'] : false;

		if ($fees_data) {
		    add_action('woocommerce_cart_calculate_fees', function () use ( $fees_data, $fee_tax_class ) {

			foreach ($fees_data as $index => $fee_data) {
			    WC()->cart->add_fee($fee_data['name'], $fee_data['amount'], (boolean) $fee_tax_class, $fee_tax_class);
			}
		    });
		}

		$discount			 = isset($phone_order_cart['discount']) ? $phone_order_cart['discount'] : false;
		$manual_cart_discount_code	 = strtolower($settings->get_option('manual_coupon_title'));

		if ($discount && ( $manual_cart_discount_code || $discount['name'] ) ) {
		    if ( $discount['name'] ) {
			$manual_cart_discount_code = $discount['name'];
		    }
		    add_action('woocommerce_get_shop_coupon_data', function ( $manual, $coupon ) use ( $discount, $manual_cart_discount_code ) {

			if ($coupon != $manual_cart_discount_code) {
			    return $manual;
			}

			// fake coupon here
			return array('amount' => $discount['amount'], 'discount_type' => $discount['type'], 'id' => - 1, 'date_created' => current_time('timestamp'));
		    }, 10, 2);
		}

		$customer_note	 = isset($phone_order_cart['customer_note']) ? $phone_order_cart['customer_note'] : false;
		$private_note	 = isset($phone_order_cart['private_note']) ? $phone_order_cart['private_note'] : false;
		$custom_fields	 = isset($phone_order_cart['custom_fields']) ? $phone_order_cart['custom_fields'] : array();

		if ($customer_note || $custom_fields) {
		    add_filter('woocommerce_checkout_get_value', function ( $value, $input ) use ( $customer_note, $custom_fields ) {
			if ('order_comments' == $input && $customer_note) {
			    $value = $customer_note;
			}

		    if ( isset($custom_fields[$input]) ) {
				//$value = is_array($custom_fields[$input]) ? join(",", $custom_fields[$input]) : $custom_fields[$input];
				$value = $custom_fields[$input];//use as is
			    $value = apply_filters("wpo_checkout_get_value", $value, $input, $custom_fields[$input]);
		    }

			return $value;
		    }, 20, 2);
		}


			    $meta_key_private_note = WC_Phone_Orders_Loader::$meta_key_private_note;
			    $meta_key_order_creator = WC_Phone_Orders_Loader::$meta_key_order_creator;

			    add_action( 'woocommerce_checkout_order_processed', function ( $order_id, $posted_data, $order ) use ( $private_note, $meta_key_private_note, $admin_id, $meta_key_order_creator ) {
				    if ( $private_note ) {
					    $customer_id = get_current_user_id();
					    wp_set_current_user($admin_id);
					    $order->add_order_note( $private_note, false, true );
					    wp_set_current_user($customer_id);
					    update_post_meta( $order_id, $meta_key_private_note, $private_note );
				    }

				    update_post_meta( $order_id, $meta_key_order_creator, $admin_id );
			    }, 10, 3 );

		$phone_order_cart['applied_shipping']	     = 1;
		$phone_order_cart['applied_payment_method']  = 1;

		    if ( ! empty( $phone_order_cart['customer']['ship_different_address'] ) ) {
			    add_filter( "option_woocommerce_ship_to_destination", function ( $value, $option ) {
				    return "shipping";
			    }, 10, 2 );
		    }

		set_transient( $cart_id . '_temp_cart', $phone_order_cart );
	    }
	}

	public function set_shipping_from_transient_cart( $phone_order_cart ) {
		if ( /*! isset( $phone_order_cart['applied_shipping'] ) &&*/ isset( $phone_order_cart['shipping']['packages'] ) ) {
			WC()->shipping->reset_shipping();

			//fix subscription shipping recurring error
			WC()->cart->calculate_totals();

			$shipping_proc = new WC_Phone_Orders_Cart_Shipping_Processor( $this->settings );
			$shipping_proc::enable_preventing_to_select_method_for_certain_packages();

			$shipping_proc->prepare_shipping( WC()->session->get( 'chosen_shipping_methods' ),
				WC()->cart->get_shipping_packages(), $phone_order_cart, WC()->customer->is_vat_exempt() );

			$shipping_proc->process_custom_shipping( $phone_order_cart );

			$chosen_shipping_methods = $shipping_proc->get_chosen_methods();

			WC()->session->set( 'chosen_shipping_methods', $chosen_shipping_methods );
			$shipping_proc::purge_packages_from_session();

			WC()->cart->calculate_totals();
		}
	}

	public function set_payment_method_from_transient_cart($phone_order_cart) {
		$payment_method = ! isset( $phone_order_cart['applied_payment_method'] ) && ! empty( $phone_order_cart['payment_method'] ) ? $phone_order_cart['payment_method'] : '';
		WC()->session->set( 'chosen_payment_method', $payment_method );
	}

	public function apply_checkout_link() {

            WC_Phone_Orders_Loader_Pro::disable_object_cache();

	    if (!isset($_GET['wpo_checkout_link'])) {
		return;
	    }

	    $cart_id = $_GET['wpo_checkout_link'];

	    $cart_data = get_transient($cart_id . '_temp_cart');

	    if (!$cart_data) {
		return;
	    }

	    $customer_id = $cart_data['customer']['id'];

	    WC_Phone_Orders_Cookie_Helper::set_payment_cookie( $customer_id, $cart_id );

		$updater = new WC_Phone_Orders_Cart_Updater( WC_Phone_Orders_Settings::getInstance() );
		$updater->process( $cart_data );

	    wp_redirect( wc_get_checkout_url() );

	    exit();
	}


}

new WC_Phone_Orders_Switch_User();
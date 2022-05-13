<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_Phone_Orders_Loader_Pro {
    protected $tabs;

    public function __construct() {
		include_once 'classes/class-wc-phone-orders-switch-user.php';
		include_once 'classes/class-wc-phone-orders-customer-multi-address.php';
		include_once 'classes/class-wc-phone-orders-cookie-helper.php';
		add_action('woocommerce_loaded', function() {
		    include_once 'classes/session-handler/class-wc-phone-orders-session-handler-base.php';
		    if ( version_compare( WC_VERSION, '3.7', '<' ) ) {
			include_once 'classes/session-handler/class-wc-phone-orders-session-handler-legacy.php';
		    } else {
			include_once 'classes/session-handler/class-wc-phone-orders-session-handler.php';
		    }
		});

		// EDD
		include 'classes/updater/class-wc-phone-orders-updater.php';
		include 'classes/updater/class-wc-phone-orders-edd.php';

	    include_once 'classes/class-wc-phone-orders-report-register.php';
	    WC_Report_Phone_Order_Report_Register::init();

		// Settings
		add_action( 'wpo_include_core_classes', array( $this, 'wpo_include_core_classes' ) );

		// load Pro tabs
		add_filter( 'wpo_admin_tabs', array( $this, 'wpo_admin_tabs' ) );

		if ( is_admin() ) {

			//for shipping method
			add_action( 'woocommerce_shipping_init', array( __CLASS__, 'woocommerce_shipping_init' ) );
			add_filter( 'woocommerce_shipping_methods', array( __CLASS__, 'woocommerce_shipping_methods' ) );

			// pro actions on admin_init
			add_action( "wc_phone_orders_construct_end", array( $this, 'wc_phone_orders_construct_end' ) );
		}

		$this->define_constants();

		add_action( 'wpo_cart_updated', array( $this, 'store_cart_in_session' ) );
		add_action( 'wpo_cart_updated', array( $this, 'store_customer_in_session' ) );

		add_filter( 'wpo_get_shipping_methods', array( $this, 'get_shipping_methods' ) );

		add_action( 'wp_loaded', function () {

			$settings = WC_Phone_Orders_Settings::getInstance();

			if ( $settings->get_option( 'support_field_vat' ) ) {
			    $this->add_billing_vat_number_to_wc_my_account();
			    $this->add_billing_vat_number_to_wc_customer_formatted_address();
			    $this->add_billing_vat_number_to_wpo_customer_formatted_address();
			}

			if ( $settings->get_option( 'use_shipping_phone' ) ) {
				$this->add_shipping_phone_to_wc_my_account();
				$this->add_shipping_phone_to_wc_customer_formatted_address();
				$this->add_shipping_phone_to_wpo_customer_formatted_address();
			}

			if ( $settings->get_option( 'customer_show_role_field' ) ) {
			    $this->add_role_to_billing_wc_customer_formatted_address();
			    $this->add_role_to_billing_wpo_customer_formatted_address();
			}


			// these hooks for admin pages only !
			if ( !is_admin() )
				return ;
			if ( $settings->get_option( 'override_customer_payment_link_in_order_page' ) ) {
				add_filter( 'woocommerce_get_checkout_payment_url', array( $this, 'update_checkout_function' ), 10, 2 );

				if ( ! empty( $_REQUEST['pay_as_customer'] ) && "true" === $_REQUEST['pay_as_customer'] && ! empty( $_REQUEST['order_id'] ) && ! empty( $_REQUEST['key'] ) ) {
					$order	    = wc_get_order( $_REQUEST['order_id'] );
					$order_key  = wc_clean( wp_unslash( $_REQUEST['key'] ) );

					if ( ! $order || ! hash_equals( $order->get_order_key(), $order_key ) ) {
						return;
					}

					if ( current_user_can( 'manage_woocommerce' ) ) {
						$current_user_id = get_current_user_id();
						WC_Phone_Orders_Cookie_Helper::set_payment_cookie( $order->get_customer_id(), null, $current_user_id );
					}
					remove_filter( 'woocommerce_get_checkout_payment_url', array( $this, 'update_checkout_function' ), 10 );
					wp_redirect( $order->get_checkout_payment_url() );
					exit();
				}
			}

			if ( $settings->get_option( 'show_edit_order_in_wc' ) ) {
				// add icons only in orders list
				add_action('current_screen', function(){
					$screen_id = false;
					if ( function_exists( 'get_current_screen' ) ) {
						$screen    = get_current_screen();
						$screen_id = isset( $screen, $screen->id ) ? $screen->id : '';
					}
					if ( ! empty( $_REQUEST['screen'] ) ) { // WPCS: input var ok.
						$screen_id = wc_clean( wp_unslash( $_REQUEST['screen'] ) ); // WPCS: input var ok, sanitization ok.
					}
					if ( 'edit-shop_order' == $screen_id ) {
						add_action( 'wp_print_scripts', array( $this, 'add_icons_for_order_action' ) );
					}
				});

				// print button with edit order url to plugin page
				add_filter( 'woocommerce_admin_order_actions_end', function ( $object ) {
					if ( $object->is_editable() || $object->get_status() == 'draft' ) {
						$action = array(
							'action' => 'edit_in_wpo',
							'url'    => add_query_arg( array(
								'page'          => WC_Phone_Orders_Main::$slug,
								'edit_order_id' => $object->get_id(),
							), admin_url( 'admin.php' ) ),
							'name'   => __( 'Edit in Phone Orders', 'phone-orders-for-woocommerce' ),
						);

						$open_link_in_same_tab = apply_filters( 'wpo_wc_order_list_edit_order_open_in_same_tab', false);

						echo sprintf( '<a class="button wc-action-button wc-action-button-%1$s %1$s" href="%2$s" aria-label="%3$s" title="%3$s" target="%4$s">%5$s</a>',
							esc_attr( $action['action'] ),
							esc_url( $action['url'] ),
							esc_attr( isset( $action['title'] ) ? $action['title'] : $action['name'] ),
							$open_link_in_same_tab ? '_self' : '_blank',
							esc_html( $action['name'] )
						);
					}
				}, 10, 2 );
			}

			if ( $settings->get_option( 'show_creator_in_orders_list' ) ) {
				// Add "Order creator" column in orders list
				add_filter( 'manage_edit-shop_order_columns', array( $this, 'add_order_creator_column_header' ), 20 );
				add_action( 'manage_shop_order_posts_custom_column', array( $this, 'add_order_creator_column_content' ), 10, 2 );
				add_action( 'restrict_manage_posts', array( $this, 'restrict_manage_posts' ) );
				add_filter( 'request', array( $this, 'request_query' ) );
			}

			if ( $settings->get_option( 'show_phone_orders_in_order_page' ) && isset($_GET['post']) ) {
				add_action( 'add_meta_boxes', array( $this, 'add_phone_orders_section_to_order_page' ) );
			}

			if ( $settings->get_option( 'hide_add_order_in_orders_list' ) ) {
			    $this->hide_woocommerce_create_order_button();
			}

			$this->check_save_allowed_roles_new_customer_settings($settings);

			if ( $settings->get_option( 'use_shipping_phone' ) ) {
				$this->add_shipping_phone_to_admin_profile();
				$this->add_shipping_phone_to_wc_order_details();
			}

			if ( $settings->get_option( 'support_field_vat' ) ) {
			    $this->add_billing_vat_number_to_admin_profile();
			    $this->add_billing_vat_number_to_wc_order_details();
			}

			$this->add_custom_fields_to_admin_profile();

			if ($settings->get_option( 'use_payment_delivery_last_order' )) {
			    add_filter('wpo_init_order_get_data', array($this, 'add_customer_last_order_payment'), 10, 2);
			    add_filter('wpo_update_customer_get_data', array($this, 'add_customer_last_order_payment'), 10, 2);
			    add_filter('wpo_get_default_shipping_method_id', array($this, 'add_customer_last_order_shipping'), 10, 2);
			}

			if ( $settings->get_option( 'show_custom_fields_in_order_email' ) ) {
			    $this->add_custom_fields_in_order_email();
			}

			if ( $settings->get_option( 'barcode_mode' ) ) {
			    add_filter( 'wpo_update_cart_cart_item_meta', function ($cart_item_meta, $item, $items) {
				if (isset($item['barcode'])) {
				    $cart_item_meta['barcode'] = $item['barcode'];
				}
				return $cart_item_meta;
			    }, 10, 3);
			    add_filter('wpo_update_cart_loaded_product', function ($loaded_product, $item) {
				if (isset($item['barcode'])) {
				    $loaded_product['barcode'] = $item['barcode'];
				}
				return $loaded_product;
			    }, 10, 2);
			}

			if ( $settings->get_option( 'show_order_type_filter_in_order_page' ) ) {
				add_action( 'restrict_manage_posts', array( $this, 'render_order_type_filter_wc_order_list' ), 50, 2 );
				add_filter( 'request', array( $this, 'filter_order_type_wc_order_list' ) );
			}
		} );

		add_filter('wpo_customer_address_additional_keys', function ($fields) {

		    $fields['vat_number'] = array(
			'label'	     => __( 'VAT Number', 'phone-orders-for-woocommerce' ),
			'value'	     => '',
			'visibility' => true,
		    );

		    return $fields;
		});

		add_action('parse_request', array($this, 'init_frontend_page'));
		add_action('parse_request', array($this, 'init_view_invoice_page'));
		add_action('parse_request', array($this, 'init_product_page'));

		if (is_admin()) {
		    remove_filter( 'admin_footer_text', array( 'WC_Phone_Orders_Loader', 'admin_footer_text' ));
		}

	    include_once 'classes/tabs/helpers/class-wc-phone-orders-shipping-package-mod-strategy-pro.php';
	    add_action( "wpo_shipping_package_mod_strategy", function () {
		    return new WC_Phone_Order_Shipping_Package_Mod_Strategy_Pro();
	    } );

	    add_filter( 'woocommerce_payment_gateways', function ( $methods ) {
		    include_once "classes/class-wc-phone-orders-gateway.php";
		    $methods[] = "WC_Phone_Orders_Gateway";

		    return $methods;
	    } );

	    add_filter('wpo_customer_edit_address_fields_to_show', function ($fields) {
		$_fields = array(
		    'role' => array(
			'label' => __( 'Role', 'phone-orders-for-woocommerce' ),
			'value' => '',
		    ),
		    'locale' => array(
			'label' => __( 'Language', 'phone-orders-for-woocommerce' ),
			'value' => '',
		    ),
		);
		return array_merge(array_slice($fields, 0, 1), array_slice($_fields, 0, 1), array_slice($fields, 1, 4), array_slice($_fields, 1, 1), array_slice($fields, 4));
	    });

	    include_once 'classes/tabs/helpers/class-wc-phone-orders-custom-products-controller-pro.php';
	    include_once 'classes/tabs/helpers/class-wc-phone-orders-custom-products-hooks-pro.php';

	    $custom_product_hooks = new WC_Phone_Orders_Custom_Products_Hooks_Pro();
	    $custom_product_hooks->make_custom_product_always_purchasable();
	    $custom_product_hooks->allow_to_store_custom_product_in_order_item();
	    $custom_product_hooks->install_action_to_load_from_session();
	    $custom_product_hooks->remove_product_edit_link_in_order_edit_page();

	    add_action('wp_loaded', function () {
		$this->auto_login_email_pay_order();
		$this->handle_payment_link_order();
	    }, 9);

	    include_once 'classes/compatibility/class-wc-phone-subscription-support.php';

	    new WC_Phone_Subscription_Support();

	    include_once 'classes/compatibility/class-wc-phone-pimwick-gift-card-compatibility.php';

	    new WC_Phone_Pimwick_Gift_Card_Compatibility();
	}

	public function init_frontend_page() {

	    global $wp;

	    $current_url = home_url(add_query_arg(array(), $wp->request));

	    $this->wpo_include_core_classes();

	    $settings_option_handler = WC_Phone_Orders_Settings::getInstance();

	    $clean_current_url = remove_query_arg(array('edit_order_id'), $current_url);

	    if ( ! $settings_option_handler->get_option('frontend_page')
		|| trim(trim($settings_option_handler->get_option('frontend_page_url')), '/') !== trim($clean_current_url, '/')
	    ) {
		return;
	    }

	    if ( ! is_user_logged_in() ) {
		wp_safe_redirect( wp_login_url( $current_url ) );
		exit();
	    }

	    if ( ! WC_Phone_Orders_Loader::check_user_capability() ) {
		return;
	    }

	    //suppress 404 redirects!
		add_action( 'wp', function(){
			global $wp_query;
			$wp_query->is_404 = false;
		},0);

	    add_action( 'template_redirect', array( 'WC_Phone_Orders_Loader', 'load_main' ) );

	    // Admin Color Schemes
	    add_action( 'template_redirect', 'register_admin_color_schemes', 1);

	    add_action('wc_phone_orders_construct_end', array($this, 'render_frontend_page'));

	    add_action( 'wp_enqueue_scripts', function () {

		//admin styles
		wp_enqueue_style( 'colors' );
		wp_enqueue_style( 'ie' );

		WC_Phone_Orders_Main::load_scripts();
	    });

	    add_filter( 'script_loader_src', array( $this, 'script_loader_src' ), 999, 2 );
	    add_filter( 'style_loader_src', array( $this, 'script_loader_src' ), 999, 2 );

		do_action( "wpo_init_frontend_page" );
	}

	public function init_view_invoice_page() {
		global $wp;
		$current_url = home_url( add_query_arg( array(), $wp->request ) );

		if ( get_home_url( null, 'wpo-view-invoice' ) !== trim( $current_url, '/' ) ) {
			return;
		}

		$nonce = isset( $_GET['nonce'] ) ? $_GET['nonce'] : "";
		if ( ! wp_verify_nonce( $nonce, 'phone-orders-for-woocommerce' ) ) {
			wp_die( 0 );
		}

		$order_id = isset( $_GET['order_id'] ) ? $_GET['order_id'] : false;
		if ( ! $order_id ) {
			wp_die( 0 );
		}

		$order = wc_get_order( $order_id );

		$wc_emails        = WC_Emails::instance();
		$emails           = $wc_emails->get_emails();
		$email_class_name = apply_filters( 'wpo_view_invoice_email_class_name', 'WC_Email_Customer_Invoice' );

		$email = isset( $emails[ $email_class_name ] ) ? $emails[ $email_class_name ] : false;
		if ( ! $email ) {
			wp_die( __( 'Email class name is incorrect', 'phone-orders-for-woocommerce' ) );
		}
		$email->object = $order;

		echo wc_get_template_html( $email->template_html, array(
			'order'         => $order,
			'email_heading' => $email->get_heading(),
			'sent_to_admin' => true,
			'plain_text'    => false,
			'email'         => $email,
		) );
		exit();
	}

	public function init_product_page() {

		if ( !isset($_GET['action']) || $_GET['action'] !== 'wpo-product-page' ) {
			return;
		}

		$product_id = isset( $_GET['product_id'] ) ? $_GET['product_id'] : false;

		if ( ! $product_id ) {
			wp_die( 0 );
		}

	    ?>

		<!DOCTYPE html>
		<html>
		    <head>
			<?php wp_head(); ?>
		    </head>
		    <body>
			<?php
			    echo WC_Shortcodes::product_page(array(
				'id' => $product_id,
			    ));
			?>
			<?php wp_footer(); ?>
		    </body>
		</html>

            <?php

	    exit();
	}

	public function script_loader_src( $src, $handle ) {
	    if (apply_filters('wpo_frontend_disable_load_src', strpos( $src, WC_PHONE_ORDERS_PLUGIN_URL ) === false
		&& !preg_match( '/\/wp-includes\//', $src )
		&& !preg_match( '/\/wp-admin\//', $src ),
		$src
	    )) {
		return "";
	    }

	    return $src;
	}

	public function render_frontend_page() {

		status_header(200);//suppress 404

		add_filter("document_title_parts", function($title){ // replace "Page Not Found"
			    $title['title'] = __( 'Add order', 'phone-orders-for-woocommerce' );
			    return $title;
		});

		$settings_option_handler = WC_Phone_Orders_Settings::getInstance();

		$hide_header = $settings_option_handler->get_option('frontend_hide_theme_header');
		$hide_footer = $settings_option_handler->get_option('frontend_hide_theme_footer');
	    ?>

	    <!DOCTYPE html>
	    <html>
		<head>
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <div style="<?php echo $hide_header ? 'display:none;' : ''; ?>">
			<?php
			    do_action( 'wp_head' );
			    do_action( 'admin_print_styles' );
			?>
		    </div>
		</head>
		<body>
		    <div style="<?php echo $hide_header ? 'display:none;' : ''; ?>">
			<?php do_action('wpo_frontend_header') ?>
		    </div>
		    <?php
			// include get_editable_roles()
			require_once(ABSPATH . 'wp-admin/includes/admin.php');
			$settings = $settings_option_handler->get_all_options();
		    ?>
		    <script>
			window.wpo_frontend = 1;
		    </script>
            <div id="wpbody-content">
                <div class="wrap woocommerce">
                <?php do_action('wpo_before_render_html_app'); ?>
                <div class="wpo_settings ui-page-theme-a">
                    <div class="wpo_settings_container">
                    <br/>
                    <div id="phone-orders-app" data-all-settings="<?php echo esc_attr( json_encode($settings) ) ?>">
                        <?php $this->tabs['add-order']->render(); ?>
                    </div>
                    </div>
                </div>
                </div>
            </div>
		    <div style="<?php echo $hide_footer ? 'display:none;' : ''; ?>">
			<?php do_action('wpo_frontend_footer') ?>
			<?php do_action( 'wp_footer' ); ?>
		    </div>
		</body>
	    </html>

            <?php
	    exit;
	}

	/**
	 * @return WC_Customer
	 */
	public function store_customer_in_session(  ) {
		WC_Phone_Orders_Loader_Pro::disable_object_cache();
		$customer = WC()->customer;

		$current_user = get_current_user_id();
		$trans_name       = $current_user . '_temp_customer_id';
		$temp_customer_id = get_transient( $trans_name );

		$temp_session = WC_Phone_Orders_Loader_Pro::get_session_handler();
		$temp_session->set_customer_id( $temp_customer_id );
		$temp_session->init();
		if ( ! $temp_customer_id ) {
			set_transient( $trans_name, $temp_session->get_customer_id() );
		}
		$temp_session->set_original_customer( $customer );
		$temp_session->save_data();

		return $customer;
	}

	public function store_cart_in_session() {
		WC_Phone_Orders_Loader_Pro::disable_object_cache();
		$current_user = get_current_user_id();
		$trans_name       = $current_user . '_temp_customer_id';
		$temp_customer_id = get_transient( $trans_name );

		$temp_session = WC_Phone_Orders_Loader_Pro::get_session_handler();
		$temp_session->set_customer_id( $temp_customer_id );
		$temp_session->init();

		if ( ! $temp_customer_id ) {
			set_transient( $trans_name, $temp_session->get_customer_id() );
		}

		$cart_keys = array(
			'cart',
			'cart_totals',
			'applied_coupons',
			'coupon_discount_totals',
			'coupon_discount_tax_totals',
			'removed_cart_contents',
			'order_awaiting_payment'
		);

		foreach ( $cart_keys as $key ) {
			$temp_session->set( $key, maybe_unserialize( WC()->session->get( $key ) ) );
		}
		$temp_session->save_data();
	}

	public function wpo_include_core_classes() {
		include_once WC_PHONE_ORDERS_PRO_VERSION_PATH . 'classes/class-wc-phone-orders-settings-pro.php';
	}

	public static function woocommerce_shipping_init() {
		include_once 'classes/class-wc-phone-shipping-method-custom-price.php';
	}

	public static function woocommerce_shipping_methods( $methods ) {
		$methods['phone_orders_custom_price'] = 'WC_Phone_Shipping_Method_Custom_Price';

		return $methods;
	}

	public function wpo_admin_tabs( $tabs ) {
		include_once WC_PHONE_ORDERS_PRO_VERSION_PATH . 'classes/tabs/class-wc-phone-orders-tab-helper-pro.php';

		$this->tabs = WC_Phone_Orders_Tabs_Helper_Pro::init_tabs( $tabs );

		return $this->tabs;
	}

	/**
	 * @param $settings WC_Phone_Orders_Settings
	 */
	public function wc_phone_orders_construct_end( $settings ) {
		// allow search by order fields?
		if ( $settings->get_option( 'search_all_customer_fields' ) ) {
			add_filter( "woocommerce_customer_search_customers", function ( $filter, $term, $limit, $type ) {
				if ( $type != 'meta_query' ) {
					return $filter;
				}

				$fields = array(
					"address_1",
					"address_2",
					"city",
					"company",
					"email",
					"first_name",
					"last_name",
					"phone",
					"postcode",
				);

				$fields = apply_filters('wpo_search_customers_meta_fields', $fields);

				foreach ( $fields as $f ) {
					$filter['meta_query'][] = array( 'key' => 'billing_' . $f, 'value' => $term, 'compare' => 'LIKE' );
				}

				foreach ( $fields as $f ) {
					$filter['meta_query'][] = array( 'key' => 'shipping_' . $f, 'value' => $term, 'compare' => 'LIKE' );
				}

				return $filter;
			}, 10, 4 );
		}

		// limit search ?
		if ( $settings->get_option( 'number_of_customers_to_show' ) ) {
			add_filter( "woocommerce_customer_search_customers", function ( $filter, $term, $limit, $type ) {
				$filter['number'] = WC_Phone_Orders_Settings::getInstance()->get_option( 'number_of_customers_to_show' );

				return $filter;
			}, 10, 4 );
		}

		// tweak customer search for our tab only
		if ( isset( $_GET['wpo_find_customer'] ) ) {
			add_filter( "woocommerce_json_search_found_customers", array( $this, 'do_customers_search_by_orders' ) );
		}
	}

	private function define_constants() {
		define( 'WC_PHONE_ORDERS_PRO_VERSION_PATH', WC_PHONE_ORDERS_PLUGIN_PATH . 'pro_version/' );
		define( 'WC_PHONE_ORDERS_PRO_VERSION_URL', WC_PHONE_ORDERS_PLUGIN_URL . 'pro_version/' );

		// after wp_cookie_constants() in the network installation
		add_action( "plugins_loaded", function () {
			// User switcher
			define( 'WC_PHONE_ADMIN_COOKIE', 'wordpress_woocommerce_po_admin_' . COOKIEHASH );
			define( 'WC_PHONE_ADMIN_REFERRER_COOKIE', 'wordpress_woocommerce_po_admin_referrer_' . COOKIEHASH );
			define( 'WC_PHONE_CUSTOMER_COOKIE', 'wordpress_woocommerce_po_customer_' . COOKIEHASH );
			define( 'WC_PHONE_CART_COOKIE', 'wordpress_woocommerce_po_cart_' . COOKIEHASH );
		} );

		define( 'WC_PHONE_ORDERS_MAIN_URL', WC_Phone_Orders_EDD::wpo_get_main_url() );
		define( 'WC_PHONE_ORDERS_STORE_URL', 'https://algolplus.com/plugins/' );
		define( 'WC_PHONE_ORDERS_ITEM_NAME', 'Phone Orders for WooCommerce (Pro)' );
		define( 'WC_PHONE_ORDERS_AUTHOR', 'AlgolPlus' );
	}

	public function do_customers_search_by_orders( $found_customers ) {

		$result = array();

		//convert
		foreach ( $found_customers as $id => $title ) {
			$result[ $title ] = array(
				'id'    => $id,
				'type'  => 'customer',
				'title' => $title,
			);
		}

                if ( ! WC_Phone_Orders_Settings::getInstance()->get_option( 'search_customer_in_orders' ) OR
					apply_filters( "wpo_skip_search_customer_in_orders", false,$_GET['term'],$result) ) {
                    return array_values($result);
                }

		$output_limit   = 0;
		$founded	= count( $result );

		$number_of_customers_to_show = WC_Phone_Orders_Settings::getInstance()->get_option( 'number_of_customers_to_show' );

		if ( $number_of_customers_to_show ) {
			$output_limit = (int) $number_of_customers_to_show - $founded > 0 ? $number_of_customers_to_show - $founded : 0;
		}

		if ( ! $output_limit ) {
		    return array_values( $result );
		}

		$search_limit = WC_Phone_Orders_Settings::getInstance()->get_option( 'limit_orders_of_search_customer' );

		//find ids
		$order_ids = $this->get_woocommerce_json_search_customers_search_by_orders( $_GET['term'], $output_limit, $search_limit);

		foreach ( $order_ids as $order_id ) {

			$order = wc_get_order( $order_id );

			if ( ! $order ) {
				continue;
			}

			$customer = $order->get_customer_id() ? "#" . $order->get_customer_id() : "[" . __( 'guest', 'phone-orders-for-woocommerce' ) . "]";

			$title = sprintf(
				esc_html__( '%1$s (%2$s &ndash; %3$s)', 'phone-orders-for-woocommerce' ),
				implode(
					' ',
					array(
						current( array_filter( array(
							$order->get_billing_first_name(),
							$order->get_shipping_first_name(),
						) ) ),
						current( array_filter( array(
							$order->get_billing_last_name(),
							$order->get_shipping_last_name(),
						) ) ),
					)
				),
				$customer,
				$order->get_billing_email()
			);
			if ( isset( $result[ $title ] ) ) {
				continue;
			}

			$result[ $title ] = array(
				'id'    => $order->get_id(),
				'type'  => 'order',
				'title' => $title,
			);
		}

		//done
		return array_values( $result );
	}


	protected function get_woocommerce_json_search_customers_search_by_orders( $term, $output_limit, $search_limit ) {
		global $wpdb;

		$search_fields = array(
		    '_billing_address_index',
		    '_shipping_address_index',
		    '_billing_last_name',
		    '_billing_email',
		);

		if ( WC_Phone_Orders_Settings::getInstance()->get_option( 'search_all_customer_fields' ) ) {

		    $fields = array(
			"address_1",
			"address_2",
			"city",
			"company",
			"email",
			"first_name",
			"last_name",
			"phone",
			"postcode",
		    );

		    foreach ( $fields as $f ) {
			$search_fields[] = '_billing_' . $f;
			$search_fields[] = '_shipping_' . $f;
		    }

		    $search_fields = array_unique($search_fields);
		}

		$search_fields = array_map('wc_clean', apply_filters('woocommerce_shop_order_search_fields', $search_fields));

		$order_ids = array();

		if ( ! empty( $search_fields ) ) {

			$order_post_type = 'shop_order';

			$search_limit = (int) $search_limit;

			if ( $search_limit > 0 ) {
			    $order_ids   = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_type = '" . $order_post_type. "' ORDER BY post_date DESC LIMIT ". $search_limit );
			    $order_ids[] = 0; // add fake zero
			}

			$order_ids = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT DISTINCT p1.post_id FROM {$wpdb->postmeta} p1 WHERE ". ($order_ids ? "p1.post_id IN ('" . join( "','", $order_ids ) ."') AND " : "" )." p1.meta_value LIKE %s AND p1.meta_key IN ('" . implode( "','",
						array_map( 'esc_sql', $search_fields ) ) . "') ORDER BY p1.post_id DESC LIMIT " . (int)$output_limit,
					// @codingStandardsIgnoreLine
					'%' . $wpdb->esc_like( wc_clean( $term ) ) . '%'
				)
			);
		}

		return $order_ids;
	}

        public function get_shipping_methods(array $shipping_methods) {

            $shipping_methods[] = 'phone_orders_custom_price';

            return $shipping_methods;
        }

	public function add_icons_for_order_action() {
		?>
        <style>

            .widefat .wc_actions a.wc-action-button-edit_in_wpo::after {
		font-family: Dashicons;
		content: "\f464";
            }

        </style>
		<?php
	}

	public static function disable_object_cache() {
	    global $_wp_using_ext_object_cache;
	    if( apply_filters("wpo_disable_object_cache", false) )
			$_wp_using_ext_object_cache = false;
    }

	public function update_checkout_function( $pay_url, $order ) {
		/** @var WC_Order $order */ global $pagenow, $post_type;
		if ( $pagenow == 'post.php' AND $post_type == "shop_order" ) {

			parse_str(parse_url($pay_url, PHP_URL_QUERY), $params);

			$pay_url = add_query_arg( array(
				"pay_as_customer" => "true",
				'order_id'        => $order->get_id(),
				'key'		  => isset($params['key']) ? $params['key'] : '',
			), admin_url() );
		}

		return $pay_url;
	}


	public static function get_session_handler_class() {
	    return version_compare( WC_VERSION, '3.7', '<' ) ? 'WC_Phone_Orders_Session_Handler_Legacy' : 'WC_Phone_Orders_Session_Handler';
	}

	public static function get_session_handler() {
	    $class = self::get_session_handler_class();
	    return new $class();
	}

	public function add_order_creator_column_header( $columns ) {
		//check permissions
		if( !current_user_can( 'list_users' ) )
			return $columns ;

		$new_columns = array();
		foreach ( $columns as $column_name => $column_info ) {
			if ( 'order_actions' === $column_name OR 'wc_actions' === $column_name ) { // Woocommerce uses wc_actions since 3.3.0
				$new_columns['wpo_order_creator'] = __( 'Order creator', 'phone-orders-for-woocommerce' );
			}
			$new_columns[ $column_name ] = $column_info;
		}

		return $new_columns;
	}

	public function add_order_creator_column_content( $column, $post_id ) {
		if ( 'wpo_order_creator' === $column ) {
			$creator_id          = get_post_meta( $post_id, WC_Phone_Orders_Loader::$meta_key_order_creator, true );
			if ( $creator_id && current_user_can( 'list_users' ) ) {
				$user_data = get_userdata( $creator_id );
				if ( current_user_can( 'edit_user', $creator_id ) AND $user_data) {
					$customer_url        = add_query_arg( array( 'user_id' => $creator_id ), admin_url( 'user-edit.php' ) );
					echo '<a href="' . $customer_url . '" target="_blank">' . $user_data->display_name . '</a>';
				} else { // just show username
					echo $user_data ? $user_data->display_name : __('Deleted user #', 'phone-orders-for-woocommerce') . $creator_id;
				}
			};
		}
	}

	public function add_phone_orders_section_to_order_page( $order ) {
		add_meta_box( 'phone-orders-section', __('Phone Orders','phone-orders-for-woocommerce'), array($this, 'add_btns_for_phone_orders_section'), 'shop_order', 'side', 'core' );
	}

	public function add_btns_for_phone_orders_section() {
		global $post;

		$order = wc_get_order($post);
		$edit_order_url = add_query_arg( array(
			'edit_order_id' => $order->get_id(),
			'page'          => WC_Phone_Orders_Main::$slug,
		), admin_url( 'admin.php' ) );
		$copy_order_url = add_query_arg( array(
			'copy_order_id' => $order->get_id(),
			'page'          => WC_Phone_Orders_Main::$slug,
		), admin_url( 'admin.php' ) );

		if( $order->is_editable() || $order->get_status() == 'draft' ) {
			?>
			<button type="button" class="add_note button" onclick="window.open('<?php echo $edit_order_url?>')"><?php _e('Edit in backend', 'phone-orders-for-woocommerce')?></button>
			<?php
		} ?>
		<button type="button" class="add_note button" onclick="window.open('<?php echo $copy_order_url?>')"><?php _e('Duplicate order', 'phone-orders-for-woocommerce')?></button>
		<?php
	}

	public function hide_woocommerce_create_order_button() {

	    add_action('in_admin_header', function() {

		global $post_type, $post_type_object;

		if (!$post_type_object || $post_type !== 'shop_order') {
		    return;
		}

		$post_type_object->cap->create_posts = false;
	    });
	}

	public function check_save_allowed_roles_new_customer_settings($settings) {
	    add_filter('wpo_ajax_save_settings', function ($options) use ($settings) {
		if (!is_super_admin()) {
		    $options['allowed_roles_new_customer'] = $settings->get_option( 'allowed_roles_new_customer' );
		}
		return $options;
	    });
	}

	public function add_shipping_phone_to_wc_order_details() {
	    add_filter('woocommerce_admin_shipping_fields', function ($shipping_fields) {
		$shipping_fields['phone'] = array(
		    'label' => __( 'Phone', 'woocommerce' ),
		);
		return $shipping_fields;
	    });
	}

	public function add_shipping_phone_to_wc_my_account() {

	    add_filter('woocommerce_shipping_fields', function ($fields) {
		if (!isset($fields['shipping_phone'])) {
		    $fields['shipping_phone'] = array(
                        'label'    => __( 'Shipping phone', 'phone-orders-for-woocommerce' ),
			'required' => false,
			'class'    => array( 'form-row-wide' ),
			'clear'    => 1,
		    );
		}
		return $fields;
	    }, 50);

	    add_filter('woocommerce_my_account_my_address_formatted_address', function ($shipping_fields, $customer_id, $type) {
		if( $type == 'shipping' ){
		    $shipping_fields['wc_shipping_phone'] = get_user_meta($customer_id, 'shipping_phone', true);
		}
		return $shipping_fields;
	    }, 50, 3);
	}

	public function add_shipping_phone_to_admin_profile() {
	    add_filter('woocommerce_customer_meta_fields', function ($fields) {
		if (!isset($fields['shipping']['fields']['shipping_phone'])) {
		    $fields['shipping']['fields']['shipping_phone'] = array(
			'label'       => __( 'Shipping phone', 'phone-orders-for-woocommerce' ),
			'description' => '',
		    );
		}
		return $fields;
	    }, 50);
	}

	public function add_shipping_phone_to_wc_customer_formatted_address() {

	    add_filter('woocommerce_formatted_address_replacements', function ($replacements, $args) {

		if ( ! empty( $args['wc_shipping_phone'] ) ) {
		    $replacements['{shipping_phone}'] = __( 'Shipping phone', 'phone-orders-for-woocommerce' ) . ': ' . $args['wc_shipping_phone'];
		}

		if ( ! empty( $args['wpo_shipping_phone'] ) ) {
		    $replacements['{shipping_phone}'] = $args['wpo_shipping_phone'];
		}

		if (!isset($replacements['{shipping_phone}'])) {
		    $replacements['{shipping_phone}'] = '';
		}

		return $replacements;
	    }, 10, 2);

	    add_filter( 'woocommerce_localisation_address_formats', function ($address_formats) {
		$field_name = 'shipping_phone';
		$modified_address_formats = array();
		foreach ( $address_formats as $country => $address_format ) {
		    if ( ! preg_match( "/\{" . $field_name . "\}/im", $address_format ) ) {
			$address_format = $address_format . "\n{" . $field_name . '}';
		    }
		    $modified_address_formats[ $country ] = $address_format;
		}
		return $modified_address_formats;
	    });
	}

	public function add_shipping_phone_to_wpo_customer_formatted_address() {
		add_filter( 'wpo_customer_formatted_address', function ( $fields, $customer_data, $type ) {
			if ( ( $type == 'shipping' ) && isset( $customer_data['shipping_phone'] ) ) {
				$fields['wpo_shipping_phone'] = $customer_data['shipping_phone'];
			}

			return $fields;
		}, 50, 3 );
	}

	public function add_billing_vat_number_to_wc_order_details() {
		add_filter( 'woocommerce_admin_billing_fields', function ( $billing_fields ) {
			$billing_fields['vat_number'] = array(
				'label' => __( 'VAT Number', 'phone-orders-for-woocommerce' ),
			);

			return $billing_fields;
		} );
	}

	public function add_billing_vat_number_to_admin_profile() {
	    add_filter('woocommerce_customer_meta_fields', function ($fields) {
		if (!isset($fields['billing']['fields']['billing_vat_number'])) {
		    $fields['billing']['fields']['billing_vat_number'] = array(
			'label'       => __( 'VAT Number', 'phone-orders-for-woocommerce' ),
			'description' => '',
		    );
		}
		return $fields;
	    }, 50);
	}

	public function add_billing_vat_number_to_wc_my_account() {

	    add_filter('woocommerce_billing_fields', function ($fields) {
		if (!isset($fields['billing_vat_number'])) {
		    $fields['billing_vat_number'] = array(
                        'label'    => __( 'VAT Number', 'phone-orders-for-woocommerce' ),
			'required' => false,
			'class'    => array( 'form-row-wide' ),
			'clear'    => 1,
		    );
		}
		return $fields;
	    }, 50);

	    add_filter('woocommerce_my_account_my_address_formatted_address', function ($billing_fields, $customer_id, $type) {
		if( $type == 'billing' ){
		    $billing_fields['wc_billing_vat_number'] = get_user_meta($customer_id, 'billing_vat_number', true);
		}
		return $billing_fields;
	    }, 50, 3);
	}

	public function add_billing_vat_number_to_wc_customer_formatted_address() {

	    add_filter('woocommerce_formatted_address_replacements', function ($replacements, $args) {

		if ( ! empty( $args['wc_billing_vat_number'] ) ) {
		    $replacements['{vat_number}'] = __( 'VAT Number', 'phone-orders-for-woocommerce' ) . ': ' . $args['wc_billing_vat_number'];
		}

		if ( ! empty( $args['wpo_billing_vat_number'] ) ) {
		    $replacements['{vat_number}'] = $args['wpo_billing_vat_number'];
		}

		if (!isset($replacements['{vat_number}'])) {
		    $replacements['{vat_number}'] = '';
		}

		return $replacements;
	    }, 10, 2);

	    add_filter( 'woocommerce_localisation_address_formats', function ($address_formats) {
		$field_name = 'vat_number';
		$modified_address_formats = array();
		foreach ( $address_formats as $country => $address_format ) {
		    $modified_address_formats[ $country ] = $address_format . "\n{" . $field_name . '}';
		}
		return $modified_address_formats;
	    }, 50);
	}

	public function add_billing_vat_number_to_wpo_customer_formatted_address() {
		add_filter( 'wpo_customer_formatted_address', function ( $fields, $customer_data, $type ) {
			if ( ( $type == 'billing' ) && isset( $customer_data['billing_vat_number'] ) ) {
				$fields['wpo_billing_vat_number'] = $customer_data['billing_vat_number'];
			}

			return $fields;
		}, 50, 3 );
	}

	public function add_custom_fields_to_admin_profile() {
	    add_filter('woocommerce_customer_meta_fields', function ($fields) {
			$settings = WC_Phone_Orders_Settings::getInstance();
			$customer_custom_fields_options = array_merge(
				WC_Phone_Orders_Add_Order_Page_Pro::full_extract_field_from_option( $settings->get_option( 'customer_custom_fields_at_top' ) ),
				WC_Phone_Orders_Add_Order_Page_Pro::full_extract_field_from_option( $settings->get_option( 'customer_custom_fields' ) )
			);
			foreach($customer_custom_fields_options as $custom_field) {
				if (!isset($fields['billing']['fields'][$custom_field['name']])) {
					$fields['billing']['fields'][$custom_field['name']] = array(
					'label'       => $custom_field['label'],
					'description' => '',
					);
				}
			}
			return $fields;
		}, 50);
	}

	public function add_customer_last_order_payment($data, $customer_id) {

		if($customer_id) {
			$orders = wc_get_orders(array(
				'type'    => 'shop_order', // skip refunds!
				'limit'    => 1,
				'customer' => $customer_id,
			));

			if ($orders) {
				$order = $orders[0];
				$data['customer_last_order_payment_method'] = $order->get_payment_method();
			}
		}

	    return $data;
	}

	public function add_customer_last_order_shipping($default_shipping_method_id, $customer_id) {

	    $orders = wc_get_orders(array(
		'limit'    => 1,
		'customer' => $customer_id,
	    ));

	    if ($orders) {

		$order = $orders[0];
		$shipping_methods = $order->get_shipping_methods();
		$shipping_method  = current($shipping_methods);

		$default_shipping_method_id = $shipping_method ? sprintf('%s:%s', $shipping_method->get_method_id(), $shipping_method->get_instance_id()) : null;
	    }

	    return $default_shipping_method_id;
	}

	public function add_custom_fields_in_order_email() {

	    add_filter('woocommerce_email_order_meta_fields', function ($fields, $sent_to_admin, $order) {

		$settings = WC_Phone_Orders_Settings::getInstance();

		$customer_custom_fields_options = array_merge(
		    WC_Phone_Orders_Add_Order_Page_Pro::full_extract_field_from_option( $settings->get_option( 'customer_custom_fields_at_top' ) ),
		    WC_Phone_Orders_Add_Order_Page_Pro::full_extract_field_from_option( $settings->get_option( 'customer_custom_fields' ) )
		);

		$order_custom_fields = WC_Phone_Orders_Add_Order_Page_Pro::full_extract_field_from_option( $settings->get_option( 'order_custom_fields' ) );

		$custom_fields = array_merge($customer_custom_fields_options, $order_custom_fields);

		$order_meta_values = array();

		foreach ( $order->get_meta_data() as $meta ) {
		    $order_meta_values[$meta->key] = $meta->value;
		}

		foreach ($custom_fields as $custom_field) {
		    $fields[] = array(
			'label' => $custom_field['label'],
			'value' => isset($order_meta_values[$custom_field['name']]) ? $order_meta_values[$custom_field['name']] : null,
		    );
		}

		return $fields;

	    }, 50, 3);
	}

	public function restrict_manage_posts() {
		global $typenow;

		if ( in_array( $typenow, wc_get_order_types( 'order-meta-boxes' ), true ) ) {
			$this->render_wc_order_list_filters();
		}
	}

	protected function render_wc_order_list_filters() {

		global $wpdb;

		$user_id = '';

		if ( ! empty( $_GET[WC_Phone_Orders_Loader::$meta_key_order_creator] ) ) {
		    $user_id = absint( $_GET[WC_Phone_Orders_Loader::$meta_key_order_creator] );
		}

		$users = array(
		    array(
			'id'   => '',
			'name' => __('All order creators', 'phone-orders-for-woocommerce'),
		    )
		);

		$order_creators_ids = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT meta_value FROM {$wpdb->postmeta} WHERE {$wpdb->postmeta}.meta_key = %s", WC_Phone_Orders_Loader::$meta_key_order_creator ) );

		$order_creators_ids = array_filter($order_creators_ids);

		foreach ($order_creators_ids as $order_creator_id) {
			$user_data = get_userdata( $order_creator_id );
		    $users[] = array(
			'id'	=> $order_creator_id,
			'name'	=> $user_data ? $user_data->user_login : __('Deleted user #', 'phone-orders-for-woocommerce') . $order_creator_id
		    );
		}

		?>
		<select class="wpo-order-creator-search" name="<?php echo WC_Phone_Orders_Loader::$meta_key_order_creator ?>">
			<?php foreach ($users as $user): ?>
			    <option value="<?php echo $user['id'] ?>" <?php echo $user['id'] == $user_id ? 'selected="selected"' : '' ?>>
				<?php echo $user['name'] ?>
			    </option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	public function request_query( $query_vars ) {
		global $typenow;

		if ( in_array( $typenow, wc_get_order_types( 'order-meta-boxes' ), true ) ) {
			return $this->query_wc_order_list_filters( $query_vars );
		}

		return $query_vars;
	}

	protected function query_wc_order_list_filters( $query_vars ) {

		if ( ! empty( $_GET[WC_Phone_Orders_Loader::$meta_key_order_creator] ) ) {

		    if (!isset($query_vars['meta_query'])) {
			$query_vars['meta_query'] = array();
		    }

		    $query_vars['meta_query'][] = array(
			'key'     => WC_Phone_Orders_Loader::$meta_key_order_creator,
			'value'   => (int) $_GET[WC_Phone_Orders_Loader::$meta_key_order_creator],
			'compare' => '=',
		    );
		}

		return $query_vars;
	}

	public function add_role_to_billing_wc_customer_formatted_address() {

	    add_filter('woocommerce_formatted_address_replacements', function ($replacements, $args) {

		if ( ! empty( $args['wpo_customer_role'] ) ) {
		    $roles = wp_roles()->roles;
		    $role  = $args['wpo_customer_role'];
		    $replacements['{customer_role}'] = isset($roles[$role]) ? translate_user_role($roles[$role]['name']) : '';
		}

		if (!isset($replacements['{customer_role}'])) {
		    $replacements['{customer_role}'] = '';
		}

		return $replacements;
	    }, 10, 2);

	    add_filter( 'woocommerce_localisation_address_formats', function ($address_formats) {
		$field_name = 'customer_role';
		$modified_address_formats = array();
		foreach ( $address_formats as $country => $address_format ) {
		    $modified_address_formats[ $country ] = $address_format . "\n{" . $field_name . '}';
		}
		return $modified_address_formats;
	    });
	}

	public function add_role_to_billing_wpo_customer_formatted_address() {
	    add_filter( 'wpo_customer_formatted_address', function ( $fields, $customer_data, $type ) {
		if ( ( $type == 'billing' ) && ! empty ( $customer_data['id'] ) && isset( $customer_data['role'] ) ) {
			$fields['wpo_customer_role'] = $customer_data['role'];
		}

		return $fields;
	    }, 50, 3 );
	}

	public function render_order_type_filter_wc_order_list($post_type) {

	    if ($post_type !== 'shop_order') {
		return;
	    }

	    $filter_items = array(
		array(
		    'id'   => 'all',
		    'name' => __('All orders', 'phone-orders-for-woocommerce'),
		),
		array(
		    'id'   => 'phone',
		    'name' => __('Only phone orders', 'phone-orders-for-woocommerce'),
		),
		array(
		    'id'   => 'frontend',
		    'name' => __('Only frontend orders', 'phone-orders-for-woocommerce'),
		),
	    );

	    $current_item_id = !empty($_REQUEST['wpo_order_type']) ? $_REQUEST['wpo_order_type'] : 'all';

	    ?>
	    <select class="wpo-order-type-filter" name="wpo_order_type">
		    <?php foreach ($filter_items as $item): ?>
			<option value="<?php echo $item['id'] ?>" <?php echo $item['id'] == $current_item_id ? 'selected="selected"' : '' ?>>
			    <?php echo $item['name'] ?>
			</option>
		    <?php endforeach; ?>
	    </select>
	    <?php
	}

	public function filter_order_type_wc_order_list( $query_vars ) {

	    if ( ! empty( $_REQUEST['wpo_order_type'] ) ) {

		$order_type = $_REQUEST['wpo_order_type'];

		if ( empty( $query_vars['meta_query'] ) ) {
		    $query_vars['meta_query'] = array();
		}

		if ($order_type === 'phone') {
		    $query_vars['meta_query'][] = array(
			'key'     => WC_Phone_Orders_Loader::$meta_key_order_creator,
			'compare' => 'EXISTS',
		    );
		}

		if ($order_type === 'frontend') {
		    $query_vars['meta_query'][] = array(
			'key'     => WC_Phone_Orders_Loader::$meta_key_order_creator,
			'compare' => 'NOT EXISTS',
		    );
		}
	    }

	    return $query_vars;
	}

	protected function auto_login_email_pay_order() {

	    $override_payment_link = WC_Phone_Orders_Settings::getInstance()->get_option( 'override_email_pay_order_link' );

	    if ( ! $override_payment_link ) {
		return;
	    }

	    $this->handle_woocommerce_pay();

	    add_action( 'woocommerce_before_resend_order_emails', function ($order, $email_type) {
		if ($email_type === 'customer_invoice') {
		    add_filter('woocommerce_get_checkout_payment_url', array($this, 'override_order_email_invoice_payment_url'), 10, 2);
		}
	    }, 10, 2);

	    add_action( 'woocommerce_after_resend_order_email', function ($order, $email_type) {
		if ($email_type === 'customer_invoice') {
		    remove_filter('woocommerce_get_checkout_payment_url', array($this, 'override_order_email_invoice_payment_url'), 10, 2);
		}
	    }, 10, 2);
	}

	public function override_order_email_invoice_payment_url($pay_url, $order) {
	    return add_query_arg('wpo_pay_order', $order->get_id(), $pay_url);
	}

	protected function handle_woocommerce_pay() {

	    $order_id = isset($_GET['wpo_pay_order']) ? absint($_GET['wpo_pay_order']) : 0;

	    if ( ! ( isset( $_GET['key'] ) && $order_id ) ) {
		return;
	    }

	    $order_key = isset( $_GET['key'] ) ? wc_clean( wp_unslash( $_GET['key'] ) ) : '';
	    $order     = wc_get_order( $order_id );

	    // Order or payment link is invalid.
	    if ( ! $order || $order->get_id() !== $order_id || ! hash_equals( $order->get_order_key(), $order_key ) ) {
		return;
	    }

	    $redirect = add_query_arg('wpo_pay_order', null, $_SERVER['REQUEST_URI']);

	    $customer_id = (int)$order->get_customer_id();

	    if ( ! is_user_logged_in() && $customer_id && ( $user = get_userdata($customer_id ) ) && ! is_super_admin( $customer_id ) ) {
		wp_set_auth_cookie( $customer_id );
		wp_set_current_user( $customer_id, $user->user_login );
	    }

	    wp_safe_redirect($redirect);
	    exit();
	}

	protected function handle_payment_link_order() {

	    $show_payment_link = WC_Phone_Orders_Settings::getInstance()->get_option( 'show_payment_link_button' );

	    if ( ! $show_payment_link ) {
		return;
	    }

	    $this->handle_woocommerce_pay();
	}
}

new WC_Phone_Orders_Loader_Pro();

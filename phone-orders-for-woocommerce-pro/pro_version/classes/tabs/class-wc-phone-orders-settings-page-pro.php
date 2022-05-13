<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_Phone_Orders_Settings_Page_Pro extends WC_Phone_Orders_Settings_Page {

	private function make_default_customer_object() {

		$customer_id = (int) $this->option_handler->get_option( 'default_customer_id' );

		if( !$customer_id )
			return null;

		$customer    = new WC_Customer( $customer_id );

		$default_customer_object = array(
			'value' => $customer_id,
			'title' => sprintf(
				'%s %s (#%s - %s)',
				$customer->get_first_name(),
				$customer->get_last_name(),
				$customer->get_id(),
				$customer->get_email()
			),
		);

		return $default_customer_object;
	}

	public function __construct() {
		parent::__construct();
		add_action( 'wpo_add_settings', array( $this, 'add_settings' ) );
		add_action( 'wpo_add_interface_settings', array( $this, 'add_interface_settings' ) );
		add_action( 'wpo_add_woocommerce_settings', array( $this, 'add_woocommerce_settings' ) );
		add_action( 'wpo_add_tax_settings', array( $this, 'add_tax_settings' ) );
		add_action( 'wpo_add_layout_settings', array( $this, 'add_layout_settings' ) );
		add_action( 'wpo_add_coupons_settings', array( $this, 'add_coupons_settings' ) );
		add_action( 'wpo_add_shipping_settings', array( $this, 'add_shipping_settings' ) );
		add_action( 'wpo_add_cart_items_settings', array( $this, 'add_cart_items_settings' ) );
	}

	public function add_settings() {
		$settings_option_handler = $this->option_handler;

		$item_ids = $settings_option_handler->get_option( 'item_default_search_result' );

		$item_default_search_result = array();

		if ( is_array( $item_ids ) ) {

			foreach ( $item_ids as $iid ) {

				$item = wc_get_product( $iid );

				if ( ! $item ) {
					continue;
				}

				$title = $this->format_row_product( $item );

				$item_default_search_result[] = array(
				    'title' => $title,
				    'value' => $iid,
				);
			}
		}

		$order_statuses_list = array();

		foreach ( wc_get_order_statuses() as $i => $status ) {
		    $order_statuses_list[] = array(
			'value' => $i,
			'title' => $status,
		    );
		}
		$order_statuses_list[] = array(
			'value' => 'draft',
			'title' => __( 'Draft', 'phone-orders-for-woocommerce' ),
		);

		$available_hide_fields_list = array(
			array(
			'label' => __( 'First name', 'phone-orders-for-woocommerce' ),
			'key'   => 'first_name',
			),
		    array(
			'label' => __( 'Last name', 'phone-orders-for-woocommerce' ),
			'key'   => 'last_name',
		    ),
		    array(
			'label' => __( 'Company', 'phone-orders-for-woocommerce' ),
			'key'   => 'company',
		    ),
		    array(
			'label' => __( 'Address 1', 'phone-orders-for-woocommerce' ),
			'key'   => 'address_1',
		    ),
		    array(
			'label' => __( 'Address 2', 'phone-orders-for-woocommerce' ),
			'key'   => 'address_2',
		    ),
		    array(
			'label' => __( 'City', 'phone-orders-for-woocommerce' ),
			'key'   => 'city',
		    ),
		    array(
			'label' => __( 'Postcode', 'phone-orders-for-woocommerce' ),
			'key'   => 'postcode',
		    ),
		    array(
			'label' => __( 'Country', 'phone-orders-for-woocommerce' ),
			'key'   => 'country',
		    ),
		    array(
			'label' => __( 'State', 'phone-orders-for-woocommerce' ),
			'key'   => 'state',
		    ),
		);

		$available_required_fields_list = array(
		    array(
			'label' => __( 'First name', 'phone-orders-for-woocommerce' ),
			'key'   => 'first_name',
		    ),
		    array(
			'label' => __( 'Last name', 'phone-orders-for-woocommerce' ),
			'key'   => 'last_name',
		    ),
		    array(
			'label' => __( 'Email', 'phone-orders-for-woocommerce' ),
			'key'   => 'email',
		    ),
		    array(
			'label' => __( 'Phone', 'phone-orders-for-woocommerce' ),
			'key'   => 'phone',
		    ),
		    array(
			'label' => __( 'Company', 'phone-orders-for-woocommerce' ),
			'key'   => 'company',
		    ),
		    array(
			'label' => __( 'Address 1', 'phone-orders-for-woocommerce' ),
			'key'   => 'address_1',
		    ),
		    array(
			'label' => __( 'Address 2', 'phone-orders-for-woocommerce' ),
			'key'   => 'address_2',
		    ),
		    array(
			'label' => __( 'City', 'phone-orders-for-woocommerce' ),
			'key'   => 'city',
		    ),
		    array(
			'label' => __( 'Postcode', 'phone-orders-for-woocommerce' ),
			'key'   => 'postcode',
		    ),
		    array(
			'label' => __( 'Country', 'phone-orders-for-woocommerce' ),
			'key'   => 'country',
		    ),
		    array(
			'label' => __( 'State', 'phone-orders-for-woocommerce' ),
			'key'   => 'state',
		    ),
		);

		$tab_data_pro = array(
			'pro' => array(
				'runAtFrontendSettings'         => array(
					'title'                                       => __( "Run at frontend", 'phone-orders-for-woocommerce' ),
					'frontendPageInstructions'	      => __('The user must be admin or has capability "edit_shop_orders"', 'phone-orders-for-woocommerce'),
					'frontendPagePluginsHelpLinkLabel'     => __('If you use the plugins to create complex products or to tweak product options - read this section', 'phone-orders-for-woocommerce'),
					'frontendPageLabel'			      => __('Accept orders at frontend page', 'phone-orders-for-woocommerce'),
					'frontendPage'				      => $settings_option_handler->get_option('frontend_page'),
					'frontendPageUrlLabel'			      => __('Frontend page url', 'phone-orders-for-woocommerce'),
					'frontendPageUrl'			      => $settings_option_handler->get_option('frontend_page_url'),

					'hideThemeHeaderLabel'			      => __('Hide theme header', 'phone-orders-for-woocommerce'),
					'hideThemeHeader'			      => $settings_option_handler->get_option('frontend_hide_theme_header'),
					'hideThemeFooterLabel'			      => __('Hide theme footer', 'phone-orders-for-woocommerce'),
					'hideThemeFooter'			      => $settings_option_handler->get_option('frontend_hide_theme_footer'),
					'allowToConfigureProductLabel'		      => __('Allow to configure product', 'phone-orders-for-woocommerce'),
					'allowToConfigureProduct'		      => $settings_option_handler->get_option('allow_to_configure_product'),
					'allowToUseMinifiedPageConfigureProductLabel' => __('Button "Configure Product" uses minified page', 'phone-orders-for-woocommerce'),
					'allowToUseMinifiedPageConfigureProduct'      => $settings_option_handler->get_option('allow_to_use_minified_page_configure_product'),
					'allowToAddProductsFromShopPageLabel' => __('Allow to add products from shop page', 'phone-orders-for-woocommerce'),
					'allowToAddProductsFromShopPage' => $settings_option_handler->get_option('allow_to_add_products_from_shop_page'),
				),
				'customerSettings'         => array(
					'title'                                       => __( "Customers", 'phone-orders-for-woocommerce' ),
					'cacheCustomerTimeoutLabel'                   => __( 'Caching search results',
						'phone-orders-for-woocommerce' ),
					'hoursLabel'                                  => __( "hours", 'phone-orders-for-woocommerce' ),
					'cacheCustomersSessionKey'                    => $settings_option_handler->get_option( 'cache_customers_session_key' ),
					'cacheCustomersTimeout'                       => (int) $settings_option_handler->get_option( 'cache_customers_timeout' ),
					'cacheCustomersDisableButton'                 => __( "Disable cache",
						'phone-orders-for-woocommerce' ),
					'cacheCustomersResetButton'                   => __( "Reset cache",
						'phone-orders-for-woocommerce' ),
					'searchAllCustomerFields'                     => $settings_option_handler->get_option( 'search_all_customer_fields' ),
					'searchAllCustomerLabel'                      => __( "Customer search by shipping/billing fields",
						'phone-orders-for-woocommerce' ),
					'searchCustomerInOrders'                      => $settings_option_handler->get_option( 'search_customer_in_orders' ),
					'searchCustomerInOrdersLabel'                 => __( "Search for customer in orders",
						'phone-orders-for-woocommerce' ),
					'numberOfCustomersToShowLabel'                => __( 'Number of customers to show in autocomplete',
						'phone-orders-for-woocommerce' ),
					'numberOfCustomersToShow'                     => (int) $settings_option_handler->get_option( 'number_of_customers_to_show' ),
					'defaultCustomerLabel'                        => __( 'Default customer',
						'phone-orders-for-woocommerce' ),
					'defaultCustomerObject'                       => $this->make_default_customer_object(),
					'defaultCustomersList'                        => array(),
					'updateCustomersProfileAfterCreateOrderLabel' => __( "Automatically update customer's profile on order creation",
						'phone-orders-for-woocommerce' ),
					'updateCustomersProfileAfterCreateOrder'      => $settings_option_handler->get_option( 'update_customers_profile_after_create_order' ),
					'selectDefaultCustomerPlaceholder'            => _x( "Type to search", "search default customer placeholder",
						'phone-orders-for-woocommerce' ),
					'doNotSubmitOnEnterLastField'                 => $settings_option_handler->get_option( 'do_not_submit_on_enter_last_field' ),
					'doNotSubmitOnEnterLastFieldLabel'            => __( "Don't close customer/address form automatically",
						'phone-orders-for-woocommerce' ),
					'noResultLabel'                               => __( "Oops! No elements found. Consider changing the search query.",
						'phone-orders-for-woocommerce' ),
					'multiSelectSearchDelay'                      => $this->multiselect_search_delay,
					'allowMultipleAddressesLabel'		      => __( "Show address book", 'phone-orders-for-woocommerce' ),
					'compatibleAddressBookNotice'	          => __( "It's compatible with", 'phone-orders-for-woocommerce' ),
					'compatibleAddressBookPluginName'	      => __( "WooCommerce Multiple Customer Addresses", 'phone-orders-for-woocommerce' ),
					'allowMultipleAddresses'                      => $settings_option_handler->get_option( 'allow_multiple_addresses' ),
					'useShippingPhoneLabel'			      => __( 'Show field "Shipping Phone"', 'phone-orders-for-woocommerce' ),
					'useShippingPhone'			      => $settings_option_handler->get_option( 'use_shipping_phone' ),
					'noOptionsTitle'                              => __( 'List is empty.', 'phone-orders-for-woocommerce' ),
					'supportFieldVATLabel'			      => __( 'Show field "VAT Number"', 'phone-orders-for-woocommerce' ),
					'supportFieldVAT'			      => $settings_option_handler->get_option( 'support_field_vat' ),
					'updateWPUserFirstLastNameLabel'	      => __( "Update user's first and last name when updating billing information", 'phone-orders-for-woocommerce' ),
					'updateWPUserFirstLastName'		      => $settings_option_handler->get_option( 'update_wp_user_first_last_name' ),
					'showOrderHistoryCustomerLabel'		      => __('Show order history for the customer', 'phone-orders-for-woocommerce'),
					'showOrderHistoryCustomer'		      => $settings_option_handler->get_option('show_order_history_customer'),
					'usePaymentDeliveryLastOrderLabel'	      => __( 'Use payment and delivery preferences from last order', 'phone-orders-for-woocommerce' ),
					'usePaymentDeliveryLastOrder'		      => $settings_option_handler->get_option( 'use_payment_delivery_last_order' ),

					'limitOrdersOfSearchCustomerLabel'	      => __('Search for customers in last X orders, 0 - unlimited', 'phone-orders-for-woocommerce'),
					'limitOrdersOfSearchCustomer'		      => $settings_option_handler->get_option( 'limit_orders_of_search_customer' ),
					'hideFieldsLabel'			     => __( 'Hide fields', 'phone-orders-for-woocommerce' ),
					'availableHideFieldsList'		     => $available_hide_fields_list,
					'hideFieldsList'			     => $settings_option_handler->get_option( 'customer_hide_fields' ),
					'requiredFieldsLabel'			     => __( 'Required fields', 'phone-orders-for-woocommerce' ),
					'availableRequiredFieldsList'		     => $available_required_fields_list,
					'requiredFieldsList'			     => $settings_option_handler->get_option( 'customer_required_fields' ),
					'showRoleFieldLabel'			     => __( 'Show Role field', 'phone-orders-for-woocommerce' ),
					'showRoleField'				     => $settings_option_handler->get_option( 'customer_show_role_field' ),
					'showLanguageFieldLabel'                     => __( 'Show Language field', 'phone-orders-for-woocommerce' ),
					'showLanguageField'                          => $settings_option_handler->get_option( 'customer_show_language_field' ),
				),
				'newCustomerPopupSettings' => array(
					'title'                                      => __( "New Customer",
						'phone-orders-for-woocommerce' ),
					'newcustomerShowPasswordFieldLabel'          => __( "Show Password field",
						'phone-orders-for-woocommerce' ),
					'newcustomerShowPasswordField'               => $settings_option_handler->get_option( 'newcustomer_show_password_field' ),
					'newcustomerShowPasswordFieldNote'           => __( "You have to tell them the password",
						'phone-orders-for-woocommerce' ),
					'newcustomerShowUsernameFieldLabel'          => __( "Show Username field",
						'phone-orders-for-woocommerce' ),
					'newcustomerShowUsernameField'               => $settings_option_handler->get_option( 'newcustomer_show_username_field' ),
					'requiredFieldsLabel'			     => __( 'Required fields', 'phone-orders-for-woocommerce' ),
					'availableRequiredFieldsList'		     => $available_required_fields_list,
					'requiredFieldsList'			     => $settings_option_handler->get_option( 'newcustomer_required_fields' ),
					'hideFieldsLabel'                            => __( "Hide fields", 'phone-orders-for-woocommerce' ),
					'hideCompanyLabel'                           => __( "Company", 'phone-orders-for-woocommerce' ),
					'hideCompany'                                => $settings_option_handler->get_option( 'newcustomer_hide_company' ),
					'hideFirstNameLabel'                         => __( "First name", 'phone-orders-for-woocommerce' ),
					'hideFirstName'                              => $settings_option_handler->get_option( 'newcustomer_hide_first_name' ),
					'hideLastNameLabel'                         => __( "Last name", 'phone-orders-for-woocommerce' ),
					'hideLastName'                              => $settings_option_handler->get_option( 'newcustomer_hide_last_name' ),
					'hideEmailLabel'                             => __( "Email", 'phone-orders-for-woocommerce' ),
					'hideEmail'                                  => $settings_option_handler->get_option( 'newcustomer_hide_email' ),
					'hideAddress1Label'                          => __( "Address 1", 'phone-orders-for-woocommerce' ),
					'hideAddress1'                               => $settings_option_handler->get_option( 'newcustomer_hide_address_1' ),
					'hideAddress2Label'                          => __( "Address 2", 'phone-orders-for-woocommerce' ),
					'hideAddress2'                               => $settings_option_handler->get_option( 'newcustomer_hide_address_2' ),
					'hideCityLabel'                              => __( "City", 'phone-orders-for-woocommerce' ),
					'hideCity'                                   => $settings_option_handler->get_option( 'newcustomer_hide_city' ),
					'hidePostcodeLabel'                          => __( "Postcode", 'phone-orders-for-woocommerce' ),
					'hidePostcode'                               => $settings_option_handler->get_option( 'newcustomer_hide_postcode' ),
					'hideCountryLabel'                           => __( "Country", 'phone-orders-for-woocommerce' ),
					'hideCountry'                                => $settings_option_handler->get_option( 'newcustomer_hide_country' ),
					'hideStateLabel'                             => __( "State", 'phone-orders-for-woocommerce' ),
					'hideState'                                  => $settings_option_handler->get_option( 'newcustomer_hide_state' ),
					'defaultCityLabel'                           => __( 'Default city',
						'phone-orders-for-woocommerce' ),
					'defaultCity'                                => $settings_option_handler->get_option( 'default_city' ),
					'defaultPostcodeLabel'                       => __( 'Default postcode',
						'phone-orders-for-woocommerce' ),
					'defaultPostcode'                            => $settings_option_handler->get_option( 'default_postcode' ),
					'defaultCountryLabel'                        => __( 'Default country',
						'phone-orders-for-woocommerce' ),
					'defaultCountry'                             => $settings_option_handler->get_option( 'default_country' ),
					'defaultStateLabel'                          => __( 'Default  state/county',
						'phone-orders-for-woocommerce' ),
					'defaultState'                               => $settings_option_handler->get_option( 'default_state' ),
					'selectPlaceholder'                          => __( 'Select option',
						'phone-orders-for-woocommerce' ),
					'dontFillShippingAddressForNewCustomer'      => $settings_option_handler->get_option( 'dont_fill_shipping_address_for_new_customer' ),
					'dontFillShippingAddressForNewCustomerLabel' => __( 'Don\'t fill shipping address',
						'phone-orders-for-woocommerce' ),
					'disableCreatingCustomersLabel'              => __( 'Disable creating customers',
						'phone-orders-for-woocommerce' ),
					'disableCreatingCustomers'                   => $settings_option_handler->get_option( 'disable_creating_customers' ),
					'newcustomerShowRoleFieldLabel'              => __( 'Show Role field', 'phone-orders-for-woocommerce' ),
					'newcustomerShowRoleField'                   => $settings_option_handler->get_option( 'newcustomer_show_role_field' ),
					'newcustomerShowLanguageFieldLabel'          => __( 'Show Language field', 'phone-orders-for-woocommerce' ),
					'newcustomerShowLanguageField'               => $settings_option_handler->get_option( 'newcustomer_show_language_field' ),
					'defaultRoleLabel'                           => __( 'Default role', 'phone-orders-for-woocommerce' ),
					'defaultRole'                                => $settings_option_handler->get_option( 'default_role' ),
					'disableNewUserEmailLabel'                   => __( 'Disable user notification email', 'phone-orders-for-woocommerce' ),
					'disableNewUserEmail'                        => $settings_option_handler->get_option( 'disable_new_user_email' ),
					'rolesList'                                  => $this->make_roles_list(),
					'tabName'                                    => 'settings',
					'noOptionsTitle'                             => __( 'List is empty.', 'phone-orders-for-woocommerce' ),
					'allowedRolesNewCustomerLabel'		     => __( 'Allowed roles', 'phone-orders-for-woocommerce' ),
					'allowedRolesNewCustomer'		     => $settings_option_handler->get_option( 'allowed_roles_new_customer' ),
					'showAllowedRolesNewCustomerSelect'	     => is_super_admin(),
					'createCustomerBaseOnExistingOrderLabel'     => __( 'Create based on existing order', 'phone-orders-for-woocommerce' ),
					'createCustomerBaseOnExistingOrder'	     => $settings_option_handler->get_option( 'create_customer_base_on_existing_order' ),
				),
				'productsSettings' => array(
					'title'                                 => __( 'Products', 'phone-orders-for-woocommerce' ),
					'productsCacheProductsTimeoutLabel'     => __( 'Caching search results', 'phone-orders-for-woocommerce' ),
					'hoursLabel'                            => __( "hours", 'phone-orders-for-woocommerce' ),
					'disableCacheButtonLabel'               => __( 'Disable cache', 'phone-orders-for-woocommerce' ),
					'resetCacheButtonLabel'                 => __( 'Reset cache', 'phone-orders-for-woocommerce' ),
					'productsSearchBySkuLabel'              => __( 'Search by SKU', 'phone-orders-for-woocommerce' ),
					'productsVerboseSearchLabel'            => __( 'Deep search', 'phone-orders-for-woocommerce' ),
					'mayBeSlowLabel'                        => __( 'in parent product, may be slow', 'phone-orders-for-woocommerce' ),
					'productsSearchByCatAndTagLabel'        => __( 'Filter products by category/tags', 'phone-orders-for-woocommerce' ),
					'productsNumberOfProductsToShowLabel'   => __( 'Number of products to show in autocomplete', 'phone-orders-for-woocommerce' ),
					'productsSortByRelevancyLabel'		=> __( 'Sort by relevance', 'phone-orders-for-woocommerce' ),
					'hideProductFieldsLabel'                => __( 'Hide fields in autocomplete', 'phone-orders-for-woocommerce' ),
					'hideStatusLabel'                       => __( "Status", 'phone-orders-for-woocommerce' ),
					'hideQtyLabel'                          => __( "Qty", 'phone-orders-for-woocommerce' ),
					'hidePriceLabel'                        => __( "Price", 'phone-orders-for-woocommerce' ),
					'hideSkuLabel'                          => __( "Sku", 'phone-orders-for-woocommerce' ),
					'hideNameLabel'                         => __( "Name", 'phone-orders-for-woocommerce' ),
					'productsShowLongAttributeNamesLabel'   => __( 'Show long attribute names', 'phone-orders-for-woocommerce' ),
					'allowDuplicateProductsLabel'           => __( 'Allow to use same product many times', 'phone-orders-for-woocommerce' ),
					'productsHideProductsWithNoPriceLabel'  => __( 'Don\'t sell products with no price defined', 'phone-orders-for-woocommerce' ),
					'productsSellBackorderProductLabel'     => __( 'Sell "out of stock" products', 'phone-orders-for-woocommerce' ),
					'productsSellDisableVariationLabel'     => __( 'Sell disabled variations', 'phone-orders-for-woocommerce' ),
					'noOptionsTitle'                        => __( 'List is empty.', 'phone-orders-for-woocommerce' ),

					'cacheSessionKey'            => $settings_option_handler->get_option( 'cache_products_session_key' ),
					'cacheTimeout'               => (int) $settings_option_handler->get_option( 'cache_products_timeout' ),
					'searchBySku'                => $settings_option_handler->get_option( 'search_by_sku' ),
					'verboseSearch'              => $settings_option_handler->get_option( 'verbose_search' ),
					'searchByCatAndTag'          => $settings_option_handler->get_option( 'search_by_cat_and_tag' ),
					'numberOfProductsToShow'     => (int) $settings_option_handler->get_option( 'number_of_products_to_show' ),
					'sortProductsByRelevancy'    => $settings_option_handler->get_option( 'sort_products_by_relevancy' ),
					'hideImage'                  => $settings_option_handler->get_option( 'autocomplete_product_hide_image' ),
					'hideStatus'                 => $settings_option_handler->get_option( 'autocomplete_product_hide_status' ),
					'hideQty'                    => $settings_option_handler->get_option( 'autocomplete_product_hide_qty' ),
					'hidePrice'                  => $settings_option_handler->get_option( 'autocomplete_product_hide_price' ),
					'hideSku'                    => $settings_option_handler->get_option( 'autocomplete_product_hide_sku' ),
					'hideName'                   => $settings_option_handler->get_option( 'autocomplete_product_hide_name' ),
					'showLongAttributeNames'     => $settings_option_handler->get_option( 'show_long_attribute_names' ),
					'allowDuplicateProducts'     => $settings_option_handler->get_option( 'allow_duplicate_products' ),
					'hideProductsWithNoPrice'    => $settings_option_handler->get_option( 'hide_products_with_no_price' ),
					'saleBackorderProducts'      => $settings_option_handler->get_option( 'sale_backorder_product' ),
					'sellDisableVariation'       => $settings_option_handler->get_option( 'sell_disable_variation' ),

					'noResultLabel'                  => __( "Oops! No elements found. Consider changing the search query.", 'phone-orders-for-woocommerce' ),
					'itemDefaultSelectedPlaceholder' => __( "Select items", 'phone-orders-for-woocommerce' ),
					'tabName'                        => 'settings',
					'multiSelectSearchDelay'         => $this->multiselect_search_delay,
					'productsDefaultSearchResultLabel'	=> __( 'Show products as default search result', 'phone-orders-for-woocommerce' ),
					'itemDefaultSearchResultPlaceholder'	=> __( 'Select items', 'phone-orders-for-woocommerce' ),
					'itemDefaultSearchResult'		=> $item_default_search_result,

					'displaySearchResultAsGridLabel'    => __( 'Show search results as a grid of images', 'phone-orders-for-woocommerce' ),
					'displaySearchResultAsGrid'	    => $settings_option_handler->get_option( 'display_search_result_as_grid' ),
					'showQtyInputAdvancedSearchLabel'   => __( 'Show QTY input in Advanced Search popup', 'phone-orders-for-woocommerce' ),
					'showQtyInputAdvancedSearch'	    => $settings_option_handler->get_option( 'show_qty_input_advanced_search' ),
					'showPriceInputAdvancedSearchLabel' => __( 'Show PRICE input in Advanced Search popup', 'phone-orders-for-woocommerce' ),
					'showPriceInputAdvancedSearch'	    => $settings_option_handler->get_option( 'show_price_input_advanced_search' ),

					'actionClickOnTitleProductItemInSearchProductsLabel'		=> __( 'Click on title - action, browse products', 'phone-orders-for-woocommerce' ),
					'actionClickOnTitleProductItemInSearchProductsAddProductToCartLabel' => __( 'Add to cart', 'phone-orders-for-woocommerce' ),
					'actionClickOnTitleProductItemInSearchProductsEditProductLabel'	=> __( 'Edit product', 'phone-orders-for-woocommerce' ),
					'actionClickOnTitleProductItemInSearchProductsViewProductLabel'	=> __( 'View product', 'phone-orders-for-woocommerce' ),
					'actionClickOnTitleProductItemInSearchProducts'			=> $settings_option_handler->get_option( 'action_click_on_title_product_item_in_search_products' ),
					'barcodeModeLabel'  => __( 'Barcode scanner mode', 'phone-orders-for-woocommerce' ),
					'barcodeMode'	    => $settings_option_handler->get_option( 'barcode_mode' ),
				),
				'addItemPopupSettings' => array(
					'title'                                => __( 'New Product', 'phone-orders-for-woocommerce' ),
					'productsDisableCreatingProductsLabel' => __( 'Disable creating products', 'phone-orders-for-woocommerce' ),
					'productsNewProductAskSKULabel'        => __( 'Show SKU while adding product', 'phone-orders-for-woocommerce' ),
					'productsNewProductVisibilityLabel'    => __( 'New product visibility', 'phone-orders-for-woocommerce' ),
					'addItemTaxClassLabel'                 => __( 'Default tax class', 'phone-orders-for-woocommerce' ),
					'productsNewProductAskTaxClassLabel'   => __( 'Show tax class selector', 'phone-orders-for-woocommerce' ),
					'noOptionsTitle'                       => __( 'List is empty.', 'phone-orders-for-woocommerce' ),
					'editCreatedProductInNewTabLabel'	   => __( 'Edit created product in new tab', 'phone-orders-for-woocommerce'),

					'productsThisSettingDeterminesWhichShopPagesProductsWillBeListedOn' => __( 'This setting determines which shop pages products will be listed on.', 'woocommerce' ),

					'disableAddingProducts'    => $settings_option_handler->get_option( 'disable_adding_products' ),
					'newProductAskSKU'         => $settings_option_handler->get_option( 'new_product_ask_sku' ),
					'productVisibility'        => $settings_option_handler->get_option( 'product_visibility' ),
					'productVisibilityOptions' => $this->make_product_visibility_options(),

					'newProductAskTaxClass' => $settings_option_handler->get_option( 'new_product_ask_tax_class' ),
					'itemTaxClass'          => $settings_option_handler->get_option( 'item_tax_class' ),
					'itemTaxClasses'        => $this->make_tax_classes(),
					'editCreatedProductInNewTab'	=> $settings_option_handler->get_option('edit_created_product_in_new_tab'),
					'showProductCategoryLabel'  => __( 'Show category selector', 'phone-orders-for-woocommerce'),
					'showProductCategory'	    => $settings_option_handler->get_option('show_product_category'),
					'showProductPropertiesLabel' => __( 'Show product properties', 'phone-orders-for-woocommerce'),
					'newProductShowWeightLabel'  => __( 'Weight', 'phone-orders-for-woocommerce'),
					'newProductShowWeight'	     => $settings_option_handler->get_option('new_product_show_weight'),
					'newProductShowLengthLabel'  => __( 'Length', 'phone-orders-for-woocommerce'),
					'newProductShowLength'	     => $settings_option_handler->get_option('new_product_show_length'),
					'newProductShowWidthLabel'   => __( 'Width', 'phone-orders-for-woocommerce'),
					'newProductShowWidth'	     => $settings_option_handler->get_option('new_product_show_width'),
					'newProductShowHeightLabel'  => __( 'Height', 'phone-orders-for-woocommerce'),
					'newProductShowHeight'	     => $settings_option_handler->get_option('new_product_show_height'),
					'createWoocommerceProductLabel'   => __( 'Checkbox "Create WooCommerce product"', 'phone-orders-for-woocommerce'),
					'createWoocommerceProduct'	  => $settings_option_handler->get_option('new_product_create_woocommerce_product'),
                    'createPrivateProductLabel'   => __('Create private product', 'phone-orders-for-woocommerce'),
                    'createPrivateProduct'        => $settings_option_handler->get_option('create_private_product'),
                    'createPrivateProductLabelTip' => __("To use such products - you must turn off 'Switch Customer' at tab Common !", 'phone-orders-for-woocommerce'),
					'createWoocommerceProductOptions' => array(
					    array(
						'title' => __( "Don't show checkbox, create product", 'phone-orders-for-woocommerce'),
						'value' => 'dont_show_checkbox__create_product',
					    ),
					    array(
						'title' => __( "Don't show checkbox, don't create product", 'phone-orders-for-woocommerce'),
						'value' => 'dont_show_checkbox__dont_create_product',
					    ),
					    array(
						'title' => __( "Show marked checkbox", 'phone-orders-for-woocommerce'),
						'value' => 'show_checkbox__marked',
					    ),
					    array(
						'title' => __( "Show unmarked checkbox", 'phone-orders-for-woocommerce'),
						'value' => 'show_checkbox__unmarked',
					    ),
					),
				),
				'feeSettings'              => array(
					'title'			    => __( 'Fee', 'phone-orders-for-woocommerce' ),
					'hideAddFeeLabel'	    => __( 'Hide "Add fee"', 'phone-orders-for-woocommerce' ),
					'feeNameLabel'		    => __( 'Fee name', 'phone-orders-for-woocommerce' ),
					'feeAmountLabel'	    => __( 'Fee amount', 'phone-orders-for-woocommerce' ),
					'feeTaxClassLabel'	    => __( 'Fee tax class', 'phone-orders-for-woocommerce' ),
					'noOptionsTitle'	    => __( 'List is empty.', 'phone-orders-for-woocommerce' ),
					'allowToUseZeroAmountLabel' => __( 'Allow to use zero amount', 'phone-orders-for-woocommerce' ),
					'hideAddFee'		    => $settings_option_handler->get_option( 'hide_add_fee' ),
					'defaultFeeName'	    => $settings_option_handler->get_option( 'default_fee_name' ),
					'defaultFeeAmount'	    => $settings_option_handler->get_option( 'default_fee_amount' ),
					'feeTaxClass'		    => $settings_option_handler->get_option( 'fee_tax_class' ),
					'taxClasses'		    => $this->make_tax_classes(),
					'allowToUseZeroAmount'	    => $settings_option_handler->get_option( 'allow_to_use_zero_amount' ),
				),
				'discountSettings'         => array(
					'title'                => __( 'Discount', 'phone-orders-for-woocommerce' ),
					'hideAddDiscountLabel' => __( 'Hide "Add discount"', 'phone-orders-for-woocommerce' ),
					'couponNameLabel'      => __( 'Coupon name  (used by manual discount)',
						'phone-orders-for-woocommerce' ),
					'allowToEditCouponNameLabel' => __( 'Allow to edit coupon name', 'phone-orders-for-woocommerce' ),
					'hideAddDiscount'      => $settings_option_handler->get_option( 'hide_add_discount' ),
					'manualCouponTitle'    => $settings_option_handler->get_option( 'manual_coupon_title' ),
					'allowToEditCouponName'=> $settings_option_handler->get_option( 'allow_to_edit_coupon_name' ),
				),
				'copyOrdersSettings' => array(
					'title'                                     => __( 'Existing orders', 'phone-orders-for-woocommerce' ),
					'cacheTimeoutLabel'                         => __( 'Caching search results',
						'phone-orders-for-woocommerce' ),
                    'hoursLabel'                                => __( "hours", 'phone-orders-for-woocommerce'),
					'disableCacheButtonLabel'                   => __( 'Disable cache',
						'phone-orders-for-woocommerce' ),
					'resetCacheButtonLabel'                     => __( 'Reset cache', 'phone-orders-for-woocommerce' ),
					'showButtonCopyOrderLabel'                  => __( 'Show buttons', 'phone-orders-for-woocommerce' ),
					'setCurrentPriceForItemsInCopiedOrderLabel' => __( 'Set current price for items in copied order',
						'phone-orders-for-woocommerce' ),
					'setCurrentPriceForShippingInCopiedOrderLabel' => __( 'Set current price for shipping in copied order', 'phone-orders-for-woocommerce' ),
					'hideFindOrdersLabel'                       => __( 'Hide "Find orders"', 'phone-orders-for-woocommerce' ),
					'sessionKey'                                => $settings_option_handler->get_option( 'cache_orders_session_key' ),
					'cacheTimeout'                              => (int) $settings_option_handler->get_option( 'cache_orders_timeout' ),
					'buttonForFindOrder'                        => $settings_option_handler->get_option( 'button_for_find_orders' ),
					'setCurrentPriceInCopiedOrder'              => $settings_option_handler->get_option( 'set_current_price_in_copied_order' ),
					'setCurrentPriceForShippingInCopiedOrder'   => $settings_option_handler->get_option( 'set_current_price_shipping_in_copied_order' ),
					'hideFindOrders'                            => $settings_option_handler->get_option( 'hide_find_orders' ),
					'seekOnlyOrdersWithStatusLabel'             => __( 'Seek only in orders having status', 'phone-orders-for-woocommerce'),
					'seekOnlyOrdersWithStatusPlaceholder'       => __( 'Select items', 'phone-orders-for-woocommerce'),
					'seekOnlyOrdersWithStatusList'              => $settings_option_handler->get_option( 'seek_only_orders_with_statuses' ),
					'dontAllowEditOrderHaveStatusListLabel'		=> __( "Don't allow to edit orders having status", 'phone-orders-for-woocommerce' ),
					'dontAllowEditOrderHaveStatusListPlaceholder'	=> __( "Select items", 'phone-orders-for-woocommerce' ),
					'orderStatusesList'				=> $order_statuses_list,
					'dontAllowEditOrderHaveStatusList'		=> $settings_option_handler->get_option( 'dont_allow_edit_order_have_status_list' ),
					'showOrdersCurrentUserLabel'		    => __( "Seek only in orders created by current user", 'phone-orders-for-woocommerce' ),
					'showOrdersCurrentUser'			    => $settings_option_handler->get_option( 'show_orders_current_user' ),
				),
				'miscSettings' => array(
					'title'                => __( 'Custom fields', 'phone-orders-for-woocommerce' ),
					'titleLinkLabel'       => __( 'How to define custom fields', 'phone-orders-for-woocommerce' ),
					'customFieldsLabel'    => __( 'Order fields', 'phone-orders-for-woocommerce' ),
					'oneFieldPerLineLabel' => __( 'One field per line, use format: Label Text|custom_fieldname', 'phone-orders-for-woocommerce' ),
					'orderCustomFields'    => $settings_option_handler->get_option( 'order_custom_fields' ),

					'customerCustomFieldsAtBottomLabel' => __( 'Customer fields (at bottom)', 'phone-orders-for-woocommerce' ),
					'customerCustomFields'    => $settings_option_handler->get_option( 'customer_custom_fields' ),

					'replaceOrderWithCustomerCustomFieldsLabel'        => __( 'Replace order fields with customer fields', 'phone-orders-for-woocommerce' ),
					'replaceOrderWithCustomerCustomFields'             => $settings_option_handler->get_option( 'replace_order_with_customer_custom_fields' ),

					'itemMetaFieldsLabel'                => __( 'Available fields for product', 'phone-orders-for-woocommerce' ),
					'itemMetaFieldsOneFieldPerLineLabel' => __( 'One field per line', 'phone-orders-for-woocommerce' ),
					'itemCustomMetaFields'               => $settings_option_handler->get_option( 'item_custom_meta_fields' ),

					'defaultListItemMetaFieldsLabel'                => __( 'Default fields for product', 'phone-orders-for-woocommerce' ),
					'defaultListItemMetaFieldsOneFieldPerLineLabel' => __( 'One field and value per line, separated by |, e.g meta_key|meta_value', 'phone-orders-for-woocommerce' ),
					'defaultListItemCustomMetaFields'               => $settings_option_handler->get_option( 'default_list_item_custom_meta_fields' ),

					'customerCustomFieldsHeaderTextAtBottomLabel'	=> __( "Section title for customer fields (at bottom)", 'phone-orders-for-woocommerce' ),
					'customerCustomFieldsHeaderText'              => $settings_option_handler->get_option( 'customer_custom_fields_header_text' ),

                    'addFormattedCustomFieldsLabel'               => __( "Show custom fields in section \"Billing Details\"", 'phone-orders-for-woocommerce' ),
					'addFormattedCustomFields'                    => $settings_option_handler->get_option( 'add_formatted_custom_fields' ),
					'customerCustomFieldsHeaderTextAtTopLabel'    => __( "Section title for customer fields (at top)", 'phone-orders-for-woocommerce' ),
					'customerCustomFieldsHeaderTextAtTop'	      => $settings_option_handler->get_option( 'customer_custom_fields_header_text_at_top' ),
					'customerCustomFieldsAtTopLabel'	      => __( 'Customer fields (at top)', 'phone-orders-for-woocommerce' ),
					'customerCustomFieldsAtTop'		      => $settings_option_handler->get_option( 'customer_custom_fields_at_top' ),
					'showOrderCustomFieldsInOrderEmailLabel'      => __( 'Show custom fields in order confirmation email', 'phone-orders-for-woocommerce' ),
					'showOrderCustomFieldsInOrderEmail'	      => $settings_option_handler->get_option( 'show_custom_fields_in_order_email' ),
					'orderCustomFieldsColumnsByLineLabel'         => __( 'Number of columns, per line', 'phone-orders-for-woocommerce' ),
					'orderCustomFieldsColumnsByLineNote'          => __( 'One number per line, if only one number - it applies to all lines', 'phone-orders-for-woocommerce' ),
					'orderCustomFieldsColumnsByLine'	      => $settings_option_handler->get_option( 'order_custom_fields_columns_by_line' ),
				),
				'redirectSettings' => array(
					'title'                                       => __( 'Checkout at frontend', 'phone-orders-for-woocommerce' ),
					'showGoToCartPageLabel'                       => __( 'Show \'Go to Cart\' button', 'phone-orders-for-woocommerce' ),
					'showGoToCheckoutPageLabel'                   => __( 'Show \'Go to Checkout\' button', 'phone-orders-for-woocommerce' ),
					'showCheckoutLinkButtonLabel'		      => __( 'Show \'Checkout link\' button', 'phone-orders-for-woocommerce' ),
					'showPaymentLinkButtonLabel'		      => __( 'Show \'Payment link\' button', 'phone-orders-for-woocommerce' ),
					'overrideProductPriceInCartLabel'             => __( 'Pass modified product prices to frontend cart', 'phone-orders-for-woocommerce' ),
					'showGoToCartButton'                          => $settings_option_handler->get_option( 'show_go_to_cart_button' ),
					'showGoToCheckoutButton'                      => $settings_option_handler->get_option( 'show_go_to_checkout_button' ),
					'showCheckoutLinkButton'		      => $settings_option_handler->get_option( 'show_checkout_link_button' ),
					'showPaymentLinkButton'			      => $settings_option_handler->get_option( 'show_payment_link_button' ),
					'overrideProductPriceInCart'                  => $settings_option_handler->get_option( 'override_product_price_in_cart' ),
				),
			),
		);

		$this->tab_data = array_merge( $this->tab_data, $tab_data_pro );
		?>
        <hr/>
        <pro-settings slot="pro-settings"
                      v-bind="<?php echo esc_attr( json_encode( $this->tab_data['pro'] ) ) ?>"></pro-settings>
		<?php
	}

	public function add_interface_settings() {

		$settings_option_handler = $this->option_handler;

		$pro_interface_settings = array(
			'hideTabsLabel'			=> __( 'Hide tabs for non-admins', 'phone-orders-for-woocommerce' ),
			'useEnglishInterfaceLabel'	=> __ ( 'Use interface in English', 'phone-orders-for-woocommerce' ),

			'hideTabs'			=> $settings_option_handler->get_option( 'hide_tabs' ),
			'useEnglishInterface'		=> $settings_option_handler->get_option( 'use_english_interface'),
		);
		?>

        <pro-interface-settings slot="pro-interface-settings"
                                v-bind="<?php echo esc_attr( json_encode( $pro_interface_settings ) ) ?>"></pro-interface-settings>
		<?php
	}

	public function add_woocommerce_settings() {

		$settings_option_handler = $this->option_handler;

		$pro_woocommerce_settings = array(
			'showEditOrderInWCLabel'	    => __( 'Show button "Edit" in orders list', 'phone-orders-for-woocommerce' ),
			'showCreatorInOrdersListLabel'	    => __( 'Show column "Order creator" in orders list', 'phone-orders-for-woocommerce' ),
			'hideAddOrderInOrdersListLabel'	    => __( 'Hide button "Add order" in orders list', 'phone-orders-for-woocommerce' ),
			'showPhoneOrdersInOrderPageLabel'	    => __( 'Show section "Phone Orders" inside order page', 'phone-orders-for-woocommerce' ),

			'showEditOrderInWC'		=> $settings_option_handler->get_option( 'show_edit_order_in_wc' ),
			'showCreatorInOrdersList'		=> $settings_option_handler->get_option( 'show_creator_in_orders_list' ),
			'hideAddOrderInOrdersList'		=> $settings_option_handler->get_option( 'hide_add_order_in_orders_list' ),
			'showPhoneOrdersInOrderPage'	=> $settings_option_handler->get_option( 'show_phone_orders_in_order_page' ),
			'showOrderTypeFilterInOrderPageLabel'	=> __( 'Show order type filter in orders list', 'phone-orders-for-woocommerce' ),
			'showOrderTypeFilterInOrderPage'	=> $settings_option_handler->get_option( 'show_order_type_filter_in_order_page' ),
			'overrideEmailPayOrderLinkLabel'              => __( 'Override link "Pay for this order" in the order email', 'phone-orders-for-woocommerce' ),
			'overrideEmailPayOrderLink'                   => $settings_option_handler->get_option( 'override_email_pay_order_link' ),
			'overrideCustomerPaymentLinkInOrderPageLabel' => __( 'Override "Customer payment page" in the order', 'phone-orders-for-woocommerce' ),
			'overrideEmailPayOrderLinkNote'               => __( 'This link will automatically login customer!', 'phone-orders-for-woocommerce' ),
			'overrideCustomerPaymentLinkInOrderPage'      => $settings_option_handler->get_option( 'override_customer_payment_link_in_order_page' ),
		);
		?>

        <pro-woocommerce-settings slot="pro-woocommerce-settings"
				  v-bind="<?php echo esc_attr( json_encode( $pro_woocommerce_settings ) ) ?>"></pro-woocommerce-settings>
		<?php
	}

	public function add_tax_settings() {

		$settings_option_handler = $this->option_handler;

		$pro_tax_settings = array(
			'hideTaxesIfTaxExemptLabel' => __( 'Hide taxes if tax exempt', 'phone-orders-for-woocommerce' ),
			'hideTaxesIfTaxExempt'      => $settings_option_handler->get_option( 'hide_taxes_if_tax_exempt' ),
			'hideTaxExempt'		    => $settings_option_handler->get_option( 'hide_tax_exempt' ),
			'hideTaxExemptLabel'	    => __( 'Hide checkbox "Tax exempt"',
						'phone-orders-for-woocommerce' ),
		);
		?>

        <pro-tax-settings slot="pro-tax-settings"
                                  v-bind="<?php echo esc_attr( json_encode( $pro_tax_settings ) ) ?>"></pro-tax-settings>
		<?php
	}

	public function add_layout_settings() {

		$settings_option_handler = $this->option_handler;

		$pro_layout_settings = array(
			'showDuplicateOrderLabel'	    => __( 'Show "duplicate order" button after order creation', 'phone-orders-for-woocommerce' ),
			'hideButtonPayAsCustomerLabel'	    => __( 'Hide button "Pay as customer"', 'phone-orders-for-woocommerce' ),
			'showButtonPayLabel'                => __( 'Show button "Pay"', 'phone-orders-for-woocommerce' ),
			'showButtonPayNote'                 => __( 'requires capability pay_for_order', 'phone-orders-for-woocommerce' ),
			'hideButtonViewOrderLabel'	    => __( 'Hide button "View Order"', 'phone-orders-for-woocommerce' ),
			'hideButtonCreateOrderLabel'	    => __( 'Hide button "Create order"', 'phone-orders-for-woocommerce' ),
			'hideButtonPutOnHoldLabel'	    => __( 'Hide button "Create Draft"', 'phone-orders-for-woocommerce' ),
			'hideButtonFullRefundLabel'	    => __( 'Hide button "Full Refund"', 'phone-orders-for-woocommerce' ),
			'hideButtonSendInvoiceLabel'	    => __( 'Hide button "Send Invoice"', 'phone-orders-for-woocommerce' ),
			'showViewInvoiceLabel'		    => __( 'Show button "View invoice"', 'phone-orders-for-woocommerce' ),
			'showViewInvoiceForOrdersLabel'     => __( 'for orders', 'phone-orders-for-woocommerce' ),
			'showViewInvoiceForDraftOrdersLabel'=> __( 'for draft orders', 'phone-orders-for-woocommerce' ),
			'showMarkAsPaidLabel'		    => __( 'Show button "Mark as paid"', 'phone-orders-for-woocommerce' ),
			'markAsPaidSetStatusLabel'	    => __( '"Mark as paid" set status to', 'phone-orders-for-woocommerce' ),
			'hidePrivateNoteLabel'		    => __ ( 'Hide field "Private note"', 'phone-orders-for-woocommerce' ),
			'hideAddGiftCardLabel'		    => __ ( 'Hide "Have a gift card?"', 'phone-orders-for-woocommerce' ),
			'showCartWeightLabel'		    => __ ( 'Show cart weight', 'phone-orders-for-woocommerce' ),

			'showDuplicateOrder'		=> $settings_option_handler->get_option( 'show_duplicate_order_button' ),
			'hideButtonPayAsCustomer'	=> $settings_option_handler->get_option( 'hide_button_pay_as_customer' ),
			'showButtonPay'                 => $settings_option_handler->get_option( 'show_button_pay' ),
			'hideButtonViewOrder'	        => $settings_option_handler->get_option( 'hide_button_view_order' ),
			'hideButtonCreateOrder'		=> $settings_option_handler->get_option( 'hide_button_create_order' ),
			'hideButtonPutOnHold'		=> $settings_option_handler->get_option( 'hide_button_put_on_hold' ),
			'hideButtonFullRefund'		=> $settings_option_handler->get_option( 'hide_button_full_refund' ),
			'hideButtonSendInvoice'		=> $settings_option_handler->get_option( 'hide_button_send_invoice' ),
			'showViewInvoice'		=> $settings_option_handler->get_option( 'show_view_invoice' ),
			'showViewInvoiceDraftOrders'    => $settings_option_handler->get_option( 'show_view_invoice_draft_orders' ),
			'showMarkAsPaid'		=> $settings_option_handler->get_option( 'show_mark_as_paid' ),
			'markAsPaidStatus'		=> $settings_option_handler->get_option( 'mark_as_paid_status'),
			'orderStatusesList'		=> $this->make_order_statuses_list(),
			'hidePrivateNote'		=> $settings_option_handler->get_option( 'hide_private_note'),
			'hideAddGiftCard'		=> $settings_option_handler->get_option( 'hide_add_gift_card'),
			'showCartWeight'		=> $settings_option_handler->get_option( 'show_cart_weight'),
		);
		?>

        <pro-layout-settings slot="pro-layout-settings"
                                  v-bind="<?php echo esc_attr( json_encode( $pro_layout_settings ) ) ?>"></pro-layout-settings>
		<?php
	}

	public function add_shipping_settings() {

		$settings_option_handler = $this->option_handler;

		$pro_shipping_settings = array(
            'selectOptimalShippingLabel' => __('Select optimal shipping method on each cart update', 'phone-orders-for-woocommerce'),
            'selectOptimalShipping' => $settings_option_handler->get_option('select_optimal_shipping'),
			'hideAddShippingLabel' => __( 'Hide shipping section', 'phone-orders-for-woocommerce' ),
			'hideAddShipping'      => $settings_option_handler->get_option( 'hide_add_shipping' ),
                        'hideShippingSection'  => $settings_option_handler->get_option( 'hide_shipping_section' ),
                        'hideShippingSectionLabel' => __( 'Hide checkbox "Ship to a different address"',
						'phone-orders-for-woocommerce' ),
			'usesEmptyAddressShipDifferentLabel'	=> __( '"Ship to a different address" uses empty address', 'phone-orders-for-woocommerce' ),
			'openPopupShipDifferentAddressLabel'	=> __( '"Ship to a different address" opens popup', 'phone-orders-for-woocommerce' ),
			'usesEmptyAddressShipDifferent'		=> $settings_option_handler->get_option( 'uses_empty_address_ship_different' ),
			'openPopupShipDifferentAddress'		=> $settings_option_handler->get_option( 'open_popup_ship_different_address' ),
		);
		?>

        <pro-shipping-settings slot="pro-shipping-settings"
                                v-bind="<?php echo esc_attr( json_encode( $pro_shipping_settings ) ) ?>"></pro-shipping-settings>
		<?php
	}

	public function add_coupons_settings() {

		$settings_option_handler = $this->option_handler;

		$pro_coupons_settings = array(
			'hideCouponWarningLabel'	    => __( 'Hide warning about disabled coupons', 'phone-orders-for-woocommerce' ),
			'hideCouponWarning'		    => $settings_option_handler->get_option( 'hide_coupon_warning' ),
			'hideAddCouponLabel'		    => __( 'Hide "Add coupon"', 'phone-orders-for-woocommerce' ),
			'hideAddCoupon'			    => $settings_option_handler->get_option( 'hide_add_coupon' ),
			'showAllCouponsInAutocompleteLabel' => __( 'Show all coupons in autocomplete', 'phone-orders-for-woocommerce' ),
			'showAllCouponsInAutocomplete'	    => $settings_option_handler->get_option( 'show_all_coupons_in_autocomplete' ),
		);
		?>

        <pro-coupons-settings slot="pro-coupons-settings"
                             v-bind="<?php echo esc_attr( json_encode( $pro_coupons_settings ) ) ?>"></pro-coupons-settings>
		<?php
	}

	public function add_cart_items_settings() {

		$settings_option_handler = $this->option_handler;

		$item_ids = $settings_option_handler->get_option( 'item_default_selected' );

		$item_default_selected = array();

		if ( is_array( $item_ids ) ) {

			foreach ( $item_ids as $iid ) {

				$item = wc_get_product( $iid );

				if ( ! $item ) {
					continue;
				}

				$title = $this->format_row_product( $item );

				$item_default_selected[] = array(
					'title' => $title,
					'value' => $iid,
				);
			}
		}

		$pro_cart_items_settings = array(
		    'productsAddProductToTopOfTheCartLabel'	=> __('Add product to top of the cart', 'phone-orders-for-woocommerce'),
		    'noOptionsTitle'				=> __('List is empty.', 'phone-orders-for-woocommerce'),
		    'productsItemPricePrecisionLabel'		=> __('Item price precision', 'phone-orders-for-woocommerce'),
		    'dontRefreshCartItemItemMetaLabel'          => __("Don't refresh cart when item meta edited", 'phone-orders-for-woocommerce'),
		    'disableEditMetaLabel'			=> __('Disable edit meta', 'phone-orders-for-woocommerce'),
		    'hideItemMetaLabel'				=> __('Hide item meta', 'phone-orders-for-woocommerce'),
			'isReadonlyPriceLabel'			=> __('Item price is read-only', 'phone-orders-for-woocommerce'),
			'allowToRenameCartItemsLabel'	=> __('Allow to rename cart items', 'phone-orders-for-woocommerce'),
			'allowToRenameCartItems'		=> $settings_option_handler->get_option('allow_to_rename_cart_items'),
		    'addProductToTopOfTheCart'			=> $settings_option_handler->get_option('add_product_to_top_of_the_cart'),
		    'itemPricePrecision'			=> (int) $settings_option_handler->get_option('item_price_precision'),
		    'dontRefreshCartItemItemMeta'               => $settings_option_handler->get_option('dont_refresh_cart_item_item_meta'),
		    'disableEditMeta'				=> $settings_option_handler->get_option('disable_edit_meta'),
		    'hideItemMeta'				=> $settings_option_handler->get_option('hide_item_meta'),
		    'isReadonlyPrice'				=> $settings_option_handler->get_option('is_readonly_price'),
		    'productsDefaultSelectedLabel'		=> __('Add products by default', 'phone-orders-for-woocommerce'),
		    'itemDefaultSelected'			=> $item_default_selected,
		    'noResultLabel'				=> __("Oops! No elements found. Consider changing the search query.", 'phone-orders-for-woocommerce'),
		    'itemDefaultSelectedPlaceholder'		=> __("Select items", 'phone-orders-for-woocommerce'),
		    'tabName'					=> 'settings',
		    'multiSelectSearchDelay'			=> $this->multiselect_search_delay,
		    'actionClickOnTitleProductItemInCartLabel'		    => __('Click on title - action, in cart', 'phone-orders-for-woocommerce'),
		    'actionClickOnTitleProductItemInCartEditProductLabel'   => __('Edit product', 'phone-orders-for-woocommerce'),
		    'actionClickOnTitleProductItemInCartViewProductLabel'   => __('View product', 'phone-orders-for-woocommerce'),
		    'actionClickOnTitleProductItemInCart'		    => $settings_option_handler->get_option('action_click_on_title_product_item_in_cart'),
		    'showAdditionalProductColumnLabel'		=> __( 'Show additional product column', 'phone-orders-for-woocommerce' ),
		    'additionalProductColumnTitleLabel'		=> __( 'Additional product column title', 'phone-orders-for-woocommerce' ),
		    'additionalProductColumnHelpLinkLabel'	=> __( 'How to use this option', 'phone-orders-for-woocommerce' ),
		    'showDiscountAmountInOrderLabel'            => __( 'Show discount amount in the order', 'phone-orders-for-woocommerce' ),
		    'showProductDescriptionLabel'               => __( 'Show product description', 'phone-orders-for-woocommerce' ),
		    'showProductDescriptionPreviewSizeLabel'    => __( 'preview size', 'phone-orders-for-woocommerce' ),
		    'showAdditionalProductColumn'		=> $settings_option_handler->get_option( 'show_additional_product_column' ),
		    'additionalProductColumnTitle'		=> $settings_option_handler->get_option( 'additional_product_column_title' ),
		    'showDiscountAmountInOrder'                 => $settings_option_handler->get_option( 'show_discount_amount_in_order' ),
		    'showProductDescription'                    => $settings_option_handler->get_option( 'show_product_description' ),
		    'showProductDescriptionPreviewSize'         => $settings_option_handler->get_option( 'show_product_description_preview_size' ),
		);
	?>

        <pro-cart-items-settings slot="pro-cart-items-settings"
                             v-bind="<?php echo esc_attr( json_encode( $pro_cart_items_settings ) ) ?>"></pro-cart-items-settings>
		<?php
	}

	private function make_product_visibility_options() {
		$product_visibility_options = array();
		foreach ( wc_get_product_visibility_options() as $name => $label ) {
			$product_visibility_options[] = array(
				'name'  => $name,
				'label' => $label,
			);
		}

		return $product_visibility_options;
	}

	public function enqueue_scripts() {
		parent::enqueue_scripts();
	}
}

<template>
    <tr>
        <td colspan=2>
            <table class="form-table">
                <tbody>
                    <tr>
                        <td colspan=2>
                            <b>{{ title }}</b>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            {{ cacheCustomerTimeoutLabel }}
                        </td>
                        <td>
                            <input type="hidden" name="cache_customers_session_key" v-model="sessionKey">
                            <input type="hidden" name="cache_customers_reset" id="cache_customers_reset" v-model.number="cacheCustomersReset">
                            <input type="number" class="option_hours" v-model.number="timeout" id="cache_customers_timeout" name="cache_customers_timeout" min=0>
                            {{ hoursLabel }}
                            <span v-if="timeout">
                                <button id="cache_customers_disable_button" @click="disableCache" class="btn btn-primary">
                                    {{ cacheCustomersDisableButton }}
                                </button>
                                <button id="cache_customers_reset_button" @click="resetCache" class="btn btn-danger">
                                    {{ cacheCustomersResetButton }}
                                </button>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            {{ searchCustomerInOrdersLabel }}
                        </td>
                        <td>
                            <input type="checkbox" class="option" v-model="searchInOrders" name="search_customer_in_orders">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            {{ limitOrdersOfSearchCustomerLabel }}
                        </td>
                        <td>
                            <input type="number" class="option_number" v-model.number="elLimitOrdersOfSearchCustomer" name="limit_orders_of_search_customer" min=0>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            {{ searchAllCustomerLabel }}
                        </td>
                        <td>
                            <input type="checkbox" class="option" v-model="searchAllFields" name="search_all_customer_fields">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            {{ numberOfCustomersToShowLabel }}
                        </td>
                        <td>
                            <input type="number" class="option_number" v-model.number="numberOfCustomers" name="number_of_customers_to_show" min=0>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            {{ defaultCustomerLabel }}
                        </td>
                        <td>
                            <multiselect
                                style="width: 100%;max-width: 800px;"
                                label="title"
                                v-model="selectedCustomer"
                                :options="defaultCustomersList"
                                track-by="value"
                                id="ajax"
                                :placeholder="selectDefaultCustomerPlaceholder"
                                :loading="isLoading"
                                :internal-search="false"
                                :show-no-results="true"
                                @search-change="asyncFind"
                                :hide-selected="false"
                                :searchable="true"
                                open-direction="bottom"
                                :show-labels="false"
                            >
                                <template slot="clear" slot-scope="props">
                                    <div class="multiselect__clear" v-show="defaultCustomerID && !props.isOpen" @mousedown.prevent.stop="clearAll(props.search)"></div>
                                </template>
                                <span slot="noResult">{{ noResultLabel }}</span>
                                <template slot="singleLabel" slot-scope="props">
                                    <span v-html="props.option.title"></span>
                                </template>
                                <template slot="option" slot-scope="props">
                                    <span v-html="props.option.title"></span>
                                </template>
                                <template slot="noOptions">
                                    <span v-html="noOptionsTitle"></span>
                                </template>
                          </multiselect>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            {{ updateCustomersProfileAfterCreateOrderLabel }}
                        </td>
                        <td>
                            <input type="checkbox" class="option" v-model="updateCustomersProfile" name="update_customers_profile_after_create_order">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            {{ updateWPUserFirstLastNameLabel }}
                        </td>
                        <td>
                            <input type="checkbox" class="option" v-model="elUpdateWPUserFirstLastName" name="update_wp_user_first_last_name">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            {{ usePaymentDeliveryLastOrderLabel }}
                        </td>
                        <td>
                            <input type="checkbox" class="option" v-model="elUsePaymentDeliveryLastOrder" name="use_payment_delivery_last_order">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            {{ doNotSubmitOnEnterLastFieldLabel }}
                        </td>
                        <td>
                            <input type="checkbox" class="option" v-model="elDoNotSubmitOnEnterLastField" name="do_not_submit_on_enter_last_field">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            {{ showRoleFieldLabel }}
                        </td>
                        <td>
                            <input type="checkbox" class="option" v-model="elShowRoleField" name="customer_show_role_field">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            {{ showLanguageFieldLabel }}
                        </td>
                        <td>
                            <input type="checkbox" class="option" v-model="elShowLanguageField" name="customer_show_language_field">
                        </td>
                    </tr>
		    <tr>
                        <td>
                            {{ hideFieldsLabel }}
                        </td>
                        <td>
			    <label class="option_checkbox_label" v-for="availableHideField in availableHideFieldsList">
				<input type="checkbox" class="option_checkbox" v-model="elHideFieldsList" :value="availableHideField.key">{{ availableHideField.label }} &nbsp;
			    </label>
                        </td>
                    </tr>
		    <tr>
                        <td>
                            {{ requiredFieldsLabel }}
                        </td>
                        <td>
			    <label class="option_checkbox_label" v-for="availableRequiredField in availableRequiredFieldsList">
				<input type="checkbox" class="option_checkbox" v-model="elRequiredFieldsList" :value="availableRequiredField.key">{{ availableRequiredField.label }} &nbsp;
			    </label>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            {{ allowMultipleAddressesLabel }}
                        </td>
                        <td>
                            <input type="checkbox" class="option" v-model="elAllowMultipleAddresses" name="allow_multiple_addresses">
                            {{ compatibleAddressBookNotice }} <a href="https://codecanyon.net/item/woocommerce-multiple-customer-addresses/16127030" target=_blank>{{ compatibleAddressBookPluginName }}</a>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            {{ useShippingPhoneLabel }}
                        </td>
                        <td>
                            <input type="checkbox" class="option" v-model="elUseShippingPhone" name="use_shipping_phone">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            {{ supportFieldVATLabel }}
                        </td>
                        <td>
                            <input type="checkbox" class="option" v-model="elSupportFieldVAT" name="support_field_vat">
                        </td>
                    </tr>
		    <tr>
			<td>
			    {{ showOrderHistoryCustomerLabel }}
			</td>
			<td>
			    <input type="checkbox" class="option" v-model="elShowOrderHistoryCustomer" name="show_order_history_customer">
			</td>
		    </tr>
                </tbody>
            </table>
        </td>
    </tr>
</template>

<script>

    import Multiselect from 'vue-multiselect';

    export default {
        created () {
            this.$root.bus.$on('settings-saved', this.onSettingsSaved);
        },
        props: {
            title: {
                default: function() {
                    return 'Customers';
                },
            },
            hoursLabel: {
                default: function() {
                    return 'hours';
                },
            },
            cacheCustomerTimeoutLabel: {
                default: function() {
                    return 'Caching search results';
                },
            },
            cacheCustomersSessionKey: {
                default: function() {
                    return '';
                },
            },
            cacheCustomersTimeout: {
                default: function() {
                    return 0;
                },
            },
            cacheCustomersDisableButton: {
                default: function() {
                    return 'Disable cache';
                },
            },
            cacheCustomersResetButton: {
                default: function() {
                    return 'Reset cache';
                },
            },
            cacheCustomersResetButton: {
                default: function() {
                    return 'Reset cache';
                },
            },
            searchAllCustomerFields: {
                default: function() {
                    return false;
                },
            },
	    searchCustomerInOrders: {
		default: function() {
			return false;
		},
	    },
            searchAllCustomerLabel: {
                default: function() {
                    return 'Customer search by shipping/billing fields';
                },
            },
	    searchCustomerInOrdersLabel: {
                default: function() {
                    return 'Search for customer in orders';
                },
            },
            numberOfCustomersToShowLabel: {
                default: function() {
                    return 'Number of customers to show in autocomplete';
                },
            },
            numberOfCustomersToShow: {
                default: function() {
                    return 0;
                },
            },
            defaultCustomerLabel: {
                default: function() {
                    return 'Default customer';
                },
            },
            defaultCustomerObject: {
                default: function() {
                    return {};
                },
            },
            updateCustomersProfileAfterCreateOrderLabel: {
                default: function() {
                    return "Automatically update customer's profile on order creation";
                },
            },
            updateCustomersProfileAfterCreateOrder: {
                default: function() {
                    return false;
                },
            },
            selectDefaultCustomerPlaceholder: {
                default: function() {
                    return 'Type to search';
                },
            },
	    doNotSubmitOnEnterLastFieldLabel: {
		    default: function() {
			    return "Don't close customer/address form automatically";
		    },
	    },
	    doNotSubmitOnEnterLastField: {
		    default: function() {
			    return false;
		    },
	    },
	    allowMultipleAddressesLabel: {
		    default: function() {
			    return 'Show address book';
		    },
	    },
	    compatibleAddressBookNotice: {
		    default: function() {
			    return "It's compatible with";
		    },
	    },
	    compatibleAddressBookPluginName: {
		    default: function() {
			    return 'WooCommerce Multiple Customer Addresses';
		    },
	    },
	    allowMultipleAddresses: {
		    default: function() {
			    return false;
		    },
	    },
	    useShippingPhoneLabel: {
		    default: function() {
			    return 'Show field "Shipping Phone"';
		    },
	    },
	    useShippingPhone: {
		    default: function() {
			    return false;
		    },
	    },
            noResultLabel: {
                default: function() {
                    return 'Oops! No elements found. Consider changing the search query.';
                },
            },
            multiSelectSearchDelay: {
                default: function() {
                    return 1000;
                }
            },
            noOptionsTitle: {
                default: function() {
                    return 'List is empty.';
                }
            },
            supportFieldVATLabel: {
                default: function() {
                    return 'Show field "VAT Number"';
                }
            },
            supportFieldVAT: {
                default: function() {
                    return false;
                }
            },
            updateWPUserFirstLastNameLabel: {
                default: function() {
                    return "Update user's first and last name when updating billing information";
                }
            },
            updateWPUserFirstLastName: {
                default: function() {
                    return false;
                }
            },
	    showOrderHistoryCustomerLabel: {
                default: function() {
                    return 'Show order history for the customer';
                },
            },
            showOrderHistoryCustomer: {
                default: function() {
                    return false;
                },
            },
            usePaymentDeliveryLastOrderLabel: {
                default: function() {
                    return 'Use payment and delivery preferences from last order';
                }
            },
            usePaymentDeliveryLastOrder: {
                default: function() {
                    return false;
                }
            },
            limitOrdersOfSearchCustomerLabel: {
                default: function() {
                    return 'Search for customers in last X orders, 0 - unlimited';
                }
            },
            limitOrdersOfSearchCustomer: {
                default: function() {
                    return 0;
                }
            },
	    hideFieldsLabel: {
                default: function() {
                    return 'Hide fields';
                }
            },
            availableHideFieldsList: {
                default: function() {
                    return [];
                }
            },
            hideFieldsList: {
                default: function() {
                    return [];
                }
            },
	    requiredFieldsLabel: {
                default: function() {
                    return 'Required fields';
                }
            },
            availableRequiredFieldsList: {
                default: function() {
                    return [];
                }
            },
            requiredFieldsList: {
                default: function() {
                    return [];
                }
            },
            showRoleFieldLabel: {
                default: function() {
                    return 'Show Role field';
                }
            },
            showRoleField: {
                default: function() {
                    return false;
                }
            },
            showLanguageFieldLabel: {
                default: function() {
                    return 'Show Language field';
                }
            },
            showLanguageField: {
                default: function() {
                    return false;
                }
            },
        },
        watch: {
            selectedCustomer (newVal, oldVal) {
                this.defaultCustomerID = this.getKeyValueOfObject(newVal, 'value');
            },
        },
        data () {
            return {
                isLoading: false,
                defaultCustomersList: [],
                selectedCustomer: this.defaultCustomerObject,
                sessionKey: this.cacheCustomersSessionKey,
                cacheCustomersReset: 0,
                timeout: this.cacheCustomersTimeout,
		searchInOrders: this.searchCustomerInOrders,
                searchAllFields: this.searchAllCustomerFields,
                numberOfCustomers: this.numberOfCustomersToShow,
                defaultCustomerID: this.getKeyValueOfObject(this.defaultCustomerObject, 'value'),
                updateCustomersProfile: this.updateCustomersProfileAfterCreateOrder,
		elDoNotSubmitOnEnterLastField: this.doNotSubmitOnEnterLastField,
		elAllowMultipleAddresses: this.allowMultipleAddresses,
		elUseShippingPhone: this.useShippingPhone,
		elSupportFieldVAT: this.supportFieldVAT,
		elUpdateWPUserFirstLastName: this.updateWPUserFirstLastName,
		elShowOrderHistoryCustomer: this.showOrderHistoryCustomer,
		elUsePaymentDeliveryLastOrder: this.usePaymentDeliveryLastOrder,
		elLimitOrdersOfSearchCustomer: this.limitOrdersOfSearchCustomer,
		elHideFieldsList: this.hideFieldsList,
		elRequiredFieldsList: this.requiredFieldsList,
		elShowRoleField: this.showRoleField,
		elShowLanguageField: this.showLanguageField,
                lastRequestTimeoutID: null,
            };
        },
        methods: {
            disableCache () {
                this.timeout = 0;
                this.saveSettingsByEvent();
            },
            resetCache () {
                this.cacheCustomersReset = 1;
                this.saveSettingsByEvent();
            },
            getSettings () {
                return {
                    cache_customers_session_key: this.sessionKey,
                    cache_customers_reset: this.cacheCustomersReset,
                    cache_customers_timeout: this.timeout,
		    search_customer_in_orders: this.searchInOrders,
                    search_all_customer_fields: this.searchAllFields,
                    number_of_customers_to_show: this.numberOfCustomers,
                    default_customer_id: this.defaultCustomerID,
                    update_customers_profile_after_create_order: this.updateCustomersProfile,
		    do_not_submit_on_enter_last_field: this.elDoNotSubmitOnEnterLastField,
		    allow_multiple_addresses: this.elAllowMultipleAddresses,
		    use_shipping_phone: this.elUseShippingPhone,
		    support_field_vat: this.elSupportFieldVAT,
		    update_wp_user_first_last_name: this.elUpdateWPUserFirstLastName,
		    show_order_history_customer: this.elShowOrderHistoryCustomer,
		    use_payment_delivery_last_order: this.elUsePaymentDeliveryLastOrder,
		    limit_orders_of_search_customer: this.elLimitOrdersOfSearchCustomer,
		    customer_hide_fields: this.elHideFieldsList,
		    customer_required_fields: this.elRequiredFieldsList,
		    customer_show_role_field: this.elShowRoleField,
		    customer_show_language_field: this.elShowLanguageField,
                };
            },
            asyncFind(query) {
                this.lastRequestTimeoutID && clearTimeout(this.lastRequestTimeoutID);

                if (!query && query !== null) {
                    this.isLoading = false;
                    this.lastRequestTimeoutID = null;
                    return;
                }

                this.isLoading = true;

                this.lastRequestTimeoutID = setTimeout(() => {
                    this.axios.get(this.url, {
                        params: {
                            action: 'woocommerce_json_search_customers',
                            security: this.search_customers_nonce,
                            term: query,
                        }
                    }).then((response) => {

                        var customers = [];

                        for (var id in response.data) {
                            if (response.data.hasOwnProperty(id)) {
                                customers.push({title: response.data[id], value: id});
                            }
                        }

                        this.defaultCustomersList = customers;

                        this.isLoading = false;
                    });
                }, this.multiSelectSearchDelay);
            },
            clearAll () {
                this.selectedCustomer = null;
            },
            onSettingsSaved (settings) {
                this.sessionKey          = settings.cache_customers_session_key;
                this.cacheCustomersReset = settings.cache_customers_reset;
            },
        },
        components: {
            Multiselect,
        },
    }
</script>

<template>
    <div>
        <run-at-frontend-settings ref="runAtFrontendSettings" v-bind="runAtFrontendSettings" v-show="activatedTabKey === 'runAtFrontendSettings'"></run-at-frontend-settings>
        <customer-settings ref="customerSettings" v-bind="customerSettings" v-show="activatedTabKey === 'customerSettings'"></customer-settings>
        <new-customer-popup-settings ref="newCustomerPopupSettings" v-bind="newCustomerPopupSettings" v-show="activatedTabKey === 'newCustomerPopupSettings'"></new-customer-popup-settings>
        <add-item-popup-settings ref="addItemPopupSettings" v-bind="addItemPopupSettings" v-show="activatedTabKey === 'addItemPopupSettings'"></add-item-popup-settings>
        <products-settings ref="productsSettings" v-bind="productsSettings" v-show="activatedTabKey === 'productsSettings'"></products-settings>
        <fee-settings ref="feeSettings" v-bind="feeSettings" v-show="activatedTabKey === 'feeSettings'"></fee-settings>
        <discount-settings ref="discountSettings" v-bind="discountSettings" v-show="activatedTabKey === 'discountSettings'"></discount-settings>
        <copy-orders-settings ref="copyOrdersSettings" v-bind="copyOrdersSettings" v-show="activatedTabKey === 'copyOrdersSettings'"></copy-orders-settings>
        <misc-settings ref="miscSettings" v-bind="miscSettings" v-show="activatedTabKey === 'miscSettings'"></misc-settings>
        <redirect-settings ref="redirectSettings" v-bind="redirectSettings" v-show="activatedTabKey === 'redirectSettings'"></redirect-settings>
    </div>
</template>

<script>

    import CustomerSettings from './customer_settings.vue';
    import NewCustomerPopupSettings from './new_customer_popup_settings.vue';
    import AddItemPopupSettings from './add_item_popup_settings.vue';
    import ProductsSettings from './products_settings.vue';
    import FeeSettings from './fee_settings.vue';
    import DiscountSettings from './discount_settings.vue';
    import ShippingSettings  from './shipping_settings.vue';
    import CopyOrdersSettings from './copy_orders_settings.vue';
    import MiscSettings  from './misc_settings.vue';
    import redirectSettings  from './redirect_settings.vue';
    import RunAtFrontendSettings  from './run_at_frontend_settings.vue';

    export default {
        props: {
            customerSettings: {
                default: function() {
                    return {};
                }
            },
            newCustomerPopupSettings: {
                default: function() {
                    return {};
                }
            },
            productsSettings: {
                default: function() {
                    return {};
                }
            },
            addItemPopupSettings: {
                default: function() {
                    return {};
                }
            },
            feeSettings: {
                default: function() {
                    return {};
                }
            },
            discountSettings: {
                default: function() {
                    return {};
                }
            },
            shippingSettings: {
                default: function() {
                    return {};
                }
            },
            copyOrdersSettings: {
                default: function() {
                    return {};
                }
            },
            miscSettings: {
                default: function() {
                    return {};
                }
            },
	    redirectSettings: {
		    default: function() {
			    return {};
		    }
	    },
	    runAtFrontendSettings: {
		    default: function() {
			    return {};
		    }
	    },
        },
	    data: function () {
		    return {
			    activatedTabKey: '',
		    };
	    },
        components: {
            CustomerSettings,
            NewCustomerPopupSettings,
	        AddItemPopupSettings,
            ProductsSettings,
            FeeSettings,
            DiscountSettings,
            CopyOrdersSettings,
            MiscSettings,
            redirectSettings,
            RunAtFrontendSettings,
        },
        methods: {
            getSettings: function () {

                var settings = {};

                this.$children.forEach(function (child) {
                    settings = Object.assign(settings, child.getSettings());
                });

                return settings;
            },
	        getTabsHeaders: function () {
		        let headers = [
                    'copyOrdersSettings',
                    'addItemPopupSettings',
                    'productsSettings',
                    'newCustomerPopupSettings',
		    'customerSettings',
                    'miscSettings',
                    'discountSettings',
                    'feeSettings',
                    'redirectSettings',
                    'runAtFrontendSettings',
                ];

		        let refs = this.$refs;

		        return headers.map( function ( key ) {
			        return {
				        'key': key,
				        'title': refs[key].title,
			        }
		        } );
	        },
	        showOption: function (key) {
		        this.activatedTabKey = key
	        },
        },
    }
</script>
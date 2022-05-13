<template>
    <span>
        <button class="btn btn-primary" @click="putOnDraft" v-show="showPutOnHoldButton">
            {{ draftButtonLabel }}
        </button>
        <button class="btn btn-primary redirect" @click="goToCartPage" id="go-to-cart-page-button"
                v-show="showGoToCartPageButton">
            {{ goToCartPageLabel }}
        </button>
        <button class="btn btn-primary redirect" @click="goToCheckoutPage" id="go-to-checkout-page-button"
                v-show="showGoToCheckoutPageButton">
            {{ goToCheckoutPageLabel }}
        </button>
        <button class="btn btn-primary redirect" id="checkout-link-button"
                @click="isDisabledCheckoutLink ? null : getCheckoutLink()"
                v-show="showCheckoutLinkButton"
                :class="{disabled: isDisabledCheckoutLink}"
                :title="isDisabledCheckoutLink ? disabledCheckoutLinkTitle : ''">
            {{ isCopiedCheckoutLink ? checkoutLinkCopiedButtonLabel : checkoutLinkButtonLabel }}
        </button>
        <button class="btn btn-primary" @click="updateOrder" id="update-order-button"
                v-show="showUpdateOrderButton">
            {{ updateOrderButtonLabel }}
        </button>
        <button class="btn btn-primary" @click="cancelUpdateOrder" id="cancel-update-button"
                v-show="showCancelUpdateOrderButton">
            {{ cancelUpdateOrderButtonLabel }}
        </button>
        <button class="btn btn-danger" @click="clearAll" id="clear-all-button"
                v-show="showClearAllButton">
            {{ clearAllButtonLabel }}
        </button>
        <button class="btn btn-primary" @click="payOrder" id="pay-order-button"
                v-show="showPayOrderButton && paymentUrl && showButtonPay"
                :title="payOrderAsCustomerTitle"
                :disabled="payOrderAsCustomerDisabled">
            {{ payButtonLabel }}
        </button>
        <button class="btn btn-primary" @click="payOrderAsCustomer" id="pay-order-as-customer-button"
                v-show="showPayOrderButton && paymentUrl && !hideButtonPayAsCustomerOption"
                :title="payOrderAsCustomerTitle"
                :disabled="payOrderAsCustomerDisabled">
            {{ payOrderButtonLabel }}
        </button>
        <button class="btn btn-primary" @click="markAsPaid" id="mark-as-paid-button"
                v-show="showMarkAsPaidOption" >
            {{ markAsPaidLabel }}
        </button>
    </span>
</template>

<script>
    export default {
        created: function () {
            this.$root.bus.$on('create-order', () => {
                this.draftToPending();
            });
        },
        props: {
        putOnHoldButtonLabel: {
            default: function() {
                return 'Create draft';
            }
        },
        updateDraftButtonLabel: {
            default: function() {
                return 'Update draft';
            }
        },
	    goToCartPageLabel: {
		    default: function() {
			    return 'Go to Cart';
		    }
	    },
	    goToCheckoutPageLabel: {
		    default: function() {
			    return 'Go to Checkout';
		    }
	    },
	    checkoutLinkButtonLabel: {
		    default: function() {
			    return 'Checkout link';
		    }
	    },
	    checkoutLinkCopiedButtonLabel: {
		    default: function() {
			    return 'Url has been copied to clipboard';
		    }
	    },
	    disabledCheckoutLinkTitle: {
		    default: function() {
			    return 'This button disabled for admins';
		    }
	    },
            updateOrderButtonLabel: {
                default: function() {
                    return 'Update order';
                }
            },
            cancelUpdateOrderButtonLabel: {
                default: function() {
                    return 'Cancel';
                }
            },
            clearAllButtonLabel: {
                default: function() {
                    return 'Clear all';
                }
            },
            payOrderButtonLabel: {
                default: function() {
                    return 'Pay order as the customer';
                }
            },
            payButtonLabel: {
                default: function() {
                    return 'Pay';
                }
            },
            markAsPaidLabel: {
                default: function() {
                    return 'Mark as paid';
                }
            },
            orderIsCompletedTitle: {
                default: function() {
                    return 'Order completed';
                }
            },
            tabName: {
                default: function() {
                    return 'add-order';
                }
            },
        },
	data() {
	    return {
		isCopiedCheckoutLink: false,
	    };
	},
        computed: {
            customer: function () {
                return this.$store.state.add_order.cart.customer;
            },
            showCreateOrderButton () {
                return !!! this.$store.state.add_order.cart.order_id
                    && !! this.$store.state.add_order.cart.items.length
                    && ! this.showUpdateOrderButton;
            },
            showPutOnHoldButton () {
                return this.showCreateOrderButton && !!!this.getSettingsOption('hide_button_put_on_hold');
            },
	    showGoToCartPageButton () {
		    return this.showCreateOrderButton && this.getSettingsOption('show_go_to_cart_button');
	    },
	    showGoToCheckoutPageButton () {
		    return this.showCreateOrderButton && this.getSettingsOption('show_go_to_checkout_button');
	    },
	    showCheckoutLinkButton () {
		    return this.showCreateOrderButton && this.getSettingsOption('show_checkout_link_button');
	    },
	    hideButtonPayAsCustomerOption () {
		    return this.getSettingsOption('hide_button_pay_as_customer');
	    },
        showMarkAsPaidOption () {
            return this.getSettingsOption('show_mark_as_paid')
                && !! this.$store.state.add_order.cart.order_id
                && !!! this.$store.state.add_order.cart.view_order_id;
        },
            showUpdateOrderButton () {
                return !! this.$store.state.add_order.cart.edit_order_id;
            },
            showCancelUpdateOrderButton () {
                return !! this.$store.state.add_order.cart.edit_order_id;
            },
            showClearAllButton () {
                return this.showCreateOrderButton
                    && ! this.$store.state.add_order.cart.edit_order_id;
            },
            showPayOrderButton () {
                return !! this.$store.state.add_order.cart.order_id && !! this.$store.state.add_order.cart.allow_refund_order;
            },
            payOrderAsCustomerTitle: function () {
                return this.payOrderAsCustomerDisabled ? this.orderIsCompletedTitle : '';
            },
            payOrderAsCustomerDisabled: function () {
                return !! this.$store.state.add_order.cart.order_is_completed;
            },
            paymentUrl: function () {
                return this.$store.state.add_order.cart.order_payment_url;
            },
            isDisabledCheckoutLink: function () {
                return this.customer && this.customer.disable_checkout_link;
            },
            draftedOrderID: function () {
                return this.$store.state.add_order.cart.drafted_order_id;
            },
            draftButtonLabel: function () {
                if (this.draftedOrderID) {
                    return this.updateDraftButtonLabel;
                } else {
                    return this.putOnHoldButtonLabel;
                }
            },
	    showButtonPay () {
		    return this.getSettingsOption('show_button_pay');
	    },
        },
        methods: {
            putOnDraft () {

                this.$store.commit('add_order/setIsLoading', true);

                this.axios.post(this.url, this.qs.stringify({
                    action: 'phone-orders-for-woocommerce',
                    method: 'put_on_draft',
                    cart: JSON.stringify(this.clearCartParam(this.$store.state.add_order.cart)),
                    tab: this.tabName,
                    log_row_id: this.$store.state.add_order.log_row_id,
	                created_date_time: this.$store.state.add_order.order_date_timestamp,
                })).then( ( response ) => {

		    if ( !!! response.data.success ) {
			this.$root.bus.$emit('show-error-message', response.data.data);
			this.$store.commit('add_order/setCartEnabled', true);
			this.$store.commit('add_order/setIsLoading', false);
			return;
		    }

                    this.$store.commit('add_order/setCartDraftedOrderID', response.data.data.drafted_order_id);
                    this.$store.commit('add_order/setCartOrderNumber', response.data.data.order_number);
                    this.$store.commit('add_order/setCartAllowRefundOrder', response.data.data.cart.allow_refund_order);
                    this.$store.commit('add_order/setButtonsMessage', response.data.data.message);
                    this.$store.commit('add_order/setIsLoading', false);
                });
            },
            prepareToGo (where, callback) {
                this.$root.bus.$emit('check-valid', where, () => {
                    this.$store.commit('add_order/setIsLoading', true);

                    this.axios.post(this.url, this.qs.stringify({
                        action: 'phone-orders-for-woocommerce',
                        method: 'prepare_to_redirect',
                        cart: JSON.stringify(this.clearCartParam(this.$store.state.add_order.cart)),
                    referrer: window.location.href,
                    is_frontend: typeof window.wpo_frontend === 'undefined' ? 0 : 1,
                    where: where,
                        tab: this.tabName,
                    })).then( ( response ) => {

                        this.$store.commit('add_order/setIsLoading', false);

                        if (typeof callback !== 'undefined') {
                            callback(response);
                            return;
                        }

                        if ( response.data.success ) {
                            this.$store.commit('add_order/enableUnconditionalRedirect', true);
                            window.open( response.data.data.url, "_self" );
                        }

                    });
                });
            },
	    goToCartPage () {
		this.prepareToGo('cart');
            },
	    goToCheckoutPage () {
		this.prepareToGo('checkout');
            },
	    getCheckoutLink () {
		this.prepareToGo('checkout_link', (response) => {
		    if ( response.data.success ) {
			this.$root.bus.$emit('show-copy-link', response.data.data.url);
		    }
		});
            },
            updateOrder () {

                this.$store.commit('add_order/setIsLoading', true);

                this.axios.post(this.url, this.qs.stringify({
                    action: 'phone-orders-for-woocommerce',
                    method: 'update_order',
                    order_id: this.$store.state.add_order.cart.edit_order_id || this.$store.state.add_order.cart.order_id,
                    cart: JSON.stringify(this.clearCartParam(this.$store.state.add_order.cart)),
                    tab: this.tabName,
	                created_date_time: this.$store.state.add_order.order_date_timestamp,
                    order_status: this.$store.state.add_order.order_status,
                })).then( ( response ) => {

		    if ( !!! response.data.success ) {
			this.$root.bus.$emit('show-error-message', response.data.data);
			this.$store.commit('add_order/setCartEnabled', true);
			this.$store.commit('add_order/setIsLoading', false);
			return;
		    }


                    this.$store.commit('add_order/setCartOrderID', this.$store.state.add_order.cart.edit_order_id);
                    this.$store.commit('add_order/setCartOrderNumber', this.$store.state.add_order.cart.edit_order_number);
                    this.$store.commit('add_order/setCartEditOrderID', null);
                    this.$store.commit('add_order/setCartEnabled', false);
                    this.$store.commit('add_order/setButtonsMessage', response.data.data.message);
                    this.$store.commit('add_order/setIsLoading', false);
                    this.updateStoredCartHash();
                });

                this.$root.bus.$emit('update-order');
            },
            cancelUpdateOrder () {
                this.$store.commit('add_order/setCartOrderID', this.$store.state.add_order.cart.edit_order_id);
                this.$store.commit('add_order/setCartOrderNumber', this.$store.state.add_order.cart.edit_order_number);
                this.$store.commit('add_order/setCartEditOrderID', null);
                this.$store.commit('add_order/setCartEditOrderNumber', null);
                this.$store.commit('add_order/setCartEnabled', false);
                this.$store.commit('add_order/setButtonsMessage', '');
                this.updateStoredCartHash();

                this.$root.bus.$emit('cancel-update-order');
            },
            clearAll () {
                this.$root.bus.$emit('clear-all');
            },
            payOrderAsCustomer () {
	            this.$store.commit('add_order/setIsLoading', true);

	            this.axios.post(this.url, this.qs.stringify({
		            action: 'phone-orders-for-woocommerce',
		            method: 'set_payment_cookie',
		            order_id: this.$store.state.add_order.cart.order_id,
			    referrer: window.location.href,
			    is_frontend: typeof window.wpo_frontend === 'undefined' ? 0 : 1,
		            tab: this.tabName,
	            })).then( ( response ) => {
	            	if ( response.data.success ) {
			            window.open( this.paymentUrl, "_self" );
                    } else {
			            this.$store.commit('add_order/setIsLoading', false);
		            }
	            });
            },
            markAsPaid () {
                this.$store.commit('add_order/setIsLoading', true);

                this.axios.post(this.url, this.qs.stringify({
                    action: 'phone-orders-for-woocommerce',
                    method: 'mark_as_paid',
                    order_id: this.$store.state.add_order.cart.edit_order_id || this.$store.state.add_order.cart.order_id,
                    tab: this.tabName,
                })).then( ( response ) => {
                    this.$root.bus.$emit('marked-as-paid', response.data.data);
                    this.$store.commit('add_order/setIsLoading', false);
                });


            },
            draftToPending () {

                if ( ! this.$store.state.add_order.cart.drafted_order_id ) {
                    return;
                }

                this.$store.commit('add_order/setIsLoading', true);

                this.axios.post(this.url, this.qs.stringify({
                    action: 'phone-orders-for-woocommerce',
                    method: 'move_from_draft',
                    drafted_order_id: this.$store.state.add_order.cart.drafted_order_id,
                    tab: this.tabName,
		    created_date_time: this.$store.state.add_order.order_date_timestamp,
		    order_status: this.$store.state.add_order.order_status,
		    cart: JSON.stringify(this.clearCartParam(this.$store.state.add_order.cart)),
                })).then( ( response ) => {

		    if ( !!! response.data.success ) {
			this.$root.bus.$emit('show-error-message', response.data.data);
			this.$store.commit('add_order/setCartEnabled', true);
			this.$store.commit('add_order/setIsLoading', false);
			return;
		    }

                    this.$store.commit('add_order/setCartOrderID', response.data.data.order_id);
                    this.$store.commit('add_order/setCartOrderNumber', response.data.data.order_number);
                    this.$store.commit('add_order/setCartDraftedOrderID', null);
                    this.$store.commit('add_order/setCartOrderPaymentUrl', response.data.data.payment_url);
		    this.$store.commit('add_order/setCartAllowRefundOrder', response.data.data.allow_refund_order);
                    this.$store.commit('add_order/setCartEnabled', false);
                    this.buttonsMessage = response.data.data.message;
                    this.$store.commit('add_order/setIsLoading', false);
                });

            },
            payOrder () {
                window.open( this.paymentUrl, "_self" );                    
            },
        },
    }
</script>
<template>
    <span>
        <button class="btn btn-primary redirect" @click="getPaymentLink" id="payment-link-button"
                v-show="showPaymentLinkButton && paymentLinkUrl"
                :title="paymentLinkButtonTitle"
                :disabled="paymentLinkButtonDisabled">
            {{ paymentLinkButtonLabel }}
        </button>
        <button class="btn btn-danger" @click="refundOrder" id="refund-order-button"
                v-show="showRefundOrderButton">
            {{ refundOrderButtonLabel }}
        </button>
    </span>
</template>

<script>
    export default {
        props: {
            tabName: {
                default: function() {
                    return 'add-order';
                }
            },
            refundOrderButtonLabel: {
                default: function() {
                    return 'Full refund';
                }
            },
            refundOrderNoticeMessage: {
                default: function() {
                    return 'Are you sure ?';
                }
            },
            paymentLinkButtonLabel: {
                default: function() {
                    return 'Payment link';
                }
            },
	    orderIsCompletedTitle: {
                default: function() {
                    return 'Order completed';
                }
            },
        },
        computed: {
            showRefundOrderButton: function () {
                return !!!this.getSettingsOption('hide_button_full_refund') && !! this.$store.state.add_order.cart.allow_refund_order;
            },
            currentOrderID: function () {
                return this.$store.state.add_order.cart.order_id || this.$store.state.add_order.cart.edit_order_id || this.$store.state.add_order.cart.drafted_order_id;
            },
	    showPaymentLinkButton () {
                return !! this.$store.state.add_order.cart.order_id && this.getSettingsOption('show_payment_link_button');
            },
	    paymentLinkButtonDisabled: function () {
                return !! this.$store.state.add_order.cart.order_is_completed;
            },
            paymentLinkButtonTitle: function () {
                return this.paymentLinkButtonDisabled ? this.orderIsCompletedTitle : '';
            },
	    paymentUrl: function () {
                return this.$store.state.add_order.cart.order_payment_url;
            },
	    orderID () {
                return this.$store.state.add_order.cart.order_id;
            },
	    paymentLinkUrl: function () {
                return this.paymentUrl ? this.paymentUrl + '&wpo_pay_order=' + this.orderID : this.paymentUrl;
            },
        },
        methods: {
            refundOrder () {

		if (!confirm(this.refundOrderNoticeMessage)) {
		    return;
		}

                this.$store.commit('add_order/setIsLoading', true);

                this.axios.post(this.url, this.qs.stringify({
                    action: 'phone-orders-for-woocommerce',
                    method: 'full_refund_order',
                    order_id: this.currentOrderID,
                    tab: this.tabName,
                })).then( ( response ) => {
		    this.$store.commit('add_order/setCartOrderID', response.data.data.order_id);
		    this.$store.commit('add_order/setCartEditOrderID', null);
		    this.$store.commit('add_order/setCartDraftedOrderID', null);
                    this.$store.commit('add_order/setCartOrderNumber', response.data.data.order_number);
                    this.$store.commit('add_order/updateOrderStatus', response.data.data.order_status);
                    this.$store.commit('add_order/setCartAllowRefundOrder', response.data.data.allow_refund_order);
                    this.$store.commit('add_order/setCartEnabled', false);
		    this.$store.commit('add_order/setButtonsMessage', response.data.data.message);
                    this.$store.commit('add_order/setIsLoading', false);
                });
            },
	    getPaymentLink () {
		this.$root.bus.$emit('show-copy-link', this.paymentLinkUrl);
            },
        },
    }
</script>
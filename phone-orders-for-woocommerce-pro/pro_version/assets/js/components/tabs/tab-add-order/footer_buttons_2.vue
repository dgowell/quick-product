<template>
    <span>
        <button class="btn btn-primary" @click="editCreatedOrder" id="edit-created-order-button"
                v-show="showEditCreatedOrderButton"
                :disabled="isDisabled"
                :title="buttonTitle">
            {{ editCreatedOrderButtonLabel }}
        </button>
    </span>
</template>

<script>
    export default {
        props: {
            editCreatedOrderButtonLabel: {
                default: function() {
                    return 'Edit created order';
                }
            },
            orderIsCompletedTitle: {
                default: function() {
                    return 'Order completed';
                }
            },
        },
        computed: {
            orderID () {
                return this.$store.state.add_order.cart.order_id;
            },
            orderNumber () {
                return this.$store.state.add_order.cart.order_number;
            },
            buttonTitle () {
                return this.isDisabled ? this.orderIsCompletedTitle : '';
            },
            isDisabled () {
                return !! this.$store.state.add_order.cart.order_is_completed;
            },
            showEditCreatedOrderButton () {
                return !! this.orderID && !!this.$store.state.add_order.cart.allow_refund_order;
            },
        },
        data: function () {
            return {};
        },
        methods: {
            editCreatedOrder () {
                this.$store.commit('add_order/setCartEditOrderID', this.orderID);
                this.$store.commit('add_order/setCartEditOrderNumber', this.orderNumber);
                this.$store.commit('add_order/setCartOrderID', null);
                this.$store.commit('add_order/setCartEnabled', true);
                this.$store.commit('add_order/setButtonsMessage', '');
                this.updateStoredCartHash();
                this.$root.bus.$emit('edit-order');
            },
        },
    }
</script>
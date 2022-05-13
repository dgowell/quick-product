<template>
    <div class="wpo_after_order_items">
        <button class="btn btn-danger" v-show="isShowButton" @click="clearCart" :disabled="!cartEnabled">
            {{ buttonLabel }}
        </button>
    </div>
</template>

<script>
    export default {
        props: {
            buttonLabel: {
                default: function() {
                    return 'Clear cart';
                }
            },
        },
        computed: {
            isShowButton: function () {
                return !!this.$store.state.add_order.cart.items.length;
            },
        },
        methods: {
            clearCart () {
                this.$store.commit('add_order/clearCart');
                this.setDefaultCustomFieldsValues();
                this.$root.bus.$emit('clear-cart');
            },
        },
    }
</script>
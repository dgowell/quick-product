<template>
    <div class="wpo_after_customer_details" v-if="isShow">
        <button class="btn btn-primary save-to-customer-button" @click="saveToCustomer">
            {{ buttonTitle }}
            <span class="save-to-customer-button__loader" v-show="isRunRequest">
                <loader></loader>
            </span>
            <b-badge pill variant="success" v-show="statusRequest === true">Saved</b-badge>
            <b-badge pill variant="danger" v-show="statusRequest === false">Error</b-badge>
        </button>
    </div>
</template>

<script>

    var loader = require('vue-spinner/dist/vue-spinner.min').ClipLoader;

    export default {
        props: {
            buttonTitle: {
                default: function() {
                    return 'Save to customer';
                }
            },
            tabName: {
                default: function() {
                    return 'add-order';
                }
            },
        },
        data: function () {
            return {
                isShow: false,
                isRunRequest: false,
                statusRequest: null,
            };
        },
        watch: {
            customer: function(newCustomer, oldCustomer) {
	            if ( oldCustomer && newCustomer ) {
                    if ( !this.deepIsEqual(newCustomer, oldCustomer)) {
		            	let newCusIdNotEmpty = (typeof newCustomer.id !== 'undefined') && (newCustomer.id !== '0');
		            	let oldCusIdNotEmpty = (typeof oldCustomer.id !== 'undefined') && (oldCustomer.id !== '0');

			            this.isShow = newCusIdNotEmpty
			                          && oldCusIdNotEmpty
			                          && newCustomer.id === oldCustomer.id;
		            }
	            } else {
		            this.isShow = false;
	            }
            }
        },
        computed: {
            customer: function () {
                return this.$store.state.add_order.cart.customer;
            },
        },
        methods: {
            saveToCustomer: function () {

                this.isRunRequest = true;

                this.axios.post( this.url, this.qs.stringify({
                    tab: this.tabName,
                    _wpnonce: this.nonce,
                    action: 'phone-orders-for-woocommerce',
                    method: 'save_customer_data',
                    customer_data: this.customer,
                    cart: JSON.stringify(this.clearCartParam(this.$store.state.add_order.cart)),
                })).then( ( response ) => {
                    this.isRunRequest  = false;
                    this.statusRequest = true;

                    setTimeout(() => {
                        this.isShow        = false;
                        this.statusRequest = null;
                    }, 2000);

                    this.$root.bus.$emit('apply-recalculated-cart', response.data.data.cart);
                }, () => {
                    this.isRunRequest  = false;
                    this.statusRequest = false;
                });
            },
        },
        components: {
            loader,
        },
    }
</script>
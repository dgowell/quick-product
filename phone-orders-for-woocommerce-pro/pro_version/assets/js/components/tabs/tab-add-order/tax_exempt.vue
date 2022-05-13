<template>
    <p v-show="isShow">
        <label>
            <input type="checkbox" v-model="isVatExempt" v-bind:disabled="!cartEnabled">
            {{ title }}
        </label>
    </p>
</template>

<script>

    export default {
        props: {
            title: {
                default: function() {
                    return 'Tax exempt';
                }
            },
            tabName: {
                default: function() {
                    return 'add-order';
                }
            },
	        isTaxEnabled: {
		        default: function() {
			        return false;
		        }
	        },
        },
        data: function () {
            return {};
        },
        computed: {
	        isVatExemptStored: {
		        get: function () {
			        return this.$store.state.add_order.cart.customer ? this.$store.state.add_order.cart.customer.is_vat_exempt : null;
		        },
		        set: function ( newVal ) {
			        this.$store.commit( 'add_order/setCustomerTaxExempt', newVal );
		        },
	        },
	        isVatExempt: {
		        get: function () {
			        return this.isVatExemptStored;
		        },
		        set: function ( newVal, oldVal ) {
			        if ( newVal !== oldVal ) {
				        this.isVatExemptStored = newVal;
                    }
		        },
	        },
	        isShow: {
		        get: function () {
			        return this.isTaxEnabled && !this.getSettingsOption('hide_tax_exempt');
		        },
		        set: function ( newVal, oldVal ) {},
	        },
        },
        methods: {
        },
    }
</script>
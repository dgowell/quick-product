<template>
    <span>
        <button class="btn btn-primary" @click="viewInvoice" id="view-invoice-button"
                v-show="viewInvoiceShow">
            {{ viewInvoiceLabel }}
        </button>
    </span>
</template>

<script>
    export default {
        props: {
            viewInvoiceLabel: {
                default: function () {
                    return 'View invoice';
                }
            },
            viewInvoicePath: {
                default: function () {
                    return '';
                }
            },
        },
        computed: {
            orderID() {
                return this.$store.state.add_order.cart.order_id;
            },
            draftOrderID() {
                return this.$store.state.add_order.cart.drafted_order_id;
            },
            viewInvoiceShow() {
                return !! this.orderID && this.getSettingsOption('show_view_invoice') || !! this.draftOrderID && this.getSettingsOption('show_view_invoice_draft_orders');
            }
        },
        data: function () {
            return {};
        },
        methods: {
            viewInvoice() {
                if (this.viewInvoicePath) {

		    var invoiceUrl = this.viewInvoicePath;

		    invoiceUrl = invoiceUrl.replace(/\%order_id/g, (this.orderID || this.draftOrderID));
		    invoiceUrl = invoiceUrl.replace(/\%nonce/g, this.nonce);

                    window.open(invoiceUrl, '_blank');
                }
            },
        },
    }
</script>
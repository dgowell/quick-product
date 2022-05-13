<template>
    <tr>
        <td colspan="2">
            <table style="width: 100%">
                <tbody>

                <tr>
                    <td>
                        {{ showDuplicateOrderLabel }}
                    </td>
                    <td>
                        <input type="checkbox" class="option" v-model="elShowDuplicateOrder"
                               name="show_duplicate_order_button">
                    </td>
                </tr>

                <tr>
                    <td>
                        {{ hideButtonPayAsCustomerLabel }}
                    </td>
                    <td>
                        <input type="checkbox" class="option" v-model="elHideButtonPayAsCustomer" name="hide_button_pay_as_customer">
                    </td>
                </tr>

                <tr>
                    <td>
                        {{ showButtonPayLabel }}
                        <br/>
                        <i><strong>{{ showButtonPayNote }}</strong></i>
                    </td>
                    <td>
                        <input type="checkbox" class="option" v-model="elShowButtonPay" name="show_button_pay">
                    </td>
                </tr>

		<tr>
                    <td>
                        {{ hideButtonViewOrderLabel }}
                    </td>
                    <td>
                        <input type="checkbox" class="option" v-model="elHideButtonViewOrder" name="hide_button_view_order">
                    </td>
                </tr>

                <tr>
                    <td>
                        {{ hideButtonCreateOrderLabel }}
                    </td>
                    <td>
                        <input type="checkbox" class="option" v-model="elHideButtonCreateOrder" name="hide_button_create_order">
                    </td>
                </tr>

                <tr>
                    <td>
                        {{ hideButtonPutOnHoldLabel }}
                    </td>
                    <td>
                        <input type="checkbox" class="option" v-model="elHideButtonPutOnHold" name="hide_button_put_on_hold">
                    </td>
                </tr>
                <tr>
                    <td>
                        {{ hideButtonFullRefundLabel }}
                    </td>
                    <td>
                        <input type="checkbox" class="option" v-model="elHideButtonFullRefund" name="hide_button_full_refund">
                    </td>
                </tr>
                <tr>
                    <td>
                        {{ hideButtonSendInvoiceLabel }}
                    </td>
                    <td>
                        <input type="checkbox" class="option" v-model="elHideButtonSendInvoice" name="hide_button_send_invoice">
                    </td>
                </tr>

                <tr>
                    <td>
                        {{ showViewInvoiceLabel }}
                    </td>
                    <td>
                        <label>
                            <input type="checkbox" class="option" v-model="elShowViewInvoice" name="show_view_invoice">
                            {{ showViewInvoiceForOrdersLabel }}
                        </label>
                        <label>
                            <input type="checkbox" class="option" v-model="elShowViewInvoiceDraftOrders" name="show_view_invoice_draft_orders">
                            {{ showViewInvoiceForDraftOrdersLabel }}
                        </label>
                    </td>
                </tr>
                <tr>
                    <td>
                        {{ showMarkAsPaidLabel }}
                    </td>
                    <td>
                        <input type="checkbox" class="option" v-model="elShowMarkAsPaid" name="show_mark_as_paid">
                    </td>
                </tr>
                <tr>
                    <td>
                        {{ markAsPaidSetStatusLabel }}
                    </td>
                    <td>
                        <multiselect
                            :allow-empty="false"
                            :hide-selected="true"
                            :searchable="false"
                            style="width: 100%;max-width: 800px;"
                            label="title"
                            v-model="statusMarkAsPaid"
                            :options="orderStatusesList"
                            track-by="value"
                            :show-labels="false"
                        >
                            <template slot="noOptions">
                                <span v-html="noOptionsTitle"></span>
                            </template>
                        </multiselect>
                    </td>
                </tr>
                <tr>
                    <td>
                        {{ hidePrivateNoteLabel }}
                    </td>
                    <td>
                        <input type="checkbox" class="option" v-model="elHidePrivateNote" name="hide_private_note">
                    </td>
                </tr>
                <tr>
                    <td>
                        {{ hideAddGiftCardLabel }}
                    </td>
                    <td>
                        <input type="checkbox" class="option" v-model="elHideAddGiftCard" name="hide_add_gift_card">
                    </td>
                </tr>
                <tr>
                    <td>
                        {{ showCartWeightLabel }}
                    </td>
                    <td>
                        <input type="checkbox" class="option" v-model="elShowCartWeight" name="show_cart_weight">
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
        props: {
            showDuplicateOrderLabel: {
                default: function () {
                    return 'Show "duplicate order" button after order creation';
                },
            },
            showDuplicateOrder: {
                default: function () {
                    return false;
                },
            },
            hideButtonPayAsCustomerLabel: {
                default: function () {
                    return "Hide button \"Pay as customer\"";
                },
            },
            hideButtonPayAsCustomer: {
                default: function () {
                    return false;
                },
            },
            showButtonPayLabel: {
                default: function () {
                    return "Show button \"Pay\"";
                },
            },
            showButtonPayNote: {
                default: function () {
                    return "requires capability pay_for_order";
                },
            },
            showButtonPay: {
                default: function () {
                    return false;
                },
            },
            hideButtonViewOrderLabel: {
                default: function () {
                    return "Hide button \"View Order\"";
                },
            },
            hideButtonViewOrder: {
                default: function () {
                    return false;
                },
            },
            hideButtonCreateOrderLabel: {
                default: function () {
                    return "Hide button \"Create order\"";
                },
            },
            hideButtonCreateOrder: {
                default: function () {
                    return false;
                },
            },
            hideButtonPutOnHoldLabel: {
                default: function () {
                    return "Hide button \"Create Draft\"";
                },
            },
            hideButtonPutOnHold: {
                default: function () {
                    return false;
                },
            },
            hideButtonFullRefundLabel: {
                default: function () {
                    return "Hide button \"Full Refund\"";
                },
            },
            hideButtonFullRefund: {
                default: function () {
                    return false;
                },
            },
            hideButtonSendInvoiceLabel: {
                default: function () {
                    return "Hide button \"Send Invoice\"";
                },
            },
            hideButtonSendInvoice: {
                default: function () {
                    return false;
                },
            },
            showViewInvoiceLabel: {
                default: function () {
                    return "Show button \"View invoice\"";
                },
            },
            showViewInvoice: {
                default: function () {
                    return false;
                },
            },
            showViewInvoiceForOrdersLabel: {
                default: function () {
                    return "for orders";
                },
            },
            showViewInvoiceForDraftOrdersLabel: {
                default: function () {
                    return "for draft orders";
                },
            },
            showViewInvoiceDraftOrders: {
                default: function () {
                    return false;
                },
            },
            showMarkAsPaidLabel: {
                default: function () {
                    return "Show button \"Mark as paid\"";
                },
            },
            showMarkAsPaid: {
                default: function () {
                    return false;
                },
            },
            markAsPaidSetStatusLabel: {
                default: function () {
                    return "\"Mark as paid\" set status to";
                }
            },
            markAsPaidStatus: {
                default: function() {
                    return '';
                },
            },
            orderStatusesList: {
                default: function() {
                    return [];
                },
            },
            noOptionsTitle: {
                default: function () {
                    return 'List is empty.';
                }
            },
            hidePrivateNoteLabel: {
                default: function () {
                    return 'Hide field "Private note"';
                },
            },
            hidePrivateNote: {
                default: function () {
                    return false;
                },
            },
            hideAddGiftCardLabel: {
                default: function () {
                    return 'Hide "Have a gift card?"';
                },
            },
            hideAddGiftCard: {
                default: function () {
                    return false;
                },
            },
            showCartWeightLabel: {
                default: function () {
                    return 'Show cart weight';
                },
            },
            showCartWeight: {
                default: function () {
                    return false;
                },
            },
        },
        data() {
            return {
                elShowDuplicateOrder: this.showDuplicateOrder,
                elHideButtonPayAsCustomer: this.hideButtonPayAsCustomer,
                elShowButtonPay: this.showButtonPay,
                elHideButtonViewOrder: this.hideButtonViewOrder,
                elHideButtonCreateOrder: this.hideButtonCreateOrder,
                elHideButtonPutOnHold: this.hideButtonPutOnHold,
                elHideButtonFullRefund: this.hideButtonFullRefund,
                elHideButtonSendInvoice: this.hideButtonSendInvoice,
                elShowViewInvoice: this.showViewInvoice,
                elShowViewInvoiceDraftOrders: this.showViewInvoiceDraftOrders,
                elShowMarkAsPaid: this.showMarkAsPaid,
                statusMarkAsPaid: this.getObjectByKeyValue(this.orderStatusesList, 'value', this.markAsPaidStatus),
                elHidePrivateNote: this.hidePrivateNote,
                elHideAddGiftCard: this.hideAddGiftCard,
                elShowCartWeight: this.showCartWeight,
            };
        },
        methods: {
            getSettings() {
                return {
                    show_duplicate_order_button: this.elShowDuplicateOrder,
                    hide_button_pay_as_customer: this.elHideButtonPayAsCustomer,
                    show_button_pay: this.elShowButtonPay,
                    hide_button_view_order: this.elHideButtonViewOrder,
                    hide_button_create_order: this.elHideButtonCreateOrder,
                    hide_button_put_on_hold: this.elHideButtonPutOnHold,
                    hide_button_full_refund: this.elHideButtonFullRefund,
                    hide_button_send_invoice: this.elHideButtonSendInvoice,
                    show_view_invoice: this.elShowViewInvoice,
                    show_view_invoice_draft_orders: this.elShowViewInvoiceDraftOrders,
                    show_mark_as_paid: this.elShowMarkAsPaid,
                    mark_as_paid_status: this.getKeyValueOfObject(this.statusMarkAsPaid, 'value'),
                    hide_private_note: this.elHidePrivateNote,
                    hide_add_gift_card: this.elHideAddGiftCard,
                    show_cart_weight: this.elShowCartWeight,
                };
            },
        },
        components: {
            Multiselect,
        },
    }
</script>
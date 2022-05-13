<template>
    <tr>
        <td colspan="2">
            <table style="width: 100%">
                <tbody>
                <tr>
                    <td>
                        {{ showEditOrderInWCLabel }}
                    </td>
                    <td>
                        <input type="checkbox" class="option" v-model="elShowEditOrderInWC" name="show_edit_order_in_wc">
                    </td>
                </tr>

                <tr>
                    <td>
                        {{ showCreatorInOrdersListLabel }}
                    </td>
                    <td>
                        <input type="checkbox" class="option" v-model="elShowCreatorInOrdersList" name="show_creator_in_orders_list">
                    </td>
                </tr>

                <tr>
                    <td>
                        {{ overrideCustomerPaymentLinkInOrderPageLabel }}
                    </td>
                    <td>
                        <input type="checkbox" class="option" v-model="elOverrideCustomerPaymentLinkInOrderPage" name="override_customer_payment_link_in_order_page">
                    </td>
                </tr>

                <tr>
                    <td>
                        {{ overrideEmailPayOrderLinkLabel }}
                <div>
                <i><strong>{{ overrideEmailPayOrderLinkNote }}</strong></i>
                </div>
                    </td>
                    <td>
                        <input type="checkbox" class="option" v-model="elOverrideEmailPayOrderLink" name="override_email_pay_order_link">
                    </td>
                </tr>

                <tr>
                    <td>
                        {{ hideAddOrderInOrdersListLabel }}
                    </td>
                    <td>
                        <input type="checkbox" class="option" v-model="elHideAddOrderInOrdersList" name="hide_add_order_in_orders_list">
                    </td>
                </tr>
                <tr>
                    <td>
                        {{ showPhoneOrdersInOrderPageLabel }}
                    </td>
                    <td>
                        <input type="checkbox" class="option" v-model="elShowPhoneOrdersInOrderPage" name="show_phone_orders_in_order_page">
                    </td>
                </tr>
                <tr>
                    <td>
                        {{ showOrderTypeFilterInOrderPageLabel }}
                    </td>
                    <td>
                        <input type="checkbox" class="option" v-model="elShowOrderTypeFilterInOrderPage" name="show_order_type_filter_in_order_page">
                    </td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
</template>

<script>

    export default {
        props: {
            showEditOrderInWCLabel: {
                default: function () {
                    return "Show button \"Edit\" in orders list ";
                },
            },
            showEditOrderInWC: {
                default: function () {
                    return true;
                },
            },
            showCreatorInOrdersListLabel: {
                default: function () {
                    return "Show column \"Order creator\" in orders list";
                },
            },
            showCreatorInOrdersList: {
                default: function () {
                    return false;
                },
            },
            overrideCustomerPaymentLinkInOrderPageLabel: {
                default: function() {
                    return 'Override "Customer payment page" in the order';
                }
            },
            overrideCustomerPaymentLinkInOrderPage: {
                default: function() {
                    return true;
                }
            },
            overrideEmailPayOrderLinkLabel: {
                default: function() {
                    return 'Override link "Pay for this order" in the order email';
                }
            },
            overrideEmailPayOrderLinkNote: {
                default: function() {
                    return 'This link will automatically login customer!';
                }
            },
            overrideEmailPayOrderLink: {
                default: function() {
                    return false;
                }
            },
            hideAddOrderInOrdersListLabel: {
                default: function () {
                    return "Hide button \"Add order\" in orders list";
                },
            },
            showPhoneOrdersInOrderPageLabel: {
                default: function () {
                    return "Show section \"Phone Orders\" inside order page";
                },
            },
            hideAddOrderInOrdersList: {
                default: function () {
                    return false;
                },
            },
            showPhoneOrdersInOrderPage: {
                default: function () {
                    return true;
                },
            },
            showOrderTypeFilterInOrderPageLabel: {
                default: function () {
                    return "Show order type filter in orders list";
                },
            },
            showOrderTypeFilterInOrderPage: {
                default: function () {
                    return false;
                },
            },
        },
        data() {
            return {
                elShowEditOrderInWC: this.showEditOrderInWC,
                elShowCreatorInOrdersList: this.showCreatorInOrdersList,
                elOverrideCustomerPaymentLinkInOrderPage: this.overrideCustomerPaymentLinkInOrderPage,
                elOverrideEmailPayOrderLink: this.overrideEmailPayOrderLink,
                elHideAddOrderInOrdersList: this.hideAddOrderInOrdersList,
                elShowPhoneOrdersInOrderPage: this.showPhoneOrdersInOrderPage,
                elShowOrderTypeFilterInOrderPage: this.showOrderTypeFilterInOrderPage,
            };
        },
        methods: {
            getSettings() {
                return {
                    show_edit_order_in_wc: this.elShowEditOrderInWC,
                    show_creator_in_orders_list: this.elShowCreatorInOrdersList,
                    override_customer_payment_link_in_order_page: this.elOverrideCustomerPaymentLinkInOrderPage,
                    override_email_pay_order_link: this.elOverrideEmailPayOrderLink,
                    hide_add_order_in_orders_list: this.elHideAddOrderInOrdersList,
                    show_phone_orders_in_order_page: this.elShowPhoneOrdersInOrderPage,
                    show_order_type_filter_in_order_page: this.elShowOrderTypeFilterInOrderPage,
                };
            },
        },
    }
</script>
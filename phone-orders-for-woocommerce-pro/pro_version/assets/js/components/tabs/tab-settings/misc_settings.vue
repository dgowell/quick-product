<template>
    <table class="form-table">
        <tbody>
            <tr>
                <td colspan=2>
                    <b>{{ title }}</b>
                    <span style="margin-left: 10px"><a href="https://docs.algolplus.com/algol_phone_order/custom-fields-pro/" target="_blank">{{ titleLinkLabel }}</a></span>
                </td>
            </tr>
            <tr>
                <td>
                    {{ showOrderCustomFieldsInOrderEmailLabel }}
                </td>
                <td>
                    <input type="checkbox" class="option" v-model="elShowOrderCustomFieldsInOrderEmail" name="show_custom_fields_in_order_email">
                </td>
            </tr>
            <tr>
                <td>
                    {{ customFieldsLabel }}
                    <br>
                    <i>{{ oneFieldPerLineLabel }}</i>
                </td>
                <td>
                    <textarea name="order_custom_fields" class="option" rows=6 v-model.lazy="elOrderCustomFields" ></textarea>
                </td>
            </tr>
            <tr>
                <td>
                    {{ orderCustomFieldsColumnsByLineLabel }}
                    <br>
                    <i>{{ orderCustomFieldsColumnsByLineNote }}</i>
                </td>
                <td>
		    <textarea name="order_custom_fields_columns_by_line" class="option" rows=6 v-model.lazy="elOrderCustomFieldsColumnsByLine"></textarea>
                </td>
            </tr>
            <tr>
                <td>
                    {{ customerCustomFieldsHeaderTextAtTopLabel }}
                </td>
                <td>
                    <input type="text" class="option" v-model="elCustomerCustomFieldsHeaderTextAtTop" name="customer_custom_fields_header_text_at_top">
                </td>
            </tr>
            <tr>
                <td>
                    {{ customerCustomFieldsAtTopLabel }}
                    <br>
                    <i>{{ oneFieldPerLineLabel }}</i>
                </td>
                <td>
                    <textarea name="customer_custom_fields_at_top" class="option" rows=6 v-model.lazy="elCustomerCustomFieldsAtTop" ></textarea>
                </td>
            </tr>
            <tr>
                <td>
                    {{ customerCustomFieldsHeaderTextAtBottomLabel }}
                </td>
                <td>
                    <input type="text" class="option" v-model="elCustomerCustomFieldsHeaderText" name="customer_custom_fields_header_text">
                </td>
            </tr>
            <tr>
                <td>
                    {{ customerCustomFieldsAtBottomLabel }}
                    <br>
                    <i>{{ oneFieldPerLineLabel }}</i>
                </td>
                <td>
                    <textarea name="customer_custom_fields" class="option" rows=6 v-model.lazy="elCustomerCustomFields" ></textarea>
                </td>
            </tr>
            <tr>
                <td>
                    {{ addFormattedCustomFieldsLabel }}
                </td>
                <td>
                    <input type="checkbox" class="option" v-model="elAddFormattedCustomFields" name="add_formatted_custom_fields">
                </td>
            </tr>
            <tr>
                <td>
                    {{ replaceOrderWithCustomerCustomFieldsLabel }}
                </td>
                <td>
                    <input type="checkbox" class="option" v-model="elreplaceOrderWithCustomerCustomFields" name="replace_order_with_customer_custom_fields">
                </td>
            </tr>
            <tr>
                <td>
                    {{ itemMetaFieldsLabel }}
                    <br>
                    <i>{{ itemMetaFieldsOneFieldPerLineLabel }}</i>
                </td>
                <td>
                    <textarea name="item_custom_meta_fields" class="option" rows=6 v-model.lazy="elItemCustomMetaFields" ></textarea>
                </td>
            </tr>
            <tr>
                <td>
                    {{ defaultListItemMetaFieldsLabel }}
                    <br>
                    <i>{{ defaultListItemMetaFieldsOneFieldPerLineLabel }}</i>
                </td>
                <td>
                    <textarea name="default_list_item_custom_meta_fields" class="option" rows=6 v-model.lazy="elDefaultListItemCustomMetaFields" ></textarea>
                </td>
            </tr>
        </tbody>
    </table>
</template>

<style>
</style>

<script>
    export default {
        props: {
            title: {
                default: function() {
                    return 'Custom fields';
                },
            },
            titleLinkLabel: {
                default: function() {
                    return 'How to define custom fields';
                },
            },
            customFieldsLabel: {
                default: function() {
                    return 'Order fields';
                },
            },
            oneFieldPerLineLabel: {
                default: function() {
                    return 'One field per line, use format: Label Text|custom_fieldname';
                },
            },
            orderCustomFields: {
                default: function() {
                    return '';
                },
            },
            itemMetaFieldsLabel: {
                default: function() {
                    return 'Available fields for product';
                },
            },
            itemMetaFieldsOneFieldPerLineLabel: {
                default: function() {
                    return 'One field per line';
                },
            },
            itemCustomMetaFields: {
                default: function() {
                    return '';
                },
            },
            defaultListItemMetaFieldsLabel: {
                default: function() {
                    return 'Default fields for product';
                },
            },
            defaultListItemMetaFieldsOneFieldPerLineLabel: {
                default: function() {
                    return 'One field and value per line, separated by |, e.g meta_key|meta_value';
                },
            },
            defaultListItemCustomMetaFields: {
                default: function() {
                    return '';
                },
            },
	    customerCustomFieldsAtBottomLabel: {
                default: function() {
                    return 'Customer fields (at bottom)';
                },
            },
	    customerCustomFields: {
		    default: function() {
			    return '';
		    },
	    },
            replaceOrderWithCustomerCustomFieldsLabel: {
                default: function() {
                    return 'Replace order fields with customer fields';
                },
            },
            replaceOrderWithCustomerCustomFields: {
		        default: function() {
			        return false;
		        },
	        },
            customerCustomFieldsHeaderTextAtBottomLabel: {
                default: function() {
                    return "Section title for customer fields (at bottom)";
                },
            },
            customerCustomFieldsHeaderText: {
                default: function() {
                    return 'Custom fields';
                },
            },
            addFormattedCustomFieldsLabel: {
                default: function() {
                    return "Show custom fields in section \"Billing Details\"";
                },
            },
            addFormattedCustomFields: {
                default: function() {
                    return false;
                },
            },
	    customerCustomFieldsHeaderTextAtTopLabel: {
                default: function() {
                    return "Section title for customer fields (at top)";
                },
            },
            customerCustomFieldsHeaderTextAtTop: {
                default: function() {
                    return 'Custom fields';
                },
            },
	    customerCustomFieldsAtTopLabel: {
                default: function() {
                    return 'Customer fields (at top)';
                },
            },
	    customerCustomFieldsAtTop: {
		default: function() {
		    return '';
		},
	    },
	    showOrderCustomFieldsInOrderEmailLabel: {
                default: function() {
                    return 'Show custom fields in order confirmation email';
                },
            },
	    showOrderCustomFieldsInOrderEmail: {
		default: function() {
		    return false;
		},
	    },
	    orderCustomFieldsColumnsByLineLabel: {
                default: function() {
                    return 'Number of columns, per line';
                },
            },
	    orderCustomFieldsColumnsByLineNote: {
                default: function() {
                    return 'One number per line, if only one number - it applies to all lines';
                },
            },
	    orderCustomFieldsColumnsByLine: {
		default: function() {
		    return '';
		},
	    },
        },
        data () {
            return {
                elOrderCustomFields: this.orderCustomFields,
                elCustomerCustomFields: this.customerCustomFields,
                elItemCustomMetaFields: this.itemCustomMetaFields,
                elDefaultListItemCustomMetaFields: this.defaultListItemCustomMetaFields,
                elreplaceOrderWithCustomerCustomFields: this.replaceOrderWithCustomerCustomFields,
                elCustomerCustomFieldsHeaderText: this.customerCustomFieldsHeaderText,
                elAddFormattedCustomFields: this.addFormattedCustomFields,
                elCustomerCustomFieldsHeaderTextAtTop: this.customerCustomFieldsHeaderTextAtTop,
                elCustomerCustomFieldsAtTop: this.customerCustomFieldsAtTop,
                elShowOrderCustomFieldsInOrderEmail: this.showOrderCustomFieldsInOrderEmail,
                elOrderCustomFieldsColumnsByLine: this.orderCustomFieldsColumnsByLine,
            };
        },
        methods: {
            getSettings () {
                return {
                    order_custom_fields: this.elOrderCustomFields,
		    customer_custom_fields: this.elCustomerCustomFields,
                    item_custom_meta_fields: this.elItemCustomMetaFields,
                    default_list_item_custom_meta_fields: this.elDefaultListItemCustomMetaFields,
                    replace_order_with_customer_custom_fields: this.elreplaceOrderWithCustomerCustomFields,
                    customer_custom_fields_header_text: this.elCustomerCustomFieldsHeaderText,
                    add_formatted_custom_fields: this.elAddFormattedCustomFields,
                    customer_custom_fields_header_text_at_top: this.elCustomerCustomFieldsHeaderTextAtTop,
		    customer_custom_fields_at_top: this.elCustomerCustomFieldsAtTop,
		    show_custom_fields_in_order_email: this.elShowOrderCustomFieldsInOrderEmail,
		    order_custom_fields_columns_by_line: this.elOrderCustomFieldsColumnsByLine,
                };
            },
        },
    }
</script>
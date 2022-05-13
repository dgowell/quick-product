<template>
    <table class="form-table">
        <tbody>
        <tr>
            <td colspan=2>
                <b>{{ title }}</b>
            </td>
        </tr>

        <tr>
            <td>{{ hideAddDiscountLabel }}</td>
            <td>
                <input type="checkbox" class="option" v-model="elHideAddDiscount" name="hide_add_discount">
            </td>
        </tr>

        <tr>
            <td>{{ couponNameLabel }}</td>
            <td>
                <input type="text" class="option" v-model="elManualCouponTitle" name="manual_coupon_title">
            </td>
        </tr>
        <tr>
            <td>{{ allowToEditCouponNameLabel }}</td>
            <td>
                <input type="checkbox" class="option" v-model="elAllowToEditCouponName" name="allow_to_edit_coupon_name">
            </td>
        </tr>
        </tbody>
    </table>
</template>

<script>
    export default {
        props: {
            title: {
                default: function () {
                    return 'Discount';
                },
            },
            hideAddDiscountLabel: {
                default: function () {
                    return 'Hide "Add discount"';
                },
            },
            couponNameLabel: {
                default: function () {
                    return 'Coupon name  (used by manual discount)';
                },
            },
            hideAddDiscount: {
                default: function () {
                    return false;
                },
            },
            manualCouponTitle: {
                default: function () {
                    return '';
                },
            },
            allowToEditCouponNameLabel: {
                default: function () {
                    return 'Allow to edit coupon name';
                },
            },
            allowToEditCouponName: {
                default: function () {
                    return false;
                },
            },
        },
        data () {
            return {
                elHideAddDiscount: this.hideAddDiscount,
                elManualCouponTitle: this.manualCouponTitle,
                elAllowToEditCouponName: this.allowToEditCouponName,
            };
        },
        methods: {
            getSettings () {
                return {
                    hide_add_discount: this.elHideAddDiscount,
                    manual_coupon_title: this.elManualCouponTitle,
                    allow_to_edit_coupon_name: this.elAllowToEditCouponName,
                };
            },
        },
    }
</script>
<template>
    <tr>
        <td colspan="2">
            <table style="width: 100%">
                <tbody>
		    <tr>
			<td>
			    {{ hideCouponWarningLabel }}
			</td>
			<td>
			    <input type="checkbox" class="option" v-model="elHideCouponWarning" name="hide_coupon_warning">
			</td>
		    </tr>
		    <tr>
			<td>
			    {{ hideAddCouponLabel }}
			</td>
			<td>
			    <input type="checkbox" class="option" v-model="elHideAddCoupon" name="hide_add_coupon">
			</td>
		    </tr>
		    <tr>
			<td>
			    {{ showAllCouponsInAutocompleteLabel }}
			</td>
			<td>
			    <input type="checkbox" class="option" v-model="elShowAllCouponsInAutocomplete" name="show_all_coupons_in_autocomplete">
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
            hideCouponWarningLabel: {
                default: function () {
                    return "Hide warning about disabled coupons";
                },
            },
            hideCouponWarning: {
                default: function () {
                    return false;
                },
            },
            hideAddCouponLabel: {
                default: function () {
                    return 'Hide "Add coupon"';
                },
            },
            hideAddCoupon: {
                default: function () {
                    return false;
                },
            },
            showAllCouponsInAutocompleteLabel: {
                default: function () {
                    return 'Show all coupons in autocomplete';
                },
            },
            showAllCouponsInAutocomplete: {
                default: function () {
                    return false;
                },
            },
        },
        data() {
            return {
                elHideCouponWarning: this.hideCouponWarning,
                elHideAddCoupon: this.hideAddCoupon,
                elShowAllCouponsInAutocomplete: this.showAllCouponsInAutocomplete,

            };
        },
        methods: {
            getSettings() {
                return {
                    hide_coupon_warning: this.elHideCouponWarning,
                    hide_add_coupon: this.elHideAddCoupon,
                    show_all_coupons_in_autocomplete: this.elShowAllCouponsInAutocomplete,
                };
            },
        },
    }
</script>
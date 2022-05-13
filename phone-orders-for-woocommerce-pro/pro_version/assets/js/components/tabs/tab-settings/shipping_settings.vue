<template>
    <tr>
        <td colspan="2">
            <table style="width: 100%">
                <tbody>
                <tr>
                  <td>
                    {{ selectOptimalShippingLabel }}
                  </td>
                  <td>
                    <input type="checkbox" class="option" v-model="elSelectOptimalShipping" name="select_optimal_shipping">
                  </td>
                </tr>
                <tr>
                    <td>
                        {{ hideAddShippingLabel }}
                    </td>
                    <td>
                        <input type="checkbox" class="option" v-model="elHideAddShipping" name="hide_add_shipping">
                    </td>
                </tr>
                <tr>
                    <td>
                        {{ hideShippingSectionLabel }}
                    </td>
                    <td>
                        <input type="checkbox" class="option" v-model="hideShipping" name="hide_shipping_section">
                    </td>
                </tr>
		<tr>
                    <td>
                        {{ usesEmptyAddressShipDifferentLabel }}
                    </td>
                    <td>
                        <input type="checkbox" class="option" v-model="elUsesEmptyAddressShipDifferent" name="uses_empty_address_ship_different">
                    </td>
                </tr>
                <tr>
                    <td>
                        {{ openPopupShipDifferentAddressLabel }}
                    </td>
                    <td>
                        <input type="checkbox" class="option" v-model="elOpenPopupShipDifferentAddress" name="open_popup_ship_different_address">
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
            selectOptimalShippingLabel: {
              default: function () {
                return 'Select optimal shipping method on each cart update';
              }
            },
            selectOptimalShipping: {
              default: function () {
                return false;
              }
            },
            hideAddShippingLabel: {
                default: function () {
                    return "Hide shipping section";
                },
            },
            hideAddShipping: {
                default: function () {
                    return false;
                },
            },
            hideShippingSectionLabel: {
		    default: function() {
			    return 'Hide checkbox "Ship to a different address"';
		    },
	    },
	    hideShippingSection: {
		    default: function() {
			    return false;
		    },
	    },
	    usesEmptyAddressShipDifferentLabel: {
                default: function () {
                    return '"Ship to a different address" uses empty address';
                },
            },
            usesEmptyAddressShipDifferent: {
                default: function () {
                    return false;
                },
            },
            openPopupShipDifferentAddressLabel: {
                default: function () {
                    return '"Ship to a different address" opens popup';
                },
            },
            openPopupShipDifferentAddress: {
                default: function () {
                    return false;
                },
            },
        },
        data() {
            return {
                elSelectOptimalShipping: this.selectOptimalShipping,
                elHideAddShipping: this.hideAddShipping,
                hideShipping: this.hideShippingSection,
		elUsesEmptyAddressShipDifferent: this.usesEmptyAddressShipDifferent,
                elOpenPopupShipDifferentAddress: this.openPopupShipDifferentAddress,
            };
        },
        methods: {
            getSettings() {
                return {
                    select_optimal_shipping: this.elSelectOptimalShipping,
                    hide_add_shipping: this.elHideAddShipping,
                    hide_shipping_section: this.hideShipping,
		    uses_empty_address_ship_different: this.elUsesEmptyAddressShipDifferent,
                    open_popup_ship_different_address: this.elOpenPopupShipDifferentAddress,
                };
            },
        },
    }
</script>
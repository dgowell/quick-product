<template>
    <tr>
        <td colspan="2">
            <table style="width: 100%">
                <tbody>
                <tr>
                    <td>
                        {{ hideTaxesIfTaxExemptLabel }}
                    </td>
                    <td>
                        <input type="checkbox" class="option" v-model="elHideTaxesIfTaxExempt" name="hide_taxes_if_tax_exempt">
                    </td>
                </tr>
		<tr>
		    <td>
			{{ hideTaxExemptLabel }}
		    </td>
		    <td>
			<input type="checkbox" class="option" v-model="elHideTaxExempt" name="hide_tax_exempt">
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
            hideTaxesIfTaxExemptLabel: {
                default: function () {
                    return 'Hide taxes if tax exempt';
                },
            },
            hideTaxesIfTaxExempt: {
                default: function () {
                    return false;
                },
            },
	    hideTaxExemptLabel: {
		    default: function() {
			    return 'Hide checkbox "Tax exempt"';
		    },
	    },
	    hideTaxExempt: {
		    default: function() {
			    return false;
		    },
	    },
        },
        data() {
            return {
                elHideTaxesIfTaxExempt: this.hideTaxesIfTaxExempt,
		elHideTaxExempt: this.hideTaxExempt,
            };
        },
        methods: {
            getSettings() {
                return {
                    hide_taxes_if_tax_exempt: this.elHideTaxesIfTaxExempt,
		    hide_tax_exempt: this.elHideTaxExempt,
                };
            },
        },
    }
</script>
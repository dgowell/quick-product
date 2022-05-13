<template>

    <table class="form-table">
        <tbody>
            <tr>
                <td colspan=2>
                    <b>
                        {{ title }}
                    </b>
                </td>
            </tr>

            <tr>
                <td>{{ hideAddFeeLabel }}</td>
                <td>
                    <input type="checkbox" class="option" v-model="elHideAddFee" name="hide_add_fee">
                </td>
            </tr>

            <tr>
                <td>{{ feeNameLabel }}</td>
                <td>
                    <input type="text" class="option" v-model="elDefaultName" name="default_fee_name">
                </td>
            </tr>

            <tr>
                <td>{{ feeAmountLabel }}</td>
                <td>
                    <input type="text" class="option_number" v-model.number="elDefaultAmount" name="default_fee_amount">
                </td>
            </tr>


            <tr>
                <td>{{ feeTaxClassLabel }}</td>
                <td>
                    <multiselect
                        :allow-empty="false"
                        :hide-selected="true"
                        :searchable="false"
                        style="width: 100%;max-width: 800px;"
                        label="title"
                        v-model="elFeeTaxClass"
                        :options="elTaxClasses"
                        track-by="slug"
                        :show-labels="false"
                    >
                        <template slot="noOptions">
                            <span v-html="noOptionsTitle"></span>
                        </template>
                    </multiselect>
                </td>
            </tr>
            <tr>
                <td>{{ allowToUseZeroAmountLabel }}</td>
                <td>
                    <input type="checkbox" class="option" v-model="elAllowToUseZeroAmount" name="allow_to_use_zero_amount">
                </td>
            </tr>
        </tbody>
    </table>
</template>

<script>

    import Multiselect from 'vue-multiselect';

    export default {
        props: {
	    title: {
		    default: function() {
			    return 'Fee';
		    },
	    },
	    hideAddFeeLabel: {
		    default: function() {
			    return 'Hide "Add fee"';
		    },
	    },
	    feeNameLabel: {
		    default: function() {
			    return 'Fee name';
		    },
	    },
	    feeAmountLabel: {
		    default: function() {
			    return 'Fee amount';
		    },
	    },
	    feeTaxClassLabel: {
		    default: function() {
			    return 'Fee tax class';
		    },
	    },
	    hideAddFee: {
		    default: function() {
			    return false;
		    },
	    },
	    defaultFeeName: {
		    default: function() {
			    return '';
		    },
	    },
	    defaultFeeAmount: {
		    default: function() {
			    return 0;
		    },
	    },
	    feeTaxClass: {
		    default: function() {
			    return '';
		    },
	    },
	    taxClasses: {
		    default: function() {
			    return [];
		    },
	    },
            noOptionsTitle: {
                default: function() {
                    return 'List is empty.';
                }
            },
            allowToUseZeroAmountLabel: {
                default: function() {
                    return 'Allow to use zero amount';
                }
            },
            allowToUseZeroAmount: {
                default: function() {
                    return false;
                }
            },
        },
        data () {
            return {
                elTaxClasses: this.taxClasses,
                elHideAddFee: this.hideAddFee,
                elDefaultName: this.defaultFeeName,
                elDefaultAmount: this.defaultFeeAmount,
                elFeeTaxClass: this.getObjectByKeyValue(this.taxClasses, 'slug', this.feeTaxClass),
                elAllowToUseZeroAmount: this.allowToUseZeroAmount,
            };
        },
        methods: {
            getSettings () {
                return {
                    hide_add_fee: this.elHideAddFee,
                    default_fee_name: this.elDefaultName,
                    default_fee_amount: this.elDefaultAmount,
                    fee_tax_class: this.getKeyValueOfObject(this.elFeeTaxClass, 'slug'),
                    allow_to_use_zero_amount: this.elAllowToUseZeroAmount,
                };
            },
        },
        components: {
            Multiselect,
        },
    }
</script>
<template>
    <b-row>
	<div class="col-12" v-show="show" v-if="Object.keys(fieldList).length !== 0">
	    <strong v-if="customFieldsLabelOption">
		<label class="mr-sm-2">
		    {{ customFieldsLabelOption }}
		</label>
	    </strong>
	    <custom-fields
		:id="'customer_custom_fields'"
		:storedFields="fields"
		:fieldListRows="fieldListRows"
		:dateFormat="dateFormat"
		:singularClassName="'customer-modal-footer__custom_field col-12 col-md-6'"
		:pluralClassName="'customer-modal-footer__custom_fields row'"
		:noOptionsTitle="noOptionsTitle"
		:selectOptionPlaceholder="selectOptionPlaceholder"
		:fileUrlPrefix="fileUrlPrefix"
		@fieldsUpdated="fieldsUpdated"
	    ></custom-fields>
	    <hr>
	</div>
    </b-row>
</template>

<style>
    .customer-modal-footer__custom_field .date-picker {
        width: 70%;
    }

    .customer-modal-footer__custom_field .wpo_custom_field,.customer-modal-footer__custom_field textarea {
        width: 100%;
    }

</style>

<script>
    import customFields from './custom_fields.vue';

    export default {
	props: {
	    dateFormat: {
		default: function () {
		    return "YYYY-MM-DD"
		}
	    },
	    empty: {
		default: function () {
		    return true
		}
	    },
	    noOptionsTitle: {
		default: function() {
		    return 'List is empty.';
		}
	    },
	    selectOptionPlaceholder: {
		default: function() {
		    return 'Select option';
		}
	    },
	    fileUrlPrefix: {
		default: function() {
		    return '';
		}
	    },
	},
	created: function () {
	    this.$root.bus.$on('edit-customer-address', (data) => {
		// manually trigger update to purge fields which not in option
		this.fieldsUpdated( this.fields );
		this.show = typeof data.addressType !== 'undefined' ? data.addressType === 'billing' : true;
	    });
	    this.$root.bus.$on('customer-custom-fields-at-top-set-custom-fields', (data) => {
		this.storedFields = data.custom_fields;
	    });
	},
	data: function () {
	    return {
		show: true,
		storedFields: {},
	    };
	},
	computed: {
	    fieldList() {

		if ( ! this.getSettingsOption( 'customer_custom_fields_at_top' ) ) {
			return [];
		}

		return this.getCustomFieldsList( this.getSettingsOption( 'customer_custom_fields_at_top' ) );
	    },
	    fieldListRows() {
		return [this.fieldList];
	    },
	    availableFieldNames() {
		return this.fieldList.map(function(field) {
		    return field.name;
		});
	    },
	    fields() {
		var result = {}, key;

		for ( key in this.storedFields ) {
			if ( this.storedFields.hasOwnProperty( key ) && this.availableFieldNames.indexOf( key ) !== -1 ) {
				result[key] = this.storedFields[key];
			}
		}

		return result;
	    },
	    customFieldsLabelOption() {
		return this.getSettingsOption( 'customer_custom_fields_header_text_at_top' );
	    },
	},
	watch: {
	    fieldList( newVal, oldVal ) {

		var custom_fields = this.getSettingsOption( 'customer_custom_fields' ) ? this.getCustomFieldsList( this.getSettingsOption( 'customer_custom_fields' ) ) : [];

		var custom_fields_at_top = this.getSettingsOption( 'customer_custom_fields_at_top' ) ? this.getCustomFieldsList( this.getSettingsOption( 'customer_custom_fields_at_top' ) ) : [];

		this.$store.commit(
		    'add_order/setDefaultCustomerCustomFields',
		    Object.assign( {}, this.getDefaultCustomFieldsValues( [...custom_fields_at_top, ...custom_fields] ) )
		);
	    },
	},
	methods: {
	    fieldsUpdated( newVal ) {
		this.$root.bus.$emit( 'edit-customer-address-custom-fields-updated-at-top', Object.assign( {}, newVal ) );
	    },
	    checkCustomer() {
		var valid = true;
		this.$children.forEach(function (child) {
		    if (typeof child.checkFields !== 'undefined') {
			if (!!!child.checkFields()) {
			    valid = false;
			    return;
			}
		    }
		});
		return valid;
	    },
	},
	components: {
	    customFields,
	},
    }
</script>
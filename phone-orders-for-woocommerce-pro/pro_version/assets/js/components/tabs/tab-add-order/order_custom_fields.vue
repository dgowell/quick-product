<template>
    <custom-fields
            v-bind:id="'order_custom_fields'"
            v-bind:storedFields="storedFields"
            v-bind:fieldListRows="fieldListRows"
            v-bind:dateFormat="dateFormat"
            v-bind:singularClassName="'order-footer__custom_field col'"
            v-bind:pluralClassName="'order-footer__custom_fields row'"
            v-bind:noOptionsTitle="noOptionsTitle"
            v-bind:selectOptionPlaceholder="selectOptionPlaceholder"
	    v-bind:fileUrlPrefix="fileUrlPrefix"
            @fieldsUpdated="fieldsUpdated"
    ></custom-fields>
</template>

<style>
    .postbox.disable-on-order .order-footer__custom_fields .date-picker {
        width: 70%;
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
		computed: {
			fieldList() {

				if ( ! this.getSettingsOption( 'order_custom_fields' ) ) {
					return [];
				}

				return this.getCustomFieldsList( this.getSettingsOption( 'order_custom_fields' ) );
			},
			fieldListSettingsRows() {
				return this.getCustomFieldsListSettingsRows( this.getSettingsOption( 'order_custom_fields_columns_by_line' ) );
			},
			fieldListRows() {

			    var rows = [];
			    var row  = [];

			    var defaultCountInRow = 1;

			    if (this.fieldListSettingsRows.length === 1) {
				defaultCountInRow = this.fieldListSettingsRows[0];
			    }

			    var currentRowIndex  = rows.length;
			    var countInRow	 = typeof this.fieldListSettingsRows[currentRowIndex] !== 'undefined' ? this.fieldListSettingsRows[currentRowIndex] : defaultCountInRow;

			    this.fieldList.forEach( (field) => {

				if (row.length >= countInRow) {
				    rows.push(row);
				    row = [];
				    currentRowIndex  = rows.length;
				    countInRow	     = typeof this.fieldListSettingsRows[currentRowIndex] !== 'undefined' ? this.fieldListSettingsRows[currentRowIndex] : defaultCountInRow;
				}

				field.style = 'max-width: ' + (100/countInRow) + '%';
				row.push(field);
			    });

			    if (row.length) {
				rows.push(row);
			    }

			    return rows;
			},
			storedFields() {
				return this.$store.state.add_order.cart.custom_fields;
			},
		},
		watch: {
			fieldList( newVal, oldVal ) {
				this.$store.commit(
					'add_order/setDefaultCartCustomFields',
					Object.assign( {}, this.getDefaultCustomFieldsValues( newVal ) )
				);
			},
		},
		methods: {
			fieldsUpdated( newVal ) {
                var storedFields = Object.assign( {}, this.$store.state.add_order.cart.custom_fields);
                if (JSON.stringify(newVal) !== JSON.stringify(storedFields) ) {
                    this.$store.commit('add_order/setCartCustomFields', newVal);
                }
			},
            checkCart() {
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
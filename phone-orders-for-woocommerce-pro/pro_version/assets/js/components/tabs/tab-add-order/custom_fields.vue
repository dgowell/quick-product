<template>
    <div>
	<div :class="[pluralClassName, 'wpo_custom_fields']" v-for="fieldList in fieldListRows">
	    <div :class="singularClassName" :style="typeof field.style !== 'undefined' ? field.style : ''" v-for="(field, index) in fieldList">
		<template v-if="isAllowedFieldType(field.type)">
		    <label :for="'field_' + fieldName(field,index)" :class="['wpo_custom_field', isRequired(field.name) ? 'required-field' : '', !isValidField(field.name) ? 'valid-field' : 'invalid-field']" :data-field_name="field.name">
			<strong>{{ field.label }}</strong>
		    </label>
		</template>

		<template v-if="field.type === 'hidden'">
		    <input type="text" readonly :id="'field_' + fieldName(field,index)" :name="fieldName(field,index)" v-model="fields[field.name]"
			   v-bind:disabled="!cartEnabled">
		</template>

		<template v-if="field.type === 'text'">
		    <textarea :id="'field_' + fieldName(field,index)" :class="inputClassName" rows=2    cols=20
			      v-model.lazy="fields[field.name]"
			      v-bind:disabled="!cartEnabled"></textarea>
		</template>

		<template v-if="field.type === 'select'">
		    <multiselect
			:class="inputClassName"
                        :id="'field_' + fieldName(field,index)"
                        :allow-empty="false"
                        v-model="fields[field.name]"
                        :searchable="true"
                        style="width: 100%;"
                        :options="field.value.length ? field.value : []"
                        :disabled="!cartEnabled"
			:placeholder="selectOptionPlaceholder"
                        :show-labels="false"
                        track-by="value"
                        label="label"
                        :ref="'field_' + fieldName(field,index)"
			@open="openSelect('field_' + fieldName(field,index), field)"
		    >
			<template slot="noOptions">
			    <span v-html="noOptionsTitle"></span>
			</template>
		    </multiselect>
		</template>

		<template v-if="field.type === 'radio'">
		    <template v-for="option in field.value">
			<input type="radio" :class="inputClassName" :id="option.value" :name="fieldName(field,index)" v-model="fields[field.name]"
			       :value="option.value" v-bind:disabled="!cartEnabled">
			<label :for="option.value">{{ option.label }}</label>
			<span></span>
		    </template>
		</template>

		<template v-if="field.type === 'checkbox'">
		    <template v-if="field.value.length > 0">
			<template v-for="option in field.value">
			    <input type="checkbox" :class="inputClassName" :id="option.value + '_field_' + fieldName(field,index)" :name="option.value + '_name_' + fieldName(field,index)"
				   v-model="fields[field.name]" :value="option.value" v-bind:disabled="!cartEnabled"
				   :true-value="option.value"
			    >
			    <label :for="option.value + '_field_' + fieldName(field,index)">
				{{ option.label }}
			    </label>
			    <span></span>
			</template>
		    </template>
		    <template v-else>
			<input type="checkbox" :class="inputClassName" :id="'field_' + fieldName(field,index)" :name="'name_' + fieldName(field,index)"
			       v-model="fields[field.name]" v-bind:disabled="!cartEnabled">
			<span></span>
		    </template>
		</template>

		<template v-if="field.type === 'date'">
		    <div class="date-picker">
			<datepicker
				v-model="fields[field.name]"
				v-bind:disabled="!cartEnabled"
				:format="formatter"
				:class="inputClassName"
				:id="option + '_field_' + fieldName(field,index)"
				:name="fieldName(field,index)"
                                :typeable="true"
			></datepicker>
		    </div>
		    <br class="clear">
		</template>

		<template v-if="field.type === 'time'">
		    <time-picker
			v-model="fields[field.name]"
			v-bind:disabled="!cartEnabled"
			:class="inputClassName"
			:id="option + '_field_' + fieldName(field,index)"
			:name="fieldName(field,index)"
		    ></time-picker>
		    <br class="clear">
		</template>

		<template v-if="field.type === 'file'">
		    <input type="file" :class="inputClassName" :id="'field_' + fieldName(field,index)" :name="'name_' + fieldName(field,index)"
			    @change="handleFileUpload($event, field.name)" :ref="'field_' + fieldName(field,index)" v-bind:disabled="!cartEnabled">
		    <div v-if="typeof fields[field.name] === 'string' && fields[field.name]">
			<a :href="fileUrlPrefix + fields[field.name]" target="_blank">{{ fields[field.name] }}</a>
		    </div>
		</template>
                <div v-if="isValidField(field.name)" class="field-error-message">{{ isValidField(field.name) }}</div>
	    </div>
	</div>
    </div>
</template>

<style>
    .wpo_custom_field.required-field {
        color: red;
    }

    .postbox.disable-on-order .wpo_custom_fields .time-picker {
        display: inline-block;
        margin: 5px;
        float: none;
    }

    .wpo_custom_field.invalid-field,
    .field-error-message {
        color: red;
    }

    .field-error-message {
        margin-bottom: 5px;
    }
</style>

<script>

    import timePicker from './time_picker.vue';
	import Multiselect from 'vue-multiselect';
	import Datepicker from 'vuejs-datepicker';
	import moment from 'moment'

	export default {
		props: {
			id: {
				default: function () {
					return ""
				}
			},
			dateFormat: {
				default: function () {
					return "YYYY-MM-DD"
				}
			},
			storedFields: {
				default: function () {
					return []
				}
			},
			fieldListRows: {
				default: function () {
					return []
				}
			},
			singularClassName: {
				default: function () {
					return ""
				}
			},
			pluralClassName: {
				default: function () {
					return ""
				}
			},
			inputClassName: {
				default: function () {
					return "custom-field-input"
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
		created: function() {

		},
		watch: {
			storedFields( newVal, oldVal ) {
				let fields = Object.assign( {}, newVal );

				this.fieldListRows.forEach( function ( fieldList ) {
				    fieldList.forEach( function ( field ) {

				    if ( typeof fields[field.name] === 'undefined' || ! fields[field.name] ) {
                        if ( typeof field.selected_values !== 'undefined' && field.selected_values.length ) {
                            if (field.type === 'checkbox') {
                                fields[field.name] = field.selected_values;
                            } else {
                                fields[field.name] = field.selected_values[0];
                            }
                        }

                        if ( (field.type === 'text' || field.type === 'time') ) {
                            fields[field.name] = field.value;
                        }
                    }

					if ( field.type === 'checkbox' ) {
						// convert single checkbox value to empty array of checkboxes
                        // convert array of checkboxes to boolean True if array is not empty
						if ( field.value.length === 0 ) {
							if ( fields[field.name] ) {
							    if (Array.isArray(fields[field.name])) {
							        fields[field.name] = !! fields[field.name].length;
                                } else if ( typeof fields[field.name] === "string") {
                                    fields[field.name] = fields[field.name] === "true" || fields[field.name] === "1";
                                }
                            } else {
								fields[field.name] = false;
                            }
						} else if (field.value.length === 1) {
							if ( fields[field.name] ) {
							     if ( Array.isArray(fields[field.name]) ) {
								fields[field.name] = fields[field.name].length ? fields[field.name][0] : '';
							    }
							} else {
							    fields[field.name] = '';
							}

						} else {
							if ( ! fields[field.name] ) {
							    fields[field.name] = [];
							} else if ( ! Array.isArray(fields[field.name]) ) {
							    fields[field.name] = fields[field.name].split('|');
							}
						}
					}

					if ( field.type === 'hidden' ) {
						fields[field.name] = typeof fields[field.name] !== undefined ? fields[field.name] : (typeof field.value[0] !== 'undefined' ? field.value[0] : '');
                    }

                    if (field.type === 'select' || field.type === 'checkbox' || field.type === 'radio') {
                        if ( Array.isArray(field.value) ) {
                            field.value = field.value.map(function (value) {
                                if (typeof value !== 'object') {
                                    let exploded = value.split("=");
                                    if (!exploded) {
                                        return false;
                                    }
                                    let selectValue = "";
                                    let selectLabel = "";

                                    if (typeof exploded[0] !== 'undefined') {
                                        selectValue = exploded[0];
                                        if (typeof exploded[1] !== 'undefined') {
                                            selectLabel = exploded[1];
                                        } else {
                                            selectLabel = selectValue;
                                        }
                                    }

                                    value = {
                                        'value': selectValue,
                                        'label': selectLabel,
                                    }
                                }

                                if ( field.type === 'select' ) {
                                    // convert string value from store to object for using in multiselect
                                    if ( fields[field.name] === value.value ) {
                                        fields[field.name] = value;
                                    }
                                }

                                return value;
                            });
                        } else {
                            field.value = [];
                        }
                    }
				} );
			    } );

				fields = this.processDateFieldsToDate( fields );

				this.fields = fields;
			},
			fields: {
				handler: function ( newVal, oldVal ) {
					newVal = this.processDateFieldsToString( newVal );
					newVal = this.processMultiValuesFieldsToString( newVal );

                    // pass all changes without comparison between current and stored values
                    // do not forget to do it in parent component
                    this.$emit( 'fieldsUpdated', newVal );
				},
				deep: true,
			},
		},
		data: function () {
		    let fields = {};
		    this.fieldListRows.forEach(function (fieldList) {
			fieldList.forEach(function (current) {
			    fields[current.name] = "";
			});
		    });

		    return {
			fields: fields,
                        validatedFields: {},
		    };
		},
		computed: {
		    requiredFields() {

			var requiredFields = [];

                        var fields = this.processDateFieldsToString( JSON.parse(JSON.stringify(this.fields)) );
			this.fieldListRows.forEach( ( fieldList ) => {
			    fieldList.forEach( ( field ) => {
				if ( field.required && this.isEmpty(fields[field.name]) ) {
				    requiredFields.push(field.name);
				}
			    } );
			} );

			return requiredFields;
		    },
		},
		methods: {
		    isEmpty(value) {
			if (typeof value === 'object') {
			    for (var key in value) {
				if (value.hasOwnProperty(key))
				    return false;
			    }

			    return true;
			} else if (Array.isArray(value)) {
			    return !!!value.length;
			}

		    return !!!value;
		},
		checkFields() {

                    var validFields = true;

                    this.validatedFields = this.getValidatedFields();

                    for (var key in this.validatedFields) {
                        if (this.validatedFields[key])
                            validFields = false;
                    }

		    return !!!this.requiredFields.length && validFields;
		},
		isRequired(fieldName) {
		    return typeof this.requiredFields !== 'undefined' && this.requiredFields.indexOf(fieldName) !== -1;
		},
		fieldName( field, index ) {
		    return this.id + '_' + field.name + '_' + index;
		},
		isAllowedFieldType( type ) {
			return ['text', 'radio', 'checkbox', 'select', 'date', 'hidden', 'time', 'file' ].indexOf( type ) !== - 1;
		},
		formatter( date ) {
			return moment( date ).format( this.dateFormat );
		},
		getDateFields() {

		    var fields = [];

		    this.fieldListRows.forEach( function ( fieldList ) {
			fieldList.forEach( function ( field ) {
			    if ( field.type === 'date' ) {
				fields.push(field.name);
			    }
			} );
		    } );

		    return fields;
		},
		getMultiValuesFields() {

		    var fields = [];

		    this.fieldListRows.forEach( function ( fieldList ) {
			fieldList.forEach( function ( field ) {
			    if ( field.type === 'select' || field.type === 'radio' || field.type === 'checkbox' ) {
				fields.push(field.name);
			    }
			} );
		    } );

		    return fields;
		},
			processDateFieldsToDate( fields ) {
				let dateFields = this.getDateFields();

				fields = Object.assign( {}, fields );

				for ( let key in fields ) {
					if ( ! fields.hasOwnProperty( key ) || dateFields.indexOf( key ) === - 1 || fields[key] === '' ) {
						continue;
					}

					let moment_date = moment( fields[key], this.dateFormat );

					if ( moment_date.isValid() ) {
						fields[key] = moment_date.toDate()
					}
				}

				return fields;
			},
            processMultiValuesFieldsToString( fields ) {
                let selectFields = this.getMultiValuesFields();

                fields = Object.assign( {}, fields );

                for ( let key in fields ) {
                    if ( ! fields.hasOwnProperty( key ) || selectFields.indexOf( key ) === - 1 ) {
                        continue;
                    }

                    if (fields[key] && fields[key].hasOwnProperty('value')) {
                        fields[key] = fields[key].value;
                    }
                }

                return fields;
            },
			processDateFieldsToString( fields ) {
				let dateFields = this.getDateFields();

				fields = Object.assign( {}, fields );

				for ( let key in fields ) {
					if ( ! fields.hasOwnProperty( key ) || dateFields.indexOf( key ) === - 1 ) {
						continue;
					}

					let moment_date = moment( fields[key] );

					if ( moment_date.isValid() ) {
						fields[key] = moment_date.format( this.dateFormat )
					}
				}

				return fields;
			},
		    openSelect( ref, field ) {

			var select  = this.$refs[ref][0];
			var options = select.$refs.list;

			if ( ! select.value && field.start_with === null ) {
			    return;
			}

			var value = select.value ? select.value.value : field.start_with;

			var highlighted = null;

			options.querySelectorAll('.multiselect__option').forEach((option, i) => {

			    var classes = option.className.split(' ').filter((item) => {
				return item !== 'multiselect__option--highlight' && item !== 'multiselect__option--selected';
			    });

			    if (option.innerText.trim() === value) {
				highlighted  = option;
				classes.push('multiselect__option--highlight');
				classes.push('multiselect__option--selected');
			    }

			    option.className = classes.join(' ');
			});

			if ( ! highlighted ) {
			    return;
			}

			this.$nextTick(() => {
			    var nextOffset    = highlighted.offsetTop;
			    nextOffset	     -= options.offsetHeight / 2 - highlighted.offsetHeight / 2;
			    options.scrollTop = nextOffset;
			})
		    },
		    handleFileUpload(event, field_name) {
			this.fields[field_name] = event.target.files[0];
		    },
		    validateField(field_name, field_value, field_data) {
			return typeof window['wpo_js_validate_custom_field'] === 'function' ? window['wpo_js_validate_custom_field'](field_name, field_value, field_data) : '';
		    },
		    isValidField(field_name) {
			return typeof this.validatedFields !== 'undefined' ? this.validatedFields[field_name] : '';
		    },
                    getValidatedFields() {

			var validatedFields = {};

                        var fields = this.processDateFieldsToString( JSON.parse(JSON.stringify(this.fields)) );
			this.fieldListRows.forEach( ( fieldList ) => {
			    fieldList.forEach( ( field ) => {
				validatedFields[field.name] = this.validateField(field.name, fields[field.name], field);
			    } );
			} );

			return validatedFields;
		    },
		},
		components: {
		    Multiselect,
		    Datepicker,
		    timePicker,
		},
	}
</script>
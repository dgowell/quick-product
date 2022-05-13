<template>
    <div v-show="isShow" class="multi-addresses-select">
	<multiselect
	    :allow-empty="true"
	    :hide-selected="true"
	    :searchable="false"
	    style="width: 80%; display: inline-block;"
	    label="address_internal_name"
	    v-model="selectedAddress"
	    :options="addressList"
	    track-by="address_internal_name"
	    :placeholder="selectPlaceholder"
	    :show-labels="false"
	    :loading="isLoading"
	    :disabled="isDisabledSelect"
	>
	    <template slot="singleLabel" slot-scope="props">
		<span v-html="props.option.address_internal_name"></span>
	    </template>
	    <template slot="option" slot-scope="props">
		<span v-html="props.option.address_internal_name"></span>
	    </template>
		<template slot="noOptions">
			<span v-html="noOptionsTitle"></span>
		</template>
	</multiselect>
	<b-button v-show="!isGuest" class="multi-addresses-select__btn-delete" @click="isEnableDeleteButton ? deleteAddress() : null" variant="danger" :disabled="!isEnableDeleteButton">
	    {{ deleteAddressLabel }}
	</b-button>
    </div>
</template>

<style>
    .multi-addresses-select {
	margin-bottom: 15px;
	margin-top: 0;
	background-color: #eee;
	padding: 8px;
	border-radius: 4px;
    }

    .multi-addresses-select__btn-delete {
	margin-left: 15px;
    }

    .multi-addresses-select .multiselect__clear {
	top: 1px;
    }
</style>

<script>

    import Multiselect from 'vue-multiselect';

    export default {
	created() {

	    this.$root.bus.$on('edit-customer-address', () => {

		this.isDisabledSelect = true;

		this.clearSelectedAddress();

		this.$nextTick(() => {
		    this.isShow && this.loadAddresses();
		})
	    });

	    this.$root.bus.$on('multi-addresses-select-reload', () => {
		this.isShow && this.loadAddresses();
	    });

	    this.$root.bus.$on('multi-addresses-select-update-selected', (address) => {
		this.selectedAddress = address;
	    });
	},
        props: {
            slotProps: {
                default: function() {
                    return {};
                }
            },
            selectPlaceholder: {
                default: function() {
                    return 'Select address book entry';
                }
            },
            deleteAddressLabel: {
                default: function() {
                    return 'Delete entry';
                }
            },
            deleteAddressPromptLabel: {
                default: function() {
                    return 'Are you sure?';
                }
            },
			noOptionsTitle: {
				default: function() {
					return 'List is empty.';
				}
			},
	    tabName: {
                default: function() {
                    return '';
                }
            },
        },
        computed: {
			isShow: function () {
				return this.getSettingsOption('allow_multiple_addresses') && (this.isGuest || !!this.slotProps.customer.billing_email);
			},
            isEnableDeleteButton: function () {
		return !!this.selectedAddress;
            },
			isGuest: function () {
				return !+this.slotProps.customer.id;
			},
        },
	data() {
	    return {
		selectedAddress: null,
		addressList: [],
		isLoading: false,
		isDisabledSelect: true,
	    };
	},
	watch: {
	    selectedAddress() {

		this.$root.bus.$emit('multi-addresses-select-selected-address-changed', this.selectedAddress);

		if (this.selectedAddress) {
		    this.$root.bus.$emit('edit-customer-update-address', this.selectedAddress);
		}
	    },
	},
        methods: {
            loadAddresses () {

		this.isLoading = true;

		this.axios.post(this.url, this.qs.stringify({
                    action: 'phone-orders-for-woocommerce',
                    method: 'multi_address_get_list',
                    customer_id: ! this.isGuest ? this.slotProps.customer.id : 0,
                    billing_email: this.slotProps.customer.billing_email,
                    address_type: this.slotProps.addressType,
                    tab: this.tabName,
                })).then( ( response ) => {
		    this.addressList	  = response.data.data.list;
		    this.isLoading	  = false;
		    this.isDisabledSelect = this.addressList.length === 0;
		});
            },
            clearSelectedAddress () {
                this.selectedAddress = null;
            },
            deleteAddress () {

		if ( ! window.confirm(this.deleteAddressPromptLabel) ) {
		    return false;
		}

		this.axios.post(this.url, this.qs.stringify({
                    action: 'phone-orders-for-woocommerce',
                    method: 'multi_addresses_delete_address',
                    customer_id: this.slotProps.customer.id,
                    address_internal_name: this.selectedAddress.address_internal_name,
		    address_type: this.slotProps.addressType,
                    tab: this.tabName,
                })).then( ( response ) => {
		    this.loadAddresses();
		    this.clearSelectedAddress();
                });
            },
        },
	components: {
	    Multiselect,
	},
    }
</script>
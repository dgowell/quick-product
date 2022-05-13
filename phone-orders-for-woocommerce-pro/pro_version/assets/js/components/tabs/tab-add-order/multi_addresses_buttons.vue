<template>
    <div v-show="isShow" class="multi-addresses-buttons">
	<b-button @click="saveAddress" variant="primary">
	    {{ saveAddressLabel }}
	    <b-badge v-show="showAddNewAddressMessage" pill variant="success">
		{{ this.savedNewAddressMessage }}
	    </b-badge>
	</b-button>
	<b-button @click="updateAddress" variant="warning" v-show="!!selectedAddress">
	    {{ updateAddressLabel }}
	    <b-badge v-show="showUpdateAddressMessage" pill variant="success">
		{{ this.updatedAddressMessage }}
	    </b-badge>
	</b-button>
    </div>
</template>

<style>

    .multi-addresses-buttons {
	display: inline-block;
    }

    .multi-addresses-buttons .success-alert {
	display: inline-block;
	margin-left: 10px;
	padding: 4px 18px;
	font-size: 13px;
	margin-bottom: 0;
    }

</style>

<script>
    export default {
	created() {
	    this.$root.bus.$on('multi-addresses-select-selected-address-changed', (address) => {
		this.selectedAddress = address;
	    });
	},
        props: {
            slotProps: {
                default: function() {
                    return {};
                }
            },
            saveAddressLabel: {
                default: function() {
                    return 'Save as new entry';
                }
            },
            updateAddressLabel: {
                default: function() {
                    return 'Update entry';
                }
            },
            savedNewAddressMessage: {
                default: function() {
                    return 'Saved';
                }
            },
            updatedAddressMessage: {
                default: function() {
                    return 'Updated';
                }
            },
            tabName: {
                default: function() {
                    return '';
                }
            },
        },
	data() {
	    return {
		selectedAddress: null,
		showAddNewAddressMessage: false,
		showUpdateAddressMessage: false,
	    };
	},
        computed: {
            isShow: function () {
                return this.getSettingsOption('allow_multiple_addresses') && !this.isGuest;
            },
			isGuest: function () {
				return !+this.slotProps.customer.id;
			},
        },
        methods: {
	    getFormAddress() {

		var address = {};

		this.slotProps.customerGroupFields.personal.fields.forEach((field) => {
		    address[field.key] = field.value;
		});

		this.slotProps.customerGroupFields.address.fields.forEach((field) => {
		    address[field.key] = field.value !== null && typeof field.value.value !== 'undefined' ? field.value.value : field.value;
		});

		return address;
	    },
            saveAddress () {

		var address = this.getFormAddress();

		this.axios.post(this.url, this.qs.stringify({
                    action: 'phone-orders-for-woocommerce',
                    method: 'multi_addresses_save_new_address',
                    customer_id: this.slotProps.customer.id,
                    address: address,
                    address_type: this.slotProps.addressType,
                    tab: this.tabName,
                })).then( ( response ) => {

		    this.showAddNewAddressMessage = true;

		    setTimeout(() => {
			this.showAddNewAddressMessage = false;
		    }, 2000);

		    this.$root.bus.$emit('multi-addresses-select-update-selected', response.data.data.address);

		    this.$root.bus.$emit('multi-addresses-select-reload');
                });
            },
            updateAddress () {

		var address = this.getFormAddress();

		this.axios.post(this.url, this.qs.stringify({
                    action: 'phone-orders-for-woocommerce',
                    method: 'multi_addresses_update_address',
                    customer_id: this.slotProps.customer.id,
		    address: address,
                    address_internal_name: this.selectedAddress.address_internal_name,
		    address_type: this.slotProps.addressType,
                    tab: this.tabName,
                })).then( ( response ) => {

		    this.$root.bus.$emit('multi-addresses-select-update-selected', response.data.data.address);

		    this.$root.bus.$emit('multi-addresses-select-reload');

		    this.showUpdateAddressMessage = true;

		    setTimeout(() => {
			this.showUpdateAddressMessage = false;
		    }, 2000);
                });
            },
        },
    }
</script>
<template>
    <div class="postbox" v-if="showFindOrders">
        <div id="find_order_div" class="disable-on-order">
            <span class="flex_block">
                {{ showButtons ? title : (isEditOrderDefaultAction ? editOrderTitle : copyOrderTitle) }}
            </span>
            <multiselect
                style="width:100%;"
                label="formated_output"
                v-model="order"
                :options="orderList"
                track-by="loaded_order_id"
                id="search-ajax-find-existing-order"
                :placeholder="selectExistingOrdersPlaceholder"
                :loading="isLoading"
                :internal-search="false"
                :show-no-results="true"
                @search-change="asyncFind"
                :hide-selected="false"
                :searchable="true"
                open-direction="bottom"
                @input="selectOrder"
                :allow-empty="false"
                @open="openSelectOrder"
                :show-labels="false"
		v-store-search-multiselect
            >
                <span slot="noResult">{{ noResultLabel }}</span>
                <template slot="singleLabel" slot-scope="props">
                    <span v-html="props.option.formated_output"></span>
                </template>
                <template slot="option" slot-scope="props">
                    <span v-html="props.option.formated_output"></span>
                </template>
                <template slot="noOptions">
                    <span v-html="noOptionsTitle"></span>
                </template>
            </multiselect>
            <span v-if="showButtons && editButtonLabel && !isViewOrderEnabled">
                <input class="btn btn-primary edit-order__button" type="button" :value="editButtonLabel" @click="editOrder" :disabled="!isEditOrderEnabled">
            </span>
            <span v-if="showButtons && viewButtonLabel && isViewOrderEnabled">
                <input class="btn btn-primary view-order__button" type="button" :value="viewButtonLabel" @click="viewOrder">
            </span>
            <span v-if="showButtons && copyButtonLabel">
                <input class="btn btn-primary copy-order__button" type="button" :value="copyButtonLabel" @click="copyOrder" :disabled="!isCopyOrderEnabled">
            </span>
        </div>
        <div class="find-order-alert">
            <div v-if="editedOrderID">
                <b-alert
                    show
                    fade
                    variant="primary"
                >
                    <span>
                        {{ noticeEditedLabel }}
                    </span>
                    <a :href="base_admin_url + 'post.php?post=' + editedOrderID + '&action=edit'" target="_blank" v-if="availableEditOrderLink">
                        #{{ editedOrderNumber }}
                    </a>
		    <span v-else>
                        #{{ editedOrderNumber }}
		    </span>
               </b-alert>
            </div>
            <div v-else-if="draftedOrderID">
                <b-alert
                    show
                    fade
                    variant="warning"
                >
                    <span>
                        {{ noticeDraftedLabel }}
                    </span>
                </b-alert>
            </div>
            <div v-else-if="loadedOrderID">
                <b-alert
                    show
                    fade
                    variant="primary"
                >
                    <span>
                        {{ noticeLoadedLabel }}
                    </span>
                    <a :href="base_admin_url + 'post.php?post=' + loadedOrderID + '&action=edit'" target="_blank" v-if="availableEditOrderLink">
                        #{{ loadedOrderNumber }}
                    </a>
		    <span v-else>
                        #{{ loadedOrderNumber }}
		    </span>
                </b-alert>
            </div>
            <div v-else-if="viewOrderID">
                <b-alert
                    show
                    fade
                    variant="primary"
                >
                    <span>
                        {{ noticeViewLabel }}
                    </span>
                    <a :href="base_admin_url + 'post.php?post=' + viewOrderID + '&action=edit'" target="_blank" v-if="availableEditOrderLink">
                        #{{ viewOrderNumber }}
                    </a>
		    <span v-else>
                        #{{ viewOrderNumber }}
		    </span>
                </b-alert>
            </div>
        </div>
    </div>
</template>

<style>
    #find_order_div .edit-order__button,
    #find_order_div .view-order__button {
        margin-left: 10px;
        padding: 4px 30px;
    }
</style>

<script>

    import Multiselect from 'vue-multiselect';

    export default {
        props: {
            title: {
                default: function() {
                    return 'Find existing order';
                }
            },
            editOrderTitle: {
                default: function() {
                    return 'Find existing order';
                }
            },
            copyOrderTitle: {
                default: function() {
                    return 'Duplicate existing order';
                }
            },
            copyButtonForFindOrdersLabel: {
                default: function() {
                    return 'Copy order';
                }
            },
            editButtonForFindOrdersLabel: {
                default: function() {
                    return 'Edit order';
                }
            },
            viewButtonForFindOrdersLabel: {
                default: function() {
                    return 'View order';
                }
            },
            noticeLoadedLabel: {
                default: function() {
                    return 'Current order was copied from order';
                }
            },
            noticeViewLabel: {
                default: function() {
                    return 'You view order';
                }
            },
            noticeEditedLabel: {
                default: function() {
                    return 'You edit order';
                }
            },
            noticeDraftedLabel: {
                default: function() {
                    return 'You edit unfinished order';
                }
            },
            tabName: {
                default: function() {
                    return 'add-order';
                }
            },
            selectExistingOrdersPlaceholder: {
                default: function() {
                    return 'Type to search';
                }
            },
            noResultLabel: {
                default: function() {
                    return 'Oops! No elements found. Consider changing the search query.';
                }
            },
            multiSelectSearchDelay: {
                default: function() {
                    return 1000;
                }
            },
            noOptionsTitle: {
                default: function() {
                    return 'List is empty.';
                }
            },
            isEditOrderDefaultAction: {
                default: function() {
                    return false;
                }
            },
        },
        data: function () {
            return {
                order: null,
                orderList: [],
                isLoading: false,
                lastRequestTimeoutID: null,
            };
        },
        created: function() {
	    this.$root.bus.$on( 'app-loaded', () => {
		this.$nextTick(() => {
		    var callback = () => { this.$root.bus.$emit('cart-inited'); };
		    let edit_order_id = this.removeGetParameter( "edit_order_id" );
		    if ( this.showEditButtonInWC ) {
			if ( edit_order_id ) {
			    this.loadOrder( edit_order_id, 'edit', callback, callback );
			}
		    }

		    let copy_order_id = this.removeGetParameter( "copy_order_id" );
		    if( copy_order_id ) {
			this.loadOrder( copy_order_id, 'copy', callback, callback );
		    }

		    let restore_cart = this.removeGetParameter( "restore_cart" );
		    if ( +restore_cart ) {
			this.restoreCart(callback, callback);
		    }
		});
	    } );
        },
        computed: {
            isFrontend() {
                return typeof window.wpo_frontend !== 'undefined';
            },
            copyButtonLabel: function () {
                return this.order ? this.order.copy_button_value : this.copyButtonForFindOrdersLabel;
            },
            editButtonLabel: function () {
                return this.order ? this.order.edit_button_value : this.editButtonForFindOrdersLabel;
            },
            viewButtonLabel: function () {
                return this.order ? this.order.view_button_value : this.viewButtonForFindOrdersLabel;
            },
            loadedOrderID: function () {
                return this.$store.state.add_order.cart.loaded_order_id;
            },
            loadedOrderNumber: function () {
                return this.$store.state.add_order.cart.loaded_order_number;
            },
            viewOrderID: function () {
                return this.$store.state.add_order.cart.view_order_id;
            },
            viewOrderNumber: function () {
                return this.$store.state.add_order.cart.view_order_number;
            },
            editedOrderID: function () {
                return this.$store.state.add_order.cart.edit_order_id;
            },
            editedOrderNumber: function () {
                return this.$store.state.add_order.cart.edit_order_number;
            },
            draftedOrderID: function () {
                return this.$store.state.add_order.cart.drafted_order_id;
            },
            wpoCacheOrdersKey: function () {
                return this.getSettingsOption('cache_orders_session_key');
            },
	    showButtons: function () {
		    return this.getSettingsOption('button_for_find_orders');
	    },
	    showEditButtonInWC: function () {
		    return this.getSettingsOption( 'show_edit_order_in_wc' );
	    },
            showFindOrders: function () {
		    return !!!this.getSettingsOption( 'hide_find_orders' );
	    },
	    availableEditOrderLink: function () {
		    return typeof window.wpo_frontend === 'undefined';
	    },
	    isEditOrderEnabled: function () {
		return this.order && this.order.allow_edit;
	    },
	    isCopyOrderEnabled: function () {
		return this.order;
	    },
	    isViewOrderEnabled: function () {
		return this.order && this.order.allow_view;
	    },
        },
        methods: {
            selectOrder () {

                if (this.showButtons) {
                    return;
                }

                this.isEditOrderDefaultAction ? this.isEditOrderEnabled && this.editOrder() : this.isCopyOrderEnabled && this.copyOrder();
            },
            copyOrder () {
	            this.loadOrder( this.order.loaded_order_id, 'copy' )
            },
            editOrder () {
	            this.loadOrder( this.order.loaded_order_id, 'edit' )
            },
            viewOrder () {
	            this.loadOrder( this.order.loaded_order_id, 'view' )
            },
	    loadOrder( order_id, mode, success_callback, error_callback ) {

		this.isLoading = true;
		this.$store.commit( 'add_order/setIsLoading', true );

		this.$store.commit('add_order/setButtonsMessage', '');

		this.axios.get( this.url, {
			params: {
				action: 'phone-orders-for-woocommerce',
				method: 'load_order',
				order_id: order_id,
				tab: this.tabName,
				mode: mode,
                is_frontend: this.isFrontend ? 1 : 0,
			}
		} ).then( ( response ) => {

			this.$store.commit(
				'add_order/setCartCustomFields',
				Object.assign( {}, this.getDefaultCustomFieldsValues(
					this.getCustomFieldsList( this.getSettingsOption( 'order_custom_fields' ) )
				), response.data.data.cart.custom_fields )
			);

			delete response.data.data.cart.custom_fields;

			this.$store.commit( 'add_order/setCart', Object.assign( {
				drafted_order_id: null,
				edit_order_id: null,
				edit_order_number: null,
				loaded_order_id: null,
				loaded_order_number: null,
				view_order_id: null,
				view_order_number: null,
				order_id: null,
				order_number: null,
				allow_refund_order: false,
			}, response.data.data.cart ) );

                        delete response.data.data.cart;

			this.$store.commit( 'add_order/setState', response.data.data );

			this.updateStoredCartHash();

			this.order = null;
			this.isLoading = false;
			this.$store.commit( 'add_order/setIsLoading', false );

			if (typeof success_callback === 'function') {
			    success_callback();
			}

                        if (mode === 'view') {
                            this.$store.commit('add_order/setCartOrderID', this.$store.state.add_order.cart.view_order_id);
                            this.$store.commit('add_order/setCartOrderNumber', this.$store.state.add_order.cart.view_order_number);

                            this.$store.commit('add_order/setCartEnabled', false);
                        } else {
                            this.$store.commit('add_order/setCartEnabled', true);
                        }

		}, () => {
			if (typeof error_callback === 'function') {
			    error_callback();
			}
			this.isLoading = false;
		} );
	    },
	    restoreCart(success_callback, error_callback) {

		this.isLoading = true;

		this.$store.commit( 'add_order/setIsLoading', true );

		this.axios.get( this.url, {
		    params: {
			action: 'phone-orders-for-woocommerce',
			method: 'restore_cart',
			tab: this.tabName,
		    }
		} ).then( ( response ) => {

		    this.$store.commit(
			'add_order/setCartCustomFields',
			Object.assign( {}, this.getDefaultCustomFieldsValues(
			    this.getCustomFieldsList( this.getSettingsOption( 'order_custom_fields' ) )
			), response.data.data.cart.custom_fields )
		    );

		    delete response.data.data.cart.custom_fields;

		    this.$store.commit( 'add_order/setCart', Object.assign( {}, response.data.data.cart ) );

		    delete response.data.data.cart;

		    this.$store.commit( 'add_order/setState', response.data.data );

		    this.updateStoredCartHash();

		    this.isLoading = false;

		    if (typeof success_callback === 'function') {
			success_callback();
		    }

		    this.$store.commit( 'add_order/setIsLoading', false );

		}, () => {
		    if (typeof error_callback === 'function') {
			error_callback();
		    }
		    this.isLoading = false;
		} );
	    },
            openSelectOrder () {
                this.orderList = [];
            },
            asyncFind(query) {
                this.lastRequestTimeoutID && clearTimeout(this.lastRequestTimeoutID);

                if (!query) {
                    this.isLoading = false;
                    this.lastRequestTimeoutID = null;
                    this.orderList = [];
                    return;
                }

                this.isLoading = true;

                this.lastRequestTimeoutID = setTimeout(() => {
                    this.axios.get(this.url, {
                        params: {
                            action: 'phone-orders-for-woocommerce',
                            wpo_cache_orders_key: this.wpoCacheOrdersKey,
                            method: 'find_orders',
                            tab: this.tabName,
                            term: query,
                        }
                    }).then((response) => {
                        this.orderList = response.data;
                        this.isLoading = false;
                    });
                }, this.multiSelectSearchDelay);
            },
        },
        components: {
            Multiselect,
        },
    }
</script>
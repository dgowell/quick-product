<template>
    <table class="form-table">
        <tbody>
	    <tr>
		<td colspan=2>
		    <b>{{ title }}</b>
		</td>
	    </tr>

		<tr>
			<td>
				{{ hideFindOrdersLabel }}
			</td>
			<td>
				<input type="checkbox" class="option" v-model="elHideFindOrders" name="hide_find_orders">
			</td>
		</tr>


		<tr>
		<td>
		    {{ cacheTimeoutLabel }}
		</td>
		<td>
		    <input type="hidden" name="cache_orders_session_key" v-model="orderSessionKey">
		    <input type="hidden" name="cache_orders_reset" id="cache_orders_reset" v-model.number="cacheReset">
		    <input type="number" class="option_hours" v-model.number="orderTimeout" id="cache_orders_timeout"
			   name="cache_orders_timeout" min="0">
		    {{ hoursLabel }}
		    <span v-if="orderTimeout">
			    <button id="cache_orders_disable_button" @click="disableCache" class="btn btn-primary">
				{{ disableCacheButtonLabel }}
			    </button>
			    <button id="cache_orders_reset_button" @click="resetCache" class="btn btn-danger">
				{{ resetCacheButtonLabel }}
			    </button>
			</span>
		</td>
	    </tr>
        <tr>
            <td>
                {{ seekOnlyOrdersWithStatusLabel }}
            </td>
            <td>
                <multiselect
                    style="width: 100%;max-width: 800px;"
                    label="title"
                    v-model="elSeekOnlyOrdersWithStatusList"
                    :options="orderStatusesList"
                    track-by="value"
                    :placeholder="seekOnlyOrdersWithStatusPlaceholder"
                    :internal-search="false"
                    :show-no-results="true"
                    :hide-selected="false"
                    :searchable="true"
                    open-direction="bottom"
                    :show-labels="false"
                    :multiple="true"
                    ref="seekOnlyOrdersWithStatusList">
                        <template slot="tag" slot-scope="props">
                            <span class="multiselect__tag">
                              <span v-html="props.option.title"></span>
                              <i aria-hidden="true" tabindex="1" @keydown.enter.prevent="removeSeekOnlyOrdersWithStatus(props.option)" @mousedown.prevent="removeSeekOnlyOrdersWithStatus(props.option)" class="multiselect__tag-icon"></i>
                            </span>
                        </template>
                        <template slot="option" slot-scope="props">
                            <span v-html="props.option.title"></span>
                        </template>
                </multiselect>
            </td>
        </tr>
	    <tr>
		<td>
		    {{ showButtonCopyOrderLabel }}
		</td>
		<td>
		    <input type="checkbox" class="option" v-model="elButtonForFindOrder" name="button_for_find_orders">
		</td>
	    </tr>

	    <tr>
		<td>
		    {{ setCurrentPriceForItemsInCopiedOrderLabel }}
		</td>
		<td>
		    <input type="hidden" name="set_current_price_in_copied_order" value="">
		    <input type="checkbox" class="option" v-model="elSetCurrentPriceInCopiedOrder" name="set_current_price_in_copied_order">
		</td>
	    </tr>

		<tr>
			<td>
				{{ setCurrentPriceForShippingInCopiedOrderLabel }}
			</td>
			<td>
				<input type="hidden" name="set_current_price_shipping_in_copied_order" value="">
				<input type="checkbox" class="option" v-model="elSetCurrentPriceForShippingInCopiedOrder" name="set_current_price_shipping_in_copied_order">
			</td>
		</tr>
	    <tr>
		<td>
		    {{ dontAllowEditOrderHaveStatusListLabel }}
		</td>
		<td>
		    <multiselect
                        style="width: 100%;max-width: 800px;"
                        label="title"
                        v-model="elDontAllowEditOrderHaveStatusList"
                        :options="orderStatusesList"
                        track-by="value"
                        :placeholder="dontAllowEditOrderHaveStatusListPlaceholder"
                        :internal-search="false"
                        :show-no-results="true"
                        :hide-selected="false"
                        :searchable="true"
                        open-direction="bottom"
                        :show-labels="false"
                        :multiple="true"
			ref="dontAllowEditOrderHaveStatusList"
                    >
                        <template slot="tag" slot-scope="props">
                            <span class="multiselect__tag">
                              <span v-html="props.option.title"></span>
                              <i aria-hidden="true" tabindex="1" @keydown.enter.prevent="removeElementDontAllowEditOrderHaveStatus(props.option)" @mousedown.prevent="removeElementDontAllowEditOrderHaveStatus(props.option)" class="multiselect__tag-icon"></i>
                            </span>
                        </template>
                        <template slot="option" slot-scope="props">
                            <span v-html="props.option.title"></span>
                        </template>
		    </multiselect>
		</td>
	    </tr>
	    <tr>
		<td>
		    {{ showOrdersCurrentUserLabel }}
		</td>
		<td>
		    <input type="checkbox" class="option" v-model="elShowOrdersCurrentUser" name="show_orders_current_user">
		</td>
	    </tr>
        </tbody>
    </table>
</template>

<style>
</style>

<script>

    import Multiselect from 'vue-multiselect';

    export default {
        created () {
            this.$root.bus.$on('settings-saved', this.onSettingsSaved);
        },
        props: {
            title: {
                default: function () {
                    return 'Find orders';
                },
            },
            hoursLabel: {
                default: function() {
                    return 'hours';
                },
            },
            cacheTimeoutLabel: {
                default: function () {
                    return 'Caching search results';
                },
            },
            disableCacheButtonLabel: {
                default: function () {
                    return 'Disable cache';
                },
            },
            resetCacheButtonLabel: {
                default: function () {
                    return 'Reset cache';
                },
            },
            showButtonCopyOrderLabel: {
                default: function () {
                    return 'Show buttons';
                },
            },
            setCurrentPriceForItemsInCopiedOrderLabel: {
                default: function () {
                    return 'Set current price for items in copied order';
                },
            },
			setCurrentPriceForShippingInCopiedOrderLabel: {
                default: function () {
                    return 'Set current price for shipping in copied order';
                },
            },
			hideFindOrdersLabel: {
				default: function () {
					return "Hide \"Find orders\"";
				},
			},
            sessionKey: {
                default: function () {
                    return '';
                },
            },
            cacheTimeout: {
                    default: function () {
                            return 0;
                    },
            },
            copyOnlyPaidOrders: {
                default: function () {
                    return false;
                },
            },
            buttonForFindOrder: {
                default: function () {
                    return false;
                },
            },
            setCurrentPriceInCopiedOrder: {
                default: function () {
                    return false;
                },
            },
			setCurrentPriceForShippingInCopiedOrder: {
                default: function () {
                    return false;
                },
            },
	    hideFindOrders: {
                default: function () {
                    return false;
                },
            },
            seekOnlyOrdersWithStatusLabel: {
                default: function () {
                    return 'Seek only in orders having status';
                },
            },
            seekOnlyOrdersWithStatusPlaceholder: {
                default: function () {
                    return 'Select items';
                }
            },
            seekOnlyOrdersWithStatusList: {
                default: function () {
                    return [];
                }
            },
            dontAllowEditOrderHaveStatusListLabel: {
                default: function () {
                    return "Don't allow to edit orders having status";
                },
            },
	    dontAllowEditOrderHaveStatusList: {
                default: function () {
                    return [];
                },
            },
	    orderStatusesList: {
                default: function() {
                    return [];
                },
            },
	    dontAllowEditOrderHaveStatusListPlaceholder: {
                default: function() {
                    return 'Select items';
                },
            },
            showOrdersCurrentUserLabel: {
                default: function () {
                    return "Seek only in orders created by current user";
                },
            },
	    showOrdersCurrentUser: {
                default: function () {
                    return false;
                },
            },
        },
        data () {

            var seekOnlyOrdersWithStatusList = [];

            this.seekOnlyOrdersWithStatusList.forEach((orderStatus) => {

                let orderStatusOption = this.getObjectByKeyValue(this.orderStatusesList, 'value', orderStatus);

                if (orderStatusOption) {
                    seekOnlyOrdersWithStatusList.push(orderStatusOption);
                }
            });

            var dontAllowEditOrderHaveStatusList = [];

                this.dontAllowEditOrderHaveStatusList.forEach((orderStatus) => {

                let orderStatusOption = this.getObjectByKeyValue(this.orderStatusesList, 'value', orderStatus);

                if (orderStatusOption) {
                    dontAllowEditOrderHaveStatusList.push(orderStatusOption);
                }
            });

            return {
                orderSessionKey: this.sessionKey,
                cacheReset: 0,
                orderTimeout: this.cacheTimeout,
                elButtonForFindOrder: this.buttonForFindOrder,
                elSetCurrentPriceInCopiedOrder: this.setCurrentPriceInCopiedOrder,
                elSetCurrentPriceForShippingInCopiedOrder: this.setCurrentPriceForShippingInCopiedOrder,
                elHideFindOrders: this.hideFindOrders,
                elSeekOnlyOrdersWithStatusList: seekOnlyOrdersWithStatusList,
                elDontAllowEditOrderHaveStatusList: dontAllowEditOrderHaveStatusList,
                elShowOrdersCurrentUser: this.showOrdersCurrentUser,
            };
        },
        methods: {
            disableCache () {
                this.orderTimeout = 0;
                this.saveSettingsByEvent();
            },
            resetCache () {
                this.cacheReset = 1;
                this.saveSettingsByEvent();
            },
            getSettings () {

                var dontAllowEditOrderHaveStatusList = [];

                this.elDontAllowEditOrderHaveStatusList.forEach((orderStatus) => {

                    let orderStatusValue = this.getKeyValueOfObject(orderStatus, 'value');

                    if (orderStatusValue) {
                        dontAllowEditOrderHaveStatusList.push(orderStatusValue);
                    }
                });

                var seekOnlyOrdersWithStatusList = [];

                this.elSeekOnlyOrdersWithStatusList.forEach((orderStatus) => {

                    let orderStatusValue = this.getKeyValueOfObject(orderStatus, 'value');

                    if (orderStatusValue) {
                        seekOnlyOrdersWithStatusList.push(orderStatusValue);
                    }
                });

                return {
                    cache_orders_session_key: this.orderSessionKey,
                    cache_orders_reset: this.cacheReset,
                    cache_orders_timeout: this.orderTimeout,
                    button_for_find_orders: this.elButtonForFindOrder,
                    set_current_price_in_copied_order: this.elSetCurrentPriceInCopiedOrder,
		    set_current_price_shipping_in_copied_order: this.elSetCurrentPriceForShippingInCopiedOrder,
                    hide_find_orders: this.elHideFindOrders,
                    seek_only_orders_with_statuses: seekOnlyOrdersWithStatusList,
		    dont_allow_edit_order_have_status_list: dontAllowEditOrderHaveStatusList,
		    show_orders_current_user: this.elShowOrdersCurrentUser,
                };
            },
            onSettingsSaved (settings) {
                this.orderSessionKey = settings.cache_orders_session_key;
                this.cacheReset      = settings.cache_orders_reset;
            },
            removeElementDontAllowEditOrderHaveStatus (option) {
                this.$refs.dontAllowEditOrderHaveStatusList.removeElement(option);
            },
            removeSeekOnlyOrdersWithStatus (option) {
                this.$refs.seekOnlyOrdersWithStatusList.removeElement(option);
            },
        },
	components: {
            Multiselect
        },
    }
</script>
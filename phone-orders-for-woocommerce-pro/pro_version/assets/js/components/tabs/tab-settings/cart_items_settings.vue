<template>
    <tr>
        <td colspan="2">
            <table style="width: 100%">
                <tbody>

            <tr>
			<td>
			    {{ showDiscountAmountInOrderLabel }}
			</td>
			<td>
			    <input type="checkbox" class="option" v-model="elShowDiscountAmountInOrder" name="show_discount_amount_in_order">
			</td>
		    </tr>
            <tr>
			<td>
			    {{ allowToRenameCartItemsLabel }}
			</td>
			<td>
			    <input type="checkbox" class="option" v-model="elAllowToRenameCartItems" name="allow_to_rename_cart_items">
			</td>
		    </tr>
                    <tr>
			<td>
			    {{ showProductDescriptionLabel }}
			</td>
			<td>
			    <input type="checkbox" class="option" v-model="elShowProductDescription" name="show_product_description">
                            <span>
                                {{ showProductDescriptionPreviewSizeLabel }}
                                <input type="number" class="option_number" v-model.number="elShowProductDescriptionPreviewSize" :min="10" :step="10" name="show_product_description_preview_size">
                            </span>
			</td>
		    </tr>

		    <tr>
			<td>
			    {{ productsDefaultSelectedLabel }}
			</td>
			<td>
			    <multiselect
				style="width: 100%;max-width: 800px;"
				label="title"
				v-model="elItemDefaultSelected"
				:options="defaultItemList"
				track-by="value"
				id="ajax"
				:placeholder="itemDefaultSelectedPlaceholder"
				:loading="isLoading"
				:internal-search="false"
				:show-no-results="true"
				@search-change="asyncFind"
				:hide-selected="false"
				:searchable="true"
				open-direction="bottom"
				:show-labels="false"
				:multiple="true"
				ref="defaultItemsSelected"
			    >
				<template slot="tag" slot-scope="props">
				    <span class="multiselect__tag">
				      <span v-html="props.option.title"></span>
				      <i aria-hidden="true" tabindex="1" @keydown.enter.prevent="removeElement(props.option)" @mousedown.prevent="removeElement(props.option)" class="multiselect__tag-icon"></i>
				    </span>
				</template>
				<span slot="noResult">{{ noResultLabel }}</span>
				<template slot="option" slot-scope="props">
				    <span v-html="props.option.title"></span>
				</template>
				<template slot="noOptions">
				    <span v-html="noOptionsTitle"></span>
				</template>
			  </multiselect>
			</td>
		    </tr>

		    <tr>
			<td>
			    {{ productsAddProductToTopOfTheCartLabel }}
			</td>
			<td>
			    <input type="checkbox" class="option" v-model="elAddProductToTopOfTheCart" name="add_product_to_top_of_the_cart">
			</td>
		    </tr>

		    <tr>
			<td>
			    {{ isReadonlyPriceLabel }}
			</td>
			<td>
			    <input type="checkbox" class="option" v-model="elIsReadonlyPrice" name="is_readonly_price">
			</td>
		    </tr>

		    <tr>
			<td>
			    {{ productsItemPricePrecisionLabel }}
			</td>
			<td>
			    <input type="number" class="option_number" v-model.number="elItemPricePrecision" name="item_price_precision" min=0>
			</td>
		    </tr>

		    <tr>
			<td>
			    {{ dontRefreshCartItemItemMetaLabel }}
			</td>
			<td>
			    <input type="checkbox" class="option" v-model="elDontRefreshCartItemItemMeta" name="dont_refresh_cart_item_item_meta">
			</td>
		    </tr>

                    <tr>
			<td>
			    {{ disableEditMetaLabel }}
			</td>
			<td>
			    <input type="checkbox" class="option" v-model="elDisableEditMeta" name="disable_edit_meta">
			</td>
		    </tr>

		    <tr>
			<td>
			    {{ hideItemMetaLabel }}
			</td>
			<td>
			    <input type="checkbox" class="option" v-model="elHideItemMeta" name="hide_item_meta">
			</td>
		    </tr>

		    <tr>
			<td>
			    {{ actionClickOnTitleProductItemInCartLabel }}
			</td>
			<td>
			    <label>
				<input type="radio" class="option" v-model="elActionClickOnTitleProductItemInCart" name="action_click_on_title_product_item_in_cart" value="edit_product">
				{{ actionClickOnTitleProductItemInCartEditProductLabel }}
			    </label>
			    <label>
				<input type="radio" class="option" v-model="elActionClickOnTitleProductItemInCart" name="action_click_on_title_product_item_in_cart" value="view_product">
				{{ actionClickOnTitleProductItemInCartViewProductLabel }}
			    </label>
			</td>
		    </tr>

		    <tr>
			<td>
			    {{ showAdditionalProductColumnLabel }}
			</td>
			<td>
			    <input type="checkbox" class="option" v-model="elShowAdditionalProductColumn" name="show_additional_product_column">
			     <a href= "https://docs.algolplus.com/algol_phone_order/show-extra-information-in-the-cart/" target=_blank>{{ additionalProductColumnHelpLinkLabel }}</a>
			</td>
		    </tr>
		    <tr v-show="elShowAdditionalProductColumn">
			<td>
			    {{ additionalProductColumnTitleLabel }}
			</td>
			<td>
			    <input type="text" class="option" v-model="elAdditionalProductColumnTitle" name="additional_product_column_title">
			</td>
		    </tr>

		</tbody>
            </table>
        </td>
    </tr>
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
            allowToRenameCartItemsLabel: {
                default: function() {
                    return 'Allow to rename cart items';
                },
            },
            productsAddProductToTopOfTheCartLabel: {
                default: function() {
                    return 'Add product to top of the cart';
                },
            },
            productsItemPricePrecisionLabel: {
                default: function() {
                    return 'Item price precision';
                },
            },
            productsDefaultSelectedLabel: {
                default: function() {
                    return 'Add products by default';
                },
            },
            noResultLabel: {
                default: function() {
                    return 'Oops! No elements found. Consider changing the search query.';
                },
            },
            itemDefaultSelectedPlaceholder: {
                default: function() {
                    return 'Select items';
                },
            },
            allowToRenameCartItems: {
                default: function() {
                    return false;
                },
            },
            addProductToTopOfTheCart: {
                default: function() {
                    return false;
                },
            },
            itemPricePrecision: {
                default: function() {
                    return 0;
                },
            },
            itemDefaultSelected: {
                default: function() {
                    return [];
                },
            },
	    dontRefreshCartItemItemMetaLabel: {
		default: function () {
		    return "Don't refresh cart when item meta edited";
		},
	    },
	    dontRefreshCartItemItemMeta: {
		default: function () {
		    return true;
		},
	    },
	    disableEditMetaLabel: {
		default: function () {
		    return 'Disable edit meta';
		},
	    },
	    disableEditMeta: {
		default: function () {
		    return false;
		},
	    },
	    hideItemMetaLabel: {
		default: function () {
		    return 'Hide item meta';
		},
	    },
	    hideItemMeta: {
		default: function () {
		    return false;
		},
	    },
	    isReadonlyPriceLabel: {
		default: function () {
		    return 'Item price is read-only';
		},
	    },
	    isReadonlyPrice: {
		default: function () {
		    return false;
		},
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
            actionClickOnTitleProductItemInCartLabel: {
                default: function() {
                    return 'Click on title - action, in cart';
                },
            },
	    actionClickOnTitleProductItemInCartEditProductLabel: {
                default: function() {
                    return 'Edit product';
                },
            },
	    actionClickOnTitleProductItemInCartViewProductLabel: {
                default: function() {
                    return 'View product';
                },
            },
	    actionClickOnTitleProductItemInCart: {
                default: function() {
                    return 'edit_product';
                },
            },
	    showAdditionalProductColumnLabel: {
                default: function () {
                    return "Show additional product column";
                },
            },
            showAdditionalProductColumn: {
                default: function () {
                    return false;
                },
            },
            additionalProductColumnHelpLinkLabel: {
                default: function () {
                    return "How to use this option";
                },
            },
            additionalProductColumnTitleLabel: {
                default: function () {
                    return "Additional product column title";
                },
            },
	    additionalProductColumnTitle: {
                default: function () {
                    return '';
                },
            },
            showDiscountAmountInOrderLabel: {
                default: function () {
                    return "Show discount amount in the order";
                },
            },
	    showDiscountAmountInOrder: {
                default: function () {
                    return false;
                },
            },
            showProductDescriptionLabel: {
                default: function () {
                    return "Show product description";
                },
            },
	    showProductDescription: {
                default: function () {
                    return false;
                },
            },
            showProductDescriptionPreviewSizeLabel: {
                default: function () {
                    return "preview size";
                },
            },
	    showProductDescriptionPreviewSize: {
                default: function () {
                    return 60;
                },
            },
        },
        data () {
            return {
                elAllowToRenameCartItems: this.allowToRenameCartItems,
	            elAddProductToTopOfTheCart: this.addProductToTopOfTheCart,
	            elIsReadonlyPrice: this.isReadonlyPrice,
	            elItemPricePrecision: this.itemPricePrecision,
	            elItemDefaultSelected: this.itemDefaultSelected,
	            elDontRefreshCartItemItemMeta: this.dontRefreshCartItemItemMeta,
	            elDisableEditMeta: this.disableEditMeta,
	            elHideItemMeta: this.hideItemMeta,
		    elShowAdditionalProductColumn: this.showAdditionalProductColumn,
		    elAdditionalProductColumnTitle: this.additionalProductColumnTitle,

		    lastRequestTimeoutID: null,
	            defaultItemList: [],
	            isLoading: false,

		    elActionClickOnTitleProductItemInCart: this.actionClickOnTitleProductItemInCart,
		    elShowDiscountAmountInOrder: this.showDiscountAmountInOrder,
		    elShowProductDescription: this.showProductDescription,
		    elShowProductDescriptionPreviewSize: this.showProductDescriptionPreviewSize,
            };
        },
        computed: {
            selectedItemIDs () {
                return this.elItemDefaultSelected.map(function (v) { return v.value });
            },
        },
        methods: {
            getSettings () {
                return {
                    allow_to_rename_cart_items: this.elAllowToRenameCartItems,
                    add_product_to_top_of_the_cart: this.elAddProductToTopOfTheCart,
                    is_readonly_price: this.elIsReadonlyPrice,
                    item_price_precision: this.elItemPricePrecision,
                    item_default_selected: this.selectedItemIDs,
                    dont_refresh_cart_item_item_meta: this.elDontRefreshCartItemItemMeta,
                    disable_edit_meta: this.elDisableEditMeta,
                    hide_item_meta: this.elHideItemMeta,
                    action_click_on_title_product_item_in_cart: this.elActionClickOnTitleProductItemInCart,
                    show_additional_product_column: this.elShowAdditionalProductColumn,
                    additional_product_column_title: this.elAdditionalProductColumnTitle,
                    show_discount_amount_in_order: this.elShowDiscountAmountInOrder,
                    show_product_description: this.elShowProductDescription,
                    show_product_description_preview_size: this.elShowProductDescriptionPreviewSize,
		        };
            },
            onSettingsSaved (settings) {
                this.productsSessionKey = settings.cache_products_session_key;
                this.cacheReset         = settings.cache_products_reset;
            },
            removeElement (option) {
                this.$refs.defaultItemsSelected.removeElement(option);
            },
            asyncFind(query) {
                this.lastRequestTimeoutID && clearTimeout(this.lastRequestTimeoutID);

                if (!query && query !== null) {
                    this.isLoading = false;
                    this.lastRequestTimeoutID = null;
                    this.defaultItemList = [];
                    return;
                }

                this.isLoading = true;

                this.lastRequestTimeoutID = setTimeout(() => {
                    this.axios.get(this.url, {
                        params: {
                            action: 'phone-orders-for-woocommerce',
                            method: 'search_products_and_variations',
                            tab: 'add-order',
                            term: query,
                            exclude: JSON.stringify(this.selectedItemIDs),
                            wpo_cache_products_key: this.productsSessionKey,
                        },
                        paramsSerializer: (params) => {
                            return this.qs.stringify(params)
                        }
                    }).then((response) => {

                        var products = [];

                        for (var id in response.data) {
                            if (response.data.hasOwnProperty(id)) {
                                var product_id = response.data[id].product_id;
                                if (this.selectedItemIDs.indexOf(+product_id) === -1) {
                                    products.push({title: response.data[id].title, value: product_id});
                                }
                            }
                        }

                        this.defaultItemList = products;

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
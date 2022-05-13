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
                    {{ productsCacheProductsTimeoutLabel }}
                </td>
                <td>
                    <input type="hidden" name="cache_products_session_key" v-model="productsSessionKey">
                    <input type="hidden" name="cache_products_reset" id="cache_products_reset" v-model.number="cacheReset">
                    <input type="number" class="option_hours" v-model.number="productsTimeout" id="cache_products_timeout"
                           name="cache_products_timeout" min="0">
                    {{ hoursLabel }}
                    <span v-if="productsTimeout">
                        <button id="cache_products_disable_button" @click="disableCache" class="btn btn-primary">
                            {{ disableCacheButtonLabel }}
                        </button>
                        <button id="cache_products_reset_button" @click="resetCache" class="btn btn-danger">
                            {{ resetCacheButtonLabel }}
                        </button>
                    </span>
                </td>
            </tr>

            <tr>
                <td>
                    {{ productsSearchBySkuLabel }}
                </td>
                <td>
                    <input type="hidden" name="search_by_sku" value="">
                    <input type="checkbox" class="option" v-model="elSearchBySku" name="search_by_sku">
                </td>
            </tr>

            <tr>
                <td>
                    {{ productsVerboseSearchLabel }}
                    <br>
                    <i>{{ mayBeSlowLabel }}</i>
                </td>
                <td>
                    <input type="hidden" name="search_by_sku" value="">
                    <input type="checkbox" class="option" v-model="elVerboseSearch" name="verbose_search">
                </td>
            </tr>

            <tr>
                <td>
                    {{ productsSearchByCatAndTagLabel }}
                </td>
                <td>
                    <input type="hidden" name="search_by_cat_and_tag" value="">
                    <input type="checkbox" class="option" v-model="elSearchByCatAndTag" name="search_by_cat_and_tag">
                </td>
            </tr>

            <tr>
                <td>
                    {{ productsNumberOfProductsToShowLabel }}
                </td>
                <td>
                    <input type="number" class="option_number" v-model.number="elNumberOfProductsToShow" name="number_of_products_to_show" min=0>
                </td>
            </tr>

            <tr>
                <td>
                    {{ productsSortByRelevancyLabel }}
                </td>
                <td>
                    <input type="checkbox" class="option" v-model.number="elSortProductsByRelevancy" name="sort_products_by_relevancy">
                </td>
            </tr>

            <tr>
                <td>
                    {{ allowDuplicateProductsLabel }}
                </td>
                <td>
                    <input type="checkbox" class="option" v-model="elAllowDuplicateProducts" name="allow_duplicate_products">
                </td>
            </tr>

            <tr>
                <td>
                    {{ productsSellBackorderProductLabel }}
                </td>
                <td>
                    <input type="checkbox" class="option" v-model="elSaleBackorderProducts" name="sale_backorder_product">
                </td>
            </tr>

            <tr>
                <td>
                    {{ productsHideProductsWithNoPriceLabel }}
                </td>
                <td>
                    <input type="hidden" name="hide_products_with_no_price" value="">
                    <input type="checkbox" class="option" v-model="elHideProductsWithNoPrice" name="hide_products_with_no_price">
                </td>
            </tr>

            <tr>
                <td>
                    {{ productsSellDisableVariationLabel }}
                </td>
                <td>
                    <input type="checkbox" class="option" v-model="elSellDisableVariation" name="sell_disable_variation">
                </td>
            </tr>

	    <tr>
                <td>
                    {{ displaySearchResultAsGridLabel }}
                </td>
                <td>
                    <input type="checkbox" class="option" v-model="elDisplaySearchResultAsGrid" name="display_search_result_as_grid">
                </td>
            </tr>
	    <tr>
                <td>
                    {{ showQtyInputAdvancedSearchLabel }}
                </td>
                <td>
                    <input type="checkbox" class="option" v-model="elShowQtyInputAdvancedSearch" name="show_qty_input_advanced_search">
                </td>
            </tr>
	    <tr>
                <td>
                    {{ showPriceInputAdvancedSearchLabel }}
                </td>
                <td>
                    <input type="checkbox" class="option" v-model="elShowPriceInputAdvancedSearch" name="show_price_input_advanced_search">
                </td>
            </tr>
	    <tr>
                <td>
                    {{ barcodeModeLabel }}
                </td>
                <td>
                    <input type="checkbox" class="option" v-model="elBarcodeMode" name="barcode_mode">
                </td>
            </tr>
            <tr>
		<td>
		    {{ hideProductFieldsLabel }}
		</td>
		<td>
		    <input type="checkbox" class="option_checkbox" v-model="elHideImage" name="autocomplete_product_hide_image">{{ hideImageLabel }}
		    <input type="checkbox" class="option_checkbox" v-model="elHideStatus" name="autocomplete_product_hide_status">{{ hideStatusLabel }} &nbsp;
		    <input type="checkbox" class="option_checkbox" v-model="elHideQty" name="autocomplete_product_hide_qty">{{ hideQtyLabel }} &nbsp;
		    <input type="checkbox" class="option_checkbox" v-model="elHidePrice" name="autocomplete_product_hide_price">{{ hidePriceLabel }} &nbsp;
		    <input type="checkbox" class="option_checkbox" v-model="elHideSku" name="autocomplete_product_hide_sku">{{ hideSkuLabel }} &nbsp;
		    <input type="checkbox" class="option_checkbox" v-model="elHideName" name="autocomplete_product_hide_name">{{ hideNameLabel }}
		</td>
	    </tr>
            <tr>
                <td>
                    {{ productsShowLongAttributeNamesLabel }}
                </td>
                <td>
                    <input type="hidden" name="show_long_attribute_names" value="">
                    <input type="checkbox" class="option" v-model="elShowLongAttributeNames" name="show_long_attribute_names">
                </td>
            </tr>
	    <tr>
                <td>
                    {{ actionClickOnTitleProductItemInSearchProductsLabel }}
                </td>
                <td>
		    <label>
			<input type="radio" class="option" v-model="elActionClickOnTitleProductItemInSearchProducts" name="action_click_on_title_product_item_in_search_products" value="add_product_to_cart">
			{{ actionClickOnTitleProductItemInSearchProductsAddProductToCartLabel }}
		    </label>
		    <label>
			<input type="radio" class="option" v-model="elActionClickOnTitleProductItemInSearchProducts" name="action_click_on_title_product_item_in_search_products" value="edit_product">
			{{ actionClickOnTitleProductItemInSearchProductsEditProductLabel }}
		    </label>
		    <label>
			<input type="radio" class="option" v-model="elActionClickOnTitleProductItemInSearchProducts" name="action_click_on_title_product_item_in_search_products" value="view_product">
			{{ actionClickOnTitleProductItemInSearchProductsViewProductLabel }}
		    </label>
                </td>
            </tr>
            <tr>
                <td>
                    {{ productsDefaultSearchResultLabel }}
                </td>
                <td>
                    <multiselect
                        style="width: 100%;max-width: 800px;"
                        label="title"
                        v-model="elItemDefaultSearchResult"
                        :options="defaultSearchResultItemList"
                        track-by="value"
                        id="ajax"
                        :placeholder="itemDefaultSearchResultPlaceholder"
                        :loading="isLoadingDefaultSearchResult"
                        :internal-search="false"
                        :show-no-results="true"
                        @search-change="asyncFindDefaultSearchResult"
                        :hide-selected="false"
                        :searchable="true"
                        open-direction="bottom"
                        :show-labels="false"
                        :multiple="true"
                        ref="defaultSearchResultItems"
                    >
                        <template slot="tag" slot-scope="props">
                            <span class="multiselect__tag">
                              <span v-html="props.option.title"></span>
                              <i aria-hidden="true" tabindex="1" @keydown.enter.prevent="removeElementDefaultSearchResult(props.option)" @mousedown.prevent="removeElementDefaultSearchResult(props.option)" class="multiselect__tag-icon"></i>
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
                default: function() {
                    return 'Products';
                },
            },
            hoursLabel: {
                default: function() {
                    return 'hours';
                },
            },
            productsCacheProductsTimeoutLabel: {
                default: function() {
                    return 'Caching search results';
                },
            },
            cacheSessionKey: {
                default: function() {
                    return '';
                },
            },
            cacheTimeout: {
                default: function() {
                    return 0;
                },
            },
            disableCacheButtonLabel: {
                default: function() {
                    return 'Disable cache';
                },
            },
            resetCacheButtonLabel: {
                default: function() {
                    return 'Reset cache';
                },
            },
            searchBySku: {
                default: function() {
                    return false;
                },
            },
            productsSearchBySkuLabel: {
                default: function() {
                    return 'Search by SKU';
                },
            },
            verboseSearch: {
                default: function() {
                    return false;
                },
            },
            productsVerboseSearchLabel: {
                default: function() {
                    return 'Deep search';
                },
            },
            mayBeSlowLabel: {
                default: function() {
                    return 'in parent product, may be slow';
                },
            },
            searchByCatAndTag: {
                default: function() {
                    return false;
                },
            },
            productsSearchByCatAndTagLabel: {
                default: function() {
                    return 'Filter products by category/tags';
                },
            },
            productsNumberOfProductsToShowLabel: {
                default: function() {
                    return 'Number of products to show in autocomplete';
                },
            },
            productsSortByRelevancyLabel: {
                default: function() {
                    return 'Sort by relevance';
                },
            },
            hideProductFieldsLabel: {
                default: function() {
                    return 'Hide fields in autocomplete';
                },
            },
            hideImageLabel: {
                default: function() {
                    return 'Image';
                },
            },
            hideStatusLabel: {
                default: function() {
                    return 'Status';
                },
            },
            hideQtyLabel: {
                default: function() {
                    return 'Qty';
                },
            },
            hidePriceLabel: {
                default: function() {
                    return 'Price';
                },
            },
            hideSkuLabel: {
                default: function() {
                    return 'Sku';
                },
            },
            hideNameLabel: {
                default: function() {
                    return 'Name';
                },
            },
            productsShowLongAttributeNamesLabel: {
                default: function() {
                    return 'Show long attribute names';
                },
            },
	    productsHideProductsWithNoPriceLabel: {
                default: function() {
                    return 'Don\'t sell products with no price defined';
                },
            },
            productsSellBackorderProductLabel: {
                default: function() {
                    return 'Sell "out of stock" products';
                },
            },
            productsSellDisableVariationLabel: {
                default: function() {
                    return 'Sell disabled variations';
                },
            },
            noResultLabel: {
                default: function() {
                    return 'Oops! No elements found. Consider changing the search query.';
                },
            },
            numberOfProductsToShow: {
                default: function() {
                    return false;
                },
            },
            sortProductsByRelevancy: {
                default: function() {
                    return true;
                },
            },
            hideImage: {
                default: function() {
                    return false;
                },
            },
            hideStatus: {
                default: function() {
                    return false;
                },
            },
            hideQty: {
                default: function() {
                    return false;
                },
            },
            hidePrice: {
                default: function() {
                    return false;
                },
            },
            hideSku: {
                default: function() {
                    return false;
                },
            },
            hideName: {
                default: function() {
                    return false;
                },
            },
            showLongAttributeNames: {
                default: function() {
                    return false;
                },
            },
            hideProductsWithNoPrice: {
                default: function() {
                    return false;
                },
            },
            saleBackorderProducts: {
                default: function() {
                    return false;
                },
            },
            sellDisableVariation: {
                default: function() {
                    return false;
                },
            },

	    allowDuplicateProductsLabel: {
		default: function () {
			return 'Allow to use same product many times';
		},
	    },
            allowDuplicateProducts: {
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
            productsDefaultSearchResultLabel: {
                default: function() {
                    return 'Show products as default search result';
                },
            },
            itemDefaultSearchResultPlaceholder: {
                default: function() {
                    return 'Select items';
                },
            },
	    itemDefaultSearchResult: {
                default: function() {
                    return [];
                },
            },
            displaySearchResultAsGridLabel: {
                default: function() {
                    return 'Show search results as a grid of images';
                },
            },
	    displaySearchResultAsGrid: {
                default: function() {
                    return true;
                },
            },
            showQtyInputAdvancedSearchLabel: {
                default: function() {
                    return 'Show QTY input in Advanced Search popup';
                },
            },
	    showQtyInputAdvancedSearch: {
                default: function() {
                    return false;
                },
            },
            showPriceInputAdvancedSearchLabel: {
                default: function() {
                    return 'Show PRICE input in Advanced Search popup';
                },
            },
	    showPriceInputAdvancedSearch: {
                default: function() {
                    return false;
                },
            },
	    actionClickOnTitleProductItemInSearchProductsLabel: {
                default: function() {
                    return 'Click on title - action, browse products';
                },
            },
	    actionClickOnTitleProductItemInSearchProductsAddProductToCartLabel: {
                default: function() {
                    return 'Add to cart';
                },
            },
	    actionClickOnTitleProductItemInSearchProductsEditProductLabel: {
                default: function() {
                    return 'Edit product';
                },
            },
	    actionClickOnTitleProductItemInSearchProductsViewProductLabel: {
                default: function() {
                    return 'View product';
                },
            },
	    actionClickOnTitleProductItemInSearchProducts: {
                default: function() {
                    return 'add_product_to_cart';
                },
            },
	    barcodeModeLabel: {
                default: function() {
                    return 'Barcode scanner mode';
                },
            },
	    barcodeMode: {
                default: function() {
                    return false;
                },
            },
        },
        data () {
            return {
		productsSessionKey: this.cacheSessionKey,
		cacheReset: 0,
		productsTimeout: + this.cacheTimeout,
		elSearchBySku: this.searchBySku,
		elVerboseSearch: this.verboseSearch,
		elSearchByCatAndTag: this.searchByCatAndTag,
		elNumberOfProductsToShow: this.numberOfProductsToShow,
		elSortProductsByRelevancy: this.sortProductsByRelevancy,
		elHideImage: this.hideImage,
		elHideStatus: this.hideStatus,
		elHideQty: this.hideQty,
		elHidePrice: this.hidePrice,
		elHideSku: this.hideSku,
		elHideName: this.hideName,
		elShowLongAttributeNames: this.showLongAttributeNames,
		elHideProductsWithNoPrice: this.hideProductsWithNoPrice,
		elSaleBackorderProducts: this.saleBackorderProducts,
		elSellDisableVariation: this.sellDisableVariation,
		elAllowDuplicateProducts: this.allowDuplicateProducts,

		elItemDefaultSearchResult: this.itemDefaultSearchResult,

		lastRequestTimeoutIDDefaultSearchResult: null,
		defaultSearchResultItemList: [],
		isLoadingDefaultSearchResult: false,

		elDisplaySearchResultAsGrid: this.displaySearchResultAsGrid,
		elShowQtyInputAdvancedSearch: this.showQtyInputAdvancedSearch,
		elShowPriceInputAdvancedSearch: this.showPriceInputAdvancedSearch,
		elActionClickOnTitleProductItemInSearchProducts: this.actionClickOnTitleProductItemInSearchProducts,
		elBarcodeMode: this.barcodeMode,
            };
        },
        computed: {
            selectedDefaultSearchResultItemIDs () {
                return this.elItemDefaultSearchResult.map(function (v) { return v.value });
            },
        },
        methods: {
            disableCache () {
                this.productsTimeout = 0;
                this.saveSettingsByEvent();
            },
            resetCache () {
                this.cacheReset = 1;
                this.saveSettingsByEvent();
            },
            getSettings () {
                return {
		    cache_products_session_key: this.productsSessionKey,
		    cache_products_reset: this.cacheReset,
		    cache_products_timeout: this.productsTimeout,
		    search_by_sku: this.elSearchBySku,
		    verbose_search: this.elVerboseSearch,
		    search_by_cat_and_tag: this.elSearchByCatAndTag,
		    number_of_products_to_show: this.elNumberOfProductsToShow,
		    sort_products_by_relevancy: this.elSortProductsByRelevancy,
		    autocomplete_product_hide_image: this.elHideImage,
		    autocomplete_product_hide_status: this.elHideStatus,
		    autocomplete_product_hide_qty: this.elHideQty,
		    autocomplete_product_hide_price: this.elHidePrice,
		    autocomplete_product_hide_sku: this.elHideSku,
		    autocomplete_product_hide_name: this.elHideName,
		    show_long_attribute_names: this.elShowLongAttributeNames,
		    hide_products_with_no_price: this.elHideProductsWithNoPrice,
		    sale_backorder_product: this.elSaleBackorderProducts,
		    sell_disable_variation: this.elSellDisableVariation,
		    allow_duplicate_products: this.elAllowDuplicateProducts,
		    item_default_search_result: this.selectedDefaultSearchResultItemIDs,
		    display_search_result_as_grid: this.elDisplaySearchResultAsGrid,
		    show_qty_input_advanced_search: this.elShowQtyInputAdvancedSearch,
		    show_price_input_advanced_search: this.elShowPriceInputAdvancedSearch,
		    action_click_on_title_product_item_in_search_products: this.elActionClickOnTitleProductItemInSearchProducts,
		    barcode_mode: this.elBarcodeMode,
                };
            },
            onSettingsSaved (settings) {
                this.productsSessionKey = settings.cache_products_session_key;
                this.cacheReset         = settings.cache_products_reset;
            },
            removeElementDefaultSearchResult (option) {
                this.$refs.defaultSearchResultItems.removeElement(option);
            },
            asyncFindDefaultSearchResult(query) {

		this.lastRequestTimeoutIDDefaultSearchResult && clearTimeout(this.lastRequestTimeoutIDDefaultSearchResult);

                if (!query && query !== null) {
                    this.isLoadingDefaultSearchResult = false;
                    this.lastRequestTimeoutIDDefaultSearchResult = null;
                    this.defaultSearchResultItemList = [];
                    return;
                }

                this.isLoadingDefaultSearchResult = true;

                this.lastRequestTimeoutIDDefaultSearchResult = setTimeout(() => {
                    this.axios.get(this.url, {
                        params: {
                            action: 'phone-orders-for-woocommerce',
                            method: 'search_products_and_variations',
                            tab: 'add-order',
                            term: query,
                            exclude: JSON.stringify(this.selectedDefaultSearchResultItemIDs),
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
                                if (this.selectedDefaultSearchResultItemIDs.indexOf(+product_id) === -1) {
				    products.push({
                                        title: response.data[id].title,
                                        value: product_id,
                                    });
                                }
                            }
                        }

                        this.defaultSearchResultItemList = products;

                        this.isLoadingDefaultSearchResult = false;
                    });
                }, this.multiSelectSearchDelay);
            },
        },
        components: {
            Multiselect,
        },
    }
</script>
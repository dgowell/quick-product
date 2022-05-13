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
                    {{ productsDisableCreatingProductsLabel }}
                </td>
                <td>
                    <input type="checkbox" class="option" v-model="elDisableAddingProducts" name="disable_adding_products">
                </td>
            </tr>

            <tr>
                <td>
                    {{ productsNewProductAskSKULabel }}
                </td>
                <td>
                    <input type="checkbox" class="option" v-model="elNewProductAskSKU" name="new_product_ask_sku">
                </td>
            </tr>

            <tr>
                <td>
                    {{ productsNewProductVisibilityLabel }}
                </td>
                <td>

                    <i>
                        {{ productsThisSettingDeterminesWhichShopPagesProductsWillBeListedOn }}
                    </i>
                    <br>
                    <multiselect
                        :allow-empty="false"
                        :searchable="false"
                        style="width: 100%;max-width: 800px;"
                        label="label"
                        v-model="elProductVisibility"
                        :options="elProductVisibilityOptions"
                        track-by="name"
                        :show-labels="false"
                    >
                        <template slot="noOptions">
                            <span v-html="noOptionsTitle"></span>
                        </template>
                    </multiselect>
                </td>

            </tr>

            <tr>
                <td>
                    {{ showProductPropertiesLabel }}
                </td>
                <td>
		    <label class="option_checkbox_label">
			<input type="checkbox" class="option_checkbox" v-model="elNewProductShowWeight" name="new_product_show_weight">{{ newProductShowWeightLabel }} &nbsp;
		    </label>
		    <label class="option_checkbox_label">
			<input type="checkbox" class="option_checkbox" v-model="elNewProductShowLength" name="new_product_show_length">{{ newProductShowLengthLabel }} &nbsp;
		    </label>
		    <label class="option_checkbox_label">
			<input type="checkbox" class="option_checkbox" v-model="elNewProductShowWidth" name="new_product_show_width">{{ newProductShowWidthLabel }} &nbsp;
		    </label>
		    <label class="option_checkbox_label">
			<input type="checkbox" class="option_checkbox" v-model="elNewProductShowHeight" name="new_product_show_height">{{ newProductShowHeightLabel }} &nbsp;
		    </label>
                </td>
            </tr>
            <tr>
                <td>
                    {{ showProductCategoryLabel }}
                </td>
                <td>
                    <input type="checkbox" class="option" v-model="elShowProductCategory" name="show_product_category">
                </td>
            </tr>

            <tr>
                <td>
                    {{ productsNewProductAskTaxClassLabel }}
                </td>
                <td>
                    <input type="checkbox" class="option" v-model="elNewProductAskTaxClass" name="new_product_ask_tax_class">
                </td>
            </tr>

            <tr>
                <td>{{ addItemTaxClassLabel }}</td>
                <td>
                    <multiselect
                            :allow-empty="false"
                            :searchable="false"
                            style="width: 100%;max-width: 800px;"
                            label="title"
                            v-model="elItemTaxClass"
                            :options="elItemTaxClasses"
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
                <td>
                    {{ editCreatedProductInNewTabLabel }}
                </td>
                <td>
                    <input type="checkbox" class="option" v-model="elEditCreatedProductInNewTab" name="edit_created_product_in_new_tab">
                </td>
            </tr>

	    <tr>
                <td>{{ createWoocommerceProductLabel }}</td>
                <td>
                    <multiselect
			:allow-empty="false"
			:searchable="false"
			style="width: 100%;max-width: 800px;"
			label="title"
			v-model="elCreateWoocommerceProduct"
			:options="createWoocommerceProductOptions"
			track-by="value"
			:show-labels="false"
                    >
                        <template slot="noOptions">
                            <span v-html="noOptionsTitle"></span>
                        </template>
                    </multiselect>
                </td>
            </tr>
            <tr>
              <td>
                {{ createPrivateProductLabel }}<br>
                <i>{{ createPrivateProductLabelTip }}</i>
              </td>
              <td>
                <input type="checkbox" class="option" v-model="elCreatePrivateProduct" name="create_private_product">
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
        props: {
            title: {
                default: function() {
                    return 'New Product';
                },
            },
            productsDisableCreatingProductsLabel: {
                default: function() {
                    return 'Disable creating products';
                },
            },
            productsNewProductAskSKULabel: {
                default: function() {
                    return 'Show SKU while adding product';
                },
            },
            productsNewProductVisibilityLabel: {
                default: function() {
                    return 'New product visibility';
                },
            },
	        productsThisSettingDeterminesWhichShopPagesProductsWillBeListedOn: {
		        default: function() {
			        return 'This setting determines which shop pages products will be listed on.';
		        },
	        },
            newProductAskSKU: {
                default: function() {
                    return 0;
                },
            },
            productVisibility: {
                default: function() {
                    return '';
                },
            },
            productVisibilityOptions: {
                default: function() {
                    return [];
                },
            },
	        disableAddingProducts: {
		        default: function() {
			        return 0;
		        },
	        },
	        productsNewProductAskTaxClassLabel: {
		        default: function() {
			        return 'Show tax class selector';
		        },
	        },
	        newProductAskTaxClass: {
		        default: function() {
			        return false;
		        },
	        },
	        addItemTaxClassLabel: {
		        default: function() {
			        return 'Default tax class';
		        },
	        },
	        itemTaxClass: {
		        default: function() {
			        return '';
		        },
	        },
	        itemTaxClasses: {
		        default: function() {
			        return [];
		        },
	        },
            noOptionsTitle: {
                default: function() {
                    return 'List is empty.';
                }
            },
            editCreatedProductInNewTabLabel: {
                default: function() {
                    return 'Edit created product in new tab';
                }
            },
            editCreatedProductInNewTab: {
                default: function() {
			        return false;
		        },
            },
            showProductCategoryLabel: {
                default: function() {
                    return 'Show category selector';
                }
            },
            showProductCategory: {
                default: function() {
		    return false;
		},
            },
            showProductPropertiesLabel: {
                default: function() {
                    return 'Show product properties';
                }
            },
            newProductShowWeightLabel: {
                default: function() {
                    return 'Weight';
                }
            },
            newProductShowWeight: {
                default: function() {
                    return false;
                }
            },
            newProductShowLengthLabel: {
                default: function() {
                    return 'Length';
                }
            },
            newProductShowLength: {
                default: function() {
                    return false;
                }
            },
            newProductShowWidthLabel: {
                default: function() {
                    return 'Width';
                }
            },
            newProductShowWidth: {
                default: function() {
                    return false;
                }
            },
            newProductShowHeightLabel: {
                default: function() {
                    return 'Height';
                }
            },
            newProductShowHeight: {
                default: function() {
                    return false;
                }
            },
            createWoocommerceProductLabel: {
                default: function() {
                    return 'Checkbox "Create WooCommerce product"';
                }
            },
            createWoocommerceProduct: {
                default: function() {
                    return null;
                }
            },
            createWoocommerceProductOptions: {
                default: function() {
                    return [];
                }
            },
            createPrivateProductLabel: {
              default: function() {
                return 'Create private product';
              }
            },
            createPrivateProduct: {
              default: function() {
                return false;
              }
            },
            createPrivateProductLabelTip: {
              default: function() {
                return "To use such products - you must turn off 'Switch Customer' at tab Common !";
              }
            },
        },
        data () {
            return {
		elProductVisibilityOptions: this.productVisibilityOptions,
		elDisableAddingProducts: this.disableAddingProducts,
                elNewProductAskSKU: this.newProductAskSKU,
                elProductVisibility: this.getObjectByKeyValue(this.productVisibilityOptions, 'name', this.productVisibility),
		elNewProductAskTaxClass: this.newProductAskTaxClass,
		elItemTaxClasses: this.itemTaxClasses,
                elItemTaxClass: this.getObjectByKeyValue(this.itemTaxClasses, 'slug', this.itemTaxClass),
                elEditCreatedProductInNewTab: this.editCreatedProductInNewTab,
                elShowProductCategory: this.showProductCategory,
		elNewProductShowWeight: this.newProductShowWeight,
		elNewProductShowLength: this.newProductShowLength,
		elNewProductShowWidth: this.newProductShowWidth,
		elNewProductShowHeight: this.newProductShowHeight,
		elCreateWoocommerceProduct: this.getObjectByKeyValue(this.createWoocommerceProductOptions, 'value', this.createWoocommerceProduct),
              elCreatePrivateProduct: this.createPrivateProduct,
            };
        },
        methods: {
            getSettings () {
                return {
                    disable_adding_products: this.elDisableAddingProducts,
                    new_product_ask_sku: this.elNewProductAskSKU,
                    new_product_ask_tax_class: this.elNewProductAskTaxClass,
                    product_visibility: this.getKeyValueOfObject(this.elProductVisibility, 'name'),
                    item_tax_class: this.getKeyValueOfObject(this.elItemTaxClass, 'slug'),
                    edit_created_product_in_new_tab: this.elEditCreatedProductInNewTab,
                    show_product_category: this.elShowProductCategory,
                    new_product_show_weight: this.elNewProductShowWeight,
                    new_product_show_length: this.elNewProductShowLength,
                    new_product_show_width: this.elNewProductShowWidth,
                    new_product_show_height: this.elNewProductShowHeight,
                    new_product_create_woocommerce_product: this.getKeyValueOfObject(this.elCreateWoocommerceProduct, 'value'),
                    create_private_product: this.elCreatePrivateProduct,
                };
            },
        },
        components: {
            Multiselect,
        },
    }
</script>
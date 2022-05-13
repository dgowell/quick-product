<template>
    <table class="form-table">
        <tbody>
	    <tr>
		<td colspan="2">
		   <b>{{ frontendPageInstructions }}</b>
		</td>
		</tr>
	    <tr>
		<td colspan="2">
		   <a href="https://docs.algolplus.com/algol_phone_order/overview-snippets-phone-orders/" target=_blank>{{ frontendPagePluginsHelpLinkLabel }}</a>
		</td>
		</tr>
	    <tr>
		<td>
		    {{ frontendPageLabel }}
		</td>
		<td>
		    <input type="checkbox" class="option" v-model="elFrontendPage" name="frontend_page">
		</td>
	    </tr>
	    <tr v-show="elFrontendPage">
		<td>
		    {{ frontendPageUrlLabel }}
		</td>
		<td>
		    <input type="text" class="option" v-model="elFrontendPageUrl" name="frontend_page_url">
		</td>
	    </tr>
	    <tr>
		<td>
		    {{ hideThemeHeaderLabel }}
		</td>
		<td>
		    <input type="checkbox" class="option" v-model="elHideThemeHeader" name="frontend_hide_theme_header">
		</td>
	    </tr>
	    <tr>
		<td>
		    {{ hideThemeFooterLabel }}
		</td>
		<td>
		    <input type="checkbox" class="option" v-model="elHideThemeFooter" name="frontend_hide_theme_footer">
		</td>
	    </tr>
	    <tr>
		<td>
		    {{ allowToConfigureProductLabel }}
		</td>
		<td>
		    <input type="checkbox" class="option" v-model="elAllowToConfigureProduct" name="allow_to_configure_product">
		</td>
	    </tr>
	    <tr>
		<td>
		    {{ allowToUseMinifiedPageConfigureProductLabel }}
		</td>
		<td>
		    <input type="checkbox" class="option" v-model="elAllowToUseMinifiedPageConfigureProduct" name="allow_to_use_minified_page_configure_product">
		</td>
	    </tr>
        <tr>
        <td>
		    {{ allowToAddProductsFromShopPageLabel }}
		</td>
		<td>
		    <input type="checkbox" class="option" v-model="elAllowToAddProductsFromShopPage" name="allow_to_add_products_from_shop_page">
		</td>
	    </tr>
	</tbody>
    </table>
</template>

<script>

    export default {
        props: {
	    title: {
                default: function() {
                    return 'Run at frontend';
                },
            },
            frontendPageInstructions: {
                default: function() {
                    return 'The user must be admin or has capability "edit_shop_orders"';
                },
            },
            frontendPagePluginsHelpLinkLabel: {
                default: function() {
                    return 'If you use the plugins to create complex products or to tweak product options - read this section';
                },
            },
            frontendPageLabel: {
                default: function() {
                    return 'Accept orders at frontend page';
                },
            },
            frontendPage: {
                default: function() {
                    return false;
                },
            },
            frontendPageUrlLabel: {
                default: function() {
                    return 'Frontend page url';
                },
            },
            frontendPageUrl: {
                default: function() {
                    return '';
                },
            },
            hideThemeHeaderLabel: {
                default: function() {
                    return 'Hide theme header';
                },
            },
            hideThemeHeader: {
                default: function() {
                    return false;
                },
            },
            hideThemeFooterLabel: {
                default: function() {
                    return 'Hide theme footer';
                },
            },
            hideThemeFooter: {
                default: function() {
                    return false;
                },
            },
            allowToConfigureProductLabel: {
                default: function() {
                    return 'Allow to configure product';
                },
            },
            allowToConfigureProduct: {
                default: function() {
                    return false;
                },
            },
            allowToUseMinifiedPageConfigureProductLabel: {
                default: function() {
                    return 'Button "Configure Product" uses minified page';
                },
            },
            allowToUseMinifiedPageConfigureProduct: {
                default: function() {
                    return false;
                },
            },
            allowToAddProductsFromShopPageLabel: {
                default: function() {
                    return 'Allow to add products from shop page';
                }
            },
            allowToAddProductsFromShopPage: {
                default: function() {
                    return false;
                }
            },
        },
        data () {
            return {
		elFrontendPage: this.frontendPage,
		elFrontendPageUrl: this.frontendPageUrl,
		elHideThemeHeader: this.hideThemeHeader,
		elHideThemeFooter: this.hideThemeFooter,
		elAllowToConfigureProduct: this.allowToConfigureProduct,
        elAllowToUseMinifiedPageConfigureProduct: this.allowToUseMinifiedPageConfigureProduct,
        elAllowToAddProductsFromShopPage: this.allowToAddProductsFromShopPage,
            };
        },
        methods: {
            getSettings () {
                return {
                    frontend_page: this.elFrontendPage,
                    frontend_page_url: this.elFrontendPageUrl,
                    frontend_hide_theme_header: this.elHideThemeHeader,
                    frontend_hide_theme_footer: this.elHideThemeFooter,
                    allow_to_configure_product: this.elAllowToConfigureProduct,
                    allow_to_use_minified_page_configure_product: this.elAllowToUseMinifiedPageConfigureProduct,
                    allow_to_add_products_from_shop_page: this.elAllowToAddProductsFromShopPage,
                };
            },
        },
    }
</script>
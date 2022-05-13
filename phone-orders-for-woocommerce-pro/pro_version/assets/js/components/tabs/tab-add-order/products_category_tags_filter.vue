<template>
    <div class="search_options" v-if="isShow">
	<b-row>
	    <b-col cols="6">
		<label for="product_cat">
		    {{ categoryLabel }}
		</label>
		<multiselect
		    :allow-empty="true"
		    :hide-selected="true"
		    :searchable="false"
		    style="width: 100%;"
		    label="filter_title"
		    v-model="productCategory"
		    :options="categoriesList"
		    track-by="slug"
		    :placeholder="selectProductsCategoryPlaceholder"
		    v-bind:disabled="!cartEnabled"
		    :show-labels="false"
		>
		    <template slot="clear" slot-scope="props">
			<div class="multiselect__clear" v-show="productCategory" @mousedown.prevent.stop="clearProductCategory"></div>
		    </template>
		    <template slot="singleLabel" slot-scope="props">
			<span v-html="props.option.filter_title"></span>
		    </template>
		    <template slot="option" slot-scope="props">
			<span v-html="props.option.filter_title"></span>
		    </template>
		    <template slot="noOptions">
			<span v-html="noOptionsTitle"></span>
		    </template>
		</multiselect>
	    </b-col>
	    <b-col cols="6">
		<label for="tag_search_option">
		    {{ tagLabel }}
		</label>
		<multiselect
		    :allow-empty="true"
		    :hide-selected="true"
		    :searchable="false"
		    style="width: 100%;"
		    label="title"
		    v-model="productTag"
		    :options="tagsList"
		    track-by="value"
		    :placeholder="selectProductsTagPlaceholder"
		    v-bind:disabled="!cartEnabled"
		    :show-labels="false"
		>
		    <template slot="clear" slot-scope="props">
			<div class="multiselect__clear" v-show="productTag" @mousedown.prevent.stop="clearProductTag"></div>
		    </template>
		    <template slot="singleLabel" slot-scope="props">
			<span v-html="props.option.title"></span>
		    </template>
		    <template slot="option" slot-scope="props">
			<span v-html="props.option.title"></span>
		    </template>
		    <template slot="noOptions">
			<span v-html="noOptionsTitle"></span>
		    </template>
		</multiselect>
	    </b-col>
	</b-row>
    </div>
</template>

<style>
    .multiselect__clear {
        position: absolute;
        right: 41px;
        height: 40px;
        width: 40px;
        display: block;
        cursor: pointer;
        z-index: 1;
    }

    .multiselect__clear:before {
        transform: rotate(45deg);
    }

    .multiselect__clear:after {
        transform: rotate(-45deg);
    }

    .multiselect__clear:after, .multiselect__clear:before {
        content: "";
        display: block;
        position: absolute;
        width: 3px;
        height: 16px;
        background: #aaa;
        top: 12px;
        right: 4px;
    }
</style>

<script>

    import Multiselect from 'vue-multiselect';

    export default {
        props: {
            categoryLabel: {
                default: function() {
                    return 'Category';
                }
            },
            selectProductsCategoryPlaceholder: {
                default: function() {
                    return 'Select a category';
                }
            },
            tagLabel: {
                default: function() {
                    return 'Tag';
                }
            },
            selectProductsTagPlaceholder: {
                default: function() {
                    return 'Select a tag';
                }
            },
            tabName: {
                default: function() {
                    return 'add-order';
                }
            },
            noOptionsTitle: {
                default: function() {
                    return 'List is empty.';
                }
            },
        },
        computed: {
            isShow: function () {
                return this.getSettingsOption('search_by_cat_and_tag');
            },
            productCategory: {
		get() {
		    var params = this.getAdditionalParams();
		    return typeof params['category_slug'] === 'undefined' ? null : this.getObjectByKeyValue(this.categoriesList, 'slug', params['category_slug'], null);
		},
		set(newVal) {

		    var params = this.getAdditionalParams();

		    params = JSON.parse(JSON.stringify(params));

		    if (newVal) {
			params['category_slug'] = newVal.slug;
		    } else {
			if (params['category_slug']) {
			    delete params['category_slug'];
			}
		    }

		    this.setAdditionalParams(params);
		},
            },
            productTag: {
		get() {
		    var params = this.getAdditionalParams();
		    return typeof params['tag_slug'] === 'undefined' ? null : this.getObjectByKeyValue(this.tagsList, 'value', params['tag_slug'], null);
		},
		set(newVal) {

		    var params = this.getAdditionalParams();

		    params = JSON.parse(JSON.stringify(params));

		    if (newVal) {
			params['tag_slug'] = newVal.value;
		    } else {
			if (params['tag_slug']) {
			    delete params['tag_slug'];
			}
		    }

		    this.setAdditionalParams(params);
		},
            },
	    categoriesList() {
		return this.$store.state.product_category_tags_filter.categories || [];
	    },
	    tagsList() {
		return this.$store.state.product_category_tags_filter.tags || [];
	    },
        },
        methods: {
            clearProductCategory: function () {
                this.productCategory = null;
            },
            clearProductTag: function () {
                this.productTag = null;
            },
            getAdditionalParams: function () {
                return this.$store.state.add_order.additional_params_product_search || {};
            },
            setAdditionalParams: function (params) {
                this.$store.commit('add_order/setAdditionalParamsProductSearch', params);
            },
        },
        components: {
            Multiselect,
        },
    }
</script>
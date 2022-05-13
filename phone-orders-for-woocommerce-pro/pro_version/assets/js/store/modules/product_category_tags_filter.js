const state = {
    categories: [],
    tags: [],
};

const mutations = {
    setCategories (state, categories) {
        state.categories = categories;
    },
    setTags (state, tags) {
        state.tags = tags;
    },
};

const actions = {
    loadCategories (context, app) {
	app.axios.get(app.url, {params: {
	    action: 'phone-orders-for-woocommerce',
	    method: 'get_products_categories_list',
	    tab: 'add-order',
	    wpo_cache_references_key: app.getSettingsOption('cache_references_session_key'),
	}}).then( ( response ) => {
	    context.commit('setCategories', response.data.data.categories_list);
	});
    },
    loadTags (context, app) {
	app.axios.get(app.url, {params: {
	    action: 'phone-orders-for-woocommerce',
	    method: 'get_products_tags_list',
	    tab: 'add-order',
	    wpo_cache_references_key: app.getSettingsOption('cache_references_session_key'),
	}}).then( ( response ) => {
	    context.commit('setTags', response.data.data.tags_list);
	});
    }
}

var store = {
    namespaced: true,
    state,
    mutations,
    actions,
}

store.init = function (app) {
    app.bus.$on(['settings-loaded', 'settings-saved'], () => {
	if (app.getSettingsOption('search_by_cat_and_tag')
	    || app.getSettingsOption('show_product_category')
	) {
	    this.dispatch('loadCategories', app);
	}
	if (app.getSettingsOption('search_by_cat_and_tag')) {
	    this.dispatch('loadTags', app);
	}
    });
}

export default store
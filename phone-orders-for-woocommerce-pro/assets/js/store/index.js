import Vue  from 'vue'
import Vuex from 'vuex'

import add_order from './modules/order'
import settings from './modules/settings'

var modules = {
    add_order: add_order,
    settings: settings,
};

Vue.use(Vuex);

try {
    modules = Object.assign(modules, require( './../../../pro_version/assets/js/store' ).default);
} catch (e) {}

var store = new Vuex.Store({
    modules,
});

store.init = function (app) {
    this._modules.root.forEachChild((module) => {
	if (typeof module._rawModule.init === 'function') {
	    module._rawModule.init.apply(module.context, [app]);
	}
    })
}

export default store
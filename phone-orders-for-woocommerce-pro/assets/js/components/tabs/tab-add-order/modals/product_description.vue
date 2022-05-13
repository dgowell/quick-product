<template>
    <div>
        <b-modal id="productDescription"
                 ref="modal"
                 :title="title"
                 size="sm"
		 :no-close-on-backdrop="modalDontCloseOnBackdropClick"
		 :static="true"
                 :hide-footer="true"
        >
            <div v-html="description"></div>
        </b-modal>
    </div>
</template>

<script>

    export default {
	created: function () {
	    this.$root.bus.$on('show-product-description', (data) => {
                this.title = data.title;
                this.description = data.description;
                this.$refs.modal.show()
            });
	},
	data: function () {
	    return {
		title: '',
		description: '',
	    };
	},
    }
</script>

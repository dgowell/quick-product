<template>
    <div>
        <b-modal id="addGiftCard"
                 ref="modal"
                 :title="addGiftCardLabel"
                 size="sm"
                 @shown="shown"
		 :no-close-on-backdrop="modalDontCloseOnBackdropClick"
		 :static="true"
        >
            <b-form inline>
                <b-form-input type="text" name="gift-card" v-model.trim="giftCard" ref="giftCard" :placeholder="giftCardPlaceholder" class="col-12"></b-form-input>
            </b-form>
            <div slot="modal-footer">
                <b-button @click="close">{{ cancelLabel }}</b-button>
                <b-button @click="apply" variant="primary" :disabled="!giftCard">{{ applyLabel }}</b-button>
            </div>
        </b-modal>
    </div>
</template>

<style>

    .wpo-gift-card-label__expired {
	color: red;
	font-weight: 600;
    }
</style>

<script>

    export default {
        props: {
	    cancelLabel: {
		default: function () {
		    return 'Cancel';
		}
	    },
	    applyLabel: {
		default: function () {
		    return 'Apply';
		}
	    },
	    addGiftCardLabel: {
		default: function () {
		    return 'Add Gift Card';
		}
	    },
	    giftCardPlaceholder: {
		default: function () {
		    return 'Gift card number';
		}
	    },
        },
	data: function () {
	    return {
		giftCard: null,
	    };
	},
        methods: {
	    apply() {
		this.saveToStore(this.giftCard);
		this.close();
	    },
	    saveToStore(giftCard) {

		if (!giftCard) {
		    return;
		}

		this.$store.commit('add_order/addGiftCard', {card_number: giftCard, title: giftCard});
	    },
	    close() {
		this.$refs.modal.hide();
	    },
	    shown() {
		this.giftCard = null;
		this.$refs.giftCard.$el.focus();
	    },
        },
    }
</script>

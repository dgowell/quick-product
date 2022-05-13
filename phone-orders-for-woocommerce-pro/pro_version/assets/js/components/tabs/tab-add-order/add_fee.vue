<template>
    <tr class="fee-list-add" v-if="isShow">
        <td class="label-total">
            <a href="#" @click.prevent.stop="cartEnabled ? addFee() : null" :class="{disabled: !cartEnabled}">
                {{ addFeeButtonLabel }}
            </a>
            <span class="total-fee-label"></span>
        </td>
        <td width="1%"></td>
        <td>
            <span class="woocommerce-Price-amount fee-value"></span>
        </td>
        <td class="total-value">
            <span class="woocommerce-Price-amount fee-value-no-action"></span>
        </td>
    </tr>
</template>

<script>
    export default {
        props: {
            addFeeButtonLabel: {
                default: function() {
                    return 'Add fee';
                }
            },
        },
        computed: {
            isShow: function () {
                return !this.getSettingsOption('hide_add_fee');
            }
        },
        methods: {
            addFee () {
                this.openModal('addFee');
            },
        },
    }
</script>
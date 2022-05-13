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
                    {{ showGoToCartPageLabel }}
                </td>
                <td>
                    <input type="hidden" name="show_go_to_cart_button" value="">
                    <input type="checkbox" class="option" v-model="elShowGoToCartButton" name="show_go_to_cart_button">
                </td>
            </tr>

            <tr>
                <td>
                    {{ showGoToCheckoutPageLabel }}
                </td>
                <td>
                    <input type="hidden" name="show_go_to_checkout_button" value="">
                    <input type="checkbox" class="option" v-model="elShowGoToCheckoutButton" name="show_go_to_checkout_button">
                </td>
            </tr>

            <tr>
                <td>
                    {{ showCheckoutLinkButtonLabel }}
                </td>
                <td>
                    <input type="checkbox" class="option" v-model="elShowCheckoutLinkButton" name="show_checkout_link_button">
                </td>
            </tr>

            <tr>
                <td>
                    {{ showPaymentLinkButtonLabel }}
                </td>
                <td>
                    <input type="checkbox" class="option" v-model="elShowPaymentLinkButton" name="show_payment_link_button">
                </td>
            </tr>

            <tr>
                <td>
                    {{ overrideProductPriceInCartLabel }}
                </td>
                <td>
                    <input type="checkbox" class="option" v-model="elOverrideProductPriceInCart" name="override_product_price_in_cart">
                </td>
            </tr>
        </tbody>
    </table>
</template>

<style>
</style>

<script>
    export default {
        props: {
            title: {
                default: function() {
                    return 'Checkout at frontend';
                },
            },
            showGoToCartPageLabel: {
                default: function() {
                    return 'Show \'Go to Cart\' button';
                }
            },
            showGoToCheckoutPageLabel: {
                default: function() {
                    return 'Show \'Go to Checkout\' button';
                }
            },
            showCheckoutLinkButtonLabel: {
                default: function() {
                    return 'Show \'Checkout link\' button';
                }
            },
            showPaymentLinkButtonLabel: {
                default: function() {
                    return 'Show \'Payment link\' button';
                }
            },
            overrideProductPriceInCartLabel: {
                default: function() {
                    return 'Pass modified product prices to frontend cart';
                }
            },
            showGoToCartButton: {
                default: function() {
                    return false;
                }
            },
            showGoToCheckoutButton: {
                default: function() {
                    return false;
                }
            },
            showCheckoutLinkButton: {
                default: function() {
                    return false;
                }
            },
            showPaymentLinkButton: {
                default: function() {
                    return false;
                }
            },
            overrideProductPriceInCart: {
                default: function() {
                    return false;
                }
            },
        },
        data () {
            return {
                elShowGoToCartButton: this.showGoToCartButton,
                elShowGoToCheckoutButton: this.showGoToCheckoutButton,
                elShowCheckoutLinkButton: this.showCheckoutLinkButton,
                elShowPaymentLinkButton: this.showPaymentLinkButton,
                elOverrideProductPriceInCart: this.overrideProductPriceInCart,
            };
        },
        methods: {
            getSettings () {
                return {
                    show_go_to_cart_button: this.elShowGoToCartButton,
                    show_go_to_checkout_button: this.elShowGoToCheckoutButton,
                    show_checkout_link_button: this.elShowCheckoutLinkButton,
                    show_payment_link_button: this.elShowPaymentLinkButton,
                    override_product_price_in_cart: this.elOverrideProductPriceInCart,
                };
            },
        },
    }
</script>
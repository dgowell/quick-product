<template>
    <div class="phone-orders-woocommerce_tab-tools phone-orders-woocommerce__tab">
        <iframe ref="report" id="report"></iframe>
        <b-button variant="primary" @click="getReport">{{ buttonLabel }}</b-button>
        <div v-show="isLoading" class="tab-loader">
            <loader></loader>
        </div>
        <div class="note" v-html="noteLabel"></div>
    </div>
</template>

<style>
    .phone-orders-woocommerce_tab-tools #report {
        display: none;
    }
    .phone-orders-woocommerce_tab-tools .tab-loader {
        top: 0;
    }
    .phone-orders-woocommerce_tab-tools .note {
        margin-top: 10px;
        color: red;
    }
</style>

<script>

    var loader = require('vue-spinner/dist/vue-spinner.min').ClipLoader;

    export default {
        props: {
            buttonLabel: {
                default: function() {
                    return 'Get report';
                }
            },
            tabName: {
                default: function() {
                    return 'tools';
                }
            },
            noteLabel: {
                default: function() {
                    return 'You should submit generated report as new ticket to <a href="https://algolplus.freshdesk.com/support/tickets/new" target="_blank">helpdesk</a>';
                }
            },
        },
        data: function () {
            return {
                isLoading: false,
            };
        },
        methods: {
            getReport() {
                this.isLoading = true;
                this.axios.post(this.url, this.qs.stringify({
                    action: 'phone-orders-for-woocommerce',
                    method: 'get_report',
                    tab: this.tabName
                })).then((response) => {
                    this.$refs.report.src = this.url + '?action=phone-orders-for-woocommerce&method=download_report&tab=' + this.tabName;
                    this.isLoading = false;
                });
            }
        },
        components: {
            loader,
        },
    }
</script>

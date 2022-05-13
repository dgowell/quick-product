<template>
    <div class="phone-orders-woocommerce_tab-license phone-orders-woocommerce__tab">
        <div v-show="isRunRequest" class="tab-loader">
            <loader></loader>
        </div>
        <div v-html="licenseHelp"></div>
        <h2 class="custom-header">{{ title }}</h2>
        <table class="form-table">
            <tbody>
                <tr valign="top">
                    <th scope="row" valign="top">
                        {{ licenseKeyLabel }}
                    </th>
                    <td>
                        <input name="edd_wpo_license_key" type="text" class="regular-text" v-model.trim="license" :readonly="isActivatedLicense"/><br>
                        <label class="description" for="edd_wpo_license_key">
                            {{ licenseKeyNote }}
                        </label>
                    </td>
                </tr>
                <tr valign="top">
                    <th valign="top"></th>
                    <td>
                        <div v-if="isActivatedLicense">
                            <span class="phone-orders-woocommerce_tab-license_success">
                                {{ licenseActiveTitle }}
                            </span>
                            <br><br>
                            <input @click="deactivateLicense" type="submit" class="btn btn-primary" name="edd_wpo_license_deactivate" :value="activeLicenseSubmitButtonTitle"/>
                        </div>
                        <div v-else>
                            <div class="phone-orders-woocommerce_tab-license_error">
                                {{ licenseInactiveTitle }}<span v-if="error">:&nbsp;
                                    <span>{{ error }}</span>
                                </span><br><br>
                            </div>
                            <input @click="activateLicense" type="submit" class="btn btn-primary" name="edd_wpo_license_activate" :value="inactiveLicenseSubmitButtonTitle"/>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<style lang="css" src="./../../../css/wc-phone-orders-pro.css"></style>

<style>
    .phone-orders-woocommerce_tab-license_success {
        color: green;
    }

    .phone-orders-woocommerce_tab-license_error {
        color: red;
    }

    .phone-orders-woocommerce_tab-license .form-table th {
        padding: 5px 0;
    }

    .license_paragraph {
        margin : 15px 0 15px 0;
    }

    .license_header {
        font-size: 24px;
    }

    #license_help_text {
        background-color: white;
        border: 1px #b2b2b2 solid;
        border-radius: 5px;
        padding: 15px 20px;
        margin: 5px 0 25px 0;
    }

</style>

<script>

    var loader = require('vue-spinner/dist/vue-spinner.min').ClipLoader;

    export default {
        props: {
            title: {
                default: function() {
                    return 'Plugin License';
                },
            },
            tabName: {
                default: function() {
                    return 'license';
                },
            },
            referrer: {
                default: function() {
                    return '';
                },
            },
            licenseKey: {
                default: function() {
                    return '';
                },
            },
            errorMessage: {
                default: function() {
                    return '';
                },
            },
            licenseStatus: {
                default: function() {
                    return false;
                },
            },
            licenseKeyNote: {
                default: function() {
                    return 'look for it inside purchase receipt (email)';
                },
            },
            licenseKeyLabel: {
                default: function() {
                    return 'License Key';
                },
            },
            licenseActiveTitle: {
                default: function() {
                    return 'License is active';
                },
            },
            licenseInactiveTitle: {
                default: function() {
                    return 'License is inactive';
                },
            },
            activeLicenseSubmitButtonTitle: {
                default: function() {
                    return 'Deactivate License';
                },
            },
            inactiveLicenseSubmitButtonTitle: {
                default: function() {
                    return 'Activate License';
                },
            },
            licenseHelp: {
                default: function() {
                    return '';
                },
            },
        },
        data: function () {
            return {
                status: this.licenseStatus,
                license: this.licenseKey,
                error: this.errorMessage,
                isRunRequest: false,
            };
        },
        computed: {
            isActivatedLicense: function () {
                return this.status !== false && this.status == 'valid';
            },
        },
        methods: {
            requestLicense(method, params) {

                this.isRunRequest = true;

		var data = {
                    action: 'phone-orders-for-woocommerce',
                    method: method,
                    edd_wpo_nonce: this.edd_wpo_nonce,
                    option_page: 'edd_wpo_license',
                    _wp_http_referer: this.referrer,
                    _wpnonce: this.nonce,
                    tab: this.tabName,
		};

                this.axios
		    .post( this.url, this.qs.stringify(Object.assign(data, params || {})) )
		    .then( ( response ) => {
			this.status       = response.data.data.status;
			this.error        = response.data.data.error;
			this.isRunRequest = false;
		    }, () => {
			this.isRunRequest = false;
		    });
            },
            activateLicense() {

		this.requestLicense('activate_license', {
                    edd_wpo_license_key: this.license,
                    edd_wpo_license_activate: this.inactiveLicenseSubmitButtonTitle,
		});
            },
            checkLicense() {

		this.requestLicense('check_license');
            },
            deactivateLicense() {

		this.requestLicense('deactivate_license', {
                    edd_wpo_license_deactivate: this.activeLicenseSubmitButtonTitle,
		});
            },
	    update() {
		this.checkLicense();
	    },
        },
        components: {
            loader,
        },
    }
</script>
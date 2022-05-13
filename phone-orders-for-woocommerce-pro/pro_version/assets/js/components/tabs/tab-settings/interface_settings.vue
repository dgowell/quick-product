<template>
    <tr>
        <td colspan="2">
            <table style="width: 100%">
                <tbody>
                <tr>
                    <td>
                        {{ hideTabsLabel }}
                    </td>
                    <td>
                        <input type="checkbox" class="option" v-model="elHideTabs" name="hide_tabs">
                    </td>
                </tr>
                <tr>
                    <td>
                        {{ useEnglishInterfaceLabel }}
                    </td>
                    <td>
                        <input type="checkbox" class="option" v-model="elUseEnglishInterface" name="use_english_interface">
                    </td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
</template>

<script>

    import Multiselect from 'vue-multiselect';

    export default {
        props: {
            hideTabsLabel: {
                default: function () {
                    return 'Hide tabs for non-admins';
                },
            },
            hideTabs: {
                default: function () {
                    return false;
                },
            },
            useEnglishInterfaceLabel: {
                default: function () {
                    return 'Use interface in English';
                },
            },
            useEnglishInterface: {
                default: function () {
                    return false;
                },
            },
        },
        data() {
            return {
                elHideTabs: this.hideTabs,
                elUseEnglishInterface: this.useEnglishInterface,
            };
        },
        methods: {
            getSettings() {
                return {
                    hide_tabs: this.elHideTabs,
                    use_english_interface: this.elUseEnglishInterface,
                };
            },
        },
        components: {
            Multiselect,
        },
    }
</script>
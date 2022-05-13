<template>
    <div class="time-picker">
        <input type="number"
               @blur="store"
               class="hour"
               :placeholder="hourPlaceholder"
               v-model="hour"
               name="hour"
               min="0"
               max="23"
               step="1"
               pattern="([01]?[0-9]{1}|2[0-3]{1})"
               v-bind:disabled="this.disabled"
        />
        :
        <input type="number"
               @blur="store"
               class="minute"
               :placeholder="minutePlaceholder"
               name="minute"
               min="0"
               max="59"
               step="1"
               v-model="minute"
               pattern="[0-5]{1}[0-9]{1}"
               v-bind:disabled="this.disabled"
        />
        <input type="hidden" name="second" v-model="second"/>
    </div>
</template>

<script>

    import moment from 'moment'

    export default {
        props: {
            value: {
                default: function () {
                    return '';
                }
            },
            disabled: {
                default: function () {
                    return false;
                }
            },
            hourPlaceholder: {
                default: function () {
                    return 'h';
                }
            },
            minutePlaceholder: {
                default: function () {
                    return 'm';
                }
            },
        },
        watch: {
            value: function (newVal) {
                this.time = moment(newVal, 'HH:mm:ss');
                if (!this.time.isValid()) {
                    this.time = moment();
                }
                this.second = 0;

                this.store();
            },
        },
        created: function () {
            this.time = moment(this.value, 'HH:mm:ss');
            this.second = 0;

            this.store();
        },
        computed: {
            hour: {
                get: function () {
                    return this.formatAsTwoDigits(this.time.hours().valueOf());
                },
                set: function (newVal) {
                    this.time.hours(newVal)
                },
            },
            minute: {
                get: function () {
                    return this.formatAsTwoDigits(this.time.minutes().valueOf());
                },
                set: function (newVal) {
                    this.time.minute(newVal)
                },
            },
            second: {
                get: function () {
                    return '00';
                },
                set: function (newVal) {
                    this.time.seconds(newVal);
                },
            },
        },
        data: function () {
            return {
                time: moment(),
            };
        },
        methods: {
            store: function () {
                this.$emit('input', this.time.format('HH:mm:ss'))
            },
            formatAsTwoDigits: function (value) {
                return value >= 10 ? value.toString() : '0' + value.toString();
            },
        },
    }
</script>
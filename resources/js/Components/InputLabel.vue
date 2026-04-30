<script setup>
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

defineProps({
    value: String,
    required: Boolean,
});
</script>

<template>
    <label class="block font-medium text-sm text-gray-700">
        <span v-if="value">{{ value }}
            <span v-if="required" class="text-red-500 ml-1 text-xs">{{ t('require') }}</span>
        </span>
        <span v-else><slot /></span>
    </label>
</template>

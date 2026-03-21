<script setup>
import AppLayout from '@/Layouts/Admin/AppLayout.vue';
import DashboardChartCompact from '@/Components/Dashboard/DashboardChartCompact.vue';
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ t('dashboard') }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <DashboardChartCompact />
                </div>
            </div>
        </div>
    </AppLayout>
</template>

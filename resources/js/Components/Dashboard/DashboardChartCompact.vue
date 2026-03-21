<template>
  <div class="max-w-6xl mx-auto">
    <!-- 数字カードをレスポンシブに横並び -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
      <!-- Pre-register 今日 -->
      <div class="p-4 bg-white rounded shadow text-center">
        <h3 class="text-gray-500 text-sm">{{ t('pre-register') }} 今日</h3>
        <p class="text-xl font-bold text-green-600">{{ preRegister.today }}</p>
      </div>
      <!-- Pre-register 今月 -->
      <div class="p-4 bg-white rounded shadow text-center">
        <h3 class="text-gray-500 text-sm">{{ t('pre-register') }} 今月</h3>
        <p class="text-xl font-bold text-orange-600">{{ preRegister.month }}</p>
      </div>
      <!-- Members 今日 -->
      <div class="p-4 bg-white rounded shadow text-center">
        <h3 class="text-gray-500 text-sm">{{ t('member') }} 今日</h3>
        <p class="text-xl font-bold text-blue-600">{{ member.today }}</p>
      </div>
      <!-- Members 今月 -->
      <div class="p-4 bg-white rounded shadow text-center">
        <h3 class="text-gray-500 text-sm">{{ t('member') }} 今月</h3>
        <p class="text-xl font-bold text-purple-600">{{ member.month }}</p>
      </div>
    </div>

    <!-- 棒グラフ -->
    <div class="bg-white rounded shadow p-4">
      <bar-chart :chart-data="chartData" :chart-options="chartOptions" />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js';
import { Bar } from 'vue-chartjs';
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

const preRegister = ref({ today: 0, month: 0 });
const member = ref({ today: 0, month: 0 });

const chartData = ref({
  labels: ['今日', '今月'],
  datasets: [
    {
      label: 'Pre-register',
      data: [0, 0],
      backgroundColor: ['#42b983', '#ffa500'],
    },
    {
      label: 'Members',
      data: [0, 0],
      backgroundColor: ['#1e90ff', '#8a2be2'],
    },
  ],
});

const chartOptions = {
  responsive: true,
  plugins: {
    legend: { position: 'top' },
    title: { display: true, text: '今日・今月 申込数比較' },
  },
};

onMounted(async () => {
  const res = await fetch('/api/dashboard-counts');
  const data = await res.json();

  preRegister.value = data.preRegister;
  member.value = data.member;

  chartData.value.datasets[0].data = [data.preRegister.today, data.preRegister.month];
  chartData.value.datasets[1].data = [data.member.today, data.member.month];
});
</script>
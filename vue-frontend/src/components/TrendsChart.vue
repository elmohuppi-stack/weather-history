<template>
  <div class="trends-chart-container bg-white rounded-lg shadow-lg p-6">
    <div class="mb-6">
      <h3 class="text-lg font-semibold text-gray-800 mb-4">Trend-Analyse</h3>

      <!-- Trend Parameter Selector -->
      <div class="flex flex-wrap gap-3 mb-6">
        <button
          v-for="param in parameters"
          :key="param.value"
          @click="selectedParameter = param.value"
          :class="[
            'px-4 py-2 rounded-lg font-medium transition-colors',
            selectedParameter === param.value
              ? 'bg-blue-600 text-white'
              : 'bg-gray-200 text-gray-700 hover:bg-gray-300',
          ]"
        >
          <i :class="['pi mr-2', param.icon]"></i>
          {{ param.label }}
        </button>
      </div>

      <!-- Trend Statistics -->
      <div
        v-if="trendData"
        class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 bg-gray-50 rounded-lg p-4"
      >
        <div>
          <p class="text-sm text-gray-600">Trend pro Jahr</p>
          <p
            :class="[
              'text-2xl font-bold',
              trendData.trend_per_year >= 0 ? 'text-red-600' : 'text-blue-600',
            ]"
          >
            {{ formatTrendValue(trendData.trend_per_year) }}/Jahr
          </p>
        </div>
        <div>
          <p class="text-sm text-gray-600">Trend pro Dekade</p>
          <p
            :class="[
              'text-2xl font-bold',
              trendData.trend_per_decade >= 0
                ? 'text-red-600'
                : 'text-blue-600',
            ]"
          >
            {{ formatTrendValue(trendData.trend_per_decade) }}/10J
          </p>
        </div>
        <div>
          <p class="text-sm text-gray-600">Zeitraum</p>
          <p class="text-2xl font-bold text-gray-800">
            {{ trendData.years_count }} Jahre
          </p>
        </div>
      </div>
    </div>

    <!-- Chart -->
    <div v-if="chartData" class="chart-wrapper">
      <Line :data="chartData" :options="chartOptions" />
    </div>

    <div v-else class="bg-gray-100 rounded-lg p-8 text-center text-gray-600">
      <i class="pi pi-spin pi-spinner text-3xl mb-3"></i>
      <p>Trend-Daten werden geladen...</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, computed } from "vue";
import { Line } from "vue-chartjs";
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler,
} from "chart.js";
import { apiService } from "@/services/api";

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler,
);

interface Props {
  stationId: string;
}

const props = defineProps<Props>();

const selectedParameter = ref<"temperature" | "precipitation" | "sunshine">(
  "temperature",
);
const trendData = ref<any>(null);
const chartData = ref<any>(null);
const loading = ref(false);
const error = ref<string | null>(null);

const parameters = [
  {
    value: "temperature" as const,
    label: "Temperatur",
    icon: "pi-thermometer",
  },
  {
    value: "precipitation" as const,
    label: "Niederschlag",
    icon: "pi-cloud-rain",
  },
  { value: "sunshine" as const, label: "Sonnenschein", icon: "pi-sun" },
];

const chartOptions = computed(() => {
  return {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        display: true,
        labels: {
          usePointStyle: true,
          padding: 15,
          font: { size: 12 },
        },
      },
      tooltip: {
        mode: "index" as const,
        intersect: false,
        backgroundColor: "rgba(0, 0, 0, 0.8)",
        titleFont: { size: 12, weight: "bold" as const },
        bodyFont: { size: 11 },
        padding: 10,
        displayColors: true,
        borderColor: "rgba(255, 255, 255, 0.2)",
        borderWidth: 1,
      },
    },
    scales: {
      y: {
        title: {
          display: true,
          text:
            selectedParameter.value === "temperature"
              ? "Temperatur (°C)"
              : selectedParameter.value === "precipitation"
                ? "Niederschlag (mm)"
                : "Sonnenschein (h)",
        },
        grid: {
          drawBorder: false,
          color: "rgba(0, 0, 0, 0.05)",
        },
      },
      x: {
        grid: {
          display: false,
        },
      },
    },
  };
});

const formatTrendValue = (value: number): string => {
  if (selectedParameter.value === "temperature") {
    return (value >= 0 ? "+" : "") + value.toFixed(2) + "°C";
  } else if (selectedParameter.value === "precipitation") {
    return (value >= 0 ? "+" : "") + value.toFixed(1) + "mm";
  } else {
    return (value >= 0 ? "+" : "") + value.toFixed(1) + "h";
  }
};

const loadTrendData = async () => {
  if (!props.stationId) return;

  loading.value = true;
  error.value = null;

  try {
    const response = await apiService.getTrends({
      parameter: selectedParameter.value,
      station_id: props.stationId,
    });

    if (!response.success || !response.data) {
      throw new Error("Trend-Daten konnten nicht geladen werden.");
    }

    const data = response.data;
    const analysis = data.analysis;

    trendData.value = {
      trend_per_year: analysis?.rate_per_year || 0,
      trend_per_decade: analysis?.rate_per_decade || 0,
      years_count: Object.keys(data.annual_values || {}).length,
    };

    // Convert annual_values object to arrays
    const annualValues = data.annual_values || {};
    const years = Object.keys(annualValues)
      .map((y) => parseInt(y))
      .sort((a, b) => a - b);
    const values = years.map((y) => annualValues[y]);

    // Calculate trend line
    if (values.length >= 2) {
      const n = values.length;
      const x = Array.from({ length: n }, (_, i) => i);
      const xSum = x.reduce((a, b) => a + b, 0);
      const ySum = values.reduce((a, b) => a + b, 0);
      const xySum = x.reduce((sum, xi, i) => sum + xi * values[i], 0);
      const x2Sum = x.reduce((sum, xi) => sum + xi * xi, 0);

      const denominator = n * x2Sum - xSum * xSum;
      let trendLine: number[] = [];

      if (denominator !== 0) {
        const slope = (n * xySum - xSum * ySum) / denominator;
        const intercept = (ySum - slope * xSum) / n;
        trendLine = x.map((xi) => intercept + slope * xi);
      }

      chartData.value = {
        labels: years.map((y) => y.toString()),
        datasets: [
          {
            label: "Jahreswerte",
            data: values,
            borderColor: "rgb(59, 130, 246)",
            backgroundColor: "rgba(59, 130, 246, 0.1)",
            borderWidth: 2,
            pointRadius: 4,
            pointBackgroundColor: "rgb(59, 130, 246)",
            pointBorderColor: "#fff",
            pointBorderWidth: 2,
            pointHoverRadius: 6,
            tension: 0.2,
            fill: true,
          },
          {
            label: "Trend-Linie",
            data: trendLine,
            borderColor: "rgb(239, 68, 68)",
            borderWidth: 2,
            borderDash: [5, 5],
            pointRadius: 0,
            pointHoverRadius: 0,
            tension: 0.2,
            fill: false,
          },
        ],
      };
    }
  } catch (err) {
    console.error("Error loading trend data:", err);
    error.value =
      err instanceof Error
        ? err.message
        : "Trend-Daten konnten nicht geladen werden.";
    trendData.value = null;
    chartData.value = null;
  } finally {
    loading.value = false;
  }
};

// Load trends when parameter changes
watch(selectedParameter, () => {
  loadTrendData();
});

// Load trends initially
watch(
  () => props.stationId,
  () => {
    if (props.stationId) {
      loadTrendData();
    }
  },
  { immediate: true },
);
</script>

<style scoped>
.trends-chart-container {
  animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

.chart-wrapper {
  position: relative;
  height: 400px;
}
</style>

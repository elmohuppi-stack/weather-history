<template>
  <div class="charts-view">
    <div class="mb-12">
      <h1
        class="text-4xl md:text-5xl font-bold text-gray-900 mb-6 tracking-tight"
      >
        Wetter <span class="text-gradient">Diagramme</span>
      </h1>
      <p class="text-xl text-gray-600 max-w-3xl leading-relaxed">
        Hier werden die importierten Messreihen der ausgewählten Station
        gezeigt: Temperatur, Niederschlag und Sonnenscheindauer.
      </p>
    </div>

    <div class="card mb-8">
      <div class="card-header">
        <h3 class="text-xl font-bold text-gray-900">Diagramm-Konfiguration</h3>
        <p class="text-sm text-gray-500 mt-1">
          Station, Zeitraum und Aggregation auswählen
        </p>
      </div>
      <div class="card-body">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2"
              >Station</label
            >
            <select v-model="selectedStationId" class="select w-full">
              <option v-for="item in stations" :key="item.id" :value="item.id">
                {{ item.name }} ({{ item.id }})
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2"
              >Zeitraum</label
            >
            <select v-model="selectedPeriod" class="select w-full">
              <option value="365">Letzte 12 Monate</option>
              <option value="1825">Letzte 5 Jahre</option>
              <option value="all">Gesamter Import</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2"
              >Aggregation</label
            >
            <select v-model="selectedAggregation" class="select w-full">
              <option value="daily">Täglich</option>
              <option value="monthly">Monatlich</option>
              <option value="yearly">Jährlich</option>
            </select>
          </div>
          <div class="flex items-end">
            <button
              @click="reloadMeasurements"
              class="btn-primary w-full"
              :disabled="loading"
            >
              <i class="pi pi-refresh mr-2"></i>
              Diagramme aktualisieren
            </button>
          </div>
        </div>
      </div>
    </div>

    <div v-if="loading" class="text-center py-16">
      <i class="pi pi-spin pi-spinner text-4xl text-blue-600"></i>
      <p class="mt-4 text-gray-600">Lade Diagrammdaten...</p>
    </div>

    <div
      v-else-if="error"
      class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-4 mb-8"
    >
      {{ error }}
    </div>

    <template v-else>
      <div
        v-if="selectedStation"
        class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8"
      >
        <div class="bg-white rounded-lg shadow p-4">
          <p class="text-sm text-gray-500">Station</p>
          <p class="text-lg font-semibold text-gray-900">
            {{ selectedStation.name }}
          </p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
          <p class="text-sm text-gray-500">Geladene Messungen</p>
          <p class="text-lg font-semibold text-gray-900">
            {{ formatNumber(filteredMeasurements.length) }}
          </p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
          <p class="text-sm text-gray-500">Durchschnittstemperatur</p>
          <p class="text-lg font-semibold text-gray-900">
            {{ formatStat(summary.avgTemp, "°C") }}
          </p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
          <p class="text-sm text-gray-500">Gesamtniederschlag</p>
          <p class="text-lg font-semibold text-gray-900">
            {{ formatStat(summary.totalPrecipitation, "mm") }}
          </p>
        </div>
      </div>

      <div
        v-if="chartPoints.length"
        class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12"
      >
        <div class="card hover-lift">
          <div class="card-header">
            <div class="flex items-center justify-between">
              <h3 class="text-xl font-bold text-gray-900">Temperaturverlauf</h3>
              <div
                class="flex items-center space-x-2 text-sm font-medium text-gray-700"
              >
                <i class="pi pi-thermometer text-red-500"></i>
                <span>{{ selectedAggregationLabel }}</span>
              </div>
            </div>
          </div>
          <div class="card-body">
            <div class="h-80">
              <Line :data="temperatureChartData" :options="chartOptions" />
            </div>
          </div>
        </div>

        <div class="card hover-lift">
          <div class="card-header">
            <div class="flex items-center justify-between">
              <h3 class="text-xl font-bold text-gray-900">
                Niederschlagsmengen
              </h3>
              <div
                class="flex items-center space-x-2 text-sm font-medium text-gray-700"
              >
                <i class="pi pi-cloud-rain text-blue-500"></i>
                <span>{{ selectedAggregationLabel }}</span>
              </div>
            </div>
          </div>
          <div class="card-body">
            <div class="h-80">
              <Bar :data="precipitationChartData" :options="barChartOptions" />
            </div>
          </div>
        </div>

        <div class="card hover-lift lg:col-span-2">
          <div class="card-header">
            <div class="flex items-center justify-between">
              <h3 class="text-xl font-bold text-gray-900">Sonnenscheindauer</h3>
              <div
                class="flex items-center space-x-2 text-sm font-medium text-gray-700"
              >
                <i class="pi pi-sun text-yellow-500"></i>
                <span>{{ selectedAggregationLabel }}</span>
              </div>
            </div>
          </div>
          <div class="card-body">
            <div class="h-96">
              <Line :data="sunshineChartData" :options="chartOptions" />
            </div>
          </div>
        </div>
      </div>

      <div
        v-else
        class="bg-white rounded-lg shadow p-8 text-center text-gray-600"
      >
        Für die ausgewählte Station sind aktuell keine Diagrammdaten verfügbar.
      </div>
    </template>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { Bar, Line } from "vue-chartjs";
import {
  BarElement,
  CategoryScale,
  Chart as ChartJS,
  Filler,
  Legend,
  LineElement,
  LinearScale,
  PointElement,
  Title,
  Tooltip,
} from "chart.js";
import { apiService, type Measurement, type Station } from "@/services/api";

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  Title,
  Tooltip,
  Legend,
  Filler,
);

type Aggregation = "daily" | "monthly" | "yearly";

type ChartPoint = {
  label: string;
  count: number;
  tempMean: number | null;
  tempMin: number | null;
  tempMax: number | null;
  precipitation: number;
  sunshine: number;
};

const route = useRoute();
const router = useRouter();

const stations = ref<Station[]>([]);
const measurements = ref<Measurement[]>([]);
const selectedStationId = ref("");
const selectedPeriod = ref("365");
const selectedAggregation = ref<Aggregation>("monthly");
const loading = ref(true);
const error = ref<string | null>(null);

const selectedStation = computed(
  () =>
    stations.value.find((station) => station.id === selectedStationId.value) ??
    null,
);

const selectedAggregationLabel = computed(() => {
  switch (selectedAggregation.value) {
    case "daily":
      return "Tägliche Werte";
    case "yearly":
      return "Jährliche Werte";
    default:
      return "Monatliche Werte";
  }
});

const filteredMeasurements = computed(() => {
  const sorted = [...measurements.value].sort(
    (a, b) => new Date(a.date).getTime() - new Date(b.date).getTime(),
  );

  if (selectedPeriod.value === "all") {
    return sorted;
  }

  const days = Number(selectedPeriod.value);
  const cutoff = new Date();
  cutoff.setDate(cutoff.getDate() - days);

  return sorted.filter((entry) => new Date(entry.date) >= cutoff);
});

const chartPoints = computed<ChartPoint[]>(() => {
  const groups = new Map<
    string,
    {
      label: string;
      count: number;
      tempMeanSum: number;
      tempMeanCount: number;
      tempMinSum: number;
      tempMinCount: number;
      tempMaxSum: number;
      tempMaxCount: number;
      precipitation: number;
      sunshine: number;
    }
  >();

  for (const item of filteredMeasurements.value) {
    const date = new Date(item.date);
    const key =
      selectedAggregation.value === "yearly"
        ? `${date.getFullYear()}`
        : selectedAggregation.value === "monthly"
          ? `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}`
          : `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}-${String(date.getDate()).padStart(2, "0")}`;

    if (!groups.has(key)) {
      groups.set(key, {
        label: key,
        count: 0,
        tempMeanSum: 0,
        tempMeanCount: 0,
        tempMinSum: 0,
        tempMinCount: 0,
        tempMaxSum: 0,
        tempMaxCount: 0,
        precipitation: 0,
        sunshine: 0,
      });
    }

    const group = groups.get(key)!;
    group.count += 1;

    const tempMean = item.temp_mean !== null ? Number(item.temp_mean) : null;
    const tempMin = item.temp_min !== null ? Number(item.temp_min) : null;
    const tempMax = item.temp_max !== null ? Number(item.temp_max) : null;
    const precipitation =
      item.precipitation !== null ? Number(item.precipitation) : 0;
    const sunshine = item.sunshine !== null ? Number(item.sunshine) : 0;

    if (tempMean !== null && !Number.isNaN(tempMean)) {
      group.tempMeanSum += tempMean;
      group.tempMeanCount += 1;
    }

    if (tempMin !== null && !Number.isNaN(tempMin)) {
      group.tempMinSum += tempMin;
      group.tempMinCount += 1;
    }

    if (tempMax !== null && !Number.isNaN(tempMax)) {
      group.tempMaxSum += tempMax;
      group.tempMaxCount += 1;
    }

    group.precipitation += precipitation;
    group.sunshine += sunshine;
  }

  return Array.from(groups.values()).map((group) => ({
    label: group.label,
    count: group.count,
    tempMean: group.tempMeanCount
      ? Number((group.tempMeanSum / group.tempMeanCount).toFixed(1))
      : null,
    tempMin: group.tempMinCount
      ? Number((group.tempMinSum / group.tempMinCount).toFixed(1))
      : null,
    tempMax: group.tempMaxCount
      ? Number((group.tempMaxSum / group.tempMaxCount).toFixed(1))
      : null,
    precipitation: Number(group.precipitation.toFixed(1)),
    sunshine: Number(group.sunshine.toFixed(1)),
  }));
});

const summary = computed(() => {
  const temps = filteredMeasurements.value
    .map((item) => (item.temp_mean !== null ? Number(item.temp_mean) : null))
    .filter((value): value is number => value !== null && !Number.isNaN(value));

  const totalPrecipitation = filteredMeasurements.value.reduce(
    (sum, item) =>
      sum + (item.precipitation !== null ? Number(item.precipitation) : 0),
    0,
  );

  const totalSunshine = filteredMeasurements.value.reduce(
    (sum, item) => sum + (item.sunshine !== null ? Number(item.sunshine) : 0),
    0,
  );

  return {
    avgTemp: temps.length
      ? temps.reduce((sum, value) => sum + value, 0) / temps.length
      : null,
    totalPrecipitation,
    totalSunshine,
  };
});

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  interaction: {
    mode: "index" as const,
    intersect: false,
  },
  plugins: {
    legend: {
      position: "top" as const,
    },
  },
  scales: {
    y: {
      beginAtZero: false,
    },
  },
};

const barChartOptions = {
  ...chartOptions,
  scales: {
    y: {
      beginAtZero: true,
    },
  },
};

const temperatureChartData = computed(() => ({
  labels: chartPoints.value.map((point) => point.label),
  datasets: [
    {
      label: "Mitteltemperatur",
      data: chartPoints.value.map((point) => point.tempMean),
      borderColor: "#ef4444",
      backgroundColor: "rgba(239, 68, 68, 0.15)",
      fill: true,
      tension: 0.25,
    },
    {
      label: "Minimum",
      data: chartPoints.value.map((point) => point.tempMin),
      borderColor: "#3b82f6",
      backgroundColor: "rgba(59, 130, 246, 0.08)",
      tension: 0.25,
    },
    {
      label: "Maximum",
      data: chartPoints.value.map((point) => point.tempMax),
      borderColor: "#f59e0b",
      backgroundColor: "rgba(245, 158, 11, 0.08)",
      tension: 0.25,
    },
  ],
}));

const precipitationChartData = computed(() => ({
  labels: chartPoints.value.map((point) => point.label),
  datasets: [
    {
      label: "Niederschlag",
      data: chartPoints.value.map((point) => point.precipitation),
      backgroundColor: "rgba(59, 130, 246, 0.7)",
      borderColor: "#2563eb",
      borderWidth: 1,
    },
  ],
}));

const sunshineChartData = computed(() => ({
  labels: chartPoints.value.map((point) => point.label),
  datasets: [
    {
      label: "Sonnenscheindauer",
      data: chartPoints.value.map((point) => point.sunshine),
      borderColor: "#f59e0b",
      backgroundColor: "rgba(245, 158, 11, 0.18)",
      fill: true,
      tension: 0.25,
    },
  ],
}));

const loadStations = async () => {
  const response = await apiService.getStations({ per_page: 500 });

  if (!response.success) {
    throw new Error("Stationsliste konnte nicht geladen werden.");
  }

  stations.value = response.data || [];

  const queryStation =
    typeof route.query.station === "string" ? route.query.station : "";
  const hasQueryStation = stations.value.some(
    (station) => station.id === queryStation,
  );

  selectedStationId.value = hasQueryStation
    ? queryStation
    : stations.value[0]?.id || "";
};

const reloadMeasurements = async () => {
  if (!selectedStationId.value) {
    measurements.value = [];
    return;
  }

  loading.value = true;
  error.value = null;

  try {
    const response = await apiService.getMeasurementsByStation(
      selectedStationId.value,
      { per_page: 20000 },
    );

    if (!response.success) {
      throw new Error("Messwerte konnten nicht geladen werden.");
    }

    measurements.value = response.data || [];
    router.replace({
      query: { ...route.query, station: selectedStationId.value },
    });
  } catch (err) {
    console.error("Error loading chart data:", err);
    error.value =
      err instanceof Error
        ? err.message
        : "Messwerte konnten nicht geladen werden.";
    measurements.value = [];
  } finally {
    loading.value = false;
  }
};

watch(
  () => route.query.station,
  (stationId) => {
    if (
      typeof stationId === "string" &&
      stationId &&
      stationId !== selectedStationId.value
    ) {
      selectedStationId.value = stationId;
      reloadMeasurements();
    }
  },
);

watch(selectedStationId, (stationId, previous) => {
  if (stationId && stationId !== previous) {
    reloadMeasurements();
  }
});

const formatNumber = (value: number) => value.toLocaleString("de-DE");

const formatStat = (value: number | null, unit: string) => {
  if (value === null || Number.isNaN(value)) {
    return "–";
  }

  return `${value.toFixed(1)} ${unit}`;
};

const initialize = async () => {
  loading.value = true;
  error.value = null;

  try {
    await loadStations();
    await reloadMeasurements();
  } catch (err) {
    console.error("Error initializing charts page:", err);
    error.value =
      err instanceof Error
        ? err.message
        : "Diagrammdaten konnten nicht geladen werden.";
  } finally {
    loading.value = false;
  }
};

initialize();
</script>

<style scoped>
.charts-view {
  min-height: calc(100vh - 64px);
}
</style>

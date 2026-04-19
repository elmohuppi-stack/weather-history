<template>
  <div class="station-detail-view">
    <div class="container mx-auto px-4 py-8">
      <router-link
        to="/stations"
        class="inline-flex items-center text-blue-600 hover:text-blue-800 mb-4"
      >
        <i class="pi pi-arrow-left mr-2"></i>
        <span>Zurück zu Stationen</span>
      </router-link>

      <div v-if="loading" class="text-center py-12">
        <i class="pi pi-spin pi-spinner text-4xl text-blue-600"></i>
        <p class="mt-4 text-gray-600">Lade Stationsdetails...</p>
      </div>

      <div
        v-else-if="error"
        class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-4"
      >
        {{ error }}
      </div>

      <div v-else-if="station" class="space-y-8">
        <div class="bg-white rounded-lg shadow-lg p-6">
          <div
            class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4 mb-6"
          >
            <div>
              <h1 class="text-3xl font-bold text-gray-800">
                {{ station.name }}
              </h1>
              <p class="text-gray-600 mt-2">
                {{
                  station.location ||
                  station.state ||
                  "Keine Ortsangabe verfügbar"
                }}
              </p>
              <p class="text-sm text-gray-500 mt-1">
                Stations-ID: {{ station.id }}
              </p>
            </div>

            <div class="flex flex-col sm:flex-row gap-2">
              <div
                class="inline-flex items-center px-4 py-2 bg-blue-100 text-blue-800 rounded-full"
              >
                <i class="pi pi-map-marker mr-2"></i>
                <span>Höhe: {{ station.elevation ?? "–" }} m</span>
              </div>
              <button
                @click="viewCharts"
                class="px-4 py-2 bg-blue-600 text-white rounded-full hover:bg-blue-700 transition-colors"
              >
                <i class="pi pi-chart-line mr-2"></i>
                Diagramme öffnen
              </button>
            </div>
          </div>

          <div
            class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-8"
          >
            <div class="bg-gray-50 rounded-lg p-4">
              <p class="text-sm text-gray-600">Daten verfügbar ab</p>
              <p class="text-lg font-semibold text-gray-900">
                {{ station.start_year || "–" }}
              </p>
            </div>

            <div class="bg-gray-50 rounded-lg p-4">
              <p class="text-sm text-gray-600">Messungen</p>
              <p class="text-lg font-semibold text-gray-900">
                {{ formatNumber(station.measurement_count) }}
              </p>
            </div>

            <div class="bg-gray-50 rounded-lg p-4">
              <p class="text-sm text-gray-600">Letzter Messtag</p>
              <p class="text-lg font-semibold text-gray-900">
                {{ formatDate(station.latest_date) }}
              </p>
            </div>

            <div class="bg-gray-50 rounded-lg p-4">
              <p class="text-sm text-gray-600">Status</p>
              <p
                class="text-lg font-semibold"
                :class="station.active ? 'text-green-700' : 'text-gray-700'"
              >
                {{ station.active ? "Aktiv" : "Inaktiv" }}
              </p>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8 text-sm">
            <div class="bg-slate-50 rounded-lg p-4">
              <p class="text-gray-500">Bundesland</p>
              <p class="font-medium text-gray-900 mt-1">
                {{ station.state || "–" }}
              </p>
            </div>
            <div class="bg-slate-50 rounded-lg p-4">
              <p class="text-gray-500">Koordinaten</p>
              <p class="font-medium text-gray-900 mt-1">
                {{ station.lat }}, {{ station.lon }}
              </p>
            </div>
          </div>

          <div class="border-t pt-6">
            <div class="flex items-center justify-between mb-4">
              <div>
                <h2 class="text-xl font-semibold text-gray-800">
                  Letzte importierte Wetterdaten
                </h2>
                <p class="text-sm text-gray-500">
                  Temperatur, Niederschlag und Sonnenschein aus der Datenbank
                </p>
              </div>
            </div>

            <div v-if="recentMeasurements.length" class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th
                      class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase"
                    >
                      Datum
                    </th>
                    <th
                      class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase"
                    >
                      Mittel
                    </th>
                    <th
                      class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase"
                    >
                      Min
                    </th>
                    <th
                      class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase"
                    >
                      Max
                    </th>
                    <th
                      class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase"
                    >
                      Niederschlag
                    </th>
                    <th
                      class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase"
                    >
                      Sonne
                    </th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                  <tr
                    v-for="measurement in recentMeasurements"
                    :key="measurement.date"
                  >
                    <td class="px-4 py-3 text-sm text-gray-900">
                      {{ formatDate(measurement.date) }}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">
                      {{ formatValue(measurement.temp_mean, "°C") }}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">
                      {{ formatValue(measurement.temp_min, "°C") }}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">
                      {{ formatValue(measurement.temp_max, "°C") }}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">
                      {{ formatValue(measurement.precipitation, "mm") }}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">
                      {{ formatValue(measurement.sunshine, "h") }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div
              v-else
              class="bg-gray-50 rounded-lg p-6 text-center text-gray-600"
            >
              Für diese Station wurden noch keine Messwerte gefunden.
            </div>
          </div>

          <!-- Climate Normals Section (1991-2020 Averages) -->
          <div class="border-t pt-6" v-if="climateNormals">
            <div class="flex items-center justify-between mb-4">
              <div>
                <h2 class="text-xl font-semibold text-gray-800">
                  Klimamittel (1991-2020)
                </h2>
                <p class="text-sm text-gray-500">
                  Monatliche Durchschnittswerte basierend auf 30 Jahren
                </p>
              </div>
            </div>

            <div
              v-if="climateNormals.monthly && climateNormals.monthly.length > 0"
              class="overflow-x-auto"
            >
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-blue-50">
                  <tr>
                    <th
                      class="px-4 py-3 text-left text-xs font-semibold text-blue-600 uppercase"
                    >
                      Monat
                    </th>
                    <th
                      class="px-4 py-3 text-left text-xs font-semibold text-blue-600 uppercase"
                    >
                      Temp. Ø (°C)
                    </th>
                    <th
                      class="px-4 py-3 text-left text-xs font-semibold text-blue-600 uppercase"
                    >
                      Max. Ø (°C)
                    </th>
                    <th
                      class="px-4 py-3 text-left text-xs font-semibold text-blue-600 uppercase"
                    >
                      Min. Ø (°C)
                    </th>
                    <th
                      class="px-4 py-3 text-left text-xs font-semibold text-blue-600 uppercase"
                    >
                      Niederschlag (mm)
                    </th>
                    <th
                      class="px-4 py-3 text-left text-xs font-semibold text-blue-600 uppercase"
                    >
                      Sonne (h)
                    </th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                  <tr
                    v-for="month in climateNormals.monthly"
                    :key="month.month"
                  >
                    <td class="px-4 py-3 text-sm font-medium text-gray-900">
                      {{ getMonthName(month.month) }}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">
                      {{ formatValue(month.temperature, "°C") }}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">
                      {{ formatValue(month.temp_max, "°C") }}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">
                      {{ formatValue(month.temp_min, "°C") }}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">
                      {{ formatValue(month.precipitation, "mm") }}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">
                      {{ formatValue(month.sunshine, "h") }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div
              v-else
              class="bg-blue-50 rounded-lg p-6 text-center text-blue-700"
            >
              Klimamittel für diese Station sind noch nicht verfügbar.
            </div>
          </div>
        </div>

        <!-- Yearly Aggregates Section -->
        <div class="border-t pt-6" v-if="station && yearlyAggregates.length > 0">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h2 class="text-xl font-semibold text-gray-800">
                Jahresaggregate
              </h2>
              <p class="text-sm text-gray-500">
                Jährliche Zusammenfassungen der Wetterdaten
              </p>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-green-50">
                <tr>
                  <th
                    class="px-4 py-3 text-left text-xs font-semibold text-green-600 uppercase"
                  >
                    Jahr
                  </th>
                  <th
                    class="px-4 py-3 text-left text-xs font-semibold text-green-600 uppercase"
                  >
                    Temp. Ø (°C)
                  </th>
                  <th
                    class="px-4 py-3 text-left text-xs font-semibold text-green-600 uppercase"
                  >
                    Temp. Max (°C)
                  </th>
                  <th
                    class="px-4 py-3 text-left text-xs font-semibold text-green-600 uppercase"
                  >
                    Temp. Min (°C)
                  </th>
                  <th
                    class="px-4 py-3 text-left text-xs font-semibold text-green-600 uppercase"
                  >
                    Niederschlag (mm)
                  </th>
                  <th
                    class="px-4 py-3 text-left text-xs font-semibold text-green-600 uppercase"
                  >
                    Sonne (h)
                  </th>
                  <th
                    class="px-4 py-3 text-left text-xs font-semibold text-green-600 uppercase"
                  >
                    Frost (Tage)
                  </th>
                  <th
                    class="px-4 py-3 text-left text-xs font-semibold text-green-600 uppercase"
                  >
                    Sommer (Tage)
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 bg-white">
                <tr
                  v-for="aggregate in yearlyAggregates"
                  :key="aggregate.year"
                  @click="selectYearForMonthly(aggregate.year)"
                  class="cursor-pointer hover:bg-green-50 transition-colors"
                >
                  <td class="px-4 py-3 text-sm font-medium text-gray-900">
                    {{ aggregate.year }}
                  </td>
                  <td class="px-4 py-3 text-sm text-gray-700">
                    {{ formatDecimal(aggregate.temperature?.mean, 1) }}
                  </td>
                  <td class="px-4 py-3 text-sm text-gray-700">
                    {{ formatDecimal(aggregate.temperature?.max_absolute, 1) }}
                  </td>
                  <td class="px-4 py-3 text-sm text-gray-700">
                    {{ formatDecimal(aggregate.temperature?.min_absolute, 1) }}
                  </td>
                  <td class="px-4 py-3 text-sm text-gray-700">
                    {{ formatDecimal(aggregate.precipitation?.sum, 1) }}
                  </td>
                  <td class="px-4 py-3 text-sm text-gray-700">
                    {{ formatDecimal(aggregate.sunshine_hours, 0) }}
                  </td>
                  <td class="px-4 py-3 text-sm text-gray-700">
                    {{ aggregate.frost_days ?? "–" }}
                  </td>
                  <td class="px-4 py-3 text-sm text-gray-700">
                    {{ aggregate.summer_days ?? "–" }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Monthly Aggregates Section -->
        <div class="border-t pt-6" v-if="station && selectedYear && monthlyAggregates.length > 0">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h2 class="text-xl font-semibold text-gray-800">
                Monatsaggregate {{ selectedYear }}
              </h2>
              <p class="text-sm text-gray-500">
                Monatliche Zusammenfassung für das ausgewählte Jahr
              </p>
            </div>
            <button
              @click="selectedYear = null"
              class="px-3 py-1 text-sm bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition-colors"
            >
              Schließen
            </button>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-purple-50">
                <tr>
                  <th
                    class="px-4 py-3 text-left text-xs font-semibold text-purple-600 uppercase"
                  >
                    Monat
                  </th>
                  <th
                    class="px-4 py-3 text-left text-xs font-semibold text-purple-600 uppercase"
                  >
                    Temp. Ø (°C)
                  </th>
                  <th
                    class="px-4 py-3 text-left text-xs font-semibold text-purple-600 uppercase"
                  >
                    Temp. Max (°C)
                  </th>
                  <th
                    class="px-4 py-3 text-left text-xs font-semibold text-purple-600 uppercase"
                  >
                    Temp. Min (°C)
                  </th>
                  <th
                    class="px-4 py-3 text-left text-xs font-semibold text-purple-600 uppercase"
                  >
                    Niederschlag (mm)
                  </th>
                  <th
                    class="px-4 py-3 text-left text-xs font-semibold text-purple-600 uppercase"
                  >
                    Sonne (h)
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 bg-white">
                <tr
                  v-for="month in monthlyAggregates"
                  :key="month.month"
                >
                  <td class="px-4 py-3 text-sm font-medium text-gray-900">
                    {{ month.month_name }}
                  </td>
                  <td class="px-4 py-3 text-sm text-gray-700">
                    {{ formatDecimal(month.temperature?.mean, 1) }}
                  </td>
                  <td class="px-4 py-3 text-sm text-gray-700">
                    {{ formatDecimal(month.temperature?.max_absolute, 1) }}
                  </td>
                  <td class="px-4 py-3 text-sm text-gray-700">
                    {{ formatDecimal(month.temperature?.min_absolute, 1) }}
                  </td>
                  <td class="px-4 py-3 text-sm text-gray-700">
                    {{ formatDecimal(month.precipitation?.sum, 1) }}
                  </td>
                  <td class="px-4 py-3 text-sm text-gray-700">
                    {{ formatDecimal(month.sunshine_hours, 0) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Trends & Veränderungen Section -->
        <div class="border-t pt-6" v-if="station">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h2 class="text-xl font-semibold text-gray-800">
                Trends & Veränderungen
              </h2>
              <p class="text-sm text-gray-500">
                Langzeittrends für Temperatur, Niederschlag und Sonnenschein
              </p>
            </div>
          </div>
          <TrendsChart :stationId="station.id" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { apiService, type Measurement, type Station } from "@/services/api";
import TrendsChart from "@/components/TrendsChart.vue";

const route = useRoute();
const router = useRouter();

const loading = ref(true);
const error = ref<string | null>(null);
const station = ref<Station | null>(null);
const recentMeasurements = ref<Measurement[]>([]);
const climateNormals = ref<any>(null);
const yearlyAggregates = ref<any[]>([]);
const monthlyAggregates = ref<any[]>([]);
const selectedYear = ref<number | null>(null);

const loadStationData = async (stationId: string) => {
  loading.value = true;
  error.value = null;

  try {
    const [stationResponse, measurementsResponse, climateNormalsResponse, yearlyResponse] =
      await Promise.all([
        apiService.getStation(stationId),
        apiService.getMeasurementsByStation(stationId, { per_page: 10 }),
        apiService.getClimateNormals(), // Get all climate normals, filter client-side
        apiService.getYearlyAggregates({ station_id: stationId, order: 'desc' }),
      ]);

    if (!stationResponse.success || !stationResponse.data) {
      throw new Error("Stationsdetails konnten nicht geladen werden.");
    }

    station.value = stationResponse.data;
    recentMeasurements.value = measurementsResponse.success
      ? measurementsResponse.data
      : [];

    // Filter climate normals to this station
    if (
      climateNormalsResponse.success &&
      climateNormalsResponse.data?.stations
    ) {
      const stationClimate = climateNormalsResponse.data.stations.find(
        (s: any) => s.station_id === stationId,
      );
      climateNormals.value = stationClimate || null;
    } else {
      climateNormals.value = null;
    }

    // Load yearly aggregates
    if (yearlyResponse.success && yearlyResponse.data?.aggregates) {
      yearlyAggregates.value = yearlyResponse.data.aggregates;
    } else {
      yearlyAggregates.value = [];
    }
  } catch (err) {
    console.error("Error loading station details:", err);
    error.value =
      err instanceof Error
        ? err.message
        : "Stationsdetails konnten nicht geladen werden.";
    station.value = null;
    recentMeasurements.value = [];
    climateNormals.value = null;
    yearlyAggregates.value = [];
  } finally {
    loading.value = false;
  }
};

// Load monthly aggregates when a year is selected
watch(selectedYear, async (year) => {
  if (year && station.value) {
    try {
      const response = await apiService.getMonthlyAggregates({
        station_id: station.value.id,
        year: year,
      });
      if (response.success && response.data?.aggregates) {
        monthlyAggregates.value = response.data.aggregates;
      } else {
        monthlyAggregates.value = [];
      }
    } catch (err) {
      console.error("Error loading monthly aggregates:", err);
      monthlyAggregates.value = [];
    }
  } else {
    monthlyAggregates.value = [];
  }
});

watch(
  () => route.params.id,
  (id) => {
    if (typeof id === "string" && id) {
      loadStationData(id);
      selectedYear.value = null;
    }
  },
  { immediate: true },
);

const formatDate = (value?: string | null) => {
  if (!value) return "–";

  return new Date(value).toLocaleDateString("de-DE", {
    year: "numeric",
    month: "2-digit",
    day: "2-digit",
  });
};

const getMonthName = (month: number): string => {
  const months = [
    "Jahresmittel",
    "Januar",
    "Februar",
    "März",
    "April",
    "Mai",
    "Juni",
    "Juli",
    "August",
    "September",
    "Oktober",
    "November",
    "Dezember",
  ];
  return months[month] || "–";
};

const formatNumber = (value?: number | null) => {
  if (value === null || value === undefined) return "0";
  return value.toLocaleString("de-DE");
};

const formatValue = (value?: number | string | null, unit = "") => {
  if (value === null || value === undefined || value === "") return "–";
  return `${value} ${unit}`.trim();
};

const formatDecimal = (value?: number | null, decimals = 1) => {
  if (value === null || value === undefined) return "–";
  return value.toFixed(decimals);
};

const selectYearForMonthly = (year: number) => {
  selectedYear.value = year;
};

const viewCharts = () => {
  if (station.value) {
    router.push(`/charts?station=${station.value.id}`);
  }
};
</script>

<style scoped lang="postcss">
.station-detail-view {
  @apply min-h-screen bg-gradient-to-br from-slate-100 to-blue-200;
}
</style>

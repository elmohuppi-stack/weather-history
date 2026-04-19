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

            <div v-if="climateNormals.stations && climateNormals.stations.length > 0" class="overflow-x-auto">
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
                    v-for="month in (climateNormals.stations[0].monthly || [])"
                    :key="month.month"
                  >
                    <td class="px-4 py-3 text-sm font-medium text-gray-900">
                      {{ getMonthName(month.month) }}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">
                      {{ formatValue(month.temperature, "°C") }}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">
                      {{ formatValue(month.temperature_max, "°C") }}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">
                      {{ formatValue(month.temperature_min, "°C") }}
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
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { apiService, type Measurement, type Station } from "@/services/api";

const route = useRoute();
const router = useRouter();

const loading = ref(true);
const error = ref<string | null>(null);
const station = ref<Station | null>(null);
const recentMeasurements = ref<Measurement[]>([]);
const climateNormals = ref<any>(null);

const loadStationData = async (stationId: string) => {
  loading.value = true;
  error.value = null;

  try {
    const [stationResponse, measurementsResponse, climateNormalsResponse] = await Promise.all([
      apiService.getStation(stationId),
      apiService.getMeasurementsByStation(stationId, { per_page: 10 }),
      apiService.getClimateNormals({ station_ids: [stationId] }),
    ]);

    if (!stationResponse.success || !stationResponse.data) {
      throw new Error("Stationsdetails konnten nicht geladen werden.");
    }

    station.value = stationResponse.data;
    recentMeasurements.value = measurementsResponse.success
      ? measurementsResponse.data
      : [];
    climateNormals.value = climateNormalsResponse.success
      ? climateNormalsResponse.data
      : null;
  } catch (err) {
    console.error("Error loading station details:", err);
    error.value =
      err instanceof Error
        ? err.message
        : "Stationsdetails konnten nicht geladen werden.";
    station.value = null;
    recentMeasurements.value = [];
    climateNormals.value = null;
  } finally {
    loading.value = false;
  }
};

watch(
  () => route.params.id,
  (id) => {
    if (typeof id === "string" && id) {
      loadStationData(id);
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

const viewCharts = () => {
  if (station.value) {
    router.push(`/charts?station=${station.value.id}`);
  }
};
</script>

<style scoped>
.station-detail-view {
  min-height: calc(100vh - 64px);
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}
</style>

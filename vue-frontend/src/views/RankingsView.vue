<script setup lang="ts">
import { ref, onMounted, watch } from "vue";
import { apiService } from "../services/api";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Dropdown from "primevue/dropdown";
import Button from "primevue/button";
import Card from "primevue/card";

interface RankingEntry {
  rank: number;
  year: number;
  station_id: string;
  station_name: string;
  value: number;
}

interface Metric {
  label: string;
  value: string;
  icon: string;
  color: string;
  unit: string;
}

const metrics: Metric[] = [
  {
    label: "Wärmste Jahre",
    value: "warmest_year",
    icon: "pi pi-sun",
    color: "bg-orange-100",
    unit: "°C",
  },
  {
    label: "Kälteste Jahre",
    value: "coldest_year",
    icon: "pi pi-cloud-snow",
    color: "bg-blue-100",
    unit: "°C",
  },
  {
    label: "Nasseste Jahre",
    value: "wettest_year",
    icon: "pi pi-cloud-rain",
    color: "bg-cyan-100",
    unit: "mm",
  },
  {
    label: "Trockenste Jahre",
    value: "driest_year",
    icon: "pi pi-sun",
    color: "bg-amber-100",
    unit: "mm",
  },
  {
    label: "Sonnigste Jahre",
    value: "sunniest_year",
    icon: "pi pi-sun",
    color: "bg-yellow-100",
    unit: "h",
  },
  {
    label: "Meiste Frosttage",
    value: "most_frosts",
    icon: "pi pi-star-fill",
    color: "bg-indigo-100",
    unit: "Tage",
  },
  {
    label: "Meiste Sommertage",
    value: "most_summer_days",
    icon: "pi pi-sun",
    color: "bg-red-100",
    unit: "Tage",
  },
];

const selectedMetric = ref<string>("warmest_year");
const selectedLimit = ref<number>(10);
const rankings = ref<RankingEntry[]>([]);
const loading = ref<boolean>(false);
const error = ref<string>("");

const limitOptions = [
  { label: "Top 5", value: 5 },
  { label: "Top 10", value: 10 },
  { label: "Top 20", value: 20 },
  { label: "Top 50", value: 50 },
];

const loadRankings = async () => {
  loading.value = true;
  error.value = "";
  try {
    const response = await apiService.getRankings({
      metric: selectedMetric.value,
      limit: selectedLimit.value,
    });

    if (response.success && response.data?.rankings) {
      rankings.value = response.data.rankings;
    } else {
      error.value = "Konnte Rankings nicht laden";
    }
  } catch (err: any) {
    error.value = err.message || "Fehler beim Laden der Rankings";
    console.error("Rankings Error:", err);
  } finally {
    loading.value = false;
  }
};

const getMetricIcon = (metricValue: string): string => {
  const metric = metrics.find((m) => m.value === metricValue);
  return metric?.icon || "pi pi-chart-bar";
};

const getMetricLabel = (metricValue: string): string => {
  const metric = metrics.find((m) => m.value === metricValue);
  return metric?.label || metricValue;
};

const getMetricUnit = (metricValue: string): string => {
  const metric = metrics.find((m) => m.value === metricValue);
  return metric?.unit || "";
};

const getMetricColor = (metricValue: string): string => {
  const metric = metrics.find((m) => m.value === metricValue);
  return metric?.color || "bg-gray-100";
};

const formatValue = (value: number): string => {
  return value.toFixed(1);
};

watch(selectedMetric, () => {
  selectedLimit.value = 10;
  loadRankings();
});

watch(selectedLimit, () => {
  loadRankings();
});

onMounted(() => {
  loadRankings();
});
</script>

<template>
  <div class="p-6 bg-gradient-to-br from-slate-50 to-slate-100 min-h-screen">
    <div class="max-w-6xl mx-auto">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-4xl font-bold text-slate-900 mb-2">
          🏆 Wetter-Rankings
        </h1>
        <p class="text-lg text-slate-600">
          Finde die extremsten Wetterjahre der letzten Jahrzehnte
        </p>
      </div>

      <!-- Metric Selector -->
      <Card class="mb-8 shadow-lg">
        <template #content>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-2"
                >Metrik wählen</label
              >
              <Dropdown
                v-model="selectedMetric"
                :options="metrics"
                optionLabel="label"
                optionValue="value"
                placeholder="Wähle eine Metrik"
                class="w-full"
              />
            </div>

            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-2"
                >Anzahl der Rankings</label
              >
              <Dropdown
                v-model="selectedLimit"
                :options="limitOptions"
                placeholder="Wähle Limit"
                class="w-full"
              />
            </div>

            <div class="flex items-end">
              <Button
                icon="pi pi-refresh"
                label="Aktualisieren"
                @click="loadRankings"
                :loading="loading"
                class="w-full"
              />
            </div>
          </div>
        </template>
      </Card>

      <!-- Metric Info Card -->
      <Card
        v-if="selectedMetric"
        :class="`mb-8 shadow-lg ${getMetricColor(selectedMetric)}`"
      >
        <template #content>
          <div class="flex items-center gap-4">
            <i
              :class="`${getMetricIcon(selectedMetric)} text-4xl text-slate-700`"
            ></i>
            <div>
              <h2 class="text-2xl font-bold text-slate-900">
                {{ getMetricLabel(selectedMetric) }}
              </h2>
              <p class="text-slate-600">
                Einheit:
                <span class="font-semibold">{{
                  getMetricUnit(selectedMetric)
                }}</span>
              </p>
            </div>
          </div>
        </template>
      </Card>

      <!-- Rankings Table -->
      <Card class="shadow-lg">
        <template #content>
          <div
            v-if="error"
            class="mb-4 p-4 bg-red-100 border border-red-300 rounded text-red-700"
          >
            {{ error }}
          </div>

          <DataTable
            :value="rankings"
            :loading="loading"
            striped-rows
            responsive-layout="scroll"
            class="p-datatable-sm"
          >
            <Column
              field="rank"
              header="Rang"
              style="width: 8%"
              class="text-center"
            >
              <template #body="{ data }">
                <span v-if="data.rank === 1" class="text-2xl">🥇</span>
                <span v-else-if="data.rank === 2" class="text-2xl">🥈</span>
                <span v-else-if="data.rank === 3" class="text-2xl">🥉</span>
                <span
                  v-else
                  class="inline-block bg-slate-200 text-slate-700 px-3 py-1 rounded-full font-semibold"
                >
                  {{ data.rank }}
                </span>
              </template>
            </Column>

            <Column field="year" header="Jahr" style="width: 12%">
              <template #body="{ data }">
                <span class="font-bold text-slate-900">{{ data.year }}</span>
              </template>
            </Column>

            <Column
              field="station_name"
              header="Messstation"
              style="width: 25%"
            >
              <template #body="{ data }">
                <div>
                  <p class="font-semibold text-slate-900">
                    {{ data.station_name }}
                  </p>
                  <p class="text-xs text-slate-500">
                    ID: {{ data.station_id }}
                  </p>
                </div>
              </template>
            </Column>

            <Column
              field="value"
              :header="`Wert (${getMetricUnit(selectedMetric)})`"
              style="width: 15%"
            >
              <template #body="{ data }">
                <span
                  class="inline-block bg-green-100 text-green-800 px-4 py-2 rounded-lg font-bold"
                >
                  {{ formatValue(data.value) }}
                  {{ getMetricUnit(selectedMetric) }}
                </span>
              </template>
            </Column>
          </DataTable>

          <div
            v-if="!loading && rankings.length === 0"
            class="text-center py-12"
          >
            <p class="text-slate-600">
              Keine Daten für die gewählte Metrik verfügbar
            </p>
          </div>
        </template>
      </Card>

      <!-- Stats Footer -->
      <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-4">
        <Card class="shadow text-center">
          <template #content>
            <p class="text-sm text-slate-600">Anzahl Rankings</p>
            <p class="text-3xl font-bold text-slate-900">
              {{ rankings.length }}
            </p>
          </template>
        </Card>

        <Card class="shadow text-center">
          <template #content>
            <p class="text-sm text-slate-600">Zeitraum</p>
            <p class="text-3xl font-bold text-slate-900">
              {{
                rankings.length > 0
                  ? `${Math.min(...rankings.map((r) => r.year))} - ${Math.max(...rankings.map((r) => r.year))}`
                  : "N/A"
              }}
            </p>
          </template>
        </Card>

        <Card class="shadow text-center">
          <template #content>
            <p class="text-sm text-slate-600">Stationen</p>
            <p class="text-3xl font-bold text-slate-900">
              {{ new Set(rankings.map((r) => r.station_id)).size }}
            </p>
          </template>
        </Card>
      </div>
    </div>
  </div>
</template>

<style scoped>
:deep(.p-datatable-sm .p-datatable-thead > tr > th) {
  padding: 0.5rem;
  font-size: 0.875rem;
}

:deep(.p-datatable-sm .p-datatable-tbody > tr > td) {
  padding: 0.75rem 0.5rem;
}
</style>

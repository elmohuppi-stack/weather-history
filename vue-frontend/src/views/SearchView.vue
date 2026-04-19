<template>
  <div class="search-view">
    <!-- Header Section -->
    <div class="mb-8">
      <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
        Wetterstation Suche & Filter
      </h1>
      <p class="text-lg text-gray-600">
        Finden Sie Wetterstationen und filtern Sie nach verschiedenen Kriterien
      </p>
    </div>

    <!-- Search & Filter Card -->
    <div class="card mb-6">
      <div class="card-body space-y-6">
        <!-- Full-text Search -->
        <div>
          <label class="block text-sm font-semibold text-gray-900 mb-3">
            <i class="pi pi-search mr-2 text-blue-600"></i>
            Station suchen
          </label>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Nach Stationsname, ID oder Ort suchen..."
            class="input-field w-full"
            @input="performSearch"
          />
          <p class="text-xs text-gray-500 mt-2">
            Sucht in Stationsnamen, IDs, Bundesländern und Ortschaften
          </p>
        </div>

        <!-- Advanced Filters -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <!-- State Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2"
              >Bundesland</label
            >
            <select
              v-model="selectedState"
              class="input-field w-full"
              @change="performSearch"
            >
              <option value="">Alle Bundesländer</option>
              <option v-for="state in states" :key="state" :value="state">
                {{ state }}
              </option>
            </select>
          </div>

          <!-- Activity Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2"
              >Status</label
            >
            <select
              v-model="selectedStatus"
              class="input-field w-full"
              @change="performSearch"
            >
              <option value="">Alle</option>
              <option value="active">Aktiv</option>
              <option value="inactive">Inaktiv</option>
            </select>
          </div>

          <!-- Elevation Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2"
              >Minimale Höhe (m)</label
            >
            <input
              v-model.number="minElevation"
              type="number"
              min="0"
              max="3000"
              class="input-field w-full"
              @input="performSearch"
            />
          </div>

          <!-- Measurement Count Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2"
              >Min. Messungen</label
            >
            <input
              v-model.number="minMeasurements"
              type="number"
              min="0"
              class="input-field w-full"
              @input="performSearch"
            />
          </div>
        </div>

        <!-- Date Range -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2"
              >Datenstart (ab Jahr)</label
            >
            <input
              v-model.number="startYear"
              type="number"
              min="1890"
              max="2026"
              class="input-field w-full"
              @input="performSearch"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2"
              >Daten bis (vor Jahr)</label
            >
            <input
              v-model.number="endYear"
              type="number"
              min="1890"
              max="2026"
              class="input-field w-full"
              @input="performSearch"
            />
          </div>
        </div>

        <!-- Filter Actions -->
        <div class="flex gap-3">
          <button @click="performSearch" class="btn-primary flex-1">
            <i class="pi pi-search mr-2"></i>
            Filter anwenden
          </button>
          <button @click="resetFilters" class="btn-secondary flex-1">
            <i class="pi pi-times mr-2"></i>
            Filter zurücksetzen
          </button>
        </div>
      </div>
    </div>

    <!-- Results Summary -->
    <div v-if="searchPerformed" class="mb-6">
      <div class="flex items-center justify-between">
        <div class="text-lg text-gray-700">
          <span class="font-semibold">{{ filteredStations.length }}</span>
          <span class="text-gray-600">Stationen gefunden</span>
          <span v-if="searchQuery" class="text-gray-500 ml-2"
            >(für "{{ searchQuery }}")</span
          >
        </div>
        <div class="flex gap-2">
          <button
            v-for="sortBy in ['name', 'elevation', 'measurements', 'latest']"
            :key="sortBy"
            @click="sortStations(sortBy)"
            :class="[
              'px-3 py-2 rounded-lg text-sm font-medium transition-colors',
              currentSort === sortBy
                ? 'bg-blue-600 text-white'
                : 'bg-gray-200 text-gray-700 hover:bg-gray-300',
            ]"
          >
            {{ sortLabels[sortBy] }}
          </button>
        </div>
      </div>
    </div>

    <!-- Results Grid -->
    <div
      v-if="searchPerformed && filteredStations.length > 0"
      class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6"
    >
      <div
        v-for="station in filteredStations"
        :key="station.id"
        class="card hover:shadow-lg transition-shadow cursor-pointer"
        @click="selectStation(station)"
      >
        <div class="card-header">
          <div class="flex items-start justify-between">
            <div>
              <h3 class="font-bold text-gray-900">{{ station.name }}</h3>
              <p class="text-sm text-gray-500">{{ station.location }}</p>
            </div>
            <span
              :class="[
                'px-2 py-1 rounded text-xs font-medium',
                station.active
                  ? 'bg-green-100 text-green-800'
                  : 'bg-red-100 text-red-800',
              ]"
            >
              {{ station.active ? "Aktiv" : "Inaktiv" }}
            </span>
          </div>
        </div>
        <div class="card-body space-y-3">
          <div class="grid grid-cols-2 gap-3 text-sm">
            <div>
              <p class="text-gray-600">ID</p>
              <p class="font-medium text-gray-900">{{ station.id }}</p>
            </div>
            <div>
              <p class="text-gray-600">Höhe</p>
              <p class="font-medium text-gray-900">{{ station.elevation }} m</p>
            </div>
            <div>
              <p class="text-gray-600">Bundesland</p>
              <p class="font-medium text-gray-900">{{ station.state }}</p>
            </div>
            <div>
              <p class="text-gray-600">Messungen</p>
              <p class="font-medium text-gray-900">
                {{ station.measurement_count?.toLocaleString() || "0" }}
              </p>
            </div>
            <div>
              <p class="text-gray-600">Start</p>
              <p class="font-medium text-gray-900">{{ station.start_year }}</p>
            </div>
            <div>
              <p class="text-gray-600">Aktuell</p>
              <p class="font-medium text-gray-900">
                {{ formatDate(station.latest_date) }}
              </p>
            </div>
          </div>
          <div class="text-xs text-gray-500 pt-2 border-t">
            <p>
              <i class="pi pi-map-marker mr-1"></i>
              {{ station.lat.toFixed(4) }}°, {{ station.lon.toFixed(4) }}°
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- No Results Message -->
    <div
      v-else-if="searchPerformed && filteredStations.length === 0"
      class="card"
    >
      <div class="card-body text-center py-12">
        <i class="pi pi-inbox text-6xl text-gray-300 mb-4"></i>
        <h3 class="text-2xl font-bold text-gray-900 mb-2">
          Keine Stationen gefunden
        </h3>
        <p class="text-gray-600 mb-6">
          Versuchen Sie andere Suchkriterien oder Filter
        </p>
        <button @click="resetFilters" class="btn-primary">
          <i class="pi pi-redo mr-2"></i>
          Filter zurücksetzen
        </button>
      </div>
    </div>

    <!-- Selected Station Details Modal -->
    <div v-if="selectedStationDetail" class="card mb-6">
      <div class="card-header">
        <div class="flex items-center justify-between">
          <h3 class="text-xl font-bold text-gray-900">
            {{ selectedStationDetail.name }}
          </h3>
          <button @click="selectedStationDetail = null" class="btn-secondary">
            <i class="pi pi-times mr-2"></i>
            Schließen
          </button>
        </div>
      </div>
      <div class="card-body">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <!-- Details -->
          <div>
            <h4 class="font-semibold text-gray-900 mb-4">Station Details</h4>
            <div class="space-y-3 text-sm">
              <div>
                <p class="text-gray-600">Stationskennung</p>
                <p class="font-medium text-gray-900">
                  {{ selectedStationDetail.id }}
                </p>
              </div>
              <div>
                <p class="text-gray-600">Ort</p>
                <p class="font-medium text-gray-900">
                  {{ selectedStationDetail.location }}
                </p>
              </div>
              <div>
                <p class="text-gray-600">Bundesland</p>
                <p class="font-medium text-gray-900">
                  {{ selectedStationDetail.state }}
                </p>
              </div>
              <div>
                <p class="text-gray-600">Höhe über Meeresspiegel</p>
                <p class="font-medium text-gray-900">
                  {{ selectedStationDetail.elevation }} m
                </p>
              </div>
            </div>
          </div>

          <!-- Data Coverage -->
          <div>
            <h4 class="font-semibold text-gray-900 mb-4">Datenverfügbarkeit</h4>
            <div class="space-y-3 text-sm">
              <div>
                <p class="text-gray-600">Datenstart</p>
                <p class="font-medium text-gray-900">
                  {{ selectedStationDetail.start_year }}
                </p>
              </div>
              <div>
                <p class="text-gray-600">Aktuell bis</p>
                <p class="font-medium text-gray-900">
                  {{ formatDate(selectedStationDetail.latest_date) }}
                </p>
              </div>
              <div>
                <p class="text-gray-600">Messungen</p>
                <p class="font-medium text-gray-900">
                  {{
                    selectedStationDetail.measurement_count?.toLocaleString()
                  }}
                </p>
              </div>
              <div>
                <p class="text-gray-600">Status</p>
                <p
                  :class="[
                    'font-medium',
                    selectedStationDetail.active
                      ? 'text-green-600'
                      : 'text-red-600',
                  ]"
                >
                  {{ selectedStationDetail.active ? "Aktiv" : "Inaktiv" }}
                </p>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div>
            <h4 class="font-semibold text-gray-900 mb-4">Aktionen</h4>
            <div class="space-y-3">
              <button @click="viewStationDetail" class="btn-primary w-full">
                <i class="pi pi-info-circle mr-2"></i>
                Details anzeigen
              </button>
              <button @click="viewMeasurements" class="btn-secondary w-full">
                <i class="pi pi-chart-line mr-2"></i>
                Messungen
              </button>
              <button @click="viewTrends" class="btn-secondary w-full">
                <i class="pi pi-chart-bar mr-2"></i>
                Trends
              </button>
              <button @click="viewOnMap" class="btn-secondary w-full">
                <i class="pi pi-map mr-2"></i>
                Auf Karte
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import { useRouter } from "vue-router";
import { apiService, type Station } from "@/services/api";

const router = useRouter();

// State
const stations = ref<Station[]>([]);
const searchQuery = ref("");
const selectedState = ref("");
const selectedStatus = ref("");
const minElevation = ref(0);
const minMeasurements = ref(0);
const startYear = ref(1890);
const endYear = ref(2026);
const searchPerformed = ref(false);
const selectedStationDetail = ref<Station | null>(null);
const currentSort = ref("name");
const isLoading = ref(true);

// Sort options
const sortLabels: Record<string, string> = {
  name: "Nach Name",
  elevation: "Nach Höhe",
  measurements: "Nach Messungen",
  latest: "Nach Aktuelles Datum",
};

// Computed properties
const states = computed(() => {
  const stateSet = new Set(stations.value.map((s) => s.state));
  return Array.from(stateSet).sort();
});

const filteredStations = computed(() => {
  let results = [...stations.value];

  // Full-text search
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    results = results.filter(
      (station) =>
        station.name.toLowerCase().includes(query) ||
        station.id.toLowerCase().includes(query) ||
        station.location.toLowerCase().includes(query) ||
        station.state.toLowerCase().includes(query),
    );
  }

  // State filter
  if (selectedState.value) {
    results = results.filter((s) => s.state === selectedState.value);
  }

  // Status filter
  if (selectedStatus.value === "active") {
    results = results.filter((s) => s.active);
  } else if (selectedStatus.value === "inactive") {
    results = results.filter((s) => !s.active);
  }

  // Elevation filter
  if (minElevation.value > 0) {
    results = results.filter((s) => s.elevation >= minElevation.value);
  }

  // Measurement count filter
  if (minMeasurements.value > 0) {
    results = results.filter(
      (s) => (s.measurement_count || 0) >= minMeasurements.value,
    );
  }

  // Date range filter
  if (startYear.value > 1890) {
    results = results.filter((s) => s.start_year <= startYear.value);
  }
  if (endYear.value < 2026) {
    const endDateStr = `${endYear.value}-12-31`;
    results = results.filter((s) => (s.latest_date || "") <= endDateStr);
  }

  // Sort
  return sortResults(results);
});

// Methods
const loadStations = async () => {
  try {
    isLoading.value = true;
    const response = await apiService.getStations();
    if (response.success) {
      stations.value = response.data || [];
    }
  } catch (err) {
    console.error("Error loading stations:", err);
  } finally {
    isLoading.value = false;
  }
};

const performSearch = () => {
  searchPerformed.value = true;
};

const resetFilters = () => {
  searchQuery.value = "";
  selectedState.value = "";
  selectedStatus.value = "";
  minElevation.value = 0;
  minMeasurements.value = 0;
  startYear.value = 1890;
  endYear.value = 2026;
  currentSort.value = "name";
  searchPerformed.value = false;
  selectedStationDetail.value = null;
};

const sortStations = (sortBy: string) => {
  currentSort.value = sortBy;
};

const sortResults = (results: Station[]): Station[] => {
  const sorted = [...results];

  switch (currentSort.value) {
    case "name":
      return sorted.sort((a, b) => a.name.localeCompare(b.name));
    case "elevation":
      return sorted.sort((a, b) => b.elevation - a.elevation);
    case "measurements":
      return sorted.sort(
        (a, b) => (b.measurement_count || 0) - (a.measurement_count || 0),
      );
    case "latest":
      return sorted.sort((a, b) =>
        (b.latest_date || "").localeCompare(a.latest_date || ""),
      );
    default:
      return sorted;
  }
};

const selectStation = (station: Station) => {
  selectedStationDetail.value = station;
};

const viewStationDetail = () => {
  if (selectedStationDetail.value) {
    router.push(`/stations/${selectedStationDetail.value.id}`);
  }
};

const viewMeasurements = () => {
  if (selectedStationDetail.value) {
    router.push(`/charts?station=${selectedStationDetail.value.id}`);
  }
};

const viewTrends = () => {
  if (selectedStationDetail.value) {
    router.push(`/stations/${selectedStationDetail.value.id}#trends`);
  }
};

const viewOnMap = () => {
  router.push("/maps");
};

const formatDate = (dateStr?: string): string => {
  if (!dateStr) return "unbekannt";
  const date = new Date(dateStr);
  return date.toLocaleDateString("de-DE");
};

// Lifecycle
onMounted(() => {
  loadStations();
});
</script>

<style scoped>
.input-field {
  @apply w-full px-4 py-2 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition;
}

.btn-primary {
  @apply px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors;
}

.btn-secondary {
  @apply px-4 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transition-colors;
}

.card {
  @apply bg-white rounded-lg shadow-md border border-gray-200;
}

.card-header {
  @apply px-6 py-4 border-b border-gray-200;
}

.card-body {
  @apply px-6 py-4;
}
</style>

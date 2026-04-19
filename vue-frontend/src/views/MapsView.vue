<template>
  <div class="maps-view">
    <!-- Header Section -->
    <div class="mb-8">
      <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">{{ $t('maps.title') }}</h1>
      <p class="text-lg text-gray-600">
        {{ $t('maps.subtitle', [16]) }}
      </p>
    </div>

    <!-- Map Controls -->
    <div class="card mb-6">
      <div class="card-body">
        <div class="flex flex-wrap gap-4 items-center">
          <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ $t('maps.controls.parameter') }}</label>
            <select v-model="selectedParameter" class="input-field">
              <option value="temperature">{{ $t('maps.parameters.temperature') }}</option>
              <option value="precipitation">{{ $t('maps.parameters.precipitation') }}</option>
              <option value="sunshine">{{ $t('maps.parameters.sunshine') }}</option>
            </select>
          </div>
          
          <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ $t('maps.controls.year') }}</label>
            <select v-model="selectedYear" class="input-field">
              <option v-for="year in years" :key="year" :value="year">{{ year }}</option>
            </select>
          </div>
          
          <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ $t('maps.controls.month') }}</label>
            <select v-model="selectedMonth" class="input-field">
              <option value="">{{ $t('maps.months.fullYear') }}</option>
              <option v-for="month in months" :key="month.value" :value="month.value">
                {{ month.label }}
              </option>
            </select>
          </div>
          
          <div class="flex items-end">
            <button @click="loadHeatmapData" class="btn-primary">
              <i class="pi pi-refresh mr-2"></i>
              {{ $t('maps.controls.updateMap') }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Map Container -->
    <div class="card mb-6">
      <div class="card-header">
        <div class="flex items-center justify-between">
          <h3 class="text-xl font-bold text-gray-900">Deutschland Wetterstationen</h3>
          <div class="flex items-center gap-2">
            <span class="badge-success">{{ stations.length }} Stationen</span>
            <button @click="toggleMapType" class="btn-secondary text-sm">
              <i class="pi" :class="mapType === 'stations' ? 'pi-map-marker' : 'pi-chart-bar'"></i>
              {{ mapType === 'stations' ? 'Heatmap' : 'Stationen' }}
            </button>
          </div>
        </div>
      </div>
      <div class="card-body p-0 bg-gray-50">
        <LeafletMap 
          :stations="stations"
          :selectedStation="selectedStation"
          :parameter="selectedParameter"
          :year="selectedYear"
          @station-selected="selectStation"
        />
      </div>
    </div>

    <!-- Selected Station Details -->
    <div v-if="selectedStation" class="card mb-6">
      <div class="card-header">
        <div class="flex items-center justify-between">
          <h3 class="text-xl font-bold text-gray-900">{{ selectedStation.name }}</h3>
          <button @click="selectedStation = null" class="btn-secondary">
            <i class="pi pi-times mr-2"></i>
            Schließen
          </button>
        </div>
      </div>
      <div class="card-body">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="space-y-4">
            <div>
              <h4 class="font-semibold text-gray-900 mb-2">Station Details</h4>
              <div class="space-y-2">
                <div class="flex justify-between">
                  <span class="text-gray-600">ID:</span>
                  <span class="font-medium">{{ selectedStation.id }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600">Bundesland:</span>
                  <span class="font-medium">{{ selectedStation.state }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600">Höhe:</span>
                  <span class="font-medium">{{ selectedStation.elevation }} m</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600">Aktiv:</span>
                  <span class="font-medium" :class="selectedStation.active ? 'text-success-600' : 'text-danger-600'">
                    {{ selectedStation.active ? 'Ja' : 'Nein' }}
                  </span>
                </div>
              </div>
            </div>
          </div>
          
          <div class="space-y-4">
            <div>
              <h4 class="font-semibold text-gray-900 mb-2">Koordinaten</h4>
              <div class="space-y-2">
                <div class="flex justify-between">
                  <span class="text-gray-600">Breitengrad:</span>
                  <span class="font-medium">{{ selectedStation.lat.toFixed(4) }}°</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600">Längengrad:</span>
                  <span class="font-medium">{{ selectedStation.lon.toFixed(4) }}°</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600">Daten bis:</span>
                  <span class="font-medium">{{ selectedStation.latest_date || '2024-12-31' }}</span>
                </div>
              </div>
            </div>
          </div>
          
          <div class="space-y-4">
            <div>
              <h4 class="font-semibold text-gray-900 mb-2">Aktionen</h4>
              <div class="space-y-3">
                <button @click="viewStationDetails" class="btn-primary w-full">
                  <i class="pi pi-info-circle mr-2"></i>
                  Details anzeigen
                </button>
                <button @click="viewStationMeasurements" class="btn-secondary w-full">
                  <i class="pi pi-chart-line mr-2"></i>
                  Messungen anzeigen
                </button>
                <button @click="viewStationStatistics" class="btn-secondary w-full">
                  <i class="pi pi-chart-bar mr-2"></i>
                  Statistiken anzeigen
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Stations List -->
    <div class="card">
      <div class="card-header">
        <h3 class="text-xl font-bold text-gray-900">Alle Wetterstationen</h3>
        <p class="text-sm text-gray-500 mt-1">Klicken Sie auf eine Station für Details</p>
      </div>
      <div class="card-body">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead>
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Station</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bundesland</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Höhe</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Daten bis</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Messungen</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aktionen</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="station in stations" :key="station.id" 
                  class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-primary-100 to-primary-200 flex items-center justify-center mr-3">
                      <i class="pi pi-map-marker text-primary-600"></i>
                    </div>
                    <div>
                      <div class="font-medium text-gray-900">{{ station.name }}</div>
                      <div class="text-sm text-gray-500">ID: {{ station.id }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">
                    {{ station.state }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-gray-900">
                  {{ station.elevation }} m
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-gray-900">
                  {{ station.latest_date || '2024-12-31' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-gray-900">
                  {{ station.measurement_count?.toLocaleString() || '12,450' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="px-2 py-1 text-xs rounded-full" 
                        :class="station.active ? 'bg-success-100 text-success-800' : 'bg-danger-100 text-danger-800'">
                    {{ station.active ? 'Aktiv' : 'Inaktiv' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <button @click="selectStation(station)" class="text-primary-600 hover:text-primary-900 mr-3">
                    <i class="pi pi-eye"></i>
                  </button>
                  <button @click="viewStationMeasurements(station)" class="text-blue-600 hover:text-blue-900">
                    <i class="pi pi-chart-line"></i>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { apiService, type Station } from '@/services/api'
import LeafletMap from '@/components/LeafletMap.vue'

const router = useRouter()
const { t } = useI18n()

// State
const stations = ref<Station[]>([])
const selectedStation = ref<Station | null>(null)
const selectedParameter = ref('temperature')
const selectedYear = ref(2024)
const selectedMonth = ref('')
const mapType = ref('stations') // 'stations' or 'heatmap'
const isLoading = ref(true)
const error = ref<string | null>(null)

// Options
const years = Array.from({ length: 35 }, (_, i) => 2024 - i)
const months = [
  { value: '1', label: 'Januar' },
  { value: '2', label: 'Februar' },
  { value: '3', label: 'März' },
  { value: '4', label: 'April' },
  { value: '5', label: 'Mai' },
  { value: '6', label: 'Juni' },
  { value: '7', label: 'Juli' },
  { value: '8', label: 'August' },
  { value: '9', label: 'September' },
  { value: '10', label: 'Oktober' },
  { value: '11', label: 'November' },
  { value: '12', label: 'Dezember' }
]

// Methods
const loadStations = async () => {
  try {
    isLoading.value = true
    error.value = null
    
    const response = await apiService.getMapStations()
    
    if (response.success) {
      // Extract stations from GeoJSON features
      if (response.data.features) {
        stations.value = response.data.features.map((feature: any) => ({
          id: feature.properties.id,
          name: feature.properties.name,
          location: feature.properties.location,
          elevation: feature.properties.elevation,
          state: feature.properties.state,
          latest_date: feature.properties.latest_date,
          active: feature.properties.active,
          lat: feature.geometry.coordinates[1],
          lon: feature.geometry.coordinates[0],
          measurement_count: feature.properties.measurement_count,
          start_year: feature.properties.start_year
        }))
      }
    } else {
      error.value = 'Failed to load stations data'
    }
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Unknown error occurred'
    console.error('Error loading stations:', err)
  } finally {
    isLoading.value = false
  }
}

const loadHeatmapData = async () => {
  try {
    const params = {
      parameter: selectedParameter.value,
      year: selectedYear.value,
      month: selectedMonth.value ? parseInt(selectedMonth.value) : undefined
    }
    
    const response = await apiService.getHeatmapData(params)
    
    if (response.success) {
      console.log('Heatmap data loaded:', response.data)
      // In production, this would update the map visualization
    }
  } catch (err) {
    console.error('Error loading heatmap data:', err)
  }
}

const selectStation = (station: Station) => {
  selectedStation.value = station
}

const toggleMapType = () => {
  mapType.value = mapType.value === 'stations' ? 'heatmap' : 'stations'
}

const viewStationDetails = () => {
  if (selectedStation.value) {
    router.push(`/stations/${selectedStation.value.id}`)
  }
}

const viewStationMeasurements = (stationOrEvent?: Station | Event) => {
  // Handle both cases: station parameter or event parameter
  let station: Station | undefined
  if (stationOrEvent && 'id' in (stationOrEvent as Station)) {
    station = stationOrEvent as Station
  }
  const stationId = station?.id || selectedStation.value?.id
  if (stationId) {
    router.push(`/charts?station=${stationId}`)
  }
}

const viewStationStatistics = () => {
  if (selectedStation.value) {
    router.push(`/stations/${selectedStation.value.id}#statistics`)
  }
}

// Lifecycle
onMounted(() => {
  loadStations()
})
</script>

<style scoped>
.maps-view {
  min-height: 60vh;
}

.map-container {
  height: 500px;
  position: relative;
}

.map-placeholder {
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, #f0f9ff 0%, #e6f7ff 100%);
  border-radius: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.map-content {
  width: 95%;
  height: 95%;
}

.map-grid {
  width: 100%;
  height: 100%;
}
</style>

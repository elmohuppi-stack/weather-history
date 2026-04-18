<template>
  <div class="stations-view">
    <div class="container mx-auto px-4 py-8">
      <h1 class="text-3xl font-bold mb-6 text-gray-800">Wetterstationen</h1>
      
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Station Card -->
        <div v-for="station in stations" :key="station.id" 
             class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
          <div class="flex items-center mb-4">
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-4">
              <i class="pi pi-map-marker text-blue-600 text-xl"></i>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-800">{{ station.name }}</h3>
              <p class="text-sm text-gray-600">{{ station.location }}</p>
            </div>
          </div>
          
          <div class="space-y-2">
            <div class="flex justify-between">
              <span class="text-gray-600">Höhe:</span>
              <span class="font-medium">{{ station.elevation }} m</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Daten ab:</span>
              <span class="font-medium">{{ station.start_year }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Messungen:</span>
              <span class="font-medium">{{ station.measurement_count?.toLocaleString() || '0' }}</span>
            </div>
          </div>
          
          <div class="mt-6 pt-4 border-t">
            <router-link :to="`/stations/${station.id}`" 
                         class="inline-flex items-center text-blue-600 hover:text-blue-800">
              <span>Details anzeigen</span>
              <i class="pi pi-arrow-right ml-2"></i>
            </router-link>
          </div>
        </div>
      </div>
      
      <!-- Loading State -->
      <div v-if="loading" class="text-center py-12">
        <i class="pi pi-spin pi-spinner text-4xl text-blue-600"></i>
        <p class="mt-4 text-gray-600">Lade Stationsdaten...</p>
      </div>
      
      <!-- Error State -->
      <div v-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4 mt-6">
        <div class="flex items-center">
          <i class="pi pi-exclamation-triangle text-red-600 mr-3"></i>
          <div>
            <h3 class="font-medium text-red-800">Fehler beim Laden</h3>
            <p class="text-red-600 text-sm mt-1">{{ error }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useStationsStore } from '@/stores/stations'

const stationsStore = useStationsStore()
const loading = ref(true)
const error = ref<string | null>(null)

const stations = ref([
  {
    id: 1,
    name: 'Berlin-Tempelhof',
    location: 'Berlin, Deutschland',
    elevation: 48,
    start_year: 1990,
    measurement_count: 12450
  },
  {
    id: 2,
    name: 'Hamburg-Fuhlsbüttel',
    location: 'Hamburg, Deutschland',
    elevation: 16,
    start_year: 1990,
    measurement_count: 12450
  },
  {
    id: 3,
    name: 'München-Flughafen',
    location: 'München, Deutschland',
    elevation: 448,
    start_year: 1990,
    measurement_count: 12450
  },
  {
    id: 4,
    name: 'Köln-Bonn',
    location: 'Köln, Deutschland',
    elevation: 91,
    start_year: 1990,
    measurement_count: 12450
  },
  {
    id: 5,
    name: 'Frankfurt am Main',
    location: 'Frankfurt, Deutschland',
    elevation: 112,
    start_year: 1990,
    measurement_count: 12450
  },
  {
    id: 6,
    name: 'Stuttgart-Echterdingen',
    location: 'Stuttgart, Deutschland',
    elevation: 371,
    start_year: 1990,
    measurement_count: 12450
  }
])

onMounted(async () => {
  try {
    loading.value = true
    // In Zukunft: await stationsStore.fetchStations()
    // stations.value = stationsStore.stations
    await new Promise(resolve => setTimeout(resolve, 1000)) // Simulate API call
  } catch (err) {
    error.value = 'Stationsdaten konnten nicht geladen werden.'
    console.error('Error loading stations:', err)
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.stations-view {
  min-height: calc(100vh - 64px);
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}
</style>
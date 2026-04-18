<template>
  <div class="stations-view">
    <!-- Header -->
    <div class="mb-12">
      <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6 tracking-tight">
        Deutsche <span class="text-gradient">Wetterstationen</span>
      </h1>
      <p class="text-xl text-gray-600 max-w-3xl leading-relaxed">
        Übersicht aller 15 Wetterstationen des Deutschen Wetterdienstes mit täglichen Klimadaten von 1990 bis 2024.
      </p>
    </div>

    <!-- Filter Bar -->
    <div class="card mb-8">
      <div class="card-body">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Region</label>
            <select class="select w-full">
              <option>Alle Regionen</option>
              <option>Norddeutschland</option>
              <option>Ostdeutschland</option>
              <option>Westdeutschland</option>
              <option>Süddeutschland</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select class="select w-full">
              <option>Alle Stationen</option>
              <option>Aktiv</option>
              <option>Historisch</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Sortierung</label>
            <select class="select w-full">
              <option>Name A-Z</option>
              <option>Höhe (absteigend)</option>
              <option>Daten ab (aufsteigend)</option>
              <option>Messungen (absteigend)</option>
            </select>
          </div>
          <div class="flex items-end">
            <button class="btn-primary w-full">
              <i class="pi pi-filter mr-2"></i>
              Filter anwenden
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Stations Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
      <!-- Station Card -->
      <div v-for="station in stations" :key="station.id" 
           class="card hover-lift group">
        <div class="card-body">
          <div class="flex items-start mb-6">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-primary-100 to-primary-200 flex items-center justify-center mr-4 group-hover:scale-110 transition-transform">
              <i class="pi pi-map-marker text-primary-600 text-2xl"></i>
            </div>
            <div class="flex-1">
              <h3 class="text-xl font-bold text-gray-900 mb-1">{{ station.name }}</h3>
              <p class="text-gray-600">{{ station.location }}</p>
              <div class="flex items-center mt-2">
                <span class="badge-primary mr-2">DWD Station</span>
                <span class="badge-success">Aktiv</span>
              </div>
            </div>
          </div>
          
          <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="text-center p-4 rounded-xl bg-gray-50">
              <p class="text-sm text-gray-500 mb-1">Höhe</p>
              <p class="text-2xl font-bold text-gray-900">{{ station.elevation }} m</p>
            </div>
            <div class="text-center p-4 rounded-xl bg-gray-50">
              <p class="text-sm text-gray-500 mb-1">Daten ab</p>
              <p class="text-2xl font-bold text-gray-900">{{ station.start_year }}</p>
            </div>
          </div>
          
          <div class="flex items-center justify-between mb-6">
            <div>
              <p class="text-sm text-gray-500">Messungen</p>
              <p class="text-lg font-bold text-gray-900">{{ station.measurement_count?.toLocaleString() || '12450' }}</p>
            </div>
            <div class="text-right">
              <p class="text-sm text-gray-500">Letztes Update</p>
              <p class="text-lg font-bold text-gray-900">2024-12-31</p>
            </div>
          </div>
          
          <div class="pt-6 border-t border-gray-100">
            <router-link :to="`/stations/${station.id}`" 
                         class="btn-primary w-full group">
              <span>Details anzeigen</span>
              <i class="pi pi-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
            </router-link>
          </div>
        </div>
      </div>
    </div>

    <!-- Stats Footer -->
    <div class="card">
      <div class="card-body">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="text-center p-6">
            <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center mx-auto mb-4">
              <i class="pi pi-database text-blue-600 text-3xl"></i>
            </div>
            <h4 class="text-xl font-bold text-gray-900 mb-2">Datenvolumen</h4>
            <p class="text-3xl font-bold text-blue-700">2.3 GB</p>
            <p class="text-gray-600 mt-2">Komprimierte Rohdaten vom DWD</p>
          </div>
          <div class="text-center p-6">
            <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-green-50 to-green-100 flex items-center justify-center mx-auto mb-4">
              <i class="pi pi-calendar text-green-600 text-3xl"></i>
            </div>
            <h4 class="text-xl font-bold text-gray-900 mb-2">Zeitraum</h4>
            <p class="text-3xl font-bold text-green-700">34 Jahre</p>
            <p class="text-gray-600 mt-2">Vollständige historische Daten</p>
          </div>
          <div class="text-center p-6">
            <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-purple-50 to-purple-100 flex items-center justify-center mx-auto mb-4">
              <i class="pi pi-cloud text-purple-600 text-3xl"></i>
            </div>
            <h4 class="text-xl font-bold text-gray-900 mb-2">Parameter</h4>
            <p class="text-3xl font-bold text-purple-700">8</p>
            <p class="text-gray-600 mt-2">Klimaparameter pro Station</p>
          </div>
        </div>
      </div>
      <div class="card-footer">
        <div class="flex justify-between items-center">
          <p class="text-sm text-gray-600">
            Datenquelle: Deutscher Wetterdienst (DWD) – Open Data Server
          </p>
          <button class="btn-secondary">
            <i class="pi pi-download mr-2"></i>
            Alle Daten exportieren
          </button>
        </div>
      </div>
    </div>
    
    <!-- Loading State -->
    <div v-if="loading" class="text-center py-20">
      <div class="inline-block animate-spin rounded-full h-16 w-16 border-t-2 border-b-2 border-primary-600"></div>
      <p class="mt-6 text-xl text-gray-600">Lade Stationsdaten...</p>
      <p class="text-gray-500 mt-2">Bitte haben Sie einen Moment Geduld.</p>
    </div>
    
    <!-- Error State -->
    <div v-if="error" class="card border border-danger-200 bg-danger-50">
      <div class="card-body">
        <div class="flex items-center">
          <div class="w-12 h-12 rounded-xl bg-danger-100 flex items-center justify-center mr-4">
            <i class="pi pi-exclamation-triangle text-danger-600 text-2xl"></i>
          </div>
          <div>
            <h3 class="text-xl font-bold text-danger-800">Fehler beim Laden</h3>
            <p class="text-danger-600 mt-1">{{ error }}</p>
            <button class="btn-secondary mt-4">
              <i class="pi pi-refresh mr-2"></i>
              Erneut versuchen
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'

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
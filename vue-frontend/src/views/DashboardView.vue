<template>
  <div class="dashboard">
    <!-- Hero Section -->
    <div class="mb-12 text-center animate-fade-in">
      <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6 tracking-tight">
        Historische <span class="text-gradient">Wetterdaten</span> Deutschlands
      </h1>
      <p class="text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
        Visualisierung täglicher Klimadaten von 16 deutschen Wetterstationen vom Deutschen Wetterdienst.
        Zeitraum 1990–2024 mit über 2.3 Millionen Datensätzen.
      </p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12 animate-slide-up">
      <div class="card hover-lift">
        <div class="card-body">
          <div class="flex items-center">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-primary-100 to-primary-200 flex items-center justify-center mr-4">
              <i class="pi pi-map-marker text-primary-600 text-2xl"></i>
            </div>
            <div>
              <p class="text-sm text-gray-500 font-medium mb-1">Stationen</p>
              <p class="text-3xl font-bold text-gray-900">{{ stats.stations || '16' }}</p>
            </div>
          </div>
          <div class="mt-4 pt-4 border-t border-gray-100">
            <p class="text-xs text-gray-500">Deutsche Wetterstationen</p>
          </div>
        </div>
      </div>

      <div class="card hover-lift">
        <div class="card-body">
          <div class="flex items-center">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-success-100 to-success-200 flex items-center justify-center mr-4">
              <i class="pi pi-calendar text-success-600 text-2xl"></i>
            </div>
            <div>
              <p class="text-sm text-gray-500 font-medium mb-1">Jahre</p>
              <p class="text-3xl font-bold text-gray-900">{{ stats.years || '34' }}</p>
            </div>
          </div>
          <div class="mt-4 pt-4 border-t border-gray-100">
            <p class="text-xs text-gray-500">1990 – 2024</p>
          </div>
        </div>
      </div>

      <div class="card hover-lift">
        <div class="card-body">
          <div class="flex items-center">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-warning-100 to-warning-200 flex items-center justify-center mr-4">
              <i class="pi pi-database text-warning-600 text-2xl"></i>
            </div>
            <div>
              <p class="text-sm text-gray-500 font-medium mb-1">Datensätze</p>
              <p class="text-3xl font-bold text-gray-900">{{ stats.measurements || '2.3M' }}</p>
            </div>
          </div>
          <div class="mt-4 pt-4 border-t border-gray-100">
            <p class="text-xs text-gray-500">Tägliche Messungen</p>
          </div>
        </div>
      </div>

      <div class="card hover-lift">
        <div class="card-body">
          <div class="flex items-center">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-danger-100 to-danger-200 flex items-center justify-center mr-4">
              <i class="pi pi-cloud text-danger-600 text-2xl"></i>
            </div>
            <div>
              <p class="text-sm text-gray-500 font-medium mb-1">Parameter</p>
              <p class="text-3xl font-bold text-gray-900">{{ stats.parameters || '8' }}</p>
            </div>
          </div>
          <div class="mt-4 pt-4 border-t border-gray-100">
            <p class="text-xs text-gray-500">Klimaparameter</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
      <!-- Recent Stations -->
      <div class="card">
        <div class="card-header">
          <div class="flex items-center justify-between">
            <h3 class="text-xl font-bold text-gray-900">Aktuelle Stationen</h3>
            <span class="badge-primary">Live</span>
          </div>
        </div>
        <div class="card-body">
          <div class="space-y-4 scrollbar-thin max-h-96 overflow-y-auto">
            <div v-for="station in recentStations" :key="station.id" 
                 class="flex items-center justify-between p-4 rounded-xl hover:bg-gray-50 transition-colors group">
              <div class="flex items-center">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-50 to-primary-100 flex items-center justify-center mr-4 group-hover:scale-110 transition-transform">
                  <i class="pi pi-map-marker text-primary-600"></i>
                </div>
                <div>
                  <p class="font-semibold text-gray-900">{{ station.name }}</p>
                  <p class="text-sm text-gray-500">{{ station.state }}</p>
                </div>
              </div>
                <div class="text-right">
                  <p class="text-sm text-gray-500">Daten bis</p>
                  <p class="font-semibold text-gray-900">{{ station.latest_date }}</p>
                </div>
            </div>
          </div>
        </div>
        <div class="card-footer">
          <button @click="$router.push('/stations')" class="btn-primary w-full">
            <i class="pi pi-arrow-right mr-2"></i>
            Alle Stationen anzeigen
          </button>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="card">
        <div class="card-header">
          <h3 class="text-xl font-bold text-gray-900">Schnellzugriff</h3>
          <p class="text-sm text-gray-500 mt-1">Wählen Sie eine Funktion</p>
        </div>
        <div class="card-body">
          <div class="space-y-4">
            <button @click="$router.push('/charts')" 
                    class="w-full flex items-center justify-between p-5 rounded-xl border border-gray-200 hover:border-primary-300 hover:bg-primary-50 transition-all group">
              <div class="flex items-center">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-100 to-blue-200 flex items-center justify-center mr-4 group-hover:scale-110 transition-transform">
                  <i class="pi pi-chart-line text-blue-600 text-xl"></i>
                </div>
                <div class="text-left">
                  <p class="font-semibold text-gray-900">Diagramme erstellen</p>
                  <p class="text-sm text-gray-500">Temperatur, Niederschlag, Sonne</p>
                </div>
              </div>
              <i class="pi pi-chevron-right text-gray-400 group-hover:text-primary-600 group-hover:translate-x-1 transition-all"></i>
            </button>
            
            <button @click="$router.push('/maps')" 
                    class="w-full flex items-center justify-between p-5 rounded-xl border border-gray-200 hover:border-success-300 hover:bg-success-50 transition-all group">
              <div class="flex items-center">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-green-100 to-green-200 flex items-center justify-center mr-4 group-hover:scale-110 transition-transform">
                  <i class="pi pi-map text-green-600 text-xl"></i>
                </div>
                <div class="text-left">
                  <p class="font-semibold text-gray-900">Kartenansicht</p>
                  <p class="text-sm text-gray-500">Interaktive Deutschland-Karte</p>
                </div>
              </div>
              <i class="pi pi-chevron-right text-gray-400 group-hover:text-success-600 group-hover:translate-x-1 transition-all"></i>
            </button>
            
            <button @click="$router.push('/export')" 
                    class="w-full flex items-center justify-between p-5 rounded-xl border border-gray-200 hover:border-purple-300 hover:bg-purple-50 transition-all group">
              <div class="flex items-center">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-100 to-purple-200 flex items-center justify-center mr-4 group-hover:scale-110 transition-transform">
                  <i class="pi pi-download text-purple-600 text-xl"></i>
                </div>
                <div class="text-left">
                  <p class="font-semibold text-gray-900">Daten exportieren</p>
                  <p class="text-sm text-gray-500">CSV, JSON, Excel, SQL</p>
                </div>
              </div>
              <i class="pi pi-chevron-right text-gray-400 group-hover:text-purple-600 group-hover:translate-x-1 transition-all"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Weather Preview -->
    <div class="card mb-12">
      <div class="card-header">
        <h3 class="text-xl font-bold text-gray-900">Wetterdaten Vorschau</h3>
        <p class="text-sm text-gray-500 mt-1">Beispieldaten vom DWD Open Data Server</p>
      </div>
      <div class="card-body">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="text-center p-6 rounded-2xl bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200">
            <i class="pi pi-thermometer text-4xl text-blue-600 mb-4"></i>
            <h4 class="font-bold text-gray-900 mb-2">Temperatur</h4>
            <p class="text-3xl font-bold text-blue-700">12.4°C</p>
            <p class="text-sm text-gray-600 mt-2">Durchschnitt 2024</p>
          </div>
          <div class="text-center p-6 rounded-2xl bg-gradient-to-br from-green-50 to-green-100 border border-green-200">
            <i class="pi pi-cloud-rain text-4xl text-green-600 mb-4"></i>
            <h4 class="font-bold text-gray-900 mb-2">Niederschlag</h4>
            <p class="text-3xl font-bold text-green-700">789 mm</p>
            <p class="text-sm text-gray-600 mt-2">Jahressumme 2024</p>
          </div>
          <div class="text-center p-6 rounded-2xl bg-gradient-to-br from-yellow-50 to-yellow-100 border border-yellow-200">
            <i class="pi pi-sun text-4xl text-yellow-600 mb-4"></i>
            <h4 class="font-bold text-gray-900 mb-2">Sonnenschein</h4>
            <p class="text-3xl font-bold text-yellow-700">1,845 h</p>
            <p class="text-sm text-gray-600 mt-2">Jahressumme 2024</p>
          </div>
        </div>
      </div>
      <div class="card-footer">
        <div class="flex justify-between items-center">
          <p class="text-sm text-gray-600">Datenquelle: Deutscher Wetterdienst (DWD) Open Data</p>
          <button @click="$router.push('/charts')" class="btn-secondary">
            <i class="pi pi-chart-bar mr-2"></i>
            Mehr Analysen
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { apiService, type Station } from '@/services/api'

interface Stats {
  stations: number
  years: number
  measurements: string
  parameters: number
}

const stats = ref<Stats>({
  stations: 0,
  years: 34,
  measurements: '0',
  parameters: 8
})

const recentStations = ref<Station[]>([])
const isLoading = ref(true)
const error = ref<string | null>(null)

onMounted(async () => {
  try {
    isLoading.value = true
    error.value = null
    
    // Fetch stations from API
    const response = await apiService.getStations()
    
    if (response.success) {
      recentStations.value = response.data
      stats.value.stations = response.meta?.total || response.data.length
      
      // Calculate total measurements (simplified - would need actual count from API)
      const totalMeasurements = response.data.reduce((sum, station) => sum + station.measurement_count, 0)
      stats.value.measurements = totalMeasurements.toLocaleString()
    } else {
      error.value = 'Failed to load station data'
    }
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Unknown error occurred'
    console.error('Error loading stations:', err)
  } finally {
    isLoading.value = false
  }
})
</script>

<style scoped>
.dashboard {
  min-height: 60vh;
}
</style>
<template>
  <div class="station-detail-view">
    <div class="container mx-auto px-4 py-8">
      <div class="mb-8">
        <router-link to="/stations" class="inline-flex items-center text-blue-600 hover:text-blue-800 mb-4">
          <i class="pi pi-arrow-left mr-2"></i>
          <span>Zurück zu Stationen</span>
        </router-link>
        
        <div class="bg-white rounded-lg shadow-lg p-6">
          <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
            <div>
              <h1 class="text-3xl font-bold text-gray-800">{{ station.name }}</h1>
              <p class="text-gray-600 mt-2">{{ station.location }}</p>
            </div>
            <div class="mt-4 md:mt-0">
              <div class="inline-flex items-center px-4 py-2 bg-blue-100 text-blue-800 rounded-full">
                <i class="pi pi-map-marker mr-2"></i>
                <span>Höhe: {{ station.elevation }} m</span>
              </div>
            </div>
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-gray-50 rounded-lg p-4">
              <div class="flex items-center">
                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                  <i class="pi pi-calendar text-blue-600"></i>
                </div>
                <div>
                  <p class="text-sm text-gray-600">Daten verfügbar ab</p>
                  <p class="text-lg font-semibold">{{ station.start_year }}</p>
                </div>
              </div>
            </div>
            
            <div class="bg-gray-50 rounded-lg p-4">
              <div class="flex items-center">
                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-3">
                  <i class="pi pi-chart-line text-green-600"></i>
                </div>
                <div>
                  <p class="text-sm text-gray-600">Messungen</p>
                  <p class="text-lg font-semibold">{{ station.measurement_count?.toLocaleString() || '0' }}</p>
                </div>
              </div>
            </div>
            
            <div class="bg-gray-50 rounded-lg p-4">
              <div class="flex items-center">
                <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center mr-3">
                  <i class="pi pi-database text-purple-600"></i>
                </div>
                <div>
                  <p class="text-sm text-gray-600">Letztes Update</p>
                  <p class="text-lg font-semibold">{{ station.last_update || '2024-12-31' }}</p>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Placeholder for charts -->
          <div class="bg-gray-100 rounded-lg p-8 text-center">
            <i class="pi pi-chart-bar text-4xl text-gray-400 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">Wetterdaten Diagramme</h3>
            <p class="text-gray-600">Hier werden Temperatur-, Niederschlags- und Sonnenscheindauer-Diagramme angezeigt.</p>
            <p class="text-gray-500 text-sm mt-2">(Wird mit echten Daten gefüllt)</p>
          </div>
        </div>
      </div>
      
      <!-- Loading State -->
      <div v-if="loading" class="text-center py-12">
        <i class="pi pi-spin pi-spinner text-4xl text-blue-600"></i>
        <p class="mt-4 text-gray-600">Lade Stationsdetails...</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const loading = ref(false)

const station = ref({
  id: parseInt(route.params.id as string),
  name: 'Berlin-Tempelhof',
  location: 'Berlin, Deutschland',
  elevation: 48,
  start_year: 1990,
  measurement_count: 12450,
  last_update: '2024-12-31'
})

onMounted(() => {
  // In Zukunft: API call to fetch station details
  loading.value = false
})
</script>

<style scoped>
.station-detail-view {
  min-height: calc(100vh - 64px);
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}
</style>
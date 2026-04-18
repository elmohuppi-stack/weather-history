<template>
  <div class="dashboard">
    <div class="mb-8">
      <h2 class="text-2xl font-bold text-gray-800 mb-4">Dashboard</h2>
      <p class="text-gray-600">
        Willkommen bei der Visualisierung historischer Wetterdaten deutscher Stationen.
        Wählen Sie eine Station oder einen Zeitraum aus, um Daten anzuzeigen.
      </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
            <i class="pi pi-map-marker text-xl"></i>
          </div>
          <div>
            <p class="text-sm text-gray-500">Stationen</p>
            <p class="text-2xl font-bold">{{ stats.stations || '15' }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
            <i class="pi pi-calendar text-xl"></i>
          </div>
          <div>
            <p class="text-sm text-gray-500">Jahre</p>
            <p class="text-2xl font-bold">{{ stats.years || '34' }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-3 rounded-full bg-purple-100 text-purple-600 mr-4">
            <i class="pi pi-database text-xl"></i>
          </div>
          <div>
            <p class="text-sm text-gray-500">Datensätze</p>
            <p class="text-2xl font-bold">{{ stats.measurements || '2.3M' }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-3 rounded-full bg-orange-100 text-orange-600 mr-4">
            <i class="pi pi-cloud text-xl"></i>
          </div>
          <div>
            <p class="text-sm text-gray-500">Parameter</p>
            <p class="text-2xl font-bold">{{ stats.parameters || '8' }}</p>
          </div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Aktuelle Stationen</h3>
        <div class="space-y-4">
          <div v-for="station in recentStations" :key="station.id" 
               class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg">
            <div>
              <p class="font-medium">{{ station.name }}</p>
              <p class="text-sm text-gray-500">{{ station.state }}</p>
            </div>
            <div class="text-right">
              <p class="text-sm text-gray-500">Daten bis</p>
              <p class="font-medium">{{ station.latestDate }}</p>
            </div>
          </div>
        </div>
        <button @click="$router.push('/stations')" 
                class="mt-4 w-full py-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition-colors">
          Alle Stationen anzeigen
        </button>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Schnellzugriff</h3>
        <div class="space-y-3">
          <button @click="$router.push('/charts')" 
                  class="w-full flex items-center justify-between p-4 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors">
            <div class="flex items-center">
              <i class="pi pi-chart-line text-blue-600 mr-3"></i>
              <span>Diagramme erstellen</span>
            </div>
            <i class="pi pi-chevron-right text-gray-400"></i>
          </button>
          
          <button @click="$router.push('/maps')" 
                  class="w-full flex items-center justify-between p-4 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors">
            <div class="flex items-center">
              <i class="pi pi-map text-green-600 mr-3"></i>
              <span>Kartenansicht</span>
            </div>
            <i class="pi pi-chevron-right text-gray-400"></i>
          </button>
          
          <button @click="$router.push('/export')" 
                  class="w-full flex items-center justify-between p-4 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors">
            <div class="flex items-center">
              <i class="pi pi-download text-purple-600 mr-3"></i>
              <span>Daten exportieren</span>
            </div>
            <i class="pi pi-chevron-right text-gray-400"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'

interface Station {
  id: string
  name: string
  state: string
  latestDate: string
}

interface Stats {
  stations: number
  years: number
  measurements: string
  parameters: number
}

const stats = ref<Stats>({
  stations: 15,
  years: 34,
  measurements: '2.3M',
  parameters: 8
})

const recentStations = ref<Station[]>([
  { id: '01048', name: 'Berlin-Tempelhof', state: 'Berlin', latestDate: '2024-12-31' },
  { id: '01358', name: 'Hamburg-Fuhlsbüttel', state: 'Hamburg', latestDate: '2024-12-31' },
  { id: '01050', name: 'München-Stadt', state: 'Bayern', latestDate: '2024-12-31' },
  { id: '01270', name: 'Köln-Bonn', state: 'NRW', latestDate: '2024-12-31' },
  { id: '01420', name: 'Frankfurt/Main', state: 'Hessen', latestDate: '2024-12-31' }
])
</script>

<style scoped>
.dashboard {
  min-height: 60vh;
}
</style>
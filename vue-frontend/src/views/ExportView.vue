<template>
  <div class="export-view">
    <div class="container mx-auto px-4 py-8">
      <h1 class="text-3xl font-bold mb-6 text-gray-800">Datenexport</h1>
      
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Export Options -->
        <div class="bg-white rounded-lg shadow-lg p-6">
          <h2 class="text-xl font-semibold text-gray-800 mb-6">Export-Einstellungen</h2>
          
          <div class="space-y-6">
            <!-- Station Selection -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-3">Stationen auswählen</label>
              <div class="space-y-2">
                <div v-for="station in stations" :key="station.id" class="flex items-center">
                  <input type="checkbox" :id="'station-' + station.id" 
                         v-model="selectedStations" :value="station.id"
                         class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                  <label :for="'station-' + station.id" class="ml-3 text-sm text-gray-700">
                    {{ station.name }}
                  </label>
                </div>
              </div>
              <div class="mt-3">
                <button @click="selectAllStations" class="text-sm text-blue-600 hover:text-blue-800 mr-4">
                  Alle auswählen
                </button>
                <button @click="deselectAllStations" class="text-sm text-gray-600 hover:text-gray-800">
                  Auswahl aufheben
                </button>
              </div>
            </div>
            
            <!-- Time Range -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-3">Zeitraum</label>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs text-gray-600 mb-1">Von</label>
                  <input type="number" v-model="startYear" min="1990" max="2024" 
                         class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                  <label class="block text-xs text-gray-600 mb-1">Bis</label>
                  <input type="number" v-model="endYear" min="1990" max="2024" 
                         class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
              </div>
              <div class="mt-2">
                <button @click="setFullRange" class="text-sm text-blue-600 hover:text-blue-800">
                  Vollständigen Zeitraum (1990-2024) verwenden
                </button>
              </div>
            </div>
            
            <!-- Data Parameters -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-3">Datenparameter</label>
              <div class="space-y-2">
                <div class="flex items-center">
                  <input type="checkbox" id="param-temp" v-model="selectedParams" value="temperature" 
                         class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                  <label for="param-temp" class="ml-3 text-sm text-gray-700">
                    Temperatur (°C)
                  </label>
                </div>
                <div class="flex items-center">
                  <input type="checkbox" id="param-precip" v-model="selectedParams" value="precipitation" 
                         class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                  <label for="param-precip" class="ml-3 text-sm text-gray-700">
                    Niederschlag (mm)
                  </label>
                </div>
                <div class="flex items-center">
                  <input type="checkbox" id="param-sunshine" v-model="selectedParams" value="sunshine" 
                         class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                  <label for="param-sunshine" class="ml-3 text-sm text-gray-700">
                    Sonnenscheindauer (h)
                  </label>
                </div>
              </div>
            </div>
            
            <!-- Export Format -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-3">Export-Format</label>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <input type="radio" id="format-csv" v-model="exportFormat" value="csv" 
                         class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                  <label for="format-csv" class="ml-2 text-sm text-gray-700">CSV</label>
                </div>
                <div>
                  <input type="radio" id="format-json" v-model="exportFormat" value="json" 
                         class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                  <label for="format-json" class="ml-2 text-sm text-gray-700">JSON</label>
                </div>
                <div>
                  <input type="radio" id="format-excel" v-model="exportFormat" value="excel" 
                         class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                  <label for="format-excel" class="ml-2 text-sm text-gray-700">Excel</label>
                </div>
                <div>
                  <input type="radio" id="format-sql" v-model="exportFormat" value="sql" 
                         class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                  <label for="format-sql" class="ml-2 text-sm text-gray-700">SQL</label>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Export Preview & Actions -->
        <div class="space-y-8">
          <!-- Preview -->
          <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Export-Vorschau</h2>
            <div class="bg-gray-50 rounded-lg p-4">
              <div class="text-sm text-gray-600 space-y-2">
                <p><span class="font-medium">Ausgewählte Stationen:</span> {{ selectedStations.length }} von {{ stations.length }}</p>
                <p><span class="font-medium">Zeitraum:</span> {{ startYear }} - {{ endYear }}</p>
                <p><span class="font-medium">Parameter:</span> {{ selectedParams.length }} ausgewählt</p>
                <p><span class="font-medium">Format:</span> {{ exportFormat.toUpperCase() }}</p>
                <p><span class="font-medium">Geschätzte Datenmenge:</span> {{ estimatedDataPoints.toLocaleString() }} Datenpunkte</p>
              </div>
            </div>
            <div class="mt-6">
              <button @click="generatePreview" 
                      class="w-full px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <i class="pi pi-eye mr-2"></i>
                Vorschau generieren
              </button>
            </div>
          </div>
          
          <!-- Export Actions -->
          <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Export starten</h2>
            <div class="space-y-4">
              <button @click="startExport" 
                      class="w-full px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors flex items-center justify-center">
                <i class="pi pi-download mr-2"></i>
                Daten exportieren
              </button>
              
              <div class="text-center text-sm text-gray-600">
                <p>Der Export wird als Datei heruntergeladen.</p>
                <p class="mt-1">Bei großen Datenmengen kann dies einige Minuten dauern.</p>
              </div>
              
              <div class="pt-4 border-t">
                <h3 class="font-medium text-gray-800 mb-2">Export-Historie</h3>
                <div class="text-sm text-gray-600">
                  <p>Letzter Export: <span class="font-medium">Noch keine Exporte</span></p>
                  <p class="mt-1">Gesamtexporte: 0</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'

const stations = [
  { id: 1, name: 'Berlin-Tempelhof' },
  { id: 2, name: 'Hamburg-Fuhlsbüttel' },
  { id: 3, name: 'München-Flughafen' },
  { id: 4, name: 'Köln-Bonn' },
  { id: 5, name: 'Frankfurt am Main' },
  { id: 6, name: 'Stuttgart-Echterdingen' }
]

const selectedStations = ref([1, 2, 3, 4, 5, 6])
const startYear = ref(1990)
const endYear = ref(2024)
const selectedParams = ref(['temperature', 'precipitation', 'sunshine'])
const exportFormat = ref('csv')

const estimatedDataPoints = computed(() => {
  const years = endYear.value - startYear.value + 1
  const stationsCount = selectedStations.value.length
  const paramsCount = selectedParams.value.length
  return years * 365 * stationsCount * paramsCount // Rough estimate
})

const selectAllStations = () => {
  selectedStations.value = stations.map(s => s.id)
}

const deselectAllStations = () => {
  selectedStations.value = []
}

const setFullRange = () => {
  startYear.value = 1990
  endYear.value = 2024
}

const generatePreview = () => {
  alert('Vorschau wird generiert... (Demo)')
}

const startExport = () => {
  alert(`Export gestartet: ${selectedStations.value.length} Stationen, ${startYear.value}-${endYear.value}, Format: ${exportFormat.value}`)
}
</script>

<style scoped>
.export-view {
  min-height: calc(100vh - 64px);
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}
</style>
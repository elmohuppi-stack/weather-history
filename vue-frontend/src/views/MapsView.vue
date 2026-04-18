<template>
  <div class="maps-view">
    <div class="container mx-auto px-4 py-8">
      <h1 class="text-3xl font-bold mb-6 text-gray-800">Wetterstationen Karte</h1>
      
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Map Container -->
        <div class="lg:col-span-2">
          <div class="bg-white rounded-lg shadow-lg p-4">
            <div class="h-96 bg-gray-200 rounded flex items-center justify-center">
              <div class="text-center">
                <i class="pi pi-map text-4xl text-gray-400 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">Deutschland Wetterstationen</h3>
                <p class="text-gray-600">Interaktive Karte mit allen Wetterstationen</p>
                <p class="text-gray-500 text-sm mt-2">(Leaflet.js Integration)</p>
              </div>
            </div>
            <div class="mt-4 flex justify-between items-center">
              <div class="text-sm text-gray-600">
                <p><span class="inline-block w-3 h-3 bg-blue-500 rounded-full mr-2"></span> Aktive Stationen: 15</p>
              </div>
              <div class="flex space-x-2">
                <button class="px-4 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200">
                  <i class="pi pi-search mr-2"></i>
                  Zoomen
                </button>
                <button class="px-4 py-2 bg-green-100 text-green-700 rounded-lg hover:bg-green-200">
                  <i class="pi pi-filter mr-2"></i>
                  Filter
                </button>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Station List -->
        <div>
          <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Stationen auf der Karte</h2>
            <div class="space-y-4 max-h-80 overflow-y-auto">
              <div v-for="station in stations" :key="station.id" 
                   class="flex items-center p-3 hover:bg-gray-50 rounded-lg cursor-pointer">
                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                  <i class="pi pi-map-marker text-blue-600"></i>
                </div>
                <div class="flex-1">
                  <h4 class="font-medium text-gray-800">{{ station.name }}</h4>
                  <p class="text-sm text-gray-600">{{ station.region }}</p>
                </div>
                <div class="text-right">
                  <p class="text-sm font-medium text-gray-800">{{ station.elevation }} m</p>
                  <p class="text-xs text-gray-500">{{ station.status }}</p>
                </div>
              </div>
            </div>
            
            <div class="mt-6 pt-4 border-t">
              <h3 class="font-medium text-gray-800 mb-3">Karten-Legende</h3>
              <div class="space-y-2">
                <div class="flex items-center">
                  <div class="w-4 h-4 bg-blue-500 rounded-full mr-3"></div>
                  <span class="text-sm text-gray-700">Aktive Station</span>
                </div>
                <div class="flex items-center">
                  <div class="w-4 h-4 bg-green-500 rounded-full mr-3"></div>
                  <span class="text-sm text-gray-700">Hauptstation</span>
                </div>
                <div class="flex items-center">
                  <div class="w-4 h-4 bg-yellow-500 rounded-full mr-3"></div>
                  <span class="text-sm text-gray-700">Historische Station</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Map Controls -->
      <div class="mt-8 bg-gray-50 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Karten-Einstellungen</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Karten-Layer</label>
            <select class="w-full px-4 py-2 border border-gray-300 rounded-lg">
              <option>OpenStreetMap</option>
              <option>Topographische Karte</option>
              <option>Satellitenbild</option>
              <option>Reliefkarte</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Daten-Layer</label>
            <select class="w-full px-4 py-2 border border-gray-300 rounded-lg">
              <option>Temperatur (aktuell)</option>
              <option>Niederschlag (Jahressumme)</option>
              <option>Sonnenscheindauer</option>
              <option>Stationen nur</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Zeitraum</label>
            <select class="w-full px-4 py-2 border border-gray-300 rounded-lg">
              <option>Aktuelles Jahr</option>
              <option>Letzte 5 Jahre</option>
              <option>1990-2024 (Vollständig)</option>
              <option>Benutzerdefiniert</option>
            </select>
          </div>
          <div class="flex items-end">
            <button class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
              <i class="pi pi-refresh mr-2"></i>
              Karte aktualisieren
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
const stations = [
  { id: 1, name: 'Berlin-Tempelhof', region: 'Berlin', elevation: 48, status: 'Aktiv' },
  { id: 2, name: 'Hamburg-Fuhlsbüttel', region: 'Hamburg', elevation: 16, status: 'Aktiv' },
  { id: 3, name: 'München-Flughafen', region: 'Bayern', elevation: 448, status: 'Aktiv' },
  { id: 4, name: 'Köln-Bonn', region: 'NRW', elevation: 91, status: 'Aktiv' },
  { id: 5, name: 'Frankfurt am Main', region: 'Hessen', elevation: 112, status: 'Aktiv' },
  { id: 6, name: 'Stuttgart-Echterdingen', region: 'Baden-Württemberg', elevation: 371, status: 'Aktiv' },
  { id: 7, name: 'Leipzig/Halle', region: 'Sachsen', elevation: 133, status: 'Aktiv' },
  { id: 8, name: 'Nürnberg', region: 'Bayern', elevation: 312, status: 'Aktiv' }
]
</script>

<style scoped>
.maps-view {
  min-height: calc(100vh - 64px);
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}
</style>
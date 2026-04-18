<template>
  <div class="charts-view">
    <div class="container mx-auto px-4 py-8">
      <h1 class="text-3xl font-bold mb-6 text-gray-800">Wetterdiagramme</h1>
      
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Temperature Chart -->
        <div class="bg-white rounded-lg shadow-lg p-6">
          <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-semibold text-gray-800">Temperaturverlauf</h2>
            <div class="flex items-center space-x-2">
              <i class="pi pi-thermometer text-red-500"></i>
              <span class="text-sm text-gray-600">1990-2024</span>
            </div>
          </div>
          <div class="h-64 bg-gray-100 rounded flex items-center justify-center">
            <div class="text-center">
              <i class="pi pi-chart-line text-4xl text-gray-400 mb-4"></i>
              <p class="text-gray-600">Temperaturdiagramm wird geladen</p>
              <p class="text-gray-500 text-sm mt-2">(Chart.js Integration)</p>
            </div>
          </div>
          <div class="mt-4 text-sm text-gray-600">
            <p>Durchschnittliche Jahrestemperaturen für ausgewählte Stationen.</p>
          </div>
        </div>
        
        <!-- Precipitation Chart -->
        <div class="bg-white rounded-lg shadow-lg p-6">
          <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-semibold text-gray-800">Niederschlagsmengen</h2>
            <div class="flex items-center space-x-2">
              <i class="pi pi-cloud-rain text-blue-500"></i>
              <span class="text-sm text-gray-600">1990-2024</span>
            </div>
          </div>
          <div class="h-64 bg-gray-100 rounded flex items-center justify-center">
            <div class="text-center">
              <i class="pi pi-chart-bar text-4xl text-gray-400 mb-4"></i>
              <p class="text-gray-600">Niederschlagsdiagramm wird geladen</p>
              <p class="text-gray-500 text-sm mt-2">(Chart.js Integration)</p>
            </div>
          </div>
          <div class="mt-4 text-sm text-gray-600">
            <p>Jährliche Niederschlagsmengen in mm für deutsche Wetterstationen.</p>
          </div>
        </div>
        
        <!-- Sunshine Chart -->
        <div class="bg-white rounded-lg shadow-lg p-6 lg:col-span-2">
          <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-semibold text-gray-800">Sonnenscheindauer</h2>
            <div class="flex items-center space-x-2">
              <i class="pi pi-sun text-yellow-500"></i>
              <span class="text-sm text-gray-600">1990-2024</span>
            </div>
          </div>
          <div class="h-80 bg-gray-100 rounded flex items-center justify-center">
            <div class="text-center">
              <i class="pi pi-chart-pie text-4xl text-gray-400 mb-4"></i>
              <p class="text-gray-600">Sonnenscheindauer-Diagramm wird geladen</p>
              <p class="text-gray-500 text-sm mt-2">(Chart.js Integration)</p>
            </div>
          </div>
          <div class="mt-4 text-sm text-gray-600">
            <p>Monatliche Sonnenscheindauer in Stunden für verschiedene Regionen Deutschlands.</p>
          </div>
        </div>
      </div>
      
      <!-- Filter Controls -->
      <div class="mt-8 bg-gray-50 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Diagramm-Einstellungen</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Station auswählen</label>
            <div class="relative">
              <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option>Alle Stationen</option>
                <option>Berlin-Tempelhof</option>
                <option>Hamburg-Fuhlsbüttel</option>
                <option>München-Flughafen</option>
              </select>
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Zeitraum</label>
            <div class="relative">
              <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option>1990-2024 (Vollständig)</option>
                <option>2000-2024</option>
                <option>2010-2024</option>
                <option>Letzte 10 Jahre</option>
              </select>
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Aggregation</label>
            <div class="relative">
              <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option>Jährlich</option>
                <option>Monatlich</option>
                <option>Täglich (ausgewählter Monat)</option>
              </select>
            </div>
          </div>
        </div>
        <div class="mt-6 flex justify-end">
          <button class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
            <i class="pi pi-refresh mr-2"></i>
            Diagramme aktualisieren
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
// Chart.js integration will be added here
</script>

<style scoped>
.charts-view {
  min-height: calc(100vh - 64px);
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}
</style>
<template>
  <div class="charts-view">
    <!-- Header -->
    <div class="mb-12">
      <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6 tracking-tight">
        Wetter <span class="text-gradient">Diagramme</span>
      </h1>
      <p class="text-xl text-gray-600 max-w-3xl leading-relaxed">
        Interaktive Visualisierung historischer Wetterdaten mit Chart.js. Analysieren Sie Temperatur, Niederschlag und Sonnenscheindauer.
      </p>
    </div>

    <!-- Chart Controls -->
    <div class="card mb-8">
      <div class="card-header">
        <h3 class="text-xl font-bold text-gray-900">Diagramm-Konfiguration</h3>
        <p class="text-sm text-gray-500 mt-1">Passen Sie die Diagramme an Ihre Anforderungen an</p>
      </div>
      <div class="card-body">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Station</label>
            <select class="select w-full">
              <option>Alle Stationen</option>
              <option>Berlin-Tempelhof</option>
              <option>Hamburg-Fuhlsbüttel</option>
              <option>München-Flughafen</option>
              <option>Köln-Bonn</option>
              <option>Frankfurt am Main</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Zeitraum</label>
            <select class="select w-full">
              <option>1990-2024 (Vollständig)</option>
              <option>2000-2024</option>
              <option>2010-2024</option>
              <option>Letzte 10 Jahre</option>
              <option>Benutzerdefiniert</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Aggregation</label>
            <select class="select w-full">
              <option>Jährlich</option>
              <option>Monatlich</option>
              <option>Vierteljährlich</option>
              <option>Täglich (ausgewählter Monat)</option>
            </select>
          </div>
          <div class="flex items-end">
            <button class="btn-primary w-full">
              <i class="pi pi-refresh mr-2"></i>
              Diagramme aktualisieren
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
      <!-- Temperature Chart -->
      <div class="card hover-lift">
        <div class="card-header">
          <div class="flex items-center justify-between">
            <h3 class="text-xl font-bold text-gray-900">Temperaturverlauf</h3>
            <div class="flex items-center space-x-2">
              <i class="pi pi-thermometer text-danger-500"></i>
              <span class="text-sm font-medium text-gray-700">1990-2024</span>
            </div>
          </div>
        </div>
        <div class="card-body">
          <div class="h-80 rounded-2xl bg-gradient-to-br from-red-50 to-red-100 border border-red-200 flex items-center justify-center">
            <div class="text-center p-8">
              <i class="pi pi-chart-line text-5xl text-red-400 mb-6"></i>
              <h4 class="text-2xl font-bold text-gray-800 mb-2">Temperaturdiagramm</h4>
              <p class="text-gray-600 mb-4">Durchschnittliche Jahrestemperaturen für ausgewählte Stationen</p>
              <div class="inline-flex items-center px-4 py-2 bg-white rounded-xl border border-red-200">
                <span class="text-sm text-gray-700">Chart.js Integration</span>
                <i class="pi pi-arrow-up-right ml-2 text-red-500"></i>
              </div>
            </div>
          </div>
        </div>
        <div class="card-footer">
          <div class="flex justify-between items-center">
            <div class="text-sm text-gray-600">
              <p>Parameter: Mittlere Jahrestemperatur (°C)</p>
            </div>
            <button class="btn-secondary">
              <i class="pi pi-download mr-2"></i>
              Daten exportieren
            </button>
          </div>
        </div>
      </div>
      
      <!-- Precipitation Chart -->
      <div class="card hover-lift">
        <div class="card-header">
          <div class="flex items-center justify-between">
            <h3 class="text-xl font-bold text-gray-900">Niederschlagsmengen</h3>
            <div class="flex items-center space-x-2">
              <i class="pi pi-cloud-rain text-primary-500"></i>
              <span class="text-sm font-medium text-gray-700">1990-2024</span>
            </div>
          </div>
        </div>
        <div class="card-body">
          <div class="h-80 rounded-2xl bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 flex items-center justify-center">
            <div class="text-center p-8">
              <i class="pi pi-chart-bar text-5xl text-blue-400 mb-6"></i>
              <h4 class="text-2xl font-bold text-gray-800 mb-2">Niederschlagsdiagramm</h4>
              <p class="text-gray-600 mb-4">Jährliche Niederschlagsmengen in mm für deutsche Wetterstationen</p>
              <div class="inline-flex items-center px-4 py-2 bg-white rounded-xl border border-blue-200">
                <span class="text-sm text-gray-700">Chart.js Integration</span>
                <i class="pi pi-arrow-up-right ml-2 text-blue-500"></i>
              </div>
            </div>
          </div>
        </div>
        <div class="card-footer">
          <div class="flex justify-between items-center">
            <div class="text-sm text-gray-600">
              <p>Parameter: Jahresniederschlagssumme (mm)</p>
            </div>
            <button class="btn-secondary">
              <i class="pi pi-download mr-2"></i>
              Daten exportieren
            </button>
          </div>
        </div>
      </div>
      
      <!-- Sunshine Chart -->
      <div class="card hover-lift lg:col-span-2">
        <div class="card-header">
          <div class="flex items-center justify-between">
            <h3 class="text-xl font-bold text-gray-900">Sonnenscheindauer</h3>
            <div class="flex items-center space-x-2">
              <i class="pi pi-sun text-warning-500"></i>
              <span class="text-sm font-medium text-gray-700">1990-2024</span>
            </div>
          </div>
        </div>
        <div class="card-body">
          <div class="h-96 rounded-2xl bg-gradient-to-br from-yellow-50 to-yellow-100 border border-yellow-200 flex items-center justify-center">
            <div class="text-center p-8 max-w-2xl">
              <i class="pi pi-chart-pie text-5xl text-yellow-400 mb-6"></i>
              <h4 class="text-2xl font-bold text-gray-800 mb-2">Sonnenscheindauer-Analyse</h4>
              <p class="text-gray-600 mb-6">Monatliche Sonnenscheindauer in Stunden für verschiedene Regionen Deutschlands</p>
              <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="text-center p-4 bg-white rounded-xl border border-yellow-200">
                  <p class="text-sm text-gray-500">Norddeutschland</p>
                  <p class="text-xl font-bold text-gray-900">1,650 h</p>
                </div>
                <div class="text-center p-4 bg-white rounded-xl border border-yellow-200">
                  <p class="text-sm text-gray-500">Süddeutschland</p>
                  <p class="text-xl font-bold text-gray-900">1,890 h</p>
                </div>
                <div class="text-center p-4 bg-white rounded-xl border border-yellow-200">
                  <p class="text-sm text-gray-500">Ostdeutschland</p>
                  <p class="text-xl font-bold text-gray-900">1,720 h</p>
                </div>
                <div class="text-center p-4 bg-white rounded-xl border border-yellow-200">
                  <p class="text-sm text-gray-500">Westdeutschland</p>
                  <p class="text-xl font-bold text-gray-900">1,780 h</p>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="card-footer">
          <div class="flex justify-between items-center">
            <div class="text-sm text-gray-600">
              <p>Parameter: Monatliche Sonnenscheindauer (Stunden), Jahresmittelwerte</p>
            </div>
            <button class="btn-secondary">
              <i class="pi pi-download mr-2"></i>
              Daten exportieren
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Advanced Options -->
    <div class="card">
      <div class="card-header">
        <h3 class="text-xl font-bold text-gray-900">Erweiterte Optionen</h3>
        <p class="text-sm text-gray-500 mt-1">Weitere Analysemöglichkeiten und Exportformate</p>
      </div>
      <div class="card-body">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="text-center p-6 rounded-2xl bg-gradient-to-br from-gray-50 to-gray-100 border border-gray-200">
            <i class="pi pi-table text-4xl text-gray-600 mb-4"></i>
            <h4 class="font-bold text-gray-900 mb-2">Daten-Tabelle</h4>
            <p class="text-gray-600 text-sm mb-4">Rohdaten in tabellarischer Form anzeigen</p>
            <button class="btn-secondary w-full">
              Tabelle öffnen
            </button>
          </div>
          <div class="text-center p-6 rounded-2xl bg-gradient-to-br from-green-50 to-green-100 border border-green-200">
            <i class="pi pi-file-excel text-4xl text-green-600 mb-4"></i>
            <h4 class="font-bold text-gray-900 mb-2">Excel-Export</h4>
            <p class="text-gray-600 text-sm mb-4">Diagrammdaten als Excel-Datei exportieren</p>
            <button class="btn-success w-full">
              <i class="pi pi-download mr-2"></i>
              Excel herunterladen
            </button>
          </div>
          <div class="text-center p-6 rounded-2xl bg-gradient-to-br from-purple-50 to-purple-100 border border-purple-200">
            <i class="pi pi-image text-4xl text-purple-600 mb-4"></i>
            <h4 class="font-bold text-gray-900 mb-2">Bild-Export</h4>
            <p class="text-gray-600 text-sm mb-4">Diagramme als PNG oder SVG exportieren</p>
            <button class="btn-primary w-full">
              <i class="pi pi-image mr-2"></i>
              Bild exportieren
            </button>
          </div>
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
}
</style>
<template>
  <div id="app">
    <header class="app-header">
      <div class="container mx-auto px-4 py-6">
        <h1 class="text-3xl font-bold text-gray-800">
          <router-link to="/" class="hover:text-blue-600 transition-colors">
            🌤️ Weather History DWD
          </router-link>
        </h1>
        <p class="text-gray-600 mt-2">
          Historische Wetterdaten deutscher Stationen vom Deutschen Wetterdienst
        </p>
      </div>
    </header>

    <nav class="bg-gray-100 border-b">
      <div class="container mx-auto px-4">
        <div class="flex space-x-6 py-4">
          <router-link 
            to="/" 
            class="nav-link"
            :class="{ 'active': $route.path === '/' }"
          >
            <i class="pi pi-home mr-2"></i>
            Dashboard
          </router-link>
          <router-link 
            to="/stations" 
            class="nav-link"
            :class="{ 'active': $route.path.startsWith('/stations') }"
          >
            <i class="pi pi-map-marker mr-2"></i>
            Stationen
          </router-link>
          <router-link 
            to="/charts" 
            class="nav-link"
            :class="{ 'active': $route.path.startsWith('/charts') }"
          >
            <i class="pi pi-chart-line mr-2"></i>
            Diagramme
          </router-link>
          <router-link 
            to="/maps" 
            class="nav-link"
            :class="{ 'active': $route.path.startsWith('/maps') }"
          >
            <i class="pi pi-map mr-2"></i>
            Karten
          </router-link>
          <router-link 
            to="/export" 
            class="nav-link"
            :class="{ 'active': $route.path.startsWith('/export') }"
          >
            <i class="pi pi-download mr-2"></i>
            Export
          </router-link>
        </div>
      </div>
    </nav>

    <main class="container mx-auto px-4 py-8">
      <router-view />
    </main>

    <footer class="bg-gray-800 text-white py-8 mt-12">
      <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div>
            <h3 class="text-xl font-bold mb-4">Weather History DWD</h3>
            <p class="text-gray-300">
              Visualisierung historischer Wetterdaten deutscher Stationen.
              Datenquelle: Deutscher Wetterdienst (DWD) Open Data.
            </p>
          </div>
          <div>
            <h3 class="text-xl font-bold mb-4">Technologien</h3>
            <ul class="text-gray-300 space-y-2">
              <li>• Vue.js 3 + TypeScript</li>
              <li>• Laravel 11 API</li>
              <li>• PostgreSQL + PostGIS</li>
              <li>• Docker + Hetzner Cloud</li>
            </ul>
          </div>
          <div>
            <h3 class="text-xl font-bold mb-4">Daten</h3>
            <p class="text-gray-300">
              Tägliche Klimadaten (KL-Format) von ausgewählten deutschen Wetterstationen.
              Zeitraum: 1990-2024.
            </p>
          </div>
        </div>
        <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
          <p>© 2024 Weather History DWD. Alle Daten vom DWD Open Data Server.</p>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
import { RouterLink, RouterView } from 'vue-router'
</script>

<style scoped>
.app-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.nav-link {
  @apply px-4 py-2 rounded-lg text-gray-700 hover:bg-white hover:text-blue-600 transition-colors;
}

.nav-link.active {
  @apply bg-white text-blue-600 font-semibold;
}
</style>
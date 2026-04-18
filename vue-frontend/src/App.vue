<template>
  <div id="app" class="min-h-screen flex flex-col gradient-bg">
    <!-- Modern Header -->
    <header class="sticky top-0 z-50 glass-effect border-b border-white/10">
      <div class="container-narrow">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between py-4 gap-4">
          <div class="flex items-center space-x-4">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center shadow-md">
              <i class="pi pi-cloud text-white text-lg"></i>
            </div>
            <div>
              <router-link to="/" class="group">
                <h1 class="text-2xl font-bold text-gradient tracking-tight">
                  {{ $t('app.title') }} <span class="text-primary-700">DWD</span>
                </h1>
                <p class="text-sm text-gray-500 mt-1 group-hover:text-primary-600 transition-colors">
                  {{ $t('app.subtitle') }}
                </p>
              </router-link>
            </div>
          </div>
          
          <nav class="flex flex-wrap gap-2">
            <router-link 
              to="/" 
              class="nav-link group"
              :class="{ 'nav-link-active': $route.path === '/' }"
            >
              <i class="pi pi-home mr-2 group-hover:scale-110 transition-transform"></i>
              <span>{{ $t('navigation.dashboard') }}</span>
            </router-link>
            <router-link 
              to="/stations" 
              class="nav-link group"
              :class="{ 'nav-link-active': $route.path.startsWith('/stations') }"
            >
              <i class="pi pi-map-marker mr-2 group-hover:scale-110 transition-transform"></i>
              <span>{{ $t('navigation.stations') }}</span>
            </router-link>
            <router-link 
              to="/charts" 
              class="nav-link group"
              :class="{ 'nav-link-active': $route.path.startsWith('/charts') }"
            >
              <i class="pi pi-chart-line mr-2 group-hover:scale-110 transition-transform"></i>
              <span>{{ $t('navigation.charts') }}</span>
            </router-link>
            <router-link 
              to="/maps" 
              class="nav-link group"
              :class="{ 'nav-link-active': $route.path.startsWith('/maps') }"
            >
              <i class="pi pi-map mr-2 group-hover:scale-110 transition-transform"></i>
              <span>{{ $t('navigation.maps') }}</span>
            </router-link>
            <router-link 
              to="/export" 
              class="nav-link group"
              :class="{ 'nav-link-active': $route.path.startsWith('/export') }"
            >
              <i class="pi pi-download mr-2 group-hover:scale-110 transition-transform"></i>
              <span>{{ $t('navigation.export') }}</span>
            </router-link>
          </nav>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 py-8">
      <div class="container-narrow animate-fade-in">
        <router-view />
      </div>
    </main>

    <!-- Modern Footer -->
    <footer class="bg-gradient-to-b from-white to-gray-50 border-t border-gray-200 py-12">
      <div class="container-narrow">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
          <div>
            <div class="flex items-center space-x-3 mb-4">
              <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center">
                <i class="pi pi-cloud text-white text-sm"></i>
              </div>
              <h3 class="text-lg font-bold text-gray-800">{{ $t('app.title') }}</h3>
            </div>
            <p class="text-gray-600 text-sm leading-relaxed">
              {{ $t('app.description') }}
            </p>
          </div>
          
          <div>
            <h4 class="font-semibold text-gray-800 mb-4">{{ $t('footer.data') }}</h4>
            <ul class="space-y-2">
              <li class="flex items-center text-sm text-gray-600">
                <i class="pi pi-check-circle text-success-500 mr-2"></i>
                <span>{{ $t('footer.dailyClimateData') }}</span>
              </li>
              <li class="flex items-center text-sm text-gray-600">
                <i class="pi pi-check-circle text-success-500 mr-2"></i>
                <span>{{ $t('footer.germanStations', [16]) }}</span>
              </li>
              <li class="flex items-center text-sm text-gray-600">
                <i class="pi pi-check-circle text-success-500 mr-2"></i>
                <span>{{ $t('footer.timePeriod') }}</span>
              </li>
              <li class="flex items-center text-sm text-gray-600">
                <i class="pi pi-check-circle text-success-500 mr-2"></i>
                <span>{{ $t('footer.datasets', ['2.3M']) }}</span>
              </li>
            </ul>
          </div>
          
          <div>
            <h4 class="font-semibold text-gray-800 mb-4">{{ $t('footer.technologies') }}</h4>
            <div class="flex flex-wrap gap-2">
              <span class="badge-primary">Vue.js 3</span>
              <span class="badge-success">TypeScript</span>
              <span class="badge-warning">Laravel 11</span>
              <span class="badge-danger">PostgreSQL</span>
              <span class="badge-primary">PostGIS</span>
              <span class="badge-success">Docker</span>
            </div>
          </div>
          
          <div>
            <h4 class="font-semibold text-gray-800 mb-4">{{ $t('footer.links') }}</h4>
            <ul class="space-y-2">
              <li>
                <a href="https://opendata.dwd.de/" target="_blank" class="flex items-center text-sm text-gray-600 hover:text-primary-600 transition-colors">
                  <i class="pi pi-external-link mr-2"></i>
                  {{ $t('footer.dwdOpenDataServer') }}
                </a>
              </li>
              <li>
                <a href="https://github.com/elmohuppi-stack/weather-history" target="_blank" class="flex items-center text-sm text-gray-600 hover:text-primary-600 transition-colors">
                  <i class="pi pi-github mr-2"></i>
                  {{ $t('footer.githubRepository') }}
                </a>
              </li>
              <li>
                <a href="http://localhost:8080" target="_blank" class="flex items-center text-sm text-gray-600 hover:text-primary-600 transition-colors">
                  <i class="pi pi-database mr-2"></i>
                  {{ $t('footer.databaseAdmin') }}
                </a>
              </li>
            </ul>
          </div>
        </div>
        
        <div class="border-t border-gray-200 mt-8 pt-8 flex flex-col md:flex-row justify-between items-center">
          <p class="text-gray-500 text-sm">
            {{ $t('footer.copyright') }}
          </p>
          <div class="flex items-center space-x-4 mt-4 md:mt-0">
            <span class="text-xs text-gray-400">{{ $t('footer.developedWith') }}</span>
            <div class="flex space-x-2">
              <i class="pi pi-heart text-danger-500"></i>
              <i class="pi pi-code text-primary-500"></i>
              <i class="pi pi-server text-success-500"></i>
            </div>
          </div>
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
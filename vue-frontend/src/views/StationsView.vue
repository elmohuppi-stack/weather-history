<template>
  <div class="stations-view">
    <!-- Header -->
    <div class="mb-12">
      <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6 tracking-tight">
        {{ $t('stations.title', ['Wetterstationen']) }}
      </h1>
      <p class="text-xl text-gray-600 max-w-3xl leading-relaxed">
        {{ $t('stations.subtitle', [16]) }}
      </p>
    </div>

    <!-- Filter Bar -->
    <div class="card mb-8">
      <div class="card-body">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ $t('stations.filters.region') }}</label>
            <select class="select w-full">
              <option>{{ $t('stations.filters.allRegions') }}</option>
              <option>{{ $t('stations.filters.northGermany') }}</option>
              <option>{{ $t('stations.filters.eastGermany') }}</option>
              <option>{{ $t('stations.filters.westGermany') }}</option>
              <option>{{ $t('stations.filters.southGermany') }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ $t('stations.filters.status') }}</label>
            <select class="select w-full">
              <option>{{ $t('stations.filters.allStations') }}</option>
              <option>{{ $t('stations.filters.active') }}</option>
              <option>{{ $t('stations.filters.historical') }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ $t('stations.filters.sorting') }}</label>
            <select class="select w-full">
              <option>{{ $t('stations.filters.nameAZ') }}</option>
              <option>{{ $t('stations.filters.heightDesc') }}</option>
              <option>{{ $t('stations.filters.dataFromAsc') }}</option>
              <option>{{ $t('stations.filters.measurementsDesc') }}</option>
            </select>
          </div>
          <div class="flex items-end">
            <button class="btn-primary w-full">
              <i class="pi pi-filter mr-2"></i>
              {{ $t('stations.filters.applyFilters') }}
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
                <span class="badge-primary mr-2">{{ $t('stations.stationCard.dwdStation') }}</span>
                <span class="badge-success">{{ station.active ? $t('stations.filters.active') : $t('stations.filters.historical') }}</span>
              </div>
            </div>
          </div>
          
          <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="text-center p-4 rounded-xl bg-gray-50">
              <p class="text-sm text-gray-500 mb-1">{{ $t('stations.stationCard.height') }}</p>
              <p class="text-2xl font-bold text-gray-900">{{ station.elevation }} {{ $t('common.meters') }}</p>
            </div>
            <div class="text-center p-4 rounded-xl bg-gray-50">
              <p class="text-sm text-gray-500 mb-1">{{ $t('stations.stationCard.dataFrom') }}</p>
              <p class="text-2xl font-bold text-gray-900">{{ station.start_year }}</p>
            </div>
          </div>
          
          <div class="flex items-center justify-between mb-6">
            <div>
              <p class="text-sm text-gray-500">{{ $t('stations.stationCard.measurements') }}</p>
              <p class="text-lg font-bold text-gray-900">{{ station.measurement_count?.toLocaleString() || '12450' }}</p>
            </div>
            <div class="text-right">
              <p class="text-sm text-gray-500">{{ $t('stations.stationCard.lastUpdate') }}</p>
              <p class="text-lg font-bold text-gray-900">{{ station.latest_date }}</p>
            </div>
          </div>
          
          <div class="pt-6 border-t border-gray-100">
            <router-link :to="`/stations/${station.id}`" 
                         class="btn-primary w-full group">
              <span>{{ $t('stations.stationCard.showDetails') }}</span>
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
            <h4 class="text-xl font-bold text-gray-900 mb-2">{{ $t('stations.stats.dataVolume') }}</h4>
            <p class="text-3xl font-bold text-blue-700">2.3 GB</p>
            <p class="text-gray-600 mt-2">{{ $t('stations.stats.dataVolumeDescription') }}</p>
          </div>
          <div class="text-center p-6">
            <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-green-50 to-green-100 flex items-center justify-center mx-auto mb-4">
              <i class="pi pi-calendar text-green-600 text-3xl"></i>
            </div>
            <h4 class="text-xl font-bold text-gray-900 mb-2">{{ $t('stations.stats.timePeriod') }}</h4>
            <p class="text-3xl font-bold text-green-700">34 {{ $t('dashboard.stats.years') }}</p>
            <p class="text-gray-600 mt-2">{{ $t('stations.stats.timePeriodDescription') }}</p>
          </div>
          <div class="text-center p-6">
            <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-purple-50 to-purple-100 flex items-center justify-center mx-auto mb-4">
              <i class="pi pi-cloud text-purple-600 text-3xl"></i>
            </div>
            <h4 class="text-xl font-bold text-gray-900 mb-2">{{ $t('stations.stats.parameters') }}</h4>
            <p class="text-3xl font-bold text-purple-700">8</p>
            <p class="text-gray-600 mt-2">{{ $t('stations.stats.parametersDescription') }}</p>
          </div>
        </div>
      </div>
      <div class="card-footer">
        <div class="flex justify-between items-center">
          <p class="text-sm text-gray-600">
            {{ $t('common.dataSource') }}
          </p>
          <button class="btn-secondary">
            <i class="pi pi-download mr-2"></i>
            {{ $t('common.exportAllData') }}
          </button>
        </div>
      </div>
    </div>
    
    <!-- Loading State -->
    <div v-if="loading" class="text-center py-20">
      <div class="inline-block animate-spin rounded-full h-16 w-16 border-t-2 border-b-2 border-primary-600"></div>
      <p class="mt-6 text-xl text-gray-600">{{ $t('stations.loading') }}</p>
      <p class="text-gray-500 mt-2">{{ $t('stations.loadingDescription') }}</p>
    </div>
    
    <!-- Error State -->
    <div v-if="error" class="card border border-danger-200 bg-danger-50">
      <div class="card-body">
        <div class="flex items-center">
          <div class="w-12 h-12 rounded-xl bg-danger-100 flex items-center justify-center mr-4">
            <i class="pi pi-exclamation-triangle text-danger-600 text-2xl"></i>
          </div>
          <div>
            <h3 class="text-xl font-bold text-danger-800">{{ $t('stations.error') }}</h3>
            <p class="text-danger-600 mt-1">{{ error }}</p>
            <button class="btn-secondary mt-4">
              <i class="pi pi-refresh mr-2"></i>
              {{ $t('stations.retry') }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { apiService, type Station } from '@/services/api'

const { t } = useI18n()

const stations = ref<Station[]>([])
const loading = ref(false)
const error = ref<string | null>(null)

onMounted(async () => {
  try {
    loading.value = true
    error.value = null
    
    const response = await apiService.getStations()
    
    if (response.success) {
      stations.value = response.data
    } else {
      error.value = t('api.error')
    }
  } catch (err) {
    error.value = err instanceof Error ? err.message : t('api.error')
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
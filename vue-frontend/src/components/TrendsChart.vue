<template>
  <div class="trends-chart-container">
    <div v-if="loading" class="text-center py-12">
      <i class="pi pi-spin pi-spinner text-4xl text-blue-600"></i>
      <p class="mt-4 text-gray-600">Lade Trend-Daten...</p>
    </div>

    <div v-else-if="error" class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-4">
      {{ error }}
    </div>

    <div v-else-if="chartData" class="space-y-6">
      <!-- Chart Selection -->
      <div class="flex flex-wrap gap-2 justify-center">
        <button
          v-for="param in parameters"
          :key="param.value"
          @click="selectedParameter = param.value"
          :class="[
            'px-4 py-2 rounded-full font-medium transition-colors',
            selectedParameter === param.value
              ? 'bg-blue-600 text-white'
              : 'bg-gray-200 text-gray-800 hover:bg-gray-300'
          ]"
        >
          {{ param.label }}
        </button>
      </div>

      <!-- Main Chart -->
      <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="relative" :style="{ height: '400px' }">
          <canvas ref="mainChart"></canvas>
        </div>
      </div>

      <!-- Trend Analysis Box -->
      <div v-if="trendAnalysis" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200">
          <div class="text-sm text-blue-600 font-semibold uppercase">Trend</div>
          <div :class="[
            'text-2xl font-bold mt-2',
            trendAnalysis.trend === 'increasing' ? 'text-red-600' : 'text-blue-600'
          ]">
            {{ trendAnalysis.trend === 'increasing' ? '↑' : '↓' }}
            {{ trendAnalysis.trend === 'increasing' ? 'Zunehmend' : 'Abnehmend' }}
          </div>
        </div>

        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4 border border-green-200">
          <div class="text-sm text-green-600 font-semibold uppercase">Pro Jahr</div>
          <div class="text-2xl font-bold text-green-700 mt-2">
            {{ formatValue(trendAnalysis.rate_per_year) }}
            <span class="text-sm">{{ getUnit() }}/Jahr</span>
          </div>
        </div>

        <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg p-4 border border-orange-200">
          <div class="text-sm text-orange-600 font-semibold uppercase">Pro Dekade</div>
          <div class="text-2xl font-bold text-orange-700 mt-2">
            {{ formatValue(trendAnalysis.rate_per_decade) }}
            <span class="text-sm">{{ getUnit() }}/Dekade</span>
          </div>
        </div>
      </div>

      <!-- Statistics -->
      <div v-if="trendAnalysis" class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-gray-50 rounded-lg p-4">
          <p class="text-xs text-gray-500 uppercase font-semibold">Minimum</p>
          <p class="text-lg font-bold text-gray-900 mt-1">
            {{ formatValue(trendAnalysis.min_value) }} {{ getUnit() }}
          </p>
        </div>
        <div class="bg-gray-50 rounded-lg p-4">
          <p class="text-xs text-gray-500 uppercase font-semibold">Maximum</p>
          <p class="text-lg font-bold text-gray-900 mt-1">
            {{ formatValue(trendAnalysis.max_value) }} {{ getUnit() }}
          </p>
        </div>
        <div class="bg-gray-50 rounded-lg p-4">
          <p class="text-xs text-gray-500 uppercase font-semibold">Durchschnitt</p>
          <p class="text-lg font-bold text-gray-900 mt-1">
            {{ formatValue(trendAnalysis.mean_value) }} {{ getUnit() }}
          </p>
        </div>
        <div class="bg-gray-50 rounded-lg p-4">
          <p class="text-xs text-gray-500 uppercase font-semibold">Datenpunkte</p>
          <p class="text-lg font-bold text-gray-900 mt-1">{{ dataPoints }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend } from 'chart.js'
import { Line } from 'vue-chartjs'
import { apiService } from '@/services/api'

// Register ChartJS components
ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend)

interface TrendsData {
  parameter: string
  parameter_unit: string
  station: {
    id: string
    name: string
  }
  period: {
    start_year: number
    end_year: number
    years: number
  }
  analysis: {
    trend: string
    rate_per_year: number
    rate_per_decade: number
    min_value: number
    max_value: number
    mean_value: number
  }
  annual_values: Record<string, number>
  decadal_averages: Record<string, number>
}

const props = defineProps<{
  stationId: string
}>()

const loading = ref(false)
const error = ref<string | null>(null)
const selectedParameter = ref('temperature')
const trendsData = ref<Record<string, TrendsData | null>>({
  temperature: null,
  precipitation: null,
  sunshine: null,
})

const parameters = [
  { label: '🌡️ Temperatur', value: 'temperature' },
  { label: '🌧️ Niederschlag', value: 'precipitation' },
  { label: '☀️ Sonnenschein', value: 'sunshine' },
]

const mainChart = ref<any>(null)

const chartData = computed(() => {
  const data = trendsData.value[selectedParameter.value]
  if (!data) return null

  const years = Object.keys(data.annual_values)
    .map(y => parseInt(y))
    .sort((a, b) => a - b)
  
  const values = years.map(y => data.annual_values[y])
  
  // Calculate trend line
  const n = values.length
  if (n < 2) return null

  const x = Array.from({ length: n }, (_, i) => i)
  const xSum = x.reduce((a, b) => a + b, 0)
  const ySum = values.reduce((a, b) => a + b, 0)
  const xySum = x.reduce((sum, xi, i) => sum + xi * values[i], 0)
  const x2Sum = x.reduce((sum, xi) => sum + xi * xi, 0)

  const denominator = n * x2Sum - xSum * xSum
  if (denominator === 0) return null

  const slope = (n * xySum - xSum * ySum) / denominator
  const intercept = (ySum - slope * xSum) / n

  const trendLine = x.map(xi => intercept + slope * xi)

  return {
    labels: years.map(y => y.toString()),
    datasets: [
      {
        label: `Messwerte (${selectedParameter.value})`,
        data: values,
        borderColor: '#3b82f6',
        backgroundColor: 'rgba(59, 130, 246, 0.1)',
        borderWidth: 2,
        pointRadius: 5,
        pointBackgroundColor: '#3b82f6',
        pointBorderColor: '#fff',
        pointBorderWidth: 2,
        tension: 0.4,
        fill: false,
      },
      {
        label: 'Trendlinie',
        data: trendLine,
        borderColor: '#ef4444',
        backgroundColor: 'rgba(239, 68, 68, 0.1)',
        borderWidth: 2,
        borderDash: [5, 5],
        pointRadius: 0,
        fill: false,
        tension: 0,
      },
    ],
  }
})

const trendAnalysis = computed(() => {
  return trendsData.value[selectedParameter.value]?.analysis || null
})

const dataPoints = computed(() => {
  const data = trendsData.value[selectedParameter.value]
  return data ? Object.keys(data.annual_values).length : 0
})

const getUnit = () => {
  const data = trendsData.value[selectedParameter.value]
  return data?.parameter_unit || ''
}

const formatValue = (value: number | undefined) => {
  if (value === undefined) return '–'
  return value.toFixed(2)
}

const loadTrendsData = async () => {
  loading.value = true
  error.value = null

  try {
    // Load all three parameters in parallel
    const [tempResponse, precipResponse, sunResponse] = await Promise.all([
      apiService.getTrends({ parameter: 'temperature', station_id: props.stationId }),
      apiService.getTrends({ parameter: 'precipitation', station_id: props.stationId }),
      apiService.getTrends({ parameter: 'sunshine', station_id: props.stationId }),
    ])

    if (tempResponse.success && tempResponse.data) {
      trendsData.value.temperature = tempResponse.data
    }
    if (precipResponse.success && precipResponse.data) {
      trendsData.value.precipitation = precipResponse.data
    }
    if (sunResponse.success && sunResponse.data) {
      trendsData.value.sunshine = sunResponse.data
    }

    if (!trendsData.value.temperature) {
      error.value = 'Keine Trend-Daten für diese Station verfügbar'
    }
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Fehler beim Laden der Trend-Daten'
    console.error('Error loading trends:', err)
  } finally {
    loading.value = false
  }
}

// Render chart when data changes
watch(
  [() => chartData.value, selectedParameter],
  ([newChartData]) => {
    if (newChartData && mainChart.value) {
      // Redraw chart
      (mainChart.value as any).update()
    }
  },
  { deep: true }
)

onMounted(() => {
  loadTrendsData()
})
</script>

<style scoped>
.trends-chart-container {
  width: 100%;
}
</style>

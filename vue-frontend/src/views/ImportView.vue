<template>
  <div class="import-view">
    <div class="container mx-auto px-4 py-8">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
          Import Management
        </h1>
        <p class="text-gray-600 dark:text-gray-400">
          Monitor and manage data imports from DWD weather stations
        </p>
      </div>

      <!-- Statistics Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900 mr-4">
              <i class="pi pi-database text-blue-600 dark:text-blue-300 text-xl"></i>
            </div>
            <div>
              <p class="text-sm text-gray-500 dark:text-gray-400">Total Imports</p>
              <p class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ statistics?.overall?.total_imports || 0 }}
              </p>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-100 dark:bg-green-900 mr-4">
              <i class="pi pi-check-circle text-green-600 dark:text-green-300 text-xl"></i>
            </div>
            <div>
              <p class="text-sm text-gray-500 dark:text-gray-400">Successful</p>
              <p class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ statistics?.overall?.successful_imports || 0 }}
              </p>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="p-3 rounded-full bg-red-100 dark:bg-red-900 mr-4">
              <i class="pi pi-times-circle text-red-600 dark:text-red-300 text-xl"></i>
            </div>
            <div>
              <p class="text-sm text-gray-500 dark:text-gray-400">Failed</p>
              <p class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ statistics?.overall?.failed_imports || 0 }}
              </p>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="p-3 rounded-full bg-purple-100 dark:bg-purple-900 mr-4">
              <i class="pi pi-chart-line text-purple-600 dark:text-purple-300 text-xl"></i>
            </div>
            <div>
              <p class="text-sm text-gray-500 dark:text-gray-400">Records Imported</p>
              <p class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ formatNumber(statistics?.overall?.total_records_imported || 0) }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="mb-8">
        <div class="flex flex-wrap gap-4">
          <Button 
            label="Trigger Historical Import" 
            icon="pi pi-history" 
            severity="secondary"
            @click="showTriggerDialog('historical')"
          />
          <Button 
            label="Trigger Recent Data Import" 
            icon="pi pi-clock" 
            severity="secondary"
            @click="showTriggerDialog('recent')"
          />
          <Button 
            label="Full Import All Stations" 
            icon="pi pi-sync" 
            severity="warning"
            @click="showTriggerDialog('full')"
          />
          <Button 
            label="Clear Old Logs" 
            icon="pi pi-trash" 
            severity="danger"
            @click="clearOldLogs"
            :loading="clearingLogs"
          />
        </div>
      </div>

      <!-- Import Logs Table -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
          <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
              Import History
            </h2>
            <div class="flex items-center space-x-4">
              <div class="flex items-center space-x-2">
                <label class="text-sm text-gray-600 dark:text-gray-400">Filter:</label>
                <Dropdown 
                  v-model="filters.type" 
                  :options="importTypes" 
                  optionLabel="label" 
                  optionValue="value"
                  placeholder="All Types"
                  class="w-40"
                />
                <Dropdown 
                  v-model="filters.success" 
                  :options="statusOptions" 
                  optionLabel="label" 
                  optionValue="value"
                  placeholder="All Status"
                  class="w-40"
                />
              </div>
              <Button 
                icon="pi pi-refresh" 
                severity="secondary" 
                @click="loadImports"
                :loading="loading"
              />
            </div>
          </div>
        </div>

        <div class="overflow-x-auto">
          <DataTable 
            :value="imports" 
            :loading="loading"
            paginator 
            :rows="20"
            :rowsPerPageOptions="[10, 20, 50]"
            :totalRecords="totalImports"
            @page="onPageChange"
            class="p-datatable-sm"
          >
            <Column field="id" header="ID" :sortable="true">
              <template #body="{ data }">
                <span class="font-mono text-sm">{{ data.id }}</span>
              </template>
            </Column>
            <Column field="created_at" header="Date" :sortable="true">
              <template #body="{ data }">
                {{ formatDate(data.created_at) }}
              </template>
            </Column>
            <Column field="import_type" header="Type" :sortable="true">
              <template #body="{ data }">
                <Badge :value="data.import_type_label" :severity="getTypeSeverity(data.import_type)" />
              </template>
            </Column>
            <Column field="station.name" header="Station" :sortable="true">
              <template #body="{ data }">
                <div v-if="data.station">
                  <router-link 
                    :to="{ name: 'station-detail', params: { id: data.station_id } }"
                    class="text-blue-600 dark:text-blue-400 hover:underline"
                  >
                    {{ data.station.name }}
                  </router-link>
                </div>
                <span v-else class="text-gray-500 dark:text-gray-400">-</span>
              </template>
            </Column>
            <Column field="operation" header="Operation" :sortable="true">
              <template #body="{ data }">
                <Badge :value="data.operation_label" severity="info" />
              </template>
            </Column>
            <Column field="records_imported" header="Records" :sortable="true">
              <template #body="{ data }">
                <div class="text-right">
                  <div class="font-semibold">{{ data.records_imported }}</div>
                  <div class="text-xs text-gray-500">
                    {{ data.records_processed }} processed
                  </div>
                </div>
              </template>
            </Column>
            <Column field="duration_seconds" header="Duration" :sortable="true">
              <template #body="{ data }">
                {{ data.formatted_duration }}
              </template>
            </Column>
            <Column field="success" header="Status" :sortable="true">
              <template #body="{ data }">
                <Badge 
                  :value="data.status" 
                  :severity="data.success ? 'success' : 'danger'" 
                />
              </template>
            </Column>
            <Column header="Actions">
              <template #body="{ data }">
                <Button 
                  icon="pi pi-eye" 
                  severity="secondary" 
                  size="small"
                  @click="showImportDetails(data)"
                />
                <Button 
                  v-if="!data.success"
                  icon="pi pi-refresh" 
                  severity="warning" 
                  size="small"
                  class="ml-2"
                  @click="retryImport(data)"
                />
                <Button 
                  icon="pi pi-trash" 
                  severity="danger" 
                  size="small"
                  class="ml-2"
                  @click="deleteImport(data.id)"
                />
              </template>
            </Column>
          </DataTable>
        </div>
      </div>

      <!-- Recent Imports -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Import Types Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            Imports by Type
          </h3>
          <div v-if="statistics?.by_type" class="h-64">
            <Chart 
              type="pie" 
              :data="importTypeChartData" 
              :options="chartOptions"
            />
          </div>
          <div v-else class="h-64 flex items-center justify-center">
            <p class="text-gray-500 dark:text-gray-400">No import data available</p>
          </div>
        </div>

        <!-- Top Stations -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            Top Imported Stations
          </h3>
          <div v-if="statistics?.by_station?.length">
            <div 
              v-for="station in statistics.by_station" 
              :key="station.id"
              class="flex items-center justify-between py-3 border-b border-gray-200 dark:border-gray-700 last:border-0"
            >
              <div class="flex items-center">
                <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center mr-3">
                  <i class="pi pi-map-marker text-blue-600 dark:text-blue-300"></i>
                </div>
                <div>
                  <p class="font-medium text-gray-900 dark:text-white">{{ station.name }}</p>
                  <p class="text-sm text-gray-500 dark:text-gray-400">ID: {{ station.id }}</p>
                </div>
              </div>
              <Badge :value="station.import_count" severity="info" />
            </div>
          </div>
          <div v-else class="h-64 flex items-center justify-center">
            <p class="text-gray-500 dark:text-gray-400">No station import data available</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Trigger Import Dialog -->
    <Dialog 
      v-model:visible="showTriggerDialogVisible" 
      header="Trigger Import" 
      :modal="true"
      :style="{ width: '500px' }"
    >
      <div class="p-4">
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Import Type
          </label>
          <Dropdown 
            v-model="triggerImportType" 
            :options="importTypes" 
            optionLabel="label" 
            optionValue="value"
            placeholder="Select import type"
            class="w-full"
          />
        </div>

        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Station (Optional)
          </label>
          <Dropdown 
            v-model="triggerStationId" 
            :options="stations" 
            optionLabel="name" 
            optionValue="id"
            placeholder="Select station (optional)"
            class="w-full"
            :filter="true"
          />
        </div>

        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Parameters (JSON)
          </label>
          <Textarea 
            v-model="triggerParameters" 
            rows="3" 
            placeholder='{"year": 2024, "force": true}'
            class="w-full font-mono text-sm"
          />
        </div>
      </div>

      <template #footer>
        <Button label="Cancel" severity="secondary" @click="showTriggerDialogVisible = false" />
        <Button 
          label="Trigger Import" 
          severity="primary" 
          @click="triggerImport"
          :loading="triggeringImport"
        />
      </template>
    </Dialog>

    <!-- Import Details Dialog -->
    <Dialog 
      v-model:visible="showDetailsDialogVisible" 
      header="Import Details" 
      :modal="true"
      :style="{ width: '600px' }"
    >
      <div v-if="selectedImport" class="p-4">
        <div class="grid grid-cols-2 gap-4 mb-6">
          <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Import ID</p>
            <p class="font-mono font-semibold">{{ selectedImport.id }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Date</p>
            <p>{{ formatDate(selectedImport.created_at) }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Type</p>
            <Badge :value="selectedImport.import_type_label" :severity="getTypeSeverity(selectedImport.import_type)" />
          </div>
          <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
            <Badge 
              :value="selectedImport.status" 
              :severity="selectedImport.success ? 'success' : 'danger'" 
            />
          </div>
          <div v-if="selectedImport.station">
            <p class="text-sm text-gray-500 dark:text-gray-400">Station</p>
            <p class="font-semibold">{{ selectedImport.station.name }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Operation</p>
            <p>{{ selectedImport.operation_label }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Duration</p>
            <p>{{ selectedImport.formatted_duration }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">User Initiated</p>
            <p>{{ selectedImport.user_initiated ? 'Yes' : 'No' }}</p>
          </div>
        </div>

        <div class="mb-6">
          <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Records</h4>
          <div class="grid grid-cols-4 gap-4">
            <div class="text-center p-3 bg-blue-50 dark:bg-blue-900/20 rounded">
              <p class="text-sm text-gray-500 dark:text-gray-400">Processed</p>
              <p class="text-xl font-bold">{{ selectedImport.records_processed }}</p>
            </div>
            <div class="text-center p-3 bg-green-50 dark:bg-green-900/20 rounded">
              <p class="text-sm text-gray-500 dark:text-gray-400">Imported</p>
              <p class="text-xl font-bold">{{ selectedImport.records_imported }}</p>
            </div>
            <div class="text-center p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded">
              <p class="text-sm text-gray-500 dark:text-gray-400">Skipped</p>
              <p class="text-xl font-bold">{{ selectedImport.records_skipped }}</p>
            </div>
            <div class="text-center p-3 bg-red-50 dark:bg-red-900/20 rounded">
              <p class="text-sm text-gray-500 dark:text-gray-400">Failed</p>
              <p class="text-xl font-bold">{{ selectedImport.records_failed }}</p>
            </div>
          </div>
        </div>

        <div v-if="selectedImport.error_message" class="mb-6">
          <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Error Message</h4>
          <div class="p-3 bg-red-50 dark:bg-red-900/20 rounded">
            <p class="text-red-700 dark:text-red-300 font-mono text-sm">{{ selectedImport.error_message }}</p>
          </div>
        </div>

        <div v-if="selectedImport.parameters" class="mb-6">
          <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Parameters</h4>
          <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded">
            <pre class="text-sm font-mono overflow-auto max-h-40">{{ JSON.stringify(selectedImport.parameters, null, 2) }}</pre>
          </div>
        </div>
      </div>

      <template #footer>
        <Button label="Close" severity="secondary" @click="showDetailsDialogVisible = false" />
        <Button 
          v-if="selectedImport && !selectedImport.success"
          label="Retry Import" 
          severity="primary" 
          @click="selectedImport && retryImport(selectedImport)"
        />
      </template>
    </Dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useToast } from 'primevue/usetoast'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import Dropdown from 'primevue/dropdown'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Badge from 'primevue/badge'
import Chart from 'primevue/chart'
import Textarea from 'primevue/textarea'
import { apiService } from '@/services/api'
import type { ImportLog, Station } from '@/types'

const toast = useToast()

// State
const imports = ref<ImportLog[]>([])
const statistics = ref<any>(null)
const stations = ref<Station[]>([])
const loading = ref(false)
const clearingLogs = ref(false)
const triggeringImport = ref(false)
const totalImports = ref(0)
const currentPage = ref(1)
const perPage = ref(20)

// Dialog states
const showTriggerDialogVisible = ref(false)
const showDetailsDialogVisible = ref(false)
const selectedImport = ref<ImportLog | null>(null)

// Filter states
const filters = ref({
  type: null as string | null,
  success: null as boolean | null,
})

// Trigger import states
const triggerImportType = ref('historical')
const triggerStationId = ref<string | null>(null)
const triggerParameters = ref('')

// Options
const importTypes = ref([
  { label: 'Historical Data', value: 'historical' },
  { label: 'Recent Data', value: 'recent' },
  { label: 'Full Import', value: 'full' },
  { label: 'Add Station', value: 'station_add' },
  { label: 'Update', value: 'update' },
])

const statusOptions = ref([
  { label: 'All Status', value: null },
  { label: 'Successful', value: true },
  { label: 'Failed', value: false },
])

// Computed
const importTypeChartData = computed(() => {
  if (!statistics.value?.by_type) return { labels: [], datasets: [] }

  const labels = Object.keys(statistics.value.by_type)
  const data = Object.values(statistics.value.by_type)

  return {
    labels: labels.map(type => {
      const typeObj = importTypes.value.find(t => t.value === type)
      return typeObj?.label || type
    }),
    datasets: [
      {
        data: data,
        backgroundColor: [
          '#3B82F6', // blue
          '#10B981', // green
          '#F59E0B', // yellow
          '#EF4444', // red
          '#8B5CF6', // purple
        ],
      },
    ],
  }
})

const chartOptions = ref({
  plugins: {
    legend: {
      position: 'bottom' as const,
    },
  },
  maintainAspectRatio: false,
})

// Methods
const formatNumber = (num: number): string => {
  if (num >= 1000000) {
    return (num / 1000000).toFixed(1) + 'M'
  }
  if (num >= 1000) {
    return (num / 1000).toFixed(1) + 'K'
  }
  return num.toString()
}

const formatDate = (dateString: string): string => {
  const date = new Date(dateString)
  return date.toLocaleString('de-DE', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const getTypeSeverity = (type: string): string => {
  switch (type) {
    case 'historical': return 'info'
    case 'recent': return 'warning'
    case 'full': return 'danger'
    case 'station_add': return 'success'
    case 'update': return 'help'
    default: return 'secondary'
  }
}

const getImportTypeLabel = (type: string): string => {
  const typeObj = importTypes.value.find(t => t.value === type)
  return typeObj?.label || type
}

const getOperationLabel = (operation: string): string => {
  switch (operation) {
    case 'import': return 'Import'
    case 'update': return 'Update'
    case 'delete': return 'Delete'
    default: return operation
  }
}

const loadImports = async () => {
  console.log('🚀 loadImports() called - SIMPLE VERSION')
  loading.value = true
  try {
    // Simple test - just fetch the data
    const testUrl = 'http://localhost:8000/api/v1/imports'
    console.log('Fetching from:', testUrl)
    
    const response = await fetch(testUrl)
    const data = await response.json()
    console.log('Fetched data:', data)
    
    // Just assign the raw data
    imports.value = data.data || []
    totalImports.value = data.meta?.total || 0
    
    console.log('✅ Imports loaded:', imports.value.length)
  } catch (error) {
    console.error('❌ Failed to load imports:', error)
  } finally {
    loading.value = false
    console.log('🏁 loadImports() completed')
  }
}

const loadStatistics = async () => {
  try {
    const response = await apiService.get('/v1/imports/statistics')
    statistics.value = response.data.data
  } catch (error) {
    console.error('Failed to load statistics:', error)
  }
}

const loadStations = async () => {
  try {
    // Use the dedicated stations API method
    const response = await apiService.getStations()
    stations.value = response.data || []
    console.log('Loaded stations:', stations.value.length)
  } catch (error) {
    console.error('Failed to load stations:', error)
    // Fallback to generic get
    try {
      const fallbackResponse = await apiService.get('/v1/stations')
      stations.value = fallbackResponse.data?.data || fallbackResponse.data || []
    } catch (fallbackError) {
      console.error('Fallback also failed:', fallbackError)
      stations.value = []
    }
  }
}

const showTriggerDialog = (type: string) => {
  triggerImportType.value = type
  triggerStationId.value = null
  triggerParameters.value = ''
  showTriggerDialogVisible.value = true
}

const triggerImport = async () => {
  triggeringImport.value = true
  try {
    const params: any = {
      type: triggerImportType.value,
    }

    if (triggerStationId.value) {
      params.station_id = triggerStationId.value
    }

    if (triggerParameters.value) {
      try {
        params.parameters = JSON.parse(triggerParameters.value)
      } catch (error) {
        toast.add({
          severity: 'error',
          summary: 'Error',
          detail: 'Invalid JSON parameters',
          life: 3000,
        })
        return
      }
    }

    const response = await apiService.post('/v1/imports/trigger', params)
    
    toast.add({
      severity: 'success',
      summary: 'Success',
      detail: response.data.message,
      life: 3000,
    })

    showTriggerDialogVisible.value = false
    
    // Wait a moment for the database to be updated
    setTimeout(() => {
      loadImports()
      loadStatistics()
    }, 500)
  } catch (error: any) {
    console.error('Failed to trigger import:', error)
    toast.add({
      severity: 'error',
      summary: 'Error',
      detail: error.response?.data?.message || 'Failed to trigger import',
      life: 3000,
    })
  } finally {
    triggeringImport.value = false
  }
}

const showImportDetails = (importLog: ImportLog) => {
  selectedImport.value = importLog
  showDetailsDialogVisible.value = true
}

const retryImport = async (importLog: ImportLog) => {
  try {
    const params: any = {
      type: importLog.import_type,
    }

    if (importLog.station_id) {
      params.station_id = importLog.station_id
    }

    if (importLog.parameters) {
      params.parameters = importLog.parameters
    }

    const response = await apiService.post('/v1/imports/trigger', params)
    
    toast.add({
      severity: 'success',
      summary: 'Success',
      detail: response.data.message,
      life: 3000,
    })

    showDetailsDialogVisible.value = false
    
    // Wait a moment for the database to be updated
    setTimeout(() => {
      loadImports()
      loadStatistics()
    }, 500)
  } catch (error: any) {
    console.error('Failed to retry import:', error)
    toast.add({
      severity: 'error',
      summary: 'Error',
      detail: error.response?.data?.message || 'Failed to retry import',
      life: 3000,
    })
  }
}

const deleteImport = async (id: number) => {
  if (!confirm('Are you sure you want to delete this import log?')) {
    return
  }

  try {
    await apiService.delete(`/v1/imports/${id}`)
    
    toast.add({
      severity: 'success',
      summary: 'Success',
      detail: 'Import log deleted successfully',
      life: 3000,
    })

    loadImports()
    loadStatistics()
  } catch (error: any) {
    console.error('Failed to delete import:', error)
    toast.add({
      severity: 'error',
      summary: 'Error',
      detail: error.response?.data?.message || 'Failed to delete import',
      life: 3000,
    })
  }
}

const clearOldLogs = async () => {
  clearingLogs.value = true
  try {
    const response = await apiService.delete('/v1/imports/clear-old')
    
    toast.add({
      severity: 'success',
      summary: 'Success',
      detail: response.data.message,
      life: 3000,
    })

    loadImports()
    loadStatistics()
  } catch (error: any) {
    console.error('Failed to clear old logs:', error)
    toast.add({
      severity: 'error',
      summary: 'Error',
      detail: error.response?.data?.message || 'Failed to clear old logs',
      life: 3000,
    })
  } finally {
    clearingLogs.value = false
  }
}

const onPageChange = (event: any) => {
  currentPage.value = event.page + 1
  perPage.value = event.rows
  loadImports()
}

// Lifecycle
onMounted(() => {
  loadImports()
  loadStatistics()
  loadStations()
})
</script>

<style scoped>
.import-view {
  min-height: calc(100vh - 64px);
}

:deep(.p-datatable) {
  font-size: 0.875rem;
}

:deep(.p-datatable .p-column-header-content) {
  font-weight: 600;
}

:deep(.p-datatable-tbody > tr > td) {
  padding: 0.75rem 1rem;
}
</style>

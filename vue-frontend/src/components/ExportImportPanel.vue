<template>
  <div class="export-import-panel bg-white rounded-lg shadow-lg p-6">
    <!-- Tabs -->
    <div class="flex gap-4 mb-6 border-b">
      <button
        @click="activeTab = 'export'"
        :class="[
          'pb-3 px-4 font-semibold transition-colors border-b-2',
          activeTab === 'export'
            ? 'text-blue-600 border-blue-600'
            : 'text-gray-600 border-transparent hover:text-gray-800',
        ]"
      >
        <i class="pi pi-download mr-2"></i>
        Daten exportieren
      </button>
      <button
        @click="activeTab = 'import'"
        :class="[
          'pb-3 px-4 font-semibold transition-colors border-b-2',
          activeTab === 'import'
            ? 'text-blue-600 border-blue-600'
            : 'text-gray-600 border-transparent hover:text-gray-800',
        ]"
      >
        <i class="pi pi-upload mr-2"></i>
        Daten importieren
      </button>
      <button
        @click="activeTab = 'history'"
        :class="[
          'pb-3 px-4 font-semibold transition-colors border-b-2',
          activeTab === 'history'
            ? 'text-blue-600 border-blue-600'
            : 'text-gray-600 border-transparent hover:text-gray-800',
        ]"
      >
        <i class="pi pi-history mr-2"></i>
        Verlauf
      </button>
    </div>

    <!-- EXPORT TAB -->
    <div v-if="activeTab === 'export'" class="space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Format Selection -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">
            Dateiformat
          </label>
          <div class="space-y-2">
            <label
              v-for="fmt in exportFormats"
              :key="fmt.value"
              class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 transition"
              :class="
                exportFormat === fmt.value
                  ? 'border-blue-600 bg-blue-50'
                  : 'border-gray-200'
              "
            >
              <input
                type="radio"
                :value="fmt.value"
                v-model="exportFormat"
                class="mr-3"
              />
              <div>
                <div class="font-medium text-gray-900">{{ fmt.label }}</div>
                <div class="text-xs text-gray-600">{{ fmt.description }}</div>
              </div>
            </label>
          </div>
        </div>

        <!-- Data Type Selection -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">
            Datentyp
          </label>
          <div class="space-y-2">
            <label
              v-for="dtype in dataTypes"
              :key="dtype.value"
              class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 transition"
              :class="
                exportDataType === dtype.value
                  ? 'border-blue-600 bg-blue-50'
                  : 'border-gray-200'
              "
            >
              <input
                type="radio"
                :value="dtype.value"
                v-model="exportDataType"
                class="mr-3"
              />
              <div>
                <div class="font-medium text-gray-900">{{ dtype.label }}</div>
                <div class="text-xs text-gray-600">{{ dtype.description }}</div>
              </div>
            </label>
          </div>
        </div>
      </div>

      <!-- Date Range -->
      <div
        class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-gray-50 rounded-lg p-4"
      >
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">
            Von (optional)
          </label>
          <input
            type="date"
            v-model="exportStartDate"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">
            Bis (optional)
          </label>
          <input
            type="date"
            v-model="exportEndDate"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>
      </div>

      <!-- Parameters Selection -->
      <div class="bg-gray-50 rounded-lg p-4">
        <label class="block text-sm font-semibold text-gray-700 mb-3">
          Parameter auswählen
        </label>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
          <label
            v-for="param in availableParameters"
            :key="param.value"
            class="flex items-center"
          >
            <input
              type="checkbox"
              :value="param.value"
              v-model="exportParameters"
              class="mr-2"
            />
            <span class="text-sm text-gray-700">{{ param.label }}</span>
          </label>
        </div>
      </div>

      <!-- Export Status -->
      <div
        v-if="exportLoading"
        class="bg-blue-50 border border-blue-200 rounded-lg p-4"
      >
        <div class="flex items-center gap-3">
          <i class="pi pi-spin pi-spinner text-blue-600"></i>
          <div>
            <p class="font-semibold text-blue-900">Export wird generiert...</p>
            <p class="text-sm text-blue-700">
              {{ exportStatus.record_count || 0 }} Datensätze
            </p>
          </div>
        </div>
      </div>

      <div
        v-if="exportError"
        class="bg-red-50 border border-red-200 rounded-lg p-4 text-red-700"
      >
        {{ exportError }}
      </div>

      <div
        v-if="exportSuccess"
        class="bg-green-50 border border-green-200 rounded-lg p-4 text-green-700"
      >
        <i class="pi pi-check-circle mr-2"></i>
        {{ exportSuccess }}
      </div>

      <!-- Export Button -->
      <div class="flex gap-3">
        <button
          @click="triggerExport"
          :disabled="exportLoading"
          class="flex-1 px-4 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 disabled:bg-gray-400 transition-colors flex items-center justify-center gap-2"
        >
          <i
            class="pi"
            :class="exportLoading ? 'pi-spin pi-spinner' : 'pi-download'"
          ></i>
          {{ exportLoading ? "Wird exportiert..." : "Jetzt exportieren" }}
        </button>
      </div>
    </div>

    <!-- IMPORT TAB -->
    <div v-if="activeTab === 'import'" class="space-y-6">
      <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <p class="text-sm text-blue-900">
          <i class="pi pi-info-circle mr-2"></i>
          Laden Sie CSV-, JSON- oder Excel-Dateien mit Messdaten hoch
        </p>
      </div>

      <!-- File Upload -->
      <div
        class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-blue-400 transition"
      >
        <input
          ref="fileInput"
          type="file"
          @change="handleFileSelect"
          accept=".csv,.json,.xlsx,.xls"
          class="hidden"
        />
        <button
          @click="openFileDialog"
          class="flex flex-col items-center gap-3 w-full"
        >
          <i class="pi pi-cloud-upload text-4xl text-blue-600"></i>
          <div>
            <p class="font-semibold text-gray-900">Datei hochladen</p>
            <p class="text-sm text-gray-600">CSV, JSON oder Excel</p>
          </div>
        </button>
      </div>

      <!-- Selected File Info -->
      <div v-if="selectedFile" class="bg-gray-50 rounded-lg p-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <i class="pi pi-file text-2xl text-gray-600"></i>
            <div>
              <p class="font-semibold text-gray-900">{{ selectedFile.name }}</p>
              <p class="text-sm text-gray-600">
                {{ formatFileSize(selectedFile.size) }}
              </p>
            </div>
          </div>
          <button
            @click="selectedFile = null"
            class="text-gray-600 hover:text-red-600 transition"
          >
            <i class="pi pi-trash"></i>
          </button>
        </div>
      </div>

      <!-- Import Options -->
      <div v-if="selectedFile" class="space-y-4 bg-gray-50 rounded-lg p-4">
        <div>
          <label class="flex items-center gap-2">
            <input type="checkbox" v-model="importOverwrite" class="rounded" />
            <span class="text-sm font-medium text-gray-700">
              Vorhandene Daten überschreiben
            </span>
          </label>
          <p class="text-xs text-gray-600 mt-1 ml-6">
            Wenn deaktiviert, werden nur neue Datensätze hinzugefügt
          </p>
        </div>
      </div>

      <!-- Import Status -->
      <div
        v-if="importLoading"
        class="bg-blue-50 border border-blue-200 rounded-lg p-4"
      >
        <div class="flex items-center gap-3">
          <i class="pi pi-spin pi-spinner text-blue-600"></i>
          <div>
            <p class="font-semibold text-blue-900">
              Import wird verarbeitet...
            </p>
            <p class="text-sm text-blue-700">
              {{ importStatus.processed || 0 }} /
              {{ importStatus.total || 0 }} Datensätze
            </p>
          </div>
        </div>
      </div>

      <div
        v-if="importError"
        class="bg-red-50 border border-red-200 rounded-lg p-4 text-red-700"
      >
        {{ importError }}
      </div>

      <div
        v-if="importSuccess"
        class="bg-green-50 border border-green-200 rounded-lg p-4 text-green-700"
      >
        <i class="pi pi-check-circle mr-2"></i>
        {{ importSuccess }}
      </div>

      <!-- Import Button -->
      <div class="flex gap-3">
        <button
          v-if="selectedFile"
          @click="triggerImport"
          :disabled="importLoading"
          class="flex-1 px-4 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 disabled:bg-gray-400 transition-colors flex items-center justify-center gap-2"
        >
          <i
            class="pi"
            :class="importLoading ? 'pi-spin pi-spinner' : 'pi-upload'"
          ></i>
          {{ importLoading ? "Wird importiert..." : "Jetzt importieren" }}
        </button>
      </div>
    </div>

    <!-- HISTORY TAB -->
    <div v-if="activeTab === 'history'" class="space-y-4">
      <div
        v-if="importHistory.length === 0"
        class="text-center py-8 text-gray-600"
      >
        <i class="pi pi-inbox text-4xl mb-3"></i>
        <p>Keine Importe gefunden</p>
      </div>

      <div v-else class="space-y-3">
        <div
          v-for="item in importHistory"
          :key="item.id"
          class="border rounded-lg p-4 hover:shadow transition"
        >
          <div class="flex items-start justify-between gap-3">
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-1">
                <i
                  :class="[
                    'pi text-lg',
                    item.success
                      ? 'pi-check text-green-600'
                      : 'pi-times text-red-600',
                  ]"
                ></i>
                <p class="font-semibold text-gray-900">
                  {{ item.import_type || "Datenimport" }}
                </p>
                <span
                  :class="[
                    'text-xs px-2 py-1 rounded',
                    item.success
                      ? 'bg-green-100 text-green-800'
                      : 'bg-red-100 text-red-800',
                  ]"
                >
                  {{ item.success ? "Erfolgreich" : "Fehlgeschlagen" }}
                </span>
              </div>
              <p class="text-sm text-gray-600">
                {{ item.records_imported || 0 }} Datensätze
                <span class="text-gray-500">
                  am {{ formatDate(item.created_at) }}
                </span>
              </p>
            </div>
            <button
              class="text-blue-600 hover:text-blue-800 transition text-sm font-medium"
            >
              Details
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from "vue";
import { apiService } from "@/services/api";

const activeTab = ref<"export" | "import" | "history">("export");

// Export state
const exportFormat = ref("csv");
const exportDataType = ref("measurements");
const exportStartDate = ref("");
const exportEndDate = ref("");
const exportParameters = ref<string[]>([
  "temp_mean",
  "precipitation",
  "sunshine",
]);
const exportLoading = ref(false);
const exportError = ref<string | null>(null);
const exportSuccess = ref<string | null>(null);
const exportStatus = ref({ record_count: 0 });

// Import state
const selectedFile = ref<File | null>(null);
const importOverwrite = ref(false);
const importLoading = ref(false);
const importError = ref<string | null>(null);
const importSuccess = ref<string | null>(null);
const importStatus = ref({ processed: 0, total: 0 });
const importHistory = ref<any[]>([]);

const fileInput = ref<HTMLInputElement | null>(null);

// Configuration
const exportFormats = [
  { value: "csv", label: "CSV", description: "Kommagetrennte Werte" },
  { value: "json", label: "JSON", description: "JavaScript Object Notation" },
  { value: "excel", label: "Excel", description: "Microsoft Excel (XLSX)" },
];

const dataTypes = [
  {
    value: "measurements",
    label: "Messdaten",
    description: "Tägliche Messwerte",
  },
  {
    value: "statistics",
    label: "Statistiken",
    description: "Aggregierte Daten",
  },
  {
    value: "stations",
    label: "Stationen",
    description: "Stationsinformationen",
  },
];

const availableParameters = [
  { value: "temp_max", label: "Max Temp" },
  { value: "temp_min", label: "Min Temp" },
  { value: "temp_mean", label: "Mittel Temp" },
  { value: "precipitation", label: "Niederschlag" },
  { value: "sunshine", label: "Sonnenschein" },
  { value: "snow_depth", label: "Schneehöhe" },
  { value: "cloud_cover", label: "Bewölkung" },
  { value: "wind_speed", label: "Windgeschwindigkeit" },
];

const triggerExport = async () => {
  exportLoading.value = true;
  exportError.value = null;
  exportSuccess.value = null;

  try {
    const response = await apiService.post("/v1/exports", {
      format: exportFormat.value,
      data_type: exportDataType.value,
      start_date: exportStartDate.value || null,
      end_date: exportEndDate.value || null,
      parameters: exportParameters.value,
    });

    if (response.success && response.data?.download_url) {
      // Trigger download
      window.location.href = response.data.download_url;
      exportSuccess.value = `Export erstellt (${response.data.estimated_records || "viele"} Datensätze)`;
    } else if (response.success) {
      exportSuccess.value = `Export erstellt (${response.data?.estimated_records || "viele"} Datensätze)`;
      exportStatus.value.record_count = response.data?.estimated_records || 0;
    } else {
      exportError.value = "Export konnte nicht generiert werden";
    }
  } catch (err) {
    exportError.value =
      err instanceof Error ? err.message : "Export fehlgeschlagen";
  } finally {
    exportLoading.value = false;
  }
};

const handleFileSelect = (event: Event) => {
  const target = event.target as HTMLInputElement;
  if (target.files && target.files[0]) {
    selectedFile.value = target.files[0];
    importError.value = null;
    importSuccess.value = null;
  }
};

const openFileDialog = () => {
  fileInput.value?.click();
};

const triggerImport = async () => {
  if (!selectedFile.value) return;

  importLoading.value = true;
  importError.value = null;
  importSuccess.value = null;

  try {
    const formData = new FormData();
    formData.append("file", selectedFile.value);
    formData.append("overwrite", importOverwrite.value ? "1" : "0");

    const response = await apiService.post("/v1/imports", formData);

    if (response.success) {
      importSuccess.value = `${response.data?.records_imported || 0} Datensätze erfolgreich importiert`;
      selectedFile.value = null;
      loadImportHistory();
    } else {
      importError.value = "Import fehlgeschlagen";
    }
  } catch (err) {
    importError.value =
      err instanceof Error ? err.message : "Import fehlgeschlagen";
  } finally {
    importLoading.value = false;
  }
};

const loadImportHistory = async () => {
  try {
    const response = await apiService.get("/v1/imports");
    if (response.success) {
      importHistory.value = response.data || [];
    }
  } catch (err) {
    console.error("Error loading import history:", err);
  }
};

const formatFileSize = (bytes: number): string => {
  if (bytes === 0) return "0 B";
  const k = 1024;
  const sizes = ["B", "KB", "MB", "GB"];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + " " + sizes[i];
};

const formatDate = (dateString: string): string => {
  return new Date(dateString).toLocaleDateString("de-DE", {
    year: "numeric",
    month: "2-digit",
    day: "2-digit",
    hour: "2-digit",
    minute: "2-digit",
  });
};

onMounted(() => {
  loadImportHistory();
});
</script>

<style scoped>
.export-import-panel {
  animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}
</style>

// Station types
export interface Station {
  id: string
  name: string
  lat: number | string
  lon: number | string
  elevation: number
  state: string
  start_date?: string
  end_date?: string
  start_year?: number
  measurement_count?: number
  latest_date?: string
  location?: string
  active: boolean
  created_at?: string
  updated_at?: string
}

// Import log types
export interface ImportLog {
  id: number
  import_type: string
  import_type_label?: string
  station_id: string | null
  station?: Station | null
  operation: string
  operation_label?: string
  records_processed: number
  records_imported: number
  records_skipped: number
  records_failed: number
  success: boolean
  status?: string
  duration_seconds: number
  formatted_duration?: string
  error_message: string | null
  parameters: Record<string, any> | null
  user_initiated: boolean
  created_at: string
  updated_at: string | null
}

// Measurement types
export interface Measurement {
  id: number
  station_id: string
  date: string
  temperature_max: number | null
  temperature_min: number | null
  temperature_mean: number | null
  precipitation: number | null
  sunshine: number | null
  snow_depth: number | null
  created_at: string
  updated_at: string
  station?: Station
}

// Statistics types
export interface OverallStatistics {
  total_imports: number
  successful_imports: number
  failed_imports: number
  total_records_processed: number
  total_records_imported: number
  total_records_skipped: number
  total_records_failed: number
  avg_duration_seconds: number
  last_import_date: string
}

export interface ImportStatistics {
  overall: OverallStatistics
  by_type: Record<string, number>
  by_station: Array<{
    id: string
    name: string
    import_count: number
  }>
  recent_imports: ImportLog[]
}

// API response types
export interface ApiResponse<T = any> {
  success: boolean
  data: T
  message?: string
  errors?: Record<string, string[]>
}

export interface PaginatedResponse<T = any> {
  data: T[]
  meta: {
    current_page: number
    last_page: number
    per_page: number
    total: number
    from: number
    to: number
  }
}

// Chart data types
export interface ChartData {
  labels: string[]
  datasets: Array<{
    label?: string
    data: number[]
    backgroundColor?: string | string[]
    borderColor?: string | string[]
    borderWidth?: number
    fill?: boolean
  }>
}

// Map types
export interface MapStation extends Station {
  import_count?: number
  last_import?: string
}

// Export types
export interface ExportFormat {
  id: string
  name: string
  description: string
  extension: string
}

export interface ExportRequest {
  format: string
  station_ids?: string[]
  date_from?: string
  date_to?: string
  parameters?: Record<string, any>
}

export interface ExportStatus {
  id: string
  status: 'pending' | 'processing' | 'completed' | 'failed'
  progress?: number
  download_url?: string
  error_message?: string
  created_at: string
  completed_at?: string
}
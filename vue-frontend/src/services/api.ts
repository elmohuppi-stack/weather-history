import axios, { type AxiosInstance, type AxiosResponse } from 'axios'

// API configuration
const API_BASE_URL = (import.meta as any).env?.VITE_API_BASE_URL || 'http://localhost:8000/api'

// Types
export interface Station {
  id: string
  name: string
  location: string
  elevation: number
  start_year: number
  measurement_count: number
  state: string
  latest_date: string
  active: boolean
  lat: number
  lon: number
}

export interface ApiResponse<T> {
  success: boolean
  data: T
  meta?: {
    total: number
    filtered: number
    page: number
    per_page: number
  }
}

export interface Measurement {
  id: number
  station_id: string
  date: string
  temp_max: number | null
  temp_min: number | null
  temp_mean: number | null
  precipitation: number | null
  sunshine: number | null
  snow_depth: number | null
  quality_flags: string
}

// API service class
class ApiService {
  private client: AxiosInstance

  constructor() {
    this.client = axios.create({
      baseURL: API_BASE_URL,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    })
  }

  // Stations API
  async getStations(params?: {
    state?: string
    active?: boolean
    page?: number
    per_page?: number
  }): Promise<ApiResponse<Station[]>> {
    const response: AxiosResponse<ApiResponse<Station[]>> = await this.client.get('/v1/stations', { params })
    return response.data
  }

  async getStation(id: string): Promise<ApiResponse<Station>> {
    const response: AxiosResponse<ApiResponse<Station>> = await this.client.get(`/v1/stations/${id}`)
    return response.data
  }

  async searchStations(query: string): Promise<ApiResponse<Station[]>> {
    const response: AxiosResponse<ApiResponse<Station[]>> = await this.client.get(`/v1/stations/search/${query}`)
    return response.data
  }

  // Measurements API
  async getMeasurements(params?: {
    station_id?: string
    start_date?: string
    end_date?: string
    page?: number
    per_page?: number
  }): Promise<ApiResponse<Measurement[]>> {
    const response: AxiosResponse<ApiResponse<Measurement[]>> = await this.client.get('/v1/measurements', { params })
    return response.data
  }

  async getMeasurementsByStation(stationId: string, params?: {
    start_date?: string
    end_date?: string
    page?: number
    per_page?: number
  }): Promise<ApiResponse<Measurement[]>> {
    const response: AxiosResponse<ApiResponse<Measurement[]>> = await this.client.get(`/v1/measurements/station/${stationId}`, { params })
    return response.data
  }

  async getMeasurementsByDateRange(params: {
    start_date: string
    end_date: string
    station_ids?: string[]
    page?: number
    per_page?: number
  }): Promise<ApiResponse<Measurement[]>> {
    const response: AxiosResponse<ApiResponse<Measurement[]>> = await this.client.get('/v1/measurements/date-range', { params })
    return response.data
  }

  // Statistics API
  async getStationStatistics(): Promise<ApiResponse<{
    total_stations: number
    active_stations: number
    total_measurements: number
    average_measurements_per_station: number
  }>> {
    const response: AxiosResponse<ApiResponse<any>> = await this.client.get('/v1/statistics/stations')
    return response.data
  }

  async getMeasurementStatistics(): Promise<ApiResponse<{
    total_measurements: number
    measurements_by_year: Record<string, number>
    measurements_by_parameter: Record<string, number>
  }>> {
    const response: AxiosResponse<ApiResponse<any>> = await this.client.get('/v1/statistics/measurements')
    return response.data
  }

  // Map API
  async getMapStations(): Promise<ApiResponse<Station[]>> {
    const response: AxiosResponse<ApiResponse<Station[]>> = await this.client.get('/v1/maps/stations')
    return response.data
  }

  // Export API
  async createExport(params: {
    station_ids: string[]
    start_date?: string
    end_date?: string
    format: 'csv' | 'json' | 'excel'
    parameters?: string[]
  }): Promise<ApiResponse<{ export_id: string; download_url: string }>> {
    const response: AxiosResponse<ApiResponse<{ export_id: string; download_url: string }>> = await this.client.post('/v1/exports', params)
    return response.data
  }
}

// Create and export singleton instance
export const apiService = new ApiService()

// Helper function to get full API URL
export function getApiUrl(path: string): string {
  return `${API_BASE_URL}${path.startsWith('/') ? path : `/${path}`}`
}

// Default export
export default apiService
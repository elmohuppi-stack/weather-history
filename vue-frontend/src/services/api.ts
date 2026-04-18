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

  // Statistics API - Updated for new endpoints
  async getOverallStatistics(): Promise<ApiResponse<any>> {
    const response: AxiosResponse<ApiResponse<any>> = await this.client.get('/v1/statistics/overall')
    return response.data
  }

  async getStationStatistics(stationId: string): Promise<ApiResponse<any>> {
    const response: AxiosResponse<ApiResponse<any>> = await this.client.get(`/v1/statistics/station/${stationId}`)
    return response.data
  }

  async getClimateNormals(params?: {
    period?: string
    station_ids?: string[]
  }): Promise<ApiResponse<any>> {
    const response: AxiosResponse<ApiResponse<any>> = await this.client.get('/v1/statistics/climate-normals', { params })
    return response.data
  }

  async getTrends(params: {
    parameter: string
    station_id?: string
    start_year?: number
    end_year?: number
  }): Promise<ApiResponse<any>> {
    const response: AxiosResponse<ApiResponse<any>> = await this.client.get('/v1/statistics/trends', { params })
    return response.data
  }

  // Measurements API - Additional methods
  async getLatestMeasurements(): Promise<ApiResponse<any>> {
    const response: AxiosResponse<ApiResponse<any>> = await this.client.get('/v1/measurements/latest')
    return response.data
  }

  // Map API - Updated for new endpoints
  async getMapStations(): Promise<ApiResponse<any>> {
    const response: AxiosResponse<ApiResponse<any>> = await this.client.get('/v1/maps/stations')
    return response.data
  }

  async getStationsWithinBounds(params: {
    north: number
    south: number
    east: number
    west: number
  }): Promise<ApiResponse<any>> {
    const response: AxiosResponse<ApiResponse<any>> = await this.client.get('/v1/maps/within-bounds', { params })
    return response.data
  }

  async getHeatmapData(params: {
    parameter: string
    year?: number
    month?: number
  }): Promise<ApiResponse<any>> {
    const response: AxiosResponse<ApiResponse<any>> = await this.client.get('/v1/maps/heatmap', { params })
    return response.data
  }

  async getClusterData(params: {
    zoom: number
  }): Promise<ApiResponse<any>> {
    const response: AxiosResponse<ApiResponse<any>> = await this.client.get('/v1/maps/clusters', { params })
    return response.data
  }

  // Export API - Updated for new endpoints
  async createExport(params: {
    format: 'csv' | 'json' | 'excel' | 'sql'
    data_type: 'stations' | 'measurements' | 'statistics'
    station_ids?: string[]
    start_date?: string
    end_date?: string
    parameters?: string[]
  }): Promise<ApiResponse<any>> {
    const response: AxiosResponse<ApiResponse<any>> = await this.client.post('/v1/exports', params)
    return response.data
  }

  async getExportStatus(exportId: string): Promise<ApiResponse<any>> {
    const response: AxiosResponse<ApiResponse<any>> = await this.client.get(`/v1/exports/${exportId}/status`)
    return response.data
  }

  async downloadExport(exportId: string, params?: {
    format?: string
  }): Promise<ApiResponse<any>> {
    const response: AxiosResponse<ApiResponse<any>> = await this.client.get(`/v1/exports/${exportId}/download`, { params })
    return response.data
  }

  async getExportFormats(): Promise<ApiResponse<any>> {
    const response: AxiosResponse<ApiResponse<any>> = await this.client.get('/v1/exports/formats')
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
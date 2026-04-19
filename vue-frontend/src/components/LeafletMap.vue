<template>
  <div class="leaflet-map-container">
    <div ref="mapContainer" class="w-full h-full"></div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import L from 'leaflet'
import type { Station } from '@/services/api'

interface Props {
  stations: Station[]
  selectedStation?: Station | null
  parameter?: string
  year?: number
}

interface Emits {
  (e: 'station-selected', station: Station): void
}

const props = withDefaults(defineProps<Props>(), {
  parameter: 'temperature',
  year: 2024
})

const emit = defineEmits<Emits>()

const mapContainer = ref<HTMLElement>()
let map: L.Map | null = null
let markers: L.CircleMarker[] = []
const stationMarkerMap = new Map<string, L.CircleMarker>()

// Initialize Leaflet map
const initMap = () => {
  if (!mapContainer.value || map) return

  // Create map centered on Germany
  map = L.map(mapContainer.value).setView([51.1657, 10.4515], 6)

  // Add OpenStreetMap tiles
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution:
      '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    maxZoom: 19,
  }).addTo(map)

  // Add markers for stations
  renderStations()

  // Fit bounds to show all markers
  if (markers.length > 0) {
    const group = new L.FeatureGroup(markers)
    map.fitBounds(group.getBounds().pad(0.1))
  }
}

// Render station markers on map
const renderStations = () => {
  if (!map) return

  // Clear existing markers
  markers.forEach(marker => map!.removeLayer(marker))
  markers = []
  stationMarkerMap.clear()

  props.stations.forEach(station => {
    // Create color based on active status
    const color = station.active ? '#3b82f6' : '#9ca3af'
    
    // Create circle marker
    const marker = L.circleMarker([station.lat, station.lon], {
      radius: 8,
      fillColor: color,
      color: '#ffffff',
      weight: 2,
      opacity: 1,
      fillOpacity: 0.8,
    })

    // Add popup with station info
    marker.bindPopup(
      `<div class="text-sm">
        <h4 class="font-semibold text-gray-900">${station.name}</h4>
        <p class="text-xs text-gray-600">${station.location}</p>
        <p class="text-xs text-gray-600">ID: ${station.id}</p>
        <p class="text-xs text-gray-600">Höhe: ${station.elevation}m</p>
        <p class="text-xs text-gray-600">Messungen: ${station.measurement_count?.toLocaleString() || 'N/A'}</p>
      </div>`
    )

    // Add click handler
    marker.on('click', () => {
      emit('station-selected', station)
      // Highlight selected station
      updateSelectedMarker(station.id)
    })

    // Hover tooltip
    marker.on('mouseover', () => {
      marker.openPopup()
    })
    marker.on('mouseout', () => {
      marker.closePopup()
    })

    marker.addTo(map!)
    markers.push(marker)
    stationMarkerMap.set(station.id, marker)
  })
}

// Update selected marker visualization
const updateSelectedMarker = (stationId: string) => {
  // Reset all markers
  stationMarkerMap.forEach(marker => {
    marker.setStyle({
      radius: 8,
      fillOpacity: 0.8,
      weight: 2,
    })
  })

  // Highlight selected marker
  const selectedMarker = stationMarkerMap.get(stationId)
  if (selectedMarker) {
    selectedMarker.setStyle({
      radius: 12,
      fillOpacity: 1,
      weight: 3,
    })
    
    // Pan to marker
    if (map) {
      map.panTo(selectedMarker.getLatLng())
    }
  }
}

// Watch for changes
watch(() => props.stations, renderStations, { deep: true })

watch(
  () => props.selectedStation,
  (newStation) => {
    if (newStation) {
      updateSelectedMarker(newStation.id)
    }
  }
)

// Lifecycle
onMounted(() => {
  initMap()
})
</script>

<style scoped>
.leaflet-map-container {
  width: 100%;
  height: 500px;
  border-radius: 0.5rem;
  overflow: hidden;
}

:deep(.leaflet-container) {
  font-family: inherit;
}

:deep(.leaflet-popup-content-wrapper) {
  border-radius: 0.375rem;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}
</style>

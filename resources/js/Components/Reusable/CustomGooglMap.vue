<script setup>
import { current_user, route } from "@/Helpers/api_helper";
import { defineExpose, ref, watch } from 'vue';
import { CustomMarker, GoogleMap, MarkerCluster } from "vue3-google-map";
import { getApiKey } from '@/Helpers/saas';
import { GoogleMapStyle } from '@/utils/MapStyle'
import { computed } from "vue";
const Props$ = defineProps({
  binMarker: {
    type: [Array, Object],
    default: []
  }
})
const center = { lat: 26.921040, lng: 75.794357 };
const mapRef = ref(null);
const binMarkers = computed(()=>{
  return [
    { lat: current_user()?.lat || 26.921040, lng: current_user().lng || 75.794357 }, // Start point
    ...Props$.binMarker
  ]
})
// watch(() => Props$.binMarker, () => {
// alert('ddd')
//   console.log("🚀 -> 🌏 ~ file: CustomGooglMap.vue:21 ~ watch ~ newValue:", Props$.binMarker)
//   binMarkers.value = Props$.binMarker
//   binMarkers.value.unshift(
//     { lat: current_user()?.lat || 26.921040, lng: current_user().lng || 75.794357 }, // Start point
//   )
// })
const points = ref(
  [
    { lat: current_user()?.lat || 26.921040, lng: current_user().lng || 75.794357 }, // Start point
  ]
);
const loading = ref  (false)
const getPolyline = async (data) => {
  loading.value = !loading.value
  points.value = await data
  points.value.unshift(
    { lat: current_user()?.lat || 26.921040, lng: current_user().lng || 75.794357 }, // Start point
  )
  if (!window.google || !window.google.maps) {
    alert('google map api not loaded')
    console.error('Google Maps API is not loaded.');
    return;
  }
  const directionsService = new google.maps.DirectionsService();
  const request = {
    origin: points.value[0], // Start point
    destination: points.value[0], // End point
    waypoints: points.value.slice(1).map(point => ({ location: point, stopover: true })), // Waypoints
    optimizeWaypoints: true, // Optimize waypoints
    travelMode: google.maps.TravelMode.DRIVING // Travel mode
  };
  directionsService.route(request, (result, status) => {
    if (status === google.maps.DirectionsStatus.OK) {
      const path = result.routes[0].overview_path;
      const polyline = new google.maps.Polyline({
        path,
        geodesic: true,
        strokeColor: '#000000',
        strokeOpacity: 1.0,
        strokeWeight: 3
      });
      mapRef.value.map.zoom = 15;
      polyline.setMap(mapRef.value.map);
    } else {
      push.error(status == 'ZERO_RESULTS' ? 'route not created for some bins' : status)
    }
  });
};
const zoomMap = () => {
  if (mapRef.value && mapRef.value.map) {
    mapRef.value.map.zoom = 20;
  }
};
const getColor = (color) => {
  if (color >= 0 && color <= 25) {
    return 'green';
  } else if (color > 25 && color <= 50) {
    return 'yellow';
  } else if (color > 50 && color <= 75) {
    return 'blue';
  } else if (color > 75 && color <= 100) {
    return 'red';
  } else {
    return 'Invalid color value';
  }
}
defineExpose({ getPolyline });
</script>
<template>
  <GoogleMap :key="loading" ref="mapRef" :api-key="getApiKey('GMAP_API')" :styles="GoogleMapStyle"
    style="width: 100%; height: 500px" :center="binMarkers[0]" :zoom="14">
    <MarkerCluster>
      <CustomMarker v-for="(location, i) in binMarkers" :options="{ position: location }"
        :key="`${location.lat}_${location.lng}`">
        <div style="text-align: center">
          <v-card class="ma-1" elevation="10">
            <v-card-text class="pa-1">
              <!-- <v-icon :color="i === 0 ? '#ff5e25' : ''" size="25"
                :icon="i === 0 ? 'mdi-office-building-marker-outline' : 'mdi-delete-circle'"></v-icon> -->
              <div class="d-flex align-center" v-if="i == 0">
                <span class="">Office</span>
                <v-icon color="black" size="25" icon="mdi-office-building-marker-outline"></v-icon>
              </div>
              <div class="d-flex align-center" v-else>
                <span>{{ location.name }} <span class="!tw-text-sm">-{{ location.completeness }}</span></span>
                <v-icon :color="getColor(location.completeness)" size="25" icon="mdi-delete-circle"></v-icon>
              </div>
            </v-card-text>
          </v-card>
        </div>
      </CustomMarker>
    </MarkerCluster>
  </GoogleMap>
  <div>
    <span>
    </span>
  </div>
  <!-- <button @click="getPolyline">Get Polyline</button> -->
</template>

<script>
import { Head, Link, usePage, useForm, router } from "@inertiajs/vue3";
import {defineComponent} from 'vue'
import {CustomMarker, GoogleMap, InfoWindow, Marker, MarkerCluster} from "vue3-google-map";
// import {router} from "@inertiajs/vue3";
import {Ziggy} from "@/ziggy";
import {route} from "@/Helpers/api_helper";
import Notification from "@/Components/Reusable/Notification.vue";
// const currentUser = computed(() => usePage().props.auth.user)
import {getApiKey, getModel, getPlatformConfig, LaravelDatatable} from "@/Helpers/saas";


export default defineComponent({
  name: "GMAP",
  props: {
    tenantMode: {
      type: Boolean,
      default: false,
    },
    canDrop: {
      type: Boolean,
      default: false,
    },
    line: {
      default: false,
    },
    showBins: {
      type: Boolean,
      default: false,
    },
    showBin: {
      type: Boolean,
      default: false,
    },
    showMyLocation:{
      type: Boolean,
      default: false,
    },
    showVehicles: {
      type: Boolean,
      default: false,
    },
    showLocations: {
      type: Boolean,
      default: false,
    }
  },
  emits: ['pinDrop'],
  computed: {
    hasRoute() {
      return this.line.length > 1;


    },
    needsMoreBinsMap(){
      return this.line.length < 2 && this.hasRoute
    },
    needsMoreBins() {
      return this.line.length < 2;
    },
    bins() {
      if (this.showBins) {
        const dd =  new LaravelDatatable('bins').models
        return dd
      }
      return []
    },
    vehicles() {
      if (this.showVehicles) {
        return new LaravelDatatable('vehicles').models
      }
      return []
    },
    locations() {
      if (this.showLocations) {
        return new LaravelDatatable('locations').models
      }
      return []
    },

    pins() {
      if (this.tenantMode){
        return [{position:getModel('tenant').map_center}]
      }
      return []

    },


  },
  methods: {
    getModel,
    getApiKey,
    getPlatformConfig,
    showBinInfo(marker) {
      router.get(route(Ziggy.routes["bins.show"], {"bin": marker.id}))
    },
    dropPin({latLng}) {
      if (this.tenantMode){
        this.tenantCenter={position:{lng: latLng.lng(), lat: latLng.lat()}}
        this.map_center={lng: latLng.lng(), lat: latLng.lat()}


      }
      if (this.canDrop) {
        if (this.marker_info.length) {
          this.marker_info.forEach(marker => {
            marker.setMap(null)
          })
          this.marker_info = []

        }
        const marker = new google.maps.Marker({
          position: latLng,
          map: this.$refs.mapRef.map,
        });
        this.marker_info.push(marker)
        this.$emit('pinDrop', {lng: latLng.lng(), lat: latLng.lat(),zoom:this.$refs.mapRef.map.getZoom()})

      } else {
        console.warn("YOU can not drop pin here")
      }


    },

    calcRoute() {
      if ( window?.google){
      const map = this.$refs.mapRef?.map


      const markerArray = this.line.map((marker) => {

              return new window.google.maps.Marker({
                map,
                position: marker.marker_options.position,
              })



          });

      const directionsService =  new google.maps.DirectionsService();
      let  first=null;
      let middle=[];
      let last=null
      // First, remove any existing markers from the map.
      for (let i = 0; i < markerArray.length; i++) {
        markerArray[i].setMap(null)
        if(i===0){
          first={location:this.line[i].marker_options.position};
        }
        if(i === (markerArray.length - 1)){
          last={location:this.line[i].marker_options.position};
        }else{
          middle.push({location:this.line[i].marker_options.position})
        }


      }



      // Retrieve the start and end locations and create a DirectionsRequest using
      directionsService
          .route({
            origin: first,
            waypoints:middle,
            destination: last,
            travelMode: google.maps.TravelMode.DRIVING,
          })
          .then((result) => {

            const route=result.routes[0]


            if (this.path) {
              this.path.setMap(null);
            }
            this.path = new google.maps.Polyline({
              path: route.overview_path,
              geodesic: true,
              strokeColor: "#ff5e25",
              strokeOpacity: 1.0,
              strokeWeight: 3,
            });
            if(map?.fitBounds){
              map?.fitBounds(route.bounds);
            }

            this.path.setMap(map);
          })
          .catch((e) => {
            window.alert("Directions request failed due to " + e);
          });

      }


    }
  },
  mounted(){
if(this.showMyLocation){
// this.currentUser
this.clientMap.lat = Number(this.currentUser.lat)
this.clientMap.lng  = Number(this.currentUser.lng)
// this.map_center={lng: Number(this.currentUser.lat), lat: Number(this.currentUser.lng)}

}
  },
  created() {
    if(this.hasRoute){
      const set=()=>{this.calcRoute()}

      setTimeout(set,1000)

    }
    if(this.tenantMode){
      this.map_center=getModel('tenant')?.map_center
    }




  },

  updated() {
    if(this.hasRoute){
      this.calcRoute()
    }


  },
  data() {
    return {
      tenantCenter:{"position":getModel('tenant')?.map_center},
      currentUser:usePage().props.auth.user,
      map_center: getPlatformConfig('map_center'),
      marker_info: [],
      clientMap:{ lat: -25.363, lng: 131.044 },
      map_zoom:  getPlatformConfig('map_center')?.zoom||getModel('tenant').map_center?.zoom,
      //https://mapstyle.withgoogle.com/
      map_styles: [
        {
          "featureType": "administrative",
          "elementType": "geometry",
          "stylers": [
            {
              "visibility": "off"
            }
          ]
        },
        {
          "featureType": "poi",
          "stylers": [
            {
              "visibility": "off"
            }
          ]
        },
        {
          "featureType": "road",
          "elementType": "labels.icon",
          "stylers": [
            {
              "visibility": "off"
            }
          ]
        },
        {
          "featureType": "transit",
          "stylers": [
            {
              "visibility": "off"
            }
          ]
        }
      ]
    }
  },
  components: {Notification, GoogleMap, InfoWindow, Marker, CustomMarker, MarkerCluster}
})
</script>

<template>


  <v-snackbar
      color="warning"
      v-model="needsMoreBins"
      :timeout="3000"
  >
  {{ $t('you need ') }}{{2-line.length}} {{ $t('bins for a route') }}
  </v-snackbar>

  <v-alert color="warning" class="ma-2" v-if="needsMoreBins" > {{ $t('you need ') }} {{2-line.length}} {{ $t('bins for a route') }}</v-alert>
  <GoogleMap  @click="dropPin" ref="mapRef" :styles  ="map_styles" :clickable-icons="false"
             :api-key="getApiKey('GMAP_API')" style="border-radius: 10px;overflow: hidden;width: 100%; height:100%" :center="map_center"
             :zoom="map_zoom">
    <template v-if="hasRoute">
      <CustomMarker v-for="(marker, i) in line" :options="marker.marker_options">
        <div style="text-align: center">

          <v-card  class="ma-1"  elevation="10">

            <v-card-text class="pa-1" >
              {{ marker.name }}
              <v-icon @click="showBinInfo(marker)" size="25" :color="marker.color" icon="mdi-delete-circle"></v-icon>
            </v-card-text>

          </v-card>
        </div>
      </CustomMarker>
    </template>
    <MarkerCluster v-if="showBin">
      <CustomMarker  :options="getModel('bin').marker_options">
        <div style="text-align: center">

          <v-card elevation="10">
            <v-icon @click="showBinInfo(getModel('bin'))" size="25" :color="getModel('bin').color" icon="mdi-delete-circle"></v-icon>
          </v-card>
        </div>
      </CustomMarker>
    </MarkerCluster>

    <MarkerCluster v-if="showBins">
      <CustomMarker v-for="(marker, i) in bins" :options="marker.marker_options">
        <div style="text-align: center">

          <v-card elevation="10">
            <v-icon @click="showBinInfo(marker)" size="25" :color="marker.color" icon="mdi-delete-circle"></v-icon>
          </v-card>
        </div>
      </CustomMarker>
    </MarkerCluster>
    <MarkerCluster v-if="showVehicles">
      <CustomMarker v-for="(marker, i) in vehicles" :options="marker.marker_options" :key="marker.id">
        <div style="text-align: center">

          <v-card elevation="10">
            <v-card-title>{{ marker.licence_plate }}</v-card-title>
          </v-card>
        </div>
      </CustomMarker>
    </MarkerCluster>

    <CustomMarker :options="{ position: clientMap, anchorPoint: 'BOTTOM_CENTER',zoom:15 }" v-if="showMyLocation" >
        <div style="text-align: center">
          <v-icon  size="30" color="primary" icon="mdi-account"></v-icon>

          
        </div>
      </CustomMarker>
    <MarkerCluster v-if="showLocations">
      <Marker v-for="(marker, i) in locations" :options="marker.marker_options" :key="marker.id">

      </Marker>
    </MarkerCluster>

    <Marker  :options="tenantCenter" >

    </Marker>




  </GoogleMap>


</template>

<style scoped>

</style>
<template>
  <v-card>
    <v-card-text>
      <!-- {{ date }} -->
      <v-row>
      <v-col>
      <v-text-field type="date" :label="$t('collected_date')"
                    :v-model="form.collected_date"
                    :error-messages="form.errors.collected_date"/>
        </v-col>
        <v-col>
          <v-autocomplete
          :label="$t('routes')"
          v-model="form.route_id"
          v-model:search="searchRoute"
          :loading="loading"
          item-value="id"
          item-title="name"
          :items="getSelectList('routes').data"
      />
        </v-col></v-row>
      <v-autocomplete
          :label="$t('Vehicles')"
          v-model="form.vehicle_id"
          v-model:search="searchVehicle"
          :loading="loading"
          item-value="id"
          item-title="licence_plate"
          :items="getSelectList('vehicles').data"
      />


      <v-text-field :label="$t('fuel_cost')"
                    v-model="form.fuel_cost"
                    :error-messages="form.errors.fuel_cost"/>

      

      <v-autocomplete
          :label="$t('receiver')"
          v-model="form.receiver"
          v-model:search="searchLocation"
          :loading="loading"
          :items="getSelectList('locations').data"
      />

      <v-row>
      <v-col>
      <v-text-field :label="$t('collected_percentage')"
                    v-model="form.collected_percentage"
                    :error-messages="form.errors.collected_percentage"/>
        </v-col>
        <v-col>

      <v-text-field :label="$t('collected_waste_mass')"
                    v-model="form.collected_waste_mass"
                    :error-messages="form.errors.collected_waste_mass"/></v-col></v-row>
      <!-- {{ form }} -->


    </v-card-text>
    <v-card-actions>
      <v-btn :disabled="!form.isDirty" elevation="2" variant="flat" color="primary"
             @click="registerRoute_collection">
        {{ $t('Edit Route_collection') }}
      </v-btn>
    </v-card-actions>

  </v-card>


</template>

<script>

import {router, useForm} from "@inertiajs/vue3";
import {Ziggy} from "@/ziggy";
import {image_url, route} from "@/Helpers/api_helper";
import {getSelectList} from "@/Helpers/saas";




export default {
  name: "EditRoute_collection",
  props: ['modelValue'],
  emits: ['update:modelValue','close_dialog'],
  components: {},
  computed: {
    date() {

      let date = new Date(this.form.collected_date)

      return date.getMonth() + "/" + date.getDate()  +"/" + date.getFullYear()
    },

  },
  created() {
    // console.log(Date(this.form.collected_date))
  },
  watch: {
    searchLocation(val) {
      val && val !== this.select && router.get(route(Ziggy.routes['log.index'], {location: val}), {}, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
          this.loading = false
        }
      })
    },
    searchRoute(val) {
      val && val !== this.select && router.get(route(Ziggy.routes['log.index'], {route: val}), {}, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
          this.loading = false
        }
      })
    },
    searchVehicle(val) {
      val && val !== this.select && router.get(route(Ziggy.routes['log.index'], {vehicle: val}), {}, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
          this.loading = false
        }
      })
    },

  },
  methods: {
    getSelectList,
    querySelections(v) {

      router.get(route(Ziggy.routes['log.index'], {location: v}), {}, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
          this.loading = false
        }
      })
    },
    image_url,

    registerRoute_collection() {
      this.form.post(route(Ziggy.routes["log.store"]), {
        onSuccess: () => {
          router.visit(route(
              Ziggy.routes["log.index"]), {
            only: ['route_collections'],
            preserveScroll: true
          })
          push.success('log updated successfully')
          this.$emit("close_dialog")
        }
      })

    },

  },
  data() {
    const route_collection_data = this.modelValue
    return {
      searchVehicle: null,
      searchRoute: null,
      searchLocation: null,
      form: useForm({
        ...route_collection_data
      })


    }
  },
}
</script>

<style scoped>

</style>

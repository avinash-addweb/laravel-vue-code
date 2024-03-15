<template>
  <v-responsive>
    <v-row>
      <v-col>
        <v-card height="100%">
          <v-card-title>{{ $t('Add Route Collection') }}</v-card-title>
          <v-card-text>
            <v-row>

            <v-col>
            <v-text-field :label="$t('collected_date')" type="date" v-model="form.collected_date"
              :error-messages="form.errors.collected_date" />
              </v-col><v-col>
                <v-autocomplete :label="$t('routes')" v-model="form.route_id" :error-messages="form.errors.route_id"
              v-model:search="searchRoute" :loading="loading" item-value="id" item-title="name"
              :items="getSelectList('routes').data" />
              </v-col></v-row>
            <v-autocomplete :label="$t('Vehicles')" v-model="form.vehicle_id" v-model:search="searchVehicle"
              :error-messages="form.errors.vehicle_id" :loading="loading" item-value="id" item-title="licence_plate"
              :items="getSelectList('vehicles').data" />
            <v-text-field :label="$t('fuel_cost')" v-model="form.fuel_cost" :error-messages="form.errors.fuel_cost" />
            
            <v-autocomplete :label="$t('receiver')" v-model="form.receiver" v-model:search="searchLocation"
              :loading="loading" :error-messages="form.errors.receiver" :items="getSelectList('locations').data" />
              <v-row>

<v-col>
              <v-text-field :label="$t('collected_percentage')" v-model="form.collected_percentage"
              :error-messages="form.errors.collected_percentage" /></v-col>
              <v-col>
            <v-text-field :label="$t('collected_waste_mass')" v-model="form.collected_waste_mass"
              :error-messages="form.errors.collected_waste_mass" /></v-col></v-row>
            <!-- {{form}} -->
          </v-card-text>
          <v-card-actions>
            <v-btn :disabled="!form.isDirty" elevation="2" variant="flat" color="primary"
              @click="registerRoute_collection">
              {{ $t('Add Route Collection') }}
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-col>
    </v-row>
  </v-responsive>
</template>
<script>
import { router, useForm } from "@inertiajs/vue3";
import { Ziggy } from "@/ziggy";
import { image_url, route } from "@/Helpers/api_helper";
import { getSelectList } from "@/Helpers/saas";
export default {
  name: "AddRouteCollection",
  props: ['modelValue'],
  emits: ['update:modelValue'],
  components: {},
  computed: {},
  created() {
  },
  watch: {
    searchLocation(val) {
      val && val !== this.select && router.get(route(Ziggy.routes['log.index'], { location: val }), {}, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
          this.loading = false
        }
      })
    },
    searchRoute(val) {
      val && val !== this.select && router.get(route(Ziggy.routes['log.index'], { route: val }), {}, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
          this.loading = false
        }
      })
    },
    searchVehicle(val) {
      val && val !== this.select && router.get(route(Ziggy.routes['log.index'], { vehicle: val }), {}, {
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
      router.get(route(Ziggy.routes['log.index'], { location: v }), {}, {
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
          push.success('new log created successfully')
          router.visit(route(
            Ziggy.routes["log.index"]), {
            only: ['route_collections'],
            preserveScroll: true
          })
        }
      })
    },
  },
  data() {
    return {
      loading: false,
      searchVehicle: null,
      searchRoute: null,
      searchLocation: null,
      select: null,
      form: useForm({
        'collected_date': "",
        'vehicle_id': "",
        'fuel_cost': "",
        'route_id': "",
        'receiver': "",
        'collected_percentage': "",
        'collected_waste_mass': "",
      })
    }
  },
}
</script>
<style scoped></style>

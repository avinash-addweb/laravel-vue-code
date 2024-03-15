<template>
  <v-responsive>
    <v-row>
 <v-col>
    <v-card height="100%">
      <v-card-title>{{ $t('Add Route') }} </v-card-title>
      <v-card-text>
        <v-text-field :label="$t('name')" v-model="new_route_form.name"
          :error-messages="new_route_form.errors.name" />
        <v-text-field :label="$t('location')" v-model="new_route_form.location"
          :error-messages="new_route_form.errors.location" />
        <v-text-field :label="$t('total_distance')" v-model="new_route_form.total_distance"
          :error-messages="new_route_form.errors.total_distance" />
        <v-select :label="$t('waste_type')" item-title="label" item-value="id"
          :error-messages="new_route_form.errors.waste_type" :items="waste_types"
          v-model="new_route_form.waste_type" />
        <v-text-field :label="$t('weekly_collection_rate')" v-model="new_route_form.weekly_collection_rate"
          :error-messages="new_route_form.errors.weekly_collection_rate" />
        <v-checkbox :label="$t('toll_cost')" v-model="new_route_form.toll_cost"
          :error-messages="new_route_form.errors.toll_cost" />
        <v-text-field :label="$t('note')" v-model="new_route_form.note"
          :error-messages="new_route_form.errors.note" />
      </v-card-text>
      <v-card-actions>
        <v-btn :disabled="!new_route_form.isDirty" elevation="2" variant="flat" color="primary"
          @click="registerRoute">
          {{ $t('Add Route') }}
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
import { getPlatformConfig } from "@/Helpers/saas";
export default {
  name: "AddRoute",
  props: ['modelValue'],
  emits: ['update:modelValue'],
  components: {},
  computed: {
    waste_types() {
      return getPlatformConfig('waste_profiles')
    }
  },
  methods: {
    getPlatformConfig,
    image_url,
    registerRoute() {
      this.new_route_form.post(route(Ziggy.routes["routes.store"]), {
        onSuccess: () => {
          router.visit(route(
            Ziggy.routes["routes.index"]), {
            only: ['routes'],
            preserveScroll: true
          })
          push.success('new route created successfully')
        }
      })
    },
    addRoute(gps) {
      // fix
      this.gpsItems.tempPins[0] = (gps)
      this.new_route_form.lat = gps.lat
      this.new_route_form.lng = gps.long
    },
    update_pin(data) {
      this.map_area[data.index] = data.pin;
    },
    saveRoute() {
      this.new_route_form.post(route("routes.store"))
    },
  },
  data() {
    return {
      new_route_form: useForm({
        "location": "",
        "total_distance": "",
        name: "",
        waste_type: "",
        weekly_collection_rate: "",
        final_receiver: "",
        toll_cost: false,
        note: ""
      })
    }
  },
}
</script>

<style scoped></style>

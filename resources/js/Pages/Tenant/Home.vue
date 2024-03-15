<script setup>
import TenantLayout from "@/Layouts/Tenant/TenantLayout.vue";
import { Head, Link, usePage, useForm, router } from "@inertiajs/vue3";
import { current_user, route } from "@/Helpers/api_helper";
import { Ziggy } from "@/ziggy";
import GMAP from "@/Components/Reusable/GMAP.vue";
import Datatable from "@/Components/Reusable/Datatable.vue";
import LineChart from "@/Components/Reusable/LineChart.vue";
import MultipleBarChart from "@/Components/Reusable/MultipleBarChart.vue";
import CustomGooglMap from "@/Components/Reusable/CustomGooglMap.vue";
import DoughnutChart from "@/Components/Reusable/DoughnutChart.vue";
import CustomDateFilter from "@/Components/Reusable/CustomDateFilter.vue";
import { parseUrlParams } from "@/Helpers/saas";
import AddBin from "@/Components/Tenant/Bins/AddBin.vue";
import ImportData from "@/Components/Tenant/Bins/ImportData.vue";
import VDateRange from "@/Components/VDateRange.vue";
import { computed, ref } from "vue";
import moment from "moment";
defineProps({
  users: Object,
});
const chartIds = {
  1: 'waste_production_daily_production',
  2: 'waste_production_monthly_production',
  3: 'waste_production_yearly_production',
  4: 'waste_categories_year',
  5: 'daily_vehicle_capacity',
  6: 'monthly_vehicle_capacity',
  7: 'daily_average_bin_capacity',
  8: 'monthly_average_bin_capacity',
  9: 'waste_categories_target',
  10: 'daily_iot_bins_payt',
}
const dateForm = ref({
  startDate: moment().subtract(2, 'months').format('YYYY-MM-DD'),
  endDate: moment().format('YYYY-MM-DD'),
})
function submitFilterForm(id) {
  const data = { id: id, ...dateForm.value }
  // return
  router.get(route(Ziggy.routes["tenant.home"]), data, { preserveScroll: true })
}
function clearFilter() {
  router.get(route(Ziggy.routes["tenant.home"]))
}
const getPolylineRef = ref(null)
const createPolyline = async (data) => {
  const gg = data.map(value => ({
    name: value.name,
    completeness: value.completeness,
    lat: Number(value.lat),
    lng: Number(value.lng)
  }));
  await getPolylineRef.value.getPolyline(gg);
}

// const createBinMarker = async (data) => {
//   const gg = data.map(value => ({
//     name: value.name,
//     completeness: value.completeness,
//     lat: Number(value.lat),
//     lng: Number(value.lng)
//   }));
//   await getPolylineRef.value.getBinMarker(gg);
// } 
// const {data} = usePage().props.datatable_bins
const createBinMarkerComputed=computed(()=>{
    const gg = usePage().props.datatable_bins.data.map(value => ({
    name: value.name,
    completeness: value.completeness,
    lat: Number(value.lat),
    lng: Number(value.lng)
  }));
  return gg
})
</script>
<template>
  <TenantLayout :seo_tile="$t('Home')">
    <template #content>
      <div style="margin-top: 50px;">
      </div>
      <div class="tw-grid tw-grid-cols-12 tw-gap-5">
        <div style="padding: 10px;" class="border rounded tw-col-span-12 lg:tw-col-span-6">
          <div class="tw-py-4 tw-flex tw-items-center tw-justify-end tw-space-x-4">
            <VDateRange :oldDate="parseUrlParams(chartIds[1], false)" @endDateEmit="dateForm.endDate = $event"
              @startDateEmit="dateForm.startDate = $event"></VDateRange>
            <v-btn size="small" @click="submitFilterForm(chartIds[1])"><v-icon size="x-large"
                icon="mdi-filter-outline" /></v-btn>
            <v-btn size="small" v-if="parseUrlParams(chartIds[1], false)" @click="clearFilter"><v-icon size="x-large"
                icon="mdi-delete-outline" /></v-btn>
          </div>
          <LineChart name="daily waste_production_"
            :data="usePage().props?.select_list_waste_production_daily_production?.data"
            :labels="usePage().props?.select_list_waste_production_daily_production?.labels"></LineChart>
        </div>
        <div style="padding: 10px;" class="border rounded tw-col-span-12 lg:tw-col-span-6">
          <div class="tw-py-4 tw-flex tw-items-center tw-justify-end tw-space-x-4">
            <VDateRange :oldDate="parseUrlParams(chartIds[2], false)" @endDateEmit="dateForm.endDate = $event"
              @startDateEmit="dateForm.startDate = $event"></VDateRange>
            <v-btn size="small" @click="submitFilterForm(chartIds[2])"><v-icon size="x-large"
                icon="mdi-filter-outline" /></v-btn>
            <v-btn size="small" v-if="parseUrlParams(chartIds[2], false)" @click="clearFilter"><v-icon size="x-large"
                icon="mdi-delete-outline" /></v-btn>
          </div>
          <MultipleBarChart :stacked="true" name="monthly waste_production"
            :data="usePage().props?.select_list_waste_production_monthly_production?.data"
            :labels="usePage().props?.select_list_waste_production_monthly_production?.labels">
          </MultipleBarChart>
        </div>
        <div style="padding: 10px;" class="border rounded tw-col-span-12 lg:tw-col-span-6">
          <div class="tw-py-4 tw-flex tw-items-center tw-justify-end tw-space-x-4">
            <VDateRange :oldDate="parseUrlParams(chartIds[3], false)" @endDateEmit="dateForm.endDate = $event"
              @startDateEmit="dateForm.startDate = $event"></VDateRange>
            <v-btn size="small" @click="submitFilterForm(chartIds[3])"><v-icon size="x-large"
                icon="mdi-filter-outline" /></v-btn>
            <v-btn size="small" v-if="parseUrlParams(chartIds[3], false)" @click="clearFilter"><v-icon size="x-large"
                icon="mdi-delete-outline" /></v-btn>
          </div>
          <MultipleBarChart name="yearly waste_production" :stacked="true"
            :data="usePage().props?.select_list_waste_production_yearly_production?.data"
            :labels="usePage().props?.select_list_waste_production_yearly_production?.labels">
          </MultipleBarChart>
        </div>
        <div style="padding: 10px;" class="border rounded tw-col-span-12 lg:tw-col-span-6">
          <div class="tw-py-4 tw-flex tw-items-center tw-justify-end tw-space-x-4">
            <VDateRange :oldDate="parseUrlParams(chartIds[4], false)" @endDateEmit="dateForm.endDate = $event"
              @startDateEmit="dateForm.startDate = $event"></VDateRange>
            <v-btn size="small" @click="submitFilterForm(chartIds[4])"><v-icon size="x-large"
                icon="mdi-filter-outline" /></v-btn>
            <v-btn size="small" v-if="parseUrlParams(chartIds[4], false)" @click="clearFilter"><v-icon size="x-large"
                icon="mdi-delete-outline" /></v-btn>
          </div>
          <DoughnutChart name="year waste_categories"
            :data="usePage().props?.select_list_waste_categories_year?.data"
            :labels="usePage().props?.select_list_waste_categories_year.labels"></DoughnutChart>
        </div>
        <div style="padding: 10px;" class="border rounded tw-col-span-12 lg:tw-col-span-6">
          <div class="tw-py-4 tw-flex tw-items-center tw-justify-end tw-space-x-4">
            <VDateRange :oldDate="parseUrlParams(chartIds[5], false)" @endDateEmit="dateForm.endDate = $event"
              @startDateEmit="dateForm.startDate = $event"></VDateRange>
            <v-btn size="small" @click="submitFilterForm(chartIds[5])"><v-icon size="x-large"
                icon="mdi-filter-outline" /></v-btn>
            <v-btn size="small" v-if="parseUrlParams(chartIds[5], false)" @click="clearFilter"><v-icon size="x-large"
                icon="mdi-delete-outline" /></v-btn>
          </div>
          <LineChart name="daily_Average vehicle_capacity"
            :data="usePage().props?.select_list_daily_vehicle_capacity?.data"
            :labels="usePage().props?.select_list_daily_vehicle_capacity?.labels">
          </LineChart>
        </div>
        <div style="padding: 10px;" class="border rounded tw-col-span-12 lg:tw-col-span-6">
          <div class="tw-py-4 tw-flex tw-items-center tw-justify-end tw-space-x-4">
            <VDateRange :oldDate="parseUrlParams(chartIds[6], false)" @endDateEmit="dateForm.endDate = $event"
              @startDateEmit="dateForm.startDate = $event"></VDateRange>
            <v-btn size="small" @click="submitFilterForm(chartIds[6])"><v-icon size="x-large"
                icon="mdi-filter-outline" /></v-btn>
            <v-btn size="small" v-if="parseUrlParams(chartIds[6], false)" @click="clearFilter"><v-icon size="x-large"
                icon="mdi-delete-outline" /></v-btn>
          </div>
          <LineChart name="monthly Average_vehicle_capacity"
            :data="usePage().props?.select_list_monthly_vehicle_capacity?.data"
            :labels="usePage().props?.select_list_monthly_vehicle_capacity?.labels">
          </LineChart>
        </div>
        <div style="padding: 10px;" class="border rounded tw-col-span-12 lg:tw-col-span-6">
          <div class="tw-py-4 tw-flex tw-items-center tw-justify-end tw-space-x-4">
            <VDateRange :oldDate="parseUrlParams(chartIds[7], false)" @endDateEmit="dateForm.endDate = $event"
              @startDateEmit="dateForm.startDate = $event"></VDateRange>
            <v-btn size="small" @click="submitFilterForm(chartIds[7])"><v-icon size="x-large"
                icon="mdi-filter-outline" /></v-btn>
            <v-btn size="small" v-if="parseUrlParams(chartIds[7], false)" @click="clearFilter"><v-icon size="x-large"
                icon="mdi-delete-outline" /></v-btn>
          </div>
          <LineChart name="daily_average_bin_capacity"
            :data="usePage().props?.select_list_daily_average_bin_capacity?.data"
            :labels="usePage().props?.select_list_daily_average_bin_capacity?.labels">
          </LineChart>
        </div>
        <div style="padding: 10px;" class="border rounded tw-col-span-12 lg:tw-col-span-6">
          <div class="tw-py-4 tw-flex tw-items-center tw-justify-end tw-space-x-4">
            <VDateRange :oldDate="parseUrlParams(chartIds[8], false)" @endDateEmit="dateForm.endDate = $event"
              @startDateEmit="dateForm.startDate = $event"></VDateRange>
            <v-btn size="small" @click="submitFilterForm(chartIds[8])"><v-icon size="x-large"
                icon="mdi-filter-outline" /></v-btn>
            <v-btn size="small" v-if="parseUrlParams(chartIds[8], false)" @click="clearFilter"><v-icon size="x-large"
                icon="mdi-delete-outline" /></v-btn>
          </div>
          <LineChart name="monthly_average_bin_capacity"
            :data="usePage().props?.select_list_monthly_average_bin_capacity?.data"
            :labels="usePage().props?.select_list_monthly_average_bin_capacity?.labels">
          </LineChart>
        </div>
        <div style="padding: 10px;" class="border rounded tw-col-span-12 lg:tw-col-span-4">
                    <DoughnutChart name="waste categories target"  type="radialBar"
                     :labels="usePage().props?.select_list_waste_categories_target?.labels"
                    :data="usePage().props?.select_list_waste_categories_target?.data"></DoughnutChart>
        </div>
        <div style="padding: 10px;" class="border rounded tw-col-span-12 lg:tw-col-span-8">
          <div class="tw-py-4 tw-flex tw-items-center tw-justify-end tw-space-x-4">
            <VDateRange :oldDate="parseUrlParams(chartIds[8], false)" @endDateEmit="dateForm.endDate = $event"
              @startDateEmit="dateForm.startDate = $event"></VDateRange>
            <v-btn size="small" @click="submitFilterForm(chartIds[10])"><v-icon size="x-large"
                icon="mdi-filter-outline" /></v-btn>
            <v-btn size="small" v-if="parseUrlParams(chartIds[10], false)" @click="clearFilter"><v-icon size="x-large"
                icon="mdi-delete-outline" /></v-btn>
          </div>
          <LineChart name="daily IOT bin"
            :data="usePage().props?.select_list_daily_iot_bins_payt?.data"
            :labels="usePage().props?.select_list_daily_iot_bins_payt?.labels">
          </LineChart>
        </div>
          <div style="padding: 10px;" class="border rounded tw tw-col-span-12 ">
            <CustomGooglMap :binMarker="createBinMarkerComputed" ref="getPolylineRef"></CustomGooglMap>
          </div>
      </div>
      <datatable :returnObject="true" row-select-only @selectedRow="createPolyline" datatable-key="bins">
        <!---- <template #filters>
          <v-btn @click="toQr" class="ma-2" color="secondary" variant="tonal" icon="mdi-qrcode">
          </v-btn>
          <v-menu v-model="menu" :close-on-content-click="false">
            <template v-slot:activator="{ props }">
              <v-btn class="ma-2" color="primary" variant="tonal" v-bind="props">
                {{ $t('Register') }}
              </v-btn>
            </template>
<v-card width="300px">
  <v-card-text>
    <v-select :label="$t('waste_profile')" multiple item-value="id" item-title="label"
      v-model="bin_query.waste_profiles" :items="getPlatformConfig('waste_profiles')" />
    <v-select :label="$t('bin_char')" multiple v-model="bin_query.bin_char" :items="getPlatformConfig('bin_chars')" />
    <v-select :label="$t('networks')" multiple v-model="bin_query.networks" :items="getPlatformConfig('networks')" />
  </v-card-text>
  <v-card-actions>
    <v-btn color="indigo" @click="filterByTypes">{{ $t('Filter') }}</v-btn>
    <v-btn :disabled="hasQuery" color="indigo" class="my-3" @click="resetFilter">{{ $t('Reset') }}
    </v-btn>
  </v-card-actions>
</v-card>
</v-menu>
</template> -->
        <!--- <template #add-popup="{ close_dialog }">
          <v-card>
            <AddBin @successful="close_dialog"></AddBin>
          </v-card>
        </template>
        <template #import-popup="{ model, close_dialog }">
          <ImportData @update:modelValue="close_dialog"></ImportData>
        </template> -->
      </datatable>
    </template>
  </TenantLayout>
</template>

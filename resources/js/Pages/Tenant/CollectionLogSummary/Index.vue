<script>
import TenantLayout from "@/Layouts/Tenant/TenantLayout.vue";
import { Listbox, ListboxButton, ListboxLabel, ListboxOption, ListboxOptions } from '@headlessui/vue'
import { ref } from "vue";
import { Ziggy } from "@/ziggy";
import { route } from "@/Helpers/api_helper";
import { decodeQueryParamUrl, getPlatformConfig, getQueryParamValue, getTimezoneOffset, parseQueryParamsGrouped, parseUrlParams } from "@/Helpers/saas";
import Datatable from "@/Components/Reusable/Datatable.vue";
import ImportData from "@/Components/Tenant/Routes/ImportData.vue";
import { router } from "@inertiajs/vue3";
import VDateRange from "@/Components/VDateRange.vue";
import EditCollectionLog from "@/Components/Tenant/CollectionLogSummary/EditCollectionLog.vue";
import moment from "moment";
export default {
  name: "Index",
  components: { Datatable, TenantLayout, EditCollectionLog, VDateRange, Listbox, ListboxButton, ListboxLabel, ListboxOption, ListboxOptions },
  created() {
    
  },
  setup() {
    return {
      parseUrlParams,moment
    }
  },
  data() {
    return {
      filter: {
        type: this.paramsFilter('type')||'All',
        startDate: moment().subtract(2, 'months').format('YYYY-MM-DD'),
      endDate: moment().format('YYYY-MM-DD'),
      },
      dataType: ['All','Automatic','Manual'],
      selected: null,
      menu: false,
    }
  },
  methods: {
    // router,
    parseQueryParamsGrouped,
    getTimezoneOffset,
    getPlatformConfig,
    paramsFilter(id = 'page') {
      const url = decodeQueryParamUrl().query;
      if (url && url[id]) {
        return url[id];
      } else {
        return false;
      }
    },
    fetchDate() {
      const S = this.paramsFilter('startDate')
      const E = this.paramsFilter('endDate')
      if (S && E) {
        return {
            'startDate':S,
            'endDate':E
          }
      }else return false
    },
    filterByTypes() {
      router.get(route(Ziggy.routes['collection-log-summaries.index']), { ...this.filter }, { preserveScroll: true })
    },
    resetFilter() {
      this.filter = {
        type: 'All',
        startDate: moment().subtract(2, 'months').format('YYYY-MM-DD'),
      endDate: moment().format('YYYY-MM-DD'),
      }
      router.get(route(Ziggy.routes["collection-log-summaries.index"]), {})
    }
  }
}
</script>

<template>
  <TenantLayout :seo_tile="$t('Collection Logs Summery')">
    <template #content>
      <div class=" tw-rounded-[10px] tw-py-2">
        <div class="d-flex tw-items-center tw-space-x-2 tw-justify-end">
          <VDateRange :oldDate="fetchDate()" @endDateEmit="filter.endDate = $event" @startDateEmit="filter.startDate = $event">
          </VDateRange>
          <div class="tw-w-32">
            <Listbox  as="div" v-model="filter.type">
              <!-- <ListboxLabel class="tw-block tw-text-sm tw-font-medium tw-text-gray-700">Assigned to</ListboxLabel> -->
              <div class="tw-relative">
                <ListboxButton  style="border: 1px solid black;border-radius:10px"
                  class=" tw-relative tw-w-full tw-cursor-default tw-rounded-md tw-bg-white tw-py-[5px] tw-pl-3 tw-pr-3 tw-text-left tw-shadow-sm  focus:tw-outline-none focus:tw-ring-1 focus:tw-ring-0">
                  <span class="tw-block tw-truncate">{{ filter.type }}</span>
                  <!-- <span
                    class="tw-pointer-events-none tw-absolute tw-inset-y-0 tw-right-0 tw-flex tw-items-center tw-pr-2">
                    <ChevronUpDownIcon class="tw-h-5 tw-w-5 tw-text-gray-400" aria-hidden="true" />
                  </span> -->
                </ListboxButton>
                <transition leave-active-class="tw-transition tw-ease-in tw-duration-100"
                  leave-from-class="tw-opacity-100" leave-to-class="tw-opacity-0">
                  <ListboxOptions
                    class="tw-absolute tw-z-10 tw-mt-1 tw-max-h-60 tw-w-full tw-overflow-auto tw-rounded-md tw-bg-white tw-py-1 tw-text-base tw-shadow-lg tw-ring-1 tw-ring-black tw-ring-opacity-5 focus:tw-outline-none ">
                    <ListboxOption as="template" v-for="item in dataType" :key="item" :value="item"
                      v-slot="{ active, selected }">
                      <li
                        :class="[active ? 'tw-text-white tw-bg-[#FF8811]' : 'tw-text-gray-900', 'tw-relative tw-cursor-default tw-select-none tw-py-2 tw-pl-3 tw-pr-9']">
                        <span :class="[selected ? 'tw-font-semibold' : 'tw-font-normal', 'tw-block tw-truncate']">{{item }}</span>
                        <!-- <span v-if="selected"
                          :class="[active ? 'tw-text-white' : 'tw-text-indigo-600', 'tw-absolute tw-inset-y-0 tw-right-0 tw-flex tw-items-center tw-pr-4']">
                          <CheckIcon class="tw-h-5 tw-w-5" aria-hidden="true" />
                        </span> -->
                      </li>
                    </ListboxOption>
                  </ListboxOptions>
                </transition>
              </div>
            </Listbox>
            <!-- <v-select density="compact" :label="$t('type')" v-model="filter.type" :items="['Automatic', 'Manual']" /> -->
          </div>
          <v-btn @click="filterByTypes"><v-icon size="x-large"
                icon="mdi-filter-outline" /></v-btn>
                <v-btn v-if="fetchDate()" @click="resetFilter"><v-icon size="x-large"
                icon="mdi-delete-outline" /></v-btn>
        </div>
      </div>
      <datatable searchDate row-select row-edit datatable-key="route_collection">
        <template #edit-popup="{model}">
          <EditCollectionLog :model-value="model"></EditCollectionLog>
        </template>
      </datatable>
    </template>
  </TenantLayout>
</template>

<style scoped></style>

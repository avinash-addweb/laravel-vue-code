<script>

import TenantLayout from "@/Layouts/Tenant/TenantLayout.vue";
import {ref} from "vue";
import {getDataTable} from "@/Helpers/saas";
import Datatable from "@/Components/Reusable/Datatable.vue";

export default {
  name: "TotalSummeryLogs",
  components: { Datatable, TenantLayout},
  created() {
    
  },
  setup(){

  }

}


</script>

<template>
  <TenantLayout :seo_tile="$t('Total Collection Summery')">

    <template #content>
      <Datatable row-select datatable-key="route_collection">
        </Datatable>
    </template>
  </TenantLayout>
</template>
<style scoped>

</style>

<template>

  <v-responsive>
    <v-row>

      <v-col cols="12">
        <v-card height="100%">

          <v-card-title>{{ $t('Import File') }}</v-card-title>
          <v-card-text>
              <v-file-input  prepend-icon="" 
                          @input="form.csvfile = $event.target.files" :error-messages="form.errors.csvfile"
                    :label="$t('Select Excel File')">
            </v-file-input>
          </v-card-text>

          <v-card-actions>
            <v-btn elevation="2" variant="flat" color="primary" @click="registerEnquiry">
              {{ $t('Import File') }}
            </v-btn>

            
                <v-btn variant="flat" color="primary" @click="downloadFile">
                      {{ $t('Download Sample File') }}
                </v-btn>
           

          </v-card-actions>
        </v-card>
      </v-col>
    </v-row>

  </v-responsive>


</template>

<script>

import {router, useForm, usePage} from "@inertiajs/vue3";
import {Ziggy} from "@/ziggy";
import {image_url, route} from "@/Helpers/api_helper";
import {getPlatformConfig} from "@/Helpers/saas";

export default {
  name: "ImportData",
  props: ['modelValue'],
  emits: ['update:modelValue'],
  computed: {
    downloadUrl() {
      return this.fileUrl;
    },
  },
  created() {


  },
  methods: {
    registerEnquiry() {
      this.form.post(route(Ziggy.routes["log.import"]), {
        onSuccess: () => {
          router.visit(route(
              Ziggy.routes["log.index"]), {
            preserveScroll: true
          })
        }
      })
    },
    downloadFile() {
      const fileUrl = '/route_collections.xlsx';
      const link = document.createElement('a');
      link.href = fileUrl;
      link.download = 'route_collections.xlsx';
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    },
  },
  data() {
    return {
      fileUrl: '/',
      form: useForm({
        csvfile: null
      })
    }
  },
}
</script>

<style scoped>

</style>

<!-- src/components/CsvImport.vue -->

<script>
import {router, useForm, usePage} from "@inertiajs/vue3";
import {Ziggy} from "@/ziggy";
import {image_url, route} from "@/Helpers/api_helper";

export default {
  name: "ImportData",
  props: ['modelValue'],
  emits: ['update:modelValue'],
  computed: {
    Ziggy() {
      return Ziggy
    },
    usePage() {
      return usePage
    }
  },
  created() {
    this.form = useForm(usePage().props.auth.user)
  },
  methods: {
      handleFileChange(event) {
        this.file = event.target.files[0];
      },
      async importCsv() {
        const formData = new FormData();

        formData.append('csvfile', this.form.csvfile);
        //formData.append('csv_file', this.file);
        await this.form.post(route(Ziggy.routes['routes.import']), formData)
      },
  },
  data() {
    return {
        form: useForm({
            csv_file:null
      })
    }
  },
}
</script>
<template>
    <v-card class="">
      <v-card-title>
        {{ $t('Import Data') }}
      </v-card-title>
      <v-card-subtitle>
       {{  $t('Select CSV/Excel file for Import data') }} 
      </v-card-subtitle>

      <v-card-text>
        <v-form>

    <!-- {{  form }} -->
     <v-file-input prepend-icon="" accept="text/csv" label="CSV File" @change="handleFileChange"></v-file-input>
     <v-btn color="primary" @click="importCsv">{{ $t('Import File') }}</v-btn>

</v-form>
    </v-card-text>
    </v-card>
  </template>

<style scoped>

</style>
  
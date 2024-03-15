<template>
  <template v-if="collection?.models?.length">
    <v-data-table v-model="selected" :search="search" :show-select="this.rowSelect || this.rowSelectOnly" :headers="data_headers"
    :items="collection.models" :return-object="returnObject" item-value="id" :items-per-page="pagination.per_page"
    hide-default-footer :sort-by="[{ key: 'id', order: 'asc' }]" class="elevation-1 my-2">
    <template v-slot:top>
        <v-toolbar flat>
          <!-- {{ tw-overflow-x-auto }} -->
          <div class=" tw-justify-between tw-items-center d-flex tw-w-full">
            <div class="tw-ml-5">
              <v-toolbar-title class="!tw-capitalize" v-if="customTitle">{{ $t('My') }} {{ $t(collection?.plural)
                }}</v-toolbar-title>
              <v-toolbar-title v-else-if="tableTitle">{{ tableTitle }}</v-toolbar-title>
              <v-toolbar-title class="!tw-capitalize" v-else>{{ $t('My') }} {{ collection?.plural?.replace(/_/g, ' ')
                }}</v-toolbar-title>
            </div>
            <div class="tw-flex tw-items-center">
              <template v-if="searchDate">
        <!-- <v-text-field class="tw-w-60 mt-5 pr-5" v-model="search" append-inner-icon="mdi-magnify" label="Search" density="compact"></v-text-field> -->
        <div style="border: 1px black solid" class=" tw-rounded-[10px] tw-bg-white tw-px-3 d-flex tw-items-center tw-mr-5">
          <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 24 24"><g fill="none" fill-rule="evenodd"><path d="M24 0v24H0V0zM12.593 23.258l-.011.002l-.071.035l-.02.004l-.014-.004l-.071-.035c-.01-.004-.019-.001-.024.005l-.004.01l-.017.428l.005.02l.01.013l.104.074l.015.004l.012-.004l.104-.074l.012-.016l.004-.017l-.017-.427c-.002-.01-.009-.017-.017-.018m.265-.113l-.013.002l-.185.093l-.01.01l-.003.011l.018.43l.005.012l.008.007l.201.093c.012.004.023 0 .029-.008l.004-.014l-.034-.614c-.003-.012-.01-.02-.02-.022m-.715.002a.023.023 0 0 0-.027.006l-.006.014l-.034.614c0 .012.007.02.017.024l.015-.002l.201-.093l.01-.008l.004-.011l.017-.43l-.003-.012l-.01-.01z"/><path fill="currentColor" d="M5.5 10a4.5 4.5 0 1 1 9 0a4.5 4.5 0 0 1-9 0M10 2.5a7.5 7.5 0 1 0 4.136 13.757l4.803 4.804a1.5 1.5 0 0 0 2.122-2.122l-4.804-4.803A7.5 7.5 0 0 0 10 2.5"/></g></svg>
          <input v-model="search" type="search" class="pl-2  tw-py-[6px] tw-outline-none" placeholder="Search...">
        </div>


              </template>
              <slot name="filters"></slot>
              <v-btn size="small" variant="flat" v-if="rowSelect" :disabled="selected.length === 0" color="primary" dark
                @click="exportSelected">
                <!-- {{ exportTitle }} -->
                <v-icon color="white" size="25" icon="mdi-export"></v-icon>
                
              </v-btn>
              <v-btn size="small" variant="outlined" v-if="rowImport" color="primary" dark @click="importData">
                <!-- {{ importTitle }} -->
                <v-icon color="white" size="25" icon="mdi-import"></v-icon>

              </v-btn>
              <v-dialog v-model="show_qr" max-width="95%">
                <!-- <v-card>
                  <v-card-text class="mx-auto">
                    <div> -->
                      <bin-qr :bin="qr_code_data"></bin-qr>

                    <!-- </div>
                  </v-card-text>

                </v-card> -->
                <!-- <template v-slot:activator="{ props }">
                  <v-btn color="primary" dark class="ma-2" variant="flat" v-bind="props">
                    {{ $t('Import') }} {{ this.collection.singular }}
                  </v-btn>
                </template> -->
              </v-dialog>

              <v-dialog v-show="import_dialog" v-if="hasImportSlot" v-model="import_dialog" max-width="95%">
                <template v-slot:activator="{ props }">
                  <v-btn color="primary" dark class="ma-2" size="small" variant="flat" v-bind="props">
                    {{ $t('Import') }} {{ this.collection.singular }}
                  </v-btn>
                </template>
                <slot name="import-popup" v-bind="{ close_dialog: closeImport }"></slot>
              </v-dialog>
              <v-dialog v-show="add_dialog" v-if="hasAddSlot" v-model="add_dialog" max-width="95%">
                <template v-slot:activator="{ props }">
                  <v-btn size="small" color="primary" dark class="ma-2" variant="flat" v-bind="props">
                    <template v-if="addButtonTitle">
                      <!-- {{ $t(addButtonTitle) }} -->
                      <v-icon color="white" size="20" icon="mdi-send"></v-icon>

                    </template>
                    <template v-else>
                      {{ $t('New') }}
                      {{ this.collection.singular }}
                    </template>
                  </v-btn>
                </template>
                <slot name="add-popup" v-bind="{ close_dialog: closeAdd }"></slot>
              </v-dialog>
              <v-dialog v-show="edit_dialog" v-model="edit_dialog" max-width="95%">
                <slot name="edit-popup" v-bind="{ model: clickedItem.model, close_dialog: closeEdit, form: formData }">
                </slot>
              </v-dialog>
              <v-dialog v-show="send_dialog" v-model="send_dialog" max-width="95%">
                <slot name="send-popup" v-bind="{ model: clickedItem.model, close_dialog: closeSend }"></slot>
              </v-dialog>
              <v-dialog v-show="reply_dialog" v-model="reply_dialog" max-width="95%">
                <slot name="reply-popup" v-bind="{ model: clickedItem.model, close_dialog: closeReply }"></slot>
              </v-dialog>
              <v-dialog v-show="status_dialog" v-model="status_dialog" max-width="95%">
                <slot name="status-popup" v-bind="{ model: clickedItem.model, close_dialog: closeStatus }"></slot>
              </v-dialog>
              <v-dialog v-show="delete_dialog" v-model="delete_dialog" max-width="95%">
                <slot name="delete-popup" v-bind="{ model: clickedItem.model, close_dialog: closeDelete }">
                  <v-card>
                    <v-card-title>{{ $t('Are you sure you want to delete?') }}</v-card-title>
                    <v-card-text>
                      <strong>{{ clickedItem.model.name }}</strong> {{ $t('with id') }} <strong>{{ clickedItem.model.id
                        }}</strong>
                    </v-card-text>
                    <v-card-text>
                      <v-expansion-panels>
                        <v-expansion-panel :title="$t('View Details')">
                          <v-expansion-panel-text>
                            <pre>
                {{ clickedItem }}
              </pre>
                          </v-expansion-panel-text>
                        </v-expansion-panel>
                      </v-expansion-panels>
                    </v-card-text>
                    <v-card-actions>
                      <v-btn class="text-decoration" @click="submitDelete" variant="flat" color="primary">
                        {{ $t('Yes Delete') }} {{ clickedItem.model.name }}
                      </v-btn>
                    </v-card-actions>
                  </v-card>
                </slot>
              </v-dialog>
            </div>
          </div>
        </v-toolbar>
      </template>
      <template v-if="pagination.total > 1" v-slot:bottom>
        <div class="text-center pt-2">
          <v-pagination v-model="pagination.current_page" :length="pagination.last_page" :total-visible="5"
            @update:modelValue="pagination.goto" @next="pagination.next" @prev="pagination.previous"
            @first="pagination.first" @last="pagination.last" showFirstLastPage></v-pagination>
        </div>
      </template>
      <template v-slot:item.action="{ item }">
        <span>
          <v-tooltip v-if="item.raw.status" :text="item.raw.reason||item.raw.status" location="top">
            <template v-slot:activator="{ props }">
              <v-btn variant="flat" :icon="item.raw.status=='Pending'?'mdi-help':item.raw.status=='Reject'?'mdi-close':'mdi-check'" :color="item.raw.status=='Pending'?'yellow':item.raw.status=='Reject'?'red':'green'" size="x-small" class="ma-2" v-bind="props"></v-btn>
            </template>
          </v-tooltip>
          <!-- <v-tooltip v-else :text="$t('Approved')" location="top">
            <template v-slot:activator="{ props }">
              <v-btn variant="flat" icon="mdi-check" size="x-small" class="ma-2" color="green" v-bind="props"></v-btn>
            </template>
          </v-tooltip> -->
          <v-tooltip v-if="showQr" :text="$t('show_qr')" location="top">
            <template v-slot:activator="{ props }">
              <v-btn variant="flat" icon="mdi-qrcode" size="x-small" class="ma-2" v-bind="props"
                @click="openShowQrModel(item.raw)">
              </v-btn>
            </template>
          </v-tooltip>
          <v-tooltip :text="$t('View Record')" location="top">
            <template v-slot:activator="{ props }">
              <v-btn v-if="rowView" variant="flat" icon="mdi-eye" size="x-small" class="ma-2" v-bind="props"
                @click="pagination.openModel(item.raw)">
              </v-btn>
            </template>
          </v-tooltip>
          <v-tooltip :text="$t('View Details')" location="top">
            <template v-slot:activator="{ props }">
              <v-btn v-if="rowDetail" variant="flat" icon="mdi-animation" size="x-small" class="ma-2" v-bind="props"
                @click="pagination.openPage(item.raw)">
              </v-btn>
            </template>
          </v-tooltip>
          <v-tooltip :text="$t('Bin Diposite History')" location="top">
            <template v-slot:activator="{ props }">
              <v-btn v-if="rowCitizenDetail" variant="flat" icon="mdi-animation" size="x-small" class="ma-2"
                v-bind="props" @click="pagination.openPage(item.raw)">
              </v-btn>
            </template>
          </v-tooltip>
          <v-tooltip :text="$t('Edit Record')" location="top">
            <template v-slot:activator="{ props }">
              <v-btn v-if="rowEdit || hasEditSlot" variant="flat" icon="mdi-pencil" size="x-small" class="ma-2"
                v-bind="props" @click="editItem(item.raw)">
              </v-btn>
            </template>
          </v-tooltip>
          <v-tooltip :text="$t('Send Request')" location="top">
            <template v-slot:activator="{ props }">
              <v-btn v-if="rowSend || hasSendSlot" variant="flat" icon="mdi-bell" size="x-small" class="ma-2"
                v-bind="props" @click="sendItem(item.raw)">
              </v-btn>
            </template>
          </v-tooltip>
          <v-tooltip :text="$t('Delete Record')" location="top">
            <template v-slot:activator="{ props }">
              <v-btn v-if="rowDelete" variant="flat" icon="mdi-delete" size="x-small" class="ma-2" v-bind="props"
                @click="deleteItem(item.raw)">
              </v-btn>
            </template>
          </v-tooltip>
          <v-tooltip :text="$t('Reply')" location="top">
            <template v-slot:activator="{ props }">
              <v-btn v-if="rowReply || hasReplySlot" variant="flat" icon="mdi-reply" size="x-small" class="ma-2"
                v-bind="props" @click="replyItem(item.raw)"></v-btn>
            </template>
          </v-tooltip>
          <v-tooltip :text="$t('Update Status')" location="top">
            <template v-slot:activator="{ props }">
              <v-btn v-if="rowStatus || hasStatusSlot" variant="flat" icon="mdi-check" size="x-small" class="ma-2"
                v-bind="props" @click="statusItem(item.raw)">
              </v-btn>
            </template>
          </v-tooltip>
        </span>
      </template>
      <template v-slot:item.created_at="{ item }">
        {{ convertToBrowserLocalTime(item.raw.created_at) }}
      </template>
      <template v-slot:item.deleted_at="{ item }">
        {{ convertToBrowserLocalTime(item.raw.deleted_at) }}
      </template>
      <template v-slot:item.updated_at="{ item }">
        {{ convertToBrowserLocalTime(item.raw.updated_at) }}
      </template>
    </v-data-table>
  </template>
  <template v-else>
    <v-toolbar flat class="my-2">
      <v-toolbar-title v-if="tableTitle">{{ tableTitle }}</v-toolbar-title>
      <v-toolbar-title class="!tw-capitalize" v-else>My {{ collection?.plural?.replace(/_/g, ' ') }}</v-toolbar-title>
      <slot name="filters"></slot>
      <v-dialog v-if="hasAddSlot" v-model="add_dialog" max-width="95%">
        <template v-slot:activator="{ props }">
          <v-btn color="primary" dark class="mb-2" v-bind="props">
            {{ $t('New') }} {{ this.collection.singular }}
          </v-btn>
        </template>
        <slot name="add-popup"></slot>
      </v-dialog>
      <v-dialog v-if="hasImportSlot" v-model="import_dialog" max-width="95%">
        <template v-slot:activator="{ props }">
          <v-btn color="primary" dark class="mb-2" v-bind="props">
            {{ $t('Import') }} {{ this.collection.singular }}
          </v-btn>
        </template>
        <slot name="import-popup"></slot>
      </v-dialog>
      <!--Send Replt to Tenant By Admin-->
      <v-dialog v-model="reply_dialog" max-width="95%">
        <slot name="reply-popup" v-bind="{ model: clickedItem.model, dialog: reply_dialog }"></slot>
      </v-dialog>
      <!--Send Replt to Tenant By Admin-->
      <v-dialog v-model="status_dialog" max-width="95%">
        <slot name="status-popup" v-bind="{ model: clickedItem.model, dialog: status_dialog }"></slot>
      </v-dialog>
      <v-dialog v-model="edit_dialog" max-width="95%">
        <slot name="edit-popup" v-bind="{ model: clickedItem.model, dialog: edit_dialog }"></slot>
      </v-dialog>
      <!--Send Push Notification-->
      <v-dialog v-model="send_dialog" max-width="55%">
        <slot name="send-popup" v-bind="{ model: clickedItem.model, dialog: send_dialog }"></slot>
      </v-dialog>
      <v-dialog v-model="delete_dialog" max-width="95%">
        <slot name="delete-popup" v-bind="{ model: clickedItem.model, close_dialog: closeDelete }">
          <v-card>
            <v-card-title>{{ $t('Are you sure you want to delete?') }}</v-card-title>
            <v-card-text>
              <strong>{{ clickedItem.model.name }}</strong> with id <strong>{{ clickedItem.model.id }}</strong>
            </v-card-text>
            <v-card-actions>
              <v-btn class="text-decoration-underline" @click="submitDelete" color="red">
                Yes Delete {{ clickedItem.model.name }}
              </v-btn>
            </v-card-actions>
          </v-card>
        </slot>
      </v-dialog>
    </v-toolbar>
    <v-alert class="my-2" color="info">
      {{ $t('No') }} <strong>{{ collection?.plural?.toUpperCase().replace(/_/g, ' ') }}</strong> {{ $t('yet') }}
    </v-alert>
  </template>
</template>
<!--  @todo Fix pagination -->
<script>
import BinQr from "@/Components/Tenant/Bins/BINQR.vue";

import { VDataTable } from "vuetify/labs/components";
import { router, useForm } from "@inertiajs/vue3";
import { route } from "@/Helpers/api_helper";
import { convertToBrowserLocalTime, LaravelDatatable } from "@/Helpers/saas";
export default {
  name: "Datatable",
  components: {
    VDataTable,BinQr
  },
  props: {
    searchDate: { type: Boolean, default: false },
    showAction: { type: Boolean, default: false },
    showQr: { type: Boolean, default: false },
    addButtonTitle: { type: [Boolean, String], default: false },
    returnObject: { type: Boolean, default: false },
    rowSend: { type: Boolean, default: false },
    rowImport: { type: Boolean, default: false },
    customTitle: { type: Boolean, default: false },
    headers: { type: Array, default: [] },
    rows: { type: Array, default: [] },
    tableTitle: { default: false },
    datatableKey: { type: String, default: "" },
    hasEditPage: { type: Boolean, default: false },
    hasReplyPage: { type: Boolean, default: false },
    hasStatusPage: { type: Boolean, default: false },
    hasAddDialog: { type: Boolean, default: false },
    hasImportDialog: { type: Boolean, default: false },
    hasDetailPage: { type: Boolean, default: false },
    hasCitizenDetailPage: { type: Boolean, default: false },
    rowView: { type: Boolean, default: false },
    rowSelect: { type: Boolean, default: false },
    rowSelectOnly: { type: Boolean, default: false },
    rowEdit: { type: Boolean, default: false },
    rowReply: { type: Boolean, default: false },
    rowStatus: { type: Boolean, default: false },
    rowDelete: { type: Boolean, default: false },
    rowDetail: { type: Boolean, default: false },
    rowCitizenDetail: { type: Boolean, default: false },
  },
  watch: {
    'edit_dialog'(val) {
      this.$forceUpdate()
    },
    'reply_dialog'(val) {
      this.$forceUpdate()
    },
    'status_dialog'(val) {
      this.$forceUpdate()
    },
    'send_dialog'(val) {
      this.$forceUpdate()
    },
    'delete_dialog'(val) {
      this.$forceUpdate()
    }
  },
  computed: {
    hasAddSlot() {
      return !!this.$slots["add-popup"];
    },
    hasImportSlot() {
      return !!this.$slots["import-popup"];
    },
    hasEditSlot() {
      return !!this.$slots["edit-popup"];
    },
    hasSendSlot() {
      return !!this.$slots["send-popup"];
    },
    hasReplySlot() {
      return !!this.$slots["reply-popup"];
    },
    hasStatusSlot() {
      return !!this.$slots["status-popup"];
    },
    hasDelete() {
      return !!this.$slots["delete-popup"];
    },
    collection() {
      return new LaravelDatatable(this.datatableKey)
    },
    data_headers() {
      let headers = []
      headers = [...headers, ...this.collection.headers]
      if (this.rowView || this.rowDelete || this.rowEdit || this.rowCitizenDetail || this.rowReply || this.rowSend || this.rowStatus || this.showQr||this.rowDetail||this.showAction) {
        headers.push({
          title: this.$t('Action'),
          align: "start",
          sortable: false,
          key: "action",
          width: '200px'
        })
      }
      return headers
    },
    exportAll() {
      return this.selected.length === this.collection.models.length || false
    },
    exportTitle() {
      if (this.exportAll) {
        return this.$t('Export All')
      }
      return this.$t('Export All')
      return "Export Selected (SOON)"
    },
    importTitle() {
      return this.$t('Import Data')
    },
    pagination() {
      return {
        current_page: this.collection.pagination.current_page,
        per_page: this.collection.pagination.per_page,
        path: this.collection.pagination.path,
        total: this.collection.pagination.total,
        last_page: this.collection.pagination.last_page,
        next: () => {
          router.get(this.collection.pagination.next_page_url)
        },
        previous: () => {
          router.get(this.collection.pagination.prev_page_url)
        },
        goto: (num) => {
          this.collection.pagination.goto(num)
        },
        openModel: (item) => {
          this.collection.pagination.openModel(item)
        },
        openPage: (item) => {
          this.collection.pagination.openPage(item)
        }
      }
    },
  },
  methods: {
    convertToBrowserLocalTime,
    useForm,
    route,
    openShowQrModel(value) {
      this.show_qr = !this.show_qr
      this.qr_code_data = value 
      console.log("🚀 -> 🌏 ~ file: Datatable.vue:371 ~ openShowQrModel ~ value:", value)

    },
    editItem(item) {
      this.clickedItem.model = Object.assign({}, item)
      this.formData = useForm(this.clickedItem.model)
      this.edit_dialog = true
    },
    replyItem(item) {
      this.clickedItem.model = Object.assign({}, item)
      this.reply_dialog = true
    },
    statusItem(item) {
      this.clickedItem.model = Object.assign({}, item)
      this.status_dialog = true
    },
    sendItem(item) {
      this.clickedItem.model = Object.assign({}, item)
      this.send_dialog = true
    },
    deleteItem(item) {
      this.clickedItem.model = Object.assign({}, item)
      this.delete_dialog = true
    },
    submitDelete() {
      if (this.collection.plural == 'citizen_queries') {
        this.collection.plural = 'citizen-queries';
      }
      if (this.collection.plural == 'contact_enquiries') {
        this.collection.plural = 'contact-enquiries';
      }
      if (this.collection.plural == 'reward_points') {
        this.collection.plural = 'rewardpoints';
      }
      if (this.collection.plural == 'master_datas') {
        this.collection.plural = 'masterdatas';
      }
      if (this.datatableKey == 'cmspages') {
        this.collection.plural = 'cmspages'
      }
      if (this.datatableKey == 'adminWasteType') {
        this.collection.plural = 'adminWasteType'
      }
      useForm({ ...this.clickedItem.model }).delete(
        route(this.collection.plural + ".destroy",
          {
            [this.collection.singular]: this.clickedItem.model.id
          }
        ),
        {
          onSuccess: () => {
            return Promise.all([
              this.delete_dialog = false,
              push.success('item deleted successfully'),
            ])
          }
        });
    },
    closeAdd() {
      this.add_dialog = false
      this.$nextTick(() => {
        this.clickedItem.model = { model: null }
        this.editedIndex = -1
        this.$forceUpdate()
      })
    },
    closeImport() {
      this.import_dialog = false
      this.$nextTick(() => {
        this.clickedItem.model = { model: null }
        this.editedIndex = -1
        this.$forceUpdate()
      })
    },
    closeEdit(model) {
      this.collection.updateModelState(model)
      this.edit_dialog = false
      this.add_dialog = false
      this.$nextTick(() => {
        this.clickedItem.model = { model: null }
        this.editedIndex = -1
        this.$forceUpdate()
      })
    },
    closeReply(model) {
      this.collection.updateModelState(model)
      this.reply_dialog = false
      this.$nextTick(() => {
        this.clickedItem.model = { model: null }
        this.editedIndex = -1
        this.$forceUpdate()
      })
    },
    closeStatus(model) {
      this.collection.updateModelState(model)
      this.status_dialog = false
      this.$nextTick(() => {
        this.clickedItem.model = { model: null }
        this.editedIndex = -1
        this.$forceUpdate()
      })
    },
    closeSend(model) {
      this.collection.updateModelState(model)
      this.send_dialog = false
      this.$nextTick(() => {
        this.clickedItem.model = { model: null }
        this.editedIndex = -1
        this.$forceUpdate()
      })
    },
    closeDelete() {
      this.delete_dialog = false
      this.$nextTick(() => {
        this.clickedItem.model = { model: null }
        this.editedIndex = -1
      })
    },
    save() {
      this.closeEdit()
      if (this.editedIndex > -1) {
        Object.assign(this.list.data[this.editedIndex], this.editedItem)
      } else {
        this.list.data.push(this.editedItem)
      }
    },
    exportSelected() {
      useForm({ ids: this.selected }).post(window.location.origin + window.location.pathname + "/export", {
        onSuccess: () => {
          return Promise.all([
            push.success('data import successfully'),
          ])
        },
        onFinish: (parameters) => {
        }
      });
    }
  },
  data() {
    return {
      search:'',
      qr_code_data:'',
      add_dialog: false,
      import_dialog: false,
      edit_dialog: false,
      reply_dialog: false,
      status_dialog: false,
      send_dialog: false,
      show_qr: false,
      delete_dialog: false,
      deleteForm: useForm({}),
      clickedItem: { model: null },
      formData: null,
      editedItem: { test: "ok" },
      itemsPerPage: 10,
      editedIndex: -1,
      selected: []
    }
  },
  watch: {
    selected: function () {
      this.$emit('selectedRow', this.selected)
    }
  },
  mounted() {
    // if (this.collection.plural == 'pages') {
    //   this.collection.plural = 'informative_page';
    // }
    if (this.collection.singular == 'binrequest') {
      this.collection.singular = 'Bin request';
    }
  }
}
</script>

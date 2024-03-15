<template>
  <v-container fluid>
    <v-row>
      <v-col>
        <v-card-title>{{ $t('Edit Route Stops') }}</v-card-title>
      </v-col>

    </v-row>
    <v-row>
      <v-col cols="3">
        <v-card>


          <v-card-text  style="overflow-y: scroll; height:70vh;">
            <draggable
                v-model="form.bins"
                group="people"
                @start="dragStart"
                @end="dragEnd"
                item-key="id"
               >
              <template #item="{index,element}">

                <v-row no-gutters>
                  <v-col >
                    <v-container class="pa-0" >
                      <v-row  no-gutters >
                        <v-col  class="text-center" align-content="center"  >
                          <v-btn
                              variant="flat"
                              class="text-primary text-disabled "
                              size="x-small"
                              icon="mdi-plus-circle"
                              @click="expand(index)"
                          >
                          </v-btn>


                          <v-expand-transition class="my-1 mb-3 " >
                            <v-card variant="elevated"  elevation="9"   v-show="expand_index===index">
                              <v-card-text class="pb-0">
                                <v-select clearable label="Select Bin" density="compact" v-model="new_bin" item-title="name"
                                           item-value="id" return-object :items="current_route.allowed_bins">

                                </v-select>
                              </v-card-text>
                              <v-card-actions class="pt-0">

                                <v-btn :disabled="!new_bin" block variant="elevated"  color="primary" @click="addAfter(index)">
                                  
                                  {{  $t('Add')  }}
                                </v-btn>
                              </v-card-actions>

                            </v-card>
                          </v-expand-transition>
                        </v-col>


                      </v-row>
                    </v-container>
                    <v-container class="py-1 "   >
                      <v-card variant="outlined">
                        <v-card-title>
                          <v-row>
                            <v-col >
                              <v-btn
                                  variant="flat"
                                  class=" "
                                  size="small"
                                  icon="mdi-close-circle"
                                  @click="remove(index)"
                              >
                              </v-btn>
                            </v-col>
                            <v-col >
                              {{ index }}
                            </v-col>
                            <v-col >
                              {{ element.name }}
                            </v-col>
                            <v-col >
                              <p class="text-disabled text-sm-subtitle-1">{{ element.id }}</p>
                            </v-col>
                          </v-row>
                        </v-card-title>
                      </v-card>

                    </v-container>
                  </v-col>
                </v-row>
              </template>
            </draggable>
            <v-container class="py-0">
              <v-row  no-gutters >
                <v-col  class="text-center" align-content="center"  >
                  <v-btn
                      variant="flat"
                      class="text-disabled text-primary"
                      size="x-small"
                      icon="mdi-plus-circle"
                      @click="expand(current_route.bins.length+1)"
                  >
                  </v-btn>

                  <v-expand-transition class="my-1 "  >
                    <v-card v-show="expand_index===current_route.bins.length+1">
                      <v-card-actions>
                        <v-select v-model="new_bin" item-title="name"
                                  item-value="id" return-object :items="current_route.allowed_bins">

                        </v-select>
                        <v-btn :disabled="!new_bin" color="primary" @click="addAfter(current_route.bins.length+1)">
                          {{  $t('Add')  }}
                        </v-btn>
                      </v-card-actions>

                    </v-card>
                  </v-expand-transition>
                </v-col>


              </v-row>
            </v-container>

          </v-card-text>
          <v-card-actions>
            <v-btn variant="elevated" color="primary" :disabled="!this.form.isDirty" @click="updateRoute">{{  $t('save')  }}</v-btn>
            <v-btn variant="outlined" color="secondary" :disabled="!this.form.isDirty" @click="resetForm">{{  $t('reset')  }}</v-btn>
          </v-card-actions>
        </v-card>


      </v-col>

      <v-col style=" height:70vh;" >
        <GMAP  :line="this.form.bins" ></GMAP>
      </v-col>

    </v-row>
  </v-container>

</template>

<script>

import {router, useForm, usePage} from "@inertiajs/vue3";
import {ref} from "vue";
import {route} from "@/Helpers/api_helper";
import draggable from 'vuedraggable'
import {Ziggy} from "@/ziggy";
import GMAP from "@/Components/Reusable/GMAP.vue";
import {getModel} from "@/Helpers/saas";


export default {
  name: "EditRoute",
  components: {GMAP, draggable},
  props: {
    modelValue: Object,
  },
  emits: ['updated'],
  computed: {

  },

  methods: {
    usePage,
    expand(index){
      if(this.expand_index===index){
        this.expand_index=-1
      }else{
        this.expand_index=index;
      }

    },
    dragStart() {
      this.expand_index=-1
      this.new_bin=null;
    },
    dragEnd() {
      this.expand_index=-1
    },
    remove(index) {
      this.form.bins.splice(index,1)
    },
    addAfter(index) {
      this.form.bins.splice(index,0,this.new_bin)
      this.expand_index=-1
      this.new_bin=null;
    },
    updateRoute() {
      this.form.put(
          route(Ziggy.routes["route.setStops"], {route: this.current_route.id}), {
            preserveScroll: true,
            onSuccess: () => {
          push.success('route update successfully')
              this.onFormSuccess();
              router.reload()
            }
          })
    },
    resetForm(){
      this.form.reset();
    },
    onFormSuccess() {
      this.$emit('updated', this.edit_bin_form)
    }

  },


  data() {
    const route = getModel("route")
    let form= useForm({bins: route.bins})
    return {
      expand_index:-1,
      current_route: route,
      new_bin: null,
      myArray: [],
      drag: false,
      test: [],
      bins: null,
      pins: [],
      form
    }
  }
}
</script>

<style scoped>

</style>

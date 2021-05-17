<template>
  <v-container fluid>
    <ValidationObserver>
      <v-form>
        <v-card flat>
          <v-card-title class="pa-0 pb-3">
            <v-toolbar dense color="tertiary" class="font-weight-regular">
              <v-toolbar-title>GENERACIÓN AUTOMÁTICA DE COBROS</v-toolbar-title>
            </v-toolbar>
          </v-card-title>
          <template>
            <v-container fluid class="px-2 pt-0">
              <v-row justify="center" class="py-0">
                <v-col cols="12" class="py-0">
                  <v-tabs dark active-class="secondary" v-model="tab">
                    <v-tab v-for="item in actions" :key="item.nameTab">{{item.nameTab}}</v-tab>
                  </v-tabs>
                  <v-tabs-items v-model="tab">
                    <v-tab-item v-for="item in actions" :key="item.nameTab">
                      <v-card flat tile>
                        <v-card-text class="pa-2">
                          <v-row align="center" no-gutters>
                            <v-col cols="12" class="pa-2">
                              <v-progress-linear></v-progress-linear>
                              <br />
                              <v-layout row wrap>
                                <v-col cols="12" md="12" class="py-0" v-if="tab == 0">
                                   <CurrentLoans/>
                                </v-col>
                                <v-col cols="12" md="12" class="py-0" v-else>
                                <LiquidatedLoans/>
                                </v-col>
                              </v-layout>
                            </v-col>
                            <v-col cols="6" class="pa-2"></v-col>
                          </v-row>
                        </v-card-text>
                      </v-card>
                    </v-tab-item>
                  </v-tabs-items>
                </v-col>
              </v-row>
            </v-container>
          </template>
        </v-card>
      </v-form>
    </ValidationObserver>
  </v-container>
</template>


<script>
import CurrentLoans from '@/components/payment/CurrentLoans'
import LiquidatedLoans from '@/components/payment/LiquidatedLoans'

export default {
  name: "payment-ImportExport",
    components: {
    LiquidatedLoans,
    CurrentLoans
  },
  data: () => ({
    tab: null,

    actions: [
      { nameTab: "Préstamos vigentes", value: "0" },
      { nameTab: "Prestamos cancelados", value: "1" },
    ],



  }),
  methods: {

  },
};
</script>

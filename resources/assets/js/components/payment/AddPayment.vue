<template>
  <v-container >
      <v-card>
        <v-card-text class="ma-0 pb-0">
          <v-container fluid class="ma-0 pb-0">
            <v-row justify="center" class="ma-0 pb-0">
              <v-col cols="12" >
                <v-layout row wrap>
                  <v-flex xs12 class="px-2">
                    <fieldset class="pa-3">
                      <center>
                        <v-toolbar-title>DATOS DE LA AMORTIZACION</v-toolbar-title>
                      </center>
                      <v-progress-linear></v-progress-linear>
                      <v-container >
                        <template>
                          <v-row>
                            <v-col cols="12" md="6">
                              <label><b>Interes Penal Generado:</b></label>
                              {{payment_detail.estimated_days.penal_generated | moneyString}}
                              <br>
                            </v-col>
                            <v-col cols="12" md="6">
                              <label><b>Interes Corriente de Días:</b></label>
                              {{payment_detail.estimated_days.current+' Total'}}
                              {{payment_detail.estimated_days.current_generated | moneyString}}
                              <br>
                            </v-col>
                          </v-row>
                          <v-divider></v-divider>
                          <v-row>
                            <v-col cols="12" md="6">
                              <p><b>Interes Penal Pendiente: </b>{{payment_detail.penal_remaining | moneyString}}</p>
                              <p><b>Interes Corriente Pendiente: </b>{{payment_detail.interest_remaining | moneyString}}</p>
                              <p><b>Pago a Interés Penal: </b>{{payment_detail.penal_payment | moneyString}}</p>
                              <p><b>Pago a Interés Corriente: </b>{{payment_detail.interest_payment | moneyString}}</p>
                              <p><b>Pago a Capital: </b>{{payment_detail.capital_payment | moneyString}}</p>
                            </v-col>
                            <v-col cols="12" md="6">
                              <p><b>Fecha de Calculo: </b>{{payment_detail.estimated_date | date}}</p>
                              <p><b>Fecha de Transaccion: </b>{{ payment_detail.now_date | date}}</p>
                              <p><b>Saldo Anterior: </b>{{payment_detail.balance | moneyString }}</p>
                              <p><b style="color:teal">Saldo Actual: {{payment_detail.next_balance | moneyString }}</b></p>
                            </v-col>
                          </v-row>
                          <v-row>
                            <v-col cols="12" class="ma-0 py-2">
                              <label><b>Total Pagado:</b></label>
                              {{payment_detail.estimated_quota | moneyString}}
                            </v-col>
                              <v-progress-linear></v-progress-linear>
                            <v-col cols="12" md="6" class="ma-0 py-2">
                              <label><b>Intereses Corrientes Pendientes:</b></label>
                              {{payment_detail.interest_accumulated | moneyString}}
                            </v-col>
                            <v-col cols="12" md="6" class="ma-0 py-2">
                              <label><b>Intereses Penales Pendientes:</b></label>
                              {{payment_detail.penal_accumulated | moneyString}}
                            </v-col>
                          </v-row>
                          <v-row v-if="false">
                            <v-col cols="12" class="ma-0 py-2">
                              <label><b>Pago a Interés Penal:</b></label>
                              {{payment_detail.penal_payment | moneyString}}
                            </v-col>
                            <v-col cols="12" class="ma-0 py-2">
                              <label><b>Pago a Interés Corriente:</b></label>
                              {{payment_detail.interest_payment | moneyString}}
                            </v-col>
                            <v-col cols="6" class="ma-0 py-2">
                              <label><b>Pago a Capital:</b></label>
                              {{payment_detail.capital_payment | moneyString}}
                            </v-col>
                              <v-col cols="6" class="ma-0 py-2">
                              <label><b>Fecha de Calculo:</b></label>
                              {{payment_detail.estimated_date | date}}
                            </v-col>
                                <v-col cols="6" class="ma-0 py-2">
                              <label><b>Interes Corriente de Días:</b></label>
                              {{payment_detail.estimated_days.current+' Total'}}
                              {{payment_detail.estimated_days.current_generated | moneyString}}
                            </v-col>
                              <v-col cols="6" class="ma-0 py-2">
                              <label><b>Fecha de Transaccion:</b></label>
                                {{ payment_detail.now_date | date}}
                            </v-col>
                            <v-col cols="6" class="ma-0 py-2">
                              <label><b>Interes Penal Generado:</b></label>
                              {{payment_detail.estimated_days.penal_generated | moneyString}}
                            </v-col>
                              <v-col cols="6" class="ma-0 py-2">
                              <label><b>Saldo Anterior:</b></label>
                              {{payment_detail.balance | moneyString }}
                            </v-col>
                            <v-col cols="6" class="ma-0 py-2">
                              <label><b>Interes Corriente Pendiente:</b></label>
                              {{payment_detail.interest_remaining | moneyString}}
                            </v-col>
                            <v-col cols="6" class="ma-0 py-2">
                              <label><b style="color:teal">Saldo Actual:</b></label>
                              <b style="color:teal">{{payment_detail.next_balance | moneyString }}</b>
                            </v-col>
                            <v-col cols="12" class="ma-0 py-2">
                              <label><b>Interes Penal Pendiente:</b></label>
                              {{payment_detail.penal_remaining | moneyString}}
                            </v-col>
                              <v-col cols="12" class="ma-0 py-2">
                              <label><b>Total Pagado:</b></label>
                              {{payment_detail.estimated_quota | moneyString}}
                            </v-col>
                              <v-progress-linear></v-progress-linear>
                            <v-col cols="6" class="ma-0 py-2">
                              <label><b>Intereses Corrientes Pendientes:</b></label>
                              {{payment_detail.interest_accumulated | moneyString}}
                            </v-col>
                            <v-col cols="6" class="ma-0 py-2">
                              <label><b>Interes Penales Pendientes:</b></label>
                              {{payment_detail.penal_accumulated | moneyString}}
                            </v-col>
                          </v-row>
                        </template>
                      </v-container>
                    </fieldset>
                  </v-flex>
                </v-layout>
            </v-col>
          </v-row>
        </v-container>
      </v-card-text>
    </v-card>
  </v-container>
</template>
<script>
export default {
  name: "add-payment",
  props: {
    payment_detail: {
      type: Object,
      required: true
    }
  },
}
</script>
<template>
  <v-container fluid>
    <ValidationObserver ref="observer">
      <v-form>
        <!--v-card-->
          <v-row justify="center">
            <v-col cols="12">
              <v-container class="py-0" v-show="datos.procedure_type_id!=12">
                <v-row>
                  <slot name="title"></slot>
                  <br />
                  <v-col cols="12" md="3">
                    <v-layout row wrap>
                      <v-flex xs12 class="px-2">
                        <fieldset class="pa-3">
                      <ValidationProvider v-slot="{ errors }" name="plazo" :rules="'numeric|min_value:'+datos.minimum_term+'|max_value:'+datos.maximum_term" mode="aggressive">
                      <v-text-field
                        :error-messages="errors"
                        label="Plazo en Meses"
                        v-model="calculos.plazo"
                        v-on:keyup.enter="Calculator()"
                      ></v-text-field>
                      </ValidationProvider>
                      <ValidationProvider v-slot="{ errors }" name="monto solicitado" :rules="'numeric|min_value:'+datos.minimun_amoun+'|max_value:'+datos.maximun_amoun" mode="aggressive">
                      <v-text-field
                        :error-messages="errors"
                        label="Monto Solicitado"
                        v-model ="calculos.montos"
                        v-on:keyup.enter="Calculator()"
                      ></v-text-field>
                      </ValidationProvider>
                        </fieldset>
                      </v-flex>
                    </v-layout>
                  </v-col>
                  <v-col cols="12" md="4">
                    <v-card-text class="py-0">
                      <v-layout row wrap>
                        <v-flex xs12 class="px-2">
                          <fieldset class="pa-3">
                            <p>PROMEDIO LIQUIDO PAGABLE:{{ calculos.payable_liquid_calculated}}</p>
                            <p>TOTAL BONOS: {{ calculos.bonus_calculated }}</p>
                            <p>LIQUIDO PARA CALIFICACION: {{ calculos.liquid_qualification_calculated}}</p>
                          </fieldset>
                        </v-flex>
                      </v-layout>
                    </v-card-text>
                  </v-col>
                  <v-col cols="12" md="4">
                    <v-card-text class="py-0">
                      <v-layout row wrap>
                        <v-flex xs12 class="px-2">
                          <fieldset class="pa-3">
                            <p>CALCULO DE CUOTA: {{ calculos.quota_calculated }}</p>
                            <p>INDICE DE ENDEUDAMIENTO: {{calculos.indebtedness_calculated }}</p>
                            <p class="success--text font-weight-black">MONTO MAXIMO SUGERIDO : {{calculos.amount_maximum_suggested}}</p>
                          </fieldset>
                        </v-flex>
                      </v-layout>
                    </v-card-text>
                  </v-col>
                  <v-col cols="12" md="1" class="ma-0 pa-0"></v-col>
                </v-row>
              </v-container>
              <BallotsResultHipotecary
              v-show="datos.procedure_type_id==12"
              :datos.sync="datos"
              :calculos.sync="calculos"/>
            </v-col>
          </v-row>
        <!--/v-card-->
      </v-form>
    </ValidationObserver>
  </v-container>
</template>
<script>

import BallotsResultHipotecary from '@/components/loan/BallotsResultHipotecary'
export default {
  name: "loan-requirement",
   data: () => ({
    ver:false
  }),
  props: {
    procedure_type: {
      type: Number,
      required: true
    },
    datos: {
      type: Object,
      required: true
    },
    bonos: {
      type: Array,
      required: true
    },
    payable_liquid: {
      type: Array,
      required: true
    },
    modalidad: {
      type: Object,
      required: true
    },
     prueba: {
      type: Array,
      required: true
    },
    calculos: {
      type: Object,
      required: true
    }
  },
    components: {
    BallotsResultHipotecary
  },
 computed: {
    ver1() {
      if(this.datos.procedure_type_id==12)
      {
        return true
      }else
      {
        return false
      }
    }
  },
  methods: {
    async Calculator() {
      try {
        if (this.modalidad.quantity_ballots > 1) {
          let res = await axios.post(`calculator`, {
            procedure_modality_id: this.modalidad.id,
            months_term: this.calculos.plazo,
            amount_requested: this.calculos.montos,
            affiliate_id: this.$route.query.affiliate_id,
            contributions: [
              {
                payable_liquid: this.payable_liquid[0],
                seniority_bonus: this.bonos[2],
                border_bonus: this.bonos[0],
                public_security_bonus: this.bonos[3],
                east_bonus: this.bonos[1]
              },
              {
                payable_liquid: this.payable_liquid[1],
                seniority_bonus: 0,
                border_bonus: 0,
                public_security_bonus: 0,
                east_bonus: 0
              },
              {
                payable_liquid: this.payable_liquid[2],
                seniority_bonus: 0,
                border_bonus: 0,
                public_security_bonus: 0,
                east_bonus: 0
              }
            ]
          })
          this.calculo = res.data

          this.calculos.payable_liquid_calculated = this.calculo.payable_liquid_calculated
          this.calculos.bonus_calculated = this.calculo.bonus_calculated
          this.calculos.liquid_qualification_calculated = this.calculo.liquid_qualification_calculated
          this.calculos.quota_calculated = this.calculo.quota_calculated
          this.calculos.indebtedness_calculated = this.calculo.indebtedness_calculated
          this.calculos.amount_maximum_suggested = this.calculo.amount_maximum_suggested
          this.calculos.plazo = this.calculos.plazo

            if (this.calculos.montos>this.calculo.amount_maximum_suggested) {
            this.calculos.montos = this.calculo.amount_maximum_suggested
          } else {
            this.calculos.montos = this.calculos.montos
          }
        } else {
          let res = await axios.post(`calculator`, {
            procedure_modality_id: this.modalidad.id,
            months_term: this.calculos.plazo,
            amount_requested: this.calculos.montos,
            affiliate_id: this.$route.query.affiliate_id,
            contributions: [
              {
                payable_liquid: this.payable_liquid[0],
                seniority_bonus: this.bonos[2],
                border_bonus: this.bonos[0],
                public_security_bonus: this.bonos[3],
                east_bonus: this.bonos[1]
              }
            ]
          })
          this.calculo = res.data

          this.calculos.payable_liquid_calculated = this.calculo.payable_liquid_calculated
          this.calculos.bonus_calculated = this.calculo.bonus_calculated
          this.calculos.liquid_qualification_calculated = this.calculo.liquid_qualification_calculated
          this.calculos.quota_calculated = this.calculo.quota_calculated
          this.calculos.indebtedness_calculated = this.calculo.indebtedness_calculated
          this.calculos.amount_maximum_suggested = this.calculo.amount_maximum_suggested
          this.calculos.plazo = this.calculos.plazo

            if (this.calculos.montos>this.calculo.amount_maximum_suggested) {
            this.calculos.montos = this.calculo.amount_maximum_suggested
          } else {
            this.calculos.montos = this.calculos.montos
          }  
       }
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    }
  }
}
</script>
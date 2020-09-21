<template>
  <v-container fluid class="py-0">
    <ValidationObserver ref="observer">
      <v-form>
        <!--v-card-->
          <v-row justify="center">
            <v-col cols="12">
              <v-container class="py-0">
                <v-row>
                  <slot name="title"></slot>
                  <br />
                  <v-col cols="12" md="3"  class="py-0">
                    <v-layout row wrap>
                      <v-flex xs12 class="px-1">
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
                        v-model ="monto_hipotecario"
                        v-on:keyup.enter="calculadora()"
                      ></v-text-field>
                      </ValidationProvider>
                        </fieldset>
                      </v-flex>
                    </v-layout>
                  </v-col>
                  <v-col cols="12" md="9" class="py-0">
                    <v-card-text class="py-0">
                      <v-layout row wrap>
                        <v-flex xs12 class="px-1">
                          <fieldset class="pa-2">
                            <p class="success--text font-weight-black py-0 mb-0">NOMBRE DEL TITULAR : STEPHANIE LUNA QUEVEDO {{calculo123}}</p>
                            <p class="py-0 mb-0">PROMEDIO LIQUIDO PAGABLE:{{ calculos.payable_liquid_calculated }}</p>
                            <p class="py-0 mb-0">TOTAL BONOS: {{ calculos.bonus_calculated+ "  " }}LIQUIDO PARA CALIFICACION: {{ calculos.liquid_qualification_calculated}}</p>
                            <p class="success--text font-weight-black py-0 mb-0">NOMBRE DEL TITULAR : STEPHANIE LUNA QUEVEDO</p>
                            <p class="py-0 mb-0">PROMEDIO LIQUIDO PAGABLE:{{ calculos.payable_liquid_calculated }}</p>
                            <p class="py-0 mb-0">TOTAL BONOS: {{ calculos.bonus_calculated+ "  " }}LIQUIDO PARA CALIFICACION: {{ calculos.liquid_qualification_calculated}}</p>
                          </fieldset>
                        </v-flex>
                      </v-layout>
                    </v-card-text>
                  </v-col>
                  <v-col cols="12" md="12" class="pl-1">
                    <v-card-text class="py-0">
                      <v-layout row wrap>
                        <v-flex xs12 class="px-0">
                          <fieldset class="pa-3">
                            <p class="py-0 mb-0">CALCULO DE CUOTA TOTAL: {{ calculos.quota_calculated }}</p>
                            <p class="success--text font-weight-black py-0 mb-0">NOMBRE DEL TITULAR : STEPHANIE LUNA QUEVEDO</p>
                              <p class="py-0 mb-0">INDICE DE ENDEUDAMIENTO: {{calculos.indebtedness_calculated }}</p>
                            <p class="py-0 mb-0">PORCENTAJE DE PAGO : {{calculos.amount_maximum_suggested}}</p>
                          </fieldset>
                        </v-flex>
                      </v-layout>
                    </v-card-text>
                  </v-col>
                 </v-row>
              </v-container>
            </v-col>
          </v-row>
        <!--/v-card-->
      </v-form>
    </ValidationObserver>
  </v-container>
</template>
<script>
export default {
  name: "ballots-result-hipotecary",
  data: () => ({
    datos: {},
    bonos: {},
    payable_liquid: {},
    modalidad: {},
    calculos: {},
    editar:true,
    monto:null,
    plazo:null,
    interval:[],
    loanTypeSelected:null,
    visible:false,
    num_type:9,
    monto_hipotecario:null
  
  }),
  props: {
   datos: {
      type: Object,
      required: true
    },/*
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
    },*/
    calculos: {
      type: Object,
      required: true
    }
  },
  methods: {
    async calculadora() {
      try {
          let res = await axios.post(`liquid_calificated`, {
            procedure_modality_id: 41,
            liquid_calification: [
              {
                affiliate_id: 123456,
                contributions: [
                {
                    payable_liquid: 2000,
                    seniority_bonus: 0,
                    border_bonus: 0,
                    public_security_bonus: 300,
                    east_bonus: 0
                },
                {
                    payable_liquid: 3000,
                    seniority_bonus: 0,
                    border_bonus: 0,
                    public_security_bonus: 300,
                    east_bonus: 0
                },
                {
                    payable_liquid: 3500,
                    seniority_bonus: 0,
                    border_bonus: 0,
                    public_security_bonus: 0,
                    east_bonus: 0
                }
              ]
            },
               {
            affiliate_id: 1,
            parent_loan_id: 6,
            contributions: [
                {
                    payable_liquid: 2000,
                    seniority_bonus: 0,
                    border_bonus: 0,
                    public_security_bonus: 300,
                    east_bonus: 0
                }
            ]
        }
            ]
          })
          this.calculo123 = res.data
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
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
<template>
  <v-container fluid>
    <ValidationObserver ref="observer">
      <v-form>
        <v-row justify="center">
          <v-col cols="2" class="py-2" v-show="show_parent_sismu || (($route.params.hash == 'remake' && data_loan_parent_aux.parent_reason != null))">
              <v-text-field
                dense
                :outlined="enable_sismu"
                :readonly="!enable_sismu"
                label="Código de Préstamo Padre"
                v-model="data_loan_parent_aux.code"
              ></v-text-field>
          </v-col>
          <v-col cols="3" class="py-2" v-show="show_parent_sismu || (($route.params.hash == 'remake' && data_loan_parent_aux.parent_reason != null))">
            <v-text-field
              dense
              v-model="data_loan_parent_aux.disbursement_date"
              label="Fecha Desembolso"
              hint="Día/Mes/Año"
              class="pa-0 ma-0"
              type="date"
              :clearable="enable_sismu"
              :outlined="enable_sismu"
              :readonly="!enable_sismu"
            ></v-text-field>
          </v-col>
          <v-col cols="2" class="py-2" v-show="show_parent_sismu || (($route.params.hash == 'remake' && data_loan_parent_aux.parent_reason != null))">
              <v-text-field
                class="py-0"
                dense
                :outlined="enable_sismu"
                :readonly="!enable_sismu"
                label="Monto"
                v-model="data_loan_parent_aux.amount_approved"
              ></v-text-field>
         </v-col>
          <v-col cols="1" class="py-2" v-show="show_parent_sismu || (($route.params.hash == 'remake' && data_loan_parent_aux.parent_reason != null))">
              <v-text-field
                class="py-0"
                dense
                :outlined="enable_sismu"
                :readonly="!enable_sismu"
                label="Plazo"
                v-model="data_loan_parent_aux.loan_term"
              ></v-text-field>
          </v-col>
          <v-col cols="2" class="py-2" v-show="show_parent_sismu || (($route.params.hash == 'remake' && data_loan_parent_aux.parent_reason != null))">
              <v-text-field
                class="py-0"
                dense
                :outlined="enable_sismu"
                :readonly="!enable_sismu"
                label="Saldo"
                v-model="data_loan_parent_aux.balance"
              ></v-text-field>
          </v-col>
          <v-col cols="2" class="py-2" v-show="show_parent_sismu || (($route.params.hash == 'remake' && data_loan_parent_aux.parent_reason != null))">
             <v-text-field
                class="py-0"
                dense
                :readonly=true
                label="Cuota"
                v-model="data_loan_parent_aux.estimated_quota"
            ></v-text-field>
          </v-col>
          <v-col cols="12" class="pt-0">
            <v-container class="py-0">
              <v-row>
                <slot name="title"></slot>
                <v-col cols="12" md="12" v-show="!data_sismu.livelihood_amount">
                  <v-layout row wrap>
                    <v-flex xs12 class="px-2">
                      <fieldset class="pa-3">
                        <h1 class="success--text text-center">LA SUBSISTENCIA DEL AFILIADO ES MENOR A LO PERMITIDO</h1>
                      </fieldset>
                    </v-flex>
                  </v-layout>
                </v-col>
                <v-col cols="12" md="3" v-show="data_sismu.livelihood_amount">
                  <v-layout row wrap>
                    <v-flex xs12 class="px-2">
                      <fieldset class="pa-3">
                         <ValidationProvider v-slot="{ errors }" name="plazo" :rules="'numeric|min_value:'+loan_detail.minimum_term+'|max_value:'+loan_detail.maximum_term" mode="aggressive">
                          <v-text-field
                            :error-messages="errors"
                            label="Plazo"
                            v-model="calculator_result.months_term"
                            v-on:keyup.enter="simulator()"
                          ></v-text-field>
                        </ValidationProvider>
                       <ValidationProvider v-slot="{ errors }" name="monto solicitado" :rules="reprogramming? '':'numeric|min_value:'+loan_detail.minimun_amoun+'|max_value:'+loan_detail.maximun_amoun" mode="aggressive">
                          <v-text-field
                            class="py-0"
                            :error-messages="errors"
                            label="Monto Solicitado"
                            v-model ="calculator_result.amount_requested"
                            v-on:keyup.enter="simulator()"
                            :readonly="reprogramming"
                          ></v-text-field>
                        </ValidationProvider>
                        <center>
                          <v-btn
                            class="py-0 text-right"
                            color="info"
                            rounded
                            x-small
                            @click="simulator()">Calcular
                          </v-btn>
                        </center>
                      </fieldset>
                    </v-flex>
                  </v-layout>
                </v-col>
                <v-col cols="12" md="4" v-show="data_sismu.livelihood_amount"  class="pb-0">
                  <v-card-text class="py-0">
                    <v-layout row wrap>
                      <v-flex xs12 class="px-2">
                        <fieldset class="py-0">
                          <ul style="list-style: none" >
                            <li v-for="(liquid,i) in liquid_calificated" :key="i" >
                              <p>{{reprogramming ? 'PROMEDIO LIQUIDO PAGABLE' : 'INGRESO'}}: {{liquid.payable_liquid_calculated | money}}</p>
                              <p class="error--text "> TOTAL BONOS: (-) {{liquid.bonus_calculated | money}} </p>
                              <p class="error--text ">  MONTO DE SUBSISTENCIA: (-) {{global_parameters.livelihood_amount | money}} </p>
                              <p class="success--text " v-show="type_sismu"> (+) CUOTA DE REFINANCIAMIENTO SISMU: {{data_sismu.quota_sismu | money}} </p>
                              <p style="color:teal" class="font-weight-black" v-if="!reprogramming" >LIQUIDO PARA CALIFICACION: {{ liquid.liquid_qualification_calculated | money}}</p>
                              <p v-show="liquid_calificated[0].guarantees.length==0">GARANTIAS: {{liquid_calificated[0].guarantees.length}}</p>
                              <p style="color:teal" class="font-weight-black" v-if="reprogramming" > MONTO REPROGRAMADO: {{data_loan_parent_aux.balance_for_reprogramming | money}} </p>
                              </li>
                            </ul>
                        </fieldset>
                      </v-flex>
                    </v-layout>
                  </v-card-text>
                </v-col>
                <v-col cols="12" md="4" v-show="data_sismu.livelihood_amount"  class="pb-0">
                  <v-card-text class="py-0">
                    <v-layout row wrap>
                      <v-flex xs12 class="px-2">
                        <fieldset class="pa-3">
                          <p v-show="calculator_result.maximum_suggested_valid" class="primary--text font-weight-black">MONTO MÁXIMO (LE = 70.00%) : {{calculator_result.amount_maximum | money}}</p>
                          <template v-if="!reprogramming">
                          <p v-show="!calculator_result.maximum_suggested_valid" class="error--text font-weight-black">MONTO MAXIMO (LE = 70.00%) :  {{calculator_result.amount_maximum_suggested | money}}</p>
                          <p v-show="calculator_result.maximum_suggested_valid" class="success--text font-weight-black">MONTO MÁXIMO SUGERIDO (LE: {{calculator_result.debt_index_suggested |percentage }}%): {{calculator_result.amount_maximum_suggested | money}}</p>
                          </template>
                          <p>CALCULO DE CUOTA: {{ calculator_result.quota_calculated_estimated_total | money}}</p>
                          <p>LÍMITE DE ENDEUDAMIENTO CALCULADO: {{(calculator_result.indebtedness_calculated_total) |percentage }}%</p>
                          <p>MONTO SOLICITADO: {{calculator_result.amount_requested | money}}</p>
                          <p class="caption" >* LÍmite de Endeudamiento (LE)</p>
                        </fieldset>
                      </v-flex>
                    </v-layout>
                  </v-card-text>
                </v-col>
                <v-col cols="12" md="3" v-show="data_sismu.livelihood_amount" v-if="liquid_calificated[0].guarantees.length > 0" ></v-col>
                  <v-col cols="12" md="8" v-show="data_sismu.livelihood_amount"  class="pa-0"  v-if="liquid_calificated[0].guarantees.length > 0">
                  <v-card-text>
                    <v-layout row wrap>
                      <v-flex xs12 class="px-2">
                        <fieldset>
                              <p class="mb-1"><b class="red--text caption mb-0" >DESCUENTO DE CUOTAS POR GARANTIAS: {{liquid_calificated[0].guarantees.length}} </b></p>
                               <ul style="list-style: none" class="py-0 ps-0 ">
                                <li v-for="(guarantees,j) in liquid_calificated[0].guarantees" :key="j" >
                                  <v-row>
                                    <v-col  cols="12" md="3" class='py-0 mb-0'>
                                      <p class="caption" >CODIGO:{{ guarantees.code}}</p>
                                    </v-col>
                                    <v-col  cols="12" md="3" class='py-0 mb-0'>
                                      <p class="caption" >CUOTA: {{ guarantees.quota | money}}</p>
                                    </v-col>
                                    <v-col  cols="12" md="3" class='py-0 mb-0'>
                                      <p class="caption" >SISTEMA: {{ guarantees.origin}}</p>
                                    </v-col>
                                    <v-col  cols="12" md="3" class='py-0 mb-0'>
                                      <p class="caption" >ESTADO: {{ guarantees.state}}</p>
                                    </v-col>
                                  </v-row>
                                  </li>
                                </ul>
                        </fieldset>
                      </v-flex>
                    </v-layout>
                  </v-card-text>
                </v-col>
              </v-row>
            </v-container>
          </v-col>
        </v-row>
      </v-form>
    </ValidationObserver>
  </v-container>
</template>
<script>
export default {
  name: "loan-requirement",
  data: () => ({
    calculator_result_aux:{},
    global_parameters:{}
  }),
  props: {
    data_sismu: {
      type: Object,
      required: true
    },
    data_loan_parent_aux: {
      type: Object,
      required: true
    },
    loan_detail: {
      type: Object,
      required: true
    },
    calculator_result: {
      type: Object,
      required: true
    },
    modalidad: {
      type: Object,
      required: true
    },
    liquid_calificated: {
      type: Array,
      required: true
    },
    lenders: {
      type: Array,
      required: true
    }
  },
  mounted(){
    this.getGlobalParameters()
  },
    computed: {

    type_sismu() {
      if(this.$route.query.type_sismu)
      {
        return true
      }
    },
    //Valor para habilitar la cabecera de datos del sismu
    show_parent_sismu() {
      if(this.refinancing || this.reprogramming)
      {
        return true
      }
      else{
        return false
      }
    },
    //Valor para habilitar la cabecera de datos del sismu
    enable_sismu() {
      if(this.$route.query.type_sismu || (this.remake && this.data_loan_parent_aux.parent_loan_id == null))//Si es sismu o rehacer sismu
      {
        return true
      }
      else{
        return false
      }
    },

    refinancing() {
      return this.$route.params.hash == 'refinancing'
    },
    reprogrammingx() {
      return this.$route.params.hash == 'reprogramming' || this.data_loan_parent_aux.parent_reason == 'REPROGRAMACIÓN'
    },
    reprogramming() {
      return this.$route.params.hash == 'reprogramming' || this.data_loan_parent_aux.parent_reason == 'REPROGRAMACIÓN'
    },
    remake() {
      return this.$route.params.hash == 'remake'
    }
  },
  methods: {
    //Metodo para sacar los parametros globales
    async getGlobalParameters(){
      try {
        let res = await axios.get(`loan_global_parameter`)
        this.global_parameters = res.data.data[0]
      } catch (e) {
        console.log(e)
      }
    },
    //Metodo del simulador para el monto maximo de prestamo
    async simulator() {
      try {
        if(this.loan_detail.maximun_amoun>=this.calculator_result.amount_requested)
        {
          let res = await axios.post(`simulator`, {
            procedure_modality_id:this.modalidad.id,
            amount_requested: !this.reprogramming ? this.calculator_result.amount_requested : this.data_loan_parent_aux.balance_for_reprogramming,
            months_term:  this.calculator_result.months_term,
            guarantor: false,
            liquid_qualification_calculated_lender: 0,
            liquid_calculated:this.liquid_calificated
          })

          this.calculator_result_aux = res.data
          this.calculator_result.quota_calculated_estimated_total = this.calculator_result_aux.quota_calculated_estimated_total
          this.calculator_result.indebtedness_calculated_total = this.calculator_result_aux.indebtedness_calculated_total
          this.calculator_result.amount_maximum_suggested = this.calculator_result_aux.amount_maximum_suggested
          this.calculator_result.amount_maximum = this.calculator_result_aux.amount_maximum

          if( this.calculator_result.amount_requested<=this.calculator_result_aux.amount_maximum && !this.reprogramming){
            this.calculator_result.amount_requested=this.calculator_result_aux.amount_requested
            this.loan_detail.amount_requested=this.calculator_result_aux.amount_requested
          }
          else if(this.reprogramming){
            this.calculator_result.amount_requested=this.data_loan_parent_aux.balance_for_reprogramming
            this.loan_detail.amount_requested=this.data_loan_parent_aux.balance_for_reprogramming  
          }else{
            this.calculator_result.amount_requested=this.calculator_result_aux.amount_maximum_suggested
            this.loan_detail.amount_requested=this.calculator_result_aux.amount_maximum_suggested
          }
          this.loan_detail.months_term=this.calculator_result_aux.months_term
          this.loan_detail.indebtedness_calculated=this.calculator_result_aux.indebtedness_calculated_total

          this.loan_detail.maximum_suggested_valid=this.calculator_result_aux.maximum_suggested_valid
          this.loan_detail.amount_maximum_suggested=this.calculator_result_aux.amount_maximum_suggested
          this.loan_detail.amount_maximum = this.calculator_result_aux.amount_maximum
          this.loan_detail.is_valid=this.calculator_result_aux.is_valid
          this.loan_detail.quota_calculated_total_lender=this.calculator_result_aux.quota_calculated_estimated_total

          this.lenders[0].indebtedness_calculated=this.calculator_result_aux.indebtedness_calculated_total
          this.lenders[0].quota_treat=this.calculator_result_aux.quota_calculated_estimated_total
        }
        else{
          this.toastr.error("El Monto Solicitado no corresponde a la Modalidad")
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
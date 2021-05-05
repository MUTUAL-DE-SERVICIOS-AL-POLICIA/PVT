<template>
  <v-container fluid>
    <ValidationObserver ref="observer">
      <v-form>
        <!--v-card-->
        <v-row justify="center">
          <v-col cols="3" class="py-2" v-show="editar || (($route.params.hash == 'remake' && data_loan_parent_aux.parent_reason != null))">
              <v-text-field
                class="py-0"
                dense
                :outlined="habilitar"
                :readonly="!habilitar"
                label="Codigo de Prestamo Padre"
                v-model="data_loan_parent_aux.code"
              ></v-text-field>
          </v-col>
          <v-col cols="3" class="py-2" v-show="editar || (($route.params.hash == 'remake' && data_loan_parent_aux.parent_reason != null))">
            <ValidationProvider v-slot="{ errors }" name="monto" :rules="'required|min_value:'+loan_detail.minimun_amoun+'|max_value:'+loan_detail.maximun_amoun"  mode="aggressive">
              <v-text-field
                :error-messages="errors"
                class="py-0"
                dense
                :outlined="habilitar"
                :readonly="!habilitar"
                label="Monto"
                v-model="data_loan_parent_aux.amount_approved"
              ></v-text-field>
            </ValidationProvider>
          </v-col>
          <v-col cols="2" class="py-2" v-show="editar || (($route.params.hash == 'remake' && data_loan_parent_aux.parent_reason != null))">
            <ValidationProvider v-slot="{ errors }" name="plazo" :rules="'required|numeric|min_value:'+loan_detail.minimum_term+'|max_value:'+loan_detail.maximum_term" mode="aggressive">
              <v-text-field
                :error-messages="errors"
                class="py-0"
                dense
                :outlined="habilitar"
                :readonly="!habilitar"
                label="Plazo"
                v-model="data_loan_parent_aux.loan_term"
              ></v-text-field>
            </ValidationProvider>
          </v-col>
          <v-col cols="2" class="py-2" v-show="editar || (($route.params.hash == 'remake' && data_loan_parent_aux.parent_reason != null))">
            <ValidationProvider v-slot="{ errors }" name="saldo" :rules="'required|min_value:0 |max_value:'+calculator_result.amount_requested" mode="aggressive">
              <v-text-field
                :error-messages="errors"
                class="py-0"
                dense
                :outlined="habilitar"
                :readonly="!habilitar"
                label="Saldo"
                v-model="data_loan_parent_aux.balance"
              ></v-text-field>
            </ValidationProvider>
          </v-col>
          <v-col cols="2" class="py-2" v-show="editar || (($route.params.hash == 'remake' && data_loan_parent_aux.parent_reason != null))">
             <v-text-field
                class="py-0"
                dense
                :outlined="habilitar"
                :readonly="!habilitar"
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
                            label="Plazo en Meses"
                            v-model="calculator_result.months_term"
                            v-on:keyup.enter="simuladores()"
                          ></v-text-field>
                        </ValidationProvider>
                       <ValidationProvider v-slot="{ errors }" name="monto solicitado" :rules="'numeric|min_value:'+loan_detail.minimun_amoun+'|max_value:'+loan_detail.maximun_amoun" mode="aggressive">
                          <v-text-field
                            class="py-0"
                            :error-messages="errors"
                            label="Monto Solicitado"
                            v-model ="calculator_result.amount_requested"
                            v-on:keyup.enter="simuladores()"
                          ></v-text-field>
                        </ValidationProvider>
                        <center>
                          <v-btn
                            class="py-0 text-right"
                            color="info"
                            rounded
                            x-small
                            @click="simuladores()">Calcular
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
                          <ul style="list-style: none" class="py-3 ps-4 ">
                            <li v-for="(liquid,i) in liquid_calificated" :key="i" >
                              <p>PROMEDIO LIQUIDO PAGABLE:{{ liquid.payable_liquid_calculated}}</p>
                              <p>TOTAL BONOS: {{ liquid.bonus_calculated }}</p>
                              <p>LIQUIDO PARA CALIFICACION: {{ liquid.liquid_qualification_calculated}}</p>
                              <p v-show="type_sismu">CUOTA DE REFINANCIAMIENTO SISMU: {{ data_sismu.quota_sismu}}</p>
                              <p v-show="liquid_calificated[0].guarantees.length==0">DESCUENTO DE CUOTAS POR GARANTIAS: {{liquid_calificated[0].guarantees.length}}</p>
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
                          <p>CALCULO DE CUOTA: {{ calculator_result.quota_calculated_estimated_total }}</p>
                          <p>INDICE DE ENDEUDAMIENTO: {{calculator_result.indebtedness_calculated_total }}</p>
                          <p>MONTO SOLICITADO: {{calculator_result.amount_requested }}</p>
                          <p v-show="calculator_result.maximum_suggested_valid" class="success--text font-weight-black">MONTO MAXIMO SUGERIDO : {{calculator_result.amount_maximum_suggested}}</p>
                          <p v-show="!calculator_result.maximum_suggested_valid" class="error--text font-weight-black">MONTO MAXIMO SUGERIDO : {{calculator_result.amount_maximum_suggested}}</p>
                        </fieldset>
                      </v-flex>
                    </v-layout>
                  </v-card-text>
                </v-col>
                <v-col cols="12" md="3" class="ma-0 py-0" v-show="data_sismu.livelihood_amount" v-if="liquid_calificated[0].guarantees.length > 0" ></v-col>
                  <v-col cols="12" md="8" v-show="data_sismu.livelihood_amount"  class="pa-0"  v-if="liquid_calificated[0].guarantees.length > 0">
                  <v-card-text class="py-0">
                    <v-layout row wrap>
                      <v-flex xs12 class="px-2">
                        <fieldset class="py-0">
                              <p class="mb-1"><b class="red--text caption mb-0" >DESCUENTO DE CUOTAS POR GARANTIAS: {{liquid_calificated[0].guarantees.length}} </b></p>
                               <ul style="list-style: none" class="py-0 ps-0 ">
                                <li v-for="(guarantees,j) in liquid_calificated[0].guarantees" :key="j" >
                                  <v-row>
                                    <v-col  cols="12" md="3" class='py-0 mb-0'>
                                      <p class="caption" >CODIGO:{{ guarantees.code}}</p>
                                    </v-col>
                                    <v-col  cols="12" md="3" class='py-0 mb-0'>
                                      <p class="caption" >CUOTA: {{ guarantees.quota }}</p>
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
        <!--/v-card-->
      </v-form>
    </ValidationObserver>
  </v-container>
</template>
<script>
export default {
  name: "loan-requirement",
  data: () => ({
    ver:false,
    calculator_result_aux:{}
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
    modalidad_id: {
      type: Number,
      required: true,
      default: 0
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
    computed: {

    type_sismu() {
      if(this.$route.query.type_sismu)
      {
        return true
      }
    },
    /*editar() {
      if(this.$route.query.type_sismu)
      {
        return true
      }
      else{
         if(this.$route.query.loan_id && this.$route.params.hash != 'remake')
          {
            return true
          }else{
            return false
          }
      }
      return this.$route.params.hash == 'new'
    },*/
    editar() {
      if(this.refinancing || this.reprogramming)
      {
        return true
      }
      /*if(this.$route.params.hash == 'remake' && this.data_loan_parent_aux.parent_reason != null)
      {
        return true
      }*/else{
        return false
      }

    },
    habilitar() {
      if(this.$route.query.type_sismu)
      {
        return true
      }
      else{
        return false
      }
      return this.$route.params.hash == 'new'
    },

    refinancing() {
      return this.$route.params.hash == 'refinancing'
    },
    reprogramming() {
      return this.$route.params.hash == 'reprogramming'
    }
  },
  methods: {
    async simuladores() {
      try {
        if(this.loan_detail.maximun_amoun>=this.calculator_result.amount_requested)
        {
          let res = await axios.post(`simulator`, {
            procedure_modality_id:this.modalidad.id,
            amount_requested: this.calculator_result.amount_requested,
            months_term:  this.calculator_result.months_term,
            guarantor: false,
            liquid_qualification_calculated_lender: 0,
            liquid_calculated:this.liquid_calificated
        })
        this.calculator_result_aux = res.data
        this.calculator_result.quota_calculated_estimated_total= this.calculator_result_aux.quota_calculated_estimated_total
        this.calculator_result.indebtedness_calculated_total= this.calculator_result_aux.indebtedness_calculated_total
        this.calculator_result.amount_maximum_suggested=this.calculator_result_aux.amount_maximum_suggested

        if( this.calculator_result.amount_requested<this.calculator_result_aux.amount_maximum_suggested){
          this.calculator_result.amount_requested=this.calculator_result_aux.amount_requested
          this.loan_detail.amount_requested=this.calculator_result_aux.amount_requested
        }else{
          this.calculator_result.amount_requested=this.calculator_result_aux.amount_maximum_suggested
          this.loan_detail.amount_requested=this.calculator_result_aux.amount_maximum_suggested
        }
        this.loan_detail.months_term=this.calculator_result_aux.months_term
        this.loan_detail.indebtedness_calculated=this.calculator_result_aux.indebtedness_calculated_total

        this.loan_detail.maximum_suggested_valid=this.calculator_result_aux.maximum_suggested_valid
        this.loan_detail.amount_maximum_suggested=this.calculator_result_aux.amount_maximum_suggested
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
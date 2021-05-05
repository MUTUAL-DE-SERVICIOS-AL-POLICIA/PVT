<template>
  <v-flex xs12 class="px-3">
    <v-form>
      <v-row justify="center">
        <v-col cols="12">
          <v-card>
            <ValidationObserver ref="observer" >
              <v-container fluid >
                <v-row justify="center" class="py-0 my-0">
                  <v-col cols="12" class="py-0 -my-0" >
                    <v-container class="py-0 my-0">
                      <v-row>
                        <v-col cols="12" :md="window_size" class="py-0 my-0 text-center">
                          MODALIDAD DEL PRÉSTAMO <!--{{loanTypeSelected.id}}-->
                        </v-col>
                        <v-col cols="12" :md="window_size" class="py-0 my-0 text-center">
                          INTERVALO DE LOS MONTOS 
                        </v-col>
                        <v-col cols="12" :md="window_size" class="py-0 my-0 text-center">
                          INTERVALO DEL PLAZO EN MESES
                        </v-col>
                        <v-col cols="12" :md="window_size" class="py-0 my-0 text-center" v-if="see_field">
                          VALOR NETO REALIZADO (VNR)
                        </v-col>
                        <!--{{contribution}}-->
                        <v-col cols="12" :md="window_size" class="py-0 my-0">
                          <v-select
                            dense
                            v-model="loanTypeSelected.id"
                            @change="Onchange()"
                            :items="modalities"
                            item-text="second_name"
                            item-value="id"
                            required
                            :disabled="edit_refi_repro"
                          ></v-select>
                        </v-col>
                        <v-col cols="12" :md="window_size" class="py-0 my-0 text-center">
                          {{monto}}
                        </v-col>
                        <v-col cols="12" :md="window_size" class="py-0 my-0 text-center" >
                          {{plazo}}
                        </v-col>
                        <v-col cols="12" :md="window_size" class="py-0 my-0" v-if="see_field">
                          <ValidationProvider v-slot="{ errors }" name="VNR" :rules="'required|min_value:'+modalidad.minimun_amoun"  mode="aggressive">
                          <v-text-field
                            :error-messages="errors"
                            dense
                            v-model="loan_detail.net_realizable_value"
                            label="VNR"
                            outlined
                            editable
                          ></v-text-field>
                          </ValidationProvider>
                        </v-col>
                      </v-row>
                    </v-container>
                  </v-col>
                </v-row>
              </v-container>
              <v-container cols="12" md="12" class="py-0 my-0">
                <v-row class="py-0 my-0">
                  <v-col cols="12" md="2" class="py-0 my-0">
                    <v-text-field
                      dense
                      v-model="number_diff_month"
                      label="Número de meses"
                      color="info"
                      append-icon="mdi-plus-box"
                      prepend-icon="mdi-minus-box"
                      @click:append="appendIconCallback"
                      @click:prepend="prependIconCallback"
                      readonly
                    ></v-text-field>
                  </v-col>
                </v-row>
                <!--boleta 1--->
                <v-row v-for="(contrib,i) in contribution" :key="i" class="py-0 my-0">
                  <v-col cols="12" md="7" class="py-0 my-0">
                    <v-row>
                      <v-col cols="12" md="12" class="py-0 my-0 pb-1 uppercase"> BOLETAS DE PAGO <b>{{contribution[i].month}}</b></v-col>
                      <v-col cols="12" md="3" class="py-0 my-0" v-if="lender_contribution.state_affiliate != 'Comisión'">
                        <ValidationProvider
                          v-slot="{ errors }"
                          name="Boleta de pago"
                          :rules="'required|min_value:' + global_parameters.livelihood_amount"
                          mode="aggressive"
                        >
                          <b style="text-align: center"></b>
                          <v-text-field
                            :error-messages="errors"
                            dense
                            v-model="contribution[i].payable_liquid"
                            label="Liquido pagable"
                            :disabled="!enabled"
                            :outlined="editar"
                          ></v-text-field>
                        </ValidationProvider>
                      </v-col>
                      <v-col cols="12" class="py-0 my-0"  :md="lender_contribution.state_affiliate == 'Comisión' ? 4 : 2">
                        <ValidationProvider
                          v-slot="{ errors }"
                          name="Monto ajuste"
                          :rules="''"
                          mode="aggressive"
                        >
                          <b style="text-align: center"></b>
                          <v-text-field
                            :error-messages="errors"
                            dense
                            v-model="contribution[i].adjustment_amount"
                            :label= "lender_contribution.state_affiliate == 'Comisión' ? 'Liquido pagable' :  'Monto ajuste'"
                            :outlined = "!(contribution[i].payable_liquid == 0 && lender_contribution.state_affiliate != 'Comisión')? true : false"
                            :disabled = "!(contribution[i].payable_liquid == 0 && lender_contribution.state_affiliate != 'Comisión')? false : true"
                          ></v-text-field>
                        </ValidationProvider>
                      </v-col>
                      <template v-if="lender_contribution.state_affiliate != 'Comisión'">
                        <v-col cols="12" md="2" class="py-0 my-0" >
                          <b style="text-align: center">= {{(parseFloat(contribution[i].adjustment_amount) + parseFloat(contribution[i].payable_liquid)).toFixed(2)}}</b>
                        </v-col>
                        <v-col cols="12" md="5" class="py-0 my-0">
                          <ValidationProvider
                            v-slot="{ errors }"
                            name="Descripción"
                            :rules="''"
                            mode="aggressive"
                          >
                            <b style="text-align: center"></b>
                            <v-textarea
                              :error-messages="errors"
                              dense
                              v-model="contribution[i].adjustment_description"
                              label="Descripción ajuste"
                            :outlined = "!(contribution[i].payable_liquid == 0 && lender_contribution.state_affiliate != 'Comisión')? true : false"
                            :disabled = "!(contribution[i].payable_liquid == 0 && lender_contribution.state_affiliate != 'Comisión')? false : true"
                              rows="1"
                            ></v-textarea>
                          </ValidationProvider>
                        </v-col>
                      </template>
                    </v-row>
                  </v-col>

                  <v-col cols="12" md="5" class="py-0 my-0">
                    <v-row class="py-0 my-0">
                      <v-col cols="12" md="12" class="py-0 my-0" v-if="lender_contribution.state_affiliate != 'Comisión'"> BONOS </v-col>
                      <template v-if="lender_contribution.state_affiliate == 'Activo'">
                        <v-col cols="12" md="3" class="py-0 my-0">
                          <ValidationProvider
                            v-slot="{ errors }"
                            name="Bono Frontera"
                            :rules="''"
                            mode="aggressive"
                          >
                            <v-text-field
                              :error-messages="errors"
                              dense
                              v-model="contribution[i].border_bonus"
                              label="Bono Frontera"
                              :disabled="!enabled"
                              :outlined="editar"
                            ></v-text-field>
                          </ValidationProvider>
                        </v-col>
                        <v-col cols="12" md="3" class="py-0 my-0">
                          <ValidationProvider
                            v-slot="{ errors }"
                            name="Bono Oriente"
                             :rules="''"
                            mode="aggressive"
                          >
                            <v-text-field
                              :error-messages="errors"
                              dense
                              v-model="contribution[i].east_bonus"
                              label="Bono Oriente"
                              :disabled="!enabled"
                              :outlined="editar"
                            ></v-text-field>
                          </ValidationProvider>
                        </v-col>
                        <v-col cols="12" md="3" class="py-0 my-0">
                          <ValidationProvider
                            v-slot="{ errors }"
                            name="Bono Cargo"
                            :rules="''"
                            mode="aggressive"
                          >
                            <v-text-field
                              :error-messages="errors"
                              dense
                              v-model="contribution[i].position_bonus"
                              label="Bono Cargo"
                              :disabled="!enabled"
                              :outlined="editar"
                            ></v-text-field>
                          </ValidationProvider>
                        </v-col>
                        <v-col cols="12" md="3" class="py-0 my-0">
                          <ValidationProvider
                            v-slot="{ errors }"
                            name="Bono Seguridad Ciudadana"
                             :rules="''"
                            mode="aggressive"
                          >
                            <v-text-field
                              :error-messages="errors"
                              dense
                              v-model="contribution[i].public_security_bonus"
                              label="Bono Seguridad Ciudadana"
                              :disabled="!enabled"
                              :outlined="editar"
                            ></v-text-field>
                          </ValidationProvider>
                        </v-col>
                     </template> 
                      <v-col cols="12" :md="lender_contribution.state_affiliate == 'Pasivo' ? 4 : 3" class="py-0 my-0" v-if="lender_contribution.state_affiliate == 'Pasivo'">
                        <ValidationProvider
                          v-slot="{ errors }"
                          name="Renta dignidad"
                          :rules="''"
                          mode="aggressive"
                        >
                          <v-text-field
                            :error-messages="errors"
                            dense
                            v-model="contribution[i].dignity_rent"
                            label="Renta dignidad"
                            :disabled="!enabled"
                            :outlined="editar"
                          ></v-text-field>
                        </ValidationProvider>
                      </v-col>
                    </v-row>
                  </v-col>        
                </v-row>
                <template >
                  <v-col cols="12" class="py-0 my-0" v-if="type_sismu"> DATOS SISMU </v-col>
                  <v-col cols="12" md="3" class="py0 my-0" v-if="type_sismu">
                    <ValidationProvider
                      v-slot="{ errors }"
                      name="cuota"
                      :rules="'min_value:1'"
                      mode="aggressive"
                    >
                      <v-text-field
                        :error-messages="errors"
                        dense
                        v-model="data_sismu.quota_sismu"
                        outlined
                        label="Cuota"
                      ></v-text-field>
                    </ValidationProvider>
                  </v-col>
                  <v-col
                    cols="12"
                    md="3"
                    class="py-0 my-0"
                    v-if="
                      modalitySelected.name == 'Refinanciamiento Préstamo Hipotecario' ||
                      modalitySelected.name == 'Refinanciamiento Préstamo a Largo Plazo'
                    "
                  >
                    <v-checkbox
                      v-model="data_sismu.cpop_sismu"
                      label="Afiliado CPOP"
                    ></v-checkbox>
                  </v-col>
                  <v-col
                    cols="12"
                    md="3"
                    class="py-0 my-0"
                    v-if="
                      modalitySelected.name == 'Préstamo a Largo Plazo' ||
                      modalitySelected.name == 'Préstamo Hipotecario'
                    "
                  >
                    <v-checkbox
                      v-model="affiliate_data.cpop_affiliate"
                      label="Afiliado CPOP"
                    ></v-checkbox>
                  </v-col>
                </template>
              </v-container>
              <BallotsHipotecary
                v-show="hipotecario && (loan_detail.not_exist_modality == false)"
                :contrib_codebtor="contrib_codebtor"
                :modalidad.sync="modalidad"
                :affiliate.sync="affiliate"
                :data_loan.sync="data_loan"
                :global_parameters="global_parameters"/>
             </ValidationObserver>
          </v-card>
        </v-col>
      </v-row>
    </v-form>
  </v-flex>
</template>
<script>
import BallotsHipotecary from '@/components/loan/BallotsHipotecary'

export default {
  name: "ballots",
  data: () => ({
    bus: new Vue(),
    enabled: false,
    editar:true,
    monto:null,
    plazo:null,
    //interval:[],
    //loanTypeSelected:null,
    visible:false,
    hipotecario:false,
    window_size:4,
    see_field:false,
    loan_modality: {},
    data_ballots: [],
    contribution: [],
    choose_diff_month: false,
    number_diff_month: 1,
    lender_contribution: {},
    modality_loan: []
  }),
   props: {
    modalidad: {
      type: Object,
      required: true
    },
    affiliate_data: {
      type: Object,
      required: true
    },
    /*bonos: {
      type: Array,
      required: true
    },
    period: {
      type: Array,
      required: true
    },
    payable_liquid: {
      type: Array,
      required: true
    },*/
    modalities: {
      type: Array,
      required: true
    },
    procedureLoan: {
      type: Object,
      required: true
    },
    contrib_codebtor: {
      type: Array,
      required:true
    },
    loan_detail: {
      type: Object,
      required: true
    },
      affiliate: {
      type: Object,
      required: true
    },
    data_loan: {
      type: Object,
      required: true
    },
    edit_refi_repro: {
      type: Boolean,
      required: true
    },
    loanTypeSelected:{
      type: Object,
      required: true,
    },
    data_sismu:{
      type: Object,
      required: true
    },
    global_parameters:{
      type: Object,
      required:true
    },
    affiliate_contribution:{
      type: Object,
      required: true
    },
    
  },
  components: {
    BallotsHipotecary,
  },
  mounted() {
    //this.getLoanIntervals()
    this.getModalityLoan()
  },
  watch: {
    'loanTypeSelected.id': function(newVal, oldVal){
      if(newVal!= oldVal)
        this.Onchange()
        //alert ('ballot' + this.loanTypeSelected.id)
    }
  },
  computed: {
    isNew() {
      return this.$route.params.hash == 'new'
    },
    refinancing() {
      return this.$route.params.hash == 'refinancing'
    },
    reprogramming() {
      if(this.$route.params.hash == 'reprogramming'){
        return true
      }else{
        return false
      }
    },
    remake() {
      if(this.$route.params.hash == 'remake'){
        return true
      }else{
        return false
      }
    },
    type_sismu() {
      if(this.$route.query.type_sismu){
        return true
      }
      return false
    },
    //Realiza una validación para verificar si existe o no el objeto, en caso de no existir manda un objeto vacio sin generar erroes
    modalitySelected() {
      let modality = (this.modality_loan.find(item => item.id == this.loanTypeSelected.id))
      return modality || {} 
    }
  },
  methods: {
    //Intervalos de Plazo y Meses de una modalidad
    /*async getLoanIntervals() {
      try {
        let res = await axios.get(`loan_interval`)
        this.interval = res.data
        console.log(this.interval)
        if(this.reprogramming){
          this.Onchange()
        }
       }catch (e) {
        console.log(e)
      }
    },*/
    async getModalityLoan() {
      try {
        let res = await axios.get(`module/6/modality_loan`)
        this.modality_loan = res.data
        console.log(this.modality_loan)
        if(this.reprogramming){
          this.Onchange()
        }
       }catch (e) {
        console.log(e)
      }
    },
   //muestra los intervalos de acuerdo a una modalidad
    async Onchange(){
      this.choose_diff_month = false
      this.number_diff_month = 1
      for (let i = 0; i< this.modality_loan.length; i++) {
        if(this.loanTypeSelected.id==this.modality_loan[i].id){
          //if($store.getters.modalityLoan.find(item => item.id == loanTypeSelected.id).name == 12){
          if(this.modalitySelected.name == 'Préstamo Hipotecario' || this.modalitySelected.name == 'Refinanciamiento Préstamo Hipotecario'){
            this.hipotecario=true
            this.window_size=3
            this.see_field=true
          }else{
            this.hipotecario=false
            this.window_size=4
            this.see_field=false
          }
          /*this.monto= this.interval[i].minimum_amount+' - '+this.interval[i].maximum_amount,
          this.plazo= this.interval[i].minimum_term+' - '+this.interval[i].maximum_term
          //intervalos es el monto, plazo y modalidad y id de una modalidad
          this.intervalos.maximun_amoun=this.interval[i].maximum_amount
          this.intervalos.maximum_term= this.interval[i].maximum_term
          this.intervalos.minimun_amoun=this.interval[i].minimum_amount
          this.intervalos.minimum_term= this.interval[i].minimum_term
          this.intervalos.procedure_type_id= this.loanTypeSelected.id*/

          this.getLoanModalityAffiliate(this.$route.query.affiliate_id)
        } /*else{
        console.log('NO ES IGUAL A MODALIDAD INTERVALS'+this.interval[i].procedure_type_id +"=="+this.loanTypeSelected.id )
        }*/
      }
    },

    //Obtiene los parametros de la modalidad por afiliado
    async getLoanModalityAffiliate(id) {
      try {
        let resp = await axios.post(`affiliate/${id}/loan_modality?procedure_type_id=${this.loanTypeSelected.id}`,{
          type_sismu: this.data_sismu.type_sismu,
          cpop_sismu: this.data_sismu.cpop_sismu,
          cpop_affiliate: this.affiliate_data.cpop_affiliate,
          //reprogramming: this.reprogramming || this.remake
        })
        if(resp.data ==''){

          this.loan_detail.not_exist_modality = true
          this.toastr.error("El afiliado no puede ser evaluado en esta modalidad")
        }else{

          this.loan_detail.not_exist_modality = false
          this.loan_modality = resp.data

          this.monto= this.loan_modality.loan_modality_parameter.minimum_amount_modality+' - '+this.loan_modality.loan_modality_parameter.maximum_amount_modality
          this.plazo= this.loan_modality.loan_modality_parameter.minimum_term_modality+' - '+this.loan_modality.loan_modality_parameter.maximum_term_modality
          //intervalos es el monto, plazo y modalidad y id de una modalidad
          this.modalidad.maximun_amoun=this.loan_modality.loan_modality_parameter.maximum_amount_modality
          this.modalidad.maximum_term= this.loan_modality.loan_modality_parameter.maximum_term_modality
          this.modalidad.minimun_amoun=this.loan_modality.loan_modality_parameter.minimum_amount_modality
          this.modalidad.minimum_term= this.loan_modality.loan_modality_parameter.minimum_term_modality
          this.procedureLoan.procedure_id= this.loanTypeSelected.id

          this.modalidad.id = this.loan_modality.id
          this.modalidad.procedure_type_id = this.loan_modality.procedure_type_id
          this.modalidad.procedure_type_name = this.loan_modality.procedure_type.name
          this.modalidad.name = this.loan_modality.name
          this.modalidad.quantity_ballots = this.loan_modality.loan_modality_parameter.quantity_ballots
          this.modalidad.guarantors = this.loan_modality.loan_modality_parameter.guarantors
          this.modalidad.min_guarantor_category = this.loan_modality.loan_modality_parameter.min_guarantor_category
          this.modalidad.max_guarantor_category = this.loan_modality.loan_modality_parameter.max_guarantor_category
          this.modalidad.personal_reference = this.loan_modality.loan_modality_parameter.personal_reference
          this.modalidad.max_cosigner = this.loan_modality.loan_modality_parameter.max_cosigner
          this.modalidad.max_lenders = this.loan_modality.loan_modality_parameter.max_lenders

          this.loan_detail.min_guarantor_category = this.loan_modality.loan_modality_parameter.min_guarantor_category
          this.loan_detail.max_guarantor_category = this.loan_modality.loan_modality_parameter.max_guarantor_category
          /*if(this.loan_modality.loan_modality_parameter.quantity_ballots > 1){
            this.visible = true
          }else{
            this.visible = false
          }*/
          this.getBallots(id)
          this.generateContributions()
        }
      }catch (e) {
        console.log(e)
        this.toastr.error(e.type)
      }finally {
        this.loading = false
      }
    },

    //Metodo para sacar boleta de un afiliado
    async getBallots(id) {
      try {
        this.data_ballots=[]
        let res = await axios.get(`affiliate/${id}/contribution`, {
           params:{
             city_id: this.$store.getters.cityId,
             choose_diff_month: this.choose_diff_month,
             number_diff_month: this.number_diff_month,
             sortBy: ['month_year'],
             sortDesc: [1],
             per_page: this.modalidad.quantity_ballots,
             page: 1,
           }
          })
        this.lender_contribution = res.data
        this.affiliate_contribution.valid = this.lender_contribution.valid
        this.affiliate_contribution.state_affiliate = this.lender_contribution.state_affiliate
        this.affiliate_contribution.name_table_contribution = this.lender_contribution.name_table_contribution
        this.data_ballots = res.data.data
        this.fecha= new Date();

        for (let i = 0; i < this.modalidad.quantity_ballots; i++) {//colocar 1
          if(this.lender_contribution.valid && this.lender_contribution.state_affiliate =='Activo'){
            this.enabled = false
            this.editar=false
             //Carga los datos en los campos para ser visualizados en la interfaz
              this.contribution[i].contributionable_id = this.data_ballots[i].id
              this.contribution[i].payable_liquid = this.data_ballots[i].payable_liquid != null ? this.data_ballots[i].payable_liquid : 0
              this.contribution[i].border_bonus = this.data_ballots[i].border_bonus != null ? this.data_ballots[i].border_bonus : 0
              this.contribution[i].east_bonus = this.data_ballots[i].east_bonus != null ? this.data_ballots[i].east_bonus : 0
              this.contribution[i].position_bonus = this.data_ballots[i].position_bonus != null ? this.data_ballots[i].position_bonus : 0
              this.contribution[i].public_security_bonus = this.data_ballots[i].public_security_bonus != null ? this.data_ballots[i].public_security_bonus : 0
              this.contribution[i].period = this.$moment(this.data_ballots[i].month_year).format('YYYY-MM-DD')
              this.contribution[i].month = this.$moment(this.data_ballots[i].month_year).format('MMMM')

          } else if(!this.lender_contribution.valid && this.lender_contribution.state_affiliate =='Activo'){
              this.enabled = false
              this.editar=false
              this.contribution[i].contributionable_id = 0
              this.contribution[i].payable_liquid = 0
              this.contribution[i].border_bonus = 0
              this.contribution[i].east_bonus = 0
              this.contribution[i].position_bonus = 0
              this.contribution[i].public_security_bonus = 0
              this.contribution[i].period = this.$moment(this.lender_contribution.current_tiket).subtract(i,'months').format('YYYY-MM-DD')
              this.contribution[i].month = this.$moment(this.lender_contribution.current_tiket).subtract(i,'months').format('MMMM')
              
          } else if(this.lender_contribution.valid && this.lender_contribution.state_affiliate =='Pasivo'){
              this.enabled = true
              this.editar = true
              if(this.data_ballots[i]){
                this.contribution[i].contributionable_id = this.data_ballots[i].id
                this.contribution[i].payable_liquid = this.data_ballots[i].rent != null ? this.data_ballots[i].rent : 0
                this.contribution[i].dignity_rent = this.data_ballots[i].dignity_rent != null ? this.data_ballots[i].dignity_rent : 0
                this.contribution[i].period = this.$moment(this.data_ballots[i].month_year).format('YYYY-MM-DD')
                this.contribution[i].month = this.$moment(this.data_ballots[i].month_year).format('MMMM')
              }else{
                this.contribution[i].contributionable_id = 0
                this.contribution[i].payable_liquid = 0
                this.contribution[i].dignity_rent = 0
                this.contribution[i].period = this.$moment(this.lender_contribution.current_tiket).subtract(i,'months').format('YYYY-MM-DD')
                this.contribution[i].month = this.$moment(this.lender_contribution.current_tiket).subtract(i,'months').format('MMMM')
                }

          }
          else if(!this.lender_contribution.valid && this.lender_contribution.state_affiliate =='Pasivo'){
              this.enabled = true
              this.editar  = true
              this.contribution[i].contributionable_id = 0
              this.contribution[i].payable_liquid = 0
              this.contribution[i].dignity_rent = 0
              this.contribution[i].period = this.$moment(this.lender_contribution.current_tiket).subtract(i,'months').format('YYYY-MM-DD')
              this.contribution[i].month = this.$moment(this.lender_contribution.current_tiket).subtract(i,'months').format('MMMM')
          }
          else if(!this.lender_contribution.valid && this.lender_contribution.state_affiliate =='Comisión'){
              this.contribution[i].contributionable_id = 0
              this.contribution[i].payable_liquid = 0
              this.contribution[i].period = this.$moment(this.lender_contribution.current_tiket).subtract(i,'months').format('YYYY-MM-DD')
              this.contribution[i].month = this.$moment(this.lender_contribution.current_tiket).subtract(i,'months').format('MMMM')
          }
          else {
            this.toastr.error("Ocurrio caso especial de afiliado que no fue considerado.")}
        }
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
    generateContributions () {
      this.contribution = []
      for (let i = 0; i < this.modalidad.quantity_ballots; i++) {
        this.contribution.push({
          contributionable_id: null,
          payable_liquid: 0,
          position_bonus: 0,
          border_bonus: 0,
          public_security_bonus: 0,
          east_bonus: 0,
          dignity_rent: 0,
          period: null,
          adjustment_amount: 0,
          adjustment_description: null,
          loan_contributions_adjust_id: null,
        })
      }
    },
    getContributions() {
      return this.contribution
    },
    appendIconCallback () {
      if(this.number_diff_month < this.global_parameters.max_months_go_back){
        this.number_diff_month++
        this.choose_diff_month = true
        this.getBallots(this.$route.query.affiliate_id)
      }
    },
    prependIconCallback () {
      if(this.number_diff_month > 1){
        this.number_diff_month--
        this.choose_diff_month = true
        this.getBallots(this.$route.query.affiliate_id)
      }
    },
  }  
}  
</script>
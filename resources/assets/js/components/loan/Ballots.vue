<template>
  <v-flex xs12 class="px-3">
    <v-form>
      <v-row justify="center">
        <v-col cols="12">
          <v-card>
            <ValidationObserver ref="observer" >
              <v-container fluid >
                <v-row justify="center" class="py-0 my-0">
                  <v-col cols="12"  >
                    <v-container class="py-0 my-0 teal--text">
                      <v-row>
                        <v-col cols="12" md="3" class="py-0 my-0 text-center">
                          <strong>MODALIDAD DEL PRÉSTAMO{{!isCommission && !isEstacional}} {{ lender_contribution.state_affiliate != 'Comisión' && loanTypeSelected.id != 29 }}</strong><br>
                          <v-row>
                            <v-col cols="12" md="12" class="py-0 -my-0 mt-4">
                          <v-select
                            dense
                            v-model="loanTypeSelected.id"
                            @change="Onchange()"
                            :items="modalities"
                            item-text="second_name"
                            item-value="id"
                            required
                            outlined
                            :disabled="edit_refi_repro"
                            :loading="is_loading"
                          ></v-select>
                            </v-col>
                          </v-row>
                        </v-col>

                        <v-col cols="12" md="5" class="py-0 my-0 text-center">
                          <strong>SUBMODALIDAD DEL PRÉSTAMO</strong><br>
                          <v-row>
                            <v-col cols="12" md="12"  class="py-0 -my-0 mt-4">
                          <v-select
                            dense
                            v-model="loan_modality"
                            @change="onchangeSubmodality()"
                            :items="submodalities"
                            item-text="name"
                            item-value="id"
                            required
                            outlined
                            :disabled="edit_refi_repro"
                            :loading="is_loading"
                            return-object
                          ></v-select>
                            </v-col>
                          </v-row>
                        </v-col> 
                        <template v-if="isRetirementFund">
                          <v-col cols="12" md="2" class="py-0 my-0 text-center">
                            <strong>FONDO DE RETIRO (Promedio Bs.)</strong><br>
                            <span v-if="affiliate.retirement_fund_average">{{ affiliate.retirement_fund_average.retirement_fund_average | money }}</span>
                            <span v-else class="red--text">No existe un monto promedio para la categoria</span>
                          </v-col>
                        </template>
                        <template v-else>
                          <v-col cols="12" md="2" class="py-0 my-0 text-center">
                            <strong>INTERVALO MONTOS</strong><br>
                            {{ monto }}
                          </v-col>
                        </template>                    
                        <v-col cols="12" md="2" class="py-0 my-0 text-center">
                          <strong>PLAZO</strong><br>
                          {{ plazo }}
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
                      label="Retroceder meses"
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
                      <v-col cols="12" md="12" class="py-0 my-0 pb-1 uppercase"> COMPROBANTE DE PAGO <b>{{contribution[i].month}}</b></v-col>
                      <v-col cols="12" md="3" class="py-0 my-0" v-if="!isCommission && !isEstacional">
                        <ValidationProvider
                          v-slot="{ errors }"
                          name="Comprobante de pago"
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
                      <v-col cols="12" class="py-0 my-0" :md="isCommission || isEstacional ? 4 : 2">
                        <ValidationProvider
                          v-slot="{ errors }"
                          name="Monto ajuste"
                          :rules="{ regex: /^[0-9]*\.?[0-9]+$/ }"
                          mode="aggressive"
                        >
                          <v-text-field
                            :error-messages="errors"
                            dense
                            v-model="contribution[i].adjustment_amount"
                            :label="adjustmentLabel(contrib)"
                            :outlined = "isAdjustment(contrib)"
                            :disabled = "!isAdjustment(contrib)"
                          ></v-text-field>
                        </ValidationProvider>
                      </v-col>
                      <!-- TOTAL Y DESCRIPCIÓN (si aplica) -->
                      <template v-if="!isCommission && !isEstacional">
                        <v-col cols="12" md="2" class="py-0 my-0" >
                          <b style="text-align: center">= {{(parseFloat(contribution[i].adjustment_amount) + parseFloat(contribution[i].payable_liquid)) | money}}</b>
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
                              :outlined = "isAdjustment(contrib)"
                              :disabled = "!isAdjustment(contrib)"
                              rows="1"
                            ></v-textarea>
                          </ValidationProvider>
                        </v-col>
                      </template>
                    </v-row>
                  </v-col>

                  <v-col cols="12" md="5" class="py-0 my-0">
                    <v-row class="py-0 my-0">
                      <v-col cols="12" md="12" class="py-0 my-0" v-if="!isCommission && !isEstacional"> BONOS </v-col>
                      <template v-if="isActive">
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
                      <v-col cols="12" :md="isPassive && !isEstacional ? 4 : 3" class="py-0 my-0" v-if="isPassive && !isEstacional">
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
                  <v-col cols="12" class="py-0 my-0" v-if="type_sismu || (remake && data_loan.data_loan != null)"> DATOS SISMU </v-col>
                  <v-col cols="12" md="3" class="py0 my-0" v-if="type_sismu || (remake && data_loan.data_loan != null)">
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
                </template>
              </v-container>
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
    enabled: false,
    editar:true,
    monto:null,
    plazo:null,
    visible:false,
    loan_modality: {},
    data_ballots: [],
    contribution: [],
    choose_diff_month: false,
    number_diff_month: 1,
    lender_contribution: {},
    submodalities: []
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
    is_loading:{
      type: Boolean,
      required: true
    },

  },
  components: {
    BallotsHipotecary,
  },
  mounted() {
    //this.getModalityLoan()
  },
  watch: {
    'loanTypeSelected.id': function(newVal, oldVal){
      if(newVal!= oldVal)
        this.Onchange()
    },

  },
  computed: {
    isNew() {
      return this.$route.params.hash == 'new'
    },
    refinancing() {
      return this.$route.params.hash == 'refinancing'
    },
    reprogramming() {
      return this.$route.params.hash == 'reprogramming'
    },
    remake() {
      return this.$route.params.hash === 'remake'
    },
    type_sismu() {
      return !!this.$route.query.type_sismu
    },
    isRetirementFund() {
      return this.loan_modality.id === 93 || this.loan_modality.id === 94
    },
    isEstacional() {
      return this.loanTypeSelected.id == 29
    },
    isCommission() {
      return this.lender_contribution && this.lender_contribution.state_affiliate == 'Comisión'
    },
    isActive() {
      return this.lender_contribution && this.lender_contribution.state_affiliate == 'Activo'
    },
    isPassive() {
      return this.lender_contribution && this.lender_contribution.state_affiliate == 'Pasivo'
    },
  },
  methods: {        
   //muestra los intervalos de acuerdo a una modalidad
    async Onchange(){
      //cargamos la sumodalidad
      this.loan_modality.id = 0 //cuando cambie la modalidad la submodalidad se colca en 0
      this.choose_diff_month = false
      this.number_diff_month = 1
      if(this.isNew || this.type_sismu){
        for (let i = 0; i< this.modalities.length; i++) {
          if(this.loanTypeSelected.id == this.modalities[i].id){
            this.getLoanModalityAffiliate(this.$route.query.affiliate_id)
        } 
      }
      }else{
        if(this.loanTypeSelected.submodality[0].id != 0){      
          this.loan_modality = this.loanTypeSelected.submodality[0]
          this.submodalities = this.loanTypeSelected.submodality          
          this.onchangeSubmodality()
        }
      }
    },
    async getLoanModalityAffiliate(id) {
      try {
        this.choose_diff_month = false
        this.number_diff_month = 1
        let resp =await axios.post(`affiliate_loan_modality/${id}/${this.loanTypeSelected.id}`,{
          refinancing: this.refinancing?true:false, //Caso: T casos sismu, F nuevo tramite
          reprogramming: this.reprogramming?true:false //Caso: T casos sismu, F nuevo tramite
        })
        if(resp.data ==''){
          this.loan_detail.not_exist_modality = true
          this.submodalities = []
          this.monto = null
          this.plazo = null
          this.toastr.error("El afiliado no puede ser evaluado en esta modalidad")
        }else{
          this.submodalities = resp.data
          this.loan_detail.not_exist_modality = false
          if(this.submodalities.length == 1){//si se obtiene una sola modalidad
            this.loan_modality = resp.data[0]
            this.onchangeSubmodality()
          }
        }
      }catch (e) {
        console.log(e)
        this.toastr.error(e.type)
      }finally {
        this.loading = false
      }
    },
    //Obtiene los parametros de la modalidad por afiliado
    async onchangeSubmodality() {
      try {
        this.choose_diff_month = false
        this.number_diff_month = 1
 
        this.monto= parseFloat(this.loan_modality.loan_modality_parameter.minimum_amount_modality).toLocaleString("de-DE")+' - '+
                    parseFloat(this.loan_modality.loan_modality_parameter.maximum_amount_modality).toLocaleString("de-DE")
        this.plazo= this.loan_modality.loan_modality_parameter.minimum_term_modality+' - '+this.loan_modality.loan_modality_parameter.maximum_term_modality
        //intervalos es el monto, plazo y modalidad y id de una modalidad
        this.modalidad.maximun_amoun=this.loan_modality.loan_modality_parameter.maximum_amount_modality
        this.modalidad.maximum_term= this.loan_modality.loan_modality_parameter.maximum_term_modality
        this.modalidad.minimun_amoun=this.loan_modality.loan_modality_parameter.minimum_amount_modality
        this.modalidad.minimum_term= this.loan_modality.loan_modality_parameter.minimum_term_modality
        this.procedureLoan.procedure_id= this.loanTypeSelected.id

        this.modalidad.id = this.loan_modality.id //id submodalidad
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

        this.modalidad.loan_month_term = this.loan_modality.loan_modality_parameter.loan_month_term
        this.validateAffiliateModality()
        
      }catch (e) {
        console.log(e)
        this.toastr.error(e.type)
      }finally {
        this.loading = false
      }
    },
    //Validar afiliado con submodalidad
    async validateAffiliateModality(){
      try {
        let res = await axios.get(`validate_affiliate_modality/${this.$route.query.affiliate_id}/${this.loan_modality.id}`) 
        this.loan_detail.not_exist_modality = false
        this.getBallots(this.$route.query.affiliate_id)
        this.generateContributions()
      } catch (e) {
        console.log(e)
        this.toastr.error(e.type)
        this.loan_detail.not_exist_modality = true
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
          if(this.lender_contribution.valid && this.isActive){
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

          } else if(!this.lender_contribution.valid && this.isActive){
              this.enabled = false
              this.editar=false
              this.contribution[i].contributionable_id = 0
              this.contribution[i].payable_liquid = 0
              this.contribution[i].border_bonus = 0
              this.contribution[i].east_bonus = 0
              this.contribution[i].position_bonus = 0
              this.contribution[i].public_security_bonus = 0
              this.contribution[i].period = this.$moment(this.lender_contribution.current_tiket).subtract(this.modalidad.quantity_ballots-1-i,'months').format('YYYY-MM-DD')
              this.contribution[i].month = this.$moment(this.lender_contribution.current_tiket).subtract(this.modalidad.quantity_ballots-1-i,'months').format('MMMM')

          } else if(this.lender_contribution.valid && this.isPassive && !this.isEstacional){
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
                this.contribution[i].period = this.$moment(this.lender_contribution.current_tiket).subtract(this.modalidad.quantity_ballots-1-i,'months').format('YYYY-MM-DD')
                this.contribution[i].month = this.$moment(this.lender_contribution.current_tiket).subtract(this.modalidad.quantity_ballots-1-i,'months').format('MMMM')
                }

          } else if(!this.lender_contribution.valid && this.isPassive && !this.isEstacional){
              this.enabled = true
              this.editar  = true
              this.contribution[i].contributionable_id = 0
              this.contribution[i].payable_liquid = 0
              this.contribution[i].dignity_rent = 0
              this.contribution[i].period = this.$moment(this.lender_contribution.current_tiket).subtract(this.modalidad.quantity_ballots-1-i,'months').format('YYYY-MM-DD')
              this.contribution[i].month = this.$moment(this.lender_contribution.current_tiket).subtract(this.modalidad.quantity_ballots-1-i,'months').format('MMMM')

          } else if(!this.lender_contribution.valid && this.isCommission){
              this.contribution[i].contributionable_id = 0
              this.contribution[i].payable_liquid = 0
              this.contribution[i].period = this.$moment(this.lender_contribution.current_tiket).subtract(this.modalidad.quantity_ballots-1-i,'months').format('YYYY-MM-DD')
              this.contribution[i].month = this.$moment(this.lender_contribution.current_tiket).subtract(this.modalidad.quantity_ballots-1-i,'months').format('MMMM')

          } 
          else if(this.isPassive && this.isEstacional){
            console.log("estacional")
              this.contribution[i].contributionable_id = 0
              this.contribution[i].payable_liquid = 0
              let month_semester = this.$moment(this.lender_contribution.current_date).subtract((this.number_diff_month-this.modalidad.quantity_ballots-i) * this.loan_modality.loan_modality_parameter.loan_month_term,'months').format('YYYY-MM-DD')
              //obtener ultima fecha del semestre
              this.contribution[i].period = this.getSemesterDate(month_semester)
              //armar periodo
              let semester_date = this.$moment(this.getSemesterDate(month_semester));
              let semester_label = semester_date.month() + 1 <= 6 ? 'PRIMER SEMESTRE' : 'SEGUNDO SEMESTRE';
              this.contribution[i].month = `${semester_label} ${semester_date.year()}`;

          } else {
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
        this.number_diff_month = this.number_diff_month+1
        this.choose_diff_month = true
        this.getBallots(this.$route.query.affiliate_id)
      }
    },
    prependIconCallback () {
      if(this.number_diff_month > 1){
        this.number_diff_month = this.number_diff_month-1
        this.choose_diff_month = true
        this.getBallots(this.$route.query.affiliate_id)
      }
    },
    getSemesterDate(dateInput) {
      // Convierte la fecha de entrada en un objeto Moment
      let date_input = this.$moment(dateInput);

      // Obtener el año de la fecha de entrada
      let year_input_date = date_input.year();

      // Construye las fechas de referencia para los semestres usando el año de la fecha de entrada
      let first_semester_end = this.$moment(year_input_date + '-06-30');
      let second_semester_end = this.$moment(year_input_date + '-12-31');

      let result;

      if (date_input.isBefore(first_semester_end)) {
          // Si estamos en el primer semestre (del 1 de enero al 30 de junio)
          console.log('Primer semestre');
          result = first_semester_end.format('YYYY-MM-DD');
      } else if (date_input.isBefore(second_semester_end)) {
          // Si estamos en el segundo semestre (del 1 de julio al 31 de diciembre)
          console.log('Segundo semestre');
          result = second_semester_end.format('YYYY-MM-DD');
      } else {
          console.log('Fecha fuera de rango');
      }
      console.log(result);
      return result;
    },
    adjustmentLabel(contrib) {
      if (this.isCommission) return 'Liquido pagable'
      if (this.isEstacional) return 'Importe cotizable'
      return 'Monto ajuste'
    },
    isAdjustment(contrib) {
      // si el campo debe mostrarse como ajuste
      return !(
        parseFloat(contrib.payable_liquid) == 0 &&
        !this.isCommission &&
        !this.isEstacional
      )
    },
    toFloat(value) {
      // convierte cadenas o null a número seguro (0 si inválido)
      const n = parseFloat(value)
      return Number.isFinite(n) ? n : 0
    },
  }
}
</script>
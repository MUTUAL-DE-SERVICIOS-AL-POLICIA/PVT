<template>
  <div>
    <v-stepper v-model="e1" >
      <v-stepper-header class=" !pa-0 ml-0" >
        <template>
         <v-stepper-step
            :key="`${1}-step`"
            :complete="e1 > 1"
            :step="1">Modalidad
          </v-stepper-step >
          <v-divider v-if="1 !== steps" :key="1" ></v-divider>
          <v-stepper-step
            :key="`${2}-step`"
            :complete="e1 > 2"
            :step="2">Calculo
          </v-stepper-step>
          <v-divider v-if="2 !== steps" :key="2" ></v-divider>
          <v-stepper-step
            :key="`${3}-step`"
            :complete="e1 > 3"
            :step="3">Garantía
          </v-stepper-step>
          <v-divider v-if="3 !== steps" :key="3" ></v-divider>
          <v-stepper-step
            :key="`${4}-step`"
            :complete="e1 > 4"
            :step="4"
            >Afiliado
          </v-stepper-step>
          <v-divider v-if="4 !== steps" :key="4" ></v-divider>
          <v-stepper-step
            :key="`${5}-step`"
            :complete="e1 > 5"
            :step="5"
           >Formulario
          </v-stepper-step>
          <v-divider v-if="5 !== steps" :key="5" ></v-divider>
          <v-stepper-step
            :key="`${6}-step`"
            :complete="e1 > 6"
            :step="6"
            >Requisitos
          </v-stepper-step>
          <v-divider v-if="6 !== steps" :key="6" ></v-divider>
        </template>
      </v-stepper-header>
      <v-stepper-items>
        <v-stepper-content :key="`${1}-content`" :step="1">
          <v-card color="grey lighten-1">
            <Ballots
              ref="ballotsComponent"
              :modalities.sync="modalities"
              :procedureLoan.sync="procedureLoan"
              :modalidad.sync="modalidad"
              :affiliate.sync="affiliate"
              :contrib_codebtor="contrib_codebtor"
              :loan_detail.sync="loan_detail"
              :data_loan.sync="data_loan"
              :edit_refi_repro.sync="edit_refi_repro"
              :loanTypeSelected.sync="loanTypeSelected"
              :data_sismu.sync ="data_sismu"
              :affiliate_contribution="affiliate_contribution"
              :global_parameters ="global_parameters"
              :affiliate_data.sync="affiliate_data"
            />
            <v-container class="py-0">
              <v-row>
                <v-spacer></v-spacer> <v-spacer></v-spacer> <v-spacer></v-spacer>
                <v-col class="py-0">
                  <v-btn
                    color="primary"
                    @click="validateStepsOne()">
                    Siguiente
                  </v-btn>
                </v-col>
                  <!--{{contrib_codebtor}}-->
              </v-row>
            </v-container>
          </v-card>
        </v-stepper-content>
        <v-stepper-content :key="`${2}-content`" :step="2" >
          <v-card color="grey lighten-1">
            <h3 class="text-uppercase text-center">{{modalidad.name}}</h3>
            <v-card class="ma-3">
              <BallotsResult ref="BallotsResult"
                v-show="modalidad.procedure_type_name != 'Préstamo Hipotecario' && modalidad.procedure_type_name != 'Refinanciamiento Préstamo Hipotecario'"
                :data_sismu.sync="data_sismu"
                :lenders.sync="lenders"
                :calculator_result.sync="calculator_result"
                :loan_detail.sync="loan_detail"
                :data_loan_parent_aux.sync="data_loan_parent_aux"
                :modalidad.sync="modalidad"
                :modalidad_id.sync="modalidad.id"
                :liquid_calificated="liquid_calificated"
                >
                <template v-slot:title>
                  <v-col cols="12" class="py-0">Resultado para el Préstamo</v-col>
                </template>
              </BallotsResult>
              <BallotsResultHipotecary
                v-show="modalidad.procedure_type_name == 'Préstamo Hipotecario' || modalidad.procedure_type_name == 'Refinanciamiento Préstamo Hipotecario'"
                :data_sismu.sync="data_sismu"
                :calculator_result.sync="calculator_result"
                :loan_detail.sync="loan_detail"
                :data_loan_parent_aux.sync="data_loan_parent_aux"
                :modalidad.sync="modalidad"
                :liquid_calificated.sync="liquid_calificated"
                :lenders.sync="lenders"
                :lenders_aux.sync="lenders_aux"
              />
            </v-card>
            <v-container class="py-0">
              <v-row>
              <v-spacer></v-spacer><v-spacer> </v-spacer> <v-spacer></v-spacer>
                <v-col class="py-0">
                  <v-btn text
                    @click="beforeStep(2)">Atras</v-btn>
                  <v-btn
                    right
                    color="primary"
                    @click.stop="validateStepsTwo()">
                    Siguiente
                  </v-btn>
                </v-col>
              </v-row>
            </v-container>
          </v-card>
        </v-stepper-content>
        <v-stepper-content :key="`${3}-content`" :step="3" >
          <v-card color="grey lighten-1">
            <h3 class="text-uppercase text-center">{{modalidad.name}}</h3>
            <HipotecaryData ref="HipotecaryData"
              v-show="modalidad.procedure_type_name=='Préstamo Hipotecario' || modalidad.procedure_type_name=='Refinanciamiento Préstamo Hipotecario'"
              :loan_detail.sync="loan_detail"
              :loan_property="loan_property"
              :bus="bus"
            />
            <Guarantor
            v-show="modalidad.procedure_type_name != 'Préstamo Hipotecario' && modalidad.procedure_type_name != 'Refinanciamiento Préstamo Hipotecario'"
            :modalidad_guarantors.sync="modalidad.guarantors"
            :modalidad.sync="modalidad"
            :loan_detail.sync="loan_detail"
            :data_loan_parent_aux.sync="data_loan_parent_aux"
            :guarantors.sync="guarantors"
            :affiliate.sync="affiliate"
            :modalidad_id.sync="modalidad.id"/>
          <v-container class="py-0" v-show="modalidad.procedure_type_name!='Préstamo Hipotecario' && modalidad.procedure_type_name!='Refinanciamiento Préstamo Hipotecario'">
            <v-row>
            <v-spacer></v-spacer><v-spacer></v-spacer> <v-spacer></v-spacer>
              <v-col class="py-0">
                <v-btn text
                @click="beforeStep(3)">Atras</v-btn>
                <v-btn right
                color="primary"
                @click="validateStepsthree()">
                Siguiente
                </v-btn>
              </v-col>
            </v-row>
          </v-container>
        </v-card>
      </v-stepper-content>
      <v-stepper-content :key="`${4}-content`" :step="4" >
        <v-card color="grey lighten-1">
        <h3 class="text-uppercase text-center">{{modalidad.name}}</h3>
          <PersonalInformation
          :affiliate.sync="affiliate"
          :addresses.sync="addresses"
          />
        <v-container class="py-0">
          <v-row>
          <v-spacer></v-spacer><v-spacer></v-spacer><v-spacer></v-spacer>
            <v-col class="py-0">
              <v-btn text
              @click="beforeStep(4)">Atras</v-btn>
              <v-btn right
              color="primary"
              @click="validateStepsFour()">
              Siguiente
              </v-btn>
            </v-col>
          </v-row>
        </v-container>
        </v-card>
      </v-stepper-content>
      <v-stepper-content :key="`${5}-content`" :step="5">
        <v-card color="grey lighten-1">
          <h3 class="text-uppercase text-center">{{modalidad.name}}</h3>
          <FormInformation ref="FormInformation"
            :modalidad_id.sync="modalidad.id"
            :affiliate.sync="affiliate"
            :loan_detail.sync="loan_detail"
            :modalidad_personal_reference.sync="modalidad.personal_reference"
            :procedureLoan.sync="procedureLoan"
            :destino.sync="destino"
            :bus="bus"
            :personal_codebtor="personal_codebtor"
            :modalidad_max_cosigner.sync="modalidad.max_cosigner"
          />
         <!-- <CoDebtor
            v-show="this.modalidad.max_cosigner > 0"
            :personal_codebtor="personal_codebtor"
            :modalidad.sync="modalidad"
          />-->
          <v-container class="py-0">
           <!-- <v-row>
            <v-spacer></v-spacer><v-spacer></v-spacer><v-spacer></v-spacer>
              <v-col class="py-0">
                <v-btn text
                @click="beforeStep(5)">Atras</v-btn>
                <v-btn
                color="primary"
                @click="validateStepsFive()">
                Siguiente
                </v-btn>
              </v-col>
            </v-row>-->
          </v-container>
        </v-card>
      </v-stepper-content>
      <v-stepper-content :key="`${6}-content`" :step="6" >
        <v-card color="grey lighten-1">
          <h3 class="text-uppercase text-center">{{modalidad.name}}</h3>
          <Requirement
            :bus="bus"
            :loan_detail.sync="loan_detail"
            :lenders.sync="lenders"
            :modalidad.sync="modalidad"
            :modalidad_id.sync="modalidad.id"
            :guarantors.sync="guarantors"
            :loan_property_id.sync ="loan_property.id"
            :data_loan_parent.sync="data_loan_parent"
            :data_loan_parent_aux.sync="data_loan_parent_aux"/>
        </v-card>
      </v-stepper-content>
    </v-stepper-items>
  </v-stepper>
</div>
</template>
<style>
.v-expansion-panel-content__wrap {
  padding: 0 0px 0px;
}
.v-stepper__content {
  padding: 0px 0px 0px;
}
</style>
<script>
import Ballots from '@/components/loan/Ballots'
import Requirement from '@/components/loan/Requirement'
import BallotsResult from '@/components/loan/BallotsResult'
import PersonalInformation from '@/components/affiliate/PersonalInformation'
import FormInformation from '@/components/loan/FormInformation'
import Guarantor from '@/components/loan/Guarantor'
import CoDebtor from '@/components/loan/CoDebtor'
import HipotecaryData from '@/components/loan/HipotecaryData'
import BallotsResultHipotecary from '@/components/loan/BallotsResultHipotecary'
export default {
  name: "loan-steps",
  props: {
    affiliate: {
      type: Object,
      required: true
    },
    affiliate_data: {
      type: Object,
      required: true
    },
    addresses: {
      type: Array,
      required: true
    }
  },
  components: {
    Requirement,
    Ballots,
    PersonalInformation,
    FormInformation,
    BallotsResult,
    BallotsResultHipotecary,
    Guarantor,
    CoDebtor,
    HipotecaryData
  },
  data: () => ({
    bus: new Vue(),
    e1: 1,
    loan_detail:{
    maximum_suggested_valid:true,
    net_realizable_value:0,
    not_exist_modality: false
    },
    data_loan:{},
    calculator_result:{},
    steps: 6,
    modalities: [],
    guarantors: [],
    lenders:[],
    lenders_aux:[],
    data_loan_parent_aux:{},
    data_loan_parent:[],
    modalidad:{},
    procedureLoan:{},
    //payable_liquid:[0,0,0],
    //bonos:[0,0,0,0],
    //period:[],
    personal_codebtor:[],
    contrib_codebtor: [],
    contributions_aux: [],
    liquid_calificated:[],
    loan_property: {},
    destino:[],
    //Variables reprogramacion y refinanciamiento
    data_loan: {},
    data_loan_aux:{},
    data_loan_parent_sismu:{},
    amount_requested:0,
    edit_refi_repro: false,
    loanTypeSelected: {
      id: 0
    },
    data_sismu:{
      type_sismu: false,
      quota_sismu: 0,
      cpop_sismu: false,
      livelihood_amount:true
    },
    livelihood_amount: 0,
    contributions: [],
    affiliate_contribution: {},
    fecha: new Date(),
    ///variables step1    
    contributionable_type: null,
    loan_contributions_adjust_ids: [],
    contributionable_ids: [],
    modalidad_refi_repro_remake: 0,
    global_parameters: {}
  }),
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
    type_sismu() {
      if(this.$route.query.type_sismu){
        this.data_sismu.type_sismu = true
      }
      return this.data_sismu.type_sismu
    },
    remake() {
      return this.$route.params.hash == 'remake'
    },
  },
  watch: {
    steps (val) {
      if (this.e1 > val) {
        this.e1 = val
      }
    },
    /*'loanTypeSelected.id': function(newVal, oldVal){
      if(newVal!= oldVal)
      this.loanTypeSelected.id = this.modalidad_refi_repro_remake
      //alert ('steps' + this.loanTypeSelected.id)
    },*/
  },
  beforeMount(){
    this.getProcedureType()
    this.bus.$on('beforeStepBus', (val) => {
      this.beforeStep(val)
    })
    this.bus.$on('nextStepBus', (val) => {
      this.nextStep(val)
    })
  },
  mounted(){
    this.getGlobalParameters()
    if(!this.isNew && !this.type_sismu){
      this.getLoan(this.$route.query.loan_id)
    }else{
    if(this.remake)
      {
        this.getLoan(this.$route.query.loan_id)
      }
      //alert("Es nuevo")
    }
  },
  methods: {
    nextStep (n) {
      if (n == this.steps) {
        this.e1 = 1
      }
      else {
        if(n==1)
        {
        }
        if(n==2)
        {
          this.$refs.BallotsResult.simuladores()
        }
        if(n==3){
          console.log('Estos son los lenders')
          console.log(this.lenders)

          console.log('Calculator')
          console.log(this.calculator_result)
        }
        if(n==4)
        {
          console.log('segundo'+this.modalidad.personal_reference)
        }
        if(n==5)
        {
          //this.personal()
          //this.savePersonalReference()
          //console.log('segundo'+this.modalidad.personal_reference)
        }
        this.e1 = n + 1
      }
    },

    beforeStep (n) {
      this.e1 = n -1
    },

    async addDataLoan()
    {
      console.log('entro a añadir loan')
      if(!this.isNew){ //Si es nuevo y rehacer de nuevo
        //this.data_loan_parent.push(this.data_loan_parent_aux);
          this.data_loan_parent=[]
          this.data_loan_parent.push({
            code: this.data_loan_parent_aux.code,
            amount_approved: this.data_loan_parent_aux.amount_approved,
            loan_term: this.data_loan_parent_aux.loan_term,
            balance: this.data_loan_parent_aux.balance,
            estimated_quota: this.data_loan_parent_aux.estimated_quota,
          });
        }
       console.log(this.data_loan_parent)
    },
    async getProcedureType(){
      try {
        let resp = await axios.get(`module`,{
          params: {
            name: 'prestamos',
            sortBy: ['name'],
            sortDesc: ['false'],
            per_page: 10,
            page: 1
          }
        })
        this.modulo= resp.data.data[0].id
        let res = await axios.get(`module/${this.modulo}/modality_loan`)
        this.modalities = res.data
        //Verifica si es refinaciamiento o reprogramación para no mostrar Anticipo
        if(this.isNew){
          let modalities_aux=[]
          for(let i = 0; i < this.modalities.length; i++ ){
            if(this.modalities[i].name == "Préstamo Anticipo" ||
              this.modalities[i].name == "Préstamo a Corto Plazo" ||
              this.modalities[i].name == "Préstamo a Largo Plazo" ||
              this.modalities[i].name == "Préstamo Hipotecario" ){
              modalities_aux.push(this.modalities[i])
            }
          }
          this.modalities = modalities_aux
        }
        else if(this.refinancing){
          let modalities_aux=[]
          for(let i = 0; i < this.modalities.length; i++ ){
            if(this.modalities[i].name == "Refinanciamiento Préstamo a Corto Plazo" ||
              this.modalities[i].name == "Refinanciamiento Préstamo a Largo Plazo" ||
              this.modalities[i].name == "Refinanciamiento Préstamo Hipotecario"){
              modalities_aux.push(this.modalities[i])
            }
          }
          this.modalities = modalities_aux
        }
        else if(this.reprogramming){
          let modalities_aux=[]
          for(let i = 0; i < this.modalities.length; i++ ){
            if(this.modalities[i].name != "Préstamo Anticipo" &&
              this.modalities[i].name != 'Préstamo a Corto Plazo' &&
              this.modalities[i].name != 'Refinanciamiento Préstamo a Corto Plazo' ){
              modalities_aux.push(this.modalities[i])
            }
          }
          this.modalities = modalities_aux
        }else if(this.remake){
          this.modalities
        }else{
          this.toastr.error('Ocurrio un error al obtener la modadlidad')
        }
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
    //Ajuste del Titular
    async saveAdjustment(){
      try {
      this.loan_contributions_adjust_ids = []
      this.contributionable_ids = []

      this.contributions.forEach(async (item, i) => {
        //Verificar si el afiliado es pasivo para introducir su contribución
        if(this.affiliate_contribution.state_affiliate == 'Pasivo'){
          let res = await axios.post(`aid_contribution/updateOrCreate`,{
            affiliate_id: this.$route.query.affiliate_id,
            month_year: this.contributions[i].period,
            rent: this.contributions[i].payable_liquid,
            dignity_rent: this.contributions[i].dignity_rent,
          })
          let contribution_passive = res.data
            this.contributions[i].contributionable_id = contribution_passive.id
            if (this.contributionable_ids.indexOf(this.contributions[i].contributionable_id) === -1) {
              this.contributionable_ids.push(this.contributions[i].contributionable_id)
            }
            this.contributionable_type = 'aid_contributions'
        }
        else if(this.affiliate_contribution.state_affiliate == 'Activo') {
          if (this.contributionable_ids.indexOf(this.contributions[i].contributionable_id) === -1) {
            this.contributionable_ids.push(this.contributions[i].contributionable_id)
          }
          this.contributionable_type = 'contributions'
        }
        else if(this.affiliate_contribution.state_affiliate == 'Comisión') {
          this.contributionable_type = 'loan_contribution_adjusts'
        }

        //Para el ajuste
        if(this.contributions[i].adjustment_amount > 0){ //aqui se debe colocar la edicion del ajuste, hacer condicional
          //guardar el ajuste
          let res = await axios.post(`loan_contribution_adjust/updateOrCreate`, {
            affiliate_id: this.$route.query.affiliate_id,
            adjustable_id: this.affiliate_contribution.state_affiliate != 'Comisión' ? this.contributions[i].contributionable_id : this.$route.query.affiliate_id,
            adjustable_type: this.affiliate_contribution.state_affiliate != 'Comisión' ? this.affiliate_contribution.name_table_contribution : 'affiliates',
            type_affiliate: 'lender',
            amount: this.contributions[i].adjustment_amount,
            type_adjust: this.affiliate_contribution.state_affiliate != 'Comisión' ? 'adjust' : 'liquid',
            period_date: this.contributions[i].period,
            description: this.affiliate_contribution.state_affiliate != 'Comisión' ? this.contributions[i].adjustment_description : 'Liquido pagable por Comisión'
          })
          this.contributions[i].loan_contributions_adjust_id = res.data.id
          console.log(this.contributions[i].loan_contributions_adjust_id)
          if (this.loan_contributions_adjust_ids.indexOf(this.contributions[i].loan_contributions_adjust_id) === -1) {
            this.loan_contributions_adjust_ids.push(this.contributions[i].loan_contributions_adjust_id)
          }

        }else{
          console.log('No tiene ajuste')
        }
      })
      }
      catch (e){
        console.log(e)
      }
    },

   //TAB1 formatContributions datos obtenidos de los campos liquido y bonos, adecuandolo a formato para guardado y obtener liquido para calificación
    formatContributions() {
      let array_lender = []
      let array_codebtors = []
      let contributions_lender =[]
      //Armar contribuciones del titular
      for (let i = 0; i < this.contributions.length; i++) {
        contributions_lender.push({
          payable_liquid: parseFloat(this.contributions[i].payable_liquid)  + parseFloat(this.contributions[i].adjustment_amount),
          position_bonus: this.contributions[i].position_bonus,
          border_bonus: this.contributions[i].border_bonus,
          public_security_bonus: this.contributions[i].public_security_bonus,
          east_bonus:this.contributions[i].east_bonus,
          dignity_rent_bonus: this.contributions[i].dignity_rent
        })
      }
      array_lender[0] = {
        affiliate_id:this.$route.query.affiliate_id,
        sismu: this.data_sismu.type_sismu,
        quota_sismu: this.data_sismu.quota_sismu,
        parent_loan_id: this.parent_loan_id,
        contributions: contributions_lender
      }
      //Armar contribuciones del codeudor afiliado
      for (let i = 0; i < this.contrib_codebtor.length; i++) {
        let contributions_codebtor=[]
        for (let j = 0; j < this.contrib_codebtor[i].contribution.length; j++) {
          contributions_codebtor.push({
            payable_liquid: parseFloat(this.contrib_codebtor[i].contribution[j].payable_liquid) + parseFloat(this.contrib_codebtor[i].contribution[j].adjustment_amount),
            border_bonus: this.contrib_codebtor[i].contribution[j].border_bonus,
            east_bonus: this.contrib_codebtor[i].contribution[j].east_bonus,
            position_bonus: this.contrib_codebtor[i].contribution[j].position_bonus,
            public_security_bonus: this.contrib_codebtor[i].contribution[j].public_security_bonus
          })
        }
        array_codebtors[i] = {
          affiliate_id: this.contrib_codebtor[i].id_affiliate,
          sismu: this.data_sismu.type_sismu,
          quota_sismu: this.data_sismu.quota_sismu,
          parent_loan_id: this.parent_loan_id,
          contributions: contributions_codebtor
        }
      }
      //concatenar array del titular y codeudores
      this.contributions_aux = array_lender.concat(array_codebtors)    
    },

    //TAB1 Obtener liquido para calificación
    async liquidCalificated(){
      this.formatContributions()
      try {
        let res = await axios.post(`liquid_calificated`,{
          liquid_calification: this.contributions_aux
        })

        this.liquid_calificated = res.data

         if(this.modalidad.procedure_type_name == 'Préstamo Hipotecario' || this.modalidad.procedure_type_name == 'Refinanciamiento Préstamo Hipotecario')
          {
              if(this.loan_detail.net_realizable_value<=this.modalidad.maximun_amoun)
              {
                this.amount_requested=this.loan_detail.net_realizable_value
              }
              else{
                this.amount_requested=this.modalidad.maximun_amoun
              }
              let res1 = await axios.post(`simulator`, {
              procedure_modality_id:this.modalidad.id,
              amount_requested: this.amount_requested,
              months_term:  this.modalidad.maximum_term,
              guarantor: false,
              liquid_qualification_calculated_lender: 0,
              liquid_calculated:this.liquid_calificated
              })

              this.calculator_result = res1.data

              if( this.calculator_result.amount_maximum_suggested<this.amount_requested){
                this.calculator_result.amount_requested=this.calculator_result.amount_maximum_suggested
                this.loan_detail.amount_requested=this.calculator_result.amount_maximum_suggested
              }else{
                this.calculator_result.montos= this.amount_requested
                this.loan_detail.amount_requested= this.amount_requested
              }

              this.lenders=res.data

              for(let i = 0; i < this.lenders.length; i++ ){
                //Armar el nombre de los lenders
                let res4 = await axios.get(`affiliate/${this.lenders[i].affiliate_id}`)
                this.lenders_aux[i] =res4.data.full_name
                //obtener las contribuciones para hipotecario de contrib_codebtor, i=0 es lender de ballots
                this.lenders[i].payment_percentage=this.calculator_result.affiliates[i].payment_percentage
                this.lenders[i].indebtedness_calculated=this.calculator_result.affiliates[i].indebtnes_calculated
                this.lenders[i].quota_treat=this.calculator_result.affiliates[i].quota_calculated_estimated
                if(i == 0){
                this.lenders[i].contributionable_type= this.contributionable_type
                this.lenders[i].loan_contributions_adjust_ids=this.loan_contributions_adjust_ids
                this.lenders[i].contributionable_ids=this.contributionable_ids
                }else{
                this.lenders[i].contributionable_type= this.contrib_codebtor[i-1].contributionable_type
                this.lenders[i].loan_contributions_adjust_ids=this.contrib_codebtor[i-1].loan_contributions_adjust_ids
                this.lenders[i].contributionable_ids=this.contrib_codebtor[i-1].contributionable_ids
                }

              }

              this.loan_detail.minimum_term=this.modalidad.minimum_term
              this.loan_detail.maximum_term=this.modalidad.maximum_term
              this.loan_detail.minimun_amoun=this.modalidad.minimun_amoun
              this.loan_detail.maximun_amoun=this.modalidad.maximun_amoun

              this.loan_detail.months_term=this.modalidad.maximum_term
              this.loan_detail.liquid_qualification_calculated=this.calculator_result.liquid_qualification_calculated_total
              this.loan_detail.indebtedness_calculated=this.calculator_result.indebtedness_calculated_total
              this.loan_detail.maximum_suggested_valid=this.calculator_result.maximum_suggested_valid
              this.loan_detail.quota_calculated_total_lender=this.calculator_result.quota_calculated_estimated_total
              this.loan_detail.is_valid=this.calculator_result.is_valid
              for(let j = 0; j < this.liquid_calificated.length; j++ ){
                if(this.liquid_calificated[j].livelihood_amount==false)
                {
                  this.data_sismu.livelihood_amount=this.liquid_calificated[j].livelihood_amount
                }else{
                  this.data_sismu.livelihood_amount=true
                }
             }
            }
            else{
              this.data_sismu.livelihood_amount=this.liquid_calificated[0].livelihood_amount

              let res1 = await axios.post(`simulator`, {
              procedure_modality_id:this.modalidad.id,
              amount_requested: this.modalidad.maximun_amoun,
              months_term:  this.modalidad.maximum_term,
              guarantor: false,
              liquid_qualification_calculated_lender: 0,
              liquid_calculated:this.liquid_calificated
              })
              this.calculator_result = res1.data

              if( this.calculator_result.amount_maximum_suggested<this.modalidad.maximun_amoun){
                this.calculator_result.amount_requested=this.calculator_result.amount_maximum_suggested
              }else{
                this.calculator_result.amount_requested=this.modalidad.maximun_amoun
              }
              this.lenders=res.data
              this.lenders[0].payment_percentage=this.calculator_result.affiliates[0].payment_percentage
              this.lenders[0].indebtedness_calculated=this.calculator_result.affiliates[0].indebtedness_calculated
              this.lenders[0].quota_treat=this.calculator_result.affiliates[0].quota_calculated
              this.lenders[0].contributionable_type=this.contributionable_type
              this.lenders[0].loan_contributions_adjust_ids=this.loan_contributions_adjust_ids
              this.lenders[0].contributionable_ids=this.contributionable_ids

              this.loan_detail.minimum_term=this.modalidad.minimum_term
              this.loan_detail.maximum_term=this.modalidad.maximum_term
              this.loan_detail.minimun_amoun=this.modalidad.minimun_amoun
              this.loan_detail.maximun_amoun=this.modalidad.maximun_amoun
              this.loan_detail.amount_requested=this.calculator_result.amount_requested
              this.loan_detail.amount_maximum_suggested=this.calculator_result.amount_maximum_suggested
              this.loan_detail.months_term=this.modalidad.maximum_term
              this.loan_detail.liquid_qualification_calculated=this.calculator_result.liquid_qualification_calculated_total
              this.loan_detail.indebtedness_calculated=this.calculator_result.indebtedness_calculated_total
              this.loan_detail.maximum_suggested_valid=this.calculator_result.maximum_suggested_valid
              this.loan_detail.is_valid=this.calculator_result.is_valid
              this.loan_detail.quota_calculated_total_lender=this.calculator_result.quota_calculated_estimated_total
          }
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
    async getLoan(id) {  
      try {
        this.loading = true
        let res = await axios.get(`loan/${id}`)
        this.data_loan = res.data

         this.data_loan_parent_aux.guarantors=res.data.guarantors

         this.data_loan_parent_aux.parent_loan_id = res.data.parent_loan_id
         this.data_loan_parent_aux.parent_reason = res.data.parent_reason

        if(this.refinancing || this.reprogramming){
          this.data_loan_parent_aux.code= res.data.code
          this.data_loan_parent_aux.amount_approved= res.data.amount_approved
          this.data_loan_parent_aux.loan_term= res.data.loan_term
          this.data_loan_parent_aux.balance= res.data.balance
          this.data_loan_parent_aux.estimated_quota= res.data.estimated_quota

        } else if(this.remake && res.data.parent_loan != null && res.data.data_loan == null){
          this.data_loan_parent_aux.code = res.data.parent_loan.code
          this.data_loan_parent_aux.amount_approved = res.data.parent_loan.amount_approved
          this.data_loan_parent_aux.loan_term = res.data.parent_loan.loan_term
          this.data_loan_parent_aux.balance = res.data.parent_loan.balance
          this.data_loan_parent_aux.estimated_quota = res.data.parent_loan.estimated_quota

        } else if(this.remake && res.data.parent_loan == null && res.data.data_loan != null){
          this.data_loan_parent_aux.code = res.data.data_loan.code
          this.data_loan_parent_aux.amount_approved = res.data.data_loan.amount_approved
          this.data_loan_parent_aux.loan_term = res.data.data_loan.loan_term
          this.data_loan_parent_aux.balance = res.data.data_loan.balance
          this.data_loan_parent_aux.estimated_quota = res.data.data_loan.estimated_quota
        }
         if(this.refinancing){
          let res3 = await axios.post(`procedure_brother`,{
            id_loan: id
          })
          this.modalidad_refi_repro_remake = res3.data.id
        }else{
          this.modalidad_refi_repro_remake = this.data_loan.modality.procedure_type_id
        }
        this.loanTypeSelected.id =this.modalidad_refi_repro_remake
        this.edit_refi_repro = true
        if(this.data_loan.property_id != null){
          let res3 = await axios.get(`loan_property/${this.data_loan.property_id}`)
          this.loan_detail.net_realizable_value = res3.data.net_realizable_value
        }
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },

    async getGlobalParameters(){
      try {
        let res = await axios.get(`loan_global_parameter`)
        this.global_parameters = res.data.data[0]
        //console.log(this.global_parameters)
        this.livelihood_amount = this.global_parameters.livelihood_amount
      } catch (e) {
        console.log(e)
      }
    },

    validateStepsOne(){
      //this.contributions = []
      let continuar = false
      //llama al a funcion getContributions del componente Ballots
      //this.contributions = this.$refs.ballotsComponent.getLoanModalityAffiliate(this.$route.query.affiliate_id) //TODO REVISAR LA LLAMDA A ESTA LINEA, A DETALLE
      if(this.$refs.ballotsComponent) {
        this.contributions = this.$refs.ballotsComponent.getContributions()
      }
      //this.saveAdjustment()
      //this.liquidCalificated()
      if(this.loanTypeSelected.id > 0){
        if(this.loan_detail.not_exist_modality==false){
          //validaciones de todas las contribuciones
          for(let i = 0; i < this.contributions.length; i++){
            if((parseFloat(this.contributions[i].payable_liquid) + parseFloat(this.contributions[i].adjustment_amount)) >= this.global_parameters.livelihood_amount){
              continuar = true
              if( continuar == true && (parseFloat(this.contributions[i].position_bonus)+ 
              parseFloat(this.contributions[i].border_bonus)+ 
              parseFloat(this.contributions[i].public_security_bonus)+ 
              parseFloat(this.contributions[i].east_bonus)) < 
              (parseFloat(this.contributions[i].payable_liquid) + 
              parseFloat(this.contributions[i].adjustment_amount))){
                continuar = true
                if(continuar == true && 
                !(this.contributions[i].adjustment_amount > 0 && 
                (this.contributions[i].adjustment_description == null || this.contributions[i].adjustment_description == '') && 
                (this.affiliate_contribution.state_affiliate == 'Pasivo' || this.affiliate_contribution.state_affiliate == 'Activo'))){
                    continuar = true
                }else{
                    continuar = false
                    this.toastr.error('Existe un ajuste en el mes de '+this.contributions[i].month.toUpperCase()+ " ingrese descripción del ajuste")
                    break;
                }
              }else{
                continuar = false
                this.toastr.error(this.contributions[i].month.toUpperCase()  + " La sumatoria de bonos debe ser menor al Liquido pagable")
                break;
              }

            }else{
              continuar = false
              this.toastr.error(this.contributions[i].month.toUpperCase()  +" El 'Liquido Pagable' + 'Monto Ajuste' debe ser mayor ó igual al Monto de subsistencia que son "+this.global_parameters.livelihood_amount+" Bs.")
              break;
            }
          }
          //validar otros casos
          if(continuar == true && !this.type_sismu){
            if(this.modalidad.procedure_type_name == 'Préstamo Hipotecario' || this.modalidad.procedure_type_name == 'Refinanciamiento Préstamo Hipotecario'){
              if(this.loan_detail.net_realizable_value >= this.modalidad.minimun_amoun){
                 this.saveAdjustment()
                this.liquidCalificated()
                this.nextStep(1)
              }else{
                this.toastr.error("El valor VNR debe ser mayor al monto minimo "+this.modalidad.minimun_amoun+ " correspondiente a la modalidad")
              }
            }else{
                 this.saveAdjustment()
                this.liquidCalificated()
                this.nextStep(1)
            }
          }else if(continuar == true && this.type_sismu){
            if(this.modalidad.procedure_type_name == 'Préstamo Hipotecario' || this.modalidad.procedure_type_name == 'Refinanciamiento Préstamo Hipotecario'){
              if(this.loan_detail.net_realizable_value >= this.modalidad.minimun_amoun){
                if(this.data_sismu.quota_sismu > 0){
                   this.saveAdjustment()
                  this.liquidCalificated()
                  this.nextStep(1)
                }else{
                  this.toastr.error("Introduzca la CUOTA del SISMU")
                }
              }else{
                this.toastr.error("El valor VNR debe ser mayor al monto minimo "+this.modalidad.minimun_amoun+ " correspondiente a la modalidad")
              }
            }else{
                if(this.data_sismu.quota_sismu > 0){
                   this.saveAdjustment()
                  this.liquidCalificated()
                  this.data_loan_parent_aux.estimated_quota= this.data_sismu.quota_sismu
                  this.nextStep(1)
                }else{
                  this.toastr.error("Introduzca la CUOTA del SISMU")
                }
            }
          }
        }else{
          this.toastr.error("El afiliado no puede ser evaluado en esta modalidad")
        }
      }else{
        this.toastr.error("Seleccione una modalidad")
      }
    },

    validateStepsTwo()
    {
      if(!this.loan_detail.maximum_suggested_valid){
        this.toastr.error("El monto solicitado no pertenece a esta modalidad.")
      }else{
        if(!this.loan_detail.is_valid)
        {
          this.toastr.error("No puede quedarse con un liquido menor al monto de subsistencia.")
        }
        else{
           if(!(this.isNew || (this.remake && this.data_loan.parent_reason == null))){
            if(this.data_loan_parent_aux.code==null)
            {
              this.toastr.error("Tiene que llenar el Codigo del Prestamo Padre.")
            }else{
              if(this.data_loan_parent_aux.amount_approved==null)
                {
                  this.toastr.error("Tiene que llenar el Monto del Prestamo Padre.")
                }else{
                  if(this.data_loan_parent_aux.loan_term==null)
                  {
                    this.toastr.error("Tiene que llenar el Plazo del Prestamo Padre.")
                  }else{
                    if(this.data_loan_parent_aux.balance==null)
                    {
                      this.toastr.error("Tiene que llenar el Saldo del Prestamo Padre.")
                    }else{
                      if(this.reprogramming){
                        if(this.data_loan_parent_aux.loan_term >= this.calculator_result.months_term )
                        {
                          this.toastr.error("El plazo no puede ser menor o igual al plazo anterior.")
                        }else{
                          if(this.data_loan_parent_aux.balance == this.calculator_result.amount_requested)
                          {
                            this.addDataLoan()
                            this.nextStep(2)
                          }else{
                            this.toastr.error("El Monto Solicitado debe ser igual al Saldo.")
                          }
                        }
                      }else{
                        if(parseFloat(this.data_loan_parent_aux.balance) >= parseFloat(this.calculator_result.amount_requested))
                        {
                          this.toastr.error("El saldo no puede ser mayor al Monto Solicitado.")
                        }
                        else{
                          if(this.data_loan_parent_aux.estimated_quota==null)
                          {
                            this.toastr.error("Tiene que llenar la Cuota del Prestamo Padre.")
                          }else{
                            this.addDataLoan()
                            this.nextStep(2)
                          }
                        }
                      }
                    }
                  }
                }
              }
            }else{
              if(this.modalidad.procedure_type_name=='Préstamo Hipotecario' || this.modalidad.procedure_type_name == 'Refinanciamiento Préstamo Hipotecario'){
                 if(parseFloat(this.calculator_result.amount_requested) > parseFloat(this.loan_detail.net_realizable_value) )
                {
                  this.toastr.error("El Monto Solicitado no puede ser mayor al Monto del Inmueble")
                }
                else{
                  this.nextStep(2)
                }
              }else{
                if(this.calculator_result.amount_requested>this.loan_detail.amount_maximum_suggested)
                {
                  this.toastr.error("El Monto Solicitado no puede ser mayor al Monto maximo sugerido")
                }
                else{
                  this.nextStep(2)
                }
              }
          }
        }
      }
    },

    validateStepsthree()
    {
      if(this.modalidad.guarantors > 0)
      {
        if(this.modalidad.guarantors==this.guarantors.length)
        {
          if(this.loan_detail.simulador==true)
          {
            this.nextStep(3)
          }else{
              this.toastr.error("Debe calcular la cuota del garante")
          }
        }
        else{
          this.toastr.error("Le falta añadir garantes.")
        }
      }else{
        this.nextStep(3)
      }
    },
         validateStepsFour()
    {
        if(this.affiliate.city_identity_card_id != null){
          if(this.addresses.length != 0){
            this.nextStep(4)
          }else{
            this.toastr.error("No se encuentra registrada ninguna dirección. Por favor registre la dirección del afiliado.")
          }
        }else{
          this.toastr.error("Por favor registre la ciudad de expedición del CI.")
        }
    },
  }
}
</script>|
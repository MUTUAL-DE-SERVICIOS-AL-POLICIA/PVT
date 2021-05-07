<template>
  <v-card flat>
    <v-card-title>
      <v-toolbar dense style="z-index: 1" color='tertiary'>
        <v-toolbar-title>
          <Breadcrumbs />
        </v-toolbar-title>
        <v-spacer></v-spacer>
        <template v-if="$route.query.workTray != 'all'">
          <template>
          <v-tooltip top>
            <template v-slot:activator="{ on }">
              <v-btn
                v-show="!loan.validated"
                v-on="on"
                icon
                outlined
                small
                color="success"
                class="darken-2 ml-4"
                @click="validation()"
              >
                <v-icon dark>mdi-file-check</v-icon>
              </v-btn>
            </template>
            <span>Validar trámite</span>
          </v-tooltip>
          <v-tooltip top>
            <template v-slot:activator="{ on }">
              <v-btn
                v-show="!loan.validated"
                v-on="on"
                icon
                outlined
                small
                color="orange"
                class="ml-4"
                @click="bus.$emit('openDialog', { edit: false, accion: 'devolver'})"
              >
                <v-icon>mdi-file-undo</v-icon>
              </v-btn>
            </template>
            <span>Devolver trámite</span>
          </v-tooltip>
          <v-tooltip top>
            <template v-slot:activator="{ on }">
              <v-btn
                top
                v-if="permissionSimpleSelected.includes('delete-loan')"
                v-on="on"
                icon
                outlined
                small
                color="error"
                class="darken-2 ml-4"
                @click="
                  bus.$emit('openDialog', { edit: false, accion: 'anular' })
                "
              >
                <v-icon>mdi-file-cancel</v-icon>
              </v-btn>
            </template>
            <span>Anular trámite</span>
          </v-tooltip>
          </template>
        </template>
        <template v-else><h6 class="caption">
          <strong>Ubicación trámite:</strong> <br />
          <v-icon x-small color="orange">mdi-folder-information</v-icon>{{role_name}} <br>
          <v-icon x-small color="blue" v-if="user_name != null">mdi-file-account</v-icon> {{user_name}}</h6>
        </template>
      </v-toolbar>
    </v-card-title>
    <v-card-text>
      <v-tabs
        v-model="tab"
        background-color="deep-blue accent-2"
        class="elevation-2"
        dark
        :vertical="vertical"
        :icons-and-text="icons"
      >
        <v-tabs-slider></v-tabs-slider>
        <v-tab v-if="!editable" :href="`#tab-1`">
          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-icon v-if="icons" v-bind="attrs" v-on="on">mdi-trending-up</v-icon>
            </template>
            <span>
              <b>DATOS DEL PRESTAMO</b>
            </span>
          </v-tooltip>
        </v-tab>
        <v-tab :href="`#tab-2`">
          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-icon v-if="icons" v-bind="attrs" v-on="on">mdi-file-eye</v-icon>
            </template>
            <span>
              <b>DATOS ESPECIFICOS DEL PRESTAMO</b>
            </span>
          </v-tooltip>
        </v-tab>
        <v-tab :href="`#tab-3`">
          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-icon v-if="icons" v-bind="attrs" v-on="on">mdi-file</v-icon>
            </template>
            <span>
              <b>DOCUMENTOS PRESENTADOS</b>
            </span>
          </v-tooltip>
        </v-tab>
        <v-tab :href="`#tab-4`">
          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-icon v-if="icons" v-bind="attrs" v-on="on">mdi-account</v-icon>
            </template>
            <span>
              <b>DATOS PERSONALES DEL AFILIADO</b>
            </span>
          </v-tooltip>
        </v-tab>
        <v-tab :href="`#tab-5`">
          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-icon v-if="icons" v-bind="attrs" v-on="on">mdi-file-account</v-icon>
            </template>
            <span>
              <b>INFORMACION ADICIONAL</b>
            </span>
          </v-tooltip>
        </v-tab>
        <v-tab :href="`#tab-6`">
          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-icon v-if="icons" v-bind="attrs" v-on="on">mdi-format-list-checkbox</v-icon>
            </template>
            <span>
              <b>KARDEX Y COBROS</b>
            </span>
          </v-tooltip>
        </v-tab>
        <v-tab :href="`#tab-7`">
          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-icon v-if="icons" v-bind="attrs" v-on="on">mdi-comment-eye-outline</v-icon>
            </template>
            <span>
              <b>HISTORIAL DEL TRAMITE</b>
            </span>
          </v-tooltip>
        </v-tab>
        <v-tab-item :value="'tab-1'">
          <v-card flat tile>
            <v-card-text>
              <Dashboard :affiliate.sync="affiliate" :loan.sync="loan" :spouse.sync="spouse" />
            </v-card-text>
          </v-card>
        </v-tab-item>
        <v-tab-item :value="'tab-2'">
          <v-card flat tile>
            <v-card-title v-if="permissionSimpleSelected.includes('print-payment-plan')">
              <v-tooltip top>
                <template v-slot:activator="{ on }">
                  <v-btn
                    fab
                    x-small
                    color="dark"
                    top
                    right
                    absolute
                    v-on="on"
                    style="margin-right: -9px; margin-top: 38px;"
                    @click="imprimir($route.params.id)"
                  >
                    <v-icon>mdi-printer</v-icon>
                  </v-btn>
                </template>
                <div>
                  <span>Imprimir plan de pagos</span>
                </div>
              </v-tooltip>
              <!--FORMULARIO PARA CALIFICACION-->
            </v-card-title>
            <v-card-title v-if="permissionSimpleSelected.includes('print-qualification-form')">
              <v-tooltip top>
                <template v-slot:activator="{ on }">
                  <v-btn
                    fab
                    x-small
                    color="info"
                    top
                    right
                    absolute
                    v-on="on"
                    style="margin-right: -9px;margin-top: 78px;"
                    @click="printQualificationForm($route.params.id)"
                  >
                    <v-icon>mdi-printer-check</v-icon>
                  </v-btn>
                </template>
                <div>
                  <span>Imprimir Formulario de calificacion</span>
                </div>
              </v-tooltip>
            </v-card-title>
            <v-card-text class="pa-0">
              <SpecificDataLoan
                :loan.sync="loan"
                :loan_refinancing.sync="loan_refinancing"
                :loan_properties="loan_properties"
                :procedure_types="procedure_types"
              >
              </SpecificDataLoan>
            </v-card-text>
          </v-card>
        </v-tab-item>
        <v-tab-item :value="'tab-3'">
          <v-card flat tile>
            <v-card-text class="pl-12">
              <DocumentsFlow>
                <template v-slot:title>
                  <v-col cols="12" class>
                    <v-toolbar-title>DOCUMENTOS PRESENTADOS</v-toolbar-title>
                  </v-col>
                </template>
              </DocumentsFlow>
            </v-card-text>
          </v-card>
        </v-tab-item>
        <v-tab-item :value="'tab-4'">
          <v-card flat tile>
            <v-card-text class="pl-0 py-0">
              <Profile
                v-if="!reload"
                :affiliate.sync="affiliate"
                :addresses.sync="addresses"
                :editable.sync="editable"
                :permission="permission"
              />
            </v-card-text>
          </v-card>
        </v-tab-item>
        <!--<v-tab-item :value="'tab-5'">
          <v-card flat tile>
            <v-card-text class="py-0 pl-0">
              <PoliceData
                v-if="!reload"
                :affiliate.sync="affiliate"
                :editable.sync="editable"
                :permission="permission"
              />
            </v-card-text>
          </v-card>
        </v-tab-item>-->
        <v-tab-item :value="'tab-5'">
          <v-card flat tile>
            <v-card-text class="py-0 pl-0">
              <AdditionalInformation
                v-if="!reload"
                :affiliate.sync="affiliate"
                :addresses.sync="addresses"
                :editable.sync="editable"
                :permission="permission"
                :id_street.sync="id_street"
                :has_registered_spouse="has_registered_spouse"
              />
            </v-card-text>
          </v-card>
        </v-tab-item>

        <v-tab-item :value="'tab-6'">
          <v-card flat tile>
            <v-card-text class="pa-0 pl-3 pr-10 py-0">
              <Kardex
              :loan.sync="loan"
              :bus="bus"
              :affiliate.sync="affiliate" />
            </v-card-text>
          </v-card>
        </v-tab-item>

        <v-tab-item :value="'tab-7'">
          <v-card flat tile>
            <v-card-text class="pa-0 pl-3 pr-0 py-0">
              <ObserverFlow :loan.sync="loan" :observations.sync="observations" :bus1="bus1" />
            </v-card-text>
          </v-card>
        </v-tab-item>
      </v-tabs>
    </v-card-text>
    <AddObservation :bus="bus" :loan="loan" />
  </v-card>
</template>
<script>
import Breadcrumbs from "@/components/shared/Breadcrumbs"
import Profile from "@/components/affiliate/Profile"
import SpecificDataLoan from "@/components/loan/SpecificDataLoan"
import DocumentsFlow from "@/components/workflow/DocumentsFlow"
import ObserverFlow from "@/components/workflow/ObserverFlow"
import AddObservation from "@/components/workflow/AddObservation"
//import PoliceData from "@/components/affiliate/PoliceData"
import Dashboard from "@/components/workflow/Dashboard"
import Kardex from "@/components/payment/Kardex"
import AdditionalInformation from '@/components/affiliate/AdditionalInformation'

export default {
  name: "flow-index",
  components: {
    Breadcrumbs,
    Profile,
    SpecificDataLoan,
    DocumentsFlow,
    //PoliceData,
    ObserverFlow,
    AddObservation,
    Dashboard,
    Kardex,
    AdditionalInformation
  },
  data: () => ({
    bus: new Vue(),
    search: "",
    bus1: new Vue(), //Creamos la instancia de bus1
    addresses: [],
    affiliate: {
      first_name: null,
      second_name: null,
      last_name: null,
      mothers_last_name: null,
      identity_card: null,
      birth_date: null,
      date_death: null,
      reason_death: null,
      phone_number: null,
      cell_phone_number: null,
      city_identity_card_id: null,
      date_entry: null,
      service_years: null,
      service_months: null,
      date_derelict: null,
      unit_name: null,
      registration: null
    },
    bonos: [0, 0, 0, 0],
    payable_liquid: [0, 0, 0],
    modalidad: {},
    loan: {},
    loan_refinancing:{},
    datos: {},
    formulario: [],
    observations: [],
    spouse: {},
    loan_properties: {},
    procedure_types: {},
    intervalos: {},
    modalidad: {},
    icons: true,
    vertical: true,
    editable: false,
    reload: false,
    tab: "tab-1",
    validate: {
      valid_disbursement: false
    },
    role_name: null,
    user_name: null
  }),
  watch: {
    search: _.debounce(function() {
      this.bus.$emit("search", this.search)
    }, 1000)
  },
  computed: {
    //permisos del selector global por rol
    permissionSimpleSelected () {
      return this.$store.getters.permissionSimpleSelected
    },
    permission() {
      return {
        primary: this.primaryPermission,
        secondary: this.secondaryPermission
      }
    },
    secondaryPermission() {
      if (this.affiliate.id) {
        return this.permissionSimpleSelected.includes(
          "update-affiliate-secondary"
        )
      } else {
        return this.permissionSimpleSelected.includes("create-affiliate")
      }
    },
    primaryPermission() {
      if (this.affiliate.id) {
        return this.permissionSimpleSelected.includes(
          "update-affiliate-primary"
        )
      } else {
        return this.permissionSimpleSelected.includes("create-affiliate")
      }
    }
  },
  mounted() {
    // si existe el query de redireccion de tab, se setea el valor
    if(this.$route.query.redirectTab) {
      this.tab = 'tab-'+this.$route.query.redirectTab
    }
    /*if(this.$route.params.workTray){
      this.workTray = 'received'
    }*/
    this.getloan(this.$route.params.id)
    this.getSpouse(this.$route.params.id)
    this.getObservation(this.$route.params.id)
    this.getProceduretype(this.$route.params.id)
    
    this.bus1.$on("emitGetObservation", id => {
      //escuchamos la emision de ObserverFlow
      this.getObservation(id) //y monstramos la lista de observaciones segun el id del prestamo
    })
  },
  methods: {
    resetForm() {
      this.getAddress(this.affiliate.id)
      this.editable = false
      this.reload = true
      this.$nextTick(() => {
        this.reload = false
      })
    },
    setBreadcrumbs() {
      let breadcrumbs = [
        {
          text: "Préstamo",
          to: { name: "flowIndex" }
        }
      ]
      breadcrumbs.push({
        text: this.loan.code,
        to: { name: "flowAdd", params: { id: this.loan.id } }
      })
      this.$store.commit("setBreadcrumbs", breadcrumbs)
    },
    async getloan(id) {
      try {
        this.loading = true
        let res = await axios.get(`loan/${id}`)
        this.loan = res.data
        this.loan.amount_approved_before= res.data.amount_approved
        this.loan.loan_term_before= res.data.loan_term
        this.loan.disbursement_date=this.$moment(res.data.disbursement_date).format('YYYY-MM-DD')
        if(this.loan.parent_reason=='REFINANCIAMIENTO')
        {
          this.loan_refinancing.refinancing = true
          if(this.loan.parent_loan_id){

            this.loan_refinancing.code = this.loan.parent_loan.code
            this.loan_refinancing.amount_approved  = this.loan.parent_loan.amount_approved
            this.loan_refinancing.loan_term  = this.loan.parent_loan.loan_term
            this.loan_refinancing.balance  = this.loan.parent_loan.balance
            this.loan_refinancing.estimated_quota = this.loan.parent_loan.estimated_quota
            this.loan_refinancing.type_sismu = false
            this.loan_refinancing.description= 'PRESTAMO DEL PVT'
          }else{
            this.loan_refinancing.code = this.loan.data_loan.code
            this.loan_refinancing.amount_approved  = this.loan.data_loan.amount_approved
            this.loan_refinancing.loan_term  = this.loan.data_loan.loan_term
            this.loan_refinancing.balance  = this.loan.data_loan.balance
            this.loan_refinancing.type_sismu = true
            this.loan_refinancing.estimated_quota = this.loan.data_loan.estimated_quota
            this.loan_refinancing.description= 'PRESTAMO DEL SISMU'
           }
        }else{
           this.loan_refinancing.refinancing = false
        }
            this.loan_refinancing.date_cut_refinancing = this.loan.date_cut_refinancing
            this.loan_refinancing.balance_parent_loan_refinancing = this.loan.balance_parent_loan_refinancing
            this.loan_refinancing.amount_approved = this.loan.amount_approved
            this.loan_refinancing.refinancing_balance = this.loan.refinancing_balance



        console.log("este es el loan" + this.loan)
        let res1 = await axios.get(`affiliate/${this.loan.lenders[0].id}`)
        this.affiliate = res1.data
        if (this.loan.property_id != null) {
          this.getLoanproperty(this.loan.property_id)
        }
        this.getProceduretype(this.loan.procedure_modality_id)
        if (this.loan.disbursable_type == "spouses") {
          this.getSpouse(this.affiliate.id)
        }
        this.setBreadcrumbs()
        this.getAddress(this.affiliate.id)
    
        this.role(this.loan.role_id)
        this.user(this.loan.user_id)
        console.log(this.loan)
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
    async getSpouse(id) {
      try {
        this.loading = true
        let res = await axios.get(`affiliate/${id}/spouse`)
        this.spouse = res.data
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },

    async getLoanproperty(id) {
      try {
        this.loading = true
        let res = await axios.get(`loan_property/${id}`)
        this.loan_properties = res.data
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },

    async getProceduretype(id) {
      try {
        this.loading = true
        let res = await axios.get(`procedure_modality/${id}`)
        this.procedure_types = res.data
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },

    async getObservation(id) {
      try {
        this.loading = true
        let res = await axios.get(`loan/${id}/observation`)
        this.observations = res.data
        
        for (this.i = 0; this.i < this.observations.length; this.i++) {
           console.log("ww"+this.observations[this.i].user_id)
          let res1 = await axios.get(`user/${this.observations[this.i].user_id}`
          )
          this.observations[this.i].user_name = res1.data.username
        }
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
    async getAddress(id) {
      try {
        this.loading = true
        let res = await axios.get(`affiliate/${id}/address`)
        this.addresses = res.data
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
    async imprimir(item) {
      try {
        if(this.loan.disbursement_date!='Fecha invalida')
        {
          let res = await axios.get(`loan/${item}/print/plan`)
            printJS({
              printable: res.data.content,
              type: res.data.type,
              file_name: res.data.file_name,
              base64: true
            })
        }
        else{

          let res1 = await axios.patch(`loan/${this.loan.id}`, {
            date_signal:true
          })
          this.loan.disbursement_date= this.$moment(res1.data.disbursement_date).format('YYYY-MM-DD')
           let res = await axios.get(`loan/${item}/print/plan`)
          printJS({
            printable: res.data.content,
            type: res.data.type,
            file_name: res.data.file_name,
            base64: true
          })

        }
      } catch (e) {
        this.toastr.error("Ocurrió un error en la impresión.")
        console.log(e)
      }
    },
    async printQualificationForm(item) {
      try {
        let res = await axios.get(`loan/${item}/print/qualification`)
        console.log("plan " + item)
        printJS({
          printable: res.data.content,
          type: res.data.type,
          file_name: res.data.file_name,
          base64: true
        })
      } catch (e) {
        this.toastr.error("Ocurrió un error en la impresión.")
        console.log(e)
      }
    },
    async role(role_id){
      try {
        let res = await axios.get(`role/${role_id}`)
        this.role_name = res.data.display_name
        console.log(this.role_name)
      } catch (e) {
        console.log(e)
      }
    },
    async user(user_id){
      try {
        let res = await axios.get(`user/${user_id}`)
        this.user_name = res.data.username
        console.log(this.user_name)
      } catch (e) {
        console.log(e)
      }
    },
    validation(){
      //VALIDACION DESEMBOLSO
      if((this.loan.disbursement_date != 'Fecha invalida' ) ){
        this.validate.valid_disbursement = true
      }else{
        this.validate.valid_disbursement = false
      }
      /////
      if(this.permissionSimpleSelected.includes('disbursement-loan') == true && this.validate.valid_disbursement == true){
         this.bus.$emit('openDialog', { edit: false, accion: 'validar' })
         //alert("entro")
      }
      else if(this.permissionSimpleSelected.includes('disbursement-loan') == false){
         this.bus.$emit('openDialog', { edit: false, accion: 'validar' })
         //alert("entro 2")
      }
      else{
        this.toastr.error('Faltan registar el campo en Desembolso.')
      }

    }
  }
}
</script>
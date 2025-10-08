<template>
  <v-card flat>
    <v-card-title>
      <v-toolbar dense style="z-index: 1" color='tertiary'>

        <v-toolbar-title>
          <Breadcrumbs />
        </v-toolbar-title>

        <v-spacer></v-spacer>

          <!-- Este botón se muestra cuando un trámite de prestamos 
          se valida, ya sea por parte de plataforma, califación, revisión jefatura etc.
          Este botón esta en workflow (Bandeja de entrada de los modulos)-->
          <v-tooltip top>
            <template v-slot:activator="{ on }">
              <v-btn
                v-if="!loan.validated && role_name != 'Archivo'"
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
          
          <!-- Se muestra conjuntamente con el botón de validar -->
          <v-tooltip top>
            <template v-slot:activator="{ on }">
              <v-btn
                v-if="!loan.validated && role_name != 'Archivo'"
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

          <!-- Botón que se muestra solo en revisión jefatura -->
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

          <div v-if="loan.modality.procedure_type.second_name == 'Anticipo'">
          <v-tooltip top>
            <template v-slot:activator="{ on }">
              <v-btn
                top
                v-if="permissionSimpleSelected.includes('delete-loan-advance')"
                v-on="on"
                icon
                outlined
                small
                color="warning"
                class="darken-2 ml-4"
                @click="
                  bus.$emit('openDialog', { edit: false, accion: 'anular_anticipo' })
                "
              >
                <v-icon>mdi-file-cancel</v-icon>
              </v-btn>
            </template>
            <span>Anular trámite de anticipo</span>
          </v-tooltip>
          </div>
          <v-tooltip top v-if="permissionSimpleSelected.includes('send-notification-contract')">
              <template v-slot:activator="{ on }">
                <v-btn
                top
                v-on="on"
                icon
                small
                color="primary"
                class="darken-2 ml-4"
                  @click="dialog_notification=true"
                >
                  <v-icon class="x-small">mdi-message-text</v-icon>
                </v-btn>
              </template>
              <div>
                <span>Enviar noticación</span>
              </div>
            </v-tooltip>
            <v-dialog
              v-model="dialog_notification"
              max-width="500"
            >
              <v-card>
                <v-card-title>
                  ¿Está seguro del envio de noticacion al afiliado <br> para el recojo del contrato?
                </v-card-title>
                <v-card-actions>
                  <v-spacer></v-spacer>
                  <v-btn
                    color="red darken-1"
                    text
                    @click="dialog_notification = false"
                  >
                    Cancelar
                  </v-btn>
                  <v-btn
                    color="green darken-1"
                    text
                    @click.stop="sendContractNotificaction()"
                    :loading= loading_notify
                  >
                    Aceptar
                  </v-btn>
                </v-card-actions>
              </v-card>
            </v-dialog>
      </v-toolbar>
    </v-card-title>

    <!-- Aquí empieza el menú de tabs (menú negro) -->
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

        <!-- T A  B   -  1 -->
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

        <!-- T A B  -   2 -->
        <v-tab :href="`#tab-2`" v-if="this.$store.getters.rolePermissionSelected.display_name != 'Cobranzas'">
          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-icon v-if="icons" v-bind="attrs" v-on="on">mdi-file-eye</v-icon>
            </template>
            <span>
              <b>DATOS ESPECIFICOS DEL PRESTAMO</b>
            </span>
          </v-tooltip>
        </v-tab>

        <!-- T A B   -  3 -->
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

        <!-- T A B   -   4 -->
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

        <!-- T A B   -  5 -->
        <v-tab :href="`#tab-5`" v-if="borrower.type == 'spouses'">
          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-icon v-if="icons" v-bind="attrs" v-on="on">mdi-account-heart</v-icon>
            </template>
            <span>
              <b>INFORMACION CONYUGE</b>
            </span>
          </v-tooltip>
        </v-tab>

        <!-- T A B   -  6 -->
        <v-tab :href="`#tab-6`">
          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-icon v-if="icons" v-bind="attrs" v-on="on">mdi-file-account</v-icon>
            </template>
            <span>
              <b>INFORMACION ADICIONAL</b>
            </span>
          </v-tooltip>
        </v-tab>

        <!-- T A B   -  7 -->
        <v-tab :href="`#tab-7`">
          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-icon v-if="icons" v-bind="attrs" v-on="on">mdi-format-list-checkbox</v-icon>
            </template>
            <span>
              <b>KARDEX Y COBROS</b>
            </span>
          </v-tooltip>
        </v-tab>

        <!-- T A B   -  8 -->
        <v-tab :href="`#tab-8`">
          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-icon v-if="icons" v-bind="attrs" v-on="on">mdi-comment-eye-outline</v-icon>
            </template>
            <span>
              <b>SEGUIMIENTO, OBSERVACION E HISTORIAL DEL TRÁMITE</b>
            </span>
          </v-tooltip>
        </v-tab>

        <!--  I T E M   T A B  -  1 -->
        <v-tab-item :value="'tab-1'">
          <v-card flat tile>
            <v-card-text>
              <Dashboard 
              :affiliate.sync="affiliate"
              :borrower.sync="borrower"
              :loan.sync="loan" 
              :spouse.sync="spouse" />
            </v-card-text>
          </v-card>
        </v-tab-item>

        <!-- I T E M   T A B   - 2 -->
        <v-tab-item :value="'tab-2'">
          <v-card flat tile>
            <v-card-title  class="pa-0" v-if="permissionSimpleSelected.includes('print-payment-plan')">
               <v-tooltip top >
                <template v-slot:activator="{ on }">
                  <v-btn
                    v-show="loan.modality.procedure_type.second_name == 'Anticipo' && removeAccents(loan.disbursement_date) != 'Fecha invalida' "
                    fab
                    x-small
                    color="success"
                    top
                    right
                    absolute
                    v-on="on"
                    style="margin-right: 40px; margin-top: 25px;"
                    @click="imprimirRecive($route.params.id)"
                    >
                    <v-icon>mdi-badge-account-horizontal</v-icon>
                  </v-btn>
                </template>
                <div>
                  <span>Imprimir el Recibo</span>
                </div>
              </v-tooltip>
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
                    style="margin-top: 25px;"
                    @click="imprimir($route.params.id)"
                  >
                    <v-icon>mdi-printer</v-icon>
                  </v-btn>
                </template>
                <div>
                  <span>Generar plan de pagos</span>
                </div>
              </v-tooltip>
            </v-card-title><div>{{ loan.modality.shortened }}</div>
            <v-card-title class="pa-0" v-if="permissionSimpleSelected.includes('print-payment-plan-reprogramming')
            && (loan.modality.shortened || '').toUpperCase().startsWith('REP')">  
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
                    style="margin-top: 25px;"
                    @click="imprimir($route.params.id)"
                  >
                    <v-icon>mdi-printer</v-icon>
                  </v-btn>
                </template>
                <div>
                  <span>Generar el nuevo plan de pagos</span>
                </div>
              </v-tooltip>
            </v-card-title>

            <v-card-title class="pa-0" v-if="permissionSimpleSelected.includes('print-qualification-form')">
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
                    style="margin-top: 25px;"
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

              <v-dialog
                v-model="dialog_minutes"
                  width="500"
               >
                <v-card>
                  <v-card-title>
                    <span class="text-h5">Introduzca el Número de Sesión</span>
                  </v-card-title>

                  <v-card-text>
                    <v-container>
                      <v-row>
                        <v-col cols="12" sm="12" md="12">
                          <ValidationProvider v-slot="{ errors }" name="numero sesion" rules="numeric|min:1" mode="aggressive">
                            <v-text-field
                              label="Número de sesión"
                              :error-messages="errors"
                              v-model="number_session"
                            ></v-text-field> 
                          </ValidationProvider>
                        </v-col>
                      </v-row>
                    </v-container>
                  </v-card-text>

                  <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="error" text @click="dialog_minutes = false">
                      CANCELAR
                    </v-btn>
                    <v-btn v-if="number_session != '' && !isNaN(number_session)" color="success" text @click="printComitteeMinute($route.params.id)">
                      IMPRIMIR
                    </v-btn>
                  </v-card-actions>
 
                </v-card>
              </v-dialog>

            <v-card-title class="pa-0" v-if="permissionSimpleSelected.includes('print-comittee-minute')"> 
              <v-tooltip top class="pa-0">
         
                <template v-slot:activator="{ on, attrs }">
                   <v-btn
                      fab
                      x-small
                      color="success"
                      top
                      right
                      absolute
                      v-on="on"
                      class="pa-0"
                      v-bind="attrs"
                      style="margin-right: 36px; margin-top: 48px; "
                      @click="dialog_minutes = true; number_session = ''"
                   >
                    <v-icon>mdi-printer-check</v-icon>
                  </v-btn>

                </template>
                <div>
                  <span>Imprimir Acta de Sesión</span>
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

        <!-- I T E M  T A B  -  3 -->
        <v-tab-item :value="'tab-3'">
          <v-card flat tile>
            <v-card-text class="pl-12">
              <DocumentsFlow
                :docsRequired.sync="docsRequired"
                :docsOptional.sync="docsOptional"
                :val_docs.sync="val_docs"
              >
                <template v-slot:title>
                  <v-col cols="12" class>
                    <v-toolbar-title>DOCUMENTOS PRESENTADOS</v-toolbar-title>
                  </v-col>
                </template>
              </DocumentsFlow>
            </v-card-text>
          </v-card>
        </v-tab-item>

        <!-- I T E M  T A B   -  4 -->
        <v-tab-item :value="'tab-4'">
          <v-card flat tile>
            <v-card-text class="pl-0 py-0">
              <Profile
                v-if="!reload"
                :affiliate.sync="affiliate"
                :editable.sync="editable"
                :permission="permission"
              />
            </v-card-text>
          </v-card>
        </v-tab-item>

        <!-- I T E M  T A B   -  5-->
        <v-tab-item :value="'tab-5'">
          <v-card flat tile>
            <v-card-text class="py-0 pl-0">
              <Spouse
                v-if="!reload"
                state_id="4"
                :spouse.sync="spouse"
                :editable.sync="editable"
                :permission="permission"
              />
            </v-card-text>
          </v-card>
        </v-tab-item>

        <!-- I T E M  T A B   -  6 -->
        <v-tab-item :value="'tab-6'">
          <v-card flat tile>
            <v-card-text class="py-0 pl-0">
              <AdditionalInformation
                v-if="!reload"
                :affiliate.sync="affiliate"
                :addresses.sync="addresses"
                :editable.sync="editable"
                :permission="permission"
                :id_street.sync="id_street"
              />
            </v-card-text>
          </v-card>
        </v-tab-item>

        <!-- I T E M   T A B  - 7-->
        <v-tab-item :value="'tab-7'">
          <v-card flat tile>
            <v-card-text class="pa-0 pl-3 pr-10 py-0">

              <!-- C O M P O N E N T E -->
              <Kardex
              :loan.sync="loan"
              :bus="bus"
              :affiliate.sync="affiliate"
              :borrower.sync="borrower"
              :spouse.sync="spouse" />

            </v-card-text>
            <v-tooltip top v-if="permissionSimpleSelected.includes('create-payment-loan') && (loan.guarantors.length>0 && loan.guarantor_amortizing)">
              <template v-slot:activator="{ on }">
                <v-btn
                  fab
                  dark
                  x-small
                  :color="'error'"
                  top
                  left
                  absolute
                  v-on="on"
                  style="margin-left: 300px; margin-top: 20px"
                  @click="dialog_guarantor_lender=true"
                >
                  <v-icon>mdi-account-switch</v-icon>
                </v-btn>
              </template>
              <div>
                <span>Cambio de amortización a titular</span>
              </div>
            </v-tooltip>
            <v-dialog
              v-model="dialog_guarantor_lender"
              max-width="500"
            >
              <v-card>
                <v-card-title>
                  Esta seguro de cambiar la amortizacion al titular?
                </v-card-title>
                <v-card-actions>
                  <v-spacer></v-spacer>
                  <v-btn
                    color="red darken-1"
                    text
                    @click="dialog_guarantor_lender = false"
                  >
                    Cancelar
                  </v-btn>
                  <v-btn
                    color="green darken-1"
                    text
                    @click.stop="changeGuarantorLender()"
                  >
                    Aceptar
                  </v-btn>
                </v-card-actions>
              </v-card>
            </v-dialog>
                <!--CAMBIO DE PRESTATARIO A GARANTE-->
            <v-tooltip top v-if="permissionSimpleSelected.includes('create-payment-loan') && (loan.guarantors.length>0 && !loan.guarantor_amortizing)">
              <template v-slot:activator="{ on }">
                <v-btn
                  fab
                  dark
                  x-small
                  :color="'green'"
                  top
                  left
                  absolute
                  v-on="on"
                  style="margin-left: 300px; margin-top: 20px"
                  @click="dialog_guarantor_lender=true"
                >
                  <v-icon>mdi-account-switch</v-icon>
                </v-btn>
              </template>
              <div>
                <span>Cambio de amortización a garantes</span>
              </div>
            </v-tooltip>
            <v-dialog
              v-model="dialog_guarantor_lender"
              max-width="500"
            >
              <v-card>
                <v-card-title>
                  Esta seguro de cambiar la amortizacion a garantes?
                </v-card-title>
                <v-card-actions>
                  <v-spacer></v-spacer>
                  <v-btn
                    color="red darken-1"
                    text
                    @click="dialog_guarantor_lender = false"
                  >
                    Cancelar
                  </v-btn>
                  <v-btn
                    color="green darken-1"
                    text
                    @click.stop="changeGuarantorLender()"
                  >
                    Aceptar
                  </v-btn>
                </v-card-actions>
              </v-card>
            </v-dialog>
          </v-card>
        </v-tab-item>

        <!-- I T E M  T A B   8-->
        <v-tab-item :value="'tab-8'">
          <v-card flat tile>
            <v-card-text class="pa-0 pl-3 pr-10 py-0">
              <ObserverFlow
              :loan.sync="loan"
              :observations.sync="observations"
              :bus1="bus1"
              :affiliate.sync="affiliate"
              :borrower.sync="borrower" />
            </v-card-text>
          </v-card>
        </v-tab-item>

      </v-tabs>
    </v-card-text>

    <v-dialog
      v-model="dialog"
      max-width="500"
    >
      <v-card>
        <v-card-title>
          ¿Está seguro de habilitar el plan de pagos?
        </v-card-title>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn
            color="red darken-1"
            text
            @click="dialog = false"
          >
            Cancelar
          </v-btn>
          <v-btn
            color="green darken-1"
            text
            @click="generatePlan($route.params.id)"
            :loading="loading_btn_plan"
          >
            Aceptar
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <AddObservation :bus="bus" :loan="loan" />
  </v-card>
</template>
<script>
import Breadcrumbs from "@/components/shared/Breadcrumbs"
import Profile from "@/components/affiliate/Profile"
import SpecificDataLoan from "@/components/workflow/SpecificDataLoan"
import DocumentsFlow from "@/components/workflow/DocumentsFlow"
import ObserverFlow from "@/components/workflow/ObserverFlow"
import AddObservation from "@/components/workflow/AddObservation"
import Dashboard from "@/components/workflow/Dashboard"
import Kardex from "@/components/payment/Kardex"
import AdditionalInformation from '@/components/affiliate/AdditionalInformation'
import Spouse from "@/components/affiliate/Spouse"
import common from "@/plugins/common";

export default {
  name: "flow-index",
  components: {
    Breadcrumbs,
    Profile,
    SpecificDataLoan,
    DocumentsFlow,
    ObserverFlow,
    AddObservation,
    Dashboard,
    Kardex,
    AdditionalInformation,
    Spouse
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
    borrower: {
      first_name: null,
      second_name: null,
      last_name: null,
      mothers_last_name: null,
      //full_name: null,
      identity_card: null,
      registration: null,
      type: null,
      disbursable: null
    },
    dialog_minutes: false,
    number_session: '',
    dialog:false,
    bonos: [0, 0, 0, 0],
    payable_liquid: [0, 0, 0],
    modalidad: {},
    loan: {
      intereses: {},
      state: {},
      user:{},
      guarantors:[]
    },
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
      valid_disbursement: false,
      valid_date_contract : false,
      valid_date_contract_return: false,
      valid_certificate : false

    },
    role_name: null,
    user_name: null,
    id_street: 0,
    dialog_guarantor_lender:false,
    dialog_notification: false,
    loading_notify: false,
    loading_btn_plan: false,
    docsRequired: [],
    docsOptional: [],
    val_docs: {}
  }),
  watch: {
    search: _.debounce(function() {
      this.bus.$emit("search", this.search)
    }, 1000),
    dialog_guarantor_lender: function(newVal, oldVal){
      if(newVal!=oldVal){
        this.getloan(this.$route.params.id)
      }
    },
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

  created() {
    this.removeAccents = common.removeAccents
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
    this.getDocumentsSubmitted(this.$route.params.id)
    //this.getSpouse(this.$route.params.id)
    this.getObservation(this.$route.params.id)
    //this.getProceduretype(this.$route.params.id)

    this.bus1.$on("emitGetObservation", id => {
      //escuchamos la emision de ObserverFlow
      this.getObservation(id) //y monstramos la lista de observaciones segun el id del prestamo
    })
  },
  methods: {
    resetForm() {
      this.getAddress(this.loan.borrower.address_id)
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

        this.loan.amount_approved_aux = this.loan.amount_approved
        //this.loan.payable_liquid_calculated_aux = this.loan.borrower[0].payable_liquid_calculated
        this.loan.liquid_qualification_calculated_aux = this.loan.liquid_qualification_calculated
        this.loan.loan_term_aux = this.loan.loan_term
        this.loan.bonus_calculated_aux = this.loan.borrower[0].bonus_calculated
        this.loan.indebtedness_calculated_aux = this.loan.indebtedness_calculated
        this.loan.estimated_quota_aux = this.loan.estimated_quota


        this.loan.disbursement_date=this.$moment(res.data.disbursement_date).format('YYYY-MM-DD')
        this.loan.delivery_contract_date=this.$moment(res.data.delivery_contract_date).format('YYYY-MM-DD')
        this.loan.return_contract_date=this.$moment(res.data.return_contract_date).format('YYYY-MM-DD')
        this.loan.regional_delivery_contract_date=this.$moment(res.data.regional_delivery_contract_date).format('YYYY-MM-DD')
        this.loan.regional_return_contract_date=this.$moment(res.data.regional_return_contract_date).format('YYYY-MM-DD')

        if(this.loan.parent_reason=='REFINANCIAMIENTO')
        {
          this.loan_refinancing.refinancing = true
          if(this.loan.parent_loan_id){

            this.loan_refinancing.code = this.loan.parent_loan.code
            this.loan_refinancing.amount_approved_son  = this.loan.parent_loan.amount_approved
            this.loan_refinancing.loan_term  = this.loan.parent_loan.loan_term
            this.loan_refinancing.balance  = this.loan.parent_loan.balance
            this.loan_refinancing.estimated_quota = this.loan.parent_loan.estimated_quota
            this.loan_refinancing.disbursement_date = this.loan.parent_loan.disbursement_date
            this.loan_refinancing.type_sismu = false
            this.loan_refinancing.description= 'PRESTAMO DEL PVT'
          }else{
            this.loan_refinancing.code = this.loan.data_loan.code
            this.loan_refinancing.amount_approved_son  = this.loan.data_loan.amount_approved
            this.loan_refinancing.loan_term  = this.loan.data_loan.loan_term
            this.loan_refinancing.balance  = this.loan.data_loan.balance
            this.loan_refinancing.type_sismu = true
            this.loan_refinancing.estimated_quota = this.loan.data_loan.estimated_quota
            this.loan_refinancing.disbursement_date = this.loan.data_loan.disbursement_date
            this.loan_refinancing.description= 'PRESTAMO DEL SISMU'
           }
        }else{
           this.loan_refinancing.refinancing = false
        }
            this.loan_refinancing.date_cut_refinancing = this.loan.date_cut_refinancing
            this.loan_refinancing.balance_parent_loan_refinancing = this.loan.balance_parent_loan_refinancing
            this.loan_refinancing.amount_approved = this.loan.amount_approved
            this.loan_refinancing.refinancing_balance = this.loan.refinancing_balance

        let res1 = await axios.get(`affiliate/${this.loan.affiliate_id}`)
        this.affiliate = res1.data
        if (this.loan.property_id != null) {
          this.getLoanproperty(this.loan.property_id)
        }
        this.getProceduretype(this.loan.procedure_modality_id)
        this.borrower = this.loan.borrower[0]
        if (this.loan.borrower[0].type == "spouses") {
          this.getSpouse(this.affiliate.id)
        }
        this.setBreadcrumbs()
        this.getAddress(this.affiliate.id)
        this.wf_state(this.loan.wf_states_id)
        if(this.loan.user_id != null){
          this.user(this.loan.user_id)
        }
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
    async imprimirRecive(item) {
      try {
          let res = await axios.get(`print_fund_rotary_output/${item}`)
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
    async imprimir(item) {
      try {
        if(this.removeAccents(this.loan.disbursement_date)!='Fecha invalida')
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
            this.dialog=true
        }
      } catch (e) {
        this.toastr.error("Ocurrió un error en la impresión.")
      }
    },
     async generatePlan(item) {
      try {
            this.loading_btn_plan = true
            let res1 = await axios.patch(`loan/${this.loan.id}`, {
            date_signal:true,
            current_role_id: this.$store.getters.rolePermissionSelected.id
          })
          this.loan.disbursement_date= this.$moment(res1.data.disbursement_date).format('YYYY-MM-DD')
           let res = await axios.get(`loan/${item}/print/plan`)
           this.loading_btn_plan = false
          printJS({
            printable: res.data.content,
            type: res.data.type,
            file_name: res.data.file_name,
            base64: true
          })
          this.dialog=false
      } catch (e) {
        this.loading_btn_plan = false
        this.toastr.error(e.type[0] || "Ocurrió un error en la impresión.")
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
    async printComitteeMinute(id_loan) {
      try {
        this.dialog_minutes = false
        let res = await axios.post(`committee_session/${id_loan}`, {
            number_session: this.number_session
        })
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
    async wfState(wf_states_id){
      try {
        let res = await axios.get(`wf_state/${wf_states_id}`)
        this.wf_state_name = res.data.name
      } catch (e) {
        console.log(e)
      }
    },
    async user(user_id){
      try {
        let res = await axios.get(`user/${user_id}`)
        this.user_name = res.data.username
        //console.log(this.user_name)
      } catch (e) {
        console.log(e)
      }
    },
    validation(){
      //VALIDACION FECHA ENTREGA DE CONTRATO
     if(this.permissionSimpleSelected.includes('registration-delivery-return-contracts') == true)
      {
        if(this.removeAccents(this.loan.delivery_contract_date) != 'Fecha invalida'){
         this.validate.valid_date_contract = true
        }else{
           this.validate.valid_date_contract = false
        }

        if(this.removeAccents(this.loan.return_contract_date) != 'Fecha invalida'){
          this.validate.valid_date_contract_return = true
        }else{
          this.validate.valid_date_contract_return = false
        }

      }else if(this.permissionSimpleSelected.includes('disbursement-loan')==true)
      {
        if(this.removeAccents(this.loan.disbursement_date) != 'Fecha invalida'){
          this.validate.valid_disbursement = true
        }else{
          this.validate.valid_disbursement = false
        }
      }else if(this.permissionSimpleSelected.includes('update-accounting-voucher')==true)
      {
        if((this.loan.num_accounting_voucher != null ) ){
          this.validate.valid_certificate = true
        }else{
          this.validate.valid_certificate = false
        }
      }

      if(this.permissionSimpleSelected.includes('registration-delivery-return-contracts') == true )
      {
        if(this.validate.valid_date_contract == true && this.validate.valid_date_contract_return == true)
        {
          if(this.permissionSimpleSelected.includes('update-accounting-voucher')==true)
          {
            if(this.validate.valid_certificate == true)
            {
              if(this.permissionSimpleSelected.includes('disbursement-loan')==true)
              {
                if(this.validate.valid_disbursement == true)
                {
                  this.bus.$emit('openDialog', { edit: false, accion: 'validar' })
                }
                else
                {
                  this.toastr.error('Falta generar el plan de pago.')
                }
              }else{
                this.bus.$emit('openDialog', { edit: false, accion: 'validar' })
              }
            }else
            {
              this.toastr.error('Faltan registar el número presupuestario.')
            }
          }else{
            this.bus.$emit('openDialog', { edit: false, accion: 'validar' })
          }
        }else{
            this.toastr.error('Falta registar la fecha de entrega y/o la fecha de devolución del contrato.')
        }
      }else{
        if(this.permissionSimpleSelected.includes('update-accounting-voucher')==true)
        {
          if(this.validate.valid_certificate == true)
          {
            if(this.permissionSimpleSelected.includes('disbursement-loan')==true)
            {
              if(this.validate.valid_disbursement == true)
              {
                this.bus.$emit('openDialog', { edit: false, accion: 'validar' })
              }else
              {
                this.toastr.error('Falta generar el plan de pago.')
              }
            }
            else
            {
              this.bus.$emit('openDialog', { edit: false, accion: 'validar' })
            }
          }
          else
          {
            this.toastr.error('Falta registrar el número de presupuesto.')
          }
        }else
        {
          if(this.permissionSimpleSelected.includes('disbursement-loan')==true)
          {
            if(this.validate.valid_disbursement == true)
            {
              this.bus.$emit('openDialog', { edit: false, accion: 'validar' })
            }else
            {
              this.toastr.error('Falta generar el plan de pago.')
            }
          }
          else
          {
            if(this.permissionSimpleSelected.includes('validate-submitted-documents')==true && this.loan.modality.procedure_type.second_name == 'Anticipo'){
              console.log(this.val_docs.valid)
              if(this.val_docs.valid){ 
                console.log("es Calific y validado"+this.val_docs.valid)
                this.bus.$emit('openDialog', { edit: false, accion: 'validar' })           

              }else{ 
                console.log(this.val_docs.valid +"es Calific y no valido"+ this.loan.modality.procedure_type.second_name)
                this.toastr.error('Existen documentos sin validar.')
              }
            }else{
              console.log("no es CAlific o no correponde")
              this.bus.$emit('openDialog', { edit: false, accion: 'validar' })
            }
          }
        }
      }
    },

    async getAddress(id) {
      try {
        this.loading = true
        let res = await axios.get(`affiliate/${id}/address`)
        this.addresses = res.data
        // Seteando el valor del address
        let address = this.addresses.find(item => item.pivot.validated)
        this.id_street = address.id
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },

    async changeGuarantorLender(){
      try {
        let res = await axios.post(`switch_guarantor_lender`,{
            loan_id: this.$route.params.id,
            role_id: this.$store.getters.rolePermissionSelected.id
        })
        this.dialog_guarantor_lender = false
        this.toastr.success(res.data.validate)
      } catch (e) {
        this.toastr.error(res.data.validate)
        console.log(e)
      }
    },

    async sendContractNotificaction(){
      try {
        this.loading_notify= true
        let res = await axios.post(`send_contract`,{
            loan_id: this.$route.params.id,
            user_id: this.$store.getters.id
        })
        this.loading_notify= false
        this.dialog_notification = false
        if(!res.data.error){
          this.toastr.success(res.data.message)
        }else{
          this.toastr.error(res.data.message)
        }
      } catch (e) {
        this.loading_notify= false
        this.dialog_notification = false
        this.toastr.error(e)
        console.log(e)
      }
    },
    async getDocumentsSubmitted(id) {
      try {
        this.loading = true
        let res = await axios.get(`loan/${id}/document`)
        this.docsRequired = res.data.required
        this.docsOptional = res.data.optional
        this.val_docs.valid = res.data.validated
        console.log(this.val_docs.valid)
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
   },
  }

</script>

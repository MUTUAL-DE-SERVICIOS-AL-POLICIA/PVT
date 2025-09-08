<template>
  <v-container >
    <v-card>
      <v-card-text >
        <v-row justify="center" class="ma-0 pb-0">
          <v-col cols="12" class="ma-0 pb-0">
            <v-container class="py-0">
              <v-col cols="12" md="12">
                <v-layout row wrap>
                  <v-flex xs12 class="px-2" >
                    <fieldset class="pa-2">

                      <center>
                       <v-toolbar-title>{{loan.modality.name}}</v-toolbar-title>
                      </center>

                      <v-progress-linear></v-progress-linear>

                      <template>
                        <v-row>

                          <v-col cols="3" class="ma-0 py-2">
                            <label><b>Nro Prestamos:</b></label>
                             {{loan.code}}
                          </v-col>

                          <v-col cols="3" class="ma-0 py-2" v-show="isNew">
                            <label><b>Fecha de Desembolso:</b></label>
                            {{loan.disbursement_date | date}}
                          </v-col>

                          <v-col cols="3" class="ma-0 py-2" v-show="!isNew">
                            <label><b>Fecha de Desembolso:</b></label>
                            {{loan.disbursement_date }}
                          </v-col>

                          <v-col cols="3" class="ma-0 py-2">
                            <label><b>Monto Desembolsado:</b></label>
                            {{ loan.amount_approved | moneyString}}
                          </v-col>

                          <v-col cols="3" class="ma-0 py-2">
                            <label><b>Plazo :</b></label>
                              {{ loan.loan_term +' Meses'}}
                          </v-col>

                          <v-col cols="12" md="12" class="py-0" v-show="isNew">
                          <ul style="list-style: none" class="py-0" >
                            <li v-for="borrower in loan.borrower" :key="borrower.id">
                              <v-col cols="12" md="12" class="pa-0">
                                <v-row class="pa-0">
                                   <v-col cols="12" md="4" class="py-0">
                                    <p><b>NOMBRE:</b> {{$options.filters.fullName(borrower, true)}}</p>
                                  </v-col>

                                  <v-col cols="12" md="4" class="py-0">
                                    <p><b>CÉDULA DE IDENTIDAD:</b> {{borrower.identity_card}}</p>
                                  </v-col>

                                  <v-col cols="12" md="4" class="py-0">
                                    <p><b>ESTADO:</b> {{borrower.state.name}}</p>
                                  </v-col>

                                  <v-col cols="12" md="12" class="py-0">
                                    <p><b>DIRECCION:</b> {{borrower.address==null? "No cuenta con una direccion registrada" : borrower.address.description}}</p>
                                  </v-col>
                                </v-row>
                              </v-col>
                            </li>
                          </ul>
                          </v-col>

                          <v-col cols="12" md="12" class="py-0" v-show="!isNew">
                            <p style="color:teal"><b>PRESTATARIO.-</b></p>
                            <ul style="list-style: none" class="py-0" >
                            <li v-for="borrower_detail in loan.borrower_detail" :key="borrower_detail.id">
                              <v-col cols="12" md="12" class="pa-0">
                                <v-row class="pa-0">
                                   <v-col cols="12" md="7" class="py-0">
                                    <p><b>NOMBRE:</b> {{borrower_detail.full_name_borrower}}</p>
                                  </v-col>

                                  <v-col cols="12" md="5" class="py-0">
                                    <p><b>CÉDULA DE IDENTIDAD:</b> {{borrower_detail.identity_card_borrower}}</p>
                                  </v-col>
                                </v-row>
                              </v-col>
                            </li>
                           </ul>
                          </v-col>

                          <v-progress-linear></v-progress-linear>

                          <v-col cols="12" class="py-0" v-show="isNew" v-if="last_payment" >
                            <center>
                              <v-toolbar-title>DATOS DEL PAGO ANTERIOR</v-toolbar-title>
                            </center>
                          </v-col>

                          <v-progress-linear v-show="isNew" v-if="last_payment"></v-progress-linear>

                          <v-col cols="3" class="ma-0 py-2"  v-show="isNew" v-if="last_payment">
                            <label><b style="color:teal" >Saldo Capital:</b></label>
                            <b style="color:teal">{{loan.balance | moneyString}}</b>
                          </v-col>

                          <v-col cols="3" class="ma-0 py-2" v-show="isNew" v-if="last_payment">
                            <label><b style="color:teal">Número de Cuota:</b></label>
                            <b style="color:teal">{{(loan.last_payment_validated.quota_number+1)  }}</b>
                          </v-col>

                          <v-col cols="3" class="ma-0 py-2" v-show="isNew" v-if="last_payment">
                            <label><b style="color:teal">Fecha del ultimo Pago:</b></label>
                            <b style="color:teal">{{loan.last_payment_validated.estimated_date | date }}</b>
                          </v-col>

                          <v-col cols="3" class="ma-0 py-2" v-show="isNew" v-if="last_payment">
                            <label><b style="color:teal" >Total Pagado:</b></label>
                            <b style="color:teal">{{loan.last_payment_validated.estimated_quota | moneyString}}</b>
                          </v-col>

                          <v-col cols="6" class="ma-0 py-2" v-show="isNew" v-if="last_payment">
                            <label><b>Intereses Corrientes Pendientes:</b></label>
                            {{loan.last_payment_validated.interest_accumulated}}
                          </v-col>

                          <v-col cols="6" class="ma-0 py-2" v-show="isNew" v-if="last_payment">
                            <label><b>Interes Penales Pendientes:</b></label>
                            {{loan.last_payment_validated.penal_accumulated}}
                          </v-col>

                          <v-progress-linear></v-progress-linear>

                          <v-col cols="9"  v-show="edit" v-if="permissionSimpleSelected.includes('create-payment-loan') && this.data_payment.validar">
                          </v-col>

                          <v-col cols="3" class="ma-0 py-0" v-show="permissionSimpleSelected.includes('create-payment-loan') && this.data_payment.validar" v-if="edit">
                            <v-checkbox class="ma-0 py-3"
                              :outlined="edit"
                              :readonly="!edit"
                              :disabled="show "
                              v-model="data_payment.validated"
                              label="Validar Pago"
                            ></v-checkbox>
                          </v-col>

                          <v-col cols="4" class="ma-0 pb-0"  >
                            <v-select
                              dense
                              class="caption"
                              style="font-size: 10px;"
                              @change="Onchange()"
                              v-model="data_payment.procedure_id"
                              :outlined="isNew"
                              :readonly="!isNew"
                              :items="type_procedure"
                              item-text="name"
                              item-value="id"
                              label='Tipo de tramite'
                              :disabled="show || edit"
                            ></v-select>
                          </v-col>

                          <v-col cols="3" class="ma-0 pb-0" v-show="isNew">
                            <v-select
                              dense
                              class="caption"
                              style="font-size: 10px;"
                              v-model="data_payment.procedure_modality_id"
                              :outlined="!edit"
                              :readonly="edit"
                              :items="type_amortizacion"
                              item-text="name"
                              item-value="id"
                              label="Tipo de Amortizacion"
                              persistent-hint
                            ></v-select>
                          </v-col>

                          <v-col cols="3" class="ma-0 pb-0">
                            <v-select
                              dense
                              class="caption"
                              style="font-size: 10px;"
                              :Onchange="OnchangeAffiliate()"
                              v-model="data_payment.affiliate_id"
                              :outlined="isNew"
                              :readonly="!isNew"
                              :items="type_affiliate"
                              item-text="name"
                              item-value="id"
                              label="Pago del "
                              persistent-hint
                              :disabled="show || edit"
                            ></v-select>
                          </v-col>

                          <v-col cols="2" class="ma-0 pb-0" v-show="permissionSimpleSelected.includes('create-payment-loan') && isNew" >
                            <v-text-field
                            v-show="permissionSimpleSelected.includes('create-payment-loan')"
                              dense
                              v-model="code_initials"
                              label="Codigo "
                              :disabled="true"
                              :readonly="true"
                            ></v-text-field>
                          </v-col>

                          <v-col cols="3" class="ma-0 pb-0" v-show="guarantor_show">
                              <ul style="list-style: none" class="pa-0" >
                                 <li v-for="loan in loan.borrowerguarantors" :key="loan.id" class="mb-4">
                                   {{$options.filters.fullName(loan, true)}}
                                   <br>
                                 </li>
                              </ul>
                          </v-col>

                          <v-col cols="1" class="my-0 pb-0" v-show="guarantor_show">
                            <ul style="list-style: none" class="pa-0 my-0" >
                               <li v-for="loan in loan.borrowerguarantors" :key="loan.id" class="my-0">
                                  <v-radio-group  v-model="radios" class="py-0 my-0">
                                    <v-radio
                                      color="info"
                                       :click="generateGuarantorCode()"
                                      :value="loan.id"
                                      class="py-0  my-0"
                                    ></v-radio>
                                  </v-radio-group>
                               </li>
                            </ul>
                          </v-col>

                          <v-col cols="4" class="ma-0 pb-0" v-show="permissionSimpleSelected.includes('create-payment-loan')" >
                            <v-text-field
                              dense
                              v-model="data_payment.voucher"
                              label="Codigo de Comprobante"
                              :outlined=" isNew || edit "
                              :readonly="show"
                              :disabled="show"
                            ></v-text-field>
                          </v-col>

                          <v-col cols="4" class="ma-0 pb-0" v-show="permissionSimpleSelected.includes('create-payment-loan')" v-if="!isNew">
                             <v-text-field
                               v-model="data_payment.code"
                               :readonly="true"
                              :disabled="true"
                              dense
                              label="Codigo"
                            ></v-text-field>
                          </v-col>

                          <v-col cols="4" class="ma-0 pb-0" v-show="permissionSimpleSelected.includes('create-payment-loan')" v-if="!isNew" >
                             <v-text-field
                              v-model="data_payment.quota_number"
                              :readonly="true"
                              :disabled="true"
                              dense
                              label="Nro.Cuota"
                            ></v-text-field>
                          </v-col>

                          <v-col cols="4">
                            <v-text-field
                              dense
                              v-model="data_payment.payment_date"
                              hint="Día/Mes/Año"
                              class="purple-input"
                              type="date"
                              label="Fecha de Calculo"
                              :clearable="edit"
                              :outlined="isNew"
                              :readonly="!isNew"
                              :disabled="show || edit"
                              @change="checkPaymentDate"
                            ></v-text-field>
                          </v-col>

                          <v-col cols="4" class="ma-0 pb-0" v-show="isNew">
                            <v-select
                              dense
                              class="caption"
                              style="font-size: 10px;"
                              :Onchange="OnchangeAffiliate()"
                              v-model="data_payment.categori_id"
                              :outlined="isNew"
                              :readonly="!isNew"
                              :items="type_category"
                              item-text="name"
                              item-value="id"
                              label="Categoria "
                              persistent-hint
                              :disabled="show || edit"
                            ></v-select>
                          </v-col>

                          <v-col cols="3" class="ma-0 pb-0" v-show="permissionSimpleSelected.includes('create-payment-loan') && isNew" >
                            <v-select
                              class="caption"
                              style="font-size: 10px;"
                              dense
                              v-model="data_payment.pago"
                              :outlined="isNew"
                              :readonly="!isNew"
                              :items="payment_types"
                              item-text="name"
                              item-value="id"
                              label="Tipo de Pago"
                              persistent-hint
                              @change="OnchangeAmortization()"
                              :disabled="show || edit"
                            ></v-select>
                          </v-col>

                          <v-col cols="4" class="ma-0 pb-0" >
                            <v-text-field
                              dense
                              v-model="data_payment.pago_total"
                              label="Total Pagado"
                              :outlined="isNew"
                              :readonly="liquidate_disable"
                              :disabled="liquidate_disable"
                            ></v-text-field>
                          </v-col>

                          <v-col cols="4" v-show="edit" v-if="permissionSimpleSelected.includes('create-payment')" >
                            <v-text-field
                              v-model="data_payment.voucher_amount_total"
                              :outlined="edit"
                              :readonly="!edit"
                              label="Total Pagado en Tesoreria"
                              dense
                            ></v-text-field>
                          </v-col>

                          <!-- <v-col cols="4" class="ma-0 pb-0" v-show="edit" v-if="permissionSimpleSelected.includes('create-payment')">
                            <v-select
                              class="caption"
                              style="font-size: 10px;"
                              dense
                              v-model="data_payment.tipo_pago"
                              :outlined="edit"
                              :readonly="!edit"
                              :items="payment_type_treasury"
                              item-text="name"
                              item-value="id"
                              label="Tipo de Pago"
                              persistent-hint
                            ></v-select>
                          </v-col> -->

                          <v-col cols="4" v-show="permissionSimpleSelected.includes('create-payment')">
                            <v-text-field
                              dense
                              v-model="data_payment.voucher_date"
                              hint="Día/Mes/Año"
                              class="purple-input"
                              type="date"
                              label="Fecha"
                              :readonly="true"
                              :disabled="true"
                            ></v-text-field>
                          </v-col>

                          <v-col cols="4" class="ma-0 pb-0" v-show="permissionSimpleSelected.includes('create-payment')">
                            <v-text-field
                              v-if="!show"
                              v-model="data_payment.glosa_voucher"
                              :outlined="edit"
                              :readonly="!edit"
                              dense
                              label="Glosa"
                            ></v-text-field>
                          </v-col>

                          <v-col cols="5" class="ma-0 pb-0" v-show="permissionSimpleSelected.includes('create-payment-loan')">
                            <v-text-field
                              v-show="isNew || edit" v-if="!show"
                              v-model="data_payment.glosa"
                              :outlined=" isNew || edit "
                              :readonly="show"
                              dense
                              label="Glosa"
                            ></v-text-field>
                          </v-col>

                          <v-col cols="8" v-show="permissionSimpleSelected.includes('create-payment-loan')">
                          </v-col>

                          <v-col cols="10" v-show="isNew" class="py-0">
                          </v-col>

                          <v-col cols="2" v-show="isNew" class="py-0">
                            <v-btn
                              color="info"
                              @click="validatedStepOne()" v-show="!show">
                              Calcular
                            </v-btn>
                          </v-col>

                          <v-expand-transition>
                            <AddPayment
                              v-show="payment_detail.show_detail_payment"
                              :payment_detail.sync="payment_detail"/>
                          </v-expand-transition>

                        </v-row>
                      </template>
                    </fieldset>
                  </v-flex>
                </v-layout>
              </v-col>
            </v-container>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>
  </v-container>
</template>
<script>
import AddPayment from '@/components/payment/AddPayment'

export default {
  name: "add-amortization",

  props: {
    data_payment: {
      type: Object,
      required: true
    },
    payment: {
      type: Object,
      required: true
    }
  },
  components: {
    AddPayment
  },

  data: () => ({
    //Variables que ayudan a la visualizacion
    guarantor_show: false, //Variable que hace visible datos informativos cuando el prestamo tiene garante
    last_payment:false, //Variable que hace visible datos informativos del ultimo pago
    borrower_show:true, //Variable que hace visible datos informativos del tittular
    //Variables
    payment_detail: {
      show_detail_payment:false,
      estimated_days:{
        penal:null,
        current:null
      }
    },
    // loan: {},
    radios:[],
    type_procedure: [],
    loan:{
      affiliate:{},
      //  lenders:[],
      borrower_detail:[],
      last_payment_validated:{},
      modality:{},
      borrower: [
        {
          city_identity_card: {},
          state: {},
          address: {},

        }
      ]
    },
    type_amortizacion: [],
    type_affiliate:[],
    type_category:[],
    code_initials:null,
    loan_payment:{},
    payment_types:[
      {
        value:1,
        name:"Liquidar"
      },
      {
        value:0,
        name:"Regular"
      }
    ],
    voucher_type:[],
    payment_type_treasury:[],
    dates: {
      paymentDate:{
        formatted: null,
        picker: false,
      }
    },
    liquidate_disable:false
  }),
  computed: {
    //Metodo para obtener Permisos por rol
    permissionSimpleSelected () {
      return this.$store.getters.permissionSimpleSelected
    },
    isNew() {
      return  this.$route.params.hash == 'new'
    },
    edit(){
      return  this.$route.params.hash == 'edit'
    },
    show(){
      return  this.$route.params.hash == 'view'
    },
  },
  beforeMount(){
      if(this.$route.params.hash == 'edit')
      {
        this.getLoanPayment(this.$route.query.loan_payment)
      }
      if(this.$route.params.hash == 'view')
      {
        this.getLoanPayment(this.$route.query.loan_payment)
      }
      this.getTypeProcedure()
      this.getPymentTypes()
      this.getVoucherTypes()
      this.getCategori()
      if(this.isNew)
      {
        this.getLoan(this.$route.query.loan_id)
      }
      if(this.$route.params.hash == 'view')
      {
        this.formatDate('paymentDate',this.data_payment.payment_date)
      }
      if(this.$route.params.hash == 'edit')
      {
        this.formatDate('paymentDate',this.data_payment.payment_date)
      }
  },
   mounted(){
    this.$forceUpdate()
  },
   watch: {
    'data_payment.payment_date': function(date) {
      this.formatDate('paymentDate', date);
      this.data_payment.pago=''
    },
    'data_payment.affiliate_id':function(){
      this.data_payment.pago=''
    },
    data_payment:{
      deep:true,
      handler(){
        this.payment_detail.show_detail_payment=false;
        this.$emit("isCalculate",true);
      }
    }
  },
  methods: {
    checkPaymentDate() {
      const selectedDate = new Date(this.data_payment.payment_date);
      const currentDate = new Date();
      const limitDate = new Date();
      limitDate.setMonth(limitDate.getMonth() + 2);
      if (selectedDate > limitDate) {
        this.toastr.warning('La fecha seleccionada es más de dos meses posterior a la fecha actual.')
      }
    },
    //Metodo para sacar todos los tipo de pago
    async OnchangeAmortization(){
      try {
        if(this.data_payment.affiliate_id=="G"){
          let res = await axios.get(`get_amount_payment`,{
            params:{
              loan: this.$route.query.loan_id,
              loan_payment_date: this.$moment(this.data_payment.payment_date).format('DD-MM-YYYY'),
              liquidate: this.data_payment.pago == 'Liquidar' ? 1 : 0,
              type: 'G'
            }
          })
          this.data_payment.pago_total=res.data.suggested_amount
        }else{
          let res = await axios.get(`get_amount_payment`,{
            params:{
              loan: this.$route.query.loan_id,
              loan_payment_date: this.$moment(this.data_payment.payment_date).format('DD-MM-YYYY'),
              liquidate: this.data_payment.pago == 'Liquidar' ? 1 : 0,
              type: 'T'
            }
          })
          this.data_payment.pago_total=res.data.suggested_amount
        }
        this.liquidate_disable = this.data_payment.pago == 'Liquidar' ? true : false
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
    //Metodo para escoger quien hace el tipo de pago "Titular o Garante"
    OnchangeAffiliate(){
      console.log(this.data_payment)
      if(this.data_payment.affiliate_id=="G")
      {
        this.guarantor_show= true
        this.borrower_show=false
      }
      else{
        this.guarantor_show= false
        this.borrower_show=true
          for (let i = 0; i<  this.loan.borrower.length; i++) {
            this.data_payment.affiliate_id_paid_by=this.loan.affiliate_id
            this.code_initials = "T-" + this.loan.borrower[i].initials
          }
      }
    },
    //Metodo que genera el codigo del garante
    generateGuarantorCode()
    {
      if(this.data_payment.affiliate_id=='G')
      {
        for (let i = 0; i<  this.loan.borrowerguarantors.length; i++) {
          if(this.loan.borrowerguarantors[i].id==this.radios)
          {
            this.data_payment.affiliate_id_paid_by=this.loan.borrowerguarantors[i].affiliate_id
            this.code_initials = this.loan.borrowerguarantors[i].type_initials
          }
        }
      }else{
        this.data_payment.voucher=this.data_payment.voucher
      }
    },
      //Metodo para sacar datos del pago
     async getLoanPayment(id) {
      try {
        this.loading = true
        let res = await axios.get(`loan_payment/${id}`)
        this.loan_payment = res.data
        //this.loan.lenders=[this.loan_payment.lenders]

        this.loan.borrower_detail=this.loan_payment.borrower
        this.loan.code=this.loan_payment.loan.code
        this.loan.disbursement_date=this.$moment(this.loan_payment.loan.disbursement_date).format("DD-MM-YYYY")
        this.loan.amount_approved=this.loan_payment.loan.amount_approved
        this.loan.loan_term=this.loan_payment.loan.loan_term
        this.loan.estimated_quota=0
        this.loan.balance=0
        this.loan.last_payment_validated.previous_payment_date=0
        this.loan.last_payment_validated.estimated_date=0
        this.loan.modality.name = res.data.modality.name

        this.data_payment.code=this.loan_payment.code
        this.data_payment.payment_date= this.loan_payment.estimated_date
        this.data_payment.pago_total=this.loan_payment.estimated_quota
        this.data_payment.affiliate_id =this.loan_payment.paid_by
        this.data_payment.voucher=this.loan_payment.voucher
        this.data_payment.pago  =this.loan_payment.amortization_type_id
        this.data_payment.loan_id  =this.loan_payment.loan_id
        this.data_payment.validated =this.loan_payment.validated
        this.data_payment.glosa =this.loan_payment.description
        this.data_payment.procedure_modality_name =this.loan_payment.modality.procedure_type.name
        this.data_payment.procedure_id= this.loan_payment.modality.procedure_type_id
        this.data_payment.amortization=2
        this.data_payment.quota_number=this.loan_payment.quota_number
        this.data_payment.estimated_quota=this.loan_payment.estimated_quota
        this.data_payment.code=this.loan_payment.code
        this.data_payment.quota_number=this.loan_payment.quota_number

        this.data_payment.voucher_amount_total = this.loan_payment.estimated_quota
        this.data_payment.comprobante = isNaN(this.loan_payment.voucher) ? '' : this.loan_payment.voucher;
        if(
            this.data_payment.procedure_modality_name == 'Amortización por Ajuste Contable' ||
            this.data_payment.procedure_modality_name == 'Amortización Automática' ||
            this.data_payment.procedure_modality_name == 'Amortización Deposito Bancario' ||
            this.data_payment.procedure_modality_name == 'Amortización por Beneficios')
          {
            this.data_payment.validar =true
          }else{
            if(this.data_payment.procedure_modality_name == 'Amortización Directa')
            {
              this.data_payment.validar =false
            }
          }
           this.type_affiliate.push(
              {
                name:"Titular",
                id:"T"
              },
              {
                name:"Garante",
                id:"G"
              })
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
    //Metodo para cargar los tipos de amortizaciones de acuerdo al tipo de tramite
    Onchange(){
      if(this.data_payment.procedure_id!=null)
      {
         this.getTypeAmortization(this.data_payment.procedure_id)
      }
    },
    //Metodo para sacar el modulo y los tipos de tramite
    async getTypeProcedure() {
      try {
        this.loading = true
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
        let res = await axios.get(`module/${this.modulo}/amortization_loan`)
        this.type_procedure = res.data
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
    //Metodo para sacar las amortizaciones
    async getTypeAmortization(id) {
      try {
        this.data_payment.procedure_modality_id='';
        this.loading = true
        let res = await axios.get(`procedure_type/${id}/modality`)
        this.type_amortizacion = res.data
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
    //Metodo para sacar la categoria del tramite
     async getCategori() {
      try {
        this.loading = true
        let res = await axios.get(`get_categorie_user`)
        this.type_category = res.data
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
    //Metodo para sacar los tipos de pagos
    async getPymentTypes() {
      try {
        this.loading = true
        let res = await axios.get(`voucher_type`)
        this.payment_type_treasury = res.data
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
    //Validaciones del paso 1
     async validatedStepOne() {
      try {
            if(this.data_payment.procedure_id)
            {
              if(this.data_payment.procedure_modality_id)
              {
                if(this.data_payment.affiliate_id)
                {
                  if(this.data_payment.pago)
                  {
                    if(this.data_payment.categori_id)
                    {
                      if(this.data_payment.pago_total)
                      {
                        this.Calcular(this.$route.query.loan_id)
                      }else{
                        this.toastr.error('Debe introducir el total pagado')
                      }
                    }
                    else{
                      this.toastr.error('Debe introducir a que categoria pertenece la amortizacion')
                    }
                  }
                  else{
                      this.toastr.error('Debe introducir el tipo de pago')
                  }
                }else{
                  this.toastr.error('Debe seleccionar quien realiza el pago')
                }
              }else{
                this.toastr.error('Debe seleccionar el tipo de amortizacion')
              }
            }
            else{
              this.toastr.error('Debe seleccionar el tipo de tramite')
          }
      }catch (e) {
        console.log(e)
      }finally {
        this.loading = false
      }},
    //Formateo de fechas
    formatDate(key, date) {
      if (date) {
        this.dates[key].formatted = this.$moment(date).format('L')
      } else {
        this.dates[key].formatted = null
      }
    },
    //Metodo para sacar la siguiente cuota
    async Calcular(id) {
    try {
      let res_duplicity = await axios.get(`get_duplicity_account`,{
          params: {
            voucher: this.data_payment.voucher,
            date: this.data_payment.payment_date,
            procedure_modality_id: this.data_payment.procedure_modality_id,
          }
        })
      console.log(res_duplicity.data)
      if(res_duplicity.data == 0)
      {
        let res = await axios.patch(`loan/${id}/payment`,{
          affiliate_id:this.data_payment.affiliate_id_paid_by,
          estimated_date:this.data_payment.payment_date,
          estimated_quota:this.data_payment.pago_total,
          liquidate : this.data_payment.liquidate,
          procedure_modality_id:this.data_payment.procedure_modality_id,
          categorie_id :this.data_payment.categori_id,
        })
        console.log(this.data_payment)
          this.payment_detail = res.data
          this.payment_detail.show_detail_payment=true
          this.payment_detail.now_date= new Date().toISOString().substr(0, 10),
          this.$forceUpdate()
          this.$emit("isCalculate",false);
      }else{
        this.toastr.error("Ya existe un deposito bancario con ese número de comprobante")
      }
      }catch (e) {
        console.log(e)
      }finally {
        this.loading = false
      }
    },
    //Metodo para sacar los tipos de voucher
     async getVoucherTypes() {
      try {
        this.loading = true
        let res = await axios.get(`voucher_type`)
        this.voucher_type = res.data
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
    //Metodo para sacar los tipos de cobros
    async getLoan(id) {
      try {
        this.loading = true
        let res = await axios.get(`loan/${id}`)
        this.loan=res.data
        if(this.loan.last_payment_validated==null)
        {
          this.loan.last_payment_validated={}
          this.last_payment=false
        }
        else{
          this.last_payment=true
        }
        if(this.loan.guarantors.length > 0)
        {
            this.type_affiliate.push(
              {
                name:"Titular",
                id:"T"
              },
              {
                name:"Garante",
                id:"G"
              })
        }else{
           this.type_affiliate.push(
              {
                name:"Titular",
                id:"T"
              })
              this.data_payment.affiliate_id="T"
              this.code_initials=this.loan.affiliate.type_initials
        }
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
  }
}
</script>

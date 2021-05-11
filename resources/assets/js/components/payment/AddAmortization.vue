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
                    <ValidationObserver ref="observer">
                    <v-form>
                      <center>
                       <v-toolbar-title>AMORTIZACIONES</v-toolbar-title>
                      </center>
                      <v-progress-linear></v-progress-linear>
                      <template>
                        <v-row>
                            <v-col cols="3" class="ma-0 py-2">
                                  <label><b>Primer Nombre:</b></label>
                                    {{garantes.lenders[0].first_name}}
                                </v-col>
                                <v-col cols="3" class="ma-0 py-2">
                                  <label><b>Paterno:</b></label>
                                  {{garantes.lenders[0].last_name}}
                                </v-col>
                                <v-col cols="3" class="ma-0 py-2">
                                  <label><b>Materno:</b></label>
                                  {{garantes.lenders[0].mothers_last_name}}
                                </v-col>
                                <v-col cols="3" class="ma-0 py-2">
                                  <label><b>C.I.:</b></label>
                                  {{ garantes.lenders[0].identity_card}}
                                </v-col>
                                <v-col cols="3" class="ma-0 py-2">
                                  <label><b>Nro Prestamos:</b></label>
                                  {{garantes.code}}
                                </v-col>
                                <v-col cols="3" class="ma-0 py-2">
                                  <label><b>Fecha de Desembolso:</b></label>
                                  {{$moment(garantes.disbursement_date).format("YYYY-MM-DD")}}
                                </v-col>
                                <v-col cols="3" class="ma-0 py-2">
                                  <label><b>Monto Desembolsado:</b></label>
                                  {{ garantes.amount_approved}}
                                </v-col>
                                 <v-col cols="3" class="ma-0 py-2">
                                  <label><b>Plazo :</b></label>
                                   {{ garantes.loan_term +' Meses'}}
                                </v-col>
                                <v-col cols="3" class="ma-0 py-2">
                                  <label><b>Cuota fija:</b></label>
                                  {{ garantes.estimated_quota}}
                                </v-col>
                                <v-col cols="3" class="ma-0 py-2">
                                  <label><b style="color:teal" >Saldo:</b></label>
                                  <b style="color:teal">{{garantes.balance}}</b>
                                </v-col>
                                <v-col cols="3" class="ma-0 py-2">
                                  <label><b>Fecha anterior de pago:</b></label>
                                  {{garantes.last_payment_validated.previous_payment_date }}
                                </v-col>
                                <v-col cols="3" class="ma-0 py-2">
                                  <label><b style="color:teal">Fecha siguiente pago:</b></label>
                                  <b style="color:teal">{{garantes.last_payment_validated.estimated_date }}</b>
                                </v-col>
                        <v-progress-linear></v-progress-linear>
                        <v-col cols="9"  v-show="editable" v-if="permissionSimpleSelected.includes('create-payment-loan') && this.data_payment.validar">
                        </v-col>
                         <v-col cols="3" class="ma-0 py-0" v-show="permissionSimpleSelected.includes('create-payment-loan') && this.data_payment.validar" v-if="editable">
                          <v-checkbox class="ma-0 py-3"
                            :outlined="editable"
                            :readonly="!editable"
                            :disabled="ver "
                            v-model="data_payment.validated"
                            label="Validar Pago"
                          ></v-checkbox>
                        </v-col>
                        <v-col cols="5" class="ma-0 pb-0"  >
                          <v-select
                            dense
                            class="caption"
                            style="font-size: 10px;"
                            @change="Onchange()"
                            v-model="data_payment.procedure_id"
                            :outlined="isNew"
                            :readonly="!isNew"
                            :items="tipo_tramite"
                            item-text="name"
                            item-value="id"
                            label='Tipo de tramite'
                            :disabled="ver || editable"
                          ></v-select>
                        </v-col>
                        <v-col cols="4" class="ma-0 pb-0" v-show="isNew">
                          <v-select
                            dense
                            class="caption"
                            style="font-size: 10px;"
                            v-model="data_payment.procedure_modality_id"
                            :outlined="!editable"
                            :readonly="editable"
                            :items="tipo_de_amortizacion"
                            item-text="name"
                            item-value="id"
                            label="Tipo de Amortizacion"
                            persistent-hint
                          ></v-select>
                        </v-col>
                        <!--v-col cols="1" class="ma-0 pb-0" v-show="isNew">
                          <label class="caption">Pago:</label>
                          </!--v-col>
                        <v-col-- cols="2" class="ma-0 pb-0" v-show="isNew">
                          <v-select
                            dense
                            class="caption"
                            style="font-size: 10px;"
                            v-model="data_payment.amortization"
                            :outlined="!editable"
                            :readonly="editable"
                            :items="tipo_de_pago_amortizacion"
                            item-text="name"
                            item-value="id"
                            persistent-hint
                          ></v-select>
                        </v-col-->
                        <v-col cols="3" class="ma-0 pb-0">
                          <v-select
                            dense
                            class="caption"
                            style="font-size: 10px;"
                            :Onchange="OnchangeAffiliate()"
                            v-model="data_payment.affiliate_id"
                            :outlined="isNew"
                            :readonly="!isNew"
                            :items="tipo_afiliado"
                            item-text="name"
                            item-value="id"
                            label="Pago del "
                            persistent-hint
                            :disabled="ver || editable"
                          ></v-select>
                        </v-col>
                        <v-col cols="3" class="ma-0 pb-0" v-show="garante_show">
                            <ul style="list-style: none" class="pa-0" >
                               <li v-for="guarantor in garantes.guarantors" :key="guarantor.id" class="mb-4">
                                 {{$options.filters.fullName(guarantor, true)}}
                                 <br>
                               </li>
                            </ul>
                              <!--h3 class="red--text" v-show="garantes.guarantors.length == 0"> *El prestamo no tiene garantes</!--h3-->
                        </v-col>
                         <v-col cols="1" class="my-0 pb-0" v-show="garante_show">
                            <ul style="list-style: none" class="pa-0 my-0" >
                               <li v-for="guarantor in garantes.guarantors" :key="guarantor.id" class="my-0">
                                  <v-radio-group  v-model="radios" class="py-0 my-0">
                              <v-radio
                              color="info"
                               :click="generateGuarantorCode()"
                              :value="guarantor.id"
                              class="py-0  my-0"
                            ></v-radio>
                          </v-radio-group>
                               </li>
                            </ul>
                        </v-col>
                        <v-col cols="4" class="ma-0 pb-0" v-show="!editable">
                          <v-text-field
                            dense
                            v-model="data_payment.voucher"
                            label="Codigo de Comprobante"
                            :outlined="isNew  || editable"
                            :disabled="ver"
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
                            v-model="data_payment.estimated_quota"
                            :readonly="true"
                            :disabled="true"
                            dense
                            label="Nro.Cuota"
                          ></v-text-field>
                        </v-col>
                           <v-col cols="4" class="ma-0 pb-0" v-show="permissionSimpleSelected.includes('create-payment-loan')" v-if="!isNew">
                           <v-text-field
                            v-model="data_payment.estimated_quota"
                            :disabled="true"
                            :readonly="true"
                            dense
                            label="Monto Pagado"
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
                            :clearable="editable"
                            :outlined="isNew"
                            :readonly="!isNew"
                            :disabled="ver || editable"
                          ></v-text-field>
                        </v-col>
                           <v-col cols="4" class="ma-0 pb-0">
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
                            :disabled="ver || editable"
                          ></v-select>
                        </v-col>
                        <v-col cols="4" class="ma-0 pb-0" v-show="view">
                          <v-text-field
                            dense
                            v-model="data_payment.pago_total"
                            label="Total Pagado"
                            :outlined="isNew || permissionSimpleSelected.includes('create-payment') "
                            :readonly="!isNew "
                            :disabled="ver"
                          ></v-text-field>
                        </v-col>
                     
                        <v-col cols="4" class="ma-0 pb-0" v-show="editable" v-if="permissionSimpleSelected.includes('create-payment')">
                          <v-select
                            class="caption"
                            style="font-size: 10px;"
                            dense
                            v-model="data_payment.tipo_pago"
                            :outlined="editable"
                            :readonly="!editable"
                            :items="payment_type_treasury"
                            item-text="name"
                            item-value="id"
                            label="Tipo de Pago"
                            persistent-hint
                          ></v-select>
                        </v-col>
                        <v-col cols="4" v-show="editable" v-if="permissionSimpleSelected.includes('create-payment')" >
                          <v-text-field
                            v-model="data_payment.comprobante"
                            :outlined="editable"
                            :readonly="!editable"
                            label="Nro. de Comprobante"
                            dense
                          ></v-text-field>
                        </v-col>
                        <v-col cols="12" class="ma-0 pb-0" v-show="permissionSimpleSelected.includes('create-payment')">
                          <v-text-field
                            v-if="!ver"
                            v-model="data_payment.glosa_voucher"
                            :outlined="editable"
                            :readonly="!editable"
                            dense
                            label="Glosa"
                          ></v-text-field>
                        </v-col>
                         <v-col cols="5" class="ma-0 pb-0" v-show="permissionSimpleSelected.includes('create-payment-loan')">
                          <v-text-field
                            v-show="isNew || editable" v-if="!ver"
                            v-model="data_payment.glosa"
                            :outlined=" isNew || editable "
                            :readonly="ver"
                            dense
                            label="Glosa"
                          ></v-text-field>
                        </v-col>
                          <v-col cols="8" v-show="permissionSimpleSelected.includes('create-payment-loan')">
                        </v-col>
                      </v-row>
                    </template>
                  </v-form>
                </ValidationObserver>
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
export default {
  name: "add-amortization",
  props: {
    data_payment: {
      type: Object,
      required: true
    },
  },
  data: () => ({
    loan: {},
    radios:[],
    garante_show: false,
    loanTypeSelectedOne:null,
    loanTypeSelectedTwo:null,
    loanTypeSelectedThree:null,
    tipo_tramite: [],
    regular:false,
    garantes:{
      lenders:[]
    },
    radio:null,
    codigo:null,
    separa:[],
    tipo_de_amortizacion: [],
    tipo_de_pago_amortizacion: [
      {name:"Regular",
      id:1
      },
      {name:"Total",
      id:2
      }
    ],
    tipo_afiliado:[],
    view:true,
    efectivo:false,
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
  }),
   computed: {
  //Metodo para obtener Permisos por rol
  permissionSimpleSelected () {
    return this.$store.getters.permissionSimpleSelected
  },   
    isNew() {
      return  this.$route.params.hash == 'new'
    },
    editable(){
       return  this.$route.params.hash == 'edit'
    },
    ver(){
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
      this.formatDate('paymentDate', date)
    }
  },
  methods: {
      OnchangeAmortization(){
 //        this.data_payment.pago_total=null
        if(this.data_payment.pago=="Liquidar"){
      
          this.data_payment.pago_total= this.garantes.balance
          this.data_payment.liquidate=true

        }else{
          this.data_payment.pago_total=(this.garantes.estimated_quota).toFixed(2)
          this.data_payment.liquidate=false
        }
      },
      OnchangeAffiliate(){
      if(this.data_payment.affiliate_id=="G")
      {
        this.garante_show= true
      }
     else{
        this.garante_show= false
      /*  if(this.isNew)
        {
          this.data_payment.voucher=null
        }*/
         for (let i = 0; i<  this.garantes.lenders.length; i++) {
            this.data_payment.affiliate_id_paid_by=this.garantes.lenders[0].id
         }
        }
    },
    generateGuarantorCode()
    {
      if(this.data_payment.affiliate_id=='G')
      {
        for (let i = 0; i<  this.garantes.guarantors.length; i++) {
        if(this.garantes.guarantors[i].id==this.radios)
        {
          this.data_payment.affiliate_id_paid_by=this.radios
          if(this.garantes.guarantors[i].first_name!=null && this.garantes.guarantors[i].second_name  && this.garantes.guarantors[i].last_name && this.garantes.guarantors[i].mothers_last_name)
          {
            this.separa[0]=this.garantes.guarantors[i].first_name
            this.separa[1]=this.garantes.guarantors[i].second_name
            this.separa[2]=this.garantes.guarantors[i].last_name
            this.separa[3]=this.garantes.guarantors[i].mothers_last_name
            this.data_payment.voucher='GAR-'+this.separa[0].charAt(0)+ this.separa[1].charAt(0)+this.separa[2].charAt(0)+this.separa[3].charAt(0)
          }
          else{
            if(this.garantes.guarantors[i].second_name!=null && this.garantes.guarantors[i].last_name!=null && this.garantes.guarantors[i].mothers_last_name!=null){
              this.separa[0]=this.garantes.guarantors[i].second_name
              this.separa[1]=this.garantes.guarantors[i].last_name
              this.separa[2]=this.garantes.guarantors[i].mothers_last_name
              this.data_payment.voucher='GAR-'+this.separa[0].charAt(0)+ this.separa[1].charAt(0)+this.separa[2].charAt(0)
          }else{
              if(this.garantes.guarantors[i].first_name!=null && this.garantes.guarantors[i].last_name!=null && this.garantes.guarantors[i].mothers_last_name!=null){
                this.separa[0]=this.garantes.guarantors[i].first_name
                this.separa[1]=this.garantes.guarantors[i].last_name
                this.separa[2]=this.garantes.guarantors[i].mothers_last_name
                this.data_payment.voucher='GAR-'+this.separa[0].charAt(0)+ this.separa[1].charAt(0)+this.separa[2].charAt(0)
            }else{
                if(this.garantes.guarantors[i].first_name!=null && this.garantes.guarantors[i].last_name!=null){
                  this.separa[0]=this.garantes.guarantors[i].first_name
                  this.separa[1]=this.garantes.guarantors[i].last_name
                  this.data_payment.voucher='GAR-'+this.separa[0].charAt(0)+ this.separa[1].charAt(0)
              }else{
                  if(this.garantes.guarantors[i].first_name!=null && this.garantes.guarantors[i].mothers_last_name!=null){
                    this.separa[0]=this.garantes.guarantors[i].first_name
                    this.separa[1]=this.garantes.guarantors[i].mothers_last_name
                    this.data_payment.voucher='GAR-'+this.separa[0].charAt(0)+ this.separa[1].charAt(0)
              }else{
                if(this.garantes.guarantors[i].second_name!=null && this.garantes.guarantors[i].last_name!=null){
                      this.separa[0]=this.garantes.guarantors[i].second_name
                      this.separa[1]=this.garantes.guarantors[i].last_name
                      this.data_payment.voucher='GAR-'+this.separa[0].charAt(0)+ this.separa[1].charAt(0)
              }else{
                  if(this.garantes.guarantors[i].second_name!=null && this.garantes.guarantors[i].mothers_last_name!=null){
                      this.separa[0]=this.garantes.guarantors[i].second_name
                      this.separa[1]=this.garantes.guarantors[i].mothers_last_name
                      this.data_payment.voucher='GAR-'+this.separa[0].charAt(0)+ this.separa[1].charAt(0)
                    }
                  }
                }
              }
            }
          }
        }
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
            this.data_payment.quota_number=this.loan_payment.quota_number
             this.data_payment.quota_number=this.loan_payment.quota_number
//quota_number
//estimated_quota.
        if(this.data_payment.procedure_modality_name == 'Amortización Complemento Económico' ||
            this.data_payment.procedure_modality_name == 'Amortización Fondo de Retiro' ||
            this.data_payment.procedure_modality_name == 'Amortización por Ajuste' ||
            this.data_payment.procedure_modality_name == 'Amortización Automática')
          {
            this.data_payment.validar =true
          }else{
            if(this.data_payment.procedure_modality_name == 'Amortización Directa')
            {
              this.data_payment.validar =false
            }
          }
           this.tipo_afiliado.push(
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
    Onchange(){
      if(this.data_payment.procedure_id!=null)
      {
         this.getTypeAmortization(this.data_payment.procedure_id)
      }
    },
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
        this.tipo_tramite = res.data
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
    async getTypeAmortization(id) {
      try {
        this.loading = true
        let res = await axios.get(`procedure_type/${id}/modality`)
        this.tipo_de_amortizacion = res.data
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
        let res = await axios.get(`payment_type`)
        this.payment_type_treasury = res.data
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
    formatDate(key, date) {
      if (date) {
        this.dates[key].formatted = this.$moment(date).format('L')
      } else {
        this.dates[key].formatted = null
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
        this.garantes=res.data
        console.log(this.garantes.guarantors.length)
        if(this.garantes.last_payment_validated==null)
        {
          this.garantes.last_payment_validated={}
        }
        if(this.garantes.guarantors.length > 0)
        {
            this.tipo_afiliado.push(
              {
                name:"Titular",
                id:"T"
              },
              {
                name:"Garante",
                id:"G"
              })
        }else{
           this.tipo_afiliado.push(
              {
                name:"Titular",
                id:"T"
              })
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
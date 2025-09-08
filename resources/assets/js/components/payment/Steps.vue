<template>
  <div>
    <v-progress-linear></v-progress-linear>

    <v-toolbar-title>
      <center style="color:teal">CREACION DE LA AMORTIZACION</center>
    </v-toolbar-title>

    <v-card color="grey lighten-1">

      <AddAmortization
        :data_payment.sync="data_payment"
        :payment.sync="payment"
        @isCalculate="isCalculate"/>

        <v-container class="py-0">
          <v-row>
            <v-spacer></v-spacer> <v-spacer></v-spacer>
            <v-col class="py-0">
              <v-btn color="seccundary"
                @click="atras()"  v-show="!isNew">
                Volver a la bandeja
              </v-btn>

              <v-btn
                color="primary"
                :loading="status_click"
                :disabled="isNew?disabled_save:false"
                @click="validatedStepOne()" v-show="!show">
                Guardar
              </v-btn>
            </v-col>
          </v-row>
        </v-container>

    </v-card>
  </div>
</template>
<script>
import AddAmortization from '@/components/payment/AddAmortization'

export default {
  name: "payment-steps",
  components: {
    AddAmortization
  },
   data: () => ({
    payment:{
      estimated_days:{
        penal:null,
        current:null
      }
    },
    disabled_save:true,
    status_click:false, //Variable que activa el loading al momento de crear una amortizacion
    data_payment:{
      payment_date:new Date().toISOString().substr(0, 10),
      voucher_date:new Date().toISOString().substr(0, 10),
      pago_total: null,
      voucher:""
    },
  }),
  computed: {
    //Metodo para obtener Permisos por rol
    permissionSimpleSelected () {
      return this.$store.getters.permissionSimpleSelected
    },
    isNew() {
      return this.$route.params.hash == 'new'
    },
    show(){
       return  this.$route.params.hash == 'view'
    },
    edit(){
       return  this.$route.params.hash == 'edit'
    },
  },
  methods: {
    //Metodo que envia al listado
    atras(){
       try {
        this.loading = true
        this.$router.push('/loanPayment')
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
    //Metodo para el creado del voucher
    async savePaymentTreasury() {
      try {
        this.status_click=true;
            let res1 = await axios.patch(`loan_payment/${this.$route.query.loan_payment}`,{
            validated:true
          })
          let res = await axios.post(`loan_payment/${this.$route.query.loan_payment}/voucher`,{

            //voucher_type_id: this.data_payment.tipo_pago,
            voucher_type_id: 2,
            bank_pay_number: this.data_payment.comprobante,
            voucher_amount_total:this.data_payment.voucher_amount_total,
            voucher_payment_date: this.data_payment.voucher_date,
            description: this.data_payment.glosa_voucher
          })
          printJS({
            printable: res.data.attachment.content,
            type: res.data.attachment.type,
            base64: true
          })
            this.$router.push('/loanPayment')
      }catch (e) {
        console.log(e)
      }finally {
        this.loading = false
        this.status_click=false;
      }
    },
    //Validar Pago
      async validatePayment() {
      try {
        this.status_click=true;
            let res1 = await axios.patch(`loan_payment/${this.$route.query.loan_payment}`,{
            validated:this.data_payment.validated,
            description:this.data_payment.glosa,
            voucher:this.data_payment.voucher
          })
          this.toastr.success('Se edito correctamente')
            this.$router.push('/loanPayment')
      }catch (e) {
        console.log(e)
      }finally {
        this.loading = false
        this.status_click=false;
      }
    },
    //Editar pago
     async editPayment() {
      try {
        this.status_click=true;
            let res1 = await axios.patch(`loan_payment/${this.$route.query.loan_payment}`,{
              description:this.data_payment.glosa,
              voucher:this.data_payment.voucher,
              validated:this.data_payment.validated
          })
          this.toastr.success('Se edito correctamente')
            this.$router.push('/loanPayment')
      }catch (e) {
        console.log(e)
      }finally {
        this.loading = false
        this.status_click=false;
      }
    },
    //Metodo para crear el Pago
    async savePayment(){
      try {
          this.status_click = true
          if(this.status_click==true)
          {
            let res = await axios.post(`loan/${this.$route.query.loan_id}/payment`,{
            estimated_date:this.data_payment.payment_date,
            estimated_quota:this.data_payment.pago_total,
            voucher:this.data_payment.voucher,
            amortization_type_id:this.data_payment.pago,
            affiliate_id:this.data_payment.affiliate_id_paid_by,
            paid_by:this.data_payment.affiliate_id,
            procedure_modality_id:this.data_payment.procedure_modality_id,
            user_id: this.$store.getters.id,
            state: this.data_payment.refinanciamiento,
            description:this.data_payment.glosa,
            liquidate : this.data_payment.liquidate,
            categorie_id:this.data_payment.categori_id,
            role_id : this.$store.getters.rolePermissionSelected.id
          })
             if(res.status==201 || res.status == 200){
              this.status_click = false
            }
            printJS({
            printable: res.data.attachment.content,
            type: res.data.attachment.type,
            base64: true
          })
          this.$router.push({ name: 'flowAdd',  params: { id: this.$route.query.loan_id, workTray: 'received'}, query:{ redirectTab: 7 } })
          this.payment = res.data
        }
      }catch (e) {
        console.log(e)
      }finally {
        this.loading = false
        this.status_click = false
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
        this.data_payment.procedure_id= this.loan_payment.procedure_modality_id
        this.data_payment.amortization=2
        if(this.data_payment.procedure_modality_name == 'Amortización Complemento Económico' ||
            this.data_payment.procedure_modality_name == 'Amortización Fondo de Retiro' ||
            this.data_payment.procedure_modality_name == 'Amortización por Ajuste' ||
            this.data_payment.procedure_modality_name == 'Amortización Automática')
          {
            this.data_payment.validar =true
          }else{
            if(this.data_payment.procedure_modality_name == 'Amortización en Efectivo')
            {
              this.data_payment.validar =false
            }
          }
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
    //Metodo calculo de siguiente cuota
    async Calcular(id) {
      try {
          let res = await axios.patch(`loan/${id}/payment`,{
            affiliate_id:this.data_payment.affiliate_id_paid_by,
            estimated_date:this.data_payment.payment_date,
            estimated_quota:this.data_payment.pago_total,
            liquidate : this.data_payment.liquidate,
            procedure_modality_id:this.data_payment.procedure_modality_id,
            categorie_id :this.data_payment.categori_id
          })
            this.payment = res.data
            this.data_payment.pago_total=this.payment.estimated_quota
      }catch (e) {
        console.log(e)
      }finally {
        this.loading = false
      }
    },
    //Validacion del segundo paso
    async validatedStepTwo()
    {
      try {
        if(this.isNew)
          {
            this.savePayment()
          }
          else{
            if(this.edit)
            {
              this.toastr.error("No tiene los permisos")
            }
          }
      }catch (e) {
        console.log(e)
      }finally {
        this.loading = false
      }
    },
    //Validacion del paso uno
    async validatedStepOne() {
      try {
           if(!this.isNew)
          {
            if(this.data_payment.procedure_modality_name == 'Amortización Complemento Económico' ||
              this.data_payment.procedure_modality_name == 'Amortización Fondo de Retiro' ||
              this.data_payment.procedure_modality_name == 'Amortización por Ajuste' ||
              this.data_payment.procedure_modality_name == 'Amortización Automática')
            {
              this.validatePayment()
            }else{
              if(this.data_payment.procedure_modality_name == 'Amortización en Efectivo' && this.permissionSimpleSelected.includes('create-payment') )
              {
                this.savePaymentTreasury()
              }else{
                this.editPayment()
              }
            }
          }
          else{
            this.validatedStepTwo()
          }
      }catch (e) {
        console.log(e)
      }finally {
        this.loading = false
      }
    },
    isCalculate(emit){
      this.disabled_save=emit;
    }
  },
}
</script>

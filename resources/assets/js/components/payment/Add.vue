<template>
  <v-card flat>
    <v-card-title >
      <v-toolbar  dense color="tertiary" style="z-index: 1;">
        <v-toolbar-title>
          <Breadcrumbs/>
        </v-toolbar-title>
        <v-spacer></v-spacer>
      </v-toolbar>
    </v-card-title>
    <template>
      <v-container>
        <div>
          <v-row>
            <v-col cols="5" v-show="!ver">
              <span>
                <v-tooltip left >
                <template v-slot:activator="{ on }">
                  <v-btn
                    icon
                    dark
                    small
                    color="success"
                    bottom
                    right
                    v-on="on"
                    :to="{ name: 'flowAdd', params: { id: $route.query.loan_id,  workTray: 'received'}, query:{ redirectTab: 6 } }"
                  >
                  <v-icon>mdi-arrow-left-bold-outline</v-icon>
                  </v-btn>
                </template>
                <span>Regresar</span>
                </v-tooltip>
              </span>
              {{"TITULAR: "+$options.filters.fullName(this.loan.lenders[0], true)}}
            </v-col>
            <v-col  cols="3" v-show="!ver">
              {{"PRESTAMO: "+this.loan.code}}
            </v-col>
            <v-col  cols="2" v-show="!ver">
              {{'MONTO:'+this.loan.amount_approved}}
            </v-col>
             <v-col  cols="2" v-show="!ver">
              {{'CUOTA:'+this.loan.estimated_quota}}
            </v-col>
            <v-col  cols="4" v-show="ver" class='mb-0 pb-0'>
                <span>
                <v-tooltip left>
                <template v-slot:activator="{ on }">
                  <v-btn
                    icon
                    dark
                    small
                    color="success"
                    bottom
                    right
                    v-on="on"
                    :to="{ name: 'flowAdd', params: { id: loan_payment.loan_id,  workTray: 'received'}, query:{ redirectTab: 6 } }"
                  >
                  <v-icon>mdi-arrow-left-bold-outline</v-icon>
                  </v-btn>
                </template>
                <span>Regresar</span>
                </v-tooltip>
              </span>
             {{"TITULAR: "+$options.filters.fullName(this.loan.lenders[0], true)}}
            </v-col>
              <v-col  cols="4" v-show="ver" class='mb-0 pb-0'>
              {{"CODIGO DEL PAGO: "+' '+this.loan_payment.code}}
            </v-col>
            <v-col  cols="4" v-show="ver" class='mb-0 pb-0'>
              {{"PRESTAMO: "+this.loan.code}}
            </v-col>
            <v-col  cols="4" v-show="ver" class='py-1'>
              {{"NÚMERO DE CUOTA: "+this.loan_payment.quota_number}}
            </v-col>
            <v-col  cols="4" v-show="ver" class='py-1'>
              {{'MONTO:'+this.loan.amount_approved}}
            </v-col>
            <v-col  cols="4" v-show="ver" class='py-1'>
              {{'CUOTA ESTIMADA MENSUAL :'+this.loan_payment.estimated_quota}}
            </v-col>
          </v-row>
          <Steps
          :loan.sync="loan"/>
        </div>
      </v-container>
    </template>
  </v-card>
</template>

<script>
import Steps from '@/components/payment/Steps'
import Breadcrumbs from '@/components/shared/Breadcrumbs'

export default {
  name: "payment-add",
  components: {
    Steps,
    Breadcrumbs
  },
  data: () => ({
    loan:{
      lenders:[{}]
    },
    loan_payment:{},
    degree_name: null,
    category_name: null,
  }),
  computed: {
    isNew() {
      return this.$route.params.hash == 'new'
    },
     editable(){
      return  this.$route.params.hash == 'edit'
    },
    ver(){
      return  this.$route.params.hash == 'view'
    },
  },
  beforeMount() {
    this.setBreadcrumbs()
      if(this.ver){
        this.getLoanPayment(this.$route.query.loan_payment)
      }
  },
  mounted() {
    if(this.editable){
      this.getLoanPayment(this.$route.query.loan_payment)
    }
    if(this.isNew){
      this.getLoan(this.$route.query.loan_id);
    }
  },
  methods:{
    //Metodo para sacar datos del prestamo
    async getLoan(id) {
      try {
        this.loading = true;
        let res = await axios.get(`loan/${id}`);
        this.loan = res.data;
       } catch (e) {
        console.log(e);
      } finally {
        this.loading = false;
      }
    },
    //Metodo para sacar datos del pago
    async getLoanPayment(id) {
      try {
        this.loading = true
        let res = await axios.get(`loan_payment/${id}`)
        this.loan_payment = res.data
        let res1 = await axios.get(`loan/${this.loan_payment.loan_id}`);
        this.loan = res1.data;
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
  //Metodo del cambio setBreadcrumbs
    setBreadcrumbs() {
      let breadcrumbs = [
        {
          text: 'Cobros',
          to: { name: 'paymentIndex' }
        }
      ]
      if (this.isNew) {
        breadcrumbs.push({
          text: 'Nuevo Cobro',
          to: { name: 'paymentIndex' }
        })
      } else {
        if (this.ver) {
          breadcrumbs.push({
          text: 'Ver Cobro',
          to: { name: 'paymentIndex' }
          })
        }else{
          breadcrumbs.push({
            text: 'Editar Cobro',
            to: { name: 'paymentIndex' }
          })
        }
      }
      this.$store.commit('setBreadcrumbs', breadcrumbs)
    }
  }
}
</script>
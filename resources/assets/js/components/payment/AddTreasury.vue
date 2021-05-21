<template>
  <v-container >
    <ValidationObserver ref="observer">
      <v-form>
        <v-card>
        <v-card-text >
            <v-row justify="center" class="ma-0 pb-0">
              <v-col cols="12" class="ma-0 pb-0">
                <v-container class="py-0">
                  <ValidationObserver ref="observer">
                    <v-form>
                      <template>
                      <v-row>
                         <v-col cols="3" class="ma-0 pb-0">
                          <label>TIPO DE VOUCHER:</label>
                        </v-col>
                        <v-col cols="3" class="ma-0 pb-0">
                          <v-select
                            dense
                            v-model="data_payment.voucher"
                            :outlined="editable"
                            :items="voucher_types"
                            item-text="name"
                            item-value="id"
                            persistent-hint
                          ></v-select>
                        </v-col>
                        <v-col cols="1" class="ma-0 pb-0"></v-col>
                        <v-col cols="2" class="ma-0 pb-0">
                          <label> FECHA DE PAGO:</label>
                        </v-col>
                        <v-col cols="3">
                          <v-menu
                              v-model="dates.disbursementDate.show"
                              :close-on-content-click="false"
                              transition="scale-transition"
                              offset-y
                              max-width="290px"
                              min-width="290px"
                            >
                            <template v-slot:activator="{ on }">
                              <v-text-field
                                dense
                                :outlined="editable"
                                v-model="dates.disbursementDate.formatted"
                                hint="Día/Mes/Año"
                                persistent-hint
                                append-icon="mdi-calendar"
                                v-on="on"

                              ></v-text-field>
                            </template>
                            <v-date-picker v-model="data_payment.payment_date" no-title @input="dates.disbursementDate.show = false"></v-date-picker>
                            </v-menu>
                        </v-col>
                        <v-col cols="3" class="ma-0 pb-0">
                          <label>TOTAL PAGADO:</label>
                      </v-col>
                      <v-col cols="3" class="ma-0 pb-0">
                          <v-text-field
                            dense
                            v-model="data_payment.pago_total"
                            :outlined="editable"
                          ></v-text-field>
                       </v-col>
                      <v-col cols="1">
                      </v-col>
                       <v-col cols="2" class="ma-0 pb-0">
                          <label>TIPO DE PAGO:</label>
                      </v-col>
                      <v-col cols="3" class="ma-0 pb-0">
                        <v-select
                            dense
                            v-model="data_payment.pago"
                            :outlined="editable"
                            :items="payment_types"
                            item-text="name"
                            item-value="id"
                            persistent-hint
                        ></v-select>
                       </v-col>
                      <v-col cols="3" class="ma-0 pb-0">
                         <label>NRO DE COMPROBANTE:</label>
                      </v-col>
                      <v-col cols="3">
                         <v-text-field
                          v-model="data_payment.comprobante"
                          :outlined="editable"
                          dense
                        ></v-text-field>
                      </v-col>
                      <v-col cols="6" class="ma-0 pb-0">
                        <v-text-field
                            v-model="data_payment.glosa"
                            :outlined="editable"
                            :readonly="!editable"
                            dense
                            label="Glosa"
                          ></v-text-field>
                      </v-col>
                    </v-row>
                    </template>
                  </v-form>
                </ValidationObserver>
              </v-container>
            </v-col>
          </v-row>
      </v-card-text>
        </v-card>
      </v-form>
    </ValidationObserver>
  </v-container>
</template>
<script>
export default {
  name: "loan-requirement",
  props: {
    data_payment: {
      type: Object,
      required: true
    }
  },
  data: () => ({
    loan: {},
    editable: true,
    //isNew:$route.params.hash,
    tipo_de_amortizacion: [
      {name:"Regular",
      id:1
      },
      {name:"Total",
      id:2
      }
    ],
    payment_types:[],
    voucher_types:[],
      dates: {
        disbursementDate:{
            formatted: null,
            picker: false
            }
          },
  }),
  beforeMount(){
    this.getVoucherTypes()
    this.getPaymentTypes()
    this.getLoan(this.$route.query.loan_id);
  },
  mounted() {
    this.getLoan(this.$route.query.loan_id);
    this.formatDate('disbursementDate', this.data_payment.payment_date)
  },
  watch: {
    'data_payment.payment_date': function(date) {
      this.formatDate('disbursementDate', date)
    }
  },
   computed: {
    /*sNew() {
      if(this.$route.params.hash == 'new')
      {
         return  editable= false,
      }
      else{
         return   editable= false,
      }
    }*/
  },
  methods: {
      formatDate(key, date) {
      if (date) {
        this.dates[key].formatted = this.$moment(date).format('L')
      } else {
        this.dates[key].formatted = null
      }
    },
     async getLoan(id) {
      try {
        this.loading = true;
        let res = await axios.get(`loan/${id}`);
        this.loan = res.data;
        console.log('esta sacando el loan')
      } catch (e) {
        console.log(e);
      } finally {
        this.loading = false;
      }
    },
    async getVoucherTypes() {
      try {
        this.loading = true
        let res = await axios.get(`voucher_type`)
        this.voucher_types = res.data
        console.log('estos son los vouchers'+this.voucher_types)
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
     async getPaymentTypes() {
      try {
        this.loading = true
        let res = await axios.get(`payment_type`)
        this.payment_types = res.data
        console.log(this.payment_types+'este es el tipo de desembolso');
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    }
  }
}
</script>

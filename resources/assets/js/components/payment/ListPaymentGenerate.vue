<template>
  <v-container fluid>
    <ValidationObserver>
      <v-form>
        <v-card flat>
          <v-card-title class="pa-0 pb-3">
            <v-toolbar dense color="tertiary" class="font-weight-regular">
              <v-toolbar-title>AMORTIZACIONES</v-toolbar-title>
            </v-toolbar>
          </v-card-title>
        <v-tooltip top>
          <template v-slot:activator="{ on }">
            <v-btn 
              fab
              @click="dowload_payments()"
              color="success"
              v-on="on"
              x-small
              absolute
              right
              style="margin-right: 40px; margin-top: -50px"
              :loading_download= loading
            >
              <v-icon>mdi-file-excel</v-icon>
            </v-btn>
          </template>
          <span>Descargar reporte</span>
        </v-tooltip>
        <v-tooltip top>
          <template v-slot:activator="{ on }">
              <v-btn
                fab
                @click="clearAll()"
                color="info"
                v-on="on"
                x-small
                absolute
                right
                style="margin-right:0px; margin-top: -50px"
              >
                <v-icon>mdi-broom</v-icon>
              </v-btn>
          </template>
          <span>Limpiar todos los filtros</span>
        </v-tooltip>

        <!-- boton para limpiar los filtros -->
        <v-tooltip top>
            <template v-slot:activator="{ on }">
              <v-btn
                fab
                @click="_show_filter()"
                :color="show_filter?'dark':'teal'"
                dark
                v-on="on"
                x-small
                absolute
                right
                style="margin-top: -50px; margin-right:80px"
              >
                <v-icon> mdi-filter </v-icon>
              </v-btn>
            </template>
            <span class="caption">Mostrar/Ocultar filtros</span>
          </v-tooltip> 

        <v-data-table
          :headers="headers"
          :items="loan_payments"
          :options.sync="options"
          :server-items-length="totalPayments"
          :footer-props="{ itemsPerPageOptions: [8, 15, 30] }"
        >
          <template v-slot:[`header.code_loan_payment`]="{ header }">
            {{ header.text }}<br>
            <v-menu offset-y :close-on-content-click="false">
              
              <!-- <template v-slot:activator="{ on, attrs }">
                <v-btn icon v-bind="attrs" v-on="on">
                  <v-icon small :color="searching.code_loan_payment !='' ? 'red' : 'blue'">
                    mdi-filter
                  </v-icon>
                </v-btn>

             
              </template> -->
              
<!-- inputs -->
              <div>
                <v-text-field
                  dense
                  v-model="searching.code_loan_payment"
                  type="text"
                  :label="'Buscaremos ' + header.text"
                  @keydown.enter="search_payments()"
                  hide-details
                  single-line
                ></v-text-field>
              </div>
            </v-menu>
          </template>
   

          <template v-slot:[`item.estimated_date_loan_payment`]="{ item }">
            {{ item.estimated_date_loan_payment | date}}
            
          </template>

          <template v-slot:[`item.date_loan_payment`]="{ item }">
            {{ item.date_loan_payment | date}}
          </template>

          <template v-slot:[`item.quota_loan_payment`]="{ item }">
            {{ item.quota_loan_payment | money}}
          </template>

         <template v-slot:[`item.modality_shortened_loan_payment`]="{ item }">
            <v-tooltip top>
              <template v-slot:activator="{ on }">
                <span v-on="on">{{ item.modality_shortened_loan_payment }}</span>
              </template>
              <span>{{ item.modality_loan_payment }}</span>
            </v-tooltip>
          </template>

          <template v-slot:[`item.actions`]="{ item }" >
            <v-tooltip bottom >
              <template v-slot:activator="{ on }">
                <v-btn
                  icon
                  small
                  v-on="on"
                  color="teal"
                  :to="{ name: 'flowAdd', params: { id: item.id_loan }, query:{ redirectTab: 7 , workTray: 'all'}}"
                ><v-icon>mdi-text-box-multiple</v-icon>
                </v-btn>
              </template>
              <span>Kardex</span>
            </v-tooltip>
            <v-menu
                offset-y
                close-on-content-click
                v-if="permissionSimpleSelected.includes('print-contract-loan') || 
                (permissionSimpleSelected.includes('print-payment-plan') && item.state_loan != 'En Proceso') || 
                (permissionSimpleSelected.includes('print-payment-kardex-loan') && item.state_loan != 'En Proceso')"
              >
                <template v-slot:activator="{ on }">
                  <v-btn
                    icon
                    color="primary"
                    dark
                    v-on="on"
                  ><v-icon>mdi-printer</v-icon>
                  </v-btn>
                </template>
                <v-list dense class="py-0">
                  <v-list-item
                    v-for="doc in printDocs"
                    :key="doc.id"
                    @click="imprimir(doc.id, item)"
                  >
                    <v-list-item-icon class="ma-0 py-0 pt-2">
                      <v-icon 
                        class="ma-0 py-0"
                        small
                        color="light-blue accent-4"
                      >{{doc.icon}}</v-icon>
                    </v-list-item-icon>
                    <v-list-item-title 
                      class="ma-0 py-0 mt-n2">
                      {{ doc.title }}
                    </v-list-item-title>
                  </v-list-item>
                </v-list>
              </v-menu>
          </template>
          <template slot="body.prepend">
              <tr v-if="show_filter">
                <!-- inputs -->
                  <td><v-text-field placeholder="Cód. Pago." spellcheck="false" class="filter-text" v-model="searching.code_loan_payment" @keydown.enter="search_payments()"></v-text-field></td>
                  <td><v-text-field placeholder="Cód. Prestamo." spellcheck="false" class="filter-text" v-model="searching.code_loan" @keydown.enter="search_payments()"></v-text-field></td>
                  <td><v-text-field disabled class="filter-text"></v-text-field></td>
                  <td><v-text-field disabled class="filter-text"></v-text-field></td>
                  <td><v-text-field disabled class="filter-text"></v-text-field></td>
                  <td><v-text-field placeholder="CI Prestatario." spellcheck="false" class="filter-text" v-model="searching.identity_card_borrower" @keydown.enter="search_payments()"></v-text-field></td>
                  <td><v-text-field placeholder="Matricula Prestatario." spellcheck="false" class="filter-text" v-model="searching.registration_borrower" @keydown.enter="search_payments()"></v-text-field></td>
                  <td><v-text-field placeholder="Nombre Prestatario." spellcheck="false" class="filter-text" v-model="searching.full_name_borrower" @keydown.enter="search_payments()"></v-text-field></td>
                  <td><v-text-field placeholder="Tipo de Amortizacion." spellcheck="false" class="filter-text" v-model="searching.modality_shortened_loan_payment" @keydown.enter="search_payments()"></v-text-field></td>
                  <td><v-text-field placeholder="Tipo de Pago." spellcheck="false" class="filter-text" v-model="searching.voucher_type_loan_payment" @keydown.enter="search_payments()"></v-text-field></td>
                  <td><v-text-field placeholder="Estado." spellcheck="false" class="filter-text" v-model="searching.states_loan_payment" @keydown.enter="search_payments()"></v-text-field></td>

                  <!-- <td><v-text-field placeholder="Cód. Prestamo." spellcheck="false" class="filter-text" v-model="searching.code_loan" @keydown.enter="search_loans()"></v-text-field></td>
                  <td><v-text-field placeholder="Fecha Calculo." spellcheck="false" class="filter-text" v-model="searching.code_loan" @keydown.enter="search_loans()"></v-text-field></td>
                  <td><v-text-field placeholder="Cód. Pago." spellcheck="false" class="filter-text" v-model="searching.code_loan" @keydown.enter="search_loans()"></v-text-field></td>
                  <td><v-text-field placeholder="Cód. Pago." spellcheck="false" class="filter-text" v-model="searching.code_loan" @keydown.enter="search_loans()"></v-text-field></td>
                  <td><v-text-field placeholder="Cód. Pago." spellcheck="false" class="filter-text" v-model="searching.code_loan" @keydown.enter="search_loans()"></v-text-field></td>
                  <td><v-text-field placeholder="Cód. Pago." spellcheck="false" class="filter-text" v-model="searching.code_loan" @keydown.enter="search_loans()"></v-text-field></td>
                  <td><v-text-field placeholder="Cód. Pago." spellcheck="false" class="filter-text" v-model="searching.code_loan" @keydown.enter="search_loans()"></v-text-field></td>
                  <td><v-text-field placeholder="Cód. Pago." spellcheck="false" class="filter-text" v-model="searching.code_loan" @keydown.enter="search_loans()"></v-text-field></td>
                  <td><v-text-field placeholder="Cód. Pago." spellcheck="false" class="filter-text" v-model="searching.code_loan" @keydown.enter="search_loans()"></v-text-field></td>
                  <td><v-text-field placeholder="Cód. Pago." spellcheck="false" class="filter-text" v-model="searching.code_loan" @keydown.enter="search_loans()"></v-text-field></td>
                   -->
                 
              </tr>
            </template>




        </v-data-table>
        </v-card>
      </v-form>
    </ValidationObserver>
  </v-container>
</template>

<script>

export default {
name: 'payment-listPaymentGenerate',
data () {
  return {
    searching:{
      code_loan_payment: '',
      code_loan:'',
      identity_card_borrower: '',
      registration_borrower:'',
      full_name_borrower:'',
      modality_shortened_loan_payment:'',
      voucher_type_loan_payment: '',
      states_loan_payment: '',
    },

    headers: [
      { text: 'Cód. Pago', value: 'code_loan_payment',input:'' , menu:false,type:"text",class: ['normal', 'white--text','text-md-center'],width: '10%'},
      { text: 'Cód. Préstamo', value: 'code_loan',input:'' , menu:false,type:"text",class: ['normal', 'white--text','text-md-center'],width: '10%'},
      { text: 'Fecha Cálculo', value: 'estimated_date_loan_payment',input:'' , menu:false,type:"text",class: ['normal', 'white--text','text-md-center'],width: '5%'},
      { text: 'Fecha Transacción', value: 'date_loan_payment',input:'' , menu:false,type:"text",class: ['normal', 'white--text','text-md-center'],width: '5%'},
      { text: 'Cuota', value: 'quota_loan_payment',input:'' , menu:false,type:"text",class: ['normal', 'white--text','text-md-center'],width: '5%'},
      { text: 'CI Prestatario', value: 'identity_card_borrower',input:'' , menu:false,type:"text",class: ['normal', 'white--text','text-md-center'],width: '5%'},
      { text: 'Matrícula Prestatario', value: 'registration_borrower' ,input:'', menu:false,type:"text",class: ['normal', 'white--text','text-md-center'],width: '5%'},
      { text: 'Nombre Completo Prestatario', value: 'full_name_borrower',input:'' , menu:false,type:"text",class: ['normal', 'white--text','text-md-center'],width: '20%'},
      { text: 'Tpo Amortización',value:'modality_shortened_loan_payment',input:'', menu:false,type:"text",class: ['normal', 'white--text','text-md-center'],width: '10%'},
      { text: 'Tipo Pago',value:'voucher_type_loan_payment',input:'', menu:false,type:"text",class: ['normal', 'white--text','text-md-center'],width: '5%'},
      { text: 'Estado',value:'states_loan_payment',input:'', menu:false,type:"text",class: ['normal', 'white--text','text-md-center'],width: '10%'},
      { text: 'Acción',value:'actions',input:'', menu:false,type:"text",class: ['normal', 'white--text','text-md-center'], sortable: false,width: '10%'},
    ],

    loan_payments: [],
    printDocs: [],
    options: {
      page: 1,
      itemsPerPage: 8,
      sortBy: ["code_loan_payment"],
      sortDesc: [false],
    },
    totalPayments: 0,
    excel: false,
    loading: false,
    //agruegue
    show_filter:true
  }
},

  computed: {
    //permisos del selector global por rol
    permissionSimpleSelected () {
      return this.$store.getters.permissionSimpleSelected
    },
  },

  watch: {
    options: function (newVal, oldVal) {
      if (newVal.page != oldVal.page ||
        newVal.itemsPerPage != oldVal.itemsPerPage ||
        newVal.sortBy != oldVal.sortBy ||
        newVal.sortDesc != oldVal.sortDesc){
          this.search_payments()
      }
    },
    searching: {
      deep: true,
      handler(val) {
        this.options.page=1
      }
    }
  },
    created(){
      this.search_payments()
      this.docsLoans()
    },

   methods: {
       async search_loans() {
      this.loading_table = true
      
      try {
        let res = await axios.get(`loan_tracking`, {
          params: {
            city_loan:this.searching.city_loan,
            name_wf_state_loan:this.searching.name_wf_state_loan,
            user_loan:this.searching.user_loan,
            code_loan: this.searching.code_loan,
            identity_card_borrower: this.searching.identity_card_borrower,
            registration_borrower: this.searching.registration_borrower,
            full_name_borrower: this.searching.full_name_borrower,
            shortened_sub_modality_loan: this.searching.shortened_sub_modality_loan,
            state_loan: this.searching.state_loan,
            excel: false,
            page: this.options.page,
            per_page: this.options.itemsPerPage,
            sortBy: this.options.sortBy,
            sortDesc: this.options.sortDesc,
            trashed_loan: this.trashed_loan
          },
        });
        this.loans = res.data.data
        this.totalLoans = res.data.total
        delete res.data["data"]
        this.options.page = res.data.current_page
        this.options.itemsPerPage = parseInt(res.data.per_page)
        //this.options.totalItems = res.data.total
        this.loading_table = false
      } catch (e) {
        console.log(e)
        this.loading_table = false
      }
    },
    async search_payments(){
        try {
            let res = await axios.get(`list_loan_payments_generate`,{
              params:{
                code_loan_payment: this.searching.code_loan_payment,
                code_loan: this.searching.code_loan,
                identity_card_borrower: this.searching.identity_card_borrower,
                registration_borrower: this.searching.registration_borrower,
                full_name_borrower: this.searching.full_name_borrower,
                modality_shortened_loan_payment:this.searching.modality_shortened_loan_payment,
                voucher_type_loan_payment:this.searching.voucher_type_loan_payment,
                states_loan_payment:this.searching.states_loan_payment,
                excel: false,
                page: this.options.page,
                per_page: this.options.itemsPerPage,
                sortBy: this.options.sortBy,
                sortDesc: this.options.sortDesc,
              }
            })
            this.loan_payments = res.data.data
            this.totalPayments = res.data.total
            delete res.data['data']
            this.options.page = res.data.current_page
            this.options.itemsPerPage = parseInt(res.data.per_page)
        } catch (e) {
            console.log(e)
        }
    },

    async dowload_payments() {
      this.loading_download = true
      await axios({
        url: "/list_loan_payments_generate",
        method: "GET",
        responseType: "blob", // important
        headers: { Accept: "application/vnd.ms-excel" },
        data: this.datos,
        params:{
          code_loan_payment: this.searching.code_loan_payment,
          code_loan: this.searching.code_loan,
          identity_card_borrower: this.searching.identity_card_borrower,
          registration_borrower: this.searching.registration_borrower,
          full_name_borrower: this.searching.full_name_borrower,
          modality_shortened_loan_payment:this.searching.modality_shortened_loan_payment,
          voucher_type_loan_payment:this.searching.voucher_type_loan_payment,
          states_loan_payment:this.searching.states_loan_payment,
          excel:true
        }
      })
        .then(response => {
          console.log(response)
          const url = window.URL.createObjectURL(new Blob([response.data]))
          const link = document.createElement("a")
          link.href = url
          link.setAttribute("download", "ReporteAmortizaciones.xls")
          document.body.appendChild(link)
          link.click()
          this.loading_download = false
        })
        .catch(e => {
          console.log(e)
          this.loading_download = false
        })
    },

    clearAll(){
      this.searching.code_loan_payment= '',
      this.searching.code_loan= '',
      this.searching.identity_card_borrower= '',
      this.searching.registration_borrower='',
      this.searching.full_name_borrower='',
      this.searching.modality_shortened_loan_payment='',
      this.searching.voucher_type_loan_payment= '',
      this.searching.states_loan_payment= '',
      this.search_payments()
    },

    async imprimir(id, item){
      try {
        let res
        if (id == 5) {
          res = await axios.get(`loan_payment/${item.id_loan_payment}/print/loan_payment`)
        }
        else if(id == 6 && item.procedure_loan_payment == 'Amortización Directa'){
          let resv = await axios.get(`loan_payment/${item.id_loan_payment}/voucher`)
          let idVoucher = resv.data.id
          res = await axios.get(`voucher/${idVoucher}/print/voucher`)
        }else{
          this.toastr.error("Este registro es una " + item.procedure_loan_payment + " no cuenta con Comprobante de Registro de Pago (Amort. Directa).")
        }
        printJS({
            printable: res.data.content,
            type: res.data.type,
            documentTitle: res.data.file_name,
            base64: true
        })
      } catch (e) {
        this.toastr.error("Ocurrió un error en la impresión.")
        console.log(e)
      }
    },

    docsLoans(){
      let docs = []
      if (this.permissionSimpleSelected.includes("print-payment-loan")) {
        docs.push({ id: 5, title: "Registro de cobro", icon: "mdi-file-check-outline" })
      }
      if (this.permissionSimpleSelected.includes("print-payment-voucher")) {
        docs.push({ id: 6, title: "Registro de pago", icon: "mdi-cash-multiple" })
      } else {
        console.log("Se ha producido un error durante la generación de la impresión")
      }
      this.printDocs = docs
      },
    //  agragado
     _show_filter(){
       this.show_filter=!this.show_filter
    }
   }
  }
</script>
<style scoped>
.v-text-field{
  background-color: white;
  width: 200px;
  padding:5px;
  margin: 0px;
  font-size: 0.8em;
  border-color: palegreen;
}
.filter-text{
  font-size: 12px;
  height: 2px;
  margin: 0 0 40px 0;
  padding: 0;
  width: 100%

}
</style>
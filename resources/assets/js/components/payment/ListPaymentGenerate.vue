<template>
  <v-container fluid>
    <ValidationObserver>
      <v-form>
        <v-card flat>
          <v-card-title class="pa-0 pb-3">
            <v-toolbar dense color="tertiary" class="font-weight-regular">
              <v-toolbar-title>Amortizaciones</v-toolbar-title>
            </v-toolbar>
          </v-card-title>
        <v-tooltip top>
          <template v-slot:activator="{ on }">
            <v-btn 
              fab
              v-on="on"
              @click="dowload_payments()" 
              color="success"  
              class="mb-2" 
              small>
                <v-icon>
                    mdi-file-excel
                </v-icon>
            </v-btn>
          </template>
          <span class="caption">Descargar reporte</span>
        </v-tooltip>
        <v-tooltip top>
          <template v-slot:activator="{ on }">
              <v-btn
                fab
                v-on="on"
                @click="clearAll()" 
                color="info"  
                class="mb-2" 
                small>
                  <v-icon>
                      mdi-broom
                  </v-icon>
              </v-btn>
          </template>
          <span class="caption">Limpiar todos los filtros</span>
        </v-tooltip> 
        <v-data-table
          :headers="headers"
          :items="loans"
          :options.sync="options"
          :server-items-length="totalAffiliates"
          :footer-props="{ itemsPerPageOptions: [5, 15, 30] }"
        >
          <template v-slot:[`header.code_loan`]="{ header }">
            {{ header.text }}<br>
            <v-menu offset-y :close-on-content-click="false">
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon v-bind="attrs" v-on="on">
                  <v-icon small :color="searching.code_loan !='' ? 'red' : 'black'">
                    mdi-filter
                  </v-icon>
                </v-btn>
              </template>
              <div>
                <v-text-field
                  dense
                  v-model="searching.code_loan"
                  type="text"
                  :label="'Buscar ' + header.text"
                  @keydown.enter="search_payments()"
                  hide-details
                  single-line
                ></v-text-field>
              </div>
            </v-menu>
          </template>

          <!--<template v-slot:[`header.disbursement_date_loan`]="{ header }">
            {{ header.text }}<br>
            <v-menu offset-y :close-on-content-click="false">
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon v-bind="attrs" v-on="on">
                  <v-icon small :color="searching.disbursement_date_loan !='' ? 'red' : 'black'">
                    mdi-filter
                  </v-icon>
                </v-btn>
              </template>
              <div>
                <v-text-field
                  dense
                  v-model="searching.disbursement_date_loan"
                  type="text"
                  :label="'Buscar ' + header.text"
                  @keydown.enter="search_payments()"
                  hide-details
                  single-line
                ></v-text-field>
              </div>
            </v-menu>
          </template>-->
          <template v-slot:[`header.state_type_affiliate`]="{ header }">
            {{ header.text }}<br>
            <v-menu offset-y :close-on-content-click="false">
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon v-bind="attrs" v-on="on">
                  <v-icon small :color="searching.state_type_affiliate !='' ? 'red' : 'black'">
                    mdi-filter
                  </v-icon>
                </v-btn>
              </template>
              <div>
                <v-text-field
                  dense
                  v-model="searching.state_type_affiliate"
                  type="text"
                  :label="'Buscar ' + header.text"
                  @keydown.enter="search_payments()"
                  hide-details
                  single-line
                ></v-text-field>
              </div>
            </v-menu>
          </template>

          <template v-slot:[`header.code_payment`]="{ header }">
            {{ header.text }}<br>
            <v-menu offset-y :close-on-content-click="false">
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon v-bind="attrs" v-on="on">
                  <v-icon small :color="searching.code_payment !='' ? 'red' : 'black'">
                    mdi-filter
                  </v-icon>
                </v-btn>
              </template>
              <div>
                <v-text-field
                  dense
                  v-model="searching.code_payment"
                  type="text"
                  :label="'Buscar ' + header.text"
                  @keydown.enter="search_payments()"
                  hide-details
                  single-line
                ></v-text-field>
              </div>
            </v-menu>
          </template>

          <template v-slot:[`header.estimated_quota_payment`]="{ header }">
            {{ header.text }}<br>
            <v-menu offset-y :close-on-content-click="false">
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon v-bind="attrs" v-on="on">
                  <v-icon small :color="searching.estimated_quota_payment !='' ? 'red' : 'black'">
                    mdi-filter
                  </v-icon>
                </v-btn>
              </template>
              <div>
                <v-text-field
                  dense
                  v-model="searching.estimated_quota_payment"
                  type="text"
                  :label="'Buscar ' + header.text"
                  @keydown.enter="search_payments()"
                  hide-details
                  single-line
                ></v-text-field>
              </div>
            </v-menu>
          </template>

          <template v-slot:[`header.voucher_payment`]="{ header }">
            {{ header.text }}<br>
            <v-menu offset-y :close-on-content-click="false">
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon v-bind="attrs" v-on="on">
                  <v-icon small :color="searching.voucher_payment !='' ? 'red' : 'black'">
                    mdi-filter
                  </v-icon>
                </v-btn>
              </template>
              <div>
                <v-text-field
                  dense
                  v-model="searching.voucher_payment"
                  type="text"
                  :label="'Buscar ' + header.text"
                  @keydown.enter="search_payments()"
                  hide-details
                  single-line
                ></v-text-field>
              </div>
            </v-menu>
          </template>

          <!--<template v-slot:[`header.estimated_date_payment`]="{ header }">
            {{ header.text }}<br>
            <v-menu offset-y :close-on-content-click="false">
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon v-bind="attrs" v-on="on">
                  <v-icon small :color="searching.estimated_date_payment !='' ? 'red' : 'black'">
                    mdi-filter
                  </v-icon>
                </v-btn>
              </template>
              <div>
                <v-text-field
                  dense
                  v-model="searching.estimated_date_payment"
                  type="text"
                  :label="'Buscar ' + header.text"
                  @keydown.enter="search_payments()"
                  hide-details
                  single-line
                ></v-text-field>
              </div>
            </v-menu>
          </template>-->

          <template v-slot:[`header.registration_affiliate`]="{ header }">
            {{ header.text }}<br>
            <v-menu offset-y :close-on-content-click="false">
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon v-bind="attrs" v-on="on">
                  <v-icon small :color="searching.registration_affiliate !='' ? 'red' : 'black'">
                    mdi-filter
                  </v-icon>
                </v-btn>
              </template>
              <div>
                <v-text-field
                  dense
                  v-model="searching.registration_affiliate"
                  type="text"
                  :label="'Buscar ' + header.text"
                  @keydown.enter="search_payments()"
                  hide-details
                  single-line
                ></v-text-field>
              </div>
            </v-menu>
          </template>

          <template v-slot:[`header.identity_card_affiliate`]="{ header }">
            {{ header.text }}<br>
            <v-menu offset-y :close-on-content-click="false">
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon v-bind="attrs" v-on="on">
                  <v-icon small :color="searching.identity_card_affiliate !='' ? 'red' : 'black'">
                    mdi-filter
                  </v-icon>
                </v-btn>
              </template>
              <div>
                <v-text-field
                  dense
                  v-model="searching.identity_card_affiliate"
                  type="text"
                  :label="'Buscar ' + header.text"
                  @keydown.enter="search_payments()"
                  hide-details
                  single-line
                ></v-text-field>
              </div>
            </v-menu>
          </template>
          
          <template v-slot:[`header.first_name_affiliate`]="{ header }">
            {{ header.text }}<br>
            <v-menu offset-y :close-on-content-click="false">
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon v-bind="attrs" v-on="on">
                  <v-icon small :color="searching.first_name_affiliate !='' ? 'red' : 'black'">
                    mdi-filter
                  </v-icon>
                </v-btn>
              </template>
              <div>
                <v-text-field
                  dense
                  v-model="searching.first_name_affiliate"
                  type="text"
                  :label="'Buscar ' + header.text"
                  @keydown.enter="search_payments()"
                  hide-details
                  single-line
                ></v-text-field>
              </div>
            </v-menu>
          </template>

          <template v-slot:[`header.second_name_affiliate`]="{ header }">
            {{ header.text }}<br>
            <v-menu offset-y :close-on-content-click="false">
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon v-bind="attrs" v-on="on">
                  <v-icon small :color="searching.second_name_affiliate !='' ? 'red' : 'black'">
                    mdi-filter
                  </v-icon>
                </v-btn>
              </template>
              <div>
                <v-text-field
                  dense
                  v-model="searching.second_name_affiliate"
                  type="text"
                  :label="'Buscar ' + header.text"
                  @keydown.enter="search_payments()"
                  hide-details
                  single-line
                ></v-text-field>
              </div>
            </v-menu>
          </template>

          <template v-slot:[`header.last_name_affiliate`]="{ header }">
            {{ header.text }}<br>
            <v-menu offset-y :close-on-content-click="false">
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon v-bind="attrs" v-on="on">
                  <v-icon small :color="searching.last_name_affiliate !='' ? 'red' : 'black'">
                    mdi-filter
                  </v-icon>
                </v-btn>
              </template>
              <div>
                <v-text-field
                  dense
                  v-model="searching.last_name_affiliate"
                  type="text"
                  :label="'Buscar ' + header.text"
                  @keydown.enter="search_payments()"
                  hide-details
                  single-line
                ></v-text-field>
              </div>
            </v-menu>
          </template>

          <template v-slot:[`header.mothers_last_name_affiliate`]="{ header }">
            {{ header.text }}<br>
            <v-menu offset-y :close-on-content-click="false">
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon v-bind="attrs" v-on="on">
                  <v-icon small :color="searching.mothers_last_name_affiliate !='' ? 'red' : 'black'">
                    mdi-filter
                  </v-icon>
                </v-btn>
              </template>
              <div>
                <v-text-field
                  dense
                  v-model="searching.mothers_last_name_affiliate"
                  type="text"
                  :label="'Buscar ' + header.text"
                  @keydown.enter="search_payments()"
                  hide-details
                  single-line
                ></v-text-field>
              </div>
            </v-menu>
          </template>
          


          <template v-slot:[`header.surname_husband_affiliate`]="{ header }">
            {{ header.text }}<br>
            <v-menu offset-y :close-on-content-click="false">
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon v-bind="attrs" v-on="on">
                  <v-icon small :color="searching.surname_husband_affiliate !='' ? 'red' : 'black'">
                    mdi-filter
                  </v-icon>
                </v-btn>
              </template>
              <div>
                <v-text-field
                  dense
                  v-model="searching.surname_husband_affiliate"
                  type="text"
                  :label="'Buscar ' + header.text"
                  @keydown.enter="search_payments()"
                  hide-details
                  single-line
                ></v-text-field>
              </div>
            </v-menu>
          </template>

           <template v-slot:[`header.name_voucher_type`]="{ header }">
            {{ header.text }}<br>
            <v-menu offset-y :close-on-content-click="false">
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon v-bind="attrs" v-on="on">
                  <v-icon small :color="searching.name_voucher_type !='' ? 'red' : 'black'">
                    mdi-filter
                  </v-icon>
                </v-btn>
              </template>
              <div>
                <v-text-field
                  dense
                  v-model="searching.name_voucher_type"
                  type="text"
                  :label="'Buscar ' + header.text"
                  @keydown.enter="search_payments()"
                  hide-details
                  single-line
                ></v-text-field>
              </div>
            </v-menu>
          </template>

          <template v-slot:[`header.state_payment`]="{ header }">
            {{ header.text }}<br>
            <v-menu offset-y :close-on-content-click="false">
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon v-bind="attrs" v-on="on">
                  <v-icon small :color="searching.state_payment !='' ? 'red' : 'black'">
                    mdi-filter
                  </v-icon>
                </v-btn>
              </template>
              <div>
                <v-text-field
                  dense
                  v-model="searching.state_payment"
                  type="text"
                  :label="'Buscar ' + header.text"
                  @keydown.enter="search_payments()"
                  hide-details
                  single-line
                ></v-text-field>
              </div>
            </v-menu>
          </template>

          <!--<template v-slot:[`header.balance_loan`]="{ header }">
            {{ header.text }}
            <v-menu offset-y :close-on-content-click="false">
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon v-bind="attrs" v-on="on">
                  <v-icon small :color="searching.balance_loan !='' ? 'red' : 'black'">
                    mdi-filter
                  </v-icon>
                </v-btn>
              </template>
              <div>
                <v-text-field
                  dense
                  v-model="searching.balance_loan"
                  type="text"
                  :label="'Buscar ' + header.text"
                  @keydown.enter="search_payments()"
                  hide-details
                  single-line
                ></v-text-field>
              </div>
            </v-menu>
          </template>-->

          <template v-slot:[`header.modality_payment`]="{ header }">
            {{ header.text }}<br>
            <v-menu offset-y :close-on-content-click="false">
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon v-bind="attrs" v-on="on">
                  <v-icon small :color="searching.modality_payment !='' ? 'red' : 'black'">
                    mdi-filter
                  </v-icon>
                </v-btn>
              </template>
              <div>
                <v-text-field
                  dense
                  v-model="searching.modality_payment"
                  type="text"
                  :label="'Buscar ' + header.text"
                  @keydown.enter="search_payments()"
                  hide-details
                  single-line
                ></v-text-field>
              </div>
            </v-menu>
          </template>

          <template v-slot:[`header.sub_modality_shortened_payment`]="{ header }">
            {{ header.text }}<br>
            <v-menu offset-y :close-on-content-click="false">
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon v-bind="attrs" v-on="on">
                  <v-icon small :color="searching.sub_modality_shortened_payment !='' ? 'red' : 'black'">
                    mdi-filter
                  </v-icon>
                </v-btn>
              </template>
              <div>
                <v-text-field
                  dense
                  v-model="searching.sub_modality_shortened_payment"
                  type="text"
                  :label="'Buscar ' + header.text"
                  @keydown.enter="search_payments()"
                  hide-details
                  single-line
                ></v-text-field>
              </div>
            </v-menu>
          </template>

          <template v-slot:[`header.state_loan`]="{ header }">
            {{ header.text }}<br>
            <v-menu offset-y :close-on-content-click="false">
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon v-bind="attrs" v-on="on">
                  <v-icon small :color="searching.state_loan !='' ? 'red' : 'black'">
                    mdi-filter
                  </v-icon>
                </v-btn>
              </template>
              <div>
                <v-text-field
                  dense
                  v-model="searching.state_loan"
                  type="text"
                  :label="'Buscar ' + header.text"
                  @keydown.enter="search_payments()"
                  hide-details
                  single-line
                ></v-text-field>
              </div>
            </v-menu>
          </template>
          
          <template v-slot:[`item.disbursement_date_loan`]="{ item }">
            {{ item.disbursement_date_loan | datetimeshorted}}
          </template>

          <template v-slot:[`item.estimated_date_payment`]="{ item }">
            {{ item.estimated_date_payment | date}}
          </template>

          <template v-slot:[`item.estimated_quota_payment`]="{ item }">
            {{ item.estimated_quota_payment | money}}
          </template>

          <template v-slot:[`item.modality_payment`]="{ item }">
          {{ item.modality_payment }}
          </template>
              <template v-slot:[`item.sub_modality_shortened_payment`]="{ item }">
            <v-tooltip top>
              <template v-slot:activator="{ on }">
                <span v-on="on">{{ item.sub_modality_shortened_payment }}</span>
              </template>
              <span>{{ item.sub_modality_payment }}</span>
            </v-tooltip>
          </template>

          <template v-slot:[`item.actions`]="{ item }" >
            <v-tooltip bottom>
              <template v-slot:activator="{ on }">
                <v-btn
                  icon
                  small
                  v-on="on"
                  color="warning"
                  :to="{ name: 'flowAdd', params: { id: item.id_loan }, query: { workTray: 'all'}}"
                ><v-icon>mdi-eye</v-icon>
                </v-btn>
              </template>
              <span>Ver trámite</span>
            </v-tooltip>
            <v-tooltip bottom >
              <template v-slot:activator="{ on }">
                <v-btn
                  icon
                  small
                  v-on="on"
                  color="teal lighten-3"
                  :to="{ name: 'flowAdd', params: { id: item.id_loan }, query:{ redirectTab: 6 , workTray: 'all'}}"
                ><v-icon>mdi-folder-multiple</v-icon>
                </v-btn>
              </template>
              <span>Kardex</span>
            </v-tooltip>
            <v-menu
                offset-y
                close-on-content-click
                v-if="permissionSimpleSelected.includes('print-contract-loan') || 
                (permissionSimpleSelected.includes('print-payment-plan') && item.state_loan == 'Vigente') || 
                (permissionSimpleSelected.includes('print-payment-kardex-loan') && item.state_loan == 'Vigente')"
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
                    @click="imprimir(doc.id, item.id_loan )"
                  >
                    <v-list-item-icon class="ma-0 py-0 pt-2">
                      <v-icon 
                        class="ma-0 py-0"
                        small
                        v-text="doc.icon"
                        color="light-blue accent-4"
                      ></v-icon>
                    </v-list-item-icon>
                    <v-list-item-title 
                      class="ma-0 py-0 mt-n2">
                      {{ doc.title }}
                    </v-list-item-title>
                  </v-list-item>
                </v-list>
              </v-menu>
          </template>
        </v-data-table>
        </v-card>
      </v-form>
    </ValidationObserver>
  </v-container>
</template>

<script>

export default {
name: 'list-payment-generate',
data () {
  return {
    searching:{
      code_loan: '',
      disbursement_date_loan:'',
      state_type_affiliate: '',
      code_payment:'',
      estimated_quota_payment:'',
      estimated_date_payment:'',
      identity_card_affiliate: '',
      registration_affiliate: '',
      last_name_affiliate: '',
      mothers_last_name_affiliate: '',
      first_name_affiliate: '',
      second_name_affiliate: '',
      surname_husband_affiliate: '',
      voucher_payment:'',
      modality_payment:'',
      sub_modality_shortened_payment:'',
      name_voucher_type:'',
      state_payment:''
    },

    headers: [
      //
      //{ text: 'Fecha Desembolso',value:'disbursement_date_loan',input:'', menu:false,type:"text",class: ['normal', 'white--text','text-md-center'],width: '5%'},
      //{ text: 'Sector',value:'state_type_affiliate',input:'', menu:false,type:"text",class: ['normal', 'white--text','text-md-center'],width: '5%'},
      { text: 'Cód. Pago', value: 'code_payment',input:'' , menu:false,type:"text",class: ['normal', 'white--text','text-md-center'],width: '5%'},
      { text: 'Cód. Préstamo', value: 'code_loan',input:'' , menu:false,type:"text",class: ['normal', 'white--text','text-md-center'],width: '5%'},
      { text: 'Fecha Pago', value: 'estimated_date_payment',input:'' , menu:false,type:"text",class: ['normal', 'white--text','text-md-center'],width: '5%'},
      { text: 'Total Pagado', value: 'estimated_quota_payment',input:'' , menu:false,type:"text",class: ['normal', 'white--text','text-md-center'],width: '5%'},
      { text: 'CI', value: 'identity_card_affiliate',input:'' , menu:false,type:"text",class: ['normal', 'white--text','text-md-center'],width: '5%'},
      { text: 'Matricula', value: 'registration_affiliate' ,input:'', menu:false,type:"text",class: ['normal', 'white--text','text-md-center'],width: '5%'},
      { text: '1er Nombre', value: 'first_name_affiliate',input:'' , menu:false,type:"text",class: ['normal', 'white--text','text-md-center'],width: '5%'},
      { text: '2do Nombre', value: 'second_name_affiliate',input:'' , menu:false,type:"text",class: ['normal', 'white--text','text-md-center'],width: '5%'},
      { text: 'Ap. Paterno', value: 'last_name_affiliate',input:'', menu:false,type:"text",class: ['normal', 'white--text','text-md-center'],width: '5%'},
      { text: 'Ap. Materno',value:'mothers_last_name_affiliate',input:'', menu:false,type:"text",class: ['normal', 'white--text','text-md-center'],width: '5%'},
      { text: 'Ap. Casada',value:'surname_husband_affiliate',input:'', menu:false,type:"text",class: ['normal', 'white--text','text-md-center'],width: '5%'},
      { text: 'Comprobante',value:'voucher_payment',input:'', menu:false,type:"text",class: ['normal', 'white--text','text-md-center'],width: '5%'},
      { text: 'Modalida pago',value:'modality_payment',input:'', menu:false,type:"text",class: ['normal', 'white--text','text-md-center'],width: '5%'},
      { text: 'Tpo de Amortización',value:'sub_modality_shortened_payment',input:'', menu:false,type:"text",class: ['normal', 'white--text','text-md-center'],width: '5%'},
      { text: 'Tipo de pago',value:'name_voucher_type',input:'', menu:false,type:"text",class: ['normal', 'white--text','text-md-center'],width: '5%'},
      { text: 'Estado',value:'state_payment',input:'', menu:false,type:"text",class: ['normal', 'white--text','text-md-center'],width: '5%'},
      { text: 'Accion',value:'actions',input:'', menu:false,type:"text",class: ['normal', 'white--text','text-md-center'], sortable: false,width: '10%'},
    ],

    items: [
      {
        name: "SI",
        value: 'TRUE',
      },
      {
        name: "NO",
        value: 'FALSE',
      },
      {
        name: "TODOS",
        value: '',
      },
    ],
    loans: [],
    printDocs: [],
    options: {
      page: 1,
      itemsPerPage: 5,
      sortBy: ["code_loan"],
      sortDesc: [false],
    },
    totalAffiliates :0,
    excel: false
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
      if (
        newVal.page != oldVal.page ||
        newVal.itemsPerPage != oldVal.itemsPerPage ||
        newVal.sortBy != oldVal.sortBy ||
        newVal.sortDesc != oldVal.sortDesc){

        this.search_payments()
      }
    },

  },
    mounted(){
      this.search_payments();
      this.docsLoans()
    },

   methods: {
    async search_payments(){
        try {
            let res = await axios.get(`list_loan_payments_generate`,{
              params:{
                code_loan: this.searching.code_loan,
                disbursement_date_loan: this.searching.disbursement_date_loan,
                state_type_affiliate: this.searching.state_type_affiliate,
                code_payment: this.searching.code_payment,
                estimated_quota_payment:this.searching.estimated_quota_payment,
                estimated_date_payment:this.searching.estimated_date_payment,
                identity_card_affiliate: this.searching.identity_card_affiliate,
                registration_affiliate: this.searching.registration_affiliate,
                last_name_affiliate: this.searching.last_name_affiliate,
                mothers_last_name_affiliate: this.searching.mothers_last_name_affiliate,
                first_name_affiliate: this.searching.first_name_affiliate,
                second_name_affiliate: this.searching.second_name_affiliate,
                surname_husband_affiliate: this.searching.surname_husband_affiliate,
                voucher_payment:this.searching.voucher_payment,
                modality_payment:this.searching.modality_payment,
                name_voucher_type:this.searching.name_voucher_type,
                state_payment:this.searching.state_payment,
                excel: false,
                page: this.options.page,
                per_page: this.options.itemsPerPage,
                sortBy: this.options.sortBy,
                sortDesc: this.options.sortDesc,
              }
            })
            this.loans = res.data.data
            this.totalAffiliates = res.data.total
            delete res.data['data']
            this.options.page = res.data.current_page
            this.options.itemsPerPage = parseInt(res.data.per_page)
            this.options.totalItems = res.data.total
        } catch (e) {
            console.log(e)
        }
    },

      async dowload_payments() {
      await axios({
        url: "/list_loan_payments_generate",
        method: "GET",
        responseType: "blob", // important
        headers: { Accept: "application/vnd.ms-excel" },
        data: this.datos,
        params:{
          code_loan: this.searching.code_loan,
          disbursement_date_loan: this.searching.disbursement_date_loan,
          state_type_affiliate: this.searching.state_type_affiliate,
          code_payment: this.searching.code_payment,
          estimated_quota_payment:this.searching.estimated_quota_payment,
          estimated_date_payment:this.searching.estimated_date_payment,
          identity_card_affiliate: this.searching.identity_card_affiliate,
          registration_affiliate: this.searching.registration_affiliate,
          last_name_affiliate: this.searching.last_name_affiliate,
          mothers_last_name_affiliate: this.searching.mothers_last_name_affiliate,
          first_name_affiliate: this.searching.first_name_affiliate,
          second_name_affiliate: this.searching.second_name_affiliate,
          surname_husband_affiliate: this.searching.surname_husband_affiliate,
          voucher_payment:this.searching.voucher_payment,
          modality_payment:this.searching.modality_payment,
          sub_modality_shortened_payment: this.searching.sub_modality_shortened_payment,
          name_voucher_type:this.searching.name_voucher_type,
          state_payment:this.searching.state_payment,
          excel:true
        }
      })
        .then(response => {
          console.log(response);
          const url = window.URL.createObjectURL(new Blob([response.data]));
          const link = document.createElement("a");
          link.href = url;
          link.setAttribute("download", "ReporteAmortizaciones.xls");
          document.body.appendChild(link);
          link.click();
        })
        .catch(error => {
          console.log(error);
        });
    },
    clearAll(){
      this.searching.code_loan= '',
      this.searching.disbursement_date_loan= '',
      this.searching.state_type_affiliate= '',
      this.searching.code_payment='',
      this.searching.estimated_quota_payment='',
      this.searching.estimated_date_payment='',
      this.searching.identity_card_affiliate= '',
      this.searching.registration_affiliate= '',
      this.searching.last_name_affiliate= '',
      this.searching.mothers_last_name_affiliate= '',
      this.searching.first_name_affiliate= '',
      this.searching.second_name_affiliate= '',
      this.searching.surname_husband_affiliate= '',
      this.searching.voucher_payment='',
      this.searching.modality_payment='',
      this.searching.sub_modality_shortened_payment='',
      this.searching.name_voucher_type='',
      this.searching.state_payment='',
      this.search_payments()
    },
    
      async imprimir(id, item)
    {
      try {
        let res;
        if (id == 5) {
          res = await axios.get(`loan_payment/${item}/print/loan_payment`);
        } else if(id == 6){
          let resv = await axios.get(`loan_payment/${item}/voucher`)
          let idVoucher = resv.data.id
          res = await axios.get(`voucher/${idVoucher}/print/voucher`);
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
      let docs = [];
      if (this.permissionSimpleSelected.includes("print-payment-loan")) {
        docs.push({ id: 5, title: "Registro de cobro", icon: "mdi-file-check-outline" });
      }
      if (this.permissionSimpleSelected.includes("print-payment-voucher")) {
        docs.push({ id: 6, title: "Registro de pago", icon: "mdi-cash-multiple" });
      } else {
        console.log("Se ha producido un error durante la generación de la impresión");
      }
      this.printDocs = docs;
      console.log(this.printDocs);
      },

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
</style>
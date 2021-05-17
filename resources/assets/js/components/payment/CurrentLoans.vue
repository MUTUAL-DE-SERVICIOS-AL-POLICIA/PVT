<template>
  <v-container fluid>
    <ValidationObserver>
      <v-form>
        <v-card flat>

        <v-tooltip top>
            <template v-slot:activator="{ on }">
        <v-btn 
          fab
          @click="download_loans()" 
          color="success"  
          class="mb-2" 
          v-on="on"
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
          @click="clearAll()" 
          color="info"  
          class="mb-2"
          v-on="on"
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
                  @keydown.enter="search_loans()"
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
                  @keydown.enter="search_loans()"
                  hide-details
                  single-line
                ></v-text-field>
              </div>
            </v-menu>
          </template>

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
                  @keydown.enter="search_loans()"
                  hide-details
                  single-line
                ></v-text-field>
              </div>
            </v-menu>
          </template>

          <template v-slot:[`header.registration_affiliateF`]="{ header }">
            {{ header.text }}<br>
            <v-menu offset-y :close-on-content-click="false">
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon v-bind="attrs" v-on="on">
                  <v-icon small :color="searching.registration_affiliateF !='' ? 'red' : 'black'">
                    mdi-filter
                  </v-icon>
                </v-btn>
              </template>
              <div>
                <v-text-field
                  dense
                  v-model="searching.registration_affiliateF"
                  type="text"
                  :label="'Buscar ' + header.text"
                  @keydown.enter="search_loans()"
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
                  @keydown.enter="search_loans()"
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
                  @keydown.enter="search_loans()"
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
                  @keydown.enter="search_loans()"
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
                  @keydown.enter="search_loans()"
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
                  @keydown.enter="search_loans()"
                  hide-details
                  single-line
                ></v-text-field>
              </div>
            </v-menu>
          </template>

           <!--<template v-slot:[`header.modality_loan`]="{ header }">
            {{ header.text }}<br>
            <v-menu offset-y :close-on-content-click="false">
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon v-bind="attrs" v-on="on">
                  <v-icon small :color="searching.modality_loan !='' ? 'red' : 'black'">
                    mdi-filter
                  </v-icon>
                </v-btn>
              </template>
              <div>
                <v-text-field
                  dense
                  v-model="searching.modality_loan"
                  type="text"
                  :label="'Buscar ' + header.text"
                  @keydown.enter="search_loans()"
                  hide-details
                  single-line
                ></v-text-field>
              </div>
            </v-menu>
          </template>

          <template v-slot:[`header.disbursement_date_loan`]="{ header }">
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
                  @keydown.enter="search_loans()"
                  hide-details
                  single-line
                ></v-text-field>
              </div>
            </v-menu>
          </template>

          <template v-slot:[`header.amount_approved_loan`]="{ header }">
            {{ header.text }}<br>
            <v-menu offset-y :close-on-content-click="false">
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon v-bind="attrs" v-on="on">
                  <v-icon small :color="searching.amount_approved_loan !='' ? 'red' : 'black'">
                    mdi-filter
                  </v-icon>
                </v-btn>
              </template>
              <div>
                <v-text-field
                  dense
                  v-model="searching.amount_approved_loan"
                  type="text"
                  :label="'Buscar ' + header.text"
                  @keydown.enter="search_loans()"
                  hide-details
                  single-line
                ></v-text-field>
              </div>
            </v-menu>
          </template>

          <template v-slot:[`header.quota_loan`]="{ header }">
            {{ header.text }}<br>
            <v-menu offset-y :close-on-content-click="false">
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon v-bind="attrs" v-on="on">
                  <v-icon small :color="searching.quota_loan !='' ? 'red' : 'black'">
                    mdi-filter
                  </v-icon>
                </v-btn>
              </template>
              <div>
                <v-text-field
                  dense
                  v-model="searching.quota_loan"
                  type="text"
                  :label="'Buscar ' + header.text"
                  @keydown.enter="search_loans()"
                  hide-details
                  single-line
                ></v-text-field>
              </div>
            </v-menu>
          </template>

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
                  @keydown.enter="search_loans()"
                  hide-details
                  single-line
                ></v-text-field>
              </div>
            </v-menu>
          </template>
          
          <template v-slot:[`header.guarantor_loan_affiliate`]="{ header }">
            {{ header.text }}<br>
            <v-menu offset-y :close-on-content-click="false">
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon v-bind="attrs" v-on="on">
                  <v-icon small :color="searching.guarantor_loan_affiliate !='' ? 'red' : 'black' ">
                    mdi-filter
                  </v-icon>
                </v-btn>
              </template>
              <div>
                <v-select
                  dense
                  :items="items"
                  item-text="name"
                  item-value="value"
                  v-model="searching.guarantor_loan_affiliate"
                  :label="'Buscar ' + header.text"
                  @change="search_loans()"
                  hide-details
                  single-line
                ></v-select>
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
                  @keydown.enter="search_loans()"
                  hide-details
                  single-line
                ></v-text-field>

              </div>
            </v-menu>
          </template>-->

          <template v-slot:[`item.modality_loan`]="{ item }">
            <v-tooltip top>
              <template v-slot:activator="{ on }">
                <span v-on="on">{{ item.modality_loan }}</span>
              </template>
              <span>{{ item.sub_modality_loan }}</span>
            </v-tooltip>
          </template>
              <template v-slot:[`item.guarantor_loan_affiliate`]="{ item }">
            {{ item.guarantor_loan_affiliate? 'SI':'NO'}}
          </template>
              <template v-slot:[`item.amount_approved_loan`]="{ item }">
            {{ item.amount_approved_loan | money}}
          </template>
          <template v-slot:[`item.balance_loan`]="{ item }">
            {{ item.balance_loan | money}}
          </template>
              <template v-slot:[`item.quota_loan`]="{ item }">
            {{ item.quota_loan | moneyString}}
          </template>
              <template v-slot:[`item.disbursement_date_loan`]="{ item }">
            {{ item.disbursement_date_loan}}
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
            <v-tooltip bottom v-if="item.state_loan == 'Vigente'">
              <template v-slot:activator="{ on }">
                <v-btn
                  icon
                  small
                  v-on="on"
                  color="teal lighten-3"
                  :to="{ name: 'flowAdd', params: { id: item.id_loan }, query:{ redirectTab: 6 }}"
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
name: 'list-loans-generate',
data () {
  return {
    searching:{
      code_loan: '',
      identity_card_affiliate: '',
      registration_affiliate: '',
      last_name_affiliate: '',
      mothers_last_name_affiliate: '',
      first_name_affiliate: '',
      second_name_affiliate: '',
      surname_husband_affiliate: '',
      modality_loan: '',
      disbursement_date_loan: '',
      amount_approved_loan: '',
      state_type_affiliate: '',
      quota_loan: '',
      state_loan: '',
      guarantor_loan_affiliate: '',
    },
      headers: [
        { text: 'Cód. Préstamo', value: 'code_loan',input:'' , menu:false,type:"text",class: ['normal', 'white--text','text-md-center'],width: '5%'},
        { text: 'CI', value: 'identity_card_affiliate',input:'' , menu:false,type:"text",class: ['normal', 'white--text','text-md-center'],width: '5%'},
        { text: 'Matricula', value: 'registration_affiliate' ,input:'', menu:false,type:"text",class: ['normal', 'white--text','text-md-center'],width: '5%'},
        { text: '1er Nombre', value: 'first_name_affiliate',input:'' , menu:false,type:"text",class: ['normal', 'white--text','text-md-center'],width: '5%'},
        { text: '2do Nombre', value: 'second_name_affiliate',input:'' , menu:false,type:"text",class: ['normal', 'white--text','text-md-center'],width: '5%'},
        { text: 'Ap. Paterno', value: 'last_name_affiliate',input:'', menu:false,type:"text",class: ['normal', 'white--text','text-md-center'],width: '5%'},
        { text: 'Ap. Materno',value:'mothers_last_name_affiliate',input:'', menu:false,type:"text",class: ['normal', 'white--text','text-md-center'],width: '5%'},
        { text: 'Ap. Casada',value:'surname_husband_affiliate',input:'', menu:false,type:"text",class: ['normal', 'white--text','text-md-center'],width: '5%'},
        { text: 'Modalidad',value:'modality_loan',input:'', menu:false,type:"text",class: ['normal', 'white--text','text-md-center'],width: '5%'},
        //{ text: 'Fecha Desembolso',value:'disbursement_date_loan',input:'', menu:false,type:"text",class: ['normal', 'white--text','text-md-center'],width: '5%'},
        { text: 'Monto Desembolsado',value:'amount_approved_loan',input:'', menu:false,type:"text",class: ['normal', 'white--text','text-md-center'],width: '5%'},
        { text: 'Saldo Capital',value:'balance_loan',input:'', menu:false,type:"text",class: ['normal', 'white--text','text-md-center'],width: '5%'},
        { text: 'Sector',value:'state_type_affiliate',input:'', menu:false,type:"text",class: ['normal', 'white--text','text-md-center'],width: '5%'},
        { text: 'Cuota',value:'quota_loan',input:'', menu:false,type:"text",class: ['normal', 'white--text','text-md-center'],width: '5%'},
        //{ text: 'Garante?',value:'guarantor_loan_affiliate',class: ['normal', 'white--text','text-md-center'],width: '5%'},
        { text: 'Estado',value:'state_loan',input:'', menu:false,type:"text",class: ['normal', 'white--text','text-md-center'],width: '5%'},
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
    totalAffiliates :0
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
         this.search_loans()
      }
    },

  },
    mounted(){
        this.search_loans();
         this.docsLoans()
    },
  methods: {
    async search_loans(){
      try {
        let res = await axios.get(`list_loan_generate`,{
          params:{
            code_loan: this.searching.code_loan,
            identity_card_affiliate: this.searching.identity_card_affiliate,
            registration_affiliate: this.searching.registration_affiliateF,
            last_name_affiliate: this.searching.last_name_affiliate,
            mothers_last_name_affiliate: this.searching.mothers_last_name_affiliate,
            first_name_affiliate: this.searching.first_name_affiliate,
            second_name_affiliate: this.searching.second_name_affiliate,
            surname_husband_affiliate: this.searching.surname_husband_affiliate,
            modality_loan: this.searching.modality_loan,
            disbursement_date_loan: this.searching.disbursement_date_loan,
            amount_approved_loan: this.searching.amount_approved_loan,
            state_type_affiliate: this.searching.state_type_affiliate,
            quota_loan: this.searching.quota_loan,
            state_loan: 'Vigente',
            guarantor_loan_affiliate: false,
            excel:false,
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

    async download_loans() {
      await axios({
        url: "/list_loan_generate",
        method: "GET",
        responseType: "blob", // important
        headers: { Accept: "application/vnd.ms-excel" },
        data: this.datos,
        params:{
          code_loan: this.searching.code_loan,
          identity_card_affiliate: this.searching.identity_card_affiliate,
          registration_affiliate: this.searching.registration_affiliateF,
          last_name_affiliate: this.searching.last_name_affiliate,
          mothers_last_name_affiliate: this.searching.mothers_last_name_affiliate,
          first_name_affiliate: this.searching.first_name_affiliate,
          second_name_affiliate: this.searching.second_name_affiliate,
          surname_husband_affiliate: this.searching.surname_husband_affiliate,
          modality_loan: this.searching.modality_loan,
          disbursement_date_loan: this.searching.disbursement_date_loan,
          amount_approved_loan: this.searching.amount_approved_loan,
          state_type_affiliate: this.searching.state_type_affiliate,
          quota_loan: this.searching.quota_loan,
          state_loan: 'Vigente',
          guarantor_loan_affiliate: false,
          excel:true
        }
      })
        .then(response => {
          console.log(response);
          const url = window.URL.createObjectURL(new Blob([response.data]));
          const link = document.createElement("a");
          link.href = url;
          link.setAttribute("download", "ReportePrestamo.xlsx");
          document.body.appendChild(link);
          link.click();
        })
        .catch(error => {
          console.log(error);
        });
    },

    clearAll(){
      this.searching.code_loan= '',
      this.searching.identity_card_affiliate= '',
      this.searching.registration_affiliate= '',
      this.searching.last_name_affiliate= '',
      this.searching.mothers_last_name_affiliate= '',
      this.searching.first_name_affiliate= '',
      this.searching.second_name_affiliate= '',
      this.searching.surname_husband_affiliate= '',
      this.searching.modality_loan= '',
      this.searching.disbursement_date_loan= '',
      this.searching.amount_approved_loan= '',
      this.searching.state_type_affiliate= '',
      this.searching.quota_loan= '',
      this.searching.state_loan= '',
      this.searching.guarantor_loan_affiliate= '',
      this.search_loans()
    },
    
      async imprimir(id, item)
    {
      try {
        let res
        if(id==1){
          res = await axios.get(`loan/${item}/print/contract`)
        }else if(id==2){
          res = await axios.get(`loan/${item}/print/form`)
        }else if(id==3) {
          res = await axios.get(`loan/${item}/print/plan`)
        }else {
          res = await axios.get(`loan/${item}/print/kardex`)
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
        let docs =[]
        if(this.permissionSimpleSelected.includes('print-contract-loan')){
          docs.push(
            { id: 1, title: 'Contrato', icon: 'mdi-file-document'},
            { id: 2, title: 'Solicitud', icon: 'mdi-file'})
        }
        if(this.permissionSimpleSelected.includes('print-payment-plan')){
          docs.push(
            { id: 3, title: 'Plan de pagos', icon: 'mdi-cash'})
        }    
        if(this.permissionSimpleSelected.includes('print-payment-kardex-loan')){
          docs.push(
            { id: 4, title: 'Kardex', icon: 'mdi-view-list'})
        }else{
          console.log("Se ha producido un error durante la generación de la impresión")
        }
        this.printDocs=docs
        console.log(this.printDocs)
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
  border-color: teal;
}
</style>
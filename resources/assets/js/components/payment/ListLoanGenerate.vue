<template>
  <v-container fluid>
    <ValidationObserver>
      <v-form>
        <v-card flat>
          <v-card-title class="pa-0 pb-3">
            <v-toolbar dense color="tertiary" class="font-weight-regular">
              <v-toolbar-title>Préstamos</v-toolbar-title>
            </v-toolbar>
          </v-card-title>
        <v-btn icon  @click="excel()">
            <v-icon color="green">
                mdi-file-excel
            </v-icon>
        </v-btn>
        <v-data-table
          :headers="headers"
          :items="loans"
          :items-per-page="5"
          class="elevation-1"
        >
          <template v-slot:[`header.code_loan`]="{ header }">
            {{ header.text }}
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
                  
            
                  @keydown.enter="search()"
                  hide-details
                  single-line
                ></v-text-field>

              </div>
            </v-menu>
          </template>
                    <template v-slot:[`header.identity_card_affiliate`]="{ header }">
            {{ header.text }}
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
                  

                  @keydown.enter="search()"
                  hide-details
                  single-line
                ></v-text-field>

              </div>
            </v-menu>
          </template>
                    <template v-slot:[`header.registration_affiliateF`]="{ header }">
            {{ header.text }}
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
                  

                  @keydown.enter="search()"
                  hide-details
                  single-line
                ></v-text-field>

              </div>
            </v-menu>
          </template>
                    <template v-slot:[`header.last_name_affiliate`]="{ header }">
            {{ header.text }}
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
                  

                  @keydown.enter="search()"
                  hide-details
                  single-line
                ></v-text-field>

              </div>
            </v-menu>
          </template>
                    <template v-slot:[`header.mothers_last_name_affiliate`]="{ header }">
            {{ header.text }}
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
                  
  
                  @keydown.enter="search()"
                  hide-details
                  single-line
                ></v-text-field>

              </div>
            </v-menu>
          </template>
                    <template v-slot:[`header.first_name_affiliate`]="{ header }">
            {{ header.text }}
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
                  

                  @keydown.enter="search()"
                  hide-details
                  single-line
                ></v-text-field>

              </div>
            </v-menu>
          </template>
                              <template v-slot:[`header.second_name_affiliate`]="{ header }">
            {{ header.text }}
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
                  

                  @keydown.enter="search()"
                  hide-details
                  single-line
                ></v-text-field>

              </div>
            </v-menu>
          </template>
                              <template v-slot:[`header.surname_husband_affiliate`]="{ header }">
            {{ header.text }}
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
                  

                  @keydown.enter="search()"
                  hide-details
                  single-line
                ></v-text-field>

              </div>
            </v-menu>
          </template>
                              <template v-slot:[`header.sub_modality_loan`]="{ header }">
            {{ header.text }}
            <v-menu offset-y :close-on-content-click="false">
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon v-bind="attrs" v-on="on">
                  <v-icon small :color="searching.sub_modality_loan !='' ? 'red' : 'black'">
                    mdi-filter
                  </v-icon>
                </v-btn>
              </template>
              <div>
                <v-text-field
                  dense
                  v-model="searching.sub_modality_loan"
                  type="text"
                  :label="'Buscar ' + header.text"
                  

                  @keydown.enter="search()"
                  hide-details
                  single-line
                ></v-text-field>

              </div>
            </v-menu>
          </template>
                              <template v-slot:[`header.modality_loan`]="{ header }">
            {{ header.text }}
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
                  

                  @keydown.enter="search()"
                  hide-details
                  single-line
                ></v-text-field>

              </div>
            </v-menu>
          </template>

                 <template v-slot:[`header.state_type_affiliate`]="{ header }">
            {{ header.text }}
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
                  

                  @keydown.enter="search()"
                  hide-details
                  single-line
                ></v-text-field>

              </div>
            </v-menu>
          </template>

                     <template v-slot:[`header.state_loan`]="{ header }">
            {{ header.text }}
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
                  

                  @keydown.enter="search()"
                  hide-details
                  single-line
                ></v-text-field>

              </div>
            </v-menu>
          </template>

        </v-data-table>
        </v-card>
      </v-form>
    </ValidationObserver>
  </v-container>
</template>

<script>
  // Table info.
  //import tableData from './sampleDataTable';
  export default {
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
          sub_modality_loan: '',
          modality_loan: '',
          amount_approved_loan: '',
          state_type_affiliate: '',
          quota_loan: '',
          state_loan: '',
          guarantor_loan_affiliate: '',
        },

              headers: [
            { text: 'Cód. Préstamo', value: 'code_loan',input:'' , menu:false,type:"text",class: ['normal', 'white--text']},
            { text: 'CI', value: 'identity_card_affiliate',input:'' , menu:false,type:"text",class: ['normal', 'white--text']},
            { text: 'Matricula', value: 'registration_affiliate' ,input:'', menu:false,type:"text",class: ['normal', 'white--text']},
            { text: '1er Nombre', value: 'first_name_affiliate',input:'' , menu:false,type:"text",class: ['normal', 'white--text']},
            { text: '2do Nombre', value: 'second_name_affiliate',input:'' , menu:false,type:"text",class: ['normal', 'white--text']},
            { text: 'Ap. Paterno', value: 'last_name_affiliate',input:'', menu:false,type:"text",class: ['normal', 'white--text']},
            { text: 'Ap. Materno',value:'mothers_last_name_affiliate',input:'', menu:false,type:"text",class: ['normal', 'white--text']},
            { text: 'Ap. Casada',value:'surname_husband_affiliate',input:'', menu:false,type:"text",class: ['normal', 'white--text']},
            { text: 'Modalidad',value:'modality_loan',input:'', menu:false,type:"text",class: ['normal', 'white--text']},
            { text: 'Submodalidad',value:'sub_modality_loan',input:'', menu:false,type:"text",class: ['normal', 'white--text']},
            { text: 'Monto Desembolsado',value:'amount_approved_loan',input:'', menu:false,type:"text",class: ['normal', 'white--text']},
            { text: 'Saldo Capital',value:'amount_approved_loan',input:'', menu:false,type:"text",class: ['normal', 'white--text']},
            { text: 'Cuota',value:'quota_loan',input:'', menu:false,type:"text",class: ['normal', 'white--text']},            
            { text: 'Sector',value:'state_type_affiliate',input:'', menu:false,type:"text",class: ['normal', 'white--text']},
            { text: 'Garante?',value:'guarantor_loan_affiliate',input:'', menu:false,type:"text",class: ['normal', 'white--text']},
            { text: 'Estado',value:'state_loan',input:'', menu:false,type:"text",class: ['normal', 'white--text']},
            { text: 'Accion',value:'actions',input:'', menu:false,type:"text",class: ['normal', 'white--text']},
        ],
        loans: [],
      }
    },
   computed: {

  },
      mounted()
    {//this.docsLoans()
        this.search();
    },
   methods: {
    async search(){
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
                sub_modality_loan: this.searching.sub_modality_loan,
                modality_loan: this.searching.modality_loan,
                amount_approved_loan: this.searching.amount_approved_loan,
                state_type_affiliate: this.searching.state_type_affiliate,
                quota_loan: this.searching.quota_loan,
                state_loan: this.searching.state_loan,
                guarantor_loan_affiliate: this.searching.guarantor_loan_affiliate,
                excel:false
              }
            } )
            this.loans = res.data.data
        } catch (e) {
            console.log(e)
        }
    },

    async excel() {
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
          sub_modality_loan: this.searching.sub_modality_loan,
          modality_loan: this.searching.modality_loan,
          amount_approved_loan: this.searching.amount_approved_loan,
          state_type_affiliate: this.searching.state_type_affiliate,
          quota_loan: this.searching.quota_loan,
          state_loan: this.searching.state_loan,
          guarantor_loan_affiliate: this.searching.guarantor_loan_affiliate,
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

    deleteSearchC(){
      this.searching.code_loan = ''
        if(this.searching.code_loan == '')
          this.search()
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
}
</style>
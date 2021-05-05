<template>
  <v-card flat >
    <v-card-title>
      <v-toolbar dense color="tertiary">
        <v-toolbar-title>Préstamos</v-toolbar-title>
      </v-toolbar>
    </v-card-title>
    <v-card-text v-if="permissionSimpleSelected.includes('show-history-loan')">
      <v-container class="py-0 px-0">
        <ValidationObserver ref="observer">
          <v-form>
            <!--v-card-->
            <v-row justify="center">
              <v-col cols="12" md="4">
                <v-card>
                  <v-container class="py-0 pb-2">
                    <v-row>
                      <v-col cols="12" md="4"></v-col>
                      <v-col cols="12" md="6"> Afiliado </v-col>
                      <v-col cols="12" md="2"></v-col>
                      <v-col cols="12" md="1"></v-col>
                      <v-col cols="12" md="8">
                        <ValidationProvider v-slot="{ errors }" 
                        vid="affiliate_ci" 
                        name="CI ó Matrícula" 
                        rules="required|min:3|max:20">
                        <v-text-field
                        :error-messages="errors"
                          dense
                          label="CI ó Matrícula"
                          v-model="affiliate_ci"
                          class="py-0"
                          single-line
                          hide-details
                          clearable
                          :loading="loading"
                          v-on:keyup.enter="validar()"
                        ></v-text-field>
                        </ValidationProvider>
                      </v-col>
                      <v-col cols="12" md="2">
                        <v-tooltip top>
                          <template v-slot:activator="{ on }">
                            <v-btn
                              fab
                              dark
                              x-small
                              v-on="on"
                              color="info"
                              :loading="loading"
                              @click.stop="validar()"
                            >
                              <v-icon>mdi-magnify</v-icon>
                            </v-btn>
                          </template>
                          <span>Buscar afiliado</span>
                        </v-tooltip>
                      </v-col>
                    </v-row>
                  </v-container>
                </v-card>
              </v-col>
              <v-col cols="12" md="8">
                <v-card>
                  <!--affiliados-->
                  <v-row v-if="history_affiliate != null && ver">
                    <template>
                      <v-col cols="12" md="12" class="ma-0 pb-0 text-center">
                        <h2 style="color: teal">
                          TITULAR: {{history_affiliate.degree ? history_affiliate.degree.shortened : ''}} 
                          {{$options.filters.fullName(history_affiliate, true)}}
                        <v-tooltip left>
                          <template v-slot:activator="{ on }">
                            <v-btn
                              icon
                              dark
                              x-large
                              color="warning"
                              bottom
                              right
                              v-on="on"
                              :to="{name: 'affiliateAdd', params: { id: history_affiliate.id, workTray: 'received'}}"
                              target="_blank"
                            >
                              <v-icon>mdi-eye</v-icon>
                            </v-btn>
                          </template>
                          <span>Ver datos del afiliado</span>
                        </v-tooltip>
                        </h2></v-col
                      >
                      <v-progress-linear></v-progress-linear>
                      <v-col cols="12" md="3" class="ma-0 pb-0">
                        <div>C.I: {{ history_affiliate.identity_card }}</div>
                        <div>MATRÍCULA: {{ history_affiliate.registration }} </div>

                      </v-col>
                      <v-col cols="12" md="3" class="ma-0 pb-0">
                        <div> CATEGORÍA: {{ history_affiliate.category ? history_affiliate.category.name : ''}}</div>
                        <div> ESTADO: {{ history_affiliate.state ? history_affiliate.state.name : '' }}</div>
                      </v-col>
                       <v-col cols="12" md="6" class="ma-0 pb-0">
                        <div>GRADO: {{ history_affiliate.degree ? history_affiliate.degree.name : '' }}</div>
                        <div>UNIDAD: {{ history_affiliate.unit ? history_affiliate.unit.name : '' }}</div>
                      </v-col>

                      <v-col cols="12"  md="8" class="font-weight-black caption ma-0 py-0"
                      >
                        NRO DE PRÉSTAMOS SOLICITADOS: {{loans_lender.loans ? loans_lender.loans.length : 0}}
                      </v-col>
                      <v-col cols="12" md="8"  class="font-weight-black caption ma-0 pt-0 pb-1"
                      >
                        NRO DE PRÉSTAMOS GARANTIZADOS: {{loans_lender.guarantees ? loans_lender.guarantees.length : 0}}
                      </v-col>
                    </template>
                  </v-row>

                  <!--conygue affiliada-->
                  <v-row v-if="history_spouse != null && ver">
                    <template v-if="history_spouse.origin == 'affiliate'">
                      <v-col cols="12" md="12" class="ma-0 pb-0 text-center">
                        <h2 style="color: teal">
                          CONYUGUE: {{history_spouse.degree ? history_spouse.degree.shortened : ''}} 
                          {{$options.filters.fullName(history_spouse, true) }}
                        <v-tooltip left>
                          <template v-slot:activator="{ on }">
                            <v-btn
                              icon
                              dark
                              x-large
                              color="warning"
                              bottom
                              right
                              v-on="on"
                              :to="{name: 'affiliateAdd', params: { id: history_spouse.id, workTray: 'received'}}"
                              target="_blank"
                            >
                              <v-icon>mdi-eye</v-icon>
                            </v-btn>
                          </template>
                          <span>Ver datos del afiliado</span>
                        </v-tooltip>
                        </h2>
                      </v-col>
                      <v-progress-linear></v-progress-linear>
                      <v-col cols="12" md="3" class="ma-0 pb-0">
                        <div>C.I: {{ history_spouse.identity_card }}</div>
                        <div>MATRÍCULA: {{ history_spouse.registration }} </div>
                      </v-col>
                      <v-col cols="12" md="3" class="ma-0 pb-0">
                        <div> CATEGORÍA: {{ history_spouse.category ? history_spouse.category.name : ''}}</div>
                        <div> ESTADO: {{ history_spouse.state ? history_spouse.state.name : '' }}</div>
                      </v-col>
                       <v-col cols="12" md="6" class="ma-0 pb-0">
                        <div>GRADO: {{ history_spouse.degree ? history_spouse.degree.name : '' }}</div>
                        <div>UNIDAD: {{ history_spouse.unit ? history_spouse.unit.name : '' }}</div>
                      </v-col>

                      <v-col cols="12"  md="8" class="font-weight-black caption ma-0 py-0">
                        NRO DE PRÉSTAMOS SOLICITADOS: {{loans_spouse.loans ? loans_spouse.loans.length : 0}}
                      </v-col>
                      <v-col cols="12" md="8"  class="font-weight-black caption ma-0 pt-0 pb-1">
                        NRO DE PRÉSTAMOS GARANTIZADOS: {{loans_spouse.guarantees ? loans_spouse.guarantees.length : 0}}
                      </v-col>
                    </template>

                     <!--conygue no afiliada-->
                    <template v-if="history_spouse.origin == 'spouse' && ver">
                      <v-col cols="12" md="12" class="ma-0 pb-0 text-center">
                        <h2 style="color: teal">
                          CONYUGUE: {{$options.filters.fullName(history_spouse, true)}}
                        </h2></v-col
                      >
                       <v-progress-linear></v-progress-linear>
                      <v-col cols="12" md="3" class="ma-0 pb-0">
                        <div>C.I: {{ history_spouse.identity_card }}</div>
                        <div>MATRÍCULA: {{ history_spouse.registration }} </div>

                      </v-col>
                      <v-col cols="12" md="3" class="ma-0 pb-0">
                        <div> APELLIDO DE CASADA: {{ history_spouse.surname_husband ? history_spouse.surname_husband : ''}}</div>
                        <div> ESTADO CIVIL: {{ history_spouse.civil_status ? history_spouse.civil_status : '' }}</div>
                      </v-col>
                       <v-col cols="12" md="6" class="ma-0 pb-0">
                        <div>FECHA DE NACIMIENTO: {{ history_spouse.birth_date ? history_spouse.birth_date : '' }}</div>
                        <div>NUMERO DE CERIFICADO DE DEFUNCIÓN: {{ history_spouse.death_certificate_number ? history_spouse.death_certificate_number : '' }}</div>
                      </v-col>

                      <v-col cols="12"  md="8" class="font-weight-black caption ma-0 py-0">
                        NRO DE PRÉSTAMOS SOLICITADOS: {{loans_spouse.loans ? loans_spouse.loans.length: 0}}
                      </v-col>
                      <v-col cols="12" md="8"  class="font-weight-black caption ma-0 pt-0 pb-1">
                        NRO DE PRÉSTAMOS GARANTIZADOS: {{loans_spouse.guarantees ? loans_spouse.guarantees.length : 0}}
                      </v-col>
                    </template>
                  </v-row>
                    <!--observables-->
                  <v-row class="ma-0 pb-0 text-uppercase" v-if="history_observables != null && ver">
                    <v-col class="text-center" cols="12" md="12">
                      <h3 class="error--text aling-text-center">
                        NO EXISTE UNA COINCIDENCIA EXACTA <br> CON EL AFILIADO
                      </h3>
                    </v-col>
                  </v-row>
                  
                </v-card>
              </v-col>
              <!--TABLA DE OBSERVABLES-->
            <template v-if="history_observables != null && ver">
              <h2 class="pa-1 text-center">COINCIDENCIAS CON BASE DE DATOS ANTIGUA</h2>
              <v-row>
                <v-col cols="12" md="12" class="py-0">
                  <h3 class="pa-1 text-center">EXACTOS</h3>
                  <v-card>
                    <v-data-table
                      class="text-uppercase"
                      dense
                      :headers="headers_observables"
                      :items="history_observables.exactos"
                      :items-per-page="10"
                      :footer-props="{ itemsPerPageOptions: [10, 15, 30] }"
                    >
                      <template v-slot:[`item.PadFechaRegistro`]="{ item }">
                        {{ item.PadFechaRegistro | date }}
                      </template>   
                      <template v-slot:[`item.fullname`]="{ item }">
                        {{ item.PadPaterno + " "+ item.PadPaterno +" "+ item.PadNombres}}
                      </template> 
                    </v-data-table>
                  </v-card>
                </v-col>
                <v-col cols="12" md="12" class="py-0">
                  <h3 class="pa-1 text-center">APROXIMACIONES</h3>
                  <v-card>
                    <v-data-table
                      class="text-uppercase"
                      dense
                      :headers="headers_observables"
                      :items="history_observables.aproximaciones"
                      :items-per-page="10"
                      :footer-props="{ itemsPerPageOptions: [10, 15, 30] }"
                    >
                      <template v-slot:[`item.PadFechaRegistro`]="{ item }">
                        {{ item.PadFechaRegistro | date }}
                      </template>   
                      <template v-slot:[`item.fullname`]="{ item }">
                        {{ item.PadPaterno + " "+ item.PadPaterno +" "+ item.PadNombres}}
                      </template> 
                    </v-data-table>
                  </v-card>
                </v-col>
              </v-row>
            </template>
            <!--tabs-->
            <v-col cols="12" class="py-0 px-0 " v-if="history_observables == null && ver">
              <v-container fluid class="py-0 px-0 ">
                <v-row class="py-0">
                  <v-col cols="12" class="py-0">
                    <v-tabs dark active-class="secondary">
                      <v-tab>Información de Préstamos</v-tab>
                        <v-tab-item>
                          <DataLoanInformation
                            :loans_lender.sync="loans_lender"
                            :loans_spouse.sync="loans_spouse"
                            :ver.sync="ver"
                            :loading.sync="loading"/>
                        </v-tab-item>
                      <v-tab>EVALUACIÓN PREVIA</v-tab>
                        <v-tab-item >
                          <DataLoanAffiliate
                            :loans1.sync="loans1"
                            :largo_plazo.sync="largo_plazo"
                            :corto_plazo.sync="corto_plazo"
                            :anticipo.sync="anticipo"
                            :ver.sync="ver"
                            :evaluacion.sync="evaluacion"/>
                      </v-tab-item>
                    </v-tabs>
                  </v-col>
                </v-row>
              </v-container>
            </v-col>
            </v-row>
            <!--/v-card-->
          </v-form>
        </ValidationObserver>
      </v-container>
    </v-card-text>
  </v-card>
</template>
<script>
import DataLoanInformation from "@/components/dashboard/DataLoanInformation";
import DataLoanAffiliate from "@/components/dashboard/DataLoanAffiliate";

export default {
  name: "index",
  components: {
    DataLoanInformation,
    DataLoanAffiliate,
  },

  data: () => ({
    civil_statuses: [
      { name: "Soltero", value: "S" },
      { name: "Casado", value: "C" },
      { name: "Viudo", value: "V" },
      { name: "Divorciado", value: "D" },
    ],
    genders: [
      {
        name: "Femenino",
        value: "F",
      },
      {
        name: "Masculino",
        value: "M",
      },
    ],

    loans: {},
    affiliate_ci: null,
    loading: false,
    ver: false,
    /*affiliate: {},
    degree_name: null,
    category_name: null,
    unit_name: null,
    state_name_status: null,
*/

    loans1: {
      message: {
        accomplished: null,
      },
      state_affiliate: {
        name: null,
      },
    },
    largo_plazo: {},
    corto_plazo: {},
    anticipo: {},
    evaluacion: false,
    history_affiliate: {},
    history_spouse: {},
    history_observables: {},
    headers_observables: [
      {
        text: "Id",
        class: ["normal", "white--text"],
        align: "left",
        value: "IdPadron",
     
      },
      {
        text: "CI",
        class: ["normal", "white--text"],
        align: "left",
        value: "PadCedulaIdentidad",
  
      },  
      {
        text: "Expedición",
        class: ["normal", "white--text"],
        align: "left",
        value: "PadExpCedula",

      },    
      {
        text: "Matrícula",
        class: ["normal", "white--text"],
        align: "left",
        value: "PadMatricula",

      },
      {
        text: "Mombre",
        class: ["normal", "white--text"],
        align: "left",
        value: "fullname",

      },
      {
        text: "Tipo",
        class: ["normal", "white--text"],
        align: "left",
        value: "PadTipo",

      },

      {
        text: "Fecha de registro",
        class: ["normal", "white--text"],
        align: "left",
        value: "PadFechaRegistro",

      },

    ],
    //para guardar los prestamos y garantias
    loans_lender:{},
    loans_spouse:{}
  }),

  watch: {
    affiliate_ci() {
      this.ver = false;
      this.loading = false
      this.history_affiliate = {},
      this.history_spouse = {},
      this.history_observables = {},
      this.loans_lender= {},
      this.loans_spouse= {}
    },
  },
  computed:{
    //permisos del selector global por rol
    permissionSimpleSelected () {
      return this.$store.getters.permissionSimpleSelected
    }
  },
  methods: {

    async validar() {
      if (await this.$refs.observer.validate()) {

        this.getHistoryAffiliate()

      }
  },
    //obtener los afiliados u observables
    async getHistoryAffiliate() {
          this.getCalculator();
          try {
        this.loading = true;
        let res = await axios.get(`affiliate_record`, {
          params: {
            ci: this.affiliate_ci,
          },
        });
        this.history_affiliate = res.data.affiliate;
        this.history_spouse = res.data.spouse;
        this.history_observables = res.data.observables;

          if (this.history_affiliate != null){
            let res2 = await axios.post(`affiliate_loans_guarantees`, { 
                affiliate_id: this.history_affiliate.id,
                type: true
            });
            this.loans_lender = res2.data
            this.ver = true

            if (this.history_spouse != null){
              if (this.history_spouse.origin == "affiliate") {

                let res3 = await axios.post(`affiliate_loans_guarantees`, {        
                    affiliate_id: this.history_spouse.id,
                    type: true      
                });
                this.loans_spouse = res3.data
                this.ver = true
                console.log("Afiliada conyugue")
              } 
              else if(this.history_spouse.origin == "spouse") {         

                let res3 = await axios.post(`affiliate_loans_guarantees`, {
                    affiliate_id: this.history_spouse.id,
                    type: false
                });
                this.loans_spouse = res3.data
                this.ver = true
                console.log("conyugue")
              } else{
                console.log("No tiene conyugue")
              }
            }  
          }       
      } catch (e) {
        this.$refs.observer.setErrors(e)
      } finally {
        this.loading = false
      }
    
    },

    /*async getLoansHistory() {
      try {
        this.getCalculator();
        this.loading = true;
        let message = [];
        let res = await axios.get(`affiliate_record`, {
          params: {
            ci: this.affiliate_ci,
          },
        });
        this.loans = res.data;
        message = this.loans.message[0];
        if (message != "afiliado-inexistente") {
  
          this.ver = true;
          if (this.loans.tit_pvt) {
            this.getAffiliate(this.loans.id);
          }
        } else {

          this.ver = true;
          console.log("no coincide");
        }
      } catch (e) {
        console.log(e);
      } finally {
        this.loading = false;
      }
    },*/
    async getCalculator() {
      try {
        this.loading = false;
        this.ver = true;
        let res = await axios.post(`search_loan`, {
          identity_card: this.affiliate_ci,
        });
        this.loans1 = res.data;
        this.cont = this.loans1.modalities.length;
        for (let i = 0; i < this.loans1.modalities.length; i++) {
          if (this.loans1.modalities[i].name_procedure_modality == "Préstamo Anticipo") {
            this.anticipo.name = this.loans1.modalities[i].name_procedure_modality;
            this.anticipo.amount_max = this.loans1.modalities[i].amount_max;
            this.anticipo.maximum_term = this.loans1.modalities[i].modality_affiliate.loan_modality_parameter.maximum_term_modality;
            this.anticipo.liquid_calification = this.loans1.modalities[i].liquid_calification;
            this.anticipo.annual_interest = this.loans1.modalities[i].interest.annual_interest;
            this.anticipo.quota_calculated = this.loans1.modalities[i].quota_calculated;
          }
          if (this.loans1.modalities[i].name_procedure_modality == "Préstamo a Corto Plazo") {
            this.corto_plazo.name = this.loans1.modalities[i].name_procedure_modality;
            this.corto_plazo.amount_max = this.loans1.modalities[i].amount_max;
            this.corto_plazo.maximum_term = this.loans1.modalities[i].modality_affiliate.loan_modality_parameter.maximum_term_modality;
            this.corto_plazo.liquid_calification = this.loans1.modalities[i].liquid_calification;
            this.corto_plazo.annual_interest = this.loans1.modalities[i].interest.annual_interest;
            this.corto_plazo.quota_calculated = this.loans1.modalities[i].quota_calculated;
          }
          if (this.loans1.modalities[i].name_procedure_modality == "Préstamo a Largo Plazo") {
            this.largo_plazo.name = this.loans1.modalities[i].name_procedure_modality;
            this.largo_plazo.amount_max = this.loans1.modalities[i].amount_max;
            this.largo_plazo.maximum_term = this.loans1.modalities[i].modality_affiliate.loan_modality_parameter.maximum_term_modality;
            this.largo_plazo.liquid_calification = this.loans1.modalities[i].liquid_calification;
            this.largo_plazo.annual_interest = this.loans1.modalities[i].interest.annual_interest;
            this.largo_plazo.quota_calculated = this.loans1.modalities[i].quota_calculated;
          }
        }

        this.evaluacion = res.data.evaluate;
        //this.$forceUpdate();
      } catch (e) {
        console.log(e);
      } finally {
        this.loading = false;
      }
    },
    /*async getAffiliate(id) {
      try {
        this.loading = true;
        let res = await axios.get(`affiliate/${id}`);
        this.affiliate = res.data;
        this.yyy=this.getDegree_name(this.affiliate.degree_id);
        this.getCategory_name(this.affiliate.category_id);
        this.getUnit_name(this.affiliate.unit_id);
        this.getState_name(id);
      } catch (e) {
        console.log(e);
      } finally {
        this.loading = false;
      }
    },
      async getDegree_name(id) {
      try {
        this.loading = true;
        let res = await axios.get(`degree/${id}`);
        this.degree_name = res.data.name;
      } catch (e) {
        console.log(e);
      } finally {
        this.loading = false;
      }
    },
    async getCategory_name(id) {
      try {
        this.loading = true;
        let res = await axios.get(`category/${id}`);
        this.category_name = res.data.name;
      } catch (e) {
        console.log(e);
      } finally {
        this.loading = false;
      }
    },
    async getUnit_name(id) {
      try {
        this.loading = true;
        let res = await axios.get(`unit/${id}`);
        this.unit_name = res.data.name;
      } catch (e) {
        console.log(e);
      } finally {
        this.loading = false;
      }
    },
    async getState_name(id) {
      try {
        this.loading = true;
        let res = await axios.get(`affiliate/${id}/state`);
        this.state_name = res.data;
        this.state_name_type = this.state_name.affiliate_state_type.name;
        this.state_name_status = this.state_name.name;
      } catch (e) {
        console.log(e);
      } finally {
        this.loading = false;
      }
    },*/
  },
};
</script>

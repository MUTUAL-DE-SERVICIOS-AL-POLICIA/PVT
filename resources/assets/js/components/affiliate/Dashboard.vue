<template>
  <v-container fluid>
    <v-row>
      <v-col cols="12" md="3" class="ma-0 pa-2 pt-0" align="center">
        <v-progress-linear
          indeterminate
          color="primary"
          v-if="loading_affiliate"
        ></v-progress-linear>
        <v-card color="info_card elevation-1" max-width="350" :loading="loading_affiliate">
            <v-chip style="position:absolute; top:0;left:0"
              class="ma-0"
              color="#151515"
              label
              text-color="white"
            >
              <v-icon left class="body-2">
                NUP:
              </v-icon>
              {{ affiliate.id }}
            </v-chip>
          <div class="pa-3 pb-4" >
            <div v-if="profilePictures.length > 0">
              <v-avatar class="mx-auto d-block elevation-3" tile size="125">
                <v-img
                  :src="`data:${profilePictures[0].format};base64,${profilePictures[0].content}`"
                />
              </v-avatar>
            </div>
            <div v-else>
              <v-avatar class="mx-auto d-block " tile size="125">
                <v-icon size="125" color="tertiary" v-if="affiliate.gender==='M'">mdi-face</v-icon>
                <v-icon size="125" color="tertiary" v-else>mdi-face-woman</v-icon>
              </v-avatar>
            </div>
          </div>
          <v-divider class="mx-4"></v-divider>
          <v-card-text class="black--text my-0 py-0" v-if="affiliate.dead && affiliate.dead_spouse == false">
            <v-row>
              <v-col cols="12" color="info_card" align="start" class=" subtitle-1">
                <strong>Cónyuge:</strong> {{$options.filters.fullName(affiliate.spouse, true) }}
                <br />
                <strong>C.I.:</strong> {{affiliate.spouse.identity_card}}
                <br />
                <strong>Matrícula:</strong> {{affiliate.spouse.registration}}
                <br />
              </v-col>
            </v-row>
          </v-card-text>
          <v-divider class="mx-4"></v-divider>
          <v-card-text class="black--text my-0 py-0">
            <v-row>
              <v-col cols="12" md="12" align="start" class="subtitle-1">
                <h4 align="center" v-if="affiliate.dead && affiliate.dead_spouse == false">
                  Datos del Afiliado
                </h4>
                <b>C.I.:  </b> {{affiliate.identity_card_ext}}
                <br>
                <b>Categoría:</b> <span v-if="affiliate.category != null">{{affiliate.category.name}}</span>
                <br />
                <b>Estado:</b> <span :class="{'warning--text':this.state_name_status=='Disponibilidad'? true:false}">{{this.state_name_status}}</span>
                <br />
                <b>Grado:</b> {{this.degree_name}}
                <br/>
                <b>Unidad:</b> {{this.unit_name}}
                <br />
                <b>Estado Civil:</b> <span v-if ="affiliate.gender==='M'">
                  {{affiliate.civil_status=='C'? 'CASADO':affiliate.civil_status=='S'? 'SOLTERO':affiliate.civil_status=='D'?'DIVORCIADO':'VIUDO'}}
                  </span>
                <span v-else>
                  {{affiliate.civil_status=='C'? 'CASADA':affiliate.civil_status=='S'? 'SOLTERA':affiliate.civil_status=='D'?'DIVORCIADA':'VIUDA'}}
                </span>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>
        <div class="red--text pa-4" v-if="affiliate.spouse != null">
          <span v-if="affiliate.dead && affiliate.dead_spouse">
              *El afiliado y cónyuge se encuentran registrados como fallecidos.
          </span>
          <!-- <span  v-if="!affiliate.dead  && !affiliate.dead_spouse">
              **Se tiene el registro datos del cónyuge. Verifique el estado del afiliado/a
          </span> -->
        </div>
        <div class="red--text pa-4" v-if="affiliate.affiliate_state_id != 4">
        <span  v-if="((cleanSpace(affiliate.death_certificate_number) != null ||
                cleanSpace(affiliate.date_death) != null  ||
                cleanSpace(affiliate.reason_death) != null))">
          **** Se tiene registrado datos de fallecimiento del afiliado, cambie el estado del afiliado a Fallecido.
        </span>
        </div>
      </v-col>

      <v-col cols="12" md="9" class=" ma-0 pa-2 pt-0">
        <v-progress-linear
          indeterminate
          color="primary"
          v-if="loading_loan"
        ></v-progress-linear>
        <v-card v-else color="info_card " shaped class="elevation-1" >
          <div class="title pl-4 pt-4">Préstamos
            <v-btn
              color="#E65100"
              icon
              class="mr-2"
              @click="alert = !alert"
            >
              <v-icon>mdi-information</v-icon>
            </v-btn>
            <v-alert
              dense
              text
              v-model="alert"
              dismissible
              transition="scale-transition"
              border="left"
              color="#E65100"
              class="mr-4"
            >
              <span class="black--text font-weight-regular">- Los préstamos de color <span class="red--text">ROJO</span> se encuentran en situación de mora o no se realizó el registro de ninguna amortización.<br>
                - La fecha de actualización de los trámites en mora considerado por el Área de Cobranzas es: {{affiliate.default_alert_date_import | date}}.</span>
            </v-alert>
          </div>
          <v-card-text>
            <div>
              <h1 v-if="loan.length === 0">NO TIENE PRÉSTAMOS VIGENTES Y EN PROCESO REGISTRADOS</h1>
              <ul style="list-style: none;" class="pa-0">
                <li v-for="(item,i) in loan" :key="i">
                  <div>
                    <strong>Cód.:</strong>
                    {{ item.code }} |
                    <strong>Desembolso:</strong>
                    {{ item.amount_approved | money}} |
                    <strong>S.Capital:</strong>
                    {{ item.balance | money}} |
                    <strong>Mod.:</strong>
                    {{ item.modality.procedure_type.second_name  }} |
                    <strong>Estado:</strong>
                     {{item.state.name}}
                    <span>
                      <v-tooltip
                        left
                      >
                        <template v-slot:activator="{ on }">
                          <v-btn
                            icon
                            dark
                            small
                            color="warning"
                            bottom
                            right
                            v-on="on"
                            :to="{ name: 'tracingAdd', params: { id: item.id} }"
                          >
                            <v-icon>mdi-eye</v-icon>
                          </v-btn>
                        </template>
                        <span>Ver préstamo</span>
                      </v-tooltip>
                    </span>
                    <span v-if="item.state.name == 'En Proceso' && permissionSimpleSelected.includes('create-loan')">
                      <v-tooltip
                        left
                      >
                        <template v-slot:activator="{ on }">
                          <v-btn
                            icon
                            dark
                            small
                            color="error"
                            bottom
                            right
                            v-on="on"
                            @click.stop="validateRemakeLoan(affiliate.id, item.id)"
                          >
                            <v-icon>mdi-redo-variant</v-icon>
                          </v-btn>
                        </template>
                        <span>Rehacer préstamo</span>
                      </v-tooltip>
                    </span>

                    <span v-if="item.state.name == 'Vigente'">
                    <v-tooltip
                    left
                    v-if="item.modality.procedure_type.name != 'Préstamo Anticipo' && permissionSimpleSelected.includes('create-loan')"
                    >
                    <template v-slot:activator="{ on }">
                      <v-btn
                        icon
                        dark
                        small
                        color="success"
                        bottom
                        right
                        v-on="on"
                        @click.stop="validateRefinancingLoan(affiliate.id, item.id)"
                      >
                      <v-icon>mdi-cash-multiple</v-icon>
                      </v-btn>
                    </template>
                    <span>Refinanciamiento</span>
                    </v-tooltip>
                    </span>

                    <!-- <span v-if="item.state.name == 'Vigente'">
                    <v-tooltip
                    left
                    v-if="(item.modality.procedure_type.name == 'Préstamo a Largo Plazo' || item.modality.procedure_type.name == 'Préstamo Hipotecario')
                          && permissionSimpleSelected.includes('create-loan') "
                    >
                      <template v-slot:activator="{ on }">
                        <v-btn
                          icon
                          dark
                          small
                          color="info"
                          bottom
                          right
                          v-on="on"
                          @click.stop="validateReprogrammingLoan(affiliate.id, item.id)"
                        >
                        <v-icon>mdi-calendar-clock</v-icon>
                        </v-btn>
                      </template>
                      <span>Reprogramacion</span>
                    </v-tooltip>
                    </span> -->
                    <v-progress-linear
                      :color="item.default_alert_state ? 'error' : 'info'"
                      height="15"
                      :value="(((item.amount_approved-item.balance)*100)/item.amount_approved)"
                      striped
                    >
                      <strong>Porcentaje pagado: {{ (((item.amount_approved-item.balance)*100)/item.amount_approved) | percentage}}% </strong>
                    </v-progress-linear>

                  </div>
                  <v-alert
                    dense
                    v-model="alert2"
                    border="left"
                    type="warning"
                    class="ma-0 mt-2"
                    color="warning"
                     v-if="item.paid_by_guarantors"
                  >
                   El préstamo <strong>{{ item.code }}</strong> tiene pagos realizados por garante(s)
                  </v-alert> 
                  <v-divider class="my-2"></v-divider>
                </li>
              </ul>
            </div>
            <v-tooltip
              left v-if="permissionSimpleSelected.includes('create-loan')"
            >
              <template v-slot:activator="{ on }">
                <v-btn
                  fab
                  dark
                  small
                  color="success"
                  bottom
                  right
                  absolute
                  v-on="on"
                  style="margin-right: 99px;"
                  @click="validateAffiliate($route.params.id, 'is_new')"
                >
                  <v-icon>mdi-plus</v-icon>
                </v-btn>
              </template>
              <span>Iniciar trámite</span>
            </v-tooltip>
            <v-tooltip
              left v-if="permissionSimpleSelected.includes('create-loan')"
            >
              <template v-slot:activator="{ on }">
                <v-btn
                  fab
                  dark
                  small
                  color="success"
                  bottom
                  right
                  absolute
                  v-on="on"
                  style="margin-right: 49px;"
                  @click="validateAffiliate($route.params.id, 'is_refinancing')"
                >
                  <v-icon>mdi-cash-multiple</v-icon>
                </v-btn>
              </template>
              <span>Refinanciamiento SISMU</span>
            </v-tooltip>
            <!-- <v-tooltip
              left v-if="permissionSimpleSelected.includes('create-loan')"
            >
              <template v-slot:activator="{ on }">
                <v-btn
                  fab
                  dark
                  small
                  color="info"
                  bottom
                  right
                  absolute
                  v-on="on"
                  style="margin-right: -9px;"
                  @click="validateAffiliate($route.params.id, 'is_reprogramming')"
                >
                  <v-icon>mdi-calendar-clock</v-icon>
                </v-btn>
              </template>
              <span>Reprogramación SISMU</span>
            </v-tooltip> -->
          </v-card-text>
        </v-card>
      </v-col>

    </v-row>
  </v-container>
</template>

<script>
import common from "@/plugins/common";
export default {
  name: "affiliate-dashboard",
  props: {
    affiliate: {
      type: Object,
      required: true
    },
    loading_affiliate:{
      required: true
    }
  },
  data: () => ({
    loading: false,
    fingerprints: [],
    profilePictures: [],
    degree_name: null,
    category_name: null,
    unit_name: null,
    state_name: null,
    state_name_type: null,
    state_name_status: null,
    loan: [],
    loan_one: null,
    loan_two: null,
    loan_three: null,
    loan_affiliate: {},
    spouse: {},
    global_parameters: {},
    validate_affiliate: false,
    loading_loan: true,
    alert: false,
    alert2:true,
    pos_index: -1,
  }),
  created() {
    this.randomColor = common.randomColor
    this.cleanSpace = common.cleanSpace
  },
  computed: {
    //Metodo para obtener Permisos por rol
    permissionSimpleSelected () {
      return this.$store.getters.permissionSimpleSelected
    },
    isNew() {
      return this.$route.params.id == "new";
    }
  },
  watch: {
    affiliate(newVal, oldVal) {
      if (oldVal != newVal) {
        if (newVal.hasOwnProperty('category_id') && this.affiliate.category_id != null){
          this.getCategory_name(newVal.category_id)
        }
        if (newVal.hasOwnProperty('degree_id') && this.affiliate.degree_id != null){
          this.getDegree_name(newVal.degree_id)
        }
        if (newVal.hasOwnProperty('unit_id') && this.affiliate.unit_id != null){
          this.getUnit_name(newVal.unit_id)
        }
      }
      this.getState_name(this.$route.params.id)
      this.getLoan(this.$route.params.id)
    }
  },
  mounted() {
    this.getGlobalParameters()
    if (!this.isNew) {
      this.getProfilePictures(this.$route.params.id);
      this.getLoan(this.$route.params.id);
      this.getState_name(this.$route.params.id);
      this.verifyLoans(this.$route.params.id)
      this.getSpouse(this.$route.params.id)
    }
  },
  methods: {
    async getProfilePictures(id) {
      try {
        this.loading = true;
        let res = await axios.get(`affiliate/${id}/profile_picture`);
        this.profilePictures = res.data;
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
        let res = await axios.get(`category/${id}`)
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
    async getLoan(id) {
      try {
        this.loading = true;
        let res = await axios.get(`affiliate/${id}/loans_affiliate`, {
          params: {
            guarantor:0
          }
        });
        this.loan = res.data.data;
        //this.loan = res.data.data.filter(item => item.state.name === 'Vigente' || item.state.name === 'En Proceso');
        let num = this.loan.length;
      } catch (e) {
        console.log(e);
      } finally {
        this.loading = false
        this.loading_loan=false
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
    },
    async getSpouse(id) {
      try {
        this.loading = true
        let res = await axios.get(`affiliate/${id}/spouse`)
        this.spouse = res.data
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
    async getGlobalParameters(){
      try {
        let res = await axios.get(`loan_global_parameter`)
        this.global_parameters = res.data.data[0]
      } catch (e) {
        console.log(e)
      }
    },

    async validateRefinancingLoan(a_id, l_id){
      try {
        let res = await axios.post(`loan/${a_id}/validate_affiliate`,{
          refinancing:true
          })
        this.validate_affiliate = res.data.validate
        if(this.validate_affiliate == true){
          let res = await axios.post(`loan/${l_id}/validate_re_loan`,{
            type_procedure: true
          })
          let validate = res.data
          if(this.loan_affiliate.process_loans < this.global_parameters.max_loans_process){
            if(this.loan_affiliate.disbursement_loans <= this.global_parameters.max_loans_active){
              if(validate.percentage){
                if(validate.paids){
                  if(validate.defaulted){
                    this.$router.push({ name: 'loanAdd',  params: { hash: 'refinancing'}, query:{ affiliate_id: a_id, loan_id: l_id } })
                    }else{
                      this.toastr.error("El préstamo se encuentra en MORA")
                    }
                }else{
                  this.toastr.error("El saldo es inferior al monto permitido para refinanciamiento")
                }
              }else{
                this.toastr.error("No tiene el 25% pagado de su préstamo para acceder a un refinanciamiento")
              }
            }else{
              this.toastr.error("El afiliado no puede tener más de "+ this.global_parameters.max_loans_active +" préstamos vigentes. Actualmente ya tiene "+ this.loan_affiliate.disbursement_loans+ " préstamos vigentes.")
            }
          }else{
            this.toastr.error("El afiliado no puede tener más de "+ this.global_parameters.max_loans_process +" trámite en proceso. Actualmente ya tiene "+ this.loan_affiliate.process_loans+ " préstamos en proceso.")
          }
        }else{
          this.toastr.error(this.validate_affiliate)
        }
      } catch (e) {
        console.log(e)
      }
    },
    async validateReprogrammingLoan(a_id, l_id){
      try {
        let res = await axios.post(`loan/${a_id}/validate_affiliate`)
        this.validate_affiliate = res.data.validate
        if(this.validate_affiliate == true){
          let res = await axios.post(`loan/${l_id}/validate_re_loan`,{
            type_procedure: false
          })
          let validate = res.data
          if(this.loan_affiliate.process_loans < this.global_parameters.max_loans_process){
            if(this.loan_affiliate.disbursement_loans <= this.global_parameters.max_loans_active){
              if(validate.paids){
                if(validate.defaulted){
                  this.$router.push({ name: 'loanAdd',  params: { hash: 'reprogramming'}, query:{ affiliate_id: a_id, loan_id: l_id } })
                }else{
                  this.toastr.error("El préstamo se encuentra en MORA")
                }
              }else{
                this.toastr.error("Tiene pendiente menos de CUATRO pagos para finalizar la deuda")
              }
            }else{
              this.toastr.error("El afiliado no puede tener más de "+ this.global_parameters.max_loans_active +" préstamos vigentes. Actualmente ya tiene "+ this.loan_affiliate.disbursement_loans+ " préstamos vigentes.")
            }
          }else{
            this.toastr.error("El afiliado no puede tener más de "+ this.global_parameters.max_loans_process +" trámite en proceso. Actualmente ya tiene "+ this.loan_affiliate.process_loans+ " préstamos en proceso.")
          }
        }else{
          this.toastr.error(this.validate_affiliate)
        }
      } catch (e) {
        console.log(e)
      }
    },
    async validateRemakeLoan(a_id, l_id){
      this.$router.push({ name: 'loanAdd', params: { hash: 'remake'}, query: {affiliate_id: a_id, loan_id: l_id}})
    },

    async verifyLoans(id){
      try {
        let res = await axios.get(`affiliate/${id}/maximum_loans`)
        this.loan_affiliate = res.data
      } catch (e) {
        console.log(e)
      }
    },

    async validateAffiliate(id, type_procedure){
      let res
      try {
        res = await axios.post(`loan/${id}/validate_affiliate`,{
          refinancing:false
        })
        this.validate_affiliate = res.data.validate
        if(this.validate_affiliate == true){
          if(type_procedure == "is_new"){
            this.$router.push({ name: 'loanAdd',  params: { hash: 'new'},  query: { affiliate_id: id}})
          } if(type_procedure == "is_refinancing"){
            this.$router.push({ name: 'loanAdd', params: { hash: 'refinancing'}, query: { affiliate_id: id, type_sismu: true}})
          } if(type_procedure == "is_reprogramming"){
            this.$router.push({ name: 'loanAdd', params: { hash: 'reprogramming'}, query: { affiliate_id: id, type_sismu: true}})
          }
        }else{
          this.toastr.error(this.validate_affiliate)
        }
      } catch (e) {
        console.log(e)
      }
    },
  }
};
</script>

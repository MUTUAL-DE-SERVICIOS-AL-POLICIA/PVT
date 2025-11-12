<template>
  <v-container fluid class="px-0">
      <v-form>
        <!--BOTONES CUANDO SE REALICE LA EDICIÓN-->
        <v-row justify="center" >
          <v-col cols="12" class="px-0">
            <v-container fluid class="py-0 px-0  ">
              <v-row class="py-0">
                <v-col cols="12" class="py-0">
                  <v-card flat tile class="py-0">
                    <v-card-text class="py-0">


                      <!-- M E N Ú    V E R T I C A L   D A T O S -->
                      <v-tabs vertical>

                        <v-tab class="py-0">
                          <v-icon left>
                            mdi-account
                          </v-icon>
                          EVALUACION DEL PRESTATARIO
                        </v-tab>

                        <v-tab>
                          <v-icon left>
                            mdi-lock
                          </v-icon>
                          GARANTIA
                        </v-tab>

                        <v-tab>
                          <v-icon left>
                            mdi-access-point
                          </v-icon>
                          INFORMACION DEL TRAMITE
                        </v-tab>

                        <v-tab>
                          <v-icon left>
                            mdi-account-arrow-left
                          </v-icon>
                          PERSONA DE REFERENCIA
                        </v-tab>

                        <v-tab>
                          <v-icon left>
                            mdi-book-open-page-variant
                          </v-icon>
                          DOCUMENTOS PRESENTADOS
                        </v-tab>

                        <v-tab>
                          <v-icon left>
                            mdi-alert
                          </v-icon>
                          OBSERVACIONES DEL TRAMITE
                        </v-tab>

                        <v-tab>
                          <v-icon left>
                            mdi-monitor-cellphone
                          </v-icon>
                          HISTORIAL DEL TRAMITE
                        </v-tab>

                        <v-tab>
                          <v-icon left>
                            mdi-monitor-cellphone
                          </v-icon>
                          HISTORIAL DE PAGOS
                        </v-tab>


                        <!-- E V A L U A C I Ó N   D E   P R E S T A T A R I O -->
                        <v-tab-item>
                          <v-card flat>
                            <v-card-text>

                              <v-row>
                                <v-col cols="12" md="12" class="py-0">
                                  <p style="color:teal"><b>EVALUACION DEL PRESTATARIO PARA ACCEDER AL PRESTAMO</b></p>
                                </v-col>

                                <v-progress-linear></v-progress-linear>
                                <br>

                                <v-col cols="12" md="4" class="py-0">
                                   <p><b>MONTO SOLICITADO: </b> {{loan.amount_approved | moneyString}} Bs.</p>
                                </v-col>

                                <v-col class="py-0">
                                  <p><b>PLAZO:</b>{{' '+loan.loan_term}}</p>
                                </v-col>

                                <v-col cols="12" md="4" class="py-0">
                                   <p><b>PROMEDIO LIQUIDO PAGABLE: </b> {{loan.borrower[0].payable_liquid_calculated | moneyString }} Bs.</p>
                                </v-col>

                                <v-col cols="12" md="4" class="py-0" >
                                   <p><b>LIQUIDO PARA CALIFICACION: </b> {{loan.borrower[0].liquid_qualification_calculated | moneyString}} Bs.</p>
                                </v-col>

                                <v-col cols="12" md="4" class="py-0">
                                   <p><b>TOTAL BONOS:</b> {{loan.borrower[0].bonus_calculated | moneyString}}</p>
                                </v-col>

                                <v-col cols="12" md="4" class="py-0">
                                   <p><b>LÍMITE DE ENDEUDAMIENTO:</b> {{loan.borrower[0].indebtedness_calculated|percentage }}% </p>
                                </v-col>

                                <v-col cols="12" md="4" class="py-0">
                                   <p><b>CALCULO DE CUOTA: </b> {{loan.estimated_quota | moneyString}} Bs.</p>
                                </v-col>
                              </v-row>

                            </v-card-text>
                          </v-card>
                        </v-tab-item>

                        <!-- S E C C I Ó N   D E   G A R A N T Í A -->
                        <v-tab-item>
                          <v-card flat class=" py-0">
                            <v-card-text  class=" py-0">
                              <v-col cols="12" md="12" color="orange">
                                <v-card-text class="pa-0 mb-0">
                                  <v-col cols="12" md="12" class="py-0 px-0" >
                                    <p style="color:teal" v-if="loan.modality.procedure_type.second_name == 'Fondo de Retiro'"><b>GARANTIA DEL FONDO DE RETIRO POLICIAL SOLIDARIO</b></p>
                                    <p v-else style="color:teal"><b>GARANTÍA</b></p>
                                  </v-col>
                                  <v-progress-linear></v-progress-linear>
                                  <br>

                                  <v-col cols="12" md="12">
                                    <div class="pa-0" v-if="loan.modality.procedure_type.name == 'Préstamo a Largo Plazo' || loan.modality.procedure_type.name == 'Préstamo a Corto Plazo'|| loan.modality.procedure_type.name == 'Refinanciamiento Préstamo a Corto Plazo' || loan.modality.procedure_type.name == 'Refinanciamiento Préstamo a Largo Plazo'">

                                        <v-col cols="12" md="12" class="pa-0" v-for="guarantor in loan.borrowerguarantors" :key="guarantor.id">
                                          <p style="color:black" class="mb-0"><b>GARANTE </b></p>          
                                            <v-progress-linear></v-progress-linear><br>

                                          <v-row>
                                            <v-col cols="12" md="3" class="py-2">
                                              <p><b>NOMBRE:</b> {{$options.filters.fullName(guarantor, true)}}</p>
                                            </v-col>

                                            <v-col cols="12" md="3" class="py-2">
                                              <p><b>CÉDULA DE IDENTIDAD:</b> {{guarantor.identity_card}}</p>
                                            </v-col>

                                            <v-col cols="12" md="3" class="py-2">
                                              <p><b>TELÉFONO:</b> {{guarantor.cell_phone_number}}</p>
                                            </v-col>

                                            <v-col cols="12" md="3" class="py-2">
                                              <p><b>PORCENTAJE DE PAGO:</b> {{guarantor.payment_percentage | percentage }}%</p>
                                            </v-col>

                                            <v-col cols="12" md="3" class="py-2">
                                              <p><b>LIQUIDO PAGABLE:</b> {{guarantor.payable_liquid_calculated | moneyString}}</p>
                                            </v-col>

                                            <v-col cols="12" md="3" class="py-2">
                                              <p><b>PROMEDIO DE BONOS:</b> {{guarantor.bonus_calculated | moneyString }}</p>
                                            </v-col>

                                            <v-col cols="12" md="3" class="py-2">
                                              <p><b>LIQUIDO PARA CALIFICACION:</b> {{guarantor.liquid_qualification_calculated | moneyString}}</p>
                                            </v-col>

                                            <v-col cols="12" md="3" class="py-2">
                                              <p><b>MONTO DE EVALUACIÓN A GARANTE:</b> {{guarantor.eval_quota | moneyString }}</p>
                                            </v-col>

                                            <v-col cols="12" md="3" class="py-2">
                                              <p><b>LÍMITE DE ENDEUDAMIENTO CALCULADO:</b> {{guarantor.indebtedness_calculated | percentage }}%</p>
                                            </v-col>
                                          </v-row>

                                        </v-col>
                 
                                      <p v-if="loan.guarantors.length==0" style="color:teal"><b> NO TIENE GARANTES </b></p>
                                      <br>
                                    </div>
                                  </v-col>
                                    <v-col cols="12" md="12" v-if="loan.modality.procedure_type.second_name == 'Fondo de Retiro'">
                                      <v-row>
                                      <v-col class="my-0 py-0" cols="12" md="12">
                                        <p><b>TOTAL BENEFICIO DEL FONDO DE RETIRO POLICIAL SOLIDARIO: </b>{{loan.retirement.average | moneyString}} </p>
                                      </v-col>
                                      <v-col class="my-0 py-0" cols="12" md="12">
                                        <p><b>COBERTURA DEL BENEFICIO DEL FONDO DE RETIRO POLICIAL SOLIDARIOS: </b>{{loan.retirement.coverage | moneyString}} </p>
                                      </v-col>
                                      <v-col class="my-0 py-0" cols="12" md="12">
                                        <p><b>PORCENTAJE CALCULADO: </b>{{loan.retirement.percentage * 100}} % </p>
                                      </v-col>
                                      </v-row>
                                    </v-col>
                                </v-card-text>
                              </v-col>
                            </v-card-text>
                          </v-card>
                        </v-tab-item>

                        <!-- S E C C I Ó N   I N F O R M A C I Ó N   D E  T R A M I T E -->
                        <v-tab-item>
                        <v-card flat>
                          <v-card-text>

                              <v-col cols="12" md="12" class="py-0" >
                                <p style="color:teal"><b>INFORMACION DEL TRAMITE</b></p>
                              </v-col>
                              <v-progress-linear></v-progress-linear>
                              <br>

                              <v-col cols="12">                  
                              <p style="color:black" class="mb-0"><b>DATOS DEL CONTRATO </b></p>
                              <v-progress-linear class="mb-4"></v-progress-linear>

                                <v-row>
                                  <v-col cols="12" md="6" class="py-2">
                                    <p><b>FECHA ENTREGA DE CONTRATO:</b> {{loan.delivery_contract_date | date}}</p>
                                  </v-col>

                                  <v-col cols="12" md="6" class="py-2">
                                    <p><b>FECHA RECEPCION DE CONTRATO:</b> {{loan.return_contract_date==null? loan.return_contract_date:' '}}</p>
                                  </v-col>
                                </v-row>
                              </v-col>

                              <v-col cols="12">
                                <p style="color:black" class="mb-0"><b>DATOS DEL DESEMBOLSO </b></p>
                                <v-progress-linear class="mb-4"></v-progress-linear>

                                <v-row>
                                  <v-col cols="12" md="6" class="py-2">
                                    <p><b>TIPO DE DESEMBOLSO:</b> {{loan.payment_type ? loan.payment_type.name : 'Sin registro'}}</p>
                                  </v-col>

                                  <v-col cols="12" md="6" class="py-2" v-show="loan.payment_type && loan.payment_type.name=='Depósito Bancario'">
                                    <p><b>ENTIDAD FINANCIERA:</b> {{loan.payment_type.name}}</p>
                                  </v-col>

                                  <v-col cols="12" md="6" class="py-2" v-show="loan.payment_type && loan.payment_type.name=='Depósito Bancario'">
                                    <p><b>CUENTA SIGEP:</b> {{loan.borrower[0].sigep_status}}</p>
                                  </v-col>

                                  <v-col cols="12" md="6" class="py-2">
                                    <p><b>CERTIFICACIÓN PRESUPUESTARIA CONTABLE:</b> {{loan.num_accounting_voucher}}</p>
                                  </v-col>

                                  <v-col cols="12" md="6" class="py-2">
                                     <p><b>FECHA DE DESEMBOLSO:</b> {{loan.disbursement_date | date}}</p>
                                  </v-col>
                                </v-row>
                              </v-col>

                              <!--                R E F I N A N C I A M I E N T O            -->
                              <v-col cols="12" md="6" class="pb-0" v-show="loan_refinancing.refinancing">
                               <b><p style="color:teal">DATOS DEL PRÉSTAMO A REFINANCIAR{{' => '+ loan_refinancing.description}}</p></b>
                              </v-col>

                              <v-progress-linear class="mb-4" v-show="loan_refinancing.refinancing"></v-progress-linear  >

                              <v-row v-show="loan_refinancing.refinancing"> <v-col cols="12" md="4" class="py-2"> <p><b>Codigo de Prestamo a Padre:</b>{{' '+loan_refinancing.code}}</p> </v-col>
                                <v-col cols="12" md="4" class="py-2" >
                                  <p><b>Monto de Préstamo Padre:</b> {{loan_refinancing.amount_approved_son | money}}</p>
                                </v-col>

                                <v-col cols="12" md="4" class="py-2">
                                  <p><b>Plazo de Préstamo Padre:</b>{{' '+loan_refinancing.loan_term}}</p>
                                </v-col>

                                <v-col cols="12" md="4" class="py-0">
                                  <p><b>Cuota de Préstamo Padre:</b> {{loan_refinancing.estimated_quota | money}}</p>
                                </v-col>

                                <v-col cols="12" md="4" class="py-0" v-show="loan_refinancing.date_cut_refinancing==null">
                                  <p><b>Fecha de Corte :</b> Sin registar</p>
                                </v-col>

                                <v-col cols="12" md="4" class="py-0" v-show="loan_refinancing.date_cut_refinancing !=null">
                                  <p><b>Fecha de Corte :</b> {{loan_refinancing.date_cut_refinancing |date }}</p>
                                </v-col>

                                <v-col cols="12" md="4" class="py-0">
                                  <p><b>Saldo de Prestamo a Refinanciar:</b> {{loan_refinancing.balance_parent_loan_refinancing | money}}</p>
                                </v-col>

                                <v-col cols="12" md="4" class="py-0" >
                                  <p class="success--text"><b>Monto Solicitado del Prestamo Nuevo:</b> {{loan_refinancing.amount_approved | money}}</p>
                                </v-col>

                                <v-col cols="12" md="4" class="py-0" >
                                  <p class="success--text"><b>Saldo Anterior de la Deuda:</b> {{loan_refinancing.balance_parent_loan_refinancing | money}}</p>
                                </v-col>

                                <v-col cols="12" md="4" class="py-0" >
                                  <p class="success--text"><b>Monto del Refinanciamiento:</b> {{loan_refinancing.refinancing_balance | money}}</p>
                                </v-col>
                              </v-row>
                            </v-card-text>
                          </v-card>
                        </v-tab-item>

                        <!-- S E C C I Ó N   P E R S O N A  R E F E R E N C I A -->
                        <v-tab-item>
                          <v-card flat>
                            <v-card-text>

                              <v-col cols="12" md="4" class="py-0" >
                                <p style="color:teal"><b>PERSONA DE REFERENCIA </b></p>
                              </v-col>

                              <v-progress-linear></v-progress-linear><br>

                              <v-data-table
                                dense
                                v-if="loan.personal_references.length>0"
                                :headers="headers"
                                :items="loan.personal_references"
                                hide-default-footer
                                >
                                <template v-slot:[`item.kinship_id`]="{ item }">
                                  {{item.kinship_id != null? kinships.find(o => o.id == item.kinship_id).name:'' }}
                                </template>
                              </v-data-table>
                              <p v-if="loan.personal_references.length==0"> <b>NO TIENE PERSONA DE REFERENCIA</b></p>

                            </v-card-text>
                          </v-card>
                        </v-tab-item>

                        <!-- S E C C I Ó N   D O C U M E N T O S   P R E S E N T A D O S-->
                        <v-tab-item>
                          <v-card flat>
                            <v-card-text>
                              <v-col cols="12" md="12" class="py-0" >
                                <p style="color:teal"><b>DOCUMENTOS PRESENTADOS</b></p>
                              </v-col>

                              <v-progress-linear></v-progress-linear>
                              <br>

                              <!-- C O M P O N E N T E-->
                              <DocumentsFlow>
                              </DocumentsFlow>

                            </v-card-text>
                          </v-card>
                        </v-tab-item>

                        <!-- S E C C I Ó N   O B S E R V A C I Ó N   T R A M I T E-->
                        <v-tab-item>
                          <v-card flat>
                            <v-card-text>
                              <v-col cols="12" md="12" class="py-0" >
                                <p style="color:teal"><b>OBSERVACIONES DEL TRAMITE</b></p>
                              </v-col>

                              <v-progress-linear></v-progress-linear>
                              <br>

                              <!-- C O M P O N E N T E -->
                              <ObserverFlow
                              :loan.sync="loan"
                               :observations.sync="observations"
                               :observation_type.sync="observation_type">
                              </ObserverFlow>

                            </v-card-text>
                          </v-card>
                        </v-tab-item>

                        <!-- S E C C I Ó N   H I S T O R I A L   T R Á M I T E -->
                        <v-tab-item>
                          <v-card flat>
                            <v-card-text>
                              <v-col cols="12" md="12" class="py-0" >
                                <p style="color:teal"><b>HISTORIAL DEL TRAMITE</b></p>
                              </v-col>

                              <v-progress-linear></v-progress-linear>
                              <br>

                              <!-- C O M P O N E N T E -->
                              <HistoryFlow
                              :loan.sync="loan">
                              </HistoryFlow>

                            </v-card-text>
                          </v-card>
                        </v-tab-item>

                      <!-- S E C C I Ó N   H I S T O R I A L   D E  P A G O S -->
                        <v-tab-item>
                          <v-card flat>
                            <v-card-text>
                              <v-col cols="12" md="12" class="py-0" >
                                <p style="color:teal"><b>HISTORIAL DE PAGOS</b></p>
                              </v-col>

                              <v-progress-linear></v-progress-linear>
                              <br>

                              <!-- C O M P O N E N T E -->
                              <HistoryFlowPayments
                                :loan="loan"
                              />

                            </v-card-text>
                          </v-card>
                        </v-tab-item>

                      </v-tabs>
                    </v-card-text>
                  </v-card>
                 </v-col>
                </v-row>
              </v-container>
            </v-col>
          </v-row>
      </v-form>
  </v-container>
</template>
<script>

import DocumentsFlow from "@/components/tracing/DocumentsFlow"
import ObserverFlow from "@/components/tracing/ObserverFlow"
import HistoryFlow from "@/components/tracing/HistoryFlow"
import HistoryFlowPayments from "@/components/tracing/HistoryFlowPayments"

export default {
  components: {
   DocumentsFlow,
   ObserverFlow,
   HistoryFlow,
   HistoryFlowPayments
  },
  name: "specific-data-loan",
  props: {
    loan_refinancing: {
      type: Object,
      required: true
    },
    loan: {
      type: Object,
      required: true
    },
    observations: {
      type: Array,
      required: true
    },
    observation_type: {
      type: Array,
      required: true
    },
    loan_properties: {
      type: Object,
      required: true
    },
    procedure_types: {
      type: Object,
      required: true
    },
  },
   data: () => ({
    items_measurement: [
      { name: "Metros cuadrados", value: "METROS CUADRADOS" },
      { name: "Hectáreas", value: "HECTÁREAS" }
    ],
       headers: [
        {
          text: 'PRIMER NOMBRE',
          align: 'start',
          sortable: false,
          value: 'first_name',
          class: ['normal', 'white--text','text-md-center']
        },
        { text: 'SEGUNDO NOMBRE',  value: 'second_name', class: ['normal', 'white--text','text-md-center'] },
        { text: 'PRIMER APELLIDO ', value: 'last_name', class: ['normal', 'white--text','text-md-center'] },
        { text: 'SEGUNDO APELLIDO ', value: 'mothers_last_name', class: ['normal', 'white--text','text-md-center'] },
        { text: 'TELÉFONO', value: 'phone_number', class: ['normal', 'white--text','text-md-center'] },
        { text: 'CELULAR', value: 'cell_phone_number', class: ['normal', 'white--text','text-md-center'] },
        { text: 'PARENTESCO', value: 'kinship_id', class: ['normal', 'white--text','text-md-center'] },
        { text: 'DIRECCION ', value: 'address', class: ['normal', 'white--text','text-md-center'] },
      ],
    city: [],
    entity: [],
    entities:null,
    kinships:[]
  }),
  beforeMount(){
    this.getCity()
    this.getEntity()
    this.getKinship()
  },
  computed: {
      cuenta() {
       for (this.i = 0; this.i< this.entity.length; this.i++) {
        if(this.loan.lenders[0].financial_entity_id==this.entity[this.i].id)
        {
          this.entities= this.entity[this.i].name
        }
      }
      return this.entities
    }
  },
  methods:{
  //Metodo para obtener la entidad financiera
    async getEntity() {
      try {
        this.loading = true
        let res = await axios.get(`financial_entity`)
        this.entity = res.data
       } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
    //Metodo para obtener la ciudad
    async getCity() {
      try {
        this.loading = true
        let res = await axios.get(`city`)
        this.city = res.data
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
    //Metodo para obtener la extencion del ci
    identityCardExt(id){
      let ext
      if(id != null){
        for(let i=0; i<this.city.length;i++){
          if(this.city[i].id == id){
            ext = this.city[i].first_shortened
          }
        }
      return ext
      }else{
        return ''
      }
    },
    async getKinship(){
      try {
        let res = await axios.get('kinship')
        this.kinships = res.data
        console.log(this.kinships)
      } catch (e) {
        console.log(e)
      }
    }
  }
}
</script>

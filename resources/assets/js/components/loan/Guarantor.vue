<template>
  <v-container fluid >
    <v-row justify="center"  v-show="datos.procedure_type_id!=12">
         <v-col cols="12" class="py-0" >
          <v-card v-show="show_garante">
            <v-container v-if="modalidad_guarantors==0">
              <v-row>
                <v-col class="text-center">
                  <h2 class="success--text" > ESTA MODALIDAD NO NECESITA GARANTE</h2>
                </v-col>
              </v-row>
            </v-container>
          </v-card>
         </v-col>
      <v-col cols="4" class="py-0" v-if="modalidad_guarantors>0" >
        <v-card>
          <v-container class="py-0">
            <v-row>
              <v-col cols="12" md="4"></v-col>
              <v-col cols="12" md="6" >
                Afiliado
              </v-col>
              <v-col cols="12" md="2"></v-col>
              <v-col cols="12" md="1"></v-col>
              <v-col cols="12" md="8" >
                <v-text-field
                  label="C.I."
                  v-model="guarantor_ci"
                  class="py-0"
                  single-line
                  hide-details
                  clearable
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="2">
                <v-tooltip>
                  <template v-slot:activator="{ on }">
                    <v-btn
                      fab
                      dark
                      x-small
                      v-on="on"
                      color="info"
                      @click.stop="activar()">
                      <v-icon>mdi-magnify</v-icon>
                    </v-btn>
                  </template>
                </v-tooltip>
              </v-col>
            </v-row>
          </v-container>
        </v-card>
        <br>
        <v-card v-show="show_calculated">
          <v-container class="py-0">
            <v-row>
              <v-col cols="12" md="12">
                <v-layout row wrap>
                  <v-flex xs12 class="px-2">
                    <fieldset class="pa-3">
                      <v-text-field
                        class="py-0"
                        label="Boleta de Pago"
                        v-model="payable_liquid[0]"
                      ></v-text-field>
                      <v-text-field
                        class="py-0"
                        v-model="bonos[0]"
                        label="Bono Frontera"
                      ></v-text-field>
                      <v-text-field
                        class="py-0"
                        v-model="bonos[1]"
                        label="Bono Oriente"
                      ></v-text-field>
                      <v-text-field
                        class="py-0"
                        v-model="bonos[2]"
                        label="Bono Cargo"
                      ></v-text-field>
                      <v-text-field
                        class="py-0"
                        v-model="bonos[3]"
                        label="Bono Seguridad"
                      ></v-text-field>
                      </fieldset>
                    </v-flex>
                  </v-layout>
                </v-col>
                <v-col cols="12" md="7" class="py-0" ></v-col>
                <v-col cols="12" md="4" class="py-0">
                  <v-btn 
                    color="info"
                    @click="Calculator()">Calcular
                  </v-btn>
                </v-col>
              </v-row>
            </v-container>
          </v-card>
        </v-col>
        <v-col cols="8" class="py-0" >
          <v-card v-show="show_garante">
            <v-container v-if="modalidad_guarantors>0">
              <v-row>
                <v-col class="text-center">
                    <h4 class="error--text" > CANTIDAD DE GARANTES QUE NECESITA ESTA MODALIDAD:{{modalidad_guarantors}}<br>
                  EL GARANTE DEBE ESTAR ENTRE UNA CATEGORIA DE {{prueba[1]}} A {{prueba[2]}} </h4>
                </v-col>
              </v-row>
            </v-container>
          </v-card>
          <v-card v-show="!show_garante">
            <v-container class="py-0">
              <v-row>
                <v-col cols="12" md="4"></v-col>
                <v-col cols="12" md="6"  class="py-0" >
                  <h2 class="red--text" v-show="!validated">NO PUEDE SER GARANTE</h2>
                  <h2 class="success--text" v-show="validated1"> PUEDE SER GARANTE</h2>
                </v-col>
                <v-col cols="12" md="12" class="py-0" v-show="affiliate_garantor.affiliate.cpop">
                  <h5 class="success--text text-center">AFILIADO CPOP</h5>
                </v-col>
                <v-col cols="12" md="6" class="ma-0 pb-0 font-weight-light">
                  AFILIADO :{{affiliate_garantor.affiliate.full_name}}
                </v-col>
                <v-col cols="12" md="3" class="ma-0 pb-0 font-weight-light" >
                  C.I :{{affiliate_garantor.affiliate.identity_card_ext}}
                </v-col>
                <v-col cols="12" md="3" class="ma-0 pb-0 font-weight-light" >
                  CATEGORIA:   {{affiliate_garantor.affiliate.category.name}}
                </v-col>
                <v-col cols="12" md="6" class="py-0 font-weight-light" >
                  MATRICULA:{{affiliate_garantor.affiliate.registration}}
                </v-col>
                <v-col cols="12" md="6" class="text-uppercase py-0 font-weight-light">
                  ESTADO:{{affiliate_garantor.affiliate.affiliate_state.name}}  
                </v-col>
                <v-col cols="12" md="12" class="font-weight-black ">
                  PRESTAMOS VIGENTES QUE TIENE EL AFILIADO       
                </v-col>
                <v-col cols="12" class="py-0">
                  <v-data-table
                    :headers="headers"
                    :items="loan"
                    :items-per-page="4"
                  >
                  </v-data-table>
                </v-col>
                <v-col cols="12" md="6" class="font-weight-black" >
                  PRESTAMOS QUE ESTA GARANTIZANDO:      
                </v-col>
                <v-col cols="12" md="2" class="font-weight-black" >
                  {{affiliate_garantor.active_guarantees_quantity}}       
                </v-col>         
              </v-row>
            </v-container>
          </v-card>
          <br>
          <v-card v-show="show_result">
            <v-container >
              <v-row>
                <v-col cols="12" md="6">
                  <v-card-text class="py-0">
                    <v-layout row wrap class="py-0">
                      <v-flex xs12 class="px-2" >
                        <fieldset class="pa-3">
                          <p class="py-0">TOTAL BONOS: {{ calculos1.bonus_calculated }}</p>
                          <p class="py-0">LIQUIDO PARA CALIFICACION:{{ calculos1.liquid_qualification_calculated}} </p>
                          <p class="py-0">CALCULO DE CUOTA:{{ calculos1.quota_calculated }} </p>
                        </fieldset>
                      </v-flex>
                    </v-layout>
                  </v-card-text>
                </v-col>
                <v-col cols="12" md="6">
                  <v-card-text class="py-0">
                    <v-layout row wrap>
                      <v-flex xs12 class="px-2">
                        <fieldset class="pa-3">
                          <p class="py-0">INDICE DE ENDEUDAMIENTO:{{calculos1.indebtedness_calculated }}</p>
                          <p class="py-0 success--text font-weight-black">MONTO MAXIMO SUGERIDO :{{calculos1.amount_maximum_suggested}} </p>                       
                          <div class="text-right"  v-show="validated">
                            <v-btn  
                              color="info"
                              rounded
                              @click="añadir()">Añadir Garante
                            </v-btn>
                          </div>
                        </fieldset>
                      </v-flex>
                    </v-layout>
                  </v-card-text>
                </v-col>
                <v-col cols="12" md="12">
                  <v-card-text class="py-0">
                    <v-layout row wrap class="py-0">
                      <v-flex xs12 class="px-2" >
                        <fieldset class="pa-3">
                          {{'Cantidad de garantes a añadir: '+modalidad_guarantors}}
                          <div
                            class="align-end font-weight-light ma-0 pa-0"
                            v-for="(otherDoc, index) of garantes_detalle"
                            :key="index"
                          >
                            {{index+1 +". "}} {{otherDoc}}
                            <v-btn text icon color="error" @click.stop="deleteOtherDocument(index)">X</v-btn>
                              <v-divider></v-divider>
                          </div>
                        </fieldset>
                      </v-flex>
                    </v-layout>
                  </v-card-text>
                </v-col>
              </v-row>
            </v-container>
          </v-card>
        </v-col>
      </v-row>
      <v-card>
        <HipotecaryData v-show="datos.procedure_type_id==12"/>
      </v-card>
    </v-container>
</template>
<script>

import HipotecaryData from '@/components/loan/HipotecaryData'
  export default {
  name: "loan-information",
   props: {
    garantes: {
      type: Array,
      required: true
    },
     affiliate: {
      type: Object,
      required: true
    },
    prueba: {
      type: Array,
      required: true
    },
    datos: {
      type: Object,
      required: true
    },
    calculos: {
      type: Object,
      required: true
    },
    modalidad_guarantors: {
      type: Number,
      required: true,
      default: 0
    },
    modalidad_id: {
      type: Number,
      required: true,
      default: 0
    },
  },
  data: () => ({
    guarantor_ci:null,
    affiliate_garantor:{
      affiliate:{
        category:{},
        affiliate_state:{}
      },
      
    },
    show_garante:true,
    show_calculated:false,
    show_result:false,
    loan:[],
    index: [],
    garantes_detalle:[],
    guarantor:[null],
    validated:true,
    validated1:false,
    payable_liquid:[0],
    bonos:[0,0,0,0],
    calculos1:{},
    headers: [
      {
        text: "Codigo",
        class: ["normal", "white--text"],
        align: "left",
        value: "code"
      },
      {
        text: "Monto Aprovado",
        class: ["normal", "white--text"],
        align: "left",
        value: "amount_approved"
      },
      {
        text: "Plazo",
        class: ["normal", "white--text"],
        align: "left",
        value: "loan_term"
      },
       {
        text: "Cuota",
        class: ["normal", "white--text"],
        align: "left",
        value: "estimated_quota"
      }
    ],
    
 
  }),
     components: {
   HipotecaryData
  },
 
  computed: {
 
  
 },
 watch:{
ver()
{
  añadir()
}
 },
  beforeMount() {
  },
  mounted() {
  },
  methods: {
    async clear()
    {

         this.payable_liquid[0]=0,
    this.bonos[0]=0
    this.bonos[1]=0
    this.bonos[2]=0
    this.bonos[3]=0
    this.calculos1.bonus_calculated=null
    this.calculos1.liquid_qualification_calculated=null
    this.calculos1.quota_calculated=null
    this.calculos1.indebtedness_calculated=null
    this.calculos1.amount_maximum_suggested=null
    this.guarantor_ci=null
    },
 async añadir()
    {
      this.garantes.push(this.affiliate_garantor.affiliate.id);
      this.garantes_detalle.push(this.affiliate_garantor.affiliate.full_name);
    console.log('entro a garantes ==> '+this.garantes)
     console.log('entro a garantes ==> '+this.garantes_detalle)

this.clear()
    },
    deleteOtherDocument(i) {
      this.garantes.splice(i, 1);
      this.garantes_detalle.splice(i, 1);
      console.log("other1 " + this.garantes);
  
      console.log("other2 " + this.garantes_detalle);
    },
   /* async añadir()
    {
      console.log('entro a garantes')
this.garantes[0]=this.affiliate_garantor.affiliate.id
    this.toastr.success("Se añadio satisfactoriamente al garante.")
      
console.log('este es el garante'+this.garantes[0])
    },*/
    async Calculator() {
      try {
        this.show_result=true  
         let res = await axios.post(`calculator`, {
            procedure_modality_id:this.modalidad_id,
            months_term:  this.calculos.plazo,
            amount_requested: this.calculos.montos,
            affiliate_id:this.affiliate_garantor.affiliate.id,
            contributions: [
            {
              payable_liquid: this.payable_liquid[0],
              seniority_bonus:  this.bonos[2],
              border_bonus: this.bonos[0],
              public_security_bonus: this.bonos[3],
              east_bonus:this.bonos[1]
            }
          ]
        })
        this.calculos1= res.data     
      }catch (e) {
        console.log(e)
      }finally {
        this.loading = false
      }
    },
    
   async activar()
    {
      try {
        if(this.guarantor_ci==this.affiliate.identity_card)
        {
          this.toastr.error("El garante no puede tener el mismo carnet que el titular.")
        }
        else{
          let resp = await axios.post(`affiliate_guarantor`,{
            identity_card: this.guarantor_ci,
            procedure_modality_id:this.modalidad_id,
          })
          this.affiliate_garantor=resp.data
          this.validated=this.affiliate_garantor.guarantor
          this.validated1=this.affiliate_garantor.guarantor
          this.show_calculated=this.affiliate_garantor.guarantor
          this.loan=this.affiliate_garantor.affiliate.loans
          this.guarantor=this.affiliate_garantor.affiliate.guarantor
          this.show_garante=false
          console.log('Entro al metodo de garanyes'+this.affiliate_garantor+'==>'+this.guarantor_ci) 
          console.log('prestamos'+this.loan)  
          console.log('guarantor'+this.guarantor)
        }
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    
    }
  }
  }
</script>
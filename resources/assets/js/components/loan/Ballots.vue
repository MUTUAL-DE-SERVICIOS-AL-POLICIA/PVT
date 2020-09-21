<template>
  <v-flex xs12 class="px-3">
      <v-form>
        <v-row justify="center">
          <v-col cols="12"  >
            <v-card>
              <v-container fluid >
                <v-row justify="center" class="py-0">
                  <v-col cols="12" class="py-0" >
                    <v-container class="py-0">
                      <v-row>
                        <v-col cols="12" md="4" class="py-0 text-center">
                          MODALIDAD DEL PRESTAMO
                        </v-col>
                        <v-col cols="12" md="4" class="py-0 text-center">
                          INTERVALO DE LOS MONTOS
                        </v-col>
                        <v-col cols="12" md="4" class="py-0 text-center">
                          INTERVALO DEL PLAZO EN MESES
                        </v-col>
                        <v-col cols="12" md="4" class="py-0">
                          <v-select
                            dense
                            v-model="loanTypeSelected"
                            :onchange="Onchange()"
                            :items="modalities"
                            item-text="name"
                            item-value="id"
                            required
                          ></v-select>
                        </v-col>
                        <v-col cols="12" md="4" class="py-0 text-center">
                          {{monto}}
                        </v-col>
                        <v-col cols="12" md="4" class="py-0 text-center" >
                          {{plazo}}
                        </v-col>
                      </v-row>
                    </v-container>
                  </v-col>
                </v-row>
              </v-container>
              <v-container class="py-0" v-show="!hipotecario">
                <v-row>
                  <v-col cols="12" md="12" class="text-center" >
                    BOLETAS DE PAGO
                  </v-col>
                  <v-col cols="12" md="4" class="py-0"  >
                    <v-text-field
                      dense
                      v-model="payable_liquid[0]"
                      label="1ra Boleta"
                      :readonly="!editar"
                      :outlined="editar"
                     ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="4" class="py-0" v-if="visible">
                    <v-text-field
                      dense
                      v-model="payable_liquid[1]"
                      label="2ra Boleta"
                      :readonly="!editar"
                      :outlined="editar"
                  ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="4" class="py-0" v-if="visible" >
                    <v-text-field
                      dense
                      v-model="payable_liquid[2]"
                      label="3ra Boleta"
                      :readonly="!editar"
                      :outlined="editar"
                     ></v-text-field>
                  </v-col>
                  <v-col cols="12" class="py-0" >
                    BONOS
                  </v-col>
                  <v-col cols="12" md="3" >
                    <v-text-field
                      dense
                      v-model="bonos[0]"
                      label="Bono Frontera"
                      :readonly="!editar"
                      :outlined="editar"
                     ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="3">
                    <v-text-field
                      dense
                      v-model="bonos[1]"
                      label="Bono Oriente"
                      :readonly="!editar"
                      :outlined="editar"
                     ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="3" >
                    <v-text-field
                      dense
                      v-model="bonos[2]"
                      label="Bono Cargo"
                      :readonly="!editar"
                      :outlined="editar"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="3">
                    <v-text-field
                      dense
                      v-model="bonos[3]"
                      label="Bono Seguridad Ciudadana"
                      :readonly="!editar"
                      :outlined="editar"
                     ></v-text-field>
                  </v-col>
                </v-row>
              </v-container>
              <BallotsHipotecary
                v-show="hipotecario"
                :contributions1.sync="contributions1"/>
            </v-card>
          </v-col>
        </v-row>
      </v-form>
  </v-flex>
</template>
<script>

import BallotsHipotecary from '@/components/loan/BallotsHipotecary'
export default { 
  name: "ballots",
  data: () => ({
    editar:true,
    monto:null,
    plazo:null,
    interval:[],
    loanTypeSelected:null,
    visible:false,
    num_type:9,
    hipotecario:false
  }),
   props: {
    procedure_type: {
      type: Number,
      required: true
    },
    contributions1: {
      type: Array,
      required: true
    },
    modalidad: {
      type: Object,
      required: true
    },
    bonos: {
      type: Array,
      required: true
    },
    payable_liquid: {
      type: Array,
      required: true
    },
    modalities: {
      type: Array,
      required: true
    },
     prueba: {
      type: Array,
      required: true
    },
    intervalos: {
      type: Object,
      required: true
    },
  },
    components: {
    BallotsHipotecary
  },
   beforeMount() {
    this.getLoanIntervals()
  },
    mounted(){
    this.getLoanModality(this.$route.query.affiliate_id)
  },
  methods:
 {//caldula los intervalos deacuerdo a una modalidad
    Onchange(){
      for (this.i = 0; this.i< this.interval.length; this.i++) {
        if(this.loanTypeSelected==this.interval[this.i].procedure_type_id)
        {
          if(this.loanTypeSelected==12)
          {
            this.hipotecario=true
          }
          else{
            this.hipotecario=false
          }
          this.monto= this.interval[this.i].minimum_amount+' - '+this.interval[this.i].maximum_amount,
          this.plazo= this.interval[this.i].minimum_term+' - '+this.interval[this.i].maximum_term
          //intervalos es el monto, plazo y modalidad y id de una modalidad
          this.intervalos.maximun_amoun=this.interval[this.i].maximum_amount
          this.intervalos.maximum_term= this.interval[this.i].maximum_term
          this.intervalos.minimun_amoun=this.interval[this.i].minimum_amount
          this.intervalos.minimum_term= this.interval[this.i].minimum_term
          this.intervalos.procedure_type_id= this.loanTypeSelected
          this.num_type=this.loanTypeSelected
          this.procedure_type=this.loanTypeSelected
          
               this.getLoanModality(this.$route.query.affiliate_id)
          this.getBallots(this.$route.query.affiliate_id)
          console.log('este es la modalidad del intervalo'+this.num_type)

        }
      }
 
   
    },
    clearForm()
    {
      this.payable_liquid[0]=0
      this.payable_liquid[1]=0
      this.payable_liquid[2]=0
      this.bonos[0]=0
      this.bonos[1]=0
      this.bonos[2]=0
      this.bonos[3]=0
    },
    //Medodo donde identifica la modalidad de acuerdo a las caracteristicas de un affiliado
    async getLoanModality(id) {
      try {
        let resp = await axios.get(`affiliate/${id}/loan_modality`,{
          params: {
            procedure_type_id:this.num_type,
            external_discount:0,
          }
        })
        console.log('entro a get modality'+this.num_type)
          this.loan_modality= resp.data
          this.modalidad.id=this.loan_modality.id
          this.modalidad.name=this.loan_modality.name
          this.modalidad.quantity_ballots=this.loan_modality.loan_modality_parameter.quantity_ballots
          this.modalidad.guarantors=this.loan_modality.loan_modality_parameter.guarantors
          this.modalidad.min_guarantor_category=this.loan_modality.loan_modality_parameter.min_guarantor_category
          this.modalidad.max_guarantor_category=this.loan_modality.loan_modality_parameter.max_guarantor_category
         this.modalidad.personal_reference=this.loan_modality.loan_modality_parameter.personal_reference
    
    
    //this.modalidad.personal_reference=true
        this.prueba[0]=this.loan_modality.loan_modality_parameter.guarantors
        this.prueba[1]=this.loan_modality.loan_modality_parameter.min_guarantor_category
        this.prueba[2]=this.loan_modality.loan_modality_parameter.max_guarantor_category
        this.prueba[3]=this.loan_modality.loan_modality_parameter.personal_reference
          if(this.loan_modality.loan_modality_parameter.quantity_ballots>1)
          {
          this.visible=true
          }
          else{
          this.visible=false
        }
      }catch (e) {
        console.log(e)
      }finally {
        this.loading = false
      }
    },
    //Intervalos de Plazo y Meses de una modalidad
    async getLoanIntervals() {
      try {
        let res = await axios.get(`loan_interval`)
        this.interval = res.data
       }catch (e) {
        console.log(e)
      }finally {
        this.loading = false
      }
    },
    //Metodo para sacar boleta de un afiliado
    async getBallots(id) {
    try {
      let res = await axios.get(`affiliate/${id}/contribution`, {
        params:{
          city_id: this.$store.getters.cityId,
          sortBy: ['month_year'],
          sortDesc: [1],
          per_page: this.modalidad.quantity_ballots,
          page: 1,
        }
      })
      if(res.data.valid)
      {
        this.editar=false
        this.datos=res.data.data
        for (this.i = 0; this.i< this.datos.length; this.i++) {
          this.payable_liquid[this.i]= this.datos[this.i].payable_liquid,
          this.bonos[0]= this.datos[0].border_bonus,
          this.bonos[1]= this.datos[0].east_bonus,
          this.bonos[2]= this.datos[0].seniority_bonus,
          this.bonos[3]= this.datos[0].public_security_bonus
        }
          for(this.j = 0; this.j< this.datos.length; this.j++)
        {
          this.contributions1[this.j].payable_liquid=this.datos[this.j].payable_liquid
          this.contributions1[this.j].id_affiliate=this.datos[this.j].affiliate_id
          if(this.j==0){
            this.contributions1[this.j].border_bonus= this.datos[this.j].border_bonus,
            this.contributions1[this.j].east_bonus= this.datos[this.j].east_bonus,
            this.contributions1[this.j].seniority_bonus= this.datos[this.j].seniority_bonus,
            this.contributions1[this.j].public_security_bonus= this.datos[this.j].public_security_bonus
          }
          else{
            this.contributions1[this.j].border_bonus=0,
            this.contributions1[this.j].east_bonus= 0,
            this.contributions1[this.j].seniority_bonus=0,
            this.contributions1[this.j].public_security_bonus=0
          }
        }
      }
    } catch (e) {
      console.log(e)
    } finally {
      this.loading = false
    }
  }
 }
};
</script>
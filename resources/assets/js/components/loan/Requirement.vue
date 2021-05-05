<template>
  <v-container fluid>
    <ValidationObserver ref="observer">
    <v-form>
    <v-card>
      <v-data-iterator :items="items" hide-default-footer>
        <template v-slot:header>
          <v-toolbar class="mb-0" color="ternary" dark flat>
            <v-toolbar-title>REQUISITOS</v-toolbar-title>
          </v-toolbar>
          <v-row>
            <v-col v-for="(group,i) in items" :key="i" cols="12" class="py-1">
              <v-card dense>
                <v-col cols="12" class="py-0" v-for="(doc,j) in group" :key="doc.id">
                  <v-list dense class="py-0">
                    <v-list-item class="py-0">
                      <v-col cols="1" class="py-0">
                        <v-list-item-content class="align-end font-weight-light">
                          <div v-if="group.length == 1">
                            <h1>{{i+1}}</h1>
                          </div>
                          <div v-else>
                            <h3>{{(i+1) +'.'+(j+1)}}</h3>
                          </div>
                        </v-list-item-content>
                      </v-col>
                      <v-col cols="10" class="py-0 ml-n8">
                        <v-list-item-content class="align-end font-weight-light py-0">{{doc.name}}</v-list-item-content>
                      </v-col>
                      <v-col cols="1" class="py-0 my-n1">
                        <div v-if="group.length == 1" class="py-0">
                          <v-checkbox
                            class="py-0"
                            color="info"
                            v-model="selected"
                            :value="doc.id"
                          ></v-checkbox>
                        </div>
                        <div v-if="group.length > 1" class="py-0 my-n1">
                          <v-radio-group :mandatory="false" v-model="radios[i]" class="py-0">
                            <v-radio
                              color="info"
                              :value="doc.id"
                              class="py-0"
                            ></v-radio>
                          </v-radio-group>
                        </div>
                      </v-col>
                    </v-list-item>
                  </v-list>
                </v-col>
              </v-card>
            </v-col>
            <v-col cols="12" md="1" class="ma-0 pa-0">
            </v-col>
          </v-row>
        </template>
      </v-data-iterator>

      <v-data-iterator :items="optional" hide-default-footer>
        <template>
          <v-toolbar-title class="align-end font-weight-black text-center ma-0 pa-0 pt-5">
            <h5>Documentos Adicionales</h5>
          </v-toolbar-title>
          <v-row>
            <v-col cols="12" class="ma-0 px-10">
              <v-autocomplete
                dense
                filled
                outlined
                shaped
                label="Búsque y elija el documento"
                 v-model="selectedOpc"
                :items="newOptional"
                item-text="name"
                item-value="id"
                @change="addOptionalDocument(selectedOpc)"
              ></v-autocomplete>
              <div class="align-end font-weight-light">
                <div v-for="(idDoc, index) of itemsOpc" :key="index">
                  <div>
                    {{index+1 + ". "}} {{(optional.find((item) => item.id === idDoc)).name}}
                    <v-btn text icon color="error" @click="deleteOptionalDocument(index,idDoc)">
                      <h2>X</h2>
                    </v-btn>
                    <v-divider></v-divider>
                  </div>
                </div>
              </div>
            </v-col>
          </v-row>
        </template>
        <template>
          <v-toolbar-title class="align-end font-weight-black text-left ma-0 pl-8 pt-5">
            <h5>Otros Documentos</h5>
          </v-toolbar-title>
          <v-row>
          <v-col cols="12" class="ma-0 px-10">
            <ValidationProvider v-slot="{ errors }" name="Registrar el documento" rules="min:3">
              <v-text-field
              :error-messages="errors"
                dense
                outlined
                color="info"
                append-outer-icon="mdi-text-box-plus"
                @click:append-outer="addOtherDocument()"
                label="Registre el documento"
                v-model="newOther"
                @keyup.enter="addOtherDocument()"
              ></v-text-field>
              </ValidationProvider>
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="12" class="ma-0 px-10">
              <div
                class="align-end font-weight-light ma-0 pa-0"
                v-for="(otherDoc, index) of otherDocuments"
                :key="index"
              >
                {{index+1 +". "}} {{otherDoc}}
                <v-btn text icon color="error" @click.stop="deleteOtherDocument(index)">X</v-btn>
                <v-divider></v-divider>
              </div>
            </v-col>
          </v-row>
        </template>
      </v-data-iterator>
    </v-card>
    <v-row>
      <v-spacer></v-spacer>
      <v-spacer></v-spacer>
      <v-spacer></v-spacer>
      <v-col class="py-0">
        <v-btn
        text
        @click="beforeStepBus(6)">Atras</v-btn>
        <v-btn
        color="primary"
        @click.stop="saveLoan()"
        :disabled="this.status_click"
        :loading="this.status_click"
        >Crear Trámite</v-btn>
      </v-col>
    </v-row>
    </v-form>
  </ValidationObserver>
  </v-container>
</template>
<script>

export default {
  name: "requirement",
  data: () => ({
    itemsPerPage: 10,
    items: [],
    optional: [],
    newOptional: [],
    requirement: [],
    index: [],
    cont: 0,
    checks: [],
    itemsOpc: [],
    selected: [],
    radios: [],
    selectedOpc: null,
    idRequirements: [],
    otherDocuments: [],
    newOther: null,
    status_click: false,
    loader: null,
    //ids_items: []
  }),
  props: {
    guarantors: {
      type: Array,
      required: true
    },
    data_loan_parent: {
      type: Array,
      required: true
    },
    loan_detail: {
      type: Object,
      required: true
    },
    lenders: {
      type: Array,
      required: true
    },
    modalidad: {
      type: Object,
      required: true
    },
    modalidad_id: {
      type: Number,
      required: true,
      default: 0
    },
    bus: {
      type: Object,
      required: true
    },
    loan_property_id: {
      type: Number,
      required: true,
      default: 0
    },
    data_loan_parent_aux: {
      type: Object,
      required: true
    },
  },
  watch: {
    modalidad_id () {
      this.getRequirement(this.modalidad_id)
      this.selected = []
      this.radios = []
      //this.ids_items = []
    },
    personal_codebtor(){
      return true
    }
  },
  computed: {
    parent_reason(){
      if(this.$route.params.hash == 'new'){
        return null
      } else if(this.$route.params.hash == 'refinancing'){
        return 'REFINANCIAMIENTO'
      }else if(this.$route.params.hash == 'reprogramming'){
        return 'REPROGRAMACIÓN'
      }else if(this.$route.params.hash == 'remake' && this.data_loan_parent_aux.parent_reason == 'REFINANCIAMIENTO'){
        return 'REFINANCIAMIENTO'
      }else if(this.$route.params.hash == 'remake' && this.data_loan_parent_aux.parent_reason == 'REPROGRAMACIÓN'){
        return 'REPROGRAMACIÓN'
      }else if(this.$route.params.hash == 'remake' && this.data_loan_parent_aux.parent_reason == null){
        return null
      }
    },
    parent_loan_id(){
      if(this.$route.query.type_sismu || this.$route.params.hash == 'new'){
        return 0
      }
      else if( this.$route.params.hash == 'remake' && this.data_loan_parent_aux.parent_loan_id != null){//es PVT refi repro
        return this.data_loan_parent_aux.parent_loan_id
      }else if( this.$route.params.hash == 'remake' && this.data_loan_parent_aux.parent_loan_id == null){//es PVT no es refi ni repro
        return 0
      }else{
        return this.$route.query.loan_id //PVT si es refi repro nuevo
      }
    }
  },
  methods: {
    beforeStepBus(val) {
      this.bus.$emit("beforeStepBus", val)
    },
    async getRequirement(id) {
      //this.ids_items=[]
      try {
        this.loading = true;
        let res = await axios.get(`procedure_modality/${id}/requirement`);
        this.requirement = res.data;
        this.items = this.requirement.required;
        this.optional = this.requirement.optional;
        this.newOptional = this.requirement.optional;
        /*console.log(this.items)
        for(let i = 0; i < this.items.length; i++ ){
          for(let j = 0; j < 1; j++ ){
           this.ids_items.push(this.items[i][j].id)
            //console.log(this.ids_items )
          }
        }
         console.log(this.id_items )*/
      } catch (e) {
        console.log(e);
      } finally {
        this.loading = false;
      }
    },
    async saveLoan() {
      try {
        this.idRequirements = this.selected.concat(this.radios.filter(Boolean))
        if (this.idRequirements.length === this.items.length) {
          this.status_click = true
          if(this.status_click==true){
            let res = await axios.post(`loan`, {
              copies: 2,
              procedure_modality_id:this.modalidad.id,
              amount_requested: this.loan_detail.amount_requested,
              city_id: this.$store.getters.cityId,
              loan_term:this.loan_detail.months_term,
              payment_type_id:this.loan_detail.payment_type_id,
              financial_entity_id: this.loan_detail.financial_entity_id,
              number_payment_type:this.loan_detail.number_payment_type,
              destiny_id: this.loan_detail.destiny_id,
              liquid_qualification_calculated:this.loan_detail.liquid_qualification_calculated,
              indebtedness_calculated:this.loan_detail.indebtedness_calculated,
              parent_loan_id: this.parent_loan_id,
              parent_reason: this.parent_reason,
              property_id: this.loan_detail.loan_property_id,
              personal_references: this.loan_detail.reference,
              cosigners:this.loan_detail.cosigners,
              disbursable_id: this.$route.query.affiliate_id,
              lenders:this.lenders,
              guarantors: this.guarantors,
              data_loan:this.data_loan_parent,
              documents: this.selected.concat(this.itemsOpc.concat(this.radios.filter(Boolean))),
              notes: this.otherDocuments,
              user_id: this.$store.getters.id,
              remake_loan_id: this.$route.params.hash == 'remake' ? this.$route.query.loan_id : 0
            });             
            if(res.status==201 || res.status == 200){
              this.status_click = false        
            }
            printJS({
              printable: res.data.attachment.content,
              type: res.data.attachment.type,
              base64: true
            })
            this.$router.push('/workflow')  
          }
        } else {
          this.toastr.error("Falta seleccionar requisitos, todos los requisitos deben ser presentados.")
          this.status_click = false
    
        }
      } catch (e) {
        console.log(e);
        this.status_click = false
      } finally {
        this.loading = false;
      }
    },
    addOptionalDocument(i) {
      //Verifica si no encuentra el valor repetido
      if (this.itemsOpc.indexOf(i) === -1) {
        this.itemsOpc.push(i)
         //filtrar en newOptional el item agregado y generar uno array nuevo sin el item
        this.newOptional = this.newOptional.filter(item => item.id !== i)
        //console.log("I= " + i);
        //console.log("selectedOpc " + this.selectedOpc);
      }

    },
    deleteOptionalDocument(i, idDoc) {
      let itemDelete = []
      this.itemsOpc.splice(i, 1)
      this.selectedOpc = " ";
      console.log("delete "+i)
      console.log("delete "+idDoc)
      //obtener el item borrado desde optional
      itemDelete = this.optional.find(item => item.id === idDoc)
      console.log(itemDelete)
      //insertarlo en newOptional
      this.newOptional.push(itemDelete)
    },
    addOtherDocument() {
      //verificar si existe algun dato
      if (this.newOther) {
         //desde otherDocuments filtrar si existe un dato registrado igual a uno guardado en newOher
        if(!(this.otherDocuments.filter(item => item === this.newOther)).length > 0){
          //si no existe repetido insertar item
          this.otherDocuments.push(this.newOther);
          console.log("other " + this.otherDocuments);
          this.newOther = ""
        }else{
          this.toastr.error("El documento ya existe")
        }
      } else {
        this.toastr.error("No registró ningún documento")
      }
    },
    deleteOtherDocument(i) {
      this.otherDocuments.splice(i, 1);
      console.log("other " + this.otherDocuments);
    }
  }
};
</script>

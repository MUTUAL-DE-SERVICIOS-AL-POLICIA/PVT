<template>
  <v-container fluid >
    <ValidationObserver ref="observer"> 
    <v-form>
      <v-row justify="center">
        <v-col cols="12" md="11" class="v-card-profile" >
              <v-row justify="center">
              <v-col cols="12">
                <v-toolbar-title>INFORMACIÓN POLICIAL</v-toolbar-title>
              </v-col>
            <v-col cols="12" md="4" >
              <ValidationProvider v-slot="{ errors }" vid="affiliate_state_id" name="Estado" rules="required">
              <v-select
                :error-messages="errors"
                dense
                :loading="loading"
                :items="affiliateState"
                item-text="name"
                item-value="id"
                label="Estado"
                v-model="affiliate.affiliate_state_id"
                :Onchange="Onchange()"
  
                :readonly="!editable || !permission.secondary"
                :outlined="editable && permission.secondary"
              ></v-select>
              </ValidationProvider>
            </v-col>
            <v-col cols="12" md="8" v-if="!visible">
             <span class="red--text" v-show="has_registered_spouse">* Se tiene registrado datos del conyugue, cambie el estado del afiliado. <br></span>
             <span class="red--text" v-show="(affiliate.death_certificate_number !=null || affiliate.date_death != null  || affiliate.reason_death != null) &&
                  (affiliate.death_certificate_number !='' || affiliate.date_death !=''  || affiliate.reason_death !='')">
                   ** Se tiene registrado datos de fallecimiento del afiliado, cambie el estado del afiliado a Fallecido.</span>  
            </v-col>
             <v-col cols="12" md="4" v-if="visible">
              <v-text-field
                dense
                v-model="affiliate.birth_date"
                  label="Fecha Fallecimiento"
                  hint="Día/Mes/Año"
                  class="purple-input"
                  type="date"
                  :readonly="!editable || !permission.secondary"
                  :outlined="editable && permission.secondary"
                :disabled="editable && !permission.secondary"
              ></v-text-field>
              </v-col>
             <v-col cols="12" md="4" v-if="visible">
               <v-text-field
                 dense
                 v-model="affiliate.death_certificate_number"
                 label="N° de Certificado de Defunción"
                 :readonly="!editable || !permission.secondary"
                 :outlined="editable && permission.secondary"
                 :disabled="editable && !permission.secondary"
               ></v-text-field>
             </v-col>
              <v-col cols="12" md="12" v-if="visible">
               <v-text-field
                 dense
                 v-model="affiliate.reason_death"
                 label="Causa Fallecimiento"
                 :readonly="!editable || !permission.secondary"
                 :outlined="editable && permission.secondary"
                 :disabled="editable && !permission.secondary"
               ></v-text-field>
             </v-col>
            <v-col cols="12" md="4" v-if="affiliate.is_duedate_undefined==false">
              <v-text-field
                dense
                v-model="affiliate.date_entry"
                label="Fecha Ingreso a la Institución Policial"
                hint="Día/Mes/Año"
                class="purple-input"
                type="date"
                :clearable="editable"
                :readonly="!editable || !permission.secondary"
                :outlined="editable && permission.secondary"
                :disabled="editable && !permission.secondary"
              ></v-text-field>
            </v-col>
            <v-col cols="12" md="4" >
            <ValidationProvider v-slot="{ errors }" vid="degree_id" name="Grado" rules="required">
            <v-select
                dense
                :error-messages="errors"
                :loading="loading"
                :items="degree"
                item-text="name"
                item-value="id"
                label="Grado"
                name="Grado"
                v-model="affiliate.degree_id"
                :readonly="!editable || !permission.primary"
                :outlined="editable && permission.primary"
                :disabled="editable && !permission.primary"
            ></v-select>
            </ValidationProvider>
            </v-col>
            <v-col cols="12" md="4" >
              <ValidationProvider v-slot="{ errors }" vid="category_id" name="Categoria" rules="required">
              <v-select
                dense
                :error-messages="errors"
                :loading="loading"
                :items="category"
                item-text="name"
                item-value="id"
                label="Categoria"
                name="categoria"
                v-model="affiliate.category_id"
                :readonly="!editable || !permission.primary"
                :outlined="editable && permission.primary"
                :disabled="editable && !permission.primary"
              ></v-select>
              </ValidationProvider>
            </v-col>
            <v-col cols="12" md="4" >
              <ValidationProvider v-slot="{ errors }" vid="unit_id" name="Unidad" rules="required">
              <v-select
                :error-messages="errors"
                dense
                :loading="loading"
                :items="unit"
                item-text="name"
                item-value="id"
                label="Unidad"
                v-model="affiliate.unit_id"
                persistent-hint
                :readonly="!editable || !permission.secondary"
                :outlined="editable && permission.secondary"
              ></v-select>
              </ValidationProvider>
            </v-col>
            <!--<v-col cols="12" md="3">
              <ValidationProvider v-slot="{ errors }" vid="service_years" name="Años de Servicio" rules="numeric|min_value:0|max_value:100">
              <v-text-field
                dense
                :error-messages="errors"
                v-model="affiliate.service_years"
                label="Años de Servicio"
                :readonly="!editable || !permission.primary"
                :outlined="editable && permission.primary"
                :disabled="editable && !permission.primary"
              ></v-text-field>
              </ValidationProvider>
            </v-col>
            <v-col cols="12" md="3" >
              <ValidationProvider v-slot="{ errors }" vid="service_months" name="Meses de Servicio" rules="numeric|min_value:0|max_value:11">
              <v-text-field
                dense
                :error-messages="errors"
                v-model="affiliate.service_months"
                label="Meses de Servicio"
                :readonly="!editable || !permission.primary"
                :outlined="editable && permission.primary"
                :disabled="editable && !permission.primary"
              ></v-text-field>
               </ValidationProvider>
            </v-col>--> 
            <v-col cols="12"  md="4" >
              <ValidationProvider v-slot="{ errors }" vid="pension_entity_id" name="Ente Gestor" :rules="(affiliate.affiliate_state_id >= 4 && affiliate.affiliate_state_id <= 6)? 'required':''">
              <v-select
                dense
                :error-messages="errors"
                :loading="loading"
                :items="pension_entity"
                item-text="name"
                item-value="id"
                label="Ente Gestor"
                name="Grado"
                v-model="affiliate.pension_entity_id"
                :readonly="!editable || !permission.secondary"
                :outlined="editable && permission.secondary"
            ></v-select>
            </ValidationProvider>
            </v-col>
             <v-col cols="12" md="4">
              <v-text-field
                dense
                v-model="affiliate.date_derelict"
                label="Fecha Desvinculacion"
                hint="Día/Mes/Año"
                class="purple-input"
                type="date"
                :readonly="!editable || !permission.secondary"
                :outlined="editable && permission.secondary"
                :disabled="editable && !permission.secondary"
              ></v-text-field>
            </v-col>
          </v-row>
        </v-col>
      </v-row>
    </v-form>
    </ValidationObserver>
  </v-container>
</template>

<script>
export default {

  name: "affiliate-police-data",
  props: {
    affiliate: {
      type: Object,
      required: true
    },
    editable: {
      type: Boolean,
      required: true
    },
    permission: {
      type: Object,
      required: true
    },
    has_registered_spouse: {
      type: Boolean,
      required: true
    }
  },
  data: () => ({
    affiliateState: [],
    category: [],
    degree: [],
    pension_entity: [],
    unit: [],
    dates: {
      dateEntry: {
        formatted: null,
        picker: false
      },
      dateDerelict: {
        formatted: null,
        picker: false
      },
      dateDeath: {
        formatted: null,
        picker: false
      }
    },
    visible: false
  }),
  computed: {
    getCalculateCategory(){
    let years = this.affiliate.service_years;
    let months = this.affiliate.service_months;
    if(this.affiliate.service_years==null ||this.affiliate.service_months ==null )
    {
      return this.affiliate.category_id
    }
    else{
      if (years < 0 || years >100  ) {
          return "error";
        }
        else{
          if (months > 0) {
          years++;
        }
        let categoria = this.category.find(c =>{
          return c.from <= years && c.to >= years
        })
        if(!!categoria){
          this.affiliate.category_id = categoria.id
        }
        }
    }
  }
  },
  beforeMount() {
    this.getCategory();
    this.getDegree();
    this.getPensionEntity();
    this.getAffiliateState();
    this.getUnit();
    this.getCalculateCategory;
  },
    mounted() {
    if (this.affiliate.id) {
      this.formatDate('dateEntry', this.affiliate.date_entry)
      this.formatDate('dateDerelict', this.affiliate.date_derelict)
      this.formatDate('dateDeath', this.affiliate.date_death)   }
  },
  watch: {
    'affiliate.date_entry': function(date) {
      this.formatDate('dateEntry',date)
    },
    'affiliate.date_derelict': function(date) {
      this.formatDate('dateDerelict',date)
    },
    'affiliate.date_death': function(date) {
      this.formatDate('dateDeath', date)
    }
  },
  methods: {
    formatDate(key, date){
      if(date){
        this.dates[key].formatted = this.$moment(date).format('L')
      } else {
        this.dates[key].formatted=null
      }
    },
    async getCategory() {
    try {
      this.loading = true
      let res = await axios.get(`category`);
      this.category = res.data;
    } catch (e) {
      this.dialog = false;
      console.log(e);
    }finally {
        this.loading = false
      }
  },
    async getAffiliateState() {
    try {
      this.loading = true
      let res = await axios.get(`affiliate_state`);
      this.affiliateState = res.data;
    } catch (e) {
      this.dialog = false;
      console.log(e);
    }finally {
        this.loading = false
      }
  },
    async getDegree() {
    try {
      this.loading = true
      let res = await axios.get(`degree`);
      this.degree = res.data;
    } catch (e) {
      this.dialog = false;
      console.log(e);
    }finally {
        this.loading = false
      }
  },
    async getPensionEntity() {
      try {
      this.loading = true
      let res = await axios.get(`pension_entity`);
      this.pension_entity = res.data;
    } catch (e) {
      this.dialog = false
      console.log(e);
    }finally {
        this.loading = false
      }
    },
    Onchange(){
      if(this.affiliate.affiliate_state_id  == 4){
          this.visible = true
        }else{
          this.visible = false
      }
    },
  async getUnit() {
    try {
      this.loading = true
      let res = await axios.get(`unit`);
      this.unit = res.data;
    } catch (e) {
      this.dialog = false;
      console.log(e);
    }finally {
        this.loading = false
      }
  },
  }
  }
</script>
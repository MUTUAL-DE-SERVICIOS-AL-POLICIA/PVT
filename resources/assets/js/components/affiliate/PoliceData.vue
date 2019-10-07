<template>
  <v-container fluid >
      <v-row justify="center">
        <v-col cols="12" md="11" class="v-card-profile" >
              <v-row justify="center">
              <v-col cols="12">
                <v-toolbar-title>INFORMACION POLICIAL</v-toolbar-title>
              </v-col>
            <v-col cols="12" md="7" >
              <v-select
                :loading="loading"
                :items="affiliateState"
                data-vv-name="Estado"
                item-text="name"
                item-value="id"
                label="Estado"
                v-model="affiliate.affiliate_state_id"
                hint="Activo"
                persistent-hint
              ></v-select>
            </v-col>
            <v-col cols="12" md="5" >
              <v-menu
                v-model="menu3"
                :close-on-content-click="false"
                :nudge-right="40"
                transition="scale-transition"
                offset-y
                min-width="290px"
              >
              <template v-slot:activator="{ on }">
                <v-text-field
                  v-model="affiliate.date_entry"
                  label="Fecha Ingreso a la Institucion Policial"
                  append-icon="mdi-calendar"
                  readonly
                  v-on="on"
                ></v-text-field>
                </template>
                <v-date-picker v-model="affiliate.date_entry" @input="menu3 = false"></v-date-picker>
              </v-menu>
            </v-col>
            <v-col cols="12" md="6" >
              <v-select
                :loading="loading"
                data-vv-name="Categoria"
                :items="category"
                item-text="name"
                item-value="id"
                label="Categoria"
                name="categoria"
                v-model="affiliate.category_id"
              ></v-select>
            </v-col>
            <v-col cols="12" md="6">
              <v-text-field
                v-model="affiliate.service_years"
                label="Años de Servicio"
                class="purple-input"
                v-validate.initial="'numeric|min:1|max:100'"
                :error-messages="errors.collect('nro')"
                data-vv-name="nro"
              ></v-text-field>
            </v-col>
            <v-col cols="12" md="6" >
              <v-text-field
                v-model="affiliate.service_months"
                label="Meses de Servicio"
                class="purple-input"
                v-validate.initial="'numeric|min:1|max:100'"
                :error-messages="errors.collect('nro')"
                data-vv-name="nro"
              ></v-text-field>
            </v-col>
            <v-col cols="12" md="6" >
              <v-select
                :loading="loading"
                data-vv-name="Grado"
                :items="degree"
                item-text="name"
                item-value="id"
                label="Grado"
                name="Grado"
                v-model="affiliate.degree_id"
                ></v-select>
              </v-col>
            <v-col cols="12" >
              <v-select
                :loading="loading"
                data-vv-name="Gestor"
                :items="pension_entity"
                item-text="name"
                item-value="id"
                label="Ente Gestor"
                name="Grado"
                v-model="affiliate.pension_entity_id"
            ></v-select>
            </v-col>
            <v-col cols="12">
              <v-menu
                v-model="menu4"
                :close-on-content-click="false"
                :nudge-right="40"
                transition="scale-transition"
                offset-y
                min-width="290px"
              >
              <template v-slot:activator="{ on }">
                <v-text-field
                v-model="affiliate.date_derelict"
                label="Fecha Desvinculacion"
                append-icon="mdi-calendar"
                readonly
                v-on="on"
                ></v-text-field>
                </template>
                <v-date-picker v-model="affiliate.date_derelict" @input="menu4 = false"></v-date-picker>
              </v-menu>
            </v-col>
          </v-row>
        </v-col>
      </v-row>
  </v-container>
</template>

<script>
  export default {
  name: "affiliate-police-data",
  data: () => ({
    affiliate: {
    date_entry:null,
    service_years:null,
    service_months:null,
    date_derelict:null
    },
    affiliateState: [],
    category: [],
    degree: [],
    pension_entity: [],
    menu3: false,
    menu4: false,
      }),
  beforeMount() {
    this.getCategory();
    this.getDegree();
    this.getPensionEntity();
    this.getAffiliateState();
  },
  mounted() {
    if (this.$route.params.id != 'new') {
      this.getAffiliate(this.$route.params.id)
    }
  },
  methods: {
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
      let res = await axios.get(`affiliateState`);
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
      let res = await axios.get(`pensionEntity`);
      this.pension_entity = res.data;
    } catch (e) {
      this.dialog = false;
      console.log(e);
    }finally {
        this.loading = false
      }
    },
    async getAffiliate(id) {
      try {
        this.loading = true
        let res = await axios.get(`affiliate/${id}`)
        this.affiliate = res.data
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    }
  }
  }
</script>
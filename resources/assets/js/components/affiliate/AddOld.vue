<template>
  <v-container fluid >
      <v-row justify="center">
        <v-col cols="12" md="6" >
          <v-toolbar-title>{{titulo}}</v-toolbar-title>
              <v-container class="py-0">
                <v-row>
                    <v-col cols="12" md="6" >
                      <v-text-field
                      v-model="affiliate.first_name"
                      class="purple-input"
                      label="Primer Nombre"
                      v-validate.initial="'required|min:1|max:250'"
                      :error-messages="errors.collect('primer nombre')"
                      data-vv-name="primer nombre"
                      ></v-text-field>
                    </v-col>
                    <v-col cols="12" md="6" >
                      <v-text-field
                      v-model="affiliate.second_name"
                      label="Segundo Nombre"
                      class="purple-input"
                      data-vv-name="segundo nombre"
                      ></v-text-field>
                    </v-col>
                    <v-col cols="12" md="4" >
                      <v-text-field
                      v-model="affiliate.last_name"
                      label="Primer Apellido"
                      class="purple-input"
                      v-validate.initial="'min:1|max:250'"
                      :error-messages="errors.collect('primer apellido')"
                      data-vv-name="primer apellido"
                      ></v-text-field>
                    </v-col>
                    <v-col cols="12" md="4" >
                      <v-text-field
                      v-model="affiliate.mothers_last_name"
                      label="Segundo Apellido"
                      class="purple-input"
                      v-validate.initial="'min:1|max:250'"
                      :error-messages="errors.collect('segundo apellido')"
                      data-vv-name="segundo apellido"
                      ></v-text-field>
                    </v-col>
                    <v-col cols="12" md="4" >
                      <v-select
                        data-vv-name="Genero"
                        :items="gender"
                        item-text="name"
                        item-value="value"
                        :loading="loading"
                        label="Genero"
                        v-model="affiliate.gender"
                      ></v-select>
                    </v-col>
                    <v-col cols="12" md="4" >
                      <v-text-field
                        v-model="affiliate.identity_card"
                        class="purple-input"
                        label="Cedula de Identidad"
                        v-validate.initial="'required|numeric|min:1|max:50'"
                        :error-messages="errors.collect('cedula identidad')"
                        data-vv-name="cedula identidad"
                      ></v-text-field>
                    </v-col>
                    <v-col cols="12" md="4" >
                      <v-select
                        data-vv-name="Ciudad de Expedición"
                        :items="cities"
                        item-text="name"
                        item-value="id"
                        :loading="loading"
                        label="Ciudad de Expedición"
                        v-model="affiliate.city_identity_card_id"
                      ></v-select>
                    </v-col>
                    <v-col cols="12" md="4">
                      <v-menu
                        v-model="menu"
                        :close-on-content-click="false"
                        :nudge-right="40"
                        transition="scale-transition"
                        offset-y
                        min-width="290px"
                      >
                      <template v-slot:activator="{ on }">
                        <v-text-field
                          v-model="affiliate.due_date"
                          label="Fecha Vencimiento CI"
                          append-icon="mdi-calendar"
                          readonly
                          v-on="on"
                        ></v-text-field>
                        </template>
                        <v-date-picker v-model="affiliate.due_date" @input="menu = false"></v-date-picker>
                      </v-menu>
                    </v-col>
                    <v-col cols="12" md="4" >
                      <v-menu
                        v-model="menu1"
                        :close-on-content-click="false"
                        :nudge-right="40"
                        transition="scale-transition"
                        offset-y
                        min-width="290px"
                      >
                      <template v-slot:activator="{ on }">
                        <v-text-field
                          v-model="affiliate.birth_date"
                          label="Fecha Nacimiento"
                          append-icon="mdi-calendar"
                          readonly
                          v-on="on"
                        ></v-text-field>
                        </template>
                        <v-date-picker v-model="affiliate.birth_date" @input="menu1 = false"></v-date-picker>
                      </v-menu>
                    </v-col>
                    <v-col cols="12" md="4" >
                      <v-select
                        :loading="loading"
                        data-vv-name="Ciudad de Nacimiento"
                        :items="cities"
                        item-text="name"
                        item-value="id"
                        name="nacimiento"
                        label="Lugar de Nacimiento"
                        v-model="affiliate.city_birth_id"
                      ></v-select>
                    </v-col>
                    <v-col cols="12" md="4" >
                      <v-select
                        :loading="loading"
                        data-vv-name="Estado Civil"
                        :items="civil"
                        item-text="name"
                        item-value="value"
                        label="Estado Civil"
                        name="estado_civil"
                        v-model="affiliate.civil_status"
                      ></v-select>
                    </v-col>
                    <v-col cols="12" md="6">
                      <v-menu
                        v-model="menu2"
                        :close-on-content-click="false"
                        :nudge-right="40"
                        transition="scale-transition"
                        offset-y
                        min-width="290px"
                      >
                      <template v-slot:activator="{ on }">
                        <v-text-field
                          v-model="affiliate.date_death"
                          label="Fecha Fallesimiento"
                          append-icon="mdi-calendar"
                          readonly
                          v-on="on"
                        ></v-text-field>
                        </template>
                        <v-date-picker v-model="affiliate.date_death" @input="menu2 = false"></v-date-picker>
                      </v-menu>
                    </v-col>
                    <v-col cols="12" md="6">
                      <v-text-field
                        v-model="affiliate.reason_death"
                        label="Causa Fallecimiento"
                        class="purple-input"
                      ></v-text-field>
                    </v-col>
                  <v-col cols="12" md="6" >
                    <v-text-field
                      v-model="affiliate.cell_phone_number"
                      label="Celular"
                      class="purple-input"
                      v-validate.initial="'min:1|max:20'"
                      :error-messages="errors.collect('celular')"
                      data-vv-name="celular"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6" >
                    <v-text-field
                      v-model="affiliate.phone_number"
                      label="Telefono"
                      class="purple-input"
                      v-validate.initial="'min:1|max:20'"
                      :error-messages="errors.collect('telefono')"
                      data-vv-name="telefono"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12">
                    <v-toolbar-title>DIRECCION DOMICILARIA</v-toolbar-title>
                  </v-col>
                  <v-col cols="12" md="6" >
                    <v-select
                      :items="city"
                      name="ciudad"
                      label="Ciudad"
                      v-model="cityTypeSelected"
                    ></v-select>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      label="Zona"
                      class="purple-input"
                      v-validate.initial="'min:1|max:250'"
                      :error-messages="errors.collect('zona')"
                      data-vv-name="zona"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      label="Calle"
                      class="purple-input"
                      v-validate.initial="'min:1|max:250'"
                      :error-messages="errors.collect('calle')"
                      data-vv-name="calle"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6" >
                    <v-text-field
                      label="Nro"
                      class="purple-input"
                      v-validate.initial="'numeric|min:1|max:10000'"
                      :error-messages="errors.collect('nro')"
                      data-vv-name="nro"
                    ></v-text-field>
                  </v-col>
                </v-row>
              </v-container>
        </v-col>
        <v-col cols="12" md="5" class="v-card-profile" >
            <v-avatar
              slot="offset"
              class="mx-auto d-block elevation-10"
              size="170"
            >
              <img  src="https://demos.creative-tim.com/vue-material-dashboard/img/marc.aba54d65.jpg" >
            </v-avatar>
            <v-card-text class="text-center">
              <v-btn type="file" color="primary">
                Adicionar Foto
              </v-btn>
              <v-btn
                color="primary"
                @click.stop="fingerprintCaptureStart()" v-if="affiliate.hasOwnProperty('id')"
                :disabled="fingerprintSucess"
              >
                <v-icon left>mdi-fingerprint</v-icon>
                <span v-if="fingerprintSucess">Huella capturada</span>
                <span v-else>Capturar huella</span>
              </v-btn>
              <v-btn color="primary">
                Informacion Conyugue
              </v-btn>
            </v-card-text>
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
              <v-col cols="12" md="6" class="text-center" >
                <v-btn
                @click="saveAffiliate()"
                :disabled="errors.any()"
                color="success">
                Guardar
                </v-btn>
                <v-btn
                color="warning"
                :to="{name:'affiliateIndex'}"
                >
                Cancelar
                </v-btn>
              </v-col>
            </v-row>
        </v-col>
      </v-row>
    <v-dialog
      v-model="fingerprintCapture"
      persistent
      width="400"
    >
      <v-card
        color="primary"
        class="py-3"
        dark
      >
        <v-card-text>
          <div class="subtitle-1 font-weight-light">Continue el proceso en el equipo biométrico ...</div>
          <v-progress-linear
            indeterminate
            color="white"
            class="mt-4"
          ></v-progress-linear>
        </v-card-text>
      </v-card>
    </v-dialog>
    <v-dialog
      v-model="fingerprintSaved"
      width="500"
    >
      <v-card>
        <v-alert :type="fingerprintSucess ? 'success' : 'error'" class="ma-0">
          <div v-if="fingerprintSucess">
            Las huellas se registraron correctamente
          </div>
          <div v-else>
            Error al capturar las huellas, vuelva a realizar el proceso
          </div>
        </v-alert>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn
            color="primary"
            @click.stop="fingerprintSaved = false"
          >
            Cerrar
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script>
import List from '@/components/affiliate/List'
  export default {
  data: () => ({
    fingerprintCapture: false,
    fingerprintSaved: false,
    fingerprintSucess: null,
    affiliate: {
    first_name: null,
    second_name:null,
    last_name: null,
    mothers_last_name:null,
    identity_card:null,
    birth_date:null,
    date_death:null,
    reason_death:null,
    date_entry:null,
    phone_number:null,
    cell_phone_number:null,
    service_years:null,
    service_months:null,
    city_identity_card_id:null,
    date_derelict:null
    },
    loading: true,
    cities: [],
    Genero: [
      'Femenino',
      'Masculino'
    ],
    civil: [
      { name:"Soltero",
        value:"S"
      },
      { name:"Casado",
        value:"C"
      },
      { name:"Viudo",
        value:"V"
      },
      { name:"Divorciado",
        value:"D"
      }
    ],
    gender: [
      { name:"Femenino",
        value:"F"
      },
      { name:"Masculino",
        value:"M"
      }
    ],
    affiliateState: [],
    category: [],
    degree: [],
    city: [],
    pension_entity: [],
    cityTypeSelected: null,
      date: null,
        menu: false,
        menu1: false,
        modal: false,
        menu2: false,
        menu3: false,
        menu4: false,
        titulo:null
      }),
  components: {
  List,
  },
  watch: {
    search: _.debounce(function () {
      this.bus.$emit('search', this.search)
    }, 1000),
  },
  destroyed() {
    Echo.leave('fingerprint')
  },
  beforeMount() {
    this.getCategory();
    this.getCities();
    this.getDegree();
    this.getPensionEntity();
    this.getAffiliateState();
    if (this.$route.params.id != 'new') {
      Echo.channel('fingerprint').listen('.saved', (msg) => {
        if (msg.data.affiliate_id == this.affiliate.id && msg.data.user_id == this.$store.getters.id) {
          this.fingerprintCapture = false
          this.fingerprintSaved = true
          this.fingerprintSucess = JSON.parse(msg.data.success)
        }
      })
    }
  },
  mounted() {
    (this.$route.params.id=='new')?this.titulo='Nuevo Afiliado':this.titulo='Editar Afiliado'
    if (this.$route.params.id != 'new') {
      this.getAffiliate(this.$route.params.id)
    }
  },
  methods: {
    async saveAffiliate() {
    try {
      if (await this.$validator.validateAll()) {
        this.loading = true
        if (this.$route.params.id != 'new') {
          let res = await axios.patch(`affiliate/${this.affiliate.id}`, this.affiliate)
          console.log(res.data)
          this.$router.push({
          name: "affiliateIndex"
          });
          this.toast('Afiliado modificado', 'success')
        } else {
          let res = await axios.post(`affiliate`, this.affiliate)
          this.toast('Afiliado adicionado', 'success')
          this.$router.push({
          name: "affiliateIndex"
          });console.log(res.data)
        }
      }
    } catch (e) {
      console.log(e)
    } finally {
      this.loading = false
    }
    },
    async fingerprintCaptureStart() {
      try {
        this.fingerprintCapture = true
        await axios.patch(`affiliate/${this.affiliate.id}/fingerprint`)
      } catch (e) {
        console.log(e)
        this.toast('Error al comunicarse con el dispositivo de captura de huellas', 'error')
        this.fingerprintCapture = false
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
      let res = await axios.get(`affiliateState`);
      this.affiliateState = res.data;
    } catch (e) {
      this.dialog = false;
      console.log(e);
    }finally {
        this.loading = false
      }
  },
    async getCities() {
    try {
      this.loading = true
      let res = await axios.get(`city`);
      this.cities = res.data;
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
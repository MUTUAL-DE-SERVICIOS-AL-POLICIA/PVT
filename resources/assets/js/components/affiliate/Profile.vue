<template>
  <v-container fluid >
      <v-row justify="center">
        <v-col cols="12" md="6" >
              <v-container class="py-0">
                <v-row>
                  <v-col cols="12">
                    <v-toolbar-title>DATOS PERSONALES</v-toolbar-title>
                  </v-col>
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
                </v-row>
              </v-container>
        </v-col>
          <v-col cols="12" md="5" >
                <v-container class="py-0">
                  <v-row>
                    <v-col cols="12">
                      <v-toolbar-title>TELÉFONOS</v-toolbar-title>
                    </v-col>
                    <v-col cols="12" md="4" >
                      <v-text-field
                        v-model="affiliate.phone_number"
                        label="Telefono"
                        v-validate.initial="'min:1|max:20'"
                        :error-messages="errors.collect('telefono')"
                        data-vv-name="telefono"
                      ></v-text-field>
                    </v-col>
                    <v-col cols="12" md="4" >
                      <v-text-field
                        v-model="affiliate.cell_phone_number"
                        label="Celular"
                        class="purple-input"
                        v-validate.initial="'min:1|max:20'"
                        :error-messages="errors.collect('celular')"
                        data-vv-name="celular"
                      ></v-text-field>
                    </v-col>
                    <v-col cols="12" md="4" >
                      <v-text-field
                        v-model="affiliate.cell_phone_number"
                        label="Celular"
                        class="purple-input"
                        v-validate.initial="'min:1|max:20'"
                        :error-messages="errors.collect('celular')"
                        data-vv-name="celular"
                      ></v-text-field>
                    </v-col>
                      <v-col cols="12" md="6">
                    <v-toolbar-title>DIRECCIÓN DOMICILARIA</v-toolbar-title>
                  </v-col>
                    <v-col cols="12" md="3">
                    <v-btn
                      fab
                      dark
                      x-small
                      color="info"
                      bottom
                      left
                      :to="{ name: 'affiliateAdd', params: { id:'new'} }"
                    >
                      <v-icon>mdi-plus</v-icon>
                    </v-btn>
                  </v-col>
                    <v-col cols="12" md="4" >
                      <v-select
                        :items="city"
                        name="ciudad"
                        label="Ciudad"
                        v-model="cityTypeSelected"
                      ></v-select>
                    </v-col>
                    <v-col cols="12" md="4" >
                      <v-text-field
                        label="Zona"
                        class="purple-input"
                        v-validate.initial="'min:1|max:250'"
                        :error-messages="errors.collect('zona')"
                        data-vv-name="zona"
                      ></v-text-field>
                    </v-col>
                    <v-col cols="12" md="4" >
                      <v-text-field
                        label="Calle"
                        class="purple-input"
                        v-validate.initial="'min:1|max:250'"
                        :error-messages="errors.collect('calle')"
                        data-vv-name="calle"
                      ></v-text-field>
                    </v-col>
                    <v-col cols="12" md="4" >
                      <v-text-field
                        label="Nro"
                        class="purple-input"
                        v-validate.initial="'numeric|min:1|max:10000'"
                        :error-messages="errors.collect('nro')"
                        data-vv-name="nro"
                      ></v-text-field>
                      <v-card-text @click="dialog=true" class="text-center">
                      <v-btn type="file" color="success">
                        Adicionar Foto
                      </v-btn>
                      </v-card-text>
                    </v-col>
                </v-row>
                <v-dialog v-model="dialog" persistent max-width="1000">
                  <v-card>
                  <v-divider></v-divider>
                  <camara></camara>
                  <v-card-actions>
                  <v-btn color="error" @click="dialog=false">CERRAR</v-btn>
                  </v-card-actions>
                  </v-card>
                </v-dialog>
              </v-container>
        </v-col>
      </v-row>
  </v-container>
</template>

<script>
import Cam from '@/components/affiliate/Webcam'
  export default {
  name: "affiliate-profile",
  data: () => ({
    dialog:false,
    cam: null,
    affiliate: {
    first_name: null,
    second_name:null,
    last_name: null,
    mothers_last_name:null,
    identity_card:null,
    birth_date:null,
    date_death:null,
    reason_death:null,
    phone_number:null,
    cell_phone_number:null,
    city_identity_card_id:null,
    },
    loading: true,
    cities: [],
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
    city: [],
    cityTypeSelected: null,
      date: null,
        menu: false,
        menu1: false,
        menu2: false,
      }),
    components: {
    'camara': Cam
    },
  beforeMount() {
    this.getCities();
  },
  mounted() {
    if (this.$route.params.id != 'new') {
      this.getAffiliate(this.$route.params.id)
    }
  },
  methods: {
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
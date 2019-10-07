<template>
  <v-container fluid >
      <v-row justify="center">
        <v-col cols="12" md="6" >
              <v-container class="py-0">
                <v-row>
                  <v-col cols="12">
                    <v-toolbar-title>INFORMACION CONYUGE</v-toolbar-title>
                  </v-col>
                    <v-col cols="12" md="6" >
                      <v-text-field
                      v-model="spouse.first_name"
                      class="purple-input"
                      label="Primer Nombre"
                      v-validate.initial="'required|min:1|max:250'"
                      :error-messages="errors.collect('primer nombre')"
                      data-vv-name="primer nombre"
                      ></v-text-field>
                    </v-col>
                    <v-col cols="12" md="6" >
                      <v-text-field
                      v-model="spouse.second_name"
                      label="Segundo Nombre"
                      class="purple-input"
                      data-vv-name="segundo nombre"
                      ></v-text-field>
                    </v-col>
                    <v-col cols="12" md="4" >
                      <v-text-field
                      v-model="spouse.last_name"
                      label="Primer Apellido"
                      class="purple-input"
                      v-validate.initial="'min:1|max:250'"
                      :error-messages="errors.collect('primer apellido')"
                      data-vv-name="primer apellido"
                      ></v-text-field>
                    </v-col>
                    <v-col cols="12" md="4" >
                      <v-text-field
                      v-model="spouse.mothers_last_name"
                      label="Segundo Apellido"
                      class="purple-input"
                      v-validate.initial="'min:1|max:250'"
                      :error-messages="errors.collect('segundo apellido')"
                      data-vv-name="segundo apellido"
                      ></v-text-field>
                    </v-col>
                    <v-col cols="12" md="4" >
                      <v-text-field
                      v-model="spouse.surname_husband"
                      label="Apellido Casada"
                      class="purple-input"
                      v-validate.initial="'min:1|max:250'"
                      :error-messages="errors.collect('apellido casado')"
                      data-vv-name="apellido casado"
                      ></v-text-field>
                    </v-col>
                    <v-col cols="12" md="4" >
                      <v-text-field
                        v-model="spouse.identity_card"
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
                        v-model="spouse.city_identity_card_id"
                      ></v-select>
                    </v-col>
                    <v-col cols="12" md="4">
                      <v-text-field
                      v-model="spouse.death_certificate_number"
                      label="Nro de Certificado de Defuncion "
                      class="purple-input"
                      v-validate.initial="'min:1|max:20'"
                      :error-messages="errors.collect('celular')"
                      data-vv-name="celular"
                      ></v-text-field>
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
                          v-model="spouse.birth_date"
                          label="Fecha Nacimiento"
                          append-icon="mdi-calendar"
                          readonly
                          v-on="on"
                        ></v-text-field>
                        </template>
                        <v-date-picker v-model="spouse.birth_date" @input="menu1 = false"></v-date-picker>
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
                        v-model="spouse.city_birth_id"
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
                        v-model="spouse.civil_status"
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
                          v-model="spouse.date_death"
                          label="Fecha Fallesimiento"
                          append-icon="mdi-calendar"
                          readonly
                          v-on="on"
                        ></v-text-field>
                        </template>
                        <v-date-picker v-model="spouse.date_death" @input="menu2 = false"></v-date-picker>
                      </v-menu>
                    </v-col>
                    <v-col cols="12" md="6">
                      <v-text-field
                        v-model="spouse.reason_death"
                        label="Causa Fallecimiento"
                        class="purple-input"
                      ></v-text-field>
                    </v-col>
                </v-row>
              </v-container>
        </v-col>
        <v-col cols="12" md="5" class="v-card-profile" >
        <v-col cols="12">
            <v-toolbar-title>INFORMACION DE SERECI</v-toolbar-title>
          </v-col>
          <v-col cols="12"  >
            <v-text-field
              label="Oficialia"
              class="purple-input"
              v-validate.initial="'min:1|max:250'"
              :error-messages="errors.collect('oficialia')"
              data-vv-name="oficialia"
            ></v-text-field>
          </v-col>
          <v-col cols="12" >
            <v-text-field
              label="Libro"
              class="purple-input"
              v-validate.initial="'min:1|max:250'"
              :error-messages="errors.collect('libro')"
              data-vv-name="libro"
            ></v-text-field>
          </v-col>
          <v-col cols="12" >
            <v-text-field
              label="Partida"
              class="purple-input"
              v-validate.initial="'min:1|max:250'"
              :error-messages="errors.collect('partida')"
              data-vv-name="partida"
            ></v-text-field>
          </v-col>
          <v-col cols="12"  >
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
                v-model="spouse.marriage_date"
                label="Fecha Matrimonio"
                append-icon="mdi-calendar"
                readonly
                v-on="on"
              ></v-text-field>
              </template>
              <v-date-picker v-model="spouse.marriage_date" @input="menu3 = false"></v-date-picker>
            </v-menu>
          </v-col>
        </v-col>
      </v-row>
  </v-container>
</template>

<script>
  export default {
  name: "affiliate-spouse",
  data: () => ({
  spouse: {
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
    city: [],
      date: null,
        menu3: false,
        menu1: false,
        menu2: false,
      }),
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
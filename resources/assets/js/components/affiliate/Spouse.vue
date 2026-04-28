<template>
  <v-container fluid>
    <ValidationObserver ref="observer">
      <v-form>
        <v-row justify="center">
          <v-col cols="12" md="6" class="v-card-profile">
            <v-row>
              <v-col cols="12">
                <v-toolbar-title>INFORMACIÓN CONYUGE</v-toolbar-title>
              </v-col>
              <v-col cols="12" md="4">
                <ValidationProvider
                  v-slot="{ errors }"
                  vid="first_name"
                  name="primer nombre"
                  rules="required|min:1|max:250"
                >
                  <v-text-field
                    :error-messages="errors"
                    dense
                    v-model="spouse.first_name"
                    class="purple-input"
                    label="Primer Nombre"
                    :readonly="!editable || !permission.secondary"
                    :outlined="editable && permission.secondary"
                    :disabled="(editable && !permission.secondary)"
                  ></v-text-field>
                </ValidationProvider>
              </v-col>
              <v-col cols="12" md="4">
                <v-text-field
                  dense
                  v-model="spouse.second_name"
                  label="Segundo Nombre"
                  :readonly="!editable || !permission.secondary"
                  :outlined="editable && permission.secondary"
                  :disabled="(editable && !permission.secondary)"
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="4">
                <ValidationProvider
                  v-slot="{ errors }"
                  vid="last_name"
                  name="primer apellido"
                  rules="min:1|max:250"
                >
                  <v-text-field
                    :error-messages="errors"
                    dense
                    v-model="spouse.last_name"
                    label="Primer Apellido"
                    class="purple-input"
                    :readonly="!editable || !permission.secondary"
                    :outlined="editable && permission.secondary"
                    :disabled="(editable && !permission.secondary)"
                  ></v-text-field>
                </ValidationProvider>
              </v-col>
              <v-col cols="12" md="4">
                <ValidationProvider
                  v-slot="{ errors }"
                  vid="mothers_last_name"
                  name="segundo apellido"
                  rules="min:1|max:250"
                >
                  <v-text-field
                    :error-messages="errors"
                    dense
                    v-model="spouse.mothers_last_name"
                    label="Segundo Apellido"
                    class="purple-input"
                    :readonly="!editable || !permission.secondary"
                    :outlined="editable && permission.secondary"
                    :disabled="(editable && !permission.secondary)"
                  ></v-text-field>
                </ValidationProvider>
              </v-col>
              <v-col cols="12" md="4">
                <ValidationProvider
                  v-slot="{ errors }"
                  vid="surname_husband"
                  name="apellido casado"
                  rules="min:1|max:250"
                >
                  <v-text-field
                    :error-messages="errors"
                    dense
                    v-model="spouse.surname_husband"
                    label="Apellido Casada"
                    class="purple-input"
                    :readonly="!editable || !permission.secondary"
                    :outlined="editable && permission.secondary"
                    :disabled="(editable && !permission.secondary)"
                  ></v-text-field>
                </ValidationProvider>
              </v-col>
              <v-col cols="12" md="4">
                <ValidationProvider
                  v-slot="{ errors }"
                  vid="identity_card"
                  name="cédula identidad"
                  rules="required|min:1|max:50"
                >
                  <v-text-field
                    :error-messages="errors"
                    dense
                    v-model="spouse.identity_card"
                    class="purple-input"
                    label="Cédula de Identidad"
                    :readonly="!editable || !permission.secondary"
                    :outlined="editable && permission.secondary"
                    :disabled="(editable && !permission.secondary)"
                  ></v-text-field>
                </ValidationProvider>
              </v-col>
              <v-col
                cols="12"
                md="4"
                v-if="spouse.is_duedate_undefined != true"
              >
                <v-text-field
                  dense
                  v-model="spouse.due_date"
                  label="Fecha Vencimiento CI"
                  hint="Día/Mes/Año"
                  class="purple-input"
                  type="date"
                  :readonly="!editable || !permission.secondary"
                  :outlined="editable && permission.secondary"
                  :disabled="(editable && !permission.secondary)"
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="3">
                <v-checkbox
                  v-model="spouse.is_duedate_undefined"
                  :readonly="!editable || !permission.secondary"
                  :outlined="editable && permission.secondary"
                  :disabled="(editable && !permission.secondary)"
                  :label="`Indefinido`"
                ></v-checkbox>
              </v-col>
              <v-col cols="12" md="4">
                <v-select
                  dense
                  :loading="loading"
                  :items="civil"
                  item-text="name"
                  item-value="value"
                  label="Estado Civil"
                  name="estado_civil"
                  v-model="spouse.civil_status"
                  :readonly="!editable || !permission.secondary"
                  :outlined="editable && permission.secondary"
                  :disabled="(editable && !permission.secondary)"
                ></v-select>
              </v-col>
              <v-col cols="12" md="4">
                <v-text-field
                  dense
                  v-model="spouse.birth_date"
                  name="spouse_birth_date"
                  label="Fecha Nacimiento"
                  hint="Día/Mes/Año"
                  type="date"
                  :readonly="!editable || !permission.secondary"
                  :outlined="editable && permission.secondary"
                  :disabled="(editable && !permission.secondary)"
                ></v-text-field>
              </v-col>

              <v-col cols="12" md="4">
                <v-select
                  dense
                  :loading="loading"
                  :items="cities"
                  item-text="name"
                  item-value="id"
                  name="nacimiento"
                  label="Lugar de Nacimiento"
                  v-model="spouse.city_birth_id"
                  :readonly="!editable || !permission.secondary"
                  :outlined="editable && permission.secondary"
                  :disabled="(editable && !permission.secondary)"
                ></v-select>
              </v-col>
            <v-col cols="12" md="12">
              <v-toolbar-title>INFORMACIÓN DE CONTACTO (Conyuge Anuente)</v-toolbar-title>
            </v-col>
              <v-col cols="12" md="6">
                <ValidationProvider
                  v-slot="{ errors }"
                  name="celular1"
                  rules="min:11|max:11"
                >
                  <v-text-field
                    :error-messages="errors"
                    dense
                    v-model="cel[0]"
                    label="Celular1"
                    :readonly="!editable || !permission.secondary"
                    :outlined="editable && permission.secondary"
                    :disabled="editable && !permission.secondary"
                    @change="updateCelular()"
                    v-mask="'(###)-#####'"
                  ></v-text-field>
                </ValidationProvider>
              </v-col>
              <v-col cols="12" md="6">
                <ValidationProvider
                  v-slot="{ errors }"
                  name="celular2"
                  rules="min:11|max:11"
                >
                  <v-text-field
                    :error-messages="errors"
                    dense
                    v-model="cel[1]"
                    label="Celular2"
                    :readonly="!editable || !permission.secondary"
                    :outlined="editable && permission.secondary"
                    :disabled="editable && !permission.secondary"
                    @change="updateCelular()"
                    v-mask="'(###)-#####'"
                  ></v-text-field>
                </ValidationProvider>
              </v-col>
            </v-row>
          </v-col>
          <v-col cols="12" md="3" class="v-card-profile" >
            <v-row>
              <v-col cols="12">
                <v-toolbar-title>INFORMACIÓN SERECI</v-toolbar-title>
              </v-col>
            <v-col cols="12" md="12">
              <ValidationProvider v-slot="{ errors }" vid="departure" name="partida" rules="integer">
              <v-text-field
                :error-messages="errors"
                dense
                v-model="spouse.departure"
                label="Número de Partida"
                class="purple-input"
                :readonly="!editable || !permission.secondary"
                :outlined="editable && permission.secondary"
                :disabled="(editable && !permission.secondary)"
              ></v-text-field>
              </ValidationProvider>
            </v-col>
            <v-col cols="12" md="12" >
              <v-text-field
                dense
                v-model="spouse.marriage_issue_date"
                label="Fecha de emisión Cert. Matrimonio"
                hint="Día/Mes/Año"
                class="purple-input"
                type="date"
                :onclick="Death()"
                clearable
                :readonly="!editable || !permission.secondary"
                :outlined="editable && permission.secondary"
                :disabled="(editable && !permission.secondary)"
              ></v-text-field>
            </v-col>
          </v-row>
        </v-col>
        <v-col cols="12" md="3" class="v-card-profile">
          <v-row>
            <v-col cols="12">
              <v-toolbar-title>INFORMACION DECESO</v-toolbar-title>
            </v-col>
            <v-col cols="12" md="12">
              <v-text-field
                dense
                v-model="spouse.date_death"
                label="Fecha Fallecimiento"
                hint="Día/Mes/Año"
                class="purple-input"
                type="date"
                :onclick="Death()"
                :readonly="!editable || !permission.secondary"
                :outlined="editable && permission.secondary"
                :disabled="(editable && !permission.secondary)"
              ></v-text-field>
            </v-col>
            <v-col cols="12" md="12" v-if="!visible">
              <ValidationProvider
                v-slot="{ errors }"
                vid="death_certificate_number"
                name="cert. de defunción"
                rules="min:1|max:20"
              >
                <v-text-field
                  :error-messages="errors"
                  dense
                  v-model="spouse.death_certificate_number"
                  label="Cert. de Defunción"
                  class="purple-input"
                  :readonly="!editable || !permission.secondary"
                  :outlined="editable && permission.secondary"
                  :disabled="(editable && !permission.secondary)"
                ></v-text-field>
              </ValidationProvider>
            </v-col>
            <v-col cols="12" md="12" v-if="!visible">
              <v-textarea
                dense
                v-model="spouse.reason_death"
                label="Causa del Fallecimiento"
                class="purple-input"
                :readonly="!editable || !permission.secondary"
                :outlined="editable && permission.secondary"
                :disabled="(editable && !permission.secondary)"
                rows="3"
              ></v-textarea>
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
  name: "affiliate-spouse",
  props: {
    spouse: {
      type: Object,
      required: true,
    },
    state_id: {
      type: Number,
      required: true,
    },
    editable: {
      type: Boolean,
      required: true,
    },
    permission: {
      type: Object,
      required: true,
    },
  },
  data: () => ({
    loading: true,
    cities: [],
    civil: [
      { name: "Soltero", value: "S" },
      { name: "Casado", value: "C" },
      { name: "Viudo", value: "V" },
      { name: "Divorciado", value: "D" },
    ],
    dates: {
      dueDate: {
        formatted: null,
        picker: false,
      },
      birthDate: {
        formatted: null,
        picker: false,
      },
      dateDeath: {
        formatted: null,
        picker: false,
      },
      marriageIssueDate: {
        formatted: null,
        picker: false,
      },

    },
    visible: false,
    cel: [null, null],
  }),
  beforeMount() {
    this.getCities();
  },
  mounted() {
    if (this.spouse.id) {
      this.formatDate("dueDate", this.spouse.due_date),
      this.formatDate("birthDate", this.spouse.birth_date),
      this.formatDate("dateDeath", this.spouse.date_death),
      this.formatDate("marriageIssueDate", this.spouse.marriage_issue_date)
      this.getCelular();
    }
  },
  watch: {
    "spouse.due_date": function (date) {
      this.formatDate("dueDate", date);
    },
    "spouse.birth_date": function (date) {
      this.formatDate("birthDate", date);
    },
    "spouse.date_death": function (date) {
      this.formatDate("dateDeath", date);
    },
    "spouse.marriage_issue_date": function (date) {
      this.formatDate("marriageIssueDate", date);
    }
  },
  methods: {
    formatDate(key, date) {
      if (date) {
        this.dates[key].formatted = this.$moment(date).format("L");
      } else {
        this.dates[key].formatted = null;
      }
    },
    async getCities() {
      try {
        this.loading = true;
        let res = await axios.get(`city`);
        this.cities = res.data;
      } catch (e) {
        this.dialog = false;
        console.log(e);
      } finally {
        this.loading = false;
      }
    },

    Death() {
      if (this.spouse.date_death == null) {
        this.visible = true;
      } else {
        this.visible = false;
      }
    },
    getCelular() {
      let part = [];
      if (this.spouse.cell_phone_number !== null) {
        part = this.spouse.cell_phone_number.split(",");
        console.log(this.cel)
        this.cel[0] = part[0];
        this.cel[1] = part[1];
      }
    },
    updateCelular() {
      let count = 0;
      let val = 0;
      if (this.cel[0]) {
        if (this.cel[0].trim() !== "") {
          this.cel[0] = this.cel[0].trim();
          count++;
          val = 0;
        }
      }
      if (this.cel[1]) {
        if (this.cel[1].trim() !== "") {
          this.cel[1] = this.cel[1].trim();
          count++;
          val = 1;
        }
      }
      if (count == 0) {
        this.spouse.cell_phone_number = null;
      } else if (count == 1) {
        this.spouse.cell_phone_number = this.cel[val];
      } else {
        this.spouse.cell_phone_number = this.cel.join(",");
      }
    },
  },
};
</script>

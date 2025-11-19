<template>
  <v-container fluid>
    <ValidationObserver ref="observer">
      <v-form>
        <v-row justify="center">
          <v-col cols="12" md="6">
            <v-container class="py-0">
              <v-row>
                <v-col cols="12">
                  <v-toolbar-title>DATOS PERSONALES</v-toolbar-title>
                </v-col>
                <v-col cols="12" md="4">
                  <ValidationProvider
                    v-slot="{ errors }"
                    vid="first_name"
                    name="Primer Nombre"
                    rules="required|alpha_spaces|min:3|max:20"
                  >
                    <v-text-field
                      :error-messages="errors"
                      dense
                      v-model="affiliate.first_name"
                      label="Primer Nombre"
                      :readonly="!editable || !permission.primary"
                      :outlined="editable && permission.primary"
                      :disabled="editable && !permission.primary"
                    ></v-text-field>
                  </ValidationProvider>
                </v-col>
                <v-col cols="12" md="4">
                  <ValidationProvider
                    v-slot="{ errors }"
                    vid="second_name"
                    name="Segundo Nombre"
                    rules="alpha_spaces|min:3|max:20"
                  >
                    <v-text-field
                      :error-messages="errors"
                      dense
                      v-model="affiliate.second_name"
                      label="Segundo Nombre"
                      :readonly="!editable || !permission.primary"
                      :outlined="editable && permission.primary"
                      :disabled="editable && !permission.primary"
                    ></v-text-field>
                  </ValidationProvider>
                </v-col>
                <v-col cols="12" md="4">
                  <ValidationProvider
                    v-slot="{ errors }"
                    vid="last_name"
                    name="Apellido Paterno"
                    rules="alpha_spaces|min:3|max:20"
                  >
                    <v-text-field
                      :error-messages="errors"
                      dense
                      v-model="affiliate.last_name"
                      label="Apellido Paterno"
                      :readonly="!editable || !permission.primary"
                      :outlined="editable && permission.primary"
                      :disabled="editable && !permission.primary"
                    ></v-text-field>
                  </ValidationProvider>
                </v-col>
                <v-col cols="12" md="4">
                  <ValidationProvider
                    v-slot="{ errors }"
                    vid="mothers_last_name"
                    name="Apellido Materno"
                    rules="alpha_spaces|min:3|max:20"
                  >
                    <v-text-field
                      :error-messages="errors"
                      dense
                      v-model="affiliate.mothers_last_name"
                      label="Apellido Materno"
                      :readonly="!editable || !permission.primary"
                      :outlined="editable && permission.primary"
                      :disabled="editable && !permission.primary"
                    ></v-text-field>
                  </ValidationProvider>
                </v-col>
                  <v-col cols="12" md="4">
                  <ValidationProvider
                    v-slot="{ errors }"
                    vid="last_name"
                    name="Apellido de Casada"
                    rules="alpha_spaces|min:3|max:20"
                  >
                    <v-text-field
                      :error-messages="errors"
                      dense
                      v-model="affiliate.surname_husband"
                      label="Apellido de Casada"
                      :readonly="!editable || !permission.primary"
                      :outlined="editable && permission.primary"
                      :disabled="editable && !permission.primary"
                    ></v-text-field>
                  </ValidationProvider>
                </v-col>
                <v-col cols="12" md="4">
                  <ValidationProvider
                    v-slot="{ errors }"
                    vid="gender"
                    name="Género"
                    rules="required|oneOf:M,F"
                  >
                    <v-select
                      :error-messages="errors"
                      dense
                      :items="genders"
                      item-text="name"
                      item-value="value"
                      :loading="loading"
                      label="Género"
                      v-model="affiliate.gender"
                      :readonly="!editable || !permission.secondary"
                      :outlined="editable && permission.secondary"
                      :disabled="editable && !permission.secondary"
                    ></v-select>
                  </ValidationProvider>
                </v-col>
                <v-col cols="12" md="4">
                  <ValidationProvider
                    v-slot="{ errors }"
                    vid="identity_card"
                    name="Cédula de Identidad"
                    rules="required|alpha_dash|min:5|max:15"
                  >
                    <v-text-field
                      :error-messages="errors"
                      dense
                      v-model="affiliate.identity_card"
                      label="Cédula de Identidad"
                      :readonly="!editable || !permission.primary"
                      :outlined="editable && permission.primary"
                      :disabled="editable && !permission.primary"
                    ></v-text-field>
                  </ValidationProvider>
                </v-col>
                <v-col
                  cols="12"
                  md="5"
                  v-if="affiliate.is_duedate_undefined == false"
                >
                  <v-text-field
                    dense
                    v-model="affiliate.due_date"
                    label="Fecha Vencimiento C.I"
                    hint="Día/Mes/Año"
                    class="purple-input"
                    type="date"
                    :clearable="editable"
                    :readonly="!editable || !permission.secondary"
                    :outlined="editable && permission.secondary"
                    :disabled="editable && !permission.secondary"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="3">
                  <v-checkbox
                    v-model="affiliate.is_duedate_undefined"
                    :readonly="!editable || !permission.secondary"
                    :outlined="editable && permission.secondary"
                    :disabled="editable && !permission.secondary"
                    :label="`Indefinido`"
                  ></v-checkbox>
                </v-col>
                <v-col cols="12" md="4">
                  <ValidationProvider
                    v-slot="{ errors }"
                    vid="civil_status"
                    name="Estado Civil"
                    rules="oneOf:C,D,S,V"
                  >
                    <v-select
                      :error-messages="errors"
                      dense
                      :loading="loading"
                      :items="civil_statuses"
                      item-text="name"
                      item-value="value"
                      label="Estado Civil"
                      v-model="affiliate.civil_status"
                      :readonly="!editable || !permission.secondary"
                      :outlined="editable && permission.secondary"
                      :disabled="editable && !permission.secondary"
                    ></v-select>
                  </ValidationProvider>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    dense
                    v-model="affiliate.birth_date"
                    label="Fecha Nacimiento"
                    hint="Día/Mes/Año"
                    class="purple-input"
                    type="date"
                    :readonly="!editable || !permission.secondary"
                    :outlined="editable && permission.secondary"
                    :disabled="editable && !permission.secondary"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <ValidationProvider
                    v-slot="{ errors }"
                    vid="city_birth_id"
                    name="Ciudad de Nacimiento"
                    rules="integer|min:1"
                  >
                    <v-select
                      :error-messages="errors"
                      dense
                      :loading="loading"
                      :items="cities"
                      item-text="name"
                      item-value="id"
                      label="Ciudad de Nacimiento"
                      v-model="affiliate.city_birth_id"
                      :readonly="!editable || !permission.secondary"
                      :outlined="editable && permission.secondary"
                      :disabled="editable && !permission.secondary"
                    ></v-select>
                  </ValidationProvider>
                </v-col>
              </v-row>
            </v-container>
          </v-col>
          <v-col cols="12" md="6" class="v-card-profile">
            <v-row justify="center">
              <v-col cols="12">
                <v-toolbar-title>INFORMACIÓN POLICIAL</v-toolbar-title>
              </v-col>
              <v-col cols="12" md="6">
                <ValidationProvider
                  v-slot="{ errors }"
                  vid="affiliate_state_id"
                  name="Estado"
                  rules="required"
                >
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
                    :readonly="!editable || !permission.primary"
                    :outlined="editable && permission.primary"
                    :disabled="editable && !permission.primary"
                  ></v-select>
                </ValidationProvider>
              </v-col>
              <v-col cols="12" md="6" v-if="!visible">
                <span class="red--text" v-show="(affiliate.death_certificate_number != null && affiliate.death_certificate_number.trim() != '') ||
                    (affiliate.date_death != null && affiliate.date_death.trim() != '') ||
                    (affiliate.reason_death != null && affiliate.reason_death.trim() != '')">
                  ** Se tiene registrado datos de fallecimiento del afiliado,
                  cambie el estado del afiliado a Fallecido.</span>
              </v-col>
              <v-col cols="12" md="6" v-if="visible">
                <v-text-field
                  dense
                  v-model="affiliate.date_death"
                  label="Fecha Fallecimiento"
                  hint="Día/Mes/Año"
                  class="purple-input"
                  type="date"
                  :readonly="!editable || !permission.secondary"
                  :outlined="editable && permission.secondary"
                  :disabled="editable && !permission.secondary"
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="6" v-if="visible">
                <v-text-field
                  dense
                  v-model="affiliate.death_certificate_number"
                  label="N° de Certificado de Defunción"
                  :readonly="!editable || !permission.secondary"
                  :outlined="editable && permission.secondary"
                  :disabled="editable && !permission.secondary"
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="6" v-if="visible">
                <v-text-field
                  dense
                  v-model="affiliate.reason_death"
                  label="Causa Fallecimiento"
                  :readonly="!editable || !permission.secondary"
                  :outlined="editable && permission.secondary"
                  :disabled="editable && !permission.secondary"
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="6">
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
              <v-col cols="12" md="6">
                <ValidationProvider
                  v-slot="{ errors }"
                  vid="degree_id"
                  name="Grado"
                  rules="required"
                >
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
              <v-col cols="12" md="6">
                <ValidationProvider
                  v-slot="{ errors }"
                  vid="category_id"
                  name="Categoria"
                  rules="required"
                >
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
              <v-col cols="12" md="6">
                <ValidationProvider
                  v-slot="{ errors }"
                  vid="unit_id"
                  name="Unidad"
                  rules="required"
                >
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
                    :readonly="!editable || !permission.primary"
                    :outlined="editable && permission.primary"
                    :disabled="editable && !permission.primary"
                  ></v-select>
                </ValidationProvider>
              </v-col>

              <v-col cols="12" md="12">
                <ValidationProvider
                  v-slot="{ errors }"
                  vid="unit_police_description"
                  name="Descripcion de Unidad"
                >
                  <v-text-field
                    :error-messages="errors"
                    dense
                    v-model="affiliate.unit_police_description"
                    label="Descripcion de Unidad"
                    :readonly="!editable || !permission.secondary"
                    :outlined="editable && permission.secondary"
                    :disabled="editable && !permission.secondary"
                  ></v-text-field>
                </ValidationProvider>
              </v-col>
              <v-col cols="12" md="12">
                <ValidationProvider
                  v-slot="{ errors }"
                  vid="availability_info"
                  name="Información de Disponibilidad"
                >
                  <v-text-field
                    :error-messages="errors"
                    dense
                    v-model="affiliate.availability_info"
                    label="Información de Disponibilidad"
                    :readonly="!editable || !permission.secondary"
                    :outlined="editable && permission.secondary"
                    :disabled="editable && !permission.secondary"
                  ></v-text-field>
                </ValidationProvider>
              </v-col>
              <v-col cols="12" md="6">
                <ValidationProvider
                  v-slot="{ errors }"
                  vid="pension_entity_id"
                  name="Ente Gestor"
                  :rules="
                    affiliate.affiliate_state_id >= 4 &&
                    affiliate.affiliate_state_id <= 6
                      ? 'required'
                      : ''
                  "
                >
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
                    :disabled="editable && !permission.secondary"
                  ></v-select>
                </ValidationProvider>
              </v-col>
              <v-col cols="12" md="6">
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
import RemoveItem from "@/components/shared/RemoveItem";

export default {
  name: "affiliate-profile",
  props: {
    affiliate: {
      type: Object,
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
  components: {
    RemoveItem,
  },
  data() {
    return {
      loading: true,
      dialog: false,
      cel: [null, null],
      cities: [],
      headers: [
        { text: "Ciudad", align: "left", value: "city_address_id" },
        { text: "Zona", align: "left", value: "description" },
        { text: "Activo", align: "left", value: "" },
        //{ text: 'Nro', align: 'left', value: 'number_address' },
        { text: "Acciones", align: "center" },
      ],
      civil_statuses: [
        { name: "Soltero", value: "S" },
        { name: "Casado", value: "C" },
        { name: "Viudo", value: "V" },
        { name: "Divorciado", value: "D" },
      ],
      genders: [
        {
          name: "Femenino",
          value: "F",
        },
        {
          name: "Masculino",
          value: "M",
        },
      ],
      city: [],
      cityTypeSelected: null,
      dates: {
        dueDate: {
          formatted: null,
          picker: false
        },
        birthDate: {
          formatted: null,
          picker: false
        },
        dateDeath: {
          formatted: null,
          picker: false
        }
      },
      bus: new Vue(),
      affiliateState: [],
      category: [],
      degree: [],
      pension_entity: [],
      unit: [],
      visible: false,
    };
  },
  beforeMount() {
    this.getCities();

    this.getCategory();
    this.getDegree();
    this.getPensionEntity();
    this.getAffiliateState();
    this.getUnit();
    this.getCalculateCategory;
  },
  mounted() {},
  watch: {
    "affiliate.due_date": function (date) {
      this.formatDate("dueDate", date);
    },
    "affiliate.date_entry": function (date) {
      this.formatDate("dateEntry", date);
    },
    "affiliate.date_derelict": function (date) {
      this.formatDate("dateDerelict", date);
    },
    "affiliate.date_death": function (date) {
      this.formatDate("dateDeath", date);
    },
   'affiliate.birth_date': function(date) {
      this.formatDate('birthDate', date)
    }
  },
  computed: {
    getCalculateCategory() {
      let years = this.affiliate.service_years;
      let months = this.affiliate.service_months;
      if (
        this.affiliate.service_years == null ||
        this.affiliate.service_months == null
      ) {
        return this.affiliate.category_id;
      } else {
        if (years < 0 || years > 100) {
          return "error";
        } else {
          if (months > 0) {
            years++;
          }
          let categoria = this.category.find((c) => {
            return c.from <= years && c.to >= years;
          });
          if (!!categoria) {
            this.affiliate.category_id = categoria.id;
          }
        }
      }
    },
  },
  methods: {
    close() {
      this.dialog = false;
      this.$emit("closeFab");
    },
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
    getCelular() {
      let part = [];
      if (this.affiliate.cell_phone_number !== null) {
        part = this.affiliate.cell_phone_number.split(",");
        this.cel[0] = part[0];
        this.cel[1] = part[1];
      }
    },

    async getCategory() {
      try {
        this.loading = true;
        let res = await axios.get(`category`);
        this.category = res.data;
      } catch (e) {
        this.dialog = false;
        console.log(e);
      } finally {
        this.loading = false;
      }
    },
    async getAffiliateState() {
      try {
        this.loading = true;
        let res = await axios.get(`affiliate_state`);
        this.affiliateState = res.data;
      } catch (e) {
        this.dialog = false;
        console.log(e);
      } finally {
        this.loading = false;
      }
    },
    async getDegree() {
      try {
        this.loading = true;
        let res = await axios.get(`degree`);
        this.degree = res.data;
      } catch (e) {
        this.dialog = false;
        console.log(e);
      } finally {
        this.loading = false;
      }
    },
    async getPensionEntity() {
      try {
        this.loading = true;
        let res = await axios.get(`pension_entity`);
        this.pension_entity = res.data;
        this.pension_entity.unshift({
          id: null,
          name: "-------"
        })
      } catch (e) {
        this.dialog = false;
        console.log(e);
      } finally {
        this.loading = false;
      }
    },
    Onchange() {
      if (this.affiliate.affiliate_state_id == 4) {
        this.visible = true;
      } else {
        this.visible = false;
      }
    },
    async getUnit() {
      try {
        this.loading = true;
        let res = await axios.get(`unit`);
        this.unit = res.data;
      } catch (e) {
        this.dialog = false;
        console.log(e);
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>

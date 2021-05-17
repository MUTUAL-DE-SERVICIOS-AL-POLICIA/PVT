<template>
  <v-container fluid>
    <ValidationObserver ref="observer">
      <v-form>
        <v-row justify="center">
          <v-col cols="12" md="6">
            <v-col cols="12">
              <v-toolbar-title>DOMICILIO</v-toolbar-title>
            </v-col>
            <v-col cols="12">
              <v-tooltip top v-if="editable && permission.secondary">
                <template v-slot:activator="{ on }">
                  <v-btn
                    fab
                    dark
                    x-small
                    v-on="on"
                    color="info"
                    @click.stop="bus.$emit('openDialog', { edit: true })"
                  >
                    <v-icon>mdi-plus</v-icon>
                  </v-btn>
                </template>
                <span>Añadir Dirección</span>
              </v-tooltip>
            </v-col>
            <v-col cols="12">
              <v-data-table
                :headers="headers"
                :items="addresses"
                hide-default-footer
                class="elevation-1"
                v-if="cities.length > 0"
                :key="refreshAddressTable"
              >
                <template v-slot:item="props">
                  <tr>
                    <td>
                      {{ cities.find((o) => o.id == props.item.city_address_id).name }}
                    </td>
                    <td>{{ props.item.description }}</td>
                    <!--<td>{{ props.item.street }}</td>
                      <td>{{ props.item.number_address }}</td>-->
                    <td>
                      <v-radio-group
                        :value="id_street"
                        @change=" (v) => { $emit('update:id_street', v) } "
                      >
                        <v-radio
                          :value="props.item.id"
                          :disabled="!editable || !permission.secondary"
                        ></v-radio>
                      </v-radio-group>
                    </td>
                    <td v-show="editable && permission.secondary">
                      <v-btn
                        text
                        icon
                        color="warning"
                        @click.stop="
                          bus.$emit('openDialog', {
                            ...props.item,
                            ...{ edit: true },
                          })
                        "
                      >
                        <v-icon>mdi-pencil</v-icon>
                      </v-btn>
                      <v-btn :disabled="props.item.id===id_street" text icon color="error" @click.stop="bus.$emit('openRemoveDialog', `address/${props.item.id}`), currentItem=props.item">
                          <v-icon>mdi-delete</v-icon>
                        </v-btn>
                    </td>
                    <td v-show="!editable">
                      <v-btn
                        v-if="props.item.latitude && props.item.longitude"
                        text
                        icon
                        color="info"
                        @click.stop="
                          bus.$emit('openDialog', {
                            ...props.item,
                            ...{ edit: false },
                          })
                        "
                      >
                        <v-icon>mdi-google-maps</v-icon>
                      </v-btn>
                    </td>
                  </tr>
                </template>
              </v-data-table>
            </v-col>
          </v-col>
          <v-col cols="12" md="6">
            <v-container class="py-0">
              <v-row>
                <v-col cols="12">
                  <v-toolbar-title>TELÉFONOS</v-toolbar-title>
                </v-col>
                <v-col cols="12" md="4">
                  <ValidationProvider
                    v-slot="{ errors }"
                    name="celular1"
                    rules="min:1|max:12"
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
                    ></v-text-field>
                  </ValidationProvider>
                </v-col>
                <v-col cols="12" md="4">
                  <ValidationProvider
                    v-slot="{ errors }"
                    name="celular2"
                    rules="min:1|max:12"
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
                    ></v-text-field>
                  </ValidationProvider>
                </v-col>

                <v-col cols="12" md="4">
                  <ValidationProvider
                    v-slot="{ errors }"
                    name="telefono"
                    rules="min:1|max:12"
                  >
                    <v-text-field
                      :error-messages="errors"
                      dense
                      v-model="affiliate.phone_number"
                      label="Fijo"
                      :readonly="!editable || !permission.secondary"
                      :outlined="editable && permission.secondary"
                      :disabled="editable && !permission.secondary"
                    ></v-text-field>
                  </ValidationProvider>
                </v-col>

                <v-col cols="12">
                  <v-toolbar-title>DATOS FINANCIEROS </v-toolbar-title>
                </v-col>
                <v-col cols="12" md="6">
                  <ValidationProvider
                    v-slot="{ errors }"
                    vid="financial_entity_id"
                    name="Entidad Financiera"
                    rules=""
                  >
                    <v-select
                      :error-messages="errors"
                      dense
                      :loading="loading"
                      :items="entity"
                      item-text="name"
                      item-value="id"
                      label="Entidad Financiera"
                      v-model="affiliate.financial_entity_id"
                      :readonly="!editable || !permission.secondary"
                      :outlined="editable && permission.secondary"
                      :disabled="editable && !permission.secondary"
                    ></v-select>
                  </ValidationProvider>
                </v-col>
                <v-col cols="12" md="6">
                  <ValidationProvider
                    v-slot="{ errors }"
                    name="Numero de Cuenta"
                    rules=""
                  >
                    <v-text-field
                      :error-messages="errors"
                      dense
                      v-model="affiliate.account_number"
                      label="Número de Cuenta SIGEP activa"
                      :readonly="!editable || !permission.secondary"
                      :outlined="editable && permission.secondary"
                      :disabled="editable && !permission.secondary"
                    ></v-text-field>
                  </ValidationProvider>
                </v-col>
                <!--<v-col cols="12" md="6">
                  <ValidationProvider
                    v-slot="{ errors }"
                    name="Cuenta de Segip"
                    rules=""
                  >
                    <v-select
                      :error-messages="errors"
                      dense
                      :items="sigep_status"
                      item-text="name"
                      item-value="id"
                      :loading="loading"
                      label="Estado del Sigep"
                      v-model="affiliate.sigep_status"
                      :readonly="!editable || !permission.secondary"
                      :outlined="editable && permission.secondary"
                      :disabled="editable && !permission.secondary"
                    ></v-select>
                  </ValidationProvider>
                </v-col>-->
              </v-row>
            </v-container>
          </v-col>
        </v-row>
      </v-form>
    </ValidationObserver>
    <AddStreet :bus="bus" :cities="cities" />
    <RemoveItem :bus="bus" />
  </v-container>
</template>
<script>
import RemoveItem from "@/components/shared/RemoveItem";
import AddStreet from "@/components/affiliate/AddStreet";

export default {
  name: "affiliate-additionalInformation",
  props: {
    affiliate: {
      type: Object,
      required: true,
    },
    addresses: {
      type: Array,
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
    id_street: {
      type: Number,
      required: true,
      default: 0,
    },
  },
  components: {
    AddStreet,
    RemoveItem,
  },
  data() {
    return {
      loading: true,
      dialog: false,
      cel: [null, null],
      cities: [],
      entity: [],
      sigep_status: [
        { name: "ACTIVO", value: "ACTIVO" },
        { name: "ELABORADO", value: "ELABORADO" },
        { name: "SIN REGISTRO", value: "SIN REGISTRO" },
        { name: "VALIDADO", value: "VALIDADO" },
      ],
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

      city: [],
      cityTypeSelected: null,
      bus: new Vue(),
      //util para refrescar el componente data-table de addresses
      refreshAddressTable: 0,
      //guarda el ultimo registro editado o eliminado
      currentItem: null,
    };
  },
  beforeMount() {
    this.getCities();
    this.getEntity();
  },
  mounted() {
    if (this.affiliate.id) {
      this.getCelular();
    }
    this.bus.$on("removed", () => {
      console.log('delete', this.currentItem)
      let newAddresses = this.addresses.filter(item => item.id !== this.currentItem.id)
      this.$emit('update:addresses', newAddresses)
      this.refreshAddressTable++
    });
    this.bus.$on("saveAddress", (address) => {
      if (address.id) {
        let addressesAux = this.addresses
        let index = addressesAux.findIndex((o) => o.id == address.id);
        if (index == -1) {
          addressesAux.unshift(address);
        } else {
          addressesAux[index] = address;
        }
        this.$emit('update:addresses', addressesAux)
        this.refreshAddressTable++
      }
    });
  },
  methods: {
    close() {
      this.dialog = false;
      this.$emit("closeFab");
    },
    async getEntity() {
      try {
        this.loading = true;
        let res = await axios.get(`financial_entity`);
        this.entity = res.data;
      } catch (e) {
        this.dialog = false;
        console.log(e);
      } finally {
        this.loading = false;
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
        this.affiliate.cell_phone_number = null;
      } else if (count == 1) {
        this.affiliate.cell_phone_number = this.cel[val];
      } else {
        this.affiliate.cell_phone_number = this.cel.join(",");
      }
    },
  },
};
</script>

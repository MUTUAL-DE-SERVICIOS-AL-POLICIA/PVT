<template>
  <v-container fluid>
    <v-row justify="center">
      <v-col cols="12" md="8">
        <v-container class="ma-0 pa-0">
          <v-card>
            <v-row class="ma-0 pa-0">
              <v-col cols="12" md="6">
                <v-toolbar-title>DOMICILIOS</v-toolbar-title>
              </v-col>
              <v-col cols="12" md="3">
                <v-tooltip top v-if="editable && permission.secondary">
                  <template v-slot:activator="{ on }">
                    <v-btn
                      fab
                      dark
                      x-small
                      v-on="on"
                      color="info"
                      @click.stop="bus.$emit('openDialog', { edit:true })"
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
                >
                  <template v-slot:item="props">
                    <tr>
                      <td>{{ cities.find(o => o.id == props.item.city_address_id).name }}</td>
                      <td>{{ props.item.description }}</td>
                      <!--*<td>{{ props.item.street }}</td>
                      <td>{{ props.item.number_address }}</td>-->
                      <td v-show="editable && permission.secondary">
                        <v-btn
                          text
                          icon
                          color="warning"
                          @click.stop="bus.$emit('openDialog', {...props.item, ...{edit:true}})"
                        >
                          <v-icon>mdi-pencil</v-icon>
                        </v-btn>
                        <v-btn
                          text
                          icon
                          color="error"
                          @click.stop="bus.$emit('openRemoveDialog', `address/${props.item.id}`)"
                        >
                          <v-icon>mdi-delete</v-icon>
                        </v-btn>
                      </td>
                    </tr>
                  </template>
                </v-data-table>
              </v-col>
            </v-row>
          </v-card>
        </v-container>
      </v-col>
      <v-col cols="12" md="3">
        <v-container class="ma-0 pa-0">
          <ValidationObserver ref="observer">
            <v-form>
              <v-card>
                <v-col cols="12" class="py-2">
                  <v-toolbar-title>TELÉFONOS</v-toolbar-title>
                </v-col>
                <v-col cols="12" class="py-0">
                  <ValidationProvider
                    v-slot="{ errors }"
                    vid="celular1"
                    name="celular1"
                    rules="min:11|max:11|required"
                  >
                    <v-text-field
                      :error-messages="errors"
                      dense
                      v-model="getCelular[0]"
                      label="Celular 1"
                      @change="updateCelular()"
                      :readonly="!editable || !permission.secondary"
                      :outlined="editable && permission.secondary"
                      :disabled="editable && !permission.secondary"
                      v-mask="'(###)-#####'"
                    ></v-text-field>
                  </ValidationProvider>
                </v-col>
                <v-col cols="12" class="py-0">
                  <ValidationProvider
                    v-slot="{ errors }"
                    vid="celular"
                    name="celular2"
                    rules="min:11|max:11"
                  >
                    <v-text-field
                      class="text-right"
                      :error-messages="errors"
                      dense
                      v-model="getCelular[1]"
                      label="Celular 2"
                      @change="updateCelular()"
                      :readonly="!editable || !permission.secondary"
                      :outlined="editable && permission.secondary"
                      :disabled="editable && !permission.secondary"
                      v-mask="'(###)-#####'"
                    ></v-text-field>
                  </ValidationProvider>
                </v-col>
                <v-col cols="12" class="py-0">
                  <ValidationProvider
                    v-slot="{ errors }"
                    vid="teléfono"
                    name="teléfono"
                    rules="min:11|max:11"
                  >
                    <v-text-field
                      :error-messages="errors"
                      dense
                      v-model="affiliate.phone_number"
                      label="Fijo"
                      :readonly="!editable || !permission.secondary"
                      :outlined="editable && permission.secondary"
                      :disabled="editable && !permission.secondary"
                      v-mask="'(#) ###-###'"
                    ></v-text-field>
                  </ValidationProvider>
                </v-col>
              </v-card>
            </v-form>
          </ValidationObserver>
        </v-container>
      </v-col>
      <v-col cols="12" md="1" class="ma-0 pa-0">
        <v-tooltip top>
          <template v-slot:activator="{ on }">
            <v-btn
              fab
              dark
              x-small
              :color="'error'"
              bottom
              right
              v-on="on"
              style="margin-right: 45px;"
              @click.stop="resetForm()"
              v-show="editable"
            >
              <v-icon>mdi-close</v-icon>
            </v-btn>
          </template>
          <div>
            <span>Cancelar</span>
          </div>
        </v-tooltip>
        <v-tooltip top>
          <template v-slot:activator="{ on }">
            <v-btn
              fab
              dark
              x-small
              :color="editable ? 'danger' : 'success'"
              bottom
              right
              v-on="on"
              style="margin-right: -9px; margin-top:10px;"
              @click.stop="saveAffiliate()"
            >
              <v-icon v-if="editable">mdi-check</v-icon>
              <v-icon v-else>mdi-pencil</v-icon>
            </v-btn>
          </template>
          <div>
            <span v-if="editable">Guardar</span>
            <span v-else>Editar</span>
          </div>
        </v-tooltip>
      </v-col>
    </v-row>
    <AddStreet :bus="bus" :cities="cities" />
    <RemoveItem :bus="bus" />
  </v-container>
</template>
<script>
import RemoveItem from "@/components/shared/RemoveItem"
import AddStreet from "@/components/affiliate/AddStreet"

export default {
  name: "affiliate-personalInformation",
  props: {
    affiliate: {
      type: Object,
      required: true
    },
    addresses: {
      type: Array,
      required: true
    }
  },
  components: {
    AddStreet,
    RemoveItem
  },
  data: () => ({
    loading: true,
    dialog: false,
    cell: [null, null],
    editable: false,
    cities: [],
    headers: [
      { text: "Ciudad", align: "left", value: "city_address_id" },
      { text: "Zona", align: "left", value: "description" },
      //{ text: "Calle", align: "left", value: "street" },
      //{ text: "Nro", align: "left", value: "number_address" },
      { text: "Acciones", align: "center" }
    ],
    city: [],
    cityTypeSelected: null,
    bus: new Vue()
  }),
  computed: {
    //Metodo para obtener Permisos por rol
    permissionSimpleSelected () {
      return this.$store.getters.permissionSimpleSelected
    },
    permission() {
      return {
        primary: this.primaryPermission,
        secondary: this.secondaryPermission
      }
    },
    secondaryPermission() {
      if (this.affiliate.id) {
        return this.permissionSimpleSelected.includes(
          "update-affiliate-secondary"
        )
      } else {
        return this.permissionSimpleSelected.includes("create-affiliate")
      }
    },
    primaryPermission() {
      if (this.affiliate.id) {
        return this.permissionSimpleSelected.includes(
          "update-affiliate-primary"
        )
      } else {
        return this.permissionSimpleSelected.includes("create-affiliate")
      }
    },
    getCelular() {
      if (this.affiliate.cell_phone_number == null) {
        return 0
      } else {
        let array = this.affiliate.cell_phone_number.split(",")
        console.log("entro")
        return array
      }
    }
  },
  beforeMount() {
    this.getCities()
    //this.updateCelular()
  },
  mounted() {
    this.bus.$on("saveAddress", address => {
      if (address.id) {
        let index = this.addresses.findIndex(o => o.id == address.id)
        if (index == -1) {
          this.addresses.unshift(address)
        } else {
          this.addresses[index] = address
        }
      }
    })
  },
  methods: {
    resetForm() {
      this.editable = false
    },
    close() {
      this.dialog = false
      this.$emit("closeFab")
    },
    async getCities() {
      try {
        this.loading = true
        let res = await axios.get(`city`)
        this.cities = res.data
      } catch (e) {
        this.dialog = false
        console.log(e)
      } finally {
        this.loading = false
      }
    },
    updateCelular() {
      let count = 0
      let val = 0
      this.cell[0] = this.getCelular[0].trim()
      this.cell[1] = this.getCelular[1].trim()
      if (this.cell[0]) {
        if (this.cell[0] !== "") {
          this.cell[0] = this.cell[0]
          count++
          val = 0
        }
      }
      if (this.cell[1]) {
        if (this.cell[1] !== "") {
          this.cell[1] = this.cell[1]
          count++
          val = 1
        }
      }
      if (count == 0) {
        this.affiliate.cell_phone_number = null
      } else if (count == 1) {
        this.affiliate.cell_phone_number = this.cell[val]
      } else {
        this.affiliate.cell_phone_number = this.cell.join(",")
      }
    },
    async saveAffiliate() {
      try {
        if (!this.editable) {
          this.editable = true
          console.log("entro al grabar por verdadero :)")
        } else {
          console.log("entro al grabar por falso :)")
          // Edit affiliate
          //await axios.patch(`affiliate/${this.affiliate.id}`, this.affiliate)
            await axios.patch(`affiliate/${this.affiliate.id}`, {
            phone_number: this.affiliate.phone_number,
            cell_phone_number: this.affiliate.cell_phone_number,
            city_identity_card_id: this.affiliate.city_identity_card_id
          })
            await axios.patch(`affiliate/${this.affiliate.id}/address`, {
            addresses: this.addresses.map(o => o.id)
          })
          this.toastr.success("Registro guardado correctamente")
          this.editable = false
        }
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    }
  }
}
</script>

<template>
  <v-dialog v-model="dialog" fullscreen hide-overlay transition="dialog-bottom-transition">
    <v-card>
      <v-container>
          <v-card-title class="px-0 mx-0" v-show="address.edit">Añadir Dirección</v-card-title>
          <v-card-title class="px-0 mx-0" v-show="!address.edit">Dirección</v-card-title>

        <ValidationObserver ref="observer">            
            <v-row>
              <v-col cols="12" md="1" v-show="address.edit" class="pb-0 mb-0">
                <v-select
                  dense
                  :items="countryCities"
                  item-text="name"
                  item-value="id"
                  label="Departamento"
                  v-model="address.city_address_id"
                ></v-select>
              </v-col>
              <v-col cols="12" md="3" v-show="address.edit" class="pb-0 mb-0">
                <ValidationProvider v-slot="{ errors }" vid="zone" name="Dirección" rules="required">
                  <v-text-field
                    :error-messages="errors"
                    dense
                    v-model="address.zone"
                    label="Zona/Barrio/Urbanización"
                    class="purple-input"
                  ></v-text-field>
                </ValidationProvider>
              </v-col>
              <v-col cols="12" md="2" v-show="address.edit" class="pb-0 mb-0">
                <ValidationProvider v-slot="{ errors }" vid="street" name="Dirección" rules="required">
                  <v-text-field
                    :error-messages="errors"
                    dense
                    v-model="address.street"
                    label="Calle/Avenida/Camino/Carretera"
                    class="purple-input"
                  ></v-text-field>
                </ValidationProvider>
              </v-col>
              <v-col cols="12" md="3" v-show="address.edit" class="pb-0 mb-0">
                <ValidationProvider v-slot="{ errors }" vid="housing_unit" name="Dirección" rules="">
                  <v-text-field
                    :error-messages="errors"
                    dense
                    v-model="address.housing_unit"
                    label="Condominio/Edificio/Torre (Bloque, Piso, N° dpto)"
                    class="purple-input"
                  ></v-text-field>
                </ValidationProvider>
              </v-col>
              <v-col cols="12" md="1" v-show="address.edit" class="pb-0 mb-0">
                <ValidationProvider v-slot="{ errors }" vid="number_address" name="Dirección" rules="required">
                  <v-text-field
                    :error-messages="errors"
                    dense
                    v-model="address.number_address"
                    label="Nro Domicilio"
                    class="purple-input"
                  ></v-text-field>
                </ValidationProvider>
              </v-col>
              <v-col cols="12" md="2" v-show="address.edit" class="pb-0 mb-0">
                <ValidationProvider v-slot="{ errors }" vid="description" name="Dirección" rules="">
                  <v-text-field
                    :error-messages="errors"
                    dense
                    v-model="address.description"
                    label="Referencia"
                    class="purple-input"
                  ></v-text-field>
                </ValidationProvider>
              </v-col>
              <v-card-title class="ma-0 pl-2">Geolocalización</v-card-title>
              <LMap
                  v-if="dialog"
                  :address.sync="address"
                />
            </v-row>
        </ValidationObserver>  
        <v-divider></v-divider>

        <v-card-actions v-show="address.edit" class="pt-0 mt-0">
          <v-spacer></v-spacer>
          <v-btn color="error" text @click.stop="close()">Cerrar</v-btn>
          <v-btn color="success" text :loading="loading" @click.stop="adicionar()">Guardar</v-btn>
        </v-card-actions>

      </v-container>
    </v-card>
  </v-dialog>
</template>

<script>
import LMap from '@/components/affiliate/LMap'

export default {
  name: "affiliate-addresses",
  props: {
    bus: {
      type: Object,
      required: true
    },
    cities: {
      type: Array,
      required: true
    },
    affiliate: {
      type: Object,
      required: true
    }
  },
  components: {
    LMap
  },
  data: () => ({
    dialog: false,
    loading: false,
    address: {
      city_address_id: '',
      description: null,
      id: null,
      latitude: null,
      longitude: null,
      image: null
    }
  }),
  mounted() {
    this.bus.$on('openDialog', (address) => {
      this.address = { ...address, edit: true } // marca si está en edición
      this.dialog = true
    })
  },
  computed: {
    countryCities() {
      return this.cities.filter(o => o.name.toUpperCase() != 'NATURALIZADO' && o.name.toUpperCase() != 'NINGUNO')
    }
  },
  methods: {
    async adicionar() {
      this.loading = true
      if (await this.$refs.observer.validate()) {
        this.saveAddress()
        this.bus.$emit('saveAddress', this.address)
        this.loading = false
        this.close()
      }
      this.loading = false
    },
    close() {
      this.dialog = false
      this.address = {}
    },

    async saveAddress() {

      let image = this.address.image
      try {
        let res = {}
        if (this.address.id) {
          res = await axios.patch(`address/${this.address.id}`, this.address)
          this.toastr.success('Domicilio Modificado')
          this.bus.$emit('saveAddress', res.data)
        } else {
          res = await axios.post(`address`, this.address)
          this.toastr.success('Domicilio Adicionado')
          this.bus.$emit('saveAddress', res.data)
        }

        let res2 = await axios.post(`/affiliates/${this.affiliate.id}/addresses/${res.data.id}/print`, {
          imagen: image
        }); // el backend retorna PDF en base64

        printJS({
          printable: res2.data.content,  // base64
          type: res2.data.type,          // debe ser 'pdf'
          file_name: res2.data.file_name,
          base64: true
        });

      } catch (e) {
        this.$refs.observer.setErrors(e)
        this.loading = false
      }
    },
  }
}
</script>
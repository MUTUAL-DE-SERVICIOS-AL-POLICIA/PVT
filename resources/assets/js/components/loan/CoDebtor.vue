<template>
  <div class="ma-3 pa-0">
    <v-data-table
      dense
      :headers="headers"
      :items="personal_codebtor"
      sort-by="identity_card"
      class="elevation-1 ma-0 pa-3"
      hide-default-footer
    >
      <template v-slot:top>
        <v-toolbar flat color="white">
          <v-toolbar-title>DATOS DEL CODEUDOR NO AFILIADO</v-toolbar-title>
          <v-divider class="mx-4" inset vertical></v-divider>
          <v-spacer></v-spacer>
          <v-dialog v-model="dialog" max-width="600px">
            <template v-slot:activator="{ on}">
              <v-btn fab dark x-small v-on="on" color="info" @click="checkLimit()">
                <v-icon>mdi-plus</v-icon>
              </v-btn>
            </template>
            <v-card>
              <v-card-title>
                <span class="headline">{{ formTitle }}</span>
              </v-card-title>
              <ValidationObserver ref="observerCodebtor">
                <v-form>
                  <v-card-text>
                    <v-container>
                      <v-row>
                        <v-col cols="12" sm="6" md="3">
                          <ValidationProvider
                            v-slot="{ errors }"
                            name="Primer Nombre"
                            rules="required|alpha_spaces|min:3|max:20"
                          >
                            <v-text-field
                              :error-messages="errors"
                              dense
                              v-model="editedItem.first_name"
                              label="Primer Nombre"
                            ></v-text-field>
                          </ValidationProvider>
                        </v-col>
                        <v-col cols="12" sm="6" md="3">
                          <ValidationProvider
                            v-slot="{ errors }"
                            name="Segundo nombre"
                            rules="alpha_spaces|min:3|max:20"
                          >
                            <v-text-field
                              :error-messages="errors"
                              dense
                              v-model="editedItem.second_name"
                              label="Segundo nombre"
                            ></v-text-field>
                          </ValidationProvider>
                        </v-col>
                        <v-col cols="12" sm="6" md="3">
                          <ValidationProvider
                            v-slot="{ errors }"
                            name="Primer Apellido"
                            rules="alpha_spaces|min:3|max:20"
                          >
                            <v-text-field
                              :error-messages="errors"
                              dense
                              v-model="editedItem.last_name"
                              label="Primer Apellido"
                            ></v-text-field>
                          </ValidationProvider>
                        </v-col>
                        <v-col cols="12" sm="6" md="3">
                          <ValidationProvider
                            v-slot="{ errors }"
                            name="Segundo Apellido"
                            :rules="(editedItem.last_name == null || editedItem.last_name == '')? 'required' : ''+'alpha_spaces|min:3|max:20'"
                          >
                            <v-text-field
                              :error-messages="errors"
                              dense
                              v-model="editedItem.mothers_last_name"
                              label="Segundo Apellido"
                            ></v-text-field>
                          </ValidationProvider>
                        </v-col>
                        <v-col cols="12" sm="6" md="3">
                          <ValidationProvider v-slot="{ errors }" name="CI" rules="required|min:3">
                            <v-text-field
                              :error-messages="errors"
                              dense
                              v-model="editedItem.identity_card"
                              label="CI"
                            ></v-text-field>
                          </ValidationProvider>
                        </v-col>
                        <v-col cols="12" sm="6" md="3">
                          <ValidationProvider
                            v-slot="{ errors }"
                            name="Ciudad de Expedición"
                            rules="required"
                          >
                            <v-select
                              :error-messages="errors"
                              v-model="editedItem.city_identity_card_id"
                              dense
                              :items="cities"
                              item-text="name"
                              item-value="id"
                              label="Ciudad de Expedición"
                            ></v-select>
                          </ValidationProvider>
                        </v-col>
                        <v-col cols="12" sm="6" md="3">
                          <ValidationProvider v-slot="{ errors }" name="Género" rules="required">
                            <v-select
                              :error-messages="errors"
                              dense
                              :items="genders"
                              item-text="name"
                              item-value="value"
                              v-model="editedItem.gender"
                              label="Género"
                            ></v-select>
                          </ValidationProvider>
                        </v-col>
                        <v-col cols="12" sm="6" md="3">
                          <ValidationProvider
                            v-slot="{ errors }"
                            name="Estado civil"
                            rules="required"
                          >
                            <v-select
                              :error-messages="errors"
                              dense
                              :items="civil_statuses"
                              item-text="name"
                              item-value="value"
                              label="Estado civil"
                              v-model="editedItem.civil_status"
                            ></v-select>
                          </ValidationProvider>
                        </v-col>
                        <v-col cols="12" sm="6" md="3">
                          <ValidationProvider
                            v-slot="{ errors }"
                            name="Ciudad de Nacimiento"
                            rules="required"
                          >
                            <v-select
                              :error-messages="errors"
                              v-model="editedItem.city_birth_id"
                              dense
                              :items="cities"
                              item-text="name"
                              item-value="id"
                              label="Ciudad de Nacimiento"
                            ></v-select>
                          </ValidationProvider>
                        </v-col>

                        <v-col cols="12" sm="6" md="3">
                          <ValidationProvider
                            v-slot="{ errors }"
                            name="Teléfono"
                            rules="integer|min:7|max:12"
                          >
                            <v-text-field
                              :error-messages="errors"
                              dense
                              v-model="editedItem.phone_number"
                              label="Teléfono"
                            ></v-text-field>
                          </ValidationProvider>
                        </v-col>
                        <v-col cols="12" sm="6" md="3">
                          <ValidationProvider
                            v-slot="{ errors }"
                            name="Celular"
                            rules="integer|min:7|max:12"
                          >
                            <v-text-field
                              :error-messages="errors"
                              dense
                              v-model="editedItem.cell_phone_number"
                              label="Celular"
                            ></v-text-field>
                          </ValidationProvider>
                        </v-col>
                        <v-col cols="12" sm="6" md="12">
                          <ValidationProvider v-slot="{ errors }" name="Dirección" rules="required">
                            <v-text-field
                              :error-messages="errors"
                              dense
                              v-model="editedItem.address"
                              label="Dirección"
                            ></v-text-field>
                          </ValidationProvider>
                        </v-col>
                      </v-row>
                    </v-container>
                  </v-card-text>
                </v-form>
              </ValidationObserver>

              <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="blue darken-1" text @click="close">Cancelar</v-btn>
                <v-btn color="blue darken-1" text @click="save">Guardar</v-btn>
              </v-card-actions>
            </v-card>
          </v-dialog>
        </v-toolbar>
      </template>
      <template v-slot:[`item.actions`]="{ item }">
        <v-icon small class="mr-2" color="success" @click="editItem(item)">mdi-pencil</v-icon>
        <v-icon small color="error" @click="deleteItem(item)">mdi-delete</v-icon>
      </template>
      <template v-slot:no-data></template>
    </v-data-table>
  </div>
</template>

<script>
export default {
  name: "loan-codebtor",
  props: {
    personal_codebtor: {
      type: Array,
      required: true
    },
    modalidad_max_cosigner: {
      type: Number,
      required: true,
      default:0
    }
  },
  data: () => ({
    dialog: false,
    headers: [
      {
        text: "CI",
        align: "start",
        class: ["normal", "white--text"],
        sortable: false,
        value: "identity_card"
      },
      {
        text: "Primer Nombre",
        class: ["normal", "white--text"],
        value: "first_name"
      },
      {
        text: "Segundo Nombre",
        class: ["normal", "white--text"],
        value: "second_name"
      },
      {
        text: "Primer Apellido",
        class: ["normal", "white--text"],
        value: "last_name"
      },
      {
        text: "Segundo Apellido",
        class: ["normal", "white--text"],
        value: "mothers_last_name"
      },
      {
        text: "Teléfono",
        class: ["normal", "white--text"],
        value: "phone_number"
      },
      {
        text: "Celular",
        class: ["normal", "white--text"],
        value: "cell_phone_number"
      },
      { text: "Dirección", class: ["normal", "white--text"], value: "address" },
      {
        text: "Actions",
        class: ["normal", "white--text"],
        value: "actions",
        sortable: false
      }
    ],

    editedIndex: -1,
    editedItem: {
      identity_card: null,
      city_identity_card_id: null,
      first_name: null,
      second_name: null,
      last_name: null,
      mothers_last_name: null,
      full_name:null,
      phone_number: null,
      cell_phone_number: null,
      address: null,
      civil_status: null,
      gender: null,
      city_birth_id: null
    },
    defaultItem: {
      identity_card: null,
      city_identity_card_id: null,
      first_name: null,
      second_name: null,
      last_name: null,
      mothers_last_name: null,
      full_name:null,
      phone_number: null,
      cell_phone_number: null,
      address: null,
      civil_status: null,
      gender: null,
      city_birth_id: null
    },
    cities: [],
    //personal_codebtor: [],
    civil_statuses: [
      { name: "Soltero", value: "S" },
      { name: "Casado", value: "C" },
      { name: "Viudo", value: "V" },
      { name: "Divorciado", value: "D" }
    ],
    genders: [
      {
        name: "Femenino",
        value: "F"
      },
      {
        name: "Masculino",
        value: "M"
      }
    ]
  }),

  computed: {
    formTitle() {
      return this.editedIndex === -1 ? "Nuevo" : "Editar";
    }
  },

  watch: {
    dialog(val) {
      console.log("entro aqui" + val);
      val || this.close();
    }
  },
  mounted() {
    this.getCities();
  },
  methods: {
    editItem(item) {
      this.editedIndex = this.personal_codebtor.indexOf(item);
      this.editedItem = Object.assign({}, item);
      this.dialog = true;
    },

    deleteItem(item) {
      const index = this.personal_codebtor.indexOf(item);
      this.personal_codebtor.splice(index, 1);
      this.toastr.success("El registro fue removido");
    },

    close() {
      this.dialog = false;
      this.$nextTick(() => {
        this.editedItem = Object.assign({}, this.defaultItem);
        this.editedIndex = -1;
      });
    },

    async save() {
      try {
        if (await this.$refs.observerCodebtor.validate()) {
          if (this.editedIndex > -1) {
            Object.assign(
              this.personal_codebtor[this.editedIndex],
              this.editedItem
            );
            console.log(this.editedIndex); //obtener el indice
            console.log(this.editedItem); //obtener el objeto
          } else {
            this.personal_codebtor.push(this.editedItem);
            console.log("nuevo editedIndex " + this.editedItem);
            console.log(this.editedItem);
          }
          this.close();
        }
      } catch (e) {
        this.$refs.observerCodebtor.setErrors(e);
      }
    },
    async getCities() {
      try {
        //this.loading = true;
        let res = await axios.get(`city`);
        this.cities = res.data;
      } catch (e) {
        this.dialog = false;
        console.log(e);
      } finally {
        //this.loading = false;
      }
    },
    checkLimit() {
      if (this.personal_codebtor.length < this.modalidad_max_cosigner) {
        console.log("no hacer nada");
      } else {
        this.dialog = false;
        this.toastr.error(
          "El número máximo de codeudores es: " + this.modalidad_max_cosigner
        );
      }
    }
  }
};
</script>
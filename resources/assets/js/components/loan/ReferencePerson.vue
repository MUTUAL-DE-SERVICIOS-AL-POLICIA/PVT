<template>
  <div class="ma-3 pa-0">
    <v-data-table
      dense
      :headers="headers"
      :items="reference_person"
      class="elevation-1 ma-0 pa-3"
      hide-default-footer
    >
      <template v-slot:top>
        <v-toolbar flat color="white">
          <v-toolbar-title>PERSONA DE REFERENCIA</v-toolbar-title>
          <v-divider class="mx-4" inset vertical></v-divider>
          <v-spacer></v-spacer>
          <v-dialog v-model="dialog" max-width="600px">
            <template v-slot:activator="{ on}">
              <v-btn fab dark x-small v-on="on" color="info">
                <v-icon>mdi-plus</v-icon>
              </v-btn>
            </template>
            <v-card>
              <v-card-title>
                <span class="headline">{{ formTitle }}</span>
              </v-card-title>
              <ValidationObserver ref="observerReferencePerson">
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
                          <ValidationProvider
                            v-slot="{ errors }"
                            name="Teléfono"
                            rules="min:11|max:11"
                          >
                            <v-text-field
                              :error-messages="errors"
                              dense
                              v-model="editedItem.phone_number"
                              label="Teléfono"
                              v-mask="'(#) ###-###'"
                            ></v-text-field>
                          </ValidationProvider>
                        </v-col>
                        <v-col cols="12" sm="6" md="3">
                          <ValidationProvider
                            v-slot="{ errors }"
                            name="Celular"
                            :rules="(editedItem.phone_number == null || editedItem.phone_number == '')? 'required' : ''+'min:11|max:11'"
                          >
                            <v-text-field
                              :error-messages="errors"
                              dense
                              v-model="editedItem.cell_phone_number"
                              label="Celular"
                              v-mask="'(###)-#####'"
                            ></v-text-field>
                          </ValidationProvider>
                        </v-col>
                        <v-col cols="12" sm="6" md="3">
                          <ValidationProvider
                            v-slot="{ errors}"
                            name="Parentesco"
                            rules="required"
                          >
                            <v-select
                              :error-messages="errors"
                              dense
                              :items="kinships"
                              item-text="name"
                              item-value="id"
                              v-model="editedItem.kinship_id"
                              label="Parentesco"
                            >
                            </v-select>
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
  name: "loan-reference-person",
  props: {
    reference_person: {
      type: Array,
      required: true
    },
  },
  data: () => ({
    dialog: false,
    headers: [
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
      {
        text: "Partentesco",
        class: ["normal", "white--text"],
        value: "kinship_id"
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
      first_name: null,
      second_name: null,
      last_name: null,
      mothers_last_name: null,
      phone_number: null,
      cell_phone_number: null,
      kinship_id: null,
      address: null
    },
    defaultItem: {
      first_name: null,
      second_name: null,
      last_name: null,
      mothers_last_name: null,
      phone_number: null,
      cell_phone_number: null,
      kinship_id: null,
      address: null
    },
    kinships: []

  }),

  computed: {
    formTitle() {
      return this.editedIndex === -1 ? "Nuevo" : "Editar";
    }
  },

  watch: {
    dialog(val) {
      val || this.close();
    }
  },
  mounted() {
    this.getKinship()
  },
  methods: {
    editItem(item) {
      this.editedIndex = this.reference_person.indexOf(item);
      this.editedItem = Object.assign({}, item);
      this.dialog = true;
    },

    deleteItem(item) {
      const index = this.reference_person.indexOf(item);
      this.reference_person.splice(index, 1);
      this.toastr.error("El registro fue removido");
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
        if (await this.$refs.observerReferencePerson.validate()) {
          if (this.editedIndex > -1) {
            Object.assign(
              this.reference_person[this.editedIndex],
              this.editedItem
            );
          } else {
            this.reference_person.push(this.editedItem);
          }
          this.close();
        }
      } catch (e) {
        this.$refs.observerReferencePerson.setErrors(e);
      }
    },
    async getKinship(){
      try {
        let res = await axios.get('kinship')
        this.kinships = res.data
      } catch (e) {
        console.log(e)
      }
    }
  }
};
</script>
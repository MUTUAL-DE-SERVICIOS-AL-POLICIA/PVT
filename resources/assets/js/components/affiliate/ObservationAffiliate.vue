<template>
  <v-container fluid>
    <v-tooltip top>
      <template v-slot:activator="{ on }">
        <v-btn
          fab
          x-small
          color="success"
          @click.stop="dialog = true"
          v-on="on"
          top
          right
          absolute
          v-if="
            !trashed &&
            permissionSimpleSelected.includes('create-observation-affiliate')
          "
          style="margin-right: 80px; margin-top: 20px"
        >
          <v-icon>mdi-plus</v-icon>
        </v-btn>
      </template>
      <div>
        <span>Crear observación</span>
      </div>
    </v-tooltip>
    <v-tooltip top>
      <template v-slot:activator="{ on }">
        <v-btn
          fab
          @click="trashed = !trashed"
          :color="!trashed ? 'error' : 'primary'"
          v-on="on"
          x-small
          top
          right
          absolute
          style="margin-right: 40px; margin-top: 20px"
        >
          <v-icon>{{
            !trashed ? "mdi-file-cancel" : "mdi-file-multiple"
          }}</v-icon>
        </v-btn>
      </template>
      <span v-if="!trashed">Ver registros anulados de observaciones</span>
      <span v-else>Ver registros de observaciones</span>
    </v-tooltip>
    <v-data-table
      :headers="headers"
      :items="observations"
      :options.sync="options"
      :loading="loading_observations"
      :item-class="itemRowBackground"
    >
      <template v-slot:[`item.id`]="{ index }">
        {{ (options.page - 1) * options.itemsPerPage + index + 1 }}
      </template>
      <template v-slot:[`item.date`]="{ item }">
        {{ item.date | datetime }}
      </template>
      <template v-slot:top> </template>
      <template v-slot:[`item.actions`]="{ item }">
        <v-icon
          small
          class="mr-2"
          color="info"
          v-if="
            !trashed &&
            item.can_update_or_delete &&
            permissionSimpleSelected.includes('update-observation-affiliate')
          "
          @click="editItem(item)"
        >
          mdi-pencil
        </v-icon>
        <v-icon
          small
          color="error"
          v-if="
            !trashed &&
            item.can_update_or_delete &&
            permissionSimpleSelected.includes('delete-observation-affiliate')
          "
          @click="deleteItem(item)"
        >
          mdi-delete
        </v-icon>
      </template>
    </v-data-table>
    <v-dialog v-model="dialog" max-width="800px">
      <ValidationObserver ref="observerObservation">
        <v-form>
          <v-card>
            <v-card-title>
              <span class="text-h5">{{ formTitle }} </span>
            </v-card-title>

            <v-card-text>
              <v-container>
                <v-row>
                  <v-col cols="12">
                    <ValidationProvider
                      v-slot="{ errors }"
                      name="Tipo de observación:"
                      rules="required"
                    >
                      <v-select
                        :error-messages="errors"
                        dense
                        :loading="loading_observation_type"
                        :items="editedIndex == -1 ? observation_type : editedItem.observation_type"
                        item-text="name"
                        item-value="id"
                        label="Tipo de observación"
                        v-model="editedItem.observation_type_id"
                        :disabled="editedIndex != -1 ? true : false"
                      >
                      </v-select>
                    </ValidationProvider>
                  </v-col>
                  <v-col cols="12">
                    <ValidationProvider
                      v-slot="{ errors }"
                      name="Descripción"
                      rules="required"
                    >
                      <v-textarea
                        :error-messages="errors"
                        v-model="editedItem.message"
                        label="Descripcion"
                        rows="4"
                      ></v-textarea>
                    </ValidationProvider>
                  </v-col>
                </v-row>
              </v-container>
            </v-card-text>

            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn color="error" text @click="close()"> Cancelar </v-btn>
              <v-btn color="success" :loading="loading_button" text @click="validateObservation()">
                Guardar
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-form>
      </ValidationObserver>
    </v-dialog>
    <v-dialog v-model="dialogDelete" max-width="500px">
      <v-card>
        <v-card-title class="text-h5"
          >Esta seguro de eliminar el registro?</v-card-title
        >
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="error" text @click="closeDelete()">Cancelar</v-btn>
          <v-btn color="success" :loading="loading_button" text @click="deleteItemConfirm()"
            >Aceptar</v-btn
          >
          <v-spacer></v-spacer>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>
<script>
import common from "@/plugins/common";
export default {
  name: "Observation-Affiliate",
  props: {
    affiliate: {
      type: Object,
      required: true,
    },
  },
  data: () => ({
    dialog: false,
    dialogDelete: false,
    headers: [
      {
        text: "Nro",
        class: ["normal", "white--text"],
        align: "left",
        value: "id",
        width: "5%",
      },
      {
        text: "Fecha",
        class: ["normal", "white--text"],
        align: "left",
        value: "date",
        width: "20%",
      },
      {
        text: "Usuario",
        class: ["normal", "white--text"],
        align: "left",
        value: "user.username",
        width: "5%",
      },
      {
        text: "Observación",
        class: ["normal", "white--text"],
        align: "left",
        value: "observation_type.name",
        width: "30%",
      },
      {
        text: "Mensaje",
        class: ["normal", "white--text"],
        align: "left",
        value: "message",
        width: "35%",
      },
      {
        text: "Acciones",
        value: "actions",
        class: ["normal", "white--text"],
        align: "center",
        sortable: false,
        width: "10%",
        filterable: false,
      },
    ],
    observations: [],
    observation_type: [],
    editedIndex: -1,
    editedItem: {},
    editedItem_original: {},
    defaultItem: {},
    loading_observations: false,
    loading_observation_type: false,
    trashed: false,
    val_observation: false,
    options:{},
    loading_button: false
  }),

  computed: {
    formTitle() {
      return this.editedIndex === -1 ? "Nuevo Registro" : "Editar registro";
    },
    //Metodo para obtener Permisos por rol
    permissionSimpleSelected() {
      return this.$store.getters.permissionSimpleSelected;
    },
  },

  watch: {
    "editedItem.tracking_date": function (date) {
      this.formatDate("trackingDate", date);
    },
    dialog(val) {
      val || this.close();
    },
    dialogDelete(val) {
      val || this.closeDelete();
    },
    options: function (newVal, oldVal) {
      if (
        newVal.page != oldVal.page ||
        newVal.itemsPerPage != oldVal.itemsPerPage
      ) {
        this.getObservations();
      }
    },
    trashed: function (newVal, oldVal) {
      if (newVal != oldVal) {
        this.options.page = 1;
        this.getObservations();
      }
    },
  },

  created() {
    this.removeAccents = common.removeAccents;
    this.getObservations();
    this.getTrackingTypes();
  },

  methods: {
    formatDate(key, date) {
      if (date) {
        this.dates[key].formatted = this.$moment(date).format("L");
      } else {
        this.dates[key].formatted = null;
      }
    },
    async getObservations() {
      try {
        this.loading_observations = true;
        let res = await axios.get(
          `affiliate/${this.affiliate.id}/observation`,
          {
            params: {
              trashed: this.trashed,
            },
          }
        );
        this.observations = res.data;
        this.loading_observations = false;
      } catch (e) {
        console.log(e);
      } finally {
        this.loading_observations = false;
      }
    },
    async getTrackingTypes() {
      try {
        this.observation_types_loading = true;
        let res = await axios.get(`module/observation_type_affiliate/${this.$route.params.id}`);
        this.observation_type = res.data;
        console.log(this.observation_type);
        this.observation_types_loading = false;
      } catch (e) {
        console.log(e);
        this.observation_types_loading = false;
      }
    },

    editItem(item) {
      this.editedIndex = this.observations.indexOf(item);
      this.editedItem = Object.assign({}, item);
      this.editedItem_original = Object.assign({}, item);
      this.dialog = true;
    },

    deleteItem(item) {
      this.editedIndex = this.observations.indexOf(item);
      this.editedItem = Object.assign({}, item);
      this.dialogDelete = true;
    },

    async deleteItemConfirm() {
      try {
        this.loading_button = true
        let res = await axios.delete(
          `affiliate/${this.$route.params.id}/observation`,
          {
            data: {
              user_id: this.editedItem.user_id,
              observation_type_id: this.editedItem.observation_type_id,
              message: this.editedItem.message,
              date: this.editedItem.date,
              enabled: this.editedItem.enabled,
            },
          }
        );
        this.getObservations()
        this.getTrackingTypes()
        this.closeDelete();
        this.loading_button = false
        this.toastr.success("Se eliminó correctamente el registro.");
      } catch (e) {
        console.log(e);
      }
    },

    close() {
      this.dialog = false;
      this.$nextTick(() => {
        this.editedItem = Object.assign({}, this.defaultItem);
        this.editedIndex = -1;
      });
    },

    closeDelete() {
      this.dialogDelete = false;
      this.$nextTick(() => {
        this.editedItem = Object.assign({}, this.defaultItem);
        this.editedIndex = -1;
      });
    },
    async saveObservation() {
      try {
        this.loading_button = true
        if (this.editedIndex == -1) {
          let res = await axios.post(`affiliate/${this.$route.params.id}/observation`,
            {
              observation_type_id: this.editedItem.observation_type_id,
              message: this.editedItem.message,
            }
          );
          this.toastr.success("Se realizó el registró correctamente.");
        } else {
          let res = await axios.patch(
            `affiliate/${this.$route.params.id}/observation`,
            {
              original: {
                user_id: this.editedItem_original.user_id,
                observation_type_id: this.editedItem_original.observation_type_id,
                message: this.editedItem_original.message,
                date: this.editedItem_original.date,
                enabled: false,
              },
              update: {
                enabled: this.editedItem.enabled,
                message: this.editedItem.message,
              },
            }
          );
          this.toastr.success("Se actualizó el registro correctamente.");
        }
        this.getObservations()
        this.getTrackingTypes()
        this.loading_button = false
        this.dialog = false
      } catch (e) {
        console.log(e)
        this.loading_button = false
        this.dialog = false
      }
    },
    async validateObservation() {
      try {
        this.val_observation = await this.$refs.observerObservation.validate();
        if (this.val_observation == true) {
          this.saveObservation();
        }
      } catch (e) {
        this.$refs.observerObservation.setErrors(e);
      }
    },

    itemRowBackground: function (item) {
      if (item.deleted_at != null) {
        return "style-4";
      }
    },
  },
};
</script>
<style scoped>
.style-4 {
  background-color: pink;
}
</style>

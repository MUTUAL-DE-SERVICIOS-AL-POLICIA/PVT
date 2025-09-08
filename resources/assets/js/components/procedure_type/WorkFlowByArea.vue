<template>
  <v-container fluid>
    <v-card flat>
      <v-card-title class="pa-0">
        <v-toolbar dense color="tertiary">
          <v-toolbar-title>FLUJO DE TRÁMITES</v-toolbar-title>
          <v-spacer></v-spacer>
          <v-btn color="success" fab x-small @click="openDialog()">
            <v-icon>mdi-plus</v-icon>
          </v-btn>
        </v-toolbar>
      </v-card-title>

      <!-- Tabla de datos -->
      <v-data-table
        :headers="headers"
        :items="workflows"
        :loading="loading"
        class="elevation-1 mt-4"
        dense
      >
        <template v-slot:item.id="{ index }">
          {{ index + 1 }}
        </template>
        <!--Acciones-->
        <template v-slot:item.actions="{ item }">
          <v-tooltip top>
            <template v-slot:activator="{ on }">
              <v-btn icon small color="success" v-on="on" @click="openDialog(item)">
                <v-icon>mdi-pencil</v-icon>
              </v-btn>
            </template>
            <span>Editar</span>
          </v-tooltip>

          <v-tooltip top>
            <template v-slot:activator="{ on }">
              <v-btn icon small color="error" v-on="on" @click="confirmDelete(item)">
                <v-icon>mdi-delete</v-icon>
              </v-btn>
            </template>
            <span>Eliminar</span>
          </v-tooltip>

          <v-tooltip top>
            <template v-slot:activator="{ on }">
              <v-btn icon small color="info" v-on="on" @click="openSequenceModal(item)">
                <v-icon>mdi-arrow-decision-outline</v-icon>
              </v-btn>
            </template>
            <span>Crear secuencia del flujo</span>
          </v-tooltip>
        </template>
      </v-data-table>
    </v-card>

    <!-- Modal -->
    <v-dialog v-model="dialog" max-width="600px">
      <v-card>
        <v-card-title>
          <span class="text-h5">{{ workflow_item.id ? 'Editar' : 'Nuevo' }}</span>
        </v-card-title>

        <v-card-text>
          <v-container>
            <v-row>
              <v-col cols="12">
                <v-text-field v-model="workflow_item.name" label="Nombre" required></v-text-field>
              </v-col>

              <v-col cols="12">
                <v-row>
                  <!-- Prefijo -->
                  <v-col cols="3">
                    <v-select
                      v-model="selectedPrefix"
                      :items="['PRES-', 'AMR-']"
                      label="Prefijo"
                      outlined
                      dense
                      class="text-uppercase"
                    ></v-select>
                  </v-col>

                  <!-- Campo de texto para el resto de la abreviatura -->
                  <v-col cols="4">
                    <v-text-field
                      v-model="shortenedSuffix"
                      label="Texto"
                      required
                      outlined
                      dense
                    ></v-text-field>
                  </v-col>
                  <!--Texto Completo-->
                  <v-col cols="5" class="d-flex align-center">
                    <v-text-field
                      v-model="shortenedFull"
                      label="Nombre Corto"
                      required
                      outlined
                      dense
                      readonly
                    ></v-text-field>
                  </v-col>

                </v-row>
              </v-col>
            </v-row>
          </v-container>
        </v-card-text>

        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="error" text @click="closeDialog()">Cancelar</v-btn>
          <v-btn color="success" text @click="saveWorkflow()">Guardar</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <!--Modal Eliminación-->
    <v-dialog v-model="delete_dialog" max-width="400px">
      <v-card>
        <v-card-title class="headline">Confirmar Eliminación</v-card-title>
        <v-card-text>
          ¿Está seguro de que desea eliminar el flujo de trámite <strong>{{ selectedItem.name }}</strong>?
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="grey" text @click="delete_dialog = false">Cancelar</v-btn>
          <v-btn color="error" text @click="deleteItem()">Eliminar</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Componente modal -->
    <SequenceModal 
      :dialog.sync="modal_sequence" 
      :item="selectedItem" 
      @close="modal_sequence = false" 
    />
  </v-container>
</template>

<script>
import SequenceModal from "@/components/procedure_type/SequenceModal";

export default {
  name: "WorkflowByArea",
  components: {
    SequenceModal
  },
  data() {
    return {
      workflows: [],
      loading: false,
      dialog: false,
      delete_dialog: false,
      selectedItem: {},
      selectedPrefix: "PRES-", // Prefijo por defecto
      shortenedText: "",  
      workflow_item: { 
        name: "", 
        shortened: "" 
      },
      headers: [
        { text: "Nro", 
          class: ['normal', 'white--text'],
          value: "id", 
          align: "center", 
          sortable: true 
        },
        { text: "Nombre", 
          class: ['normal', 'white--text'],
          value: "name", 
          align: "left", 
          sortable: true 
        },
        { text: "Abreviado", 
          class: ['normal', 'white--text'],
          value: "shortened", 
          align: "left", 
          sortable: true 
        },
        { text: "Acciones", 
          class: ['normal', 'white--text'],
          value: "actions", 
          align: "center", 
          sortable: false 
        },
      ],
      modal_sequence: false
    };
  },
  mounted() {
    this.getWorkflows();
  },
  computed: {
    shortenedSuffix: {
      get() {
        return this.workflow_item.shortened.replace(/^(PRES-|AMR-)/, ""); 
      },
      set(value) {
        this.workflow_item.shortened = this.selectedPrefix + value;
      }
    },
    shortenedFull() {
      return this.selectedPrefix + this.shortenedSuffix;
    }
  },
  methods: {
    async getWorkflows() {
      this.loading = true;
      try {
        const res = await axios.get("/workflow");
        this.workflows = res.data;
      } catch (e) {
        console.error(e);
      } finally {
        this.loading = false;
      }
    },
    openDialog(item = { name: "", shortened: "" }) {
      this.workflow_item = { ...item };
      this.selectedPrefix = item.shortened?.startsWith("AMR-") ? "AMR-" : "PRES-";
      this.dialog = true;
    },
    closeDialog() {
      this.dialog = false;
    },
    async saveWorkflow() {
      try {
        const data = {
          name: this.workflow_item.name.toUpperCase(),
          shortened: this.shortenedFull.toUpperCase()
        };

        if (this.workflow_item.id) {
          await axios.patch(`/workflow/${this.workflow_item.id}`, data);
        } else {
          await axios.post("/workflow", data);
        }
        
        this.closeDialog();
        this.getWorkflows();
      } catch (e) {
        console.error(e);
      }
    },
    confirmDelete(item) {
      this.selectedItem = item;
      this.delete_dialog = true;
    },
    async deleteItem() {
      try {
        let res = await axios.delete(`/workflow/${this.selectedItem.id}`); 
        if(res.status==201 || res.status == 200){
          this.workflows = this.workflows.filter(w => w.id !== this.selectedItem.id);
        } else {
          this.toastr.error("Error al eliminar el flujo de trámite.");
        }
        this.delete_dialog = false;
      } catch (e) {
        console.log(e)
      }      
    },
    openSequenceModal(item) {
      this.selectedItem = item;
      this.modal_sequence = true;
    }
  }
}
</script>

<template>
  <v-dialog
    v-model="dialogLocal" 
    fullscreen
    hide-overlay
    transition="dialog-bottom-transition"
    persistent
  >
    <v-card>
      <v-card-title>
        <v-toolbar dense color="tertiary">
          <v-btn icon @click="closeModal()">
            <v-icon>mdi-close</v-icon>
          </v-btn>
          <v-toolbar-title>Secuencia del flujo <strong>{{ item.name }}</strong></v-toolbar-title>
        </v-toolbar>
      </v-card-title>
      
      <v-card-text>
        <v-card>
          <v-card-text>
            <v-row class="px-5 pb-3">
              <div class="title">
                <span>Flujo de trabajo para los trámites de tipo </span>
                <span class="font-weight-black"></span>
              </div>
              <v-spacer></v-spacer>
              <div v-if="$store.getters.permissions.includes('update-setting')">
                <v-tooltip top>
                  <template v-slot:activator="{ on }">
                    <v-btn fab x-small color="info" v-on="on" class="mr-3" @click="addSequence">
                      <v-icon>mdi-plus</v-icon>
                    </v-btn>
                  </template>
                  <div><span>Añadir secuencia</span></div>
                </v-tooltip>
              </div>
            </v-row>

            <v-simple-table fixed-header class="responsive-table">
              <thead>
                <tr>
                  <th class="text-center">#</th>
                  <th class="text-center">Desde</th>
                  <th></th>
                  <th class="text-center">Hacia</th>
                  <th class="text-center" v-if="$store.getters.permissions.includes('update-setting')">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(sequence, index) in wf_sequences" :key="index">
                  <td class="text-center">{{ index + 1 }}</td>
                  <td>
                    <v-select
                      :items="wf_states"
                      item-text="name"
                      item-value="id"
                      class="pt-5"
                      v-model="sequence.wf_state_current_id"
                      outlined
                      dense
                      :readonly="wf_sequences.length > 1"
                    ></v-select>
                  </td>
                  <td class="text-center"><v-icon>mdi-arrow-right-bold-outline</v-icon></td>
                  <td>
                    <v-select
                      :items="availableNextStates(sequence)"
                      item-text="name"
                      item-value="id"
                      class="pt-5"
                      v-model="sequence.wf_state_next_id"
                      outlined
                      dense
                      @change="saveWfSequence(sequence, index)"
                    ></v-select>
                  </td>
                  <td class="text-center" v-if="$store.getters.permissions.includes('update-setting')">
                    <v-tooltip v-if="index == wf_sequences.length - 1" top>
                      <template v-slot:activator="{ on }">
                        <v-btn 
                          icon 
                          color="error" 
                          v-on="on" 
                          @click="deleteSequence(sequence.id, index)"
                        >
                          <v-icon>mdi-minus-circle</v-icon>
                        </v-btn>
                      </template>
                      <div><span>Eliminar secuencia</span></div>
                    </v-tooltip>
                  </td>
                </tr>
              </tbody>
            </v-simple-table>

          </v-card-text>
        </v-card>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

<script>
export default {
  name: "WfSequenceModal",
  props: {
    dialog: Boolean,
    item: Object,
  },
  data() {
    return {
      dialogLocal: this.dialog,
      loading: true,
      wf_states: [],
      wf_sequences: [],
    };
  },
  watch: {
    dialog(val) {
      this.dialogLocal = val;
      if (val) {
        this.getWorkflowStates();
        this.getWfSequences();
      }
    },
    dialogLocal(val) {
      this.$emit("update:dialog", val);
    },
  },
  methods: {
    async getWorkflowStates() {
      try {
        let res = await axios.get("wf_state");
        this.wf_states = res.data;
      } catch (e) {
        console.error("Error al obtener estados:", e);
      }
    },
    async getWfSequences() {
      try {
        this.loading = true;
        let res = await axios.post("get_sequence", { 
          workflow_id: this.item.id 
        });
        this.wf_sequences = res.data;
      } catch (e) {
        console.error("Error al obtener secuencias:", e);
      } finally {
        this.loading = false;
      }
    },
    addSequence() {
      let lastNextStateId = this.wf_sequences.length > 0 ? this.wf_sequences[this.wf_sequences.length - 1].wf_state_next_id : null;
      let newSequence = {
        wf_state_current_id: lastNextStateId,
        wf_state_next_id: null
      };
      this.wf_sequences.push(newSequence);
    },
    async saveWfSequence(sequence, index) {
      try {
        if (!sequence.wf_state_current_id || !sequence.wf_state_next_id) {
          this.toastr.error("Debe seleccionar los flujos de origen y destino.");
          return;
        }
        this.loading = true;
        if (sequence.id) {
          await axios.patch(`wf_sequence/${sequence.id}`, {
            wf_state_current_id: sequence.wf_state_current_id,
            wf_state_next_id: sequence.wf_state_next_id
          });
        } else {
          let res = await axios.post("wf_sequence", {
            workflow_id: this.item.id,
            wf_state_current_id: sequence.wf_state_current_id,
            wf_state_next_id: sequence.wf_state_next_id
          });
          this.wf_sequences[index].id = res.data.id;
        }
        this.getWfSequences(); 
        this.toastr.success("Secuencia guardada correctamente.");
      } catch (e) {
        console.error("Error al guardar secuencia:", e);
      } finally {
        this.loading = false;
      }
    },
    async deleteSequence(id, index) {
      if (!id) {
        this.wf_sequences.splice(index, 1);
        return;
      }
      if (!confirm("¿Seguro que deseas eliminar esta secuencia?")) return;
      try {
        await axios.delete(`wf_sequence/${id}`);
        this.getWfSequences();
        this.toastr.error("Secuencia eliminada correctamente.");
      } catch (e) {
        this.toastr.error("Error al eliminar secuencia:", e);
      }
    },
    //obtene los estados que no han sido seleccionados
    availableNextStates(currentSequence) {
      let selectedNextIds = this.wf_sequences //Se tiene todas las secuencias
        .filter(seq => seq !== currentSequence) //Filtra todas las secuencias excepto la actual
        .map(seq => seq.wf_state_next_id) //Obtiene los IDs de los estados siguientes en las demás secuencias
        .filter(id => id !== null) //Elimina valores nulos
      return this.wf_states.filter(state => !selectedNextIds.includes(state.id)) //Filtra los estados disponibles
    },
    closeModal() {
      this.dialogLocal = false;
      this.$emit("close");
    },
  },
};
</script>

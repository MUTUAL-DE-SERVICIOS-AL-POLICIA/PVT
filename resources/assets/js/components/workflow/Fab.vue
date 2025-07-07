<template>
  <div>
    <v-tooltip top>
      <template v-slot:activator="{ on }">
        <v-btn
          v-on="on"
          color="success"
          dark
          small
          absolute
          bottom
          right
          fab
          @click="sheet = true"
        >
          <v-icon>mdi-send</v-icon>
        </v-btn>
      </template>
      <span>Derivar</span>
    </v-tooltip>
    <v-row justify="center">
      <v-dialog 
        v-model="sheet" 
        scrollable 
        max-width="300px" 
        inset 
        persistent>
        <v-card>
          <v-toolbar dense flat color="">
            <v-card-title>Derivar {{ `(${selectedLoans.length})` }} trámites </v-card-title>
            <v-spacer></v-spacer>
          </v-toolbar>
          <v-card-text style="height: 300px;">
            <div>
              <v-select
                v-if="filteredWFStates.length > 1"
                v-model="selectedStateId"
                :items="filteredWFStates"
                label="Seleccione el área para derivar"
                class="pt-3 my-0"
                item-text="name"
                item-value="id"
                dense
              ></v-select>
              <div v-else-if="filteredWFStates.length === 1">
                <h3>Área para derivar: {{ filteredWFStates[0].name }}</h3>
              </div>
              <div v-else>
                <h3 class="red">No se tiene un área para derivar.</h3>
              </div>
            </div>
            <div class="blue--text">Los siguientes trámites serán derivados:</div>
            <small>{{ selectedLoans.map(o => o.code).join(', ') }}</small>
          </v-card-text>
          <v-divider></v-divider>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn color="error" text @click="sheet = false">Cerrar</v-btn>
            <template v-if="filteredWFStates.length >= 1">
              <v-btn
                color="success"
                text
                @click="derivationLoans()"
                :disabled="status_click"
                :loading="status_click"
              >
                Derivar
              </v-btn>
            </template>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </v-row>
  </div>
</template>

<script>
export default {
  name: 'workflow-fab',
  props: {
    bus: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      sheet: false,
      selectedLoans: [],
      flow: {
        previous: [],
        next: []
      },
      selectedStateId: null,
      idLoans: [],
      status_click: false
    };
  },
  computed: {
    filteredWFStates() {
      return this.$store.getters.wfStates.filter(o => this.flow.next.includes(o.id));
    }
  },
  watch: {
    selectedLoans(val) {
      if (val.length) 
        this.getFlow()
    }
  },
  mounted() {
    this.bus.$on('selectLoans', (data) => {
      this.selectedLoans = data
    })
  },
  methods: {
    async getFlow() {
      try {
        let res = await axios.get(`loan/${this.selectedLoans[0].id}/flow`)
        this.flow = res.data
      } catch (e) {
        console.error(e)
      }
    },
    async derivationLoans() {
      this.idLoans = this.selectedLoans.map(o => o.id)
      try {
        this.status_click = true
        let res = await axios.patch(`loans`, {
          ids: this.idLoans,
          next_state_id: this.filteredWFStates.length > 1 ? this.selectedStateId : this.filteredWFStates[0].id,
          current_role_id: this.$store.getters.rolePermissionSelected.id
        });
        this.sheet = false;
        this.bus.$emit('emitRefreshLoans');
        this.toastr.success("El trámite fue derivado.");
        printJS({
          printable: res.data.attachment.content,
          type: res.data.attachment.type,
          documentTitle: res.data.attachment.file_name,
          base64: true
        })
      } catch (e) {
        this.toastr.error('Operación Invalida.');
        this.sheet = false;
        this.bus.$emit('emitRefreshLoans');
      } finally {
        this.status_click = false;
      }
    }
  }
}
</script>
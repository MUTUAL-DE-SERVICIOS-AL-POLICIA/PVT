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
          <v-card-title>Derivar {{ " ("+selectedLoans.length +") "}} trámites</v-card-title>
           <v-spacer></v-spacer>
          </v-toolbar>
          <v-card-text style="height: 300px;">
            <div>
              <v-select v-if="$store.getters.roles.filter(o => flow.next.includes(o.id)).length > 1"
                v-model="selectedRoleId"
                :items="$store.getters.roles.filter(o => flow.next.includes(o.id))"
                label="Seleccione el área para derivar"
                class="pt-3 my-0"
                item-text="display_name"
                item-value="id"
                dense
              ></v-select>
              <div v-else-if="$store.getters.roles.filter(o => flow.next.includes(o.id)).length == 1"><h3>Área para derivar: {{String($store.getters.roles.filter(o => flow.next.includes(o.id)).map(o => o.display_name))}}</h3></div>
              <div v-else><h3 class="red">No se tiene un área para derivar.</h3></div>           
            </div>

            <div class="blue--text">Los siguientes trámites serán derivados: </div>     
            <small>{{ selectedLoans.map(o => o.code).join(', ') }}</small>
          </v-card-text>
          <v-divider></v-divider>
          <v-card-actions>
             <v-spacer></v-spacer>
            <v-btn color="error" text @click="sheet = false">Cerrar</v-btn>
            <template v-if="$store.getters.roles.filter(o => flow.next.includes(o.id)).length >= 1">
              <v-btn 
                color="success" 
                text 
                @click="derivationLoans()"
                :disabled="this.status_click"
                :loading="this.status_click"
                >Derivar
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
      selectedRoleId: null,
      idLoans: [],
      status_click: false
    }
  },
  watch: {
    selectedLoans(val) {
      if (val.length) {
        this.getFlow()
      }
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
        console.log(e)
      }
    },
    async derivationLoans() {
      let res
      this.idLoans = this.selectedLoans.map(o => o.id)
      try {
        if(this.$store.getters.roles.filter(o => this.flow.next.includes(o.id)).length > 1){
          //this.loading = true;
          this.status_click = true
            res = await axios.patch(`loans`, {
              ids: this.idLoans,
              role_id: this.selectedRoleId,
              current_role_id: this.$store.getters.rolePermissionSelected.id
            });
            /*////HACER DESDE BACK
            for(let i = 0; i< this.idLoans.length; i++){
               let res1 = await axios.patch(`loan/${this.idLoans[i]}`, {
                user_id: null
                })
              console.log(res1)
            }
            ////////*/
            this.sheet = false;
            this.bus.$emit('emitRefreshLoans');
            this.toastr.success("El trámite fue derivado." ) 
        }else{
            //this.loading = true;
             this.status_click = true
            res = await axios.patch(`loans`, {
              ids: this.idLoans,
              role_id: parseInt(this.$store.getters.roles.filter(o => this.flow.next.includes(o.id)).map(o => o.id)),
              current_role_id: this.$store.getters.rolePermissionSelected.id
            });  
            /*////HACER DESDE BACK
            for(let i = 0; i< this.idLoans.length; i++){
               let res1 = await axios.patch(`loan/${this.idLoans[i]}`, {
                user_id: null
                })
              console.log(res1)
            }
            ////////*/
            this.sheet = false;
            this.bus.$emit('emitRefreshLoans');
            this.toastr.success("El trámite fue derivado." ) 
        }

        printJS({
          printable: res.data.attachment.content,
          type: res.data.attachment.type,
          documentTitle: res.data.attachment.file_name,
          base64: true
        })

        if(res.status==201 || res.status == 200){
          this.status_click = false        
        }
     
      } catch (e) {
        console.log(e)
        this.status_click = false
        this.toastr.error("Ocurrió un error en la derivación...")
      }
    }
  }
}
</script>
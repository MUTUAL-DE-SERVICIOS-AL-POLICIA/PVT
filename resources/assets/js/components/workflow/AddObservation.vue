<template>
  <v-dialog
    v-model="dialog"
    width="600"
  >
    <v-card>
      <v-toolbar dense flat color="">
        <v-toolbar-title v-show="!observation.edit && observation.accion=='devolver'">Devolver trámite</v-toolbar-title>
        <v-toolbar-title v-show="!observation.edit && observation.accion=='anular'">Anular trámite</v-toolbar-title>
        <v-toolbar-title v-show="!observation.edit && observation.accion=='validar'">Validar trámite</v-toolbar-title>
        <v-toolbar-title v-show="observation.edit">Editar Observacion</v-toolbar-title>         
        <v-spacer></v-spacer>
      </v-toolbar>
      <v-divider></v-divider>
      <v-card-text class="ma-0 pb-0">
        <v-container fluid class="ma-0 pb-0">
          <v-row justify="center" class="ma-0 pb-0">
            <v-col cols="12" class="ma-0 pb-0">
              <v-container class="py-0">
                <ValidationObserver ref="observer">
                  <v-form>
                    <template v-if="observation.accion !='validar'">
                    <v-row v-show="observation.accion=='devolver'">
                      <v-col cols="4" class="ma-0 pb-0">
                        <label>Devolver al área de:</label>
                      </v-col>
                      <v-col cols="8" class="ma-0 pb-0">
                        <v-select
                          dense
                          v-model="valArea"
                          :items="areas"
                          item-text="display_name"
                          item-value="id"
                          @change="searchUserFlow()"
                        ></v-select>
                        <h4 class="text-center">{{user_name}}</h4>
                      </v-col>
                    </v-row>
                    <v-row>
                      <v-col cols="4" class="ma-0 pb-0">
                        <label>Tipo de observación:</label>
                      </v-col>
                      <v-col cols="8" class="ma-0 pb-0">
                        <v-select
                          dense
                          :readonly="observation.edit"
                          v-model="observation.observation_type_id"
                          :items="observation_type"
                          item-text="name"
                          item-value="id"
                          persistent-hint
                        ></v-select>
                      </v-col>
                      <v-col cols="12" class="ma-0 pb-0">
                        <v-text-field
                          dense
                          :readonly="observation.edit"
                          v-model="observation.message"
                          label="Descripcion"
                          ></v-text-field>
                      </v-col>
                      <v-col cols="6" class="ma-0 pb-0" v-show="observation.edit">
                        <v-checkbox
                          class="ma-0 pb-0"
                          label="Subsanar Tramite"
                          v-model="observation.enabled"
                        ></v-checkbox>
                    </v-col>
                    </v-row>
                    </template>
                    <template v-else>
                      ¿Desea validar el trámite <strong>{{this.loan.code}}</strong> ?.
                    </template>
                  </v-form>
                </ValidationObserver>
              </v-container>
            </v-col>
          </v-row>
        </v-container>
      </v-card-text>
      <v-divider></v-divider>
      <v-card-actions class="ma-0 pb-0">
      <v-spacer></v-spacer>
      <v-btn color="error" text @click.stop="close()">Cerrar</v-btn>
      <v-btn color="success" text @click.stop="saveObservation($route.params.id)">Aceptar</v-btn>
    </v-card-actions>
    </v-card>
  </v-dialog>
</template>
<script>

export default {
  name: "flow-add-observation",
  props: {
    bus: {
      type: Object,
      required: true
    },
    loan: {
      type: Object,
      required: true
    },
  },
  data: () => ({
    dialog: false,
    observation: {},
    observation_type: [],
    flow: {},
    valArea: [],
    areas: [],
    user_id_previous: 0,
    user_name: null,
    length_previus: 0
  }),
  beforeMount(){
    this.getObservationType()
    this.getFlow(this.$route.params.id)
  },
  mounted() {
    this.bus.$on('openDialog', (observation) => {
      this.observation = observation
      this.dialog = true
      console.log("resultado")
      console.log(observation)
    })
  },
  methods: {
    async adicionar() {
      if (await this.$refs.observer.validate()) {
        this.saveObservation(this.$route.params.id)
        this.bus.$emit('saveObservation', this.observation)
        this.close()
      }
    },
    close() {
      this.dialog = false
      this.observation = {}
    },
    async saveObservation(id) {
      try {
          if(this.observation.edit)
          {
            let res = await axios.patch(`loan/${id}/observation`, {
              original: {
                user_id: this.observation.user_id,
                observation_type_id: this.observation.observation_type_id,
                message: this.observation.message,
                date: this.observation.date,
                enabled: false
              },
              update: {
                enabled: this.observation.enabled
                }
            })
            this.bus.$emit('emitSaveObservation', 'vacio')//emitir SAveObservation edit, para ObserverFlow
          }else{
            if(this.observation.accion=='validar'){
                let res = await axios.patch(`loan/${id}`, {
                validated: true,
                user_id: this.$store.getters.id,
                current_role_id: this.$store.getters.rolePermissionSelected.id
              })
              this.toastr.success("Se validó el trámite correctamente.")
            }else{
              let res = await axios.post(`loan/${id}/observation`, {
              observation_type_id:this.observation.observation_type_id,
              message:this.observation.message})
              if(this.observation.accion=='devolver'){
                let res1 = await axios.patch(`loan/${id}`, {
                role_id: this.valArea,
                user_id: this.user_id_previous
                })
                 this.toastr.success("Se devolvio el tramite correctamente.")
              }else{
                let res2 = await axios.delete(`loan/${id}`)
                let code = res2.data.code
                this.toastr.success("El trámite " + code + " fue anulado correctamente.")
              }
            }
             this.$router.push("/workflow")
          }
          this.dialog = false
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
    async getObservationType() {
      try {
        this.loading = true
        let res = await axios.get(`module/${this.$store.getters.module.id}/observation_type`)
        this.observation_type = res.data
        console.log('estas son las observaciones'+this.observation_type)
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
    async getFlow(id) {
      try {
        let res = await axios.get(`loan/${id}/flow`)
        this.flow = res.data
        this.length_previus = this.flow.previous.length
        this.areas = this.$store.getters.roles.filter(o =>
          this.flow.previous.includes(o.id)
        )
      } catch (e) {
        console.log(e)
      }
    },
    async getUser(id) {
      try{ 
        let user
        let res = await axios.get(`user/${id}`)
        user = res.data
        this.user_name = "Usuario: " + user.username

      } catch (e) {
        console.log(e)
      }
    },
    searchUserFlow(){
      for(let i = 0; i< this.flow.previous.length; i++){
        if(this.flow.previous[i] == this.valArea){
          this.user_id_previous = this.flow.previous_user[i]
          this.getUser(this.user_id_previous) 
        }
      }      
    },

  }
}
</script>
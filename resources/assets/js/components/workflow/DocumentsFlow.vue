<template>
  <v-container fluid >
    <!--<span>{{ JSON.stringify(permissionSimpleSelected) }}</span>
     <pre>{{ permissionSimpleSelected }}</pre>
     <pre>{{ rolePermissionSelected.id }}</pre>-->
    <v-toolbar-title  class="pb-2">DOCUMENTOS PRESENTADOS</v-toolbar-title>
    <v-form>
      <template top v-if="permissionSimpleSelected.includes('validate-submitted-documents')">
        <div >
            <v-tooltip >
              <template v-slot:activator="{ on }">
                <v-btn
                  fab
                  dark
                  x-small
                  :color="'error'"
                  top
                  right
                  absolute
                  v-on="on"
                  style="margin-right: 75px;"
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

          <v-tooltip top v-if="permissionSimpleSelected.includes('validate-submitted-documents')">
            <template v-slot:activator="{ on }">
              <v-btn
                fab
                dark
                x-small
                :color="editable ? 'danger' : 'success'"
                top
                right
                absolute
                v-on="on"
                style="margin-right: 35px;"
                @click.stop="validarDoc()"
              >
                <v-icon v-if="editable">mdi-check</v-icon>
                <v-icon v-else>mdi-check-bold</v-icon>
              </v-btn>
            </template>
            <div>
              <span v-if="editable">Guardar</span>
              <span v-else>Validar documentos</span>
          </div>
          </v-tooltip>
        </div>
      </template>
      <template v-if="permissionSimpleSelected.includes('update-documents-requirements')" >
          <div >
            <v-tooltip top>
              <template v-slot:activator="{ on }">
                <v-btn
                  fab
                  dark
                  x-small
                  :color="'error'"
                  top
                  right
                  absolute
                  v-on="on"
                  style="margin-right: 45px;"
                  @click.stop="resetForm()"
                  v-show="editar"
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
                :color="editar ? 'danger' : 'blue'"
                top
                right
                absolute
                v-on="on"
                style="margin-right: 0px;"
                @click.stop="editarDoc()"
              >
                <v-icon v-if="editar">mdi-check</v-icon>
                <v-icon v-else>mdi-pencil</v-icon>
              </v-btn>
            </template>
            <div>
              <span v-if="editable">Guardar</span>
              <span v-else>Editar documentos</span>
          </div>
          </v-tooltip>
        </div>
      </template>
      <v-toolbar-title>REQUERIDOS</v-toolbar-title>
       <v-progress-linear></v-progress-linear>
      <!--<v-card>-->
       <!-- <v-row>
          <v-col cols="12" >-->
            <!--<v-data-iterator :items="docsRequired" hide-default-footer>-->
              <!--<template>-->
                <v-row  class="py-3">
                  <v-col v-for="(req,i) in docsRequired" :key="req.id" cols="12" class="py-1">
                    <v-card dense>
                      <v-row>
                        <v-col cols="12" class="py-0">
                          <v-list dense class="py-0">
                            <v-list-item class="py-0">
                              <v-col cols="1" class="py-0">
                                <v-list-item-content class="align-end font-weight-light">
                                  <div>
                                    <h1>{{i+1}}</h1>
                                  </div>
                                </v-list-item-content>
                              </v-col>
                              <v-col cols="8" class="py-0 ml-n8">
                              {{ req.name }}
                              </v-col>
                              <v-col cols="3" class="py-0 my-0">
                                <div
                                  class="py-0"
                                  v-if="permissionSimpleSelected.includes('validate-submitted-documents')"
                                >
                                  <!--div class="py-0" -->
                                  <v-checkbox
                                    class="py-0"
                                    color="success"
                                    v-model="req.pivot.is_valid"
                                     @change="createObjectDocuments(req.id, req.pivot.is_valid, req.pivot.comment)"
                                    :disabled="!editable"
                                  ></v-checkbox>
                                </div>
                                <v-spacer></v-spacer>
                              </v-col>
                            </v-list-item>
                          </v-list>
                        </v-col>
                      </v-row>
                      <v-row v-if="permissionSimpleSelected.includes('validate-submitted-documents')">
                      <!--v-row -->
                        <v-col cols="12" class="ma-0 py-0 px-10">
                          <v-text-field
                            dense
                            outlined
                            color="success"
                            label="Comentario"
                            v-model="req.pivot.comment"
                             @change="createObjectDocuments(req.id, req.pivot.is_valid, req.pivot.comment)"
                            :disabled="!editable"
                          ></v-text-field>
                        </v-col>
                        <!--<v-col cols="1" class="ma-0 pa-0">
                          <v-tooltip top>
                            <template v-slot:activator="{ on }">
                              <v-btn
                                v-on="on"
                                icon
                                color="info"
                                class="m0-0 pt-4"
                                @click="validarDoc(req.id, req.pivot.is_valid, req.pivot.comment)"
                              >
                                <v-icon>mdi-content-save</v-icon>
                              </v-btn>
                            </template>
                            <span>Guardar validación de documento</span>
                          </v-tooltip>
                        </v-col>-->
                      </v-row>
                    </v-card>
                  </v-col>
                </v-row>
              <!--</template>
            </v-data-iterator>
          </v-col>
        </v-row>-->
        <v-row>
          <v-col cols="12" v-if="docsOptional.length >0" >
           <!--<v-data-iterator :items="docsOptional" hide-default-footer>-->
              <template>
                <v-toolbar-title>ADICIONALES</v-toolbar-title>
                 <v-progress-linear></v-progress-linear>
                <v-row  class="py-3">
                  <v-col v-for="(opt,i) in docsOptional" :key="opt.id" cols="12" class="py-1">
                    <v-card dense>
                      <v-row>
                        <v-col cols="12" class="py-0">
                          <v-list dense class="py-0">
                            <v-list-item class="py-0">
                              <v-col cols="1" class="py-0">
                                <v-list-item-content class="align-end font-weight-light">
                                  <div>
                                    <h1>{{i+1}}</h1>
                                  </div>
                                </v-list-item-content>
                              </v-col>
                              <v-col cols="8" class="py-0 ml-n8">
                                {{ opt.name }}
                              </v-col>
                              <v-col cols="3" class="py-0 my-0">
                                <div
                                  class="py-0"
                                  v-if="permissionSimpleSelected.includes('validate-submitted-documents')"
                                >
                                  <v-checkbox
                                    class="py-0"
                                    color="success"
                                    v-model="opt.pivot.is_valid"
                                    @change="createObjectDocuments(req.id, req.pivot.is_valid, req.pivot.comment)"
                                    :disabled="!editable"
                                  ></v-checkbox>
                                </div>
                                <v-spacer></v-spacer>
                              </v-col>
                            </v-list-item>
                          </v-list>
                        </v-col>
                      </v-row>
                      <v-row v-if="permissionSimpleSelected.includes('validate-submitted-documents')">
                        <v-col cols="12" class="ma-0 py-0 pl-10 pr-2">
                          <v-text-field
                            dense
                            outlined
                            color="success"
                            label="Comentario"
                            v-model="opt.pivot.comment"
                            @change="createObjectDocuments(req.id, req.pivot.is_valid, req.pivot.comment)"
                            :disabled="!editable"
                          ></v-text-field>
                        </v-col>
                        <!--<v-col cols="1" class="ma-0 pa-0">
                          <v-tooltip top>
                            <template v-slot:activator="{ on }">
                              <v-btn
                                v-on="on"
                                icon
                                color="info"
                                class="m0-0 pt-4"
                                @click="validarDoc(opt.id, opt.pivot.is_valid, opt.pivot.comment)"
                              >
                                <v-icon>mdi-content-save</v-icon>
                              </v-btn>
                            </template>
                            <span>Guardar validación de documento</span>
                          </v-tooltip>
                        </v-col>-->
                      </v-row>
                    </v-card>
                  </v-col>
                </v-row>
              </template>
              <template>
                <v-toolbar-title class="align-end font-weight-black text-left ma-0 pa-4 pl-8" v-show="!editar">
                  <h5 v-if="notes.length >0">Otros Documentos</h5>
                </v-toolbar-title>
                <v-row v-show="!editar">
                  <v-col cols="12" class="ma-0 px-10">
                    <div
                      class="align-end font-weight-light ma-0 pa-0 pl-2"
                      v-for="(note, index) of notes"
                      :key="index"
                    >
                      {{index+1 +". "}} {{note.message}}
                      <v-divider></v-divider>
                    </div>
                  </v-col>
                </v-row>
              </template>
              <template>
                <v-row  v-show="editar">
                  <v-col cols="12" class="ma-0 px-10">
                    <v-data-table
                      :headers="headers"
                      :items="notes"
                    >
                      <template v-slot:top>
                        <v-toolbar
                          flat
                        >
                          <v-toolbar-title>OTROS DOCUMENTOS</v-toolbar-title>
                          <v-divider
                            class="mx-4"
                            inset
                            vertical
                          ></v-divider>
                          <v-spacer></v-spacer>
                          <v-dialog
                            v-model="dialog"
                            max-width="500px"
                          >
                            <v-card>
                              <v-card-title>
                                <span style="color:teal" class="headline">EDITAR OTROS DOCUMENTOS</span>
                              </v-card-title>
                              <v-progress-linear></v-progress-linear>
                              <v-card-text >
                                <v-container>
                                  <v-row>
                                    <v-col cols="12" sm="12" md="12">
                                      <v-text-field
                                        v-model="editedItem.message"
                                        label="Descripcion"
                                      ></v-text-field>
                                    </v-col>
                                  </v-row>
                                </v-container>
                              </v-card-text>

                              <v-card-actions >
                                <v-spacer></v-spacer>
                                <v-btn
                                  color="red"
                                  text
                                  @click="close"
                                >
                                  Cerrar
                                </v-btn>
                                <v-btn
                                  color="success"
                                  text
                                  @click="guardar()"
                                >
                                  Guardar
                                </v-btn>
                              </v-card-actions>
                            </v-card>
                          </v-dialog>
                          <v-dialog v-model="dialogDelete" max-width="300px">
                            <v-card>
                              <v-card-text>
                                <h3 style="color:teal" >¿Esta seguro que quiere eliminar el documento?</h3>
                              <v-card-actions>
                                <v-spacer></v-spacer>
                                <v-btn color="red darken-1" text @click="closeDelete">Cancel</v-btn>
                                <v-btn color="success darken-1" text @click="deleteItemConfirm">OK</v-btn>
                                <v-spacer></v-spacer>
                              </v-card-actions>
                              </v-card-text>
                            </v-card>
                          </v-dialog>
                        </v-toolbar>
                      </template>
                      <template v-slot:[`item.actions`]="{ item }">
                        <v-icon
                          small
                          class="mr-2"
                          @click="editItem(item)"
                        >
                          mdi-pencil
                        </v-icon>
                        <v-icon
                          small
                          @click="deleteItem(item)"
                        >
                          mdi-delete
                        </v-icon>
                      </template>
                      <template v-slot:no-data>
                        <v-btn
                          color="primary"
                          @click="initialize"
                        >
                          Reset
                        </v-btn>
                      </template>
                    </v-data-table>
                  </v-col>
                </v-row>
              </template>
            <!--</v-data-iterator>-->
          </v-col>
        </v-row>
      <!--</v-card>-->
    </v-form>
  </v-container>
</template>
<script>
export default {
  name: "documents-flow",
  data: () => ({

 dialog: false,
      dialogDelete: false,
      headers: [
        {
          text: 'Descripcion',
          align: 'start',
          sortable: false,
          value: 'message',
        },
        { text: 'Actions', value: 'actions', sortable: false },
      ],
      desserts: [],
      editedIndex: -1,
      editedItem: {},
      defaultItem: {},
    editar:false,
    docsRequired: [],
    docsOptional: [],
    notes: [],
    editable: false,
    reload: false,
    documents:[]
  }),


computed: {
      //Metodo para obtener Permisos por rol
      permissionSimpleSelected () {
        return this.$store.getters.permissionSimpleSelected
      },
      rolePermissionSelected () {
        return this.$store.getters.rolePermissionSelected
      },
      
      formTitle () {
        return this.editedIndex === -1 ? 'New Item' : 'Edit Item'
      },
    },

    watch: {
      dialog (val) {
        val || this.close()
      },
      dialogDelete (val) {
        val || this.closeDelete()
      },
    },

    created () {
      this.initialize()
    },





  beforeMount() {
    this.getDocumentsSubmitted(this.$route.params.id)
    this.getNotes(this.$route.params.id)
  },

  methods: {

    async editarDoc() {
      try {
        if (!this.editar) {
          this.editar = true
        } else {
          this.editar = false
          this.toastr.success('Se esta editando.')
        }
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },

     async guardar(){
      let res = await axios.patch(`note/${this.editedItem.id}`, {
        message:this.editedItem.message
       })
        this.toastr.success('Se registró correctamente.')
    this.close()
    this.$forceUpdate()
},

      editItem (item) {
        this.editedItem =  item
        this.dialog = true
      },

      deleteItem (item) {
        this.editedIndex = this.notes.indexOf(item)
        this.editedItem = Object.assign({}, item)
        this.dialogDelete = true
      },

      deleteItemConfirm () {
        this.desserts.splice(this.editedIndex, 1)
        this.closeDelete()
      },

      close () {
        this.dialog = false
        this.$nextTick(() => {
          this.editedItem = Object.assign({}, this.defaultItem)
          this.editedIndex = -1
        })
      },

      closeDelete () {
        this.dialogDelete = false
        this.$nextTick(() => {
          this.editedItem = Object.assign({}, this.defaultItem)
          this.editedIndex = -1
        })
      },

      save () {
        if (this.editedIndex > -1) {
          Object.assign(this.desserts[this.editedIndex], this.editedItem)
        } else {
          this.desserts.push(this.editedItem)
        }
        this.close()
      },






    async getDocumentsSubmitted(id) {
      try {
        this.loading = true
        let res = await axios.get(`loan/${id}/document`)
        this.docsRequired = res.data.required
        this.docsOptional = res.data.optional
        console.log(this.docsRequired + " " + this.docsOptional)
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
    async getNotes(id) {
      try {
        this.loading = true
        let res = await axios.get(`loan/${id}/note`)
        this.notes = res.data
        console.log("NOTES  " + this.notes)
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
    createObjectDocuments(id, is_valid, comment){
      let document = {}
      document.id = id,
      document.is_valid = is_valid,
      document.comment = comment
      console.log("mostar objeto")
      console.log(document)
     
          this.documents.push(document)
      
      console.log(this.documents)

    },
    async validarDoc() {
      try {
        if (!this.editable) {
          this.editable = true
        } else {
          for(let i=0; i < this.documents.length; i++){
            console.log(this.documents[i].id)
            let res = await axios.patch(`loan/${this.$route.params.id}/document/${this.documents[i].id}`,
            {
              is_valid: this.documents[i].is_valid,
              comment: this.documents[i].comment
            }
          )
          }
          this.toastr.success("Los documentos se validarón correctamente")
          this.editable = false
        }
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
    resetForm() {
      this.editar = false
      this.editable = false
      this.reload = true
      this.$nextTick(() => {
      this.reload = false
      })
    },
  }
}
</script>
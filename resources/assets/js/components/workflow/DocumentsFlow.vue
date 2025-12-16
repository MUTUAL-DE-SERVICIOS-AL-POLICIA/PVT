<template>
  <v-container fluid >
    <v-toolbar-title  class="pb-2">DOCUMENTOS PRESENTADOS</v-toolbar-title>
      <v-form>
        <template v-if="permissionSimpleSelected.includes('validate-submitted-documents')">
          <div>
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
                  style="margin-right: 75px; margin-top: 20px;"
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
                  style="margin-right: 35px; margin-top: 20px;"
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
        <v-toolbar-title class="pb-2">REQUERIDOS</v-toolbar-title>
        <v-progress-linear></v-progress-linear>
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
                          <h2>{{ req.name }}</h2>
                        </v-col>
                        <v-col cols="3" class="py-0 my-0">
                          <div
                            class="py-0"
                          >
                            <v-checkbox
                              class="py-0"
                              :color="editable?'warning':'success'"
                              v-model="req.pivot.is_valid"
                              @change="createObjectDocuments(req.id, req.pivot.is_valid, req.pivot.comment)"
                              :readonly="!editable"
                            ></v-checkbox>
                          </div>
                          <v-spacer></v-spacer>
                        </v-col>
                      </v-list-item>
                    </v-list>
                  </v-col>
                </v-row>
                <v-row>
                  <v-col cols="12" class="ma-0 py-0 px-10">
                    <v-text-field
                      dense
                      :outlined="editable"
                      :color="editable?'warning':'success'"
                      label="Comentario"
                      v-model="req.pivot.comment"
                      @input="createObjectDocuments(req.id, req.pivot.is_valid, req.pivot.comment)"
                      :readonly="!editable"
                      clearable
                    ></v-text-field>
                  </v-col>
                </v-row>
              </v-card>
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="12">
              <template  v-if="docsOptional.length >0">
                <v-toolbar-title class="pb-2">ADICIONALES</v-toolbar-title>
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
                                    <h2>{{ opt.name }}</h2>
                                  </v-col>
                                  <v-col cols="3" class="py-0 my-0">
                                    <div
                                      class="py-0"
                                    >
                                      <v-checkbox
                                        class="py-0"
                                        :color="editable?'warning':'success'"
                                        v-model="opt.pivot.is_valid"
                                        @change="createObjectDocuments(opt.id, opt.pivot.is_valid, opt.pivot.comment)"
                                        :readonly="!editable"
                                      ></v-checkbox>
                                    </div>
                                    <v-spacer></v-spacer>
                                  </v-col>
                                </v-list-item>
                              </v-list>
                            </v-col>
                          </v-row>
                        <v-row>
                          <v-col cols="12" class="ma-0 py-0 pl-10 pr-2">
                            <v-text-field
                              dense
                              :outlined="editable"
                              :color="editable?'warning':'success'"
                              label="Comentario"
                              v-model="opt.pivot.comment"
                              @input="createObjectDocuments(opt.id, opt.pivot.is_valid, opt.pivot.comment)"
                              :readonly="!editable"
                              clearable
                            ></v-text-field>
                          </v-col>
                        </v-row>
                      </v-card>
                    </v-col>
                  </v-row>
                </template>
                <template  v-if="notes.length >0">
                  <v-row>
                    <v-col cols="3">
                      <v-toolbar-title class="align-end font-weight-black text-left ma-0 py-0 pa-0 pl-8" >
                        <h4 >Otros Documentos</h4>
                      </v-toolbar-title>
                    </v-col>
                    <v-col cols="6" class=" ma-0 pa-0 pl-1 py-0">
                      <template v-if="permissionSimpleSelected.includes('update-documents-requirements')" >
                        <div>
                          <v-tooltip top>
                            <template v-slot:activator="{ on }">
                              <v-btn
                                fab
                                dark
                                x-small
                                :color="'error'"
                                top
                                right
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
                          <!-- <v-tooltip top>
                            <template v-slot:activator="{ on }">
                              <v-btn
                                fab
                                dark
                                x-small
                                :color="editar ? 'danger' : 'blue'"
                                top
                                right
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
                              <span v-else>Editar Otros Documentos</span>
                            </div>
                          </v-tooltip> -->
                        </div>
                      </template>
                    </v-col>
                  </v-row>
                  <v-row v-show="!editar">
                    <v-col cols="12" class="ma-0 px-10">
                      <div
                        class="align-end  ma-0 pa-0 pl-2"
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
                  <v-row >
                    <v-col cols="12" class="ma-0 px-10" v-show="editar">
                      <v-data-table
                        :headers="headers"
                        :items="notes"
                      >
                        <template v-slot:top>
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
                                  <v-btn color="red darken-1" text @click="dialogDeletex">Cancelar</v-btn>
                                  <v-btn color="success darken-1" text @click="saveDelete(editedItem.id)">Aceptar</v-btn>
                                  <v-spacer></v-spacer>
                                </v-card-actions>
                              </v-card-text>
                            </v-card>
                          </v-dialog>
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
                      </v-data-table>
                    </v-col>
                  </v-row>
                </template>
              </v-col>
            </v-row>
          </v-form>
        </v-container>
</template>
<script>
export default {
  name: "documents-flow",
  props: {
    val_docs: {
      type: Object,
      required: true
      },
    docsRequired: {
      type: Array,
      required: true
    },
    docsOptional: {
      type: Array,
      required: true
    },
  },
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
    editedIndex: -1,
    editedItem: {},
    defaultItem: {},
    editar:false,
    // docsRequired: [],
    // docsOptional: [],
    notes: [],
    editable: false,
    reload: false,
    documents:[],
  }),


  computed: {
    //Metodo para obtener Permisos por rol
    permissionSimpleSelected () {
      return this.$store.getters.permissionSimpleSelected
    },
  },
    watch: {
      dialog (val) {
        val || this.close()
      },
    },

 beforeMount() {
    //this.getDocumentsSubmitted(this.$route.params.id)
    this.getNotes(this.$route.params.id)
  },

  methods: {

    async editarDoc() {
      try {
        if (!this.editar) {
          this.editar = true
        } else {
          this.editar = false
        }
         this.$forceUpdate()
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
    //Metodo para editar otros documentos
      async guardar(){
      let res = await axios.patch(`note/${this.editedItem.id}`, {
        message:this.editedItem.message
       })
      this.toastr.success('Se registró correctamente.')
      this.close()
      },
    //Obteniendo el valor de la descripcion de otros documentos
      editItem (item) {
        this.editedItem =  item
        this.dialog = true
      },
    //Obteniendo el id de otros documentos
      deleteItem (item) {
        this.editedItem =  item
        this.dialogDelete = true
      },
    //Metodo para cerrar el modal de edicion y refrescar los datos nuevos
      close () {
        this.dialog = false
        this.$nextTick(() => {
          this.editedItem = Object.assign({}, this.defaultItem)
          this.editedIndex = -1
        })
      },
    //Metodo para eliminar otros documentos
      saveDelete (id) {
        try {
          this.dialogDelete=false
          let res = axios.delete(`note/${id}`)
          this.toastr.success('Se elimino correctamente')
          this.getNotes(this.$route.params.id)
        } catch (e) {
          console.log(e)
        } finally {
          this.loading = false
        }
      },
    // async getDocumentsSubmitted(id) {
    //   try {
    //     this.loading = true
    //     let res = await axios.get(`loan/${id}/document`)
    //     this.docsRequired = res.data.required
    //     this.docsOptional = res.data.optional
    //     this.validated = res.data.validated
    //   } catch (e) {
    //     console.log(e)
    //   } finally {
    //     this.loading = false
    //   }
    // },
    //Metodo para sacar los Otros Documentos
    async getNotes(id) {
      try {
        this.loading = true
        let res = await axios.get(`loan/${id}/note`)
        this.notes = res.data
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
    //Metodo para cerrar el modal de eliminar
    dialogDeletex(){
      this.dialogDelete=false
    },
    createObjectDocuments(procedure_document_id, is_valid, comment) {
      // Verificar si el documento ya existe en this.documents
      let existingDocument = this.documents.find(doc => doc.procedure_document_id === procedure_document_id);

      // Si el documento ya existe, se actualiza
      if (existingDocument) {
        existingDocument.is_valid = is_valid
        existingDocument.comment = comment
      } else {
        // Si el documento no existe, crear uno nuevo y agregarlo a this.documents
        let document = {
          procedure_document_id: procedure_document_id,
          comment: comment,
          is_valid: is_valid
        };
        this.documents.push(document)
      }
    },
    async validarDoc() {
      try {
        if (!this.editable) {
          this.editable = true;
        } else {
          let res = await axios.patch(`loan/${this.$route.params.id}/documents`, {
            documents:this.documents
          });
          this.toastr.success("Los documentos se validaron correctamente");
          this.editable = false
          
          this.val_docs.valid =res.data.every(doc => doc.pivot.is_valid===true)
        }
      } catch (e) {
        console.log(e);
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

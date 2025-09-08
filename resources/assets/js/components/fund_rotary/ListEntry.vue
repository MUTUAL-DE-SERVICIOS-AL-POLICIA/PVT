<template>
  <v-container fluid>
    <ValidationObserver>
      <v-form>
        <v-card flat>
          <v-card-title class="pa-0 pb-3">
            <v-toolbar dense color="tertiary" class="font-weight-regular">
              <v-toolbar-title>FONDO ROTATORIO</v-toolbar-title>
            </v-toolbar>
          </v-card-title>
          <!--Buscador por fecha-->
          <v-row align="center" no-gutters class="ma-0 pa-0">
            <v-col cols="3" class="pa-2">
              <v-text-field
                dense
                v-model="initial_date"
                label="Desde fecha"
                hint="Día/Mes/Año"
                type="date"
                :max="$moment(Date.now()).format('YYYY-MM-DD')"
                outlined
                clearable
              ></v-text-field>
            </v-col>

            <v-col cols="3" class="pa-2">
              <v-text-field
                dense
                v-model="final_date"
                label="Hasta fecha"
                hint="Día/Mes/Año"
                type="date"
                :min="initial_date"
                :max="$moment(Date.now()).format('YYYY-MM-DD')"
                outlined
                clearable
              ></v-text-field>
            </v-col>

            <v-col cols="1" class="pa-0">
              <v-btn
                fab
                color="info"
                x-small
                @click.stop="getFundRotary()"
                :loading="loading"
                style="margin-top: -30px"
              >
              <v-icon>mdi-magnify</v-icon>
              </v-btn>
            </v-col>

            <v-col cols="2" class="pa-0">
            </v-col>
            <!--Resultados-->
            <v-col cols="3" class="pa-0 caption" style="margin-top: -30px">
              <v-card
                outlined
                style="cursor: pointer;border: thin solid rgba(0, 0, 0, 0.5);"
                elevation="2">
              <div class="teal--text font-weight-bold  text-center py-1">
                <strong>TOTAL INGRESOS BS.:</strong> {{fund_rotatory_totals.total_entry_amount | money}}<br>
                <strong>TOTAL SALIDAS BS.:</strong> {{fund_rotatory_totals.total_output_amount | money}}<br>
                <strong>SALDO FINAL BS.:</strong> {{fund_rotatory_totals.final_balance | money}}
              </div>
              </v-card>
            </v-col>
          </v-row>
          <!--Datatble-->
          <v-data-table
            :headers="headers"
            :items="fund_rotatory_list"
            :loading="loading"
            :options.sync="options"
            :server-items-length="total_items"
            :footer-props="{ itemsPerPageOptions: [8, 15, 30] }"
            multi-sort
            :key="refreshFoundRotatoryTable"
            dense
            :item-class="itemRowBackground"
            class="ma-0 pa-0"
          >
            <!--Botones superiores-->
            <template v-slot:top>
              <v-tooltip top>
                <template v-slot:activator="{ on }">
                  <v-btn
                    fab
                    x-small
                    color="warning"
                    dark
                    v-on="on"
                    right
                    absolute
                    style="margin-top: -135px; margin-right:120px"
                    @click="getFundRotary()"
                  >
                    <v-icon>mdi-refresh</v-icon>
                  </v-btn>
                </template>
                <span>Recargar información</span>
              </v-tooltip>

              <v-dialog v-model="dialog" max-width="600px">
                <template v-slot:activator="{ on }" v-if="permissionSimpleSelected.includes('create-entry-fund-rotatory')">
                  <v-btn
                    fab
                    x-small
                    color="success"
                    dark
                    v-on="on"
                    right
                    absolute
                    style="margin-top: -135px; margin-right:80px"
                  >
                    <v-icon>mdi-plus</v-icon>
                  </v-btn>
                </template>

                <v-card>
                  <v-card-title>
                    <span class="text-h5">Registro</span>
                  </v-card-title>

                  <v-card-text>
                    <v-container>
                      <v-row>
                        <v-col cols="12" sm="12" md="12">
                          <v-text-field
                            v-model="fund_rotatory_item.date_check_delivery"
                            label="Fecha de entrega cheque"
                            type="date"
                          ></v-text-field>
                        </v-col>
                        <v-col cols="12" sm="12" md="12">
                          <v-text-field
                            v-model="fund_rotatory_item.entry_amount"
                            label="Monto"
                          ></v-text-field>
                        </v-col>
                        <v-col cols="12" sm="12" md="12">
                          <v-text-field
                            v-model="fund_rotatory_item.description"
                            label="Concepto"
                          ></v-text-field>
                        </v-col>
                      </v-row>
                    </v-container>
                  </v-card-text>

                  <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="error" text @click="close()">
                      CANCELAR
                    </v-btn>
                    <v-btn color="success" text @click="saveFundRotary()">
                      GUARDAR
                    </v-btn>
                  </v-card-actions>
                </v-card>
              </v-dialog>

              <v-tooltip top>
                <template v-slot:activator="{ on }">
                  <v-btn
                    fab
                    @click="download_report()"
                    color="error"
                    v-on="on"
                    x-small
                    absolute
                    right
                    style="margin-top: -135px; margin-right:40px"
                    :loading="loading_button" 
                  >
                    <v-icon> mdi-file-pdf</v-icon>
                  </v-btn>
                </template>
                <span class="caption">Descargar reporte</span>
              </v-tooltip>

              <v-dialog v-model="dialog_closing_movements" max-width="600px">
                <template v-slot:activator="{ on }" v-if="permissionSimpleSelected.includes('closing-movement-fund-rotatory')">
                  <v-btn
                    fab
                    x-small
                    color="info"
                    dark
                    v-on="on"
                    right
                    absolute
                    style="margin-top: -135px"
                  >
                    <v-icon>mdi-calendar-remove-outline</v-icon>
                  </v-btn>
                </template>

                <v-card>
                  <v-card-title>
                    <span class="text-h5">Cierre de Gestión</span>
                  </v-card-title>

                  <v-card-text>
                    <v-container>
                      <v-row>
                        <v-col cols="12" sm="12" md="12">
                          <v-text-field
                            v-model="fund_rotatory_item.description_close"
                            label="Concepto"
                          ></v-text-field>
                        </v-col>
                      </v-row>
                    </v-container>
                  </v-card-text>

                  <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="error" text @click="close_closing_movements()">
                      CANCELAR
                    </v-btn>
                    <v-btn color="success" text @click="closing_movements()">
                      GUARDAR
                    </v-btn>
                  </v-card-actions>
                </v-card>
              </v-dialog>

            </template>
            <!--Encabezados-->
            <template v-slot:[`item.date_check_delivery`]="{ item }">
              {{ item.date_check_delivery | date}}
            </template>
            <template v-slot:[`item.created_at`]="{ item }">
              {{ item.created_at | datetimeshorted}}
            </template>
            <template v-slot:[`item.movement_concept`]="{ item }">
              <v-tooltip top>
                <template v-slot:activator="{ on }">
                  <span v-on="on">{{item.movement_concept.name}}  - {{item.description | uppercase}}</span>
                </template>
                <span>{{ item.movement_concept.description}}</span>
              </v-tooltip>
            </template>
            <template v-slot:[`item.entry_amount`]="{ item }">
              {{ item.entry_amount | money}}
            </template>
            <template v-slot:[`item.output_amount`]="{ item }">
              {{ item.output_amount | money}}
            </template>
            <template v-slot:[`item.balance`]="{ item }">
              <span :class="item.balance < min_amount_fund_rotary && item.is_last ? 'warning black--text font-weight-bold' : ''">
              {{ item.balance | money}}
              </span>
            </template>
            <template v-slot:[`item.actions`]="{ item }">
              <v-tooltip bottom v-if="permissionSimpleSelected.includes('print-disbursement-receipt')">
                <template v-slot:activator="{ on }">
                  <v-btn
                    v-if="item.type_movement_fund_rotatory == 'EGRESO' &&  item.movement_concept.name != 'CIERRE DE FONDO ROTATORIO'"
                    icon
                    x-small
                    v-on="on"
                    color="primary"
                    @click="printReceipt(item.loan_id)"
                  ><v-icon>mdi-printer</v-icon>
                  </v-btn>
                </template>
                <span>Imnprimir recibo de pago</span>
              </v-tooltip>
            <!--Acciones registros-->
              <v-tooltip bottom v-if="permissionSimpleSelected.includes('update-movement-fund-rotatory')">
                <template v-slot:activator="{ on }">
                  <v-btn
                    v-if="item.is_last==true && item.type_movement_fund_rotatory == 'INGRESO'"
                    icon
                    small
                    v-on="on"
                    color="warning"
                    @click="editItem(item)"
                  ><v-icon>mdi-pencil</v-icon>
                  </v-btn>
                </template>
                <span>Editar registro </span>
              </v-tooltip>

              <!-- <v-tooltip bottom v-if="permissionSimpleSelected.includes('delete-movement-fund-rotatory')">
                <template v-slot:activator="{ on }">
                  <v-btn
                    v-if="item.is_last==true"
                    icon
                    small
                    v-on="on"
                    color="error"
                    @click.stop="bus.$emit('openRemoveDialog', `delete_movement/${item.id}`)"
                  ><v-icon>mdi-delete</v-icon>
                  </v-btn>
                </template>
                <span>Anular registro</span>
              </v-tooltip> -->

            </template>
          </v-data-table>
          <RemoveItem :bus="bus" />
        </v-card>
      </v-form>
    </ValidationObserver>
  </v-container>
</template>

<script>
import RemoveItem from "@/components/shared/RemoveItem";
export default {
  name: "fund_rotatory_entry-list",
  components: {
    RemoveItem
  },
  data: () => ({
    bus: new Vue(),
    loading: true,
    loading_button: false,
    options: {
      page: 1,
      itemsPerPage: 8,
      //sortBy: ["code_entry"],
      //sortDesc: [false],
    },
    fund_rotatory_list: [],
    fund_rotatory_totals: [],
    total_items: 0,
    headers: [
      {
        text: "Nro",
        value: "id",
        class: ["normal", "white--text"],
        width: "5%",
        sortable: true,
      },
            {
        text: "Fecha-Hora registro",
        value: "created_at",
        class: ["normal", "white--text"],
        width: "10%",
        sortable: false,
      },
      {
        text: "Fecha entrega cheque",
        value: "date_check_delivery",
        class: ["normal", "white--text"],
        width: "5%",
        sortable: false,
      },
      {
        text: "Documento",
        value: "movement_concept_code",
        class: ["normal", "white--text"],
        width: "10%",
        sortable: false,
      },
      {
        text: "Concepto",
        value: "movement_concept",
        class: ["normal", "white--text"],
        width: "30%",
        sortable: false,
      },
      {
        text: "Ingreso [Bs]",
        value: "entry_amount",
        class: ["normal", "white--text"],
        width: "10%",
        sortable: false,
      },
      {
        text: "Salida [Bs]",
        value: "output_amount",
        class: ["normal", "white--text"],
        width: "10%",
        sortable: false,
      },
      {
        text: "Saldo [Bs]",
        value: "balance",
        class: ["normal", "white--text"],
        width: "10%",
        sortable: false,
      },
      {
        text: "Acción",
        value: "actions",
        class: ["normal", "white--text"],
        sortable: false,
        width: "20%",
        sortable: false,
      },
    ],
 
    fund_rotatory_item: {},
    dialog: false,
    dialog_closing_movements: false,
    defaultItem: {},
    refreshFoundRotatoryTable: 0,
    editedIndex: -1,
    initial_date: null,
    final_date: null,
    min_amount_fund_rotary: null
  }),
  computed: {
    //Metodo para obtener Permisos por rol
    permissionSimpleSelected() {
      return this.$store.getters.permissionSimpleSelected;
    },
  },
  watch: {
    options: function (newVal, oldVal) {
      if (
        newVal.page != oldVal.page ||
        newVal.itemsPerPage != oldVal.itemsPerPage
      ) {
        this.getFundRotary();
      }
    },
  },
  mounted() {
    this.initial_date=this.$moment(Date.now()).format('YYYY-MM-DD')
    this.final_date=this.$moment(Date.now()).format('YYYY-MM-DD')
    this.getGlobalParameters()
    this.bus.$on("removed", (val) => {
      this.getFundRotary();
    });
    this.getFundRotary();
  },
  methods: {
    async getFundRotary() {
      try {
        this.loading = true;
        let res = await axios.get(`list_movements_fund_rotatory` , {
          params: {
            initial_date: this.initial_date,
            final_date: this.final_date,
            page: this.options.page,
            per_page: this.options.itemsPerPage,
          },
        }
        )
        this.fund_rotatory_list = res.data.movement_concepts.data;
        this.fund_rotatory_totals = res.data
        console.log(this.fund_rotatory_list);
        this.total_items = res.data.movement_concepts.total;
        delete res.data["data"];
        this.options.page = res.data.movement_concepts.current_page;
        this.options.itemsPerPage = parseInt(res.data.movement_concepts.per_page);
        this.totalItems = res.data.movement_concepts.total;
        this.refreshFoundRotatoryTable++
      } catch (e) {
        console.log(e);
      } finally {
        this.loading = false;
      }
    },
      async download_report(){
        try {
          this.loading_button = true
          let res = await axios.get(`list_movements_fund_rotatory`, {
            params: {
              initial_date: this.initial_date,
              final_date: this.final_date,
              pdf: true
            },
          });
          printJS({
            printable: res.data.content,
            type: res.data.type,
            file_name: res.data.file_name,
            base64: true,
          });
          this.loading_button = false
        } catch (e) {
          this.loading_button = false
          this.toastr.error("Ocurrió un error en la impresión, seleecione los criterios de búsqueda.");
          console.log(e);
        }
      },

    async saveFundRotary() {
      try {
        if (this.fund_rotatory_item.id) {
          let res = await axios.patch(`movements/${this.fund_rotatory_item.id}`,{
              date_check_delivery: this.$moment(this.fund_rotatory_item.date_check_delivery).format("YYYY-MM-DD"),
              entry_amount: this.fund_rotatory_item.entry_amount,
              description: this.fund_rotatory_item.description,
              role_id: this.$store.getters.rolePermissionSelected.id,
            }
          );
        }else {
          let res = await axios.post(`movement_fund_rotatory_entry/store_input`, {
            date_check_delivery: this.fund_rotatory_item.date_check_delivery,
            entry_amount: this.fund_rotatory_item.entry_amount,
            description: this.fund_rotatory_item.description,
            movement_concept_id: '2',
            role_id: this.$store.getters.rolePermissionSelected.id,
          });
        }

        this.close();
        this.getFundRotary();
        this.toastr.success('Registro guardado correctamente.')
      } catch (e) {
        console.log(e);
        this.toastr.error('Ocurrio un error.')
      } finally {
        this.loading = false;
      }
    },
    editItem(item) {
      console.log(item)
      this.fund_rotatory_item = item;
      this.fund_rotatory_item.date_check_delivery= this.$moment(this.fund_rotatory_item.date_check_delivery).format('YYYY-MM-DD')
      console.log("edit");
      console.log(this.fund_rotatory_item);
      this.dialog = true;
    },

    deleteItem(item) {
      const index = this.fund_rotatory_item.indexOf(item);
      confirm("Esta seguro que?") && this.fund_rotatory_item.splice(index, 1);
    },

    close() {
      this.dialog = false;
      this.$nextTick(() => {
        this.fund_rotatory_item = Object.assign({}, this.defaultItem);
        this.editedIndex = -1;
      });
    },
    close_closing_movements() {
      this.dialog_closing_movements = false;
      this.$nextTick(() => {
        this.fund_rotatory_item = Object.assign({}, this.defaultItem);
        this.editedIndex = -1;
      });
    },
        async printReceipt(item) {
      try {
          let res = await axios.get(`print_fund_rotary_output/${item}`)
            printJS({
              printable: res.data.content,
              type: res.data.type,
              file_name: res.data.file_name,
              base64: true
            })
      } catch (e) {
        this.toastr.error("Ocurrió un error en la impresión.")
        console.log(e)
      }
    },
    async closing_movements(){
      try {
          let res = await axios.post(`closing_movements`,{
              description: this.fund_rotatory_item.description_close,
              role_id: this.$store.getters.rolePermissionSelected.id,
            }
          );
        this.close_closing_movements();
        this.toastr.success('Registro guardado correctamente.')
        this.getFundRotary();
      } catch (e) {
        console.log(e);
        this.toastr.error('Ocurrio un error en el guardado.')
      } finally {
        this.loading = false;
      }
    },
    itemRowBackground: function (item) {
      if(item.type_movement_fund_rotatory == 'INGRESO'){
        return 'style-5'
      }
      else if(item.movement_concept.name == 'CIERRE DE FONDO ROTATORIO'){
        return 'style-6'
      }else{
        return 'style-3'
      }
    },
    async getGlobalParameters(){
      try {
        let res = await axios.get(`loan_global_parameter`)
        let global_parameters = res.data.data[0]
        this.min_amount_fund_rotary = global_parameters.min_amount_fund_rotary
      } catch (e) {
        console.log(e)
      }
    },
  },
}
</script>
<style>
.style-5 {
  background-color: #B2EBF2
}
.style-6 {
  background-color: #26C6DA
}
</style>


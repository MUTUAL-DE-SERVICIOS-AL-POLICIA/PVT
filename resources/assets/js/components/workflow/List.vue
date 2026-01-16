<template>
  <v-data-table
    dense
    v-model="selectedLoans"
    :headers="headers"
    :items="loans"
    :loading="loading"
    :options="options"
    :server-items-length="totalLoans"
    :footer-props="{ itemsPerPageOptions: [8, 15, 30] }"
    :item-class="itemRowBackground"
    multi-sort
    :show-select="tray == 'validated'"
    @update:options="updateOptions"
  >
    <template v-slot:[`header.data-table-select`]="{ on, props }">
      <v-simple-checkbox color="info" class="grey lighten-3" v-bind="props" v-on="on"></v-simple-checkbox>
    </template>
    <template v-slot:[`item.data-table-select`]="{ isSelected, select }">
      <v-simple-checkbox color="success" :value="isSelected" @input="select($event)"></v-simple-checkbox>
    </template>
    <template v-slot:[`item.borrower[0].identity_card`]="{ item }">
      {{ item.borrower[0] ? item.borrower[0].identity_card  : ''}}
    </template>
    <template v-slot:[`item.borrower`]="{ item }">
      {{ item.borrower[0] ? $options.filters.fullName(item.borrower[0], true) : '' }}
    </template>
    <template v-slot:[`item.role_id`]="{ item }">
      {{ $store.getters.roles.find(o => o.id == item.role_id).display_name }}
    </template>
    <template v-slot:[`item.procedure_modality_id`]="{ item }">
      <v-tooltip top>
        <template v-slot:activator="{ on }">
          <span v-on="on">{{ searchProcedureModality(item, 'shortened') }}</span>
        </template>
        <span>{{ searchProcedureModality(item, 'name') }}</span>
      </v-tooltip>
    </template>
    <template v-slot:[`item.request_date`]="{ item }">
      {{ item.request_date | date }}
    </template>
    <template v-slot:[`item.amount_approved`]="{ item }">
      {{ item.amount_approved | money }}
    </template>
    <template v-slot:[`item.estimated_quota`]="{ item }">
      {{ item.estimated_quota | money}}
    </template>
    <template v-slot:[`item.user`]="{ item }">
      {{ item.user }}
    </template>
    <template v-slot:[`item.actions`]="{ item }">
      <v-tooltip bottom>
        <template v-slot:activator="{ on }">
          <v-btn
            icon
            small
            v-on="on"
            color="warning"
            :to="tray != 'all'? { name: 'flowAdd', params: { id: item.id }} : { name: 'flowAdd', params: { id: item.id }, query: { workTray: tray}}"
          ><v-icon>mdi-eye</v-icon>
          </v-btn>
        </template>
        <span>{{tray != 'all'? 'Revisar trámite' : 'Ver trámite'}}</span>
      </v-tooltip>

      <v-tooltip bottom v-if="permissionSimpleSelected.includes('release-loan-user')">
        <template v-slot:activator="{ on }" v-if="item.user_id != null">
          <v-btn
            icon
            small
            v-on="on"
            color="error"
            @click.stop="freeLoan(item.id, item.code)"
          >
            <v-icon>mdi-lock-open-variant</v-icon>
          </v-btn>
        </template>
        <span>Liberar usuario del trámite</span>
      </v-tooltip>

      <v-menu
        offset-y
        close-on-content-click
        v-if="permissionSimpleSelected.includes('print-contract-loan') || permissionSimpleSelected.includes('print-payment-plan') || permissionSimpleSelected.includes('print-payment-kardex-loan') "
      >
        <template v-slot:activator="{ on }">
          <v-btn
            icon
            color="primary"
            dark
            v-on="on"
          ><v-icon>mdi-printer</v-icon>
          </v-btn>
        </template>
        <v-list dense class="py-0">
          <v-list-item
            v-for="doc in printDocs"
            :key="doc.id"
            @click="imprimir(doc.id, item)"
          >
            <v-list-item-icon class="ma-0 py-0 pt-2">
              <v-icon 
                class="ma-0 py-0"
                small
                color="light-blue accent-4"
              >{{doc.icon}}</v-icon>
            </v-list-item-icon>
            <v-list-item-title 
              class="ma-0 py-0 mt-n2">
              {{ doc.title }}
            </v-list-item-title>
          </v-list-item>
        </v-list>
      </v-menu>
    </template>
  </v-data-table>
</template>

<script>
export default {
  name: 'workflow-list',
  props: {
    bus: {
      type: Object,
      required: true
    },
    tray: {
      type: String,
      default: 'received'
    },
    options: {
      type: Object,
      default: {
        itemsPerPage: 8,
        page: 1,
        sortBy: ['request_date'],
        sortDesc: [true]
      }
    },
    loans: {
      type: Array,
      required: true
    },
    totalLoans: {
      type: Number,
      required: true
    },
    loading: {
      type: Boolean,
      required: true
    },
    procedureModalities: {
      type: Array,
      required: true
    },
    workflowSelected: {
      type: Number,
      required: true,
      default: 0
    }
  },
  computed: {
    //permisos del selector global por rol
    permissionSimpleSelected() {
      return this.$store.getters.permissionSimpleSelected
    },
    fullname(item) {
      return this.$options.filters.fullName(item, true)
    }
  },
  data: () => ({
    selectedLoans: [],
    headers: [
      {
        text: 'Código',
        value: 'code',
        class: ['normal', 'white--text'],
        align: 'center',
        sortable: true
      }, {
        text: 'CI',
        value: 'borrower[0].identity_card',
        class: ['normal', 'white--text'],
        align: 'center',
        sortable: true
      }, {
        text: 'Nombre',
        value: 'borrower',
        class: ['normal', 'white--text'],
        align: 'center',
        sortable: true
      }, {
        text: 'Modalidad',
        class: ['normal', 'white--text'],
        align: 'center',
        value: 'procedure_modality_id',
        sortable: true
      }, {
        text: 'Fecha solicitud',
        value: 'request_date',
        class: ['normal', 'white--text'],
        align: 'center',
        sortable: true
      }, {
        text: 'Monto aprobado [Bs]',
        value: 'amount_approved',
        class: ['normal', 'white--text'],
        align: 'center',
        sortable: true
      }, {
        text: 'Plazo [Meses]',
        value: 'loan_term',
        class: ['normal', 'white--text'],
        align: 'center',
        sortable: false
      }, {
        text: 'Cuota [Bs]',
        value: 'estimated_quota',
        class: ['normal', 'white--text'],
        align: 'center',
        sortable: false
      },{
        text: 'Acciones',
        value: 'actions',
        class: ['normal', 'white--text'],
        align: 'center',
        sortable: false
      }
    ],
        printDocs: []
  }),
  watch: {
    workflowSelected(newVal, oldVal) {
      if (newVal != oldVal) 
        this.selectedLoans = []
    },
    selectedLoans(val) {
      this.bus.$emit('selectLoans', this.selectedLoans)
      this.$emit('allowFlow', val.length > 0)
    },
    tray(val) {
      if (typeof val === 'string') this.updateHeader()
    }
  },
  mounted() {
    this.bus.$on('emitRefreshLoans', () => {
      this.selectedLoans = []
    })
    this.docsLoans()
  },
  methods: {
    itemRowBackground(item) {
      if (item.validated && item.user_id != null) 
        return 'style-1'
      else if (!item.validated && item.user_id != null) 
        return 'style-2'
      else
        return 'style-3'
    },
    searchProcedureModality(item, attribute = null) {
      let procedureModality = this.procedureModalities.find(o => o.id == item.procedure_modality_id)
      return procedureModality ? (attribute ? procedureModality[attribute] : procedureModality) : null
    },
    updateOptions($event) {
      if (this.options.page != $event.page || this.options.itemsPerPage != $event.itemsPerPage || this.options.sortBy != $event.sortBy || this.options.sortDesc != $event.sortDesc) {
        this.$emit('update:options', $event);
      }
    },
    async imprimir(id, item) {
      try {
        let res
        if(id==1){
          res = await axios.get(`loan/${item.id}/print/contract`)
        }else if(id==2){
          res = await axios.get(`loan/${item.id}/print/form`)
        }else if(id==3){
          if(item.disbursement_date != null)
            res = await axios.get(`loan/${item.id}/print/plan`)
          else
            this.toastr.error("El préstamo no se encuentra desembolsado.")
        }else if(id==4){
          res = await axios.get(`loan/${item.id}/print/process_form`)
        }
        printJS({
          printable: res.data.content,
          type: res.data.type,
          documentTitle: res.data.file_name,
          base64: true
        })
      } catch (e) {
        this.toastr.error("Ocurrió un error en la impresión.")
        console.error(e)
      }
    },
    updateHeader() {
      if (this.tray !== 'all') {
        this.headers = this.headers.filter(o => o.value !== 'role_id')
      } else if (!this.headers.some(o => o.value === 'role_id')) {
        this.headers.unshift({
          text: 'Área',
          class: ['normal', 'white--text'],
          align: 'center',
          value: 'role_id',
          sortable: true
        })
      }
    },
    docsLoans() {
      try {
        let docs = []
          if (this.permissionSimpleSelected.includes('print-contract-loan')) {
            docs.push(
              { id: 1, title: 'Contrato', icon: 'mdi-file-document' },
              { id: 2, title: 'Solicitud', icon: 'mdi-file' }
            )
          }
          if (this.permissionSimpleSelected.includes('print-payment-plan')) {
            docs.push({ id: 3, title: 'Plan de pagos', icon: 'mdi-cash' })
          }

          docs.push({ id: 4, title: "Hoja de Trámite", icon: "mdi-text-box-multiple-outline" })
      
      this.printDocs = docs        
      } catch (e) {
        this.toastr.error("Se ha producido un error durante la generación de la impresión")
        console.error(e)
      }
    },
    async freeLoan(id, code) {
      try {
        await axios.patch(`loan/${id}`, {
          user_id: null,
          validated: false
        });
        this.bus.$emit('emitRefreshLoans');
        this.toastr.success(`El trámite ${code} fue liberado`);
      } catch (e) {
        console.error(e);
        this.toastr.error("Ocurrió un error en la liberación del trámite...");
      }
    },
  }
}
</script>

<style>
th.text-start {
  background-color: #757575
}
.style-1 {
  background-color: #8BC34A
}
.style-2 {
  background-color: yellow
}
</style>
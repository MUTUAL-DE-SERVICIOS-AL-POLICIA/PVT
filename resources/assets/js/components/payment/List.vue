<template>
<div>
  <v-data-table
    v-model="selectedLoans"
    :headers="headers"
    :items="loans"
    :loading="loading"
    :options="options"
    :server-items-length="totalLoans"
    :footer-props="{ itemsPerPageOptions: [8, 15, 30] }"
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

    <!--<template v-slot:item.procedure_modality_id="{ item }">
      <v-tooltip top>
        <template v-slot:activator="{ on }">
          <span v-on="on">{{ searchProcedureModality(item, 'shortened') }}</span>
        </template>
        <span>{{ searchProcedureModality(item, 'name') }}</span>
      </v-tooltip>
    </template>-->
    <template v-slot:[`item.affiliate`]="{ item }">
      {{ $options.filters.fullName(item.affiliate, true) }}
    </template>

    <template v-slot:[`item.role_id`]="{ item }">
      {{ $store.getters.roles.find(o => o.id == item.role_id).display_name }}
    </template>

    <template v-slot:[`item.estimated_date`]="{ item }">
      {{ item.estimated_date | date }}
    </template>

    <template v-slot:[`item.estimated_quota`]="{ item }">
      {{ item.estimated_quota | moneyString }}
    </template>

    <template v-slot:[`item.capital_payment`]="{ item }">
      {{ item.capital_payment | moneyString }}
    </template>

    <template v-slot:[`item.interest_payment`]="{ item }">
      {{ item.interest_payment | moneyString }}
    </template>

    <template v-slot:[`item.penal_payment`]="{ item }">
      {{ item.penal_payment | moneyString }}
    </template>

      <template v-slot:[`item.actions`]="{ item }">
        <v-tooltip bottom>
          <template v-slot:activator="{ on }">
            <v-btn
              icon
              small
              v-on="on"
              color="warning"
              :to="{ name: 'paymentAdd',  params: { hash: 'view'},  query: { loan_payment: item.id}}"
            >
              <v-icon>mdi-eye</v-icon>
            </v-btn>
          </template>
          <span>Ver registro de cobro</span>
        </v-tooltip>

        <v-tooltip bottom v-if="permissionSimpleSelected.includes('create-payment-loan')">
          <template v-slot:activator="{ on }">
            <v-btn
              icon
              small
              v-on="on"
              v-if="item.state.name!='Pagado'"
              color="success"
              :to="{ name: 'paymentAdd',  params: { hash: 'edit'},  query: { loan_payment: item.id}}"
            >
              <v-icon>mdi-file-document-edit-outline</v-icon>
            </v-btn>
          </template>
          <span>Validar pago</span>
        </v-tooltip>

        <v-tooltip bottom v-if="permissionSimpleSelected.includes('create-payment')">
          <template v-slot:activator="{ on }">
            <v-btn
              icon
              small
              v-on="on"
              v-if="item.state.name!='Pagado'"
              color="light-blue accent-4"
              :to="{ name: 'paymentAdd',  params: { hash: 'edit'},  query: { loan_payment: item.id}}"
            >
              <v-icon>mdi-file-document-edit-outline</v-icon>
            </v-btn>
          </template>
          <span>Registrar pago</span>
        </v-tooltip>

        <v-tooltip bottom v-if="permissionSimpleSelected.includes('delete-payment-loan')">
          <template v-slot:activator="{ on }">
            <v-btn
              icon
              small
              v-on="on"
              color="error"
              v-if="item.state.name == 'Pendiente de ajuste' || item.state.name == 'Pendiente de Pago' "
              @click.stop="bus.$emit('openRemoveDialog', `loan_payment/${item.id}`)"
            >
              <v-icon>mdi-file-cancel-outline</v-icon>
            </v-btn>
          </template>
          <span>Anular registro de cobro</span>
        </v-tooltip>

        <v-menu offset-y close-on-content-click>
          <template v-slot:activator="{ on }">
            <v-btn icon color="primary" dark v-on="on">
              <v-icon>mdi-printer</v-icon>
            </v-btn>
          </template>
          <v-list dense class="py-0">
            <v-list-item v-for="doc in printDocs" :key="doc.id" @click="imprimir(doc.id, item.id)">
              <v-list-item-icon class="ma-0 py-0 pt-2">
                <v-icon class="ma-0 py-0" small v-text="doc.icon" color="light-blue accent-4"></v-icon>
              </v-list-item-icon>
              <v-list-item-title class="ma-0 py-0 mt-n2">{{ doc.title }}</v-list-item-title>
            </v-list-item>
          </v-list>
        </v-menu>
        <template></template>
      </template>
    </v-data-table>
    <RemoveItem :bus="bus"/>
  </div>
</template>

<script>

import RemoveItem from '@/components/shared/RemoveItem'
export default {
  name: 'payment-list',
    components:{
    RemoveItem
  },
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
    procedureTypeSelected:{
      type:Number,
      required: true,
      default: 0
    }
  },
  computed: {
      //Metodo para obtener Permisos por rol
      permissionSimpleSelected () {
        return this.$store.getters.permissionSimpleSelected
      }
  },
  data: () => ({
    selectedLoans: [],
    headers: [
      {
        text: 'Nro recibo',
        value: 'code',
        class: ['normal', 'white--text'],
        align: 'center',
        sortable: true
      },
      {
        text: 'Préstamo',
        value: 'loan.code',
        class: ['normal', 'white--text'],
        align: 'center',
        sortable: true
      },
           {
        text: 'Nombre',
        value: 'affiliate',
        class: ['normal', 'white--text'],
        align: 'center',
        sortable: true
      },
      {
        text: 'Fecha de pago',
        value: 'estimated_date',
        class: ['normal', 'white--text'],
        align: 'center',
        sortable: true
      }, {
        text: 'Nro de cuota',
        value: 'quota_number',
        class: ['normal', 'white--text'],
        align: 'center',
        sortable: false
      }, {
        text: 'Cuota [Bs]',
        value: 'estimated_quota',
        class: ['normal', 'white--text'],
        align: 'center',
        sortable: false
      }, {
        text: 'Interes [Bs]',
        value: 'interest_payment',
        class: ['normal', 'white--text'],
        align: 'center',
        sortable: false
      }, {
        text: 'Interes penal [Bs]',
        value: 'penal_payment',
        class: ['normal', 'white--text'],
        align: 'center',
        sortable: false
      }, {
        text: 'Capital pagado [Bs]',
        value: 'capital_payment',
        class: ['normal', 'white--text'],
        align: 'center',
        sortable: false
      }, {
        text: 'Estado',
        value: 'state.name',
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
    procedureTypeSelected(newVal, oldVal) {
      if(newVal != oldVal)
        this.selectedLoans = []
    },
    selectedLoans(val) {
      this.bus.$emit('selectLoans', this.selectedLoans)
      if (val.length) {
        this.$emit('allowFlow', true)
      } else {
        this.$emit('allowFlow', false)
      }
    },
    tray(val) {
      if (typeof val === 'string') this.updateHeader()
    }
  },

  mounted() {
    this.bus.$on('emitRefreshLoans', val => {
      this.selectedLoans = []
    }),
    this.docsLoans()
  },
  methods: {
    searchProcedureModality(item, attribute = null) {
      let procedureModality = this.procedureModalities.find(o => o.id == item.procedure_modality_id)
      if (procedureModality) {
        if (attribute) {
          return procedureModality[attribute]
        } else {
          return procedureModality
        }
      } else {
        return null
      }
    },
    updateOptions($event) {
      if (this.options.page != $event.page || this.options.itemsPerPage != $event.itemsPerPage || this.options.sortBy != $event.sortBy || this.options.sortDesc != $event.sortDesc) this.$emit('update:options', $event)
    },
    async imprimir(id, item)
    {
      try {
        let res;
        if (id == 5) {
          res = await axios.get(`loan_payment/${item}/print/loan_payment`);
        } else if(id == 6){
          let resv = await axios.get(`loan_payment/${item}/voucher`)
          let idVoucher = resv.data.id
          res = await axios.get(`voucher/${idVoucher}/print/voucher`);
        }
        printJS({
            printable: res.data.content,
            type: res.data.type,
            documentTitle: res.data.file_name,
            base64: true
        })  
      } catch (e) {
        this.toastr.error("Ocurrió un error en la impresión.")
        console.log(e)
      }      
    },
    updateHeader() {
      if (this.tray != 'all') {
        this.headers = this.headers.filter(o => o.value != 'role_id')
        this.headers = this.headers.filter(o => o.value != 'procedure_modality_id')
      } else {
        if (!this.headers.some(o => o.value == 'role_id')) {
         /* this.headers.unshift({
            text: 'Modalidad',
            class: ['normal', 'white--text'],
            align: 'center',
            value: 'procedure_modality_id',
            sortable: true
          })*/
          this.headers.unshift({
            text: 'Área',
            class: ['normal', 'white--text'],
            align: 'center',
            value: 'role_id',
            sortable: true
          })
        }
      }
    },
    docsLoans() {
      let docs = [];
      if (this.permissionSimpleSelected.includes("print-payment-loan")) {
        docs.push({ id: 5, title: "Registro de cobro", icon: "mdi-file-check-outline" });
      }
      if (this.permissionSimpleSelected.includes("print-payment-voucher")) {
        docs.push({ id: 6, title: "Registro de pago", icon: "mdi-cash-multiple" });
      } else {
        console.log("Se ha producido un error durante la generación de la impresión");
      }
      this.printDocs = docs;
      console.log(this.printDocs);
    }
  }
}
</script>
<style>
th.text-start {
  background-color: #757575;
}
</style>
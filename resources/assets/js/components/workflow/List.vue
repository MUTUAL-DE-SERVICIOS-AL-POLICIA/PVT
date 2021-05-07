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
    <template v-slot:[`item.lenders[0].identity_card`]="{ item }">
      {{ item.lenders[0] ? item.lenders[0].identity_card  : ''}}
    </template>
    <template v-slot:[`item.lenders`]="{ item }">
      {{ item.lenders[0] ? $options.filters.fullName(item.lenders[0], true) : '' }}
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
    <template v-slot:[`item.amount_requested`]="{ item }">
      {{ item.amount_requested | money }}
    </template>
    <template v-slot:[`item.balance`]="{ item }">
      {{ item.balance | money }}
    </template>
    <template v-slot:[`item.estimated_quota`]="{ item }">
      {{ item.estimated_quota }}
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
            @click="imprimir(doc.id, item.id)"
          >
            <v-list-item-icon class="ma-0 py-0 pt-2">
              <v-icon 
                class="ma-0 py-0"
                small
                v-text="doc.icon"
                color="light-blue accent-4"
              ></v-icon>
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
    procedureTypeSelected1:{
      type:Number,
      required: true,
      default: 0
    }
  },
  computed:{
      //permisos del selector global por rol
    permissionSimpleSelected () {
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
        text: 'Correlativo',
        value: 'code',
        class: ['normal', 'white--text'],
        align: 'center',
        sortable: true
      }, {
        text: 'CI',
        value: 'lenders[0].identity_card',
        class: ['normal', 'white--text'],
        align: 'center',
        sortable: true
      }, {
        text: 'Nombre',
        value: 'lenders',
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
        text: 'Solicitado [Bs]',
        value: 'amount_requested',
        class: ['normal', 'white--text'],
        align: 'center',
        sortable: true
      }, {
        text: 'Saldo capital [Bs]',
        value: 'balance',
        class: ['normal', 'white--text'],
        align: 'center',
        sortable: false
      }, {
        text: 'Meses Plazo',
        value: 'loan_term',
        class: ['normal', 'white--text'],
        align: 'center',
        sortable: true
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
    itemRowBackground: function (item) {
      if(item.validated === true && item.user_id != null){
        return 'style-1'
      }else if(item.validated === false && item.user_id != null){
        return 'style-2'
      }else{
        return 'style-3'
      }
    },
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
        let res
        if(id==1){
          res = await axios.get(`loan/${item}/print/contract`)
        }else if(id==2){
          res = await axios.get(`loan/${item}/print/form`)
        }else if(id==3) {
          res = await axios.get(`loan/${item}/print/plan`)
        }else {
          res = await axios.get(`loan/${item}/print/kardex`)
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
        //this.headers = this.headers.filter(o => o.value != 'procedure_modality_id')
      } else {
        if (!this.headers.some(o => o.value == 'role_id')) {
          /*this.headers.unshift({
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

      docsLoans(){
        let docs =[]    
        if(this.permissionSimpleSelected.includes('print-contract-loan')){
          docs.push(
            { id: 1, title: 'Contrato', icon: 'mdi-file-document'},
            { id: 2, title: 'Solicitud', icon: 'mdi-file'})
        }
        if(this.permissionSimpleSelected.includes('print-payment-plan')){
          docs.push(
            { id: 3, title: 'Plan de pagos', icon: 'mdi-cash'})
        }    
        if(this.permissionSimpleSelected.includes('print-payment-kardex-loan')){
          docs.push(
            { id: 4, title: 'Kardex', icon: 'mdi-view-list'})
        }else{
          console.log("Se ha producido un error durante la generación de la impresión")
        }
        this.printDocs=docs
        console.log(this.printDocs)
      },
      
    async freeLoan(id, code) {
      try {     
          //this.loading = true;
            let res = await axios.patch(`loan/${id}`, {
              user_id: null,
              validated: false
            });
            console.log(res)
            //this.sheet = false;
            this.bus.$emit('emitRefreshLoans');
            this.toastr.success("El trámite "+ code +" fue liberado" ) 
     
      } catch (e) {
        console.log(e)
        this.toastr.error("Ocurrió un error en la liberación del trámite...")
      }
    }


    } 
  }

</script>
<style>
th.text-start {
  background-color: #757575;
}
.style-1 {
  background-color: #8BC34A
}
.style-2 {
  background-color: yellow
}
</style>

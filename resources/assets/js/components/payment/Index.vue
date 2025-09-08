<template>
  <v-card flat>
    <v-card-title class="pb-0">
      <v-toolbar dense color="tertiary">
        <v-toolbar-title>
          <Breadcrumbs />
        </v-toolbar-title>
        <v-spacer></v-spacer>
        <v-btn-toggle
          v-model="filters.traySelected"
          active-class="primary white--text"
          mandatory
          v-if="!track"
        >
        <v-btn
            v-for="tray in trays"
            :key="tray.name"
            :value="tray.name"
          >
            <v-badge
              :content="tray.count.toString()"
              :color="tray.color"
              class="ml-2"
              :class="Number.isInteger(tray.count) ? 'mr-5' : 'mr-6'"
              right
              top
            >
              {{ tray.display_name }}
            </v-badge>
          </v-btn>
        </v-btn-toggle>

        <template v-if="permissionSimpleSelected.includes('show-deleted-payment')">
          <v-tooltip 
            top 
            v-if="track"
          >
            <template v-slot:activator="{ on }">
              <v-btn
                v-on="on"
                icon
                outlined
                small
                :color="trackNull ? 'brown' : 'error'"
                class="darken-2 ml-4"
                @click="nulledLoans()"
              >
                <v-icon>
                  {{ trackNull ? 'mdi-swap-horizontal' : 'mdi-file-cancel' }}
                </v-icon>
              </v-btn>
            </template>
            <span v-if="trackNull">Seguimiento de trámites</span>
            <span v-else>Trámites anulados</span>
          </v-tooltip>
        </template>

        <v-divider
          class="mx-2"
          inset
          vertical
        ></v-divider>
        <v-flex xs3>
          <v-text-field
            v-model="search"
            append-icon="mdi-magnify"
            label="Buscar Cód."
            single-line
            hide-details
            clearable
          ></v-text-field>
        </v-flex>
      </v-toolbar>
    </v-card-title>
    <v-tooltip 
      left 
      content-class="secondary"
    >
      <template v-slot:activator="{ on }">
        <v-btn
          fab
          dark
          fixed
          bottom
          right
          color="warning"
          class="mb-5"
          v-on="on"
          v-show="newLoans.length > 0"
          @click="clearNotification"
        >
          <v-badge
            :content="newLoans.length.toString()"
            right
            top
          >
            <v-icon>mdi-bell-ring</v-icon>
          </v-badge>
        </v-btn>
      </template>
      <v-list
        class="secondary"
        dense
        dark
      >
        <v-subheader>Ver trámites nuevos:</v-subheader>
        <v-list-item-group>
          <v-list-item 
            v-for="(loan, index) in newLoans.slice(0, newLoansMax)" 
            :key="loan.id"
          >
            <v-list-item-content>
              <v-list-item-title>{{ index }}</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
          <v-list-item v-if="newLoans.length > newLoansMax">
            <v-list-item-content>
              <v-list-item-title>{{ newLoans.length - newLoansMax }} más ...</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
        </v-list-item-group>
      </v-list>
    </v-tooltip>
    <v-card-text>
      <v-row v-if="!track">
        <v-toolbar flat>
          <v-col :cols="singleRol ? 12 : 12">
            <v-tabs
              v-model="filters.workflowSelected"
              dark
              grow
              center-active
              active-class="secondary"
            >
              <v-tab v-for="(workflowType, index) in $store.getters.amortizationLoan" :key="workflowType.id">
                <v-badge
                  :content="workflowTypesCount.hasOwnProperty(index) ? workflowTypesCount[index].toString() : '-'"
                  :color="workflowTypeClass(index)"
                  right
                  top
                >
                  {{ workflowType.name }}
                </v-badge>
              </v-tab>
            </v-tabs>
          </v-col>
          <v-col cols="2" v-show="false">
            <v-select
              :v-model="filters.roleSelected = this.$store.getters.rolePermissionSelected.id"
              :items="roles"
              label="Filtro"
              class="pt-3 my-0"
              item-text="display_name"
              item-value="id"
              dense
            ></v-select>
          </v-col>
          <Fab v-show="allowFlow" :bus="bus"/>
        </v-toolbar>
      </v-row>
      <v-row>
        <v-col cols="12">
          <List
            :bus="bus"
            :tray="filters.traySelected"
            :workflowSelected="parseInt(filters.workflowSelected)"
            :procedureModalities="procedureModalities"
            :options.sync="options"
            :loans="loans"
            :totalLoans="totalLoans"
            :loading="loading"
            @allowFlow="allowFlow = $event"
            :workflowTypesCount="workflowTypesCount"
          />
        </v-col>
      </v-row>
    </v-card-text>
  </v-card>
</template>

<script>
import Breadcrumbs from '@/components/shared/Breadcrumbs'
import List from '@/components/payment/List'
import Fab from '@/components/payment/Fab'

export default {
  name: "payment-index",
  components: {
    Breadcrumbs,
    Fab,
    List
  },
  data() {
    return {
      track: false,
      search: '',
      bus: new Vue(),
      newLoans: [],
      newLoansMax: 3,
      allowFlow: false,
      workflowTypesCount: [],
      trays: [
        {
          name: 'received',
          display_name: 'RECIBIDOS',
          count: 0,
          color: 'info'
        },{
          name: 'validated',
          display_name: 'VALIDADOS',
          count: 0,
          color: 'success'
        }
      ],
      filters: {
        traySelected: null,
        workflowSelected: null,
        roleSelected: null
      },
      params: {},
      roles: [],
      options: {
        itemsPerPage: 15,
        page: 1,
        sortBy: ['request_date'],
        sortDesc: [true]
      },
      loans: [],
      totalLoans: 0,
      loading: true,
      procedureModalities: [],
      trackNull: null
    }
  },
  computed: {
    //permisos del selector global por rol
    permissionSimpleSelected() {
      return this.$store.getters.permissionSimpleSelected
    },
    rolePermissionSelected() {
      return this.$store.getters.rolePermissionSelected
    },
    singleRol() {
      return this.roles.length <= 1
    },
    hasTray() {
      return this.procedureModalities.length && this.permissionSimpleSelected.includes('update-payment-loan') && this.permissionSimpleSelected.includes('show-all-payment-loan')
    }
  },
  beforeCreate() {
    this.$store.dispatch('selectModuleAmortization', 'prestamos').then(() => {
      this.getProcedureModalities()
      this.roles = this.$store.getters.wfStates
        .filter(o => o.module_id === this.$store.getters.module.id && this.$store.getters.userRoles.includes(o.name))
      if (this.roles.length > 0) this.filters.roleSelected = this.roles[0].id;
    });
  },
  beforeMount() {
    Echo.channel('loan').listen('.flow', (msg) => {
      if (msg.data.role_id === this.filters.roleSelected || this.filters.roleSelected === 0) {
        this.newLoans = msg.data.derived;
      }
    })
  },
  mounted() {
    this.filters.workflowSelected = this.$store.getters.amortizationLoan[0]
    this.workflowTypesCount = new Array(this.$store.getters.amortizationLoan.length).fill('-')
    this.bus.$on('emitRefreshLoans', () => {
      this.updateLoanList();
    })
  },
  watch: {
    search: _.debounce(function () {
      this.getLoans()
    }, 1000),
    filters: {
      deep: true,
      handler(val) {
        if (val.traySelected != null && val.workflowSelected != null && val.roleSelected != null) {
          let workflowType = this.$store.getters.amortizationLoan[this.filters.workflowSelected]
          if (workflowType) 
            this.setFilters(workflowType.id);
        }
      }
    },
    options: {
      deep: true,
      handler(val) {
        this.getLoans()
      }
    },
    track(val) {
      if (val) {
        this.filters.workflowSelected = null
        this.filters.roleSelected = 0
        this.filters.traySelected = 'all'
        this.search = ''
        this.params = {
          role_id: this.filters.roleSelected,
        }
        this.newLoans = []
        this.getLoans()
      } else {
        this.filters.workflowSelected = this.$store.getters.amortizationLoan[0]
        this.filters.roleSelected = this.roles[0].id
        this.clearNotification()
      }
    }
  },
  methods: {
    async getProcedureModalities() {
      for (const workflow of this.$store.getters.amortizationLoan) {
        try {
          let res = await axios.get(`procedure_modality`, {
            params: {
              workflow_id: workflow.id,
              module_id: 6,
              page: 1,
              per_page: 100
            }
          })
          this.procedureModalities = this.procedureModalities.concat(res.data.data)
        } catch (e) {
          console.error(e)
        }
      }
    },
    updateLoanList() {
      this.getRoleStatistics()
      this.getWorkflowStatistics()
      this.getLoans()
    },
    workflowTypeClass(index) {
      if (this.workflowTypesCount.hasOwnProperty(index)) {
        if (this.workflowTypesCount[index] > 0) 
          return 'tertiary black--text'
      }
      return 'normal black--text'
    },
    clearNotification() {
      this.search = ''
      if (this.track) {
        this.getLoans()
      } else {
        this.filters.traySelected = 'received'
        this.updateLoanList()
      }
      this.newLoans = []
    },
    setFilters(workflow) {
      let filters = {
        workflow_id: workflow,
        role_id: this.filters.roleSelected
      }
      if (this.filters.traySelected !== 'received') {
        filters = {
          workflow_id: workflow,
          role_id: this.filters.roleSelected,
          user_id: this.$store.getters.id
        }
      }
      switch (this.filters.traySelected) {
        case 'received':
          filters.validated = false
          break
        case 'my_received':
          filters.validated = false
          break
        case 'validated':
          filters.validated = true
          break
      }
      this.params = filters
      this.updateLoanList()
    },
    async getLoans() {
      try {
        if (!this.permissionSimpleSelected.includes('update-payment-loan') && this.permissionSimpleSelected.includes('show-all-payment-loan')) {
          this.track = true
        }
        this.loading = true
        let res = await axios.get(`loan_payment`, {
          params: {
            ...{
              page: this.options.page,
              per_page: this.options.itemsPerPage,
              sortBy: this.options.sortBy,
              sortDesc: this.options.sortDesc,
              search: this.search
            },
            ...this.params
          }
        });
        this.loans = res.data.data
        this.totalLoans = res.data.total
        this.options.page = res.data.current_page
        this.options.itemsPerPage = parseInt(res.data.per_page)
        this.options.totalItems = res.data.total
        this.setBreadcrumbs()
      } catch (e) {
        console.error(e)
      } finally {
        this.loading = false
      }
    },
    async getRoleStatistics() {
      try {
        let res = await axios.get(`statistic`, {
          params: {
            module: 'prestamos',
            filter: 'user_amortizations',
            role_id: this.filters.roleSelected
          }
        })
        let roleData = res.data.find(o => o.wf_states_id == this.$store.getters.rolePermissionSelected.wf_states_id);
        if (roleData) {
          Object.entries(roleData.data).forEach(([key, val]) => {
            let index = this.trays.findIndex(o => o.name === key);
            if (index !== -1) this.trays[index].count = val <= 999 ? val : '+999';
          });
        }
      } catch (e) {
        console.error(e)
      }
    },
    async getWorkflowStatistics() {
      try {
        const res = await axios.get(`statistic`, {
          params: {
            module: 'prestamos',
            filter: 'procedure_type_amortizations',
            role_id: this.filters.roleSelected
          }
        })
        res.data.forEach((procedure, index) => {
          let role = procedure.data.find(o => o.wf_states_id == this.$store.getters.rolePermissionSelected.wf_states_id)
          if (role) {
            this.workflowTypesCount[index] = role.data[this.filters.traySelected];
            if (this.workflowTypesCount[index] > 9999) this.workflowTypesCount[index] = '+999..';
            this.$forceUpdate()
          }
        })
      } catch (e) {
        console.error(e)
      }
    },
    setBreadcrumbs() {
      const breadcrumbs = [
        {
          text: "Cobros",
          to: { name: "loanPaymentIndex" }
        }
      ];
      this.$store.commit("setBreadcrumbs", breadcrumbs);
    },
    nulledLoans() {
      this.trackNull = !this.trackNull;
      this.params = this.trackNull ? { trashed: 1 } : {};
      this.getLoans();
    }
  }
}
</script>
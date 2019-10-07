<template>
  <v-data-table
    :headers="headers"
    :items="affiliates"
    :loading="loading"
    :options.sync="options"
    :server-items-length="totalAffiliates"
    :footer-props="{ itemsPerPageOptions: [8, 15, 30] }"
  >
    <template v-slot:item="props">
    <tr>
      <td class="text-xs-left">{{ props.item | fullName(byFirstName = true) }} </td>
      <td class="text-xs-left">{{ props.item.identity_card }}</td>
      <td>
        <v-icon class="mr-1" :color="props.item.picture_saved ? 'success' : 'error'">mdi-camera</v-icon>
        <v-icon class="ml-1" :color="props.item.fingerprint_saved ? 'success' : 'error'">mdi-fingerprint</v-icon>
      </td>
    
      <td >
        <v-btn
          fab
          dark
          x-small
          :to="{ name: 'affiliateAdd', params: { id: props.item.id }}"
          color="warning"
        >
        <v-icon>mdi-eye</v-icon>
        </v-btn>
      </td>
      </tr>
    </template>
  </v-data-table>
</template>

<script>
import Add from '@/components/affiliate/Add'
import List from '@/components/affiliate/List'

export default {
  name: 'affiliates-list',
  components: {
  Add,
  List,
  },
  props: ['bus'],
  data: () => ({
    loading: true,
    search: '',
    options: {
      page: 1,
      itemsPerPage: 8,
      sortBy: ['first_name'],
      sortDesc: [false]
    },
    affiliates: [],
    totalAffiliates: 0,
    headers: [
     
      { 
        text: 'Nombre',
        value: 'first_name', 
        class: ['normal', 'white--text'],
        width: '40%',
        sortable: false 
      },{ 
        text: 'Nro. de CI',
        value: 'identity_card',
        class: ['normal', 'white--text'],
        width: '20%',
        sortable: false 
      }, {
        text: 'Biométrico',
        class: ['normal', 'white--text'],
        width: '20%',
        sortable: false
      },{ 
        text: 'Accion',
        class: ['normal', 'white--text'],
        width: '20%',
        sortable: false
      }    
    ]
  }),
  watch: {
    options: function(newVal, oldVal) {
      if (newVal.page != oldVal.page || newVal.itemsPerPage != oldVal.itemsPerPage || newVal.sortBy != oldVal.sortBy || newVal.sortDesc != oldVal.sortDesc) {
        this.getAffiliates()
      }
    },  
    search: function(newVal, oldVal) {
      if (newVal != oldVal) {
        this.getAffiliates()
      }
    },
  },
  mounted() {
    this.bus.$on('search', val => {
      this.search = val
    })
    this.getAffiliates()
  },
  methods: {
    async getAffiliates(params) {
      try {
        this.loading = true
        let res = await axios.get(`affiliate`, {
          params: {
            page: this.options.page,
            per_page: this.options.itemsPerPage,
            sortBy: this.options.sortBy,
            sortDesc: this.options.sortDesc,
            search: this.search
          }
        })
        this.affiliates = res.data.data
        this.totalAffiliates = res.data.total
        delete res.data['data']
        this.options.page = res.data.current_page
        this.options.itemsPerPage = parseInt(res.data.per_page)
        this.options.totalItems = res.data.total
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    }
  }
}
</script>


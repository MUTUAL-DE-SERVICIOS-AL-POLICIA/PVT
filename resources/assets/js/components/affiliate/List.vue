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
       <td class="text-xs-left">{{ props.item.affiliate_state ? props.item.affiliate_state.name : ''}}</td>
      <td class="text-xs-left">{{ props.item.category ? props.item.category.name : '' }}</td>
     
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
        width: '35%',
        sortable: false 
      },{ 
        text: 'Nro. de CI',
        value: 'identity_card',
        class: ['normal', 'white--text'],
        width: '10%',
        sortable: false 
      },{
        text: 'Estado',
        value: 'affiliate_state_id',
        class: ['normal', 'white--text'],
        width: '10%',
        sortable: false
      },{
        text: 'Categoría',
        value: 'category_id',
        class: ['normal', 'white--text'],
        width: '10%',
        sortable: false
      },{
        text: 'Biométrico',
        class: ['normal', 'white--text'],
        width: '15%',
        sortable: false
      },{ 
        text: 'Acción',
        class: ['normal', 'white--text'],
        width: '10%',
        sortable: false
      }    
    ],
    state: [],
    category:[]

  }),
  watch: {
    options: function(newVal, oldVal) {
      if (newVal.page != oldVal.page || newVal.itemsPerPage != oldVal.itemsPerPage || newVal.sortBy != oldVal.sortBy || newVal.sortDesc != oldVal.sortDesc) {
        this.getAffiliates()
      }
    },  
    search: function(newVal, oldVal) {
      if (newVal != oldVal) {
        this.options.page = 1
        this.getAffiliates()
      }
    },
  },
  mounted() {
    this.bus.$on('search', val => {
      this.search = val
    })
    this.getAffiliates()
    //this.getCategory()
    //this.getAffiliateState()
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
    },
    /*async getCategory(id) {
      try {
        this.loading = true;
        let res = await axios.get(`category`)
        this.category = res.data;
      } catch (e) {
        console.log(e);
      } finally {
        this.loading = false;
      }
    },    
    searchCategory(item) {
       let procedureCategory = this.category.find(o => o.id == item)
      if (procedureCategory) {
        return procedureCategory.name        
      } else {
        return null
      }
    },
    async getAffiliateState() {
      try {
        this.loading = true;
        let res = await axios.get(`affiliate_state`);
        this.state = res.data
        console.log(this.state)
      } catch (e) {
        console.log(e);
      } finally {
        this.loading = false;
      }
    },
    searchState(item) {
      let procedureState = this.state.find(o => o.id == item)
      if (procedureState) {
        return procedureState.name        
      } else {
        return null
      }
    },*/


  }
}
</script>


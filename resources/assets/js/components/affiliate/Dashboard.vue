<template>
  <v-container class="ma-0 pa-0">
    <v-row>
      <v-col cols="8" class="text-center">
        <v-card color='#EDF2F4' shaped class="mx-5">
            <v-card-title>
              Préstamos
            </v-card-title>
            <v-card-text>
              <div>
                <h1 v-if="loan.length === 0">NO TIENE PRÉSTAMOS REGISTRADOS</h1>
                    <ul style="list-style: none;" class="pa-0">
                      <li v-for="(item,index) in loan" :key="item.id" class="pb-2" >
                        <div v-if="index<3">
                          <strong>Cód. préstamo: </strong> {{ item.code }} | 
                          <strong>Desembolsado: </strong> {{ item.amount_approved }} | 
                          <strong>Total pagado: </strong> {{ item.amount_approved - item.balance }}                      
                          <v-progress-linear 
                              :color="randomColor()"
                              height="15"
                              :value= '(((item.amount_approved-item.balance)*100)/item.amount_approved).toFixed(2)'
                              striped
                          >
                          <strong>Porcentaje pagado: {{ (((item.amount_approved-item.balance)*100)/item.amount_approved).toFixed(2) }}%</strong>
                          </v-progress-linear>
                        </div>
                      </li>
                    </ul>
              </div>
              <v-tooltip left>
                  <template v-slot:activator="{ on }">
                    <v-btn
                      fab
                      dark
                      small
                      color="success"
                      bottom
                      right
                      absolute
                      v-on="on"
                      style="margin-right: -9px;"
                      :to="{ name: 'loanAdd', params: { hash: 'new'}, query: { affiliate_id: affiliate.id}}"
                    >
                      <v-icon>mdi-plus</v-icon>
                    </v-btn>
                  </template>
                  <span>Nuevo trámite</span>
                </v-tooltip>
                <v-tooltip left  v-if="loan.length > 0">
                  <template v-slot:activator="{ on }">
                    <v-btn
                      fab
                      dark
                      small
                      color="warning"
                      v-on="on"                      
                      class="mr-10"
                      absolute
                      bottom
                      right
                      @click="clickShowLoans()"
                    >
                      <v-icon>mdi-eye</v-icon>
                    </v-btn>
                  </template>
                  <span>Ver préstamos</span>
                </v-tooltip>

              </v-card-text>
            </v-card>
      </v-col>
      <v-col cols="4" class="ma-0 pa-0">
        <v-card
          color='secondary'
        >
          <v-card-title class="ma-0 pa-0">
            <v-col cols="6">
           <v-row>
              <div >

                <div v-if="profilePictures.length > 0">
                <v-avatar
                    class="mx-auto d-block elevation-3"
                    size="125"
                  >
                    <v-img :src="`data:${profilePictures[0].format};base64,${profilePictures[0].content}`"/>
                  </v-avatar>
                  
              </div>
              <div v-else>
                <v-avatar                    
                    class="mx-auto d-block elevation-3"
                    size="125"
                  >
                    <v-icon
                      size="125"
                      color="black"
                      
                      v-if="affiliate.gender==='M'">
                      mdi-face
                    </v-icon>
                    <v-icon
                      size="125"
                      color="black"
                      
                      v-else>
                      mdi-face-woman
                    </v-icon>
                  </v-avatar>
              </div>
              </div>
            </v-row>
          </v-col>
          <v-col cols="4" class="red--text text--lighten-5 ma-0 pa-0">
            <small>
              ITEM: {{this.category_name}}
              <br>
              C.I.: {{affiliate.identity_card}}
              <v-icon color='#EDF2F4'>mdi-account</v-icon>
              {{this.state_name_type}}<br>
            </small>
              <h6 class="text-center">{{this.state_name_status}}</h6>
          </v-col>
          </v-card-title>
          <v-card-text class="ma-0 pa-0">
            <title>Prestamos</title>
            <v-col cols="12" color='#EDF2F4' class="red--text text--lighten-5 ma-0 pa-0" >
              <center>
                <v-icon color='#EDF2F4'>mdi-police-badge</v-icon>
                {{this.degree_name}}
                <br>
                UNIDAD: {{this.unit_name}}
                <br>
                TIPO: {{affiliate.type}}
              </center>
            </v-col>
            <br>
            <v-col cols="12" color='#EDF2F4' class="red--text text--lighten-5 ma-0 pa-0">
              <center>
                <v-icon color='#EDF2F4'>mdi-account-heart</v-icon>
                {{affiliate.civil_status=='C'? 'CASADO':affiliate.civil_status=='S'? 'SOLTERO':affiliate.civil_status=='D'?'DIVORCIADO':'VIUDO'}}
                <br>
              </center>
            </v-col>
            </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import common from '@/plugins/common'
export default {
  name: 'affiliate-fingerprint',
  props: {
    affiliate: {
      type: Object,
      required: true
    }
  },
  data: () => ({
    loading: false,
    fingerprints: [],
    profilePictures: [],
    degree_name:null,
    category_name:null,
    unit_name:null,
    state_name:null,
    state_name_type:null,
    state_name_status:null,
    loan:[],
    loan_one:null,
    loan_two:null,
    loan_three:null,
  }),
  created() {
    this.randomColor = common.randomColor
  },
  computed: {
    isNew() {
      return this.$route.params.id == 'new'
          },
  },
  mounted() {
    if (!this.isNew) {
      this.getProfilePictures(this.$route.params.id)
      this.getDegree_name(this.$route.params.id)
      this.getCategory_name(this.$route.params.id)
      this.getUnit_name(this.$route.params.id)
      this.getLoan(this.$route.params.id)
      this.getState_name(this.$route.params.id)
    }

  },
  methods: {
    async getProfilePictures(id) {
      try {
        this.loading = true
        let res = await axios.get(`affiliate/${id}/profile_picture`)
        this.profilePictures = res.data
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
    async getDegree_name(id) {
      try {
        this.loading = true
        let res = await axios.get(`affiliate/${id}/degree`)
        this.degree_name = res.data.name
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
      async getCategory_name(id) {
      try {
        this.loading = true
        let res = await axios.get(`affiliate/${id}/category`)
        this.category_name = res.data.name
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
      async getUnit_name(id) {
      try {
        this.loading = true
        let res = await axios.get(`affiliate/${id}/unit`)
        this.unit_name = res.data.name
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
    async getLoan(id) {
      try {
        this.loading = true
        let res = await axios.get(`affiliate/${7}/loan?guarantor=0`, {
          params: {
            sortBy: ['request_date'],
            sortDesc: [true],
            per_page: 3
          }
        })
        this.loan = res.data.data
        let num= this.loan.length

      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
    async getState_name(id) {
      try {
        this.loading = true
        let res = await axios.get(`affiliate/${id}/state`)
        this.state_name = res.data
        this.state_name_type = this.state_name.affiliate_state_type.name
        this.state_name_status=this.state_name.name
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
    clickShowLoans(){
      this.$router.push('/loan')
    }
  }
}
</script>

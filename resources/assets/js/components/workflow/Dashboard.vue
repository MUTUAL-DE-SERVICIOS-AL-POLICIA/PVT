<template>
  <v-container fluid class="ma-0 pa-0">
    <v-row>
      <v-col cols="6" class="text-center">
        <v-row>
          <v-col cols="12" class="text-center py-0" style="margin-bottom:10px">
            <v-card class="py-0" color="#173B0B" dark max-width="100%" max-height="1000"
            >
            <v-chip style="position:absolute; top:0;left:0"
              class="ma-0"
              color="#151515"
              label
              text-color="white"
            >
              <v-icon left class="body-2">
                NUP:
              </v-icon>
              {{ affiliate.id }}
            </v-chip>
              <v-card-text class="headline font-weight-bold">
                <v-icon large left style="font-size: 100px;">
                  mdi-shield-account
                </v-icon>
                <h6>
                  <strong><b style="color:white">CI: </b></strong>
                  {{ affiliate.identity_card }}
                </h6>
                <h6>
                  <strong><b style="color:white"> {{borrower.type == 'affiliates' ? 'PRESTATARIO':'TITULAR' }}: </b></strong>
                  {{ $options.filters.fullName(affiliate, true) }}
                </h6>
                <h6><strong><b style="color:white">GRADO: </b></strong> {{ degree_name }}</h6>
                <h6><strong><b style="color:white">UNIDAD: </b></strong> {{ unit_name }}</h6>
              </v-card-text>
            </v-card>
          </v-col>

          <v-col cols="12" class="text-center py-0" style="margin-bottom:10px">
            <div v-if="borrower.type == 'spouses'">
              <v-card class="py-0" color="#406b32" dark max-width="100%" max-height="1000"
              >
                <v-card-text class="headline font-weight-bold">
                  <v-icon large left style="font-size: 50px;">
                    mdi-account-heart
                  </v-icon>
                  <h6><strong><b style="color:white">PRESTATARIO:</b></strong> {{ $options.filters.fullName(borrower, true)}}</h6>
                  <h6><strong><b style="color:white">C.I: </b></strong> {{ borrower.identity_card }}</h6>
                </v-card-text>
              </v-card>
            </div>
          </v-col>
          <v-col cols="12" class="text-center py-0">
            <div v-for="(lenders,i) in loan.lenders" :key="i">
              <v-card class="py-0" color="#25604c" dark max-width="100%" max-height="1000">
                <v-card-text class="headline font-weight-bold" v-if="(lenders,i)>0" >
                  <h6><strong><b style="color:white">CODEUDOR: </b>{{  $options.filters.fullName(lenders,true)}}</strong></h6>
                  <h6><strong><b style="color:white">C.I: </b>{{lenders.identity_card}}</strong> </h6>
                </v-card-text>
              </v-card>
              </div>
          </v-col>
        </v-row>
      </v-col>
      <v-col cols="6" class="text-center">
        <v-row>
          <v-col cols="6" class="text-center py-0">
            <v-card class="py-0" color="#585858" dark max-height="135">
              <v-card-text class="headline font-weight-bold">
                <v-icon large left style="font-size: 50px;">
                  mdi-currency-usd
                </v-icon>
                <h5><strong><b style="color:white">MONTO SOLICITADO:</b></strong></h5>
                <h5>{{ loan.amount_approved | money}}  Bs </h5>
              </v-card-text>
            </v-card>
          </v-col>
          <v-col cols="6" class="text-center py-0">
            <v-card class="mx-auto" color="#424242" dark max-height="135">
              <v-card-text class="headline font-weight-bold">
                <v-icon large left style="font-size: 50px;">
                  mdi-timer-sand
                </v-icon>
                <h5><strong><b style="color:white">MESES PLAZO:</b></strong></h5>
                <h5>{{ loan.loan_term }}</h5>
              </v-card-text>
            </v-card>
          </v-col>
          <v-col cols="12" class="text-center py-0" style="margin-top:10px">
            <v-card class="mx-auto" color="#151515" dark max-height="400">
              <v-card-text class="headline font-weight-bold">
                <v-icon large left style="font-size: 50px;">
                 mdi-bank
                </v-icon>
                <h5><strong><b style="color:white">MODALIDAD:</b></strong></h5>
                <h5 class="text-uppercase">{{ loan.modality.name}}</h5>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>

export default {
  name: "flow-dashboard",
  props: {
    affiliate: {
      type: Object,
      required: true
    },
    borrower: {
      type: Object,
      required: true
    },
    loan: {
      type: Object,
      required: true
    },
    spouse: {
      type: Object,
      required: true
    }
  },
  data: () => ({
    loading: false,
    degree_name: null,
    unit_name: null,
    last_name: null,
    mothers_last_name: null,
    last_name:null,
    first_name:null,
    procedure_modality_name: ""
  }),
  computed: {
    isNew() {
      return this.$route.params.id == "new"
    },

  },
  watch: {
    affiliate(newVal, oldVal) {
      if (oldVal != newVal) {
        if (newVal.hasOwnProperty("degree_id"))
          this.getDegree_name(newVal.degree_id);
        if (newVal.hasOwnProperty("unit_id")){
          if(newVal.unit_id != null)this.getUnit_name(newVal.unit_id)
        }
      }
    },
    loan(newVal, oldVal) {
      if (oldVal != newVal) {
        if (newVal.hasOwnProperty("procedure_modality_id"))
          this.getProcedureModalityName(newVal.procedure_modality_id)
      }
    }
  },
  methods: {
    async getDegree_name(id) {
      try {
        this.loading = true
        let res = await axios.get(`degree/${id}`)
        this.degree_name = res.data.name
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
    async getUnit_name(id) {
      try {
        this.loading = true;
        let res = await axios.get(`unit/${id}`)
        this.unit_name = res.data.name;
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
    async getProcedureModalityName(id) {
      try {
        this.loading = true;
        let res = await axios.get(`procedure_modality/${id}`)
        this.procedure_modality_name = res.data.name
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    }
  }
};
</script>

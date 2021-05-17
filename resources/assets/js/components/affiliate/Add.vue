<template>
  <v-card flat>
    <v-card-title>
      <v-toolbar dense color="tertiary" style="z-index: 1;">
        <v-toolbar-title>
          <Breadcrumbs/>
        </v-toolbar-title>
        <v-spacer></v-spacer>
        <v-tooltip top>
          <template v-slot:activator="{ on }">
            <v-btn
              fab
              dark
              x-small
              :color="'error'"
              bottom
              right
              absolute
              v-on="on"
              style="margin-right: 45px;"
              @click.stop="resetForm()"
              v-show="!isNew && editable"
            >
              <v-icon>mdi-close</v-icon>
            </v-btn>
          </template>
          <div>
            <span>Cancelar</span>
          </div>
        </v-tooltip>
        <v-tooltip top v-if="tab != 'tab-1'">
          <template v-slot:activator="{ on }">
            <v-btn
              fab
              dark
              small
              :color="editable ? 'danger' : 'success'"
              bottom
              right
              absolute
              v-on="on"
              style="margin-right: -9px;"
              @click.stop="saveAffiliate()"
            >
              <v-icon v-if="editable">mdi-check</v-icon>
              <v-icon v-else>mdi-pencil</v-icon>
            </v-btn>
          </template>
          <div>
            <span v-if="editable">Guardar</span>
            <span v-else>Editar</span>
          </div>
        </v-tooltip>
      </v-toolbar>
    </v-card-title>
    <v-card-text>
      <v-tabs
        v-model="tab"
        background-color="deep-blue accent-2"
        class="elevation-2"
        dark
        :vertical="vertical"
        :icons-and-text="icons"
      >
        <v-tabs-slider></v-tabs-slider>

        <v-tab
          v-if="!editable"
          v-show="!isNew"
          :href="`#tab-1`"
        >
        <v-tooltip bottom>
        <template v-slot:activator="{ on, attrs }">
          <v-icon 
          v-if="icons"
          v-bind="attrs"
          v-on="on"
          >mdi-trending-up
          </v-icon>
          </template>
        <span><b>PRESTAMOS, REFINANCIAMIENTO Y REPROGRAMACION</b></span>
        </v-tooltip>
        </v-tab>
        <v-tab
          :href="`#tab-2`"
        >
        <v-tooltip bottom>
        <template v-slot:activator="{ on, attrs }">
          <v-icon 
          v-if="icons"
          v-bind="attrs"
          v-on="on"
          >mdi-account
          </v-icon>
        </template>
        <span><b>DATOS PERSONALES DEL AFILIADO</b></span>
      </v-tooltip>
        </v-tab>
        <v-tab
          :href="`#tab-3`"
          v-show="!isNew"
        >
        <v-tooltip bottom>
        <template v-slot:activator="{ on, attrs }">
          <v-icon 
          v-if="icons"
          v-bind="attrs"
          v-on="on"
          >mdi-account-heart
          </v-icon>
          </template>
        <span><b>INFORMACION CONYUGE</b></span>
      </v-tooltip>
        </v-tab>
        <v-tab
          :href="`#tab-4`"
        >
        <v-tooltip bottom>
        <template v-slot:activator="{ on, attrs }">
          <v-icon 
          v-if="icons" 
          v-bind="attrs"
          v-on="on"
          >mdi-file-account
          </v-icon>
        </template>
        <span><b>INFORMACION ADICIONAL</b></span>
      </v-tooltip>
        </v-tab>

        <v-tab
          v-show="!isNew"
          :href="`#tab-5`"
        >
        <v-tooltip bottom>
        <template v-slot:activator="{ on, attrs}">
          <v-icon
          v-if="icons"
          v-bind="attrs"
          v-on="on">
          mdi-account-cash
          </v-icon>
        </template>
        <span><b>CONTRIBUCIONES</b></span>
        </v-tooltip>
        </v-tab>

        <v-tab
          v-show="!isNew"
          :href="`#tab-6`"
        >
        <v-tooltip bottom>
        <template v-slot:activator="{ on, attrs }">
          <v-icon
          v-if="icons"
          v-bind="attrs"
          v-on="on"
          >mdi-fingerprint
          </v-icon>
          </template>
        <span><b>INFORMACION DEL BIOMETRICO</b></span>
        </v-tooltip>
        </v-tab>

        <v-tab-item
        :value="'tab-1'"
      >
        <v-card flat tile >
          <v-card-text>
            <Dashboard
              :affiliate.sync="affiliate"/>
          </v-card-text>
        </v-card>
      </v-tab-item>
        <v-tab-item
          :value="'tab-2'"
        >
          <v-card flat tile >
            <v-card-text>
              <Profile
                v-if="!reload"
                :affiliate.sync="affiliate"
                :addresses.sync="addresses"
                :editable.sync="editable"
                :permission="permission"
                :id_street.sync="id_street"
                 :has_registered_spouse="has_registered_spouse"
              />
            </v-card-text>
          </v-card>
        </v-tab-item>


        <!--<v-tab-item
          :value="'tab-3'"
        >
          <v-card flat tile >
            <v-card-text>
              <PoliceData
                v-if="!reload"
                :affiliate.sync="affiliate"
                :editable.sync="editable"
                :permission="permission"
                :has_registered_spouse="has_registered_spouse"
              />
            </v-card-text>
          </v-card>
        </v-tab-item>-->
          <v-tab-item
          :value="'tab-3'"
        >
          <v-card flat tile >
            <v-card-text>
              <Spouse
                v-if="!reload"
                :state_id.sync="affiliate.affiliate_state_id"
                :spouse.sync="spouse"
                :editable.sync="editable"
                :permission="permission"
              />
            </v-card-text>
          </v-card>
        </v-tab-item>
        <v-tab-item
          :value="'tab-4'"
        >
          <v-card flat tile >
            <v-card-text>
              <AdditionalInformation
                v-if="!reload"
                :affiliate.sync="affiliate"
                :addresses.sync="addresses"
                :editable.sync="editable"
                :permission="permission"
                :id_street.sync="id_street"
                :has_registered_spouse="has_registered_spouse"
              />
            </v-card-text>
          </v-card>
        </v-tab-item>

        <v-tab-item
          :value="'tab-5'"
        >
          <v-card flat tile >
          <v-card-text>
            <Contributions
              :affiliate.sync="affiliate"
            />
            <!-- <Document
              :permission="permission"
              :affiliate.sync="affiliate"
              :editable.sync="editable"
            /> -->
            </v-card-text>
          </v-card>
        </v-tab-item>

        <v-tab-item
          :value="'tab-6'"
        >
          <v-card flat tile >
          <v-card-text>
            <Fingerprint
              :permission="permission"
              :affiliate.sync="affiliate"
              :editable.sync="editable"
            /></v-card-text>
          </v-card>
        </v-tab-item>

      </v-tabs>
    </v-card-text>
  </v-card>
</template>
<script>
import Breadcrumbs from '@/components/shared/Breadcrumbs'
import Profile from '@/components/affiliate/Profile'
//import PoliceData from '@/components/affiliate/PoliceData'
import Spouse from '@/components/affiliate/Spouse'
import Fingerprint from '@/components/affiliate/Fingerprint'
//import Document from '@/components/affiliate/Document'
import Dashboard from '@/components/affiliate/Dashboard'
import AdditionalInformation from '@/components/affiliate/AdditionalInformation'
import Contributions from '@/components/affiliate/Contributions'

export default {
  name: "affiliate-index",
  components: {
    Breadcrumbs,
    Profile,
    //PoliceData,
    Spouse,
    Fingerprint,
    //Document,
    Dashboard,
    AdditionalInformation,
    Contributions
  },
  data: () => ({
    addresses:[],
    affiliate:{
      first_name: null,
      second_name:null,
      last_name: null,
      mothers_last_name:null,
      identity_card:null,
      birth_date:null,
      date_death:null,
      reason_death:null,
      phone_number:null,
      cell_phone_number:null,
      city_identity_card_id:null,
      date_entry:null,
      date_derelict:null,
      unit_name:null,
      affiliate_state_id: null
    },
    spouse: {
      affiliate_id: null,
      first_name: null,
      second_name:null,
      last_name: null,
      mothers_last_name:null,
      identity_card:null,
      birth_date:null,
      date_death:null,
      reason_death:null,
      phone_number:null,
      cell_phone_number:null,
      city_identity_card_id:null,
      death_certificate_number:null,
      city_birth_id:null,
      civil_status:null,
      official:null,
      book:null,
      departure:null,
      marriage_date:null
    },
    icons: true,
    vertical: true,
    tabs: 3,
    editable: false,
    reload: false,
    tab: 'tab-1',
    has_registered_spouse: false,
    bus: new Vue(),
    id_street: 0,
  }),
  computed: {
    //permisos del selector global por rol
    permissionSimpleSelected () {
      return this.$store.getters.permissionSimpleSelected
    },
    
    isNew() {
      return this.$route.params.id == 'new'
          },
    permission() {
      return {
        primary: this.primaryPermission,
        secondary: this.secondaryPermission
      }
    },
    secondaryPermission() {
      if (this.affiliate.id) {
        return this.permissionSimpleSelected.includes('update-affiliate-secondary')
      } else {
        return this.permissionSimpleSelected.includes('create-affiliate')
    }
  },
  primaryPermission() {
      if (this.affiliate.id) {
        return this.permissionSimpleSelected.includes('update-affiliate-primary')
      } else {
        return this.permissionSimpleSelected.includes('create-affiliate')
      }
    }
  },
  watch:{
    'spouse.id': function(newVal, oldVal){
      if(newVal != oldVal)
      this.getAffiliate(this.$route.params.id)
    }
  },
  mounted() {
    if (!this.isNew) {
      this.resetForm()
    } else {
      this.tab = 'tab-2'
      this.editable = true
      this.setBreadcrumbs()
    }
    
  },
  methods: {
    resetForm() {
      this.getAffiliate(this.$route.params.id)

      this.editable = false
      this.reload = true
      this.$nextTick(() => {
      this.reload = false
      })
    },
    async saveAffiliate() {
      try {
        if (!this.editable) {
            this.editable = true
        } else {
          if (this.isNew) {
          // New affiliate
            let res = await axios.post(`affiliate`, this.affiliate)
            this.toastr.success('Registro guardado correctamente')
            this.editable = false
            //Actualizar dirección,  obteniendo respuesta POST afiliado nuevo (res.data.id)
            await axios.patch(`affiliate/${res.data.id}/address`, {
              addresses: this.addresses.map(o => o.id)
            })
          } else {
            // Edit affiliate
            await axios.patch(`affiliate/${this.affiliate.id}`, this.affiliate)
            await axios.patch(`affiliate/${this.affiliate.id}/address`, {
              addresses: this.addresses.map(o => o.id),
              addresses_valid: this.id_street
            })
            
            //Preguntar si afiliado esta fallecido 
            if(this.affiliate.affiliate_state_id == 4){
              if(this.spouse.id){
                await axios.patch(`spouse/${this.spouse.id}`, this.spouse)
              } else if(Object.entries(this.spouse).length !== 0){
                this.spouse.affiliate_id=this.affiliate.id
                let res = await axios.post(`spouse`, this.spouse)
                this.getAffiliate(this.$route.params.id)
              } else if(Object.entries(this.spouse).length === 0){ 
                this.toastr.success("Se Actualizó los datos del afiliado...")
              } else {
                this.toastr.error("Solo puede registrar a la conyugue si el estado del afilaidos es 'Fallecido'")
              }
            } else{
              this.toastr.success("Se Actualizó los datos del afiliado")
            }
            this.editable = false
          }
                
        }
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
    setBreadcrumbs() {
      let breadcrumbs = [
        {
          text: 'Afiliados',
          to: { name: 'affiliateIndex' }
        }
      ]
      if (this.isNew) {
        breadcrumbs.push({
          text: 'Nuevo Afiliado',
          to: { name: 'affiliateAdd', params: { id: 'new' } }
        })
      } else {
        breadcrumbs.push({
          text: this.$options.filters.fullName(this.affiliate, true),
          to: { name: 'affiliateAdd', params: { id: this.affiliate.id } }
        })
      }
      this.$store.commit('setBreadcrumbs', breadcrumbs)
    },
    async getAffiliate(id) {
      try {
        this.loading = true
        let res = await axios.get(`affiliate/${id}`)
        this.affiliate = res.data
        this.setBreadcrumbs()
        this.getAddress(id)
        //if (this.affiliate.dead) {
          this.getSpouse(this.affiliate.id)
        //}
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
    async getSpouse(id) {
      try {
        this.loading = true
        let res = await axios.get(`affiliate/${id}/spouse`)
        this.spouse = res.data
        if(Object.entries(this.spouse).length === 0){
          this.has_registered_spouse = false
        }else{
          this.has_registered_spouse = true
        }
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
    async getAddress(id) {
      try {
        this.loading = true
        let res = await axios.get(`affiliate/${id}/address`)
        this.addresses = res.data
        // Seteando el valor del address
        let address = this.addresses.find(item => item.pivot.validated)
        this.id_street = address.id
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
  },
}
</script>

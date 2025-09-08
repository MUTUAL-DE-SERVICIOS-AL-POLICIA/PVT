<template>
  <div>
    <Appbar/>
    <section class="px-6">
      <v-row class="justify-center align-center my-4">
        <v-col cols="auto" class="ml-auto"></v-col>
        <v-col cols="auto">
          <strong class="grey--text text-h5">Plataforma Virtual de Trámites</strong>
        </v-col>
        <v-col cols="auto" class="ml-auto">
          <v-tooltip top>
            <template v-slot:activator="{ on, attrs }">
              <v-btn @click="openHorizon" v-bind="attrs" v-on="on" color="secondary" small>
                <span class="mr-2">Visor de Sms's</span>
                <v-icon small>mdi-open-in-new</v-icon>
              </v-btn>
            </template>
            <span>Ir al Visor de Sms's</span>
          </v-tooltip>
        </v-col>
      </v-row>
      <v-row>
        <v-col cols="3" v-for="(item, index) in rolesPermissionsItems" :key="index" class="pa-2">
          <v-card 
            shaped 
            outlined 
            @click="clickRole(item)" 
            style="cursor: pointer;border: thin solid rgba(0, 0, 0, 0.5);"
            elevation="2">
            <v-card-text>
              <v-icon color="teal">mdi-account-circle</v-icon>&nbsp;
              <span class="teal--text font-weight-bold  text-center">{{item.display_name }}</span><br>
              <span class=" font-weight-medium text-uppercase text-center">{{ item.module.name }}</span><br>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </section>
  </div>
</template>

<script>
import Appbar from '../../layout/Appbar.vue'

export default {
  name: 'changeRole',
  components: {
    Appbar
  },
  data: () => ({
		rolesPermissionsItems: [],
  }),
	async created() {
		// al entrar a esta pagina se elimina el rolePermissionSelected del store
    this.$store.commit('setRolePermissionSelected', null)
    await this.getRolePermissions()
		// si es administrador va directo al dashboard
		/*if(this.$store.getters.userRoles[0] == 'TE-admin') {
			// this.$store.commit('setRolePermissionSelected', {})
			this.$router.push("dashboard")
		}*/
    //this.setDefaultValues()
  },
  methods: {
    async getRolePermissions() {
			try {
        let res = await axios.get(`user_role/permission`)
        let aux_rolesPermissionsItems = res.data
        aux_rolesPermissionsItems.forEach(item => {
          //delete item.id
          delete item.module_id
          delete item.action
          delete item.created_at
          delete item.updated_at
          delete item.correlative
          delete item.name
          delete item.sequence_number
          item.permissions = item.permissions.map(item => ({display_name: item.display_name, name: item.name }))
        })
        this.rolesPermissionsItems =  aux_rolesPermissionsItems
        
      } catch (e) {
        console.log(e)
      }
    },
		/*setDefaultValues() {
      if(this.rolesPermissionsItems.length > 0) {
        this.rolePermissionSelected = this.rolesPermissionsItems[0]
      }
    },*/
		clickRole(item) {
			this.$store.commit('setRolePermissionSelected', item)
      if(item.id != 104)
			  this.$router.push("dashboard")
      else
        this.$router.push("sismu")
      
		},
    goToHorizon() {
      const tokenType = this.$store.getters.tokenType;
      const accessToken = this.$store.getters.accessToken;

      if (!tokenType || !accessToken) {
        alert('No hay sesión activa. Por favor, inicia sesión.');
        return;
      }
      const token = `${accessToken}`;
      const encodedToken = encodeURIComponent(token);
      const queue = `muserpol`;
      window.open(`/horizon-queues?t=${queue}`, '_blank');
    },
    openHorizon() {
      this.goToHorizon()
    }
  },
}
</script>
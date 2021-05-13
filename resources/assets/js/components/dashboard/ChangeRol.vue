<template>
  <section class="px-6">
		<v-row class="justify-center my-4">
			<strong class="grey--text text-h5">Plataforma Virtual de Trámites</strong><br>
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
						{{ item.display_name }}
					</v-card-text>
				</v-card>
			</v-col>
		</v-row>
	</section>
</template>

<script>
export default {
  name: 'changeRole',
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
        let res = await axios.get(`user_role/${6}/permission`)
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
			this.$router.push("dashboard")
		}
  },
}
</script>
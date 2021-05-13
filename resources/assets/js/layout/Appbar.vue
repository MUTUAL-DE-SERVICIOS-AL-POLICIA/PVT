<template>
  <v-app-bar
    app
    fixed
    dense
    flat
    clipped-left
    dark
    :color="bar.color"
  >
    <template v-if="rolePermissionSelected">
      <v-app-bar-nav-icon @click.stop="$emit('update:expanded', !expanded)"></v-app-bar-nav-icon>
    </template>
    <v-toolbar-title>{{ bar.text }}</v-toolbar-title>
    <v-spacer></v-spacer>
    <div width="300px">
      <!-- <v-select
        outlined
        hide-details
        v-model="rolePermissionSelected"
        :items="rolesPermissionsItems"
        label="Rol actual"
        class="py-0 my-0"
        item-text="display_name"
        return-object
        dense
      ></v-select> -->
      <span class="text-caption font-weight-bold">{{ rolePermissionSelected ? rolePermissionSelected.display_name : '' }}</span>
        <v-btn
          v-on="on"
          fab
          dark
          x-small
          v-if="rolePermissionSelected!=null"
          color="white"
          outlined
          class="mx-3"
          @click="$router.push('/changeRol')">
          <v-icon>mdi-keyboard-return</v-icon>
        </v-btn>
    </div>
    <LoggedUser/>
  </v-app-bar>
</template>

<script>
import { mapGetters } from 'vuex'
import LoggedUser from '@/layout/LoggedUser'

export default {
  name: 'app-mainbar',
  components: {
    LoggedUser
  },
  props: {
    expanded: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      rolesPermissionsItems: [],
    }
  },
  async created() {
    await this.getRolePermissions()
    // this.setDefaultValues()
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
        console.log(this.rolesPermissionsItems)
      } catch (e) {
        console.log(e)
      }
    },
    setDefaultValues() {
      if(this.rolesPermissionsItems.length > 0) {
        this.rolePermissionSelected = this.rolesPermissionsItems[0]
      }
    },
  },
  computed: {
    ...mapGetters(['rolePermissionSelected']),
    bar() {
      if (process.env.NODE_ENV != 'production') {
        return {
          color: `error`,
          text: `PLATAFORMA VIRTUAL DE TRÁMITES (VERSIÓN DE PRUEBA)`
        }
      } else {
        return {
          color: `primary`,
          text: `PLATAFORMA VIRTUAL DE TRÁMITES`
        }
      }
    },

  },
  watch: {
    'rolePermissionSelected.display_name'(val) {
      this.$store.commit('setRolePermissionSelected', this.rolePermissionSelected)
    }
  }
}
</script>

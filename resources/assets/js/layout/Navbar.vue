<template>
  <v-navigation-drawer
    app
    fixed
    permanent
    clipped
    :expand-on-hover="!expanded"
    width="160px"
  >
    <v-list
      nav
      dense
    >
      <div
        v-for="item in menuItems"
        :key="item.title"
      >
        <v-list-item
          :to="{ name: item.href, query: item.params }"
          v-if="!item.group && checkPermission(item)"
        >
          <v-list-item-icon class="ml-0 mr-2">
            <v-icon>{{ item.icon }}</v-icon>
          </v-list-item-icon>
          <v-list-item-title>{{ item.title }}</v-list-item-title>
        </v-list-item>
        <v-list-group
          v-if="item.group && checkPermission(item)"
          :prepend-icon="item.icon"
          mandatory
        >
          <v-list-item slot="activator">
            <v-list-item-title style="margin-left: -31px;">{{ item.title }}</v-list-item-title>
          </v-list-item>
          <template v-for="subItem in item.group">
            <v-list-item
              class="pl-5"
              :to="{ name: subItem.href, query: subItem.params }"
              :key="subItem.title"
              v-if="checkPermission(subItem)"
            >
              <v-list-item-icon class="mr-2">
                <v-icon>{{ subItem.icon }}</v-icon>
              </v-list-item-icon>
              <v-list-item-title>{{ subItem.title }}</v-list-item-title>
            </v-list-item>
          </template>
        </v-list-group>
        <v-divider></v-divider>
      </div>
    </v-list>
  </v-navigation-drawer>
</template>

<script>
import menuItems from '@/menu.json'

export default {
  name: 'app-navbar',
  props: {
    expanded: {
      type: Boolean,
      default: false
    }
  },
  data: () => ({
    menuItems: menuItems
  }),
    computed: {
    //permisos del selector global por rol
      permissionSimpleSelected () {
        return this.$store.getters.permissionSimpleSelected
      }
    },
  methods: {
    checkPermission(item) {
      let hasPermission = true
      //if(this.$store.getters.userRoles[0] != 'TE-admin'){
        //console.log(this.$store.getters.userRoles[0] == 'TE-admin')
        if (item.hasOwnProperty('permission')) hasPermission &= (item.permission == null || this.permissionSimpleSelected.includes(item.permission))
        if (item.hasOwnProperty('role')) hasPermission &= this.$store.getters.userRoles.includes(item.role)
      /*}else{
        console.log(this.$store.getters.userRoles[0] == 'TE-admin')
        if (item.hasOwnProperty('permission')) hasPermission &= (item.permission == null || this.$store.getters.permissions.includes(item.permission))
        if (item.hasOwnProperty('role')) hasPermission &= this.$store.getters.userRoles.includes(item.role)
      }*/

      return hasPermission
    }
  }
}
</script>
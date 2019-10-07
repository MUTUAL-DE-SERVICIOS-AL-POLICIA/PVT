<template>
  <v-card flat>
    <v-card-title>
      <v-toolbar dense color="tertiary">
        <v-toolbar-title>Usuarios</v-toolbar-title>
        <v-spacer></v-spacer>
        <v-btn-toggle
          v-model="active"
          active-class="primary white--text"
          mandatory
        >
          <v-btn text :value="true">
            ACTIVOS
          </v-btn>
          <v-btn text :value="false">
            INACTIVOS
          </v-btn>
        </v-btn-toggle>
        <v-divider
          class="mx-2"
          inset
          vertical
        ></v-divider>
        <v-flex xs3>
          <v-text-field
            v-model="search"
            append-icon="mdi-magnify"
            label="Buscar"
            class="mr-5 pr-5"
            single-line
            hide-details
            clearable
          ></v-text-field>
        </v-flex>
        <Fab v-if="['create-user', 'update-user'].some(i => $store.getters.permissions.includes(i))" :bus="bus"/>
      </v-toolbar>
    </v-card-title>
    <v-card-text>
      <List :bus="bus"/>
    </v-card-text>
    <RemoveItem :bus="bus"/>
  </v-card>
</template>
<script>
import List from '@/components/user/List'
import Fab from '@/components/user/Fab'
import RemoveItem from '@/components/shared/RemoveItem'

export default {
  name: "user-index",
  components: {
    Fab,
    List,
    RemoveItem
  },
  data: () => ({
    search: '',
    bus: new Vue(),
    active: true
  }),
  watch: {
    search: _.debounce(function () {
      this.bus.$emit('search', this.search)
    }, 1000),
    active: function() {
      this.bus.$emit('active', this.active)
    }
  }
}
</script>

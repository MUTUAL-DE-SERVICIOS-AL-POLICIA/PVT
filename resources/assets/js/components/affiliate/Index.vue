<template>
  <v-card flat>
    <v-card-title>
      <v-toolbar dense color="tertiary">
        <v-toolbar-title>
          <Breadcrumbs/>
        </v-toolbar-title>
        <v-spacer></v-spacer>
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
        <v-tooltip left v-if="$store.getters.permissions.includes('create-affiliate')">
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
              :to="{ name: 'affiliateAdd', params: { id:'new'} }"
            >
              <v-icon>mdi-plus</v-icon>
            </v-btn>
          </template>
          <span>Añadir afiliado</span>
        </v-tooltip>
      </v-toolbar>
    </v-card-title>
    <v-card-text>
      <List :bus="bus"/>
    </v-card-text>
  </v-card>
</template>
<script>
import Breadcrumbs from '@/components/shared/Breadcrumbs'
import List from '@/components/affiliate/List'

export default {
  name: "affiliateIndex",
  components: {
    Breadcrumbs,
    List
  },
  data: () => ({
    search: '',
    bus: new Vue()
  }),
  beforeMount() {
    this.$store.commit('setBreadcrumbs', [
      {
        text: 'Afiliados',
        to: { name: 'affiliateIndex' }
      }
    ])
  },
  watch: {
    search: _.debounce(function () {
      this.bus.$emit('search', this.search)
    }, 1000)
  }
}
</script>

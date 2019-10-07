<template>
  <div>
    <v-tooltip top>
      <template v-slot:activator="{ on }">
        <v-btn
          fab
          dark
          x-small
          v-on="on"
          color="danger"
          @click.stop="dialog = true"
        >
          <v-icon>mdi-sync</v-icon>
        </v-btn>
      </template>
      <span>Sincronizar con LDAP</span>
    </v-tooltip>
    <v-dialog
      v-model="dialog"
      width="500"
      persistent
    >
      <v-card>
        <v-toolbar dense flat color="tertiary">
          <v-toolbar-title>Desactivar usuarios</v-toolbar-title>
          <v-spacer></v-spacer>
          <v-btn icon @click.stop="close()">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-toolbar>
        <v-card-text style="overflow: scroll; overflow-x: hidden; height: 320px;">
          <v-list dense>
            <v-list-item two-line v-for="user in users" :key="user.id">
              <v-list-item-content>
                <v-list-item-title>{{ user | fullName | uppercase }}</v-list-item-title>
                <v-list-item-subtitle>{{ user.position | capitalize }}</v-list-item-subtitle>
              </v-list-item-content>
            </v-list-item>
          </v-list>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn
            color="error"
            @click="synchronizeUsers()"
          >Sincronizar</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
export default {
  name: 'user-sync',
  props: {
    bus: {
      type: Object,
      required: true
    }
  },
  data: () => ({
    loading: true,
    dialog: false,
    users: []
  }),
  mounted() {
    this.getUsers()
  },
  watch: {
    dialog(val) {
      if (!val) {
        this.clearForm()
      } else {
        this.bus.$emit('getUsers')
        if (this.users.length == 0) {
          this.toast('No se encontraron bajas', 'info')
          this.close()
        }
      }
    }
  },
  methods: {
    clearForm() {
      this.users = []
    },
    close() {
      this.dialog = false
      this.$emit('closeFab')
    },
    async getUsers() {
      try {
        this.loading = true
        let res = await axios.get(`ldap/sync`)
        this.users = res.data
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
    synchronizeUsers() {
      this.users.forEach(user => this.switchActiveUser(user.id))
      this.toast('Sincronización terminada', 'success')
      this.close()
    },
    async switchActiveUser(id) {
      try {
        this.loading = true
        let res = await axios.patch(`user/${id}`, {
          active: false
        })
        this.bus.$emit('removed', id)
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    }
  }
}
</script>
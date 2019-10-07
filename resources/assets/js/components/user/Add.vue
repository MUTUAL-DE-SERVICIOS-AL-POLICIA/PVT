<template>
  <div>
    <v-tooltip top>
      <template v-slot:activator="{ on }">
        <v-btn
          fab
          dark
          x-small
          v-on="on"
          color="info"
          @click.stop="dialog = true"
        >
          <v-icon>mdi-plus</v-icon>
        </v-btn>
      </template>
      <span>Añadir usuario</span>
    </v-tooltip>
    <v-dialog
      v-model="dialog"
      width="500"
      persistent
    >
      <v-card>
        <v-toolbar dense flat color="tertiary">
          <v-toolbar-title>Añadir usuario</v-toolbar-title>
          <v-spacer></v-spacer>
          <v-btn icon @click.stop="close()">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-toolbar>
        <v-card-title></v-card-title>
        <v-card-text>
          <Ldap :bus="bus" @input="userSelected = $event"/>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn
            color="error"
            @click="saveUser()"
            :disabled="errors.any()"
          >Añadir</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
import Ldap from '@/components/user/Ldap'

export default {
  name: 'ldap-add',
  components: {
    Ldap
  },
  props: {
    bus: {
      type: Object,
      required: true
    }
  },
  data: () => ({
    loading: true,
    dialog: false,
    userSelected: null
  }),
  watch: {
    dialog(val) {
      if (!val) {
        this.clearForm()
      } else {
        this.bus.$emit('getUsers')
      }
    }
  },
  methods: {
    clearForm() {
      this.userSelected = null
    },
    close() {
      this.dialog = false
      this.$emit('closeFab')
    },
    async saveUser() {
      try {
        if (this.userSelected) {
          this.loading = true
          let res = await axios.post(`user`, {
            first_name: this.userSelected.givenName,
            last_name: this.userSelected.sn,
            username: this.userSelected.uid,
            password: this.userSelected.uid,
            position: this.userSelected.title
          })
          this.toast('Usuario adicionado', 'success')
          this.bus.$emit('added', res.data)
          this.clearForm()
          this.close()
        } else {
          this.toast('Debe seleccionar un usuario', 'danger')
        }
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    }
  }
}
</script>
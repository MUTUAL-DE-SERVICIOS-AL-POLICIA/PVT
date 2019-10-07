<template>
  <v-dialog persistent v-model="dialog" max-width="31%" @keydown.esc="close">
    <v-card>
      <v-card-title>¿Seguro que desea eliminar el registro?</v-card-title>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn color="success" small @click.stop="close()"><v-icon small>mdi-check</v-icon> Cancelar</v-btn>
        <v-btn color="error" small @click.stop="remove()"><v-icon small>mdi-close</v-icon> Eliminar</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
export default {
  name: 'remove-item',
  props: {
    bus: {
      type: Object,
      required: true
    }
  },
  data: () => ({
    path: '',
    dialog: false
  }),
  methods: {
    close() {
      this.path = ''
      this.dialog = false
      this.bus.$emit('closeRemoveDialog')
    },
    async remove() {
      try {
        let res = await axios.delete(this.path)
        this.toast('Eliminado correctamente', 'success')
        this.bus.$emit('removed', Number(this.path.split('/').pop()))
        this.close()
      } catch (e) {
        console.log(e);
      }
    }
  },
  mounted() {
    this.bus.$on('openRemoveDialog', url => {
      this.path = url
      this.dialog = true
    })
  }
}
</script>
<template>
  <v-dialog persistent v-model="dialog" max-width="31%" @keydown.esc="close">
    <v-card>
      <v-card-title>¿Seguro que desea eliminar el registro?</v-card-title>
      <v-card-actions v-if="!loading">
        <v-spacer></v-spacer>
        <v-btn color="success" small @click.stop="close()"><v-icon small>mdi-check</v-icon> Cancelar</v-btn>
        <v-btn color="error" small @click.stop="remove()"><v-icon small>mdi-close</v-icon> Eliminar</v-btn>
      </v-card-actions>
      <Loading v-else/>
    </v-card>
  </v-dialog>
</template>

<script>
import Loading from '@/components/shared/Loading'

export default {
  name: 'remove-item',
  components: {
    Loading
  },
  props: {
    bus: {
      type: Object,
      required: true
    }
  },
  data: () => ({
    loading: false,
    path: '',
    dialog: false
  }),
  methods: {
    close() {
      this.dialog = false
      this.path = ''
      this.bus.$emit('closeRemoveDialog')
    },
    async remove() {
      try {
        this.loading = true
        let res = await axios.delete(this.path)
        if(!res.data.message){
          this.toastr.success('Eliminado correctamente')
        }else{
          this.toastr.error(res.data.message)
        }
        this.close()
        this.bus.$emit('removed', Number(this.path.split('/').pop()))
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
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
<template>
  <v-card flat>
    <v-card-title>
      <v-toolbar dense color="tertiary">
        <v-toolbar-title>Registro de actividad</v-toolbar-title>
      </v-toolbar>
    </v-card-title>
    <v-card-text>
      <List/>
    </v-card-text>
  </v-card>
</template>

<script>
import List from '@/components/record/List'

export default {
  name: 'record-index',
  components: {
    List
  }
}
</script>
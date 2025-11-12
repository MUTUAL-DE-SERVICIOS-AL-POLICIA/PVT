<template>
  <div v-if="permission">
    <!-- Botón Cancelar -->
    <v-tooltip top>
      <template v-slot:activator="{ on }">
        <v-btn
          fab
          dark
          x-small
          color="error"
          v-on="on"
          @click.stop="onCancel"
          v-show="editing"
          :style="cancelStyle"
        >
          <v-icon>{{ iconCancel }}</v-icon>
        </v-btn>
      </template>
      <div>
        <span>{{ textCancel }}</span>
      </div>
    </v-tooltip>

    <!-- Botón Editar / Guardar -->
    <v-tooltip top>
      <template v-slot:activator="{ on }">
        <v-btn
          fab
          dark
          x-small
          :color="editing ? 'danger' : 'success'"
          v-on="on"
          @click.stop="onEdit"
          :style="editStyle"
        >
          <v-icon v-if="editing">{{ iconEditing }}</v-icon>
          <v-icon v-else>{{ iconDefault }}</v-icon>
        </v-btn>
      </template>
      <div>
        <span>{{ editing ? textEditing : textDefault }}</span>
      </div>
    </v-tooltip>
  </div>
</template>

<script>
export default {
  props: {
    editing: { type: Boolean, required: true },
    permission: { type: Boolean, default: true },
    onCancel: { type: Function, required: true },
    onEdit: { type: Function, required: true },
    iconEditing: { type: String, default: "mdi-check" },
    iconCancel: { type: String, default: "mdi-close" },
    iconDefault: { type: String, default: "mdi-pencil" },
    textEditing: { type: String, default: "Guardar" },
    textCancel: { type: String, default: "Cancelar" },
    textDefault: { type: String, default: "Editar" },
    cancelStyle: { type: Object, default: () => ({}) },
    editStyle: { type: Object, default: () => ({}) },
  },
};
</script>

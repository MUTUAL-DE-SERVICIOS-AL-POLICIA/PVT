<template>
  <v-breadcrumbs
    :items="$store.getters.breadcrumbs"
    divider=">"
    large
    class="ml-0 pl-0"
  >
    <template v-slot:item="props">
      <v-breadcrumbs-item
        class="v-toolbar__title"
        :to="props.item.to"
        active-class="font-weight-regular"
      >
        {{ props.item.text }}
      </v-breadcrumbs-item>
    </template>
  </v-breadcrumbs>
</template>

<script>
export default {
  name: 'breadcrumbs'
}
</script>
<template>
  <v-card flat>
    <v-card-title>
      <v-toolbar dense color="tertiary">
        <v-toolbar-title>Perfil</v-toolbar-title>
      </v-toolbar>
    </v-card-title>
    <v-card-text>
      <v-card>
        <v-card-text>
          <v-simple-table dense>
            <tbody>
              <tr>
                <td class="font-weight-black text-end pr-5">Usuario:</td>
                <td class="text-left">{{ $store.getters.user }}</td>
                <td class="font-weight-black text-end pr-5">Roles:</td>
                <td class="text-left">{{ $store.getters.roles.join(', ') }}</td>
                <td v-if="!$store.getters.ldapAuth || $store.getters.user == 'admin'">
                  <ChangePassword/>
                </td>
              </tr>
            </tbody>
          </v-simple-table>
        </v-card-text>
      </v-card>
    </v-card-text>
  </v-card>
</template>

<script>
import ChangePassword from '@/components/login/ChangePassword'

export default {
  name: "Profile",
  components: {
    ChangePassword
  }
}
</script>

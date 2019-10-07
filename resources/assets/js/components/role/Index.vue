<template>
  <v-card flat>
    <v-card-title>
      <v-toolbar dense color="tertiary">
        <v-toolbar-title>Asignación de permisos</v-toolbar-title>
      </v-toolbar>
    </v-card-title>
    <v-card-text>
      <v-card>
        <v-card-title>
          <v-row align="center" no-gutters>
            <v-col cols="12" md="6">
              <v-select
                v-model="selectedModule"
                :items="modules"
                label="Módulo"
                item-text="description"
                item-value="id"
                :loading="loading"
                prepend-inner-icon="mdi-format-list-checks"
                class="mx-3"
                dense
                flat
                outlined
                shaped
                solo
              ></v-select>
            </v-col>
            <v-col cols="12" md="6">
              <v-select
                v-model="selectedRole"
                :items="roles"
                label="Rol"
                item-text="display_name"
                item-value="id"
                :loading="loading"
                prepend-inner-icon="mdi-security"
                class="mx-3"
                :disabled="!selectedModule"
                dense
                flat
                outlined
                shaped
                solo
              ></v-select>
            </v-col>
          </v-row>
        </v-card-title>
        <v-card-text v-if="selectedRole">
          <div class="title">
            <span>Permisos para el rol </span>
            <span class="font-weight-black">{{ roles.find(o => o.id == selectedRole).display_name }}</span>
          </div>
          <v-row no-gutters>
            <template v-for="(permissionsColumn, index) in chunkedPermissions">
              <v-col :key="index">
                <div
                  v-for="permission in permissionsColumn"
                  :key="permission.id"
                  class="my-3"
                >
                  <v-hover v-slot:default="{ hover }">
                    <v-chip
                      :class="hover ? 'elevation-4' : 'elevation-0'"
                      :color="selectedPermissions.includes(permission.id) ? 'info' : 'secondary'"
                      dark
                      style="width: 280px;"
                      :outlined="!selectedPermissions.includes(permission.id)"
                      @click.stop="switchPermission(permission.id)"
                    >
                      <v-avatar left v-if="selectedPermissions.includes(permission.id)">
                        <v-icon>mdi-checkbox-marked-circle</v-icon>
                      </v-avatar>
                      {{ permission.display_name }}
                    </v-chip>
                  </v-hover>
                </div>
              </v-col>
            </template>
          </v-row>
        </v-card-text>
      </v-card>
    </v-card-text>
  </v-card>
</template>

<script>
export default {
  name: "role-index",
  data: () => ({
    loading: true,
    permissions: [],
    modules: [],
    roles: [],
    selectedModule: null,
    selectedRole: null,
    selectedPermissions: []
  }),
  watch: {
    selectedModule(newVal, oldVal) {
      if (newVal != oldVal && newVal != null) {
        this.selectedRole = null
        this.selectedPermissions = []
        this.getModuleRoles(newVal)
      }
    },
    selectedRole(newVal, oldVal) {
      if (newVal != oldVal && newVal != null) {
        this.selectedPermissions = []
        this.getRolePermissions(newVal)
      }
    }
  },
  computed: {
    chunkedPermissions() {
      return _.chunk(this.permissions, 8)
    }
  },
  mounted() {
    this.getPermissions()
    this.getModules()
  },
  methods: {
    async switchPermission(id) {
      try {
        if (this.$store.getters.permissions.includes('update-role')) {
          this.loading = true
          if (this.selectedPermissions.includes(id)) {
            this.selectedPermissions = this.selectedPermissions.filter(o => o != id)
          } else {
            this.selectedPermissions.push(id)
          }
          let res = await axios.post(`role/${this.selectedRole}/permission`, {
            permissions: this.selectedPermissions
          })
          this.selectedPermissions = res.data
          this.toast('Actualizado correctamente', 'success')
        }
      } catch (e) {
        console.log(e)
        this.selectedPermissions = this.selectedPermissions.filter(o => o != id)
      } finally {
        this.loading = false
      }
    },
    async getRolePermissions(id) {
      try {
        this.loading = true
        let res = await axios.get(`role/${id}/permission`)
        this.selectedPermissions = res.data
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
    async getModuleRoles(id) {
      try {
        this.loading = true
        let res = await axios.get(`module/${id}/role`)
        this.roles = res.data
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
    async getPermissions() {
      try {
        this.loading = true
        let res = await axios.get(`permission`)
        this.permissions = res.data
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
    async getModules() {
      try {
        this.loading = true
        let res = await axios.get(`module`)
        this.modules = res.data
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    }
  }
};
</script>

<template>
  <v-card flat>
    <v-card-title>
      <v-toolbar dense color="tertiary">
        <v-toolbar-title>Asignación de permisos</v-toolbar-title>
      </v-toolbar>
    </v-card-title>
    <v-card-text>
      <v-card>
        <v-card-text>
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
        </v-card-text>
        <v-card v-if="selectedRole">
          <v-card-title>
            <v-toolbar dense color="grey lighten-3">
              <v-toolbar-title>
                <span>Permisos para el rol </span>
                <span class="font-weight-black">{{ roles.find(o => o.id == selectedRole).display_name }}</span>
              </v-toolbar-title>
              <v-spacer></v-spacer>
              <v-divider
                class="mx-2"
                inset
                vertical
              ></v-divider>
              <v-flex xs3>
                <Search @search="search = $event"/>
              </v-flex>
            </v-toolbar>
          </v-card-title>
          <v-card-text>
            <v-row v-if="permissions.length && !loading">
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
            <Loading v-else-if="loading"/>
            <div v-else class="text-center data-box">
              No hay datos disponibles
            </div>
            <v-row>
              <v-pagination
                v-model="options.page"
                :length="options.lastPage"
                :total-visible="8"
                color="secondary"
              ></v-pagination>
            </v-row>
          </v-card-text>
        </v-card>
      </v-card>
    </v-card-text>
  </v-card>
</template>
<script>
import Loading from '@/components/shared/Loading'
import Search from '@/components/shared/Search'
import Module from '@/services/ModuleService'

export default {
  name: "role-index",
  components: {
    Loading,
    Search
  },
  data: function() {
    return {
      loading: true,
      search: '',
      options: {
        page: 1,
        lastPage: 1,
        itemsPerPage: 32,
        sortBy: ['name'],
        sortDesc: [false]
      },
      permissions: [],
      modules: [],
      roles: [],
      selectedModule: null,
      selectedRole: null,
      selectedPermissions: []
    }
  },
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
    },
    search() {
      this.options.page = 1
      this.getPermissions()
    },
    options: {
      deep: true,
      handler() {
        this.getPermissions()
      }
    }
  },
  computed: {
    chunkedPermissions() {
      return _.chunk(this.permissions, 8)
    }
  },
  mounted() {
    this.getModules()
  },
  methods: {
    async switchPermission(id) {
      try {
        if (this.$store.getters.permissionSimpleSelected.includes('update-role')) {
          this.loading = true
          if (this.selectedPermissions.includes(id)) {
            this.selectedPermissions = this.selectedPermissions.filter(o => o != id)
          } else {
            this.selectedPermissions.push(id)
          }
          let res = await axios.patch(`role/${this.selectedRole}/permission`, {
            permissions: this.selectedPermissions
          })
          this.selectedPermissions = res.data.permissions
          this.toastr.success('Actualizado correctamente')
        } else {
          this.toastr.warning('No autorizado')
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
        this.selectedPermissions = res.data.permissions
        this.getPermissions()
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
        this.search = ''
        this.options.page = 1
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
    async getPermissions() {
      try {
        this.loading = true
        let res = await axios.get(`permission`, {
          params: {
            page: this.options.page,
            per_page: this.options.itemsPerPage,
            sortBy: this.options.sortBy,
            sortDesc: this.options.sortDesc,
            search: this.search
          }
        })
        this.permissions = res.data.data
        delete res.data['data']
        this.options.page = res.data.current_page
        this.options.lastPage = res.data.last_page
        this.options.itemsPerPage = parseInt(res.data.per_page)
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
    getModules() {
      this.loading = true
      const module = new Module()
      module.get(null, {
        page: 1,
        per_page: 100,
        sortBy: ['description'],
        sortDesc: [false]
      }).then(res => {
        this.modules = res.data
      }).catch(e => {
        console.log(e)
      }).finally(() => {
        this.loading = false
      })
    }
  }
};
</script>

<template>
  <v-data-table
    :headers="headers"
    :items="users"
    :loading="loading"
    :options.sync="options"
    :server-items-length="totalUsers"
    :footer-props="{ itemsPerPageOptions: [8, 15, 30] }"
    multi-sort
    single-expand
  >
    <template v-slot:item="props">
      <tr :class="props.isExpanded ? 'secondary white--text' : ''">
        <td @click.stop="expand(props)">{{ props.item.last_name | uppercase }}</td>
        <td @click.stop="expand(props)">{{ props.item.first_name | uppercase }}</td>
        <td @click.stop="expand(props)">{{ props.item.position | uppercase }}</td>
        <td @click.stop="expand(props)">{{ props.item.username | lowercase }}</td>
        <td v-if="active">
          <v-tooltip top v-if="$store.getters.permissions.includes('update-user')">
            <template v-slot:activator="{ on }">
              <v-btn
                fab
                dark
                x-small
                color="warning"
                v-on="on"
                @click.stop="switchActiveUser(props.item.id)"
              >
                <v-icon>mdi-cancel</v-icon>
              </v-btn>
            </template>
            <span class="caption">Deshabilitar</span>
          </v-tooltip>
        </td>
        <td v-else>
          <v-tooltip top v-if="$store.getters.permissions.includes('update-user')">
            <template v-slot:activator="{ on }">
              <v-btn
                fab
                dark
                x-small
                color="success"
                class="mr-2"
                v-on="on"
                @click.stop="switchActiveUser(props.item.id)"
              >
                <v-icon>mdi-sync</v-icon>
              </v-btn>
            </template>
            <span class="caption">Habilitar</span>
          </v-tooltip>
          <v-tooltip top  v-if="$store.getters.permissions.includes('delete-user')">
            <template v-slot:activator="{ on }">
              <v-btn
                fab
                dark
                x-small
                color="error"
                v-on="on"
                @click.stop="bus.$emit('openRemoveDialog', `user/${props.item.id}`)"
              >
                <v-icon>mdi-close</v-icon>
              </v-btn>
            </template>
            <span class="caption">Eliminar</span>
          </v-tooltip>
        </td>
      </tr>
    </template>
    <template v-slot:expanded-item="{ headers }">
      <tr>
        <td :colspan="headers.length" class="px-0">
          <Role :user.sync="selectedUser"/>
        </td>
      </tr>
    </template>
  </v-data-table>
</template>

<script>
import Role from '@/components/user/Role'

export default {
  name: 'user-list',
  components: {
    Role
  },
  props: {
    bus: {
      type: Object,
      required: true
    }
  },
  data: () => ({
    loading: true,
    search: '',
    active: true,
    options: {
      page: 1,
      itemsPerPage: 8,
      sortBy: ['last_name'],
      sortDesc: [false]
    },
    users: [],
    selectedUser: 0,
    totalUsers: 0,
    headers: [
      {
        text: 'Apellido',
        value: 'last_name',
        class: ['normal', 'white--text'],
        width: '15%',
        sortable: true
      }, {
        text: 'Nombre',
        value: 'first_name',
        class: ['normal', 'white--text'],
        width: '15%',
        sortable: true
      }, {
        text: 'Cargo',
        value: 'position',
        class: ['normal', 'white--text'],
        width: '50%',
        sortable: true
      }, {
        text: 'Usuario',
        value: 'username',
        class: ['normal', 'white--text'],
        width: '10%',
        sortable: true
      }, {
        text: 'Acciones',
        class: ['normal', 'white--text'],
        width: '10%',
        sortable: false
      }
    ]
  }),
  watch: {
    options: function(newVal, oldVal) {
      if (newVal.page != oldVal.page || newVal.itemsPerPage != oldVal.itemsPerPage || newVal.sortBy != oldVal.sortBy || newVal.sortDesc != oldVal.sortDesc) {
        this.getUsers()
      }
    },
    search: function(newVal, oldVal) {
      if (newVal != oldVal) {
        this.getUsers()
      }
    },
    active: function(newVal, oldVal) {
      if (newVal != oldVal) {
        this.getUsers()
      }
    }
  },
  mounted() {
    this.bus.$on('added', val => {
      this.getUsers()
    })
    this.bus.$on('removed', val => {
      this.getUsers()
    })
    this.bus.$on('search', val => {
      this.search = val
    })
    this.bus.$on('active', val => {
      this.active = val
    })
    this.getUsers()
  },
  methods: {
    expand(props) {
      props.expand(!props.isExpanded && this.active && this.$store.getters.permissions.includes('update-user'))
      if (this.selectedUser != props.item.id) this.selectedUser = props.item.id
    },
    async switchActiveUser(id) {
      try {
        this.loading = true
        let res = await axios.patch(`user/${id}`, {
          active: !this.active
        })
        this.bus.$emit('removed', id)
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    },
    async getUsers(params) {
      try {
        this.loading = true
        let res = await axios.get(`user`, {
          params: {
            page: this.options.page,
            per_page: this.options.itemsPerPage,
            sortBy: this.options.sortBy,
            sortDesc: this.options.sortDesc,
            active: this.active,
            search: this.search
          }
        })
        this.users = res.data.data
        this.totalUsers = res.data.total
        delete res.data['data']
        this.options.page = res.data.current_page
        this.options.itemsPerPage = parseInt(res.data.per_page)
        this.options.totalItems = res.data.total
      } catch (e) {
        console.log(e)
      } finally {
        this.loading = false
      }
    }
  }
}
</script>

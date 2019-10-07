import moment from 'moment'
import jwt from 'jsonwebtoken'
import VuexPersistence from 'vuex-persist'

const vuexLocal = new VuexPersistence({
  key: 'pvt',
  storage: window.localStorage
})

export default {
  state: {
    id: null,
    user: null,
    roles: [],
    permissions: [],
    ldapAuth: JSON.parse(process.env.MIX_LDAP_AUTHENTICATION),
    dateNow: moment().format('Y-MM-DD'),
    token: {
      type: null,
      value: null,
      expiration: null
    },
    breadcrumbs: []
  },
  getters: {
    ldapAuth(state) {
      return state.ldapAuth
    },
    id(state) {
      return state.id
    },
    user(state) {
      return state.user
    },
    roles(state) {
      return state.roles
    },
    permissions(state) {
      return state.permissions
    },
    dateNow(state) {
      return state.dateNow
    },
    token(state) {
      return state.token
    },
    tokenExpired(state) {
      if (state.token.expiration) {
        return moment().isAfter(state.token.expiration)
      }
    },
    breadcrumbs(state) {
      return state.breadcrumbs
    }
  },
  mutations: {
    logout(state) {
      state.id = null
      state.user = null
      state.roles = []
      state.permissions = []
      state.token = {
        type: null,
        value: null,
        expiration: null
      }
    },
    login(state, data) {
      state.id = data.id
      state.user = data.user
      state.roles = data.roles
      state.permissions = data.permissions
      state.token = {
        type: data.token_type,
        value: data.token,
        expiration: data.exp
      }
      axios.defaults.headers.common['Authorization'] = `${data.token_type} ${data.token}`
    },
    setDate(state, newValue) {
      state.dateNow = newValue
    },
    refreshToken(state, data) {
      state.token = {
        type: data.token_type,
        value: data.token,
        expiration: moment.unix(jwt.decode(data.token).exp).format()
      }
      axios.defaults.headers.common['Authorization'] = `${data.token_type} ${data.token}`
    },
    setBreadcrumbs(state, data) {
      state.breadcrumbs = data
    },
    addBreadcrumb(state, index, data) {
      state.breadcrumbs[index] = data
    }
  },
  actions: {
    logout(context) {
      context.commit('logout')
    },
    login({ commit }, data) {
      const payload = jwt.decode(data.token)
      commit('login', Object.assign({ ...data, ...payload }, { exp: moment.unix(payload.exp).format() }))
    },
    async refresh({ commit, state }) {
      try {
        const expiration = moment(state.token.expiration)
        const now = moment()
        if (now.isAfter(expiration.clone().subtract(JSON.parse(process.env.MIX_JWT_TTL)/10, 'minutes')) && expiration.isAfter(now)) {
          const res = await axios.patch(`auth/${state.id}`)
          commit('refreshToken', res.data)
        }
      } catch (e) {
        console.log(e)
      }
    }
  },
  plugins: [vuexLocal.plugin]
}
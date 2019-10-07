// Vue instance
import '@/bootstrap'
Vue.config.productionTip = false
import App from '@/layout/App'

// Toast notification
import toast from '@/plugins/toast'
Vue.mixin({
  methods: {
    toast: toast
  }
})

// Validator
import '@/plugins/vee-validate'

// Vuetify
import vuetify from '@/plugins/vuetify'

// Locale
import VueI18n from 'vue-i18n'
const i18n = new VueI18n({
  locale: 'es'
})

// Router
import router from '@/plugins/router'

// Vuex
import Vuex from 'vuex'
import StoreData from '@/store'
const store = new Vuex.Store(StoreData)

// Moment
import '@/plugins/moment'

// Filters
import '@/plugins/filters'

// JWT
axios.defaults.headers.common['Accept'] = 'application/json'
axios.defaults.headers.common['Content-Type'] = 'application/json'
axios.defaults.headers.common['Authorization'] = `${store.getters.token.type} ${store.getters.token.value}`
axios.interceptors.response.use(response => {
  return response
}, error => {
  if (error.response) {
    if (error.response.status == 401) {
      store.dispatch('logout')
      if (router.currentRoute.name != 'login') {
        router.push('login')
      }
    }
    for (let key in error.response.data.errors) {
      error.response.data.errors[key].forEach(error => {
        toast(error, 'error')
      })
    }
  }
  return Promise.reject(error)
})
router.beforeEach((to, from, next) => {
  const requiresAuth = to.matched.some(record => record.meta.requiresAuth)
  const user = store.state.user
  store.dispatch('refresh')
  if (requiresAuth && !user) {
    next({
      path: '/login'
    })
  } else if (to.path == '/login' && user) {
    next({
      path: '/'
    })
  } else {
    next()
  }
})
if (store.getters.tokenExpired) {
  store.dispatch('logout')
  router.go('login')
}

new Vue({
  router,
  i18n,
  store,
  vuetify,
  render: h => h(App)
}).$mount('#app')
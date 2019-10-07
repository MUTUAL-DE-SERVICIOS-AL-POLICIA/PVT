import moment from 'moment'
import 'moment/locale/es'
moment.locale('es')

Vue.filter('uppercase', value => {
	return value.toUpperCase()
})
Vue.filter('lowercase', value => {
	return value.toLowerCase()
})
Vue.filter('capitalize', value => {
  return value.toLowerCase().split(' ').map(word => `${word.charAt(0).toUpperCase()}${word.slice(1)}`).join(' ')
})
Vue.filter('datetime', value => {
	return moment(value).format('LLL')
})
Vue.filter('fullName', (value, byFirstName = false) => {
  let fullName = []
	if (byFirstName) {
		fullName = [value.first_name, value.second_name, value.last_name, value.mothers_last_name]
	} else {
    fullName = [value.last_name, value.mothers_last_name, value.first_name, value.second_name]
  }
  return fullName.filter(o => o).join(' ')
})
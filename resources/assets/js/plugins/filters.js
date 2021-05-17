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
Vue.filter('datetimeshorted', value => {
	if(value != null)
	return moment(value).format('D/MM/YYYY HH:mm:ss')
	else ''
})
Vue.filter('date', value => {
	if(value != null)
	return moment(value).format('L')
	else ''
})
Vue.filter('money', value => {
	if(value == 0) return '0.00'
	else return value.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
})
Vue.filter('moneyString', value => {
	value=Number(value)
	if(value == ''){
		return 0
	}else{
		if (value) return value.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
	}
  })
Vue.filter('fullName', (value, byFirstName = false) => {
  let fullName = []
	if (byFirstName) {
		fullName = [value.fullname, value.first_name, value.second_name, value.last_name, value.mothers_last_name] // se usa el parametro value.full_name solo para el historial
	} else {
    fullName = [value.lastname, value.mothers_last_name, value.first_name, value.second_name, value.full_name]
  }
  return fullName.filter(o => o).join(' ')
})
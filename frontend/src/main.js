import Vue from 'vue'
import App from './App.vue'
import router from './router'
import store from './store'
import vuetify from './plugins/vuetify'
import 'roboto-fontface/css/roboto/roboto-fontface.css'
import 'material-design-icons-iconfont/dist/material-design-icons.css'

import axios from 'axios'
import VueAxios from 'vue-axios'

import formatDate from './filters/formatDate'
import getIndex from './filters/getIndex'

Vue.use(VueAxios, axios)
Vue.use(formatDate)
Vue.use(getIndex)

Vue.config.productionTip = false

new Vue({
  router,
  store,
  vuetify,
  render: h => h(App)
}).$mount('#app')

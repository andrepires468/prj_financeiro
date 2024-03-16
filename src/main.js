import { createApp } from 'vue'
import App from './App.vue'

import { BootstrapVue, IconsPlugin  } from 'bootstrap-vue'
import {router} from './router'

import 'bootstrap/dist/css/bootstrap.css'
import 'bootstrap-vue/dist/bootstrap-vue.css'


const app = createApp(App)


app.use(
    BootstrapVue,
    IconsPlugin
)

app.use(router).mount('#app')




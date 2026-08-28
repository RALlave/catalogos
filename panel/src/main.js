import { createPinia } from 'pinia'
import { createApp } from 'vue'

import App from './App.vue'
import { router } from './router'

import './assets/css/base.css'
import './assets/css/components.css'
import './assets/css/layout.css'
import './assets/css/auth.css'
import './assets/css/themes.css'

createApp(App)
    .use(createPinia())
    .use(router)
    .mount('#app')

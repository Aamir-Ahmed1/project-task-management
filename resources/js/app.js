import { createApp } from 'vue'
import router from './router'
import { useAuth } from './composables/useAuth'
import App from './components/App.vue'

const { loadFromStorage } = useAuth()
loadFromStorage()

const app = createApp(App)
app.use(router)
app.mount('#app')

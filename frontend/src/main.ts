import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import './style.css'
import { useAuthStore } from './stores/auth'

// Manejo global de errores
window.addEventListener('error', (e) => {
  console.error('Global error:', e.error)
})
window.addEventListener('unhandledrejection', (e) => {
  console.error('Unhandled rejection:', e.reason)
})

const app = createApp(App)
app.use(createPinia())

// Hydrate auth BEFORE registering router to avoid race condition
const auth = useAuthStore()
await auth.fetchMe()

app.use(router)
app.mount('#app')
